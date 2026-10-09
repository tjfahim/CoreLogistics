<?php
// app/Http/Controllers/Admin/PermissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::withCount('roles');

        if ($request->filled('group')) {
            $query->where('group_name', $request->group);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        $permissions = $query->orderBy('group_name')->orderBy('name')
            ->paginate(20)->withQueryString();

        $groupNames  = Permission::getGroupNames();
        $groupCounts = Permission::selectRaw('group_name, COUNT(*) as total')
            ->groupBy('group_name')
            ->pluck('total', 'group_name');

        return view('admin.permissions.index', compact('permissions', 'groupNames', 'groupCounts'));
    }

    public function create()
    {
        $groupNames = Permission::getGroupNames();
        return view('admin.permissions.create', compact('groupNames'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:permissions,slug',
            'group_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Permission::create([
            'name'       => $request->name,
            'slug'       => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
            'group_name' => $request->group_name,
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission)
    {
        $permission->load('roles');
        return view('admin.permissions.show', compact('permission'));
    }

    public function edit(Permission $permission)
    {
        $groupNames = Permission::getGroupNames();
        return view('admin.permissions.edit', compact('permission', 'groupNames'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:permissions,slug,' . $permission->id,
            'group_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $permission->update([
            'name'       => $request->name,
            'slug'       => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
            'group_name' => $request->group_name,
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }

    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_name'         => 'required|string|max:255',
            'permissions'        => 'required|array|min:1',
            'permissions.*.name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $created = 0;
        $skipped = 0;

        foreach ($request->permissions as $item) {
            $name = trim($item['name'] ?? '');
            if ($name === '') continue;

            $slug = Str::slug($name);

            if (Permission::where('slug', $slug)->exists()) {
                $skipped++;
                continue;
            }

            Permission::create([
                'name'       => $name,
                'slug'       => $slug,
                'group_name' => $request->group_name,
            ]);
            $created++;
        }

        $msg = "{$created} permission(s) created.";
        if ($skipped > 0) {
            $msg .= " {$skipped} skipped (duplicate slug).";
        }

        return redirect()->route('permissions.index')
            ->with('success', $msg);
    }
}