<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Agent;
use App\Models\Box;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /** LIST */
    public function index()
    {
        $shipments = Shipment::with(['agent', 'cnfAgent', 'boxes'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.shipments.index', compact('shipments'));
    }

    /** SHOW CREATE FORM */
    public function create()
    {
        $agents = Agent::all();
        $boxes  = Box::where('status', 'packed')->get();

        return view('admin.shipments.create', compact('agents', 'boxes'));
    }

   /** STORE SHIPMENT */
public function store(Request $request)
{
    $validated = $request->validate([
        'shipment_code' => 'required|unique:shipments',
        'total_weight'  => 'nullable|numeric',
        'box_ids'       => 'required|array',
        'box_ids.*'     => 'exists:boxes,id',
    ]);

    $shipment = Shipment::create($validated);

    // attach boxes
    $shipment->boxes()->sync($validated['box_ids']);

    // Update status of selected boxes
    Box::whereIn('id', $validated['box_ids'])->update(['status' => 'shipping_ongoing']);

    return redirect()->route('shipments.index')
        ->with('success', 'Shipment created successfully!');
}


    /** SHOW EDIT FORM */
   /** SHOW EDIT FORM */
public function edit($id)
{
    $shipment = Shipment::with('boxes')->findOrFail($id);

    // IDs of boxes already in the shipment
    $attachedBoxIds = $shipment->boxes->pluck('id')->toArray();

    // Load boxes with status 'packed' OR already attached
    $boxes = Box::where('status', 'packed')
                ->orWhereIn('id', $attachedBoxIds)
                ->get();

    $agents = Agent::all();

    return view('admin.shipments.edit', compact('shipment', 'agents', 'boxes'));
}


   /** UPDATE SHIPMENT */
public function update(Request $request, $id)
{
    $shipment = Shipment::findOrFail($id);

    $validated = $request->validate([
        'shipment_code' => 'required|unique:shipments,shipment_code,' . $shipment->id,
        'total_weight'  => 'nullable|numeric',
        'box_ids'       => 'required|array',
        'box_ids.*'     => 'exists:boxes,id',
    ]);

    // Get currently attached boxes
    $oldBoxIds = $shipment->boxes()->pluck('boxes.id')->toArray();

    // Update shipment
    $shipment->update($validated);

    // Sync boxes
    $shipment->boxes()->sync($validated['box_ids']);

    // Determine removed boxes and restore their status
    $removedBoxIds = array_diff($oldBoxIds, $validated['box_ids']);
    if(!empty($removedBoxIds)) {
        Box::whereIn('id', $removedBoxIds)->update(['status' => 'packed']);
    }

    // Set status for newly added boxes
    $newBoxIds = array_diff($validated['box_ids'], $oldBoxIds);
    if(!empty($newBoxIds)) {
        Box::whereIn('id', $newBoxIds)->update(['status' => 'shipping_ongoing']);
    }

    return redirect()->route('shipments.index')
        ->with('success', 'Shipment updated successfully!');
}

   /** DELETE SHIPMENT */
public function destroy($id)
{
    $shipment = Shipment::findOrFail($id);

    // Restore box status before detaching
    $boxIds = $shipment->boxes()->pluck('boxes.id')->toArray();
    Box::whereIn('id', $boxIds)->update(['status' => 'packed']);

    // detach boxes
    $shipment->boxes()->detach();

    // delete shipment
    $shipment->delete();

    return redirect()->route('shipments.index')
        ->with('success', 'Shipment deleted successfully!');
}
}
