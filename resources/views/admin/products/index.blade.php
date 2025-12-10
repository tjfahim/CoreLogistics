@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Products Manage</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Products</li>
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
                <strong class="card-title">Product List</strong>
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add Product
                </a>
            </div>

            <div class="card-body">
                <table id="productTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Weight</th>
                            <th>Price</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($products as $p)
                        <tr>
                            <td>{{ $p->id }}</td>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->short_description }}</td>
                            <td>{{ $p->weight }}</td>
                            <td>{{ $p->price ?? '0.00' }} tk</td>
                            <td>{{ $p->customer->name ?? '-' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $p->status)) }}</td>
                            <td>
                                <a href="{{ route('products.show', $p->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                <a href="{{ route('products.edit', $p->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="d-inline">
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
                            <td colspan="7" class="text-center text-muted">No products found</td>
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
    $('#productTable').DataTable();
});
</script>
@endsection




