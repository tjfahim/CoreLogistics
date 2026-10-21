@php $rn = request()->route() ? request()->route()->getName() : ''; @endphp
<div class="rp-presets no-print" style="margin:0 0 14px">
    @foreach([['reports.dashboard','Reports Home'],['reports.final','Final Report'],['reports.summary','Business Summary'],['reports.period','Month / Date wise'],['reports.shipments','Shipment Report'],['reports.customers','Customer Report']] as [$r,$t])
        <a href="{{ route($r) }}" style="{{ $rn === $r ? 'background:#2f5bea;color:#fff' : '' }}">{{ $t }}</a>
    @endforeach
    @can('view-office-expense')<a href="{{ route('reports.office') }}" style="{{ $rn === 'reports.office' ? 'background:#2f5bea;color:#fff' : '' }}">Office Expense</a>@endcan
</div>
