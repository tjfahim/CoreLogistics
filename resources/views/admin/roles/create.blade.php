@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Create New Role</strong>
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm float-right">
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

                        <form action="{{ route('roles.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Role Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="status"
                                                class="form-control @error('status') is-invalid @enderror" required>
                                            <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea name="description" id="description" rows="3"
                                                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Permissions --}}
                            {{-- Permissions grouped by group_name --}}
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Assign Permissions</strong>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPermissions">
                <i class="fa fa-check-square-o"></i> Select All
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllPermissions">
                <i class="fa fa-square-o"></i> Deselect All
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
          

            @forelse($permissionsByGroup as $groupName => $permissions)
    <div class="col-md-6 mb-3">
        <div class="border rounded p-2 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong class="text-primary">{{ $groupName }}</strong>
                <button type="button"
                        class="btn btn-sm btn-link toggle-group"
                        data-group="{{ Str::slug($groupName) }}">
                    Toggle
                </button>
            </div>
            <div class="group-permissions" data-group="{{ Str::slug($groupName) }}">
                @foreach($permissions as $permission)
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox"
                               class="custom-control-input permission-checkbox"
                               name="permissions[]"
                               id="perm_{{ $permission->id }}"
                               value="{{ $permission->id }}"
                               {{ in_array($permission->id, old('permissions', $rolePermissions ?? [])) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="perm_{{ $permission->id }}">
                            {{ $permission->name }}
                            <small class="text-muted d-block">({{ $permission->slug }})</small>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center text-muted">
        No permissions found. <a href="{{ route('permissions.create') }}">Create one</a>.
    </div>
@endforelse
        </div>
    </div>
</div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Create Role
                                </button>
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // Select all permissions
        $('#selectAllPermissions').on('click', function () {
            $('.permission-checkbox').prop('checked', true);
        });

        // Deselect all permissions
        $('#deselectAllPermissions').on('click', function () {
            $('.permission-checkbox').prop('checked', false);
        });

        // Toggle group
        $('.toggle-group').on('click', function () {
            var groupId = $(this).data('group');
            var checkboxes = $('.group-permissions[data-group="' + groupId + '"] .permission-checkbox');
            var allChecked = checkboxes.length === checkboxes.filter(':checked').length;
            checkboxes.prop('checked', !allChecked);
        });
    });
</script>
@endsection