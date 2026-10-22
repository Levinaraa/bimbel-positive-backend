<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Cek apakah user login dan memiliki role yang sesuai (misal: 'guru')
        if (!$request->user() || $request->user()->role !== $role) {
            return response()->json(['message' => 'Akses ditolak. Area khusus ' . $role], 403);
        }

        return $next($request);
    }
}
