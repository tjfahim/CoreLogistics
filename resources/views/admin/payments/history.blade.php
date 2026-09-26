@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        Payment History: {{ $customer->name }} ({{ $customer->customer_code }})
        <br>
        <small class="text-muted">Shipment: {{ $itemshipment->shipment_name }}</small>
    </h2>
    <div class="btn-group">
        <a href="javascript:history.back()" 
           class="btn btn-secondary">
            Back
        </a>
        <a href="{{ route('payments.create', ['customer' => $customer, 'itemshipment' => $itemshipment]) }}" 
           class="btn btn-success">
            New Payment
        </a>
    </div>
</div>
    
    <!-- Financial Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Bill Amount</h5>
                    <h3>{{ number_format($totalAmount, 0) }} tk</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Paid</h5>
                    <h3>{{ number_format($totalPaid, 0) }} tk</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Total Due</h5>
                    <h3>{{ number_format($totalDue, 0) }} tk</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5>Total Payments</h5>
                    <h3>{{ $payments->count() }}</h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment History Table -->
    <div class="card">
        <div class="card-header">
            <h4>Payment Records</h4>
        </div>
        <div class="card-body">
            @if($payments->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Amount (tk)</th>
                            <th>Payment Method</th>
                            <th>Items Paid</th>
                            <th>Reference No</th>
                            <th>Remaining Due</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $index => $payment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $payment->payment_date->format('d M, Y') }}
                            </td>
                            <td class="text-success fw-bold">
                                {{ number_format($payment->amount, 0) }}
                            </td>
                            <td>
                                @php
                                    $methodColors = [
                                        'cash' => 'primary',
                                        'bank_transfer' => 'info',
                                        'cheque' => 'warning',
                                        'card' => 'success'
                                    ];
                                    $color = $methodColors[$payment->payment_method] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                </span>
                            </td>
                            <td>
                                {{ $payment->paymentItems->count() }} item(s)
                            </td>
                            <td>
                                @if($payment->reference_no)
                                <code>{{ $payment->reference_no }}</code>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-danger">
                                {{ number_format($payment->remaining_due, 0) }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" 
                                            class="btn btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#paymentDetails{{ $payment->id }}">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                                
                                <!-- Details Modal -->
                                <div class="modal fade" id="paymentDetails{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Payment Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <table class="table">
                                                            <tr>
                                                                <th>Customer:</th>
                                                                <td>{{ $customer->name }} ({{ $customer->customer_code }})</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Payment Date:</th>
                                                                <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Amount:</th>
                                                                <td class="text-success fw-bold">{{ number_format($payment->amount, 0) }} tk</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Payment Method:</th>
                                                                <td>
                                                                    <span class="badge bg-{{ $color }}">
                                                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Reference No:</th>
                                                                <td>{{ $payment->reference_no ?: 'N/A' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Remaining Due:</th>
                                                                <td class="text-danger">{{ number_format($payment->remaining_due, 0) }} tk</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Notes:</th>
                                                                <td>{{ $payment->notes ?: 'No notes' }}</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Items Paid in this Payment:</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>Item</th>
                                                                        <th>Tracking No</th>
                                                                        <th>Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($payment->paymentItems as $itemIndex => $paymentItem)
                                                                    <tr>
                                                                        <td>{{ $itemIndex + 1 }}</td>
                                                                        <td>
                                                                            {{ $paymentItem->cargoItem->goods_name_en }}
                                                                            @if($paymentItem->cargoItem->goods_name_local)
                                                                            <br><small class="text-muted">{{ $paymentItem->cargoItem->goods_name_local }}</small>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $paymentItem->cargoItem->tracking_no }}</td>
                                                                        <td>{{ number_format($paymentItem->amount, 0) }} tk</td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                                        <td><strong>{{ number_format($payment->amount, 0) }} tk</strong></td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="2"><strong>TOTALS</strong></td>
                            <td><strong>{{ number_format($payments->sum('amount'), 0) }} tk</strong></td>
                            <td colspan="5"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="alert alert-info text-center">
                <i class="fa fa-info-circle fa-2x mb-3"></i>
                <h4>No Payment Records Found</h4>
                <p>This customer hasn't made any payments yet.</p>
               
            </div>
            @endif
        </div>
    </div>
    
    <!-- Payment Statistics -->
    @if($payments->count() > 0)
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Payment Methods Distribution</h5>
                </div>
                <div class="card-body">
                    @php
                        $methodTotals = $payments->groupBy('payment_method')->map->sum('amount');
                    @endphp
                    <table class="table">
                        @foreach($methodTotals as $method => $total)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $method)) }}</td>
                            <td class="text-end">{{ number_format($total, 0) }} tk</td>
                            <td class="text-end">
                                {{ number_format(($total / $totalPaid) * 100, 1) }}%
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Payment Timeline</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">Last 6 months</small>
                    @php
                        $last6Months = [];
                        for ($i = 5; $i >= 0; $i--) {
                            $date = now()->subMonths($i);
                            $monthYear = $date->format('M Y');
                            $last6Months[$monthYear] = 0;
                        }
                        
                        foreach ($payments as $payment) {
                            $monthYear = $payment->payment_date->format('M Y');
                            if (isset($last6Months[$monthYear])) {
                                $last6Months[$monthYear] += $payment->amount;
                            }
                        }
                    @endphp
                    
                    @foreach($last6Months as $month => $amount)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>{{ $month }}</span>
                            <span>{{ number_format($amount, 0) }} tk</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @if($totalPaid > 0)
                            <div class="progress-bar bg-success" 
                                 role="progressbar" 
                                 style="width: {{ ($amount / $totalPaid) * 100 }}%">
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection