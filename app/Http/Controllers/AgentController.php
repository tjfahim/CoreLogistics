<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    // Show all agents
    public function index()
    {
        $agents = Agent::all();
        return view('admin.agents.index', compact('agents'));
    }

    // Show create form
    public function create()
    {
        return view('admin.agents.create');
    }

    // Store new agent
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:freight_forwarder,cnf_agent',
            'company_name'  => 'nullable|string|max:255',
            'email'         => 'nullable|email|unique:agents,email',
            'phone'         => 'nullable|string|max:50',
            'address'       => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        Agent::create($validated);

        return redirect()->route('agents.index')->with('success', 'Agent created successfully.');
    }

    // Show edit form
    public function edit($id)
    {
        $agent = Agent::findOrFail($id);
        return view('admin.agents.edit', compact('agent'));
    }

    // Update agent
    public function update(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:freight_forwarder,cnf_agent',
            'company_name'  => 'nullable|string|max:255',
            'email'         => 'nullable|email|unique:agents,email,' . $id,
            'phone'         => 'nullable|string|max:50',
            'address'       => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        $agent->update($validated);

        return redirect()->route('agents.index')->with('success', 'Agent updated successfully.');
    }

    // Delete agent
    public function destroy($id)
    {
        Agent::findOrFail($id)->delete();

        return redirect()->route('agents.index')->with('success', 'Agent deleted successfully.');
    }
}
