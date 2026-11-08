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
        'custom_permissions',
    ];

    protected $casts = [
        'custom_permissions' => 'array',
        'is_active' => 'boolean',
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
            'archiver' => 'کارمەندی ئەرشیف و سەپمیت (Step 6)',
            'legal' => 'فەرمانبەری یاسایی (Legal Officer)',
            'admin' => 'کارگێڕی گشتی / سەرپەرشتیار (Admin)',
            default => 'کارمەند',
        };
    }

    public function getCleanRoleNameAttribute(): string
    {
        return match($this->role) {
            'data_entry' => 'کارمەندی داتا ئەنتەری',
            'inspector' => 'ئەندازیار / کەشف و تەخمین',
            'auditor' => 'کارمەندی وردبینی',
            'cashier' => 'ژمێریار / وەسڵبڕ',
            'booklet' => 'بەشی دەفتەر',
            'archiver' => 'کارمەندی ئەرشیف',
            'legal' => 'فەرمانبەری یاسایی',
            'admin' => 'سەرپەرشتیاری گشتی',
            default => 'کارمەند',
        };
    }

    public function getCleanNameAttribute(): string
    {
        $name = trim(preg_replace('/\s*\(Admin\)\s*/i', '', $this->name));
        return $name ?: $this->name;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isArchiver(): bool
    {
        return $this->role === 'archiver';
    }

    public function isLegal(): bool
    {
        return $this->role === 'legal';
    }

    public function hasPermission(string $permission): bool
    {
        return \App\Services\PermissionService::userHasPermission($this, $permission);
    }
}

