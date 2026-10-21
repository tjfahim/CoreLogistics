@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="rp-head"><div><h1>Agent Ledger</h1><p class="sub">What we owe each agent (cost lines) versus what we have paid.</p></div>
        <div class="rp-actions">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <a class="rp-btn red" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
        </div></div>
    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Total bills</div><div class="v">{{ number_format($rows->sum('bill'),2) }}</div></div>
        <div class="rp-kpi teal"><div class="l">Paid</div><div class="v">{{ number_format($rows->sum('paid'),2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">We still owe</div><div class="v">{{ number_format($rows->sum('due'),2) }}</div></div>
    </div>
    <div class="rp-card"><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr><th>Agent</th><th>Company</th><th class="num">Cost lines</th><th class="num">Total bill</th><th class="num">Paid</th><th class="num">Due</th><th></th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr><td><strong>{{ $r['agent']->name }}</strong></td><td>{{ $r['agent']->company_name }}</td><td class="num">{{ $r['lines'] }}</td>
                <td class="num">{{ number_format($r['bill'],2) }}</td><td class="num">{{ number_format($r['paid'],2) }}</td>
                <td class="num"><strong class="{{ $r['due'] > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($r['due'],2) }}</strong></td>
                <td><a class="rp-btn sm" href="{{ route('ledger.agent', $r['agent']->id) }}"><i class="fa fa-book"></i> Ledger</a></td></tr>
        @empty<tr><td colspan="7" class="rp-empty">No agents.</td></tr>@endforelse
        </tbody></table></div></div>
</div>
@endsection
