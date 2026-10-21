<?php

namespace App\Http\Controllers;

use App\Exports\SheetsExport;
use App\Models\CargoItem;
use App\Models\CargoItemStatusLog;
use App\Models\CustomerPayment;
use App\Models\ItemsShipmentCost;
use App\Models\ItemsShipmentPayment;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * The one report page: operations (goods in / shipped / arrived / delivered) + money (cost, paid out,
 * collected, billed, profit, receivable, payable) for any date range, with every figure linking to its details.
 */
class FinalReportController extends Controller
{
    public const DETAIL_TYPES = [
        'received'    => 'Goods received (China warehouse)',
        'shipped'     => 'Goods shipped',
        'arrived'     => 'Goods arrived in BD warehouse',
        'delivered'   => 'Goods delivered to customers',
        'costs'       => 'Costing (bills)',
        'payments'    => 'Payments we made',
        'collections' => 'Collections (money received)',
    ];

    public function __construct(private AccountingService $acc)
    {
    }

    // ------------------------------------------------------------------ page

    public function index(Request $request)
    {
        abort_unless(Gate::allows('shipment-report'), 403);
        [$from, $to] = $this->range($request);
        $d = $this->build($from, $to);

        if ($request->boolean('export') || $request->boolean('pdf')) {
            return SheetsExport::respond($this->summarySheets($d, $from, $to), 'final-report-' . $from->toDateString() . '-to-' . $to->toDateString() . '.xlsx');
        }

        return view('admin.reports.final', $d + ['from' => $from, 'to' => $to, 'types' => self::DETAIL_TYPES]);
    }

    public function detail(Request $request, string $type)
    {
        abort_unless(Gate::allows('shipment-report'), 403);
        abort_unless(isset(self::DETAIL_TYPES[$type]), 404);
        [$from, $to] = $this->range($request);

        $sheet = $this->detailSheet($type, $from, $to);

        if ($request->boolean('export') || $request->boolean('pdf')) {
            return SheetsExport::respond([$sheet], 'final-' . $type . '-' . $from->toDateString() . '-to-' . $to->toDateString() . '.xlsx');
        }

        return view('admin.reports.final-detail', [
            'type' => $type, 'title' => self::DETAIL_TYPES[$type], 'sheet' => $sheet, 'from' => $from, 'to' => $to, 'types' => self::DETAIL_TYPES,
        ]);
    }

    // ----------------------------------------------------------------- range

