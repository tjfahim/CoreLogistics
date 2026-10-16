@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong class="card-title">Payments &amp; Collections</strong>
                        <div>
                            <a href="{{ route('old-payments.report') }}" class="btn btn-info btn-sm">
                                <i class="fa fa-bar-chart"></i> Monthly Report
                            </a>
                            <a href="{{ route('old-payments.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Entry
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        {{-- Filters --}}
                        <form method="GET" class="form-inline mb-3">
                            <select name="type" class="form-control form-control-sm mr-2">
                                <option value="">All types</option>
                                <option value="paid" @selected(request('type') === 'paid')>Paid (shipment bill)</option>
                                <option value="received" @selected(request('type') === 'received')>Received (customer)</option>
                            </select>
                            <select name="old_shipment_id" class="form-control form-control-sm mr-2">
                                <option value="">All shipments</option>
                                @foreach($shipments as $s)
                                    <option value="{{ $s->id }}" @selected(request('old_shipment_id') == $s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                            <select name="old_customer_id" class="form-control form-control-sm mr-2">
                                <option value="">All customers</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" @selected(request('old_customer_id') == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-secondary btn-sm">Filter</button>
                            <a href="{{ route('old-payments.index') }}" class="btn btn-link btn-sm">Reset</a>
                        </form>

                        <div class="row mb-3">
                            <div class="col-md-4"><strong>Total paid:</strong> <span class="text-danger">{{ number_format($totalPaid, 2) }}</span></div>
                            <div class="col-md-4"><strong>Total received:</strong> <span class="text-success">{{ number_format($totalReceived, 2) }}</span></div>
                            <div class="col-md-4"><strong>Net:</strong> {{ number_format($totalReceived - $totalPaid, 2) }}</div>
                        </div>

                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th><th>Date</th><th>Type</th><th>Shipment</th><th>Customer</th>
                                    <th>Method</th><th>Note</th><th class="text-right">Amount</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $key => $p)
                                <tr>
                                    <td>{{ $payments->firstItem() + $key }}</td>
                                    <td>{{ $p->date->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge {{ $p->type === 'paid' ? 'badge-danger' : 'badge-success' }}">
                                            {{ $p->type === 'paid' ? 'Paid' : 'Received' }}
                                        </span>
                                    </td>
                                    <td>{{ $p->shipment->name ?? '—' }}</td>
                                    <td>{{ $p->customer->name ?? '—' }}</td>
                                    <td>{{ $p->method }}</td>
                                    <td>{{ $p->note }}</td>
                                    <td class="text-right">{{ number_format($p->amount, 2) }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('old-payments.edit', $p->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('old-payments.destroy', $p->id) }}" method="POST" class="d-inline delete-form">
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
                                <tr><td colspan="9" class="text-center text-muted">No entries found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $payments->links('pagination::bootstrap-4') }}
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
            if (confirm('Are you sure you want to delete this entry?')) {
                this.submit();
            }
        });
    });
</script>
@endsection
