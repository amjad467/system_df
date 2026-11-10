<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if (BlockedIp::isBlocked($ip)) {
            $record = BlockedIp::where('ip_address', $ip)->first();
            $reason = $record?->reason ?? 'چالاکی گوماناوی';
            $until = $record?->blocked_until ? $record->blocked_until->format('Y-m-d H:i:s') : 'هەمیشەیی';

            return response()->view('errors.blocked', [
                'ip' => $ip,
                'reason' => $reason,
                'until' => $until,
            ], 403);
        }

        return $next($request);
    }
}
