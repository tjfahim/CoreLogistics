@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Create Permission</strong>
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary btn-sm float-right">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('permissions.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}"
                                               placeholder="e.g. Create Users" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="slug">Slug <small class="text-muted">(auto if empty)</small></label>
                                        <input type="text" name="slug" id="slug"
                                               class="form-control @error('slug') is-invalid @enderror"
                                               value="{{ old('slug') }}"
                                               placeholder="e.g. create-users">
                                        @error('slug')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="group_name">Group Name <span class="text-danger">*</span></label>
                                        <input type="text" name="group_name" id="group_name"
                                               class="form-control @error('group_name') is-invalid @enderror"
                                               value="{{ old('group_name') }}"
                                               list="groupList"
                                               placeholder="e.g. User Management" required>
                                        <datalist id="groupList">
                                            @foreach($groupNames as $g)
                                                <option value="{{ $g }}">
                                            @endforeach
                                        </datalist>
                                        <small class="text-muted">
                                            Permissions with the same group will be displayed together.
                                        </small>
                                        @error('group_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Create Permission
                            </button>
                            <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection