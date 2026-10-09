@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Agent Manage</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Agents</li>
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
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Agent List</strong>

                {{-- ✅ Add Agent --}}
                @can('add-agents')
                    <a href="{{ route('agents.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add Agent
                    </a>
                @endcan
            </div>

            <div class="card-body">

                <table id="agentTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($agents as $a)
                        <tr>
                            <td>{{ $a->id }}</td>
                            <td>{{ $a->name }}</td>
                            <td>
                                @if($a->type == 'freight_forwarder')
                                    <span class="badge badge-info">Freight Forwarder</span>
                                @else
                                    <span class="badge badge-warning">CNF Agent</span>
                                @endif
                            </td>
                            <td>{{ $a->company_name }}</td>
                            <td>{{ $a->email }}</td>
                            <td>{{ $a->phone }}</td>
                            <td>{{ $a->address }}</td>

                            <td>
                                @canany(['edit-agents', 'delete-agents'])
                                    {{-- ✅ Edit --}}
                                    @can('edit-agents')
                                        <a href="{{ route('agents.edit', $a->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                    @endcan

                                    {{-- ✅ Delete --}}
                                    @can('delete-agents')
                                        <form action="{{ route('agents.destroy', $a->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    <span class="text-muted">—</span>
                                @endcanany
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No agents found</td>
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
    $('#agentTable').DataTable({
        "order": [[0, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": 7 }
        ],
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50],
        "responsive": true
    });

    // Confirm before delete
    $('.delete-form').on('submit', function (e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this agent?')) {
            this.submit();
        }
    });
});
</script>

@endsection