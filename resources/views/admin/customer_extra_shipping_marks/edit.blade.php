@extends('admin.layouts.master')

@section('main_content')

{{-- ===================== Select2 CSS ===================== --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Extra Shipping Marks</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('customer-extra-shipping-marks.index') }}">Extra Shipping Marks</a></li>
                    <li class="active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Edit Shipping Mark</strong>
                <a href="{{ route('customer-extra-shipping-marks.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- ✅ Guard entire form --}}
                @cannot('edit-shipping-mark')
                    <div class="alert alert-danger">
                        You do not have permission to edit shipping marks.
                    </div>
                @endcannot

                @can('edit-shipping-mark')
                    <form action="{{ route('customer-extra-shipping-marks.update', $mark->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="font-weight-bold">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id"
                                    id="customerSelect"
                                    class="form-control @error('customer_id') is-invalid @enderror"
                                    style="width: 100%;"
                                    required>
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                        {{ old('customer_id', $mark->customer_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->customer_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Shipping Mark <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="shipping_mark"
                                   value="{{ old('shipping_mark', $mark->shipping_mark) }}"
                                   class="form-control @error('shipping_mark') is-invalid @enderror"
                                   required>
                            @error('shipping_mark')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-primary btn-sm px-3">
                                <i class="fa fa-save"></i> Update Shipping Mark
                            </button>
                        </div>

                    </form>
                @endcan

            </div>

        </div>
    </div>
</div>

@endsection

{{-- ===================== Select2 JS + Init ===================== --}}
@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    jQuery(function () {
        jQuery('#customerSelect').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Select Customer --',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush