<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
    h1 { font-size: 22px; margin: 0; letter-spacing: 1px; }
    .muted { color: #666; }
    .head { width: 100%; margin-bottom: 14px; }
    .head td { vertical-align: top; }
    .right { text-align: right; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
    table.items th { background: #2c3e50; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px; }
    table.items td { padding: 4px 6px; border-bottom: 1px solid #ddd; }
    table.items tr:nth-child(even) td { background: #f6f8fa; }
    .num { text-align: right; white-space: nowrap; }
    .total td { font-weight: bold; background: #e9ecef !important; border-top: 2px solid #2c3e50; }
    .box { border: 1px solid #ddd; padding: 8px; margin-top: 12px; }
</style>
</head>
<body>
<table class="head"><tr>
    <td><h1>INVOICE</h1><div class="muted">{{ config('app.name') }}</div></td>
    <td class="right">
        @if($invoice)<div><strong>{{ $invoice->invoice_number }}</strong></div>@endif
        <div class="muted">Date: {{ now()->format('d M Y') }}</div>
        @if($itemshipment)<div class="muted">Shipment: {{ $itemshipment->shipment_name }}</div>@endif
    </td>
</tr></table>

<div class="box">
    <strong>Bill to:</strong> {{ $customer->name }} @if($customer->customer_code)({{ $customer->customer_code }})@endif
    @if($customer->phone)<br>{{ $customer->phone }}@endif
    @if($customer->address)<br>{{ $customer->address }}@endif
</div>

<table class="items">
    <thead><tr><th>#</th><th>Goods</th><th>Tracking</th><th class="num">Weight (kg)</th><th class="num">Price / kg</th><th class="num">Fixed price</th><th class="num">Total (BDT)</th></tr></thead>
    <tbody>
    @foreach($itemDetails as $i => $d)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $d['goods_name_en'] }}</td>
            <td>{{ $d['tracking_no'] }}</td>
            <td class="num">{{ number_format($d['weight_kg'], 2) }}</td>
            <td class="num">{{ $d['price_per_kg'] ? number_format($d['price_per_kg'], 2) : '' }}</td>
            <td class="num">{{ $d['price'] ? number_format($d['price'], 2) : '' }}</td>
            <td class="num">{{ $d['has_price'] ? number_format($d['total_price'], 2) : 'not priced' }}</td>
        </tr>
    @endforeach
    <tr class="total"><td colspan="3">TOTAL ({{ $totalItems }} items)</td><td class="num">{{ number_format($totalWeight, 2) }}</td><td></td><td></td><td class="num">{{ number_format($totalPrice, 2) }}</td></tr>
    </tbody>
</table>
@if($itemsWithoutPrice > 0)<p class="muted">{{ $itemsWithoutPrice }} item(s) are not priced yet and are not included in the total.</p>@endif
</body>
</html>
