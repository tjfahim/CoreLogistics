@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Customer Manage</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Customers</li>
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
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Customer List</strong>

                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add Customer
                </a>
            </div>

            <div class="card-body">

                <table id="customerTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Customer Code</th>
                            <th>Shipping Mark</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($customers as $c)
                        <tr>
                            <td>{{ $c->id }}</td>
                            <td>{{ $c->name }}</td>
                            <td>{{ $c->email }}</td>
                            <td>{{ $c->phone }}</td>
                            <td>{{ $c->customer_code }}</td>
                            <td>{{ $c->shipping_mark1 }}, {{ $c->shipping_mark2 }}, {{ $c->shipping_mark3 }} {{ $c->shipping_mark4 }}</td>
                            <td>{{ $c->address }}</td>
                            <td>
                                <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>

                                <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No customers found</td>
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
    $('#customerTable').DataTable({
        "order": [[0, "desc"]], // sort by ID descending
        "columnDefs": [
            { "orderable": false, "targets": 6 } // Action column not sortable
        ],
        "pageLength": 10, // rows per page
        "lengthMenu": [5, 10, 25, 50],
        "responsive": true
    });
});
</script>
@endsection