@extends('admin.layouts.master')
@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4"><div class="page-header float-left"><div class="page-title"><h1>Add Office Bills</h1></div></div></div>
    <div class="col-sm-8"><div class="page-header float-right"><div class="page-title">
        <ol class="breadcrumb text-right">
            <li><a href="{{ route('office-bills.index') }}">Office Bills</a></li>
            <li class="active">Add</li>
        </ol>
    </div></div></div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Add Office Bills (one row = one bill)</strong>
                <a href="{{ route('office-bills.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('office-bills.store') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                    <table class="table table-bordered" id="billRows">
                        <thead class="thead-dark">
                            <tr><th style="min-width:170px">Office</th><th style="min-width:150px">Bill type</th><th style="min-width:150px">Date</th>
                                <th style="min-width:130px">Amount (BDT)</th><th>Note</th><th width="50"></th></tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot><tr><td colspan="3" class="text-right"><strong>Total</strong></td><td><strong id="grandTotal">0.00</strong></td><td colspan="2"></td></tr></tfoot>
                    </table>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2" id="addRow"><i class="fa fa-plus"></i> Add another bill</button>
                    <button class="btn btn-primary btn-sm px-3"><i class="fa fa-save"></i> Save all</button>
                </form>
            </div>
        </div>
    </div>
</div>

<datalist id="billTypes">
    <option>Wifi / Internet</option><option>Electricity</option><option>Water</option><option>Gas</option>
    <option>Rent</option><option>Salary</option><option>Tea / Snacks</option><option>Maintenance</option><option>Other</option>
</datalist>

<script>
const OFFICES = @json($offices->map(fn($o) => ['id' => $o->id, 'name' => $o->name]));
const OLD = @json(old('bills', [[]]));
const today = new Date().toISOString().slice(0, 10);
const tbody = document.querySelector('#billRows tbody');
let idx = 0;

function esc(v) { return String(v ?? '').replace(/"/g, '&quot;'); }

function addRow(d = {}) {
    const i = idx++;
    const opts = OFFICES.map(o => `<option value="${o.id}" ${String(d.office_id) === String(o.id) ? 'selected' : ''}>${o.name}</option>`).join('');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><select name="bills[${i}][office_id]" class="form-control form-control-sm" required><option value="">-- Office --</option>${opts}</select></td>
        <td><input list="billTypes" name="bills[${i}][bill_type]" value="${esc(d.bill_type)}" class="form-control form-control-sm" placeholder="Wifi, Rent..."></td>
        <td><input type="date" name="bills[${i}][bill_date]" value="${esc(d.bill_date || today)}" class="form-control form-control-sm" required></td>
        <td><input type="number" step="0.01" min="0" name="bills[${i}][amount]" value="${esc(d.amount)}" class="form-control form-control-sm amt" required></td>
        <td><input type="text" name="bills[${i}][note]" value="${esc(d.note)}" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-danger btn-sm rm"><i class="fa fa-times"></i></button></td>`;
    tbody.appendChild(tr);
    calc();
}
function calc() {
    let t = 0; document.querySelectorAll('.amt').forEach(a => t += parseFloat(a.value) || 0);
    document.getElementById('grandTotal').textContent = t.toFixed(2);
}
document.getElementById('addRow').onclick = () => addRow();
document.addEventListener('input', e => { if (e.target.classList.contains('amt')) calc(); });
document.addEventListener('click', e => {
    const b = e.target.closest('.rm'); if (!b) return;
    if (tbody.children.length > 1) { b.closest('tr').remove(); calc(); }
});
(OLD.length ? OLD : [{}]).forEach(addRow);
</script>
@endsection
