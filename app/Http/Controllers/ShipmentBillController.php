<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\CostType;
use App\Models\ItemsShipment;
use App\Models\ItemsShipmentCost;
use App\Models\ItemsShipmentPayment;
use App\Models\ShipmentBillPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Accounting -> Shipment Billing (what WE owe: freight, CNF, tax, warehouse ... per shipment).
 * One voucher can settle cost lines of several shipments at once.
 */
class ShipmentBillController extends Controller
{
    public const METHODS = [
        'cash'           => 'Cash',
        'bank'           => 'Bank Transfer',
        'check'          => 'Check',
        'mobile_banking' => 'Mobile Banking',
    ];

    /** Overview: every shipment with cost / paid / due + recent vouchers. */
    public function index(Request $request)
    {
        abort_unless(Gate::allows('view-shipment-cost'), 403);

        $onlyDue = $request->boolean('only_due');

        $totals = ItemsShipmentCost::query()
            ->selectRaw('items_shipment_id,
                         COUNT(*) as `lines`,
                         SUM(CASE WHEN due_amount > 0 THEN 1 ELSE 0 END) as due_lines,
                         SUM(final_bdt_cost) as total_cost,
                         SUM(paid_amount) as total_paid,
                         SUM(due_amount) as total_due')
            ->groupBy('items_shipment_id')
            ->get()
            ->keyBy('items_shipment_id');

        $shipments = ItemsShipment::query()
            ->orderByDesc('shipment_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($s) use ($totals) {
                $t = $totals->get($s->id);
                $s->bill = [
                    'lines'      => (int) ($t->lines ?? 0),
                    'due_lines'  => (int) ($t->due_lines ?? 0),
                    'total_cost' => (float) ($t->total_cost ?? 0),
                    'total_paid' => (float) ($t->total_paid ?? 0),
                    'total_due'  => (float) ($t->total_due ?? 0),
                ];
                return $s;
            });

        $grand = [
            'cost' => round($shipments->sum(fn ($s) => $s->bill['total_cost']), 2),
            'paid' => round($shipments->sum(fn ($s) => $s->bill['total_paid']), 2),
            'due'  => round($shipments->sum(fn ($s) => $s->bill['total_due']), 2),
        ];

        if ($onlyDue) {
            $shipments = $shipments->filter(fn ($s) => $s->bill['total_due'] > 0)->values();
        }

        $vouchers = ShipmentBillPayment::with(['user', 'agent'])
            ->withCount('payments')
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('admin.billing.index', compact('shipments', 'grand', 'vouchers', 'onlyDue'));
    }

    /** Pay form: choose cost lines of many shipments, one amount, split manually or auto. */
    public function create(Request $request)
    {
        abort_unless(Gate::allows('add-bill-payment'), 403);

        $agentId    = $request->integer('agent_id') ?: null;
        $costTypeId = $request->integer('cost_type_id') ?: null;

        $costs = ItemsShipmentCost::with(['itemsShipment', 'costType', 'agent'])
            ->where('due_amount', '>', 0)
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->when($costTypeId, fn ($q) => $q->where('cost_type_id', $costTypeId))
            ->orderBy('items_shipment_id')
            ->orderBy('cost_date')
            ->orderBy('id')
            ->get();

        // oldest shipment first, so "auto split" clears the oldest bills first
        $groups = $costs->groupBy('items_shipment_id')
            ->sortBy(fn ($lines) => optional($lines->first()->itemsShipment)->shipment_date ?? now()->addYears(50));

        $agents    = Agent::orderBy('name')->get();
        $costTypes = CostType::orderBy('name')->get();
        $methods   = self::METHODS;

        $preShipment = (int) $request->query('shipment');
        $preCost     = (int) $request->query('cost');
        $totalDue    = round((float) $costs->sum('due_amount'), 2);

        return view('admin.billing.pay', compact(
            'groups', 'agents', 'costTypes', 'methods',
            'agentId', 'costTypeId', 'preShipment', 'preCost', 'totalDue'
        ));
    }

