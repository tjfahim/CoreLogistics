@extends('admin.layouts.master')

@section('main_content')
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .pd { --ink:#0f1f3d; --ink-2:#5b6b88; --line:#e6ebf3; --bg:#f4f6fb; --card:#fff;
          --teal:#0e9f8e; --blue:#2f5bea; --amber:#f2a516; --coral:#e5484d; --violet:#7c5cf0;
          font-family:'Manrope',system-ui,sans-serif; color:var(--ink); padding:8px 4px 40px; }
    .pd * { box-sizing:border-box; }
    .pd h1,.pd h2,.pd h3,.pd h4,.pd p { margin:0; }

    /* Hero: the one bold element */
    .pd-hero { background:linear-gradient(120deg,#0f1f3d 0%,#1b3a8a 60%,#0e9f8e 135%); color:#fff;
               border-radius:20px; padding:28px 32px; display:grid; grid-template-columns:1.1fr 1fr; gap:28px; margin-bottom:22px; }
    .pd-hero h1 { font-size:26px; font-weight:800; letter-spacing:-.4px; }
    .pd-hero .sub { opacity:.72; font-size:14px; margin-top:4px; }
    .pd-hero .big { font-size:44px; font-weight:800; letter-spacing:-1.2px; margin-top:18px; line-height:1; }
    .pd-hero .big small { font-size:16px; font-weight:600; opacity:.7; margin-left:6px; }
    .pd-hero .lbl { font-size:13px; opacity:.75; margin-top:6px; }
    .pd-hero-side { display:grid; grid-template-columns:1fr 1fr; gap:12px; align-content:center; }
    .pd-mini { background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.14); border-radius:14px; padding:14px 16px; }
    .pd-mini .v { font-size:20px; font-weight:800; }
    .pd-mini .l { font-size:12px; opacity:.75; margin-top:2px; }

    .pd-grid { display:grid; gap:18px; margin-bottom:22px; }
    .g4 { grid-template-columns:repeat(4,1fr); }
    .g3 { grid-template-columns:repeat(3,1fr); }
    .g21 { grid-template-columns:2fr 1fr; }
    .g2 { grid-template-columns:1fr 1fr; }

    .pd-card { background:var(--card); border:1px solid var(--line); border-radius:16px; padding:20px 22px; }
    .pd-card h3 { font-size:15px; font-weight:700; }
    .pd-card .hint { font-size:12.5px; color:var(--ink-2); margin-top:2px; }
    .pd-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }

    /* KPI */
    .pd-kpi { display:flex; gap:14px; align-items:center; }
    .pd-ico { width:46px; height:46px; border-radius:12px; display:grid; place-items:center; font-size:19px; flex:none; }
    .pd-kpi .v { font-size:22px; font-weight:800; letter-spacing:-.4px; }
    .pd-kpi .l { font-size:12.5px; color:var(--ink-2); font-weight:600; }
    .pd-kpi .s { font-size:12px; margin-top:2px; color:var(--ink-2); }

    .c-teal{background:#e3f6f3;color:var(--teal)} .c-blue{background:#e8eefd;color:var(--blue)}
    .c-amber{background:#fdf1d9;color:#c98300} .c-coral{background:#fde8e9;color:var(--coral)}
    .c-violet{background:#efeafd;color:var(--violet)}

    .chart-box { position:relative; height:290px; }
    .chart-box.sm { height:230px; }

    /* progress */
    .pd-bar { height:9px; background:#edf1f8; border-radius:99px; overflow:hidden; }
    .pd-bar i { display:block; height:100%; width:0; border-radius:99px; transition:width 1.2s cubic-bezier(.2,.8,.2,1); }
    .pd-prog { margin-bottom:18px; }
    .pd-prog:last-child { margin-bottom:0; }
    .pd-prog .row1 { display:flex; justify-content:space-between; font-size:13px; font-weight:600; margin-bottom:7px; }
    .pd-prog .row1 span:last-child { color:var(--ink-2); font-weight:500; }

    /* tables */
    .pd table { width:100%; border-collapse:collapse; font-size:13.5px; }
    .pd th { text-align:left; font-size:12px; color:var(--ink-2); font-weight:600; padding:0 0 10px; border-bottom:1px solid var(--line); }
    .pd td { padding:12px 0; border-bottom:1px solid var(--line); }
    .pd tr:last-child td { border-bottom:0; }
    .pd .r { text-align:right; }
    .pd-pill { display:inline-block; padding:3px 10px; border-radius:99px; font-size:12px; font-weight:700; }
    .pill-ok{background:#e3f6f3;color:var(--teal)} .pill-info{background:#e8eefd;color:var(--blue)}
    .pill-warn{background:#fdf1d9;color:#c98300} .pill-bad{background:#fde8e9;color:var(--coral)}
    .pd a.lnk { color:var(--blue); font-weight:700; text-decoration:none; font-size:13px; }
    .pd .neg { color:var(--coral); } .pd .pos { color:var(--teal); }

    .pd-avatar { width:34px; height:34px; border-radius:50%; background:#e8eefd; color:var(--blue); display:inline-grid;
                 place-items:center; font-weight:800; font-size:13px; margin-right:10px; vertical-align:middle; }
    .pd-empty { color:var(--ink-2); font-size:13.5px; padding:26px 0; text-align:center; }

    .pd-qa { display:inline-flex; gap:8px; align-items:center; background:#fff; border:1px solid var(--line); border-radius:12px; padding:9px 14px; font-weight:700; font-size:13px; color:var(--ink); text-decoration:none; }
    .pd-qa:hover { background:#e8eefd; color:var(--blue); text-decoration:none; }

    /* single entrance for the hero numbers only (counters); cards stay still */
    @media (max-width:1100px){ .g4{grid-template-columns:repeat(2,1fr)} .g21,.g3,.g2{grid-template-columns:1fr} .pd-hero{grid-template-columns:1fr} }
    @media (max-width:560px){ .g4{grid-template-columns:1fr} .pd-hero{padding:22px} .pd-hero .big{font-size:34px} }
    @media (prefers-reduced-motion:reduce){ .pd-bar i{transition:none} }
</style>

<div class="pd">

    {{-- ===== HERO ===== --}}
    <div class="pd-hero">
        <div>
            <h1 style="color: white;">Welcome back, {{ auth()->user()->name ?? 'Admin' }}</h1>
            <p class="sub">{{ now()->format('l, d F Y') }} &middot; {{ $stats['active_shipments'] }} in progress of {{ $stats['shipments'] }} shipments</p>
            <div class="big"><span class="count" data-val="{{ round($stats['profit']) }}">0</span><small>TK</small></div>
            <p class="lbl">Net profit (customer bills minus shipment costs, same as the ledgers)</p>
        </div>
        <div class="pd-hero-side">
            <div class="pd-mini"><div class="v"><span class="count" data-val="{{ round($stats['billed']) }}">0</span></div><div class="l">Total billed</div></div>
            <div class="pd-mini"><div class="v"><span class="count" data-val="{{ round($stats['received']) }}">0</span></div><div class="l">Received from customers</div></div>
            <div class="pd-mini"><div class="v"><span class="count" data-val="{{ round($stats['cost']) }}">0</span></div><div class="l">Shipment costs</div></div>
            <div class="pd-mini"><div class="v"><span class="count" data-val="{{ round($stats['cash']) }}">0</span></div><div class="l">Cash (received - paid out)</div></div>
        </div>
    </div>


    {{-- ===== QUICK ACTIONS ===== --}}
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
        <a class="pd-qa" href="{{ route('ledger.index') }}"><i class="fa fa-address-book-o"></i> Customer Ledger</a>
        <a class="pd-qa" href="{{ route('shipment-bills.index') }}"><i class="fa fa-money"></i> Shipment Billing</a>
        <a class="pd-qa" href="{{ route('shipment-bills.ledger') }}"><i class="fa fa-book"></i> Bill Ledger</a>
        <a class="pd-qa" href="{{ route('reports.summary') }}"><i class="fa fa-pie-chart"></i> Business Summary</a>
        <a class="pd-qa" href="{{ route('reports.period') }}"><i class="fa fa-calendar"></i> Month Report</a>
        <a class="pd-qa" href="{{ route('notifications.index') }}"><i class="fa fa-bell"></i> Notifications</a>
    </div>

    {{-- ===== THIS MONTH vs LAST MONTH ===== --}}
    @php
        $mrow = [['Billed','billed'],['Received','received'],['Shipment cost','cost'],['Profit','profit']];
    @endphp
    <div class="pd-grid g4">
        @foreach($mrow as [$lbl,$k])
            @php $cur = $month['this'][$k]; $prv = $month['last'][$k]; $chg = $prv != 0 ? round(($cur - $prv) / abs($prv) * 100) : null; @endphp
            <div class="pd-card">
                <div class="l" style="font-size:12.5px;color:var(--ink-2);font-weight:600">{{ $lbl }} - {{ now()->format('F') }}</div>
                <div style="font-size:22px;font-weight:800;margin-top:4px">{{ number_format($cur) }}</div>
                <div style="font-size:12px;color:var(--ink-2);margin-top:2px">
                    Last month {{ number_format($prv) }}
                    @if($chg !== null)<span class="{{ ($chg >= 0) === ($k !== 'cost') ? 'pos' : 'neg' }}"><b>{{ $chg >= 0 ? '+' : '' }}{{ $chg }}%</b></span>@endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== NEEDS ATTENTION + NOTIFICATIONS ===== --}}
    <div class="pd-grid g2">
        <div class="pd-card">
            <div class="pd-head"><div><h3>Needs attention</h3><p class="hint">Data gaps that hide money from the ledgers</p></div></div>
            <table><tbody>
                <tr><td>Items with no customer</td><td class="r"><span class="pd-pill {{ $alerts['unassigned'] ? 'pill-warn' : 'pill-ok' }}">{{ $alerts['unassigned'] }}</span></td></tr>
                <tr><td>Items with no price</td><td class="r"><span class="pd-pill {{ $alerts['unpriced'] ? 'pill-warn' : 'pill-ok' }}">{{ $alerts['unpriced'] }}</span></td></tr>
                <tr><td>Items not in any shipment</td><td class="r"><span class="pd-pill {{ $alerts['no_shipment'] ? 'pill-info' : 'pill-ok' }}">{{ $alerts['no_shipment'] }}</span></td></tr>
                <tr><td>Owed to agents</td><td class="r neg"><strong>{{ number_format($alerts['cost_due']) }}</strong></td></tr>
                <tr><td>Owed by customers</td><td class="r neg"><strong>{{ number_format($stats['customer_due']) }}</strong></td></tr>
            </tbody></table>
        </div>
        <div class="pd-card">
            <div class="pd-head"><div><h3>Customers to notify</h3><p class="hint">Receipts not yet notified, per status</p></div><a class="lnk" href="{{ route('notifications.index') }}">Open</a></div>
            <table><tbody>
                @foreach(\App\Models\CargoItem::STATUS_FLOW as $st => $label)
                    <tr><td>{{ $label }}</td><td class="r"><span class="pd-pill {{ ($notifyPending[$st] ?? 0) ? 'pill-warn' : 'pill-ok' }}">{{ $notifyPending[$st] ?? 0 }}</span></td></tr>
                @endforeach
            </tbody></table>
        </div>
    </div>

    {{-- ===== TREND + COLLECTION ===== --}}
    <div class="pd-grid g21">
        <div class="pd-card">
            <div class="pd-head"><div><h3>Billing, collection and cost</h3><p class="hint">Last 6 months: billed by shipment date, received by payment date</p></div></div>
            <div class="chart-box"><canvas id="trendChart"></canvas></div>
        </div>
        <div class="pd-card">
            <div class="pd-head"><div><h3>Payment progress</h3><p class="hint">Money in and money out</p></div></div>
            <div class="pd-prog">
                <div class="row1"><span>Customer collection</span><span>{{ $stats['collection_pct'] }}%</span></div>
                <div class="pd-bar"><i style="background:var(--teal)" data-w="{{ min(100, $stats['collection_pct']) }}"></i></div>
            </div>
            <div class="pd-prog">
                <div class="row1"><span>Shipment costs paid</span><span>{{ $stats['cost_paid_pct'] }}%</span></div>
                <div class="pd-bar"><i style="background:var(--blue)" data-w="{{ min(100, $stats['cost_paid_pct']) }}"></i></div>
            </div>
            <div class="pd-prog">
                <div class="row1"><span>Owed to agents</span><span>{{ number_format($stats['cost_due']) }} TK</span></div>
                <div class="pd-bar"><i style="background:var(--amber)" data-w="{{ $stats['cost'] > 0 ? min(100, round($stats['cost_due'] / $stats['cost'] * 100)) : 0 }}"></i></div>
            </div>
            <div style="margin-top:22px;border-top:1px solid var(--line);padding-top:16px;">
                <div class="chart-box sm" style="height:150px"><canvas id="costStatusChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ===== BREAKDOWNS ===== --}}
    <div class="pd-grid g3">
        <div class="pd-card">
            <div class="pd-head"><div><h3>Costs by type</h3><p class="hint">Where shipment money goes</p></div></div>
            @if($costByType->count())
                <div class="chart-box sm"><canvas id="costTypeChart"></canvas></div>
            @else <div class="pd-empty">No costs recorded yet.</div> @endif
        </div>
        <div class="pd-card">
            <div class="pd-head"><div><h3>Cargo status</h3><p class="hint">Items per pipeline status</p></div></div>
            @if($cargoStatus->count())
                <div class="chart-box sm"><canvas id="cargoChart"></canvas></div>
            @else <div class="pd-empty">No cargo items yet.</div> @endif
        </div>
        <div class="pd-card">
            <div class="pd-head"><div><h3>Top customers</h3><p class="hint">By total billing (TK)</p></div></div>
            @if(count($topCustomers['labels']))
                <div class="chart-box sm"><canvas id="customerChart"></canvas></div>
            @else <div class="pd-empty">No billed customers yet.</div> @endif
        </div>
    </div>
 {{-- ===== KPI CARDS ===== --}}
    <div class="pd-grid g4">
        <div class="pd-card pd-kpi">
            <div class="pd-ico c-blue"><i class="fa fa-ship"></i></div>
            <div><div class="v"><span class="count" data-val="{{ $stats['shipments'] }}">0</span></div><div class="l">Shipments</div><div class="s">{{ $stats['active_shipments'] }} active</div></div>
        </div>
        <div class="pd-card pd-kpi">
            <div class="pd-ico c-teal"><i class="fa fa-users"></i></div>
            <div><div class="v"><span class="count" data-val="{{ $stats['customers'] }}">0</span></div><div class="l">Customers</div><div class="s">{{ number_format($stats['cargo_items']) }} cargo items</div></div>
        </div>
        <div class="pd-card pd-kpi">
            <div class="pd-ico c-violet"><i class="fa fa-cubes"></i></div>
            <div><div class="v"><span class="count" data-val="{{ round($stats['total_weight']) }}">0</span> kg</div><div class="l">Total weight</div><div class="s">{{ number_format($stats['total_cbm'], 1) }} CBM</div></div>
        </div>
        <div class="pd-card pd-kpi">
            <div class="pd-ico c-amber"><i class="fa fa-clock-o"></i></div>
            <div><div class="v"><span class="count" data-val="{{ round($stats['customer_due']) }}">0</span></div><div class="l">Customer due (TK)</div><div class="s">{{ $stats['collection_pct'] }}% collected</div></div>
        </div>
    </div>
    {{-- ===== TABLES ===== --}}
    <div class="pd-grid g2">
        <div class="pd-card">
            <div class="pd-head"><div><h3>Recent shipments</h3><p class="hint">Latest six</p></div>
                <a class="lnk" href="{{ route('reports.dashboard') }}">Open reports</a></div>
            @forelse($recentShipments as $s)
                @if($loop->first)
                <table><thead><tr><th>Shipment</th><th class="r">Billed</th><th class="r">Cost</th><th class="r">Profit</th><th class="r"></th></tr></thead><tbody>
                @endif
                <tr>
                    <td><strong>{{ $s->name }}</strong><br><span style="color:var(--ink-2);font-size:12px">{{ $s->date }} &middot; {{ $s->items }} items</span></td>
                    <td class="r">{{ number_format($s->billed) }}</td>
                    <td class="r">{{ number_format($s->cost) }}</td>
                    <td class="r {{ $s->profit < 0 ? 'neg' : 'pos' }}">{{ number_format($s->profit) }}</td>
                    <td class="r"><a class="lnk" href="{{ route('reports.shipments', ['shipment_ids' => [$s->id]]) }}">View</a></td>
                </tr>
                @if($loop->last) </tbody></table> @endif
            @empty <div class="pd-empty">No shipments yet.</div> @endforelse
        </div>

        <div class="pd-card">
            <div class="pd-head"><div><h3>Latest customer payments</h3><p class="hint">Newest first</p></div></div>
            @forelse($recentPayments as $p)
                @if($loop->first)
                <table><thead><tr><th>Customer</th><th>Method</th><th class="r">Amount</th></tr></thead><tbody>
                @endif
                <tr>
                    <td><span class="pd-avatar">{{ strtoupper(substr($p->customer->name ?? '?', 0, 1)) }}</span>{{ $p->customer->name ?? 'Unknown' }}
                        <br><span style="color:var(--ink-2);font-size:12px;margin-left:44px">{{ optional($p->payment_date)->format('d M Y') }}</span></td>
                    <td><span class="pd-pill pill-info">{{ ucfirst($p->payment_method ?? 'n/a') }}</span></td>
                    <td class="r pos"><strong>{{ number_format($p->amount) }}</strong></td>
                </tr>
                @if($loop->last) </tbody></table> @endif
            @empty <div class="pd-empty">No customer payments yet.</div> @endforelse
        </div>
    </div>

    {{-- ===== CUSTOMER DUES + VOUCHERS ===== --}}
    <div class="pd-grid g2">
        <div class="pd-card">
            <div class="pd-head"><div><h3>Highest customer dues</h3><p class="hint">Shipment bills minus received payments</p></div><a class="lnk" href="{{ route('ledger.index') }}">All</a></div>
            @forelse($topDue as $c)
                @if($loop->first)<table><thead><tr><th>Customer</th><th class="r">Billed</th><th class="r">Due</th><th class="r"></th></tr></thead><tbody>@endif
                <tr><td><span class="pd-avatar">{{ strtoupper(substr($c->name, 0, 1)) }}</span>{{ $c->name }}<br><span style="color:var(--ink-2);font-size:12px;margin-left:44px">{{ $c->shipments_due }} shipment(s) due</span></td>
                    <td class="r">{{ number_format($c->billed) }}</td><td class="r neg"><strong>{{ number_format($c->due) }}</strong></td>
                    <td class="r"><a class="lnk" href="{{ route('ledger.customer', $c->customer_id) }}">Ledger</a></td></tr>
                @if($loop->last)</tbody></table>@endif
            @empty <div class="pd-empty">No customer dues.</div> @endforelse
        </div>
        <div class="pd-card">
            <div class="pd-head"><div><h3>Recent bill vouchers</h3><p class="hint">Payments to agents and suppliers</p></div><a class="lnk" href="{{ route('shipment-bills.index') }}">All</a></div>
            @forelse($recentVouchers as $v)
                @if($loop->first)<table><thead><tr><th>Voucher</th><th>Paid to</th><th class="r">Amount</th></tr></thead><tbody>@endif
                <tr><td><a class="lnk" href="{{ route('shipment-bills.show', $v->id) }}">{{ $v->voucher_no }}</a><br><span style="color:var(--ink-2);font-size:12px">{{ optional($v->payment_date)->format('d M Y') }}</span></td>
                    <td>{{ optional($v->agent)->name ?? '—' }}</td><td class="r"><strong>{{ number_format($v->amount) }}</strong></td></tr>
                @if($loop->last)</tbody></table>@endif
            @empty <div class="pd-empty">No vouchers yet.</div> @endforelse
        </div>
    </div>

    {{-- ===== AGENTS DUE ===== --}}
    <div class="pd-card">
        <div class="pd-head"><div><h3>Agents waiting for payment</h3><p class="hint">Top five by amount due</p></div></div>
        @forelse($agentDue as $a)
            @if($loop->first)
            <table><thead><tr><th>Agent</th><th class="r">Total</th><th class="r">Paid</th><th class="r">Due</th><th style="width:28%">Progress</th></tr></thead><tbody>
            @endif
            @php $pct = $a['total'] > 0 ? round($a['paid'] / $a['total'] * 100) : 0; @endphp
            <tr>
                <td><span class="pd-avatar">{{ strtoupper(substr($a['name'], 0, 1)) }}</span>{{ $a['name'] }}</td>
                <td class="r">{{ number_format($a['total']) }}</td>
                <td class="r pos">{{ number_format($a['paid']) }}</td>
                <td class="r neg"><strong>{{ number_format($a['due']) }}</strong></td>
                <td><div class="pd-bar"><i style="background:var(--teal)" data-w="{{ $pct }}"></i></div></td>
            </tr>
            @if($loop->last) </tbody></table> @endif
        @empty <div class="pd-empty">All agents are paid up.</div> @endforelse
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Wait for window load so the layout's jQuery (loaded in the footer) is ready.
window.addEventListener('load', function () {
    var $ = window.jQuery;

    /* ---------- Animated counters ---------- */
    if ($) {
        $('.count').each(function () {
            var $el = $(this), end = parseFloat($el.data('val')) || 0;
            $({ n: 0 }).animate({ n: end }, {
                duration: 1400, easing: 'swing',
                step: function (now) { $el.text(Math.round(now).toLocaleString('en-US')); },
                complete: function () { $el.text(Math.round(end).toLocaleString('en-US')); }
            });
        });
        // progress bars
        setTimeout(function () { $('.pd-bar i').each(function () { $(this).css('width', $(this).data('w') + '%'); }); }, 150);
    }

    /* ---------- Charts ---------- */
    Chart.defaults.font.family = "'Manrope', sans-serif";
    Chart.defaults.color = '#5b6b88';
    var C = { teal:'#0e9f8e', blue:'#2f5bea', amber:'#f2a516', coral:'#e5484d', violet:'#7c5cf0', slate:'#94a3b8' };
    var palette = [C.blue, C.teal, C.amber, C.violet, C.coral, C.slate, '#14b8a6'];
    var fmt = function (v) { return Number(v).toLocaleString('en-US'); };
    var grid = { color: '#eef1f7', drawBorder: false };

    // Trend
    var t = @json($trend);
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: { labels: t.labels, datasets: [
            { label: 'Billed',   data: t.billed,   borderColor: C.blue,  backgroundColor: 'rgba(47,91,234,.12)', fill: true, tension: .38, borderWidth: 2.5, pointRadius: 3 },
            { label: 'Received', data: t.received, borderColor: C.teal,  backgroundColor: 'transparent', tension: .38, borderWidth: 2.5, pointRadius: 3 },
            { label: 'Cost',     data: t.cost,     borderColor: C.amber, backgroundColor: 'transparent', tension: .38, borderWidth: 2.5, pointRadius: 3, borderDash: [6,4] }
        ]},
        options: { maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                       tooltip: { callbacks: { label: function (c) { return c.dataset.label + ': ' + fmt(c.parsed.y) + ' TK'; } } } },
            scales: { y: { beginAtZero: true, grid: grid, ticks: { callback: fmt } }, x: { grid: { display: false } } },
            animation: { duration: 1400, easing: 'easeOutQuart' } }
    });

    // Cost payment status
    var cs = @json($costStatus);
    new Chart(document.getElementById('costStatusChart'), {
        type: 'bar',
        data: { labels: ['Pending', 'Partial', 'Paid'], datasets: [{ data: [cs.pending, cs.partial, cs.paid],
            backgroundColor: [C.coral, C.amber, C.teal], borderRadius: 8, maxBarThickness: 46 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false }, title: { display: true, text: 'Cost items by payment status', align: 'start', color: '#0f1f3d' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: grid }, x: { grid: { display: false } } } }
    });

    // Cost types
    var ct = @json($costByType);
    if (document.getElementById('costTypeChart')) {
        new Chart(document.getElementById('costTypeChart'), {
            type: 'doughnut',
            data: { labels: Object.keys(ct), datasets: [{ data: Object.values(ct), backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }] },
            options: { maintainAspectRatio: false, cutout: '66%',
                plugins: { legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8 } },
                           tooltip: { callbacks: { label: function (c) { return c.label + ': ' + fmt(c.parsed) + ' TK'; } } } } }
        });
    }

    // Cargo status
    var cg = @json($cargoStatus);
    if (document.getElementById('cargoChart')) {
        new Chart(document.getElementById('cargoChart'), {
            type: 'doughnut',
            data: { labels: Object.keys(cg), datasets: [{ data: Object.values(cg), backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }] },
            options: { maintainAspectRatio: false, cutout: '66%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8 } } } }
        });
    }

    // Top customers
    var tc = @json($topCustomers);
    if (document.getElementById('customerChart')) {
        new Chart(document.getElementById('customerChart'), {
            type: 'bar',
            data: { labels: tc.labels, datasets: [{ data: tc.values, backgroundColor: C.blue, borderRadius: 8, maxBarThickness: 22 }] },
            options: { indexAxis: 'y', maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return fmt(c.parsed.x) + ' TK'; } } } },
                scales: { x: { grid: grid, ticks: { callback: fmt } }, y: { grid: { display: false } } } }
        });
    }
});
</script>
@endsection