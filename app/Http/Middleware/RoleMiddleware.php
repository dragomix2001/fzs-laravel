<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        $user = $request->user();
        
        if (!in_array($user->role, $roles)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect('/')->with('error', 'Немате приступ овој страници.');
        }

        return $next($request);
    }
}
