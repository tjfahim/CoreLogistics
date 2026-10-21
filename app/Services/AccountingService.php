<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Models\ItemsShipment;
use App\Models\ItemsShipmentCost;
use App\Models\ItemsShipmentPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * One place for every accounting number (ledgers, reports, dashboard),
 * so all screens always agree with each other.
 *
 *  Customer side (money we should receive)
 *      billed   = sum of priced cargo items of the customer inside a shipment
 *      paid     = sum of payment allocations given to that shipment
 *      due      = billed - paid (never below 0, per shipment)
 *
 *  Supplier side (money we owe: freight, CNF, tax ...)
 *      bill     = items_shipment_costs.final_bdt_cost
 *      paid     = items_shipment_payments.amount
 *      due      = bill - paid
 */
class AccountingService
{
    /** Same rule as CargoItem::getTotalPriceAttribute(), written in SQL. */
    public const BILL_SQL = "CASE
        WHEN COALESCE(ci.price, 0) <> 0 THEN ci.price
        WHEN COALESCE(ci.price_per_kg, 0) <> 0 AND COALESCE(ci.weight_kg, 0) <> 0 THEN ci.price_per_kg * ci.weight_kg
        ELSE 0 END";

    /** "RMB 100.00 @ 17.5" for payments made / received in a foreign currency, else null. */
    public static function curNote($p): ?string
    {
        if (($p->currency ?? 'bdt') === 'bdt' || !$p->foreign_amount) {
            return null;
        }
        return strtoupper($p->currency) . ' ' . number_format((float) $p->foreign_amount, 2)
            . ' @ ' . rtrim(rtrim(number_format((float) $p->exchange_rate, 4), '0'), '.');
    }

