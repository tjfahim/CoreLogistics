<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Services\CustomerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LedgerController extends Controller
{
    public function __construct(private CustomerLedgerService $ledger)
    {
    }

    /**
     * Ledger index — every customer with billed/paid/due summary.
     * (same row keys as before, so the index blade keeps working)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::with('cargoItems.itemshipments')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('customer_code', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->get();

        // one query for all paid totals, one for last payment dates
        $paidByCustomer = CustomerPaymentAllocation::query()
            ->join('customer_payments', 'customer_payments.id', '=', 'customer_payment_allocations.customer_payment_id')
            ->selectRaw('customer_payments.customer_id as cid, SUM(customer_payment_allocations.amount) as total')
            ->groupBy('customer_payments.customer_id')
            ->pluck('total', 'cid');

        $lastPayments = CustomerPayment::selectRaw('customer_id, MAX(payment_date) as last_date')
            ->groupBy('customer_id')
            ->pluck('last_date', 'customer_id');

        $ledgerRows = $customers->map(function ($customer) use ($paidByCustomer, $lastPayments) {
            $billed      = 0;
            $shipmentIds = [];

            foreach ($customer->cargoItems as $item) {
                foreach ($item->itemshipments as $shipment) {
                    $shipmentIds[$shipment->id] = true;
                    if ($item->hasPrice()) {
                        $billed += (float) ($item->total_price ?? 0);
                    }
                }
            }

            $paid = (float) ($paidByCustomer[$customer->id] ?? 0);
            $due  = max($billed - $paid, 0);

            return [
                'customer'          => $customer,
                'total_billed'      => $billed,
                'total_paid'        => $paid,
                'total_due'         => $due,
                'shipment_count'    => count($shipmentIds),
                'last_payment_date' => $lastPayments[$customer->id] ?? null,
                'status'            => $billed == 0
                    ? 'no-billing'
                    : ($due <= 0 ? 'clear' : 'due'),
            ];
        });

        $grandTotalBilled = $ledgerRows->sum('total_billed');
        $grandTotalPaid   = $ledgerRows->sum('total_paid');
        $grandTotalDue    = $ledgerRows->sum('total_due');

        return view('admin.ledger.index', compact(
            'ledgerRows', 'grandTotalBilled', 'grandTotalPaid', 'grandTotalDue', 'search'
        ));
    }

    /**
     * Full ledger for one customer: shipments (with items modal), statement, payment history.
     */
    public function customer(Customer $customer)
    {
        $customer->load('extraShippingMarks');

        $shipmentSummaries = $this->ledger->shipmentBills($customer);
        $totals            = $this->ledger->totals($shipmentSummaries);

        $totalBilled = $totals['billed'];
        $totalPaid   = $totals['paid'];
        $totalDue    = $totals['due'];

        $payments = CustomerPayment::where('customer_id', $customer->id)
            ->with(['user', 'allocations.itemshipment', 'paymentItems'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        // ---------- Statement of account ----------
        $ledgerEntries = collect();

        foreach ($shipmentSummaries as $s) {
            if ($s['billed'] > 0) {
                $ledgerEntries->push([
                    'date'        => $s['shipment']->shipment_date ?? $s['shipment']->created_at,
                    'type'        => 'debit',
                    'description' => 'Billed — Shipment: ' . $s['shipment']->shipment_name,
                    'details'     => $s['priced_items'] . ' priced item(s)',
                    'reference'   => $s['shipment']->shipment_name,
                    'amount'      => $s['billed'],
                ]);
            }
        }

        foreach ($payments as $payment) {
            $breakdown = $payment->allocations
                ->map(fn ($a) => ($a->itemshipment->shipment_name ?? ('#' . $a->itemshipment_id)) . ': ' . number_format($a->amount, 0))
                ->implode(' | ');

            $ledgerEntries->push([
                'date'        => $payment->payment_date,
                'type'        => 'credit',
                'description' => 'Payment Received' . ($payment->reference_no ? ' (Ref: ' . $payment->reference_no . ')' : ''),
                'details'     => $breakdown,
                'reference'   => $payment->reference_no,
                'amount'      => $payment->amount,
            ]);
        }

        // by date; on the same day bills come before payments
        $ledgerEntries = $ledgerEntries
            ->sortBy(fn ($e) => Carbon::parse($e['date'])->format('Y-m-d') . ($e['type'] === 'debit' ? '0' : '1'))
            ->values();

        $balance = 0;
        $ledgerEntries = $ledgerEntries->map(function ($entry) use (&$balance) {
            $balance += $entry['type'] === 'debit' ? $entry['amount'] : -$entry['amount'];
            $entry['balance'] = $balance;
            return $entry;
        });

        return view('admin.ledger.show', compact(
            'customer',
            'shipmentSummaries',
            'ledgerEntries',
            'payments',
            'totals',
            'totalBilled',
            'totalPaid',
            'totalDue'
        ));
    }
}