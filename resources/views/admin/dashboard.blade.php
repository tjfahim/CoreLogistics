@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Dashboard</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    
    <!-- Statistics Row -->
    <div class="row">
        <div class="col-xl-3 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="ti-layout-grid2 text-warning border-warning"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Total Shipments</div>
                            <div class="stat-digit">1,258</div>
                            <div class="stat-text">+12% from last month</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="ti-check-box text-success border-success"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Completed This Month</div>
                            <div class="stat-digit">342</div>
                            <div class="stat-text">85% success rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="ti-money text-primary border-primary"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Monthly Revenue</div>
                            <div class="stat-digit">128,450 tk</div>
                            <div class="stat-text">+18.5% growth</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="ti-stats-up text-info border-info"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Monthly Profit</div>
                            <div class="stat-digit"> 42,850 tk</div>
                            <div class="stat-text">33.3% margin</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Second Row: Customers, Agents, Warehouses -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Total Customers</h4>
                    <div class="flot-container">
                        <div class="text-center mb-3">
                            <h2 class="text-primary">586</h2>
                            <p class="text-muted">Active: 512 | Inactive: 74</p>
                        </div>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" style="width: 87.4%" role="progressbar">
                                <span class="sr-only">87.4% Active</span>
                            </div>
                        </div>
                        <p class="text-muted">
                            <i class="ti-user mr-1"></i>
                            New this month: 42 customers
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Agents & Partners</h4>
                    <div class="flot-container">
                        <div class="text-center mb-3">
                            <h2 class="text-info">28</h2>
                            <p class="text-muted">China: 12 | Bangladesh: 16</p>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <h6>Shipping Agents</h6>
                                <p class="mb-0">8 Active</p>
                            </div>
                            <div>
                                <h6>Customs Agents</h6>
                                <p class="mb-0">6 Active</p>
                            </div>
                            <div>
                                <h6>Delivery Partners</h6>
                                <p class="mb-0">14 Active</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">China Warehouse Status</h4>
                    <div class="flot-container">
                        <div class="text-center mb-3">
                            <h2 class="text-warning">3,258</h2>
                            <p class="text-muted">Items in Storage</p>
                        </div>
                        <div class="mb-3">
                            <h6>Warehouse Capacity</h6>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 65%" role="progressbar">
                                    <span class="sr-only">65% Used</span>
                                </div>
                            </div>
                            <p class="text-muted mt-2">65% utilized | 35% available</p>
                        </div>
                        <p class="text-muted">
                            <i class="ti-package mr-1"></i>
                            Next shipment: 450 items ready
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Third Row: Charts -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4>Shipment Performance - Last 6 Months</h4>
                </div>
                <div class="card-body">
                    <div class="flot-container">
                        <canvas id="shipmentChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4>Shipment Status Distribution</h4>
                </div>
                <div class="card-body">
                    <div class="flot-container">
                        <canvas id="statusPieChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Fourth Row: Pending Bills -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-danger">
                        <i class="ti-alert mr-2"></i>
                        Customer Bill Pending
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Pending Amount</th>
                                    <th>Overdue Days</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>ABC Trading Co.</td>
                                    <td>12,500 tk</td>
                                    <td>15</td>
                                    <td><span class="badge badge-danger">Critical</span></td>
                                </tr>
                                <tr>
                                    <td>XYZ Imports Ltd.</td>
                                    <td>8,250 tk</td>
                                    <td>8</td>
                                    <td><span class="badge badge-warning">Warning</span></td>
                                </tr>
                                <tr>
                                    <td>Global Exporters</td>
                                    <td>5,800</td>
                                    <td>5</td>
                                    <td><span class="badge badge-warning">Warning</span></td>
                                </tr>
                                <tr>
                                    <td>Best Buy Trading</td>
                                    <td>3,200</td>
                                    <td>3</td>
                                    <td><span class="badge badge-info">Normal</span></td>
                                </tr>
                                <tr>
                                    <td>Premium Goods Inc.</td>
                                    <td>1,950</td>
                                    <td>2</td>
                                    <td><span class="badge badge-info">Normal</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <h5>Total Pending: 31,700</h5>
                        <p class="text-muted">From 25 customers</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-warning">
                        <i class="ti-receipt mr-2"></i>
                        Shipment Bill Pending
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Vendor/Agent</th>
                                    <th>Pending Amount</th>
                                    <th>Due Date</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>China Shipping Lines</td>
                                    <td>45,800</td>
                                    <td>2024-12-05</td>
                                    <td><span class="badge badge-danger">High</span></td>
                                </tr>
                                <tr>
                                    <td>Guangzhou Warehouse</td>
                                    <td>12,300</td>
                                    <td>2024-12-10</td>
                                    <td><span class="badge badge-warning">Medium</span></td>
                                </tr>
                                <tr>
                                    <td>Customs Clearance</td>
                                    <td>8,750</td>
                                    <td>2024-12-08</td>
                                    <td><span class="badge badge-warning">Medium</span></td>
                                </tr>
                                <tr>
                                    <td>Port Authority</td>
                                    <td>6,500</td>
                                    <td>2024-12-15</td>
                                    <td><span class="badge badge-info">Low</span></td>
                                </tr>
                                <tr>
                                    <td>Local Transport</td>
                                    <td>4,200</td>
                                    <td>2024-12-12</td>
                                    <td><span class="badge badge-info">Low</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <h5>Total Pending: 77,550</h5>
                        <p class="text-muted">For 8 shipments</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Fifth Row: Recent Activity -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4>Recent Shipment Activity</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tracking #</th>
                                    <th>Customer</th>
                                    <th>Origin</th>
                                    <th>Destination</th>
                                    <th>Status</th>
                                    <th>ETA</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>SH-2024-1258</td>
                                    <td>Alibaba Trading</td>
                                    <td>Guangzhou, CN</td>
                                    <td>Dhaka, BD</td>
                                    <td><span class="badge badge-success">Delivered</span></td>
                                    <td>2024-12-01</td>
                                    <td>8,500</td>
                                </tr>
                                <tr>
                                    <td>SH-2024-1257</td>
                                    <td>Dhaka Electronics</td>
                                    <td>Shenzhen, CN</td>
                                    <td>Chittagong, BD</td>
                                    <td><span class="badge badge-primary">In Transit</span></td>
                                    <td>2024-12-05</td>
                                    <td>12,300</td>
                                </tr>
                                <tr>
                                    <td>SH-2024-1256</td>
                                    <td>Fashion House Ltd.</td>
                                    <td>Shanghai, CN</td>
                                    <td>Dhaka, BD</td>
                                    <td><span class="badge badge-warning">China Warehouse</span></td>
                                    <td>2024-12-10</td>
                                    <td>5,800</td>
                                </tr>
                                <tr>
                                    <td>SH-2024-1255</td>
                                    <td>Tech Imports Inc.</td>
                                    <td>Hong Kong, CN</td>
                                    <td>Dhaka, BD</td>
                                    <td><span class="badge badge-info">Customs Clearance</span></td>
                                    <td>2024-12-03</td>
                                    <td>15,200</td>
                                </tr>
                                <tr>
                                    <td>SH-2024-1254</td>
                                    <td>Global Textiles</td>
                                    <td>Guangzhou, CN</td>
                                    <td>Chittagong, BD</td>
                                    <td><span class="badge badge-secondary">Processing</span></td>
                                    <td>2024-12-12</td>
                                    <td>7,400</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shipment Performance Line Chart
    const ctx1 = document.getElementById('shipmentChart').getContext('2d');
    const shipmentChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Shipments Completed',
                data: [280, 300, 310, 325, 342, 350],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Revenue ($K)',
                data: [98, 105, 112, 120, 128, 135],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Shipment Status Pie Chart
    const ctx2 = document.getElementById('statusPieChart').getContext('2d');
    const statusPieChart = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: ['Delivered', 'In Transit', 'China Warehouse', 'Customs', 'Processing'],
            datasets: [{
                data: [342, 156, 85, 42, 35],
                backgroundColor: [
                    '#28a745',
                    '#007bff',
                    '#ffc107',
                    '#17a2b8',
                    '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>

@endsection