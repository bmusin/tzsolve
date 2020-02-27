<?php

namespace App\Http\Middleware;

use App\Http\Controllers\FeedbackController as FC;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($guard === 'client' && Auth::guard($guard)->check()) {
            return redirect()->route('feedbacks.create');
        }
        if ($guard === 'web' && Auth::guard($guard)->check()) {
            return redirect()->route('requests');
        }
        return $next($request);
    }
}
