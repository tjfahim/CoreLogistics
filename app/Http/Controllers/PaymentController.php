<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CargoItem;
use App\Models\CustomerPaymentItem;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    // Show payment form for a specific shipment
    public function create(Customer $customer, ItemsShipment $itemshipment)
    {
        $items = $customer->cargoItems()
            ->where(function($query) {
                $query->whereNotNull('price')
                      ->orWhereNotNull('price_per_kg');
            })
            ->whereNull('paid_at') // Only unpaid items
            ->whereHas('itemshipments', function ($q) use ($itemshipment) {
                $q->where('items_shipments.id', $itemshipment->id);
            })
            ->get();
        // Calculate total bill from ALL items in THIS shipment (paid + unpaid)
        $allItems = $customer->cargoItems()
            ->where(function($query) {
                $query->whereNotNull('price')
                      ->orWhereNotNull('price_per_kg');
            })
            ->whereHas('itemshipments', function ($q) use ($itemshipment) {
                $q->where('items_shipments.id', $itemshipment->id);
            })
            ->get();
        
        $totalAmount = $allItems->sum(function($item) {
            return $item->total_price ?? 0;
        });
        
        // Calculate total paid for THIS shipment
        $totalPaid = CustomerPayment::where('customer_id', $customer->id)
            ->whereHas('paymentItems.cargoItem.itemshipments', function ($q) use ($itemshipment) {
                $q->where('items_shipments.id', $itemshipment->id);
            })
            ->sum('amount');
        
        $totalDue = $totalAmount - $totalPaid;
        
        // Add shipment info to the view
        return view('admin.payments.create', compact(
            'customer',
            'itemshipment',
            'items',
            'totalAmount',
            'totalPaid',
            'totalDue'
        ));
    }
    
    // Process payment for a specific shipment
    public function store(Request $request, Customer $customer, ItemsShipment $itemshipment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,card',
            'payment_date' => 'required|date',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'exists:cargo_items,id'
        ]);
        
        // Verify selected items belong to this shipment
        $selectedItems = CargoItem::whereIn('id', $request->selected_items)
            ->whereNull('paid_at')
            ->whereHas('itemshipments', function ($q) use ($itemshipment) {
                $q->where('items_shipments.id', $itemshipment->id);
            })
            ->get();
            
        if ($selectedItems->count() !== count($request->selected_items)) {
            return redirect()->back()
                ->with('error', 'Some selected items do not belong to this shipment or are already paid!')
                ->withInput();
        }
        
        // Calculate totals for THIS shipment
        $allItems = $customer->cargoItems()
            ->where(function($query) {
                $query->whereNotNull('price')
                      ->orWhereNotNull('price_per_kg');
            })
            ->whereHas('itemshipments', function ($q) use ($itemshipment) {
                $q->where('items_shipments.id', $itemshipment->id);
            })
            ->get();
        
        $totalAmount = $allItems->sum(function($item) {
            return $item->total_price ?? 0;
        });
        
        $totalPaid = CustomerPayment::where('customer_id', $customer->id)
            ->whereHas('paymentItems.cargoItem.itemshipments', function ($q) use ($itemshipment) {
                $q->where('items_shipments.id', $itemshipment->id);
            })
            ->sum('amount');
        
        $totalDue = $totalAmount - $totalPaid;
        
        // Calculate selected items total
        $selectedTotal = $selectedItems->sum('total_price');
        
        if ($request->amount != $selectedTotal) {
            return redirect()->back()
                ->with('error', 'Payment amount must match selected items total!')
                ->withInput();
        }
        
        if ($request->amount > $totalDue) {
            return redirect()->back()
                ->with('error', 'Payment amount cannot exceed total due amount!')
                ->withInput();
        }
        
        try {
            DB::transaction(function () use ($request, $customer, $itemshipment, $totalDue, $selectedItems) {
                // Create payment record
                $payment = CustomerPayment::create([
                    'customer_id' => $customer->id,
                    'itemshipment_id' => $itemshipment->id, // Add shipment reference
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'payment_date' => $request->payment_date,
                    'reference_no' => $request->reference_no,
                    'notes' => $request->notes,
                    'remaining_due' => $totalDue - $request->amount
                ]);
                
                foreach ($selectedItems as $item) {
                    CustomerPaymentItem::create([
                        'customer_payment_id' => $payment->id,
                        'cargo_item_id' => $item->id,
                        'amount' => $item->total_price
                    ]);
                    
                     $item->paid_at = now();
    $item->save();
                }
            });
            
            // Redirect back to the shipment bill page
            return redirect()->back()->with('success', 'Payment processed successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error processing payment: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    // Show payment history for a specific shipment
    public function history(Customer $customer, ItemsShipment $itemshipment)
    {
        // Get payments for THIS shipment
        $payments = CustomerPayment::where('customer_id', $customer->id)
            ->where('itemshipment_id', $itemshipment->id) // Filter by shipment
            ->with(['paymentItems.cargoItem'])
            ->orderBy('payment_date', 'desc')
            ->get();
        
        // Get all items in THIS shipment
        $items = $customer->cargoItems()
            ->where(function($query) {
                $query->whereNotNull('price')
                      ->orWhereNotNull('price_per_kg');
            })
            ->whereHas('itemshipments', function ($q) use ($itemshipment) {
                $q->where('items_shipments.id', $itemshipment->id);
            })
            ->get();
        
        $totalAmount = $items->sum(function($item) {
            return $item->total_price ?? 0;
        });
        
        $totalPaid = $payments->sum('amount');
        $totalDue = $totalAmount - $totalPaid;
        
        return view('admin.payments.history', compact(
            'customer',
            'itemshipment',
            'payments',
            'totalAmount',
            'totalPaid',
            'totalDue'
        ));
    }
}