@extends('admin.layouts.master')

@section('main_content')
@include('admin.partials.pd-styles')

@php
    $statusColors = ['pending'=>'pill-gray','packed'=>'pill-info','ready_to_ship'=>'pill-info','shipping_ongoing'=>'pill-info','arrived_bd'=>'pill-warn','waiting_for_customs'=>'pill-warn','ready_to_deliver'=>'pill-warn','delivered'=>'pill-ok'];
    $billPill = ['paid'=>'pill-ok','partial'=>'pill-warn','due'=>'pill-bad','no-billing'=>'pill-gray'];
    $billLabel = ['paid'=>'Paid','partial'=>'Partial','due'=>'Due','no-billing'=>'No billing'];
    $methodLabel = fn ($m) => $m ? ucwords(str_replace('_', ' ', $m)) : '';
    $filtered = $from || $to;
    $thisMonth = [now()->startOfMonth()->toDateString(), now()->toDateString()];
    $lastMonth = [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()];
    $thisYear  = [now()->startOfYear()->toDateString(), now()->toDateString()];
@endphp

<div class="rp">
    <div class="mb-2 no-print">
        <a href="{{ route('ledger.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Customer Ledger</a>
    </div>

    <div class="rp-print-head"><b>Statement of Account</b> &middot; {{ $customer->name }} ({{ $customer->customer_code }})
        &middot; {{ $filtered ? (($from ?: 'start') . ' to ' . ($to ?: 'today')) : 'All time' }} &middot; printed {{ now()->format('d M Y H:i') }}</div>

    <div class="rp-head">
        <div>
            <h1>{{ $customer->name }} <span class="rp-pill pill-info" style="vertical-align:middle">{{ $customer->customer_code }}</span></h1>
            <p class="sub">
                {{ $customer->phone }}@if($customer->email) &middot; {{ $customer->email }}@endif
                @if($customer->address) &middot; {{ $customer->address }}@endif
            </p>
            @if(count($customer->all_shipping_marks) > 0)
                <p style="margin-top:6px">@foreach($customer->all_shipping_marks as $mark)<span class="rp-pill pill-gray">{{ $mark }}</span> @endforeach</p>
            @endif
        </div>
        <div class="rp-actions no-print">
            <a class="rp-btn" href="{{ route('ledger.index') }}"><i class="fa fa-arrow-left"></i> All customers</a>
            @can('add-payment')
                @if($totals['due'] > 0)
                    <a href="{{ route('payments.receive', $customer->id) }}" class="rp-btn green"><i class="fa fa-money"></i> Receive Payment</a>
                @endif
            @endcan
            <a class="rp-btn" style="color:#0b7f72" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a> <a class="rp-btn" style="color:#c0392b" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
            <button class="rp-btn" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
            <a href="{{ route('customers.edit', $customer->id) }}" class="rp-btn"><i class="fa fa-edit"></i> Edit</a>
        </div>
    </div>

    @if(session('success'))<div class="rp-flash ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="rp-flash bad">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="rp-flash bad">{{ $errors->first() }}</div>@endif

    {{-- Position --}}
    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Total billed</div><div class="v">{{ number_format($totals['billed'], 2) }}</div><div class="s">{{ $totals['shipments'] }} shipment(s)</div></div>
        <div class="rp-kpi teal"><div class="l">Total received</div><div class="v pos">{{ number_format($totals['paid'], 2) }}</div><div class="s">{{ $payments->count() }} receipt(s)</div></div>
        <div class="rp-kpi coral"><div class="l">Outstanding due</div><div class="v {{ $totals['due'] > 0 ? 'neg' : 'pos' }}">{{ number_format($totals['due'], 2) }}</div>
            <div class="s">{{ $totals['shipments_due'] }} shipment(s) with due</div></div>
        @if(($st['advance'] ?? 0) > 0)
        <div class="rp-kpi teal"><div class="l">Advance (credit)</div><div class="v pos">{{ number_format($st['advance'], 2) }}</div><div class="s">received, not yet applied</div></div>
        @endif
    </div>

    {{-- Date filter --}}
    <form class="rp-card no-print" method="GET">
        <div class="rp-filter">
            <div><label>From</label><input type="date" name="from" value="{{ $from }}"></div>
            <div><label>To</label><input type="date" name="to" value="{{ $to }}"></div>
            <div style="display:flex;gap:8px">
                <button class="rp-btn primary" type="submit"><i class="fa fa-filter"></i> Apply</button>
                <a class="rp-btn" href="{{ route('ledger.customer', $customer->id) }}">All time</a>
            </div>
        </div>
        <div class="rp-presets">
            <a href="{{ route('ledger.customer', [$customer->id, 'from' => $thisMonth[0], 'to' => $thisMonth[1]]) }}">This month</a>
            <a href="{{ route('ledger.customer', [$customer->id, 'from' => $lastMonth[0], 'to' => $lastMonth[1]]) }}">Last month</a>
            <a href="{{ route('ledger.customer', [$customer->id, 'from' => $thisYear[0], 'to' => $thisYear[1]]) }}">This year</a>
        </div>
    </form>

    {{-- Statement --}}
    <div class="rp-card">
        <div class="rp-cardhead">
            <div><div class="ttl">Statement of account</div>
                <p class="hint" style="margin:0">Balance = what the customer still owes us. Bills come before receipts on the same day.</p></div>
            @if($filtered)<span class="rp-pill pill-info">{{ $from ?: 'start' }} &rarr; {{ $to ?: 'today' }}</span>@endif
        </div>
        <div class="rp-table-wrap">
        <table class="rp-table">
            <thead>
                <tr>
                    <th>Date</th><th>Document</th><th>Description</th><th>Method / Ref</th><th>Entered by</th>
                    <th class="num">Debit (billed)</th><th class="num">Credit (received)</th><th class="num">Balance</th><th class="no-print"></th>
                </tr>
            </thead>
            <tbody>
                <tr class="grp"><td colspan="5">Opening balance{{ $from ? ' (before ' . $from . ')' : '' }}</td>
                    <td class="num"></td><td class="num"></td><td class="num">{{ number_format($st['opening'], 2) }}</td><td class="no-print"></td></tr>
                @forelse($st['entries'] as $e)
                    <tr>
                        <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($e['date'])->format('d M Y') }}@if(!empty($e['time']))<br><small class="muted">{{ $e['time'] }}</small>@endif</td>
                        <td><span class="rp-pill {{ $e['kind'] === 'bill' ? 'pill-info' : 'pill-ok' }}">{{ $e['doc'] }}</span></td>
                        <td>{{ $e['desc'] }}@if($e['detail'])<br><small class="muted">{{ $e['detail'] }}</small>@endif @if(!empty($e['cur']))<br><small class="muted">Paid in {{ $e['cur'] }}</small>@endif</td>
                        <td>{{ $methodLabel($e['method']) }}@if($e['reference'])<br><small class="muted">{{ $e['reference'] }}</small>@endif</td>
                        <td>{{ $e['user'] ?? '' }}</td>
                        <td class="num">{{ $e['debit'] > 0 ? number_format($e['debit'], 2) : '' }}</td>
                        <td class="num pos">{{ $e['credit'] > 0 ? number_format($e['credit'], 2) : '' }}</td>
                        <td class="num"><strong class="{{ $e['balance'] > 0.004 ? 'neg' : 'pos' }}">{{ number_format(abs($e['balance']), 2) }}{{ $e['balance'] < -0.004 ? ' Adv' : '' }}</strong></td>
                        <td class="no-print">
                            @if($e['kind'] === 'receipt')
                                @can('delete-payment')
                                    <form action="{{ route('payments.destroy', $e['payment_id']) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete receipt {{ $e['doc'] }} of {{ number_format($e['credit'], 2) }}? Shipment dues will go back up.');">
                                        @csrf @method('DELETE')
                                        <button class="rp-btn sm red" title="Delete receipt"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="rp-empty">No entries in this period.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">Period total / closing balance</td>
                    <td class="num">{{ number_format($st['debit'], 2) }}</td>
                    <td class="num">{{ number_format($st['credit'], 2) }}</td>
                    <td class="num">{{ number_format($st['closing'], 2) }}</td>
                    <td class="no-print"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>

    {{-- Per shipment --}}
    <div class="rp-card">
        <div class="ttl">Shipment-wise position</div>
        <p class="hint">Receipts are matched to shipments, so you can see exactly which shipment is still open.</p>
        <div class="rp-table-wrap">
        <table class="rp-table">
            <thead>
                <tr><th>Shipment</th><th>Date</th><th>Items</th><th class="num">Weight (kg)</th>
                    <th class="num">Billed</th><th class="num">Received</th><th class="num">Due</th><th>Status</th><th class="no-print">Action</th></tr>
            </thead>
            <tbody>
                @forelse($shipmentSummaries as $s)
                    <tr class="{{ $s->due > 0 ? 'open' : '' }}">
                        <td><a class="lnk" href="{{ route('itemsshipments.show', $s->shipment_id) }}">{{ $s->shipment_name }}</a></td>
                        <td>{{ $s->date }}</td>
                        <td>{{ $s->items }}@if($s->items > $s->priced_items) <small class="neg">({{ $s->items - $s->priced_items }} no price)</small>@endif</td>
                        <td class="num">{{ number_format($s->weight, 2) }}</td>
                        <td class="num">{{ number_format($s->billed, 2) }}</td>
                        <td class="num pos">{{ number_format($s->paid, 2) }}</td>
                        <td class="num {{ $s->due > 0 ? 'neg' : '' }}"><strong>{{ number_format($s->due, 2) }}</strong></td>
                        <td><span class="rp-pill {{ $billPill[$s->status] }}">{{ $billLabel[$s->status] }}</span></td>
                        <td class="no-print" style="white-space:nowrap">
                            <button type="button" class="rp-btn sm" onclick="openModal('itemsModal{{ $s->shipment_id }}')"><i class="fa fa-list"></i> Items</button>
                            @can('add-payment')
                                @if($s->due > 0)
                                    <a href="{{ route('payments.receive', ['customer' => $customer->id, 'shipment' => $s->shipment_id]) }}" class="rp-btn sm green">Receive</a>
                                @endif
                            @endcan
                            @can('view-invoices')
                                <a href="{{ route('customers.shipment.bill', ['customer' => $customer->id, 'itemshipment' => $s->shipment_id]) }}" class="rp-btn sm">Invoice</a>
                            @endcan
                            @can('view-payment-history')
                                <a href="{{ route('payments.history', ['customer' => $customer->id, 'itemshipment' => $s->shipment_id]) }}" class="rp-btn sm">History</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="rp-empty">No shipments yet.</td></tr>
                @endforelse
            </tbody>
            @if($shipmentSummaries->count())
            <tfoot>
                <tr><td colspan="4">TOTAL</td><td class="num">{{ number_format($totals['billed'], 2) }}</td>
                    <td class="num">{{ number_format($totals['paid'], 2) }}</td><td class="num">{{ number_format($totals['due'], 2) }}</td><td colspan="2" class="no-print"></td></tr>
            </tfoot>
            @endif
        </table>
        </div>
    </div>
