<?php

namespace App\Http\Controllers;

use App\Models\CostType;
use Illuminate\Http\Request;

class CostTypeController extends Controller
{
    public function index()
    {
        $costTypes = CostType::latest()->get();
        return view('admin.cost_types.index', compact('costTypes'));
    }

    public function create()
    {
        return view('admin.cost_types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:fixed,per_kg,other',
            'have_agent' => 'required|boolean',
            'have_partial_payment' => 'required|boolean',
        ]);

        CostType::create($request->all());

        return redirect()->route('cost-types.index')->with('success', 'Cost type created successfully.');
    }

    public function edit($id)
    {
        $costType = CostType::findOrFail($id);
        return view('admin.cost_types.edit', compact('costType'));
    }

    public function update(Request $request, $id)
    {
        $costType = CostType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:fixed,per_kg,other',
            'have_agent' => 'required|boolean',
            'have_partial_payment' => 'required|boolean',
        ]);

        $costType->update($request->all());

        return redirect()->route('cost-types.index')->with('success', 'Cost type updated successfully.');
    }

    public function destroy($id)
    {
        $costType = CostType::findOrFail($id);
        $costType->delete();

        return redirect()->route('cost-types.index')->with('success', 'Cost type deleted successfully.');
    }
}
