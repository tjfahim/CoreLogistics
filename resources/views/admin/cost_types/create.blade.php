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

                {{-- ✅ Guard entire form --}}
                @cannot('add-cost-types')
                    <div class="alert alert-danger">
                        You do not have permission to add cost types.
                    </div>
                @endcannot

                @can('add-cost-types')
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
                                <option value="fixed" {{ old('type')=='fixed' ? 'selected' : '' }}>Fixed</option>
                                <option value="per_kg" {{ old('type')=='per_kg' ? 'selected' : '' }}>Per KG</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Have Agent</label>
                            <select name="have_agent" class="form-control">
                                <option value="0" {{ old('have_agent')=='0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('have_agent')=='1' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Have Partial Payment</label>
                            <select name="have_partial_payment" class="form-control">
                                <option value="0" {{ old('have_partial_payment')=='0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('have_partial_payment')=='1' ? 'selected' : '' }}>Yes</option>
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
                @endcan

            </div>

        </div>
    </div>

</div>

@endsection