@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Add Product</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('products.index') }}">Products</a></li>
                    <li class="active">Add Product</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
              <div class="card-header d-flex justify-content-between ">
                <strong>Add New Product</strong>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                    
                    </div>
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
  <div class="form-group mb-3">
                        <label class="font-weight-bold">Customer</label>
                        <select name="customer_id" class="form-control @error('customer_id') is-invalid @enderror">
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Product Name">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Short Description</label>
                        <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" placeholder="Short description">{{ old('short_description') }}</textarea>
                        @error('short_description') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight" value="{{ old('weight') }}" class="form-control @error('weight') is-invalid @enderror" placeholder="Weight in kg">
                        @error('weight') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Price tk (kg)</label>
                        <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="form-control @error('price') is-invalid @enderror" placeholder="price per kg">
                        @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                      
                        <button class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
