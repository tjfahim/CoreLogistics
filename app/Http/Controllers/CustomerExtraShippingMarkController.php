<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerExtraShippingMark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerExtraShippingMarkController extends Controller
{
    /**
     * Display a listing of the resource with search.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $marks = CustomerExtraShippingMark::with('customer')
            ->search($search)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get();

        return view('admin.customer_extra_shipping_marks.index', compact('marks', 'customers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $selectedCustomer = $request->input('customer_id');

        return view('admin.customer_extra_shipping_marks.create', compact('customers', 'selectedCustomer'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'   => 'required|exists:customers,id',
            'shipping_mark' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check duplicate (customer_id + shipping_mark)
        $exists = CustomerExtraShippingMark::where('customer_id', $request->customer_id)
            ->where('shipping_mark', $request->shipping_mark)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This shipping mark already exists for the selected customer.');
        }

        CustomerExtraShippingMark::create([
            'customer_id'   => $request->customer_id,
            'shipping_mark' => trim($request->shipping_mark),
        ]);

        // Optional: redirect back to customer if came from customer page
        if ($request->filled('redirect_to_customer')) {
            return redirect()->route('customers.edit', $request->customer_id)
                ->with('success', 'Shipping mark added successfully.');
        }

        return redirect()->route('customer-extra-shipping-marks.index')
            ->with('success', 'Shipping mark created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $mark = CustomerExtraShippingMark::with('customer')->findOrFail($id);

        return view('admin.customer_extra_shipping_marks.show', compact('mark'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $mark = CustomerExtraShippingMark::findOrFail($id);
        $customers = Customer::orderBy('name')->get();

        return view('admin.customer_extra_shipping_marks.edit', compact('mark', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $mark = CustomerExtraShippingMark::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'customer_id'   => 'required|exists:customers,id',
            'shipping_mark' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check duplicate excluding current
        $exists = CustomerExtraShippingMark::where('customer_id', $request->customer_id)
            ->where('shipping_mark', $request->shipping_mark)
            ->where('id', '!=', $mark->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This shipping mark already exists for the selected customer.');
        }

        $mark->update([
            'customer_id'   => $request->customer_id,
            'shipping_mark' => trim($request->shipping_mark),
        ]);

        return redirect()->route('customer-extra-shipping-marks.index')
            ->with('success', 'Shipping mark updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $mark = CustomerExtraShippingMark::findOrFail($id);
        $mark->delete();

        return redirect()->back()->with('success', 'Shipping mark deleted successfully.');
    }

    /**
     * Inline store via AJAX (from customer edit page).
     */
    public function inlineStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'   => 'required|exists:customers,id',
            'shipping_mark' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $exists = CustomerExtraShippingMark::where('customer_id', $request->customer_id)
            ->where('shipping_mark', $request->shipping_mark)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This shipping mark already exists for this customer.',
            ], 409);
        }

        $mark = CustomerExtraShippingMark::create([
            'customer_id'   => $request->customer_id,
            'shipping_mark' => trim($request->shipping_mark),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shipping mark added successfully.',
            'data'    => $mark,
        ]);
    }

    /**
     * Inline delete via AJAX.
     */
    public function inlineDestroy($id)
    {
        $mark = CustomerExtraShippingMark::find($id);

        if (!$mark) {
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }

        $mark->delete();

        return response()->json(['success' => true, 'message' => 'Deleted successfully.']);
    }
}