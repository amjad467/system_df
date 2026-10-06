<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\RolePermission;

class PermissionService
{
    public static $permissionsMap = [
        'transactions.create' => 'دروستکردنی مامەڵەی نوێ',
        'transactions.edit' => 'دەستکاریکردنی زانیارییەکانی مامەڵە',
        'transactions.inspect' => 'ئەنجامدانی کەشف و تەخمین',
        'transactions.audit' => 'وردبینیکردن و پەسەندکردنی مامەڵە',
        'transactions.return' => 'گەڕاندنەوەی مامەڵەی هەڵە بۆ تەخمین',
        'transactions.pay' => 'تۆمارکردنی وەسڵ و وەرگرتنی پارە',
        'transactions.complete_booklet' => 'تۆمارکردنی دەفتەر و چاپکردن',
        'transactions.cancel' => 'پووچەڵکردنەوەی مامەڵە',
        'transactions.delete' => 'سڕینەوەی کاتی (Soft Delete) یان ئەرشیف',
        'transactions.restore' => 'گەڕاندنەوەی مامەڵەی سڕدراوە',
        'users.manage' => 'بەڕێوەبردنی ئەکاونت و دەسەڵاتی فەرمانبەران',
        'settings.manage' => 'بەڕێوەبردنی نرخەکان و بەستێنەکانی سیستەم',
        'reports.view' => 'بینینی ڕاپۆرتە گشتییەکانی داهات و ئامار',
    ];

    public static $roleDefaults = [
        'admin' => ['*'],
        'data_entry' => ['transactions.create', 'transactions.edit'],
        'inspector' => ['transactions.create', 'transactions.edit', 'transactions.inspect'],
        'auditor' => ['transactions.audit', 'transactions.return'],
        'cashier' => ['transactions.pay'],
        'booklet' => ['transactions.complete_booklet'],
    ];

    public static function hasPermission(string $role, string $permission): bool
    {
        if ($role === 'admin') {
            return true;
        }

        $allowed = self::$roleDefaults[$role] ?? [];
        if (in_array($permission, $allowed) || in_array('*', $allowed)) {
            return true;
        }

        return RolePermission::where('role', $role)
            ->where('permission_name', $permission)
            ->exists();
    }

    public static function seedDefaults()
    {
        foreach (self::$permissionsMap as $name => $label) {
            Permission::firstOrCreate(['name' => $name], ['label_kurdish' => $label]);
        }

        foreach (self::$roleDefaults as $role => $perms) {
            foreach ($perms as $perm) {
                if ($perm !== '*') {
                    RolePermission::firstOrCreate([
                        'role' => $role,
                        'permission_name' => $perm
                    ]);
                }
            }
        }
    }
}
