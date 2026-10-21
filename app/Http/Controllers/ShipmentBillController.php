<?php

namespace App\Http\Controllers;

use App\Exports\SheetsExport;
use App\Models\Agent;
use App\Models\CostType;
use App\Models\ItemsShipment;
use App\Models\ItemsShipmentCost;
use App\Models\ItemsShipmentPayment;
use App\Models\ShipmentBillPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\AccountingService;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Accounting -> Shipment Billing (what WE owe: freight, CNF, tax, warehouse ... per shipment).
 * One voucher can settle cost lines of several shipments at once.
 */
class ShipmentBillController extends Controller
{
    public const METHODS = [
        'cash'           => 'Cash',
        'bank'           => 'Bank Transfer',
        'check'          => 'Check',
        'mobile_banking' => 'Mobile Banking',
    ];

    /** Overview: every shipment with cost / paid / due + recent vouchers. */
    public function index(Request $request)
    {
        abort_unless(Gate::allows('view-shipment-cost'), 403);

        $onlyDue = $request->boolean('only_due');

        $totals = ItemsShipmentCost::query()
            ->selectRaw('items_shipment_id,
                         COUNT(*) as `lines`,
                         SUM(CASE WHEN due_amount > 0 THEN 1 ELSE 0 END) as due_lines,
                         SUM(final_bdt_cost) as total_cost,
                         SUM(paid_amount) as total_paid,
                         SUM(due_amount) as total_due')
            ->groupBy('items_shipment_id')
            ->get()
            ->keyBy('items_shipment_id');

        $shipments = ItemsShipment::query()
            ->orderByDesc('shipment_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($s) use ($totals) {
                $t = $totals->get($s->id);
                $s->bill = [
                    'lines'      => (int) ($t->lines ?? 0),
                    'due_lines'  => (int) ($t->due_lines ?? 0),
                    'total_cost' => (float) ($t->total_cost ?? 0),
                    'total_paid' => (float) ($t->total_paid ?? 0),
                    'total_due'  => (float) ($t->total_due ?? 0),
                ];
                return $s;
            });

        $grand = [
            'cost' => round($shipments->sum(fn ($s) => $s->bill['total_cost']), 2),
            'paid' => round($shipments->sum(fn ($s) => $s->bill['total_paid']), 2),
            'due'  => round($shipments->sum(fn ($s) => $s->bill['total_due']), 2),
        ];

        if ($onlyDue) {
            $shipments = $shipments->filter(fn ($s) => $s->bill['total_due'] > 0)->values();
        }

        $vouchers = ShipmentBillPayment::with(['user', 'agent'])
            ->withCount('payments')
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        if (($request->boolean('export') || $request->boolean('pdf'))) {
            return $this->exportIndex($shipments, $grand, $vouchers);
        }

        return view('admin.billing.index', compact('shipments', 'grand', 'vouchers', 'onlyDue'));
    }

    /**
     * Shipment bill ledger (what we owe): every bill and every payment with a running balance.
     * Filters: shipment(s), agent, cost type, date range.
     */
    public function ledger(Request $request, AccountingService $acc)
    {
        abort_unless(Gate::allows('view-shipment-cost'), 403);

        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);

        $filters = [
            'shipment_ids' => array_map('intval', (array) $request->input('shipment_ids', [])),
            'agent_id'     => $request->integer('agent_id') ?: null,
            'cost_type_id' => $request->integer('cost_type_id') ?: null,
            'from'         => $request->input('from'),
            'to'           => $request->input('to'),
        ];

        $led = $acc->payableLedger($filters);

        if (($request->boolean('export') || $request->boolean('pdf'))) {
            return $this->exportLedger($led, $filters);
        }

        $shipments = ItemsShipment::orderByDesc('shipment_date')->orderByDesc('id')->get(['id', 'shipment_name', 'shipment_date']);
        $agents    = Agent::orderBy('name')->get(['id', 'name']);
        $costTypes = CostType::orderBy('name')->get(['id', 'name']);
        $methods   = self::METHODS;

