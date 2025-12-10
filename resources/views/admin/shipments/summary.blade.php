@extends('admin.layouts.master')

@section('main_content')
<div class="container mt-4">

    <h3 class="mb-4 text-center">📦 Shipment Summary: <strong>{{ $shipment->shipment_code }}</strong></h3>

    <!-- Overview / Profit -->
    @php
        $totalEarning = $summary['customers']['total_price'];
        $totalCost = $summary['shipment']['total_cost'];
        $profit = $totalEarning - $totalCost;
    @endphp

    <!-- Overview / Profit -->
@php
    $totalEarning = $summary['customers']['total_price'];
    $totalCost = $summary['shipment']['total_cost'];
    $profit = $totalEarning - $totalCost;

    // Cash on Hand = Total Paid by Customers - Total Paid for Shipment Costs
    $cashOnHand = $summary['customers']['total_paid'] - $summary['shipment']['total_paid'];
@endphp

<div class="row g-3 mb-4 text-center">
    <div class="col-md-3">
        <div class="card shadow-sm border-info">
            <div class="card-body">
                <h6 class="card-title text-info">Total Earnings</h6>
                <h4>{{ number_format($totalEarning, 2) }} TK</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-primary">
            <div class="card-body">
                <h6 class="card-title text-primary">Total Shipment Cost</h6>
                <h4>{{ number_format($totalCost, 2) }} TK</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-success">
            <div class="card-body">
                <h6 class="card-title text-success">Profit / Loss</h6>
                <h4>{{ number_format($profit, 2) }} TK</h4>
            </div>
        </div>
    </div>
    @php
    // Cash on Hand = Paid by Customers - Paid for Shipment Costs
    $cashOnHand = $summary['customers']['total_paid'] - $summary['shipment']['total_paid'];
@endphp

<div class="col-md-3">
    <div class="card shadow-sm border-warning">
        <div class="card-body">
            <h6 class="card-title text-warning">Cash on Hand</h6>
            <h4 class="{{ $cashOnHand < 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($cashOnHand, 2) }} TK
            </h4>
        </div>
    </div>
</div>
</div>


  

  <!-- Shipment Costs by Type -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">💰 Shipment Costs by Type</h5>
    </div>
    <div class="card-body">

        <!-- Cards for Costs Summary -->
        <div class="row g-3 mb-3 text-center">
            @php
                $totalAmount = $summary['shipment']['total_cost'];
                $totalPaid = $summary['shipment']['total_paid'];
                $totalDue = $summary['shipment']['total_due'];
            @endphp
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <strong>Total Amount</strong><br>
                    {{ number_format($totalAmount, 2) }} TK
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <strong>Total Paid</strong><br>
                    {{ number_format($totalPaid, 2) }} TK
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <strong>Total Due</strong><br>
                    {{ number_format($totalDue, 2) }} TK
                </div>
            </div>
        </div>

        <!-- Detailed Costs Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered" id="costsTable">
                <thead class="table-dark">
                    <tr>
                        <th>Cost Type</th>
                        <th>Total Amount (TK)</th>
                        <th>Paid Amount (TK)</th>
                        <th>Due Amount (TK)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['shipment']['costs_by_type'] as $type => $cost)
                    <tr>
                        <td>{{ $type }}</td>
                        <td>{{ number_format($cost['total_amount'], 2) }}</td>
                        <td>{{ number_format($cost['paid_amount'], 2) }}</td>
                        <td>{{ number_format($cost['due_amount'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td>Total</td>
                        <td>{{ number_format($totalAmount, 2) }}</td>
                        <td>{{ number_format($totalPaid, 2) }}</td>
                        <td>{{ number_format($totalDue, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>


    <!-- Customer Earnings -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">👤 Customer Earnings</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3 text-center">
                <div class="col-md-2">
                    <div class="p-3 border rounded bg-light">
                        <strong>Total Price</strong><br>
                        {{ number_format($summary['customers']['total_price'], 2) }} TK
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 border rounded bg-light">
                        <strong>Total Paid</strong><br>
                        {{ number_format($summary['customers']['total_paid'], 2) }} TK
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 border rounded bg-light">
                        <strong>Total Due</strong><br>
                        {{ number_format($summary['customers']['total_due'], 2) }} TK
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded bg-light">
                        <strong>Delivered Weight</strong><br>
                        {{ number_format($summary['customers']['total_delivered_weight'], 2) }} KG
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded bg-light">
                        <strong>Remaining Weight</strong><br>
                        {{ number_format($summary['customers']['total_remaining_weight'], 2) }} KG
                    </div>
                </div>
            </div>

            <!-- Detailed Customer Bills Table -->
            <!-- Detailed Customer Bills Table -->
<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered" id="customerBillsTable">
        <thead class="table-dark">
            <tr>
                <th>Customer</th>
                <th>Total Weight (KG)</th>
                <th>Delivered Weight (KG)</th>
                <th>Remaining Weight (KG)</th>
                <th>Total Price (TK)</th>
                <th>Paid Amount (TK)</th>
                <th>Due Amount (TK)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumTotalWeight = 0;
                $sumDelivered = 0;
                $sumPrice = 0;
                $sumPaid = 0;
                $sumDue = 0;
            @endphp
            @foreach($shipment->customerBills as $bill)
                @php
                    // calculate dynamically from deliveries
                    $deliveredWeight = $bill->deliveries->sum('delivered_weight');
                    $paidAmount = $bill->deliveries->sum('amount');
                    $remainingWeight = $bill->total_weight - $deliveredWeight;
                    $dueAmount = $bill->total_price - $paidAmount;

                    // determine status
                    if($dueAmount <= 0 && $remainingWeight <= 0){
                        $status = 'paid';
                    } elseif($paidAmount > 0 || $deliveredWeight > 0){
                        $status = 'partial';
                    } else {
                        $status = 'pending';
                    }

                    $sumTotalWeight += $bill->total_weight;
                    $sumDelivered += $deliveredWeight;
                    $sumPrice += $bill->total_price;
                    $sumPaid += $paidAmount;
                    $sumDue += $dueAmount;
                @endphp
                <tr>
                    <td>{{ $bill->customer->name }}</td>
                    <td>{{ number_format($bill->total_weight, 2) }}</td>
                    <td>{{ number_format($deliveredWeight, 2) }}</td>
                    <td>{{ number_format($remainingWeight, 2) }}</td>
                    <td>{{ number_format($bill->total_price, 2) }}</td>
                    <td>{{ number_format($paidAmount, 2) }}</td>
                    <td>{{ number_format($dueAmount, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $status=='paid'?'success':($status=='partial'?'warning':'danger') }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light fw-bold">
            <tr>
                <td>Total</td>
                <td>{{ number_format($sumTotalWeight, 2) }}</td>
                <td>{{ number_format($sumDelivered, 2) }}</td>
                <td>{{ number_format($sumTotalWeight - $sumDelivered, 2) }}</td>
                <td>{{ number_format($sumPrice, 2) }}</td>
                <td>{{ number_format($sumPaid, 2) }}</td>
                <td>{{ number_format($sumDue, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>


        </div>
    </div>

</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#costsTable, #customerBillsTable').DataTable({
            pageLength: 10,
            order: [],
            lengthChange: true,
            searching: true,
            responsive: true
        });
    });
</script>
@endpush

@endsection
