<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::latest()->paginate(25);
        return view('logs.index', compact('logs'));
    }
}
