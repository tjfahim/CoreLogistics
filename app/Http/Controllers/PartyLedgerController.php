<?php

namespace App\Http\Controllers;

use App\Exports\SheetsExport;
use App\Models\Agent;
use App\Models\ItemsShipment;
use App\Services\AccountingService;
use App\Services\ThirdPartyLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Ledgers for the parties that are not customers:
 *   - agents      (money we owe them: cost lines vs payments)
 *   - shipments   (one shipment: what we billed / collected vs what it cost / we paid)
 * Customers and third parties have their own ledger pages.
 */
class PartyLedgerController extends Controller
{
    public function __construct(private AccountingService $acc, private ThirdPartyLedgerService $tp)
    {
    }

    /** All agents with billed (cost) / paid / due. */
    public function agents(Request $request)
    {
        abort_unless(Gate::allows('view-shipment-cost'), 403);

        $led = $this->acc->payableLedger([]);
        $agents = Agent::orderBy('name')->get(['id', 'name', 'company_name', 'type']);
        $pos = $led['by_agent'];   // name keyed, aggregate by agent name

        $rows = $agents->map(function ($a) use ($pos) {
            $p = $pos->firstWhere('name', $a->name);
            return ['agent' => $a, 'lines' => $p['lines'] ?? 0, 'bill' => $p['bill'] ?? 0, 'paid' => $p['paid'] ?? 0, 'due' => $p['due'] ?? 0];
        });

        if ($request->boolean('export') || $request->boolean('pdf')) {
            $sheet = [
                'title' => 'Agents', 'heading' => 'Agent ledger - all agents',
                'subtitle' => 'Generated ' . now()->format('d M Y H:i'),
                'headings' => ['Agent', 'Company', 'Cost lines', 'Total bill', 'Paid', 'Due'],
                'rows' => $rows->map(fn ($r) => [$r['agent']->name, $r['agent']->company_name, $r['lines'], $r['bill'], $r['paid'], $r['due']])->all(),
                'totals' => ['TOTAL', '', $rows->sum('lines'), $rows->sum('bill'), $rows->sum('paid'), $rows->sum('due')],
                'money' => [3, 4, 5], 'int' => [2],
            ];
            return SheetsExport::respond([$sheet], 'agent-ledger-' . date('Y-m-d') . '.xlsx');
        }

        return view('admin.ledger.agents', compact('rows'));
    }

    /** One agent: every bill and payment with a running balance. */
    public function agent(Request $request, Agent $agent)
    {
        abort_unless(Gate::allows('view-shipment-cost'), 403);
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);

        $from = $request->input('from');
        $to   = $request->input('to');
        $led  = $this->acc->payableLedger(['agent_id' => $agent->id, 'from' => $from, 'to' => $to]);

        if ($request->boolean('export') || $request->boolean('pdf')) {
            $period = ($from || $to) ? (($from ?: 'start') . ' to ' . ($to ?: 'today')) : 'All time';
            $sheet = [
                'title' => 'Ledger', 'heading' => 'Agent ledger - ' . $agent->name,
                'subtitle' => 'Period: ' . $period . ' | Opening ' . number_format($led['opening'], 2) . ' | Closing (we owe) ' . number_format($led['closing'], 2),
                'headings' => ['Date', 'Document', 'Shipment', 'Description', 'Details', 'Method', 'Bill (we owe)', 'Paid', 'Balance (due)'],
                'rows' => array_merge(
                    [['', '', '', 'Opening balance', '', '', '', '', $led['opening']]],
                    $led['entries']->map(fn ($e) => [
                        $e['date'], $e['doc'], $e['shipment'], $e['desc'], $e['detail'],
                        $e['method'] ? ucfirst(str_replace('_', ' ', $e['method'])) : '',
                        $e['bill'] ?: '', $e['paid'] ?: '', $e['balance'],
                    ])->all()
                ),
                'totals' => ['', '', '', 'TOTAL / CLOSING', '', '', $led['bills'], $led['paid'], $led['closing']],
                'money' => [6, 7, 8],
            ];
            return SheetsExport::respond([$sheet], 'agent-ledger-' . $agent->id . '-' . date('Y-m-d') . '.xlsx');
        }

