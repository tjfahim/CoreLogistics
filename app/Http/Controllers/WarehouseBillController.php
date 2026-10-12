<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MonthlySummary;
use App\Models\Office;
use App\Models\OfficeBill;
use App\Models\WarehouseBill;
use Illuminate\Http\Request;

class WarehouseBillController extends Controller
{
    public function index(Request $request)
    {
        $offices = Office::with('employees')->orderBy('name')->get();

        $year  = (int) $request->input('year',  date('Y'));
        $month = (int) $request->input('month', date('n'));

        // Only what we show
        $officeBillsTotal = OfficeBill::where('year', $year)->where('month', $month)->sum('amount');
        $salariesTotal    = Employee::sum('salary');
        $grandTotal       = $officeBillsTotal + $salariesTotal;

        $savedSummary = MonthlySummary::where('year', $year)->where('month', $month)->first();

        // Show recent-saved card ONLY after Save (flag in query string)
        $showRecent = $request->boolean('saved');

        $recent = $showRecent
            ? MonthlySummary::orderByDesc('year')->orderByDesc('month')->limit(20)->get()
            : collect();

        return view('admin.warehouse_bills.index', compact(
            'offices',
            'year', 'month',
            'officeBillsTotal', 'salariesTotal', 'grandTotal',
            'savedSummary', 'showRecent', 'recent'
        ));
    }

    /**
     * Save only: year, month, average_kg, cost_per_kg (+ computed totals).
     */
    public function saveMonthlySummary(Request $request)
    {
        $request->validate([
            'year'       => 'required|integer|min:2000|max:2100',
            'month'      => 'required|integer|min:1|max:12',
            'default_kg' => 'required|numeric|min:0.01',
        ]);

        $year  = (int) $request->year;
        $month = (int) $request->month;

        // Recompute server-side
        $officeBillsTotal = OfficeBill::where('year', $year)->where('month', $month)->sum('amount');
        $salariesTotal    = Employee::sum('salary');
        $grandTotal       = $officeBillsTotal + $salariesTotal;

        $defaultKg = (float) $request->default_kg;
        $costPerKg = $defaultKg > 0 ? round($grandTotal / $defaultKg, 4) : 0;

        MonthlySummary::updateOrCreate(
            ['year' => $year, 'month' => $month],
            [
                'office_bills_total'    => $officeBillsTotal,
                'salaries_total'        => $salariesTotal,
                'warehouse_bills_total' => 0,   // not used now
                'grand_total'           => $grandTotal,
                'default_kg'            => $defaultKg,
                'cost_per_kg'           => $costPerKg,
            ]
        );

        return redirect()
            ->route('warehouse-bills.index', [
                'year'  => $year,
                'month' => $month,
                'saved' => 1,
            ])
            ->with('success', 'Saved. Cost per KG: ' . number_format($costPerKg, 4) . ' BDT');
    }

    // Keep these two so existing routes / links don't break
    public function store(Request $request)
    {
        $request->validate([
            'office_id'    => 'required|exists:offices,id',
            'kg'           => 'required|numeric|min:0',
            'rate_per_kg'  => 'required|numeric|min:0',
            'bill_date'    => 'required|date',
            'note'         => 'nullable|string|max:255',
        ]);

        $total = round($request->kg * $request->rate_per_kg, 2);

        WarehouseBill::create([
            'office_id'    => $request->office_id,
            'kg'           => $request->kg,
            'rate_per_kg'  => $request->rate_per_kg,
            'total_amount' => $total,
            'bill_date'    => $request->bill_date,
            'note'         => $request->note,
        ]);

        return redirect()->route('warehouse-bills.index')
            ->with('success', 'Warehouse bill saved: ' . number_format($total, 2) . ' BDT');
    }

    public function destroy(WarehouseBill $warehouse_bill)
    {
        $warehouse_bill->delete();
        return redirect()->route('warehouse-bills.index')->with('success', 'Warehouse bill deleted.');
    }

    public function summary(Request $request)
    {
        $year  = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('n'));

        $data = Office::with(['employees'])->get()->map(function ($office) use ($year, $month) {
            $bill = OfficeBill::where('office_id', $office->id)
                ->where('year', $year)->where('month', $month)->first();

            $salaries = $office->employees->sum('salary');

            return [
                'office'   => $office->name,
                'bill'     => $bill->amount ?? 0,
                'salaries' => $salaries,
                'total'    => ($bill->amount ?? 0) + $salaries,
            ];
        });

        return response()->json(['year' => $year, 'month' => $month, 'rows' => $data]);
    }
}