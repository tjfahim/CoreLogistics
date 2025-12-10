@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Cost Type Manage</h1>
            </div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Cost Types</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="badge badge-pill badge-success">Success</span>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Cost Type List</strong>

                <a href="{{ route('cost-types.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add Cost Type
                </a>
            </div>

            <div class="card-body">
                <table id="costTypeTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Have Agent</th>
                            <th>Have Partial Payment</th>
                            <th>Description</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($costTypes as $ct)
                        <tr>
                            <td>{{ $ct->id }}</td>
                            <td>{{ $ct->name }}</td>
                            <td>{{ ucfirst($ct->type) }}</td>
                            <td>
                                @if($ct->have_agent)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-danger">No</span>
                                @endif
                            </td>                            
                            <td>
                                @if($ct->have_partial_payment)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-danger">No</span>
                                @endif
                            </td>                            
                            <td>{{ $ct->description }}</td>
                            <td>
                                <a href="{{ route('cost-types.edit', $ct->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>

                                <form action="{{ route('cost-types.destroy', $ct->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No Cost Types Found</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

<script>
$(document).ready(function() {
    $('#costTypeTable').DataTable({
        "order": [[0, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": 4 }
        ]
    });
});
</script>

@endsection
