<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بینینی تۆماری لۆگەکان تەنها بۆ ئەدمینی سیستەمە!');
        }

        $query = ActivityLog::latest();

        // 1. Filter by User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 2. Filter by Action Type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 3. Filter by Date Range (From Date -> To Date)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 4. Keyword Search (searches in details, user_name, ip_address, action)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('details', 'like', "%{$s}%")
                  ->orWhere('user_name', 'like', "%{$s}%")
                  ->orWhere('action', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        // Export to CSV if requested
        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->get());
        }

        $logs = $query->paginate(30)->withQueryString();

        // Filter Options
        $users = User::orderBy('name')->get(['id', 'name', 'role']);
        $distinctActions = ActivityLog::distinct()->whereNotNull('action')->orderBy('action')->pluck('action');

        // Analytics & Stats Overview Cards
        $stats = [
            'total_logs' => ActivityLog::count(),
            'today_logs' => ActivityLog::whereDate('created_at', today())->count(),
            'active_users_today' => ActivityLog::whereDate('created_at', today())->distinct('user_id')->count('user_id'),
            'sensitive_actions' => ActivityLog::where(function($q) {
                $q->where('action', 'like', '%سڕینەوە%')
                  ->orWhere('action', 'like', '%ڕێکخستنەوە%')
                  ->orWhere('action', 'like', '%پووچەڵ%')
                  ->orWhere('action', 'like', '%دەستکاری%');
            })->count(),
        ];

        return view('logs.index', compact('logs', 'users', 'distinctActions', 'stats'));
    }

    private function exportCsv($records)
    {
        $filename = 'system_activity_logs_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8 in Excel

            fputcsv($file, ['#', 'بەکارهێنەر', 'کردار / چالاکی', 'وردەکاری', 'ئایپی', 'کات و بەروار']);

            foreach ($records as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user_name,
                    $log->action,
                    $log->details ?? '—',
                    $log->ip_address ?? '—',
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
