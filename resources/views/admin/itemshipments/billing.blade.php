@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Shipment Billing: {{ $itemshipment->shipment_name }}</h2>
        <div class="btn-group">
            <a href="javascript:history.back()" 
               class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updatePricesModal">
                <i class="fa fa-edit"></i> Update Prices
            </button>
            
        </div>
    </div>
    
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
        <div class="card-header">
            <h4>Customer Bills Summary</h4>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($billingSummary as $bill)
                        <tr>
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
                                @if($bill['total_price'] > 0)
                                <strong>{{ number_format($bill['total_price'], 0) }}</strong>
                                @else
                                <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($bill['customer_id'])
                                <div class="">
                                    <a href="{{ route('customers.shipment.bill', ['customer' => $bill['customer_id'], 'itemshipment' => $itemshipment->id]) }}" 
                                       class="btn btn-sm btn-info" title="View Bill">
                                        View Bill
                                    </a>
                                   <a href="{{ route('payments.create', ['customer' => $bill['customer_id'], 'itemshipment' => $itemshipment->id]) }}" 
           class="btn btn-sm btn-success">
            <i class="fa fa-money-bill-wave"></i> Receive Payment
        </a>
        
        <a href="{{ route('payments.history', ['customer' => $bill['customer_id'], 'itemshipment' => $itemshipment->id]) }}" 
           class="btn btn-sm btn-info">
            <i class="fa fa-history"></i> Payment History
        </a>
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
                            <td>
                                <strong>
                                    {{ number_format(array_sum(array_column($billingSummary, 'total_weight')), 0) }} kg
                                </strong>
                            </td>
                            <td>
                                <strong>
                                    {{ number_format(array_sum(array_column($billingSummary, 'total_price')), 0) }}
                                </strong>
                            </td>
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

<!-- Update Prices Modal -->
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
                                    <td>
                                        {{ $item->customer ? $item->customer->name : 'Unassigned' }}
                                    </td>
                                    <td>{{ number_format($item->weight_kg, 0) }}</td>
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
                                               class="form-control form-control-sm" 
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

@endsection

@push('scripts')
<script>
    // Auto-calculate total price when either field changes
    document.addEventListener('DOMContentLoaded', function() {
        const priceInputs = document.querySelectorAll('input[name*="[price]"], input[name*="[price_per_kg]"]');
        
        priceInputs.forEach(input => {
            input.addEventListener('change', function() {
                const row = this.closest('tr');
                const price = row.querySelector('input[name*="[price]"]').value;
                const pricePerKg = row.querySelector('input[name*="[price_per_kg]"]').value;
                const weight = parseFloat(row.querySelector('td:nth-child(3)').textContent);
                
                if (price && pricePerKg) {
                    alert('Please enter either Fixed Price OR Price per kg, not both.');
                    this.value = '';
                }
            });
        });
    });
</script>
@endpush