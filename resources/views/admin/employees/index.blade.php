@extends('admin.layouts.master')
@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4"><div class="page-header float-left"><div class="page-title"><h1>Employees</h1></div></div></div>
    <div class="col-sm-8"><div class="page-header float-right"><div class="page-title">
        <ol class="breadcrumb text-right"><li class="active">Employee</li></ol>
    </div></div></div>
</div>

<div class="content mt-3">
    @if(session('success'))
        <div class="col-sm-12"><div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button class="close" data-dismiss="alert"><span>&times;</span></button>
        </div></div>
    @endif

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Employee List</strong>
                <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add Employee
                </a>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr><th>ID</th><th>Name</th><th>Office</th><th>Salary (BDT)</th><th width="160">Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $e)
                        <tr>
                            <td>{{ $e->id }}</td>
                            <td>{{ $e->name }}</td>
                            <td>{{ $e->office->name ?? '—' }}</td>
                            <td>{{ number_format($e->salary, 2) }}</td>
                            <td>
                                <a href="{{ route('employees.edit', $e->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                <form action="{{ route('employees.destroy', $e->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">No employees yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.delete-form').forEach(f => f.addEventListener('submit', e => {
    e.preventDefault();
    if (confirm('Delete this employee?')) f.submit();
}));
</script>
@endsection