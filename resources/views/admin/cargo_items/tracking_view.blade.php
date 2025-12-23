@extends('admin.layouts.master')

@section('main_content')

<style>
    .tracking-header {
        background: linear-gradient(135deg, #004aad, #007bff);
        color: white;
        padding: 25px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 25px;
    }

    .info-box {
        background: #f8fbff;
        border: 1px solid #e1ecff;
        border-radius: 8px;
        padding: 14px 18px;
        min-height: 90px;
    }

    .label-text {
        font-size: 13px;
        font-weight: 600;
        color: #0050a8;
        margin-bottom: 4px;
    }

    .value-text {
        font-size: 16px;
        font-weight: 500;
        color: #222;
    }

    .badge-status {
        padding: 8px 16px;
        font-size: 15px;
        border-radius: 8px;
    }
</style>


<div class="content mt-4">

    {{-- HEADER --}}
    <div class="tracking-header">
        <h2 class="fw-bold mb-1">📦 Shipment Tracking Result</h2>
        <p class="mb-2">Tracking No: <strong>{{ $item->tracking_no }}</strong></p>

        @php
            $statusColors = [
                'pending' => 'secondary',
                'packed' => 'info',
                'ready_to_ship' => 'primary',
                'shipping_ongoing' => 'warning',
                'arrived_bd' => 'dark',
                'waiting_for_customs' => 'secondary',
                'ready_to_deliver' => 'primary',
                'delivered' => 'success'
            ];
            $color = $statusColors[$item->status] ?? 'secondary';
        @endphp

        <span class="badge bg-{{ $color }} badge-status">
            {{ strtoupper(str_replace('_', ' ', $item->status)) }}
        </span>
    </div>



    {{-- SHIPMENT INFO CARD --}}
    @php $shipment = $item->itemshipments->first(); @endphp

    @if($shipment)
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-primary text-light">
            🚚 Shipment Information
        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-6">
                    <div class="info-box">
                        <div class="label-text">Shipment Name</div>
                        <div class="value-text">{{ $shipment->shipment_name }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-box">
                        <div class="label-text">Date</div>
                        <div class="value-text">{{ $shipment->shipment_date?->format('d M, Y') }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-box">
                        <div class="label-text">Status</div>
                        <div class="value-text">{{ ucfirst($shipment->status) }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-box">
                        <div class="label-text">Note</div>
                        <div class="value-text">{{ $shipment->note ?? '-' }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endif




    {{-- MAIN PACKAGE DETAILS --}}
    <div class="card shadow-sm">
        <div class="card-header fw-bold bg-light">
            📦 Package Details
        </div>

        <div class="card-body">

            <div class="row g-3">

                {{-- FIRST COLUMN --}}
                <div class="col-md-6">

                    <div class="info-box mb-3">
                        <div class="label-text">Goods Name</div>
                        <div class="value-text">{{ $item->goods_name_en }} ({{ $item->goods_name_local }})</div>
                    </div>

                    <div class="info-box mb-3">
                        <div class="label-text">Printed / Written CTN</div>
                        <div class="value-text">{{ $item->printed_ctn_no }} / {{ $item->written_ctn_no }}</div>
                    </div>

                    <div class="info-box mb-3">
                        <div class="label-text">Pack No</div>
                        <div class="value-text">{{ $item->pack_no }}</div>
                    </div>

                    <div class="info-box mb-3">
                        <div class="label-text">Shipping Mark</div>
                        <div class="value-text">{{ $item->shipping_mark ?? '-' }}</div>
                    </div>

                    <div class="info-box mb-3">
                        <div class="label-text">Warehouse</div>
                        <div class="value-text">{{ $item->warehouse ?? '-' }}</div>
                    </div>

                </div>


                {{-- SECOND COLUMN --}}
                <div class="col-md-6">

                    <div class="info-box mb-3">
                        <div class="label-text">Weight</div>
                        <div class="value-text">{{ $item->weight_kg }} kg</div>
                    </div>

                    <div class="info-box mb-3">
                        <div class="label-text">Dimensions (cm)</div>
                        <div class="value-text">{{ $item->length_cm }} × {{ $item->width_cm }} × {{ $item->height_cm }}</div>
                    </div>

                    <div class="info-box mb-3">
                        <div class="label-text">CBM</div>
                        <div class="value-text">{{ $item->cbm }}</div>
                    </div>

                    <div class="info-box mb-3">
                        <div class="label-text">Goods Received?</div>
                        <div class="value-text">{{ $item->goods_received ?? '-' }}</div>
                    </div>

                    <div class="info-box mb-3">
                        <div class="label-text">Remark</div>
                        <div class="value-text">{{ $item->remark ?? '-' }}</div>
                    </div>

                </div>

            </div>
        </div>
    </div>


    {{-- BACK BUTTON --}}
    <div class="mt-4">
        <a href="{{ route('cargo-items.tracking-view') }}" class="btn btn-dark">
            ⬅ Back
        </a>
    </div>

</div>

@endsection
