<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
    h1 { font-size: 15px; margin: 0 0 2px; }
    .sub { color: #666; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    th { background: #2c3e50; color: #fff; padding: 4px 5px; text-align: left; font-size: 8.5px; }
    td { padding: 3px 5px; border-bottom: 1px solid #ddd; vertical-align: top; }
    tr:nth-child(even) td { background: #f6f8fa; }
    .num { text-align: right; white-space: nowrap; }
    tfoot td, .tot td { font-weight: bold; background: #e9ecef !important; border-top: 2px solid #2c3e50; }
    .foot { position: fixed; bottom: -8px; left: 0; right: 0; font-size: 8px; color: #888; text-align: right; }
    .pb { page-break-after: always; }
</style>
</head>
<body>
<div class="foot">Generated {{ $generated }}</div>
@foreach($sheets as $si => $s)
    @php
        $money = $s['money'] ?? []; $int = $s['int'] ?? [];
        $fmt = function ($v, $i) use ($money, $int) {
            if ($v === null || $v === '') return '';
            if (is_numeric($v) && in_array($i, $money, true)) return number_format((float) $v, 2);
            if (is_numeric($v) && in_array($i, $int, true)) return number_format((float) $v, 0);
            return $v;
        };
        $isNum = fn ($i) => in_array($i, $money, true) || in_array($i, $int, true);
    @endphp
    <h1>{{ $s['heading'] ?? ($s['title'] ?? '') }}</h1>
    @if(!empty($s['subtitle']))<div class="sub">{{ $s['subtitle'] }}</div>@endif
    <table>
        <thead><tr>@foreach($s['headings'] ?? [] as $i => $h)<th class="{{ $isNum($i) ? 'num' : '' }}">{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
        @forelse($s['rows'] ?? [] as $row)
            <tr>@foreach($row as $i => $v)<td class="{{ $isNum($i) ? 'num' : '' }}">{{ $fmt($v, $i) }}</td>@endforeach</tr>
        @empty
            <tr><td colspan="{{ max(1, count($s['headings'] ?? [])) }}">No data</td></tr>
        @endforelse
        </tbody>
        @if(!empty($s['totals']))
        <tfoot><tr>@foreach($s['totals'] as $i => $v)<td class="{{ $isNum($i) ? 'num' : '' }}">{{ $fmt($v, $i) }}</td>@endforeach</tr></tfoot>
        @endif
    </table>
    @if(!$loop->last)<div class="pb"></div>@endif
@endforeach
</body>
</html>
