<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TrafficDirectorate;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('trafficDirectorate')->latest()->get();
        $directorates = TrafficDirectorate::all();
        return view('users.index', compact('users', 'directorates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_login' => 'required|string|max:100|unique:users,user_login',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|string',
            'traffic_directorate_id' => 'nullable|exists:traffic_directorates,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'user_login' => $validated['user_login'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'traffic_directorate_id' => $validated['traffic_directorate_id'] ?? null,
            'is_active' => true,
        ]);

        AuditLogger::log('دروستکردنی بەکارهێنەری نوێ', User::class, $user->id, "ناوی کارمەند: {$user->name} - دەسەڵات: {$user->role}");

        return redirect()->back()->with('success', 'بەکار‌هێنەر/فەرمانبەری نوێ بە سەرکەوتوویی دروستکرا.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_login' => 'nullable|string|max:100|unique:users,user_login,' . $user->id,
            'email' => 'required|string|unique:users,email,' . $user->id,
            'role' => 'required|string',
            'traffic_directorate_id' => 'nullable|exists:traffic_directorates,id',
            'password' => 'nullable|min:6',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'traffic_directorate_id' => $validated['traffic_directorate_id'] ?? null,
        ];

        if (!empty($validated['user_login'])) {
            $data['user_login'] = $validated['user_login'];
        }

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        AuditLogger::log('دەستکاریکردنی بەکارهێنەر', User::class, $user->id, "ناوی کارمەند: {$user->name}");

        return redirect()->back()->with('success', 'زانیاری و پاسوۆردی بەکارهێنەر بە سەرکەوتوویی نوێکرایەوە.');
    }

    public function changeMyPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'new_password.confirmed' => 'پاسوۆردە نوێییەکە لەگەڵ دووبارەکردنەوەکەیدا یەکناگرێتەوە!',
            'new_password.min' => 'پاسوۆردی نوێ دەبێت لانیکەم ٦ پیت یان ژمارە بێت.'
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return redirect()->back()->with('error', 'پاسوۆردی ئێستات هەڵەیە!');
        }

        auth()->user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        AuditLogger::log('گۆڕینی پاسوۆردی کەسی', User::class, auth()->id(), "بەکارهێنەر پاسوۆردی خۆی گۆڕی");

        return redirect()->back()->with('success', 'پاسوۆردەکەت بە سەرکەوتوویی گۆڕدرا.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        AuditLogger::log('گۆڕینی باری بەکارهێنەر', User::class, $user->id, "ناوی کارمەند: {$user->name}");
        return redirect()->back()->with('success', 'باری بەکار‌هێنەر دەستکاری کرا.');
    }
}

