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
            <a class="rp-btn" style="color:#0b7f72" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
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
                        <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($e['date'])->format('d M Y') }}</td>
                        <td><span class="rp-pill {{ $e['kind'] === 'bill' ? 'pill-info' : 'pill-ok' }}">{{ $e['doc'] }}</span></td>
                        <td>{{ $e['desc'] }}@if($e['detail'])<br><small class="muted">{{ $e['detail'] }}</small>@endif</td>
                        <td>{{ $methodLabel($e['method']) }}@if($e['reference'])<br><small class="muted">{{ $e['reference'] }}</small>@endif</td>
                        <td>{{ $e['user'] ?? '' }}</td>
                        <td class="num">{{ $e['debit'] > 0 ? number_format($e['debit'], 2) : '' }}</td>
                        <td class="num pos">{{ $e['credit'] > 0 ? number_format($e['credit'], 2) : '' }}</td>
                        <td class="num"><strong class="{{ $e['balance'] > 0.004 ? 'neg' : 'pos' }}">{{ number_format($e['balance'], 2) }}</strong></td>
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

{{-- Items popups --}}
@foreach($shipmentSummaries as $s)
    @php $sid = $s->shipment_id; $its = $itemsByShipment->get($sid, collect()); @endphp
    <div class="modal fade" id="itemsModal{{ $sid }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $s->shipment_name }} &mdash; {{ $customer->name }}
                        <br><small class="text-muted">Billed {{ number_format($s->billed, 2) }} &middot; Received {{ number_format($s->paid, 2) }} &middot; Due {{ number_format($s->due, 2) }}</small></h5>
                    <button type="button" class="btn btn-sm btn-light" onclick="closeModal('itemsModal{{ $sid }}')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light"><tr><th>#</th><th>Product</th><th>Receipt</th><th>Tracking</th><th>CTN</th>
                                <th class="text-end">Weight</th><th class="text-end">CBM</th><th class="text-end">Price</th><th>Status</th></tr></thead>
                            <tbody>
                            @foreach($its as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><strong>{{ $item->goods_name_en }}</strong>@if($item->goods_name_local)<br><small class="text-muted">{{ $item->goods_name_local }}</small>@endif</td>
                                    <td>{{ $item->receipt_no ?? '—' }}</td>
                                    <td>{{ $item->tracking_no ?? '—' }}</td>
                                    <td>{{ $item->printed_ctn_no ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($item->weight_kg, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->cbm, 3) }}</td>
                                    <td class="text-end">@if($item->hasPrice()){{ number_format($item->total_price, 2) }}@else<span class="text-danger">No price</span>@endif</td>
                                    <td><span class="rp-pill {{ $statusColors[$item->status] ?? 'pill-gray' }}">{{ ucwords(str_replace('_', ' ', $item->status ?: 'unknown')) }}</span></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    @can('add-payment')
                        @if($s->due > 0)
                            <a href="{{ route('payments.receive', ['customer' => $customer->id, 'shipment' => $sid]) }}" class="btn btn-primary">Receive Payment</a>
                        @endif
                    @endcan
                    <button type="button" class="btn btn-secondary" onclick="closeModal('itemsModal{{ $sid }}')">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
window.openModal = window.openModal || function (id) {
    var el = document.getElementById(id); if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) { window.bootstrap.Modal.getOrCreateInstance(el).show(); }
    else if (window.jQuery) { window.jQuery(el).modal('show'); }
};
window.closeModal = window.closeModal || function (id) {
    var el = document.getElementById(id); if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) { window.bootstrap.Modal.getOrCreateInstance(el).hide(); }
    else if (window.jQuery) { window.jQuery(el).modal('hide'); }
};
</script>
@endsection
