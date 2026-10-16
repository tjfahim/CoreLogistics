@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="animated fadeIn">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Customer: {{ $customer->name }}</strong>
                <div>
                    <a href="{{ route('old-payments.create', ['type' => 'received', 'old_customer_id' => $customer->id]) }}"
                       class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Collect Payment</a>
                    <a href="{{ route('old-customers.edit', $customer->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Edit</a>
                    <a href="{{ route('old-customers.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>Number:</strong> {{ $customer->number ?: '—' }}</div>
                    <div class="col-md-3"><strong>Total bill:</strong> {{ number_format($customer->bill_total, 2) }}</div>
                    <div class="col-md-3"><strong>Received:</strong> <span class="text-success">{{ number_format($customer->received_total, 2) }}</span></div>
                    <div class="col-md-3">
                        <strong>Due:</strong>
                        <span class="{{ $customer->due > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($customer->due, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items grouped by shipment --}}
        @forelse($itemsByShipment as $shipmentId => $items)
        @php $shipment = $items->first()->shipment; @endphp
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>
                    <a href="{{ route('old-shipments.show', $shipment->id) }}">{{ $shipment->name }}</a>
                    <small class="text-muted">({{ $shipment->date->format('d M Y') }})</small>
                </strong>
                <span>Subtotal: {{ number_format($items->sum('final_price'), 2) }}</span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Item</th><th class="text-right">Weight</th><th>CTN</th><th class="text-right">CBM</th>
                            <th>Mark</th><th class="text-right">Price/kg</th><th class="text-right">Final</th><th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td class="text-right">{{ number_format($item->weight, 3) }}</td>
                            <td>{{ $item->ctn_no }}</td>
                            <td class="text-right">{{ number_format($item->cbm, 3) }}</td>
                            <td>{{ $item->shipping_mark }}</td>
                            <td class="text-right">{{ number_format($item->price_per_kg, 2) }}</td>
                            <td class="text-right">{{ number_format($item->final_price, 2) }}</td>
                            <td>{{ $item->remark }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="card"><div class="card-body text-muted text-center">No items assigned to this customer.</div></div>
        @endforelse

        {{-- Payments --}}
        <div class="card">
            <div class="card-header"><strong>Collections</strong></div>
            <div class="card-body">
                <table class="table table-bordered table-sm mb-0">
                    <thead><tr><th>Date</th><th>Shipment</th><th>Method</th><th>Note</th><th class="text-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse($customer->payments->where('type', 'received')->sortByDesc('date') as $p)
                        <tr>
                            <td>{{ $p->date->format('d M Y') }}</td>
                            <td>{{ $p->shipment->name ?? '—' }}</td>
                            <td>{{ $p->method }}</td>
                            <td>{{ $p->note }}</td>
                            <td class="text-right">{{ number_format($p->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">No collections yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
