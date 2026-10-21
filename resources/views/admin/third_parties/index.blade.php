@extends('admin.layouts.master')
@section('main_content')
@include('admin.partials.pd-styles')
<div class="rp">
    <div class="rp-head">
        <div><h1>Third Parties</h1><p class="sub">Companies that ship through us. Their items are marked separately everywhere.</p></div>
        <div class="rp-actions">
            @can('create-customers')<a class="rp-btn primary" href="{{ route('third-parties.create') }}"><i class="fa fa-plus"></i> Add third party</a>@endcan
        </div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    @php $tb = $balances->sum('billed'); $tp = $balances->sum('paid'); @endphp
    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Billed</div><div class="v">{{ number_format($tb,2) }}</div></div>
        <div class="rp-kpi teal"><div class="l">Received</div><div class="v">{{ number_format($tp,2) }}</div></div>
        <div class="rp-kpi coral"><div class="l">Due from third parties</div><div class="v">{{ number_format($tb-$tp,2) }}</div></div>
    </div>

    <div class="rp-card"><div class="rp-table-wrap"><table class="rp-table">
        <thead><tr><th>Company</th><th>Contact</th><th class="num">Items</th><th class="num">Billed</th><th class="num">Received</th><th class="num">Due</th><th>Last payment</th><th></th></tr></thead>
        <tbody>
        @forelse($parties as $p)
            @php $b = $balances->get($p->id); @endphp
            <tr>
                <td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $p->color }};margin-right:6px"></span>
                    <strong>{{ $p->name }}</strong> @if($p->code)<small class="text-muted">({{ $p->code }})</small>@endif
                    @unless($p->is_active)<span class="badge badge-secondary">inactive</span>@endunless</td>
                <td>{{ $p->contact_person }} {{ $p->phone }}</td>
                <td class="num">{{ $p->items_count }}</td>
                <td class="num">{{ number_format($b->billed ?? 0,2) }}</td>
                <td class="num">{{ number_format($b->paid ?? 0,2) }}</td>
                <td class="num"><strong class="{{ ($b->due ?? 0) > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($b->due ?? 0,2) }}</strong></td>
                <td>{{ $b->last_payment ?? '—' }}</td>
                <td style="white-space:nowrap">
                    <a class="rp-btn sm" href="{{ route('third-parties.ledger', $p->id) }}"><i class="fa fa-book"></i> Ledger</a>
                    @can('edit-customers')<a class="rp-btn sm" href="{{ route('third-parties.edit', $p->id) }}"><i class="fa fa-edit"></i></a>@endcan
                    @can('delete-customers')
                    <form action="{{ route('third-parties.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $p->name }}?')">@csrf @method('DELETE')<button class="rp-btn sm red"><i class="fa fa-trash"></i></button></form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="rp-empty">No third parties yet.</td></tr>
        @endforelse
        </tbody></table></div></div>
</div>
@endsection
