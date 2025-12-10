<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentCost;
use App\Models\ShipmentPayment;
use App\Models\CostType;
use App\Models\Agent;
use App\Models\CustomerBill;
use App\Models\CustomerDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentCostController extends Controller
{
    // Show shipment costs
    public function show($shipmentId)
    {
        $shipment = Shipment::with(['costs.costType', 'costs.agent', 'costs.payments', 'agent', 'cnfAgent', 'boxes'])->findOrFail($shipmentId);
        $costTypes = CostType::all();
        $agents = Agent::all();

        return view('admin.shipments.costs', compact('shipment', 'costTypes', 'agents'));
    }

    // Add cost to shipment
    public function store(Request $request, $shipmentId)
    {
        $request->validate([
            'cost_type_id' => 'required|exists:cost_types,id',
            'agent_id' => 'nullable|exists:agents,id',
            'rate_per_kg' => 'nullable|numeric|min:0',
            'total_weight' => 'nullable|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'cost_date' => 'required|date'
        ]);

        try {
            DB::transaction(function () use ($request, $shipmentId) {
                $amount = $request->amount;
                if ($request->rate_per_kg && $request->total_weight) {
                    $amount = $request->rate_per_kg * $request->total_weight;
                }

                $shipmentCost = ShipmentCost::create([
                    'shipment_id' => $shipmentId,
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

                // Update shipment status
                $shipment = Shipment::find($shipmentId);
                if ($shipment->status === 'pending') {
                    $shipment->update(['status' => 'cost_calculated']);
                }
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
                $shipmentCost = ShipmentCost::findOrFail($costId);
                
                // Check if payment amount exceeds due amount
                if ($request->amount > $shipmentCost->due_amount) {
                    throw new \Exception('Payment amount cannot exceed due amount.');
                }

                // Create payment
                ShipmentPayment::create([
                    'shipment_cost_id' => $costId,
                    'amount' => $request->amount,
                    'payment_date' => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'reference_no' => $request->reference_no,
                    'notes' => $request->notes,
                    'paid_by' => $request->paid_by
                ]);

                // Update cost status
                $newPaidAmount = $shipmentCost->paid_amount + $request->amount;
                $newDueAmount = $shipmentCost->amount - $newPaidAmount;

                $status = 'pending';
                if ($newDueAmount <= 0) {
                    $status = 'paid';
                } elseif ($newPaidAmount > 0) {
                    $status = 'partial';
                }

                $shipmentCost->update([
                    'paid_amount' => $newPaidAmount,
                    'due_amount' => max(0, $newDueAmount),
                    'status' => $status
                ]);

                // Update shipment status if all costs are paid
                $this->updateShipmentStatus($shipmentCost->shipment_id);
            });

            return redirect()->back()->with('success', 'Payment added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error adding payment: ' . $e->getMessage());
        }
    }

    // Update shipment status based on costs
    private function updateShipmentStatus($shipmentId)
    {
        $shipment = Shipment::with('costs')->find($shipmentId);
        $allCostsPaid = $shipment->costs->every(function ($cost) {
            return $cost->status === 'paid';
        });

        if ($allCostsPaid && $shipment->status !== 'completed') {
            $shipment->update(['status' => 'completed']);
        }
    }
    public function generateCustomerBills($shipmentId)
{
    $shipment = Shipment::with('boxes.products.customer')->findOrFail($shipmentId);

    $customers = [];

    foreach ($shipment->boxes as $box) {
        foreach ($box->products as $product) {

            $cid = $product->customer_id;

            if (!isset($customers[$cid])) {
                $customers[$cid] = [
                    'total_weight' => 0,
                    'total_price' => 0,
                ];
            }

            // weight inside this shipment
            $weight = $product->pivot->weight_in_box;

            // price per kg (you must ensure `price_per_kg` column exists)
            $pricePerKg = $product->price;

            $customers[$cid]['total_weight'] += $weight;

            // price based on per kg
            $customers[$cid]['total_price'] += ($weight * $pricePerKg);
        }
    }

    foreach ($customers as $customerId => $data) {
        CustomerBill::updateOrCreate(
            [
                'shipment_id' => $shipmentId,
                'customer_id' => $customerId,
            ],
            [
                'total_weight' => $data['total_weight'],
                'total_price' => $data['total_price'],
                'due_amount'   => $data['total_price'], // due = total
            ]
        );
    }

    return back()->with('success', 'Customer bills generated successfully.');
}
public function customerBillPayment(Request $request, $billId)
{
    $bill = CustomerBill::findOrFail($billId);

    $request->validate([
        'amount' => 'required|numeric|min:1',
        'delivery_weight' => 'required|numeric|min:0.01|max:' . $bill->total_weight,
        'delivery_date' => 'required|date',
    ]);

    DB::transaction(function () use ($bill, $request) {
        $deliveryKg = $request->delivery_weight;
        $amount = $request->amount;

        // Add delivery + payment
        CustomerDelivery::create([
            'customer_bill_id' => $bill->id,
            'delivered_weight' => $deliveryKg,
            'amount' => $amount,
            'delivery_date' => $request->delivery_date,
            'notes' => $request->notes
        ]);

        // Update totals
        $paid = $bill->deliveries()->sum('amount');
        $deliveredWeight = $bill->deliveries()->sum('delivered_weight');

        $bill->update([
            'paid_amount' => $paid,
            'due_amount' => $bill->total_price - $paid,
            'status' => ($bill->total_price - $paid <= 0 && $bill->total_weight - $deliveredWeight <= 0) ? 'paid' : 'partial',
        ]);
    });

    return redirect()->back()->with('success', 'Payment and delivery updated successfully.');
}
public function customerBills($shipmentId)
{
    $shipment = Shipment::with('boxes.products.customer')->findOrFail($shipmentId);

    // --- Generate / Update Customer Bills ---
    $customers = [];

    foreach ($shipment->boxes as $box) {
        foreach ($box->products as $product) {
            $cid = $product->customer_id;

            if (!isset($customers[$cid])) {
                $customers[$cid] = [
                    'total_weight' => 0,
                    'total_price'  => 0,
                ];
            }

            $weight = $product->pivot->weight_in_box;
            $pricePerKg = $product->price;

            $customers[$cid]['total_weight'] += $weight;
            $customers[$cid]['total_price']  += ($weight * $pricePerKg);
        }
    }

    foreach ($customers as $customerId => $data) {
        CustomerBill::updateOrCreate(
            [
                'shipment_id' => $shipmentId,
                'customer_id' => $customerId,
            ],
            [
                'total_weight' => $data['total_weight'],
                'total_price'  => $data['total_price'],
                'paid_amount'  => 0,
                'due_amount'   => $data['total_price'], // initial due
                'status'       => 'pending'
            ]
        );
    }

    // --- Fetch bills to display, calculate dynamic due based on payments ---
    $bills = CustomerBill::with('customer', 'deliveries')->where('shipment_id', $shipmentId)->get();

    foreach ($bills as $bill) {
        $paid = $bill->deliveries->sum('amount');
        $deliveredWeight = $bill->deliveries->sum('delivered_weight');
        $bill->paid_amount = $paid;
        $bill->due_amount = $bill->total_price - $paid;
        $bill->remaining_weight = $bill->total_weight - $deliveredWeight;

        // Set status dynamically
        if ($bill->due_amount <= 0 && $bill->remaining_weight <= 0) {
            $bill->status = 'paid';
        } elseif ($bill->paid_amount > 0 || $deliveredWeight > 0) {
            $bill->status = 'partial';
        } else {
            $bill->status = 'pending';
        }
    }

    return view('admin.shipments.customer_bills', compact('shipment', 'bills'));
}

public function customerBillHistory($billId)
{
    $bill = CustomerBill::with('deliveries')->findOrFail($billId);

    return view('admin.shipments.partials.bill_history', compact('bill'));
}
    // Show cost summary in blade
   public function summary($shipmentId)
{
    $shipment = Shipment::with([
        'costs.costType',
        'costs.payments',
        'boxes.products.customer',
        'customerBills.deliveries',
        'customerBills.customer'
    ])->findOrFail($shipmentId);

    // Shipment expenses
    $totalShipmentCost = $shipment->costs->sum('amount');
    $totalShipmentPaid = $shipment->costs->sum('paid_amount');
    $totalShipmentDue  = $shipment->costs->sum('due_amount');

    $costsByType = $shipment->costs->groupBy(function ($cost) {
        return $cost->costType ? $cost->costType->name : 'Other';
    })->map(function ($items) {
        return [
            'total_amount' => $items->sum('amount'),
            'paid_amount'  => $items->sum('paid_amount'),
            'due_amount'   => $items->sum('due_amount'),
        ];
    });

    $bills = $shipment->customerBills;

    $totalCustomerPrice = $bills->sum('total_price');
    $totalCustomerPaid  = $bills->sum(function($bill){
        return $bill->deliveries->sum('amount'); // calculate paid from deliveries
    });
    $totalCustomerDue   = $bills->sum(function($bill){
        return $bill->total_price - $bill->deliveries->sum('amount');
    });
    $totalDeliveredWeight = $bills->sum(function($bill){
        return $bill->deliveries->sum('delivered_weight');
    });
    $totalRemainingWeight = $bills->sum('total_weight') - $totalDeliveredWeight;

    $summary = [
        'shipment' => [
            'total_cost' => $totalShipmentCost,
            'total_paid' => $totalShipmentPaid,
            'total_due'  => $totalShipmentDue,
            'costs_by_type' => $costsByType,
        ],
        'customers' => [
            'total_price' => $totalCustomerPrice,
            'total_paid'  => $totalCustomerPaid,
            'total_due'   => $totalCustomerDue,
            'total_delivered_weight' => $totalDeliveredWeight,
            'total_remaining_weight' => $totalRemainingWeight,
        ]
    ];

    return view('admin.shipments.summary', compact('shipment', 'summary'));
}


}