    /** Save one voucher + one payment line per cost line (all-or-nothing). */
    public function store(Request $request)
    {
        abort_unless(Gate::allows('add-bill-payment'), 403);

        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:' . implode(',', array_keys(self::METHODS)),
            'reference_no'   => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'agent_id'       => 'nullable|exists:agents,id',
            'allocations'    => 'required|array',
            'allocations.*'  => 'nullable|numeric|min:0',
        ]);

        $allocations = collect($request->input('allocations', []))
            ->map(fn ($v) => round((float) $v, 2))
            ->filter(fn ($v) => $v > 0);

        if ($allocations->isEmpty()) {
            return back()->withInput()->with('error', 'Allocate the payment to at least one cost line.');
        }

        try {
            $voucher = DB::transaction(function () use ($request, $allocations) {
                // lock the lines so two accountants cannot overpay the same bill
                $costs = ItemsShipmentCost::with('costType')
                    ->whereIn('id', $allocations->keys()->map(fn ($k) => (int) $k)->all())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($allocations as $costId => $amt) {
                    $cost = $costs->get((int) $costId);

                    if (!$cost) {
                        throw new \Exception("Cost line #{$costId} was not found.");
                    }

                    $due = round((float) $cost->due_amount, 2);

                    if ($amt > $due + 0.004) {
                        throw new \Exception(
                            ($cost->costType->name ?? 'Cost') . ' of shipment #' . $cost->items_shipment_id .
                            ': payment ' . number_format($amt, 2) . ' is more than its due ' . number_format($due, 2) . '.'
                        );
                    }

                    // cost types without partial payment must be paid in full
                    if ($cost->costType && !$cost->costType->have_partial_payment && abs($amt - $due) > 0.004) {
                        throw new \Exception(
                            ($cost->costType->name ?? 'Cost') . ' of shipment #' . $cost->items_shipment_id .
                            ' does not allow partial payment. Pay the full ' . number_format($due, 2) . ' or leave it blank.'
                        );
                    }
                }

                $total = round((float) $allocations->sum(), 2);

                if (abs($total - round((float) $request->amount, 2)) > 0.009) {
                    throw new \Exception('Allocated total (' . number_format($total, 2) .
                        ') must equal the paid amount (' . number_format((float) $request->amount, 2) . ').');
                }

                $voucher = ShipmentBillPayment::create([
                    'user_id'        => Auth::id(),
                    'agent_id'       => $request->agent_id,
                    'amount'         => $total,
                    'payment_date'   => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'reference_no'   => $request->reference_no,
                    'notes'          => $request->notes,
                ]);

                $voucher->update(['voucher_no' => 'SBP-' . str_pad((string) $voucher->id, 6, '0', STR_PAD_LEFT)]);

                $paidBy = optional(Auth::user())->name;

                foreach ($allocations as $costId => $amt) {
                    // model events refresh paid / due / status of the cost line
                    ItemsShipmentPayment::create([
                        'items_shipment_cost_id'   => (int) $costId,
                        'amount'                   => $amt,
                        'payment_date'             => $request->payment_date,
                        'payment_method'           => $request->payment_method,
                        'reference_no'             => $request->reference_no,
                        'notes'                    => $request->notes,
                        'paid_by'                  => $paidBy,
                        'user_id'                  => Auth::id(),
                        'shipment_bill_payment_id' => $voucher->id,
                    ]);
                }

                return $voucher;
            });

            return redirect()->route('shipment-bills.show', $voucher->id)
                ->with('success', 'Payment of ' . number_format($voucher->amount, 2) . ' tk saved as ' . $voucher->voucher_no . '.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error saving payment: ' . $e->getMessage());
        }
    }

    /** One voucher with its split across shipments / cost lines. */
    public function show(ShipmentBillPayment $voucher)
    {
        abort_unless(Gate::allows('view-shipment-cost'), 403);

        $voucher->load([
            'user', 'agent',
            'payments.user',
            'payments.itemsShipmentCost.costType',
            'payments.itemsShipmentCost.agent',
            'payments.itemsShipmentCost.itemsShipment',
        ]);

        $byShipment = $voucher->payments->groupBy(fn ($p) => $p->itemsShipmentCost->items_shipment_id);

        return view('admin.billing.show', compact('voucher', 'byShipment'));
    }

    /** Delete a whole voucher: every line is removed and the cost dues go back up. */
    public function destroy(ShipmentBillPayment $voucher)
    {
        abort_unless(Gate::allows('delete-bill-payment'), 403);

        try {
            DB::transaction(function () use ($voucher) {
                // delete line by line so the model events recalc every cost line
                $voucher->payments()->get()->each->delete();
                ShipmentBillPayment::whereKey($voucher->id)->delete();
            });

            return redirect()->route('shipment-bills.index')
                ->with('success', 'Voucher deleted. Shipment cost dues were recalculated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting voucher: ' . $e->getMessage());
        }
    }
}
