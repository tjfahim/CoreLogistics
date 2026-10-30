@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4"><div class="page-header float-left"><div class="page-title"><h1>Final Weight</h1></div></div></div>
    <div class="col-sm-8"><div class="page-header float-right"><div class="page-title">
        <ol class="breadcrumb text-right">
            <li><a href="{{ route('itemsshipments.index') }}">Shipments</a></li>
            <li><a href="{{ route('itemsshipments.show', $itemshipment->id) }}">{{ $itemshipment->shipment_name }}</a></li>
            <li class="active">Final Weight</li>
        </ol></div></div></div>
</div>

<div class="content mt-3">
<div class="card">
    <div class="card-body">
        <div class="row mb-3 text-center">
            <div class="col-md-3"><div class="border rounded p-2"><small>Items</small><h4>{{ $stats['items'] }}</h4></div></div>
            <div class="col-md-3"><div class="border rounded p-2"><small>With final weight</small><h4 class="text-success">{{ $stats['set'] }} / {{ $stats['items'] }}</h4></div></div>
            <div class="col-md-3"><div class="border rounded p-2"><small>Original weight (kg)</small><h4>{{ number_format($stats['original'], 2) }}</h4></div></div>
            <div class="col-md-3"><div class="border rounded p-2"><small>Billed weight (kg)</small><h4 class="text-primary">{{ number_format($stats['final'], 2) }}</h4></div></div>
        </div>

        <div class="alert alert-info py-2">
            Customer invoices, ledgers and reports use the <strong>final weight</strong>; where it is empty the original weight is used.
            The original weight is never changed. Leave a box empty to go back to the original weight.
        </div>

        <form method="GET" class="form-inline mb-3">
            <select name="filter" class="form-control mr-2" onchange="this.form.submit()">
                <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All items</option>
                <option value="missing" {{ $filter === 'missing' ? 'selected' : '' }}>Final weight missing</option>
                <option value="set" {{ $filter === 'set' ? 'selected' : '' }}>Final weight set</option>
            </select>
            <input type="text" name="search" value="{{ $search }}" class="form-control mr-2" placeholder="CTN / tracking / goods">
            <button class="btn btn-secondary">Filter</button>
        </form>

        @can('edit-final-weight')
        <form method="POST" action="{{ route('itemshipments.final-weight.update', $itemshipment->id) }}">
            @csrf
            <div class="text-right mb-2"><button class="btn btn-success"><i class="fa fa-save"></i> Save All</button></div>
        @endcan

        @php $colors = ['#e3f2fd','#e8f5e9','#fff8e1','#fce4ec','#ede7f6','#e0f7fa','#fbe9e7']; $ci = 0; @endphp
        <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-dark">
                <tr><th>Printed CTN</th><th>Customer</th><th>Shipping Mark</th><th>Tracking</th><th>Goods</th><th class="text-right">Original (kg)</th><th style="width:160px">Final (kg)</th><th class="text-right">Billed (kg)</th></tr>
            </thead>
            <tbody>
            @forelse($groupedItems as $ctn => $items)
                @php $multi = $items->count() > 1; $bg = $multi ? $colors[$ci++ % count($colors)] : ''; @endphp
                @if($multi)
                    <tr style="background: {{ $bg }}">
                        <td colspan="5"><strong>Box {{ $ctn }}</strong> &mdash; {{ $items->count() }} items</td>
                        <td class="text-right">{{ number_format($items->sum('weight_kg'), 2) }}</td>
                        <td>@can('edit-final-weight')<input type="number" step="0.001" min="0" name="box_weight[{{ $ctn }}]" class="form-control form-control-sm" placeholder="whole box (split equally)">@endcan</td>
                        <td class="text-right"><strong>{{ number_format($items->sum('billing_weight'), 2) }}</strong></td>
                    </tr>
                @endif
                @foreach($items as $item)
                    <tr style="background: {{ $bg }}">
                        <td>{{ $item->printed_ctn_no ?? '-' }}</td>
                        <td>{{ optional($item->customer)->name ?? '—' }}</td>
                        <td>{{ $item->shipping_mark }}</td>
                        <td>{{ $item->tracking_no }}</td>
                        <td>{{ $item->goods_name_en }}</td>
                        <td class="text-right">{{ number_format($item->weight_kg, 2) }}</td>
                        <td>
                            @can('edit-final-weight')
                                <input type="number" step="0.001" min="0" name="final_weight[{{ $item->id }}]" value="{{ $item->final_weight_kg !== null ? rtrim(rtrim(number_format($item->final_weight_kg, 3, '.', ''), '0'), '.') : '' }}" class="form-control form-control-sm" placeholder="{{ number_format($item->weight_kg, 2) }}">
                            @else
                                {{ $item->final_weight_kg ?? '-' }}
                            @endcan
                        </td>
                        <td class="text-right">{{ number_format($item->billing_weight, 2) }}
                            @if($item->final_weight_kg !== null)<span class="badge badge-success">final</span>@endif</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="8" class="text-center text-muted">No items.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>

        @can('edit-final-weight')
            <div class="text-right"><button class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save All</button></div>
        </form>
        @endcan
    </div>
</div>
</div>
@endsection
