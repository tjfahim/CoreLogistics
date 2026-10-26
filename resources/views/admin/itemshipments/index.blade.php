@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Shipments Manage</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Shipments</li>
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Shipment List</strong>

                {{-- ✅ Create Shipment --}}
                @can('create-shipments')
                    <a href="{{ route('itemsshipments.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create Shipment
                    </a>
                @endcan
            </div>

            <div class="card-body">
                <table id="shipmentsTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Shipment Name</th>
                            <th>Shipment Date</th>
                            <th>Items Count</th>
                            <th>Status</th>
                            <th>Note</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($shipments as $shipment)
                        <tr>
                            <td>{{ $shipment->id }}</td>
                            <td><strong>{{ $shipment->shipment_name }}</strong></td>
                            <td>{{ $shipment->shipment_date ? $shipment->shipment_date->format('Y-m-d') : '-' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $shipment->cargoItems->count() }} items</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $shipment->status == 'draft' ? 'secondary' : ($shipment->status == 'pending' ? 'warning' : 'success') }}">
                                    {{ ucfirst($shipment->status) }}
                                </span>
                            </td>
                            <td>{{ $shipment->note ? Str::limit($shipment->note, 50) : '-' }}</td>
                            <td>{{ $shipment->created_at->format('Y-m-d') }}</td>
                            <td>
                                @canany([
                                    'view-ctn',
                                    'view-price',
                                    'edit-shipments',
                                    'view-returns',
                                    'view-shipments',
                                    'delete-shipments'
                                ])

                                    {{-- ✅ CTN --}}
                                    @can('view-ctn')
                                        <a href="{{ route('itemshipments.edit-ctn-numbers', $shipment->id) }}" class="btn btn-primary btn-sm">
                                            CTN
                                        </a>
                                    @endcan

                                    {{-- ✅ Prices --}}
                                    @can('view-price')
                                        <a href="{{ route('itemshipments.edit-prices', $shipment->id) }}" class="btn btn-primary btn-sm">
                                            Prices
                                        </a>
                                    @endcan

                                    {{-- Customer invoices / ledger of this shipment --}}
                                    @can('view-invoices')
                                        <a href="{{ route('shipments.billing', $shipment->id) }}" class="btn btn-primary btn-sm">
                                            Customer Ledger
                                        </a>
                                    @endcan

                                    {{-- ✅ Customer Assignment — edit-shipments --}}
                                    @can('edit-shipments')
                                        <a href="{{ route('itemshipments.customer.assignment', $shipment->id) }}" class="btn btn-primary btn-sm">
                                            Customers
                                        </a>
                                    @endcan

                                    {{-- ✅ Returns --}}
                                    @can('view-returns')
                                        <a href="{{ route('itemsshipments.manage-returns', $shipment->id) }}" class="btn btn-warning btn-sm">
                                            Returns
                                        </a>
                                    @endcan

                                    {{-- ✅ View --}}
                                    @can('view-shipments')
                                        <a href="{{ route('itemsshipments.show', $shipment->id) }}" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan

                                    {{-- ✅ Edit --}}
                                    @can('edit-shipments')
                                        <a href="{{ route('itemsshipments.edit', $shipment->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    @endcan

                                    {{-- ✅ Delete --}}
                                    @can('delete-shipments')
                                        <form action="{{ route('itemsshipments.destroy', $shipment->id) }}"
                                              method="POST"
                                              class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    <span class="text-muted">—</span>
                                @endcanany
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#shipmentsTable').DataTable({
        "pageLength": 50,
        "lengthMenu": [ 50, 100, 200, 500, 1000 ],
        "order": [[0, 'desc']]
    });

    // Confirm before delete
    $('.delete-form').on('submit', function (e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this shipment?')) {
            this.submit();
        }
    });
});
</script>

<style>
    .table td, .table th {
        padding: 0.5rem !important;
        vertical-align: middle !important;
        text-align: center !important;
    }
</style>
@endsection