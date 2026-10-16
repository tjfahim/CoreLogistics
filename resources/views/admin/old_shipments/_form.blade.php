@php
    $shipment = $shipment ?? null;

    $itemRows = old('items', $shipment ? $shipment->items->map(fn ($i) => [
        'name'            => $i->name,
        'old_customer_id' => $i->old_customer_id,
        'weight'          => (float) $i->weight,
        'ctn_no'          => $i->ctn_no,
        'cbm'             => (float) $i->cbm,
        'remark'          => $i->remark,
        'shipping_mark'   => $i->shipping_mark,
        'price_per_kg'    => (float) $i->price_per_kg,
        'final_price'     => (float) $i->final_price,
    ])->all() : [[]]);

    $billRows = old('bills', $shipment ? $shipment->bills->map(fn ($b) => [
        'cost_type'   => $b->cost_type,
        'total_price' => (float) $b->total_price,
    ])->all() : [[]]);

    if (empty($itemRows)) $itemRows = [[]];
    if (empty($billRows)) $billRows = [[]];
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Shipment basics --}}
<div class="row">
    <div class="col-md-4 form-group">
        <label>Shipment Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $shipment->name ?? '') }}" required>
    </div>
    <div class="col-md-4 form-group">
        <label>Date <span class="text-danger">*</span></label>
        <input type="date" name="date" class="form-control"
               value="{{ old('date', optional($shipment->date ?? null)->format('Y-m-d') ?? date('Y-m-d')) }}" required>
    </div>
    <div class="col-md-4 form-group">
        <label>Shipping Mark</label>
        <input type="text" name="shipping_mark" class="form-control" value="{{ old('shipping_mark', $shipment->shipping_mark ?? '') }}">
    </div>
</div>

{{-- Items --}}
<hr>
<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Shipment Items</h5>
    <button type="button" class="btn btn-success btn-sm" id="addItemRow">
        <i class="fa fa-plus"></i> Add Item
    </button>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-sm" id="itemsTable">
        <thead class="thead-light">
            <tr>
                <th style="min-width:180px">Customer</th>
                <th style="min-width:150px">Item Name *</th>
                <th style="min-width:90px">Weight (kg)</th>
                <th style="min-width:80px">CTN No</th>
                <th style="min-width:80px">CBM</th>
                <th style="min-width:120px">Shipping Mark</th>
                <th style="min-width:110px">Price / kg</th>
                <th style="min-width:120px">Final Price</th>
                <th style="min-width:130px">Remark</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="itemsBody">
            @foreach($itemRows as $i => $row)
            <tr class="item-row">
                <td>
                    <select name="items[{{ $i }}][old_customer_id]" class="form-control form-control-sm">
                        <option value="">— No customer —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(($row['old_customer_id'] ?? '') == $c->id)>
                                {{ $c->name }}{{ $c->number ? ' (' . $c->number . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="items[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $row['name'] ?? '' }}"></td>
                <td><input type="number" step="0.001" min="0" name="items[{{ $i }}][weight]" class="form-control form-control-sm calc-weight" value="{{ $row['weight'] ?? '' }}"></td>
                <td><input type="text" name="items[{{ $i }}][ctn_no]" class="form-control form-control-sm" value="{{ $row['ctn_no'] ?? '' }}"></td>
                <td><input type="number" step="0.001" min="0" name="items[{{ $i }}][cbm]" class="form-control form-control-sm" value="{{ $row['cbm'] ?? '' }}"></td>
                <td><input type="text" name="items[{{ $i }}][shipping_mark]" class="form-control form-control-sm" value="{{ $row['shipping_mark'] ?? '' }}"></td>
                <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][price_per_kg]" class="form-control form-control-sm calc-rate" value="{{ $row['price_per_kg'] ?? '' }}"></td>
                <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][final_price]" class="form-control form-control-sm calc-final" value="{{ $row['final_price'] ?? '' }}"></td>
                <td><input type="text" name="items[{{ $i }}][remark]" class="form-control form-control-sm" value="{{ $row['remark'] ?? '' }}"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-times"></i></button></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" class="text-right">Customer Bill Total</th>
                <th id="itemsTotal">0.00</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>
</div>
<small class="text-muted">Final price auto-fills as weight × price/kg; you can overwrite it. Leave it blank to let the server calculate.</small>