        return view('admin.ledger.agent', compact('agent', 'led', 'from', 'to'));
    }

    /** One shipment: receivable side (customers + third parties) and payable side (costs). */
    public function shipment(Request $request, ItemsShipment $itemshipment)
    {
        abort_unless(Gate::allows('view-shipments'), 403);

        $id = $itemshipment->id;

        // ---- receivable side
        $custRows = $this->acc->shipmentCustomers([$id]);
        $tpRows   = $this->tp->shipmentRows(['shipment_ids' => [$id]]);
        $tpNames  = \App\Models\ThirdParty::whereIn('id', $tpRows->pluck('third_party_id'))->pluck('name', 'id');

        $recv = collect();
        foreach ($custRows as $r) {
            $recv->push(['party' => optional($r->customer)->name ?? ('#' . $r->customer_id), 'kind' => 'Customer',
                         'items' => $r->items, 'weight' => $r->weight, 'billed' => $r->billed, 'paid' => $r->paid, 'due' => $r->due]);
        }
        foreach ($tpRows as $r) {
            $recv->push(['party' => $tpNames[$r->third_party_id] ?? ('#' . $r->third_party_id), 'kind' => 'Third party',
                         'items' => $r->items, 'weight' => $r->weight, 'billed' => $r->billed, 'paid' => null, 'due' => null]);
        }

        $receipts = \App\Models\CustomerPaymentAllocation::with('payment.customer:id,name')
            ->where('itemshipment_id', $id)->get()
            ->sortBy(fn ($a) => $a->payment->payment_date)->values();

        // ---- payable side
        $led = $this->acc->payableLedger(['shipment_ids' => [$id]]);

        $billed    = round($recv->sum('billed'), 2);
        $collected = round((float) $receipts->sum('amount'), 2);
        $cost      = $led['bills'];
        $sum = [
            'billed' => $billed, 'collected' => $collected, 'receivable' => round($custRows->sum('due'), 2),
            'cost' => $cost, 'cost_paid' => $led['paid'], 'cost_due' => $led['closing'],
            'profit' => round($billed - $cost, 2),
        ];

        if ($request->boolean('export') || $request->boolean('pdf')) {
            $a = [
                'title' => 'Receivable', 'heading' => 'Shipment ledger - ' . $itemshipment->shipment_name,
                'subtitle' => 'Billed ' . number_format($billed, 2) . ' | Collected (customers) ' . number_format($collected, 2) . ' | Cost ' . number_format($cost, 2) . ' | Profit ' . number_format($sum['profit'], 2),
                'headings' => ['Party', 'Type', 'Items', 'Weight (kg)', 'Billed', 'Received', 'Due'],
                'rows' => $recv->map(fn ($r) => [$r['party'], $r['kind'], $r['items'], $r['weight'], $r['billed'], $r['paid'] ?? '', $r['due'] ?? ''])->all(),
                'totals' => ['TOTAL', '', $recv->sum('items'), $recv->sum('weight'), $billed, $collected, $sum['receivable']],
                'money' => [3, 4, 5, 6], 'int' => [2],
            ];
            $b = [
                'title' => 'Collections', 'heading' => 'Customer payments received for this shipment',
                'headings' => ['Date', 'Customer', 'Method', 'Reference', 'Amount'],
                'rows' => $receipts->map(fn ($x) => [$x->payment->payment_date->toDateString(), optional($x->payment->customer)->name, $x->payment->payment_method, $x->payment->reference_no, (float) $x->amount])->all(),
                'totals' => ['TOTAL', '', '', '', $collected], 'money' => [4],
            ];
            $c = [
                'title' => 'Costs', 'heading' => 'Costs and payments of this shipment',
                'headings' => ['Date', 'Document', 'Description', 'Agent', 'Details', 'Bill', 'Paid', 'Balance'],
                'rows' => $led['entries']->map(fn ($e) => [$e['date'], $e['doc'], $e['desc'], $e['party'], $e['detail'], $e['bill'] ?: '', $e['paid'] ?: '', $e['balance']])->all(),
                'totals' => ['TOTAL', '', '', '', '', $led['bills'], $led['paid'], $led['closing']], 'money' => [5, 6, 7],
            ];
            return SheetsExport::respond([$a, $b, $c], 'shipment-ledger-' . $id . '-' . date('Y-m-d') . '.xlsx');
        }

        return view('admin.ledger.shipment', compact('itemshipment', 'recv', 'receipts', 'led', 'sum'));
    }
}
