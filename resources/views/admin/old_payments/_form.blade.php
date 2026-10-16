@php
    $payment = $payment ?? null;
    $preset  = $preset ?? [];

    $type       = old('type', $payment->type ?? ($preset['type'] ?? 'received'));
    $shipmentId = old('old_shipment_id', $payment->old_shipment_id ?? ($preset['old_shipment_id'] ?? ''));
    $customerId = old('old_customer_id', $payment->old_customer_id ?? ($preset['old_customer_id'] ?? ''));
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

<div class="row">
    <div class="col-md-4 form-group">
        <label>Type <span class="text-danger">*</span></label>
        <select name="type" id="entryType" class="form-control" required>
            <option value="received" @selected($type === 'received')>Received — collected from customer</option>
            <option value="paid" @selected($type === 'paid')>Paid — shipment bill payment</option>
        </select>
    </div>

    <div class="col-md-4 form-group" id="shipmentGroup">
        <label>Shipment <span class="text-danger" id="shipmentStar">*</span></label>
        <select name="old_shipment_id" id="shipmentSelect" class="form-control">
            <option value="">— Select shipment —</option>
            @foreach($shipments as $s)
                <option value="{{ $s->id }}" @selected($shipmentId == $s->id)>{{ $s->name }} ({{ $s->date->format('d M Y') }})</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 form-group" id="customerGroup">
        <label>Customer <span class="text-danger">*</span></label>
        <select name="old_customer_id" id="customerSelect" class="form-control">
            <option value="">— Select customer —</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected($customerId == $c->id)>{{ $c->name }}{{ $c->number ? ' (' . $c->number . ')' : '' }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label>Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $payment->amount ?? '') }}" required>
    </div>
    <div class="col-md-3 form-group">
        <label>Date <span class="text-danger">*</span></label>
        <input type="date" name="date" class="form-control"
               value="{{ old('date', optional($payment->date ?? null)->format('Y-m-d') ?? date('Y-m-d')) }}" required>
    </div>
    <div class="col-md-3 form-group">
        <label>Method</label>
        <input type="text" name="method" class="form-control" value="{{ old('method', $payment->method ?? '') }}" placeholder="Cash, Bank, bKash...">
    </div>
    <div class="col-md-3 form-group">
        <label>Note</label>
        <input type="text" name="note" class="form-control" value="{{ old('note', $payment->note ?? '') }}">
    </div>
</div>

<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Entry</button>
<a href="{{ route('old-payments.index') }}" class="btn btn-secondary">Cancel</a>

<script>
    // Toggle fields by type: paid -> shipment only, received -> customer (+ optional shipment)
    document.addEventListener('DOMContentLoaded', function () {
        var type = document.getElementById('entryType');
        var customerGroup = document.getElementById('customerGroup');
        var customerSelect = document.getElementById('customerSelect');
        var shipmentSelect = document.getElementById('shipmentSelect');
        var star = document.getElementById('shipmentStar');

        function apply() {
            var paid = type.value === 'paid';
            customerGroup.style.display = paid ? 'none' : '';
            customerSelect.required = !paid;
            shipmentSelect.required = paid;
            star.style.display = paid ? '' : 'none';
            if (paid) { customerSelect.value = ''; }
        }

        type.addEventListener('change', apply);
        apply();
    });
</script>
