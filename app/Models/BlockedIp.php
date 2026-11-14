<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'reason',
        'blocked_until',
        'blocked_by',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
    ];

    /**
     * Check if an IP address is actively blocked right now (cached for high performance).
     */
    public static function isBlocked(string $ip): bool
    {
        try {
            return (bool) \Illuminate\Support\Facades\Cache::remember("ip_is_blocked_{$ip}", 60, function () use ($ip) {
                $record = static::where('ip_address', $ip)->first();
                if (!$record) {
                    return false;
                }

                // If permanent block (blocked_until is null) or time is in future
                if (is_null($record->blocked_until) || $record->blocked_until->isFuture()) {
                    return true;
                }

                // Expired temporary block, clean it up
                $record->delete();
                return false;
            });
        } catch (\Throwable $e) {
            return false;
        }
    }
}

