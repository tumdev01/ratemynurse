<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsNursing
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->user_type === 'NURSING') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized. restrict access only.'], 403);
    }
}
