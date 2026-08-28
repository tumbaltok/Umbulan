<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasVerifiedPhone()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nomor WhatsApp Anda belum diverifikasi.'], 403);
            }

            return redirect()->route('verification.phone.notice');
        }

        return $next($request);
    }
}
