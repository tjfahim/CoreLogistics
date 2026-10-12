<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::latest()->get();
        return view('admin.offices.index', compact('offices'));
    }

    public function create() { return view('admin.offices.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        Office::create($request->only('name', 'location'));

        return redirect()->route('offices.index')->with('success', 'Office created successfully.');
    }

    public function edit(Office $office) { return view('admin.offices.edit', compact('office')); }

    public function update(Request $request, Office $office)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $office->update($request->only('name', 'location'));

        return redirect()->route('offices.index')->with('success', 'Office updated successfully.');
    }

    public function destroy(Office $office)
    {
        $office->delete();
        return redirect()->route('offices.index')->with('success', 'Office deleted successfully.');
    }
}