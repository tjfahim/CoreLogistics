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
    @if(session('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger">{{ session('error') }}</div>
        </div>
    @endif

    <div class="col-lg-12">

        @cannot('view-shipments')
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-danger mb-0">
                        You do not have permission to view shipments.
                    </div>
                </div>
            </div>
        @endcannot

        @can('view-shipments')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Shipment: {{ $itemshipment->shipment_name }}</strong>
                <div>
                    {{-- ✅ Edit --}}
                    @can('edit-shipments')
                        <a href="{{ route('itemsshipments.edit', $itemshipment->id) }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                    @endcan

                    <a href="{{ route('itemsshipments.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>

                    {{-- ✅ Print — anyone who can view can print --}}
                    <button class="btn btn-primary btn-sm" onclick="printShipment()">
                        <i class="fa fa-print"></i> Print
                    </button>
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
                                    <span id="statusBadge" class="badge badge-{{ 
                                        $itemshipment->status == 'draft' ? 'secondary' : 
                                        ($itemshipment->status == 'pending' ? 'warning' : 
                                        ($itemshipment->status == 'waiting_for_customs' ? 'info' : 'success')) 
                                    }}">
                                        @if($itemshipment->status == 'pending')
                                            Shipment Ongoing
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $itemshipment->status)) }}
                                        @endif
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
                @php
                    $colors = [
                        '#ff6b6b', '#1dd1a1', '#54a0ff', '#feca57', '#5f27cd',
                        '#10ac84', '#ff9f43', '#2e86de', '#ee5253', '#222f3e',
                        '#0abde3', '#576574', '#8395a7', '#ff6348', '#1e3799',
                    ];
                    $colorIndex = 0;
                    $groupedItems = $itemshipment->cargoItems->groupBy('printed_ctn_no');
                @endphp

                @if($itemshipment->cargoItems->count() > 0)
                    @can('edit-warehouse-item')
                    <form id="boxForm" method="POST" class="form-inline mb-2 p-2 border rounded bg-light">
                        @csrf
                        <strong class="mr-2"><i class="fa fa-cube"></i> Box:</strong>
                        <input type="text" name="box_no" class="form-control form-control-sm mr-2" style="width:140px"
                               placeholder="auto (BOX-1…)">
                        <button type="submit" class="btn btn-primary btn-sm mr-2"
                                formaction="{{ route('itemsshipments.make-box', $itemshipment->id) }}">
                            <i class="fa fa-object-group"></i> Group selected into box
                        </button>
                        <button type="submit" class="btn btn-outline-secondary btn-sm"
                                formaction="{{ route('itemsshipments.remove-box', $itemshipment->id) }}">
                            <i class="fa fa-object-ungroup"></i> Remove from box
                        </button>
                        <small class="text-muted ml-3">সিলেক্ট করা আইটেমগুলো একটি বক্সের গ্রুপ হিসেবে দেখাবে; আইটেমের বাকি সব কাজ আগের মতোই।</small>
                    </form>
                    @endcan
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    @can('edit-warehouse-item')<th><input type="checkbox" id="checkAllBox"></th>@endcan
                                    <th>ID</th>
                                    <th>Box</th>
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
                                @foreach($groupedItems as $ctn => $items)
                                    @php
                                        $bg = ($items->count() > 1) ? $colors[$colorIndex % count($colors)] : '';
                                        if($items->count() > 1) $colorIndex++;
                                    @endphp
                                    @foreach($items as $item)
                                    <tr style="background: {{ $bg }}">
                                        @can('edit-warehouse-item')<td><input type="checkbox" class="box-check" name="item_ids[]" value="{{ $item->id }}" form="boxForm"></td>@endcan
                                        <td>{{ $item->id }}</td>
                                        <td>@if($item->box_no)<span class="badge badge-dark">{{ $item->box_no }}</span>@endif</td>
                                        <td>{{ $item->printed_ctn_no }}</td>
                                        <td>{{ $item->written_ctn_no }}</td>
                                        <td>{{ $item->tracking_no }}</td>
                                        <td>{{ $item->goods_name_en }}</td>
                                        <td>{{ $item->pack_no }}</td>
                                        <td>{{ $item->unit_pcs }}</td>
                                        <td>{{ $item->weight_kg ?? '0.000' }}</td>
                                        <td>{{ number_format($item->length_cm, 0) }}x{{ number_format($item->width_cm, 0) }}x{{ number_format($item->height_cm, 0) }} = {{ number_format($item->cbm ?? 0, 6) }}</td>
                                        <td>{{ $item->shipping_mark }}</td>
                                        <td>{{ $item->remark }}</td>
                                        <td>{{ $item->is_copy ? 'Yes' : 'No' }}</td>
                                        <td>{{ $item->warehouse ?? '-' }}</td>
                                        <td>{{ $item->price ?? '-' }}</td>
                                    </tr>
                                    @endforeach
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
        @endcan
    </div>
</div>

<script>
document.getElementById('checkAllBox')?.addEventListener('change', function () {
    document.querySelectorAll('.box-check').forEach(function (c) { c.checked = this.checked; }, this);
});
function printShipment() {
    var printContents = document.querySelector('.card').innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>

<style>
.table td, .table th {
    padding: 0.5rem !important;
    vertical-align: middle !important;
    text-align: center !important;
}
.table { --bs-table-bg: transparent; }

@media print {
    body * { visibility: hidden; }
    .card, .card * { visibility: visible; }
    .card { width: 100%; margin: 0; padding: 0; border: none; }
    .btn, .btn-group, nav, .breadcrumbs { display: none !important; }
}
</style>
@endsection