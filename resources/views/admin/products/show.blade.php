@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>View Product</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('products.index') }}">Products</a></li>
                    <li class="active">View Product</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Product Details</strong>
                <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>ID</th>
                        <td>{{ $product->id }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $product->name }}</td>
                    </tr>
                    <tr>
                        <th>Customer</th>
                        <td>{{ $product->customer->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Short Description</th>
                        <td>{{ $product->short_description ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Weight (kg)</th>
                        <td>{{ $product->weight }}</td>
                    </tr>
                    <tr>
                        <th>Price (kg)</th>
                        <td>{{ $product->price }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{ ucfirst(str_replace('_', ' ', $product->status)) }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $product->created_at }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $product->updated_at }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
