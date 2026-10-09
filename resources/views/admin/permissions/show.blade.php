@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong class="card-title">Permission: {{ $permission->name }}</strong>
                        <div>
                            <a href="{{ route('permissions.edit', $permission->id) }}"
                               class="btn btn-warning btn-sm">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('permissions.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="150">Name</th>
                                        <td>{{ $permission->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Slug</th>
                                        <td><code>{{ $permission->slug }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Group</th>
                                        <td>
                                            <a href="{{ route('permissions.index', ['group' => $permission->group_name]) }}"
                                               class="badge badge-info">
                                                {{ $permission->group_name }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created</th>
                                        <td>{{ $permission->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="text-muted">Assigned to Roles</h5>
                                        <h2 class="text-primary">{{ $permission->roles->count() }}</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-3 mb-3">Roles using this permission</h5>
                        @if($permission->roles->count())
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permission->roles as $i => $role)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $role->name }}</td>
                                            <td><code>{{ $role->slug }}</code></td>
                                            <td>
                                                <span class="badge {{ $role->status ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $role->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-info">This permission is not assigned to any role yet.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection