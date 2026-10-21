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

        $advance = $this->ledger->advance($customer);

        return view('admin.payments.receive', compact('customer', 'dueBills', 'totals', 'preselect', 'return', 'advance'));
    }

    /**
     * Save one receipt + its allocations (all-or-nothing).
     */
    public function storeReceipt(Request $request, Customer $customer)
    {
        abort_unless(Gate::allows('add-payment'), 403);

        try {
            $cur = \App\Support\PaymentCurrency::apply($request);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:cash,mobile_banking,cheque,bank_transfer',
            'payment_date'   => 'required|date',
            'reference_no'   => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'allocations'    => 'nullable|array',
            'allocations.*'  => 'nullable|numeric|min:0',
            'advance_used'   => 'nullable|numeric|min:0',
        ]);

        $allocations = collect($request->input('allocations', []))
            ->map(fn ($v) => round((float) $v, 2))
            ->filter(fn ($v) => $v > 0);

        try {
            $advanceCreated = 0.0;
            $payment = DB::transaction(function () use ($request, $customer, $allocations, $cur, &$advanceCreated) {
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

                $received   = round((float) $request->amount, 2);
                $advUsed    = round((float) $request->input('advance_used', 0), 2);
                $available  = $this->ledger->advance($customer);

                if ($advUsed > $available + 0.004) {
                    throw new \Exception('Advance used (' . number_format($advUsed, 2) .
                        ') is more than the available advance (' . number_format($available, 2) . ').');
                }
                if ($allocatedTotal > $received + $advUsed + 0.009) {
                    throw new \Exception('Allocated total (' . number_format($allocatedTotal, 2) .
                        ') is more than the received amount plus advance used (' . number_format($received + $advUsed, 2) . ').');
                }

                // whatever is received but not allocated stays with the customer as ADVANCE
                $advanceCreated = max(round($received + $advUsed - $allocatedTotal, 2), 0);

                $dueBefore = (float) $bills->sum('due');

                $payment = CustomerPayment::create([
                    'customer_id'     => $customer->id,
                    'user_id'         => Auth::id(),
                    'itemshipment_id' => $allocations->count() === 1 ? (int) $allocations->keys()->first() : null,
                    'amount'          => $received,
                    'currency'        => $cur['currency'],
                    'foreign_amount'  => $cur['foreign_amount'],
                    'exchange_rate'   => $cur['exchange_rate'],
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
                $allocations->count() . ' shipment(s).'
                . ($advanceCreated > 0 ? ' Extra ' . number_format($advanceCreated, 2) . ' tk saved as customer advance.' : '');

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

        if (request()->boolean('pdf')) {
            $sheet = [
                'title' => 'Payments', 'heading' => 'Payment history - ' . $customer->name . ' / ' . $itemshipment->shipment_name,
                'subtitle' => 'Billed ' . number_format($totalAmount, 2) . ' | Paid ' . number_format($totalPaid, 2) . ' | Due ' . number_format($totalDue, 2),
                'headings' => ['Date', 'Receipt', 'Method', 'Reference', 'Paid in', 'Amount (BDT)', 'Due after'],
                'rows' => $allocations->map(fn ($a) => [
                    $a->payment->payment_date->toDateString(), \App\Services\AccountingService::receiptNo($a->payment->id),
                    ucfirst(str_replace('_', ' ', $a->payment->payment_method)), $a->payment->reference_no,
                    $a->payment->currency !== 'bdt' ? strtoupper($a->payment->currency) . ' ' . number_format((float) $a->payment->foreign_amount, 2) . ' @ ' . rtrim(rtrim(number_format((float) $a->payment->exchange_rate, 4), '0'), '.') : '',
                    (float) $a->amount, (float) $a->remaining_after,
                ])->all(),
                'totals' => ['TOTAL', '', '', '', '', $totalPaid, $totalDue], 'money' => [5, 6],
            ];
            return \App\Support\SheetsPdf::download([$sheet], 'payment-history-' . $customer->id . '-' . $itemshipment->id . '.pdf');
        }

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