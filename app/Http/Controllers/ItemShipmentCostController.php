<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemsShipment;
use App\Models\ItemsShipmentCost;
use App\Models\ItemsShipmentCostAttachment;
use App\Models\ItemsShipmentPayment;
use App\Models\CostType;
use App\Models\Agent;
use App\Models\CtnSequence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ItemShipmentCostController extends Controller
{
    // Show item shipment costs
    public function show($itemShipmentId)
    {
        $itemShipment = ItemsShipment::with([
            'costs.costType',
            'costs.agent',
            'costs.payments',
            'costs.attachments',   // NEW
            'cargoItems'
        ])->findOrFail($itemShipmentId);

        $costTypes = CostType::all();
        $agents = Agent::all();
        $currencies = ItemsShipmentCost::CURRENCIES; // NEW

        \Log::info('Showing costs for item shipment:', ['id' => $itemShipmentId, 'name' => $itemShipment->shipment_name]);

        return view('admin.itemshipments.costs', compact('itemShipment', 'costTypes', 'agents', 'currencies'));
    }

    // Add cost to item shipment (currency + exchange rate + required attachments)
    public function store(Request $request, $itemShipmentId)
    {
        $request->validate([
            'cost_type_id'      => 'required|exists:cost_types,id',
            'agent_id'          => 'nullable|exists:agents,id',
            'currency'          => ['required', Rule::in(array_keys(ItemsShipmentCost::CURRENCIES))],
            'exchange_rate_bdt' => 'required_unless:currency,bdt|nullable|numeric|gt:0',
            'rate_per_kg'       => 'nullable|numeric|min:0',
            'total_weight'      => 'nullable|numeric|min:0',
            'amount'            => 'required|numeric|min:0',
            'description'       => 'nullable|string',
            'cost_date'         => 'required|date',
            'attachments'       => 'required|array|min:1|max:10',
            'attachments.*'     => 'file|mimes:jpg,jpeg,png,webp,pdf|max:10240', // 10 MB each
        ], [
            'attachments.required'      => 'Please attach at least one image or PDF.',
            'attachments.min'           => 'Please attach at least one image or PDF.',
            'attachments.*.mimes'       => 'Attachments must be JPG, PNG, WEBP or PDF files.',
            'attachments.*.max'         => 'Each attachment must be 10 MB or smaller.',
            'exchange_rate_bdt.required_unless' => 'Exchange rate (BDT) is required when currency is not BDT.',
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $itemShipmentId, &$storedPaths) {
                // Amount in the selected currency
                $amount = (float) $request->amount;
                if ($request->rate_per_kg && $request->total_weight) {
                    $amount = (float) $request->rate_per_kg * (float) $request->total_weight;
                }

                // Never trust the browser for the final figure
                $rate     = $request->currency === 'bdt' ? 1 : (float) $request->exchange_rate_bdt;
                $finalBdt = round($amount * $rate, 2);

                $cost = ItemsShipmentCost::create([
                    'items_shipment_id' => $itemShipmentId,
                    'cost_type_id'      => $request->cost_type_id,
                    'agent_id'          => $request->agent_id,
                    'currency'          => $request->currency,
                    'exchange_rate_bdt' => $rate,
                    'description'       => $request->description,
                    'rate_per_kg'       => $request->rate_per_kg,
                    'total_weight'      => $request->total_weight,
                    'amount'            => $amount,
                    'final_bdt_cost'    => $finalBdt,
                    'paid_amount'       => 0,
                    'due_amount'        => $finalBdt,
                    'cost_date'         => $request->cost_date,
                    'status'            => 'pending'
                ]);

                $this->saveAttachmentFiles($cost, $request->file('attachments'), $storedPaths);
            });

            return redirect()->back()->with('success', 'Cost added successfully.');
        } catch (\Exception $e) {
            // DB failed -> remove files already written to disk
            Storage::disk('public')->delete($storedPaths);
            return redirect()->back()->withInput()->with('error', 'Error adding cost: ' . $e->getMessage());
        }
    }

    // Upload more attachments to an existing cost
    public function addAttachments(Request $request, $costId)
    {
        $request->validate([
            'attachments'   => 'required|array|min:1|max:10',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ], [
            'attachments.required' => 'Please choose at least one image or PDF.',
            'attachments.*.mimes'  => 'Attachments must be JPG, PNG, WEBP or PDF files.',
            'attachments.*.max'    => 'Each attachment must be 10 MB or smaller.',
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $costId, &$storedPaths) {
                $cost = ItemsShipmentCost::findOrFail($costId);
                $this->saveAttachmentFiles($cost, $request->file('attachments'), $storedPaths);
            });

            return redirect()->back()->with('success', 'Attachments uploaded successfully.');
        } catch (\Exception $e) {
            Storage::disk('public')->delete($storedPaths);
            return redirect()->back()->with('error', 'Error uploading attachments: ' . $e->getMessage());
        }
    }

    // Delete a single attachment (a cost must always keep at least one)
    public function destroyAttachment($attachmentId)
    {
        try {
            $attachment = ItemsShipmentCostAttachment::findOrFail($attachmentId);

            if ($attachment->cost->attachments()->count() <= 1) {
                return redirect()->back()->with('error', 'A cost must keep at least one attachment. Upload a new file before deleting this one.');
            }

            $path = $attachment->file_path;
            $attachment->delete();
            Storage::disk('public')->delete($path);

            return redirect()->back()->with('success', 'Attachment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting attachment: ' . $e->getMessage());
        }
    }

    // Helper: store uploaded files + create attachment rows
    private function saveAttachmentFiles(ItemsShipmentCost $cost, array $files, array &$storedPaths)
    {
        foreach ($files as $file) {
            // read metadata first, before the temp file is moved
            $originalName = $file->getClientOriginalName();
            $mime         = $file->getMimeType();
            $size         = $file->getSize();

            $path = $file->store("shipment-costs/{$cost->id}", 'public');
            $storedPaths[] = $path;

            $cost->attachments()->create([
                'file_path'     => $path,
                'original_name' => $originalName,
                'mime_type'     => $mime,
                'file_size'     => $size,
            ]);
        }
    }

    // Add payment for a cost (payments are in BDT, validated against due_amount)
    public function addPayment(Request $request, $costId)
    {
        try {
            $cur = \App\Support\PaymentCurrency::apply($request);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,mobile_banking,cheque,bank_transfer',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string',
            'paid_by' => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($request, $costId, $cur) {
                $itemShipmentCost = ItemsShipmentCost::lockForUpdate()->findOrFail($costId);

                // Check if payment amount exceeds due amount
                if ($request->amount > $itemShipmentCost->due_amount) {
                    throw new \Exception('Payment amount cannot exceed due amount.');
                }

                // Create payment
                ItemsShipmentPayment::create([
                    'items_shipment_cost_id' => $costId,
                    'amount' => $request->amount,
                    'currency' => $cur['currency'],
                    'foreign_amount' => $cur['foreign_amount'],
                    'exchange_rate' => $cur['exchange_rate'],
                    'payment_date' => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'reference_no' => $request->reference_no,
                    'notes' => $request->notes,
                    'paid_by' => $request->paid_by ?: optional(Auth::user())->name,
                    'user_id' => Auth::id(),
                ]);

                // Update cost status using the model method
                $itemShipmentCost->updatePaymentStatus();
            });

            return redirect()->back()->with('success', 'Payment added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error adding payment: ' . $e->getMessage());
        }
    }

    // Delete a cost (also removes its payments, attachment rows and files)
    public function destroyCost($costId)
    {
        try {
            $itemShipmentCost = ItemsShipmentCost::with('attachments')->findOrFail($costId);
            $itemShipmentId = $itemShipmentCost->items_shipment_id;
            $filePaths = $itemShipmentCost->attachments->pluck('file_path')->all();

            DB::transaction(function () use ($itemShipmentCost) {
                $itemShipmentCost->payments()->delete();
                $itemShipmentCost->attachments()->delete();
                $itemShipmentCost->delete();
            });

            // only after the DB delete succeeded
            Storage::disk('public')->delete($filePaths);
            Storage::disk('public')->deleteDirectory("shipment-costs/{$costId}");

            return redirect()->route('itemshipments.costs.show', $itemShipmentId)
                ->with('success', 'Cost deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting cost: ' . $e->getMessage());
        }
    }

    // Delete a payment
    public function destroyPayment($paymentId)
    {
        try {
            $payment = ItemsShipmentPayment::findOrFail($paymentId);
            $costId = $payment->items_shipment_cost_id;
            $cost = ItemsShipmentCost::find($costId);
            $itemShipmentId = $cost->items_shipment_id;

            DB::transaction(function () use ($payment, $cost) {
                $payment->delete();
                // Update cost status after payment deletion
                $cost->updatePaymentStatus();
            });

            return redirect()->route('itemshipments.costs.show', $itemShipmentId)
                ->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }

    // Get financial summary (for AJAX requests)
    public function getFinancialSummary($itemShipmentId)
    {
        $itemShipment = ItemsShipment::findOrFail($itemShipmentId);

        return response()->json([
            'total_cost' => $itemShipment->total_cost,
            'total_paid' => $itemShipment->total_paid,
            'total_due' => $itemShipment->total_due,
            'pending_costs' => $itemShipment->pending_costs,
            'partial_costs' => $itemShipment->partial_costs,
            'paid_costs' => $itemShipment->paid_costs,
            'total_weight' => $itemShipment->total_weight,
            'total_cbm' => $itemShipment->total_cbm,
            'total_items' => $itemShipment->total_items,
        ]);
    }

    public function pricepage(ItemsShipment $itemshipment)
    {
        $itemshipment->load('cargoItems');
        return view('admin.itemshipments.price', compact('itemshipment'));
    }

    public function updatePrices(Request $request, ItemsShipment $itemshipment)
    {
        $request->validate([
            'prices' => 'required|array',
            'prices.*' => 'nullable|numeric',
        ]);

        try {
            DB::transaction(function () use ($request, $itemshipment) {
                foreach ($request->prices as $itemId => $pricePerKg) {
                    $item = $itemshipment->cargoItems()->where('cargo_items.id', $itemId)->first();

                    if ($item && $pricePerKg !== null) {
                        $totalPrice = $pricePerKg * ($item->billing_weight ?? 0);

                        $item->update([
                            'price_per_kg' => $pricePerKg,
                            'price' => $totalPrice
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Prices updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating prices: ' . $e->getMessage());
        }
    }

    public function ctn_update(ItemsShipment $itemshipment)
    {
        $itemshipment->load('cargoItems');
        $groupedItems = $itemshipment->cargoItems->groupBy('printed_ctn_no');

        $ctnSuggestion = CtnSequence::suggestion();

        return view('admin.itemshipments.printed_ctn', compact('itemshipment', 'groupedItems', 'ctnSuggestion'));
    }

    /** Final weight page: one row per item, grouped by printed CTN, with a filter. */
    public function finalWeightPage(Request $request, ItemsShipment $itemshipment)
    {
        $filter = $request->input('filter', 'all'); // all | missing | set
        $search = trim((string) $request->input('search'));

        $items = $itemshipment->cargoItems()->get()
            ->when($filter === 'missing', fn ($c) => $c->filter(fn ($i) => $i->final_weight_kg === null))
            ->when($filter === 'set', fn ($c) => $c->filter(fn ($i) => $i->final_weight_kg !== null))
            ->when($search !== '', fn ($c) => $c->filter(fn ($i) => stripos($i->printed_ctn_no . ' ' . $i->tracking_no . ' ' . $i->goods_name_en, $search) !== false));

        $groupedItems = $items->groupBy(fn ($i) => $i->printed_ctn_no ?: '—');

        $all = $itemshipment->cargoItems()->get();
        $stats = [
            'items'    => $all->count(),
            'set'      => $all->whereNotNull('final_weight_kg')->count(),
            'original' => round($all->sum('weight_kg'), 2),
            'final'    => round($all->sum('billing_weight'), 2),
        ];

        return view('admin.itemshipments.final_weight', compact('itemshipment', 'groupedItems', 'filter', 'search', 'stats'));
    }

    public function updateFinalWeight(Request $request, ItemsShipment $itemshipment)
    {
        $request->validate([
            'final_weight'   => 'nullable|array',
            'final_weight.*' => 'nullable|numeric|min:0|max:100000',
            'box_weight'     => 'nullable|array',
            'box_weight.*'   => 'nullable|numeric|min:0|max:100000',
        ]);

        $changed = 0;
        DB::transaction(function () use ($request, $itemshipment, &$changed) {
            $items = $itemshipment->cargoItems()->get()->keyBy('id');
            $new = [];

            foreach ((array) $request->input('final_weight', []) as $id => $w) {
                if ($items->has($id)) {
                    $new[$id] = ($w === null || $w === '') ? null : round((float) $w, 3);
                }
            }

            // "whole box" weight: split equally between the items of that printed CTN
            foreach ((array) $request->input('box_weight', []) as $ctn => $w) {
                if ($w === null || $w === '') continue;
                $group = $items->filter(fn ($i) => (string) $i->printed_ctn_no === (string) $ctn);
                if ($group->isEmpty()) continue;
                $each = round((float) $w / $group->count(), 3);
                foreach ($group as $i) $new[$i->id] = $each;
            }

            foreach ($new as $id => $fw) {
                $item = $items[$id];
                $old = $item->final_weight_kg !== null ? (float) $item->final_weight_kg : null;
                if ($old === $fw) continue;

                $item->final_weight_kg = $fw;
                // price per kg is set -> keep the bill in step with the weight that is billed
                if ($item->price_per_kg) {
                    $item->price = round((float) $item->price_per_kg * $item->billing_weight, 2);
                }
                $item->save();
                $changed++;
            }
        });

        return redirect()->route('itemshipments.final-weight', $itemshipment->id)
            ->with('success', $changed . ' item(s) updated. Bills and reports now use the final weight.');
    }

    public function updateCtnNumbers(Request $request, ItemsShipment $itemshipment)
    {
        $request->validate([
            'printed_ctn' => 'required|array',
            'printed_ctn.*' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request, $itemshipment) {

                foreach ($request->printed_ctn as $itemId => $ctnNo) {

                    $item = $itemshipment->cargoItems()
                        ->where('cargo_items.id', $itemId)
                        ->first();

                    if ($item) {

                        // Update CTN number
                        $item->printed_ctn_no = $ctnNo;

                        if (in_array($item->status, ['pending', 'packed'])) {
                            $item->status = 'ready_to_ship';
                        }

                        $item->save();
                    }
                }
            });

            $lastCtn = collect($request->printed_ctn)->filter(fn ($v) => trim((string) $v) !== '')->last();
            CtnSequence::record($lastCtn);

            return redirect()->back()->with('success', 'CTN numbers updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * One-click auto assign: format like "AB-100" => groups get AB-100, AB-101, ...
     * Items that already share the same printed CTN stay together (same new number).
     */
    public function autoAssignCtn(Request $request, ItemsShipment $itemshipment)
    {
        $request->validate(['ctn_format' => 'required|string|max:100']);

        $parsed = CtnSequence::parse($request->ctn_format);
        if (!$parsed) {
            return back()->with('error', 'Format must end with a number, e.g. AB-100');
        }
        [$prefix, $number, $pad] = $parsed;

        try {
            $last = null;
            DB::transaction(function () use ($itemshipment, $prefix, &$number, $pad, &$last) {
                $items = $itemshipment->cargoItems()->orderBy('cargo_items.id')->get();

                // group by current CTN (blank CTN items are each their own group)
                $groups = [];
                foreach ($items as $item) {
                    $key = trim((string) $item->printed_ctn_no) !== ''
                        ? 'c:' . $item->printed_ctn_no
                        : 'i:' . $item->id;
                    $groups[$key][] = $item;
                }

                foreach ($groups as $groupItems) {
                    $ctn = $prefix . str_pad((string) $number, $pad, '0', STR_PAD_LEFT);
                    foreach ($groupItems as $item) {
                        $item->printed_ctn_no = $ctn;
                        if (in_array($item->status, ['pending', 'packed'])) {
                            $item->status = 'ready_to_ship';
                        }
                        $item->save();
                    }
                    $last = $ctn;
                    $number++;
                }
            });

            CtnSequence::record($last);

            return back()->with('success', 'CTN auto-assigned. Last CTN: ' . $last);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to auto assign: ' . $e->getMessage());
        }
    }
}
