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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed  ...$roles  Role-role yang diperbolehkan
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Jika tidak login atau role tidak sesuai, batalkan akses
        if (!$user || !in_array($user->role, $roles)) {
            abort(403, 'Unauthorized'); // Bisa diganti redirect('/dashboard')->with('error', 'Access denied')
        }

        return $next($request);
    }
}