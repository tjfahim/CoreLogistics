@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="mb-2 no-print">
        <a href="{{ route('reports.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports</a>
    </div>

    <div class="rp-head">
        <div><h1>Office Expense {{ $year }}</h1><p class="sub">Office bills, warehouse bills and salaries. Kept apart from shipment accounting.</p></div>
        <div class="rp-actions no-print">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export'=>1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <button class="rp-btn" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    @include('admin.partials.rp-nav')
    <form class="rp-card no-print" method="GET"><div class="rp-filter">
        <div><label>Year</label><input type="number" name="year" value="{{ $year }}" min="2000" max="2100"></div>
        <div><label>Office</label><select name="office_id"><option value="">All offices</option>@foreach($offices as $o)<option value="{{ $o->id }}" {{ $officeId==$o->id?'selected':'' }}>{{ $o->name }}</option>@endforeach</select></div>
        <div style="display:flex;gap:8px"><button class="rp-btn primary"><i class="fa fa-filter"></i> Show</button><a class="rp-btn" href="{{ route('reports.office') }}">Reset</a></div>
    </div></form>
    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Office bills</div><div class="v">{{ number_format($totals['office_bills'],2) }}</div></div>
        <div class="rp-kpi amber"><div class="l">Warehouse bills</div><div class="v">{{ number_format($totals['warehouse'],2) }}</div><div class="s">{{ number_format($totals['warehouse_kg'],2) }} kg</div></div>
        <div class="rp-kpi coral"><div class="l">Total expense {{ $year }}</div><div class="v">{{ number_format($totals['all'],2) }}</div></div>
        <div class="rp-kpi violet"><div class="l">Monthly payroll</div><div class="v">{{ number_format($totals['payroll'],2) }}</div><div class="s">{{ $totals['employees'] }} employee(s)</div></div>
    </div>
    <div class="rp-card"><div class="ttl">Office x month</div><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr><th>Office</th>@for($i=1;$i<=12;$i++)<th class="num">{{ date('M',mktime(0,0,0,$i,1)) }}</th>@endfor<th class="num">Total</th></tr></thead>
        <tbody>@forelse($matrix as $m)<tr><td>{{ $m['office']->name }}</td>@foreach($m['months'] as $v)<td class="num">{{ $v>0 ? number_format($v,0) : '—' }}</td>@endforeach<td class="num"><strong>{{ number_format($m['total'],2) }}</strong></td></tr>@empty<tr><td colspan="14" class="rp-empty">No offices.</td></tr>@endforelse</tbody>
        <tfoot><tr><td>TOTAL</td>@foreach($monthTotals as $v)<td class="num">{{ number_format($v,0) }}</td>@endforeach<td class="num">{{ number_format($totals['all'],2) }}</td></tr></tfoot></table></div></div>
    <div class="rp-grid2">
        <div class="rp-card"><div class="ttl">Warehouse bills</div><div class="rp-table-wrap"><table class="rp-table"><thead><tr><th>Date</th><th>Office</th><th class="num">Kg</th><th class="num">Rate</th><th class="num">Total</th></tr></thead><tbody>
            @forelse($warehouse as $w)<tr><td>{{ $w->bill_date->format('d M Y') }}</td><td>{{ optional($w->office)->name }}</td><td class="num">{{ number_format($w->kg,2) }}</td><td class="num">{{ number_format($w->rate_per_kg,2) }}</td><td class="num">{{ number_format($w->total_amount,2) }}</td></tr>@empty<tr><td colspan="5" class="rp-empty">None.</td></tr>@endforelse</tbody>
            <tfoot><tr><td colspan="2">TOTAL</td><td class="num">{{ number_format($totals['warehouse_kg'],2) }}</td><td></td><td class="num">{{ number_format($totals['warehouse'],2) }}</td></tr></tfoot></table></div></div>
        <div class="rp-card"><div class="ttl">Employees</div><div class="rp-table-wrap"><table class="rp-table"><thead><tr><th>Name</th><th>Office</th><th class="num">Salary</th></tr></thead><tbody>
            @forelse($payroll as $e)<tr><td>{{ $e->name }}</td><td>{{ optional($e->office)->name }}</td><td class="num">{{ number_format($e->salary,2) }}</td></tr>@empty<tr><td colspan="3" class="rp-empty">None.</td></tr>@endforelse</tbody>
            <tfoot><tr><td colspan="2">TOTAL</td><td class="num">{{ number_format($totals['payroll'],2) }}</td></tr></tfoot></table></div></div>
    </div>
</div>
@endsection
