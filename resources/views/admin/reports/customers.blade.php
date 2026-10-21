@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="mb-2 no-print">
        <a href="{{ route('reports.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports</a>
    </div>

    <div class="rp-head">
        <div><h1>Customer Report</h1><p class="sub">Closing due = opening due + billed in period - received in period.</p></div>
        <div class="rp-actions no-print">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export'=>1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a> <a class="rp-btn red" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
            <button class="rp-btn" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    @include('admin.partials.rp-nav')
    <form class="rp-card no-print" method="GET">
        <div class="rp-filter">
            <div style="grid-column:span 2"><label>Customers</label><select name="customer_ids[]" id="custSel" multiple style="width:100%">
                @foreach($allCustomers as $c)<option value="{{ $c->id }}" {{ in_array($c->id,$ids)?'selected':'' }}>{{ $c->name }} {{ $c->customer_code ? '('.$c->customer_code.')' : '' }}</option>@endforeach</select></div>
            <div><label>From</label><input type="date" name="from" value="{{ $from }}"></div>
            <div><label>To</label><input type="date" name="to" value="{{ $to }}"></div>
            <div><label>Balance</label><select name="status"><option value="">All</option><option value="due" {{ $status==='due'?'selected':'' }}>With due</option><option value="clear" {{ $status==='clear'?'selected':'' }}>Clear</option></select></div>
            <div style="display:flex;gap:8px"><button class="rp-btn primary"><i class="fa fa-filter"></i> Show</button><a class="rp-btn" href="{{ route('reports.customers') }}">Reset</a></div>
        </div>
        @include('admin.partials.rp-dates')
    </form>
    <div class="rp-print-head"><b>Customer Report</b><br>{{ $from ?: 'start' }} to {{ $to ?: 'today' }}</div>
    <div class="rp-kpis">
        <div class="rp-kpi amber"><div class="l">Opening due</div><div class="v">{{ number_format($rep['totals']['opening'],2) }}</div></div>
        <div class="rp-kpi"><div class="l">Billed</div><div class="v">{{ number_format($rep['totals']['billed'],2) }}</div></div>
        <div class="rp-kpi teal"><div class="l">Received</div><div class="v">{{ number_format($rep['totals']['received'],2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Closing due</div><div class="v neg">{{ number_format($rep['totals']['closing'],2) }}</div><div class="s">{{ $rep['rows']->count() }} customer(s)</div></div>
    </div>
    <div class="rp-card"><div class="rp-table-wrap"><table class="rp-table" id="ct">
        <thead><tr><th>Customer</th><th>Phone</th><th class="num">Shipments</th><th class="num">Items</th><th class="num">Kg</th><th class="num">Opening</th><th class="num">Billed</th><th class="num">Received</th><th class="num">Closing due</th><th>Last payment</th><th class="no-print"></th></tr></thead>
        <tbody>@foreach($rep['rows'] as $c)<tr><td><a class="lnk" href="{{ route('ledger.customer',[$c->customer_id,'from'=>$from,'to'=>$to]) }}">{{ $c->name }}</a><br><small class="muted">{{ $c->code }}</small></td><td>{{ $c->phone }}</td>
            <td class="num">{{ $c->shipments }}</td><td class="num">{{ $c->items }}</td><td class="num">{{ number_format($c->weight,2) }}</td>
            <td class="num" data-order="{{ $c->opening }}">{{ number_format($c->opening,2) }}</td><td class="num" data-order="{{ $c->billed }}">{{ number_format($c->billed,2) }}</td><td class="num pos" data-order="{{ $c->received }}">{{ number_format($c->received,2) }}</td>
            <td class="num {{ $c->closing>0?'neg':'' }}" data-order="{{ $c->closing }}"><strong>{{ number_format($c->closing,2) }}</strong></td><td>{{ $c->last_payment ? substr($c->last_payment,0,10) : '—' }}</td>
            <td class="no-print"><a class="rp-btn sm" href="{{ route('ledger.customer',[$c->customer_id,'from'=>$from,'to'=>$to]) }}">Ledger</a></td></tr>@endforeach</tbody>
        <tfoot><tr><td colspan="2">TOTAL</td><td class="num">{{ $rep['totals']['shipments'] }}</td><td class="num">{{ $rep['totals']['items'] }}</td><td class="num">{{ number_format($rep['totals']['weight'],2) }}</td>
            <td class="num">{{ number_format($rep['totals']['opening'],2) }}</td><td class="num">{{ number_format($rep['totals']['billed'],2) }}</td><td class="num">{{ number_format($rep['totals']['received'],2) }}</td><td class="num">{{ number_format($rep['totals']['closing'],2) }}</td><td colspan="2"></td></tr></tfoot>
    </table></div></div>
</div>
<script>$(function(){ if ($.fn.select2) $('#custSel').select2({placeholder:'All customers'});
 if ($.fn.DataTable) $('#ct').DataTable({order:[[8,'desc']],pageLength:50,columnDefs:[{orderable:false,targets:10}]}); });</script>
@endsection
