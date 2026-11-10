<?php

namespace App\Services;

use App\Models\SecuritySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Send an urgent security alert to the configured Admin Telegram chat.
     */
    public static function sendAlert(string $title, string $details, string $severity = 'high', array $meta = []): bool
    {
        try {
            $token = SecuritySetting::get('telegram_bot_token', env('TELEGRAM_BOT_TOKEN'));
            $chatId = SecuritySetting::get('telegram_chat_id', env('TELEGRAM_CHAT_ID'));
            $enabled = SecuritySetting::get('telegram_alerts_enabled', '1');

            if (!$enabled || empty($token) || empty($chatId)) {
                return false;
            }

            $icon = match ($severity) {
                'critical' => '🚨🚨🚨 *[مەترسی زۆر بەپەلە / CRITICAL]*',
                'high' => '⚠️⚠️ *[مەترسی بەرز / HIGH]*',
                'medium' => '🔔 *[ئاگاداری گرنگ / MEDIUM]*',
                default => 'ℹ️ *[زانیاری ئاسایش / INFO]*',
            };

            $time = now()->format('Y-m-d H:i:s');
            $ip = $meta['ip'] ?? request()->ip();
            $user = $meta['user'] ?? (auth()->user()?->name ?? 'نادیار');

            $message = "{$icon}\n\n";
            $message .= "*سیستەمی دەفتەری ئۆتۆمبێلی گەشتیاری*\n";
            $message .= "🛡 *بابەت:* {$title}\n";
            $message .= "📝 *وردەکاری:* {$details}\n";
            $message .= "👤 *بەکارهێنەر:* `{$user}`\n";
            $message .= "🌐 *ناونیشانی ئایپی:* `{$ip}`\n";
            $message .= "⏰ *کات:* `{$time}`\n";

            if (!empty($meta['action_taken'])) {
                $message .= "🔒 *کرداری ئەنجامدراو:* {$meta['action_taken']}\n";
            }

            return self::sendRawMessage($token, $chatId, $message);
        } catch (\Throwable $e) {
            Log::warning('Telegram Alert failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send raw markdown text message via Telegram Bot API.
     */
    public static function sendRawMessage(string $token, string $chatId, string $text): bool
    {
        try {
            $response = Http::timeout(4)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Telegram sendRawMessage error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test Telegram connection with explicit token and chat ID.
     */
    public static function testConnection(string $token, string $chatId): array
    {
        try {
            $testMsg = "✅ *پەیوەندی تەلەگرام سەرکەوتوو بوو!*\n\nئەمە کورتەنامەیەکی تاقیکردنەوەیە لە سیستەمی بەڕێوەبردنی دەفتەری ئۆتۆمبێلی گەشتیارییەوە (گومرگی سلێمانی).\nلە کاتی هەر ڕووداوێکی ئەمنی، هێرش، یان هەوڵێکی گوماناوی لێرەوە ئاگاداریت پێدەگات.\n⏰ کات: " . now()->format('Y-m-d H:i:s');

            $res = Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $testMsg,
                'parse_mode' => 'Markdown',
            ]);

            if ($res->successful()) {
                return ['success' => true, 'message' => 'نامەی تاقیکردنەوە بە سەرکەوتوویی بۆ تەلەگرامەکەت نێردرا!'];
            }

            $error = $res->json()['description'] ?? 'هەڵەی نەناسراو لە سێرڤەری تەلەگرام';
            return ['success' => false, 'message' => 'تەلەگرام وەڵامی نەدایەوە: ' . $error];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'پەیوەندی دروست نەبوو: ' . $e->getMessage()];
        }
    }
}

