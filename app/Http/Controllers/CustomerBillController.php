<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CargoItem;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerBillController extends Controller
{
    // Show customer billing page for a shipment
  
    public function showShipmentBilling(ItemsShipment $itemshipment)
    {
        if (request()->boolean('pdf')) {
            return $this->billingPdf($itemshipment);
        }

        $items = $itemshipment->cargoItems()
            ->with(['customer'])
            ->get()
            ->groupBy('customer_id');

        $billingSummary = [];
        $totalItems = 0;
        $totalPricedItems = 0;
        $totalUnpricedItems = 0;
        $totalAssignedItems = 0;
        $totalUnassignedItems = 0;

        foreach ($items as $customerId => $customerItems) {
            $customer = $customerId ? Customer::find($customerId) : null;

            $totalPrice = 0;
            $totalWeight = 0;
            $itemsWithPrice = 0;
            $itemsWithoutPrice = 0;

            foreach ($customerItems as $item) {
                $totalWeight += $item->weight_kg;

                if ($item->hasPrice()) {
                    $totalPrice += $item->total_price;
                    $itemsWithPrice++;
                } else {
                    $itemsWithoutPrice++;
                }
            }

            // ------------------------------------------------
            // Calculate paid amount for this customer + shipment
            // Using the SAME logic as PaymentController::create()
            // ------------------------------------------------
            $paidAmount = 0;

            if ($customerId) {
                $paidAmount = CustomerPayment::where('customer_id', $customerId)
                    ->whereHas('paymentItems.cargoItem.itemshipments', function ($q) use ($itemshipment) {
                        $q->where('items_shipments.id', $itemshipment->id);
                    })
                    ->sum('amount');
            }

            $billingSummary[] = [
                'customer_id'         => $customerId,
                'customer_name'       => $customer ? $customer->name : 'Unassigned',
                'customer_code'       => $customer ? $customer->customer_code : 'N/A',
                'total_items'         => count($customerItems),
                'items_with_price'    => $itemsWithPrice,
                'items_without_price' => $itemsWithoutPrice,
                'total_weight'        => $totalWeight,
                'total_price'         => $totalPrice,
                'paid_amount'         => $paidAmount,
                'due_amount'          => max($totalPrice - $paidAmount, 0),
                'items'               => $customerItems,
            ];

            $totalItems += count($customerItems);
            $totalPricedItems += $itemsWithPrice;
            $totalUnpricedItems += $itemsWithoutPrice;

            if ($customerId) {
                $totalAssignedItems += count($customerItems);
            } else {
                $totalUnassignedItems += count($customerItems);
            }
        }

        return view('admin.itemshipments.billing', compact(
            'itemshipment',
            'billingSummary',
            'totalItems',
            'totalPricedItems',
            'totalUnpricedItems',
            'totalAssignedItems',
            'totalUnassignedItems'
        ));
    }
    // Show individual customer bill
  public function showCustomerBill(Customer $customer, ItemsShipment $itemshipment = null)
{
    $query = $customer->cargoItems()->with(['itemshipments']);

    if ($itemshipment) {
        $query->whereHas('itemshipments', function ($q) use ($itemshipment) {
            $q->where('items_shipments.id', $itemshipment->id);
        });
    }

    $items = $query->get();

    $totalItems = $items->count();
    $totalWeight = $items->sum('weight_kg');
    $totalPrice = 0;
    $itemsWithPrice = 0;
    $itemsWithoutPrice = 0;

    $itemDetails = [];
    foreach ($items as $item) {
        $hasPrice = $item->hasPrice();
        $itemPrice = $item->total_price;

        if ($hasPrice) {
            $totalPrice += $itemPrice;
            $itemsWithPrice++;
        } else {
            $itemsWithoutPrice++;
        }

        $itemDetails[] = [
            'id'               => $item->id,
            'goods_name_en'    => $item->goods_name_en,
            'goods_name_local' => $item->goods_name_local,
            'weight_kg'        => $item->weight_kg,
            'cbm'              => $item->cbm,
            'tracking_no'      => $item->tracking_no,
            'has_price'        => $hasPrice,
            'price'            => $item->price,
            'price_per_kg'     => $item->price_per_kg,
            'total_price'      => $itemPrice,
            'shipments'        => $item->itemshipments->pluck('shipment_name')->implode(', '),
        ];
    }

    // Invoice: only when scoped to a shipment
    $invoice = null;
    if ($itemshipment) {
        $invoice = Invoice::forPair($customer->id, $itemshipment->id);
    }

    if (request()->boolean('pdf')) {
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.pdf.bill', compact(
            'customer', 'itemshipment', 'invoice', 'itemDetails', 'totalItems', 'totalWeight', 'totalPrice', 'itemsWithPrice', 'itemsWithoutPrice'
        ))->setPaper('a4')->download('invoice-' . preg_replace('/[^A-Za-z0-9_-]/', '', $customer->customer_code ?: $customer->id)
            . ($itemshipment ? '-shipment-' . $itemshipment->id : '') . '.pdf');
    }

    return view('admin.itemshipments.bill', compact(
        'customer',
        'itemshipment',
        'invoice',
        'itemDetails',
        'totalItems',
        'totalWeight',
        'totalPrice',
        'itemsWithPrice',
        'itemsWithoutPrice'
    ));
}
    
    // Update item prices
    public function updateItemPrices(Request $request, ItemsShipment $itemshipment)
    {
        $request->validate([
            'prices' => 'required|array',
            'prices.*.price' => 'nullable|numeric|min:0',
            'prices.*.price_per_kg' => 'nullable|numeric|min:0',
        ]);
        
        try {
            DB::transaction(function () use ($request, $itemshipment) {
                foreach ($request->prices as $productId => $priceData) {
                    $itemshipment->cargoItems()
                        ->where('cargo_items.id', $productId)
                        ->update([
                            'price' => $priceData['price'] ?? null,
                            'price_per_kg' => $priceData['price_per_kg'] ?? null
                        ]);
                }
            });
            
            return redirect()->back()
                ->with('success', 'Prices updated successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating prices: ' . $e->getMessage());
        }
    }

    /** PDF of the whole shipment billing: every customer / third party billed, received, due. */
    private function billingPdf(ItemsShipment $itemshipment)
    {
        $acc  = app(\App\Services\AccountingService::class);
        $tpS  = app(\App\Services\ThirdPartyLedgerService::class);
        $rows = $acc->shipmentCustomers([$itemshipment->id]);
        $tp   = $tpS->shipmentRows(['shipment_ids' => [$itemshipment->id]]);
        $tpNames = \App\Models\ThirdParty::whereIn('id', $tp->pluck('third_party_id'))->pluck('name', 'id');

        $lines = [];
        foreach ($rows as $r) {
            $lines[] = [optional($r->customer)->customer_code, optional($r->customer)->name ?? ('#' . $r->customer_id), 'Customer',
                        $r->items, (float) $r->weight, $r->billed, $r->paid, $r->due];
        }
        foreach ($tp as $r) {
            $lines[] = ['', $tpNames[$r->third_party_id] ?? ('#' . $r->third_party_id), 'Third party', $r->items, (float) $r->weight, $r->billed, '', ''];
        }
        $billed = collect($lines)->sum(5);

        $sheet = [
            'title' => 'Billing', 'heading' => 'Shipment billing - ' . $itemshipment->shipment_name,
            'subtitle' => 'Generated ' . now()->format('d M Y H:i') . ' | Total billed ' . number_format($billed, 2),
            'headings' => ['Code', 'Party', 'Type', 'Items', 'Weight (kg)', 'Billed', 'Received', 'Due'],
            'rows' => $lines,
            'totals' => ['TOTAL', '', '', collect($lines)->sum(3), collect($lines)->sum(4), $billed, $rows->sum('paid'), $rows->sum('due')],
            'money' => [4, 5, 6, 7], 'int' => [3],
        ];

        return \App\Support\SheetsPdf::download([$sheet], 'billing-shipment-' . $itemshipment->id . '.pdf');
    }
}
