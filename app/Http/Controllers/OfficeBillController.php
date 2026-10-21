<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\OfficeBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficeBillController extends Controller
{
    public function index(Request $request)
    {
        $offices = Office::orderBy('name')->get();

        $q = OfficeBill::with('office')
            ->when($request->filled('office_id'), fn ($q) => $q->where('office_id', $request->office_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('bill_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('bill_date', '<=', $request->to));

        $total = (clone $q)->sum('amount');
        $bills = $q->orderByDesc('bill_date')->orderByDesc('id')->get();

        return view('admin.office_bills.index', compact('bills', 'offices', 'total'));
    }

    public function create()
    {
        $offices = Office::orderBy('name')->get();
        return view('admin.office_bills.create', compact('offices'));
    }

    /** Several bills at once (rows). Every row is a NEW bill - nothing is overwritten. */
    public function store(Request $request)
    {
        $request->validate([
            'bills'               => 'required|array|min:1',
            'bills.*.office_id'   => 'required|exists:offices,id',
            'bills.*.bill_type'   => 'nullable|string|max:80',
            'bills.*.bill_date'   => 'required|date',
            'bills.*.amount'      => 'required|numeric|min:0',
            'bills.*.note'        => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->bills as $row) {
                $date = \Carbon\Carbon::parse($row['bill_date']);
                OfficeBill::create([
                    'office_id' => $row['office_id'],
                    'bill_type' => $row['bill_type'] ?? null,
                    'bill_date' => $date->toDateString(),
                    'year'      => $date->year,
                    'month'     => $date->month,
                    'amount'    => $row['amount'],
                    'note'      => $row['note'] ?? null,
                ]);
            }
        });

        return redirect()->route('office-bills.index')
            ->with('success', count($request->bills) . ' office bill(s) saved.');
    }

    public function edit(OfficeBill $office_bill)
    {
        $offices = Office::orderBy('name')->get();
        return view('admin.office_bills.edit', ['bill' => $office_bill, 'offices' => $offices]);
    }

    public function update(Request $request, OfficeBill $office_bill)
    {
        $data = $request->validate([
            'office_id' => 'required|exists:offices,id',
            'bill_type' => 'nullable|string|max:80',
            'bill_date' => 'required|date',
            'amount'    => 'required|numeric|min:0',
            'note'      => 'nullable|string|max:255',
        ]);

        $date = \Carbon\Carbon::parse($data['bill_date']);
        $data['bill_date'] = $date->toDateString();
        $data['year']  = $date->year;
        $data['month'] = $date->month;

        $office_bill->update($data);

        return redirect()->route('office-bills.index')->with('success', 'Office bill updated successfully.');
    }

    public function destroy(OfficeBill $office_bill)
    {
        $office_bill->delete();
        return redirect()->route('office-bills.index')->with('success', 'Office bill deleted successfully.');
    }
}
