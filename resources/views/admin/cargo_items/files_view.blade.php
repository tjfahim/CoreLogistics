@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>File Data</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('cargo-items.files.index') }}">Imported Files</a></li>
                    <li class="active">View</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <strong>{{ $file->title ?? $file->file_name }}</strong>
                <div>
                    <a href="{{ route('cargo-items.files.download', $file->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-download"></i> Download
                    </a>
                    <a href="{{ route('cargo-items.files.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>File Name:</strong> {{ $file->file_name }}</div>
                    <div class="col-md-3"><strong>Uploaded:</strong> {{ $file->created_at->format('Y-m-d H:i') }}</div>
                    <div class="col-md-2"><strong>Total Rows:</strong> {{ $file->total_rows }}</div>
                    <div class="col-md-2"><strong>Imported:</strong> {{ $file->imported_rows }}</div>
                    <div class="col-md-2"><strong>Duplicates:</strong> {{ $file->duplicate_rows }}</div>
                </div>
                @if($file->note)
                    <div class="mt-2"><strong>Note:</strong> {{ $file->note }}</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong>Parsed Data ({{ count($transformedItems) }} rows)</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-bordered table-sm table-striped">
                        <thead class="thead-dark" style="position: sticky; top: 0;">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Printed CTN</th>
                                <th>Written CTN</th>
                                <th>Tracking No.</th>
                                <th>Goods Name (EN)</th>
                                <th>Pack No.</th>
                                <th>Unit Pcs</th>
                                <th>Weight</th>
                                <th>L</th>
                                <th>W</th>
                                <th>H</th>
                                <th>CBM</th>
                                <th>Shipping Mark</th>
                                <th>Warehouse</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transformedItems as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item['date'] ?? '—' }}</td>
                                    <td>{{ $item['printed_ctn_no'] }}</td>
                                    <td>{{ $item['written_ctn_no'] }}</td>
                                    <td>{{ $item['tracking_no'] }}</td>
                                    <td>{{ $item['goods_name_en'] }}</td>
                                    <td>{{ $item['pack_no'] }}</td>
                                    <td>{{ $item['unit_pcs'] }}</td>
                                    <td>{{ $item['weight_kg'] }}</td>
                                    <td>{{ $item['length_cm'] }}</td>
                                    <td>{{ $item['width_cm'] }}</td>
                                    <td>{{ $item['height_cm'] }}</td>
                                    <td>{{ $item['cbm'] }}</td>
                                    <td>{{ $item['shipping_mark'] }}</td>
                                    <td>{{ $item['warehouse'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="text-center text-muted">No valid rows found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection