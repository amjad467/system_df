<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'user_login',
        'email',
        'password',
        'role',
        'is_active',
        'traffic_directorate_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function trafficDirectorate(): BelongsTo
    {
        return $this->belongsTo(TrafficDirectorate::class, 'traffic_directorate_id');
    }

    public function getRoleNameKurdishAttribute(): string
    {
        return match($this->role) {
            'data_entry' => 'کارمەندی داتا ئەنتەری (Step 1)',
            'inspector' => 'ئەندازیار / کەشف و تەخمین (Step 2)',
            'auditor' => 'کارمەندی وردبینی (Step 3)',
            'cashier' => 'ژمێریار / وەسڵبڕ (Step 4)',
            'booklet' => 'بەشی دەفتەر (Step 5)',
            'admin' => 'کارگێڕی گشتی / سەرپەرشتیار (Admin)',
            default => 'کارمەند',
        };
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasPermission(string $permission): bool
    {
        return \App\Services\PermissionService::hasPermission($this->role, $permission);
    }
}

