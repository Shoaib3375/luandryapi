<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(\Illuminate\Http\Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user || !$user->is_admin) {
            return response()->json(['error' => 'Unauthorized — Admins only'], 403);
        }

        return $next($request);
    }
}
