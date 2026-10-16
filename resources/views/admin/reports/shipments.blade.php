@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="rp-head">
        <div><h1>Shipment Report</h1><p class="sub">Pick one or many shipments (or leave empty for all) and an optional shipment-date range.</p></div>
        <div class="rp-actions no-print">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export'=>1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <button class="rp-btn" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    @include('admin.partials.rp-nav')

    <form class="rp-card no-print" method="GET">
        <div class="rp-filter">
            <div style="grid-column:span 2"><label>Shipments</label>
                <select name="shipment_ids[]" id="shipSel" multiple style="width:100%">
                    @foreach($allShipments as $s)<option value="{{ $s->id }}" {{ in_array($s->id,$ids)?'selected':'' }}>{{ $s->shipment_name }}</option>@endforeach
                </select></div>
            <div><label>From</label><input type="date" name="from" value="{{ $from }}"></div>
            <div><label>To</label><input type="date" name="to" value="{{ $to }}"></div>
            <div style="display:flex;gap:8px"><button class="rp-btn primary"><i class="fa fa-filter"></i> Show</button><a class="rp-btn" href="{{ route('reports.shipments') }}">Reset</a></div>
        </div>
        @include('admin.partials.rp-dates')
    </form>

    <div class="rp-print-head"><b>Shipment Report</b><br>{{ $fin->count() }} shipment(s) {{ $from||$to ? '| '.($from?:'start').' to '.($to?:'today') : '' }}</div>

    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Shipments</div><div class="v">{{ $tot['count'] }}</div><div class="s">{{ number_format($tot['items']) }} items, {{ number_format($tot['weight'],2) }} kg</div></div>
        <div class="rp-kpi teal"><div class="l">Customer billed</div><div class="v">{{ number_format($tot['billed'],2) }}</div><div class="s">Received {{ number_format($tot['received'],2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Receivable</div><div class="v">{{ number_format($tot['receivable'],2) }}</div></div>
        <div class="rp-kpi amber"><div class="l">Shipment cost</div><div class="v">{{ number_format($tot['cost'],2) }}</div><div class="s">Paid {{ number_format($tot['cost_paid'],2) }} / Due {{ number_format($tot['cost_due'],2) }}</div></div>
        <div class="rp-kpi violet"><div class="l">Profit</div><div class="v {{ $tot['profit']<0?'neg':'pos' }}">{{ number_format($tot['profit'],2) }}</div><div class="s">Margin {{ $tot['margin'] ?? '—' }}%</div></div>
    </div>

    @if($tot['unassigned'] || $tot['unpriced'])
        <div class="rp-note">Data check: {{ (int)$tot['unassigned'] }} item(s) have no customer and {{ (int)$tot['unpriced'] }} have no price, so they are not in customer billing.</div>
    @endif

    <div class="rp-card">
        <div class="ttl">All shipments</div>
        <div class="rp-table-wrap"><table class="rp-table">
            <thead><tr><th>Shipment</th><th>Date</th><th>Status</th><th class="num">Items</th><th class="num">Kg</th><th class="num">Billed</th><th class="num">Received</th><th class="num">Receivable</th><th class="num">Cost</th><th class="num">Cost due</th><th class="num">Profit</th><th class="num">Margin</th></tr></thead>
            <tbody>
            @forelse($fin as $f)
                <tr><td><a class="lnk" href="{{ route('reports.shipments',['shipment_ids'=>[$f->id]]) }}">{{ $f->name }}</a></td><td>{{ $f->date }}</td><td>{{ ucfirst(str_replace('_',' ',$f->status)) }}</td>
                    <td class="num">{{ $f->items }}</td><td class="num">{{ number_format($f->weight,2) }}</td>
                    <td class="num">{{ number_format($f->billed,2) }}</td><td class="num pos">{{ number_format($f->received,2) }}</td><td class="num {{ $f->receivable>0?'neg':'' }}">{{ number_format($f->receivable,2) }}</td>
                    <td class="num">{{ number_format($f->cost,2) }}</td><td class="num {{ $f->cost_due>0?'neg':'' }}">{{ number_format($f->cost_due,2) }}</td>
                    <td class="num {{ $f->profit<0?'neg':'pos' }}"><strong>{{ number_format($f->profit,2) }}</strong></td><td class="num">{{ $f->margin !== null ? $f->margin.'%' : '—' }}</td></tr>
            @empty<tr><td colspan="12" class="rp-empty">No shipments found.</td></tr>@endforelse
            </tbody>
            <tfoot><tr><td colspan="3">TOTAL</td><td class="num">{{ $tot['items'] }}</td><td class="num">{{ number_format($tot['weight'],2) }}</td><td class="num">{{ number_format($tot['billed'],2) }}</td><td class="num">{{ number_format($tot['received'],2) }}</td><td class="num">{{ number_format($tot['receivable'],2) }}</td><td class="num">{{ number_format($tot['cost'],2) }}</td><td class="num">{{ number_format($tot['cost_due'],2) }}</td><td class="num">{{ number_format($tot['profit'],2) }}</td><td class="num">{{ $tot['margin'] ?? '—' }}</td></tr></tfoot>
        </table></div>
    </div>

    @if($matrix->count() && $fin->count() > 1 && $fin->count() <= 12)
    <div class="rp-card">
        <div class="ttl">Cost type x shipment (BDT)</div>
        <div class="rp-table-wrap"><table class="rp-table">
            <thead><tr><th>Cost type</th>@foreach($fin as $f)<th class="num">{{ $f->name }}</th>@endforeach<th class="num">Total</th></tr></thead>
            <tbody>@foreach($matrix as $type=>$m)<tr><td>{{ $type }}</td>@foreach($fin as $f)<td class="num">{{ number_format($m['by_ship'][$f->id] ?? 0,2) }}</td>@endforeach<td class="num"><strong>{{ number_format($m['total'],2) }}</strong></td></tr>@endforeach</tbody>
        </table></div>
    </div>
    @endif

    @foreach($fin->whereIn('id',$detailIds) as $f)
        <div class="rp-card" style="break-inside:auto">
            <div class="rp-cardhead"><div class="ttl">{{ $f->name }} <small class="muted">{{ $f->date }}</small></div>
                <div class="no-print">
                    @can('view-shipment-cost')<a class="rp-btn sm" href="{{ route('itemshipments.costs.show',$f->id) }}">Costs</a>@endcan
                </div></div>
            <div class="rp-grid2">
                <div>
                    <b>Cost lines</b>
                    <div class="rp-table-wrap"><table class="rp-table"><thead><tr><th>Type</th><th>Agent</th><th class="num">Cost</th><th class="num">Paid</th><th class="num">Due</th></tr></thead><tbody>
                    @php $cl = $costLines->get($f->id, collect()); @endphp
                    @forelse($cl as $c)<tr><td>{{ optional($c->costType)->name }}</td><td>{{ optional($c->agent)->name }}</td><td class="num">{{ number_format($c->final_bdt_cost ?: $c->amount,2) }}</td><td class="num pos">{{ number_format($c->paid_amount,2) }}</td><td class="num {{ $c->due_amount>0?'neg':'' }}">{{ number_format($c->due_amount,2) }}</td></tr>
                    @empty<tr><td colspan="5" class="rp-empty">No cost lines.</td></tr>@endforelse</tbody>
                    <tfoot><tr><td colspan="2">TOTAL</td><td class="num">{{ number_format($cl->sum(fn($c)=>$c->final_bdt_cost ?: $c->amount),2) }}</td><td class="num">{{ number_format($cl->sum('paid_amount'),2) }}</td><td class="num">{{ number_format($cl->sum('due_amount'),2) }}</td></tr></tfoot></table></div>
                </div>
                <div>
                    <b>Customers</b>
                    <div class="rp-table-wrap"><table class="rp-table"><thead><tr><th>Customer</th><th class="num">Items</th><th class="num">Billed</th><th class="num">Paid</th><th class="num">Due</th></tr></thead><tbody>
                    @php $cr = $custRows->get($f->id, collect()); @endphp
                    @forelse($cr as $r)<tr><td><a class="lnk" href="{{ route('ledger.customer',$r->customer_id) }}">{{ optional($r->customer)->name }}</a></td><td class="num">{{ $r->items }}</td><td class="num">{{ number_format($r->billed,2) }}</td><td class="num pos">{{ number_format($r->paid,2) }}</td><td class="num {{ $r->due>0?'neg':'' }}">{{ number_format($r->due,2) }}</td></tr>
                    @empty<tr><td colspan="5" class="rp-empty">No customer billing.</td></tr>@endforelse</tbody>
                    <tfoot><tr><td>TOTAL</td><td class="num">{{ $cr->sum('items') }}</td><td class="num">{{ number_format($cr->sum('billed'),2) }}</td><td class="num">{{ number_format($cr->sum('paid'),2) }}</td><td class="num">{{ number_format($cr->sum('due'),2) }}</td></tr></tfoot></table></div>
                </div>
            </div>
        </div>
    @endforeach
    @if($fin->count() > count($detailIds))<div class="rp-note">Detail is shown for the first {{ count($detailIds) }} shipments. Excel contains all of them.</div>@endif
</div>
<script>$(function(){ if ($.fn.select2) $('#shipSel').select2({placeholder:'All shipments'}); });</script>
@endsection
