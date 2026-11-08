<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Self-heal: If database is empty on hosted service, automatically restore all 84 baseline records
        if (Transaction::count() === 0) {
            \App\Services\DataRestoreService::restoreAll();
        }

        $totalTransactions = Transaction::count();
        $totalIncome = Transaction::where('is_paid', true)->sum('total_pay');
        
        // Today's Metrics
        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $todayIncome = Transaction::where('is_paid', true)
            ->where(function ($q) {
                $q->whereDate('paid_at', today())
                  ->orWhere(function ($sub) {
                      $sub->whereNull('paid_at')->whereDate('transaction_date', today());
                  });
            })->sum('total_pay');

        // Workflow Pipeline Counters
        $pendingInspection = Transaction::where('is_inspected', false)->whereNull('cancelled_at')->count();
        $pendingAudit = Transaction::where('is_inspected', true)->where('is_audited', false)->whereNull('cancelled_at')->count();
        $pendingPayment = Transaction::where('is_audited', true)->where('is_paid', false)->whereNull('cancelled_at')->count();
        $pendingBooklet = Transaction::where('is_paid', true)->where('is_booklet_completed', false)->whereNull('cancelled_at')->count();
        $completedBooklets = Transaction::where('is_booklet_completed', true)->count();
        
        $expiredBookletsCount = Transaction::where('is_booklet_completed', true)
            ->whereNotNull('end_date')
            ->where('end_date', '<', today())
            ->count();

        $returnedTransactions = Transaction::where('is_returned', true)->latest()->get();
        $returnedCount = $returnedTransactions->count();

        $typeDistribution = \App\Models\TransactionType::withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->take(5)
            ->get();

        $recentTransactions = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake'])
            ->latest()
            ->take(8)
            ->get();

        $recentLogs = ActivityLog::latest()->take(6)->get();

        return view('dashboard', compact(
            'totalTransactions',
            'totalIncome',
            'todayTransactions',
            'todayIncome',
            'pendingInspection',
            'pendingAudit',
            'pendingPayment',
            'pendingBooklet',
            'completedBooklets',
            'expiredBookletsCount',
            'returnedTransactions',
            'returnedCount',
            'typeDistribution',
            'recentTransactions',
            'recentLogs'
        ));
    }
}

