@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Shipment Manage</h1>
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

    <!-- Success Message -->
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="badge badge-pill badge-success">Success</span>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Shipment List</strong>

                <a href="{{ route('shipments.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add Shipment
                </a>
            </div>

            <div class="card-body">

                <table id="shipmentTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Shipment Code</th>
                            <th>Total Weight</th>
                            <th>Status</th>
                            <th>Boxes</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($shipments as $s)
                        <tr>
                            <td>{{ $s->id }}</td>
                            <td>{{ $s->shipment_code }}</td>
                            <td>{{ $s->total_weight }}</td>
                            <td>{{ ucfirst($s->status) }}</td>
                            <td>{{ $s->boxes->count() }}</td>

                            <td>
                                <a href="{{ route('shipments.costs', $s->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-dollar-sign"></i> Costs
                                </a>
                                <!-- <a href="{{ route('shipments.generateCustomerBills', $s->id) }}"
                                   class="btn btn-success btn-sm">
                                    <i class="fa fa-calculator"></i> Generate Bills
                                </a> -->
                                <a href="{{ route('shipments.customerBills', $s->id) }}"
                                   class="btn btn-dark btn-sm">
                                    <i class="fa fa-users"></i> Customer Bills
                                </a>
                                <a href="{{ route('shipments.costs.summary', $s->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-file-invoice"></i> Summary
                                </a>
                                <a href="{{ route('shipments.edit', $s->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>

                                <form action="{{ route('shipments.destroy', $s->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No shipments found</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

<script>
$(document).ready(function() {
    $('#shipmentTable').DataTable({
        "order": [[0, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": 5 } // last column = Action
        ],
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50],
        "responsive": true
    });
});
</script>

@endsection
