<?php

namespace App\Http\Controllers;

use App\Models\CargoItem;
use App\Models\ItemReturnHistory;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemShipmentController extends Controller
{
    /**
     * Map shipment status → cargo item status
     * Adjust these if your workflows differ.
     */
    private const SHIPMENT_TO_ITEM_STATUS = [
        'draft'                 => 'packed',
        'pending'               => 'shipping_ongoing',
        'shipped'               => 'shipping_ongoing',
        'arrived_bd'            => 'arrived_bd',
        'waiting_for_customs'   => 'waiting_for_customs',
        'delivered'             => 'ready_to_deliver',
        'cancelled'             => 'pending',
    ];

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
            'shipment_date'  => 'nullable|date',
            'selected_items' => 'required|string',
        ]);

        $itemIds = array_filter(array_map('intval', explode(',', $request->selected_items)));

        DB::transaction(function () use ($request, $itemIds) {
            $shipmentName = $request->shipment_name ?: ItemsShipment::generateShipmentName();

            $shipment = ItemsShipment::create([
                'shipment_name' => $shipmentName,
                'shipment_date' => $request->shipment_date,
                'note'          => $request->note,
                'status'        => $request->status ?? 'draft',
            ]);

            // Attach items
            $shipment->cargoItems()->attach($itemIds);

            // Sync all attached items to match shipment status
            $this->syncItemsStatus($itemIds, $shipment->status);
        });

        return redirect()->route('itemsshipments.index')
            ->with('success', 'Shipment created successfully with ' . count($itemIds) . ' items.');
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

    // IDs already attached to this shipment
    $assignedIds = $itemshipment->cargoItems->pluck('id')->toArray();

    // Available items = pending/packed AND NOT attached to ANY shipment
    $availableItems = CargoItem::whereIn('status', ['pending', 'packed'])
        ->whereDoesntHave('itemshipments')     // not attached anywhere
        ->orderBy('id', 'desc')
        ->get();

    return view('admin.itemshipments.edit', compact('itemshipment', 'availableItems'));
}
public function manageReturns(ItemsShipment $itemshipment)
{
    $itemshipment->load('cargoItems.latestReturn');
    return view('admin.itemshipments.returns', compact('itemshipment'));
}

/**
 * Return selected items from a shipment (AJAX).
 */
