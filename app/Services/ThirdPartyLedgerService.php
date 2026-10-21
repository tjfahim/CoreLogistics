<?php

namespace App\Services;

use App\Models\ItemsShipment;
use App\Models\ThirdParty;
use App\Models\ThirdPartyPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Third-party companies ship through us. We bill them per shipment (priced items) and they pay us
 * lump sums. Money flow mirrors the customer side:
 *      billed = sum of the company's priced items inside a shipment (debit)
 *      paid   = payments received from the company (credit)
 *      due    = billed - paid   (negative = advance)
 */
class ThirdPartyLedgerService
{
    /** One row per (third party, shipment). */
    public function shipmentRows(array $f = []): Collection
    {
        $tpIds       = array_filter($f['third_party_ids'] ?? []);
        $shipmentIds = array_filter($f['shipment_ids'] ?? []);

        $billed = DB::table('cargo_items as ci')
            ->join('cargo_item_shipment as cis', 'cis.cargo_item_id', '=', 'ci.id')
            ->whereNotNull('ci.third_party_id')
            ->when($tpIds, fn ($q) => $q->whereIn('ci.third_party_id', $tpIds))
            ->when($shipmentIds, fn ($q) => $q->whereIn('cis.items_shipment_id', $shipmentIds))
            ->groupBy('ci.third_party_id', 'cis.items_shipment_id')
            ->selectRaw('ci.third_party_id, cis.items_shipment_id as shipment_id,
                COUNT(ci.id) as items,
                SUM(CASE WHEN ci.price IS NOT NULL OR ci.price_per_kg IS NOT NULL THEN 1 ELSE 0 END) as priced_items,
                SUM(COALESCE(ci.weight_kg, 0)) as weight,
                SUM(' . AccountingService::BILL_SQL . ') as billed')
            ->get();

        if ($billed->isEmpty()) {
            return collect();
        }

        $shipments = ItemsShipment::whereIn('id', $billed->pluck('shipment_id')->unique())
            ->get(['id', 'shipment_name', 'shipment_date', 'status', 'created_at'])->keyBy('id');

        return $billed->map(function ($r) use ($shipments) {
            $s = $shipments->get($r->shipment_id);
            return (object) [
                'third_party_id' => (int) $r->third_party_id,
                'shipment_id'    => (int) $r->shipment_id,
                'shipment_name'  => $s->shipment_name ?? ('#' . $r->shipment_id),
                'date'           => AccountingService::dateOf($s),
                'items'          => (int) $r->items,
                'priced_items'   => (int) $r->priced_items,
                'weight'         => (float) $r->weight,
                'billed'         => round((float) $r->billed, 2),
            ];
        })->sortBy([['date', 'asc'], ['shipment_id', 'asc']])->values();
    }

    /** Lifetime billed / paid / due for every third party (or the given ids). */
    public function balances(array $ids = []): Collection
    {
        $rows = $this->shipmentRows(['third_party_ids' => $ids]);

        $paid = DB::table('third_party_payments')
            ->when($ids, fn ($q) => $q->whereIn('third_party_id', $ids))
            ->selectRaw('third_party_id, SUM(amount) as total, MAX(payment_date) as last_date')
            ->groupBy('third_party_id')->get()->keyBy('third_party_id');

        $out = collect();
        foreach (array_unique(array_merge($rows->pluck('third_party_id')->all(), $paid->keys()->all())) as $id) {
            $g  = $rows->where('third_party_id', $id);
            $p  = $paid->get($id);
            $b  = round($g->sum('billed'), 2);
            $pd = round((float) ($p->total ?? 0), 2);
            $out->put($id, (object) [
                'third_party_id' => (int) $id,
                'shipments'      => $g->count(),
                'items'          => $g->sum('items'),
                'billed'         => $b,
                'paid'           => $pd,
                'due'            => round($b - $pd, 2),
                'last_payment'   => $p->last_date ?? null,
            ]);
        }
        return $out;
    }

    /** Statement of account with opening balance and running balance. */
    public function statement(ThirdParty $tp, ?string $from = null, ?string $to = null): array
    {
        $rows     = $this->shipmentRows(['third_party_ids' => [$tp->id]]);
        $payments = ThirdPartyPayment::with('user:id,name')->where('third_party_id', $tp->id)->get();

        $all = collect();
        foreach ($rows as $r) {
            if ($r->billed > 0) {
                $all->push([
                    'date' => $r->date, 'order' => 0, 'sid' => $r->shipment_id, 'kind' => 'bill',
                    'doc' => 'TPB-' . $tp->id . '-' . $r->shipment_id, 'desc' => 'Shipment bill: ' . $r->shipment_name,
                    'detail' => $r->priced_items . ' priced item(s), ' . number_format($r->weight, 2) . ' kg'
                                . ($r->items > $r->priced_items ? ' (' . ($r->items - $r->priced_items) . ' unpriced)' : ''),
                    'method' => null, 'reference' => null, 'user' => null,
                    'debit' => $r->billed, 'credit' => 0.0, 'payment_id' => null,
                ]);
            }
        }
        foreach ($payments as $p) {
            $all->push([
                'date' => $p->payment_date->toDateString(), 'order' => 1, 'sid' => $p->id, 'kind' => 'receipt',
                'doc' => 'TPR-' . str_pad((string) $p->id, 6, '0', STR_PAD_LEFT), 'desc' => 'Payment received',
                'detail' => ($p->currency !== 'bdt'
                    ? number_format((float) $p->foreign_amount, 2) . ' ' . strtoupper($p->currency) . ' @ ' . rtrim(rtrim(number_format((float) $p->exchange_rate, 4), '0'), '.')
                    : null) ?: $p->notes,
                'method' => $p->payment_method, 'reference' => $p->reference_no, 'user' => optional($p->user)->name,
                'debit' => 0.0, 'credit' => round((float) $p->amount, 2), 'payment_id' => $p->id,
            ]);
        }

        $all = $all->sortBy([['date', 'asc'], ['order', 'asc'], ['sid', 'asc']])->values();

        $opening = 0.0;
        $entries = collect();
        foreach ($all as $e) {
            if ($from && $e['date'] < $from) { $opening += $e['debit'] - $e['credit']; continue; }
            if ($to && $e['date'] > $to)     { continue; }
            $entries->push($e);
        }

        $opening = round($opening, 2);
        $balance = $opening;
        $entries = $entries->map(function ($e) use (&$balance) {
            $balance = round($balance + $e['debit'] - $e['credit'], 2);
            $e['balance'] = $balance;
            return $e;
        });

        $debit  = round($entries->sum('debit'), 2);
        $credit = round($entries->sum('credit'), 2);

        return [
            'shipments' => $rows,
            'payments'  => $payments->sortByDesc('payment_date')->values(),
            'entries'   => $entries,
            'opening'   => $opening,
            'debit'     => $debit,
            'credit'    => $credit,
            'closing'   => round($opening + $debit - $credit, 2),
            'totals'    => [
                'billed' => round($rows->sum('billed'), 2),
                'paid'   => round((float) $payments->sum('amount'), 2),
                'due'    => round($rows->sum('billed') - (float) $payments->sum('amount'), 2),
                'shipments' => $rows->count(),
            ],
        ];
    }
}
