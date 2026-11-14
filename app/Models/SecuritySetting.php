<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return \Illuminate\Support\Facades\Cache::remember("sec_setting_{$key}", 300, function () use ($key, $default) {
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value, ?string $description = null): void
    {
        try {
            \Illuminate\Support\Facades\Cache::forget("sec_setting_{$key}");
            static::updateOrCreate(
                ['key' => $key],
                ['value' => (string)$value, 'description' => $description]
            );
        } catch (\Throwable $e) {
            // ignore fallback
        }
    }
}

