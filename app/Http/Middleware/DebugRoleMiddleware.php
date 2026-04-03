<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        Log::info('=== ROLE MIDDLEWARE DEBUG ===', [
            'path' => $request->path(),
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'user_roles' => auth()->user()?->roles->pluck('name')->toArray(),
            'required_roles' => $roles,
        ]);

        foreach ($roles as $role) {
            if (auth()->check() && auth()->user()->hasRole($role)) {
                Log::info('✓ User has required role: ' . $role);
                return $next($request);
            }
        }

        Log::error('✗ User denied - missing required role', [
            'required' => $roles,
            'user_has' => auth()->user()?->roles->pluck('name')->toArray() ?? 'NOT_AUTHENTICATED'
        ]);

        abort(403, 'Unauthorized - Missing required role');
    }
}
