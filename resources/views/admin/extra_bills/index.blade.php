@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid mt-3">
    <h3><i class="fa fa-plus-square"></i> Extra Bills</h3>
    <p class="text-muted">Extra charge for a customer inside a shipment. It is added to his bill and due (customer ledger, invoice, shipment account and reports). A customer can have many extra bills in one shipment.</p>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    @can('manage-extra-bills')
    <div class="card mb-3">
        <div class="card-header"><strong>Add extra bill</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('extra-bills.store') }}" class="form-row align-items-end">
                @csrf
                <input type="hidden" name="filter_shipment_id" value="{{ $shipmentId }}">
                <input type="hidden" name="filter_customer_id" value="{{ $customerId }}">
                <div class="form-group col-md-3"><label>Shipment</label>
                    <select name="items_shipment_id" class="form-control" required>
                        <option value="">-- select --</option>
                        @foreach($shipments as $s)<option value="{{ $s->id }}" @selected(old('items_shipment_id', $shipmentId) == $s->id)>{{ $s->shipment_name }}</option>@endforeach
                    </select></div>
                <div class="form-group col-md-3"><label>Customer</label>
                    <select name="customer_id" class="form-control" required>
                        <option value="">-- select --</option>
                        @foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id', $customerId) == $c->id)>{{ $c->name }} ({{ $c->customer_code }})</option>@endforeach
                    </select></div>
                <div class="form-group col-md-3"><label>Note / title</label><input type="text" name="title" class="form-control" value="{{ old('title') }}" maxlength="255" required></div>
                <div class="form-group col-md-3"><label>Type</label>
                    <select name="bill_type" id="ebType" class="form-control">
                        <option value="fixed" @selected(old('bill_type') === 'fixed')>Fixed amount</option>
                        <option value="per_kg" @selected(old('bill_type') === 'per_kg')>Per kg (rate x customer's final kg)</option>
                    </select></div>
                <div class="form-group col-md-2" id="ebAmount"><label>Amount</label><input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount') }}"></div>
                <div class="form-group col-md-2" id="ebRate" style="display:none"><label>Rate per kg</label><input type="number" step="0.01" min="0" name="rate_per_kg" class="form-control" value="{{ old('rate_per_kg') }}"></div>
                <div class="form-group col-md-2"><label>Date</label><input type="date" name="bill_date" class="form-control" value="{{ old('bill_date', now()->toDateString()) }}"></div>
                <div class="form-group col-md-3"><label>Remark (optional)</label><input type="text" name="note" class="form-control" value="{{ old('note') }}" maxlength="1000"></div>
                <div class="form-group col-md-2"><button class="btn btn-primary btn-block">Add</button></div>
            </form>
        </div>
    </div>
    <script>
    (function () {
        var t = document.getElementById('ebType');
        function sync() {
            var k = t.value === 'per_kg';
            document.getElementById('ebAmount').style.display = k ? 'none' : '';
            document.getElementById('ebRate').style.display = k ? '' : 'none';
        }
        t.addEventListener('change', sync); sync();
    })();
    </script>
    @endcan

    <form method="GET" class="form-inline mb-3">
        <select name="shipment_id" class="form-control form-control-sm mr-2"><option value="">All shipments</option>
            @foreach($shipments as $s)<option value="{{ $s->id }}" @selected($shipmentId == $s->id)>{{ $s->shipment_name }}</option>@endforeach</select>
        <select name="customer_id" class="form-control form-control-sm mr-2"><option value="">All customers</option>
            @foreach($customers as $c)<option value="{{ $c->id }}" @selected($customerId == $c->id)>{{ $c->name }}</option>@endforeach</select>
        <button class="btn btn-secondary btn-sm">Filter</button>
    </form>

    <div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead class="thead-dark"><tr><th>Date</th><th>Shipment</th><th>Customer</th><th>Note</th><th>Type</th><th class="text-right">Kg</th><th class="text-right">Rate</th><th class="text-right">Amount</th><th></th></tr></thead>
        <tbody>
        @forelse($bills as $b)
            <tr>
                <td>{{ optional($b->bill_date)->format('Y-m-d') }}</td>
                <td>{{ \App\Models\ItemsShipment::cleanName(optional($b->itemshipment)->shipment_name) }}</td>
                <td><a href="{{ route('ledger.customer', $b->customer_id) }}">{{ optional($b->customer)->name }}</a></td>
                <td>{{ $b->title }}@if($b->note)<br><small class="text-muted">{{ $b->note }}</small>@endif</td>
                <td>{{ $b->bill_type === 'per_kg' ? 'Per kg' : 'Fixed' }}</td>
                <td class="text-right">{{ $b->bill_type === 'per_kg' ? number_format($b->kg, 2) : '' }}</td>
                <td class="text-right">{{ $b->bill_type === 'per_kg' ? number_format($b->rate_per_kg, 2) : '' }}</td>
                <td class="text-right"><strong>{{ number_format($b->effective, 2) }}</strong></td>
                <td class="text-center">@can('manage-extra-bills')
                    <form method="POST" action="{{ route('extra-bills.destroy', $b->id) }}" onsubmit="return confirm('Delete this extra bill?')">@csrf @method('DELETE')<button class="btn btn-link btn-sm text-danger p-0"><i class="fa fa-trash"></i></button></form>@endcan</td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted">No extra bills.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr><th colspan="7">Total</th><th class="text-right">{{ number_format($bills->sum('effective'), 2) }}</th><th></th></tr></tfoot>
    </table>
    </div>
</div>
@endsection
