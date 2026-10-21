@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
@php
    $q = ['from' => $from->toDateString(), 'to' => $to->toDateString()];
    $period = $from->format('d M Y') . ($from->equalTo($to) ? '' : ' → ' . $to->format('d M Y'));
    $m = $money; $o = $ops;
@endphp
<div class="rp">
    <div class="rp-head">
        <div><h1>Final Report</h1><p class="sub">Goods flow and money for <strong>{{ $period }}</strong>. Click any figure for its details.</p></div>
        <div class="rp-actions no-print">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <a class="rp-btn red" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
            <button class="rp-btn" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    @include('admin.partials.rp-nav')

    <form class="rp-card no-print" method="GET"><div class="rp-filter">
        <div><label>From</label><input type="date" name="from" value="{{ $from->toDateString() }}"></div>
        <div><label>To</label><input type="date" name="to" value="{{ $to->toDateString() }}"></div>
        <div style="display:flex;gap:8px"><button class="rp-btn primary"><i class="fa fa-filter"></i> Show</button></div>
        <div class="rp-presets" style="margin:0">
            @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This week', 'month' => 'This month', 'year' => 'This year'] as $k => $l)
                <a href="{{ route('reports.final', ['preset' => $k == 'today' ? null : $k]) }}">{{ $l }}</a>
            @endforeach
        </div>
    </div></form>

    <div class="ttl" style="margin:6px 0 8px;font-weight:800">Goods</div>
    <div class="rp-kpis">
        @foreach(['received' => ['Received in China warehouse', ''], 'shipped' => ['Shipped', 'amber'], 'arrived' => ['Entered BD warehouse', 'violet'], 'delivered' => ['Delivered to customers', 'teal']] as $k => [$label, $cls])
            <a href="{{ route('reports.final.detail', ['type' => $k] + $q) }}" style="text-decoration:none;color:inherit">
                <div class="rp-kpi {{ $cls }}"><div class="l">{{ $label }}</div><div class="v">{{ number_format($o[$k]['items']) }} <small style="font-size:13px">items</small></div>
                    <div class="s">{{ number_format($o[$k]['boxes']) }} boxes · {{ number_format($o[$k]['kg'], 2) }} kg</div></div></a>
        @endforeach
    </div>

    <div class="ttl" style="margin:6px 0 8px;font-weight:800">Money</div>
    <div class="rp-kpis">
        <a href="{{ route('reports.final.detail', ['type' => 'costs'] + $q) }}" style="text-decoration:none;color:inherit"><div class="rp-kpi amber"><div class="l">Costing</div><div class="v">{{ number_format($m['cost'],2) }}</div><div class="s">{{ $m['cost_lines'] }} bill line(s)</div></div></a>
        <a href="{{ route('reports.final.detail', ['type' => 'payments'] + $q) }}" style="text-decoration:none;color:inherit"><div class="rp-kpi coral"><div class="l">Paid out</div><div class="v">{{ number_format($m['paid_out'],2) }}</div>
            <div class="s">{{ $m['paid_lines'] }} payment(s)@foreach($m['foreign'] as $cur => $f) · {{ strtoupper($cur) }} {{ number_format($f['foreign'],2) }}@endforeach</div></div></a>
        <a href="{{ route('reports.final.detail', ['type' => 'collections'] + $q) }}" style="text-decoration:none;color:inherit"><div class="rp-kpi teal"><div class="l">Collected</div><div class="v">{{ number_format($m['collected'],2) }}</div><div class="s">{{ $m['coll_lines'] }} receipt(s)</div></div></a>
        <a href="{{ route('reports.period', $q) }}" style="text-decoration:none;color:inherit"><div class="rp-kpi"><div class="l">Billed</div><div class="v">{{ number_format($m['billed'],2) }}</div><div class="s">shipments in period</div></div></a>
        <div class="rp-kpi {{ $m['profit'] >= 0 ? 'teal' : 'coral' }}"><div class="l">Profit</div><div class="v">{{ number_format($m['profit'],2) }}</div><div class="s">billed − costing</div></div>
        <div class="rp-kpi {{ $m['net_cash'] >= 0 ? 'teal' : 'coral' }}"><div class="l">Net cash</div><div class="v">{{ number_format($m['net_cash'],2) }}</div><div class="s">collected − paid out</div></div>
    </div>

    <div class="rp-kpis">
        <a href="{{ route('ledger.index') }}" style="text-decoration:none;color:inherit"><div class="rp-kpi coral"><div class="l">Receivable (customers + 3rd parties)</div><div class="v">{{ number_format($m['receivable'],2) }}</div><div class="s">at end of period</div></div></a>
        <a href="{{ route('shipment-bills.ledger') }}" style="text-decoration:none;color:inherit"><div class="rp-kpi amber"><div class="l">Payable (agents / suppliers)</div><div class="v">{{ number_format($m['payable'],2) }}</div><div class="s">at end of period</div></div></a>
    </div>

    <div class="rp-card"><div class="ttl">{{ $byMonth ? 'Month wise' : 'Day wise' }}</div><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr><th>{{ $byMonth ? 'Month' : 'Date' }}</th><th class="num">Received</th><th class="num">Shipped</th><th class="num">Arrived BD</th><th class="num">Delivered</th><th class="num">Costing</th><th class="num">Paid out</th><th class="num">Collected</th></tr></thead>
        <tbody>@foreach($days as $r)<tr><td>{{ $r['label'] }}</td><td class="num">{{ $r['received'] ?: '—' }}</td><td class="num">{{ $r['shipped'] ?: '—' }}</td><td class="num">{{ $r['arrived'] ?: '—' }}</td><td class="num">{{ $r['delivered'] ?: '—' }}</td>
            <td class="num">{{ $r['cost'] ? number_format($r['cost'],2) : '—' }}</td><td class="num">{{ $r['paid_out'] ? number_format($r['paid_out'],2) : '—' }}</td><td class="num">{{ $r['collected'] ? number_format($r['collected'],2) : '—' }}</td></tr>@endforeach</tbody>
        @php $dd = collect($days); @endphp
        <tfoot><tr><td>TOTAL</td><td class="num">{{ $dd->sum('received') }}</td><td class="num">{{ $dd->sum('shipped') }}</td><td class="num">{{ $dd->sum('arrived') }}</td><td class="num">{{ $dd->sum('delivered') }}</td>
            <td class="num">{{ number_format($dd->sum('cost'),2) }}</td><td class="num">{{ number_format($dd->sum('paid_out'),2) }}</td><td class="num">{{ number_format($dd->sum('collected'),2) }}</td></tr></tfoot>
    </table></div></div>

    <div class="rp-grid2">
        <div class="rp-card"><div class="ttl">Customers who owe us <a class="no-print" href="{{ route('ledger.index', ['status' => 'due']) }}" style="font-size:12px;font-weight:600">all →</a></div><div class="rp-table-wrap"><table class="rp-table">
            <thead><tr><th>Customer</th><th class="num">Due</th></tr></thead>
            <tbody>@forelse($topDue as $c)<tr><td><a href="{{ route('ledger.customer', $c->customer_id) }}">{{ $c->name }}</a></td><td class="num"><strong>{{ number_format($c->closing,2) }}</strong></td></tr>@empty<tr><td colspan="2" class="rp-empty">No dues.</td></tr>@endforelse</tbody></table></div></div>
        <div class="rp-card"><div class="ttl">Third parties <a class="no-print" href="{{ route('third-parties.index') }}" style="font-size:12px;font-weight:600">all →</a></div><div class="rp-table-wrap"><table class="rp-table">
            <thead><tr><th>Company</th><th class="num">Billed</th><th class="num">Received</th><th class="num">Due</th></tr></thead>
            <tbody>@forelse($tpRows as $r)<tr><td><a href="{{ route('third-parties.ledger', $r['id']) }}">{{ $r['name'] }}</a></td><td class="num">{{ number_format($r['billed'],2) }}</td><td class="num">{{ number_format($r['paid'],2) }}</td><td class="num"><strong>{{ number_format($r['due'],2) }}</strong></td></tr>@empty<tr><td colspan="4" class="rp-empty">No third-party business yet.</td></tr>@endforelse</tbody></table></div></div>
    </div>

    <div class="rp-grid2">
        <div class="rp-card"><div class="ttl">Agents - what we owe <a class="no-print" href="{{ route('ledger.agents') }}" style="font-size:12px;font-weight:600">all →</a></div><div class="rp-table-wrap"><table class="rp-table">
            <thead><tr><th>Agent</th><th class="num">Bill</th><th class="num">Paid</th><th class="num">Due</th></tr></thead>
            <tbody>@forelse($agents as $a)<tr><td>@if(isset($agentIds[$a['name']]))<a href="{{ route('ledger.agent', $agentIds[$a['name']]) }}">{{ $a['name'] }}</a>@else{{ $a['name'] }}@endif</td><td class="num">{{ number_format($a['bill'],2) }}</td><td class="num">{{ number_format($a['paid'],2) }}</td><td class="num"><strong>{{ number_format($a['due'],2) }}</strong></td></tr>@empty<tr><td colspan="4" class="rp-empty">No costs yet.</td></tr>@endforelse</tbody></table></div></div>
        <div class="rp-card"><div class="ttl">Shipments in period</div><div class="rp-table-wrap"><table class="rp-table">
            <thead><tr><th>Shipment</th><th class="num">Billed</th><th class="num">Cost</th><th class="num">Profit</th></tr></thead>
            <tbody>@forelse($fin as $r)<tr><td><a href="{{ route('ledger.shipment', $r->id) }}">{{ $r->name }}</a> <small class="text-muted">{{ $r->date }}</small></td><td class="num">{{ number_format($r->billed,2) }}</td><td class="num">{{ number_format($r->cost,2) }}</td><td class="num"><strong class="{{ $r->profit < 0 ? 'text-danger' : '' }}">{{ number_format($r->profit,2) }}</strong></td></tr>@empty<tr><td colspan="4" class="rp-empty">No shipments dated in this period.</td></tr>@endforelse</tbody></table></div></div>
    </div>
</div>
@endsection
