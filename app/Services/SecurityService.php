<?php

namespace App\Services;

use App\Models\BlockedIp;
use App\Models\SecurityIncident;
use App\Models\SecuritySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityService
{
    /**
     * Record a security incident and trigger real-time alerts.
     */
    public static function recordIncident(
        string $type,
        string $severity,
        string $details,
        ?string $payload = null,
        bool $isBlocked = false,
        ?\DateTimeInterface $blockedUntil = null,
        ?int $userId = null,
        ?string $userName = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): SecurityIncident {
        $ip = $ip ?? request()->ip();
        $userAgent = $userAgent ?? request()->userAgent();
        $user = auth()->user();
        $userId = $userId ?? $user?->id;
        $userName = $userName ?? $user?->name;

        $incident = SecurityIncident::create([
            'type' => $type,
            'severity' => $severity,
            'ip_address' => $ip,
            'user_id' => $userId,
            'user_name' => $userName,
            'user_agent' => $userAgent,
            'details' => $details,
            'payload' => $payload,
            'is_blocked' => $isBlocked,
            'blocked_until' => $blockedUntil,
            'is_resolved' => false,
        ]);

        // Send instant Telegram alert for high & critical events or off-hours
        if (in_array($severity, ['high', 'critical']) || $type === 'off_hours_access') {
            TelegramService::sendAlert(
                $incident->type_name_kurdish,
                $details,
                $severity,
                [
                    'ip' => $ip,
                    'user' => $userName ?? 'نەناسراو',
                    'action_taken' => $isBlocked ? "ئایپییەکە هەتا کاتی {$blockedUntil?->format('H:i:s')} بلۆک کرا" : "تۆمارکرا لە بەشی مۆنیتەرینگ",
                ]
            );
        }

        return $incident;
    }

    /**
     * Track failed login attempts and enforce Brute-Force defense.
     */
    public static function handleFailedLogin(string $ip, ?string $username = null): int
    {
        $cacheKey = "sec_failed_login_" . md5($ip);
        $attempts = (int)Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, now()->addMinutes(15));

        $threshold = (int)SecuritySetting::get('brute_force_threshold', 5);
        $lockoutMinutes = (int)SecuritySetting::get('brute_force_lockout_minutes', 30);

        if ($attempts >= $threshold) {
            $blockedUntil = now()->addMinutes($lockoutMinutes);
            self::blockIp($ip, "تێپەڕاندنی سنووری هەوڵەکانی پاسوۆرد ({$attempts} هەوڵی هەڵە)", $lockoutMinutes, 'سیستەمی بەرگری ئۆتۆماتیکی');

            self::recordIncident(
                type: 'brute_force',
                severity: 'critical',
                details: "هێرشی ئەگەریی تێپەڕەوشە (Brute-Force) بۆ سەر ناوی بەکارهێنەر: [{$username}]. بەهۆی لێدانی {$attempts} پاسوۆردی هەڵە لە ماوەیەکی کەمدا.",
                payload: "Username attempted: {$username}",
                isBlocked: true,
                blockedUntil: $blockedUntil,
                ip: $ip
            );

            // Reset attempts so lockout timer takes over
            Cache::forget($cacheKey);
        } else {
            self::recordIncident(
                type: 'brute_force',
                severity: 'medium',
                details: "هەوڵێکی هەڵەی چوونەژوورەوە تۆمارکرا ({$attempts} لە {$threshold}) بۆ بەکارهێنەر: [{$username}]",
                payload: "Username: {$username}",
                isBlocked: false,
                ip: $ip
            );
        }

        return $attempts;
    }

    /**
     * Inspect successful logins for off-hours access and new devices.
     */
    public static function handleSuccessfulLogin($user, Request $request): void
    {
        $ip = $request->ip();
        Cache::forget("sec_failed_login_" . md5($ip));

        // 1. Check Off-Hours Access (e.g. between 19:00 and 07:00, or Friday)
        $hour = (int)now()->format('H');
        $isFriday = now()->isFriday();
        $isOffHours = ($hour < 7 || $hour >= 19 || $isFriday);

        if ($isOffHours && SecuritySetting::get('off_hours_alerts_enabled', '1') === '1') {
            $timeReason = $isFriday ? 'لە ڕۆژی هەینیدا' : "لە کاتژمێر نائاسایی شەوانە (" . now()->format('H:i') . ")";
            self::recordIncident(
                type: 'off_hours_access',
                severity: 'medium',
                details: "کارمەند [{$user->name}] {$timeReason} لە دەرەوەی کاتی فەرمی دەوام چووەتە ناو سیستمەکە.",
                payload: "Login time: " . now()->toDateTimeString(),
                userId: $user->id,
                userName: $user->name,
                ip: $ip
            );
        }

        // 2. Check New Device / IP
        $lastIp = Cache::get("user_last_ip_{$user->id}");
        if ($lastIp && $lastIp !== $ip) {
            self::recordIncident(
                type: 'new_device',
                severity: 'low',
                details: "کارمەند [{$user->name}] لە ناونیشانی ئایپی نوێوە چووەتە ژوورەوە. (ئایپی کۆن: {$lastIp} ⬅️ ئایپی نوێ: {$ip})",
                userId: $user->id,
                userName: $user->name,
                ip: $ip
            );
        }
        Cache::forever("user_last_ip_{$user->id}", $ip);
    }

    /**
     * Scan incoming HTTP request for SQL Injection and XSS attack patterns.
     */
    public static function detectMaliciousPayload(Request $request): bool
    {
        if (SecuritySetting::get('sql_injection_defense_enabled', '1') === '0') {
            return false;
        }

        $patterns = [
            'union\s+select' => 'SQLi (UNION SELECT)',
            'select\s+.*\s+from\s+sqlite_master' => 'SQLi (Metadata Probe)',
            'select\s+.*\s+from\s+information_schema' => 'SQLi (Information Schema)',
            "'\s*or\s+'?1'?\s*=\s*'?1" => 'SQLi (OR 1=1 Bypass)',
            '--\s*$' => 'SQLi Commenting',
            ';\s*drop\s+table' => 'SQLi Destructive DROP',
            '<script[\s\S]*?>[\s\S]*?<\/script>' => 'XSS Script Injection',
            'javascript\s*:' => 'XSS Javascript URI',
            '\.\.\/\.\.\/' => 'Path Traversal Attack',
            '\/etc\/passwd' => 'LFI / System File Probe',
        ];

        // Gather all inputs except passwords
        $inputs = $request->except(['password', 'password_confirmation', '_token']);
        $rawQuery = $request->getQueryString() ?? '';
        $path = $request->path();

        $contentToScan = json_encode($inputs, JSON_UNESCAPED_UNICODE) . ' ' . $rawQuery . ' ' . $path;

        foreach ($patterns as $pattern => $name) {
            if (preg_match('/' . $pattern . '/i', $contentToScan, $matches)) {
                $ip = $request->ip();
                $matchedText = $matches[0] ?? $name;

                // Ban IP for 60 minutes
                self::blockIp($ip, "پەلاماری زیانبەخشی {$name}", 60, 'سیستەمی قەڵغانی ئەمنی');

                self::recordIncident(
                    type: 'sqli_attempt',
                    severity: 'critical',
                    details: "هێرشێکی گوماناوی و زیانبەخش دەستنیشان کرا: [{$name}] لە لایەن ئەم ئایپییەوە. سیستەمەکە ڕێگری لێکرد و ئایپییەکەی قوفڵ کرد.",
                    payload: "Matched Pattern: {$matchedText}\nURI: {$request->fullUrl()}",
                    isBlocked: true,
                    blockedUntil: now()->addMinutes(60),
                    ip: $ip
                );

                return true;
            }
        }

        return false;
    }

    /**
     * Manually or automatically block an IP.
     */
    public static function blockIp(string $ip, string $reason, ?int $minutes = null, ?string $blockedBy = null): void
    {
        $blockedUntil = $minutes ? now()->addMinutes($minutes) : null;
        BlockedIp::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'blocked_until' => $blockedUntil,
                'blocked_by' => $blockedBy ?? (auth()->user()?->name ?? 'سیستەم'),
            ]
        );
    }

    /**
     * Unblock an IP address.
     */
    public static function unblockIp(string $ip): void
    {
        BlockedIp::where('ip_address', $ip)->delete();
    }
}
