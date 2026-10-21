<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Office;
use App\Models\SalaryRecord;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('office')->latest()->get();
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $offices = Office::orderBy('name')->get();
        return view('admin.employees.create', compact('offices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'office_id' => 'required|exists:offices,id',
            'salary'    => 'required|numeric|min:0',
        ]);

        SalaryRecord::ensureCurrentMonth();   // freeze this month before changing anything

        $employee = Employee::create($request->only('name', 'office_id', 'salary'));
        $this->syncCurrentMonth($employee);   // a new joiner is paid this month too

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        $offices = Office::orderBy('name')->get();
        return view('admin.employees.edit', compact('employee', 'offices'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'office_id' => 'required|exists:offices,id',
            'salary'    => 'required|numeric|min:0',
        ]);

        SalaryRecord::ensureCurrentMonth();

        $employee->update($request->only('name', 'office_id', 'salary'));
        $this->syncCurrentMonth($employee->fresh());   // correction applies to this month; history stays untouched

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        SalaryRecord::ensureCurrentMonth();   // this month's record stays (they were paid); next months exclude them

        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    private function syncCurrentMonth(Employee $e): void
    {
        $e->loadMissing('office:id,name');
        SalaryRecord::updateOrCreate(
            ['year' => (int) now()->format('Y'), 'month' => (int) now()->format('n'), 'employee_id' => $e->id],
            ['employee_name' => $e->name, 'office_id' => $e->office_id,
             'office_name' => optional($e->office)->name, 'amount' => $e->salary]
        );
    }

    /** Month-by-month salary history: who got paid how much (deleted employees included). */
    public function history(Request $request)
    {
        SalaryRecord::ensureCurrentMonth();

        $months = SalaryRecord::selectRaw('year, month, COUNT(*) as people, SUM(amount) as total')
            ->groupBy('year', 'month')->orderByDesc('year')->orderByDesc('month')->get();

        $year  = (int) $request->input('year',  $months->first()->year  ?? now()->year);
        $month = (int) $request->input('month', $months->first()->month ?? now()->month);

        $records = SalaryRecord::with('employee:id')
            ->where('year', $year)->where('month', $month)
            ->orderBy('office_name')->orderBy('employee_name')->get();

        return view('admin.employees.salary_history', compact('months', 'records', 'year', 'month'));
    }
}
