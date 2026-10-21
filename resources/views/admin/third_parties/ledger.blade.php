@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="mb-2"><a href="{{ route('third-parties.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Third parties</a></div>
    <div class="rp-head">
        <div><h1>{{ $party->name }} <small class="text-muted">ledger</small></h1>
            <p class="sub">Date-wise billed / received / due. Closing balance = what this company still owes us (negative = advance).</p></div>
        <div class="rp-actions">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <a class="rp-btn red" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
        </div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    <form class="rp-card" method="GET"><div class="rp-filter">
        <div><label>From</label><input type="date" name="from" value="{{ $from }}"></div>
        <div><label>To</label><input type="date" name="to" value="{{ $to }}"></div>
        <div style="display:flex;gap:8px"><button class="rp-btn primary"><i class="fa fa-filter"></i> Show</button><a class="rp-btn" href="{{ route('third-parties.ledger', $party->id) }}">Reset</a></div>
    </div></form>

    @can('add-payment')
    <div class="rp-card"><div class="ttl"><i class="fa fa-plus-circle"></i> Receive payment from {{ $party->name }}</div>
    <form method="POST" action="{{ route('third-parties.payments.store', $party->id) }}" class="p-2">
        @csrf
        <div class="row">
            <div class="col-md-3 form-group"><label>Amount (BDT) *</label><input type="number" step="0.01" min="0.01" name="amount" id="tpAmount" class="form-control" required></div>
            <div class="col-md-3 form-group"><label>Date *</label><input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control" required></div>
            <div class="col-md-3 form-group"><label>Method *</label><select name="payment_method" class="form-control"><option value="cash">Cash</option><option value="bank_transfer">Bank transfer</option><option value="cheque">Cheque</option><option value="card">Card</option></select></div>
            <div class="col-md-3 form-group"><label>Reference</label><input name="reference_no" class="form-control"></div>
            <div class="col-12">@include('admin.partials.currency-fields', ['amountId' => 'tpAmount', 'uid' => 'tp'])</div>
            <div class="col-md-9 form-group"><label>Notes</label><input name="notes" class="form-control"></div>
            <div class="col-md-3 form-group d-flex align-items-end"><button class="btn btn-success btn-block"><i class="fa fa-save"></i> Save payment</button></div>
        </div>
    </form></div>
    @endcan

    <div class="rp-card"><div class="ttl">Statement</div><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr><th>Date</th><th>Document</th><th>Description</th><th>Details</th><th>Method</th><th class="num">Billed (debit)</th><th class="num">Received (credit)</th><th class="num">Balance (due)</th><th></th></tr></thead>
        <tbody>
            <tr><td colspan="7"><em>Opening balance</em></td><td class="num"><strong>{{ number_format($st['opening'],2) }}</strong></td><td></td></tr>
            @forelse($st['entries'] as $e)
                <tr>
                    <td>{{ $e['date'] }}</td><td>{{ $e['doc'] }}</td><td>{{ $e['desc'] }}</td><td><small>{{ $e['detail'] }}</small></td>
                    <td>{{ $e['method'] ? ucfirst(str_replace('_',' ',$e['method'])) : '' }} <small class="text-muted">{{ $e['reference'] }}</small></td>
                    <td class="num">{{ $e['debit'] ? number_format($e['debit'],2) : '' }}</td>
                    <td class="num text-success">{{ $e['credit'] ? number_format($e['credit'],2) : '' }}</td>
                    <td class="num"><strong>{{ number_format($e['balance'],2) }}</strong></td>
                    <td>@if($e['kind']==='receipt')@can('delete-payment')<form action="{{ route('third-parties.payments.destroy', $e['payment_id']) }}" method="POST" onsubmit="return confirm('Delete this payment?')">@csrf @method('DELETE')<button class="rp-btn sm red"><i class="fa fa-trash"></i></button></form>@endcan @endif</td>
                </tr>
            @empty
                <tr><td colspan="9" class="rp-empty">No entries.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><td colspan="5">SUMMARY ({{ $from ?: 'start' }} → {{ $to ?: 'today' }})</td><td class="num">{{ number_format($st['debit'],2) }}</td><td class="num">{{ number_format($st['credit'],2) }}</td><td class="num">{{ number_format($st['closing'],2) }}</td><td></td></tr></tfoot>
    </table></div></div>

    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Total billed</div><div class="v">{{ number_format($st['totals']['billed'],2) }}</div><div class="s">{{ $st['totals']['shipments'] }} shipment(s)</div></div>
        <div class="rp-kpi teal"><div class="l">Total received</div><div class="v">{{ number_format($st['totals']['paid'],2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Total due</div><div class="v">{{ number_format($st['totals']['due'],2) }}</div></div>
    </div>
</div>
@endsection
