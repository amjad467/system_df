<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Self-heal: If database is empty on hosted service, automatically restore all 84 baseline records
        if (Transaction::count() === 0) {
            \App\Services\DataRestoreService::restoreAll();
        }

        $user = auth()->user();

        // Check if user is Data Entry or explicitly viewing Data Entry mode
        $isDataEntry = $user && ($user->role === 'data_entry' || $request->get('view') === 'data_entry');

        if ($isDataEntry) {
            $searchTerm = trim($request->get('search') ?? $request->get('barcode') ?? '');
            $searchedTransaction = null;
            $searchNotFound = false;

            if (!empty($searchTerm)) {
                $searchedTransaction = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake', 'carColor', 'plateType', 'director'])
                    ->where('barcode', $searchTerm)
                    ->orWhere('plate_number', $searchTerm)
                    ->orWhere('chassis_number', $searchTerm)
                    ->orWhere('phone_number', $searchTerm)
                    ->orWhere('booklet_number', $searchTerm)
                    ->latest()
                    ->first();

                if (!$searchedTransaction) {
                    $searchNotFound = true;
                }
            }

            // Recent slips registered by this user today (or recent 15) - ZERO stats/income calculations!
            $recentMyTransactions = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake', 'plateType'])
                ->when(!$user->isAdmin(), function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->where('created_by', $user->id)
                            ->orWhere('user_input', $user->clean_name)
                            ->orWhere('user_input', $user->name);
                    });
                })
                ->whereDate('created_at', today())
                ->latest()
                ->take(15)
                ->get();

            if ($recentMyTransactions->isEmpty()) {
                $recentMyTransactions = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake', 'plateType'])
                    ->when(!$user->isAdmin(), function ($q) use ($user) {
                        $q->where(function ($sub) use ($user) {
                            $sub->where('created_by', $user->id)
                                ->orWhere('user_input', $user->clean_name)
                                ->orWhere('user_input', $user->name);
                        });
                    })
                    ->latest()
                    ->take(10)
                    ->get();
            }

            // Returned transactions for this data entry clerk (if any need correction)
            $myReturnedTransactions = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_returned', true)
                ->when(!$user->isAdmin(), function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->where('created_by', $user->id)
                            ->orWhere('user_input', $user->clean_name)
                            ->orWhere('user_input', $user->name);
                    });
                })
                ->latest()
                ->get();

            return view('dashboard_data_entry', compact(
                'searchedTransaction',
                'searchTerm',
                'searchNotFound',
                'recentMyTransactions',
                'myReturnedTransactions'
            ));
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
        $completedBooklets = Transaction::where('is_booklet_completed', true)->whereNull('cancelled_at')->count();
        
        $expiredBookletsCount = Transaction::expiredBooklets()->count();

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

