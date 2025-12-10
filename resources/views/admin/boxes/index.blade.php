@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Box Management</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Boxes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Box List</strong>
                <a href="{{ route('boxes.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add Box
                </a>
            </div>
            <div class="card-body">
                <table id="boxesTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Box Code</th>
                            <th>Total Weight (kg)</th>
                            <th>Status</th>
                            <th>Products</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boxes as $box)
                        <tr>
                            <td>{{ $box->id }}</td>
                            <td>{{ $box->box_code }}</td>
                            <td>{{ $box->total_weight }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $box->status)) }}</td>
                            <td>
                                @foreach($box->products as $product)
                                    <span class="badge bg-secondary">
                                        {{ $product->name }} ({{ $product->pivot->weight_in_box }} kg)
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                <!-- <a href="{{ route('boxes.edit', $box->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a> -->
                                <form action="{{ route('boxes.destroy', $box->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No boxes found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#boxesTable').DataTable();
    });
</script>
@endsection
