@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">
    <h1 class="h3 mb-3">Old Excel Uploads</h1>

    @foreach (['success','error'] as $m)
        @if(session($m)) <div class="alert alert-{{ $m === 'error' ? 'danger' : 'success' }}">{{ session($m) }}</div> @endif
    @endforeach
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="card shadow mb-4">
        <div class="card-header"><strong>Upload old Excel (the *_import_ready.xlsx file)</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('legacy.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Excel file (.xlsx)</label>
                        <input type="file" name="file" class="form-control-file" accept=".xlsx" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" placeholder="June 2026 deliveries">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Note</label>
                        <input type="text" name="note" class="form-control">
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-block">Upload &amp; Read</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                <tr>
                    <th>Title</th><th>Status</th><th class="text-right">Rows</th><th class="text-right">Errors</th>
                    <th class="text-right">Billed</th><th class="text-right">Paid</th><th class="text-right">Due</th>
                    <th>Uploaded</th><th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($uploads as $u)
                    <tr>
                        <td>{{ $u->title }}<br><small class="text-muted">{{ $u->file_name }}</small></td>
                        <td>
                            <span class="badge badge-{{ ['staged'=>'warning','approved'=>'info','imported'=>'success'][$u->status] }}">{{ ucfirst($u->status) }}</span>
                        </td>
                        <td class="text-right">{{ $u->total_rows }}</td>
                        <td class="text-right {{ $u->error_rows ? 'text-danger font-weight-bold' : '' }}">{{ $u->error_rows }}</td>
                        <td class="text-right">{{ number_format($u->total_billed, 0) }}</td>
                        <td class="text-right">{{ number_format($u->total_paid, 0) }}</td>
                        <td class="text-right">{{ number_format($u->total_due, 0) }}</td>
                        <td>{{ $u->created_at->format('d M Y H:i') }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('legacy.show', $u) }}" class="btn btn-sm btn-outline-primary">Review</a>
                            @if($u->status !== 'staged')
                                <a href="{{ route('legacy.report', $u) }}" class="btn btn-sm btn-info">Report</a>
                            @endif
                            <a href="{{ route('legacy.download', $u) }}" class="btn btn-sm btn-outline-secondary">File</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Nothing uploaded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
