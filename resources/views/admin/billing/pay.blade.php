@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid mt-3">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            Pay Shipment Bills
            <br><small class="text-muted">One payment, split across the cost lines of one or many shipments</small>
        </h2>
        <a href="{{ route('shipment-bills.index') }}" class="btn btn-secondary">Back to Billing</a>
    </div>

    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- Filter (GET) --}}
    <form method="GET" action="{{ route('shipment-bills.create') }}" class="card mb-3">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label>Agent / Payee</label>
                    <select name="agent_id" class="form-control">
                        <option value="">All agents</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" {{ (int) $agentId === $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Cost Type</label>
                    <select name="cost_type_id" class="form-control">
                        <option value="">All cost types</option>
                        @foreach($costTypes as $t)
                            <option value="{{ $t->id }}" {{ (int) $costTypeId === $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-primary">Filter</button>
                    <a href="{{ route('shipment-bills.create') }}" class="btn btn-outline-secondary">Reset</a>
                    <span class="ml-3 text-muted">Total due shown: <strong class="text-danger">{{ number_format($totalDue, 2) }}</strong> tk</span>
                </div>
            </div>
        </div>
    </form>

    @if($groups->isEmpty())
        <div class="alert alert-success"><i class="fa fa-check-circle"></i> No unpaid shipment costs for this filter.</div>
    @else
    <form id="payForm" action="{{ route('shipment-bills.store') }}" method="POST">
        @csrf
        <input type="hidden" name="agent_id" value="{{ old('agent_id', $agentId) }}">

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">1. Payment Details</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Amount Paid (tk) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="amount"
                               class="form-control form-control-lg" value="{{ old('amount') }}" required>
                        <small class="text-muted">Typing an amount auto-splits it (oldest shipment first). Edit the split below if needed.</small>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            @foreach($methods as $key => $label)
                                <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Reference No</label>
                        <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}" placeholder="Transaction ID / Check No">
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label>Notes</label>
                    <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">2. Split Across Shipment Costs</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="autoBtn">Auto split (oldest first)</button>
                    <button type="button" class="btn btn-sm btn-outline-success" id="allBtn">Pay everything</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearBtn">Clear</button>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Cost Type</th><th>Agent</th><th>Cost Date</th>
                            <th class="text-right">Cost (BDT)</th><th class="text-right">Paid</th>
                            <th class="text-right">Due</th><th style="width:180px;">Pay Now (tk)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $shipmentId => $lines)
                            @php $ship = $lines->first()->itemsShipment; @endphp
                            <tr class="table-secondary shipment-row" data-shipment="{{ $shipmentId }}">
                                <td colspan="5">
                                    <strong>{{ $ship->shipment_name ?? ('Shipment #' . $shipmentId) }}</strong>
                                    <small class="text-muted">{{ optional($ship->shipment_date)->format('d M, Y') }}</small>
                                    <button type="button" class="btn btn-xs btn-sm btn-link fill-shipment" data-shipment="{{ $shipmentId }}">pay full</button>
                                </td>
                                <td class="text-right text-danger"><strong>{{ number_format($lines->sum('due_amount'), 2) }}</strong></td>
                                <td><small class="text-muted">Subtotal: <strong class="ship-total" data-shipment="{{ $shipmentId }}">0.00</strong></small></td>
                            </tr>
                            @foreach($lines as $c)
                                @php
                                    $val = old("allocations.{$c->id}",
                                        ($preShipment === (int) $shipmentId || $preCost === $c->id)
                                            ? number_format($c->due_amount, 2, '.', '') : '');
                                @endphp
                                <tr>
                                    <td>
                                        {{ $c->costType->name ?? '—' }}
                                        @if($c->costType && !$c->costType->have_partial_payment)
                                            <span class="badge badge-warning">full only</span>
                                        @endif
                                        @if($c->currency !== 'bdt')
                                            <br><small class="text-muted">{{ number_format($c->amount, 2) }} {{ $c->currency_label }} @ {{ rtrim(rtrim(number_format($c->exchange_rate_bdt, 4), '0'), '.') }}</small>
                                        @endif
                                    </td>
                                    <td>{{ optional($c->agent)->name ?? '—' }}</td>
                                    <td>{{ optional($c->cost_date)->format('d M, Y') }}</td>
                                    <td class="text-right">{{ number_format($c->final_bdt_cost ?: $c->amount, 2) }}</td>
                                    <td class="text-right text-success">{{ number_format($c->paid_amount, 2) }}</td>
                                    <td class="text-right text-danger">{{ number_format($c->due_amount, 2) }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="{{ $c->due_amount }}"
                                               name="allocations[{{ $c->id }}]" class="form-control alloc-input"
                                               data-due="{{ $c->due_amount }}"
                                               data-partial="{{ $c->costType && $c->costType->have_partial_payment ? 1 : 0 }}"
                                               data-shipment="{{ $shipmentId }}"
                                               value="{{ $val }}" placeholder="0">
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot class="thead-light">
                        <tr>
                            <td colspan="5" class="text-right"><strong>Total Due:</strong></td>
                            <td class="text-right text-danger"><strong>{{ number_format($totalDue, 2) }}</strong></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-right"><strong>Allocated:</strong></td>
                            <td><strong id="allocatedTotal" class="text-success">0.00</strong> tk</td>
                        </tr>
                    </tfoot>
                </table>
                <div id="allocMsg" class="text-danger font-weight-bold"></div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('shipment-bills.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                <i class="fa fa-money"></i> Pay <span id="btnAmount">0.00</span> tk
            </button>
        </div>
    </form>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('payForm');
    if (!form) return;

    const amountInput = document.getElementById('amount');
    const inputs      = Array.from(document.querySelectorAll('.alloc-input'));
    const allocEl     = document.getElementById('allocatedTotal');
    const msgEl       = document.getElementById('allocMsg');
    const submitBtn   = document.getElementById('submitBtn');
    const btnAmount   = document.getElementById('btnAmount');
    const totalDue    = inputs.reduce((s, i) => s + num(i.dataset.due), 0);

    function num(v) { const n = parseFloat(v); return isNaN(n) ? 0 : n; }
    function r2(n)  { return Math.round(n * 100) / 100; }
    function fmt(n) { return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function sumAlloc() { return r2(inputs.reduce((s, i) => s + num(i.value), 0)); }

    // rows are already oldest shipment first; "full only" lines are paid whole or skipped
    function distribute(amount) {
        let rem = amount;
        inputs.forEach(i => {
            const due = num(i.dataset.due);
            let give = Math.min(rem, due);
            if (i.dataset.partial === '0' && give < due) give = 0;
            i.value = give > 0 ? give.toFixed(2) : '';
            rem = r2(rem - give);
        });
    }

    function refresh() {
        const allocated = sumAlloc();
        const amount    = r2(num(amountInput.value));
        let ok = true, msg = '';

        inputs.forEach(i => {
            const v = num(i.value), due = num(i.dataset.due);
            const bad = v > due + 0.005 || (i.dataset.partial === '0' && v > 0 && Math.abs(v - due) > 0.005);
            i.classList.toggle('is-invalid', bad);
            if (bad) ok = false;
        });

        // shipment subtotals
        document.querySelectorAll('.ship-total').forEach(el => {
            el.textContent = fmt(inputs.filter(i => i.dataset.shipment === el.dataset.shipment)
                                       .reduce((s, i) => s + num(i.value), 0));
        });

        allocEl.textContent   = fmt(allocated);
        btnAmount.textContent = fmt(allocated);

        if (!ok) {
            msg = 'A line is more than its due, or a "full only" line is not paid in full.';
        } else if (allocated <= 0) {
            ok = false; msg = 'Enter an amount, or fill the "Pay Now" boxes.';
        } else if (amount > totalDue + 0.005) {
            ok = false; msg = 'Amount is more than the total due (' + fmt(totalDue) + ').';
        } else if (Math.abs(allocated - amount) > 0.009) {
            ok = false;
            msg = amount > allocated
                ? 'Unallocated: ' + fmt(amount - allocated) + ' tk. Split the rest across the lines.'
                : 'Allocated is ' + fmt(allocated - amount) + ' tk more than the paid amount.';
        }

        msgEl.textContent = msg;
        submitBtn.disabled = !ok;
    }

    amountInput.addEventListener('input', function () {
        distribute(Math.min(r2(num(amountInput.value)), totalDue));
        refresh();
    });

    inputs.forEach(i => i.addEventListener('input', function () {
        const s = sumAlloc();
        amountInput.value = s > 0 ? s.toFixed(2) : '';
        refresh();
    }));

    document.querySelectorAll('.fill-shipment').forEach(btn => btn.addEventListener('click', function () {
        inputs.filter(i => i.dataset.shipment === btn.dataset.shipment)
              .forEach(i => i.value = num(i.dataset.due).toFixed(2));
        const s = sumAlloc();
        amountInput.value = s > 0 ? s.toFixed(2) : '';
        refresh();
    }));

    document.getElementById('autoBtn').addEventListener('click', function () {
        distribute(Math.min(r2(num(amountInput.value)), totalDue));
        refresh();
    });
    document.getElementById('allBtn').addEventListener('click', function () {
        inputs.forEach(i => i.value = num(i.dataset.due).toFixed(2));
        amountInput.value = sumAlloc().toFixed(2);
        refresh();
    });
    document.getElementById('clearBtn').addEventListener('click', function () {
        inputs.forEach(i => i.value = '');
        amountInput.value = '';
        refresh();
    });

    // keep the amount in step with any prefilled lines (shipment / cost shortcut, or old input)
    if (sumAlloc() > 0 && !amountInput.value) amountInput.value = sumAlloc().toFixed(2);
    refresh();

    form.addEventListener('submit', function () { submitBtn.disabled = true; });
});
</script>
@endsection
