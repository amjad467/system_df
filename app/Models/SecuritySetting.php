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
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value, ?string $description = null): void
    {
        try {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => (string)$value, 'description' => $description]
            );
        } catch (\Throwable $e) {
            // ignore fallback
        }
    }
}

