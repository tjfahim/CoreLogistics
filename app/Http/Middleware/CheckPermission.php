<?php
// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Usage:
     *   ->middleware('permission:view-agents')
     *   ->middleware('permission:add-agents,edit-agents')  // any-of
     *
     * @param  string  ...$permissions  One or more permission slugs.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        // Not logged in
        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin bypass (also handled by Gate::before, but keep it here for safety)
        if ($user->roleRelation && $user->roleRelation->slug === 'superadmin') {
            return $next($request);
        }

        // Legacy admin flag
        if ((int) $user->role === 2) {
            return $next($request);
        }

        // No permissions passed → just require login
        if (empty($permissions)) {
            return $next($request);
        }

        // Pass if user has ANY of the given permissions
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        // Denied
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'error' => 'You do not have permission to perform this action.',
            ], 403);
        }

        abort(403, 'You do not have permission to perform this action.');
    }
}