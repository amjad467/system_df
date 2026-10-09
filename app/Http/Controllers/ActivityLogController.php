<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بینینی تۆماری لۆگەکان تەنها بۆ ئەدمینی سیستەمە!');
        }

        $logs = ActivityLog::latest()->paginate(25);
        return view('logs.index', compact('logs'));
    }
}
