<?php

namespace App\Http\Controllers;

use App\Models\CargoItem;
use App\Models\Customer;
use App\Models\CustomerExtraShippingMark;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    // Show all customers
    public function index()
    {
        $customers = Customer::with('extraShippingMarks')->get();
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
            'is_partner' => 'nullable|boolean',
            'extra_shipping_marks' => 'nullable|array',
            'extra_shipping_marks.*' => 'nullable|string|max:255',
        ]);

        // Create customer
        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'customer_code' => $validated['customer_code'],
            'address' => $validated['address'] ?? null,
            'shipping_mark1' => $validated['shipping_mark1'] ?? null,
            'shipping_mark2' => $validated['shipping_mark2'] ?? null,
            'shipping_mark3' => $validated['shipping_mark3'] ?? null,
            'shipping_mark4' => $validated['shipping_mark4'] ?? null,
            'is_partner' => $request->boolean('is_partner'),
        ]);

        // Save extra shipping marks
        if (!empty($validated['extra_shipping_marks'])) {
            foreach ($validated['extra_shipping_marks'] as $mark) {
                if (!empty(trim($mark))) {
                    CustomerExtraShippingMark::create([
                        'customer_id' => $customer->id,
                        'shipping_mark' => trim($mark),
                    ]);
                }
            }
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    // Show edit page
    public function edit($id)
    {
        $customer = Customer::with('extraShippingMarks')->findOrFail($id);
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
            'is_partner' => 'nullable|boolean',
            'extra_shipping_marks' => 'nullable|array',
            'extra_shipping_marks.*' => 'nullable|string|max:255',
        ]);

        // Update customer
        $customer->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'customer_code' => $validated['customer_code'],
            'address' => $validated['address'] ?? null,
            'shipping_mark1' => $validated['shipping_mark1'] ?? null,
            'shipping_mark2' => $validated['shipping_mark2'] ?? null,
            'shipping_mark3' => $validated['shipping_mark3'] ?? null,
            'shipping_mark4' => $validated['shipping_mark4'] ?? null,
            'is_partner' => $request->boolean('is_partner'),
        ]);

        // Delete existing extra shipping marks
        $customer->extraShippingMarks()->delete();

        // Save new extra shipping marks
        if (!empty($validated['extra_shipping_marks'])) {
            foreach ($validated['extra_shipping_marks'] as $mark) {
                if (!empty(trim($mark))) {
                    CustomerExtraShippingMark::create([
                        'customer_id' => $customer->id,
                        'shipping_mark' => trim($mark),
                    ]);
                }
            }
        }

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    // Delete customer
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Delete extra shipping marks first
        $customer->extraShippingMarks()->delete();
        
        $customer->delete();

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
        
        $customers = Customer::with('extraShippingMarks')->get();
        
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
            ->whereNull('customer_id')
            ->whereNotNull('shipping_mark')
            ->get();
        
        // Get all customers with shipping marks (including extra marks)
        $customers = Customer::with('extraShippingMarks')
            ->where(function($query) {
                $query->whereNotNull('shipping_mark1')
                      ->orWhereNotNull('shipping_mark2')
                      ->orWhereNotNull('shipping_mark3')
                      ->orWhereNotNull('shipping_mark4')
                      ->orWhereHas('extraShippingMarks');
            })->get();
        
        \Log::info("Auto-assignment started for shipment {$itemshipment->id}");
        \Log::info("Found {$products->count()} unassigned products with shipping marks");
        \Log::info("Found {$customers->count()} customers with shipping marks");
        
        foreach ($customers as $customer) {
            $customerMarks = $customer->all_shipping_marks;
            \Log::info("Customer {$customer->id} ({$customer->name}) has shipping marks: " . implode(', ', $customerMarks));
        }
        
        foreach ($products as $product) {
            if (!is_null($product->customer_id)) {
                $skippedCount++;
                continue;
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
        
        return redirect()->back()->with('success', $message);
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
            $customerMarks = $customer->all_shipping_marks;
            
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
        $mark = trim($mark);
        $mark = preg_replace('/\s+/', ' ', $mark);
        $mark = preg_replace('/[\/\.\-_]+/', '/', $mark);
        $mark = preg_replace('/\s*\/\s*/', '/', $mark);
        $mark = strtoupper($mark);
        return $mark;
    }

    // Exact shipping mark matching with variations
    private function exactShippingMarkMatch($productMark, $customerMark)
    {
        if (empty($productMark) || empty($customerMark)) {
            return false;
        }
        
        if ($productMark === $customerMark) {
            return true;
        }
        
        $productParts = explode('/', $productMark);
        $customerParts = explode('/', $customerMark);
        
        $productParts = array_filter($productParts);
        $customerParts = array_filter($customerParts);
        
        if (count($productParts) === count($customerParts)) {
            $allPartsMatch = true;
            for ($i = 0; $i < count($productParts); $i++) {
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
        
        $productNoSpaces = preg_replace('/\s+/', '', $productMark);
        $customerNoSpaces = preg_replace('/\s+/', '', $customerMark);
        
        if ($productNoSpaces === $customerNoSpaces) {
            return true;
        }
        
        return false;
    }
}