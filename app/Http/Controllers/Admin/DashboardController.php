<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CargoItem;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Models\ItemsShipment;
use App\Models\ItemsShipmentCost;
use App\Models\ItemsShipmentPayment;
use App\Models\ReceiptStatusNotification;
use App\Models\ShipmentBillPayment;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
   
public function dashboard()
{
    $acc = app(AccountingService::class);

    // ---------- Shipment business (same numbers as the ledgers and reports) ----------
    $fin = $acc->shipmentFinancials([]);
    $tot = $acc->sumFinancials($fin);

    $balances       = $acc->customerBalances();
    $totalReceived  = (float) CustomerPayment::sum('amount');
    $totalCostPaid  = (float) ItemsShipmentPayment::sum('amount');
    $customerDue    = round((float) $balances->sum('due'), 2);

    $stats = [
        'shipments'        => $fin->count(),
        'active_shipments' => $fin->whereNotIn('status', ['delivered', 'cancelled'])->count(),
        'customers'        => Customer::count(),
        'cargo_items'      => CargoItem::count(),
        'total_weight'     => (float) $tot['weight'],
        'total_cbm'        => (float) $tot['cbm'],
        'billed'           => $tot['billed'],
        'received'         => $totalReceived,
        'customer_due'     => $customerDue,
        'cost'             => $tot['cost'],
        'cost_paid'        => $totalCostPaid,
        'cost_due'         => round(max($tot['cost'] - $totalCostPaid, 0), 2),
        'profit'           => $tot['profit'],
        'cash'             => round($totalReceived - $totalCostPaid, 2),
        'collection_pct'   => $tot['billed'] > 0 ? round($totalReceived / $tot['billed'] * 100, 1) : 0,
        'cost_paid_pct'    => $tot['cost'] > 0 ? round($totalCostPaid / $tot['cost'] * 100, 1) : 0,
    ];

    // ---------- This month vs last month ----------
    $thisFrom = now()->startOfMonth()->toDateString();
    $lastFrom = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
    $lastTo   = now()->subMonthNoOverflow()->endOfMonth()->toDateString();
    $thisM = $acc->periodReport($thisFrom, now()->toDateString(), 'month')['totals'];
    $lastM = $acc->periodReport($lastFrom, $lastTo, 'month')['totals'];
    $month = ['this' => $thisM, 'last' => $lastM];

    // ---------- Last 6 months trend ----------
    $rep = $acc->periodReport(now()->subMonths(5)->startOfMonth()->toDateString(), now()->toDateString(), 'month');
    $trend = [
        'labels'   => $rep['rows']->pluck('label')->values(),
        'billed'   => $rep['rows']->map(fn ($r) => round($r['billed']))->values(),
        'cost'     => $rep['rows']->map(fn ($r) => round($r['cost']))->values(),
        'received' => $rep['rows']->map(fn ($r) => round($r['received']))->values(),
    ];

    // ---------- Cost breakdown by type ----------
    $costs = ItemsShipmentCost::with(['costType:id,name', 'agent:id,name'])->get();
    $costVal = fn ($c) => (float) ($c->final_bdt_cost ?: $c->amount);
    $costByType = $costs->groupBy(fn ($c) => $c->costType->name ?? 'Other')
        ->map(fn ($g) => round($g->sum($costVal)))->sortDesc()->take(7);

    // ---------- Cargo status ----------
    $cargoStatus = CargoItem::selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status')
        ->mapWithKeys(fn ($n, $st) => [CargoItem::STATUS_FLOW[$st] ?? ucfirst(str_replace('_', ' ', (string) $st)) => $n]);

    // ---------- Customers: top billed / highest due ----------
    $names = Customer::pluck('name', 'id');
    $topRaw = $balances->sortByDesc('billed')->take(5);
    $topCustomers = [
        'labels' => $topRaw->map(fn ($c) => $names[$c->customer_id] ?? ('#' . $c->customer_id))->values(),
        'values' => $topRaw->map(fn ($c) => round($c->billed))->values(),
    ];
    $topDue = $balances->where('due', '>', 0)->sortByDesc('due')->take(6)->map(function ($c) use ($names) {
        $c->name = $names[$c->customer_id] ?? ('#' . $c->customer_id);
        return $c;
    })->values();

    // ---------- Agents with money still owed ----------
    $agentDue = $costs->whereNotNull('agent_id')->groupBy('agent_id')
        ->map(fn ($g) => [
            'name'  => $g->first()->agent->name ?? 'Unknown',
            'total' => (float) $g->sum($costVal),
            'paid'  => (float) $g->sum('paid_amount'),
            'due'   => (float) $g->sum('due_amount'),
        ])
        ->filter(fn ($a) => $a['due'] > 0)->sortByDesc('due')->take(5)->values();

    // ---------- Recent activity ----------
    $recentShipments = $fin->sortByDesc('date')->take(6)->values();
    $recentPayments  = CustomerPayment::with('customer:id,name')->latest('payment_date')->latest('id')->take(6)->get();
    $recentVouchers  = ShipmentBillPayment::with('agent:id,name')->latest('payment_date')->latest('id')->take(5)->get();

    $costStatus = [
        'pending' => $costs->where('status', 'pending')->count(),
        'partial' => $costs->where('status', 'partial')->count(),
        'paid'    => $costs->where('status', 'paid')->count(),
    ];

    // ---------- Things that need attention ----------
    $alerts = [
        'unassigned'  => (int) $tot['unassigned'],
        'unpriced'    => (int) $tot['unpriced'],
        'no_shipment' => CargoItem::whereDoesntHave('itemshipments')->count(),
        'cost_due'    => $stats['cost_due'],
    ];

    // ---------- Customer notification pipeline ----------
    $notifyPending = ReceiptStatusNotification::pendingCounts();

    return view('admin.dashboard', compact(
        'stats', 'month', 'trend', 'costByType', 'cargoStatus', 'topCustomers', 'topDue',
        'agentDue', 'recentShipments', 'recentPayments', 'recentVouchers', 'costStatus', 'alerts', 'notifyPending'
    ));
}

    public function searchInvoice(Request $request)
    {
        $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
        ]);

        $invoice = Invoice::with(['customer', 'itemshipment'])
            ->where('invoice_number', trim($request->invoice_number))
            ->first();

        if (!$invoice) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'No invoice found for: ' . $request->invoice_number);
        }

        return redirect()->route('shipments.billing', [
            'itemshipment' => $invoice->itemshipment_id,
            'highlight'    => $invoice->customer_id,
        ]);
    }

    public function searchTracking(Request $request)
    {
        $request->validate([
            'tracking_no' => ['required', 'string', 'max:100'],
        ]);

        $tracking = trim($request->tracking_no);

        $item = CargoItem::where('tracking_no', $tracking)->first();

        if (!$item) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'No item found with Tracking No: ' . $tracking);
        }

        // Uses the existing admin tracking view
        return view('admin.cargo_items.tracking_view', compact('item'));
    }
}