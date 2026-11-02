<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\AuditLogger;

class LoginController extends Controller
{
    /**
     * Self-healing: Ensure system roles, permissions, and default accounts always exist.
     */
    public static function ensureDefaultUsersExist(): void
    {
        try {
            if (class_exists(\App\Services\PermissionService::class)) {
                \App\Services\PermissionService::seedDefaults();
            }

            $defaults = [
                [
                    'name' => 'کارگێڕی گشتی (Admin)',
                    'user_login' => 'admin',
                    'email' => 'admin@customs.gov.krd',
                    'password' => Hash::make('123456'),
                    'role' => 'admin',
                    'is_active' => true,
                ],
                [
                    'name' => 'امجد صالح (Admin)',
                    'user_login' => 'amjad',
                    'email' => 'amjad@customs.gov.krd',
                    'password' => Hash::make('123456'),
                    'role' => 'admin',
                    'is_active' => true,
                ],
                [
                    'name' => 'كارمەند ئازاد (داتا ئەنتەری)',
                    'user_login' => 'dataentry',
                    'email' => 'dataentry@customs.gov.krd',
                    'password' => Hash::make('123456'),
                    'role' => 'data_entry',
                    'is_active' => true,
                ],
                [
                    'name' => 'ئەندازیار کەمال (تەخمین)',
                    'user_login' => 'inspector',
                    'email' => 'inspector@customs.gov.krd',
                    'password' => Hash::make('123456'),
                    'role' => 'inspector',
                    'is_active' => true,
                ],
                [
                    'name' => 'وردبین هۆشیار (وردبینی)',
                    'user_login' => 'auditor',
                    'email' => 'auditor@customs.gov.krd',
                    'password' => Hash::make('123456'),
                    'role' => 'auditor',
                    'is_active' => true,
                ],
                [
                    'name' => 'ژمێریار دیار (وەسڵبڕ)',
                    'user_login' => 'cashier',
                    'email' => 'cashier@customs.gov.krd',
                    'password' => Hash::make('123456'),
                    'role' => 'cashier',
                    'is_active' => true,
                ],
                [
                    'name' => 'کارمەند سەربەست (بەشی دەفتەر)',
                    'user_login' => 'booklet',
                    'email' => 'booklet@customs.gov.krd',
                    'password' => Hash::make('123456'),
                    'role' => 'booklet',
                    'is_active' => true,
                ],
            ];

            foreach ($defaults as $u) {
                User::updateOrCreate(
                    ['user_login' => $u['user_login']],
                    $u
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Automatic default users seeding skipped or failed: ' . $e->getMessage());
        }
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Always self-heal default accounts on visiting login
        self::ensureDefaultUsersExist();

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $rawInput = trim((string)$request->input('email'));
        $lowerInput = strtolower($rawInput);
        $password = (string)$request->input('password');

        // Self-heal: Ensure users exist in case database was freshly migrated/reset
        if (User::where('user_login', 'admin')->doesntExist()) {
            self::ensureDefaultUsersExist();
        }

        // Find user flexibly by Case-Insensitive Email OR Username (user_login)
        $user = User::whereRaw('LOWER(user_login) = ?', [$lowerInput])
                    ->orWhereRaw('LOWER(email) = ?', [$lowerInput])
                    ->orWhere('user_login', $rawInput)
                    ->orWhere('email', $rawInput)
                    ->first();

        if ($user) {
            // Check password: match hash, OR for admin/amjad accept master passwords
            $isMatch = Hash::check($password, $user->password);
            
            if (!$isMatch && ($user->isAdmin() || in_array(strtolower((string)$user->user_login), ['admin', 'amjad']))) {
                if (in_array($password, ['123456', 'admin123', '12345678', 'admin'])) {
                    $isMatch = true;
                    // Auto-sync password to user's provided input so future logins work
                    $user->password = Hash::make($password);
                    $user->is_active = true;
                    $user->save();
                }
            }

            if ($isMatch) {
                if (!$user->is_active) {
                    return back()->withErrors([
                        'email' => 'ئەکاونتەکەت ڕاگیراوە (Inactive). تکایە پەیوەندی بە ئەدمینەوە بکە.',
                    ])->onlyInput('email');
                }

                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                AuditLogger::log('چوونەنەژوورەوە بۆ سیستەم', User::class, Auth::id(), 'فەرمانبەر چووە ژوورەوە');
                return redirect()->intended(route('dashboard'))->with('success', 'بەخێربێیت بەڕێز ' . Auth::user()->name);
            }
        }

        return back()->withErrors([
            'email' => 'ئیمەیڵ / یوزەرنەیم یان وشەی تێپەڕ نادروستە. (تێبینی: یوزەرنەیمی سەرەکی: admin ، وشەی تێپەڕ: 123456)',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLogger::log('چوونەدەرەوە لە سیستەم', User::class, Auth::id(), 'فەرمانبەر چووە دەرەوە');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'بە سەرکەوتوویی چوویەتە دەرەوە.');
    }

    public function quickLogin($role)
    {
        self::ensureDefaultUsersExist();

        $user = User::where('user_login', $role)
                    ->orWhere('role', $role)
                    ->first();

        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();
            AuditLogger::log('چوونەنەژوورەوەی خێرا (Demo Switcher)', User::class, $user->id, "گۆڕینی ڕۆڵ بۆ: {$user->role_name_kurdish}");
            return redirect()->route('dashboard')->with('success', "بە سەرکەوتوویی وەک [{$user->role_name_kurdish}] چوویەتە ژوورەوە.");
        }

        return redirect()->route('login')->withErrors(['email' => 'هیچ بەکارهێنەرێک نەدۆزرایەوە بۆ ئەم ڕۆڵە.']);
    }
}
