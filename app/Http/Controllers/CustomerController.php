<?php

namespace App\Http\Controllers;

use App\Models\CargoItem;
use App\Models\Customer;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    // Show all customers
    public function index()
    {
        $customers = Customer::all();
        return view('customers.index', compact('customers'));
    }

    // Show create form
    public function create()
    {
        return view('customers.create');
    }

    // Store customer
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required',
            'customer_code' => 'required|unique:customers,customer_code',
            'address' => 'nullable',
            'shipping_mark1' => 'nullable',
            'shipping_mark2' => 'nullable',
            'shipping_mark3' => 'nullable',
            'shipping_mark4' => 'nullable',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    // Show edit page
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    // Update customer
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:customers,email,' . $id,
            'phone' => 'required',
            'customer_code' => 'required|unique:customers,customer_code,' . $id,
            'address' => 'nullable',
            'shipping_mark1' => 'nullable',
            'shipping_mark2' => 'nullable',
            'shipping_mark3' => 'nullable',
            'shipping_mark4' => 'nullable',
        ]);
        $customer->update($validated);
                return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    // Delete customer
    public function destroy($id)
    {
        Customer::findOrFail($id)->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    

    // Show product assignment for specific shipment
public function showProductAssignment(ItemsShipment $itemshipment)
{
    $itemshipment->load('cargoItems.customer');
    
    $products = $itemshipment->cargoItems()
        ->with('customer')
        ->latest()
        ->paginate(5000); 
    
    $customers = Customer::all();
    
    return view('admin.itemshipments.product-assignment', compact('products', 'customers', 'itemshipment'));
}

// Assign products to customers for specific shipment
public function assignProducts(Request $request, ItemsShipment $itemshipment)
{
    $request->validate([
        'assignments' => 'required|array',
        'assignments.*.customer_id' => 'nullable|exists:customers,id',
    ]);

    try {
        DB::transaction(function () use ($request, $itemshipment) {
            foreach ($request->assignments as $productId => $assignment) {
                $customerId = $assignment['customer_id'] ?? null;
                
                // Ensure the product belongs to this shipment
                $itemshipment->cargoItems()
                    ->where('cargo_items.id', $productId)
                    ->update([
                        'customer_id' => $customerId
                    ]);
            }
        });

        return redirect()->back()
            ->with('success', 'Products assigned to customers successfully!');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error assigning products: ' . $e->getMessage());
    }
}

// Bulk assign products to a single customer for specific shipment
public function bulkAssign(Request $request, ItemsShipment $itemshipment)
{
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'product_ids' => 'required|array',
        'product_ids.*' => 'exists:cargo_items,id',
    ]);

    try {
        // Ensure products belong to this shipment
        $itemshipment->cargoItems()
            ->whereIn('cargo_items.id', $request->product_ids)
            ->update(['customer_id' => $request->customer_id]);

        return redirect()->back()
            ->with('success', count($request->product_ids) . ' products assigned to customer successfully!');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error in bulk assignment: ' . $e->getMessage());
    }
}
public function autoAssignByShippingMark(ItemsShipment $itemshipment)
{
    $assignedCount = 0;
    $skippedCount = 0;
    $noMatchCount = 0;
    
    // Get all products in this shipment that don't have customers assigned
    $products = $itemshipment->cargoItems()
        ->whereNull('customer_id') // Only unassigned products
        ->whereNotNull('shipping_mark') // Only products with shipping marks
        ->get();
    
    // Get all customers with shipping marks
    $customers = Customer::where(function($query) {
        $query->whereNotNull('shipping_mark1')
              ->orWhereNotNull('shipping_mark2')
              ->orWhereNotNull('shipping_mark3')
              ->orWhereNotNull('shipping_mark4');
    })->get();
    
    // Log for debugging - including customer shipping marks
    \Log::info("Auto-assignment started for shipment {$itemshipment->id}");
    \Log::info("Found {$products->count()} unassigned products with shipping marks");
    \Log::info("Found {$customers->count()} customers with shipping marks");
    
    foreach ($customers as $customer) {
        $customerMarks = [
            $customer->shipping_mark1,
            $customer->shipping_mark2,
            $customer->shipping_mark3,
            $customer->shipping_mark4
        ];
        $customerMarks = array_filter($customerMarks, function($mark) {
            return !empty(trim($mark));
        });
        \Log::info("Customer {$customer->id} ({$customer->name}) has shipping marks: " . implode(', ', $customerMarks));
    }
    
    foreach ($products as $product) {
        // Double check if product already has a customer (safety check)
        if (!is_null($product->customer_id)) {
            $skippedCount++;
            \Log::info("Skipped product {$product->id} - already assigned to customer {$product->customer_id}");
            continue; // Skip if already assigned
        }
        
        $matchedCustomer = $this->findCustomerByShippingMark($product->shipping_mark, $customers);
        
        if ($matchedCustomer) {
            $product->update(['customer_id' => $matchedCustomer->id]);
            $assignedCount++;
            
            \Log::info("Assigned product {$product->id} ({$product->shipping_mark}) to customer {$matchedCustomer->id} ({$matchedCustomer->name})");
        } else {
            $noMatchCount++;
            \Log::info("No customer found for product {$product->id} with shipping mark: {$product->shipping_mark}");
        }
    }
    
    $message = "Auto-assignment completed! ";
    $message .= "Assigned: {$assignedCount} products, ";
    $message .= "Skipped: {$skippedCount} (already assigned), ";
    $message .= "No match: {$noMatchCount} products";
    
    return redirect()->back()
        ->with('success', $message);
}

