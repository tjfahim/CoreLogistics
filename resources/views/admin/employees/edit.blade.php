@extends('admin.layouts.master')
@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4"><div class="page-header float-left"><div class="page-title"><h1>Edit Employee</h1></div></div></div>
    <div class="col-sm-8"><div class="page-header float-right"><div class="page-title">
        <ol class="breadcrumb text-right">
            <li><a href="{{ route('employees.index') }}">Employees</a></li>
            <li class="active">Edit</li>
        </ol>
    </div></div></div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Edit Employee</strong>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('employees.update', $employee->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label class="font-weight-bold">Name</label>
                        <input type="text" name="name" value="{{ old('name', $employee->name) }}"
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Office</label>
                        <select name="office_id" class="form-control @error('office_id') is-invalid @enderror">
                            @foreach($offices as $o)
                                <option value="{{ $o->id }}" {{ old('office_id', $employee->office_id)==$o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                            @endforeach
                        </select>
                        @error('office_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Salary (BDT)</label>
                        <input type="number" step="0.01" min="0" name="salary"
                               value="{{ old('salary', $employee->salary) }}"
                               class="form-control @error('salary') is-invalid @enderror">
                        @error('salary')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <button class="btn btn-primary btn-sm px-3"><i class="fa fa-save"></i> Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection