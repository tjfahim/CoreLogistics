@extends('admin.layouts.master')
@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4"><div class="page-header float-left"><div class="page-title"><h1>Office Bills</h1></div></div></div>
    <div class="col-sm-8"><div class="page-header float-right"><div class="page-title">
        <ol class="breadcrumb text-right"><li class="active">Office Bills</li></ol>
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
                <strong>Office Bill List</strong>
                <a href="{{ route('office-bills.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add Office Bill
                </a>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th><th>Office</th><th>Year</th><th>Month</th>
                            <th>Amount (BDT)</th><th>Note</th><th width="160">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $b)
                        <tr>
                            <td>{{ $b->id }}</td>
                            <td>{{ $b->office->name ?? '—' }}</td>
                            <td>{{ $b->year }}</td>
                            <td>{{ $b->month_name }}</td>
                            <td>{{ number_format($b->amount, 2) }}</td>
                            <td>{{ $b->note }}</td>
                            <td>
                                <a href="{{ route('office-bills.edit', $b->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                <form action="{{ route('office-bills.destroy', $b->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted">No office bills yet</td></tr>
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
    if (confirm('Delete this office bill?')) f.submit();
}));
</script>
@endsection