<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;

class PermissionService
{
    /**
     * Grouped and categorized permissions for the Checkbox Matrix.
     */
    public static $categories = [
        'workflow' => [
            'label' => 'قۆناغەکانی ڕێڕەوی مامەڵە (Workflow Steps)',
            'icon' => 'fa-solid fa-diagram-project text-sky-400',
            'permissions' => [
                'transactions.create' => 'تۆمارکردنی مامەڵەی نوێ (Step 1)',
                'transactions.edit' => 'دەستکاریکردنی زانیارییەکانی مامەڵە',
                'transactions.inspect' => 'ئەنجامدانی کەشف و تەخمین (Step 2)',
                'transactions.audit' => 'وردبینیکردن و پەسەندکردن (Step 3)',
                'transactions.return' => 'گەڕاندنەوەی مامەڵەی هەڵە بۆ پێشتر',
                'transactions.pay' => 'تۆمارکردنی وەسڵ و وەرگرتنی پارە (Step 4)',
                'transactions.complete_booklet' => 'دابینکردنی دەفتەر و کالیبرەیشن (Step 5)',
                'transactions.submit' => 'سەپمیت و تۆمارکردنی بۆکسی ئەرشیف (Step 6)',
                'transactions.cancel' => 'پووچەڵکردنەوەی دەفتەر و مامەڵە',
            ]
        ],
        'prints' => [
            'label' => 'چاپی نوسراوە فەرمییەکان و پسوولەکان (Official Prints)',
            'icon' => 'fa-solid fa-print text-amber-400',
            'permissions' => [
                'prints.official_index' => 'دەستگەیشتن بە پەڕەی چاپی نوسراوە فەرمییەکان',
                'prints.data_entry' => 'چاپی فیشەی داتائەنتەری (بەدواداچوون)',
                'prints.inspection' => 'چاپی پسوولەی کەشف و تەخمین',
                'prints.pledge' => 'چاپی فۆڕمی بەڵێننامەی یاسایی',
                'prints.receipt' => 'چاپی پسوولەی وەرگرتنی پارە (۳۷/أ)',
                'prints.inspection_letter' => 'چاپی نوسراوی فەرمی پشکنین',
                'prints.restriction' => 'چاپی نوسراوی دانانی نیشانەی ڕەفتارنەکردن',
                'prints.cancellation' => 'چاپی نوسراوی فەرمی پووچەڵکردنەوە',
                'prints.booklet' => 'چاپی دەفتەری گەشتیاری',
            ]
        ],
        'reports' => [
            'label' => 'ڕاپۆرتە دارایی و ژمێریارییەکان (Reports & Accounting)',
            'icon' => 'fa-solid fa-file-invoice-dollar text-emerald-400',
            'permissions' => [
                'reports.accounting_66' => 'بینین و چاپی ڕاپۆرتی ڕۆژانەی محاسبة ٦٦',
                'reports.accounting_export' => 'داگرتنی داتای محاسبة ٦٦ وەک CSV/Excel',
                'reports.expired_booklets' => 'بینینی لیستی دەفتەرە بەسەرچووەکان',
                'reports.expired_booklets_notices' => 'ئاگادارکردنەوە و ئاماری دەفتەرە بەسەرچووەکان (واتسئەپ و ڤایبەر)',
            ]
        ],
        'system' => [
            'label' => 'بەڕێوەبردنی سیستەم و ئاسایش (Management & System)',
            'icon' => 'fa-solid fa-shield-halved text-indigo-400',
            'permissions' => [
                'users.manage' => 'بەڕێوەبردنی ئەکاونت و دەسەڵاتی فەرمانبەران',
                'settings.pricing' => 'دەستکاریکردنی نرخەکان و پێناسە داراییەکان',
                'settings.lookups' => 'بەڕێوەبردنی پێناسە و لیستەکان (Lookups)',
                'transactions.delete' => 'سڕینەوەی کاتی مامەڵە (Soft Delete)',
                'transactions.restore' => 'گەڕاندنەوەی سەرجەم مامەڵە سڕاوەکان',
                'logs.view' => 'بینینی لۆگی جوڵە و چالاکییەکان (Audit Logs)',
            ]
        ],
    ];

