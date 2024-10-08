<?php

namespace FuryBee\LoggerUi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EnsureUserIsAuthorized
{
    /**
     * Ensures the user is authorized to visit Logger UI Dashboard.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $allowed = app()->environment('local')
            || Gate::allows('viewLoggerUI', [$request->user()]);

        abort_unless($allowed, 403);

        return $next($request);
    }
}
