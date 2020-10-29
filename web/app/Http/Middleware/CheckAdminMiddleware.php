<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminMiddleware
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
        $adminUsers = explode(',', env('SUMRA_ADMIN_USERS', ''));
        if(empty($adminUsers) || !in_array($request->header('user-id'), $adminUsers)){
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Access error',
                'message' => "You have not permissions to access"
            ], 403);
        }

        return $next($request);
    }
}
