<?php

namespace App\Http\Controllers;

use App\Models\BlockedIp;
use App\Models\SecurityIncident;
use App\Models\SecuritySetting;
use App\Services\AuditLogger;
use App\Services\SecurityService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityHubController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بینینی بەشی ئاسایش و مۆنیتەرینگ تەنها بۆ کارگێڕی گشتییە!');
        }

        $query = SecurityIncident::latest();

        // Filters
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('ip_address', 'like', "%{$s}%")
                  ->orWhere('user_name', 'like', "%{$s}%")
                  ->orWhere('details', 'like', "%{$s}%");
            });
        }

        $incidents = $query->paginate(20)->withQueryString();

        // Quick Overview Stats
        $stats = [
            'unresolved' => SecurityIncident::unresolvedCount(),
            'today_attacks' => SecurityIncident::whereDate('created_at', today())->whereIn('severity', ['high', 'critical'])->count(),
            'blocked_ips_count' => BlockedIp::count(),
            'today_failed_logins' => SecurityIncident::where('type', 'brute_force')->whereDate('created_at', today())->count(),
        ];

        // Active Blocked IPs
        $blockedIps = BlockedIp::latest()->get();

        // Settings
        $settings = [
            'telegram_bot_token' => SecuritySetting::get('telegram_bot_token', env('TELEGRAM_BOT_TOKEN', '')),
            'telegram_chat_id' => SecuritySetting::get('telegram_chat_id', env('TELEGRAM_CHAT_ID', '')),
            'telegram_alerts_enabled' => SecuritySetting::get('telegram_alerts_enabled', '1'),
            'brute_force_threshold' => SecuritySetting::get('brute_force_threshold', '5'),
            'brute_force_lockout_minutes' => SecuritySetting::get('brute_force_lockout_minutes', '30'),
            'off_hours_alerts_enabled' => SecuritySetting::get('off_hours_alerts_enabled', '1'),
            'sql_injection_defense_enabled' => SecuritySetting::get('sql_injection_defense_enabled', '1'),
        ];

        // Server Health Metrics
        $dbPath = database_path('database.sqlite');
        $dbSizeMb = file_exists($dbPath) ? round(filesize($dbPath) / 1024 / 1024, 2) : 0;

        $serverHealth = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'db_driver' => DB::connection()->getDriverName(),
            'db_size' => $dbSizeMb . ' MB',
            'server_time' => now()->format('Y-m-d H:i:s'),
            'transactions_count' => DB::table('transactions')->count(),
            'users_count' => DB::table('users')->count(),
        ];

        return view('security.index', compact('incidents', 'stats', 'blockedIps', 'settings', 'serverHealth'));
    }

    public function resolve(SecurityIncident $incident)
    {
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'دەسەڵاتت نییە!');
        }

        $incident->update([
            'is_resolved' => true,
            'resolved_by' => auth()->user()->name,
            'resolved_at' => now(),
        ]);

        AuditLogger::log('چارەسەرکردنی ڕووداوی ئەمنی', SecurityIncident::class, $incident->id, "ڕووداوی [{$incident->type_name_kurdish}] وەک چارەسەرکراو دیاری کرا");

        return back()->with('success', 'ڕووداوە ئەمنییەکە بە سەرکەوتوویی وەک چارەسەرکراو دیاری کرا.');
    }

    public function resolveAll()
    {
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'دەسەڵاتت نییە!');
        }

        SecurityIncident::where('is_resolved', false)->update([
            'is_resolved' => true,
            'resolved_by' => auth()->user()->name,
            'resolved_at' => now(),
        ]);

        AuditLogger::log('چارەسەرکردنی سەرجەم ڕووداوە ئەمنییەکان', null, null, 'سەرجەم ئاگادارییە ئەمنییە هەڵپەسێردراوەکان چارەسەر کران');

        return back()->with('success', 'سەرجەم ڕووداوە ئەمنییەکان وەک چارەسەرکراو دیاری کران.');
    }

    public function blockIpManual(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'دەسەڵاتت نییە!');
        }

        $request->validate([
            'ip_address' => 'required|string',
            'reason' => 'required|string',
            'duration_hours' => 'nullable|integer',
        ]);

        $ip = trim($request->ip_address);
        $reason = trim($request->reason);
        $durationHours = $request->duration_hours ? (int)$request->duration_hours : null;
        $minutes = $durationHours ? $durationHours * 60 : null;

        SecurityService::blockIp($ip, $reason, $minutes, auth()->user()->name);

        SecurityService::recordIncident(
            type: 'manual_block',
            severity: 'high',
            details: "ئایپی [{$ip}] لە لایەن ئەدمین [".auth()->user()->name."] خرایە لیستی ڕەشەوە. هۆکار: {$reason}",
            isBlocked: true,
            blockedUntil: $minutes ? now()->addMinutes($minutes) : null,
            ip: $ip
        );

        AuditLogger::log('بلۆککردنی ئایپی لە لایەن ئەدمین', null, null, "ئایپی {$ip} بلۆک کرا: {$reason}");

        return back()->with('success', "ناونیشانی ئایپی [{$ip}] بە سەرکەوتوویی خرایە لیستی ڕەش و بلۆک کرا.");
    }

    public function unblockIp(BlockedIp $blockedIp)
    {
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'دەسەڵاتت نییە!');
        }

        $ip = $blockedIp->ip_address;
        $blockedIp->delete();

        AuditLogger::log('لابردنی بلۆکی ئایپی', null, null, "ئایپی {$ip} لە لیستی ڕەش دەرهێنرا");

        return back()->with('success', "ناونیشانی ئایپی [{$ip}] بە سەرکەوتوویی لە بلۆک دەرهێنرا.");
    }

    public function updateSettings(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'دەسەڵاتت نییە!');
        }

        SecuritySetting::set('telegram_bot_token', trim((string)$request->input('telegram_bot_token', '')), 'کلیلی بۆتی تەلەگرام');
        SecuritySetting::set('telegram_chat_id', trim((string)$request->input('telegram_chat_id', '')), 'ناسنامەی چاتی تەلەگرام');
        SecuritySetting::set('telegram_alerts_enabled', $request->has('telegram_alerts_enabled') ? '1' : '0', 'کارابوونی ئاگاداری تەلەگرام');
        SecuritySetting::set('brute_force_threshold', max(3, (int)$request->input('brute_force_threshold', 5)), 'سنووری پاسوۆردی هەڵە');
        SecuritySetting::set('brute_force_lockout_minutes', max(5, (int)$request->input('brute_force_lockout_minutes', 30)), 'ماوەی بلۆک بە خولەک');
        SecuritySetting::set('off_hours_alerts_enabled', $request->has('off_hours_alerts_enabled') ? '1' : '0', 'ئاگاداری کاتی نائاسایی');
        SecuritySetting::set('sql_injection_defense_enabled', $request->has('sql_injection_defense_enabled') ? '1' : '0', 'بەرگری لە SQLi و XSS');

        AuditLogger::log('نوێکردنەوەی ڕێکخستنە ئەمنییەکان', null, null, 'ڕێکخستنی تەلەگرام و قەڵغانی ئەمنی نوێکرایەوە');

        return back()->with('success', 'ڕێکخستنە ئەمنییەکان و ئاگادارییەکانی تەلەگرام بە سەرکەوتوویی نوێکرانەوە.');
    }

    public function testTelegram(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'ڕێگەپێدراو نییە.'], 403);
        }

        $token = trim((string)$request->input('telegram_bot_token'));
        $chatId = trim((string)$request->input('telegram_chat_id'));

        if (empty($token) || empty($chatId)) {
            return response()->json(['success' => false, 'message' => 'تکایە سەرەتا Token و Chat ID پڕبکەرەوە!'], 422);
        }

        $result = TelegramService::testConnection($token, $chatId);
        return response()->json($result);
    }
}

