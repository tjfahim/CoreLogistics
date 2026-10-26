@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Shipment Billing: {{ $itemshipment->shipment_name }}</h2>
        <div class="btn-group">
            <a href="{{ route('itemsshipments.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>

            {{-- ✅ Update Prices — edit-invoices --}}
            @can('edit-invoices')
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updatePricesModal">
                    <i class="fa fa-edit"></i> Update Prices
                </button>
            @endcan
        </div>
    </div>

    @if(request('highlight'))
        @php
            $highlighted = collect($billingSummary)->firstWhere('customer_id', (int) request('highlight'));
        @endphp
        @if($highlighted)
            <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                <div>
                    <i class="fa fa-search"></i>
                    Showing invoice for <strong>{{ $highlighted['customer_name'] }}</strong>
                    ({{ $highlighted['customer_code'] }}).
                </div>
                <a href="{{ route('shipments.billing', $itemshipment->id) }}" class="btn btn-sm btn-outline-secondary">
                    Clear
                </a>
            </div>
        @endif
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5>Total Items</h5>
                    <h3>{{ $totalItems }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Items with Price</h5>
                    <h3>{{ $totalPricedItems }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Items without Price</h5>
                    <h3>{{ $totalUnpricedItems }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Assigned Items</h5>
                    <h3>{{ $totalAssignedItems }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Bills Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Customer Bills Summary</h4>
            <div>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('ledger.shipment', $itemshipment->id) }}"><i class="fa fa-book"></i> Shipment ledger</a>
                <a class="btn btn-danger btn-sm" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
            </div>
        </div>
        <div class="card-body">
            @if(count($billingSummary) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Customer Code</th>
                            <th>Customer Name</th>
                            <th>Items</th>
                            <th>With Price</th>
                            <th>Without Price</th>
                            <th>Total Weight (kg)</th>
                            <th>Total Price (TK)</th>
                            <th>Paid (TK)</th>
                            <th>Due (TK)</th>
                            <th>Previous Due (TK)</th>
                            <th>Advance (TK)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grandWeight = 0;
                            $grandPrice  = 0;
                            $grandPaid   = 0;
                            $grandDue    = 0;
                            $highlightId = (int) request('highlight');
                        @endphp

                        @foreach($billingSummary as $bill)
                            @php
                                $totalPrice = $bill['total_price'];
                                $paid       = $bill['paid_amount'] ?? 0;
                                $due        = max($totalPrice - $paid, 0);

                                $grandWeight += $bill['total_weight'];
                                $grandPrice  += $totalPrice;
                                $grandPaid   += $paid;
                                $grandDue    += $due;

                                $isHighlighted = $highlightId && $bill['customer_id'] == $highlightId;
                            @endphp
                            <tr id="customer-row-{{ $bill['customer_id'] }}"
                                class="{{ $isHighlighted ? 'table-warning' : '' }}">
                                <td>{{ $bill['customer_code'] }}</td>
                                <td>{{ $bill['customer_name'] }}</td>
                                <td>{{ $bill['total_items'] }}</td>
                                <td>
                                    @if($bill['items_with_price'] > 0)
                                        <span class="badge bg-success">{{ $bill['items_with_price'] }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $bill['items_with_price'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bill['items_without_price'] > 0)
                                        <span class="badge bg-warning">{{ $bill['items_without_price'] }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $bill['items_without_price'] }}</span>
                                    @endif
                                </td>
                                <td>{{ number_format($bill['total_weight'], 0) }}</td>
                                <td>
                                    @if($totalPrice > 0)
                                        <strong>{{ number_format($totalPrice, 0) }}</strong>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($paid > 0)
                                        <strong class="text-success">{{ number_format($paid, 0) }}</strong>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($totalPrice > 0)
                                        @if($due <= 0)
                                            <span class="badge bg-success">PAID</span>
                                        @else
                                            <strong class="text-danger">{{ number_format($due, 0) }}</strong>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>@if(($bill['prev_due'] ?? 0) > 0)<strong class="text-danger">{{ number_format($bill['prev_due'], 0) }}</strong>@else <span class="text-muted">0</span>@endif</td>
                                <td>@if(($bill['advance'] ?? 0) > 0)<strong class="text-success">{{ number_format($bill['advance'], 0) }}</strong>@else <span class="text-muted">0</span>@endif</td>
                                <td>
                                    <a href="{{ route('ledger.customer', $bill['customer_id']) }}" class="btn btn-sm btn-secondary">
    <i class="fa fa-book"></i> Customer Ledger
</a>
                                    @if($bill['customer_id'])
                                        @can('view-invoices')
                                            <a href="{{ route('customers.bill', $bill['customer_id']) }}" class="btn btn-sm btn-dark" title="All shipments incl. previous dues">
                                                <i class="fa fa-file-text-o"></i> Invoice + Previous
                                            </a>
                                        @endcan
                                    @endif
                                    @if($bill['customer_id'])
                                        <div class="d-flex flex-wrap gap-1">
                                            {{-- ✅ Invoice — view-invoices --}}
                                            @can('view-invoices')
                                                <a href="{{ route('customers.shipment.bill', ['customer' => $bill['customer_id'], 'itemshipment' => $itemshipment->id]) }}"
                                                   class="btn btn-sm btn-info">
                                                   Invoice (this shipment)
                                                </a>
                                            @endcan

                                            {{-- ✅ Receive Payment — add-payment --}}
                                            @can('add-payment')
                                                <a href="{{ route('payments.create', ['customer' => $bill['customer_id'], 'itemshipment' => $itemshipment->id]) }}"
                                                   class="btn btn-sm btn-success">
                                                    <i class="fa fa-money-bill-wave"></i> Receive Payment
                                                </a>
                                            @endcan

                                            {{-- ✅ Payment History — view-payment-history --}}
                                            @can('view-payment-history')
                                                <a href="{{ route('payments.history', ['customer' => $bill['customer_id'], 'itemshipment' => $itemshipment->id]) }}"
                                                   class="btn btn-sm btn-info">
                                                    <i class="fa fa-history"></i> Payment History
                                                </a>
                                            @endcan

                                            @cannot('view-invoices')
                                                @cannot('add-payment')
                                                    @cannot('view-payment-history')
                                                        <span class="text-muted">—</span>
                                                    @endcannot
                                                @endcannot
                                            @endcannot
                                        </div>
                                    @else
                                        <span class="text-muted">No customer assigned</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="2"><strong>TOTALS</strong></td>
                            <td><strong>{{ $totalItems }}</strong></td>
                            <td><strong>{{ $totalPricedItems }}</strong></td>
                            <td><strong>{{ $totalUnpricedItems }}</strong></td>
                            <td><strong>{{ number_format($grandWeight, 0) }} kg</strong></td>
                            <td><strong>{{ number_format($grandPrice, 0) }}</strong></td>
                            <td><strong>{{ number_format($grandPaid, 0) }}</strong></td>
                            <td><strong>{{ number_format($grandDue, 0) }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> No items found in this shipment.
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Update Prices Modal — only render if user can edit-invoices --}}
@can('edit-invoices')
<div class="modal fade" id="updatePricesModal" tabindex="-1" aria-labelledby="updatePricesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePricesModalLabel">Update Item Prices</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('shipments.update-prices', $itemshipment->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Update prices for items in this shipment. Leave empty to clear price.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Customer</th>
                                    <th>Weight (kg)</th>
                                    <th>Fixed Price ($)</th>
                                    <th>Price per kg ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $allItems = $itemshipment->cargoItems()->with('customer')->get();
                                @endphp
                                @foreach($allItems as $item)
                                <tr>
                                    <td>
                                        {{ $item->goods_name_en }}<br>
                                        <small class="text-muted">Tracking: {{ $item->tracking_no }}</small>
                                    </td>
                                    <td>{{ $item->customer ? $item->customer->name : 'Unassigned' }}</td>
                                    <td>{{ number_format($item->billing_weight, 0) }}</td>
                                    <td>
                                        <input type="number"
                                               name="prices[{{ $item->id }}][price]"
                                               class="form-control form-control-sm"
                                               step="0.01"
                                               min="0"
                                               value="{{ $item->price }}">
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="prices[{{ $item->id }}][price_per_kg]"
                                               data-group="{{ \Illuminate\Support\Str::lower(trim($item->goods_name_en)) }}|{{ \Illuminate\Support\Str::lower(trim($item->shipping_mark)) }}"
                                               class="form-control form-control-sm per-kg-auto"
                                               step="0.01"
                                               min="0"
                                               value="{{ $item->price_per_kg }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ---- Highlight scroll ----
        var highlightedRow = document.querySelector('.table-warning');
        if (highlightedRow) {
            highlightedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // ---- Auto price: same goods + shipping mark get the same price per kg ----
        const perKgInputs = document.querySelectorAll('.per-kg-auto');
        perKgInputs.forEach(input => {
            input.addEventListener('input', function () {
                perKgInputs.forEach(o => {
                    const fixed = o.closest('tr').querySelector('input[name*="[price]"]');
                    if (o !== this && o.dataset.group === this.dataset.group && !(fixed && fixed.value)) {
                        o.value = this.value;
                    }
                });
            });
        });

        // ---- Price validation (only if modal inputs exist) ----
        const priceInputs = document.querySelectorAll('input[name*="[price]"], input[name*="[price_per_kg]"]');

        priceInputs.forEach(input => {
            input.addEventListener('change', function () {
                const row = this.closest('tr');
                const price = row.querySelector('input[name*="[price]"]').value;
                const pricePerKg = row.querySelector('input[name*="[price_per_kg]"]').value;

                if (price && pricePerKg) {
                    alert('Please enter either Fixed Price OR Price per kg, not both.');
                    this.value = '';
                }
            });
        });
    });
</script>
@endpush