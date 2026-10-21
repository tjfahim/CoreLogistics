@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
@php
    $isNum = fn ($i) => in_array($i, $sheet['money'] ?? [], true) || in_array($i, $sheet['int'] ?? [], true);
    $fmt = function ($v, $i) use ($sheet) {
        if ($v === null || $v === '') return '';
        if (is_numeric($v) && in_array($i, $sheet['money'] ?? [], true)) return number_format((float) $v, 2);
        return $v;
    };
@endphp
<div class="rp">
    <div class="mb-2 no-print"><a href="{{ route('reports.final', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Final report</a></div>
    <div class="rp-head">
        <div><h1>{{ $title }}</h1><p class="sub">{{ $sheet['subtitle'] ?? '' }}</p></div>
        <div class="rp-actions no-print">
            <a class="rp-btn green" href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
            <a class="rp-btn red" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
        </div>
    </div>
    <form class="rp-card no-print" method="GET"><div class="rp-filter">
        <div><label>From</label><input type="date" name="from" value="{{ $from->toDateString() }}"></div>
        <div><label>To</label><input type="date" name="to" value="{{ $to->toDateString() }}"></div>
        <div><button class="rp-btn primary"><i class="fa fa-filter"></i> Show</button></div>
        <div class="rp-presets" style="margin:0">
            @foreach($types as $k => $l)<a href="{{ route('reports.final.detail', ['type' => $k, 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" style="{{ $k === $type ? 'background:#2f5bea;color:#fff' : '' }}">{{ $l }}</a>@endforeach
        </div>
    </div></form>
    <div class="rp-card"><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr>@foreach($sheet['headings'] as $i => $h)<th class="{{ $isNum($i) ? 'num' : '' }}">{{ $h }}</th>@endforeach</tr></thead>
        <tbody>@forelse($sheet['rows'] as $row)<tr>@foreach($row as $i => $v)<td class="{{ $isNum($i) ? 'num' : '' }}">{{ $fmt($v, $i) }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($sheet['headings']) }}" class="rp-empty">Nothing in this period.</td></tr>@endforelse</tbody>
        @if(!empty($sheet['totals']))<tfoot><tr>@foreach($sheet['totals'] as $i => $v)<td class="{{ $isNum($i) ? 'num' : '' }}">{{ $fmt($v, $i) }}</td>@endforeach</tr></tfoot>@endif
    </table></div></div>
</div>
@endsection