        return view('admin.billing.ledger', compact('led', 'filters', 'shipments', 'agents', 'costTypes', 'methods'));
    }

    private function exportLedger(array $led, array $f)
    {
        $period = ($f['from'] || $f['to']) ? (($f['from'] ?: 'start') . ' to ' . ($f['to'] ?: 'today')) : 'All time';
        $method = fn ($m) => $m ? (self::METHODS[$m] ?? ucfirst($m)) : '';

        $ledger = [
            'title'    => 'Bill Ledger',
            'heading'  => 'Shipment Bill Ledger (payable)',
            'subtitle' => 'Period: ' . $period . '  |  Opening ' . number_format($led['opening'], 2) . '  |  Closing (we owe) ' . number_format($led['closing'], 2),
            'headings' => ['Date', 'Document', 'Shipment', 'Agent / Payee', 'Description', 'Details', 'Method', 'Reference', 'Entered by', 'Bill (we owe)', 'Payment', 'Balance (we owe)'],
            'rows'     => array_merge(
                [['', '', '', '', 'Opening balance', '', '', '', '', '', '', $led['opening']]],
                $led['entries']->map(fn ($e) => [
                    $e['date'], $e['doc'], $e['shipment'], $e['party'], $e['desc'], $e['detail'], $method($e['method']),
                    $e['reference'], $e['user'], $e['bill'] ?: '', $e['paid'] ?: '', $e['balance'],
                ])->all()
            ),
            'totals'   => ['', '', '', '', 'TOTAL / CLOSING', '', '', '', '', $led['bills'], $led['paid'], $led['closing']],
            'money'    => [9, 10, 11],
        ];

        $byShip = [
            'title'    => 'By Shipment',
            'heading'  => 'Bill position by shipment',
            'headings' => ['Shipment', 'Shipment date', 'Cost lines', 'Total bill', 'Paid', 'Due'],
            'rows'     => $led['by_shipment']->map(fn ($r) => [$r['name'], $r['date'], $r['lines'], $r['bill'], $r['paid'], $r['due']])->all(),
            'totals'   => ['TOTAL', '', $led['by_shipment']->sum('lines'), $led['total_bill'], $led['total_paid'], $led['total_due']],
            'money'    => [3, 4, 5], 'int' => [2],
        ];

        $byAgent = [
            'title'    => 'By Agent',
            'heading'  => 'Bill position by agent / payee',
            'headings' => ['Agent', 'Cost lines', 'Total bill', 'Paid', 'Due'],
            'rows'     => $led['by_agent']->map(fn ($r) => [$r['name'], $r['lines'], $r['bill'], $r['paid'], $r['due']])->all(),
            'totals'   => ['TOTAL', $led['by_agent']->sum('lines'), $led['total_bill'], $led['total_paid'], $led['total_due']],
            'money'    => [2, 3, 4], 'int' => [1],
        ];

        return SheetsExport::respond([$ledger, $byShip, $byAgent], 'shipment-bill-ledger-' . date('Y-m-d') . '.xlsx');
    }

    private function exportIndex($shipments, array $grand, $vouchers)
    {
        $ship = [
            'title'    => 'Shipment Bills',
            'heading'  => 'Shipment Billing - cost, paid and due per shipment',
            'headings' => ['Shipment', 'Date', 'Status', 'Cost lines', 'Unpaid lines', 'Total cost (BDT)', 'Paid', 'Due'],
            'rows'     => $shipments->map(fn ($s) => [
                $s->shipment_name, optional($s->shipment_date)->format('Y-m-d'), ucfirst($s->status),
                $s->bill['lines'], $s->bill['due_lines'], $s->bill['total_cost'], $s->bill['total_paid'], $s->bill['total_due'],
            ])->all(),
            'totals'   => ['TOTAL', '', '', $shipments->sum(fn ($s) => $s->bill['lines']), $shipments->sum(fn ($s) => $s->bill['due_lines']),
                           $grand['cost'], $grand['paid'], $grand['due']],
            'money'    => [5, 6, 7], 'int' => [3, 4],
        ];

        $vch = [
            'title'    => 'Vouchers',
            'heading'  => 'Payment vouchers (latest 50)',
            'headings' => ['Voucher', 'Date', 'Method', 'Reference', 'Paid to', 'Lines', 'Amount', 'Saved by'],
            'rows'     => $vouchers->map(fn ($v) => [
                $v->voucher_no, optional($v->payment_date)->format('Y-m-d'), self::METHODS[$v->payment_method] ?? $v->payment_method,
                $v->reference_no, optional($v->agent)->name, $v->payments_count, (float) $v->amount, optional($v->user)->name,
            ])->all(),
            'totals'   => ['TOTAL', '', '', '', '', $vouchers->sum('payments_count'), (float) $vouchers->sum('amount'), ''],
            'money'    => [6], 'int' => [5],
        ];

        return SheetsExport::respond([$ship, $vch], 'shipment-billing-' . date('Y-m-d') . '.xlsx');
    }

    /** Pay form: choose cost lines of many shipments, one amount, split manually or auto. */
    public function create(Request $request)
    {
        abort_unless(Gate::allows('add-bill-payment'), 403);

        $agentId    = $request->integer('agent_id') ?: null;
        $costTypeId = $request->integer('cost_type_id') ?: null;

        $costs = ItemsShipmentCost::with(['itemsShipment', 'costType', 'agent'])
            ->where('due_amount', '>', 0)
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->when($costTypeId, fn ($q) => $q->where('cost_type_id', $costTypeId))
            ->orderBy('items_shipment_id')
            ->orderBy('cost_date')
            ->orderBy('id')
            ->get();

        // oldest shipment first, so "auto split" clears the oldest bills first
        $groups = $costs->groupBy('items_shipment_id')
            ->sortBy(fn ($lines) => optional($lines->first()->itemsShipment)->shipment_date ?? now()->addYears(50));

        $agents    = Agent::orderBy('name')->get();
        $costTypes = CostType::orderBy('name')->get();
        $methods   = self::METHODS;

        $preShipment = (int) $request->query('shipment');
        $preCost     = (int) $request->query('cost');
        $totalDue    = round((float) $costs->sum('due_amount'), 2);

        return view('admin.billing.pay', compact(
            'groups', 'agents', 'costTypes', 'methods',
            'agentId', 'costTypeId', 'preShipment', 'preCost', 'totalDue'
        ));
    }

    /** Save one voucher + one payment line per cost line (all-or-nothing). */
    public function store(Request $request)
    {
        abort_unless(Gate::allows('add-bill-payment'), 403);

        try {
            $cur = \App\Support\PaymentCurrency::apply($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:' . implode(',', array_keys(self::METHODS)),
            'reference_no'   => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'agent_id'       => 'nullable|exists:agents,id',
            'allocations'    => 'required|array',
            'allocations.*'  => 'nullable|numeric|min:0',
        ]);

        $allocations = collect($request->input('allocations', []))
            ->map(fn ($v) => round((float) $v, 2))
            ->filter(fn ($v) => $v > 0);

        if ($allocations->isEmpty()) {
            return back()->withInput()->with('error', 'Allocate the payment to at least one cost line.');
        }

        try {
            $voucher = DB::transaction(function () use ($request, $allocations, $cur) {
                // lock the lines so two accountants cannot overpay the same bill
                $costs = ItemsShipmentCost::with('costType')
                    ->whereIn('id', $allocations->keys()->map(fn ($k) => (int) $k)->all())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($allocations as $costId => $amt) {
                    $cost = $costs->get((int) $costId);

                    if (!$cost) {
                        throw new \Exception("Cost line #{$costId} was not found.");
                    }

                    $due = round((float) $cost->due_amount, 2);

                    if ($amt > $due + 0.004) {
                        throw new \Exception(
                            ($cost->costType->name ?? 'Cost') . ' of shipment #' . $cost->items_shipment_id .
                            ': payment ' . number_format($amt, 2) . ' is more than its due ' . number_format($due, 2) . '.'
                        );
                    }

                    // cost types without partial payment must be paid in full
                    if ($cost->costType && !$cost->costType->have_partial_payment && abs($amt - $due) > 0.004) {
                        throw new \Exception(
                            ($cost->costType->name ?? 'Cost') . ' of shipment #' . $cost->items_shipment_id .
                            ' does not allow partial payment. Pay the full ' . number_format($due, 2) . ' or leave it blank.'
                        );
                    }
                }

                $total = round((float) $allocations->sum(), 2);

                if (abs($total - round((float) $request->amount, 2)) > 0.009) {
                    throw new \Exception('Allocated total (' . number_format($total, 2) .
                        ') must equal the paid amount (' . number_format((float) $request->amount, 2) . ').');
                }

                $voucher = ShipmentBillPayment::create([
                    'user_id'        => Auth::id(),
                    'agent_id'       => $request->agent_id,
                    'amount'         => $total,
                    'currency'       => $cur['currency'],
                    'foreign_amount' => $cur['foreign_amount'],
                    'exchange_rate'  => $cur['exchange_rate'],
                    'payment_date'   => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'reference_no'   => $request->reference_no,
                    'notes'          => $request->notes,
                ]);

                $voucher->update(['voucher_no' => 'SBP-' . str_pad((string) $voucher->id, 6, '0', STR_PAD_LEFT)]);

                $paidBy = optional(Auth::user())->name;

                foreach ($allocations as $costId => $amt) {
                    // model events refresh paid / due / status of the cost line
                    ItemsShipmentPayment::create([
                        'items_shipment_cost_id'   => (int) $costId,
                        'amount'                   => $amt,
                        'currency'                 => $cur['currency'],
                        'exchange_rate'            => $cur['exchange_rate'],
                        'foreign_amount'           => $cur['foreign_amount'] ? round($amt / $cur['exchange_rate'], 2) : null,
                        'payment_date'             => $request->payment_date,
                        'payment_method'           => $request->payment_method,
                        'reference_no'             => $request->reference_no,
                        'notes'                    => $request->notes,
                        'paid_by'                  => $paidBy,
                        'user_id'                  => Auth::id(),
                        'shipment_bill_payment_id' => $voucher->id,
                    ]);
                }

                return $voucher;
            });

            return redirect()->route('shipment-bills.show', $voucher->id)
                ->with('success', 'Payment of ' . number_format($voucher->amount, 2) . ' tk saved as ' . $voucher->voucher_no . '.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error saving payment: ' . $e->getMessage());
        }
    }

    /** One voucher with its split across shipments / cost lines. */
    public function show(ShipmentBillPayment $voucher)
    {
        abort_unless(Gate::allows('view-shipment-cost'), 403);

        $voucher->load([
            'user', 'agent',
            'payments.user',
            'payments.itemsShipmentCost.costType',
            'payments.itemsShipmentCost.agent',
            'payments.itemsShipmentCost.itemsShipment',
        ]);

        $byShipment = $voucher->payments->groupBy(fn ($p) => $p->itemsShipmentCost->items_shipment_id);

        if (request()->boolean('pdf')) {
            $cur = $voucher->currency !== 'bdt'
                ? ' | Paid in ' . strtoupper($voucher->currency) . ' ' . number_format((float) $voucher->foreign_amount, 2) . ' @ ' . rtrim(rtrim(number_format((float) $voucher->exchange_rate, 4), '0'), '.') . ' = ' . number_format($voucher->amount, 2) . ' BDT'
                : '';
            $sheet = [
                'title' => 'Voucher', 'heading' => 'Payment voucher ' . ($voucher->voucher_no ?? ('#' . $voucher->id)),
                'subtitle' => 'Date ' . optional($voucher->payment_date)->format('d M Y') . ' | Method ' . ucfirst(str_replace('_', ' ', $voucher->payment_method))
                              . ' | Ref ' . ($voucher->reference_no ?: '-') . ' | Paid to ' . (optional($voucher->agent)->name ?? '-') . $cur,
                'headings' => ['Shipment', 'Cost type', 'Agent', 'Amount (BDT)'],
                'rows' => $voucher->payments->map(fn ($p) => [
                    optional($p->itemsShipmentCost->itemsShipment)->shipment_name, optional($p->itemsShipmentCost->costType)->name,
                    optional($p->itemsShipmentCost->agent)->name, (float) $p->amount,
                ])->all(),
                'totals' => ['TOTAL', '', '', (float) $voucher->amount], 'money' => [3],
            ];
            return \App\Support\SheetsPdf::download([$sheet], 'voucher-' . ($voucher->voucher_no ?? $voucher->id) . '.pdf');
        }

        return view('admin.billing.show', compact('voucher', 'byShipment'));
    }

    /** Delete a whole voucher: every line is removed and the cost dues go back up. */
    public function destroy(ShipmentBillPayment $voucher)
    {
        abort_unless(Gate::allows('delete-bill-payment'), 403);

        try {
            DB::transaction(function () use ($voucher) {
                // delete line by line so the model events recalc every cost line
                $voucher->payments()->get()->each->delete();
                ShipmentBillPayment::whereKey($voucher->id)->delete();
            });

            return redirect()->route('shipment-bills.index')
                ->with('success', 'Voucher deleted. Shipment cost dues were recalculated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting voucher: ' . $e->getMessage());
        }
    }
}
