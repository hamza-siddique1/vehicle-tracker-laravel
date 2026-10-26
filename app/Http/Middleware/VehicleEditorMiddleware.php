<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Roles allowed to edit a vehicle record: the yard hierarchy plus the NDTC manager.
 */
class VehicleEditorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $allowedRoles = ['ndtc_manager', 'yard_manager', 'vehicle_manager', 'admin'];
        if (! in_array(Auth::user()->role, $allowedRoles)) {
            abort(403);
        }

        return $next($request);
    }
}
