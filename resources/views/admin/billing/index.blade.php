@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title"><h1>Shipment Billing</h1></div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="active">Shipment Billing</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    @if(session('success'))<div class="col-sm-12"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
    @if(session('error'))<div class="col-sm-12"><div class="alert alert-danger">{{ session('error') }}</div></div>@endif

    {{-- Summary --}}
    <div class="col-sm-12">
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-primary text-white"><div class="card-body">
                    <h6>Total Shipment Cost (BDT)</h6><h3>{{ number_format($grand['cost'], 2) }}</h3>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white"><div class="card-body">
                    <h6>Total Paid</h6><h3>{{ number_format($grand['paid'], 2) }}</h3>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white"><div class="card-body">
                    <h6>Total Due (we owe)</h6><h3>{{ number_format($grand['due'], 2) }}</h3>
                </div></div>
            </div>
        </div>
    </div>

    {{-- Shipments --}}
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Shipment Bills</strong>
                <div>
                    @if($onlyDue)
                        <a href="{{ route('shipment-bills.index') }}" class="btn btn-sm btn-outline-secondary">Show all</a>
                    @else
                        <a href="{{ route('shipment-bills.index', ['only_due' => 1]) }}" class="btn btn-sm btn-outline-danger">Only with due</a>
                    @endif
                    <a href="{{ route('shipment-bills.ledger') }}" class="btn btn-sm btn-info"><i class="fa fa-book"></i> Bill Ledger</a>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel-o"></i> Excel</a> <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf-o"></i> PDF</a>
                    @can('add-bill-payment')
                        <a href="{{ route('shipment-bills.create') }}" class="btn btn-sm btn-success">
                            <i class="fa fa-money"></i> Pay Multiple Shipments
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body table-responsive">
                <table id="billTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Shipment</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Cost Lines</th>
                            <th class="text-right">Total Cost</th>
                            <th class="text-right">Paid</th>
                            <th class="text-right">Due</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shipments as $s)
                            @php $b = $s->bill; @endphp
                            <tr>
                                <td><strong>{{ $s->shipment_name }}</strong></td>
                                <td>{{ $s->shipment_date ? $s->shipment_date->format('Y-m-d') : '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ $s->status == 'draft' ? 'secondary' : ($s->status == 'pending' ? 'warning' : 'success') }}">{{ ucfirst($s->status) }}</span>
                                </td>
                                <td>{{ $b['lines'] }} @if($b['due_lines'])<small class="text-danger">({{ $b['due_lines'] }} unpaid)</small>@endif</td>
                                <td class="text-right">{{ number_format($b['total_cost'], 2) }}</td>
                                <td class="text-right text-success">{{ number_format($b['total_paid'], 2) }}</td>
                                <td class="text-right text-danger"><strong>{{ number_format($b['total_due'], 2) }}</strong></td>
                                <td>
                                    @can('view-shipment-cost')
                                        <a href="{{ route('itemshipments.costs.show', $s->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-list"></i> Manage Bills
                                        </a>
                                    @endcan
                                    @can('add-bill-payment')
                                        @if($b['total_due'] > 0)
                                            <a href="{{ route('shipment-bills.create', ['shipment' => $s->id]) }}" class="btn btn-sm btn-success">
                                                <i class="fa fa-money"></i> Pay
                                            </a>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Vouchers --}}
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header"><strong class="card-title">Recent Payment Vouchers</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Voucher</th><th>Date</th><th>Method</th><th>Reference</th><th>Paid To</th>
                            <th>Lines</th><th class="text-right">Amount</th><th>Saved By</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $v)
                            <tr>
                                <td><strong>{{ $v->voucher_no ?? ('#' . $v->id) }}</strong></td>
                                <td>{{ optional($v->payment_date)->format('Y-m-d') }}</td>
                                <td>{{ \App\Http\Controllers\ShipmentBillController::METHODS[$v->payment_method] ?? ucfirst($v->payment_method) }}</td>
                                <td>{{ $v->reference_no ?? '—' }}</td>
                                <td>{{ optional($v->agent)->name ?? '—' }}</td>
                                <td>{{ $v->payments_count }}</td>
                                <td class="text-right"><strong>{{ number_format($v->amount, 2) }}</strong></td>
                                <td>{{ optional($v->user)->name ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('shipment-bills.show', $v->id) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                                    @can('delete-bill-payment')
                                        <form action="{{ route('shipment-bills.destroy', $v->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete voucher {{ $v->voucher_no }}? All its lines are removed and shipment dues go back up.');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">No multi-shipment vouchers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <small class="text-muted">Single payments made from a shipment's cost page are not vouchers; they appear in that shipment's payment history.</small>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#billTable').DataTable({ pageLength: 50, lengthMenu: [25, 50, 100, 500], order: [[1, 'desc']] });
});
</script>
@endsection
