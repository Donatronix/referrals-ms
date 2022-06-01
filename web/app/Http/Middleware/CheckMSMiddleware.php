<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMSMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $microservices = explode(',', env('SUMRA_MS', ''));

        if (empty($microservices) || !in_array($request->header('app-id', null), $microservices)) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Access error',
                'message' => "You have not permissions to access",
            ], 403);
        }

        return $next($request);
    }
}
