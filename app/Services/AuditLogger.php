<?php

namespace App\Services;

use App\Models\ActivityLog;

class AuditLogger
{
    public static function log(string $action, ?string $modelType = null, ?int $modelId = null, ?string $details = null, $oldValues = null, $newValues = null): void
    {
        $user = auth()->user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'سیستەم / کارمەند',
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'details' => $details,
            'old_values' => is_array($oldValues) ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : $oldValues,
            'new_values' => is_array($newValues) ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

