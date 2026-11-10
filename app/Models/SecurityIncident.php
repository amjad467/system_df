<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityIncident extends Model
{
    protected $fillable = [
        'type',
        'severity',
        'ip_address',
        'user_id',
        'user_name',
        'user_agent',
        'details',
        'payload',
        'is_blocked',
        'blocked_until',
        'is_resolved',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
        'is_resolved' => 'boolean',
        'blocked_until' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function unresolvedCount(): int
    {
        try {
            return static::where('is_resolved', false)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getSeverityBadgeAttribute(): string
    {
        return match ($this->severity) {
            'critical' => '<span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-red-500/15 text-red-400 border border-red-500/30 flex items-center gap-1 w-max"><i class="fa-solid fa-triangle-exclamation"></i> زۆر مەترسیدار</span>',
            'high' => '<span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-500/15 text-amber-400 border border-amber-500/30 flex items-center gap-1 w-max"><i class="fa-solid fa-circle-exclamation"></i> بەرز</span>',
            'medium' => '<span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-yellow-500/15 text-yellow-400 border border-yellow-500/30 flex items-center gap-1 w-max"><i class="fa-solid fa-bell"></i> مامناوەند</span>',
            default => '<span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-sky-500/15 text-sky-400 border border-sky-500/30 flex items-center gap-1 w-max"><i class="fa-solid fa-info-circle"></i> ئاگاداری</span>',
        };
    }

    public function getTypeNameKurdishAttribute(): string
    {
        return match ($this->type) {
            'brute_force' => 'پەلاماری تێپەڕەوشە (Brute-Force)',
            'sqli_attempt' => 'هێرشی دەرزی لێدان (SQL Injection)',
            'xss_attempt' => 'هێرشی سکریپت (XSS Attempt)',
            'off_hours_access' => 'چوونەژوورەوە لە کاتی نائاسایی (Off-Hours)',
            'new_device' => 'ئامێر یان ئایپی نوێ (New Device/IP)',
            'bulk_delete' => 'سڕینەوەی بەکۆمەڵ (Bulk Deletion)',
            'fraud_attempt' => 'گومانی فێڵ لە پارەدان',
            'bot_trap' => 'تەڵەی بۆتی هاککەر (Honeypot)',
            'manual_block' => 'بلۆککردنی دەستی لەلایەن ئەدمین',
            default => 'ڕووداوی ئەمنی',
        };
    }
}
