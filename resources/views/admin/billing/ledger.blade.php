@extends('admin.layouts.master')

@section('main_content')
@include('admin.partials.pd-styles')

@php
    $f = $filters;
    $filtered = $f['from'] || $f['to'];
    $q = fn (array $x = []) => array_filter(array_merge(request()->query(), $x), fn ($v) => $v !== null && $v !== '' && $v !== []);
    $thisMonth = [now()->startOfMonth()->toDateString(), now()->toDateString()];
    $lastMonth = [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()];
    $thisYear  = [now()->startOfYear()->toDateString(), now()->toDateString()];
@endphp

<div class="rp">
    <div class="mb-2 no-print">
        <a href="{{ route('shipment-bills.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Shipment Billing</a>
    </div>

    <div class="rp-print-head"><b>Shipment Bill Ledger</b> &middot; {{ $filtered ? (($f['from'] ?: 'start') . ' to ' . ($f['to'] ?: 'today')) : 'All time' }} &middot; printed {{ now()->format('d M Y H:i') }}</div>

    <div class="rp-head">
        <div>
            <h1>Shipment Bill Ledger</h1>
            <p class="sub">Every cost bill and every payment we made, with a running balance of what we still owe.</p>
        </div>
        <div class="rp-actions no-print">
            <a class="rp-btn" href="{{ route('shipment-bills.index') }}"><i class="fa fa-arrow-left"></i> Billing</a>
            @can('add-bill-payment')<a class="rp-btn green" href="{{ route('shipment-bills.create') }}"><i class="fa fa-money"></i> Pay Bills</a>@endcan
            <a class="rp-btn" style="color:#0b7f72" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <button class="rp-btn" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>

    {{-- Filters --}}
    <form class="rp-card no-print" method="GET" action="{{ route('shipment-bills.ledger') }}">
        <div class="rp-filter">
            <div style="grid-column:span 2">
                <label>Shipment(s)</label>
                <select name="shipment_ids[]" id="shipSel" multiple>
                    @foreach($shipments as $s)
                        <option value="{{ $s->id }}" {{ in_array($s->id, $f['shipment_ids']) ? 'selected' : '' }}>{{ $s->shipment_name }}{{ $s->shipment_date ? ' (' . $s->shipment_date->format('d M Y') . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Agent / payee</label>
                <select name="agent_id"><option value="">All</option>
                    @foreach($agents as $a)<option value="{{ $a->id }}" {{ (int) $f['agent_id'] === $a->id ? 'selected' : '' }}>{{ $a->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label>Cost type</label>
                <select name="cost_type_id"><option value="">All</option>
                    @foreach($costTypes as $t)<option value="{{ $t->id }}" {{ (int) $f['cost_type_id'] === $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div><label>From</label><input type="date" name="from" value="{{ $f['from'] }}"></div>
            <div><label>To</label><input type="date" name="to" value="{{ $f['to'] }}"></div>
            <div style="display:flex;gap:8px">
                <button class="rp-btn primary" type="submit"><i class="fa fa-filter"></i> Apply</button>
                <a class="rp-btn" href="{{ route('shipment-bills.ledger') }}">Reset</a>
            </div>
        </div>
        <div class="rp-presets">
            <a href="{{ route('shipment-bills.ledger', $q(['from' => $thisMonth[0], 'to' => $thisMonth[1]])) }}">This month</a>
            <a href="{{ route('shipment-bills.ledger', $q(['from' => $lastMonth[0], 'to' => $lastMonth[1]])) }}">Last month</a>
            <a href="{{ route('shipment-bills.ledger', $q(['from' => $thisYear[0], 'to' => $thisYear[1]])) }}">This year</a>
        </div>
    </form>

    {{-- Period position --}}
    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Opening balance</div><div class="v">{{ number_format($led['opening'], 2) }}</div><div class="s">owed before the period</div></div>
        <div class="rp-kpi amber"><div class="l">Bills in period</div><div class="v">{{ number_format($led['bills'], 2) }}</div><div class="s">costs added</div></div>
        <div class="rp-kpi teal"><div class="l">Paid in period</div><div class="v pos">{{ number_format($led['paid'], 2) }}</div><div class="s">money out</div></div>
        <div class="rp-kpi coral"><div class="l">Closing balance (we owe)</div><div class="v {{ $led['closing'] > 0.004 ? 'neg' : 'pos' }}">{{ number_format($led['closing'], 2) }}</div><div class="s">end of period</div></div>
    </div>

    {{-- Entries --}}
    <div class="rp-card">
        <div class="ttl">Entries</div>
        <p class="hint">Bills raise the balance, payments reduce it. Bills come before payments on the same day.</p>
        <div class="rp-table-wrap">
        <table class="rp-table">
            <thead>
                <tr>
                    <th>Date</th><th>Document</th><th>Shipment</th><th>Agent / payee</th><th>Description</th><th>Method / Ref</th><th>Entered by</th>
                    <th class="num">Bill</th><th class="num">Payment</th><th class="num">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="grp"><td colspan="7">Opening balance{{ $f['from'] ? ' (before ' . $f['from'] . ')' : '' }}</td>
                    <td class="num"></td><td class="num"></td><td class="num">{{ number_format($led['opening'], 2) }}</td></tr>
                @forelse($led['entries'] as $e)
                    <tr>
                        <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($e['date'])->format('d M Y') }}</td>
                        <td>
                            @if($e['kind'] === 'payment' && $e['voucher_id'])
                                <a href="{{ route('shipment-bills.show', $e['voucher_id']) }}" class="rp-pill pill-ok" style="text-decoration:none">{{ $e['doc'] }}</a>
                            @else
                                <span class="rp-pill {{ $e['kind'] === 'bill' ? 'pill-warn' : 'pill-ok' }}">{{ $e['doc'] }}</span>
                            @endif
                        </td>
                        <td>@if($e['shipment_id'])<a class="lnk" href="{{ route('itemshipments.costs.show', $e['shipment_id']) }}">{{ $e['shipment'] }}</a>@endif</td>
                        <td>{{ $e['party'] ?? '—' }}</td>
                        <td>{{ $e['desc'] }}@if($e['detail'])<br><small class="muted">{{ $e['detail'] }}</small>@endif</td>
                        <td>{{ $e['method'] ? ($methods[$e['method']] ?? ucfirst($e['method'])) : '' }}@if($e['reference'])<br><small class="muted">{{ $e['reference'] }}</small>@endif</td>
                        <td>{{ $e['user'] ?? '' }}</td>
                        <td class="num">{{ $e['bill'] > 0 ? number_format($e['bill'], 2) : '' }}</td>
                        <td class="num pos">{{ $e['paid'] > 0 ? number_format($e['paid'], 2) : '' }}</td>
                        <td class="num"><strong class="{{ $e['balance'] > 0.004 ? 'neg' : 'pos' }}">{{ number_format($e['balance'], 2) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="rp-empty">No entries for this filter.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr><td colspan="7">Period total / closing balance</td>
                    <td class="num">{{ number_format($led['bills'], 2) }}</td><td class="num">{{ number_format($led['paid'], 2) }}</td><td class="num">{{ number_format($led['closing'], 2) }}</td></tr>
            </tfoot>
        </table>
        </div>
    </div>

    <div class="rp-grid2">
        <div class="rp-card">
            <div class="ttl">Position by shipment</div>
            <p class="hint">All bills of the filtered shipments, regardless of date.</p>
            <div class="rp-table-wrap"><table class="rp-table">
                <thead><tr><th>Shipment</th><th class="num">Bill</th><th class="num">Paid</th><th class="num">Due</th></tr></thead>
                <tbody>
                @forelse($led['by_shipment'] as $r)
                    <tr><td>{{ $r['name'] }} <small class="muted">{{ $r['lines'] }} line(s)</small></td>
                        <td class="num">{{ number_format($r['bill'], 2) }}</td><td class="num pos">{{ number_format($r['paid'], 2) }}</td>
                        <td class="num {{ $r['due'] > 0.004 ? 'neg' : '' }}"><strong>{{ number_format($r['due'], 2) }}</strong></td></tr>
                @empty <tr><td colspan="4" class="rp-empty">Nothing here.</td></tr> @endforelse
                </tbody>
                <tfoot><tr><td>TOTAL</td><td class="num">{{ number_format($led['total_bill'], 2) }}</td><td class="num">{{ number_format($led['total_paid'], 2) }}</td><td class="num">{{ number_format($led['total_due'], 2) }}</td></tr></tfoot>
            </table></div>
        </div>
        <div class="rp-card">
            <div class="ttl">Position by agent / payee</div>
            <p class="hint">Who we still have to pay.</p>
            <div class="rp-table-wrap"><table class="rp-table">
                <thead><tr><th>Agent</th><th class="num">Bill</th><th class="num">Paid</th><th class="num">Due</th></tr></thead>
                <tbody>
                @forelse($led['by_agent'] as $r)
                    <tr><td>{{ $r['name'] }} <small class="muted">{{ $r['lines'] }} line(s)</small></td>
                        <td class="num">{{ number_format($r['bill'], 2) }}</td><td class="num pos">{{ number_format($r['paid'], 2) }}</td>
                        <td class="num {{ $r['due'] > 0.004 ? 'neg' : '' }}"><strong>{{ number_format($r['due'], 2) }}</strong></td></tr>
                @empty <tr><td colspan="4" class="rp-empty">Nothing here.</td></tr> @endforelse
                </tbody>
                <tfoot><tr><td>TOTAL</td><td class="num">{{ number_format($led['total_bill'], 2) }}</td><td class="num">{{ number_format($led['total_paid'], 2) }}</td><td class="num">{{ number_format($led['total_due'], 2) }}</td></tr></tfoot>
            </table></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    if ($.fn.select2) { $('#shipSel').select2({ theme: 'bootstrap-5', placeholder: 'All shipments', width: '100%' }); }
});
</script>
@endsection
