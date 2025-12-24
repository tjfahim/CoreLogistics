@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2">Shipment Reports Dashboard</h1>
            <p class="lead">Overview of all shipments and their financial status</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Shipments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalShipments }}</div>
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Cost</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalCost, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Paid</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalPaid, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Due</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalDue, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shipments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Shipments</h6>
            <a href="{{ route('reports.multiple') }}" class="btn btn-primary">
                <i class="fas fa-chart-bar"></i> Generate Multiple Shipments Report
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="shipmentsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Shipment Name</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total Cost</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>Items Count</th>
                            <th>Total Weight (kg)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shipments as $shipment)
                        <tr>
                            <td>{{ $shipment->shipment_name }}</td>
                            <td>{{ $shipment->shipment_date }}</td>
                            <td>
                                <span class="badge badge-{{ $shipment->status === 'active' ? 'success' : 'info' }}">
                                    {{ ucfirst($shipment->status) }}
                                </span>
                            </td>
                            <td>{{ number_format($shipment->total_cost, 2) }}</td>
                            <td>{{ number_format($shipment->total_paid, 2) }}</td>
                            <td>
                                @if($shipment->total_due > 0)
                                    <span class="text-danger">{{ number_format($shipment->total_due, 2) }}</span>
                                @else
                                    <span class="text-success">0.00</span>
                                @endif
                            </td>
                            <td>{{ $shipment->total_items }}</td>
                            <td>{{ number_format($shipment->total_weight, 2) }} kg</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('reports.single', $shipment->id) }}" 
                                       class="btn btn-sm btn-info" title="View Report">
                                         Report
                                    </a>
                                    <a href="{{ route('reports.download.pdf', ['single', $shipment->id]) }}" 
                                       class="btn btn-sm btn-secondary" title="Download PDF">
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $shipments->links() }}
            </div>
        </div>
    </div>

    
</div>
@endsection


