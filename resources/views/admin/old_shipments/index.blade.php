@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong class="card-title">Old Shipments</strong>
                        <a href="{{ route('old-shipments.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Add New Shipment
                        </a>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        <form method="GET" class="form-inline mb-3">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2"
                                   placeholder="Search name / shipping mark">
                            <button class="btn btn-secondary btn-sm">Search</button>
                            @if(request('q'))
                                <a href="{{ route('old-shipments.index') }}" class="btn btn-link btn-sm">Clear</a>
                            @endif
                        </form>

                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Shipping Mark</th>
                                    <th class="text-right">Items</th>
                                    <th class="text-right">Shipment Bill</th>
                                    <th class="text-right">Customer Bill</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shipments as $key => $shipment)
                                <tr>
                                    <td>{{ $shipments->firstItem() + $key }}</td>
                                    <td>{{ $shipment->name }}</td>
                                    <td>{{ $shipment->date->format('d M Y') }}</td>
                                    <td>{{ $shipment->shipping_mark }}</td>
                                    <td class="text-right">{{ $shipment->items_count }}</td>
                                    <td class="text-right">{{ number_format($shipment->bills_sum ?? 0, 2) }}</td>
                                    <td class="text-right">{{ number_format($shipment->items_sum ?? 0, 2) }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('old-shipments.show', $shipment->id) }}" class="btn btn-info btn-sm" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('old-shipments.edit', $shipment->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('old-shipments.destroy', $shipment->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted">No shipments found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $shipments->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            if (confirm('Delete this shipment with all its items, bills and payments?')) {
                this.submit();
            }
        });
    });
</script>
@endsection
