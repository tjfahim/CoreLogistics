@extends('admin.layouts.master')

@section('main_content')
@php
    $fmt = fn ($n) => number_format($n, 0);
    $net = $upload->total_billed - $upload->total_paid - $totalCredit;
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Old Report: {{ $upload->title }}</h1>
            <small class="text-muted">From the uploaded Excel ({{ $upload->file_name }}). Does not touch live data.</small>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-secondary">Print</button>
            <a href="{{ route('legacy.show', $upload) }}" class="btn btn-sm btn-outline-secondary">Review</a>
            <a href="{{ route('legacy.index') }}" class="btn btn-sm btn-outline-secondary">All uploads</a>
        </div>
    </div>

    <div class="row mb-4">
        @foreach ([
            ['Items', $upload->total_rows, 'primary'],
            ['Weight (kg)', $fmt($upload->total_weight), 'secondary'],
            ['Total Billed', $fmt($upload->total_billed), 'success'],
            ['Total Paid', $fmt($upload->total_paid), 'info'],
            ['Due (before credits)', $fmt($upload->total_billed - $upload->total_paid), 'warning'],
            ['Net Due (after credits)', $fmt($net), 'danger'],
        ] as [$label, $val, $color])
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card border-left-{{ $color }} shadow h-100 py-2"><div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-{{ $color }} text-uppercase">{{ $label }}</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $val }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    @if($detail)
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between">
                <strong>{{ $detailTitle }}</strong>
                <a href="{{ route('legacy.report', $upload) }}" class="btn btn-sm btn-outline-secondary">Close</a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm table-bordered mb-0" style="font-size:12px">
                    <thead class="thead-light"><tr>
                        <th>Date</th><th>Customer</th><th>Shipment</th><th>Mark</th><th>Goods</th><th class="text-right">CTN</th>
                        <th class="text-right">Kg</th><th class="text-right">Rate</th><th class="text-right">Bill</th>
                        <th class="text-right">Paid</th><th class="text-right">Due</th><th>Receiver</th>
                    </tr></thead>
                    <tbody>
                    @foreach($detail as $r)
                        <tr>
                            <td>{{ $r->delivered_at?->format('d/m/Y') }}</td><td>{{ $r->customer_name }}</td><td>{{ $r->shipment_no }}</td>
                            <td>{{ $r->shipping_mark }}</td><td>{{ \Illuminate\Support\Str::limit($r->goods_name_en, 30) }}</td>
                            <td class="text-right">{{ $r->pack_no }}</td><td class="text-right">{{ number_format($r->weight_kg, 2) }}</td>
                            <td class="text-right">{{ $r->price_per_kg }}</td><td class="text-right">{{ $fmt($r->price) }}</td>
                            <td class="text-right">{{ $fmt($r->paid_amount) }}</td>
                            <td class="text-right {{ $r->balance > 5 ? 'text-danger' : '' }}">{{ $fmt($r->balance) }}</td>
                            <td>{{ $r->receiver_name }} {{ $r->receiver_phone }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="font-weight-bold"><tr>
                        <td colspan="6">Total</td>
                        <td class="text-right">{{ number_format($detail->sum('weight_kg'), 2) }}</td><td></td>
                        <td class="text-right">{{ $fmt($detail->sum('price')) }}</td>
                        <td class="text-right">{{ $fmt($detail->sum('paid_amount')) }}</td>
                        <td class="text-right">{{ $fmt($detail->sum('price') - $detail->sum('paid_amount')) }}</td><td></td>
                    </tr></tfoot>
                </table>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header"><strong>By Shipment</strong></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light"><tr>
                            <th>Shipment</th><th>Origin</th><th class="text-right">Items</th><th class="text-right">Kg</th>
                            <th class="text-right">Billed</th><th class="text-right">Paid</th><th class="text-right">Due</th>
                        </tr></thead>
                        <tbody>
                        @foreach($byShipment as $s)
                            <tr>
                                <td><a href="{{ route('legacy.report', [$upload, 'shipment' => $s->shipment_no]) }}">{{ $s->shipment_no }}</a></td>
                                <td>{{ $s->warehouse }}</td><td class="text-right">{{ $s->items }}</td>
                                <td class="text-right">{{ number_format($s->weight, 1) }}</td>
                                <td class="text-right">{{ $fmt($s->billed) }}</td><td class="text-right">{{ $fmt($s->paid) }}</td>
                                <td class="text-right {{ $s->billed - $s->paid > 5 ? 'text-danger' : 'text-success' }}">{{ $fmt($s->billed - $s->paid) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="font-weight-bold"><tr>
                            <td colspan="2">Total</td><td class="text-right">{{ $byShipment->sum('items') }}</td>
                            <td class="text-right">{{ number_format($byShipment->sum('weight'), 1) }}</td>
                            <td class="text-right">{{ $fmt($byShipment->sum('billed')) }}</td><td class="text-right">{{ $fmt($byShipment->sum('paid')) }}</td>
                            <td class="text-right">{{ $fmt($byShipment->sum('billed') - $byShipment->sum('paid')) }}</td>
                        </tr></tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header"><strong>By Customer</strong></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light"><tr>
                            <th>Customer</th><th class="text-right">Items</th><th class="text-right">Billed</th>
                            <th class="text-right">Paid</th><th class="text-right">Credit</th><th class="text-right">Net due</th>
                        </tr></thead>
                        <tbody>
                        @foreach($byCustomer as $c)
                            @php $due = $c->billed - $c->paid - $c->credit; @endphp
                            <tr>
                                <td><a href="{{ route('legacy.report', [$upload, 'customer' => $c->customer_code]) }}">{{ $c->customer_name }}</a></td>
                                <td class="text-right">{{ $c->items }}</td><td class="text-right">{{ $fmt($c->billed) }}</td>
                                <td class="text-right">{{ $fmt($c->paid) }}</td><td class="text-right">{{ $c->credit ? $fmt($c->credit) : '' }}</td>
                                <td class="text-right {{ $due > 5 ? 'text-danger' : ($due < -5 ? 'text-primary' : 'text-success') }}">{{ $fmt($due) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="font-weight-bold"><tr>
                            <td>Total</td><td class="text-right">{{ $byCustomer->sum('items') }}</td>
                            <td class="text-right">{{ $fmt($byCustomer->sum('billed')) }}</td><td class="text-right">{{ $fmt($byCustomer->sum('paid')) }}</td>
                            <td class="text-right">{{ $fmt($totalCredit) }}</td><td class="text-right">{{ $fmt($net) }}</td>
                        </tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
