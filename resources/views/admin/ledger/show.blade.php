@extends('admin.layouts.master')

@section('main_content')

@php
    // cargo item status -> badge colour (unknown statuses fall back to "secondary")
    $statusColors = [
        'pending'       => 'secondary',
        'packed'        => 'info',
        'ready_to_ship' => 'primary',
        'shipped'       => 'primary',
        'arrived_bd'    => 'warning',
        'delivered'     => 'success',
        'returned'      => 'danger',
    ];
    $billColors = ['paid' => 'success', 'partial' => 'warning', 'due' => 'danger', 'no-billing' => 'secondary'];
    $billLabels = ['paid' => 'Paid', 'partial' => 'Partial', 'due' => 'Due', 'no-billing' => 'No Billing'];
    $methodColors = ['cash' => 'primary', 'bank_transfer' => 'info', 'cheque' => 'warning', 'card' => 'success'];
@endphp

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title"><h1>Ledger — {{ $customer->name }}</h1></div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('ledger.index') }}">Ledger</a></li>
                    <li class="active">{{ $customer->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    {{-- Customer info --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Customer Info</strong>
                    <div>
                        {{-- ✅ Receive payment for the whole customer --}}
                        @can('add-payment')
                            @if($totalDue > 0)
                                <a href="{{ route('payments.receive', $customer->id) }}" class="btn btn-sm btn-success">
                                    <i class="fa fa-money-bill-wave"></i> Receive Payment
                                </a>
                            @else
                                <span class="badge badge-success bg-success p-2">All Clear</span>
                            @endif
                        @endcan
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-warning">
                            <i class="fa fa-edit"></i> Edit Customer
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><strong>Code:</strong> {{ $customer->customer_code }}</div>
                        <div class="col-md-3"><strong>Phone:</strong> {{ $customer->phone }}</div>
                        <div class="col-md-3"><strong>Email:</strong> {{ $customer->email }}</div>
                        <div class="col-md-3"><strong>Address:</strong> {{ $customer->address ?? '—' }}</div>
                    </div>
                    @if(count($customer->all_shipping_marks) > 0)
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <strong>Shipping Marks:</strong>
                                @foreach($customer->all_shipping_marks as $mark)
                                    <span class="badge badge-info bg-info">{{ $mark }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row mt-3">
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Total Billed</h6><h3>Tk {{ number_format($totalBilled, 2) }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Total Paid</h6><h3 class="text-success">Tk {{ number_format($totalPaid, 2) }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Total Due</h6><h3 class="text-danger">Tk {{ number_format($totalDue, 2) }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Shipments</h6>
                <h3>{{ $totals['shipments'] }}</h3>
                <small class="text-muted">{{ $totals['shipments_due'] }} with due · {{ $payments->count() }} payment(s)</small>
            </div></div>
        </div>
    </div>

    {{-- Per-shipment summary --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><strong>Shipment Summary</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Shipment</th><th>Date</th><th>Items</th><th>Weight (kg)</th>
                                <th class="text-right text-end">Billed</th>
                                <th class="text-right text-end">Paid</th>
                                <th class="text-right text-end">Due</th>
                                <th>Status</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shipmentSummaries as $s)
                                @php $sid = $s['shipment']->id; $st = $s['status']; @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('itemsshipments.show', $sid) }}">{{ $s['shipment']->shipment_name }}</a>
                                    </td>
                                    <td>{{ optional($s['shipment']->shipment_date)->format('Y-m-d') ?? '—' }}</td>
                                    <td>
                                        <strong>{{ $s['total_items'] }}</strong> item(s)
                                        @if($s['unpriced_items'] > 0)
                                            <small class="text-danger">({{ $s['unpriced_items'] }} no price)</small>
                                        @endif
                                        <div>
                                            @foreach($s['items']->groupBy('status') as $status => $group)
                                                <span class="badge badge-{{ $statusColors[$status] ?? 'secondary' }} bg-{{ $statusColors[$status] ?? 'secondary' }}">
                                                    {{ ucwords(str_replace('_', ' ', $status ?: 'unknown')) }}: {{ $group->count() }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>{{ number_format($s['total_weight'], 2) }}</td>
                                    <td class="text-right text-end">Tk {{ number_format($s['billed'], 2) }}</td>
                                    <td class="text-right text-end text-success">Tk {{ number_format($s['paid'], 2) }}</td>
                                    <td class="text-right text-end text-danger">Tk {{ number_format($s['due'], 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $billColors[$st] }} bg-{{ $billColors[$st] }}">{{ $billLabels[$st] }}</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" onclick="openModal('itemsModal{{ $sid }}')">
                                            <i class="fa fa-list"></i> Items
                                        </button>

                                        @can('add-payment')
                                            @if($s['due'] > 0)
                                                <a href="{{ route('payments.receive', ['customer' => $customer->id, 'shipment' => $sid]) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fa fa-money-bill-wave"></i> Receive
                                                </a>
                                            @endif
                                        @endcan

                                        @can('view-invoices')
                                            <a href="{{ route('customers.shipment.bill', ['customer' => $customer->id, 'itemshipment' => $sid]) }}"
                                               class="btn btn-sm btn-secondary">Invoice</a>
                                        @endcan

                                        @can('view-payment-history')
                                            <a href="{{ route('payments.history', ['customer' => $customer->id, 'itemshipment' => $sid]) }}"
                                               class="btn btn-sm btn-secondary">
                                                <i class="fa fa-history"></i> History
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center">No shipments found.</td></tr>
                            @endforelse
                        </tbody>
                        @if($shipmentSummaries->count() > 0)
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right text-end">Totals</th>
                                <th class="text-right text-end">Tk {{ number_format($totalBilled, 2) }}</th>
                                <th class="text-right text-end text-success">Tk {{ number_format($totalPaid, 2) }}</th>
                                <th class="text-right text-end text-danger">Tk {{ number_format($totalDue, 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Statement of account --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><strong>Statement of Account</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th><th>Description</th><th>Reference</th>
                                <th class="text-right text-end">Debit (Billed)</th>
                                <th class="text-right text-end">Credit (Paid)</th>
                                <th class="text-right text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgerEntries as $entry)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($entry['date'])->format('Y-m-d') }}</td>
                                    <td>
                                        {{ $entry['description'] }}
                                        @if(!empty($entry['details']))
                                            <br><small class="text-muted">{{ $entry['details'] }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $entry['reference'] ?? '—' }}</td>
                                    <td class="text-right text-end">
                                        {{ $entry['type'] === 'debit' ? 'Tk '.number_format($entry['amount'], 2) : '' }}
                                    </td>
                                    <td class="text-right text-end text-success">
                                        {{ $entry['type'] === 'credit' ? 'Tk '.number_format($entry['amount'], 2) : '' }}
                                    </td>
                                    <td class="text-right text-end"><strong>Tk {{ number_format($entry['balance'], 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right text-end">Totals</th>
                                <th class="text-right text-end">Tk {{ number_format($totalBilled, 2) }}</th>
                                <th class="text-right text-end text-success">Tk {{ number_format($totalPaid, 2) }}</th>
                                <th class="text-right text-end">Tk {{ number_format($totalDue, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Full payment history (with the shipment split of each receipt) --}}
    <div class="row mt-3 mb-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><strong>Payment History</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th><th>Method</th><th>Reference</th>
                                <th>Split Across Shipments</th>
                                <th class="text-right text-end">Amount</th>
                                <th>Notes</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                @php $mc = $methodColors[$payment->payment_method] ?? 'secondary'; @endphp
                                <tr>
                                    <td>{{ optional($payment->payment_date)->format('Y-m-d') }}</td>
                                    <td><span class="badge badge-{{ $mc }} bg-{{ $mc }}">{{ ucfirst(str_replace('_',' ', $payment->payment_method)) }}</span></td>
                                    <td>{{ $payment->reference_no ?? '—' }}</td>
                                    <td>
                                        @forelse($payment->allocations as $alloc)
                                            <div>
                                                @if($alloc->itemshipment)
                                                    <a href="{{ route('itemsshipments.show', $alloc->itemshipment->id) }}">{{ $alloc->itemshipment->shipment_name }}</a>
                                                @else
                                                    Shipment #{{ $alloc->itemshipment_id }}
                                                @endif
                                                — <strong>Tk {{ number_format($alloc->amount, 2) }}</strong>
                                            </div>
                                        @empty
                                            <span class="text-muted">Not allocated</span>
                                        @endforelse
                                        @if($payment->paymentItems->count() > 0)
                                            <small class="text-muted">(item-level payment: {{ $payment->paymentItems->count() }} item(s))</small>
                                        @endif
                                    </td>
                                    <td class="text-right text-end"><strong>Tk {{ number_format($payment->amount, 2) }}</strong></td>
                                    <td>{{ $payment->notes ?? '—' }}</td>
                                    <td>
                                        @can('delete-payment')
                                            <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Delete this payment of Tk {{ number_format($payment->amount, 2) }}? Shipment dues will go back up.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No payments recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                        @if($payments->count() > 0)
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right text-end">Total Received</th>
                                <th class="text-right text-end">Tk {{ number_format($payments->sum('amount'), 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ============ Items modal (one per shipment) ============ --}}
@foreach($shipmentSummaries as $s)
    @php $sid = $s['shipment']->id; @endphp
    <div class="modal fade" id="itemsModal{{ $sid }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $s['shipment']->shipment_name }} — {{ $customer->name }}
                        <br>
                        <small class="text-muted">
                            Billed Tk {{ number_format($s['billed'], 2) }} ·
                            Paid Tk {{ number_format($s['paid'], 2) }} ·
                            Due Tk {{ number_format($s['due'], 2) }}
                        </small>
                    </h5>
                    <button type="button" class="btn btn-sm btn-light" onclick="closeModal('itemsModal{{ $sid }}')">&times;</button>
                </div>
                <div class="modal-body">
                    <h6>Items ({{ $s['total_items'] }})</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light table-light">
                                <tr>
                                    <th>#</th><th>Product</th><th>Tracking</th><th>CTN</th>
                                    <th class="text-right text-end">Weight (kg)</th>
                                    <th class="text-right text-end">CBM</th>
                                    <th class="text-right text-end">Price (Tk)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($s['items'] as $i => $item)
                                    @php $ic = $statusColors[$item->status] ?? 'secondary'; @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->goods_name_en }}</strong>
                                            @if($item->goods_name_local)
                                                <br><small class="text-muted">{{ $item->goods_name_local }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->tracking_no ?? '—' }}</td>
                                        <td>{{ $item->printed_ctn_no ?? '—' }}</td>
                                        <td class="text-right text-end">{{ number_format($item->weight_kg, 2) }}</td>
                                        <td class="text-right text-end">{{ number_format($item->cbm, 3) }}</td>
                                        <td class="text-right text-end">
                                            @if($item->hasPrice())
                                                {{ number_format($item->total_price, 2) }}
                                            @else
                                                <span class="text-danger">No price</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $ic }} bg-{{ $ic }}">
                                                {{ ucwords(str_replace('_', ' ', $item->status ?: 'unknown')) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h6>Payments received for this shipment</h6>
                    @if($s['allocations']->count() > 0)
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light table-light">
                                <tr><th>Date</th><th>Method</th><th>Reference</th><th class="text-right text-end">Amount (Tk)</th></tr>
                            </thead>
                            <tbody>
                                @foreach($s['allocations'] as $alloc)
                                    <tr>
                                        <td>{{ $alloc->payment->payment_date->format('d M, Y') }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $alloc->payment->payment_method)) }}</td>
                                        <td>{{ $alloc->payment->reference_no ?? '—' }}</td>
                                        <td class="text-right text-end">{{ number_format($alloc->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-muted">No payment received for this shipment yet.</div>
                    @endif
                </div>
                <div class="modal-footer">
                    @can('add-payment')
                        @if($s['due'] > 0)
                            <a href="{{ route('payments.receive', ['customer' => $customer->id, 'shipment' => $sid]) }}" class="btn btn-primary">
                                Receive Payment
                            </a>
                        @endif
                    @endcan
                    <button type="button" class="btn btn-secondary" onclick="closeModal('itemsModal{{ $sid }}')">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
// Works with Bootstrap 5 or Bootstrap 4 (jQuery) — whichever your layout loads
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