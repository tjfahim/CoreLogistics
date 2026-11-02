@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid mt-3">
    <h3><i class="fa fa-university"></i> Cash Book</h3>
    <p class="text-muted">Cash in hand and bank are cumulative: money stays in its account until you spend, transfer or withdraw it.</p>

    @if(!$startDate)
        <div class="alert alert-warning"><strong>Set the starting balance first.</strong> Add an entry of type "Opening balance" (how much cash in hand and in bank you have now, with today's date). Cash book counts only from that date, so old customer dues and old payments do not make cash negative.</div>
    @else
        <div class="alert alert-info py-2">Cash book starts from <strong>{{ $startDate }}</strong> (opening balance date). Customer / agent dues are not cash: cash changes only when money is actually received or paid.</div>
    @endif
    <div class="row mb-3">
        <div class="col-md-4"><div class="card"><div class="card-body text-center"><small>Cash in hand (now)</small><h3 class="{{ $now['cash'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($now['cash'], 2) }}</h3></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body text-center"><small>Bank / mobile banking (now)</small><h3 class="{{ $now['bank'] < 0 ? 'text-danger' : 'text-primary' }}">{{ number_format($now['bank'], 2) }}</h3></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body text-center"><small>Total money (now)</small><h3>{{ number_format($now['total'], 2) }}</h3></div></div></div>
    </div>

    @can('manage-cash-book')
    <div class="card mb-3">
        <div class="card-header"><strong>Add entry</strong> <small class="text-muted">(starting balance, move cash to bank, deposit, withdraw)</small></div>
        <div class="card-body">
            <form method="POST" action="{{ route('cashbook.store') }}" class="form-row align-items-end">
                @csrf
                <div class="form-group col-md-2">
                    <label>Type</label>
                    <select name="type" id="cbType" class="form-control" required>
                        @foreach(\App\Models\FundMovement::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-md-2" id="cbSingle">
                    <label>Account</label>
                    <select name="account" class="form-control">
                        @foreach(\App\Models\FundMovement::ACCOUNTS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-md-2" id="cbFrom" style="display:none">
                    <label>From</label>
                    <select name="from_account" class="form-control">
                        @foreach(\App\Models\FundMovement::ACCOUNTS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-md-2" id="cbTo" style="display:none">
                    <label>To</label>
                    <select name="to_account" class="form-control">
                        @foreach(array_reverse(\App\Models\FundMovement::ACCOUNTS, true) as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-md-2"><label>Date</label><input type="date" name="movement_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                <div class="form-group col-md-2"><label>Amount</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" required></div>
                <div class="form-group col-md-3"><label>Note</label><input type="text" name="note" class="form-control" maxlength="255"></div>
                <div class="form-group col-md-1"><button class="btn btn-primary btn-block">Save</button></div>
            </form>
        </div>
    </div>
    <script>
    (function () {
        var t = document.getElementById('cbType');
        function sync() {
            var tr = t.value === 'transfer';
            document.getElementById('cbSingle').style.display = tr ? 'none' : '';
            document.getElementById('cbFrom').style.display = tr ? '' : 'none';
            document.getElementById('cbTo').style.display = tr ? '' : 'none';
        }
        t.addEventListener('change', sync); sync();
    })();
    </script>
    @endcan

    <form method="GET" class="form-inline mb-3">
        <label class="mr-2">From</label><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm mr-2">
        <label class="mr-2">To</label><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm mr-2">
        <button class="btn btn-secondary btn-sm">Show</button>
    </form>

    <div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead class="thead-dark">
            <tr><th>Date</th><th>Account</th><th>Description</th><th class="text-right">In</th><th class="text-right">Out</th><th class="text-right">Balance (account)</th><th></th></tr>
        </thead>
        <tbody>
            <tr class="table-secondary"><td colspan="3"><strong>Opening ({{ $from }})</strong></td><td colspan="2" class="text-right">Cash {{ number_format($st['opening']['cash'], 2) }}</td><td class="text-right">Bank {{ number_format($st['opening']['bank'], 2) }}</td><td></td></tr>
            @forelse($st['rows'] as $r)
                <tr>
                    <td>{{ $r['date'] }}</td>
                    <td><span class="badge badge-{{ $r['account'] === 'cash' ? 'success' : 'primary' }}">{{ $r['account'] === 'cash' ? 'Cash' : 'Bank' }}</span></td>
                    <td>{{ $r['desc'] }}</td>
                    <td class="text-right text-success">{{ $r['in'] ? number_format($r['in'], 2) : '' }}</td>
                    <td class="text-right text-danger">{{ $r['out'] ? number_format($r['out'], 2) : '' }}</td>
                    <td class="text-right">{{ number_format($r['balance'], 2) }}</td>
                    <td class="text-center">
                        @if($r['movement_id'] && Gate::allows('manage-cash-book') && $r['in'] > 0 || ($r['movement_id'] && $r['out'] > 0 && Gate::allows('manage-cash-book')))
                            <form method="POST" action="{{ route('cashbook.destroy', $r['movement_id']) }}" onsubmit="return confirm('Delete this entry?')">@csrf @method('DELETE')<button class="btn btn-link btn-sm text-danger p-0"><i class="fa fa-trash"></i></button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No entries in this period.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><th colspan="3">In / Out in period</th><th class="text-right">{{ number_format($st['in']['cash'] + $st['in']['bank'], 2) }}</th><th class="text-right">{{ number_format($st['out']['cash'] + $st['out']['bank'], 2) }}</th><th colspan="2"></th></tr>
            <tr class="table-info"><th colspan="3">Closing ({{ $to }})</th><th colspan="2" class="text-right">Cash {{ number_format($st['closing']['cash'], 2) }}</th><th class="text-right">Bank {{ number_format($st['closing']['bank'], 2) }}</th><th></th></tr>
        </tfoot>
    </table>
    </div>

    <p class="text-muted"><small>Cash = receipts / payments made in cash, office &amp; warehouse bills, salaries. Bank = every other payment method (bank transfer, cheque, mobile banking).</small></p>
</div>
@endsection
