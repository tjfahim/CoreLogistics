@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title"><h1>Ledger — {{ $customer->name }}</h1></div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('ledger.index') }}">Ledger</a></li>
                    <li class="active">{{ $customer->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Customer info --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Customer Info</strong>
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-warning">
                        <i class="fa fa-edit"></i> Edit Customer
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><strong>Code:</strong> {{ $customer->customer_code }}</div>
                        <div class="col-md-3"><strong>Phone:</strong> {{ $customer->phone }}</div>
                        <div class="col-md-3"><strong>Email:</strong> {{ $customer->email }}</div>
                        <div class="col-md-3"><strong>Address:</strong> {{ $customer->address ?? '—' }}</div>
                    </div>
                    @if(count($customer->all_shipping_marks) > 0)
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <strong>Shipping Marks:</strong>
                                @foreach($customer->all_shipping_marks as $mark)
                                    <span class="badge badge-info">{{ $mark }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row mt-3">
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Total Billed</h6><h3>Tk {{ number_format($totalBilled, 2) }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Total Paid</h6><h3 class="text-success">Tk {{ number_format($totalPaid, 2) }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Total Due</h6><h3 class="text-danger">Tk {{ number_format($totalDue, 2) }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Shipments</h6><h3>{{ $shipments->count() }}</h3>
            </div></div>
        </div>
    </div>

    {{-- Per-shipment summary --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><strong>Shipment Summary</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Shipment</th><th>Date</th><th>Items</th><th>Weight (kg)</th>
                                <th>Billed</th><th>Paid</th><th>Due</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shipmentSummaries as $s)
                                <tr>
                                    <td>
                                        <a href="{{ route('itemsshipments.show', $s['shipment']->id) }}">
                                            {{ $s['shipment']->shipment_name }}
                                        </a>
                                    </td>
                                    <td>{{ optional($s['shipment']->shipment_date)->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $s['total_items'] }} ({{ $s['priced_items'] }} priced)</td>
                                    <td>{{ number_format($s['total_weight'], 2) }}</td>
                                    <td>Tk {{ number_format($s['total_amount'], 2) }}</td>
                                    <td class="text-success">Tk {{ number_format($s['paid_amount'], 2) }}</td>
                                    <td class="text-danger">Tk {{ number_format($s['due_amount'], 2) }}</td>
                                    <td>
                                        @if($s['due_amount'] > 0)
                                            <a href="{{ route('payments.create', [$customer->id, $s['shipment']->id]) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fa fa-money"></i> Record Payment
                                            </a>
                                        @else
                                            <span class="badge badge-success">Fully Paid</span>
                                        @endif
                                        <a href="{{ route('payments.history', [$customer->id, $s['shipment']->id]) }}"
                                           class="btn btn-sm btn-secondary">
                                            <i class="fa fa-history"></i> History
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No shipments found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Statement of account --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><strong>Statement of Account</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th><th>Description</th><th>Reference</th>
                                <th class="text-right">Debit (Billed)</th>
                                <th class="text-right">Credit (Paid)</th>
                                <th class="text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgerEntries as $entry)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($entry['date'])->format('Y-m-d') }}</td>
                                    <td>{{ $entry['description'] }}</td>
                                    <td>{{ $entry['reference'] ?? '—' }}</td>
                                    <td class="text-right">
                                        {{ $entry['type'] === 'debit' ? 'Tk '.number_format($entry['amount'], 2) : '' }}
                                    </td>
                                    <td class="text-right text-success">
                                        {{ $entry['type'] === 'credit' ? 'Tk '.number_format($entry['amount'], 2) : '' }}
                                    </td>
                                    <td class="text-right"><strong>Tk {{ number_format($entry['balance'], 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Totals</th>
                                <th class="text-right">Tk {{ number_format($totalBilled, 2) }}</th>
                                <th class="text-right text-success">Tk {{ number_format($totalPaid, 2) }}</th>
                                <th class="text-right">Tk {{ number_format($totalDue, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Full payment history --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><strong>Payment History</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th><th>Shipment</th><th>Method</th>
                                <th>Reference</th><th>Items Covered</th>
                                <th class="text-right">Amount</th><th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>{{ optional($payment->payment_date)->format('Y-m-d') }}</td>
                                    <td>
                                        @if($payment->itemshipment)
                                            <a href="{{ route('itemsshipments.show', $payment->itemshipment->id) }}">
                                                {{ $payment->itemshipment->shipment_name }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td><span class="badge badge-secondary">{{ ucfirst(str_replace('_',' ', $payment->payment_method)) }}</span></td>
                                    <td>{{ $payment->reference_no ?? '—' }}</td>
                                    <td>{{ $payment->paymentItems->count() }} item(s)</td>
                                    <td class="text-right">Tk {{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->notes ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No payments recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection