<?php

namespace App\Http\Controllers;

use App\Exports\SheetsExport;
use App\Models\Customer;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Customer ledger (money customers owe us).
 * Entries are always built from the real bills and receipts, so the ledger can never drift from the data.
 */
class LedgerController extends Controller
{
    public function __construct(private AccountingService $acc)
    {
    }

    /** All customers with billed / received / due. */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status'); // due | clear | no-billing

        $customers = Customer::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('customer_code', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'customer_code', 'phone']);

        $bal = $this->acc->customerBalances($customers->pluck('id')->all());

        $ledgerRows = $customers->map(function ($c) use ($bal) {
            $b = $bal->get($c->id);
            $billed = (float) ($b->billed ?? 0);
            $due    = (float) ($b->due ?? 0);

            return [
                'customer'          => $c,
                'total_billed'      => $billed,
                'total_paid'        => (float) ($b->paid ?? 0),
                'total_due'         => $due,
                'shipment_count'    => (int) ($b->shipments ?? 0),
                'shipments_due'     => (int) ($b->shipments_due ?? 0),
                'last_payment_date' => $b->last_payment ?? null,
                'oldest_due_date'   => $b->oldest_due_date ?? null,
                'status'            => $billed == 0 ? 'no-billing' : ($due <= 0 ? 'clear' : 'due'),
            ];
        });

        if (in_array($status, ['due', 'clear', 'no-billing'], true)) {
            $ledgerRows = $ledgerRows->where('status', $status)->values();
        }

        $grandTotalBilled = $ledgerRows->sum('total_billed');
        $grandTotalPaid   = $ledgerRows->sum('total_paid');
        $grandTotalDue    = $ledgerRows->sum('total_due');

        if (($request->boolean('export') || $request->boolean('pdf'))) {
            return $this->exportIndex($ledgerRows, $grandTotalBilled, $grandTotalPaid, $grandTotalDue);
        }

        return view('admin.ledger.index', compact(
            'ledgerRows', 'grandTotalBilled', 'grandTotalPaid', 'grandTotalDue', 'search', 'status'
        ));
    }

    /** Full ledger of one customer. Optional date range: ?from=YYYY-MM-DD&to=YYYY-MM-DD */
    public function customer(Request $request, Customer $customer)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);

        $from = $request->input('from');
        $to   = $request->input('to');

        $customer->load('extraShippingMarks');

        $st = $this->acc->customerStatement($customer, $from, $to);

        if (($request->boolean('export') || $request->boolean('pdf'))) {
            return $this->exportCustomer($customer, $st, $from, $to);
        }

        $shipmentSummaries = $st['shipments'];
        $totals            = $st['totals'];
        $payments          = $st['payments'];
        $payments->load('paymentItems');

        // items of every shipment for the "Items" popup
        $itemsByShipment = $customer->cargoItems()
            ->with('itemshipments:id')
            ->get()
            ->flatMap(fn ($i) => $i->itemshipments->map(fn ($s) => [$s->id, $i]))
            ->groupBy(0)
            ->map(fn ($g) => $g->pluck(1)->values());

        return view('admin.ledger.show', compact(
            'customer', 'st', 'shipmentSummaries', 'totals', 'payments', 'itemsByShipment', 'from', 'to'
        ));
    }

    // ------------------------------------------------------------------ Excel

    private function exportIndex($rows, $billed, $paid, $due)
    {
        $sheet = [
            'title'    => 'Customer Ledger',
            'heading'  => 'Customer Ledger - all customers',
            'subtitle' => 'Generated ' . now()->format('d M Y H:i') . '  |  Receivable: ' . number_format($due, 2),
            'headings' => ['Customer', 'Code', 'Phone', 'Shipments', 'Shipments with due', 'Total billed', 'Total received', 'Total due', 'Oldest due shipment', 'Last payment', 'Status'],
            'rows'     => $rows->map(fn ($r) => [
                $r['customer']->name, $r['customer']->customer_code, $r['customer']->phone,
                $r['shipment_count'], $r['shipments_due'], $r['total_billed'], $r['total_paid'], $r['total_due'],
                $r['oldest_due_date'] ?? '', $r['last_payment_date'] ? substr($r['last_payment_date'], 0, 10) : '',
                ['due' => 'Due', 'clear' => 'Clear', 'no-billing' => 'No billing'][$r['status']],
            ])->all(),
            'totals'   => ['TOTAL', '', '', '', '', $billed, $paid, $due, '', '', ''],
            'money'    => [5, 6, 7],
            'int'      => [3, 4],
        ];

        return SheetsExport::respond([$sheet], 'customer-ledger-' . date('Y-m-d') . '.xlsx');
    }

    private function exportCustomer(Customer $c, array $st, ?string $from, ?string $to)
    {
        $period = ($from || $to) ? (($from ?: 'start') . ' to ' . ($to ?: 'today')) : 'All time';

        $stmt = [
            'title'    => 'Statement',
            'heading'  => 'Statement of Account - ' . $c->name . ' (' . $c->customer_code . ')',
            'subtitle' => 'Period: ' . $period . '  |  Opening ' . number_format($st['opening'], 2) . '  |  Closing ' . number_format($st['closing'], 2),
            'headings' => ['Date', 'Document', 'Description', 'Details', 'Method', 'Reference', 'Entered by', 'Debit (billed)', 'Credit (received)', 'Balance (due)'],
            'rows'     => array_merge(
                [[ '', '', 'Opening balance', '', '', '', '', '', '', $st['opening'] ]],
                $st['entries']->map(fn ($e) => [
                    $e['date'], $e['doc'], $e['desc'], $e['detail'], $e['method'] ? ucfirst(str_replace('_', ' ', $e['method'])) : '',
                    $e['reference'], $e['user'], $e['debit'] ?: '', $e['credit'] ?: '', $e['balance'],
                ])->all()
            ),
            'totals'   => ['', '', 'TOTAL / CLOSING', '', '', '', '', $st['debit'], $st['credit'], $st['closing']],
            'money'    => [7, 8, 9],
        ];

        $ship = [
            'title'    => 'Shipments',
            'heading'  => 'Shipment summary - ' . $c->name,
            'headings' => ['Shipment', 'Date', 'Items', 'Priced items', 'Weight (kg)', 'Billed', 'Received', 'Due', 'Status'],
            'rows'     => $st['shipments']->map(fn ($s) => [
                $s->shipment_name, $s->date, $s->items, $s->priced_items, $s->weight, $s->billed, $s->paid, $s->due,
                ['paid' => 'Paid', 'partial' => 'Partial', 'due' => 'Due', 'no-billing' => 'No billing'][$s->status],
            ])->all(),
            'totals'   => ['TOTAL', '', $st['shipments']->sum('items'), $st['shipments']->sum('priced_items'), $st['shipments']->sum('weight'),
                           $st['totals']['billed'], $st['totals']['paid'], $st['totals']['due'], ''],
            'money'    => [4, 5, 6, 7],
            'int'      => [2, 3],
        ];

        return SheetsExport::respond([$stmt, $ship], 'ledger-' . preg_replace('/[^A-Za-z0-9_-]/', '', $c->customer_code ?: $c->id) . '-' . date('Y-m-d') . '.xlsx');
    }
}
