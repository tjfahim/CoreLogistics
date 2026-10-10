<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CargoItem;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Models\ItemsShipment;
use App\Models\ItemsShipmentCost;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
   
public function dashboard()
{
    // ---------- Base data (loaded once, reused everywhere) ----------
    $items = CargoItem::select('id', 'customer_id', 'status', 'weight_kg', 'cbm', 'price', 'price_per_kg', 'date', 'created_at')->get();
    $costs = ItemsShipmentCost::with(['costType:id,name', 'agent:id,name'])->get();
    $customerPayments = CustomerPayment::select('id', 'customer_id', 'amount', 'payment_date', 'payment_method', 'created_at')->get();
 
    $itemBill = fn ($i) => (float) ($i->total_price ?? 0); // price OR price_per_kg * weight_kg
 
    // ---------- KPI numbers ----------
    $totalBilled   = $items->sum($itemBill);
    $totalReceived = (float) $customerPayments->sum('amount');
    $customerDue   = max(0, $totalBilled - $totalReceived);
 
    $totalCost     = (float) $costs->sum('amount');
    $totalCostPaid = (float) $costs->sum('paid_amount');
    $totalCostDue  = (float) $costs->sum('due_amount');
 
    $profit        = $totalBilled - $totalCost;
    $cashOnHand    = $totalReceived - $totalCostPaid;
    $collectionPct = $totalBilled > 0 ? round(($totalReceived / $totalBilled) * 100, 1) : 0;
    $costPaidPct   = $totalCost > 0 ? round(($totalCostPaid / $totalCost) * 100, 1) : 0;
 
    $stats = [
        'shipments'        => ItemsShipment::count(),
        'active_shipments' => ItemsShipment::where('status', 'active')->count(),
        'customers'        => Customer::count(),
        'cargo_items'      => $items->count(),
        'total_weight'     => (float) $items->sum('weight_kg'),
        'total_cbm'        => (float) $items->sum('cbm'),
        'billed'           => $totalBilled,
        'received'         => $totalReceived,
        'customer_due'     => $customerDue,
        'cost'             => $totalCost,
        'cost_paid'        => $totalCostPaid,
        'cost_due'         => $totalCostDue,
        'profit'           => $profit,
        'cash'             => $cashOnHand,
        'collection_pct'   => $collectionPct,
        'cost_paid_pct'    => $costPaidPct,
    ];
 
    // ---------- Last 6 months trend ----------
    $months = collect(range(5, 0))->map(fn ($n) => Carbon::now()->startOfMonth()->subMonths($n));
    $monthKeys = $months->map->format('Y-m');
 
    $billedByMonth = $items->groupBy(fn ($i) => Carbon::parse($i->date ?? $i->created_at)->format('Y-m'))
        ->map(fn ($g) => $g->sum($itemBill));
    $costByMonth = $costs->groupBy(fn ($c) => Carbon::parse($c->cost_date ?? $c->created_at)->format('Y-m'))
        ->map(fn ($g) => (float) $g->sum('amount'));
    $receivedByMonth = $customerPayments->groupBy(fn ($p) => Carbon::parse($p->payment_date ?? $p->created_at)->format('Y-m'))
        ->map(fn ($g) => (float) $g->sum('amount'));
 
    $trend = [
        'labels'   => $months->map->format('M Y')->values(),
        'billed'   => $monthKeys->map(fn ($k) => round($billedByMonth[$k] ?? 0))->values(),
        'cost'     => $monthKeys->map(fn ($k) => round($costByMonth[$k] ?? 0))->values(),
        'received' => $monthKeys->map(fn ($k) => round($receivedByMonth[$k] ?? 0))->values(),
    ];
 
    // ---------- Cost breakdown by type ----------
    $costByType = $costs->groupBy(fn ($c) => $c->costType->name ?? 'Other')
        ->map(fn ($g) => round($g->sum('amount')))
        ->sortDesc()
        ->take(7);
 
    // ---------- Cargo status ----------
    $cargoStatus = $items->groupBy(fn ($i) => ucfirst($i->status ?? 'unknown'))->map->count();
 
    // ---------- Top customers by billing ----------
    $topRaw = $items->whereNotNull('customer_id')
        ->groupBy('customer_id')
        ->map(fn ($g) => round($g->sum($itemBill)))
        ->sortDesc()
        ->take(5);
 
    $customerNames = Customer::whereIn('id', $topRaw->keys())->pluck('name', 'id');
    $topCustomers = [
        'labels' => $topRaw->keys()->map(fn ($id) => $customerNames[$id] ?? ('#' . $id))->values(),
        'values' => $topRaw->values(),
    ];
 
    // ---------- Agents with money still owed ----------
    $agentDue = $costs->whereNotNull('agent_id')
        ->groupBy('agent_id')
        ->map(fn ($g) => [
            'name'  => $g->first()->agent->name ?? 'Unknown',
            'total' => (float) $g->sum('amount'),
            'paid'  => (float) $g->sum('paid_amount'),
            'due'   => (float) $g->sum('due_amount'),
        ])
        ->filter(fn ($a) => $a['due'] > 0)
        ->sortByDesc('due')
        ->take(5)
        ->values();
 
    // ---------- Recent activity ----------
    $recentShipments = ItemsShipment::with('costs')
        ->withCount('cargoItems')
        ->latest()
        ->take(6)
        ->get();
 
    $recentPayments = CustomerPayment::with('customer:id,name')
        ->latest('payment_date')
        ->take(6)
        ->get();
 
    // Cost payment status counts
    $costStatus = [
        'pending' => $costs->where('status', 'pending')->count(),
        'partial' => $costs->where('status', 'partial')->count(),
        'paid'    => $costs->where('status', 'paid')->count(),
    ];
 
    return view('admin.dashboard', compact(
        'stats', 'trend', 'costByType', 'cargoStatus', 'topCustomers',
        'agentDue', 'recentShipments', 'recentPayments', 'costStatus'
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