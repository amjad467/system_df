<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Director extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getActiveDirector(): ?self
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('directors', 'is_active')) {
                return static::where('is_active', true)->first() ?? static::first();
            }
        } catch (\Throwable $e) {}

        return static::first();
    }
}
