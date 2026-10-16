@extends('admin.layouts.master')

@section('main_content')
@include('admin.partials.pd-styles')

<div class="rp">
    <div class="rp-head">
        <div>
            <h1>Customer Ledger</h1>
            <p class="sub">What every customer owes us, built from shipment bills and received payments.</p>
        </div>
        <div class="rp-actions no-print">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <button class="rp-btn" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>

    @if(session('success'))<div class="rp-flash ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="rp-flash bad">{{ session('error') }}</div>@endif

    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Total billed</div><div class="v">{{ number_format($grandTotalBilled, 2) }}</div><div class="s">{{ $ledgerRows->count() }} customer(s) shown</div></div>
        <div class="rp-kpi teal"><div class="l">Total received</div><div class="v">{{ number_format($grandTotalPaid, 2) }}</div>
            <div class="s">{{ $grandTotalBilled > 0 ? round($grandTotalPaid / $grandTotalBilled * 100, 1) : 0 }}% collected</div></div>
        <div class="rp-kpi coral"><div class="l">Total outstanding</div><div class="v neg">{{ number_format($grandTotalDue, 2) }}</div>
            <div class="s">{{ $ledgerRows->where('status', 'due')->count() }} customer(s) with due</div></div>
    </div>

    <form class="rp-card no-print" method="GET">
        <div class="rp-filter">
            <div>
                <label>Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Name, code, phone, email">
            </div>
            <div>
                <label>Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="due" {{ ($status ?? '') === 'due' ? 'selected' : '' }}>With due</option>
                    <option value="clear" {{ ($status ?? '') === 'clear' ? 'selected' : '' }}>Clear</option>
                    <option value="no-billing" {{ ($status ?? '') === 'no-billing' ? 'selected' : '' }}>No billing</option>
                </select>
            </div>
            <div style="display:flex;gap:8px">
                <button class="rp-btn primary" type="submit"><i class="fa fa-filter"></i> Filter</button>
                <a class="rp-btn" href="{{ route('ledger.index') }}">Reset</a>
            </div>
        </div>
    </form>

    <div class="rp-card">
        <div class="rp-table-wrap">
        <table class="rp-table" id="ledgerTable">
            <thead>
                <tr>
                    <th>Customer</th><th>Phone</th><th class="num">Shipments</th>
                    <th class="num">Billed</th><th class="num">Received</th><th class="num">Due</th>
                    <th>Collected</th><th>Oldest due</th><th>Last payment</th><th>Status</th><th class="no-print">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ledgerRows as $row)
                    @php $pct = $row['total_billed'] > 0 ? min(100, round($row['total_paid'] / $row['total_billed'] * 100)) : 0; @endphp
                    <tr>
                        <td><a class="lnk" href="{{ route('ledger.customer', $row['customer']->id) }}">{{ $row['customer']->name }}</a>
                            <br><small class="muted">{{ $row['customer']->customer_code }}</small></td>
                        <td>{{ $row['customer']->phone }}</td>
                        <td class="num">{{ $row['shipment_count'] }}@if($row['shipments_due'])<br><small class="neg">{{ $row['shipments_due'] }} due</small>@endif</td>
                        <td class="num" data-order="{{ $row['total_billed'] }}">{{ number_format($row['total_billed'], 2) }}</td>
                        <td class="num pos" data-order="{{ $row['total_paid'] }}">{{ number_format($row['total_paid'], 2) }}</td>
                        <td class="num {{ $row['total_due'] > 0 ? 'neg' : '' }}" data-order="{{ $row['total_due'] }}"><strong>{{ number_format($row['total_due'], 2) }}</strong></td>
                        <td style="min-width:90px"><div class="rp-bar"><i style="width:{{ $pct }}%"></i></div><small class="muted">{{ $pct }}%</small></td>
                        <td>{{ $row['oldest_due_date'] ?? '—' }}</td>
                        <td>{{ $row['last_payment_date'] ? substr($row['last_payment_date'], 0, 10) : '—' }}</td>
                        <td>
                            @if($row['status'] === 'clear')<span class="rp-pill pill-ok">Clear</span>
                            @elseif($row['status'] === 'due')<span class="rp-pill pill-bad">Due</span>
                            @else<span class="rp-pill pill-gray">No billing</span>@endif
                        </td>
                        <td class="no-print" style="white-space:nowrap">
                            <a href="{{ route('ledger.customer', $row['customer']->id) }}" class="rp-btn sm"><i class="fa fa-book"></i> Ledger</a>
                            @can('add-payment')
                                @if($row['total_due'] > 0)
                                    <a href="{{ route('payments.receive', $row['customer']->id) }}" class="rp-btn sm green"><i class="fa fa-money"></i> Receive</a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">TOTAL</td>
                    <td class="num">{{ number_format($grandTotalBilled, 2) }}</td>
                    <td class="num">{{ number_format($grandTotalPaid, 2) }}</td>
                    <td class="num">{{ number_format($grandTotalDue, 2) }}</td>
                    <td colspan="5"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#ledgerTable').DataTable({
        order: [[5, 'desc']], pageLength: 50, lengthMenu: [25, 50, 100, 500],
        columnDefs: [{ orderable: false, targets: 10 }]
    });
});
</script>
@endsection
