@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="mb-2"><a href="{{ route('itemsshipments.show', $itemshipment->id) }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Shipment</a></div>
    <div class="rp-head"><div><h1>{{ $itemshipment->shipment_name }} <small class="text-muted">shipment ledger</small></h1>
        <p class="sub">What this shipment billed and collected, against what it cost and what we paid.</p></div>
        <div class="rp-actions">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <a class="rp-btn red" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
        </div></div>
    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Billed</div><div class="v">{{ number_format($sum['billed'],2) }}</div></div>
        <div class="rp-kpi teal"><div class="l">Collected (customers)</div><div class="v">{{ number_format($sum['collected'],2) }}</div><div class="s">receivable {{ number_format($sum['receivable'],2) }}</div></div>
        <div class="rp-kpi amber"><div class="l">Cost</div><div class="v">{{ number_format($sum['cost'],2) }}</div><div class="s">paid {{ number_format($sum['cost_paid'],2) }} · due {{ number_format($sum['cost_due'],2) }}</div></div>
        <div class="rp-kpi {{ $sum['profit'] >= 0 ? 'teal' : 'coral' }}"><div class="l">Profit</div><div class="v">{{ number_format($sum['profit'],2) }}</div></div>
    </div>
    <div class="rp-card"><div class="ttl">Receivable - who we billed</div><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr><th>Party</th><th>Type</th><th class="num">Items</th><th class="num">Weight</th><th class="num">Billed</th><th class="num">Received</th><th class="num">Due</th></tr></thead>
        <tbody>@forelse($recv as $r)<tr><td>{{ $r['party'] }}</td><td>@if($r['kind']==='Partner')<span class="badge" style="background:#6f42c1;color:#fff">Partner</span>@else Customer @endif</td>
            <td class="num">{{ $r['items'] }}</td><td class="num">{{ number_format($r['weight'],2) }}</td><td class="num">{{ number_format($r['billed'],2) }}</td>
            <td class="num">{{ $r['paid'] === null ? '—' : number_format($r['paid'],2) }}</td><td class="num">{{ $r['due'] === null ? '—' : number_format($r['due'],2) }}</td></tr>
            @empty<tr><td colspan="7" class="rp-empty">No billed items.</td></tr>@endforelse</tbody>
        <tfoot><tr><td colspan="4">TOTAL</td><td class="num">{{ number_format($sum['billed'],2) }}</td><td class="num">{{ number_format($sum['collected'],2) }}</td><td class="num">{{ number_format($sum['receivable'],2) }}</td></tr></tfoot>
    </table></div></div>
    <div class="rp-card"><div class="ttl">Customer payments received</div><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr><th>Date</th><th>Customer</th><th>Method</th><th>Reference</th><th class="num">Amount</th></tr></thead>
        <tbody>@forelse($receipts as $x)<tr><td>{{ $x->payment->payment_date->format('Y-m-d') }}</td><td>{{ optional($x->payment->customer)->name }}</td><td>{{ ucfirst(str_replace('_',' ',$x->payment->payment_method)) }}</td><td>{{ $x->payment->reference_no }}</td><td class="num">{{ number_format($x->amount,2) }}</td></tr>@empty<tr><td colspan="5" class="rp-empty">No payments yet.</td></tr>@endforelse</tbody>
        <tfoot><tr><td colspan="4">TOTAL</td><td class="num">{{ number_format($sum['collected'],2) }}</td></tr></tfoot></table></div></div>
    <div class="rp-card"><div class="ttl">Costs and our payments</div><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr><th>Date</th><th>Document</th><th>Description</th><th>Agent</th><th>Details</th><th class="num">Bill</th><th class="num">Paid</th><th class="num">Balance</th></tr></thead>
        <tbody>@forelse($led['entries'] as $e)<tr><td style="white-space:nowrap">{{ $e['date'] }}@if(!empty($e['time']))<br><small class="text-muted">{{ $e['time'] }}</small>@endif</td><td>{{ $e['doc'] }}</td><td>{{ $e['desc'] }}</td><td>{{ $e['party'] }}</td><td><small>{{ $e['detail'] }}</small></td>
            <td class="num">{{ $e['bill'] ? number_format($e['bill'],2) : '' }}</td><td class="num text-success">{{ $e['paid'] ? number_format($e['paid'],2) : '' }}</td><td class="num"><strong>{{ number_format($e['balance'],2) }}</strong></td></tr>
            @empty<tr><td colspan="8" class="rp-empty">No costs yet.</td></tr>@endforelse</tbody>
        <tfoot><tr><td colspan="5">SUMMARY</td><td class="num">{{ number_format($led['bills'],2) }}</td><td class="num">{{ number_format($led['paid'],2) }}</td><td class="num">{{ number_format($led['closing'],2) }}</td></tr></tfoot>
    </table></div></div>
</div>
@endsection
