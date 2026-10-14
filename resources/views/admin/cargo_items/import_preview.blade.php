@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Import Preview</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('cargo-items.index') }}">Items</a></li>
                    <li><a href="{{ route('cargo-items.create') }}">Add Items</a></li>
                    <li class="active">Preview</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">

        @cannot('import-warehouse-file')
            <div class="alert alert-danger">
                You do not have permission to import warehouse files.
            </div>
        @endcannot

        @can('import-warehouse-file')

            {{-- File Info --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><strong>File:</strong> {{ $excelFile->file_name }}</div>
                        <div class="col-md-3"><strong>Title:</strong> {{ $excelFile->title ?? '—' }}</div>
                        <div class="col-md-2"><strong>Total Rows:</strong> {{ count($items) }}</div>
                        <div class="col-md-2"><strong class="text-danger">Duplicates:</strong> {{ count($duplicates) }}</div>
                        <div class="col-md-2"><strong class="text-success">Unique:</strong> {{ count($items) - count($duplicates) }}</div>
                    </div>
                    @if($excelFile->note)
                        <div class="mt-2"><strong>Note:</strong> {{ $excelFile->note }}</div>
                    @endif
                </div>
            </div>

            {{-- Unique rows --}}
            <div class="card mb-3 border-success">
                <div class="card-header bg-success text-white">
                    <strong><i class="fa fa-check-circle"></i> Unique Rows — {{ count($uniqueItems) }} item(s) will be imported</strong>
                </div>
                <div class="card-body p-0">
                    @if(count($uniqueItems) === 0)
                        <div class="p-3 text-center text-muted">No unique rows found.</div>
                    @else
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-bordered table-sm table-striped mb-0">
                                <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1;">
            <tr>
                <th>#</th>
                <th>Received Date</th>
                <th>Receipt No.</th>
                <th>Printed CTN</th>
                <th>Written CTN</th>
                <th>Tracking No.</th>
                <th>Goods Name (EN)</th>
                <th>品名 (Local)</th>
                <th>Pack No.</th>
                <th>Unit Pcs</th>
                <th>Weight (kg)</th>
                <th>L (cm)</th>
                <th>W (cm)</th>
                <th>H (cm)</th>
                <th>CBM</th>
                <th>Shipping Mark</th>
                <th>Warehouse</th>
                <th>Remark</th>
                <th>Copy</th>
                <th>Status</th>
            </tr>
        </thead>
                                <tbody>
            @foreach ($items as $i => $item)
                @php $dupReason = $dupReasons[$i + 1] ?? null; @endphp
                <tr class="{{ $dupReason ? 'table-warning' : '' }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['date'] ?? '' }}</td>
                    <td>{{ $item['receipt_no'] ?? '' }}</td>
                    <td>{{ $item['printed_ctn_no'] ?? '' }}</td>
                    <td>{{ $item['written_ctn_no'] ?? '' }}</td>
                    <td>{{ $item['tracking_no'] ?? '' }}</td>
                    <td>{{ $item['goods_name_en'] ?? '' }}</td>
                    <td>{{ $item['goods_name_local'] ?? '' }}</td>
                    <td>{{ $item['pack_no'] ?? '' }}</td>
                    <td>{{ $item['unit_pcs'] ?? '' }}</td>
                    <td>{{ $item['weight_kg'] ?? '' }}</td>
                    <td>{{ $item['length_cm'] ?? '' }}</td>
                    <td>{{ $item['width_cm'] ?? '' }}</td>
                    <td>{{ $item['height_cm'] ?? '' }}</td>
                    <td>{{ isset($item['cbm']) ? number_format((float) $item['cbm'], 4) : '' }}</td>
                    <td>{{ $item['shipping_mark'] ?? '' }}</td>
                    <td>{{ $item['warehouse'] ?? '' }}</td>
                    <td>{{ $item['remark'] ?? '' }}</td>
                    <td class="text-center">{{ !empty($item['is_copy']) ? 'YES' : 'NO' }}</td>
                    <td>
                        @if ($dupReason)
                            <span class="badge badge-warning">{{ $dupReason }}</span>
                        @else
                            <span class="badge badge-success">New</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Duplicates --}}
            @if(count($duplicates) > 0)
            <div class="card mb-3 border-danger">
                <div class="card-header bg-danger text-white">
                    <strong><i class="fa fa-exclamation-triangle"></i> Duplicate Rows — {{ count($duplicates) }} item(s) will be skipped</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-sm table-striped mb-0">
                            <thead class="thead-light" style="position: sticky; top: 0;">
                                <tr>
                                    <th>#</th>
                                    <th>Row #</th>
                                    <th>Tracking No.</th>
                                    <th>Goods Name (EN)</th>
                                    <th>Printed CTN</th>
                                    <th>Written CTN</th>
                                    <th>Weight</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($duplicates as $i => $dup)
                                    <tr class="table-danger">
                                        <td>{{ $i + 1 }}</td>
                                        <td><span class="badge badge-danger">{{ $dup['row'] }}</span></td>
                                        <td>{{ $dup['tracking_no'] }}</td>
                                        <td>{{ $dup['goods_name_en'] }}</td>
                                        <td>{{ $dup['printed_ctn_no'] ?? '—' }}</td>
                                        <td>{{ $dup['written_ctn_no'] ?? '—' }}</td>
                                        <td>{{ $dup['weight_kg'] ?? '—' }}</td>
                                        <td>
                                            <span class="badge badge-{{ str_contains($dup['reason'], 'database') ? 'danger' : 'warning' }}">
                                                {{ $dup['reason'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Full preview --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Full Data Preview ({{ count($items) }} rows total)</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-bordered table-sm table-striped">
                            <thead class="thead-dark" style="position: sticky; top: 0;">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Tracking No.</th>
                                    <th>Goods Name (EN)</th>
                                    <th>Pack No.</th>
                                    <th>Unit Pcs</th>
                                    <th>Weight</th>
                                    <th>CBM</th>
                                    <th>Shipping Mark</th>
                                    <th>Warehouse</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $duplicateRowNumbers = collect($duplicates)->pluck('row')->toArray(); @endphp
                                @foreach($items as $i => $item)
                                    @php $rowNum = $i + 1; @endphp
                                    <tr class="{{ in_array($rowNum, $duplicateRowNumbers) ? 'table-danger' : '' }}">
                                        <td>{{ $rowNum }}</td>
                                        <td>{{ $item['date'] ?? '—' }}</td>
                                        <td>{{ $item['tracking_no'] }}</td>
                                        <td>{{ $item['goods_name_en'] }}</td>
                                        <td>{{ $item['pack_no'] }}</td>
                                        <td>{{ $item['unit_pcs'] }}</td>
                                        <td>{{ $item['weight_kg'] }}</td>
                                        <td>{{ $item['cbm'] }}</td>
                                        <td>{{ $item['shipping_mark'] }}</td>
                                        <td>{{ $item['warehouse'] }}</td>
                                        <td>
                                            @if(in_array($rowNum, $duplicateRowNumbers))
                                                <span class="badge badge-danger">Duplicate</span>
                                            @else
                                                <span class="badge badge-success">New</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="card">
                <div class="card-body d-flex justify-content-between">
                    <form action="{{ route('cargo-items.import.cancel', $excelFile->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-secondary btn-sm">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                    </form>

                    <div>
                        <form action="{{ route('cargo-items.import.confirm', $excelFile->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="skip_duplicates" value="1">
                            <button class="btn btn-primary btn-sm">
                                <i class="fa fa-check"></i>
                                @if(count($duplicates) > 0)
                                    Import {{ count($items) - count($duplicates) }} Rows (Skip Duplicates)
                                @else
                                    Import All {{ count($items) }} Rows
                                @endif
                            </button>
                        </form>

                        @if(count($duplicates) > 0)
                            <form action="{{ route('cargo-items.import.confirm', $excelFile->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('This will import ALL rows including duplicates. Continue?')">
                                @csrf
                                <input type="hidden" name="skip_duplicates" value="0">
                                <button class="btn btn-warning btn-sm">
                                    <i class="fa fa-exclamation-triangle"></i> Import All (Include Duplicates)
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

        @endcan

    </div>
</div>
@endsection