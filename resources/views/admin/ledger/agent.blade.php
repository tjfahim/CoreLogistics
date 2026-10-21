@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="mb-2"><a href="{{ route('ledger.agents') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Agents</a></div>
    <div class="rp-head"><div><h1>{{ $agent->name }} <small class="text-muted">agent ledger</small></h1>
        <p class="sub">Every bill and payment, date-wise, with the running amount we owe.</p></div>
        <div class="rp-actions">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <a class="rp-btn red" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
        </div></div>
    <form class="rp-card" method="GET"><div class="rp-filter">
        <div><label>From</label><input type="date" name="from" value="{{ $from }}"></div>
        <div><label>To</label><input type="date" name="to" value="{{ $to }}"></div>
        <div style="display:flex;gap:8px"><button class="rp-btn primary"><i class="fa fa-filter"></i> Show</button><a class="rp-btn" href="{{ route('ledger.agent', $agent->id) }}">Reset</a></div>
    </div></form>
    <div class="rp-card"><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr><th>Date</th><th>Document</th><th>Shipment</th><th>Description</th><th>Details</th><th class="num">Bill (we owe)</th><th class="num">Paid</th><th class="num">Balance (due)</th></tr></thead>
        <tbody>
            <tr><td colspan="7"><em>Opening balance</em></td><td class="num"><strong>{{ number_format($led['opening'],2) }}</strong></td></tr>
            @forelse($led['entries'] as $e)
            <tr><td>{{ $e['date'] }}</td><td>{{ $e['doc'] }}</td>
                <td>@if($e['shipment_id'])<a href="{{ route('ledger.shipment', $e['shipment_id']) }}">{{ $e['shipment'] }}</a>@endif</td>
                <td>{{ $e['desc'] }}</td><td><small>{{ $e['detail'] }} {{ $e['method'] ? '· '.ucfirst(str_replace('_',' ',$e['method'])) : '' }}</small></td>
                <td class="num">{{ $e['bill'] ? number_format($e['bill'],2) : '' }}</td>
                <td class="num text-success">{{ $e['paid'] ? number_format($e['paid'],2) : '' }}</td>
                <td class="num"><strong>{{ number_format($e['balance'],2) }}</strong></td></tr>
            @empty<tr><td colspan="8" class="rp-empty">No entries.</td></tr>@endforelse
        </tbody>
        <tfoot><tr><td colspan="5">SUMMARY</td><td class="num">{{ number_format($led['bills'],2) }}</td><td class="num">{{ number_format($led['paid'],2) }}</td><td class="num">{{ number_format($led['closing'],2) }}</td></tr></tfoot>
    </table></div></div>
    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Bills</div><div class="v">{{ number_format($led['bills'],2) }}</div></div>
        <div class="rp-kpi teal"><div class="l">Paid</div><div class="v">{{ number_format($led['paid'],2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Due to agent</div><div class="v">{{ number_format($led['closing'],2) }}</div></div>
    </div>
</div>
@endsection
