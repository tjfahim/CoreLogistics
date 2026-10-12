@extends('admin.layouts.master')
@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4"><div class="page-header float-left"><div class="page-title"><h1>Add Office</h1></div></div></div>
    <div class="col-sm-8"><div class="page-header float-right"><div class="page-title">
        <ol class="breadcrumb text-right">
            <li><a href="{{ route('offices.index') }}">Offices</a></li>
            <li class="active">Add</li>
        </ol>
    </div></div></div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Add New Office</strong>
                <a href="{{ route('offices.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('offices.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Location</label>
                        <input type="text" name="location" value="{{ old('location') }}"
                               class="form-control @error('location') is-invalid @enderror">
                        @error('location')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <button class="btn btn-primary btn-sm px-3"><i class="fa fa-save"></i> Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection