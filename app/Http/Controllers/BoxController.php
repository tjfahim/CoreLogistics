<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Product;
use Illuminate\Http\Request;

class BoxController extends Controller
{
  public function index()
    {
        $boxes = Box::with('products')->get();
        return view('admin.boxes.index', compact('boxes'));
    }

    // Show create box form
    public function create()
    {
        $products = Product::where('weight', '>', 0)->get(); // Only products with remaining weight
        return view('admin.boxes.create', compact('products'));
    }

  // Store box and split products automatically
public function store(Request $request)
{
    $validated = $request->validate([
        'box_code' => 'required|string|unique:boxes,box_code',
        'max_box_weight' => 'required|numeric|min:0.01',
        'products' => 'array',
        'products.*.id' => 'exists:products,id',
        'products.*.weight' => 'nullable|numeric|min:0.01',
    ]);

    $maxBoxWeight = $validated['max_box_weight'];

    // Filter products with non-zero weight input or use full remaining weight
    $inputProducts = collect($validated['products']);

    $products = Product::whereIn('id', $inputProducts->pluck('id'))->get();

    $boxesCreated = [];

    while($products->sum('weight') > 0) {
        $box = Box::create([
            'box_code' => $validated['box_code'].'-'.(count($boxesCreated)+1),
            'total_weight' => 0,
            'status' => 'packed'
        ]);

        $boxWeight = 0;

        foreach($products as $product) {
            if($product->weight <= 0) continue;

            $remainingBoxCapacity = $maxBoxWeight - $boxWeight;
            if($remainingBoxCapacity <= 0) break;

            // Use input weight if provided, otherwise use full remaining product weight
            $inputWeight = $inputProducts->firstWhere('id', $product->id)['weight'] ?? $product->weight;
            $weightToAdd = min($product->weight, $remainingBoxCapacity, $inputWeight);

            if($weightToAdd <= 0) continue;

            // Attach to box
            $box->products()->attach($product->id, ['weight_in_box' => $weightToAdd]);

            // Reduce product weight
            $product->weight -= $weightToAdd;
            $product->status = $product->weight > 0 ? 'pending' : 'packed';
            $product->save();

            $boxWeight += $weightToAdd;
        }

        $box->total_weight = $boxWeight;
        $box->save();

        $boxesCreated[] = $box;

        
    }

    return redirect()->route('boxes.index')->with('success', 'Boxes created and products allocated successfully!');
}


    // Show edit box form
    public function edit(Box $box)
    {
        $products = Product::where('weight', '>', 0)->orWhereIn('id', $box->products->pluck('id'))->get();
        return view('admin.boxes.edit', compact('box', 'products'));
    }

    // Update box (optional: update status only)
    public function update(Request $request, Box $box)
    {
        $validated = $request->validate([
            'status' => 'required|in:packed,shipping_ongoing,arrived_bd,ready_for_delivery,delivered',
        ]);

        $box->update($validated);

        return redirect()->route('boxes.index')->with('success', 'Box status updated successfully');
    }

    // Delete box
    public function destroy(Box $box)
    {
        // Restore product weights
        foreach ($box->products as $product) {
            $product->weight += $product->pivot->weight_in_box;
            $product->status = 'pending'; // Optional: revert status
            $product->save();
        }

        // Detach all products from box
        $box->products()->detach();

        // Delete the box
        $box->delete();

        return redirect()->route('boxes.index')->with('success', 'Box deleted successfully and product weights restored!');
    }
}