// Helper function to find customer by shipping mark
private function findCustomerByShippingMark($productMark, $customers)
{
    $productMark = trim($productMark);
    if (empty($productMark)) {
        return null;
    }
    
    $productMark = $this->normalizeShippingMark($productMark);
    
    foreach ($customers as $customer) {
        // Check all four shipping mark fields
        $customerMarks = [
            $customer->shipping_mark1,
            $customer->shipping_mark2,
            $customer->shipping_mark3,
            $customer->shipping_mark4
        ];
        
        foreach ($customerMarks as $customerMark) {
            if (!empty($customerMark)) {
                $normalizedCustomerMark = $this->normalizeShippingMark($customerMark);
                if ($this->exactShippingMarkMatch($productMark, $normalizedCustomerMark)) {
                    return $customer;
                }
            }
        }
    }
    
    return null;
}

// Normalize shipping mark for consistent comparison
private function normalizeShippingMark($mark)
{
    // Remove extra spaces
    $mark = trim($mark);
    
    // Replace multiple spaces with single space
    $mark = preg_replace('/\s+/', ' ', $mark);
    
    // Standardize separators (replace common separators with standard one)
    $mark = preg_replace('/[\/\.\-_]+/', '/', $mark);
    
    // Remove spaces around separators
    $mark = preg_replace('/\s*\/\s*/', '/', $mark);
    
    // Convert to uppercase for case-insensitive comparison
    $mark = strtoupper($mark);
    
    return $mark;
}

// Exact shipping mark matching with variations
private function exactShippingMarkMatch($productMark, $customerMark)
{
    if (empty($productMark) || empty($customerMark)) {
        return false;
    }
    
    // 1. Exact match after normalization
    if ($productMark === $customerMark) {
        return true;
    }
    
    // 2. Split and check if all parts match (but allow for minor differences)
    $productParts = explode('/', $productMark);
    $customerParts = explode('/', $customerMark);
    
    // Remove empty parts
    $productParts = array_filter($productParts);
    $customerParts = array_filter($customerParts);
    
    // If both have same number of parts, compare part by part
    if (count($productParts) === count($customerParts)) {
        $allPartsMatch = true;
        for ($i = 0; $i < count($productParts); $i++) {
            // Allow for minor variations like "AKS A4" vs "AKSA4" (remove spaces)
            $prodPart = preg_replace('/\s+/', '', $productParts[$i]);
            $custPart = preg_replace('/\s+/', '', $customerParts[$i]);
            
            if ($prodPart !== $custPart) {
                $allPartsMatch = false;
                break;
            }
        }
        if ($allPartsMatch) {
            return true;
        }
    }
    
    // 3. Compare as strings ignoring spaces
    $productNoSpaces = preg_replace('/\s+/', '', $productMark);
    $customerNoSpaces = preg_replace('/\s+/', '', $customerMark);
    
    if ($productNoSpaces === $customerNoSpaces) {
        return true;
    }
    
    return false;
}
}