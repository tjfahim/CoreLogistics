@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="mb-2 no-print">
        <a href="{{ route('reports.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports</a>
    </div>

    <div class="rp-head">
        <div><h1>{{ $group==='day' ? 'Day' : 'Month' }}-wise Report</h1><p class="sub">Billed by shipment date, cost by cost date, received and paid by payment date.</p></div>
        <div class="rp-actions no-print">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export'=>1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a> <a class="rp-btn red" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
            <button class="rp-btn" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    @include('admin.partials.rp-nav')
    <form class="rp-card no-print" method="GET">
        <div class="rp-filter">
            <div><label>From</label><input type="date" name="from" value="{{ $from }}"></div>
            <div><label>To</label><input type="date" name="to" value="{{ $to }}"></div>
            <div><label>Group by</label><select name="group"><option value="month" {{ $group==='month'?'selected':'' }}>Month</option><option value="day" {{ $group==='day'?'selected':'' }}>Day</option></select></div>
            <div style="display:flex;gap:8px"><button class="rp-btn primary"><i class="fa fa-filter"></i> Show</button><a class="rp-btn" href="{{ route('reports.period') }}">Reset</a></div>
        </div>
        @include('admin.partials.rp-dates')
    </form>
    @if($capped)<div class="rp-note">Range is longer than a year, so it was grouped by month.</div>@endif
    <div class="rp-print-head"><b>{{ $group==='day'?'Day':'Month' }}-wise Report</b><br>{{ $from }} to {{ $to }}</div>

    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Billed</div><div class="v">{{ number_format($rep['totals']['billed'],2) }}</div><div class="s">{{ $rep['totals']['shipments'] }} shipment(s)</div></div>
        <div class="rp-kpi teal"><div class="l">Received</div><div class="v">{{ number_format($rep['totals']['received'],2) }}</div></div>
        <div class="rp-kpi amber"><div class="l">Cost</div><div class="v">{{ number_format($rep['totals']['cost'],2) }}</div><div class="s">Paid out {{ number_format($rep['totals']['paid_out'],2) }}</div></div>
        <div class="rp-kpi violet"><div class="l">Profit</div><div class="v {{ $rep['totals']['profit']<0?'neg':'pos' }}">{{ number_format($rep['totals']['profit'],2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Net cash</div><div class="v {{ $rep['totals']['net']<0?'neg':'pos' }}">{{ number_format($rep['totals']['net'],2) }}</div></div>
    </div>

    <div class="rp-card"><canvas id="pc" height="90"></canvas></div>

    <div class="rp-card"><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr><th>{{ $group==='day'?'Date':'Month' }}</th><th class="num">Shipments</th><th class="num">Billed</th><th class="num">Received</th><th class="num">Cost</th><th class="num">Paid out</th><th class="num">Profit</th><th class="num">Net cash</th></tr></thead>
        <tbody>@forelse($rep['rows'] as $r)<tr><td>{{ $r['label'] }}</td><td class="num">{{ $r['shipments'] }}</td><td class="num">{{ number_format($r['billed'],2) }}</td><td class="num pos">{{ number_format($r['received'],2) }}</td><td class="num">{{ number_format($r['cost'],2) }}</td><td class="num">{{ number_format($r['paid_out'],2) }}</td><td class="num {{ $r['profit']<0?'neg':'pos' }}"><strong>{{ number_format($r['profit'],2) }}</strong></td><td class="num {{ $r['net']<0?'neg':'' }}">{{ number_format($r['net'],2) }}</td></tr>
        @empty<tr><td colspan="8" class="rp-empty">No data.</td></tr>@endforelse</tbody>
        <tfoot><tr><td>TOTAL</td><td class="num">{{ $rep['totals']['shipments'] }}</td>@foreach(['billed','received','cost','paid_out','profit','net'] as $k)<td class="num">{{ number_format($rep['totals'][$k],2) }}</td>@endforeach</tr></tfoot>
    </table></div></div>

    <div class="rp-card"><div class="ttl">Customer receipts ({{ $rep['receipts']->count() }})</div>
        <div class="rp-table-wrap"><table class="rp-table"><thead><tr><th>Date</th><th>Receipt</th><th>Customer</th><th>Method</th><th>Split</th><th>By</th><th class="num">Amount</th></tr></thead><tbody>
        @forelse($rep['receipts'] as $p)<tr><td>{{ $p->payment_date->format('d M Y') }}</td><td>{{ \App\Services\AccountingService::receiptNo($p->id) }}</td><td>{{ optional($p->customer)->name }}</td><td>{{ ucwords(str_replace('_',' ',$p->payment_method)) }}</td>
            <td><small>{{ $p->allocations->map(fn($a)=>(optional($a->itemshipment)->shipment_name ?? '#'.$a->itemshipment_id).': '.number_format($a->amount,2))->implode(' | ') }}</small></td><td>{{ optional($p->user)->name }}</td><td class="num pos">{{ number_format($p->amount,2) }}</td></tr>
        @empty<tr><td colspan="7" class="rp-empty">No receipts.</td></tr>@endforelse</tbody>
        <tfoot><tr><td colspan="6">TOTAL</td><td class="num">{{ number_format($rep['receipts']->sum('amount'),2) }}</td></tr></tfoot></table></div></div>

    <div class="rp-card"><div class="ttl">Payments to suppliers ({{ $rep['payouts']->count() }})</div>
        <div class="rp-table-wrap"><table class="rp-table"><thead><tr><th>Date</th><th>Voucher</th><th>Shipment</th><th>Cost type</th><th>Agent</th><th>Method</th><th>By</th><th class="num">Amount</th></tr></thead><tbody>
        @forelse($rep['payouts'] as $p)@php $c=$p->itemsShipmentCost; @endphp<tr><td>{{ $p->payment_date->format('d M Y') }}</td><td>{{ $p->voucher->voucher_no ?? 'Direct' }}</td><td>{{ optional(optional($c)->itemsShipment)->shipment_name }}</td><td>{{ optional(optional($c)->costType)->name }}</td><td>{{ optional(optional($c)->agent)->name }}</td><td>{{ ucwords(str_replace('_',' ',$p->payment_method)) }}</td><td>{{ optional($p->user)->name ?? $p->paid_by }}</td><td class="num">{{ number_format($p->amount,2) }}</td></tr>
        @empty<tr><td colspan="8" class="rp-empty">No payments.</td></tr>@endforelse</tbody>
        <tfoot><tr><td colspan="7">TOTAL</td><td class="num">{{ number_format($rep['payouts']->sum('amount'),2) }}</td></tr></tfoot></table></div></div>
</div>
@php $chartRows = $rep['rows']->map(function ($r) { return ['l' => $r['label'], 'b' => $r['billed'], 'r' => $r['received'], 'c' => $r['cost']]; })->values(); @endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){ var R=@json($chartRows);
 if(!window.Chart||!R.length) return;
 new Chart(document.getElementById('pc'),{type:'bar',data:{labels:R.map(x=>x.l),datasets:[
  {label:'Billed',data:R.map(x=>x.b),backgroundColor:'#2f5bea'},{label:'Received',data:R.map(x=>x.r),backgroundColor:'#0e9f8e'},{label:'Cost',data:R.map(x=>x.c),backgroundColor:'#f59e0b'}]},
  options:{responsive:true,plugins:{legend:{position:'bottom'}}}});})();
</script>
@endsection
