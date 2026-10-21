<?php

namespace App\Http\Controllers;

use App\Exports\SheetsExport;
use App\Models\ThirdParty;
use App\Models\ThirdPartyPayment;
use App\Services\ThirdPartyLedgerService;
use App\Support\PaymentCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/** Third-party companies that ship through us: CRUD, payments received, ledger. */
class ThirdPartyController extends Controller
{
    public function __construct(private ThirdPartyLedgerService $svc)
    {
    }

    public function index()
    {
        abort_unless(Gate::allows('view-customers'), 403);

        $parties  = ThirdParty::withCount('items')->orderBy('name')->get();
        $balances = $this->svc->balances();

        return view('admin.third_parties.index', compact('parties', 'balances'));
    }

    public function create()
    {
        abort_unless(Gate::allows('create-customers'), 403);
        return view('admin.third_parties.form', ['party' => new ThirdParty(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('create-customers'), 403);
        ThirdParty::create($this->data($request));
        return redirect()->route('third-parties.index')->with('success', 'Third party created.');
    }

    public function edit(ThirdParty $third_party)
    {
        abort_unless(Gate::allows('edit-customers'), 403);
        return view('admin.third_parties.form', ['party' => $third_party]);
    }

    public function update(Request $request, ThirdParty $third_party)
    {
        abort_unless(Gate::allows('edit-customers'), 403);
        $third_party->update($this->data($request, $third_party));
        return redirect()->route('third-parties.index')->with('success', 'Third party updated.');
    }

    public function destroy(ThirdParty $third_party)
    {
        abort_unless(Gate::allows('delete-customers'), 403);

        if ($third_party->items()->exists() || $third_party->payments()->exists()) {
            return back()->with('error', 'This company has items or payments. Mark it inactive instead of deleting.');
        }
        $third_party->delete();
        return redirect()->route('third-parties.index')->with('success', 'Third party deleted.');
    }

    private function data(Request $request, ?ThirdParty $party = null): array
    {
        $d = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:40|unique:third_parties,code' . ($party ? ',' . $party->id : ''),
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:255',
            'note'           => 'nullable|string',
        ]);
        $d['is_active'] = $request->boolean('is_active');
        return $d;
    }

    // ---------------------------------------------------------------- ledger

    public function ledger(Request $request, ThirdParty $third_party)
    {
        abort_unless(Gate::allows('view-customers'), 403);
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);

        $from = $request->input('from');
        $to   = $request->input('to');
        $st   = $this->svc->statement($third_party, $from, $to);

        if ($request->boolean('export') || $request->boolean('pdf')) {
            $period = ($from || $to) ? (($from ?: 'start') . ' to ' . ($to ?: 'today')) : 'All time';
            $sheet = [
                'title'    => 'Ledger',
                'heading'  => 'Ledger - ' . $third_party->name,
                'subtitle' => 'Period: ' . $period . ' | Opening ' . number_format($st['opening'], 2) . ' | Closing (due) ' . number_format($st['closing'], 2),
                'headings' => ['Date', 'Document', 'Description', 'Details', 'Method', 'Billed (debit)', 'Received (credit)', 'Balance (due)'],
                'rows'     => array_merge(
                    [['', '', 'Opening balance', '', '', '', '', $st['opening']]],
                    $st['entries']->map(fn ($e) => [
                        $e['date'], $e['doc'], $e['desc'], $e['detail'],
                        $e['method'] ? ucfirst(str_replace('_', ' ', $e['method'])) : '',
                        $e['debit'] ?: '', $e['credit'] ?: '', $e['balance'],
                    ])->all()
                ),
                'totals'   => ['', '', 'TOTAL / CLOSING', '', '', $st['debit'], $st['credit'], $st['closing']],
                'money'    => [5, 6, 7],
            ];
            return SheetsExport::respond([$sheet], 'third-party-ledger-' . $third_party->id . '-' . date('Y-m-d') . '.xlsx');
        }

        return view('admin.third_parties.ledger', ['party' => $third_party, 'st' => $st, 'from' => $from, 'to' => $to]);
    }

    // -------------------------------------------------------------- payments

    public function storePayment(Request $request, ThirdParty $third_party)
    {
        abort_unless(Gate::allows('add-payment'), 403);

        try {
            $cur = PaymentCurrency::apply($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,card',
            'reference_no'   => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
        ]);

        ThirdPartyPayment::create([
            'third_party_id' => $third_party->id,
            'user_id'        => Auth::id(),
            'amount'         => $request->amount,
            'currency'       => $cur['currency'],
            'foreign_amount' => $cur['foreign_amount'],
            'exchange_rate'  => $cur['exchange_rate'],
            'payment_date'   => $request->payment_date,
            'payment_method' => $request->payment_method,
            'reference_no'   => $request->reference_no,
            'notes'          => $request->notes,
        ]);

        return redirect()->route('third-parties.ledger', $third_party->id)
            ->with('success', 'Payment of ' . number_format($request->amount, 2) . ' BDT recorded.');
    }

    public function destroyPayment(ThirdPartyPayment $payment)
    {
        abort_unless(Gate::allows('delete-payment'), 403);
        $id = $payment->third_party_id;
        $payment->delete();
        return redirect()->route('third-parties.ledger', $id)->with('success', 'Payment deleted.');
    }
}
