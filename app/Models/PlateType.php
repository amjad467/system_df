<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlateType extends Model
{
    protected $guarded = [];

    /**
     * Get clean Kurdish name without Arabic terms in parentheses.
     */
    public function getCleanKurdishNameAttribute(): string
    {
        return trim(preg_replace('/\s*\(.*?\)/', '', $this->name_kurdish ?? ''));
    }

    /**
     * Ensure name_kurdish returns cleaned Kurdish name without Arabic terms.
     */
    public function getNameKurdishAttribute($value): string
    {
        return trim(preg_replace('/\s*\(.*?\)/', '', $value ?? ''));
    }
}
