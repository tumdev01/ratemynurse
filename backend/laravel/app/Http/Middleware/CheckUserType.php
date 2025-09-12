<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserType
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->user_type, $types)) {
            // ถ้าไม่ใช่ SUPERADMIN หรือ ADMIN → abort 403
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
