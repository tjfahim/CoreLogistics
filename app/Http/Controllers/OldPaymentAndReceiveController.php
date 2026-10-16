<?php

namespace App\Http\Controllers;

use App\Models\OldCustomer;
use App\Models\OldPaymentAndReceive;
use App\Models\OldShipment;
use App\Models\OldShipmentBill;
use App\Models\OldShipmentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class OldPaymentAndReceiveController extends Controller
{
    public function index(Request $request)
    {
        $base = OldPaymentAndReceive::query()
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->old_shipment_id, fn ($q) => $q->where('old_shipment_id', $request->old_shipment_id))
            ->when($request->old_customer_id, fn ($q) => $q->where('old_customer_id', $request->old_customer_id));

        $totalPaid     = (clone $base)->where('type', 'paid')->sum('amount');
        $totalReceived = (clone $base)->where('type', 'received')->sum('amount');

        $payments = $base->with(['shipment', 'customer'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $shipments = OldShipment::orderBy('date', 'desc')->get();
        $customers = OldCustomer::orderBy('name')->get();

        return view('admin.old_payments.index', compact(
            'payments', 'shipments', 'customers', 'totalPaid', 'totalReceived'
        ));
    }

    public function create(Request $request)
    {
        return view('admin.old_payments.create', [
            'shipments' => OldShipment::orderBy('date', 'desc')->get(),
            'customers' => OldCustomer::orderBy('name')->get(),
            'preset'    => $request->only('type', 'old_shipment_id', 'old_customer_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules($request));

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        OldPaymentAndReceive::create($this->payload($request));

        return redirect()->route('old-payments.index')
            ->with('success', 'Entry saved successfully.');
    }

    public function edit(OldPaymentAndReceive $old_payment)
    {
        return view('admin.old_payments.edit', [
            'payment'   => $old_payment,
            'shipments' => OldShipment::orderBy('date', 'desc')->get(),
            'customers' => OldCustomer::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, OldPaymentAndReceive $old_payment)
    {
        $validator = Validator::make($request->all(), $this->rules($request));

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $old_payment->update($this->payload($request));

        return redirect()->route('old-payments.index')
            ->with('success', 'Entry updated successfully.');
    }

    public function destroy(OldPaymentAndReceive $old_payment)
    {
        $old_payment->delete();

        return redirect()->route('old-payments.index')
            ->with('success', 'Entry deleted successfully.');
    }

    /** Monthly details report with summary. */
    public function report(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $start     = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $end       = $start->copy()->endOfMonth();
        $startDate = $start->toDateString();
        $endDate   = $end->toDateString();

        // opening balance = everything received minus paid before this month
        $before  = OldPaymentAndReceive::where('date', '<', $startDate);
        $opening = (float) (clone $before)->where('type', 'received')->sum('amount')
                 - (float) (clone $before)->where('type', 'paid')->sum('amount');

        // month entries with running balance
        $entries = OldPaymentAndReceive::with(['shipment', 'customer'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $running = $opening;
        foreach ($entries as $e) {
            $running += $e->type === 'received' ? (float) $e->amount : -(float) $e->amount;
            $e->running = $running;
        }

        $totalReceived = (float) $entries->where('type', 'received')->sum('amount');
        $totalPaid     = (float) $entries->where('type', 'paid')->sum('amount');
        $closing       = $opening + $totalReceived - $totalPaid;

        // collections grouped by customer
        $byCustomer = $entries->where('type', 'received')
            ->groupBy(fn ($e) => $e->old_customer_id ?? 0)
            ->map(fn ($g) => (object) [
                'name'  => $g->first()->customer->name ?? 'Unknown',
                'count' => $g->count(),
                'total' => (float) $g->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        // received / paid grouped by shipment
        $byShipment = $entries
            ->groupBy(fn ($e) => $e->old_shipment_id ?? 0)
            ->map(fn ($g) => (object) [
                'name'     => $g->first()->shipment->name ?? 'No shipment',
                'received' => (float) $g->where('type', 'received')->sum('amount'),
                'paid'     => (float) $g->where('type', 'paid')->sum('amount'),
            ])
            ->values();

        // shipments dated in this month: what was billed
        $shipments = OldShipment::whereBetween('date', [$startDate, $endDate])
            ->withCount('items')
            ->withSum('bills as bills_sum', 'total_price')
            ->withSum('items as items_sum', 'final_price')
            ->orderBy('date')
            ->get();

        // 12-month overview for the selected year
        $yearRows = OldPaymentAndReceive::whereYear('date', $start->year)->get(['date', 'type', 'amount']);
        $yearSummary = [];
        for ($m = 1; $m <= 12; $m++) {
            $rows = $yearRows->filter(fn ($r) => (int) $r->date->format('n') === $m);
            $rec  = (float) $rows->where('type', 'received')->sum('amount');
            $paid = (float) $rows->where('type', 'paid')->sum('amount');
            $yearSummary[] = (object) [
                'key'      => sprintf('%04d-%02d', $start->year, $m),
                'label'    => Carbon::create($start->year, $m, 1)->format('F'),
                'received' => $rec,
                'paid'     => $paid,
                'net'      => $rec - $paid,
            ];
        }

        // all-time outstanding
        $overall = (object) [
            'customer_bill'     => (float) OldShipmentItem::sum('final_price'),
            'customer_received' => (float) OldPaymentAndReceive::where('type', 'received')->sum('amount'),
            'shipment_bill'     => (float) OldShipmentBill::sum('total_price'),
            'shipment_paid'     => (float) OldPaymentAndReceive::where('type', 'paid')->sum('amount'),
        ];

        return view('admin.old_payments.report', [
            'month'         => $month,
            'start'         => $start,
            'prevMonth'     => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth'     => $start->copy()->addMonth()->format('Y-m'),
            'opening'       => $opening,
            'closing'       => $closing,
            'totalReceived' => $totalReceived,
            'totalPaid'     => $totalPaid,
            'entries'       => $entries,
            'byCustomer'    => $byCustomer,
            'byShipment'    => $byShipment,
            'shipments'     => $shipments,
            'yearSummary'   => $yearSummary,
            'overall'       => $overall,
        ]);
    }

    // ------------------------------------------------------------------

    private function rules(Request $request): array
    {
        return [
            'type'            => 'required|in:paid,received',
            // paid = shipment bill payment, received = customer collection
            'old_shipment_id' => ($request->type === 'paid' ? 'required' : 'nullable') . '|exists:old_shipments,id',
            'old_customer_id' => ($request->type === 'received' ? 'required' : 'nullable') . '|exists:old_customers,id',
            'amount'          => 'required|numeric|min:0.01',
            'date'            => 'required|date',
            'method'          => 'nullable|string|max:100',
            'note'            => 'nullable|string|max:255',
        ];
    }

    private function payload(Request $request): array
    {
        $data = $request->only('type', 'old_shipment_id', 'old_customer_id', 'amount', 'date', 'method', 'note');

        // paid entries never carry a customer
        if ($data['type'] === 'paid') {
            $data['old_customer_id'] = null;
        }

        $data['old_shipment_id'] = $data['old_shipment_id'] ?: null;
        $data['old_customer_id'] = $data['old_customer_id'] ?: null;

        return $data;
    }
}
