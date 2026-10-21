@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid mt-3">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Payment Voucher {{ $voucher->voucher_no ?? ('#' . $voucher->id) }}</h2>
        <div class="btn-group">
            <a href="{{ route('shipment-bills.index') }}" class="btn btn-secondary">Back to Billing</a>
            <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" class="btn btn-outline-danger"><i class="fa fa-file-pdf-o"></i> PDF</a>
            @can('delete-bill-payment')
                <form action="{{ route('shipment-bills.destroy', $voucher->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Delete this voucher? All its lines are removed and shipment dues go back up.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger"><i class="fa fa-trash"></i> Delete Voucher</button>
                </form>
            @endcan
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Date</strong><br>{{ optional($voucher->payment_date)->format('d M, Y') }}</div>
                <div class="col-md-3"><strong>Method</strong><br>{{ \App\Http\Controllers\ShipmentBillController::METHODS[$voucher->payment_method] ?? ucfirst($voucher->payment_method) }}</div>
                <div class="col-md-3"><strong>Reference</strong><br>{{ $voucher->reference_no ?? '—' }}</div>
                <div class="col-md-3"><strong>Paid To</strong><br>{{ optional($voucher->agent)->name ?? '—' }}</div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3"><strong>Saved By</strong><br>{{ optional($voucher->user)->name ?? '—' }}</div>
                <div class="col-md-3"><strong>Saved At</strong><br>{{ $voucher->created_at->format('d M, Y H:i') }}</div>
                <div class="col-md-6"><strong>Notes</strong><br>{{ $voucher->notes ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header d-flex justify-content-between">
            <strong>Split Across Shipments</strong>
            <strong>Total: {{ number_format($voucher->amount, 2) }} tk</strong>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Shipment</th><th>Cost Type</th><th>Agent</th>
                        <th class="text-right">Cost (BDT)</th><th class="text-right">Paid in this voucher</th>
                        <th class="text-right">Still Due</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($byShipment as $shipmentId => $lines)
                        @php $shipment = $lines->first()->itemsShipmentCost->itemsShipment; @endphp
                        <tr class="table-secondary">
                            <td colspan="4">
                                <strong>
                                    <a href="{{ route('itemshipments.costs.show', $shipmentId) }}">{{ $shipment->shipment_name ?? ('Shipment #' . $shipmentId) }}</a>
                                </strong>
                            </td>
                            <td class="text-right"><strong>{{ number_format($lines->sum('amount'), 2) }}</strong></td>
                            <td></td>
                        </tr>
                        @foreach($lines as $p)
                            @php $c = $p->itemsShipmentCost; @endphp
                            <tr>
                                <td></td>
                                <td>{{ $c->costType->name ?? '—' }}</td>
                                <td>{{ optional($c->agent)->name ?? '—' }}</td>
                                <td class="text-right">{{ number_format($c->final_bdt_cost ?: $c->amount, 2) }}</td>
                                <td class="text-right">{{ number_format($p->amount, 2) }}</td>
                                <td class="text-right {{ $c->due_amount > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($c->due_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
