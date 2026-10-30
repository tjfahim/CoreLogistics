@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Manage Prices</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('itemsshipments.index') }}">Shipments</a></li>
                    <li><a href="{{ route('itemsshipments.show', $itemshipment->id) }}">{{ $itemshipment->shipment_name }}</a></li>
                    <li class="active">Manage Prices</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="badge badge-pill badge-success">Success</span> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="badge badge-pill badge-danger">Error</span> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Manage Prices - {{ $itemshipment->shipment_name }}</strong>
                <div>
                    <a href="{{ route('itemsshipments.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">

                {{-- ✅ Page-level guard: need view-price to see, edit-price to edit --}}
                @cannot('view-price')
                    <div class="alert alert-danger mb-0">
                        You do not have permission to view prices.
                    </div>
                @endcannot

                @can('view-price')

                @include('admin.itemshipments._cost_per_kg')

                <!-- Shipment Summary -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Total Items</h6>
                                <h4 class="text-primary">{{ $itemshipment->cargoItems->count() }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Total Weight</h6>
                                <h4 class="text-info">{{ number_format($itemshipment->cargoItems->sum('billing_weight'), 0) }} kg</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Current Total</h6>
                                <h4 class="text-success">{{ number_format($itemshipment->cargoItems->sum('price'), 0) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Status</h6>
                                <span class="badge badge-{{ $itemshipment->status == 'draft' ? 'secondary' : ($itemshipment->status == 'pending' ? 'warning' : 'success') }}">
                                    {{ ucfirst($itemshipment->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form is only rendered if user can edit prices --}}
                @can('edit-price')
                    <form action="{{ route('itemshipments.update-prices', $itemshipment->id) }}" method="POST" id="priceForm">
                        @csrf
                @endcan

                    @can('edit-price')
                    <div class="border rounded p-2 mb-3 bg-light d-flex flex-wrap align-items-center">
                        <label class="mb-0 mr-2 font-weight-bold"><input type="checkbox" id="autoSame" checked>
                            Auto-fill same goods &amp; mark
                        </label>
                        <small class="text-muted mr-4">একটা বক্সে প্রাইস দিলে একই পণ্য/মার্কের সব বক্সে বসে যাবে। কোনো বক্সের আলাদা প্রাইস চাইলে তার "Override" টিক দিন — সেটা আর অন্যদের সাথে বদলাবে না।</small>
                        <div class="input-group input-group-sm" style="max-width:340px">
                            <div class="input-group-prepend"><span class="input-group-text">Price / kg for ALL</span></div>
                            <input type="number" step="0.01" min="0" id="allPrice" class="form-control" placeholder="0.00">
                            <div class="input-group-append"><button type="button" class="btn btn-outline-primary" id="applyAll">Apply to all</button></div>
                        </div>
                    </div>
                    @endcan

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Update Item Prices (Price per KG)</h5>

                        @can('edit-price')
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-save"></i> Update All Prices
                            </button>
                        @endcan
                    </div>

                    @if($itemshipment->cargoItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>CTN NO.</th>
                                        <th>Goods Name</th>
                                        <th>Customer</th>
                                        <th>Shipping Mark</th>
                                        <th>CBM</th>
                                        <th>Weight (kg)</th>
                                        <th>Current Price/KG (৳)</th>
                                        <th>New Price/KG (৳)</th>
                                        @can('edit-price')<th title="Keep this box's own price (don't follow the same-goods group)">Override</th>@endcan
                                        <th>Total Price (৳)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itemshipment->cargoItems as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->printed_ctn_no }}</strong>
                                            @if($item->written_ctn_no)
                                                <br><small class="text-muted">({{ $item->written_ctn_no }})</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->goods_name_en }}</td>
                                        <td>{{ optional($item->customer)->name ?? '—' }}</td>
                                        <td>{{ $item->shipping_mark }}</td>
                                        <td>
                                            {{ number_format($item->length_cm, 0) }}x{{ number_format($item->width_cm, 0) }}x{{ number_format($item->height_cm, 0) }} = {{ number_format($item->cbm ?? 0, 6) }}
                                        </td>
                                        <td class="text-right">{{ number_format($item->billing_weight ?? 0, 0) }}</td>
                                        <td class="text-right text-info">
                                            <strong>{{ $item->price_per_kg ? number_format($item->price_per_kg, 2) : '0.00' }}</strong>
                                        </td>
                                        <td>
                                            @can('edit-price')
                                                <input type="number"
                                                       name="prices[{{ $item->id }}]"
                                                       value="{{ $item->price_per_kg ?? '' }}"
                                                       step="0.01"
                                                       min="0"
                                                       class="form-control form-control-sm price-per-kg"
                                                       data-item-id="{{ $item->id }}"
                                                       data-group="{{ \Illuminate\Support\Str::lower(trim($item->goods_name_en)) }}|{{ \Illuminate\Support\Str::lower(trim($item->shipping_mark)) }}"
                                                       data-weight="{{ $item->billing_weight ?? 0 }}"
                                                       placeholder="0.00">
                                            @else
                                                {{ $item->price_per_kg ? number_format($item->price_per_kg, 2) : '0.00' }}
                                            @endcan
                                        </td>
                                        @can('edit-price')
                                        <td class="text-center">
                                            <input type="checkbox" class="override-check" data-item-id="{{ $item->id }}" title="Override: own price for this box">
                                        </td>
                                        @endcan
                                        <td class="text-right total-price" id="total-price-{{ $item->id }}">
                                            <strong class="text-success">{{ number_format($item->price ?? 0, 0) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $item->status === 'shipping_ongoing' ? 'warning' : ($item->status === 'packed' ? 'info' : 'secondary') }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-info">
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Grand Totals:</strong></td>
                                        <td class="text-right">
                                            <strong>{{ number_format($itemshipment->cargoItems->avg('price_per_kg'), 0) }}</strong>
                                        </td>
                                        <td></td>
                                        <td class="text-right"><strong id="grand-total">{{ number_format($itemshipment->cargoItems->sum('price'), 0) }}</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning text-center">
                            <i class="fa fa-exclamation-triangle"></i> No items found in this shipment.
                        </div>
                    @endif

                    @can('edit-price')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Update Item Prices (Price per KG)</h5>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-save"></i> Update All Prices
                            </button>
                        </div>
                    @endcan

                @can('edit-price')
                    </form>
                @endcan

                @endcan {{-- view-price --}}
            </div>
        </div>
    </div>
</div>

<style>
    .table td, .table th { padding: 0.75rem; vertical-align: middle; }
    .price-per-kg { min-width: 120px; text-align: center; font-weight: bold; }
    .total-price { font-weight: bold; min-width: 100px; }
    .card.bg-light { border: 1px solid #dee2e6; }
    .table tfoot { font-weight: bold; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const priceInputs = document.querySelectorAll('.price-per-kg');
    const grandTotalElement = document.getElementById('grand-total');

    if (priceInputs.length === 0) return; // no edit permission → nothing to do

    function calculateTotalPrice(input) {
        const itemId = input.getAttribute('data-item-id');
        const weight = parseFloat(input.getAttribute('data-weight')) || 0;
        const pricePerKg = parseFloat(input.value) || 0;
        const totalPrice = weight * pricePerKg;

        const totalPriceElement = document.getElementById(`total-price-${itemId}`);
        if (totalPriceElement) {
            totalPriceElement.innerHTML = `<strong class="text-success">৳${totalPrice.toFixed(2)}</strong>`;
        }

        updateGrandTotal();
    }

    function updateGrandTotal() {
        let grandTotal = 0;
        priceInputs.forEach(input => {
            const weight = parseFloat(input.getAttribute('data-weight')) || 0;
            const pricePerKg = parseFloat(input.value) || 0;
            grandTotal += weight * pricePerKg;
        });
        if (grandTotalElement) {
            grandTotalElement.textContent = `৳${grandTotal.toFixed(2)}`;
        }
    }

    const autoSame = document.getElementById('autoSame');

    const overrideOf = id => document.querySelector('.override-check[data-item-id="' + id + '"]');
    const isOverride = input => { const c = overrideOf(input.dataset.itemId); return !!(c && c.checked); };

    // rows whose saved price already differs from the rest of their group start as "override"
    const groups = {};
    priceInputs.forEach(i => { (groups[i.dataset.group] = groups[i.dataset.group] || []).push(i); });
    Object.values(groups).forEach(list => {
        const vals = list.map(i => (parseFloat(i.value) || 0).toFixed(2));
        if (list.length > 1 && new Set(vals).size > 1) {
            const freq = {}; vals.forEach(v => freq[v] = (freq[v] || 0) + 1);
            const common = Object.keys(freq).sort((a, b) => freq[b] - freq[a])[0];
            list.forEach((i, k) => { if (vals[k] !== common) { const c = overrideOf(i.dataset.itemId); if (c) c.checked = true; } });
        }
    });

    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            calculateTotalPrice(this);
            // one box priced -> every other NON-override box of the same goods + shipping mark gets the same price.
            // An overridden box neither spreads its price nor receives one.
            if (autoSame && autoSame.checked && this.dataset.group && !isOverride(this)) {
                const g = this.dataset.group, v = this.value;
                priceInputs.forEach(o => {
                    if (o !== this && o.dataset.group === g && !isOverride(o)) { o.value = v; calculateTotalPrice(o); }
                });
            }
        });
        calculateTotalPrice(input);
    });

    const applyAll = document.getElementById('applyAll');
    if (applyAll) {
        applyAll.addEventListener('click', function () {
            const v = document.getElementById('allPrice').value;
            if (v === '') return;
            priceInputs.forEach(o => { if (isOverride(o)) return; o.value = v; calculateTotalPrice(o); });
        });
    }
});
</script>
@endsection