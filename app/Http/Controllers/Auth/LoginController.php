<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\AuditLogger;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $loginInput = trim($request->input('email'));
        $password = $request->input('password');

        // Find user flexibly by either Email OR Username (user_login)
        $user = User::where('email', $loginInput)
                    ->orWhere('user_login', $loginInput)
                    ->first();

        if ($user && Hash::check($password, $user->password)) {
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

        return back()->withErrors([
            'email' => 'ئیمەیڵ / یوزەرنەیم یان وشەی تێپەڕ نادروستە.',
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
        $user = User::where('role', $role)->first();
        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();
            AuditLogger::log('چوونەنەژوورەوەی خێرا (Demo Switcher)', User::class, $user->id, "گۆڕینی ڕۆڵ بۆ: {$user->role_name_kurdish}");
            return redirect()->route('dashboard')->with('success', "بە سەرکەوتوویی وەک [{$user->role_name_kurdish}] چوویەتە ژوورەوە.");
        }

        return redirect()->back()->with('error', 'هیچ بەکارهێنەرێک نەدۆزرایەوە بۆ ئەم ڕۆڵە.');
    }
}
