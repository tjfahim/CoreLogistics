@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong class="card-title">User Management</strong>

                        {{-- ✅ Create button --}}
                        @can('create-users')
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add New User
                            </a>
                        @endcan
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

                        <table class="table table-striped table-bordered" id="userTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $key => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $key }}</td>
                                    <td>
                                        <img src="{{ $user->image_url }}"
                                             alt="{{ $user->name }}"
                                             class="rounded-circle"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->roleRelation)
                                            @php
                                                $badgeClass = match($user->roleRelation->slug) {
                                                    'superadmin' => 'badge-dark',
                                                    'admin'      => 'badge-success',
                                                    'manager'    => 'badge-primary',
                                                    default      => 'badge-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ $user->roleRelation->name }}
                                            </span>
                                        @elseif($user->role == 2)
                                            <span class="badge badge-success">Admin (legacy)</span>
                                        @else
                                            <span class="badge badge-secondary">User</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- ✅ Toggle Status --}}
                                        @can('toggle-user-status')
                                            <span class="badge {{ $user->status == 1 ? 'badge-success' : 'badge-danger' }} status-badge"
                                                  data-id="{{ $user->id }}"
                                                  style="cursor:pointer;">
                                                {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        @else
                                            <span class="badge {{ $user->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                                {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        @endcan
                                    </td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            {{-- ✅ View --}}
                                            @can('view-users')
                                                <a href="{{ route('users.show', $user->id) }}"
                                                   class="btn btn-info btn-sm" title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            @endcan

                                            {{-- ✅ Edit --}}
                                            @can('edit-users')
                                                <a href="{{ route('users.edit', $user->id) }}"
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endcan

                                            {{-- ✅ Delete --}}
                                            @can('delete-users')
                                                @if(auth()->id() !== $user->id)
                                                <form action="{{ route('users.destroy', $user->id) }}"
                                                      method="POST"
                                                      class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $users->links('pagination::bootstrap-4') }}
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
    $(document).ready(function() {
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this user?')) {
                this.submit();
            }
        });

        $('.status-badge').on('click', function() {
            var badge = $(this);
            var userId = badge.data('id');

            $.ajax({
                url: '/admin/users/' + userId + '/toggle-status',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        if (response.status == 1) {
                            badge.removeClass('badge-danger').addClass('badge-success').text('Active');
                        } else {
                            badge.removeClass('badge-success').addClass('badge-danger').text('Inactive');
                        }
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.error || 'Something went wrong');
                }
            });
        });
    });
</script>
@endsection