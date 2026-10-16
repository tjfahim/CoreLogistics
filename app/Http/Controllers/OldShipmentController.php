<?php

namespace App\Http\Controllers;

use App\Models\OldCustomer;
use App\Models\OldShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OldShipmentController extends Controller
{
    public function index(Request $request)
    {
        $shipments = OldShipment::withCount('items')
            ->withSum('bills as bills_sum', 'total_price')
            ->withSum('items as items_sum', 'final_price')
            ->when($request->q, function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('shipping_mark', 'like', '%' . $request->q . '%');
                });
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.old_shipments.index', compact('shipments'));
    }

    public function create()
    {
        $customers = OldCustomer::orderBy('name')->get();
        return view('admin.old_shipments.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'items' => $this->cleanRows($request->input('items', []), ['name', 'weight', 'ctn_no', 'cbm', 'price_per_kg', 'final_price', 'old_customer_id']),
            'bills' => $this->cleanRows($request->input('bills', []), ['cost_type', 'total_price']),
        ]);

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) {
            $shipment = OldShipment::create($request->only('name', 'date', 'shipping_mark'));
            $this->syncChildren($shipment, $request);
        });

        return redirect()->route('old-shipments.index')
            ->with('success', 'Shipment created successfully.');
    }

    public function show(OldShipment $old_shipment)
    {
        $old_shipment->load(['items.customer', 'bills', 'payments.customer']);

        // per-customer summary inside this shipment
        $customerRows = $old_shipment->items
            ->whereNotNull('old_customer_id')
            ->groupBy('old_customer_id')
            ->map(function ($items) use ($old_shipment) {
                $customer = $items->first()->customer;
                $total    = $items->sum('final_price');
                $received = $old_shipment->payments
                    ->where('type', 'received')
                    ->where('old_customer_id', $customer->id)
                    ->sum('amount');

                return (object) [
                    'customer' => $customer,
                    'items'    => $items->count(),
                    'weight'   => $items->sum('weight'),
                    'total'    => $total,
                    'received' => $received,
                    'due'      => $total - $received,
                ];
            });

        $unassigned = $old_shipment->items->whereNull('old_customer_id');

        return view('admin.old_shipments.show', [
            'shipment'     => $old_shipment,
            'customerRows' => $customerRows,
            'unassigned'   => $unassigned,
        ]);
    }

    public function edit(OldShipment $old_shipment)
    {
        $old_shipment->load(['items', 'bills']);
        $customers = OldCustomer::orderBy('name')->get();

        return view('admin.old_shipments.edit', [
            'shipment'  => $old_shipment,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, OldShipment $old_shipment)
    {
        $request->merge([
            'items' => $this->cleanRows($request->input('items', []), ['name', 'weight', 'ctn_no', 'cbm', 'price_per_kg', 'final_price', 'old_customer_id']),
            'bills' => $this->cleanRows($request->input('bills', []), ['cost_type', 'total_price']),
        ]);

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $old_shipment) {
            $old_shipment->update($request->only('name', 'date', 'shipping_mark'));

            // rebuild child rows
            $old_shipment->items()->delete();
            $old_shipment->bills()->delete();
            $this->syncChildren($old_shipment, $request);
        });

        return redirect()->route('old-shipments.index')
            ->with('success', 'Shipment updated successfully.');
    }

    public function destroy(OldShipment $old_shipment)
    {
        // items, bills and payments are removed by cascade
        $old_shipment->delete();

        return redirect()->route('old-shipments.index')
            ->with('success', 'Shipment deleted successfully.');
    }

    // ------------------------------------------------------------------

    private function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'date'          => 'required|date',
            'shipping_mark' => 'nullable|string|max:255',

            'items'                    => 'nullable|array',
            'items.*.name'             => 'required|string|max:255',
            'items.*.old_customer_id'  => 'nullable|exists:old_customers,id',
            'items.*.weight'           => 'nullable|numeric|min:0',
            'items.*.ctn_no'           => 'nullable|string|max:100',
            'items.*.cbm'              => 'nullable|numeric|min:0',
            'items.*.remark'           => 'nullable|string|max:255',
            'items.*.shipping_mark'    => 'nullable|string|max:255',
            'items.*.price_per_kg'     => 'nullable|numeric|min:0',
            'items.*.final_price'      => 'nullable|numeric|min:0',

            'bills'               => 'nullable|array',
            'bills.*.cost_type'   => 'required|string|max:255',
            'bills.*.total_price' => 'required|numeric|min:0',
        ];
    }

    /** Drop rows where every watched field is empty (blank rows from the form). */
    private function cleanRows(array $rows, array $keys): array
    {
        return collect($rows)
            ->filter(function ($row) use ($keys) {
                foreach ($keys as $k) {
                    if (isset($row[$k]) && $row[$k] !== '' && $row[$k] !== null) {
                        return true;
                    }
                }
                return false;
            })
            ->values()
            ->all();
    }

    private function syncChildren(OldShipment $shipment, Request $request): void
    {
        foreach ($request->input('items', []) as $row) {
            $weight = (float) ($row['weight'] ?? 0);
            $rate   = (float) ($row['price_per_kg'] ?? 0);

            // final price: use entered value, otherwise weight x price/kg
            $final = (isset($row['final_price']) && $row['final_price'] !== '')
                ? (float) $row['final_price']
                : round($weight * $rate, 2);

            $shipment->items()->create([
                'old_customer_id' => $row['old_customer_id'] ?: null,
                'name'            => $row['name'],
                'weight'          => $weight,
                'ctn_no'          => $row['ctn_no'] ?? null,
                'cbm'             => (float) ($row['cbm'] ?? 0),
                'remark'          => $row['remark'] ?? null,
                'shipping_mark'   => $row['shipping_mark'] ?? null,
                'price_per_kg'    => $rate,
                'final_price'     => $final,
            ]);
        }

        foreach ($request->input('bills', []) as $row) {
            $shipment->bills()->create([
                'cost_type'   => $row['cost_type'],
                'total_price' => (float) $row['total_price'],
            ]);
        }
    }
}
