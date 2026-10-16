<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tracking - {{ $item->tracking_no }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #eaf2f9;
            font-family: 'Segoe UI', Roboto, sans-serif;
        }

        .track-container {
            max-width: 950px;
            margin: 60px auto;
        }

        .track-header {
            background: linear-gradient(135deg, #1a314cff, #001a38ff);
            color: #fff;
            padding: 35px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
        }

        .status-badge {
            font-size: 17px;
            padding: 10px 18px;
            border-radius: 8px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .track-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 35px;
            margin-top: 25px;
            box-shadow: 0 3px 14px rgba(0,0,0,0.10);
        }

        .section-title {
            font-size: 22px;
            font-weight: 600;
            border-left: 4px solid #007bff;
            padding-left: 10px;
            margin-bottom: 18px;
            margin-top: 15px;
        }

        .label-box {
            font-weight: 600;
            color: #00509e;
        }

        .value-box {
            font-size: 16px;
            color: #333;
        }

        .btn-custom {
            border-radius: 10px;
            padding: 12px 24px;
            font-size: 17px;
        }

        .highlight-box {
            background: #f7fbff;
            border-left: 5px solid #007bff;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .badge-big {
            font-size: 22px;
            padding: 12px 20px;
        }

    </style>
</head>

<body>

<div class="track-container">

    {{-- ===== HEADER ===== --}}
    <div class="track-header">
        <h1 class="fw-bold">📦 Shipment Tracking</h1>
        <h4>Your Tracking Number</h4>
        <p class="fs-4"><strong>{{ $item->tracking_no }}</strong></p>

        @php
            $colors = [
                'pending' => 'secondary',
                'packed' => 'info',
                'ready_to_ship' => 'primary',
                'shipping_ongoing' => 'warning',
                'arrived_bd' => 'dark',
                'waiting_for_customs' => 'secondary',
                'ready_to_deliver' => 'info',
                'delivered' => 'success'
            ];

            $color = $colors[$item->status] ?? 'secondary';
        @endphp

        <span class="badge bg-{{ $color }} text-light badge-big">
            {{ strtoupper(str_replace('_', ' ', $item->status)) }}
        </span>
    </div>


    {{-- ===== DETAILS CARD ===== --}}
    <div class="track-card">

        {{-- SHIPMENT --}}
        @php
            $shipment = $item->itemshipments->first();
        @endphp

        @if($shipment)
        <div class="mb-4">
            <h5 class="section-title">Shipment Information</h5>

            <div class="highlight-box row">
                <p class="col-md-6 "><strong>Name:</strong> {{ $shipment->shipment_name }}</p>
                <p class="col-md-6 "><strong>Date:</strong> {{ $shipment->shipment_date?->format('d M, Y') }}</p>
                <p class="col-md-6 "><strong>Status:</strong> {{ ucfirst($shipment->status) }}</p>
                <p class="col-md-6 "><strong>Note:</strong> {{ $shipment->note ?? '-' }}</p>
            </div>
        </div>
        @endif

        <h5 class="section-title">Package Details</h5>

        <div class="row g-3">

            <div class="col-md-6">
                <div class="label-box">Goods Name</div>
                <div class="value-box">{{ $item->goods_name_en }} ({{ $item->goods_name_local }})</div>
            </div>

            <div class="col-md-6">
                <div class="label-box">Printed CTN</div>
                <div class="value-box">{{ $item->printed_ctn_no }}</div>
            </div>

            <div class="col-md-6">
                <div class="label-box">Written CTN</div>
                <div class="value-box">{{ $item->written_ctn_no }}</div>
            </div>

            <div class="col-md-6">
                <div class="label-box">Pack Number</div>
                <div class="value-box">{{ $item->pack_no }}</div>
            </div>
            <div class="col-md-6">
                <div class="label-box">Receipt Number</div>
                <div class="value-box">{{ $item->receipt_no }}</div>
            </div>

            <div class="col-md-6">
                <div class="label-box">Weight</div>
                <div class="value-box">{{ $item->weight_kg }} kg</div>
            </div>

            <div class="col-md-6">
                <div class="label-box">Dimensions</div>
                <div class="value-box">{{ $item->length_cm }} × {{ $item->width_cm }} × {{ $item->height_cm }} cm</div>
            </div>

            <div class="col-md-6">
                <div class="label-box">CBM</div>
                <div class="value-box">{{ $item->cbm }}</div>
            </div>

            <div class="col-md-6">
                <div class="label-box">Shipping Mark</div>
                <div class="value-box">{{ $item->shipping_mark ?? '-' }}</div>
            </div>

            <div class="col-md-6">
                <div class="label-box">Remark</div>
                <div class="value-box">{{ $item->remark ?? '-' }}</div>
            </div>

            <div class="col-md-6">
                <div class="label-box">Warehouse</div>
                <div class="value-box">{{ $item->warehouse ?? '-' }}</div>
            </div>

            <div class="col-md-6">
                <div class="label-box">Goods Received</div>
                <div class="value-box">{{ $item->goods_received ?? '-' }}</div>
            </div>

        </div>

    </div>

    {{-- BACK BUTTON --}}

    <div class="text-center mt-4">
            
        <a href="{{ url('/tracking') }}" class="btn btn-primary btn-custom">
            🔍 Track Another Shipment
        </a>
    </div>

</div>

</body>
</html>
