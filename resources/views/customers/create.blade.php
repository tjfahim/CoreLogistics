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
                    <li class="active">Add Customer</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    <div class="col-lg-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between">
                <strong>Add New Customer</strong>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">

                {{-- ✅ Guard entire form --}}
                @cannot('create-customers')
                    <div class="alert alert-danger">
                        You do not have permission to create customers.
                    </div>
                @endcannot

                @can('create-customers')
                    <form action="{{ route('customers.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label class="font-weight-bold">Name</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
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
                                   value="{{ old('email') }}"
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
                                   value="{{ old('phone') }}"
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
                                   value="{{ old('customer_code') }}"
                                   class="form-control @error('customer_code') is-invalid @enderror"
                                   placeholder="CUS-1001">
                            @error('customer_code')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <hr>
                        <h5 class="mb-3"><strong>Fixed Shipping Marks (1-4)</strong></h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Shipping Mark 1</label>
                                    <input type="text"
                                           name="shipping_mark1"
                                           value="{{ old('shipping_mark1') }}"
                                           class="form-control @error('shipping_mark1') is-invalid @enderror">
                                    @error('shipping_mark1')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Shipping Mark 2</label>
                                    <input type="text"
                                           name="shipping_mark2"
                                           value="{{ old('shipping_mark2') }}"
                                           class="form-control @error('shipping_mark2') is-invalid @enderror">
                                    @error('shipping_mark2')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Shipping Mark 3</label>
                                    <input type="text"
                                           name="shipping_mark3"
                                           value="{{ old('shipping_mark3') }}"
                                           class="form-control @error('shipping_mark3') is-invalid @enderror">
                                    @error('shipping_mark3')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Shipping Mark 4</label>
                                    <input type="text"
                                           name="shipping_mark4"
                                           value="{{ old('shipping_mark4') }}"
                                           class="form-control @error('shipping_mark4') is-invalid @enderror">
                                    @error('shipping_mark4')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><strong>Extra Shipping Marks</strong></h5>
                            <button type="button" class="btn btn-success btn-sm" id="addExtraMark">
                                <i class="fa fa-plus"></i> Add Extra Shipping Mark
                            </button>
                        </div>

                        <div id="extraMarksContainer">
                            @if(old('extra_shipping_marks'))
                                @foreach(old('extra_shipping_marks') as $index => $mark)
                                    <div class="form-group extra-mark-row">
                                        <div class="input-group">
                                            <input type="text"
                                                   name="extra_shipping_marks[]"
                                                   value="{{ $mark }}"
                                                   class="form-control"
                                                   placeholder="Enter extra shipping mark">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger remove-mark">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Address</label>
                            <textarea name="address" rows="3"
                                      class="form-control @error('address') is-invalid @enderror"
                                      placeholder="Enter full address">{{ old('address') }}</textarea>
                            @error('address')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-primary btn-sm px-3">
                                <i class="fa fa-save"></i> Save Customer
                            </button>
                        </div>

                    </form>
                @endcan

            </div>

        </div>

    </div>

</div>

<script>
$(document).ready(function() {
    // Add extra shipping mark row
    $('#addExtraMark').click(function() {
        var row = `
            <div class="form-group extra-mark-row">
                <div class="input-group">
                    <input type="text"
                           name="extra_shipping_marks[]"
                           class="form-control"
                           placeholder="Enter extra shipping mark">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-mark">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#extraMarksContainer').append(row);
    });

    // Remove extra shipping mark row
    $(document).on('click', '.remove-mark', function() {
        $(this).closest('.extra-mark-row').remove();
    });
});
</script>
@endsection