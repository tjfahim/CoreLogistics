@extends('admin.layouts.master')

@section('main_content')
@php
    $methodColors = ['cash' => 'primary', 'bank_transfer' => 'info', 'cheque' => 'warning', 'card' => 'success'];
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            Payment History: {{ $customer->name }} ({{ $customer->customer_code }})
            <br>
            <small class="text-muted">Shipment: {{ $itemshipment->shipment_name }}</small>
        </h2>
        <div class="btn-group">
            <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
            <a href="{{ route('ledger.customer', $customer->id) }}" class="btn btn-info">Customer Ledger</a>
            @can('add-payment')
                @if($totalDue > 0)
                    <a href="{{ route('payments.receive', ['customer' => $customer->id, 'shipment' => $itemshipment->id]) }}"
                       class="btn btn-success">New Payment</a>
                @endif
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Financial Summary (this shipment only) -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white"><div class="card-body">
                <h5>Total Bill Amount</h5><h3>{{ number_format($totalAmount, 0) }} tk</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white"><div class="card-body">
                <h5>Total Paid</h5><h3>{{ number_format($totalPaid, 0) }} tk</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white"><div class="card-body">
                <h5>Total Due</h5><h3>{{ number_format($totalDue, 0) }} tk</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white"><div class="card-body">
                <h5>Total Payments</h5><h3>{{ $allocations->count() }}</h3>
            </div></div>
        </div>
    </div>

    <!-- Payment History Table -->
    <div class="card">
        <div class="card-header"><h4>Payment Records</h4></div>
        <div class="card-body">
            @if($allocations->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Paid To This Shipment (tk)</th>
                            <th>Payment Total (tk)</th>
                            <th>Method</th>
                            <th>Reference No</th>
                            <th>Shipment Due After</th>
                            <th>Received By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allocations as $index => $alloc)
                        @php
                            $payment = $alloc->payment;
                            $color   = $methodColors[$payment->payment_method] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                            <td class="text-success fw-bold font-weight-bold">{{ number_format($alloc->amount, 0) }}</td>
                            <td>
                                {{ number_format($payment->amount, 0) }}
                                @if($payment->allocations->count() > 1)
                                    <br><small class="text-muted">split across {{ $payment->allocations->count() }} shipments</small>
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $color }} bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span></td>
                            <td>
                                @if($payment->reference_no)
                                    <code>{{ $payment->reference_no }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-danger">{{ number_format($alloc->remaining_after, 0) }}</td>
                            <td>{{ optional($payment->user)->name ?? '—' }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" onclick="openModal('paymentDetails{{ $alloc->id }}')">
                                    <i class="fa fa-eye"></i>
                                </button>

                                @can('delete-payment')
                                    <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this whole payment of {{ number_format($payment->amount, 2) }} tk (all shipments it covers)?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="2"><strong>TOTALS</strong></td>
                            <td><strong>{{ number_format($totalPaid, 0) }} tk</strong></td>
                            <td colspan="6"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="alert alert-info text-center">
                <i class="fa fa-info-circle fa-2x mb-3"></i>
                <h4>No Payment Records Found</h4>
                <p>No payment has been received for this shipment yet.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Payment Statistics -->
    @if($allocations->count() > 0 && $totalPaid > 0)
    <div class="row mt-4 mb-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5>Payment Methods Distribution</h5></div>
                <div class="card-body">
                    @php
                        $methodTotals = $allocations->groupBy(fn ($a) => $a->payment->payment_method)
                            ->map(fn ($g) => $g->sum('amount'));
                    @endphp
                    <table class="table">
                        @foreach($methodTotals as $method => $total)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $method)) }}</td>
                            <td class="text-right text-end">{{ number_format($total, 0) }} tk</td>
                            <td class="text-right text-end">{{ number_format(($total / $totalPaid) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5>Payment Timeline</h5></div>
                <div class="card-body">
                    <small class="text-muted">Last 6 months</small>
                    @php
                        $last6Months = [];
                        for ($i = 5; $i >= 0; $i--) {
                            $last6Months[now()->subMonths($i)->format('M Y')] = 0;
                        }
                        foreach ($allocations as $a) {
                            $key = $a->payment->payment_date->format('M Y');
                            if (isset($last6Months[$key])) {
                                $last6Months[$key] += $a->amount;
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
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ ($amount / $totalPaid) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Details modals --}}
@foreach($allocations as $alloc)
    @php
        $payment = $alloc->payment;
        $color   = $methodColors[$payment->payment_method] ?? 'secondary';
    @endphp
    <div class="modal fade" id="paymentDetails{{ $alloc->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn btn-sm btn-light" onclick="closeModal('paymentDetails{{ $alloc->id }}')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr><th>Customer:</th><td>{{ $customer->name }} ({{ $customer->customer_code }})</td></tr>
                                <tr><th>Payment Date:</th><td>{{ $payment->payment_date->format('d M, Y') }}</td></tr>
                                <tr><th>Payment Total:</th><td class="text-success fw-bold font-weight-bold">{{ number_format($payment->amount, 0) }} tk</td></tr>
                                <tr><th>Method:</th><td><span class="badge badge-{{ $color }} bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span></td></tr>
                                <tr><th>Reference No:</th><td>{{ $payment->reference_no ?: 'N/A' }}</td></tr>
                                <tr><th>Received By:</th><td>{{ optional($payment->user)->name ?? '—' }}</td></tr>
                                <tr><th>Notes:</th><td>{{ $payment->notes ?: 'No notes' }}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>How this payment was split:</h6>
                            <table class="table table-sm">
                                <thead><tr><th>Shipment</th><th class="text-right text-end">Amount</th></tr></thead>
                                <tbody>
                                    @foreach($payment->allocations as $pa)
                                        <tr class="{{ $pa->itemshipment_id === $itemshipment->id ? 'table-warning' : '' }}">
                                            <td>{{ $pa->itemshipment->shipment_name ?? ('#' . $pa->itemshipment_id) }}</td>
                                            <td class="text-right text-end">{{ number_format($pa->amount, 0) }} tk</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td class="text-right text-end"><strong>{{ number_format($payment->allocations->sum('amount'), 0) }} tk</strong></td>
                                    </tr>
                                </tfoot>
                            </table>

                            @if($payment->paymentItems->count() > 0)
                                <h6 class="mt-3">Items paid (old item-level payment):</h6>
                                <table class="table table-sm">
                                    <thead><tr><th>Item</th><th>Tracking</th><th class="text-right text-end">Amount</th></tr></thead>
                                    <tbody>
                                        @foreach($payment->paymentItems as $paymentItem)
                                            <tr>
                                                <td>{{ $paymentItem->cargoItem->goods_name_en ?? '—' }}</td>
                                                <td>{{ $paymentItem->cargoItem->tracking_no ?? '—' }}</td>
                                                <td class="text-right text-end">{{ number_format($paymentItem->amount, 0) }} tk</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('paymentDetails{{ $alloc->id }}')">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
window.openModal = window.openModal || function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
    } else if (window.jQuery) {
        window.jQuery(el).modal('show');
    }
};
window.closeModal = window.closeModal || function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el).hide();
    } else if (window.jQuery) {
        window.jQuery(el).modal('hide');
    }
};
</script>
@endsection