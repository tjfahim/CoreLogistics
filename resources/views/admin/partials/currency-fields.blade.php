{{--
  Currency block for payment forms. Pass: $amountId (id of the BDT amount input), $uid (unique suffix).
  The BDT amount input is filled automatically:  foreign amount x rate = final BDT.
--}}
@php $uid = $uid ?? 'x'; @endphp
<div class="cur-box border rounded p-2 mb-3 bg-light" id="curBox{{ $uid }}">
    <div class="row">
        <div class="col-md-3 form-group mb-1">
            <label class="mb-0"><small><strong>Paid in</strong></small></label>
            <select name="currency" class="form-control form-control-sm cur-sel">
                @foreach(\App\Support\PaymentCurrency::CURRENCIES as $k => $label)
                    <option value="{{ $k }}" {{ old('currency', 'bdt') === $k ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 form-group mb-1 cur-foreign d-none">
            <label class="mb-0"><small><strong>Amount (<span class="cur-name">RMB</span>)</strong></small></label>
            <input type="number" step="0.01" min="0" name="foreign_amount" value="{{ old('foreign_amount') }}" class="form-control form-control-sm cur-foreign-amt">
        </div>
        <div class="col-md-3 form-group mb-1 cur-foreign d-none">
            <label class="mb-0"><small><strong>Rate (1 <span class="cur-name">RMB</span> = ? BDT)</strong></small></label>
            <input type="number" step="0.0001" min="0" name="exchange_rate" value="{{ old('exchange_rate') }}" class="form-control form-control-sm cur-rate">
        </div>
        <div class="col-md-3 form-group mb-1 cur-foreign d-none">
            <label class="mb-0"><small><strong>Final price (BDT)</strong></small></label>
            <input type="text" class="form-control form-control-sm font-weight-bold cur-final" readonly>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const box = document.getElementById('curBox{{ $uid }}');
    const amount = document.getElementById('{{ $amountId }}');
    if (!box || !amount) return;
    const sel = box.querySelector('.cur-sel'), fAmt = box.querySelector('.cur-foreign-amt'),
          rate = box.querySelector('.cur-rate'), fin = box.querySelector('.cur-final');
    const wasReadonly = amount.readOnly;
    function sync() {
        const foreign = sel.value !== 'bdt';
        box.querySelectorAll('.cur-foreign').forEach(e => e.classList.toggle('d-none', !foreign));
        box.querySelectorAll('.cur-name').forEach(e => e.textContent = sel.options[sel.selectedIndex].text);
        if (!foreign) { amount.readOnly = wasReadonly; return; }
        amount.readOnly = true;
        const v = (parseFloat(fAmt.value) || 0) * (parseFloat(rate.value) || 0);
        fin.value = v > 0 ? v.toFixed(2) : '';
        amount.value = v > 0 ? v.toFixed(2) : '';
        amount.dispatchEvent(new Event('input', { bubbles: true }));
    }
    [sel, fAmt, rate].forEach(e => e.addEventListener('input', sync));
    sync();
});
</script>
