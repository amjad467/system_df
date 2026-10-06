<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Notify a specific user or an entire role desk
     */
    public static function send(?int $userId, ?string $role, string $title, string $message, ?string $link = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'target_role' => $role,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false,
        ]);
    }

    /**
     * Get unread notifications for current user/role
     */
    public static function getUnreadForCurrentUser()
    {
        if (!auth()->check()) {
            return collect();
        }

        $user = auth()->user();

        return Notification::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('target_role', $user->role)
              ->orWhere('target_role', 'all');
        })->where('is_read', false)->latest()->take(10)->get();
    }
}
