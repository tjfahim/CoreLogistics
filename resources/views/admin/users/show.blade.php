@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">User Details</strong>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm float-right">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <img src="{{ $user->image_url }}" alt="{{ $user->name }}" 
                                     class="rounded-circle img-fluid" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                                <h4 class="mt-3">{{ $user->name }}</h4>
                                <span class="badge {{ $user->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="col-md-9">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200">ID</th>
                                        <td>{{ $user->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Role</th>
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
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge {{ $user->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                                {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ $user->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{ $user->updated_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                </table>
                                <div class="mt-3">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user->id) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection