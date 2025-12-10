@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Customer Bill: {{ $customer->name }} ({{ $customer->customer_code }})</h2>
        <div class="btn-group">
            @if($itemshipment)
                <a href="{{ route('shipments.billing', $itemshipment->id) }}" 
                   class="btn btn-secondary">
                     Back to Shipment Billing
                </a>
            @else
                <a href="{{ route('customers.index') }}" 
                   class="btn btn-secondary">
                     Back to Customers
                </a>
            @endif
            
         

            
            
          
            <button class="btn btn-warning" onclick="window.print()">
                Print Bill
            </button>
        </div>
    </div>
    
    <!-- Customer Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Customer Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Customer Code:</strong> {{ $customer->customer_code }}
                </div>
                <div class="col-md-3">
                    <strong>Email:</strong> {{ $customer->email }}
                </div>
                <div class="col-md-3">
                    <strong>Phone:</strong> {{ $customer->phone }}
                </div>
                <div class="col-md-3">
                    <strong>Address:</strong> {{ $customer->address }}
                </div>
            </div>
            @if($itemshipment)
            <div class="row mt-2">
                <div class="col-md-12">
                    <strong>Shipment:</strong> {{ $itemshipment->shipment_name }} 
                    ({{ $itemshipment->shipment_date }})
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
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
                    <h3>{{ $itemsWithPrice }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Items without Price</h5>
                    <h3>{{ $itemsWithoutPrice }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Bill</h5>
                    <h3>{{ number_format($totalPrice, 2) }} tk</h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bill Details -->
    <div class="card">
        <div class="card-header">
            <h4>Bill Details</h4>
        </div>
        <div class="card-body">
            @if(count($itemDetails) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Tracking No</th>
                            <th>Weight (kg)</th>
                            <th>CBM</th>
                            <th>Fixed Price (tk)</th>
                            <th>Price per kg (tk)</th>
                            <th>Total (tk)</th>
                            <th>Status</th>
                            <th>Shipment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemDetails as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item['goods_name_en'] }}</strong>
                                @if($item['goods_name_local'])
                                <br><small class="text-muted">{{ $item['goods_name_local'] }}</small>
                                @endif
                            </td>
                            <td>{{ $item['tracking_no'] }}</td>
                            <td>{{ number_format($item['weight_kg'], 2) }}</td>
                            <td>{{ number_format($item['cbm'], 3) }}</td>
                            <td>
                                @if($item['price'])
                                {{ number_format($item['price'], 2) }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item['price_per_kg'])
                                {{ number_format($item['price_per_kg'], 2) }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item['has_price'])
                                <strong>{{ number_format($item['total_price'], 2) }}</strong>
                                @else
                                <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($item['has_price'])
                                    <span class="badge bg-success">Priced</span>
                                @else
                                    <span class="badge bg-warning">No Price</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $item['shipments'] }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="3"><strong>TOTALS</strong></td>
                            <td><strong>{{ number_format($totalWeight, 2) }} kg</strong></td>
                            <td></td>
                            <td colspan="2"></td>
                            <td><strong>{{ number_format($totalPrice, 2) }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="alert alert-info">
                 No items found for this customer.
            </div>
            @endif
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="mt-4 text-center">
        <div class="btn-group" role="group">
          
            
            @if($itemshipment)
            <a href="{{ route('shipments.billing', $itemshipment->id) }}" 
               class="btn btn-secondary btn-lg">
                Back to Shipment
            </a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .btn-group, .card-header h5, .d-flex {
            display: none !important;
        }
        .card {
            border: none;
        }
        .card-body {
            padding: 0;
        }
        table {
            font-size: 12px;
        }
    }
</style>
@endpush