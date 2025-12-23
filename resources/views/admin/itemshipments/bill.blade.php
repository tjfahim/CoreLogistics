@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid invoice-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2></h2>
        <div class="btn-group">
            @if($itemshipment)
                <a href="javascript:history.back()"  
                   class="btn btn-secondary">
                     Back
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
    <!-- Header -->
    <div class="invoice-header d-flex justify-content-between align-items-center mb-4">
        <h2>Invoice</h2>
        <div class="company-info text-end">
            <strong>TechLab33</strong><br>
            123 Street Name, Dhaka, Bangladesh<br>
            Phone: +880 1234 56789<br>
            Email: info@techlab33.com
        </div>
    </div>

    <!-- Customer & Shipment Info -->
    <div class="invoice-info d-flex justify-content-between mb-4">
        <div class="info-box p-2 border">
            <strong>Customer Info</strong><br>
            Name: {{ $customer->name }}<br>
            Code: {{ $customer->customer_code }}<br>
            Email: {{ $customer->email }}<br>
            Phone: {{ $customer->phone }}<br>
            Address: {{ $customer->address }}
        </div>
        @if($itemshipment)
        <div class="info-box p-2 border">
            <strong>Shipment Info</strong><br>
            Shipment Name: {{ $itemshipment->shipment_name }}<br>
            Date: {{ $itemshipment->shipment_date }}
        </div>
        @endif
    </div>


     <!-- Summary Cards -->
    <div class="row mb-4 summary-cards">
        <div class="col-md-3 summary-card border">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5>Total Items</h5>
                    <h3>{{ $totalItems }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 summary-card border">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Items with Price</h5>
                    <h3>{{ $itemsWithPrice }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 summary-card   border">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Items without Price</h5>
                    <h3>{{ $itemsWithoutPrice }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 summary-card border">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Bill</h5>
                    <h3>{{ number_format($totalPrice, 2) }} tk</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover">
            <thead>
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
                        <br><small>{{ $item['goods_name_local'] }}</small>
                        @endif
                    </td>
                    <td>{{ $item['tracking_no'] }}</td>
                    <td>{{ number_format($item['weight_kg'], 2) }}</td>
                    <td>{{ number_format($item['cbm'], 3) }}</td>
                    <td>{{ $item['price'] ? number_format($item['price'],2) : '-' }}</td>
                    <td>{{ $item['price_per_kg'] ? number_format($item['price_per_kg'],2) : '-' }}</td>
                    <td>{{ $item['has_price'] ? number_format($item['total_price'],2) : 'Not set' }}</td>
                    <td>
                        <span class="badge {{ $item['has_price'] ? 'bg-success' : 'bg-secondary' }}">
                            {{ $item['has_price'] ? 'Priced' : 'No Price' }}
                        </span>
                    </td>
                    <td>{{ $item['shipments'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">TOTALS</td>
                    <td>{{ number_format($totalWeight, 2) }} kg</td>
                    <td></td>
                    <td colspan="2"></td>
                    <td>{{ number_format($totalPrice,2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

   

    
</div>

@endsection


<style>
 
    
    @media print {

          body * { visibility: hidden; }
    .invoice-container, .invoice-container * { visibility: visible; }

    /* Layout */
    .invoice-container { width: 100%;  padding: 0; font-family: "Times New Roman", serif; }
    .invoice-header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; margin-top: 100px;}
    .invoice-header h2 { font-size: 28px; text-transform: uppercase; letter-spacing: 1px; margin: 0; }
    .company-info { text-align: right; font-size: 14px; }
    .invoice-info { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px; }
    .info-box { border: 1px solid #000; padding: 10px; flex: 1 1 48%; }

    .summary-cards { display: flex; justify-content: space-between; margin-bottom: 20px; }
    .summary-card { flex: 1 1 23%; border: 1px solid #000; padding: 10px; text-align: center; font-size: 14px; margin-right: 1%; }
    .summary-card:last-child { margin-right: 0; }
    .summary-card h5 { margin: 0; font-size: 12px; text-transform: uppercase; }
    .summary-card h3 { margin: 5px 0 0; font-size: 18px; }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    table th, table td { border: 1px solid #000; padding: 6px; }
    thead th { background-color: #000; color: #fff; font-weight: bold; }
    tbody tr:nth-child(even) { background-color: #f2f2f2; }
    tfoot td { font-weight: bold; background-color: #eaeaea; }

    .badge { border: 1px solid #000000ed; color: black !important; background: white !important; padding: 2px 4px; font-size: 12px; }

    .invoice-footer { margin-top: 40px; display: flex; justify-content: space-between; }
    
    /* Hide sidebar */
    #left-panel,
    .left-panel,
    .navbar,
    aside,
    .sidebar,
    #menuToggle {
        display: none !important;
    }

    /* Expand printed content */
    #right-panel {
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    header.header,
    .header,
    .header-menu {
        display: none !important;
    }


    /* Hide non-print elements */
    .btn, .btn-group, nav, .breadcrumbs, .d-flex, .page-title {
        display: none !important;
    }

    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-family: "Times New Roman", serif;
        background: white !important;
    }

    /* Invoice container */
    .container-fluid {
       
        margin: 0 auto;
        width: 95%;
    }

    /* Header styling */
    h2 {
        text-align: center;
        font-size: 26px;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 20px;
        border-bottom: 2px solid black;
        padding-bottom: 10px;
    }

   
    .row.mb-4 {
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
    }

    .row.mb-4 > .col-md-3 {
        flex: 1 1 24% !important; /* make 4 cards fit in a row */
        max-width: 24% !important;
        margin: 0 !important;
    }

    /* Optional: make cards smaller for print */
    .row.mb-4 .card {
        margin-bottom: 0 !important;
    }

    .row strong {
        font-size: 14px;
    }

    /* Invoice table styling */
    table {
        width: 100%;
        border-collapse: collapse !important;
    }

    table th, table td {
        border: 1px solid black !important;
        padding: 6px !important;
        font-size: 13px;
    }

    thead {
        background: #000 !important;
        color: #fff !important;
    }

    tfoot {
        background: #eaeaea !important;
        font-weight: bold;
    }

    /* Make badges simple on print */
    .badge {
        border: 1px solid black !important;
        color: black !important;
        background: white !important;
    }

    /* For better scaling */
    @page {
        margin: 20mm;
    }
}
</style>
