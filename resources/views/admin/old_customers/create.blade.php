@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Add New Customer</strong>
                <a href="{{ route('old-customers.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('old-customers.store') }}" method="POST">
                    @csrf
                    @include('admin.old_customers._form', ['customer' => null])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
