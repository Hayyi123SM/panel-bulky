<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OtpMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('otp_verified') || $request->session()->get('otp_verified') !== true) {
            return redirect()->route('otp.form'); // Arahkan ke halaman input OTP
        }

        return $next($request);
    }
}
