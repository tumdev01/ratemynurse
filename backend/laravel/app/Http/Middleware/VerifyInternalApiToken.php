<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyInternalApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Internal-Token');

        if ($token !== config('services.internal.token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
