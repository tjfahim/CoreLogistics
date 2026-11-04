<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerExtraBill;
use App\Models\ItemsShipment;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/** Extra bills of customers inside a shipment (fixed amount, or rate per kg of the customer's final weight). */
class ExtraBillController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('view-extra-bills'), 403);

        $shipmentId = $request->integer('shipment_id') ?: null;
        $customerId = $request->integer('customer_id') ?: null;

        $bills = CustomerExtraBill::with(['customer:id,name,customer_code', 'itemshipment:id,shipment_name'])
            ->when($shipmentId, fn ($q) => $q->where('items_shipment_id', $shipmentId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->orderByDesc('id')->get();

        // final kg of each customer in each shipment (per-kg bills use it)
        $kg = app(AccountingService::class)
            ->customerShipmentRows(array_filter(['customer_ids' => $customerId ? [$customerId] : [], 'shipment_ids' => $shipmentId ? [$shipmentId] : []]))
            ->keyBy(fn ($r) => $r->customer_id . '-' . $r->shipment_id);

        $bills->each(function ($b) use ($kg) {
            $w = (float) ($kg->get($b->customer_id . '-' . $b->items_shipment_id)->weight ?? 0);
            $b->kg = $w;
            $b->effective = $b->amountFor($w);
        });

        $shipments = ItemsShipment::orderByDesc('id')->get(['id', 'shipment_name']);
        $customers = Customer::orderBy('name')->get(['id', 'name', 'customer_code']);

        return view('admin.extra_bills.index', compact('bills', 'shipments', 'customers', 'shipmentId', 'customerId'));
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('manage-extra-bills'), 403);

        $data = $request->validate([
            'items_shipment_id' => 'required|exists:items_shipments,id',
            'customer_id'       => 'required|exists:customers,id',
            'title'             => 'required|string|max:255',
            'bill_type'         => 'required|in:fixed,per_kg',
            'amount'            => 'required_if:bill_type,fixed|nullable|numeric|min:0',
            'rate_per_kg'       => 'required_if:bill_type,per_kg|nullable|numeric|min:0',
            'bill_date'         => 'nullable|date',
            'note'              => 'nullable|string|max:1000',
        ]);

        CustomerExtraBill::create([
            'items_shipment_id' => $data['items_shipment_id'],
            'customer_id'       => $data['customer_id'],
            'title'             => $data['title'],
            'bill_type'         => $data['bill_type'],
            'amount'            => $data['bill_type'] === 'fixed' ? round((float) $data['amount'], 2) : 0,
            'rate_per_kg'       => $data['bill_type'] === 'per_kg' ? round((float) $data['rate_per_kg'], 2) : null,
            'bill_date'         => $data['bill_date'] ?? now()->toDateString(),
            'note'              => $data['note'] ?? null,
            'user_id'           => Auth::id(),
        ]);

        return redirect()->route('extra-bills.index', array_filter([
            'shipment_id' => $request->input('filter_shipment_id'),
            'customer_id' => $request->input('filter_customer_id'),
        ]))->with('success', 'Extra bill added.');
    }

    public function destroy(CustomerExtraBill $extraBill)
    {
        abort_unless(Gate::allows('manage-extra-bills'), 403);
        $extraBill->delete();
        return back()->with('success', 'Extra bill deleted.');
    }
}
