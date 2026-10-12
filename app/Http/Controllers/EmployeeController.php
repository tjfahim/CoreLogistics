<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Office;
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

        Employee::create($request->only('name', 'office_id', 'salary'));

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

        $employee->update($request->only('name', 'office_id', 'salary'));

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}