{{-- Bills --}}
<hr>
<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Shipment Bill</h5>
    <button type="button" class="btn btn-success btn-sm" id="addBillRow">
        <i class="fa fa-plus"></i> Add Cost
    </button>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-sm" id="billsTable">
        <thead class="thead-light">
            <tr>
                <th>Cost Type *</th>
                <th style="width:200px">Total Price *</th>
                <th style="width:50px"></th>
            </tr>
        </thead>
        <tbody id="billsBody">
            @foreach($billRows as $i => $row)
            <tr class="bill-row">
                <td><input type="text" name="bills[{{ $i }}][cost_type]" class="form-control form-control-sm" value="{{ $row['cost_type'] ?? '' }}" placeholder="e.g. Freight, Custom, Local transport"></td>
                <td><input type="number" step="0.01" min="0" name="bills[{{ $i }}][total_price]" class="form-control form-control-sm bill-price" value="{{ $row['total_price'] ?? '' }}"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-times"></i></button></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="text-right">Shipment Bill Total</th>
                <th id="billsTotal">0.00</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-3">
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Shipment</button>
    <a href="{{ route('old-shipments.index') }}" class="btn btn-secondary">Cancel</a>
</div>

{{-- Row templates (__INDEX__ is replaced by JS) --}}
<template id="itemRowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[__INDEX__][old_customer_id]" class="form-control form-control-sm">
                <option value="">— No customer —</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}{{ $c->number ? ' (' . $c->number . ')' : '' }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="items[__INDEX__][name]" class="form-control form-control-sm"></td>
        <td><input type="number" step="0.001" min="0" name="items[__INDEX__][weight]" class="form-control form-control-sm calc-weight"></td>
        <td><input type="text" name="items[__INDEX__][ctn_no]" class="form-control form-control-sm"></td>
        <td><input type="number" step="0.001" min="0" name="items[__INDEX__][cbm]" class="form-control form-control-sm"></td>
        <td><input type="text" name="items[__INDEX__][shipping_mark]" class="form-control form-control-sm"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][price_per_kg]" class="form-control form-control-sm calc-rate"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][final_price]" class="form-control form-control-sm calc-final"></td>
        <td><input type="text" name="items[__INDEX__][remark]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-times"></i></button></td>
    </tr>
</template>

<template id="billRowTemplate">
    <tr class="bill-row">
        <td><input type="text" name="bills[__INDEX__][cost_type]" class="form-control form-control-sm" placeholder="e.g. Freight, Custom, Local transport"></td>
        <td><input type="number" step="0.01" min="0" name="bills[__INDEX__][total_price]" class="form-control form-control-sm bill-price"></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-times"></i></button></td>
    </tr>
</template>

{{-- Inline vanilla JS (no jQuery, no @section needed) --}}
<script>
(function () {
    var itemsBody = document.getElementById('itemsBody');
    var billsBody = document.getElementById('billsBody');
    var itemIndex = itemsBody.querySelectorAll('.item-row').length;
    var billIndex = billsBody.querySelectorAll('.bill-row').length;

    function num(v) {
        var n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }

    function recalcTotals() {
        var items = 0, bills = 0;
        itemsBody.querySelectorAll('.calc-final').forEach(function (el) { items += num(el.value); });
        billsBody.querySelectorAll('.bill-price').forEach(function (el) { bills += num(el.value); });
        document.getElementById('itemsTotal').textContent = items.toFixed(2);
        document.getElementById('billsTotal').textContent = bills.toFixed(2);
    }

    function addRow(templateId, tbody, index) {
        var html = document.getElementById(templateId).innerHTML.replace(/__INDEX__/g, index);
        tbody.insertAdjacentHTML('beforeend', html);
    }

    document.getElementById('addItemRow').addEventListener('click', function () {
        addRow('itemRowTemplate', itemsBody, itemIndex++);
    });

    document.getElementById('addBillRow').addEventListener('click', function () {
        addRow('billRowTemplate', billsBody, billIndex++);
    });

    // remove row (keep at least one row on screen)
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-row');
        if (!btn) return;

        var tbody = btn.closest('tbody');
        if (!tbody || (tbody !== itemsBody && tbody !== billsBody)) return;

        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
        } else {
            tbody.querySelectorAll('input').forEach(function (i) { i.value = ''; });
            tbody.querySelectorAll('select').forEach(function (s) { s.value = ''; });
        }
        recalcTotals();
    });

    // final price = weight x price/kg (you can overwrite it afterwards)
    document.addEventListener('input', function (e) {
        var t = e.target;
        if (t.classList.contains('calc-weight') || t.classList.contains('calc-rate')) {
            var row = t.closest('tr');
            var w = num(row.querySelector('.calc-weight').value);
            var r = num(row.querySelector('.calc-rate').value);
            row.querySelector('.calc-final').value = (w * r).toFixed(2);
            recalcTotals();
        } else if (t.classList.contains('calc-final') || t.classList.contains('bill-price')) {
            recalcTotals();
        }
    });

    recalcTotals();
})();
</script>
