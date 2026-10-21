@extends('admin.layouts.master')

@section('main_content')
<div class="invoice-container">

    <div class="no-print">
        <a href="{{ route('shipments.billing', $itemshipment->id) }}" class="btn btn-outline-secondary btn-sm">Back</a>

        {{-- ✅ Print — view-invoices --}}
        @can('view-invoices')
            <button class="btn btn-dark btn-sm" onclick="window.print()">
                <i class="fa fa-print"></i> Print Bill
            </button>
            <a class="btn btn-danger btn-sm" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}">
                <i class="fa fa-file-pdf-o"></i> PDF
            </a>
        @endcan
    </div>

    @cannot('view-invoices')
        <div class="invoice">
            <div class="alert alert-danger mb-0">
                You do not have permission to view invoices.
            </div>
        </div>
    @endcannot

    @can('view-invoices')
    <div class="invoice">

        {{-- Header --}}
        <div class="inv-header">
            <div class="inv-header-left">
                <h1>INVOICE</h1>
                <p class="inv-meta">
                    Invoice #: <strong>{{ $invoice->invoice_number ?? 'N/A' }}</strong><br>
                    Date: <strong>{{ now()->format('d M Y') }}</strong>
                </p>
            </div>
            <div class="inv-header-right">
                <div class="inv-company">TechLab33</div>
                <p class="inv-company-details">
                    123 Street Name, Dhaka, Bangladesh<br>
                    Phone: +880 1234 56789<br>
                    Email: info@techlab33.com
                </p>
            </div>
        </div>

        {{-- Bill To / Shipment --}}
        <div class="inv-parties">
            <div class="inv-party">
                <div class="inv-party-label">Billed To</div>
                <div class="inv-party-name">{{ $customer->name }}</div>
                <div class="inv-party-details">
                    Code: {{ $customer->customer_code }}<br>
                    Email: {{ $customer->email }}<br>
                    Phone: {{ $customer->phone }}<br>
                    Address: {{ $customer->address }}
                </div>
            </div>
            @if($itemshipment)
                <div class="inv-party">
                    <div class="inv-party-label">Shipment</div>
                    <div class="inv-party-name">{{ $itemshipment->shipment_name }}</div>
                    <div class="inv-party-details">
                        Date: {{ $itemshipment->shipment_date }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Summary strip --}}
        <table class="inv-summary">
            <tr>
                <td>
                    <div class="inv-summary-label">Total Items</div>
                    <div class="inv-summary-value">{{ $totalItems }}</div>
                </td>
                <td>
                    <div class="inv-summary-label">Priced Items</div>
                    <div class="inv-summary-value">{{ $itemsWithPrice }}</div>
                </td>
                <td>
                    <div class="inv-summary-label">Unpriced Items</div>
                    <div class="inv-summary-value">{{ $itemsWithoutPrice }}</div>
                </td>
                <td>
                    <div class="inv-summary-label">Total Bill</div>
                    <div class="inv-summary-value">{{ number_format($totalPrice, 0) }} TK</div>
                </td>
            </tr>
        </table>

        {{-- Items Table --}}
        <table class="inv-table">
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th>Product</th>
                    <th>Tracking</th>
                    <th class="col-num">Weight (kg)</th>
                    <th class="col-num">CBM</th>
                    <th class="col-num">Fixed Price</th>
                    <th class="col-num">Price / kg</th>
                    <th class="col-num">Total</th>
                    <th class="col-status">Status</th>
                    <th>Shipment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemDetails as $index => $item)
                    <tr>
                        <td class="col-num">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item['goods_name_en'] }}</strong>
                            @if($item['goods_name_local'])
                                <div class="inv-sub">{{ $item['goods_name_local'] }}</div>
                            @endif
                        </td>
                        <td>{{ $item['tracking_no'] }}</td>
                        <td class="col-num">{{ number_format($item['weight_kg'], 0) }}</td>
                        <td class="col-num">{{ number_format($item['cbm'], 3) }}</td>
                        <td class="col-num">{{ $item['price'] ? number_format($item['price'], 2) : '—' }}</td>
                        <td class="col-num">{{ $item['price_per_kg'] ? number_format($item['price_per_kg'], 2) : '—' }}</td>
                        <td class="col-num">
                            @if($item['has_price'])
                                <strong>{{ number_format($item['total_price'], 2) }}</strong>
                            @else
                                <span class="inv-muted">—</span>
                            @endif
                        </td>
                        <td class="col-status">
                            <span class="inv-tag {{ $item['has_price'] ? 'inv-tag-priced' : 'inv-tag-unpriced' }}">
                                {{ $item['has_price'] ? 'Priced' : 'No Price' }}
                            </span>
                        </td>
                        <td>{{ $item['shipments'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="inv-total-label">TOTAL</td>
                    <td class="col-num">{{ number_format($totalWeight, 0) }} kg</td>
                    <td class="col-num"></td>
                    <td class="col-num"></td>
                    <td class="col-num"></td>
                    <td class="col-num"><strong>{{ number_format($totalPrice, 2) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>

        {{-- Footer --}}
        <div class="inv-footer">
            <div class="inv-footer-note">
                This is a computer-generated invoice and does not require a signature.
            </div>
            <div class="inv-footer-sign">
                <div class="inv-sign-line"></div>
                <div class="inv-sign-label">Authorized Signature</div>
            </div>
        </div>

    </div>
    @endcan
</div>
@endsection

<style>
    /* ... keep all your existing CSS unchanged ... */
    .invoice-container { background: #f7f7f8; padding: 24px 16px 40px; min-height: 100vh; }
    .no-print { max-width: 900px; margin: 0 auto 16px; display: flex; justify-content: flex-end; gap: 8px; }
    .invoice { background: #fff; max-width: 900px; margin: 0 auto; padding: 40px; border: 1px solid #d1d5db; border-radius: 4px; color: #111; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; }
    .inv-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #111; margin-bottom: 24px; }
    .inv-header-left h1 { font-size: 26px; font-weight: 700; letter-spacing: 2px; margin: 0 0 8px 0; text-transform: uppercase; }
    .inv-meta { margin: 0; font-size: 12px; color: #374151; }
    .inv-header-right { text-align: right; }
    .inv-company { font-size: 16px; font-weight: 700; margin-bottom: 6px; }
    .inv-company-details { margin: 0; font-size: 12px; color: #374151; line-height: 1.6; }
    .inv-parties { display: flex; gap: 16px; margin-bottom: 24px; }
    .inv-party { flex: 1; border: 1px solid #d1d5db; padding: 12px 14px; }
    .inv-party-label { font-size: 10px; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; margin-bottom: 6px; }
    .inv-party-name { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
    .inv-party-details { font-size: 12px; color: #374151; line-height: 1.6; }
    .inv-summary { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .inv-summary td { border: 1px solid #d1d5db; padding: 10px 14px; text-align: center; width: 25%; }
    .inv-summary-label { font-size: 10px; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; margin-bottom: 4px; }
    .inv-summary-value { font-size: 18px; font-weight: 700; }
    .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .inv-table th, .inv-table td { border: 1px solid #d1d5db; padding: 8px 10px; vertical-align: middle; }
    .inv-table thead th { background: #f3f4f6; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #374151; text-align: left; }
    .inv-table tbody tr:nth-child(even) { background: #fafafa; }
    .inv-table tfoot td { background: #f3f4f6; font-size: 12px; border-top: 2px solid #111; }
    .col-num { text-align: right; white-space: nowrap; }
    .col-status { text-align: center; white-space: nowrap; }
    .inv-sub { font-size: 11px; color: #6b7280; }
    .inv-muted { color: #9ca3af; }
    .inv-total-label { font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .inv-tag { display: inline-block; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; padding: 2px 8px; border: 1px solid #111; border-radius: 2px; }
    .inv-tag-priced { background: #111; color: #fff; }
    .inv-tag-unpriced { background: #fff; color: #111; }
    .inv-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; padding-top: 16px; border-top: 1px solid #d1d5db; }
    .inv-footer-note { font-size: 11px; color: #6b7280; max-width: 60%; }
    .inv-footer-sign { text-align: center; min-width: 200px; }
    .inv-sign-line { border-top: 1px solid #111; margin-bottom: 6px; height: 1px; width: 100%; }
    .inv-sign-label { font-size: 11px; color: #374151; }
    @media print { /* keep all your existing print CSS */ }
</style>