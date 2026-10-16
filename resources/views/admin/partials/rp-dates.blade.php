@php
    $__q = fn($f,$t) => request()->fullUrlWithQuery(['from'=>$f,'to'=>$t,'export'=>null]);
@endphp
<div class="rp-presets no-print">
    <a href="{{ $__q(now()->toDateString(), now()->toDateString()) }}">Today</a>
    <a href="{{ $__q(now()->startOfMonth()->toDateString(), now()->toDateString()) }}">This month</a>
    <a href="{{ $__q(now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()) }}">Last month</a>
    <a href="{{ $__q(now()->subMonths(2)->startOfMonth()->toDateString(), now()->toDateString()) }}">Last 3 months</a>
    <a href="{{ $__q(now()->startOfYear()->toDateString(), now()->toDateString()) }}">This year</a>
    <a href="{{ $__q(now()->subYear()->startOfYear()->toDateString(), now()->subYear()->endOfYear()->toDateString()) }}">Last year</a>
    <a href="{{ $__q('', '') }}">All time</a>
</div>
