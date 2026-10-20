@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="mb-2 no-print">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="rp-head">
        <div><h1>Reports</h1><p class="sub">Every report is built from live shipment bills, cost lines and payments. Each one has Excel and Print.</p></div>
    </div>
    @include('admin.partials.rp-nav')

    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Billed (this year)</div><div class="v">{{ number_format($sum['billed'],2) }}</div></div>
        <div class="rp-kpi teal"><div class="l">Received</div><div class="v">{{ number_format($sum['received'],2) }}</div></div>
        <div class="rp-kpi amber"><div class="l">Shipment cost</div><div class="v">{{ number_format($sum['cost'],2) }}</div></div>
        <div class="rp-kpi violet"><div class="l">Profit</div><div class="v {{ $sum['profit']<0?'neg':'' }}">{{ number_format($sum['profit'],2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Receivable now</div><div class="v">{{ number_format($sum['receivable'],2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Payable now</div><div class="v">{{ number_format($sum['payable'],2) }}</div></div>
    </div>

    <div class="rp-grid2">
        @php $cards = [
            ['reports.summary','fa-pie-chart','Business Summary','Company result for any period: billed, received, cost, profit, cost by type/agent, top customers.'],
            ['reports.period','fa-calendar','Month / Date wise','Month-wise or day-wise from date to date with every receipt and supplier payment.'],
            ['reports.shipments','fa-ship','Shipment Report','One shipment or many together: cost lines, customers, profit, cost type matrix.'],
            ['reports.customers','fa-users','Customer Report','Opening, billed, received and closing due for each customer.'],
        ]; @endphp
        @foreach($cards as [$r,$ic,$t,$d])
            <a class="rp-card" href="{{ route($r) }}" style="text-decoration:none;color:inherit;display:block">
                <div class="ttl"><i class="fa {{ $ic }}"></i> {{ $t }}</div><div class="hint" style="margin:6px 0 0">{{ $d }}</div>
            </a>
        @endforeach
        @can('view-office-expense')
            <a class="rp-card" href="{{ route('reports.office') }}" style="text-decoration:none;color:inherit;display:block">
                <div class="ttl"><i class="fa fa-building"></i> Office Expense</div><div class="hint" style="margin:6px 0 0">Office bills, warehouse bills and salaries. Completely separate from shipment accounting.</div>
            </a>
        @endcan
        <a class="rp-card" href="{{ route('ledger.index') }}" style="text-decoration:none;color:inherit;display:block">
            <div class="ttl"><i class="fa fa-book"></i> Customer Ledger</div><div class="hint" style="margin:6px 0 0">Statement per customer with running balance.</div>
        </a>
        <a class="rp-card" href="{{ route('shipment-bills.ledger') }}" style="text-decoration:none;color:inherit;display:block">
            <div class="ttl"><i class="fa fa-book"></i> Shipment Bill Ledger</div><div class="hint" style="margin:6px 0 0">What we owe agents/suppliers, with payments and balance.</div>
        </a>
    </div>
</div>
@endsection
