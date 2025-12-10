<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
     public function index()
    {
        $products = Product::with('customer')->get();
        return view('admin.products.index', compact('products'));
    }

    // Show create form
    public function create()
    {
        $customers = Customer::all();
        return view('admin.products.create', compact('customers'));
    }

    // Store product
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'weight' => 'required|numeric',
            'price' => 'numeric',
            'customer_id' => 'required|exists:customers,id',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    // Show edit form
    public function edit(Product $product)
    {
        $customers = Customer::all();
        return view('admin.products.edit', compact('product', 'customers'));
    }
public function show(Product $product)
{
    $product->load('customer'); // eager load customer
    return view('admin.products.show', compact('product'));
}
    // Update product
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'weight' => 'required|numeric',
            'price' => 'numeric',
            'customer_id' => 'required|exists:customers,id',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    // Delete product
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }
}
