@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Edit Payment / Collection</strong>
                <a href="{{ route('old-payments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('old-payments.update', $payment->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.old_payments._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
