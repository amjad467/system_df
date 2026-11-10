<?php

namespace App\Http\Middleware;

use App\Services\SecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectMaliciousTraffic
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't scan routes within the security hub itself to allow viewing logs
        if ($request->is('security*')) {
            return $next($request);
        }

        // Scan for malicious SQL Injection / XSS / Bot probes
        if (SecurityService::detectMaliciousPayload($request)) {
            return response()->view('errors.blocked', [
                'ip' => $request->ip(),
                'reason' => 'ڕێگری لە داواکارییەکی زیانبەخش و مەترسیدار کرا لە لایەن قەڵغانی ئەمنی',
                'until' => now()->addMinutes(60)->format('Y-m-d H:i:s'),
            ], 403);
        }

        return $next($request);
    }
}

