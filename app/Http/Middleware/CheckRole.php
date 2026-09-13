<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  <-- Notice the three dots (variadic operator)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Check if the user is authenticated
        // 2. Check if the user's role exists inside the allowed roles array

        if (!auth()->check() || !in_array(auth()->user()->role, $roles)) {
            return response()->json([
                'message' => 'Unauthorized. Required role: ' . implode(' or ', $roles)
            ], 403);
        }

        return $next($request);
    }
}
