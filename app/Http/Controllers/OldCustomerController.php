<?php

namespace App\Http\Controllers;

use App\Models\OldCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OldCustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = OldCustomer::withCount('items')
            ->withSum('items as bill_sum', 'final_price')
            ->withSum(['payments as received_sum' => fn ($q) => $q->where('type', 'received')], 'amount')
            ->when($request->q, function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('number', 'like', '%' . $request->q . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.old_customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.old_customers.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        OldCustomer::create($request->only('name', 'number'));

        return redirect()->route('old-customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(OldCustomer $old_customer)
    {
        $old_customer->load(['items.shipment', 'payments.shipment']);

        $itemsByShipment = $old_customer->items->groupBy('old_shipment_id');

        return view('admin.old_customers.show', [
            'customer'        => $old_customer,
            'itemsByShipment' => $itemsByShipment,
        ]);
    }

    public function edit(OldCustomer $old_customer)
    {
        return view('admin.old_customers.edit', ['customer' => $old_customer]);
    }

    public function update(Request $request, OldCustomer $old_customer)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $old_customer->update($request->only('name', 'number'));

        return redirect()->route('old-customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(OldCustomer $old_customer)
    {
        if ($old_customer->items()->exists() || $old_customer->payments()->exists()) {
            return redirect()->route('old-customers.index')
                ->with('error', 'This customer has items or payments. Remove them first.');
        }

        $old_customer->delete();

        return redirect()->route('old-customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
