@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Update Printed CTN Numbers</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('itemsshipments.index') }}">Shipments</a></li>
                    <li>
                        <a href="{{ route('itemsshipments.show', $itemshipment->id) }}">
                            {{ $itemshipment->shipment_name }}
                        </a>
                    </li>
                    <li class="active">Update CTN</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <span class="badge badge-pill badge-success">Success</span> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <span class="badge badge-pill badge-danger">Error</span> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>Printed CTN Numbers - {{ $itemshipment->shipment_name }}</strong>
            <a href="{{ route('itemsshipments.show', $itemshipment->id) }}"
               class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">

            <form action="{{ route('itemshipments.update-ctn', $itemshipment->id) }}" method="POST">
                @csrf

                <div class="d-flex justify-content-between mb-3">
                    <h5>Update Printed CTN Numbers</h5>
                    <button class="btn btn-success">
                        <i class="fa fa-save"></i> Save All
                    </button>
                </div>

                @php
                    // Solid colors for groups
                    $colors = [
                        '#ff6b6b', '#1dd1a1', '#54a0ff', '#feca57', '#5f27cd',
                        '#10ac84', '#ff9f43', '#2e86de', '#ee5253', '#222f3e',
                        '#0abde3', '#576574', '#8395a7', '#ff6348', '#1e3799',
                    ];
                    $colorIndex = 0;
                @endphp

                @if($groupedItems->count() > 0)

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Old Printed CTN</th>
                                <th>New Printed CTN</th>
                                <th>Written CTN</th>
                                <th>Goods Name</th>
                                <th>Weight</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($groupedItems as $ctn => $items)

                                {{-- Only color groups with 2 or more items --}}
                                @if($items->count() > 1)
                                    @php
                                        $bg = $colors[$colorIndex % count($colors)];
                                        $colorIndex++;
                                    @endphp
                                @else
                                    @php
                                        $bg = '';
                                    @endphp
                                @endif

                                @foreach($items as $item)
                                <tr style="background: {{ $bg }}">
                                    <td>{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>

                                    <td><strong>{{ $item->printed_ctn_no ?? '-' }}</strong></td>

                                    <td>
                                        <input type="text"
                                               name="printed_ctn[{{ $item->id }}]"
                                               value="{{ $item->printed_ctn_no }}"
                                               class="form-control form-control-sm"
                                               placeholder="Enter new CTN (nullable)">
                                    </td>

                                    <td>{{ $item->written_ctn_no ?? '-' }}</td>

                                    <td>{{ $item->goods_name_en }}</td>

                                    <td>{{ number_format($item->weight_kg, 2) }} kg</td>

                                    <td>
                                        <span class="badge badge-{{ $item->status == 'packed' ? 'info' : 'secondary' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach

                            @endforeach

                        </tbody>

                    </table>
                </div>

                @else
                    <div class="alert alert-warning text-center">
                        No cargo items found.
                    </div>
                @endif

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa fa-save"></i> Save All Changes
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<style>
    .table td, .table th {
        vertical-align: middle !important;
    }
    .table {
        --bs-table-bg: transparent;
    }
</style>

@endsection
