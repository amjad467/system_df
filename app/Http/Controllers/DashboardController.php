<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Self-heal: If database is empty on hosted service, automatically restore all baseline records
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

            // Today's registered count by this clerk
            $todayMyEntriesCount = Transaction::whereDate('created_at', today())
                ->when(!$user->isAdmin(), function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->where('created_by', $user->id)
                            ->orWhere('user_input', $user->clean_name)
                            ->orWhere('user_input', $user->name);
                    });
                })->count();

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
                'todayMyEntriesCount',
                'recentMyTransactions',
                'myReturnedTransactions'
            ));
        }

        // =========================================================================
        // PIPELINE & ROLE-BASED DASHBOARD DATA
        // =========================================================================
        $activeRole = $request->get('view') ?? ($user ? $user->role : 'admin');
        if (!$user->isAdmin()) {
            $activeRole = $user->role;
        }

        // 1. Daily Volume Counters for Each Step (مامەڵەی ئەمڕۆ بۆ ستێپەکان)
        $todayDataEntryCount = Transaction::whereDate('created_at', today())->count();
        $todayInspectedCount = Transaction::where('is_inspected', true)->whereDate('inspected_at', today())->count();
        $todayAuditedCount   = Transaction::where('is_audited', true)->whereDate('audited_at', today())->count();
        $todayPaidCount      = Transaction::where('is_paid', true)
            ->where(function ($q) {
                $q->whereDate('paid_at', today())
                  ->orWhere(function ($sub) {
                      $sub->whereNull('paid_at')->whereDate('transaction_date', today());
                  });
            })->count();
        $todayBookletsCount  = Transaction::where('is_booklet_completed', true)->whereDate('booklet_completed_at', today())->count();
        $todayArchivedCount  = Transaction::where('is_submitted', true)->whereDate('submitted_at', today())->count();

        // 2. Pending Workflow Pipeline Counters
        $pendingInspection = Transaction::where('is_inspected', false)->whereNull('cancelled_at')->count();
        $pendingAudit      = Transaction::where('is_inspected', true)->where('is_audited', false)->whereNull('cancelled_at')->count();
        $pendingPayment    = Transaction::where('is_audited', true)->where('is_paid', false)->whereNull('cancelled_at')->count();
        $pendingBooklet    = Transaction::where('is_paid', true)->where('is_booklet_completed', false)->whereNull('cancelled_at')->count();
        $pendingSubmit     = Transaction::where('is_paid', true)
            ->where(function ($q) {
                $q->where('is_booklet_completed', true)
                  ->orWhereIn('transaction_type_id', [5, 6, 8]);
            })
            ->where('is_submitted', false)
            ->whereNull('cancelled_at')
            ->count();

        $completedBooklets = Transaction::where('is_booklet_completed', true)->whereNull('cancelled_at')->count();
        $totalTransactions = Transaction::count();
        $expiredBookletsCount = Transaction::expiredBooklets()->count();

        $returnedTransactions = Transaction::where('is_returned', true)->latest()->get();
        $returnedCount = $returnedTransactions->count();

        // 3. Financial Metrics (STRICTLY FOR CASHIER & ADMIN ONLY!)
        $canSeeMoney = $user && ($user->isAdmin() || $user->role === 'cashier');
        $totalIncome = 0;
        $todayIncome = 0;

        if ($canSeeMoney) {
            $totalIncome = Transaction::where('is_paid', true)->sum('total_pay');
            $todayIncome = Transaction::where('is_paid', true)
                ->where(function ($q) {
                    $q->whereDate('paid_at', today())
                      ->orWhere(function ($sub) {
                          $sub->whereNull('paid_at')->whereDate('transaction_date', today());
                      });
                })->sum('total_pay');
        }

        // 4. Role-Specific Queues & Action Lists
        $pendingInspectionList = collect();
        $pendingAuditList      = collect();
        $pendingPaymentList    = collect();
        $recentPaidTodayList   = collect();
        $pendingBookletList    = collect();
        $pendingSubmitList     = collect();

        if ($user->isAdmin() || $activeRole === 'inspector') {
            $pendingInspectionList = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_inspected', false)
                ->whereNull('cancelled_at')
                ->latest()
                ->take(8)
                ->get();
        }

        if ($user->isAdmin() || $activeRole === 'auditor') {
            $pendingAuditList = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_inspected', true)
                ->where('is_audited', false)
                ->whereNull('cancelled_at')
                ->latest()
                ->take(8)
                ->get();
        }

        if ($user->isAdmin() || $activeRole === 'cashier') {
            $pendingPaymentList = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_audited', true)
                ->where('is_paid', false)
                ->whereNull('cancelled_at')
                ->latest()
                ->take(8)
                ->get();

            $recentPaidTodayList = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_paid', true)
                ->where(function ($q) {
                    $q->whereDate('paid_at', today())
                      ->orWhere(function ($sub) {
                          $sub->whereNull('paid_at')->whereDate('transaction_date', today());
                      });
                })
                ->latest()
                ->take(8)
                ->get();
        }

        if ($user->isAdmin() || $activeRole === 'booklet') {
            $pendingBookletList = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_paid', true)
                ->where('is_booklet_completed', false)
                ->whereNull('cancelled_at')
                ->latest()
                ->take(8)
                ->get();
        }

        if ($user->isAdmin() || $activeRole === 'archiver') {
            $pendingSubmitList = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_paid', true)
                ->where(function ($q) {
                    $q->where('is_booklet_completed', true)
                      ->orWhereIn('transaction_type_id', [5, 6, 8]);
                })
                ->where('is_submitted', false)
                ->whereNull('cancelled_at')
                ->latest()
                ->take(8)
                ->get();
        }

        // 5. Archiver Booklet Statistics by Date & Types (NO MONEY!)
        $allTxTypes = TransactionType::all();
        $archiverFilter = $request->get('archiver_filter', 'today');
        $archiverFrom = $request->get('from_date');
        $archiverTo   = $request->get('to_date');

        $archiverQuery = Transaction::query()->where('is_submitted', true)->whereNull('cancelled_at');

        if ($archiverFrom && $archiverTo) {
            $archiverQuery->whereBetween('submitted_at', [
                Carbon::parse($archiverFrom)->startOfDay(),
                Carbon::parse($archiverTo)->endOfDay(),
            ]);
        } elseif ($archiverFilter === 'today') {
            $archiverQuery->whereDate('submitted_at', today());
        } elseif ($archiverFilter === 'week') {
            $archiverQuery->whereBetween('submitted_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($archiverFilter === 'month') {
            $archiverQuery->whereMonth('submitted_at', now()->month)->whereYear('submitted_at', now()->year);
        } // 'all' means all submitted

        $archiverCountsRaw = (clone $archiverQuery)
            ->select('transaction_type_id', DB::raw('count(*) as count'))
            ->groupBy('transaction_type_id')
            ->pluck('count', 'transaction_type_id')
            ->toArray();

        $archiverTotalCount = (clone $archiverQuery)->count();

        // 6. General metrics for Admin overview
        $typeDistribution = TransactionType::withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->take(5)
            ->get();

        $recentTransactions = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake'])
            ->latest()
            ->take(8)
            ->get();

        $recentLogs = ActivityLog::latest()->take(6)->get();

        return view('dashboard', compact(
            'activeRole',
            'canSeeMoney',
            'totalTransactions',
            'totalIncome',
            'todayIncome',
            'todayDataEntryCount',
            'todayInspectedCount',
            'todayAuditedCount',
            'todayPaidCount',
            'todayBookletsCount',
            'todayArchivedCount',
            'pendingInspection',
            'pendingAudit',
            'pendingPayment',
            'pendingBooklet',
            'pendingSubmit',
            'completedBooklets',
            'expiredBookletsCount',
            'returnedTransactions',
            'returnedCount',
            'pendingInspectionList',
            'pendingAuditList',
            'pendingPaymentList',
            'recentPaidTodayList',
            'pendingBookletList',
            'pendingSubmitList',
            'allTxTypes',
            'archiverFilter',
            'archiverFrom',
            'archiverTo',
            'archiverCountsRaw',
            'archiverTotalCount',
            'typeDistribution',
            'recentTransactions',
            'recentLogs'
        ));
    }
}