public function returnItems(Request $request, ItemsShipment $itemshipment)
{
    $data = $request->validate([
        'item_ids'    => 'required|string',
        'return_date' => 'required|date',
        'note'        => 'nullable|string|max:1000',
    ]);

    $ids = array_filter(array_map('intval', explode(',', $data['item_ids'])));

    if (empty($ids)) {
        return response()->json(['success' => false, 'message' => 'No items selected.'], 422);
    }

    // Only items currently attached to THIS shipment
    $validIds = $itemshipment->cargoItems()
        ->whereIn('cargo_items.id', $ids)
        ->pluck('cargo_items.id')
        ->toArray();

    if (empty($validIds)) {
        return response()->json(['success' => false, 'message' => 'No matching items in this shipment.'], 422);
    }

    DB::transaction(function () use ($validIds, $data, $itemshipment) {
        $items = CargoItem::whereIn('id', $validIds)->get();

        foreach ($items as $item) {
            ItemReturnHistory::create([
                'cargo_item_id'     => $item->id,
                'items_shipment_id' => $itemshipment->id,
                'note'              => $data['note'] ?? null,
                'return_date'       => $data['return_date'],
                'previous_status'   => $item->status,
                'returned_by'       => auth()->id(),
            ]);

            // Detach from this shipment
            $itemshipment->cargoItems()->detach($item->id);

            // Reset to pending
            $item->update(['status' => 'pending']);
        }
    });

    return response()->json([
        'success' => true,
        'message' => count($validIds) . ' item(s) returned to pending.',
    ]);
}
public function update(Request $request, ItemsShipment $itemshipment)
{
    $request->validate([
        'shipment_name'  => 'required|string|max:255',
        'shipment_date'  => 'nullable|date',
        'status'         => 'nullable|string',
          'selected_items' => 'nullable', 
    ]);

    \Log::info('=== SHIPMENT UPDATE ===', [
        'shipment_id'  => $itemshipment->id,
        'raw_selected' => $request->input('selected_items'),
        'raw_status'   => $request->input('status'),
    ]);

    DB::transaction(function () use ($request, $itemshipment) {

        // 1. Update shipment fields
        $itemshipment->update([
            'shipment_name' => $request->shipment_name,
            'shipment_date' => $request->shipment_date,
            'note'          => $request->note,
            'status'        => $request->status ?? $itemshipment->status,
        ]);

        // 2. Get CURRENT attached item IDs (before sync)
        $currentItemIds = $itemshipment->cargoItems()
            ->pluck('cargo_items.id')
            ->toArray();

        // 3. Parse selected_items
        $newItemIds = [];
        $raw = (string) $request->input('selected_items', '');
        if ($raw !== '') {
            foreach (explode(',', $raw) as $p) {
                $id = (int) trim($p);
                if ($id > 0) {
                    $newItemIds[] = $id;
                }
            }
            $newItemIds = array_values(array_unique($newItemIds));
        }

        \Log::info('Parsed item IDs', [
            'current' => $currentItemIds,
            'new'     => $newItemIds,
        ]);

        $itemsToRemove = array_values(array_diff($currentItemIds, $newItemIds));
        $itemsToAdd    = array_values(array_diff($newItemIds, $currentItemIds));

        // 4. Sync pivot table
        $itemshipment->cargoItems()->sync($newItemIds);

        // 5. Removed items → reset to pending
        if (!empty($itemsToRemove)) {
            CargoItem::whereIn('id', $itemsToRemove)->update(['status' => 'pending']);
        }

        // 6. Attached items → follow shipment status
        if (!empty($newItemIds)) {
            $this->syncItemsStatus($newItemIds, $itemshipment->status);
        }

        \Log::info('Update complete', [
            'final_attached'  => $newItemIds,
            'shipment_status' => $itemshipment->status,
            'removed'         => $itemsToRemove,
            'added'           => $itemsToAdd,
        ]);
    });

    return redirect()
        ->route('itemsshipments.show', $itemshipment->id)
        ->with('success', 'Shipment updated successfully.');
}

    public function destroy(ItemsShipment $itemshipment)
    {
        DB::transaction(function () use ($itemshipment) {
            // Get attached item IDs
            $itemIds = $itemshipment->cargoItems->pluck('id')->toArray();

            // Detach first (so pivot is cleared cleanly even if DB cascade differs)
            if (!empty($itemIds)) {
                $itemshipment->cargoItems()->detach();
                CargoItem::whereIn('id', $itemIds)->update(['status' => 'pending']);
            }

            // Now delete the shipment
            $itemshipment->delete();
        });

        return redirect()->route('itemsshipments.index')
            ->with('success', 'Shipment deleted successfully. All attached items returned to pending.');
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
            $shipment = ItemsShipment::where('status', 'draft')->first();

            if (!$shipment) {
                $shipment = ItemsShipment::create([
                    'shipment_name' => ItemsShipment::generateShipmentName(),
                    'status'        => 'draft'
                ]);
            }

            return response()->json([
                'success'  => true,
                'shipment' => $shipment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function bulkUnpackItems(Request $request)
{
    try {
        $request->validate([
            'shipment_id' => 'required|exists:items_shipments,id',
            'item_ids'    => 'required|array|min:1',
            'item_ids.*'  => 'integer|exists:cargo_items,id',
        ]);

        $shipment = ItemsShipment::findOrFail($request->shipment_id);
        $itemIds  = $request->item_ids;

        // Which of these are actually in this shipment?
        $inShipment = $shipment->cargoItems()
            ->whereIn('cargo_item_id', $itemIds)
            ->pluck('cargo_item_id')
            ->all();

        if (!empty($inShipment)) {
            $shipment->cargoItems()->detach($inShipment);
            CargoItem::whereIn('id', $inShipment)->update(['status' => 'pending']);
        }

        return response()->json([
            'success' => true,
            'removed' => count($inShipment),
            'skipped' => count($itemIds) - count($inShipment),
            'message' => count($inShipment) . ' item(s) marked as pending.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
public function bulkToggleItems(Request $request)
{
    try {
        $request->validate([
            'shipment_id' => 'required|exists:items_shipments,id',
            'item_ids'    => 'required|array|min:1',
            'item_ids.*'  => 'integer|exists:cargo_items,id',
        ]);

        $shipment = ItemsShipment::findOrFail($request->shipment_id);
        $itemIds  = $request->item_ids;

        // Which of these are already in the shipment?
        $alreadyIn = $shipment->cargoItems()
            ->whereIn('cargo_item_id', $itemIds)
            ->pluck('cargo_item_id')
            ->all();

        $toAttach = array_values(array_diff($itemIds, $alreadyIn));

        if (!empty($toAttach)) {
            // Attach new ones (avoid duplicate pivot rows)
            $shipment->cargoItems()->syncWithoutDetaching($toAttach);
            $this->syncItemsStatus($toAttach, $shipment->status);
        }

        return response()->json([
            'success' => true,
            'added'   => count($toAttach),
            'skipped' => count($alreadyIn),
            'message' => count($toAttach) . ' item(s) marked as packed.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
    public function toggleItem(Request $request)
    {
        try {
            $request->validate([
                'shipment_id' => 'required|exists:items_shipments,id',
                'item_id'     => 'required|exists:cargo_items,id'
            ]);

            $shipment = ItemsShipment::findOrFail($request->shipment_id);
            $itemId   = $request->item_id;

            $itemExists = $shipment->cargoItems()
                ->where('cargo_item_id', $itemId)
                ->exists();

            if ($itemExists) {
                // Remove from shipment → back to pending
                $shipment->cargoItems()->detach($itemId);
                CargoItem::where('id', $itemId)->update(['status' => 'pending']);

                return response()->json([
                    'success' => true,
                    'action'  => 'removed',
                    'message' => 'Item removed from shipment'
                ]);
            }

            // Add to shipment → follow shipment status
            $shipment->cargoItems()->attach($itemId);
            $this->syncItemsStatus([$itemId], $shipment->status);

            return response()->json([
                'success' => true,
                'action'  => 'added',
                'message' => 'Item added to shipment'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Sync a list of cargo item IDs to match a shipment's status.
     * Does nothing if the shipment status is not mapped.
     */
    private function syncItemsStatus(array $itemIds, ?string $shipmentStatus): void
    {
        if (empty($itemIds) || !$shipmentStatus) {
            return;
        }

        $targetStatus = self::SHIPMENT_TO_ITEM_STATUS[$shipmentStatus] ?? null;

        if (!$targetStatus) {
            return;
        }

        CargoItem::whereIn('id', $itemIds)->update(['status' => $targetStatus]);
    }
}