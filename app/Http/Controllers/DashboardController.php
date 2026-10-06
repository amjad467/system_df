<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalTransactions = Transaction::count();
        $totalIncome = Transaction::where('is_paid', true)->sum('total_pay');
        $pendingInspection = Transaction::where('is_inspected', false)->where('is_cancelled', false)->count();
        $pendingAudit = Transaction::where('is_inspected', true)->where('is_audited', false)->where('is_cancelled', false)->count();
        $pendingPayment = Transaction::where('is_audited', true)->where('is_paid', false)->where('is_cancelled', false)->count();
        $pendingBooklet = Transaction::where('is_paid', true)->where('is_booklet_completed', false)->where('is_cancelled', false)->count();
        $completedBooklets = Transaction::where('is_booklet_completed', true)->count();

        $returnedTransactions = Transaction::where('is_returned', true)->latest()->get();
        $returnedCount = $returnedTransactions->count();

        $recentTransactions = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake'])
            ->latest()
            ->take(10)
            ->get();

        $recentLogs = ActivityLog::latest()->take(8)->get();

        return view('dashboard', compact(
            'totalTransactions',
            'totalIncome',
            'pendingInspection',
            'pendingAudit',
            'pendingPayment',
            'pendingBooklet',
            'completedBooklets',
            'returnedTransactions',
            'returnedCount',
            'recentTransactions',
            'recentLogs'
        ));
    }
}

