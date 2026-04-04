<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = $guards === [] ? [null] : $guards;

        foreach ($guards as $guard) {
            if (! Auth::guard($guard)->guest()) {
                Auth::shouldUse($guard);

                return $next($request);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        }

        return redirect()->guest('login');
    }
}