    private function range(Request $request): array
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'preset' => 'nullable|string']);

        $today = now()->startOfDay();
        switch ($request->input('preset')) {
            case 'yesterday': $from = $today->copy()->subDay(); $to = $from->copy(); break;
            case 'week':      $from = $today->copy()->startOfWeek(); $to = $today->copy(); break;
            case 'month':     $from = $today->copy()->startOfMonth(); $to = $today->copy(); break;
            case 'year':      $from = $today->copy()->startOfYear(); $to = $today->copy(); break;
            default:
                $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : $today->copy();
                $to   = $request->filled('to')   ? Carbon::parse($request->to)->startOfDay()   : $from->copy();
        }
        if ($to < $from) {
            [$from, $to] = [$to, $from];
        }
        return [$from, $to];
    }

    // ------------------------------------------------------------- data sets

    /** Items whose FIRST arrival at one of $statuses happened inside the range, with that date. */
    private function reached(array $statuses, Carbon $from, Carbon $to): Collection
    {
        $logs = CargoItemStatusLog::whereIn('status', $statuses)
            ->selectRaw('cargo_item_id, MIN(changed_at) as first_at')
            ->groupBy('cargo_item_id')
            ->havingRaw('MIN(changed_at) BETWEEN ? AND ?', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->get()->keyBy('cargo_item_id');

        if ($logs->isEmpty()) {
            return collect();
        }

        return CargoItem::with(['customer:id,name,is_partner'])
            ->whereIn('id', $logs->keys())->get()
            ->each(fn ($i) => $i->event_date = Carbon::parse($logs[$i->id]->first_at)->toDateString())
            ->sortBy('event_date')->values();
    }

    private function receivedItems(Carbon $from, Carbon $to): Collection
    {
        return CargoItem::with(['customer:id,name,is_partner'])
            ->whereRaw('DATE(COALESCE(date, created_at)) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->each(fn ($i) => $i->event_date = ($i->date ?? $i->created_at)->toDateString())
            ->sortBy('event_date')->values();
    }

    private function costs(Carbon $from, Carbon $to): Collection
    {
        return ItemsShipmentCost::with(['costType:id,name', 'agent:id,name', 'itemsShipment:id,shipment_name'])
            ->whereBetween('cost_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('cost_date')->orderBy('id')->get();
    }

    private function payouts(Carbon $from, Carbon $to): Collection
    {
        return ItemsShipmentPayment::with(['itemsShipmentCost.costType:id,name', 'itemsShipmentCost.agent:id,name', 'itemsShipmentCost.itemsShipment:id,shipment_name', 'voucher:id,voucher_no'])
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('payment_date')->orderBy('id')->get();
    }

    private function collections(Carbon $from, Carbon $to): Collection
    {
        $c = CustomerPayment::with('customer:id,name')
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])->get()
            ->map(fn ($p) => ['date' => $p->payment_date->toDateString(), 'party' => optional($p->customer)->name ?? '—', 'kind' => 'Customer',
                              'method' => $p->payment_method, 'reference' => $p->reference_no, 'cur' => $this->curText($p), 'amount' => (float) $p->amount]);

        return $c->sortBy('date')->values();
    }

    private function curText($p): string
    {
        return ($p->currency ?? 'bdt') === 'bdt' ? '' :
            strtoupper($p->currency) . ' ' . number_format((float) $p->foreign_amount, 2) . ' @ ' . rtrim(rtrim(number_format((float) $p->exchange_rate, 4), '0'), '.');
    }

    private function boxes(Collection $items): int
    {
        return $items->map(fn ($i) => trim((string) $i->printed_ctn_no) !== '' ? 'c:' . $i->printed_ctn_no : 'i:' . $i->id)->unique()->count();
    }

    private function stat(Collection $items): array
    {
        return ['items' => $items->count(), 'boxes' => $this->boxes($items), 'kg' => round((float) $items->sum('weight_kg'), 2)];
    }

    // ----------------------------------------------------------------- build

    private function build(Carbon $from, Carbon $to): array
    {
        $f = $from->toDateString();
        $t = $to->toDateString();

        $received  = $this->receivedItems($from, $to);
        $shipped   = $this->reached(['shipping_ongoing', 'arrived_bd', 'waiting_for_customs', 'ready_to_deliver', 'delivered'], $from, $to);
        $arrived   = $this->reached(['arrived_bd', 'waiting_for_customs', 'ready_to_deliver', 'delivered'], $from, $to);
        $delivered = $this->reached(['delivered'], $from, $to);
        $costs     = $this->costs($from, $to);
        $payouts   = $this->payouts($from, $to);
        $coll      = $this->collections($from, $to);

        $costVal = fn ($c) => (float) ($c->final_bdt_cost ?: $c->amount);
        $ops = [
            'received'  => $this->stat($received),
            'shipped'   => $this->stat($shipped),
            'arrived'   => $this->stat($arrived),
            'delivered' => $this->stat($delivered),
        ];

        // money paid in foreign currency (kept for history)
        $foreign = $payouts->where('currency', '!=', 'bdt')->groupBy('currency')->map(fn ($g) => [
            'foreign' => round((float) $g->sum('foreign_amount'), 2), 'bdt' => round((float) $g->sum('amount'), 2),
        ]);

        $sum = $this->acc->summary($f, $t);

        $money = [
            'cost'      => round($costs->sum($costVal), 2),
            'cost_lines'=> $costs->count(),
            'paid_out'  => round((float) $payouts->sum('amount'), 2),
            'paid_lines'=> $payouts->count(),
            'collected' => round((float) $coll->sum('amount'), 2),
            'coll_lines'=> $coll->count(),
            'billed'    => $sum['billed'],
            'profit'    => round($sum['billed'] - $costs->sum($costVal), 2),
            'net_cash'  => round((float) $coll->sum('amount') - (float) $payouts->sum('amount'), 2),
            'receivable'=> $sum['receivable'],
            'payable'   => $sum['payable'],
            'foreign'   => $foreign,
        ];

        // ---- day wise (month wise if the range is long)
        $byMonth = $from->diffInDays($to) > 62;
        $key = fn (string $d) => $byMonth ? substr($d, 0, 7) : $d;
        $days = [];
        for ($c = $from->copy(); $c <= $to; $byMonth ? $c->addMonthNoOverflow()->startOfMonth() : $c->addDay()) {
            $days[$byMonth ? $c->format('Y-m') : $c->toDateString()] = [
                'label' => $byMonth ? $c->format('M Y') : $c->format('d M Y'),
                'received' => 0, 'shipped' => 0, 'arrived' => 0, 'delivered' => 0, 'cost' => 0.0, 'paid_out' => 0.0, 'collected' => 0.0,
            ];
        }
        foreach (['received' => $received, 'shipped' => $shipped, 'arrived' => $arrived, 'delivered' => $delivered] as $k => $col) {
            foreach ($col as $i) { if (isset($days[$key($i->event_date)])) $days[$key($i->event_date)][$k]++; }
        }
        foreach ($costs as $c) { $k = $key($c->cost_date->toDateString()); if (isset($days[$k])) $days[$k]['cost'] += $costVal($c); }
        foreach ($payouts as $p) { $k = $key($p->payment_date->toDateString()); if (isset($days[$k])) $days[$k]['paid_out'] += (float) $p->amount; }
        foreach ($coll as $p) { $k = $key($p['date']); if (isset($days[$k])) $days[$k]['collected'] += $p['amount']; }

        // ---- who owes whom (position now)
        $agentLed   = $this->acc->payableLedger([]);
        $partners   = \App\Models\Customer::where('is_partner', true)->pluck('name', 'id');
        $tpRows     = $partners->isEmpty() ? collect() : $this->acc->customerBalances($partners->keys()->all())
                        ->map(fn ($b) => ['id' => $b->customer_id, 'name' => $partners[$b->customer_id] ?? '#' . $b->customer_id,
                                          'billed' => $b->billed, 'paid' => $b->paid, 'due' => $b->due])
                        ->sortByDesc('due')->values();

        $agentIds = \App\Models\Agent::pluck('id', 'name');

        return [
            'ops' => $ops, 'money' => $money, 'days' => $days, 'byMonth' => $byMonth, 'sum' => $sum,
            'fin' => $sum['fin'], 'agents' => $agentLed['by_agent'], 'agentIds' => $agentIds, 'tpRows' => $tpRows,
            'topDue' => $sum['top_due'],
        ];
    }

    // ----------------------------------------------------------- export data

    private function summarySheets(array $d, Carbon $from, Carbon $to): array
    {
        $o = $d['ops']; $m = $d['money'];
        $period = $from->format('d M Y') . ($from->equalTo($to) ? '' : ' - ' . $to->format('d M Y'));

        $a = [
            'title' => 'Summary', 'heading' => 'Final report', 'subtitle' => 'Period: ' . $period,
            'headings' => ['Measure', 'Items / lines', 'Boxes', 'Weight (kg)', 'Amount (BDT)'],
            'rows' => [
                ['Goods received (China warehouse)', $o['received']['items'], $o['received']['boxes'], $o['received']['kg'], ''],
                ['Goods shipped', $o['shipped']['items'], $o['shipped']['boxes'], $o['shipped']['kg'], ''],
                ['Arrived in BD warehouse', $o['arrived']['items'], $o['arrived']['boxes'], $o['arrived']['kg'], ''],
                ['Delivered to customers', $o['delivered']['items'], $o['delivered']['boxes'], $o['delivered']['kg'], ''],
                ['Costing (bills)', $m['cost_lines'], '', '', $m['cost']],
                ['Payments we made', $m['paid_lines'], '', '', $m['paid_out']],
                ['Collections (received)', $m['coll_lines'], '', '', $m['collected']],
                ['Billed (shipments in period)', '', '', '', $m['billed']],
                ['Profit (billed - costing)', '', '', '', $m['profit']],
                ['Net cash (collected - paid)', '', '', '', $m['net_cash']],
                ['Receivable at end (customers + partners)', '', '', '', $m['receivable']],
                ['Payable at end (suppliers / agents)', '', '', '', $m['payable']],
            ],
            'money' => [3, 4], 'int' => [1, 2],
        ];
        $b = [
            'title' => $d['byMonth'] ? 'Month wise' : 'Day wise', 'heading' => ($d['byMonth'] ? 'Month' : 'Day') . ' wise activity',
            'headings' => [$d['byMonth'] ? 'Month' : 'Date', 'Received', 'Shipped', 'Arrived BD', 'Delivered', 'Costing', 'Paid out', 'Collected'],
            'rows' => collect($d['days'])->map(fn ($r) => [$r['label'], $r['received'], $r['shipped'], $r['arrived'], $r['delivered'], round($r['cost'], 2), round($r['paid_out'], 2), round($r['collected'], 2)])->values()->all(),
            'totals' => ['TOTAL', collect($d['days'])->sum('received'), collect($d['days'])->sum('shipped'), collect($d['days'])->sum('arrived'), collect($d['days'])->sum('delivered'),
                         round(collect($d['days'])->sum('cost'), 2), round(collect($d['days'])->sum('paid_out'), 2), round(collect($d['days'])->sum('collected'), 2)],
            'money' => [5, 6, 7], 'int' => [1, 2, 3, 4],
        ];
        $c = [
            'title' => 'Partners', 'heading' => 'Partners - billed / received / due',
            'headings' => ['Partner', 'Billed', 'Received', 'Due'],
            'rows' => $d['tpRows']->map(fn ($r) => [$r['name'], $r['billed'], $r['paid'], $r['due']])->all(),
            'totals' => ['TOTAL', $d['tpRows']->sum('billed'), $d['tpRows']->sum('paid'), $d['tpRows']->sum('due')], 'money' => [1, 2, 3],
        ];
        $e = [
            'title' => 'Agents', 'heading' => 'Agents - what we owe',
            'headings' => ['Agent', 'Cost lines', 'Total bill', 'Paid', 'Due'],
            'rows' => $d['agents']->map(fn ($r) => [$r['name'], $r['lines'], $r['bill'], $r['paid'], $r['due']])->all(),
            'totals' => ['TOTAL', $d['agents']->sum('lines'), $d['agents']->sum('bill'), $d['agents']->sum('paid'), $d['agents']->sum('due')], 'money' => [2, 3, 4], 'int' => [1],
        ];
        $f = [
            'title' => 'Shipments', 'heading' => 'Shipments in period',
            'headings' => ['Shipment', 'Date', 'Items', 'Billed', 'Received', 'Cost', 'Profit'],
            'rows' => $d['fin']->map(fn ($r) => [$r->name, $r->date, $r->items, $r->billed, $r->received, $r->cost, $r->profit])->all(),
            'totals' => ['TOTAL', '', $d['fin']->sum('items'), $d['fin']->sum('billed'), $d['fin']->sum('received'), $d['fin']->sum('cost'), $d['fin']->sum('profit')],
            'money' => [3, 4, 5, 6], 'int' => [2],
        ];

        return [$a, $b, $f, $e, $c];
    }

    private function detailSheet(string $type, Carbon $from, Carbon $to): array
    {
        $period = $from->format('d M Y') . ($from->equalTo($to) ? '' : ' - ' . $to->format('d M Y'));
        $costVal = fn ($c) => (float) ($c->final_bdt_cost ?: $c->amount);

        if (in_array($type, ['received', 'shipped', 'arrived', 'delivered'], true)) {
            $items = match ($type) {
                'received'  => $this->receivedItems($from, $to),
                'shipped'   => $this->reached(['shipping_ongoing', 'arrived_bd', 'waiting_for_customs', 'ready_to_deliver', 'delivered'], $from, $to),
                'arrived'   => $this->reached(['arrived_bd', 'waiting_for_customs', 'ready_to_deliver', 'delivered'], $from, $to),
                'delivered' => $this->reached(['delivered'], $from, $to),
            };
            return [
                'title' => 'Items', 'heading' => self::DETAIL_TYPES[$type], 'subtitle' => 'Period: ' . $period . ' | ' . $items->count() . ' items, ' . $this->boxes($items) . ' boxes',
                'headings' => ['Date', 'ID', 'CTN', 'Box', 'Goods', 'Tracking', 'Customer', 'Weight (kg)', 'Status'],
                'rows' => $items->map(fn ($i) => [
                    $i->event_date, $i->id, $i->printed_ctn_no, $i->box_no, $i->goods_name_en, $i->tracking_no,
                    (optional($i->customer)->name ?? '—') . ($i->is_third_party ? ' [3P]' : ''),
                    (float) $i->weight_kg, ucfirst(str_replace('_', ' ', $i->status)),
                ])->all(),
                'totals' => ['TOTAL', $items->count(), '', '', '', '', '', round((float) $items->sum('weight_kg'), 2), ''],
                'money' => [7], 'int' => [1],
            ];
        }

        if ($type === 'costs') {
            $rows = $this->costs($from, $to);
            return [
                'title' => 'Costs', 'heading' => self::DETAIL_TYPES[$type], 'subtitle' => 'Period: ' . $period,
                'headings' => ['Date', 'Shipment', 'Cost type', 'Agent', 'Original', 'Rate', 'Final (BDT)', 'Paid', 'Due'],
                'rows' => $rows->map(fn ($c) => [
                    $c->cost_date->toDateString(), optional($c->itemsShipment)->shipment_name, optional($c->costType)->name, optional($c->agent)->name,
                    $c->currency !== 'bdt' ? strtoupper($c->currency) . ' ' . number_format($c->amount, 2) : '', $c->currency !== 'bdt' ? (float) $c->exchange_rate_bdt : '',
                    $costVal($c), (float) $c->paid_amount, (float) $c->due_amount,
                ])->all(),
                'totals' => ['TOTAL', '', '', '', '', '', round($rows->sum($costVal), 2), round((float) $rows->sum('paid_amount'), 2), round((float) $rows->sum('due_amount'), 2)],
                'money' => [6, 7, 8],
            ];
        }

        if ($type === 'payments') {
            $rows = $this->payouts($from, $to);
            return [
                'title' => 'Payments', 'heading' => self::DETAIL_TYPES[$type], 'subtitle' => 'Period: ' . $period,
                'headings' => ['Date', 'Voucher', 'Shipment', 'Cost type', 'Agent', 'Method', 'Paid in', 'Amount (BDT)'],
                'rows' => $rows->map(fn ($p) => [
                    $p->payment_date->toDateString(), optional($p->voucher)->voucher_no, optional(optional($p->itemsShipmentCost)->itemsShipment)->shipment_name,
                    optional(optional($p->itemsShipmentCost)->costType)->name, optional(optional($p->itemsShipmentCost)->agent)->name,
                    ucfirst(str_replace('_', ' ', $p->payment_method)), $this->curText($p), (float) $p->amount,
                ])->all(),
                'totals' => ['TOTAL', '', '', '', '', '', '', round((float) $rows->sum('amount'), 2)], 'money' => [7],
            ];
        }

        $rows = $this->collections($from, $to);
        return [
            'title' => 'Collections', 'heading' => self::DETAIL_TYPES[$type], 'subtitle' => 'Period: ' . $period,
            'headings' => ['Date', 'From', 'Type', 'Method', 'Reference', 'Paid in', 'Amount (BDT)'],
            'rows' => $rows->map(fn ($r) => [$r['date'], $r['party'], $r['kind'], ucfirst(str_replace('_', ' ', $r['method'])), $r['reference'], $r['cur'], $r['amount']])->all(),
            'totals' => ['TOTAL', '', '', '', '', '', round($rows->sum('amount'), 2)], 'money' => [6],
        ];
    }
}
