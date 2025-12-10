<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemsShipment;
use App\Models\ItemsShipmentCost;
use App\Models\ItemsShipmentPayment;
use App\Models\CostType;
use App\Models\Agent;
use Illuminate\Support\Facades\DB;

class ItemShipmentCostController extends Controller
{
    // Show item shipment costs
 public function show($itemShipmentId)
    {
        $itemShipment = ItemsShipment::with([
            'costs.costType', 
            'costs.agent', 
            'costs.payments', 
            'cargoItems'
        ])->findOrFail($itemShipmentId);
        
        $costTypes = CostType::all();
        $agents = Agent::all();

        // Debug info
        \Log::info('Showing costs for item shipment:', ['id' => $itemShipmentId, 'name' => $itemShipment->shipment_name]);

        return view('admin.itemshipments.costs', compact('itemShipment', 'costTypes', 'agents'));
    }


    // Add cost to item shipment
public function store(Request $request, $itemShipmentId)
{
    $request->validate([
        'cost_type_id' => 'required|exists:cost_types,id',
        'agent_id' => 'nullable|exists:agents,id',
        'rate_per_kg' => 'nullable',
        'total_weight' => 'nullable',
        'amount' => 'required',
        'description' => 'nullable|string',
        'cost_date' => 'required|date',
    ]);

    try {
        DB::transaction(function () use ($request, $itemShipmentId) {
            $amount = $request->amount;
            if ($request->rate_per_kg && $request->total_weight) {
                $amount = $request->rate_per_kg * $request->total_weight;
            }

            $itemShipmentCost = ItemsShipmentCost::create([
                'items_shipment_id' => $itemShipmentId,
                'cost_type_id' => $request->cost_type_id,
                'agent_id' => $request->agent_id,
                'description' => $request->description,
                'rate_per_kg' => $request->rate_per_kg,
                'total_weight' => $request->total_weight,
                'amount' => $amount,
                'due_amount' => $amount,
                'cost_date' => $request->cost_date,
                'status' => 'pending'
            ]);

           
        });

        return redirect()->back()->with('success', 'Cost added successfully.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error adding cost: ' . $e->getMessage());
    }
}

    // Add payment for a cost
    public function addPayment(Request $request, $costId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string',
            'paid_by' => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($request, $costId) {
                $itemShipmentCost = ItemsShipmentCost::findOrFail($costId);
                
                // Check if payment amount exceeds due amount
                if ($request->amount > $itemShipmentCost->due_amount) {
                    throw new \Exception('Payment amount cannot exceed due amount.');
                }

                // Create payment
                ItemsShipmentPayment::create([
                    'items_shipment_cost_id' => $costId,
                    'amount' => $request->amount,
                    'payment_date' => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'reference_no' => $request->reference_no,
                    'notes' => $request->notes,
                    'paid_by' => $request->paid_by
                ]);

                // Update cost status using the model method
                $itemShipmentCost->updatePaymentStatus();

                // Update shipment status if all costs are paid
                $this->updateItemShipmentStatus($itemShipmentCost->items_shipment_id);
            });

            return redirect()->back()->with('success', 'Payment added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error adding payment: ' . $e->getMessage());
        }
    }

    // Update item shipment status based on costs
    private function updateItemShipmentStatus($itemShipmentId)
    {
        $itemShipment = ItemsShipment::with('costs')->find($itemShipmentId);
        $allCostsPaid = $itemShipment->costs->every(function ($cost) {
            return $cost->status === 'paid';
        });

        if ($allCostsPaid && $itemShipment->status !== 'completed') {
            $itemShipment->update(['status' => 'completed']);
        }
    }

    // Delete a cost
    public function destroyCost($costId)
    {
        try {
            $itemShipmentCost = ItemsShipmentCost::findOrFail($costId);
            $itemShipmentId = $itemShipmentCost->items_shipment_id;
            
            DB::transaction(function () use ($itemShipmentCost) {
                // Delete associated payments first
                $itemShipmentCost->payments()->delete();
                // Delete the cost
                $itemShipmentCost->delete();
            });

            return redirect()->route('itemshipments.costs.show', $itemShipmentId)
                ->with('success', 'Cost deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting cost: ' . $e->getMessage());
        }
    }

    // Delete a payment
    public function destroyPayment($paymentId)
    {
        try {
            $payment = ItemsShipmentPayment::findOrFail($paymentId);
            $costId = $payment->items_shipment_cost_id;
            $cost = ItemsShipmentCost::find($costId);
            $itemShipmentId = $cost->items_shipment_id;

            DB::transaction(function () use ($payment, $cost) {
                $payment->delete();
                // Update cost status after payment deletion
                $cost->updatePaymentStatus();
            });

            return redirect()->route('itemshipments.costs.show', $itemShipmentId)
                ->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }

    // Get financial summary (for AJAX requests)
    public function getFinancialSummary($itemShipmentId)
    {
        $itemShipment = ItemsShipment::findOrFail($itemShipmentId);
        
        return response()->json([
            'total_cost' => $itemShipment->total_cost,
            'total_paid' => $itemShipment->total_paid,
            'total_due' => $itemShipment->total_due,
            'pending_costs' => $itemShipment->pending_costs,
            'partial_costs' => $itemShipment->partial_costs,
            'paid_costs' => $itemShipment->paid_costs,
            'total_weight' => $itemShipment->total_weight,
            'total_cbm' => $itemShipment->total_cbm,
            'total_items' => $itemShipment->total_items,
        ]);
    }
public function pricepage(ItemsShipment $itemshipment)
    {
        $itemshipment->load('cargoItems');
        return view('admin.itemshipments.price', compact('itemshipment'));
    }
    public function updatePrices(Request $request, ItemsShipment $itemshipment)
{
    $request->validate([
        'prices' => 'required|array',
        'prices.*' => 'nullable|numeric',
    ]);

    try {
        DB::transaction(function () use ($request, $itemshipment) {
            foreach ($request->prices as $itemId => $pricePerKg) {
                $item = $itemshipment->cargoItems()->where('cargo_items.id', $itemId)->first();
                
                if ($item && $pricePerKg !== null) {
                    $totalPrice = $pricePerKg * ($item->weight_kg ?? 0);
                    
                    $item->update([
                        'price_per_kg' => $pricePerKg,
                        'price' => $totalPrice
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Prices updated successfully.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error updating prices: ' . $e->getMessage());
    }
}

public function ctn_update(ItemsShipment $itemshipment)
    {
        $itemshipment->load('cargoItems');
        $groupedItems = $itemshipment->cargoItems->groupBy('printed_ctn_no');

return view('admin.itemshipments.printed_ctn', compact('itemshipment', 'groupedItems'));
    }
public function updateCtnNumbers(Request $request, ItemsShipment $itemshipment)
{
    $request->validate([
        'printed_ctn' => 'required|array',
        'printed_ctn.*' => 'nullable|string|max:255',
    ]);

    try {
        DB::transaction(function () use ($request, $itemshipment) {

            foreach ($request->printed_ctn as $itemId => $ctnNo) {

                $item = $itemshipment->cargoItems()
                    ->where('cargo_items.id', $itemId)
                    ->first();

                if ($item) {

                    // Update CTN number
                    $item->printed_ctn_no = $ctnNo;

                    if (in_array($item->status, ['pending', 'packed'])) {
                            $item->status = 'ready_to_ship';
                        }

                    $item->save();
                }
            }
        });

        return redirect()->back()->with('success', 'CTN numbers updated successfully.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
    }
}


}