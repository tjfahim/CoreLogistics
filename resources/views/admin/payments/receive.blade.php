@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            Receive Payment: {{ $customer->name }} ({{ $customer->customer_code }})
            <br>
            <small class="text-muted">Enter the amount, then split it across the shipments</small>
        </h2>
        <div class="btn-group">
            <a href="{{ ($return ?? null) === 'billing' && !empty($preselect) ? route('shipments.billing', $preselect) : route('ledger.customer', $customer->id) }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('ledger.customer', $customer->id) }}" class="btn btn-info">Customer Ledger</a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Summary --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white"><div class="card-body">
                <h5>Total Billed</h5><h3>{{ number_format($totals['billed'], 0) }} tk</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white"><div class="card-body">
                <h5>Total Paid</h5><h3>{{ number_format($totals['paid'], 0) }} tk</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white"><div class="card-body">
                <h5>Total Due</h5><h3>{{ number_format($totals['due'], 0) }} tk</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white"><div class="card-body">
                <h5>Shipments with Due</h5><h3>{{ $dueBills->count() }}</h3>
            </div></div>
        </div>
    </div>

    @if($dueBills->count() === 0)
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i> This customer has no due amount. Anything you receive now is saved as advance.
        </div>
    @endif
    @php
        $preselectBill = $dueBills->first(fn ($b) => $b['shipment']->id === (int) $preselect);
        $prefillAmount = old('amount', $preselectBill ? number_format($preselectBill['due'], 2, '.', '') : '');
    @endphp

    <form id="receiveForm" action="{{ route('payments.receive.store', $customer->id) }}" method="POST">
        @csrf
        <input type="hidden" name="return" value="{{ old('return', $return) }}">
        <input type="hidden" name="shipment" value="{{ old('shipment', $preselect ?: '') }}">

        {{-- Payment details --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">1. Payment Details</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3 form-group">
                            <label for="amount" class="form-label">Amount Received (tk) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="1" name="amount" id="amount"
                                   class="form-control form-control-lg" value="{{ $prefillAmount }}" required>
                            <small class="text-muted">Typing an amount auto-splits it (oldest shipment first). You can edit the split below.</small>
                        </div>
                    </div>
                    <div class="col-12">
                        @include('admin.partials.currency-fields', ['amountId' => 'amount', 'uid' => 'rcv'])
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3 form-group">
                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control"
                                   value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3 form-group">
                            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-control form-select" required>
                                <option value="">Select Method</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="mobile_banking" {{ old('payment_method') == 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3 form-group">
                            <label for="reference_no" class="form-label">Reference No (Optional)</label>
                            <input type="text" name="reference_no" id="reference_no" class="form-control"
                                   value="{{ old('reference_no') }}" placeholder="Cheque No, Transaction ID">
                        </div>
                    </div>
                </div>
                <div class="mb-0 form-group">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea name="notes" id="notes" rows="2" class="form-control"
                              placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        @if(($advance ?? 0) > 0)
        <div class="alert alert-info d-flex align-items-center flex-wrap">
            <div class="mr-3 me-3"><strong>Available advance:</strong> {{ number_format($advance, 2) }} tk</div>
            <div class="input-group input-group-sm" style="max-width:300px">
                <span class="input-group-text input-group-prepend">Use advance</span>
                <input type="number" step="0.01" min="0" max="{{ $advance }}" name="advance_used" id="advanceUsed"
                       class="form-control" value="{{ old('advance_used') }}" placeholder="0">
            </div>
            <small class="ml-2 ms-2">Adds to the amount you can split below.</small>
        </div>
        @endif

        {{-- Allocation --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">2. Split Across Shipments</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="autoBtn">Auto split (oldest first)</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearBtn">Clear</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Shipment</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th class="text-right text-end">Billed</th>
                                <th class="text-right text-end">Paid</th>
                                <th class="text-right text-end">Due</th>
                                <th style="width:180px;">Pay Now (tk)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dueBills as $b)
                                @php
                                    $sid = $b['shipment']->id;
                                    $val = old("allocations.$sid", $preselect === $sid ? number_format($b['due'], 2, '.', '') : '');
                                @endphp
                                <tr class="{{ $preselect === $sid ? 'table-warning' : '' }}">
                                    <td><strong>{{ $b['shipment']->shipment_name }}</strong></td>
                                    <td>{{ optional($b['shipment']->shipment_date)->format('d M, Y') ?? '—' }}</td>
                                    <td>{{ $b['total_items'] }} ({{ $b['priced_items'] }} priced)</td>
                                    <td class="text-right text-end">{{ number_format($b['billed'], 0) }}</td>
                                    <td class="text-right text-end text-success">{{ number_format($b['paid'], 0) }}</td>
                                    <td class="text-right text-end text-danger"><strong>{{ number_format($b['due'], 0) }}</strong></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="{{ $b['due'] }}"
                                               name="allocations[{{ $sid }}]"
                                               class="form-control alloc-input"
                                               data-due="{{ $b['due'] }}"
                                               value="{{ $val }}" placeholder="0">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-right text-end"><strong>Total Due:</strong></td>
                                <td class="text-right text-end text-danger"><strong>{{ number_format($dueBills->sum('due'), 0) }}</strong></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-right text-end"><strong>Allocated:</strong></td>
                                <td><strong id="allocatedTotal" class="text-success">0.00</strong> tk</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id="allocMsg" class="text-danger fw-bold font-weight-bold"></div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('ledger.customer', $customer->id) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                <i class="fa fa-money-bill-wave"></i> Receive <span id="btnAmount">0.00</span> tk
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('receiveForm');
    if (!form) return;

    const amountInput  = document.getElementById('amount');
    const inputs       = Array.from(document.querySelectorAll('.alloc-input'));
    const allocatedEl  = document.getElementById('allocatedTotal');
    const msgEl        = document.getElementById('allocMsg');
    const submitBtn    = document.getElementById('submitBtn');
    const btnAmount    = document.getElementById('btnAmount');
    const advEl        = document.getElementById('advanceUsed');
    const advUsed      = () => advEl ? Math.max(num(advEl.value), 0) : 0;
    const totalDue     = inputs.reduce((s, i) => s + num(i.dataset.due), 0);

    function num(v) { const n = parseFloat(v); return isNaN(n) ? 0 : n; }
    function r2(n)  { return Math.round(n * 100) / 100; }
    function fmt(n) { return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    function sumAlloc() { return r2(inputs.reduce((s, i) => s + num(i.value), 0)); }

    // oldest shipment first (rows are already ordered by date)
    function distribute(amount) {
        let rem = amount;
        inputs.forEach(i => {
            const give = Math.min(rem, num(i.dataset.due));
            i.value = give > 0 ? give.toFixed(2) : '';
            rem = r2(rem - give);
        });
    }

    function refresh() {
        const allocated = sumAlloc();
        const amount    = r2(num(amountInput.value));
        let ok = true, msg = '', info = false;

        inputs.forEach(i => {
            if (num(i.value) > num(i.dataset.due) + 0.005) { ok = false; i.classList.add('is-invalid'); }
            else { i.classList.remove('is-invalid'); }
        });

        allocatedEl.textContent = fmt(allocated);
        btnAmount.textContent   = fmt(amount);

        if (!ok) {
            msg = 'One or more allocations are more than that shipment\'s due.';
        } else if (amount <= 0) {
            ok = false; msg = 'Enter the amount received.';
        } else if (allocated > amount + advUsed() + 0.009) {
            ok = false;
            msg = 'Allocated is ' + fmt(allocated - amount - advUsed()) + ' tk more than the received amount (+ advance).';
        } else if (amount + advUsed() - allocated > 0.009) {
            msg = 'Extra ' + fmt(amount + advUsed() - allocated) + ' tk will be saved as customer ADVANCE.';
            info = true;
        }


        msgEl.className = (info ? 'text-success' : 'text-danger') + ' fw-bold font-weight-bold';
        msgEl.textContent = msg;
        submitBtn.disabled = !ok;
    }

    // typing the amount -> auto split
    amountInput.addEventListener('input', function () {
        distribute(Math.min(r2(num(amountInput.value)) + advUsed(), totalDue));
        refresh();
    });
    if (advEl) advEl.addEventListener('input', function () { distribute(Math.min(r2(num(amountInput.value)) + advUsed(), totalDue)); refresh(); });

    // editing a split -> amount follows the total of the split
    // (the received amount stays as typed, so extra money can be kept as advance)
    inputs.forEach(i => i.addEventListener('input', function () {
        if (num(amountInput.value) <= 0) { const s = sumAlloc() - advUsed(); amountInput.value = s > 0 ? s.toFixed(2) : ''; }
        refresh();
    }));

    document.getElementById('autoBtn').addEventListener('click', function () {
        distribute(Math.min(r2(num(amountInput.value)) + advUsed(), totalDue));
        refresh();
    });

    document.getElementById('clearBtn').addEventListener('click', function () {
        inputs.forEach(i => i.value = '');
        amountInput.value = '';
        refresh();
    });

    form.addEventListener('submit', function (e) {
        const count = inputs.filter(i => num(i.value) > 0).length;
        if (!confirm('Receive ' + fmt(num(amountInput.value)) + ' tk (' + count + ' shipment(s) allocated)?')) {
            e.preventDefault();
        }
    });

    refresh(); // initial state (keeps pre-filled / old values)
});
</script>
@endsection