@extends('admin.layouts.master')
@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4"><div class="page-header float-left"><div class="page-title"><h1>Office Bills</h1></div></div></div>
    <div class="col-sm-8"><div class="page-header float-right"><div class="page-title">
        <ol class="breadcrumb text-right">
            <li><a href="{{ route('office-bills.index') }}">Office Bills</a></li>
            <li class="active">Office Bills</li>
        </ol>
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
                    <i class="fa fa-plus"></i> Add Office Bills
                </a>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline mb-3">
                    <select name="office_id" class="form-control form-control-sm mr-2">
                        <option value="">All offices</option>
                        @foreach($offices as $o)<option value="{{ $o->id }}" {{ request('office_id')==$o->id ? 'selected':'' }}>{{ $o->name }}</option>@endforeach
                    </select>
                    <label class="mr-1 mb-0">From</label><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm mr-2">
                    <label class="mr-1 mb-0">To</label><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm mr-2">
                    <button class="btn btn-secondary btn-sm mr-1"><i class="fa fa-filter"></i> Filter</button>
                    <a href="{{ route('office-bills.index') }}" class="btn btn-light btn-sm">Reset</a>
                </form>

                <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th><th>Date</th><th>Office</th><th>Type</th>
                            <th>Amount (BDT)</th><th>Note</th><th width="160">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $b)
                        <tr>
                            <td>{{ $b->id }}</td>
                            <td>{{ optional($b->bill_date)->format('d M Y') }}</td>
                            <td>{{ $b->office->name ?? '—' }}</td>
                            <td>{{ $b->bill_type ?? '—' }}</td>
                            <td class="text-right">{{ number_format($b->amount, 2) }}</td>
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
                    <tfoot><tr class="font-weight-bold"><td colspan="4" class="text-right">Total ({{ $bills->count() }} bills)</td><td class="text-right">{{ number_format($total, 2) }}</td><td colspan="2"></td></tr></tfoot>
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
