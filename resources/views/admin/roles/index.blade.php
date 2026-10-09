@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong class="card-title">Role Management</strong>
                        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Add New Role
                        </a>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Description</th>
                                    <th width="100">Permissions</th>
                                    <th width="80">Users</th>
                                    <th width="100">Status</th>
                                    <th width="170">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $key => $role)
                                <tr>
                                    <td>{{ $roles->firstItem() + $key }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td><code>{{ $role->slug }}</code></td>
                                    <td>{{ Str::limit($role->description, 50) ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $role->permissions_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $role->users_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $role->status ? 'badge-success' : 'badge-danger' }} status-badge"
                                              data-id="{{ $role->id }}" style="cursor:pointer;">
                                            {{ $role->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('roles.show', $role->id) }}"
                                               class="btn btn-info btn-sm" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('roles.edit', $role->id) }}"
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('roles.destroy', $role->id) }}"
                                                  method="POST"
                                                  class="d-inline delete-form">
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
                                    <td colspan="8" class="text-center">No roles found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $roles->links('pagination::bootstrap-4') }}
                        </div>
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
        // Delete confirm
        $('.delete-form').on('submit', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this role?')) {
                this.submit();
            }
        });

        // Toggle status via AJAX
        $('.status-badge').on('click', function () {
            var badge = $(this);
            var roleId = badge.data('id');

            $.ajax({
                url: '/admin/roles/' + roleId + '/toggle-status',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    if (response.success) {
                        if (response.status == 1) {
                            badge.removeClass('badge-danger').addClass('badge-success').text('Active');
                        } else {
                            badge.removeClass('badge-success').addClass('badge-danger').text('Inactive');
                        }
                    }
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.error || 'Something went wrong');
                }
            });
        });
    });
</script>
@endsection