@extends('admin.layouts.master')
@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4"><div class="page-header float-left"><div class="page-title"><h1>Salary History</h1></div></div></div>
    <div class="col-sm-8"><div class="page-header float-right"><div class="page-title">
        <ol class="breadcrumb text-right"><li><a href="{{ route('employees.index') }}">Employees</a></li><li class="active">Salary History</li></ol>
    </div></div></div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><strong>Month by month</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-dark"><tr><th>Month</th><th class="text-center">People</th><th class="text-right">Total</th></tr></thead>
                        <tbody>
                        @forelse($months as $m)
                            <tr class="{{ $m->year == $year && $m->month == $month ? 'table-primary' : '' }}">
                                <td><a href="{{ route('salary-history', ['year' => $m->year, 'month' => $m->month]) }}">{{ date('F', mktime(0,0,0,$m->month,1)) }} {{ $m->year }}</a></td>
                                <td class="text-center">{{ $m->people }}</td>
                                <td class="text-right">{{ number_format($m->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No salary history yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><strong>{{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</strong></div>
                <div class="card-body">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark"><tr><th>#</th><th>Employee</th><th>Office</th><th class="text-right">Salary paid (BDT)</th></tr></thead>
                        <tbody>
                        @forelse($records as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r->employee_name }} @if(!$r->employee_id)<span class="badge badge-secondary">removed</span>@endif</td>
                                <td>{{ $r->office_name ?? '—' }}</td>
                                <td class="text-right">{{ number_format($r->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No record for this month</td></tr>
                        @endforelse
                        </tbody>
                        <tfoot><tr class="font-weight-bold"><td colspan="3" class="text-right">Total ({{ $records->count() }} people)</td><td class="text-right">{{ number_format($records->sum('amount'), 2) }}</td></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
