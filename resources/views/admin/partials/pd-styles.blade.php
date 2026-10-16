{{-- Shared look for ledgers, reports and the notification tracker (matches the dashboard) --}}
<style>
    .rp { --ink:#0f1f3d; --ink-2:#5b6b88; --line:#e6ebf3; --bg:#f4f6fb; --card:#fff;
          --teal:#0e9f8e; --blue:#2f5bea; --amber:#f2a516; --coral:#e5484d; --violet:#7c5cf0;
          font-family:'Manrope',system-ui,sans-serif; color:var(--ink); padding:6px 4px 40px; }
    .rp *, .rp *::before, .rp *::after { box-sizing:border-box; }
    .rp h1,.rp h2,.rp h3,.rp h4,.rp p { margin:0; }

    .rp-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
    .rp-head h1 { font-size:24px; font-weight:800; letter-spacing:-.3px; }
    .rp-head .sub { color:var(--ink-2); font-size:13.5px; margin-top:4px; }
    .rp-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }

    .rp-btn { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border-radius:10px; border:1px solid var(--line);
              background:#fff; color:var(--ink); font-weight:700; font-size:13px; text-decoration:none; cursor:pointer; line-height:1.2; }
    .rp-btn:hover { background:#f1f4fb; color:var(--ink); text-decoration:none; }
    .rp-btn.primary { background:var(--blue); border-color:var(--blue); color:#fff; }
    .rp-btn.primary:hover { background:#2449c4; color:#fff; }
    .rp-btn.green { background:#0e9f8e; border-color:#0e9f8e; color:#fff; }
    .rp-btn.green:hover { background:#0b8577; color:#fff; }
    .rp-btn.red { background:#fff; border-color:#f5c6c8; color:var(--coral); }
    .rp-btn.sm { padding:5px 10px; font-size:12px; border-radius:8px; }

    .rp-card { background:var(--card); border:1px solid var(--line); border-radius:16px; padding:18px 20px; margin-bottom:18px; }
    .rp-card > h3, .rp-card .ttl { font-size:15px; font-weight:800; margin-bottom:2px; }
    .rp-card .hint { font-size:12.5px; color:var(--ink-2); margin-bottom:12px; }
    .rp-cardhead { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap; }

    .rp-filter { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:12px; align-items:end; }
    .rp-filter label { display:block; font-size:12px; font-weight:700; color:var(--ink-2); margin-bottom:5px; }
    .rp-filter .form-control, .rp-filter .form-select, .rp-filter select, .rp-filter input[type=date], .rp-filter input[type=text], .rp-filter input[type=month] {
        width:100%; border:1px solid var(--line); border-radius:10px; padding:8px 10px; font-size:13.5px; background:#fff; color:var(--ink); min-height:38px; }
    .rp-presets { display:flex; gap:6px; flex-wrap:wrap; margin-top:10px; }
    .rp-presets a { font-size:12px; font-weight:700; color:var(--blue); background:#e8eefd; border-radius:99px; padding:4px 11px; text-decoration:none; }
    .rp-presets a:hover { background:#d6e1fc; }

    .rp-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px; margin-bottom:18px; }
    .rp-kpi { background:#fff; border:1px solid var(--line); border-radius:14px; padding:14px 16px; border-left:4px solid var(--blue); }
    .rp-kpi .l { font-size:12px; color:var(--ink-2); font-weight:700; text-transform:uppercase; letter-spacing:.3px; }
    .rp-kpi .v { font-size:22px; font-weight:800; letter-spacing:-.4px; margin-top:4px; }
    .rp-kpi .s { font-size:12px; color:var(--ink-2); margin-top:2px; }
    .rp-kpi.teal { border-left-color:var(--teal); } .rp-kpi.amber { border-left-color:var(--amber); }
    .rp-kpi.coral { border-left-color:var(--coral); } .rp-kpi.violet { border-left-color:var(--violet); }

    .rp-table-wrap { overflow-x:auto; }
    .rp table.rp-table { width:100%; border-collapse:separate; border-spacing:0; font-size:13.2px; }
    .rp .rp-table th { background:#f4f6fb; color:var(--ink-2); font-size:11.5px; font-weight:800; text-transform:uppercase; letter-spacing:.3px;
                       padding:10px 12px; border-bottom:1px solid var(--line); white-space:nowrap; text-align:left; position:sticky; top:0; z-index:1; }
    .rp .rp-table td { padding:10px 12px; border-bottom:1px solid #eef1f7; vertical-align:top; }
    .rp .rp-table tbody tr:hover td { background:#fafbfe; }
    .rp .rp-table .num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .rp .rp-table tfoot td, .rp .rp-table tfoot th { background:#eef2fd; font-weight:800; border-top:2px solid #cfd9f5; padding:10px 12px; }
    .rp .rp-table tr.grp td { background:#f7f9fd; font-weight:800; color:var(--ink); }
    .rp .rp-table tr.open td { background:#fffbea; }
    .rp .neg { color:var(--coral); } .rp .pos { color:var(--teal); } .rp .muted { color:var(--ink-2); }
    .rp small.muted { font-size:12px; }
    .rp a.lnk { color:var(--blue); font-weight:700; text-decoration:none; }
    .rp a.lnk:hover { text-decoration:underline; }

    .rp-pill { display:inline-block; padding:3px 10px; border-radius:99px; font-size:11.5px; font-weight:800; white-space:nowrap; }
    .pill-ok{background:#e3f6f3;color:#0b7f72} .pill-info{background:#e8eefd;color:#2f5bea}
    .pill-warn{background:#fdf1d9;color:#a96d00} .pill-bad{background:#fde8e9;color:#c93a3f} .pill-gray{background:#eef1f7;color:#5b6b88}

    .rp-bar { height:8px; background:#edf1f8; border-radius:99px; overflow:hidden; min-width:70px; }
    .rp-bar i { display:block; height:100%; border-radius:99px; background:var(--teal); }

    .rp-empty { text-align:center; color:var(--ink-2); padding:34px 10px; font-size:14px; }
    .rp-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    @media (max-width:1000px){ .rp-grid2 { grid-template-columns:1fr; } }

    .rp-note { background:#fff8e6; border:1px solid #f6e2a8; color:#7a5800; border-radius:10px; padding:9px 12px; font-size:12.5px; margin-bottom:14px; }
    .rp-flash { border-radius:10px; padding:10px 14px; margin-bottom:14px; font-size:13.5px; font-weight:600; }
    .rp-flash.ok { background:#e3f6f3; color:#0b7f72; } .rp-flash.bad { background:#fde8e9; color:#c93a3f; }

    .rp-print-head { display:none; }

    @media print {
        .tl-sidebar, .tl-topbar, .tl-overlay, .tl-progress, .tl-footer, .no-print, .breadcrumbs, #header, footer { display:none !important; }
        .tl-main, #right-panel { margin:0 !important; padding:0 !important; width:100% !important; }
        body { background:#fff !important; }
        .rp { padding:0; }
        .rp-card, .rp-kpi { break-inside:avoid; border-color:#ccd3e0; }
        .rp .rp-table th { position:static; background:#eef1f8 !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        .rp .rp-table tfoot td, .rp .rp-table tfoot th { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        .rp-print-head { display:block; border-bottom:2px solid #0f1f3d; padding-bottom:8px; margin-bottom:14px; }
        .rp-print-head b { font-size:18px; }
        .rp-table-wrap { overflow:visible; }
        @page { size:A4 landscape; margin:10mm; }
    }
</style>
