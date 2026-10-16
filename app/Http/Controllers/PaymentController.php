<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\ItemsShipment;
use App\Services\CustomerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function __construct(private CustomerLedgerService $ledger)
    {
    }

    /**
     * OLD entry point (billing page links here). Now it just opens the
     * new receive-payment form with this shipment pre-filled.
     */
    public function create(Customer $customer, ItemsShipment $itemshipment)
    {
        return redirect()->route('payments.receive', [
            'customer' => $customer->id,
            'shipment' => $itemshipment->id,
            'return'   => 'billing',
        ]);
    }

    /**
     * Receive-payment form: one amount, split manually across the customer's due shipments.
     * Optional ?shipment=ID pre-fills that shipment's full due.
     */
    public function receive(Request $request, Customer $customer)
    {
        $bills    = $this->ledger->shipmentBills($customer);
        $dueBills = $bills->filter(fn ($b) => $b['due'] > 0)->values();
        $totals   = $this->ledger->totals($bills);

        $preselect = (int) $request->query('shipment');
        $return    = $request->query('return');

        return view('admin.payments.receive', compact('customer', 'dueBills', 'totals', 'preselect', 'return'));
    }

    /**
     * Save one receipt + its allocations (all-or-nothing).
     */
    public function storeReceipt(Request $request, Customer $customer)
    {
        abort_unless(Gate::allows('add-payment'), 403);

        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,card',
            'payment_date'   => 'required|date',
            'reference_no'   => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'allocations'    => 'required|array',
            'allocations.*'  => 'nullable|numeric|min:0',
        ]);

        $allocations = collect($request->input('allocations', []))
            ->map(fn ($v) => round((float) $v, 2))
            ->filter(fn ($v) => $v > 0);

        if ($allocations->isEmpty()) {
            return redirect()->back()->withInput()
                ->with('error', 'Allocate the payment to at least one shipment.');
        }

        try {
            $payment = DB::transaction(function () use ($request, $customer, $allocations) {
                // serialize concurrent payments for the same customer
                Customer::whereKey($customer->id)->lockForUpdate()->first();

                $bills = $this->ledger->shipmentBills($customer)
                    ->keyBy(fn ($b) => $b['shipment']->id);

                foreach ($allocations as $shipmentId => $amt) {
                    $bill = $bills->get((int) $shipmentId);

                    if (!$bill) {
                        throw new \Exception("Shipment #{$shipmentId} does not belong to this customer.");
                    }
                    if ($amt > $bill['due'] + 0.004) {
                        throw new \Exception(
                            "Allocation for {$bill['shipment']->shipment_name} (" . number_format($amt, 2) .
                            ") is more than its due (" . number_format($bill['due'], 2) . ")."
                        );
                    }
                }

                $allocatedTotal = round((float) $allocations->sum(), 2);

                if (abs($allocatedTotal - round((float) $request->amount, 2)) > 0.009) {
                    throw new \Exception('Allocated total (' . number_format($allocatedTotal, 2) .
                        ') must equal the received amount (' . number_format((float) $request->amount, 2) . ').');
                }

                $dueBefore = (float) $bills->sum('due');

                $payment = CustomerPayment::create([
                    'customer_id'     => $customer->id,
                    'user_id'         => Auth::id(),
                    'itemshipment_id' => $allocations->count() === 1 ? (int) $allocations->keys()->first() : null,
                    'amount'          => $allocatedTotal,
                    'payment_method'  => $request->payment_method,
                    'payment_date'    => $request->payment_date,
                    'reference_no'    => $request->reference_no,
                    'notes'           => $request->notes,
                    'remaining_due'   => max($dueBefore - $allocatedTotal, 0),
                ]);

                foreach ($allocations as $shipmentId => $amt) {
                    CustomerPaymentAllocation::create([
                        'customer_payment_id' => $payment->id,
                        'itemshipment_id'     => (int) $shipmentId,
                        'amount'              => $amt,
                    ]);
                }

                return $payment;
            });

            $message = 'Payment of ' . number_format($payment->amount, 2) . ' tk received and allocated to ' .
                $allocations->count() . ' shipment(s).';

            if ($request->input('return') === 'billing' && $request->filled('shipment')) {
                return redirect()->route('shipments.billing', (int) $request->input('shipment'))
                    ->with('success', $message);
            }

            return redirect()->route('ledger.customer', $customer->id)->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    /**
     * Delete a whole receipt (all its allocations). Needs the "delete-payment" permission.
     */
    public function destroy(CustomerPayment $payment)
    {
        abort_unless(Gate::allows('delete-payment'), 403);

        try {
            DB::transaction(function () use ($payment) {
                // legacy item-level payments: release the items again
                foreach ($payment->paymentItems()->with('cargoItem')->get() as $paymentItem) {
                    if ($paymentItem->cargoItem) {
                        $paymentItem->cargoItem->paid_at = null;
                        $paymentItem->cargoItem->save();
                    }
                }

                $payment->paymentItems()->delete();
                $payment->allocations()->delete();
                $payment->delete();
            });

            return redirect()->back()->with('success', 'Payment deleted. Shipment dues were recalculated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }

    /**
     * Payment history of one customer for one shipment (allocation based).
     */
    public function history(Customer $customer, ItemsShipment $itemshipment)
    {
        $bill        = $this->ledger->shipmentBills($customer)->firstWhere('shipment.id', $itemshipment->id);
        $totalAmount = $bill['billed'] ?? 0;

        $allocations = CustomerPaymentAllocation::with(['payment.user', 'payment.allocations.itemshipment', 'payment.paymentItems.cargoItem'])
            ->where('itemshipment_id', $itemshipment->id)
            ->whereHas('payment', fn ($q) => $q->where('customer_id', $customer->id))
            ->get()
            ->sort(fn ($a, $b) => [$a->payment->payment_date, $a->id] <=> [$b->payment->payment_date, $b->id])
            ->values();

        // running remaining due for THIS shipment after each payment
        $running = 0;
        foreach ($allocations as $alloc) {
            $running += (float) $alloc->amount;
            $alloc->setAttribute('remaining_after', max($totalAmount - $running, 0));
        }

        $allocations = $allocations->reverse()->values(); // newest first
        $totalPaid   = (float) $allocations->sum('amount');
        $totalDue    = max($totalAmount - $totalPaid, 0);

        return view('admin.payments.history', compact(
            'customer',
            'itemshipment',
            'allocations',
            'totalAmount',
            'totalPaid',
            'totalDue'
        ));
    }
}