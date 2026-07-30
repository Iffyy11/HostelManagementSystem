<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            return redirect()->route('login');
        }

        if (! $user->hasAnyRole($roles)) {
            return redirect($user->dashboardRoute())
                ->with('error', 'You do not have permission to access that area.');
        }

        return $next($request);
    }
}
