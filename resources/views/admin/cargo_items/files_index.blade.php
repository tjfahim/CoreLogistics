@extends('admin.layouts.master')

@section('main_content')
<div class="mb-2 no-print">
    <a href="{{ route('cargo-items.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Warehouse</a>
</div>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Imported Excel Files</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('cargo-items.create') }}">Add Items</a></li>
                    <li class="active">Imported Files</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show">
                <span class="badge badge-pill badge-success">Success</span>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible fade show">
                <span class="badge badge-pill badge-danger">Error</span>
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Imported Excel Files</strong>

                {{-- ✅ Upload New — only if user can import --}}
                @can('import-warehouse-file')
                    <a href="{{ route('cargo-items.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Upload New
                    </a>
                @endcan
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>File Name</th>
                            <th>Note</th>
                            <th>Total</th>
                            <th>Imported</th>
                            <th>Duplicates</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($files as $f)
                        <tr>
                            <td>{{ $f->id }}</td>
                            <td>{{ $f->title }}</td>
                            <td>
                                <i class="fa fa-file-excel-o text-success"></i>
                                {{ $f->file_name }}
                            </td>
                            <td>{{ Str::limit($f->note, 40) ?? '—' }}</td>
                            <td><span class="badge badge-info">{{ $f->total_rows }}</span></td>
                            <td><span class="badge badge-success">{{ $f->imported_rows }}</span></td>
                            <td>
                                @if($f->duplicate_rows > 0)
                                    <span class="badge badge-warning">{{ $f->duplicate_rows }}</span>
                                @else
                                    <span class="badge badge-secondary">0</span>
                                @endif
                            </td>
                            <td>{{ $f->uploader->name ?? '—' }}</td>
                            <td>{{ $f->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @canany(['view-warehouse-item', 'import-warehouse-file', 'delete-warehouse-item'])
                                    {{-- ✅ View --}}
                                    @can('view-warehouse-item')
                                        <a href="{{ route('cargo-items.files.view', $f->id) }}"
                                           class="btn btn-info btn-sm" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan

                                    {{-- ✅ Download --}}
                                    @can('view-warehouse-item')
                                        <a href="{{ route('cargo-items.files.download', $f->id) }}"
                                           class="btn btn-secondary btn-sm" title="Download">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    @endcan

                                    {{-- ✅ Delete --}}
                                    @can('delete-warehouse-item')
                                        <form action="{{ route('cargo-items.files.destroy', $f->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this file permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" title="Delete">
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
                            <td colspan="10" class="text-center text-muted">No imported files yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $files->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection