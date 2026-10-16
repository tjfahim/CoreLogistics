@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Edit Shipment: {{ $shipment->name }}</strong>
                <a href="{{ route('old-shipments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('old-shipments.update', $shipment->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.old_shipments._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
