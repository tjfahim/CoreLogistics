<?php

namespace App\Http\Controllers;

use App\Models\CargoItem;
use App\Models\ItemReturnHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemReturnController extends Controller
{
    /**
     * Show return form for selected items (POST → renders modal content via AJAX or a page).
     */
    public function create(Request $request)
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->input('item_ids', ''))));

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No items selected.'], 422);
        }

        $items = CargoItem::with('itemshipments')->whereIn('id', $ids)->get();

        if ($items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Items not found.'], 404);
        }

        $html = view('admin.cargo_items.partials.return_modal_content', compact('items'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
            'count'   => $items->count(),
        ]);
    }

    /**
     * Store return history and reset items to pending.
     */
    public function store(Request $request)
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

        DB::transaction(function () use ($ids, $data) {
            $items = CargoItem::with('itemshipments')->whereIn('id', $ids)->get();

            foreach ($items as $item) {
                // Save history BEFORE mutating
                ItemReturnHistory::create([
                    'cargo_item_id'    => $item->id,
                    'items_shipment_id'=> $item->itemshipments->first()->id ?? null,
                    'note'             => $data['note'] ?? null,
                    'return_date'      => $data['return_date'],
                    'previous_status'  => $item->status,
                    'returned_by'      => auth()->id(),
                ]);

                // Detach from all shipments
                $item->itemshipments()->detach();

                // Reset to pending
                $item->update(['status' => 'pending']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' item(s) returned to pending.',
        ]);
    }

    /**
     * Show history page (optional).
     */
    public function index(Request $request)
    {
        $histories = ItemReturnHistory::with(['cargoItem', 'shipment', 'returnedBy'])
            ->latest('return_date')
            ->paginate(50);

        return view('admin.cargo_items.return_history', compact('histories'));
    }
}