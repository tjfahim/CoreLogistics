@extends('admin.layouts.master')
@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4"><div class="page-header float-left"><div class="page-title"><h1>Summary Bill</h1></div></div></div>
    <div class="col-sm-8"><div class="page-header float-right"><div class="page-title">
        <ol class="breadcrumb text-right"><li class="active">Summary Bill</li></ol>
    </div></div></div>
</div>

<div class="content mt-3">
    @if(session('success'))
        <div class="col-sm-12"><div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button class="close" data-dismiss="alert"><span>&times;</span></button>
        </div></div>
    @endif
    @if(session('error'))
        <div class="col-sm-12"><div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button class="close" data-dismiss="alert"><span>&times;</span></button>
        </div></div>
    @endif

    {{-- ================= MONTHLY SUMMARY ================= --}}
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Monthly Summary</strong>
                <form method="GET" action="{{ route('warehouse-bills.index') }}" class="form-inline">
                    <label class="mr-1 mb-0">Month</label>
                    <select name="month" class="form-control form-control-sm mr-2">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $month==$m ? 'selected':'' }}>
                                {{ date('F', mktime(0,0,0,$m,1)) }}
                            </option>
                        @endfor
                    </select>

                    <label class="mr-1 mb-0">Year</label>
                    <input type="number" name="year" value="{{ $year }}"
                           class="form-control form-control-sm mr-2" style="width:90px;">

                    <button class="btn btn-secondary btn-sm"><i class="fa fa-search"></i> Load</button>
                </form>
            </div>

            <div class="card-body">
                {{-- Totals --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-primary mb-2">
                            <div class="card-body text-center py-3">
                                <small class="text-uppercase text-muted d-block">Office Bills</small>
                                <strong>{{ number_format($officeBillsTotal, 2) }} BDT</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info mb-2">
                            <div class="card-body text-center py-3">
                                <small class="text-uppercase text-muted d-block">Employee Salaries @if($salaryInfo['is_estimate'])<span title="No salary record for this month; using current employees">(est.)</span>@else({{ $salaryInfo['count'] }})@endif</small>
                                <strong>{{ number_format($salariesTotal, 2) }} BDT</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success mb-2">
                            <div class="card-body text-center py-3">
                                <small class="text-uppercase text-muted d-block">Grand Total</small>
                                <strong>{{ number_format($grandTotal, 2) }} BDT</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Average KG → per 1 KG --}}
                <form action="{{ route('warehouse-bills.monthly-summary') }}" method="POST" class="row align-items-end">
                    @csrf
                    <input type="hidden" name="year"  value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">

                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Title (optional)</label>
                        <input type="text" name="title" maxlength="120" class="form-control" placeholder="e.g. 1st week">
                    </div>

                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Average KG</label>
                        <input type="number" step="0.01" min="0.01" name="default_kg" id="defaultKgInput"
                               value="{{ old('default_kg') }}"
                               class="form-control" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Per 1 KG Bill (BDT)</label>
                        <input type="text" id="costPerKgPreview"
                               class="form-control font-weight-bold"
                               value="{{ $savedSummary->cost_per_kg ?? '0.0000' }}" readonly>
                        <small class="text-muted">= Grand Total ÷ Average KG</small>
                    </div>

                    <div class="col-md-3 form-group">
                        <button class="btn btn-success btn-block">
                            <i class="fa fa-save"></i>
                            Save as new bill
                        </button>
                        @if($savedSummary)
                            <small class="text-muted d-block mt-1">
                                {{ $monthBills->count() }} bill(s) saved this month · latest {{ number_format($savedSummary->cost_per_kg, 4) }} BDT/kg
                            </small>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============ RECENT SAVED SUMMARIES (only after Save) ============ --}}
    @if($showRecent)
    <div class="col-lg-12 mt-3">
        <div class="card">
            <div class="card-header"><strong>Recent Summary Bills (Saved Summaries)</strong></div>
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Office Bills</th>
                            <th>Salaries</th>
                            <th>Grand Total</th>
                            <th>Avg KG</th>
                            <th>Per 1 KG (BDT)</th>
                            <th>Saved At</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $i => $s)
                        <tr @if($s->year == $year && $s->month == $month) class="table-primary" @endif>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ date('F', mktime(0,0,0,$s->month,1)) }}@if($s->title) <small class="text-muted">· {{ $s->title }}</small>@endif</td>
                            <td>{{ $s->year }}</td>
                            <td>{{ number_format($s->office_bills_total, 2) }}</td>
                            <td>{{ number_format($s->salaries_total, 2) }}</td>
                            <td><strong>{{ number_format($s->grand_total, 2) }}</strong></td>
                            <td>{{ number_format($s->default_kg, 2) }}</td>
                            <td><strong>{{ number_format($s->cost_per_kg, 4) }}</strong></td>
                            <td>{{ $s->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <form action="{{ route('warehouse-bills.summary.destroy', $s->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this summary bill?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center text-muted">No saved summaries yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
const defaultKgEl = document.getElementById('defaultKgInput');
const costPerKgEl = document.getElementById('costPerKgPreview');
const GRAND_TOTAL = {{ (float) $grandTotal }};

function updatePerKgPreview() {
    const kg = parseFloat(defaultKgEl.value) || 0;
    costPerKgEl.value = kg > 0 ? (GRAND_TOTAL / kg).toFixed(4) : '0.0000';
}
if (defaultKgEl && costPerKgEl) {
    defaultKgEl.addEventListener('input', updatePerKgPreview);
}
</script>
@endsection