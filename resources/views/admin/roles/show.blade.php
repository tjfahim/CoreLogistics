@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong class="card-title">Role Details: {{ $role->name }}</strong>
                        <div>
                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-warning btn-sm">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="150">Name</th>
                                        <td>{{ $role->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Slug</th>
                                        <td><code>{{ $role->slug }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Description</th>
                                        <td>{{ $role->description ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge {{ $role->status ? 'badge-success' : 'badge-danger' }}">
                                                {{ $role->status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created</th>
                                        <td>{{ $role->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="text-muted">Total Permissions</h5>
                                        <h2 class="text-primary">{{ $role->permissions->count() }}</h2>
                                    </div>
                                </div>
                                <div class="card bg-light mt-2">
                                    <div class="card-body text-center">
                                        <h5 class="text-muted">Assigned Users</h5>
                                        <h2 class="text-info">{{ $role->users->count() }}</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Permissions grouped --}}
                        <h5 class="mb-3">Assigned Permissions</h5>
<div class="row">
    @forelse($permissionsByGroup as $groupName => $permissions)
        <div class="col-md-6 mb-3">
            <div class="border rounded p-3 h-100">
                <h6 class="text-primary">{{ $groupName }}</h6>
                <hr class="my-2">
                <ul class="list-unstyled mb-0">
                    @foreach($permissions as $permission)
                        <li class="mb-1">
                            <i class="fa fa-check-circle text-success"></i>
                            {{ $permission->name }}
                            <small class="text-muted">({{ $permission->slug }})</small>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning">
                No permissions assigned to this role.
            </div>
        </div>
    @endforelse
</div>
                        {{-- Assigned users --}}
                        <h5 class="mb-3 mt-4">Assigned Users ({{ $role->users->count() }})</h5>
                        @if($role->users->count() > 0)
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->users as $i => $user)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                <span class="badge {{ $user->status ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $user->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-info">No users assigned to this role.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection