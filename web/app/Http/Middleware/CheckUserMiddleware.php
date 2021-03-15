<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check exist user-id and it not null
        if (empty($request->header('user-id', null))) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Auth error',
                'message' => 'Unauthorized access'
            ], 401);
        }

        return $next($request);
    }
}
