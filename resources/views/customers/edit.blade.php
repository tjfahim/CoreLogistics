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
                    <li><a href="{{ route('customers.index') }}">Customers</a></li>
                    <li class="active">Edit Customer</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    <div class="col-lg-12">

        <div class="card">

            
                 <div class="card-header d-flex justify-content-between ">
                <strong>Edit Customer</strong>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                    
                    </div>

            <div class="card-body">

                <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-bold">Name</label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name', $customer->name) }}" 
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Enter customer name">
                        @error('name') 
                            <small class="text-danger">{{ $message }}</small> 
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Email</label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email', $customer->email) }}" 
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="example@mail.com">
                        @error('email') 
                            <small class="text-danger">{{ $message }}</small> 
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Phone</label>
                        <input type="text" 
                               name="phone" 
                               value="{{ old('phone', $customer->phone) }}" 
                               class="form-control @error('phone') is-invalid @enderror"
                               placeholder="017xxxxxxxx">
                        @error('phone') 
                            <small class="text-danger">{{ $message }}</small> 
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Customer Code</label>
                        <input type="text" 
                               name="customer_code" 
                               value="{{ old('customer_code', $customer->customer_code) }}" 
                               class="form-control @error('customer_code') is-invalid @enderror"
                               placeholder="CUS-1001">
                        @error('customer_code') 
                            <small class="text-danger">{{ $message }}</small> 
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Shipping Mark 1</label>
                        <input type="text" 
                               name="shipping_mark1" 
                               value="{{ old('shipping_mark1', $customer->shipping_mark1) }}" 
                               class="form-control @error('shipping_mark1') is-invalid @enderror"
                               placeholder="CUS-1001">
                        @error('shipping_mark1') 
                            <small class="text-danger">{{ $message }}</small> 
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Shipping Mark 2</label>
                        <input type="text" 
                               name="shipping_mark2" 
                               value="{{ old('shipping_mark2', $customer->shipping_mark2) }}" 
                               class="form-control @error('shipping_mark2') is-invalid @enderror"
                               placeholder="CUS-2002">
                        @error('shipping_mark2') 
                            <small class="text-danger">{{ $message }}</small> 
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Shipping Mark 3</label>
                        <input type="text" 
                               name="shipping_mark3" 
                               value="{{ old('shipping_mark3', $customer->shipping_mark3) }}" 
                               class="form-control @error('shipping_mark3') is-invalid @enderror"
                               placeholder="CUS-3003">
                        @error('shipping_mark3') 
                            <small class="text-danger">{{ $message }}</small> 
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Shipping Mark 4</label>
                        <input type="text" 
                               name="shipping_mark4" 
                               value="{{ old('shipping_mark4', $customer->shipping_mark4) }}" 
                               class="form-control @error('shipping_mark4') is-invalid @enderror"
                               placeholder="CUS-4004">
                        @error('shipping_mark4') 
                            <small class="text-danger">{{ $message }}</small> 
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Address</label>
                        <textarea name="address" rows="3"
                                  class="form-control @error('address') is-invalid @enderror"
                                  placeholder="Enter full address">{{ old('address', $customer->address) }}</textarea>
                        @error('address') 
                            <small class="text-danger">{{ $message }}</small> 
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between mt-3">

                    

                        <button class="btn btn-primary btn-sm px-3">
                            <i class="fa fa-save"></i> Update Customer
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection
