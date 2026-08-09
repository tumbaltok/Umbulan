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
        
        if ($user && !$user->phone_verified_at) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nomor telepon Anda belum diverifikasi.'], 403);
            }
            return redirect()->route('account.index')->with('error', 'Silakan verifikasi nomor telepon Anda terlebih dahulu.');
        }

        return $next($request);
    }
}