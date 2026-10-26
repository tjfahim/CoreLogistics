@php $pk = \App\Services\ShipmentCostPerKg::compute($itemShipment ?? $itemshipment); $f = fn($v) => $v === null ? '—' : number_format($v, 2); @endphp
<div class="card mb-3">
    <div class="card-header"><strong><i class="fa fa-balance-scale"></i> Cost per kg</strong>
        <small class="text-muted ml-2">basis: {{ $pk['basis_source'] }} = {{ number_format($pk['basis'], 2) }} kg</small></div>
    <div class="card-body">
        <div class="row text-center mb-3">
            <div class="col"><small>Original weight</small><h5>{{ number_format($pk['original'], 2) }} kg</h5></div>
            <div class="col"><small>Final weight</small><h5>{{ number_format($pk['final'], 2) }} kg</h5></div>
            @if($pk['freight_weight'])<div class="col"><small>Freight agent weight</small><h5>{{ number_format($pk['freight_weight'], 2) }} kg</h5></div>@endif
            @if($pk['cbm_weight'])<div class="col"><small>Freight CBM weight</small><h5>{{ number_format($pk['cbm_weight'], 2) }} kg</h5></div>@endif
            <div class="col"><small>Total cost (BDT)</small><h5>{{ number_format($pk['total_cost'], 0) }}</h5></div>
        </div>
        <table class="table table-bordered table-sm mb-0">
            <thead class="thead-light"><tr><th>Basis</th><th class="text-right">Freight / kg</th><th class="text-right">CBM / kg</th><th class="text-right">Other bills / kg</th><th class="text-right">Total / kg</th></tr></thead>
            <tbody>
                <tr><td>{{ $pk['row1']['label'] }}</td><td class="text-right">{{ $f($pk['row1']['freight']) }}</td><td class="text-right">—</td><td class="text-right">{{ $f($pk['row1']['other']) }}</td><td class="text-right"><strong>{{ $f($pk['row1']['total']) }}</strong></td></tr>
                @if($pk['row2'])
                <tr><td>{{ $pk['row2']['label'] }}</td><td class="text-right">—</td><td class="text-right">{{ $f($pk['row2']['cbm']) }}</td><td class="text-right">{{ $f($pk['row2']['other']) }}</td><td class="text-right"><strong>{{ $f($pk['row2']['total']) }}</strong></td></tr>
                @endif
            </tbody>
        </table>
        <small class="text-muted">Other bills = every cost except the freight-agent bills (airport, tax, LC, documents, CNF, loading ...). In the CBM row, "other" also includes the normal freight-agent bill.</small>
    </div>
</div>
