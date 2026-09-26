<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LedgerController extends Controller
{
    /**
     * Ledger index — every customer with billed/paid/due summary.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('customer_code', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->get();

        $ledgerRows = $customers->map(function ($customer) {
            $pricedItems = $customer->cargoItems()
                ->where(function ($q) {
                    $q->whereNotNull('price')->orWhereNotNull('price_per_kg');
                })
                ->get();

            $totalBilled = $pricedItems->sum('total_price');
            $totalPaid   = CustomerPayment::where('customer_id', $customer->id)->sum('amount');
            $totalDue    = max($totalBilled - $totalPaid, 0);

            $lastPayment = CustomerPayment::where('customer_id', $customer->id)
                ->orderByDesc('payment_date')
                ->first();

            $shipmentCount = $customer->cargoItems()
                ->with('itemshipments')
                ->get()
                ->pluck('itemshipments')
                ->flatten()
                ->pluck('id')
                ->unique()
                ->count();

            return [
                'customer'          => $customer,
                'total_billed'      => $totalBilled,
                'total_paid'        => $totalPaid,
                'total_due'         => $totalDue,
                'shipment_count'    => $shipmentCount,
                'last_payment_date' => $lastPayment?->payment_date,
                'status'            => $totalBilled == 0
                    ? 'no-billing'
                    : ($totalDue <= 0 ? 'clear' : 'due'),
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
     * Full ledger detail for a single customer: shipments, statement, payment history.
     */
    public function customer(Customer $customer)
    {
        $customer->load('extraShippingMarks');

        $shipmentIds = $customer->cargoItems()
            ->with('itemshipments')
            ->get()
            ->pluck('itemshipments')
            ->flatten()
            ->pluck('id')
            ->unique();

        $shipments = ItemsShipment::whereIn('id', $shipmentIds)
            ->orderBy('shipment_date')
            ->get();

        $ledgerEntries = collect();
        $shipmentSummaries = [];

        foreach ($shipments as $shipment) {
            $items = $customer->cargoItems()
                ->whereHas('itemshipments', function ($q) use ($shipment) {
                    $q->where('items_shipments.id', $shipment->id);
                })
                ->get();

            $pricedItems   = $items->filter(fn ($i) => $i->hasPrice());
            $shipmentTotal = $pricedItems->sum('total_price');

            $shipmentPaid = CustomerPayment::where('customer_id', $customer->id)
                ->whereHas('paymentItems.cargoItem.itemshipments', function ($q) use ($shipment) {
                    $q->where('items_shipments.id', $shipment->id);
                })
                ->sum('amount');

            $shipmentSummaries[] = [
                'shipment'     => $shipment,
                'total_items'  => $items->count(),
                'priced_items' => $pricedItems->count(),
                'total_weight' => $items->sum('weight_kg'),
                'total_amount' => $shipmentTotal,
                'paid_amount'  => $shipmentPaid,
                'due_amount'   => max($shipmentTotal - $shipmentPaid, 0),
                'items'        => $items,
            ];

            if ($shipmentTotal > 0) {
                $ledgerEntries->push([
                    'date'        => $shipment->shipment_date ?? $shipment->created_at,
                    'type'        => 'debit',
                    'description' => 'Billed — Shipment: ' . $shipment->shipment_name,
                    'reference'   => $shipment->shipment_name,
                    'amount'      => $shipmentTotal,
                    'shipment_id' => $shipment->id,
                ]);
            }
        }

        // Separate, detailed payment history (not merged into the statement)
        $payments = CustomerPayment::where('customer_id', $customer->id)
            ->with(['itemshipment', 'paymentItems.cargoItem'])
            ->orderByDesc('payment_date')
            ->get();

        foreach ($payments as $payment) {
            $ledgerEntries->push([
                'date'        => $payment->payment_date,
                'type'        => 'credit',
                'description' => 'Payment Received'
                    . ($payment->itemshipment ? ' — ' . $payment->itemshipment->shipment_name : '')
                    . ($payment->reference_no ? ' (Ref: ' . $payment->reference_no . ')' : ''),
                'reference'      => $payment->reference_no,
                'amount'         => $payment->amount,
                'payment_method' => $payment->payment_method,
                'payment_id'     => $payment->id,
            ]);
        }

        $ledgerEntries = $ledgerEntries->sortBy(fn ($e) => Carbon::parse($e['date']))->values();

        $balance = 0;
        $ledgerEntries = $ledgerEntries->map(function ($entry) use (&$balance) {
            $balance += $entry['type'] === 'debit' ? $entry['amount'] : -$entry['amount'];
            $entry['balance'] = $balance;
            return $entry;
        });

        $totalBilled = collect($shipmentSummaries)->sum('total_amount');
        $totalPaid   = collect($shipmentSummaries)->sum('paid_amount');
        $totalDue    = max($totalBilled - $totalPaid, 0);

        return view('admin.ledger.show', compact(
            'customer',
            'shipments',
            'shipmentSummaries',
            'ledgerEntries',
            'payments',
            'totalBilled',
            'totalPaid',
            'totalDue'
        ));
    }
}