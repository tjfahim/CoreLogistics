@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Review: {{ $upload->title }}
            <span class="badge badge-{{ ['staged'=>'warning','approved'=>'info','imported'=>'success'][$upload->status] }}">{{ ucfirst($upload->status) }}</span>
        </h1>
        <a href="{{ route('legacy.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    @foreach (['success','error'] as $m)
        @if(session($m)) <div class="alert alert-{{ $m === 'error' ? 'danger' : 'success' }}">{{ session($m) }}</div> @endif
    @endforeach

    {{-- 1. Check totals against the Excel --}}
    <div class="card shadow mb-4">
        <div class="card-header"><strong>1. Check totals against the Excel</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="thead-light"><tr><th>Check</th><th class="text-right">Stored in DB</th><th class="text-right">Excel says</th><th></th></tr></thead>
                <tbody>
                @foreach($checks as $c)
                    <tr>
                        <td>{{ $c['label'] }}</td>
                        <td class="text-right">{{ number_format($c['ours'], 2) }}</td>
                        <td class="text-right">{{ $c['excel'] === null ? '-' : number_format($c['excel'], 2) }}</td>
                        <td class="text-center">
                            @if($c['ok'] === null) <span class="text-muted">n/a</span>
                            @elseif($c['ok']) <span class="text-success">&#10003; match</span>
                            @else <span class="text-danger font-weight-bold">&#10007; different</span> @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Rows --}}
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <strong>2. Check the rows ({{ $upload->total_rows }})</strong>
            <form class="form-inline" method="GET">
                <select name="filter" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="all"      {{ $filter==='all' ? 'selected' : '' }}>All rows</option>
                    <option value="errors"   {{ $filter==='errors' ? 'selected' : '' }}>Errors only ({{ $upload->error_rows }})</option>
                    <option value="warnings" {{ $filter==='warnings' ? 'selected' : '' }}>Warnings only</option>
                    <option value="flagged"  {{ $filter==='flagged' ? 'selected' : '' }}>Flagged in Excel</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm mr-2" placeholder="search">
                <button class="btn btn-sm btn-primary">Go</button>
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-bordered mb-0" style="font-size:12px">
                <thead class="thead-light">
                <tr>
                    <th>Old row</th><th>Date</th><th>Customer</th><th>Shipment</th><th>Mark</th><th>Tracking</th><th>Goods</th>
                    <th class="text-right">CTN</th><th class="text-right">Kg</th><th class="text-right">Rate</th>
                    <th class="text-right">Price</th><th class="text-right">Paid</th><th class="text-right">Balance</th>
                    <th>Receiver</th><th>Issues</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $r)
                    <tr class="{{ $r->error_count ? 'table-danger' : ($r->warning_count ? 'table-warning' : '') }}">
                        <td>{{ $r->old_row }}</td>
                        <td>{{ $r->delivered_at?->format('d/m/y') }}</td>
                        <td>{{ $r->customer_name }}<br><small class="text-muted">{{ $r->customer_code }}</small></td>
                        <td>{{ $r->shipment_no }}</td>
                        <td>{{ $r->shipping_mark }}</td>
                        <td>{{ $r->tracking_no }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($r->goods_name_en, 30) }}</td>
                        <td class="text-right">{{ $r->pack_no }}</td>
                        <td class="text-right">{{ number_format($r->weight_kg, 2) }}</td>
                        <td class="text-right">{{ $r->price_per_kg }}</td>
                        <td class="text-right">{{ number_format($r->price, 0) }}</td>
                        <td class="text-right">{{ number_format($r->paid_amount, 0) }}</td>
                        <td class="text-right {{ $r->balance > 5 ? 'text-danger' : '' }}">{{ number_format($r->balance, 0) }}</td>
                        <td>{{ $r->receiver_name }}<br><small>{{ $r->receiver_phone }}</small></td>
                        <td>
                            @foreach(($r->issues['errors'] ?? []) as $e) <div class="text-danger">{{ $e }}</div> @endforeach
                            @foreach(($r->issues['warnings'] ?? []) as $w) <div class="text-warning">{{ $w }}</div> @endforeach
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer"> {{ $rows->links('pagination::bootstrap-4') }}</div>
    </div>

    {{-- 3. Actions --}}
    <div class="card shadow mb-4">
        <div class="card-header"><strong>3. Next step</strong></div>
        <div class="card-body">
            @if($upload->status === 'staged')
                @if($upload->error_rows > 0)
                    <div class="alert alert-danger mb-2">{{ $upload->error_rows }} row(s) have errors. Fix the Excel and upload again.</div>
                @endif

                {{-- ✅ Approve — needs edit-old-data --}}
                @can('edit-old-data')
                    <form method="POST" action="{{ route('legacy.approve', $upload) }}" class="d-inline">@csrf
                        <button class="btn btn-success" {{ $upload->error_rows > 0 ? 'disabled' : '' }}>Data is correct: Approve &amp; show old report</button>
                    </form>
                @endcan

                {{-- ✅ Delete — needs delete-old-data --}}
                @can('delete-old-data')
                    <form method="POST" action="{{ route('legacy.destroy', $upload) }}" class="d-inline" onsubmit="return confirm('Delete this upload?')">
                        @csrf @method('DELETE') <button class="btn btn-outline-danger">Delete upload</button>
                    </form>
                @endcan
            @else
                {{-- ✅ Report link — needs view-old-data --}}
                @can('view-old-data')
                    <a href="{{ route('legacy.report', $upload) }}" class="btn btn-info">Open old report</a>
                @endcan

                @if($upload->status === 'approved')
                    {{-- ✅ Import into live — needs edit-old-data --}}
                    @can('edit-old-data')
                        <form method="POST" action="{{ route('legacy.import-live', $upload) }}" class="d-inline"
                              onsubmit="return confirm('Copy into the LIVE customers, shipments, cargo items and payments? This cannot be undone from here.')">
                            @csrf <button class="btn btn-warning">Import into live tables (optional)</button>
                        </form>
                    @endcan

                    {{-- ✅ Delete — needs delete-old-data --}}
                    @can('delete-old-data')
                        <form method="POST" action="{{ route('legacy.destroy', $upload) }}" class="d-inline" onsubmit="return confirm('Delete this upload?')">
                            @csrf @method('DELETE') <button class="btn btn-outline-danger">Delete upload</button>
                        </form>
                    @endcan
                @else
                    <span class="text-success ml-2">Imported {{ $upload->imported_at->format('d M Y H:i') }}</span>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection