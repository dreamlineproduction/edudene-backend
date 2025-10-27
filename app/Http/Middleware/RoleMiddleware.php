<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,$role): Response
    {
        $user =  auth('sanctum')->user();

        if (!$user || $user->role_id != $role) {
            return jsonResponse(false, 'Unauthorized: Access denied.', null, 403);           
        }

        return $next($request);
    }
}
