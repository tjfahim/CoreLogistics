@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        {{-- Header --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Shipment: {{ $shipment->name }}</strong>
                <div>
                    <a href="{{ route('old-shipments.edit', $shipment->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Edit</a>
                    <a href="{{ route('old-shipments.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><strong>Date:</strong> {{ $shipment->date->format('d M Y') }}</div>
                    <div class="col-md-4"><strong>Shipping Mark:</strong> {{ $shipment->shipping_mark ?: '—' }}</div>
                    <div class="col-md-4"><strong>Items:</strong> {{ $shipment->items->count() }}</div>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><strong>Shipment Bill (we pay)</strong></div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr><td>Total bill</td><td class="text-right">{{ number_format($shipment->bill_total, 2) }}</td></tr>
                            <tr><td>Paid</td><td class="text-right text-success">{{ number_format($shipment->paid_total, 2) }}</td></tr>
                            <tr><th>Due</th><th class="text-right {{ $shipment->bill_due > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($shipment->bill_due, 2) }}</th></tr>
                        </table>
                        <a href="{{ route('old-payments.create', ['type' => 'paid', 'old_shipment_id' => $shipment->id]) }}"
                           class="btn btn-outline-primary btn-sm mt-2"><i class="fa fa-plus"></i> Add Payment</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><strong>Customer Bill (we collect)</strong></div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr><td>Total items price</td><td class="text-right">{{ number_format($shipment->items_total, 2) }}</td></tr>
                            <tr><td>Received</td><td class="text-right text-success">{{ number_format($shipment->received_total, 2) }}</td></tr>
                            <tr><th>Due</th><th class="text-right {{ $shipment->collection_due > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($shipment->collection_due, 2) }}</th></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bills --}}
        <div class="card">
            <div class="card-header"><strong>Shipment Bill Details</strong></div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead><tr><th>Cost Type</th><th class="text-right">Total Price</th></tr></thead>
                    <tbody>
                        @forelse($shipment->bills as $bill)
                            <tr><td>{{ $bill->cost_type }}</td><td class="text-right">{{ number_format($bill->total_price, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">No bill entries.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot><tr><th>Total</th><th class="text-right">{{ number_format($shipment->bill_total, 2) }}</th></tr></tfoot>
                </table>
            </div>
        </div>

        {{-- Customers --}}
        <div class="card">
            <div class="card-header"><strong>Customers in this Shipment</strong></div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Customer</th><th>Number</th>
                            <th class="text-right">Items</th><th class="text-right">Weight</th>
                            <th class="text-right">Bill</th><th class="text-right">Received</th><th class="text-right">Due</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerRows as $row)
                        <tr>
                            <td><a href="{{ route('old-customers.show', $row->customer->id) }}">{{ $row->customer->name }}</a></td>
                            <td>{{ $row->customer->number }}</td>
                            <td class="text-right">{{ $row->items }}</td>
                            <td class="text-right">{{ number_format($row->weight, 3) }}</td>
                            <td class="text-right">{{ number_format($row->total, 2) }}</td>
                            <td class="text-right text-success">{{ number_format($row->received, 2) }}</td>
                            <td class="text-right {{ $row->due > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($row->due, 2) }}</td>
                            <td>
                                <a href="{{ route('old-payments.create', ['type' => 'received', 'old_shipment_id' => $shipment->id, 'old_customer_id' => $row->customer->id]) }}"
                                   class="btn btn-outline-success btn-sm">Collect</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted">No customers assigned to items.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($unassigned->count())
                    <div class="text-warning">
                        {{ $unassigned->count() }} item(s) have no customer assigned
                        (total {{ number_format($unassigned->sum('final_price'), 2) }}).
                    </div>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <div class="card">
            <div class="card-header"><strong>Items</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>#</th><th>Customer</th><th>Item</th><th class="text-right">Weight</th>
                            <th>CTN</th><th class="text-right">CBM</th><th>Mark</th>
                            <th class="text-right">Price/kg</th><th class="text-right">Final</th><th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipment->items as $n => $item)
                        <tr>
                            <td>{{ $n + 1 }}</td>
                            <td>{{ $item->customer->name ?? '—' }}</td>
                            <td>{{ $item->name }}</td>
                            <td class="text-right">{{ number_format($item->weight, 3) }}</td>
                            <td>{{ $item->ctn_no }}</td>
                            <td class="text-right">{{ number_format($item->cbm, 3) }}</td>
                            <td>{{ $item->shipping_mark }}</td>
                            <td class="text-right">{{ number_format($item->price_per_kg, 2) }}</td>
                            <td class="text-right">{{ number_format($item->final_price, 2) }}</td>
                            <td>{{ $item->remark }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center text-muted">No items.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Totals</th>
                            <th class="text-right">{{ number_format($shipment->items->sum('weight'), 3) }}</th>
                            <th></th>
                            <th class="text-right">{{ number_format($shipment->items->sum('cbm'), 3) }}</th>
                            <th colspan="2"></th>
                            <th class="text-right">{{ number_format($shipment->items->sum('final_price'), 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Payments --}}
        <div class="card">
            <div class="card-header"><strong>Payments &amp; Collections</strong></div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr><th>Date</th><th>Type</th><th>Customer</th><th>Method</th><th>Note</th><th class="text-right">Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse($shipment->payments->sortByDesc('date') as $p)
                        <tr>
                            <td>{{ $p->date->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $p->type === 'paid' ? 'badge-danger' : 'badge-success' }}">
                                    {{ $p->type === 'paid' ? 'Paid' : 'Received' }}
                                </span>
                            </td>
                            <td>{{ $p->customer->name ?? '—' }}</td>
                            <td>{{ $p->method }}</td>
                            <td>{{ $p->note }}</td>
                            <td class="text-right">{{ number_format($p->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">No entries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
