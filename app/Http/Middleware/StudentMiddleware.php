<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class StudentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->is_admin == 0) {
            return $next($request);
        }

        return redirect('/');
    }
}
