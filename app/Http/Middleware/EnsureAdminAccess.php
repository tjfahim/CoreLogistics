<?php
// app/Http/Middleware/EnsureAdminAccess.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Must be active
        if ((int) $user->status !== 1) {
            abort(403, 'Your account is inactive.');
        }

        // Must have one of the admin-panel roles
        $allowedSlugs = ['superadmin', 'admin', 'manager'];

        $hasRole = $user->roleRelation
            && in_array($user->roleRelation->slug, $allowedSlugs, true);

        // Fallback: legacy role == 2
        if (!$hasRole && (int) $user->role === 2) {
            $hasRole = true;
        }

        if (!$hasRole) {
            abort(403, 'You do not have access to the admin panel.');
        }

        return $next($request);
    }
}