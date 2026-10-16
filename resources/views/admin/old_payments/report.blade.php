@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">

        {{-- Header + month picker --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <strong class="card-title">Monthly Report — {{ $start->format('F Y') }}</strong>
                <div class="d-flex align-items-center">
                    <a href="{{ route('old-payments.report', ['month' => $prevMonth]) }}" class="btn btn-outline-secondary btn-sm mr-1">
                        <i class="fa fa-chevron-left"></i>
                    </a>
                    <form method="GET" action="{{ route('old-payments.report') }}" class="form-inline">
                        <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm mr-1" onchange="this.form.submit()">
                        <button class="btn btn-secondary btn-sm">Show</button>
                    </form>
                    <a href="{{ route('old-payments.report', ['month' => $nextMonth]) }}" class="btn btn-outline-secondary btn-sm ml-1">
                        <i class="fa fa-chevron-right"></i>
                    </a>
                    <a href="{{ route('old-payments.index') }}" class="btn btn-primary btn-sm ml-3">
                        <i class="fa fa-list"></i> All Entries
                    </a>
                </div>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Opening Balance</div>
                        <h4 class="mb-0 {{ $opening < 0 ? 'text-danger' : '' }}">{{ number_format($opening, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Received (collections)</div>
                        <h4 class="mb-0 text-success">{{ number_format($totalReceived, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Paid (shipment bills)</div>
                        <h4 class="mb-0 text-danger">{{ number_format($totalPaid, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Closing Balance</div>
                        <h4 class="mb-0 {{ $closing < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($closing, 2) }}</h4>
                        <small class="text-muted">Net this month: {{ number_format($totalReceived - $totalPaid, 2) }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Billed this month + overall outstanding --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><strong>Billed in {{ $start->format('F Y') }} (shipments dated this month)</strong></div>
                    <div class="card-body">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th><th>Shipment</th><th class="text-right">Items</th>
                                    <th class="text-right">Shipment Bill</th><th class="text-right">Customer Bill</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shipments as $s)
                                <tr>
                                    <td>{{ $s->date->format('d M') }}</td>
                                    <td><a href="{{ route('old-shipments.show', $s->id) }}">{{ $s->name }}</a></td>
                                    <td class="text-right">{{ $s->items_count }}</td>
                                    <td class="text-right">{{ number_format($s->bills_sum ?? 0, 2) }}</td>
                                    <td class="text-right">{{ number_format($s->items_sum ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted">No shipments this month.</td></tr>
                                @endforelse
                            </tbody>
                            @if($shipments->count())
                            <tfoot>
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th class="text-right">{{ $shipments->sum('items_count') }}</th>
                                    <th class="text-right">{{ number_format($shipments->sum('bills_sum'), 2) }}</th>
                                    <th class="text-right">{{ number_format($shipments->sum('items_sum'), 2) }}</th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><strong>Overall Outstanding (all time)</strong></div>
                    <div class="card-body">
                        @php
                            $custDue = $overall->customer_bill - $overall->customer_received;
                            $shipDue = $overall->shipment_bill - $overall->shipment_paid;
                        @endphp
                        <table class="table table-sm table-bordered mb-0">
                            <thead><tr><th></th><th class="text-right">Billed</th><th class="text-right">Settled</th><th class="text-right">Due</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td>Customers (to collect)</td>
                                    <td class="text-right">{{ number_format($overall->customer_bill, 2) }}</td>
                                    <td class="text-right">{{ number_format($overall->customer_received, 2) }}</td>
                                    <td class="text-right {{ $custDue > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($custDue, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Shipment bills (to pay)</td>
                                    <td class="text-right">{{ number_format($overall->shipment_bill, 2) }}</td>
                                    <td class="text-right">{{ number_format($overall->shipment_paid, 2) }}</td>
                                    <td class="text-right {{ $shipDue > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($shipDue, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- By customer / by shipment --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><strong>Collections by Customer</strong></div>
                    <div class="card-body">
                        <table class="table table-sm table-bordered mb-0">
                            <thead><tr><th>Customer</th><th class="text-right">Entries</th><th class="text-right">Received</th></tr></thead>
                            <tbody>
                                @forelse($byCustomer as $row)
                                <tr>
                                    <td>{{ $row->name }}</td>
                                    <td class="text-right">{{ $row->count }}</td>
                                    <td class="text-right">{{ number_format($row->total, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">No collections this month.</td></tr>
                                @endforelse
                            </tbody>
                            @if($byCustomer->count())
                            <tfoot>
                                <tr><th>Total</th><th class="text-right">{{ $byCustomer->sum('count') }}</th><th class="text-right">{{ number_format($totalReceived, 2) }}</th></tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><strong>By Shipment</strong></div>
                    <div class="card-body">
                        <table class="table table-sm table-bordered mb-0">
                            <thead><tr><th>Shipment</th><th class="text-right">Received</th><th class="text-right">Paid</th></tr></thead>
                            <tbody>
                                @forelse($byShipment as $row)
                                <tr>
                                    <td>{{ $row->name }}</td>
                                    <td class="text-right text-success">{{ number_format($row->received, 2) }}</td>
                                    <td class="text-right text-danger">{{ number_format($row->paid, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">No entries this month.</td></tr>
                                @endforelse
                            </tbody>
                            @if($byShipment->count())
                            <tfoot>
                                <tr><th>Total</th><th class="text-right">{{ number_format($totalReceived, 2) }}</th><th class="text-right">{{ number_format($totalPaid, 2) }}</th></tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="card">
            <div class="card-header"><strong>Details — {{ $start->format('F Y') }}</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Date</th><th>Type</th><th>Shipment</th><th>Customer</th><th>Method</th><th>Note</th>
                            <th class="text-right">Received</th><th class="text-right">Paid</th><th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-secondary">
                            <td colspan="8" class="text-right"><strong>Opening balance</strong></td>
                            <td class="text-right"><strong>{{ number_format($opening, 2) }}</strong></td>
                        </tr>
                        @forelse($entries as $e)
                        <tr>
                            <td>{{ $e->date->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $e->type === 'paid' ? 'badge-danger' : 'badge-success' }}">
                                    {{ $e->type === 'paid' ? 'Paid' : 'Received' }}
                                </span>
                            </td>
                            <td>{{ $e->shipment->name ?? '—' }}</td>
                            <td>{{ $e->customer->name ?? '—' }}</td>
                            <td>{{ $e->method }}</td>
                            <td>{{ $e->note }}</td>
                            <td class="text-right">{{ $e->type === 'received' ? number_format($e->amount, 2) : '' }}</td>
                            <td class="text-right">{{ $e->type === 'paid' ? number_format($e->amount, 2) : '' }}</td>
                            <td class="text-right {{ $e->running < 0 ? 'text-danger' : '' }}">{{ number_format($e->running, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted">No entries this month.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Month total</th>
                            <th class="text-right">{{ number_format($totalReceived, 2) }}</th>
                            <th class="text-right">{{ number_format($totalPaid, 2) }}</th>
                            <th></th>
                        </tr>
                        <tr class="table-secondary">
                            <th colspan="8" class="text-right">Closing balance</th>
                            <th class="text-right">{{ number_format($closing, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Year overview --}}
        <div class="card">
            <div class="card-header"><strong>{{ $start->year }} Overview (month by month)</strong></div>
            <div class="card-body">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr><th>Month</th><th class="text-right">Received</th><th class="text-right">Paid</th><th class="text-right">Net</th></tr>
                    </thead>
                    <tbody>
                        @foreach($yearSummary as $row)
                        <tr class="{{ $row->key === $month ? 'table-info' : '' }}">
                            <td><a href="{{ route('old-payments.report', ['month' => $row->key]) }}">{{ $row->label }}</a></td>
                            <td class="text-right">{{ number_format($row->received, 2) }}</td>
                            <td class="text-right">{{ number_format($row->paid, 2) }}</td>
                            <td class="text-right {{ $row->net < 0 ? 'text-danger' : '' }}">{{ number_format($row->net, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php
                            $yr = collect($yearSummary);
                        @endphp
                        <tr>
                            <th>Year total</th>
                            <th class="text-right">{{ number_format($yr->sum('received'), 2) }}</th>
                            <th class="text-right">{{ number_format($yr->sum('paid'), 2) }}</th>
                            <th class="text-right">{{ number_format($yr->sum('net'), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
