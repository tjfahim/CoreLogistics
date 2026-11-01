<?php

namespace App\Http\Controllers;

use App\Models\FundMovement;
use App\Services\CashBookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CashBookController extends Controller
{
    public function __construct(private CashBookService $book)
    {
    }

    public function index(Request $request)
    {
        abort_unless(Gate::allows('view-cash-book'), 403);
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $st = $this->book->statement($from, $to);
        $now = $this->book->balances();
        $movements = FundMovement::with('user:id,name')->latest('movement_date')->latest('id')->take(30)->get();

        return view('admin.cashbook.index', compact('st', 'now', 'from', 'to', 'movements'));
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('manage-cash-book'), 403);

        $data = $request->validate([
            'type'          => 'required|in:opening,transfer,deposit,withdraw',
            'movement_date' => 'required|date',
            'amount'        => 'required|numeric|gt:0',
            'account'       => 'nullable|in:cash,bank',
            'from_account'  => 'nullable|in:cash,bank',
            'to_account'    => 'nullable|in:cash,bank',
            'note'          => 'nullable|string|max:255',
        ]);

        $from = $to = null;
        switch ($data['type']) {
            case 'opening':
            case 'deposit':
                $to = $data['account'] ?? null;
                break;
            case 'withdraw':
                $from = $data['account'] ?? null;
                break;
            case 'transfer':
                $from = $data['from_account'] ?? null;
                $to   = $data['to_account'] ?? null;
                if ($from && $from === $to) {
                    return back()->withInput()->with('error', 'Transfer needs two different accounts.');
                }
                break;
        }
        if (($data['type'] === 'transfer' && (!$from || !$to)) || ($data['type'] !== 'transfer' && !($from ?? $to))) {
            return back()->withInput()->with('error', 'Please choose the account.');
        }

        FundMovement::create([
            'movement_date' => $data['movement_date'], 'type' => $data['type'],
            'from_account' => $from, 'to_account' => $to, 'amount' => round((float) $data['amount'], 2),
            'note' => $data['note'] ?? null, 'user_id' => Auth::id(),
        ]);

        return redirect()->route('cashbook.index')->with('success', 'Entry saved.');
    }

    public function destroy(FundMovement $movement)
    {
        abort_unless(Gate::allows('manage-cash-book'), 403);
        $movement->delete();
        return back()->with('success', 'Entry deleted.');
    }
}
