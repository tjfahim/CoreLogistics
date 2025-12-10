@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title"><h1>Add Cost Type</h1></div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('cost-types.index') }}">Cost Types</a></li>
                    <li class="active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    <div class="col-lg-12">
        <div class="card">
            
            <div class="card-header d-flex justify-content-between">
                <strong>Add New Cost Type</strong>
                <a href="{{ route('cost-types.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">

                <form action="{{ route('cost-types.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="font-weight-bold">Name</label>
                        <input type="text" name="name"
                            value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="e.g. Transport Cost">

                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Type</label>
                        <select name="type" class="form-control">
                            <option value="fixed">Fixed</option>
                            <option value="per_kg">Per KG</option>
                        </select>
                    </div>
                  <div class="form-group">
                        <label class="font-weight-bold">Have Agent</label>
                        <select name="have_agent" class="form-control">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Have Partial Payment</label>
                        <select name="have_partial_payment" class="form-control">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Description</label>
                        <textarea name="description" rows="3"
                                  class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <button class="btn btn-primary btn-sm px-3">
                        <i class="fa fa-save"></i> Save Cost Type
                    </button>

                </form>

            </div>

        </div>
    </div>

</div>

@endsection
