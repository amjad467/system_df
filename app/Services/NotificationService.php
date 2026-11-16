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
        if ($userId) {
            \Illuminate\Support\Facades\Cache::forget("unread_notifs_user_{$userId}");
        }
        $notification = Notification::create([
            'user_id' => $userId,
            'target_role' => $role,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false,
        ]);

        return $notification;
    }

    /**
     * Get unread notifications for current user/role (cached for 20s to prevent layout query on every request)
     */
    public static function getUnreadForCurrentUser()
    {
        if (!auth()->check()) {
            return collect();
        }

        $user = auth()->user();

        return \Illuminate\Support\Facades\Cache::remember("unread_notifs_user_{$user->id}", 20, function () use ($user) {
            return Notification::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('target_role', $user->role)
                  ->orWhere('target_role', 'all');
                if ($user->isAdmin()) {
                    $q->orWhereNotNull('target_role');
                }
            })->where('is_read', false)->latest()->take(20)->get();
        });
    }

    /**
     * Mark all notifications as read for current user
     */
    public static function markAllAsReadForCurrentUser(): int
    {
        if (!auth()->check()) {
            return 0;
        }

        $user = auth()->user();
        \Illuminate\Support\Facades\Cache::forget("unread_notifs_user_{$user->id}");

        return Notification::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('target_role', $user->role)
              ->orWhere('target_role', 'all');
            if ($user->isAdmin()) {
                $q->orWhereNotNull('target_role');
            }
        })->where('is_read', false)->update(['is_read' => true]);
    }

    /**
     * Mark return / error notifications as read when transaction is fixed or reinspected
     */
    public static function clearReturnNotificationsForTransaction(int $transactionId, ?int $linkedId = null): void
    {
        $ids = array_filter([$transactionId, $linkedId]);
        Notification::where(function ($q) use ($ids) {
            foreach ($ids as $id) {
                $q->orWhere('link', 'like', "%/transactions/{$id}%");
            }
        })->where('is_read', false)
          ->where(function ($q) {
              $q->where('title', 'like', '%گەڕێنراوەتەوە%')
                ->orWhere('title', 'like', '%هەڵوەشاندنەوە%')
                ->orWhere('title', 'like', '%هەڵە%');
          })->update(['is_read' => true]);

        \Illuminate\Support\Facades\Cache::flush();
    }
}
