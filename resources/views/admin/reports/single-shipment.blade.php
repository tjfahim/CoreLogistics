@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2">Shipment Cost Report</h1>
                    <p class="lead mb-0">Shipment: {{ $report['shipment']->shipment_name }}</p>
                    <small class="text-muted">Date: {{ $report['shipment']->shipment_date }}</small>
                </div>
                <div class="btn-group">
                    <a href="{{ route('reports.download.pdf', ['single', $report['shipment']->id]) }}" 
                       class="btn btn-primary">
                         Download PDF
                    </a>
                    <a href="{{ route('reports.dashboard') }}" class="btn btn-secondary">
                          Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Financial Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-muted">Total Cost</h6>
                                <h3 class="font-weight-bold">{{ number_format($report['summary']['total_cost'], 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3 bg-success text-white">
                                <h6>Total Paid</h6>
                                <h3 class="font-weight-bold">{{ number_format($report['summary']['total_paid'], 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3 bg-warning text-white">
                                <h6>Total Due</h6>
                                <h3 class="font-weight-bold">{{ number_format($report['summary']['total_due'], 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3 bg-primary text-white">
                                <h6>Payment Progress</h6>
                                @if($report['summary']['total_cost'] > 0)
                                <h3 class="font-weight-bold">
                                    {{ number_format(($report['summary']['total_paid'] / $report['summary']['total_cost']) * 100, 1) }}%
                                </h3>
                                @else
                                <h3 class="font-weight-bold">0%</h3>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Shipment Details</h6>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> 
                        <span class="badge badge-{{ $report['shipment']->status === 'active' ? 'success' : 'info' }}">
                            {{ ucfirst($report['shipment']->status) }}
                        </span>
                    </p>
                    <p><strong>Total Items:</strong> {{ $report['summary']['total_items'] }}</p>
                    <p><strong>Total Weight:</strong> {{ number_format($report['summary']['total_weight'], 2) }} kg</p>
                    <p><strong>Total CBM:</strong> {{ number_format($report['summary']['total_cbm'], 2) }}</p>
                    @if($report['shipment']->note)
                    <p><strong>Notes:</strong> {{ $report['shipment']->note }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
  <!-- Payment Status Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Status Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="p-3 border rounded">
                                <h6 class="text-muted">Pending</h6>
                                <h3 class="text-danger">{{ number_format($report['summary']['pending_costs'], 2) }}</h3>
                                <small>{{ $report['shipment']->costs->where('status', 'pending')->count() }} costs</small>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="p-3 border rounded">
                                <h6 class="text-muted">Partial</h6>
                                <h3 class="text-warning">{{ number_format($report['summary']['partial_costs'], 2) }}</h3>
                                <small>{{ $report['shipment']->costs->where('status', 'partial')->count() }} costs</small>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="p-3 border rounded">
                                <h6 class="text-muted">Paid</h6>
                                <h3 class="text-success">{{ number_format($report['summary']['paid_costs'], 2) }}</h3>
                                <small>{{ $report['shipment']->costs->where('status', 'paid')->count() }} costs</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
      <!-- Summary Breakdown -->
    <div class="row">
        <!-- Cost Type Summary -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Cost Type Summary</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cost Type</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Paid</th>
                                    <th class="text-right">Due</th>
                                    <th class="text-right">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['cost_type_wise_summary'] as $costType => $data)
                                <tr>
                                    <td>{{ $costType }}</td>
                                    <td class="text-right">{{ number_format($data['total_amount'], 2) }}</td>
                                    <td class="text-right">{{ number_format($data['total_paid'], 2) }}</td>
                                    <td class="text-right">{{ number_format($data['total_due'], 2) }}</td>
                                    <td class="text-right">{{ $data['count'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agent Summary -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Agent Summary</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Agent</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Paid</th>
                                    <th class="text-right">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['agent_wise_summary'] as $agentName => $data)
                                <tr>
                                    <td>{{ $agentName }}</td>
                                    <td class="text-right">{{ number_format($data['total_amount'], 2) }}</td>
                                    <td class="text-right">{{ number_format($data['total_paid'], 2) }}</td>
                                    <td class="text-right">{{ number_format($data['total_due'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
   <!-- Cost Details -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold">Cost Details</h5>
                    <span class="badge badge-dark">{{ $report['shipment']->costs->count() }} Costs</span>
                </div>
            </div>
            <div class="card-body">
                @if($report['shipment']->costs->count() > 0)
                <!-- Cost Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-left-info shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-info font-weight-bold">Total Cost</h6>
                                        <h3 class="mb-0">${{ number_format($report['summary']['total_cost'], 2) }}</h3>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-left-success shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-success font-weight-bold">Total Paid</h6>
                                        <h3 class="mb-0">${{ number_format($report['summary']['total_paid'], 2) }}</h3>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-left-warning shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-warning font-weight-bold">Total Due</h6>
                                        <h3 class="mb-0">${{ number_format($report['summary']['total_due'], 2) }}</h3>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-left-primary shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-primary font-weight-bold">Payment Progress</h6>
                                        <h3 class="mb-0">
                                            @if($report['summary']['total_cost'] > 0)
                                                {{ number_format(($report['summary']['total_paid'] / $report['summary']['total_cost']) * 100, 1) }}%
                                            @else
                                                0%
                                            @endif
                                        </h3>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cost Items - Accordion Style -->
                <div class="cost-accordion" id="costAccordion">
                    @foreach($report['shipment']->costs as $index => $cost)
                    <div class="card mb-3 border shadow-sm">
                        <!-- Cost Header -->
                        <div class="card-header p-0" id="heading{{ $index }}">
                            <button class="btn btn-link w-100 text-left d-flex justify-content-between align-items-center p-3" 
                                    type="button" data-toggle="collapse" data-target="#collapse{{ $index }}" 
                                    aria-expanded="true" aria-controls="collapse{{ $index }}">
                                <div class="d-flex align-items-center">
                                   
                                    <div>
                                        <h6 class="mb-0 font-weight-bold text-dark">{{ $cost->costType->name }}</h6>
                                        <small class="text-muted">
                                            @if($cost->agent)
                                                {{ $cost->agent->name }}
                                                @if($cost->agent->company_name)
                                                    • {{ $cost->agent->company_name }}
                                                @endif
                                            @else
                                                No Agent
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-4">
                                            <h5 class="mb-0 font-weight-bold">${{ number_format($cost->amount, 2) }}</h5>
                                            <small class="text-muted">Amount</small>
                                        </div>
                                        <div>
                                            @if($cost->status === 'paid')
                                                <span class="badge badge-success badge-pill p-2 px-3">
                                                   PAID
                                                </span>
                                            @elseif($cost->status === 'partial')
                                                <span class="badge badge-warning badge-pill p-2 px-3">
                                                    PARTIAL
                                                </span>
                                            @else
                                                <span class="badge badge-danger badge-pill p-2 px-3">
                                                    PENDING
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <!-- Cost Details Collapse -->
                        <div id="collapse{{ $index }}" class="collapse {{ $index == 0 ? 'show' : '' }}" 
                             aria-labelledby="heading{{ $index }}" data-parent="#costAccordion">
                            <div class="card-body">
                                <div class="row">
                                    <!-- Basic Info -->
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-light mb-3">
                                            <div class="card-body">
                                                <h6 class="font-weight-bold mb-3">Cost Information</h6>
                                                <div class="row">
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted d-block">Description</small>
                                                        <span class="font-weight-medium">{{ $cost->description ?: 'No description' }}</span>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted d-block">Date</small>
                                                        <span class="font-weight-medium">{{ $cost->cost_date->format('M d, Y') }}</span>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted d-block">Rate/Kg</small>
                                                        <span class="font-weight-medium">
                                                            @if($cost->rate_per_kg)
                                                                ${{ number_format($cost->rate_per_kg, 2) }}
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted d-block">Total Weight</small>
                                                        <span class="font-weight-medium">
                                                            @if($cost->total_weight)
                                                                {{ number_format($cost->total_weight, 2) }} kg
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Status -->
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-light mb-3">
                                            <div class="card-body">
                                                <h6 class="font-weight-bold mb-3">Payment Status</h6>
                                                <div class="row">
                                                    <div class="col-6 mb-3">
                                                        <div class="text-center p-2 border rounded">
                                                            <small class="text-muted d-block">Amount</small>
                                                            <h5 class="mb-0 font-weight-bold">${{ number_format($cost->amount, 2) }}</h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <div class="text-center p-2 border rounded bg-success text-white">
                                                            <small class="d-block">Paid</small>
                                                            <h5 class="mb-0 font-weight-bold">${{ number_format($cost->paid_amount, 2) }}</h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-center p-2 border rounded bg-warning text-white">
                                                            <small class="d-block">Due</small>
                                                            <h5 class="mb-0 font-weight-bold">${{ number_format($cost->due_amount, 2) }}</h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-center p-2 border rounded bg-info text-white">
                                                            <small class="d-block">Progress</small>
                                                            <h5 class="mb-0 font-weight-bold">
                                                                @if($cost->amount > 0)
                                                                    {{ number_format(($cost->paid_amount / $cost->amount) * 100, 1) }}%
                                                                @else
                                                                    0%
                                                                @endif
                                                            </h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Transactions -->
                                @if($cost->payments->count() > 0)
                                <div class="mt-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <h6 class="font-weight-bold mb-0 mr-3">Payment Transactions</h6>
                                        <span class="badge badge-info">{{ $cost->payments->count() }} payments</span>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-0">
                                                        <div class="d-flex align-items-center">
                                                           
                                                            <span>Date</span>
                                                        </div>
                                                    </th>
                                                    <th class="border-0">
                                                        <div class="d-flex align-items-center">
                                                            
                                                            <span>Amount</span>
                                                        </div>
                                                    </th>
                                                    <th class="border-0">
                                                        <div class="d-flex align-items-center">
                                                            
                                                            <span>Method</span>
                                                        </div>
                                                    </th>
                                                    <th class="border-0">
                                                        <div class="d-flex align-items-center">
                                                            
                                                            <span>Reference</span>
                                                        </div>
                                                    </th>
                                                    <th class="border-0">
                                                        <div class="d-flex align-items-center">
                                                            
                                                            <span>Notes</span>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cost->payments as $payment)
                                                <tr class="border-bottom">
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-2 text-center">
                                                                <div class="text-primary font-weight-bold">{{ $payment->payment_date->format('d') }}</div>
                                                                <div class="text-muted small">{{ $payment->payment_date->format('M') }}</div>
                                                            </div>
                                                            <div class="small text-muted">{{ $payment->payment_date->format('Y') }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="font-weight-bold text-success">${{ number_format($payment->amount, 2) }}</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="badge badge-light border px-3 py-1">
                                                            {{ ucfirst($payment->payment_method) }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($payment->reference_no)
                                                            <code class="bg-light p-1 rounded">{{ $payment->reference_no }}</code>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($payment->notes)
                                                            <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                                                  title="{{ $payment->notes }}">
                                                                {{ $payment->notes }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @else
                                <div class="text-center py-4">
                                    <div class="text-muted mb-2">
                                        
                                        <h5>No Payment Transactions</h5>
                                        <p class="mb-0">No payments have been made for this cost item.</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Summary Footer -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                   
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold">Cost Summary</h6>
                                    <small class="text-muted">Totals across all cost items</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="font-weight-medium">Total Cost:</span>
                                    <span class="font-weight-bold">${{ number_format($report['summary']['total_cost'], 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="font-weight-medium">Total Paid:</span>
                                    <span class="font-weight-bold text-success">${{ number_format($report['summary']['total_paid'], 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="font-weight-medium">Total Due:</span>
                                    <span class="font-weight-bold text-warning">${{ number_format($report['summary']['total_due'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="text-muted mb-3">
                       
                        <h4>No Costs Added</h4>
                        <p class="mb-0">This shipment doesn't have any cost items yet.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

   
  

  
</div>
@endsection
<script>
    // Smooth accordion animation
    $(document).ready(function() {
        $('.collapse').on('show.bs.collapse', function() {
            $(this).prev('.card-header').find('.fa-caret-down').css('transform', 'rotate(180deg)');
        }).on('hide.bs.collapse', function() {
            $(this).prev('.card-header').find('.fa-caret-down').css('transform', 'rotate(0deg)');
        });
    });
</script>
<style>
    .table td, .table th {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.85em;
        padding: 0.35em 0.65em;
    }
     .cost-accordion .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .cost-accordion .card-header button {
        text-decoration: none !important;
        color: #495057 !important;
        transition: all 0.3s ease;
    }
    
    .cost-accordion .card-header button:hover {
        background-color: #f1f3f9;
    }
    
    .cost-accordion .card-header button[aria-expanded="true"] .fa-caret-down {
        transform: rotate(180deg);
    }
    
    .badge-pill {
        border-radius: 50rem !important;
    }
    
    .border-left-info {
        border-left: .25rem solid #36b9cc!important;
    }
    
    .border-left-success {
        border-left: .25rem solid #1cc88a!important;
    }
    
    .border-left-warning {
        border-left: .25rem solid #f6c23e!important;
    }
    
    .border-left-primary {
        border-left: .25rem solid #2b3965ff!important;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(87deg, #23325dff 0, #07153fff 100%)!important;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.02);
    }

</style>