</div>

{{-- Items popups (self-contained: does not depend on the Bootstrap modal plugin) --}}
<style>
    .lm-overlay { display:none; position:fixed; inset:0; z-index:200000; background:rgba(15,31,61,.55); overflow-y:auto; padding:30px 14px; }
    .lm-overlay.open { display:block; }
    .lm-box { background:#fff; border-radius:14px; max-width:1100px; margin:0 auto; box-shadow:0 20px 60px rgba(0,0,0,.35); overflow:hidden; }
    .lm-head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; padding:16px 20px; border-bottom:1px solid #e6ebf3; background:#f7f9fd; }
    .lm-head h5 { margin:0; font-size:17px; font-weight:800; color:#0f1f3d; }
    .lm-head small { color:#5b6b88; font-weight:600; }
    .lm-x { border:0; background:#e8ecf5; width:32px; height:32px; border-radius:8px; font-size:20px; line-height:1; cursor:pointer; color:#0f1f3d; }
    .lm-x:hover { background:#d6dcec; }
    .lm-body { padding:16px 20px; overflow-x:auto; }
    .lm-body table { width:100%; border-collapse:collapse; font-size:13px; }
    .lm-body th { background:#f4f6fb; padding:8px 10px; text-align:left; font-size:11.5px; text-transform:uppercase; color:#5b6b88; border-bottom:2px solid #e1e6f1; white-space:nowrap; }
    .lm-body td { padding:8px 10px; border-bottom:1px solid #eef1f7; vertical-align:top; }
    .lm-body .r { text-align:right; white-space:nowrap; }
    .lm-foot { display:flex; justify-content:flex-end; gap:8px; padding:12px 20px; border-top:1px solid #e6ebf3; background:#f7f9fd; }
    body.lm-lock { overflow:hidden; }
</style>

@foreach($shipmentSummaries as $s)
    @php
        $sid = $s->shipment_id;
        $its = $itemsByShipment->get($sid, collect());
        $wSum = $its->sum('billing_weight'); $cSum = $its->sum('cbm');
    @endphp
    <div class="lm-overlay no-print" id="itemsModal{{ $sid }}" data-lm="1">
        <div class="lm-box" role="dialog" aria-modal="true">
            <div class="lm-head">
                <h5>{{ $s->shipment_name }} &mdash; {{ $customer->name }}
                    <br><small>{{ $its->count() }} item(s) &middot; Billed {{ number_format($s->billed, 2) }} &middot; Received {{ number_format($s->paid, 2) }} &middot; Due {{ number_format($s->due, 2) }}</small></h5>
                <button type="button" class="lm-x" data-lm-close="1" aria-label="Close">&times;</button>
            </div>
            <div class="lm-body">
                <table>
                    <thead><tr><th>#</th><th>Product</th><th>Receipt</th><th>Tracking</th><th>CTN</th>
                        <th class="r">Weight</th><th class="r">CBM</th><th class="r">Price</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($its as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $item->goods_name_en }}</strong>@if($item->goods_name_local)<br><small class="muted">{{ $item->goods_name_local }}</small>@endif</td>
                            <td>{{ $item->receipt_no ?: '—' }}</td>
                            <td>{{ $item->tracking_no ?: '—' }}</td>
                            <td>{{ $item->printed_ctn_no ?: '—' }}</td>
                            <td class="r">{{ number_format($item->billing_weight, 2) }}</td>
                            <td class="r">{{ number_format($item->cbm, 3) }}</td>
                            <td class="r">@if($item->hasPrice()){{ number_format($item->total_price, 2) }}@else<span class="neg">No price</span>@endif</td>
                            <td><span class="rp-pill {{ $statusColors[$item->status] ?? 'pill-gray' }}">{{ ucwords(str_replace('_', ' ', $item->status ?: 'unknown')) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="rp-empty">No items found for this customer in this shipment.</td></tr>
                    @endforelse
                    </tbody>
                    @if($its->count())
                    <tfoot><tr><td colspan="5" style="font-weight:800">TOTAL</td><td class="r"><strong>{{ number_format($wSum, 2) }}</strong></td>
                        <td class="r"><strong>{{ number_format($cSum, 3) }}</strong></td><td class="r"><strong>{{ number_format($s->billed, 2) }}</strong></td><td></td></tr></tfoot>
                    @endif
                </table>
            </div>
            <div class="lm-foot">
                @can('add-payment')
                    @if($s->due > 0)
                        <a href="{{ route('payments.receive', ['customer' => $customer->id, 'shipment' => $sid]) }}" class="rp-btn green">Receive Payment</a>
                    @endif
                @endcan
                <button type="button" class="rp-btn" data-lm-close="1">Close</button>
            </div>
        </div>
    </div>
@endforeach

<script>
(function () {
    // move popups to <body> so no parent (transform / overflow / z-index) can hide them
    document.querySelectorAll('.lm-overlay[data-lm]').forEach(function (el) { document.body.appendChild(el); });

    window.openModal = function (id) {
        var el = document.getElementById(id); if (!el) return;
        el.classList.add('open'); document.body.classList.add('lm-lock');
    };
    window.closeModal = function (id) {
        var el = id ? document.getElementById(id) : document.querySelector('.lm-overlay.open');
        if (el) el.classList.remove('open');
        if (!document.querySelector('.lm-overlay.open')) document.body.classList.remove('lm-lock');
    };
    document.addEventListener('click', function (e) {
        var ov = e.target.closest('.lm-overlay');
        if (!ov) return;
        if (e.target === ov || e.target.closest('[data-lm-close]')) { closeModal(ov.id); }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
})();
</script>
@endsection