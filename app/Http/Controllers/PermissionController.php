<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RolePermission;
use App\Services\PermissionService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'تەنها سەرپەرشتیار (Admin) دەسەڵاتی گۆڕینی پێرمشن و دەسەڵاتەکانی هەیە.');
        }

        PermissionService::ensureDefaultsSeeded();

        $categories = PermissionService::$categories;
        $roles = PermissionService::$roles;
        
        // Build matrix of [role => [permission_name => true]]
        $rolePermissions = RolePermission::all();
        $matrix = [];
        foreach ($roles as $roleKey => $roleLabel) {
            $matrix[$roleKey] = [];
            $roleDbPerms = $rolePermissions->where('role', $roleKey)->pluck('permission_name')->toArray();
            
            // If DB has no entries for this role yet, fallback to defaults
            if (empty($roleDbPerms) && isset(PermissionService::$roleDefaults[$roleKey])) {
                $roleDbPerms = PermissionService::$roleDefaults[$roleKey];
            }

            foreach ($roleDbPerms as $perm) {
                $matrix[$roleKey][$perm] = true;
            }
        }

        // All active users for the individual user permission configurator
        $users = User::orderBy('name')->get();
        $selectedUserId = $request->get('user_id', $users->first()->id ?? null);
        $selectedUser = $selectedUserId ? User::find($selectedUserId) : null;

        $userEffectivePerms = [];
        if ($selectedUser) {
            if ($selectedUser->custom_permissions !== null) {
                $raw = is_array($selectedUser->custom_permissions) 
                    ? $selectedUser->custom_permissions 
                    : json_decode($selectedUser->custom_permissions, true);
                $userEffectivePerms = is_array($raw) ? array_fill_keys($raw, true) : [];
            } else {
                $rolePerms = PermissionService::getPermissionsForRole($selectedUser->role);
                $userEffectivePerms = array_fill_keys($rolePerms, true);
            }
        }

        return view('settings.permissions', compact(
            'categories',
            'roles',
            'matrix',
            'users',
            'selectedUser',
            'userEffectivePerms'
        ));
    }

    public function updateRolePermissions(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'تەنها ئەدمین دەسەڵاتی دەستکاریکردنی پێرمشنەکانی هەیە.');
        }

        $inputPermissions = $request->input('permissions', []);

        foreach (PermissionService::$roles as $roleKey => $roleLabel) {
            // Delete existing records for this role
            RolePermission::where('role', $roleKey)->delete();

            // Insert new checked permissions
            $rolePerms = isset($inputPermissions[$roleKey]) && is_array($inputPermissions[$roleKey])
                ? array_keys($inputPermissions[$roleKey])
                : [];

            foreach ($rolePerms as $permKey) {
                RolePermission::create([
                    'role' => $roleKey,
                    'permission_name' => $permKey,
                ]);
            }
        }

        AuditLogger::log('دەستکاریکردنی خشتەی پێرمشنەکانی بەشەکان', RolePermission::class, null, 'ئەدمین خشتەی چێک بۆکسی دەسەڵاتی ڕۆڵەکانی نوێکردەوە.');

        return redirect()->route('settings.permissions')->with('success', 'خشتەی پێرمشن و دەسەڵاتەکانی بەشەکان بە سەرکەوتوویی پاشەکەوتکرا.');
    }

    public function updateUserPermissions(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'تەنها ئەدمین دەسەڵاتی دەستکاریکردنی پێرمشنەکانی هەیە.');
        }

        // If admin chooses to reset this user to role defaults:
        if ($request->has('reset_to_role')) {
            $user->custom_permissions = null;
            $user->save();

            AuditLogger::log('گەڕاندنەوەی پێرمشنی فەرمانبەر بۆ باری سەرەکی', User::class, $user->id, "پێرمشنەکانی کارمەند {$user->name} گەڕێنرانەوە بۆ دەسەڵاتی گشتی بەشەکەی ({$user->role})");

            return redirect()->route('settings.permissions', ['user_id' => $user->id])
                ->with('success', "پێرمشنەکانی فەرمانبەر ({$user->name}) گەڕێنرانەوە بۆ پێرمشنی بنەڕەتی بەشەکەی.");
        }

        // Custom checked permissions for this specific user
        $checked = $request->input('user_permissions', []);
        $userPerms = is_array($checked) ? array_keys($checked) : [];

        $user->custom_permissions = $userPerms;
        $user->save();

        AuditLogger::log('دەستکاریکردنی تایبەتی پێرمشنی فەرمانبەر', User::class, $user->id, "پێرمشنی تایبەت بۆ کارمەند {$user->name} تۆمارکرا (" . count($userPerms) . " پێرمشن)");

        return redirect()->route('settings.permissions', ['user_id' => $user->id])
            ->with('success', "پێرمشن و دەسەڵاتە تایبەتەکانی فەرمانبەر ({$user->name}) بە سەرکەوتوویی پاشەکەوتکرا.");
    }

    public function resetDefaults()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'تەنها ئەدمین دەسەڵاتی هەیە.');
        }

        RolePermission::truncate();
        foreach (PermissionService::$roleDefaults as $role => $perms) {
            if ($role === 'admin') continue;
            foreach ($perms as $perm) {
                if ($perm !== '*') {
                    RolePermission::create([
                        'role' => $role,
                        'permission_name' => $perm
                    ]);
                }
            }
        }

        AuditLogger::log('ڕێکخستنەوەی سەرجەم پێرمشنەکان بۆ فەرمی', RolePermission::class, null, 'ئەدمین خشتەی پێرمشنەکانی گەڕاندەوە بۆ دەسەڵاتە فەرمییە بنەڕەتییەکان');

        return redirect()->route('settings.permissions')->with('success', 'سەرجەم دەسەڵاتەکان گەڕێنرانەوە بۆ ڕێکخستنی بنەڕەتی فەرمی.');
    }
}

