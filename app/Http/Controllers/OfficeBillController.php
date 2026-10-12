<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\OfficeBill;
use Illuminate\Http\Request;

class OfficeBillController extends Controller
{
    public function index()
    {
        $bills = OfficeBill::with('office')->latest()->get();
        return view('admin.office_bills.index', compact('bills'));
    }

    public function create()
    {
        $offices = Office::orderBy('name')->get();
        return view('admin.office_bills.create', compact('offices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'office_id' => 'required|exists:offices,id',
            'year'      => 'required|integer|min:2000|max:2100',
            'month'     => 'required|integer|min:1|max:12',
            'amount'    => 'required|numeric|min:0',
            'note'      => 'nullable|string|max:255',
        ]);

        OfficeBill::updateOrCreate(
            [
                'office_id' => $request->office_id,
                'year'      => $request->year,
                'month'     => $request->month,
            ],
            [
                'amount' => $request->amount,
                'note'   => $request->note,
            ]
        );

        return redirect()->route('office-bills.index')->with('success', 'Office bill saved successfully.');
    }

    public function edit(OfficeBill $office_bill)
    {
        $offices = Office::orderBy('name')->get();
        return view('admin.office_bills.edit', ['bill' => $office_bill, 'offices' => $offices]);
    }

    public function update(Request $request, OfficeBill $office_bill)
    {
        $request->validate([
            'office_id' => 'required|exists:offices,id',
            'year'      => 'required|integer|min:2000|max:2100',
            'month'     => 'required|integer|min:1|max:12',
            'amount'    => 'required|numeric|min:0',
            'note'      => 'nullable|string|max:255',
        ]);

        $office_bill->update($request->only('office_id', 'year', 'month', 'amount', 'note'));

        return redirect()->route('office-bills.index')->with('success', 'Office bill updated successfully.');
    }

    public function destroy(OfficeBill $office_bill)
    {
        $office_bill->delete();
        return redirect()->route('office-bills.index')->with('success', 'Office bill deleted successfully.');
    }
}