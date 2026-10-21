<?php

namespace App\Http\Controllers;

use App\Exports\SheetsExport;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\MonthlySummary;
use App\Models\SalaryRecord;
use App\Models\ItemsShipment;
use App\Models\Office;
use App\Models\OfficeBill;
use App\Models\WarehouseBill;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports: single / multiple shipment, month or date wise, customer wise, company summary,
 * and (kept completely apart) office expense. Every report has an Excel download (?export=1).
 */
class ReportController extends Controller
{
    private const METHODS = ['cash' => 'Cash', 'bank' => 'Bank', 'bank_transfer' => 'Bank Transfer', 'check' => 'Check',
                             'cheque' => 'Cheque', 'card' => 'Card', 'mobile_banking' => 'Mobile Banking'];

    public function __construct(private AccountingService $acc)
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Gate::allows('shipment-report'), 403);
            return $next($request);
        });
    }

    private function method(?string $m): string
    {
        return $m ? (self::METHODS[$m] ?? ucwords(str_replace('_', ' ', $m))) : '';
    }

    private function dates(Request $r, ?string $defFrom, ?string $defTo): array
    {
        $r->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);

        $from = $r->has('from') ? ($r->input('from') ?: null) : $defFrom;
        $to   = $r->has('to') ? ($r->input('to') ?: null) : $defTo;

        return [$from, $to];
    }

    private function label(?string $from, ?string $to): string
    {
        if (!$from && !$to) {
            return 'All time';
        }
        return ($from ? Carbon::parse($from)->format('d M Y') : 'start') . ' to ' . ($to ? Carbon::parse($to)->format('d M Y') : 'today');
    }

    // ------------------------------------------------------------------ hub

    public function dashboard()
    {
        $from = now()->startOfYear()->toDateString();
        $to   = now()->toDateString();
        $sum  = $this->acc->summary($from, $to);

        return view('admin.reports.dashboard', compact('sum', 'from', 'to'));
    }

    public function single($id)
    {
        return redirect()->route('reports.shipments', ['shipment_ids' => [(int) $id]]);
    }

    // ------------------------------------------------------------------ shipments

    public function shipments(Request $request)
    {
        $ids = array_values(array_filter(array_map('intval', (array) $request->input('shipment_ids', []))));
        [$from, $to] = $this->dates($request, null, null);

        $fin   = $this->acc->shipmentFinancials($ids, $from, $to);
        $tot   = $this->acc->sumFinancials($fin);
        $shown = $fin->pluck('id')->all();

        // detail needs the rows; cap to keep the page fast
        $detailIds = array_slice($shown, 0, 25);
        $costLines = $this->acc->costLines($detailIds)->groupBy('items_shipment_id');
        $custRows  = $this->acc->shipmentCustomers($detailIds)->groupBy('shipment_id');

        // cost type x shipment matrix
        $allLines = $this->acc->costLines($shown);
        $types    = $allLines->groupBy(fn ($c) => optional($c->costType)->name ?? 'Other');
        $matrix   = $types->map(function ($g) use ($shown) {
            $perShip = [];
            foreach ($shown as $sid) {
                $perShip[$sid] = round($g->where('items_shipment_id', $sid)->sum(fn ($c) => (float) ($c->final_bdt_cost ?: $c->amount)), 2);
            }
            return ['by_ship' => $perShip, 'total' => round(array_sum($perShip), 2)];
        })->sortByDesc('total');

        $allShipments = ItemsShipment::orderByDesc('shipment_date')->orderByDesc('id')->get(['id', 'shipment_name', 'shipment_date']);

        if ($request->boolean('export')) {
            return $this->exportShipments($fin, $tot, $allLines, $this->acc->shipmentCustomers($shown), $matrix, $from, $to);
        }

        return view('admin.reports.shipments', compact(
            'fin', 'tot', 'costLines', 'custRows', 'matrix', 'allShipments', 'ids', 'from', 'to', 'detailIds'
        ));
    }

    private function exportShipments($fin, $tot, $lines, $cust, $matrix, $from, $to)
    {
        $sub = 'Shipments: ' . $fin->count() . '  |  Period (shipment date): ' . $this->label($from, $to);

        $s1 = [
            'title' => 'Shipments', 'heading' => 'Shipment Report', 'subtitle' => $sub,
            'headings' => ['Shipment', 'Date', 'Status', 'Items', 'Weight (kg)', 'CBM', 'Customers', 'Customer billed', 'Received', 'Receivable',
                           'Shipment cost', 'Cost paid', 'Cost due', 'Profit', 'Margin %'],
            'rows' => $fin->map(fn ($f) => [$f->name, $f->date, ucfirst($f->status), $f->items, $f->weight, $f->cbm, $f->customers, $f->billed, $f->received,
                           $f->receivable, $f->cost, $f->cost_paid, $f->cost_due, $f->profit, $f->margin ?? ''])->all(),
            'totals' => ['TOTAL', '', '', $tot['items'], $tot['weight'], $tot['cbm'], '', $tot['billed'], $tot['received'], $tot['receivable'],
                         $tot['cost'], $tot['cost_paid'], $tot['cost_due'], $tot['profit'], $tot['margin'] ?? ''],
            'money' => [4, 5, 7, 8, 9, 10, 11, 12, 13], 'int' => [3, 6],
        ];

        $names = $fin->pluck('name', 'id');
        $s2 = [
            'title' => 'Cost lines', 'heading' => 'Shipment cost lines', 'subtitle' => $sub,
            'headings' => ['Shipment', 'Cost type', 'Agent', 'Cost date', 'Currency', 'Original amount', 'Rate', 'Cost (BDT)', 'Paid', 'Due', 'Status'],
            'rows' => $lines->map(fn ($c) => [$names[$c->items_shipment_id] ?? $c->items_shipment_id, optional($c->costType)->name, optional($c->agent)->name,
                           optional($c->cost_date)->format('Y-m-d'), strtoupper($c->currency), (float) $c->amount, (float) $c->exchange_rate_bdt,
                           (float) ($c->final_bdt_cost ?: $c->amount), (float) $c->paid_amount, (float) $c->due_amount, ucfirst($c->status)])->all(),
            'totals' => ['TOTAL', '', '', '', '', '', '', $lines->sum(fn ($c) => (float) ($c->final_bdt_cost ?: $c->amount)), $lines->sum('paid_amount'), $lines->sum('due_amount'), ''],
            'money' => [5, 6, 7, 8, 9],
        ];

        $s3 = [
            'title' => 'Customers', 'heading' => 'Customers per shipment', 'subtitle' => $sub,
            'headings' => ['Shipment', 'Customer', 'Code', 'Items', 'Weight (kg)', 'Billed', 'Received', 'Due', 'Status'],
            'rows' => $cust->map(fn ($r) => [$r->shipment_name, optional($r->customer)->name, optional($r->customer)->customer_code, $r->items, $r->weight,
                           $r->billed, $r->paid, $r->due, ['paid' => 'Paid', 'partial' => 'Partial', 'due' => 'Due', 'no-billing' => 'No billing'][$r->status]])->all(),
            'totals' => ['TOTAL', '', '', $cust->sum('items'), $cust->sum('weight'), $cust->sum('billed'), $cust->sum('paid'), $cust->sum('due'), ''],
            'money' => [4, 5, 6, 7], 'int' => [3],
        ];

        $s4 = [
            'title' => 'Cost by type', 'heading' => 'Cost type x shipment (BDT)', 'subtitle' => $sub,
            'headings' => array_merge(['Cost type'], $fin->pluck('name')->all(), ['TOTAL']),
            'rows' => $matrix->map(fn ($m, $type) => array_merge([$type], array_values($m['by_ship']), [$m['total']]))->values()->all(),
            'totals' => array_merge(['TOTAL'], $fin->map(fn ($f) => $matrix->sum(fn ($m) => $m['by_ship'][$f->id] ?? 0))->all(), [$matrix->sum('total')]),
            'money' => range(1, $fin->count() + 1),
        ];

        return Excel::download(new SheetsExport([$s1, $s2, $s3, $s4]), 'shipment-report-' . date('Y-m-d') . '.xlsx');
    }

    // ------------------------------------------------------------------ month / date wise

    public function period(Request $request)
    {
        [$from, $to] = $this->dates($request, now()->subMonths(5)->startOfMonth()->toDateString(), now()->toDateString());
        $from = $from ?: now()->subMonths(5)->startOfMonth()->toDateString();
        $to   = $to ?: now()->toDateString();

        $group = $request->input('group') === 'day' ? 'day' : 'month';
        if ($group === 'day' && Carbon::parse($from)->diffInDays(Carbon::parse($to)) > 370) {
            $group = 'month';
            $capped = true;
        }

        $rep = $this->acc->periodReport($from, $to, $group);

        if ($request->boolean('export')) {
            return $this->exportPeriod($rep, $from, $to, $group);
        }

        $capped = $capped ?? false;

        return view('admin.reports.period', compact('rep', 'from', 'to', 'group', 'capped'));
    }

    private function exportPeriod(array $rep, string $from, string $to, string $group)
    {
        $sub = 'Period: ' . $this->label($from, $to) . '  |  grouped by ' . $group;

        $s1 = [
            'title' => ucfirst($group) . ' wise', 'heading' => ucfirst($group) . '-wise Report', 'subtitle' => $sub,
            'headings' => [$group === 'day' ? 'Date' : 'Month', 'Shipments', 'Customer billed', 'Received', 'Shipment cost', 'Paid to suppliers', 'Profit (billed - cost)', 'Net cash (received - paid)'],
            'rows' => $rep['rows']->map(fn ($r) => [$r['label'], $r['shipments'], $r['billed'], $r['received'], $r['cost'], $r['paid_out'], $r['profit'], $r['net']])->all(),
            'totals' => ['TOTAL', $rep['totals']['shipments'], $rep['totals']['billed'], $rep['totals']['received'], $rep['totals']['cost'], $rep['totals']['paid_out'], $rep['totals']['profit'], $rep['totals']['net']],
            'money' => [2, 3, 4, 5, 6, 7], 'int' => [1],
        ];

        $s2 = [
            'title' => 'Receipts', 'heading' => 'Customer receipts', 'subtitle' => $sub,
            'headings' => ['Date', 'Receipt', 'Customer', 'Method', 'Reference', 'Split across shipments', 'Entered by', 'Amount'],
            'rows' => $rep['receipts']->map(fn ($p) => [$p->payment_date->format('Y-m-d'), AccountingService::receiptNo($p->id), optional($p->customer)->name,
                           $this->method($p->payment_method), $p->reference_no,
                           $p->allocations->map(fn ($a) => (optional($a->itemshipment)->shipment_name ?? '#' . $a->itemshipment_id) . ': ' . number_format($a->amount, 2))->implode(' | '),
                           optional($p->user)->name, (float) $p->amount])->all(),
            'totals' => ['TOTAL', '', '', '', '', '', '', (float) $rep['receipts']->sum('amount')],
            'money' => [7],
        ];

        $s3 = [
            'title' => 'Supplier payments', 'heading' => 'Payments to suppliers', 'subtitle' => $sub,
            'headings' => ['Date', 'Voucher', 'Shipment', 'Cost type', 'Agent', 'Method', 'Reference', 'Entered by', 'Amount'],
            'rows' => $rep['payouts']->map(fn ($p) => [$p->payment_date->format('Y-m-d'), $p->voucher->voucher_no ?? 'Direct',
                           optional(optional($p->itemsShipmentCost)->itemsShipment)->shipment_name, optional(optional($p->itemsShipmentCost)->costType)->name,
                           optional(optional($p->itemsShipmentCost)->agent)->name, $this->method($p->payment_method), $p->reference_no,
                           optional($p->user)->name ?? $p->paid_by, (float) $p->amount])->all(),
            'totals' => ['TOTAL', '', '', '', '', '', '', '', (float) $rep['payouts']->sum('amount')],
            'money' => [8],
        ];

        return Excel::download(new SheetsExport([$s1, $s2, $s3]), "period-report-{$from}-to-{$to}.xlsx");
    }

    // ------------------------------------------------------------------ customer wise

    public function customers(Request $request)
    {
        [$from, $to] = $this->dates($request, null, null);
        $ids    = array_values(array_filter(array_map('intval', (array) $request->input('customer_ids', []))));
        $status = in_array($request->input('status'), ['due', 'clear'], true) ? $request->input('status') : null;

        $rep = $this->acc->customerReport($from, $to, $ids, $status);

        if ($request->boolean('export')) {
            return $this->exportCustomers($rep, $from, $to);
        }

        $allCustomers = Customer::orderBy('name')->get(['id', 'name', 'customer_code']);

        return view('admin.reports.customers', compact('rep', 'from', 'to', 'ids', 'status', 'allCustomers'));
    }

    private function exportCustomers(array $rep, ?string $from, ?string $to)
    {
        $s = [
            'title' => 'Customers', 'heading' => 'Customer-wise Report',
            'subtitle' => 'Period: ' . $this->label($from, $to) . '  |  Closing = opening + billed - received',
            'headings' => ['Customer', 'Code', 'Phone', 'Shipments', 'Items', 'Weight (kg)', 'Opening due', 'Billed in period', 'Received in period', 'Closing due', 'Last payment'],
            'rows' => $rep['rows']->map(fn ($c) => [$c->name, $c->code, $c->phone, $c->shipments, $c->items, $c->weight, $c->opening, $c->billed,
                           $c->received, $c->closing, $c->last_payment ? substr($c->last_payment, 0, 10) : ''])->all(),
            'totals' => ['TOTAL', '', '', $rep['totals']['shipments'], $rep['totals']['items'], $rep['totals']['weight'], $rep['totals']['opening'],
                         $rep['totals']['billed'], $rep['totals']['received'], $rep['totals']['closing'], ''],
            'money' => [5, 6, 7, 8, 9], 'int' => [3, 4],
        ];

        return Excel::download(new SheetsExport([$s]), 'customer-report-' . date('Y-m-d') . '.xlsx');
    }

    // ------------------------------------------------------------------ company summary

    public function summary(Request $request)
    {
        [$from, $to] = $this->dates($request, now()->startOfYear()->toDateString(), now()->toDateString());

        $sum = $this->acc->summary($from, $to);

        if ($request->boolean('export')) {
            return $this->exportSummary($sum, $from, $to);
        }

        return view('admin.reports.summary', compact('sum', 'from', 'to'));
    }

    private function exportSummary(array $sum, ?string $from, ?string $to)
    {
        $sub = 'Period: ' . $this->label($from, $to);

        $s1 = [
            'title' => 'Summary', 'heading' => 'Business Summary', 'subtitle' => $sub,
            'headings' => ['Item', 'Amount (BDT)'],
            'rows' => [
                ['Customer billed (shipments in period)', $sum['billed']], ['Received from customers', $sum['received']],
                ['Shipment costs (in period)', $sum['cost']], ['Paid to suppliers', $sum['paid_out']],
                ['Profit = billed - cost', $sum['profit']], ['Net cash = received - paid', $sum['cash_net']],
                ['Receivable at end of period', $sum['receivable']], ['Payable at end of period', $sum['payable']],
            ],
            'money' => [1],
        ];

        $s2 = [
            'title' => 'Cost breakdown', 'heading' => 'Costs by type and agent', 'subtitle' => $sub,
            'headings' => ['Group', 'Name', 'Amount'],
            'rows' => array_merge(
                $sum['by_type']->map(fn ($v, $k) => ['Cost type', $k, $v])->values()->all(),
                $sum['by_agent']->map(fn ($v, $k) => ['Agent', $k, $v])->values()->all()
            ),
            'money' => [2],
        ];

        $s3 = [
            'title' => 'Customers', 'heading' => 'Top customers', 'subtitle' => $sub,
            'headings' => ['Customer', 'Code', 'Billed in period', 'Received in period', 'Closing due'],
            'rows' => $sum['top_billed']->map(fn ($c) => [$c->name, $c->code, $c->billed, $c->received, $c->closing])->all(),
            'money' => [2, 3, 4],
        ];

        $s4 = [
            'title' => 'Shipments', 'heading' => 'Shipments in period', 'subtitle' => $sub,
            'headings' => ['Shipment', 'Date', 'Billed', 'Received', 'Cost', 'Profit', 'Margin %'],
            'rows' => $sum['fin']->map(fn ($f) => [$f->name, $f->date, $f->billed, $f->received, $f->cost, $f->profit, $f->margin ?? ''])->all(),
            'totals' => ['TOTAL', '', $sum['tot']['billed'], $sum['tot']['received'], $sum['tot']['cost'], $sum['tot']['profit'], $sum['tot']['margin'] ?? ''],
            'money' => [2, 3, 4, 5],
        ];

        return Excel::download(new SheetsExport([$s1, $s2, $s3, $s4]), 'business-summary-' . date('Y-m-d') . '.xlsx');
    }

    // ------------------------------------------------------------------ office expense (separate from shipments)

    public function office(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100', 'office_id' => 'nullable|integer',
            'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from',
        ]);

        $officeId = $request->integer('office_id') ?: null;

        // Range: explicit from/to, else the whole selected year
        if ($request->filled('from') || $request->filled('to')) {
            $from = \Carbon\Carbon::parse($request->input('from') ?: ($request->input('to')))->startOfDay();
            $to   = \Carbon\Carbon::parse($request->input('to') ?: ($request->input('from')))->endOfDay();
            $year = (int) $from->year;
        } else {
            $year = (int) ($request->input('year') ?: now()->year);
            $from = \Carbon\Carbon::create($year, 1, 1)->startOfDay();
            $to   = \Carbon\Carbon::create($year, 12, 31)->endOfDay();
        }
        $customRange = $request->filled('from') || $request->filled('to');

        $offices = Office::orderBy('name')->get();

        $bills = OfficeBill::with('office:id,name')
            ->whereBetween('bill_date', [$from->toDateString(), $to->toDateString()])
            ->when($officeId, fn ($q) => $q->where('office_id', $officeId))->get();

        $warehouse = WarehouseBill::with('office:id,name')
            ->whereBetween('bill_date', [$from->toDateString(), $to->toDateString()])
            ->when($officeId, fn ($q) => $q->where('office_id', $officeId))
            ->orderBy('bill_date')->get();

        // salaries actually paid (month snapshots), month granularity
        SalaryRecord::ensureCurrentMonth();
        $fromKey = $from->year * 12 + $from->month;
        $toKey   = $to->year * 12 + $to->month;
        $salaries = SalaryRecord::whereRaw('(year * 12 + month) BETWEEN ? AND ?', [$fromKey, $toKey])
            ->when($officeId, fn ($q) => $q->where('office_id', $officeId))->get();

        $payroll = Employee::with('office:id,name')->when($officeId, fn ($q) => $q->where('office_id', $officeId))->get();

        // office x month matrix (months of $year inside the range)
        $matrix = [];
        foreach ($offices->when($officeId, fn ($c) => $c->where('id', $officeId)) as $o) {
            $row = array_fill(1, 12, 0.0);
            foreach ($bills->where('office_id', $o->id) as $b) {
                if ((int) $b->bill_date->year === $year) $row[(int) $b->bill_date->month] += (float) $b->amount;
            }
            foreach ($warehouse->where('office_id', $o->id) as $w) {
                if ((int) $w->bill_date->year === $year) $row[(int) $w->bill_date->format('n')] += (float) $w->total_amount;
            }
            foreach ($salaries->where('office_id', $o->id)->where('year', $year) as $sr) {
                $row[(int) $sr->month] += (float) $sr->amount;
            }
            $matrix[$o->id] = ['office' => $o, 'months' => $row, 'total' => array_sum($row),
                               'office_bills' => (float) $bills->where('office_id', $o->id)->sum('amount'),
                               'warehouse' => (float) $warehouse->where('office_id', $o->id)->sum('total_amount'),
                               'salaries' => (float) $salaries->where('office_id', $o->id)->sum('amount'),
                               'payroll' => (float) $payroll->where('office_id', $o->id)->sum('salary'),
                               'employees' => $payroll->where('office_id', $o->id)->count()];
        }

        $monthTotals = array_fill(1, 12, 0.0);
        foreach ($matrix as $m) {
            foreach ($m['months'] as $i => $v) {
                $monthTotals[$i] += $v;
            }
        }

        // ---- day-wise (office bills + warehouse bills) ----
        $daily = [];
        foreach ($bills as $b) {
            $d = $b->bill_date->toDateString();
            $daily[$d]['office'] = ($daily[$d]['office'] ?? 0) + (float) $b->amount;
        }
        foreach ($warehouse as $w) {
            $d = $w->bill_date->toDateString();
            $daily[$d]['warehouse'] = ($daily[$d]['warehouse'] ?? 0) + (float) $w->total_amount;
        }
        krsort($daily);

        // ---- month-wise over the whole range (may span years) ----
        $monthly = [];
        for ($c = $from->copy()->startOfMonth(); $c <= $to; $c->addMonth()) {
            $monthly[$c->format('Y-m')] = ['label' => $c->format('M Y'), 'office' => 0.0, 'warehouse' => 0.0, 'salary' => 0.0, 'people' => 0];
        }
        foreach ($bills as $b)     { $k = $b->bill_date->format('Y-m'); if (isset($monthly[$k])) $monthly[$k]['office'] += (float) $b->amount; }
        foreach ($warehouse as $w) { $k = $w->bill_date->format('Y-m'); if (isset($monthly[$k])) $monthly[$k]['warehouse'] += (float) $w->total_amount; }
        foreach ($salaries as $sr) { $k = sprintf('%04d-%02d', $sr->year, $sr->month); if (isset($monthly[$k])) { $monthly[$k]['salary'] += (float) $sr->amount; $monthly[$k]['people']++; } }
        foreach ($monthly as &$mm) { $mm['total'] = $mm['office'] + $mm['warehouse'] + $mm['salary']; }
        unset($mm);

        $monthsInRange = max(1, count($monthly));
        $rangeTotal    = array_sum(array_column($monthly, 'total'));

        // today / this month (office+warehouse bills are dated; salary is monthly)
        $todayStr = now()->toDateString();
        $todayTotal = (float) OfficeBill::whereDate('bill_date', $todayStr)->when($officeId, fn ($q) => $q->where('office_id', $officeId))->sum('amount')
                    + (float) WarehouseBill::whereDate('bill_date', $todayStr)->when($officeId, fn ($q) => $q->where('office_id', $officeId))->sum('total_amount');
        $thisMonth  = (float) OfficeBill::whereYear('bill_date', now()->year)->whereMonth('bill_date', now()->month)->when($officeId, fn ($q) => $q->where('office_id', $officeId))->sum('amount')
                    + (float) WarehouseBill::whereYear('bill_date', now()->year)->whereMonth('bill_date', now()->month)->when($officeId, fn ($q) => $q->where('office_id', $officeId))->sum('total_amount')
                    + (float) SalaryRecord::where('year', now()->year)->where('month', now()->month)->when($officeId, fn ($q) => $q->where('office_id', $officeId))->sum('amount');

        // per-kg cost: from the saved Summary Bills falling inside the range
        $perKg = MonthlySummary::whereRaw('(year * 12 + month) BETWEEN ? AND ?', [$fromKey, $toKey])->get();
        $perKgStats = [
            'count'   => $perKg->count(),
            'average' => $perKg->count() ? (float) $perKg->avg('cost_per_kg') : 0.0,
            'latest'  => optional($perKg->sortByDesc('id')->first())->cost_per_kg,
        ];

        $totals = [
            'office_bills' => (float) $bills->sum('amount'),
            'warehouse'    => (float) $warehouse->sum('total_amount'),
            'warehouse_kg' => (float) $warehouse->sum('kg'),
            'salaries'     => (float) $salaries->sum('amount'),
            'payroll'      => (float) $payroll->sum('salary'),
            'employees'    => $payroll->count(),
            'all'          => (float) $rangeTotal,
            'avg_month'    => $rangeTotal / $monthsInRange,
            'months'       => $monthsInRange,
            'today'        => $todayTotal,
            'this_month'   => $thisMonth,
        ];

        if ($request->boolean('export')) {
            return $this->exportOffice($matrix, $monthTotals, $warehouse, $payroll, $totals, $year);
        }

        return view('admin.reports.office', compact(
            'matrix', 'monthTotals', 'warehouse', 'payroll', 'totals', 'year', 'offices', 'officeId',
            'from', 'to', 'customRange', 'daily', 'monthly', 'perKgStats'
        ));
    }

    private function exportOffice(array $matrix, array $monthTotals, $warehouse, $payroll, array $totals, int $year)
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = date('M', mktime(0, 0, 0, $i, 1));
        }

        $s1 = [
            'title' => 'Office expense', 'heading' => "Office Expense {$year} (office bills + warehouse bills)",
            'subtitle' => 'Kept separate from shipment accounting',
            'headings' => array_merge(['Office'], $months, ['Year total']),
            'rows' => collect($matrix)->map(fn ($m) => array_merge([$m['office']->name], array_values($m['months']), [$m['total']]))->values()->all(),
            'totals' => array_merge(['TOTAL'], array_values($monthTotals), [$totals['all']]),
            'money' => range(1, 13),
        ];

        $s2 = [
            'title' => 'Warehouse bills', 'heading' => "Warehouse bills {$year}",
            'headings' => ['Date', 'Office', 'Kg', 'Rate / kg', 'Total', 'Note'],
            'rows' => $warehouse->map(fn ($w) => [$w->bill_date->format('Y-m-d'), optional($w->office)->name, (float) $w->kg, (float) $w->rate_per_kg, (float) $w->total_amount, $w->note])->all(),
            'totals' => ['TOTAL', '', $totals['warehouse_kg'], '', $totals['warehouse'], ''],
            'money' => [2, 3, 4],
        ];

        $s3 = [
            'title' => 'Payroll', 'heading' => 'Employees and monthly salary',
            'headings' => ['Employee', 'Office', 'Monthly salary'],
            'rows' => $payroll->map(fn ($e) => [$e->name, optional($e->office)->name, (float) $e->salary])->all(),
            'totals' => ['TOTAL', '', $totals['payroll']],
            'money' => [2],
        ];

        return Excel::download(new SheetsExport([$s1, $s2, $s3]), "office-expense-{$year}.xlsx");
    }
}
