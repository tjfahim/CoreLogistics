@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Add New Box</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('boxes.index') }}">Boxes</a></li>
                    <li class="active">Add Box</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Add New Box</strong>
                <a href="{{ route('boxes.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
            <div class="card-body">
                <form action="{{ route('boxes.store') }}" method="POST">
                    @csrf

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Box Code</label>
                        <input type="text" name="box_code" class="form-control @error('box_code') is-invalid @enderror" placeholder="Box Code" value="{{ old('box_code') }}">
                        @error('box_code') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Max Box Weight (kg)</label>
                        <input type="number" step="0.01" name="max_box_weight" class="form-control @error('max_box_weight') is-invalid @enderror" placeholder="Max weight per box" value="{{ old('max_box_weight') }}">
                        @error('max_box_weight') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Select Products</label>
                        <table id="productTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="margin-left: 50px;">Select</th>
                                    <th>Name</th>
                                    <th>Remaining Weight (kg)</th>
                                    <!-- <th>Weight to Add (kg)</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="products[{{ $product->id }}][id]" value="{{ $product->id }}" class="form-check-input" id="product{{ $product->id }}" style="margin-left:0px;">
                                        </td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->weight }}</td>
                                        <!-- <td>
                                            <input type="number" step="0.01" name="products[{{ $product->id }}][weight]" class="form-control @error('products.' . $product->id . '.weight') is-invalid @enderror" placeholder="Weight to add" value="{{ old('products.' . $product->id . '.weight') }}">
                                            @error('products.' . $product->id . '.weight')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </td> -->
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @error('products')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <button class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Create Box</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#productTable').DataTable({
            "scrollY": "400px",
            "scrollCollapse": true,
            "paging": true,
            "searching": true,
        });
    });
</script>
@endsection