    /**
     * Default baseline permissions for each role.
     */
    public static $roleDefaults = [
        'admin' => ['*'],
        'data_entry' => [
            'transactions.create',
            'transactions.edit',
            'prints.data_entry',
        ],
        'inspector' => [
            'transactions.inspect',
            'prints.inspection',
        ],
        'legal' => [
            'prints.pledge',
            'prints.official_index',
        ],
        'auditor' => [
            'transactions.audit',
            'transactions.return',
            'prints.restriction',
            'prints.cancellation',
            'prints.official_index',
        ],
        'cashier' => [
            'transactions.pay',
            'prints.receipt',
            'prints.inspection_letter',
            'reports.accounting_66',
            'reports.accounting_export',
        ],
        'booklet' => [
            'transactions.complete_booklet',
            'prints.booklet',
            'prints.official_index',
            'reports.expired_booklets',
            'reports.expired_booklets_notices',
        ],
        'archiver' => [
            'transactions.submit',
            'reports.expired_booklets',
            'reports.expired_booklets_notices',
        ],
    ];

    /**
     * System roles list with Kurdish display labels.
     */
    public static $roles = [
        'data_entry' => 'کارمەندی داتا ئەنتەری (Step 1)',
        'inspector' => 'ئەندازیار / تەخمین (Step 2)',
        'legal' => 'فەرمانبەری یاسایی (بەڵێننامە)',
        'auditor' => 'کارمەندی وردبینی (Step 3)',
        'cashier' => 'ژمێریار / وەسڵبڕ (Step 4)',
        'booklet' => 'بەشی دەفتەر (Step 5)',
        'archiver' => 'کارمەندی ئەرشیف و سەپمیت (Step 6)',
    ];

    /**
     * Returns a flat dictionary of permission_key => Kurdish Label.
     */
    public static function getAllPermissions(): array
    {
        $all = [];
        foreach (self::$categories as $cat) {
            foreach ($cat['permissions'] as $k => $v) {
                $all[$k] = $v;
            }
        }
        return $all;
    }

    /**
     * In-memory cache for user permissions during a single request lifecycle.
     */
    protected static array $permissionsCache = [];

    /**
     * Checks if a user has a specific permission (ultra-fast with in-memory memoization).
     */
    public static function userHasPermission(User $user, string $permission): bool
    {
        if ($user->isAdmin() || $user->role === 'admin') {
            return true;
        }

        $cacheKey = $user->id . '_' . $user->role;
        if (!isset(static::$permissionsCache[$cacheKey])) {
            static::$permissionsCache[$cacheKey] = \Illuminate\Support\Facades\Cache::remember(
                "user_perms_{$cacheKey}",
                300,
                function () use ($user) {
                    // 1. Check custom permissions
                    if ($user->custom_permissions !== null) {
                        $userPerms = is_array($user->custom_permissions) 
                            ? $user->custom_permissions 
                            : json_decode($user->custom_permissions, true);
                        if (is_array($userPerms)) {
                            return $userPerms;
                        }
                    }

                    // 2. Check role permissions from database
                    $perms = RolePermission::where('role', $user->role)->pluck('permission_name')->toArray();
                    if (!empty($perms)) {
                        return $perms;
                    }

                    // 3. Fallback to code defaults
                    return self::$roleDefaults[$user->role] ?? [];
                }
            );
        }

        $activePerms = static::$permissionsCache[$cacheKey];
        return in_array($permission, $activePerms) || in_array('*', $activePerms);
    }

    /**
     * Get active permissions array for a role.
     */
    public static function getPermissionsForRole(string $role): array
    {
        if ($role === 'admin') {
            return array_keys(self::getAllPermissions());
        }

        $dbPerms = RolePermission::where('role', $role)->pluck('permission_name')->toArray();
        if (!empty($dbPerms)) {
            return $dbPerms;
        }

        return self::$roleDefaults[$role] ?? [];
    }

    /**
     * Seed or sync database defaults if role_permissions is empty.
     */
    public static function ensureDefaultsSeeded(): void
    {
        if (RolePermission::count() === 0) {
            foreach (self::$roleDefaults as $role => $perms) {
                if ($role === 'admin') continue;
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
}
