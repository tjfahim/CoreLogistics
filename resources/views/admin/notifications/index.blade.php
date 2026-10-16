@extends('admin.layouts.master')

@section('main_content')
@include('admin.partials.pd-styles')
<style>
    .nt-cell { text-align:center; min-width:104px; cursor:pointer; user-select:none; }
    .nt-cell .pill { display:inline-block; border-radius:10px; padding:5px 9px; font-size:12px; font-weight:800; min-width:84px; border:1px solid transparent; }
    .nt-cell.done .pill { background:#e3f6f3; color:#0b7f72; border-color:#bfe9e3; }
    .nt-cell.need .pill { background:#fdf1d9; color:#a96d00; border-color:#f4dca0; }
    .nt-cell.none .pill { background:#f4f6fb; color:#9aa6bd; }
    .nt-cell:hover .pill { filter:brightness(.96); box-shadow:0 1px 4px rgba(15,31,61,.12); }
    .nt-cell small { display:block; font-size:10.5px; color:var(--ink-2); margin-top:2px; line-height:1.25; }
    .nt-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 11px; border-radius:99px; background:#fff; border:1px solid var(--line);
               font-size:12.5px; font-weight:700; color:var(--ink); text-decoration:none; }
    .nt-chip b { background:#fdf1d9; color:#a96d00; border-radius:99px; padding:1px 8px; }
    .nt-chip.zero b { background:#e3f6f3; color:#0b7f72; }
    .nt-chip.active { border-color:var(--blue); box-shadow:0 0 0 2px #e8eefd; }
</style>

<div class="rp">
    <div class="rp-head">
        <div>
            <h1>Customer Notifications</h1>
            <p class="sub">Every receipt, every warehouse status. Tick a cell once you have told the customer.</p>
        </div>
        <div class="rp-actions no-print">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
        </div>
    </div>

    @if(session('success'))<div class="rp-flash ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rp-flash bad">{{ $errors->first() }}</div>@endif

    @if($withoutReceipt > 0)
        <div class="rp-note">{{ number_format($withoutReceipt) }} cargo item(s) have no Receipt_NO, so they cannot be tracked here. Fill the receipt number on those items.</div>
    @endif

    {{-- Waiting per status --}}
    <div class="rp-card">
        <div class="ttl">Waiting to be notified</div>
        <p class="hint">Receipts that already reached the status but the customer has not been told. Click a status to see them.</p>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @foreach($statuses as $k => $label)
                <a class="nt-chip {{ ($pending[$k] ?? 0) == 0 ? 'zero' : '' }} {{ request('status') === $k && request('notified') === 'no' ? 'active' : '' }}"
                   href="{{ route('notifications.index', ['status' => $k, 'notified' => 'no']) }}">
                    {{ $label }} <b data-pending="{{ $k }}">{{ $pending[$k] ?? 0 }}</b>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Filters --}}
    <form class="rp-card no-print" method="GET" action="{{ route('notifications.index') }}">
        <div class="rp-filter">
            <div>
                <label>Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Receipt no, shipping mark, customer, phone">
            </div>
            <div>
                <label>Status</label>
                <select name="status">
                    <option value="">All statuses</option>
                    @foreach($statuses as $k => $label)
                        <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Customer told?</label>
                <select name="notified">
                    <option value="">Any</option>
                    <option value="no"  {{ request('notified') === 'no'  ? 'selected' : '' }}>Not notified yet</option>
                    <option value="yes" {{ request('notified') === 'yes' ? 'selected' : '' }}>Already notified</option>
                </select>
            </div>
            <div>
                <label>Customer assigned?</label>
                <select name="assigned">
                    <option value="">Any</option>
                    <option value="no" {{ request('assigned') === 'no' ? 'selected' : '' }}>Not assigned yet</option>
                </select>
            </div>
            <div style="display:flex;gap:8px">
                <button class="rp-btn primary" type="submit"><i class="fa fa-filter"></i> Filter</button>
                <a class="rp-btn" href="{{ route('notifications.index') }}">Reset</a>
            </div>
        </div>
        <p class="muted" style="font-size:12px;margin-top:10px">"Not notified yet" and "Already notified" need a Status selected: they look at that one status.</p>
    </form>

    {{-- Matrix --}}
    <form class="rp-card" method="POST" action="{{ route('notifications.bulk') }}" id="bulkForm">
        @csrf
        <div class="rp-cardhead no-print">
            <div class="ttl">{{ number_format($receipts->total()) }} receipt(s)</div>
            @can('manage-notifications')
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                <span class="muted" style="font-size:12.5px">With selected:</span>
                <select name="status" class="form-select" style="width:auto;min-height:34px;border:1px solid var(--line);border-radius:8px;padding:4px 8px" required>
                    <option value="">Status...</option>
                    @foreach($statuses as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach
                </select>
                <button type="submit" name="is_notified" value="1" class="rp-btn sm green">Mark notified</button>
                <button type="submit" name="is_notified" value="0" class="rp-btn sm red">Mark NOT notified</button>
            </div>
            @endcan
        </div>

        <div class="rp-table-wrap">
        <table class="rp-table">
            <thead>
                <tr>
                    <th class="no-print" style="width:30px"><input type="checkbox" id="checkAll"></th>
                    <th>Receipt No</th>
                    <th>Customer</th>
                    <th class="num">Items</th>
                    @foreach($statuses as $k => $label)<th style="text-align:center">{{ $label }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
            @forelse($receipts as $r)
                @php
                    $people = $customers->get($r->receipt_no, collect())->unique('id');
                    $rowNotified = $notified->get($r->receipt_no, collect());
                @endphp
                <tr>
                    <td class="no-print"><input type="checkbox" name="receipt_nos[]" value="{{ $r->receipt_no }}" class="rowCheck"></td>
                    <td><strong>{{ $r->receipt_no }}</strong>
                        @if($r->shipping_mark)<br><small class="muted">{{ $r->shipping_mark }}</small>@endif</td>
                    <td>
                        @forelse($people as $p)
                            <div>{{ $p->name }} <small class="muted">({{ $p->customer_code }})</small>
                                @if($p->phone)<br><a class="lnk" style="font-size:12px" href="tel:{{ $p->phone }}">{{ $p->phone }}</a>@endif</div>
                        @empty
                            <span class="rp-pill pill-gray">Not assigned</span>
                        @endforelse
                        @if($people->count() && $r->unassigned > 0)<small class="muted">+{{ $r->unassigned }} item(s) unassigned</small>@endif
                    </td>
                    <td class="num">{{ $r->items }}<br><small class="muted">{{ number_format($r->weight, 1) }} kg</small></td>

                    @foreach($keys as $i => $k)
                        @php
                            $reached = 0;
                            foreach (array_slice($keys, $i) as $kk) { $reached += (int) $r->{'s_' . $kk}; }
                            $n    = $rowNotified->get($k);
                            $done = $n && $n->is_notified;
                            $cls  = $done ? 'done' : ($reached > 0 ? 'need' : 'none');
                        @endphp
                        <td class="nt-cell {{ $cls }}"
                            data-receipt="{{ $r->receipt_no }}" data-status="{{ $k }}"
                            data-reached="{{ $reached }}" data-total="{{ $r->items }}" data-done="{{ $done ? 1 : 0 }}">
                            <span class="pill">
                                @if($done) <i class="fa fa-check"></i> Notified
                                @elseif($reached > 0) <i class="fa fa-bell-o"></i> Notify ({{ $reached }}/{{ $r->items }})
                                @else &mdash; @endif
                            </span>
                            <small class="meta">@if($done){{ optional($n->notified_at)->format('d M, H:i') }} @if($n->user)&middot; {{ $n->user->name }}@endif @endif</small>
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ 4 + count($keys) }}" class="rp-empty">No receipts match this filter.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>

        <div class="no-print" style="margin-top:14px">{{ $receipts->links() }}</div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var canManage = {{ \Illuminate\Support\Facades\Gate::allows('manage-notifications') ? 'true' : 'false' }};
    var token = '{{ csrf_token() }}';
    var url = '{{ route('notifications.toggle') }}';

    var all = document.getElementById('checkAll');
    if (all) all.addEventListener('change', function () {
        document.querySelectorAll('.rowCheck').forEach(function (c) { c.checked = all.checked; });
    });

    function paint(cell, st) {
        var reached = parseInt(cell.dataset.reached, 10), total = cell.dataset.total;
        var pill = cell.querySelector('.pill'), meta = cell.querySelector('.meta');
        cell.classList.remove('done', 'need', 'none');
        if (st.is_notified) {
            cell.classList.add('done'); cell.dataset.done = 1;
            pill.innerHTML = '<i class="fa fa-check"></i> Notified';
            meta.textContent = (st.date || '') + (st.user ? ' · ' + st.user : '');
        } else {
            cell.dataset.done = 0; meta.textContent = '';
            if (reached > 0) { cell.classList.add('need'); pill.innerHTML = '<i class="fa fa-bell-o"></i> Notify (' + reached + '/' + total + ')'; }
            else { cell.classList.add('none'); pill.innerHTML = '&mdash;'; }
        }
    }

    document.querySelectorAll('.nt-cell').forEach(function (cell) {
        cell.addEventListener('click', function () {
            if (!canManage) return;
            var wasDone = cell.dataset.done === '1';
            if (!wasDone && parseInt(cell.dataset.reached, 10) === 0 &&
                !confirm('No item of this receipt has reached this status yet. Mark it as notified anyway?')) return;
            if (wasDone && !confirm('Mark as NOT notified?')) return;

            cell.style.opacity = .5;
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ receipt_no: cell.dataset.receipt, status: cell.dataset.status, is_notified: wasDone ? 0 : 1 })
            }).then(function (r) { return r.json(); }).then(function (d) {
                cell.style.opacity = 1;
                if (!d.ok) { alert(d.message || 'Could not save.'); return; }
                paint(cell, d);
                Object.keys(d.pending || {}).forEach(function (k) {
                    var b = document.querySelector('[data-pending="' + k + '"]');
                    if (b) { b.textContent = d.pending[k]; b.closest('.nt-chip').classList.toggle('zero', d.pending[k] === 0); }
                });
            }).catch(function () { cell.style.opacity = 1; alert('Network error. Nothing was saved.'); });
        });
    });
});
</script>
@endsection
