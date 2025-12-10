@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Shipment Details</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('itemsshipments.index') }}">Shipments</a></li>
                    <li class="active">Shipment Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Shipment: {{ $itemshipment->shipment_name }}</strong>
                <div>
                    <a href="{{ route('itemsshipments.edit', $itemshipment->id) }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('itemsshipments.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Shipment Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Shipment Name:</th>
                                <td>{{ $itemshipment->shipment_name }}</td>
                            </tr>
                            <tr>
                                <th>Shipment Date:</th>
                                <td>{{ $itemshipment->shipment_date ? $itemshipment->shipment_date->format('Y-m-d') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge badge-{{ $itemshipment->status == 'draft' ? 'secondary' : ($itemshipment->status == 'pending' ? 'warning' : 'success') }}">
                                        {{ ucfirst($itemshipment->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td>{{ $itemshipment->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Additional Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Total Items:</th>
                                <td>
                                    <span class="badge badge-info">{{ $itemshipment->cargoItems->count() }} items</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Note:</th>
                                <td>{{ $itemshipment->note ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h5>Items in this Shipment</h5>
                @if($itemshipment->cargoItems->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                  <th>Printed CTN NO.</th>
                                            <th>Written CTN NO.</th>
                                            <th>Tracking NO.</th>
                                            <th>Goods Name</th>
                                            <th>Pack No.</th>
                                            <th>Unit Pcs</th>
                                            <th>Weight (kg)</th>
                                            <th>CBM</th>
                                            <th>Shipping Mark</th>
                                            <th>Remark</th>
                                            <th>Copy</th>
                                            <th>Warehouse</th>
                                            <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itemshipment->cargoItems as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->printed_ctn_no }}</td>
                                    <td>{{ $item->written_ctn_no }}</td>
                                    <td>{{ $item->tracking_no }}</td>
                                    <td>{{ $item->goods_name_en }}</td>
                                    <td>{{ $item->pack_no }}</td>
                                    <td>{{ $item->unit_pcs }}</td>
                                    <td>{{ $item->weight_kg ?? '0.000' }}</td>
                                    <td>
                                        {{ number_format($item->length_cm, 0) }}x{{ number_format($item->width_cm, 0) }}x{{ number_format($item->height_cm, 0) }} = {{ number_format($item->cbm ?? 0, 6) }}
                                    </td>
                                                                                <td>{{ $item->shipping_mark }}</td>
                                            <td>{{ $item->remark }}</td>
                                            <td>{{ $item->is_copy ? 'Yes' : 'No' }}</td>

                                    <td>{{ $item->warehouse ?? '-' }}</td>
                                    <td>{{ $item->price ?? '-' }}</td>

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        No items found in this shipment.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .table td, .table th {
        padding: 0.5rem !important;
        vertical-align: middle !important;
        text-align: center !important;
    }
</style>
@endsection