    public static function receiptNo(int $id): string
    {
        return 'RCV-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public static function dateOf(?ItemsShipment $s): string
    {
        if (!$s) {
            return now()->toDateString();
        }
        return ($s->shipment_date ?? $s->created_at)->toDateString();
    }

    // =====================================================================
    //  CUSTOMER SIDE
    // =====================================================================

    /**
     * One row per (customer, shipment): items, weight, billed, paid, due.
     *
     * @param array{customer_ids?:array,shipment_ids?:array} $f
     */
    public function customerShipmentRows(array $f = []): Collection
    {
        $customerIds = array_filter($f['customer_ids'] ?? []);
        $shipmentIds = array_filter($f['shipment_ids'] ?? []);

        $billed = DB::table('cargo_items as ci')
            ->join('cargo_item_shipment as cis', 'cis.cargo_item_id', '=', 'ci.id')
            ->whereNotNull('ci.customer_id')
            ->when($customerIds, fn ($q) => $q->whereIn('ci.customer_id', $customerIds))
            ->when($shipmentIds, fn ($q) => $q->whereIn('cis.items_shipment_id', $shipmentIds))
            ->groupBy('ci.customer_id', 'cis.items_shipment_id')
            ->selectRaw('ci.customer_id, cis.items_shipment_id as shipment_id,
                COUNT(ci.id) as items,
                SUM(CASE WHEN ci.price IS NOT NULL OR ci.price_per_kg IS NOT NULL THEN 1 ELSE 0 END) as priced_items,
                SUM(COALESCE(ci.weight_kg, 0)) as weight,
                SUM(COALESCE(ci.cbm, 0)) as cbm,
                SUM(' . self::BILL_SQL . ') as billed')
            ->get();

        if ($billed->isEmpty()) {
            return collect();
        }

        $paid = DB::table('customer_payment_allocations as a')
            ->join('customer_payments as p', 'p.id', '=', 'a.customer_payment_id')
            ->when($customerIds, fn ($q) => $q->whereIn('p.customer_id', $customerIds))
            ->when($shipmentIds, fn ($q) => $q->whereIn('a.itemshipment_id', $shipmentIds))
            ->groupBy('p.customer_id', 'a.itemshipment_id')
            ->selectRaw('p.customer_id, a.itemshipment_id as shipment_id, SUM(a.amount) as paid')
            ->get()
            ->keyBy(fn ($r) => $r->customer_id . '-' . $r->shipment_id);

        $shipments = ItemsShipment::whereIn('id', $billed->pluck('shipment_id')->unique())
            ->get(['id', 'shipment_name', 'shipment_date', 'status', 'created_at'])
            ->keyBy('id');

        return $billed->map(function ($r) use ($paid, $shipments) {
            $s   = $shipments->get($r->shipment_id);
            $b   = round((float) $r->billed, 2);
            $p   = round((float) ($paid->get($r->customer_id . '-' . $r->shipment_id)->paid ?? 0), 2);
            $due = max(round($b - $p, 2), 0);

            return (object) [
                'customer_id'   => (int) $r->customer_id,
                'shipment_id'   => (int) $r->shipment_id,
                'shipment'      => $s,
                'shipment_name' => $s->shipment_name ?? ('#' . $r->shipment_id),
                'date'          => self::dateOf($s),
                'items'         => (int) $r->items,
                'priced_items'  => (int) $r->priced_items,
                'weight'        => (float) $r->weight,
                'cbm'           => (float) $r->cbm,
                'billed'        => $b,
                'paid'          => $p,
                'due'           => $due,
                'status'        => $b <= 0 ? 'no-billing' : ($due <= 0 ? 'paid' : ($p > 0 ? 'partial' : 'due')),
            ];
        })->sortBy([['date', 'asc'], ['shipment_id', 'asc']])->values();
    }

    /**
     * Customer list with lifetime billed / paid / due (fast, for the ledger index and dashboard).
     */
    public function customerBalances(array $customerIds = []): Collection
    {
        $rows = $this->customerShipmentRows(['customer_ids' => $customerIds]);

        $paidAll = DB::table('customer_payments')
            ->when($customerIds, fn ($q) => $q->whereIn('customer_id', $customerIds))
            ->selectRaw('customer_id, SUM(amount) as total, MAX(payment_date) as last_date, COUNT(*) as n')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        return $rows->groupBy('customer_id')->map(function ($g, $cid) use ($paidAll) {
            $p = $paidAll->get($cid);
            return (object) [
                'customer_id'    => (int) $cid,
                'shipments'      => $g->count(),
                'items'          => $g->sum('items'),
                'billed'         => round($g->sum('billed'), 2),
                'paid'           => round((float) ($p->total ?? 0), 2),
                'due'            => round($g->sum('due'), 2),
                'advance'        => max(round((float) ($p->total ?? 0) - $g->sum('paid'), 2), 0),
                'shipments_due'  => $g->where('due', '>', 0)->count(),
                'last_payment'   => $p->last_date ?? null,
                'oldest_due_date'=> optional($g->where('due', '>', 0)->sortBy('date')->first())->date,
            ];
        });
    }

    /**
     * Statement of account for one customer, entry by entry.
     * Opening balance = everything before $from. Balance = amount the customer still owes us.
     */
    public function customerStatement(Customer $customer, ?string $from = null, ?string $to = null): array
    {
        $rows = $this->customerShipmentRows(['customer_ids' => [$customer->id]]);

        $invoices = Invoice::where('customer_id', $customer->id)
            ->pluck('invoice_number', 'itemshipment_id');

        $payments = CustomerPayment::with(['user:id,name', 'allocations.itemshipment:id,shipment_name'])
            ->where('customer_id', $customer->id)
            ->get();

        $all = collect();

        foreach ($rows as $r) {
            if ($r->billed > 0) {
                $all->push([
                    'date'      => $r->date,
                    'order'     => 0,
                    'ts'        => '',
                    'time'      => null,
                    'sid'       => $r->shipment_id,
                    'kind'      => 'bill',
                    'doc'       => $invoices[$r->shipment_id] ?? ('BILL-' . $r->shipment_id),
                    'desc'      => 'Shipment bill: ' . $r->shipment_name,
                    'detail'    => $r->priced_items . ' priced item(s), ' . number_format($r->weight, 2) . ' kg'
                                   . ($r->items > $r->priced_items ? ' (' . ($r->items - $r->priced_items) . ' unpriced)' : ''),
                    'method'    => null,
                    'reference' => null,
                    'user'      => null,
                    'debit'     => $r->billed,
                    'credit'    => 0.0,
                    'payment_id'=> null,
                ]);
            }
        }

        foreach ($payments as $p) {
            $all->push([
                'date'      => $p->payment_date->toDateString(),
                'order'     => 1,
                'ts'        => optional($p->created_at)->format('Y-m-d H:i:s') ?? '',
                'time'      => optional($p->created_at)->format('H:i'),
                'sid'       => $p->id,
                'kind'      => 'receipt',
                'doc'       => self::receiptNo($p->id),
                'desc'      => 'Payment received',
                'detail'    => $p->allocations
                    ->map(fn ($a) => ($a->itemshipment->shipment_name ?? ('#' . $a->itemshipment_id)) . ': ' . number_format($a->amount, 2))
                    ->implode('  |  ') ?: ($p->notes ?: null),
                'cur'       => self::curNote($p),
                'method'    => $p->payment_method,
                'reference' => $p->reference_no,
                'user'      => optional($p->user)->name,
                'debit'     => 0.0,
                'credit'    => round((float) $p->amount, 2),
                'payment_id'=> $p->id,
            ]);
        }

        $all = $all->sortBy([['date', 'asc'], ['ts', 'asc'], ['order', 'asc'], ['sid', 'asc']])->values();

        $opening = 0.0;
        $entries = collect();
        $balance = 0.0;

        foreach ($all as $e) {
            $before = $from && $e['date'] < $from;
            $after  = $to && $e['date'] > $to;

            if ($before) {
                $opening += $e['debit'] - $e['credit'];
                continue;
            }
            if ($after) {
                continue;
            }
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
            'payments'  => $payments->sortByDesc(fn ($p) => $p->payment_date->toDateString() . str_pad((string) $p->id, 10, '0', STR_PAD_LEFT))->values(),
            'entries'   => $entries,
            'opening'   => $opening,
            'debit'     => $debit,
            'credit'    => $credit,
            'closing'   => round($opening + $debit - $credit, 2),
            'advance'   => max(round((float) $payments->sum('amount') - (float) $rows->sum('paid'), 2), 0),
            'totals'    => [
                'billed'        => round($rows->sum('billed'), 2),
                'paid'          => round($rows->sum('paid'), 2),
                'due'           => round($rows->sum('due'), 2),
                'shipments'     => $rows->count(),
                'shipments_due' => $rows->where('due', '>', 0)->count(),
            ],
        ];
    }

    // =====================================================================
    //  SUPPLIER SIDE (shipment bills)
    // =====================================================================

    /**
     * Payable ledger: every cost line (bill) and every payment, with running balance (what we still owe).
     *
     * @param array{shipment_ids?:array,agent_id?:int|null,cost_type_id?:int|null,from?:?string,to?:?string} $f
     */
    public function payableLedger(array $f = []): array
    {
        $shipmentIds = array_filter($f['shipment_ids'] ?? []);
        $agentId     = $f['agent_id'] ?? null;
        $typeId      = $f['cost_type_id'] ?? null;
        $from        = $f['from'] ?? null;
        $to          = $f['to'] ?? null;

        $scope = function ($q) use ($shipmentIds, $agentId, $typeId) {
            return $q->when($shipmentIds, fn ($x) => $x->whereIn('items_shipment_id', $shipmentIds))
                     ->when($agentId, fn ($x) => $x->where('agent_id', $agentId))
                     ->when($typeId, fn ($x) => $x->where('cost_type_id', $typeId));
        };

        $costs = $scope(ItemsShipmentCost::query())
            ->with(['costType:id,name', 'agent:id,name', 'itemsShipment:id,shipment_name,shipment_date'])
            ->get();

        $payments = ItemsShipmentPayment::with([
                'user:id,name', 'voucher:id,voucher_no',
                'itemsShipmentCost.costType:id,name', 'itemsShipmentCost.agent:id,name',
                'itemsShipmentCost.itemsShipment:id,shipment_name',
            ])
            ->whereHas('itemsShipmentCost', fn ($q) => $scope($q))
            ->get();

        $all = collect();

        foreach ($costs as $c) {
            $bill = round((float) ($c->final_bdt_cost ?: $c->amount), 2);
            $all->push([
                'date'      => optional($c->cost_date)->toDateString() ?? $c->created_at->toDateString(),
                'order'     => 0,
                'ts'        => optional($c->created_at)->format('Y-m-d H:i:s') ?? '',
                'time'      => optional($c->created_at)->format('H:i'),
                'sid'       => $c->id,
                'kind'      => 'bill',
                'doc'       => 'BILL-' . str_pad((string) $c->id, 6, '0', STR_PAD_LEFT),
                'shipment'  => optional($c->itemsShipment)->shipment_name,
                'shipment_id' => $c->items_shipment_id,
                'party'     => optional($c->agent)->name,
                'desc'      => optional($c->costType)->name ?? 'Cost',
                'detail'    => ($c->currency !== 'bdt'
                                ? number_format($c->amount, 2) . ' ' . strtoupper($c->currency) . ' @ ' . rtrim(rtrim(number_format($c->exchange_rate_bdt, 4), '0'), '.')
                                : null) ?: $c->description,
                'method'    => null,
                'reference' => null,
                'user'      => null,
                'bill'      => $bill,
                'paid'      => 0.0,
                'voucher_id'=> null,
            ]);
        }

        foreach ($payments as $p) {
            $c = $p->itemsShipmentCost;
            $all->push([
                'date'      => $p->payment_date->toDateString(),
                'order'     => 1,
                'ts'        => optional($p->created_at)->format('Y-m-d H:i:s') ?? '',
                'time'      => optional($p->created_at)->format('H:i'),
                'sid'       => $p->id,
                'kind'      => 'payment',
                'doc'       => $p->voucher->voucher_no ?? ('PAY-' . str_pad((string) $p->id, 6, '0', STR_PAD_LEFT)),
                'shipment'  => optional(optional($c)->itemsShipment)->shipment_name,
                'shipment_id' => optional($c)->items_shipment_id,
                'party'     => optional(optional($c)->agent)->name,
                'desc'      => 'Payment: ' . (optional(optional($c)->costType)->name ?? 'Cost'),
                'detail'    => trim(($p->notes ?: '') . ' ' . (self::curNote($p) ? '[' . self::curNote($p) . ']' : '')) ?: null,
                'method'    => $p->payment_method,
                'reference' => $p->reference_no,
                'user'      => optional($p->user)->name ?? $p->paid_by,
                'bill'      => 0.0,
                'paid'      => round((float) $p->amount, 2),
                'voucher_id'=> $p->shipment_bill_payment_id,
            ]);
        }

        $all = $all->sortBy([['ts', 'asc'], ['date', 'asc'], ['order', 'asc'], ['sid', 'asc']])->values();

        $opening = 0.0;
        $entries = collect();

        foreach ($all as $e) {
            if ($from && $e['date'] < $from) {
                $opening += $e['bill'] - $e['paid'];
                continue;
            }
            if ($to && $e['date'] > $to) {
                continue;
            }
            $entries->push($e);
        }

        $opening = round($opening, 2);
        $balance = $opening;
        $entries = $entries->map(function ($e) use (&$balance) {
            $balance = round($balance + $e['bill'] - $e['paid'], 2);
            $e['balance'] = $balance;
            return $e;
        });

        $bills = round($entries->sum('bill'), 2);
        $paid  = round($entries->sum('paid'), 2);

        // position per shipment and per agent (whole filtered set, not date limited)
        $paidByCost = $payments->groupBy('items_shipment_cost_id')->map(fn ($g) => (float) $g->sum('amount'));
        $line = fn ($c) => [
            'bill' => round((float) ($c->final_bdt_cost ?: $c->amount), 2),
            'paid' => round((float) ($paidByCost[$c->id] ?? 0), 2),
        ];

        $byShipment = $costs->groupBy('items_shipment_id')->map(function ($g) use ($line) {
            $bill = $g->sum(fn ($c) => $line($c)['bill']);
            $paid = $g->sum(fn ($c) => $line($c)['paid']);
            return [
                'name' => optional($g->first()->itemsShipment)->shipment_name ?? ('#' . $g->first()->items_shipment_id),
                'date' => optional(optional($g->first()->itemsShipment)->shipment_date)->toDateString(),
                'lines' => $g->count(), 'bill' => round($bill, 2), 'paid' => round($paid, 2), 'due' => round($bill - $paid, 2),
            ];
        })->sortBy('date')->values();

        $byAgent = $costs->groupBy(fn ($c) => $c->agent_id ?: 0)->map(function ($g) use ($line) {
            $bill = $g->sum(fn ($c) => $line($c)['bill']);
            $paid = $g->sum(fn ($c) => $line($c)['paid']);
            return [
                'name' => optional($g->first()->agent)->name ?? 'No agent',
                'lines' => $g->count(), 'bill' => round($bill, 2), 'paid' => round($paid, 2), 'due' => round($bill - $paid, 2),
            ];
        })->sortByDesc('due')->values();

        return [
            'entries'     => $entries,
            'opening'     => $opening,
            'bills'       => $bills,
            'paid'        => $paid,
            'closing'     => round($opening + $bills - $paid, 2),
            'by_shipment' => $byShipment,
            'by_agent'    => $byAgent,
            'total_bill'  => round($byShipment->sum('bill'), 2),
            'total_paid'  => round($byShipment->sum('paid'), 2),
            'total_due'   => round($byShipment->sum('due'), 2),
        ];
    }

    // =====================================================================
    //  REPORT BUILDING BLOCKS
    // =====================================================================

    /** Cargo item counts per shipment (all items, assigned or not). */
    private function itemStats(array $shipmentIds): Collection
    {
        if (!$shipmentIds) {
            return collect();
        }

        return DB::table('cargo_items as ci')
            ->join('cargo_item_shipment as cis', 'cis.cargo_item_id', '=', 'ci.id')
            ->whereIn('cis.items_shipment_id', $shipmentIds)
            ->groupBy('cis.items_shipment_id')
            ->selectRaw('cis.items_shipment_id as shipment_id, COUNT(ci.id) as items,
                SUM(COALESCE(ci.weight_kg, 0)) as weight, SUM(COALESCE(ci.cbm, 0)) as cbm,
                SUM(CASE WHEN ci.customer_id IS NULL THEN 1 ELSE 0 END) as unassigned,
                SUM(CASE WHEN ci.price IS NULL AND ci.price_per_kg IS NULL THEN 1 ELSE 0 END) as unpriced')
            ->get()
            ->keyBy('shipment_id');
    }

    /**
     * One row per shipment with customer side + supplier side + profit.
     */
    public function shipmentFinancials(array $shipmentIds = [], ?string $from = null, ?string $to = null): Collection
    {
        $shipments = ItemsShipment::query()
            ->when($shipmentIds, fn ($q) => $q->whereIn('id', $shipmentIds))
            ->when($from, fn ($q) => $q->whereRaw('COALESCE(shipment_date, DATE(created_at)) >= ?', [$from]))
            ->when($to, fn ($q) => $q->whereRaw('COALESCE(shipment_date, DATE(created_at)) <= ?', [$to]))
            ->orderByRaw('COALESCE(shipment_date, DATE(created_at)) asc')
            ->orderBy('id')
            ->get();

        $ids   = $shipments->pluck('id')->all();
        $items = $this->itemStats($ids);

        $cust = $this->customerShipmentRows(['shipment_ids' => $ids])->groupBy('shipment_id');

        $costs = ItemsShipmentCost::whereIn('items_shipment_id', $ids)
    ->selectRaw('items_shipment_id,
                 COUNT(*) as `lines`,
                 SUM(final_bdt_cost) as `cost`,
                 SUM(paid_amount) as `paid`,
                 SUM(due_amount) as `due`')
    ->groupBy('items_shipment_id')
    ->get()
    ->keyBy('items_shipment_id');

        return $shipments->map(function ($s) use ($items, $cust, $costs) {
            $i = $items->get($s->id);
            $c = $cust->get($s->id, collect());
            $k = $costs->get($s->id);

            $billed = round($c->sum('billed'), 2);
            $recv   = round($c->sum('paid'), 2);
            $cost   = round((float) ($k->cost ?? 0), 2);

            return (object) [
                'shipment'     => $s,
                'id'           => $s->id,
                'name'         => $s->shipment_name,
                'date'         => self::dateOf($s),
                'status'       => $s->status,
                'items'        => (int) ($i->items ?? 0),
                'weight'       => (float) ($i->weight ?? 0),
                'cbm'          => (float) ($i->cbm ?? 0),
                'unassigned'   => (int) ($i->unassigned ?? 0),
                'unpriced'     => (int) ($i->unpriced ?? 0),
                'customers'    => $c->pluck('customer_id')->unique()->count(),
                'billed'       => $billed,
                'received'     => $recv,
                'receivable'   => round($c->sum('due'), 2),
                'cost_lines'   => (int) ($k->lines ?? 0),
                'cost'         => $cost,
                'cost_paid'    => round((float) ($k->paid ?? 0), 2),
                'cost_due'     => round((float) ($k->due ?? 0), 2),
                'profit'       => round($billed - $cost, 2),
                'margin'       => $billed > 0 ? round(($billed - $cost) / $billed * 100, 1) : null,
            ];
        });
    }

    /** Totals row for shipmentFinancials(). */
    public function sumFinancials(Collection $rows): array
    {
        $t = [];
        foreach (['items', 'weight', 'cbm', 'billed', 'received', 'receivable', 'cost', 'cost_paid', 'cost_due', 'profit', 'unassigned', 'unpriced'] as $k) {
            $t[$k] = round((float) $rows->sum($k), 2);
        }
        $t['margin'] = $t['billed'] > 0 ? round($t['profit'] / $t['billed'] * 100, 1) : null;
        $t['count']  = $rows->count();
        return $t;
    }

    /** Cost lines (with their payments) of the given shipments. */
    public function costLines(array $shipmentIds): Collection
    {
        return ItemsShipmentCost::with(['costType:id,name', 'agent:id,name', 'payments.user:id,name', 'payments.voucher:id,voucher_no'])
            ->whereIn('items_shipment_id', $shipmentIds)
            ->orderBy('items_shipment_id')->orderBy('cost_date')->orderBy('id')
            ->get();
    }

    /** Customers inside the given shipments with billed / paid / due. */
    public function shipmentCustomers(array $shipmentIds): Collection
    {
        $rows  = $this->customerShipmentRows(['shipment_ids' => $shipmentIds]);
        $names = Customer::whereIn('id', $rows->pluck('customer_id')->unique())->get(['id', 'name', 'customer_code'])->keyBy('id');

        return $rows->map(function ($r) use ($names) {
            $r->customer = $names->get($r->customer_id);
            return $r;
        });
    }

    /**
     * Period report: money in / out and result per month (or day).
     * Billed = by shipment date, Cost = by cost date, Received / Paid out = by payment date.
     */
    public function periodReport(string $from, string $to, string $group = 'month'): array
    {
        $group = $group === 'day' ? 'day' : 'month';
        $key   = fn (string $d) => $group === 'day' ? substr($d, 0, 10) : substr($d, 0, 7);

        // empty buckets so months without activity still show as 0
        $buckets = [];
        $cursor  = Carbon::parse($from)->startOfDay();
        $end     = Carbon::parse($to)->startOfDay();
        $guard   = 0;
        while ($cursor <= $end && $guard++ < 800) {
            $buckets[$cursor->format($group === 'day' ? 'Y-m-d' : 'Y-m')] = [
                'shipments' => 0, 'billed' => 0.0, 'received' => 0.0, 'cost' => 0.0, 'paid_out' => 0.0,
            ];
            $group === 'day' ? $cursor->addDay() : $cursor->addMonthNoOverflow()->startOfMonth();
        }

        $fin = $this->shipmentFinancials([], $from, $to);
        foreach ($fin as $f) {
            $k = $key($f->date);
            if (isset($buckets[$k])) {
                $buckets[$k]['shipments']++;
                $buckets[$k]['billed'] += $f->billed;
            }
        }

        $receipts = CustomerPayment::with(['customer:id,name', 'user:id,name', 'allocations.itemshipment:id,shipment_name'])
            ->whereDate('payment_date', '>=', $from)->whereDate('payment_date', '<=', $to)
            ->orderBy('payment_date')->orderBy('id')->get();
        foreach ($receipts as $p) {
            $k = $key($p->payment_date->toDateString());
            if (isset($buckets[$k])) {
                $buckets[$k]['received'] += (float) $p->amount;
            }
        }

        $costs = ItemsShipmentCost::whereDate('cost_date', '>=', $from)->whereDate('cost_date', '<=', $to)->get();
        foreach ($costs as $c) {
            $k = $key($c->cost_date->toDateString());
            if (isset($buckets[$k])) {
                $buckets[$k]['cost'] += (float) ($c->final_bdt_cost ?: $c->amount);
            }
        }

        $payouts = ItemsShipmentPayment::with(['user:id,name', 'voucher:id,voucher_no', 'itemsShipmentCost.costType:id,name',
                'itemsShipmentCost.agent:id,name', 'itemsShipmentCost.itemsShipment:id,shipment_name'])
            ->whereDate('payment_date', '>=', $from)->whereDate('payment_date', '<=', $to)
            ->orderBy('payment_date')->orderBy('id')->get();
        foreach ($payouts as $p) {
            $k = $key($p->payment_date->toDateString());
            if (isset($buckets[$k])) {
                $buckets[$k]['paid_out'] += (float) $p->amount;
            }
        }

        $rows = collect($buckets)->map(function ($b, $k) use ($group) {
            $b['label']  = $group === 'day' ? Carbon::parse($k)->format('d M Y') : Carbon::parse($k . '-01')->format('F Y');
            $b['key']    = $k;
            $b['profit'] = round($b['billed'] - $b['cost'], 2);
            $b['net']    = round($b['received'] - $b['paid_out'], 2);
            foreach (['billed', 'received', 'cost', 'paid_out'] as $f) {
                $b[$f] = round($b[$f], 2);
            }
            return $b;
        })->values();

        $totals = [
            'shipments' => $rows->sum('shipments'),
            'billed'    => round($rows->sum('billed'), 2),
            'received'  => round($rows->sum('received'), 2),
            'cost'      => round($rows->sum('cost'), 2),
            'paid_out'  => round($rows->sum('paid_out'), 2),
            'profit'    => round($rows->sum('profit'), 2),
            'net'       => round($rows->sum('net'), 2),
        ];

        return compact('rows', 'totals', 'receipts', 'payouts', 'fin');
    }

    /**
     * Customer-wise: opening, billed, received, closing for a period.
     */
    public function customerReport(?string $from = null, ?string $to = null, array $customerIds = [], ?string $status = null): array
    {
        $rows = $this->customerShipmentRows(['customer_ids' => $customerIds]);

        $pay = DB::table('customer_payments')
            ->when($customerIds, fn ($q) => $q->whereIn('customer_id', $customerIds))
            ->get(['customer_id', 'amount', 'payment_date']);

        $names = Customer::when($customerIds, fn ($q) => $q->whereIn('id', $customerIds))
            ->get(['id', 'name', 'customer_code', 'phone'])->keyBy('id');

        $ids = $rows->pluck('customer_id')->merge($pay->pluck('customer_id'))->unique();

        $out = $ids->map(function ($cid) use ($rows, $pay, $names, $from, $to) {
            $r = $rows->where('customer_id', $cid);
            $p = $pay->where('customer_id', $cid);

            $openBill = $from ? $r->where('date', '<', $from)->sum('billed') : 0;
            $openPaid = $from ? $p->filter(fn ($x) => substr($x->payment_date, 0, 10) < $from)->sum('amount') : 0;

            $inBill = $r->filter(fn ($x) => (!$from || $x->date >= $from) && (!$to || $x->date <= $to));
            $inPay  = $p->filter(fn ($x) => (!$from || substr($x->payment_date, 0, 10) >= $from) && (!$to || substr($x->payment_date, 0, 10) <= $to));

            $opening = round($openBill - $openPaid, 2);
            $billed  = round($inBill->sum('billed'), 2);
            $recv    = round($inPay->sum('amount'), 2);

            $c = $names->get($cid);
            return (object) [
                'customer_id' => $cid,
                'name'        => $c->name ?? ('#' . $cid),
                'code'        => $c->customer_code ?? '',
                'phone'       => $c->phone ?? '',
                'shipments'   => $inBill->count(),
                'items'       => $inBill->sum('items'),
                'weight'      => round($inBill->sum('weight'), 2),
                'opening'     => $opening,
                'billed'      => $billed,
                'received'    => $recv,
                'closing'     => round($opening + $billed - $recv, 2),
                'last_payment'=> optional($p->sortByDesc('payment_date')->first())->payment_date,
            ];
        })->filter(fn ($c) => $c->opening != 0 || $c->billed != 0 || $c->received != 0);

        if ($status === 'due') {
            $out = $out->filter(fn ($c) => $c->closing > 0.004);
        } elseif ($status === 'clear') {
            $out = $out->filter(fn ($c) => $c->closing <= 0.004);
        }

        $out = $out->sortByDesc('closing')->values();

        return [
            'rows'   => $out,
            'totals' => [
                'opening'  => round($out->sum('opening'), 2),
                'billed'   => round($out->sum('billed'), 2),
                'received' => round($out->sum('received'), 2),
                'closing'  => round($out->sum('closing'), 2),
                'shipments'=> $out->sum('shipments'),
                'items'    => $out->sum('items'),
                'weight'   => round($out->sum('weight'), 2),
            ],
        ];
    }

    /**
     * Company summary for a period + position at the end of it.
     */
    public function summary(?string $from, ?string $to): array
    {
        $fin = $this->shipmentFinancials([], $from, $to);
        $tot = $this->sumFinancials($fin);

        $range = fn ($q, $col) => $q->when($from, fn ($x) => $x->whereDate($col, '>=', $from))
                                    ->when($to, fn ($x) => $x->whereDate($col, '<=', $to));

        $receipts = $range(CustomerPayment::query(), 'payment_date')->get(['amount', 'payment_method', 'payment_date', 'customer_id']);
        $payouts  = $range(ItemsShipmentPayment::query(), 'payment_date')->get(['amount', 'payment_method']);
        $costs    = $range(ItemsShipmentCost::with('costType:id,name', 'agent:id,name'), 'cost_date')->get();

        $costVal = fn ($c) => (float) ($c->final_bdt_cost ?: $c->amount);

        // position at the end of the period (everything up to $to)
        $allRows = $this->customerShipmentRows();
        $upTo    = $to ? $allRows->where('date', '<=', $to) : $allRows;
        $paidUpTo = DB::table('customer_payments')->when($to, fn ($q) => $q->whereDate('payment_date', '<=', $to))->sum('amount');
        $receivable = round($upTo->sum('billed') - (float) $paidUpTo, 2);

        $allCosts = ItemsShipmentCost::when($to, fn ($q) => $q->whereDate('cost_date', '<=', $to))->get();
        $paidCostUpTo = ItemsShipmentPayment::when($to, fn ($q) => $q->whereDate('payment_date', '<=', $to))->sum('amount');
        $payable = round($allCosts->sum($costVal) - (float) $paidCostUpTo, 2);

        $byType = $costs->groupBy(fn ($c) => optional($c->costType)->name ?? 'Other')
            ->map(fn ($g) => round($g->sum($costVal), 2))->sortDesc();

        $byAgent = $costs->groupBy(fn ($c) => optional($c->agent)->name ?? 'No agent')
            ->map(fn ($g) => round($g->sum($costVal), 2))->sortDesc();

        $recvMethod = $receipts->groupBy('payment_method')->map(fn ($g) => round($g->sum('amount'), 2))->sortDesc();
        $payMethod  = $payouts->groupBy('payment_method')->map(fn ($g) => round($g->sum('amount'), 2))->sortDesc();

        $topCust = $this->customerReport($from, $to);
        $names   = $topCust['rows'];

        return [
            'fin'        => $fin,
            'tot'        => $tot,
            'billed'     => $tot['billed'],
            'received'   => round((float) $receipts->sum('amount'), 2),
            'cost'       => round($costs->sum($costVal), 2),
            'paid_out'   => round((float) $payouts->sum('amount'), 2),
            'profit'     => round($tot['billed'] - $costs->sum($costVal), 2),
            'cash_net'   => round((float) $receipts->sum('amount') - (float) $payouts->sum('amount'), 2),
            'receivable' => $receivable,
            'payable'    => $payable,
            'by_type'    => $byType,
            'by_agent'   => $byAgent,
            'recv_method'=> $recvMethod,
            'pay_method' => $payMethod,
            'top_billed' => $names->sortByDesc('billed')->take(10)->values(),
            'top_due'    => $names->where('closing', '>', 0)->sortByDesc('closing')->take(10)->values(),
        ];
    }
}
