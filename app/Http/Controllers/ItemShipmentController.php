<?php

namespace App\Http\Controllers;

use App\Models\CargoItem;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemShipmentController extends Controller
{
    public function index()
    {
        $shipments = ItemsShipment::with('cargoItems')->latest()->get();
        return view('admin.itemshipments.index', compact('shipments'));
    }

   
    public function create()
    {
        $items = CargoItem::availableForShipment()
            ->whereDoesntHave('itemshipments')
            ->get();
        
        return view('admin.itemshipments.create', compact('items'));
    }

   public function store(Request $request)
{
    $request->validate([
        'shipment_date' => 'nullable|date',
        'selected_items' => 'required|string',
    ]);

    DB::transaction(function () use ($request) {
        // Generate automatic shipment name if not provided
        $shipmentName = $request->shipment_name ?: ItemsShipment::generateShipmentName();

        $shipment = ItemsShipment::create([
            'shipment_name' => $shipmentName,
            'shipment_date' => $request->shipment_date,
            'note' => $request->note,
            'status' => $request->status ?? 'draft',
        ]);

        $itemIds = explode(',', $request->selected_items);
        
        // Attach items to shipment
        $shipment->cargoItems()->attach($itemIds);
        
        if ($shipment->status === 'pending') {
            CargoItem::whereIn('id', $itemIds)
                     ->where('status', 'packed')
                     ->update(['status' => 'shipping_ongoing']);
        } else {
            // For other statuses (draft), set to packed
            CargoItem::whereIn('id', $itemIds)->update(['status' => 'packed']);
        }
        if ($shipment->status === 'waiting_for_customs') {
            CargoItem::whereIn('id', $itemIds)
                     ->where('status', 'shipping_ongoing')
                     ->update(['status' => 'waiting_for_customs']);
        } else {
            CargoItem::whereIn('id', $itemIds)->update(['status' => 'shipping_ongoing']);
        }
        if ($shipment->status === 'delivered') {
            CargoItem::whereIn('id', $itemIds)
                     ->where('status', 'waiting_for_customs')
                     ->update(['status' => 'delivered']);
        } else {
            CargoItem::whereIn('id', $itemIds)->update(['status' => 'waiting_for_customs']);
        }
    });

    return redirect()->route('itemsshipments.index')
        ->with('success', 'Shipment created successfully with ' . count(explode(',', $request->selected_items)) . ' items.');
}

    public function show(ItemsShipment $itemshipment)
    {
        $itemshipment->load('cargoItems');
         $groupedItems = $itemshipment->cargoItems->groupBy('printed_ctn_no');
        return view('admin.itemshipments.show', compact('itemshipment', 'groupedItems'));
    }

    public function edit(ItemsShipment $itemshipment)
    {
        $itemshipment->load('cargoItems');
        
        // Get available items (pending or packed and not in this shipment)
        $availableItems = CargoItem::availableForShipment()
            ->whereDoesntHave('itemshipments', function($query) use ($itemshipment) {
                $query->where('items_shipment_id', '!=', $itemshipment->id);
            })
            ->get();

        return view('admin.itemshipments.edit', compact('itemshipment', 'availableItems'));
    }

   public function update(Request $request, ItemsShipment $itemshipment)
{
    $request->validate([
        'shipment_name' => 'required|string|max:255',
        'shipment_date' => 'nullable|date',
    ]);

    DB::transaction(function () use ($request, $itemshipment) {
        $oldStatus = $itemshipment->status;
        $newStatus = $request->status;

        // Update shipment
        $itemshipment->update([
            'shipment_name' => $request->shipment_name,
            'shipment_date' => $request->shipment_date,
            'note' => $request->note,
            'status' => $newStatus,
        ]);

        // Handle added/removed items
        if ($request->has('selected_items')) {
            $newItemIds = explode(',', $request->selected_items);
            $currentItemIds = $itemshipment->cargoItems->pluck('id')->toArray();

            $itemsToRemove = array_diff($currentItemIds, $newItemIds);
            $itemsToAdd = array_diff($newItemIds, $currentItemIds);

            // Sync items
            $itemshipment->cargoItems()->sync($newItemIds);

            if (!empty($itemsToRemove)) {
                // Removed items go back to pending
                CargoItem::whereIn('id', $itemsToRemove)->update(['status' => 'pending']);
            }

            if (!empty($itemsToAdd)) {
                // Added items follow the shipment status
                switch ($newStatus) {
                    case 'pending':
                        CargoItem::whereIn('id', $itemsToAdd)
                                 ->where('status', 'packed')
                                 ->update(['status' => 'shipping_ongoing']);
                        break;
                    case 'waiting_for_customs':
                        CargoItem::whereIn('id', $itemsToAdd)
                                 ->where('status', 'shipping_ongoing')
                                 ->update(['status' => 'waiting_for_customs']);
                        break;
                    case 'delivered':
                        CargoItem::whereIn('id', $itemsToAdd)
                                 ->where('status', 'waiting_for_customs')
                                 ->update(['status' => 'ready_to_deliver']);
                        break;
                    default:
                        CargoItem::whereIn('id', $itemsToAdd)->update(['status' => 'packed']);
                }
            }
        }

        // Update existing items based on status change
        switch ($newStatus) {
            case 'pending':
                CargoItem::whereIn('id', $itemshipment->cargoItems->pluck('id')->toArray())
                         ->where('status', 'packed')
                         ->update(['status' => 'shipping_ongoing']);
                break;

            case 'waiting_for_customs':
                CargoItem::whereIn('id', $itemshipment->cargoItems->pluck('id')->toArray())
                         ->where('status', 'shipping_ongoing')
                         ->update(['status' => 'waiting_for_customs']);
                break;

            case 'delivered':
                CargoItem::whereIn('id', $itemshipment->cargoItems->pluck('id')->toArray())
                         ->where('status', 'waiting_for_customs')
                         ->update(['status' => 'ready_to_deliver']);
                break;

            default:
                CargoItem::whereIn('id', $itemshipment->cargoItems->pluck('id')->toArray())
                         ->update(['status' => 'packed']);
        }
    });

    return redirect()->route('itemsshipments.show', $itemshipment->id)
                     ->with('success', 'Shipment updated successfully.');
}

    public function destroy(ItemsShipment $itemshipment)
    {
        DB::transaction(function () use ($itemshipment) {
            // Get item IDs before deletion
            $itemIds = $itemshipment->cargoItems->pluck('id')->toArray();
            
            // Update items status back to pending
            CargoItem::whereIn('id', $itemIds)->update(['status' => 'pending']);
            
            // Delete shipment (this will automatically remove pivot records due to cascade)
            $itemshipment->delete();
        });

        return redirect()->route('itemsshipments.index')
            ->with('success', 'Shipment deleted successfully.');
    }

    public function getShipmentItems()
    {
        $items = CargoItem::availableForShipment()
            ->whereDoesntHave('itemshipments')
            ->get();
            
        return view('admin.cargo_items.index', compact('items'));
    }

    public function getOrCreateDraft()
{
    try {
        // Get the first draft shipment or create a new one
        $shipment = ItemsShipment::where('status', 'draft')->first();
        
        if (!$shipment) {
            $shipment = ItemsShipment::create([
                'shipment_name' => ItemsShipment::generateShipmentName(),
                'status' => 'draft'
            ]);
        }

        return response()->json([
            'success' => true,
            'shipment' => $shipment
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


public function toggleItem(Request $request)
{
    try {
        $request->validate([
            'shipment_id' => 'required|exists:items_shipments,id',
            'item_id' => 'required|exists:cargo_items,id'
        ]);

        $shipment = ItemsShipment::findOrFail($request->shipment_id);
        $itemId = $request->item_id;

        // Check if item is already in this shipment
        $itemExists = $shipment->cargoItems()->where('cargo_item_id', $itemId)->exists();

        if ($itemExists) {
            // Remove item from shipment
            $shipment->cargoItems()->detach($itemId);
            CargoItem::where('id', $itemId)->update(['status' => 'pending']);
            
            return response()->json([
                'success' => true,
                'action' => 'removed',
                'message' => 'Item removed from shipment'
            ]);
        } else {
            // Add item to shipment
            $shipment->cargoItems()->attach($itemId);
            CargoItem::where('id', $itemId)->update(['status' => 'packed']);
            
            return response()->json([
                'success' => true,
                'action' => 'added',
                'message' => 'Item added to shipment'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}