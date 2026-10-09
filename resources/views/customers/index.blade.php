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

    {{-- Success Message --}}
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

    @if(session('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
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

                {{-- ✅ Add Customer --}}
                @can('create-customers')
                    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add Customer
                    </a>
                @endcan
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
                            <th>Shipping Marks</th>
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
                            <td>
                                @php
                                    $allMarks = $c->all_shipping_marks;
                                @endphp
                                @if(count($allMarks) > 0)
                                    @foreach($allMarks as $mark)
                                        <span class="badge badge-info mb-1">{{ $mark }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No marks</span>
                                @endif
                            </td>
                            <td>{{ $c->address }}</td>
                            <td>
                                @canany(['view-customers', 'edit-customers', 'delete-customers'])
                                    {{-- ✅ Ledger (depends on view permission) --}}
                                    @can('view-customers')
                                        <a href="{{ route('ledger.customer', $c->id) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-book"></i> Ledger
                                        </a>
                                    @endcan

                                    {{-- ✅ Edit --}}
                                    @can('edit-customers')
                                        <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                    @endcan

                                    {{-- ✅ Delete --}}
                                    @can('delete-customers')
                                        <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="d-inline delete-form">
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
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No customers found</td>
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
        "order": [[0, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": 7 }
        ],
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50],
        "responsive": true
    });

    // Confirm before delete
    $('.delete-form').on('submit', function (e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this customer?')) {
            this.submit();
        }
    });
});
</script>
@endsection