@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Edit Box</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('boxes.index') }}">Boxes</a></li>
                    <li class="active">Edit Box</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Edit Box Status</strong>
                <a href="{{ route('boxes.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('boxes.update', $box->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Box Code</label>
                        <input type="text" class="form-control" value="{{ $box->box_code }}" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Total Weight (kg)</label>
                        <input type="text" class="form-control" value="{{ $box->total_weight }}" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Status</label>
                        <select name="status" class="form-control">
                            <option value="packed" {{ $box->status == 'packed' ? 'selected' : '' }}>Packed</option>
                            <option value="shipping_ongoing" {{ $box->status == 'shipping_ongoing' ? 'selected' : '' }}>Shipping Ongoing</option>
                            <option value="arrived_bd" {{ $box->status == 'arrived_bd' ? 'selected' : '' }}>Arrived BD</option>
                            <option value="ready_for_delivery" {{ $box->status == 'ready_for_delivery' ? 'selected' : '' }}>Ready for Delivery</option>
                            <option value="delivered" {{ $box->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        </select>
                    </div>

                    <button class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
