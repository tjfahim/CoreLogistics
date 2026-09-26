@extends('admin.layouts.master')

@section('main_content')
<div class="container mt-4">

    <h3 class="mb-4 text-center">📊 Shipment Report</h3>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">Total Earnings</h6>
                    <h4>{{ number_format($totalEarnings, 0) }} TK</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <h6 class="card-title text-primary">Total Shipment Cost</h6>
                    <h4>{{ number_format($totalShipmentCost, 0) }} TK</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h6 class="card-title text-success">Profit / Loss</h6>
                    <h4>{{ number_format($profit, 0) }} TK</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning">Cash on Hand</h6>
                    <h4 class="{{ $cashOnHand < 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($cashOnHand, 0) }} TK
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="#" class="row g-3 align-items-center">
                <div class="col-auto">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-auto">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Shipments Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📦 Shipments</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Shipment Code</th>
                        <th>Total Earnings</th>
                        <th>Total Cost</th>
                        <th>Profit</th>
                        <th>Cash on Hand</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shipments as $shipment)
    @php
        $shipmentTotalCost = $shipment->costs->sum('amount');
        $shipmentTotalPaid = $shipment->costs->sum('paid_amount');

        $shipmentTotalEarning = $shipment->customerBills->sum('total_price');

        // Correct: sum payments dynamically from deliveries
        $shipmentCustomerPaid = $shipment->customerBills->sum(function($bill){
            return $bill->deliveries->sum('amount');
        });

        $shipmentProfit = $shipmentTotalEarning - $shipmentTotalCost;
        $shipmentCashOnHand = $shipmentCustomerPaid - $shipmentTotalPaid;
    @endphp
    <tr>
        <td>{{ $shipment->shipment_code }}</td>
        <td>{{ number_format($shipmentTotalEarning, 0) }} TK</td>
        <td>{{ number_format($shipmentTotalCost, 0) }} TK</td>
        <td>{{ number_format($shipmentProfit, 0) }} TK</td>
        <td class="{{ $shipmentCashOnHand < 0 ? 'text-danger' : 'text-success' }}">
            {{ number_format($shipmentCashOnHand, 0) }} TK
        </td>
        <td>
            <a href="{{ route('shipments.costs.summary', $shipment->id) }}" class="btn btn-sm btn-info">
                Summary
            </a>
        </td>
    </tr>
@endforeach

                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
