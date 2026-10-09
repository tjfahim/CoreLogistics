@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">

            {{-- Group sidebar --}}
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Groups</strong>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('permissions.index') }}"
                           class="list-group-item list-group-item-action {{ !request('group') ? 'active' : '' }}">
                            All Permissions
                            <span class="badge badge-secondary float-right">{{ $groupCounts->sum() }}</span>
                        </a>
                        @foreach($groupNames as $groupName)
                            <a href="{{ route('permissions.index', ['group' => $groupName]) }}"
                               class="list-group-item list-group-item-action {{ request('group') == $groupName ? 'active' : '' }}">
                                {{ $groupName }}
                                <span class="badge badge-secondary float-right">
                                    {{ $groupCounts[$groupName] ?? 0 }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Permissions table --}}
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong class="card-title">
                            Permissions
                            @if(request('group'))
                                - <span class="text-primary">{{ request('group') }}</span>
                            @endif
                        </strong>
                        <div>
                            <a href="{{ route('permissions.bulk-store') }}" class="btn btn-outline-primary btn-sm"
                               data-toggle="modal" data-target="#bulkModal">
                                <i class="fa fa-layer-group"></i> Bulk Create
                            </a>
                            <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Permission
                            </a>
                        </div>
                    </div>
                    <div class="card-body">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        {{-- Search --}}
                        <form method="GET" action="{{ route('permissions.index') }}" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search name, slug, or group..."
                                       value="{{ request('search') }}">
                                @if(request('group'))
                                    <input type="hidden" name="group" value="{{ request('group') }}">
                                @endif
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                    @if(request('search') || request('group'))
                                        <a href="{{ route('permissions.index') }}" class="btn btn-outline-danger">
                                            <i class="fa fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th width="200">Group</th>
                                    <th width="100">Roles</th>
                                    <th width="170">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $key => $permission)
                                <tr>
                                    <td>{{ $permissions->firstItem() + $key }}</td>
                                    <td>{{ $permission->name }}</td>
                                    <td><code>{{ $permission->slug }}</code></td>
                                    <td>
                                        <a href="{{ route('permissions.index', ['group' => $permission->group_name]) }}"
                                           class="badge badge-info">
                                            {{ $permission->group_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ $permission->roles_count ?? $permission->roles()->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('permissions.show', $permission->id) }}"
                                               class="btn btn-info btn-sm" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('permissions.edit', $permission->id) }}"
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('permissions.destroy', $permission->id) }}"
                                                  method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No permissions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $permissions->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Create Modal --}}
<div class="modal fade" id="bulkModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('permissions.bulk-store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Create Permissions</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="group_name" class="form-control"
                               list="groupList" value="{{ request('group') }}" required>
                        <datalist id="groupList">
                            @foreach($groupNames as $g)
                                <option value="{{ $g }}">
                            @endforeach
                        </datalist>
                    </div>

                    <label>Permissions <span class="text-danger">*</span></label>
                    <div id="bulkRows">
                        <div class="input-group mb-2">
                            <input type="text" name="permissions[0][name]" class="form-control"
                                   placeholder="e.g. view-users" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-danger remove-row">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addRow">
                        <i class="fa fa-plus"></i> Add another
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // Delete confirm
        $('.delete-form').on('submit', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this permission?')) {
                this.submit();
            }
        });

        // Bulk rows
        var rowIndex = 1;
        $('#addRow').on('click', function () {
            var html = '<div class="input-group mb-2">' +
                '<input type="text" name="permissions[' + rowIndex + '][name]" class="form-control" placeholder="e.g. edit-users" required>' +
                '<div class="input-group-append">' +
                '<button type="button" class="btn btn-outline-danger remove-row"><i class="fa fa-times"></i></button>' +
                '</div></div>';
            $('#bulkRows').append(html);
            rowIndex++;
        });

        $(document).on('click', '.remove-row', function () {
            if ($('#bulkRows .input-group').length > 1) {
                $(this).closest('.input-group').remove();
            }
        });
    });
</script>
@endsection