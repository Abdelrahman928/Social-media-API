<?php

namespace App\Http\Middleware;

use App\interfaces\IMustVerifyMobile;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() ||
            ($request->user() && $request->user()->phone_number &&
            ! $request->user()->hasVerifiedMobile())) {
                return response()->json(['message' => 'Your mobile number is not verified.'], 403);
        }

        return $next($request);
    }
}
