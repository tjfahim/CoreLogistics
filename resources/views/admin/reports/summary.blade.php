@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="rp-head">
        <div><h1>Business Summary</h1><p class="sub">Shipment business only. Office expense is reported separately.</p></div>
        <div class="rp-actions no-print">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export'=>1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <button class="rp-btn" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    @include('admin.partials.rp-nav')
    <form class="rp-card no-print" method="GET">
        <div class="rp-filter">
            <div><label>From</label><input type="date" name="from" value="{{ $from }}"></div>
            <div><label>To</label><input type="date" name="to" value="{{ $to }}"></div>
            <div style="display:flex;gap:8px"><button class="rp-btn primary"><i class="fa fa-filter"></i> Show</button><a class="rp-btn" href="{{ route('reports.summary') }}">Reset</a></div>
        </div>
        @include('admin.partials.rp-dates')
    </form>
    <div class="rp-print-head"><b>Business Summary</b><br>{{ $from ?: 'start' }} to {{ $to ?: 'today' }}</div>
    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Customer billed</div><div class="v">{{ number_format($sum['billed'],2) }}</div></div>
        <div class="rp-kpi amber"><div class="l">Shipment cost</div><div class="v">{{ number_format($sum['cost'],2) }}</div></div>
        <div class="rp-kpi violet"><div class="l">Profit</div><div class="v {{ $sum['profit']<0?'neg':'pos' }}">{{ number_format($sum['profit'],2) }}</div><div class="s">Margin {{ $sum['billed']>0 ? round($sum['profit']/$sum['billed']*100,1) : 0 }}%</div></div>
        <div class="rp-kpi teal"><div class="l">Received</div><div class="v">{{ number_format($sum['received'],2) }}</div></div>
        <div class="rp-kpi amber"><div class="l">Paid to suppliers</div><div class="v">{{ number_format($sum['paid_out'],2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Net cash</div><div class="v {{ $sum['cash_net']<0?'neg':'pos' }}">{{ number_format($sum['cash_net'],2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Receivable (end)</div><div class="v">{{ number_format($sum['receivable'],2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Payable (end)</div><div class="v">{{ number_format($sum['payable'],2) }}</div></div>
    </div>
    <div class="rp-grid2">
        <div class="rp-card"><div class="ttl">Cost by type</div><table class="rp-table"><tbody>@forelse($sum['by_type'] as $k=>$v)<tr><td>{{ $k }}</td><td class="num">{{ number_format($v,2) }}</td></tr>@empty<tr><td class="rp-empty">No costs.</td></tr>@endforelse</tbody><tfoot><tr><td>TOTAL</td><td class="num">{{ number_format($sum['by_type']->sum(),2) }}</td></tr></tfoot></table></div>
        <div class="rp-card"><div class="ttl">Cost by agent</div><table class="rp-table"><tbody>@forelse($sum['by_agent'] as $k=>$v)<tr><td>{{ $k }}</td><td class="num">{{ number_format($v,2) }}</td></tr>@empty<tr><td class="rp-empty">No costs.</td></tr>@endforelse</tbody><tfoot><tr><td>TOTAL</td><td class="num">{{ number_format($sum['by_agent']->sum(),2) }}</td></tr></tfoot></table></div>
        <div class="rp-card"><div class="ttl">Received by method</div><table class="rp-table"><tbody>@forelse($sum['recv_method'] as $k=>$v)<tr><td>{{ ucwords(str_replace('_',' ',$k)) }}</td><td class="num">{{ number_format($v,2) }}</td></tr>@empty<tr><td class="rp-empty">None.</td></tr>@endforelse</tbody></table></div>
        <div class="rp-card"><div class="ttl">Paid out by method</div><table class="rp-table"><tbody>@forelse($sum['pay_method'] as $k=>$v)<tr><td>{{ ucwords(str_replace('_',' ',$k)) }}</td><td class="num">{{ number_format($v,2) }}</td></tr>@empty<tr><td class="rp-empty">None.</td></tr>@endforelse</tbody></table></div>
        <div class="rp-card"><div class="ttl">Top customers by billing</div><table class="rp-table"><thead><tr><th>Customer</th><th class="num">Billed</th><th class="num">Due</th></tr></thead><tbody>@forelse($sum['top_billed'] as $c)<tr><td><a class="lnk" href="{{ route('ledger.customer',$c->customer_id) }}">{{ $c->name }}</a></td><td class="num">{{ number_format($c->billed,2) }}</td><td class="num">{{ number_format($c->closing,2) }}</td></tr>@empty<tr><td colspan="3" class="rp-empty">None.</td></tr>@endforelse</tbody></table></div>
        <div class="rp-card"><div class="ttl">Highest dues</div><table class="rp-table"><thead><tr><th>Customer</th><th class="num">Due</th></tr></thead><tbody>@forelse($sum['top_due'] as $c)<tr><td><a class="lnk" href="{{ route('ledger.customer',$c->customer_id) }}">{{ $c->name }}</a></td><td class="num neg">{{ number_format($c->closing,2) }}</td></tr>@empty<tr><td colspan="2" class="rp-empty">No dues.</td></tr>@endforelse</tbody></table></div>
    </div>
    <div class="rp-card"><div class="ttl">Shipments in period</div><div class="rp-table-wrap"><table class="rp-table"><thead><tr><th>Shipment</th><th>Date</th><th class="num">Billed</th><th class="num">Received</th><th class="num">Cost</th><th class="num">Profit</th><th class="num">Margin</th></tr></thead><tbody>
        @forelse($sum['fin'] as $f)<tr><td><a class="lnk" href="{{ route('reports.shipments',['shipment_ids'=>[$f->id]]) }}">{{ $f->name }}</a></td><td>{{ $f->date }}</td><td class="num">{{ number_format($f->billed,2) }}</td><td class="num">{{ number_format($f->received,2) }}</td><td class="num">{{ number_format($f->cost,2) }}</td><td class="num {{ $f->profit<0?'neg':'pos' }}">{{ number_format($f->profit,2) }}</td><td class="num">{{ $f->margin!==null ? $f->margin.'%' : '—' }}</td></tr>@empty<tr><td colspan="7" class="rp-empty">No shipments.</td></tr>@endforelse</tbody>
        <tfoot><tr><td colspan="2">TOTAL</td><td class="num">{{ number_format($sum['tot']['billed'],2) }}</td><td class="num">{{ number_format($sum['tot']['received'],2) }}</td><td class="num">{{ number_format($sum['tot']['cost'],2) }}</td><td class="num">{{ number_format($sum['tot']['profit'],2) }}</td><td class="num">{{ $sum['tot']['margin'] ?? '—' }}</td></tr></tfoot></table></div></div>
</div>
@endsection
