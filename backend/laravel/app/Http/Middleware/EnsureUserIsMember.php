<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsMember
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->user_type === 'MEMBER') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized. restrict access only.'], 403);
    }
}
