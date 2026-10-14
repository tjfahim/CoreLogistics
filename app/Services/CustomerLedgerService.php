<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPaymentAllocation;
use App\Models\ItemsShipment;
use Illuminate\Support\Collection;

/**
 * Single source of truth for "billed / paid / due" per customer per shipment.
 * Used by: ledger, billing page, receive-payment form, payment history.
 *
 * billed = sum of total_price of the customer's PRICED items inside the shipment
 * paid   = sum of payment allocations given to that shipment
 * due    = billed - paid (never below 0)
 */
class CustomerLedgerService
{
    public function shipmentBills(Customer $customer): Collection
    {
        $items = $customer->cargoItems()->with('itemshipments')->get();

        $itemsByShipment = [];
        foreach ($items as $item) {
            foreach ($item->itemshipments as $shipment) {
                $itemsByShipment[$shipment->id][] = $item;
            }
        }

        if (empty($itemsByShipment)) {
            return collect();
        }

        $shipments = ItemsShipment::whereIn('id', array_keys($itemsByShipment))
            ->orderBy('shipment_date')
            ->orderBy('id')
            ->get();

        $allocations = CustomerPaymentAllocation::with('payment')
            ->whereHas('payment', fn ($q) => $q->where('customer_id', $customer->id))
            ->get()
            ->groupBy('itemshipment_id');

        return $shipments->map(function ($shipment) use ($itemsByShipment, $allocations) {
            $shipmentItems = collect($itemsByShipment[$shipment->id])->values();
            $priced        = $shipmentItems->filter(fn ($i) => $i->hasPrice());

            $billed = round((float) $priced->sum(fn ($i) => $i->total_price ?? 0), 2);

            $shipAllocs = ($allocations->get($shipment->id) ?? collect())
                ->sort(fn ($a, $b) => [$b->payment->payment_date, $b->id] <=> [$a->payment->payment_date, $a->id])
                ->values();

            $paid = round((float) $shipAllocs->sum('amount'), 2);
            $due  = max(round($billed - $paid, 2), 0);

            if ($billed <= 0) {
                $status = 'no-billing';
            } elseif ($due <= 0) {
                $status = 'paid';
            } elseif ($paid > 0) {
                $status = 'partial';
            } else {
                $status = 'due';
            }

            return [
                'shipment'       => $shipment,
                'items'          => $shipmentItems,
                'total_items'    => $shipmentItems->count(),
                'priced_items'   => $priced->count(),
                'unpriced_items' => $shipmentItems->count() - $priced->count(),
                'total_weight'   => (float) $shipmentItems->sum('weight_kg'),
                'billed'         => $billed,
                'paid'           => $paid,
                'due'            => $due,
                'status'         => $status,
                'allocations'    => $shipAllocs,
            ];
        })->values();
    }

    public function totals(Collection $bills): array
    {
        return [
            'billed'        => round((float) $bills->sum('billed'), 2),
            'paid'          => round((float) $bills->sum('paid'), 2),
            'due'           => round((float) $bills->sum('due'), 2),
            'shipments'     => $bills->count(),
            'shipments_due' => $bills->filter(fn ($b) => $b['due'] > 0)->count(),
        ];
    }
}