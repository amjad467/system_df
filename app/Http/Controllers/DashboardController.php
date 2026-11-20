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
        if (!\Illuminate\Support\Facades\Cache::get('db_seeded_confirmed', false)) {
            if (Transaction::count() === 0) {
                \App\Services\DataRestoreService::restoreAll();
            }
            \Illuminate\Support\Facades\Cache::forever('db_seeded_confirmed', true);
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

            $hasCreatedBy = \Illuminate\Support\Facades\Schema::hasColumn('transactions', 'created_by');

            $filterByUser = function ($q) use ($user, $hasCreatedBy) {
                $q->where(function ($sub) use ($user, $hasCreatedBy) {
                    if ($hasCreatedBy) {
                        $sub->where('created_by', $user->id);
                    }
                    $sub->orWhere('user_input', $user->clean_name)
                        ->orWhere('user_input', $user->name);
                });
            };

            // Today's registered count by this clerk
            $todayMyEntriesCount = Transaction::whereDate('created_at', today())
                ->when(!$user->isAdmin(), $filterByUser)->count();

            // Recent slips registered by this user today (or recent 15) - ZERO stats/income calculations!
            $recentMyTransactions = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake', 'plateType'])
                ->when(!$user->isAdmin(), $filterByUser)
                ->whereDate('created_at', today())
                ->latest()
                ->take(15)
                ->get();

            if ($recentMyTransactions->isEmpty()) {
                $recentMyTransactions = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake', 'plateType'])
                    ->when(!$user->isAdmin(), $filterByUser)
                    ->latest()
                    ->take(10)
                    ->get();
            }

            // Returned transactions for this data entry clerk (if any need correction)
            $myReturnedTransactions = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_returned', true)
                ->when(!$user->isAdmin(), $filterByUser)
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

        // 1 & 2. Cached Workflow Pipeline Counters (Single Aggregate Query cached for 60s)
        $todayStr = today()->toDateString();
        $pipelineData = \Illuminate\Support\Facades\Cache::remember('dashboard_pipeline_metrics_' . $todayStr, 60, function () use ($todayStr) {
            $stats = DB::table('transactions')
                ->whereNull('deleted_at')
                ->selectRaw("
                    COUNT(id) as total_transactions,
                    COUNT(CASE WHEN DATE(created_at) = '{$todayStr}' THEN 1 END) as today_data_entry,
                    COUNT(CASE WHEN (is_inspected = true OR is_inspected = 1) AND DATE(inspected_at) = '{$todayStr}' THEN 1 END) as today_inspected,
                    COUNT(CASE WHEN (is_audited = true OR is_audited = 1) AND DATE(audited_at) = '{$todayStr}' THEN 1 END) as today_audited,
                    COUNT(CASE WHEN (is_paid = true OR is_paid = 1) AND (DATE(paid_at) = '{$todayStr}' OR (paid_at IS NULL AND DATE(transaction_date) = '{$todayStr}')) THEN 1 END) as today_paid,
                    COUNT(CASE WHEN (is_booklet_completed = true OR is_booklet_completed = 1) AND DATE(booklet_completed_at) = '{$todayStr}' THEN 1 END) as today_booklets,
                    COUNT(CASE WHEN (is_submitted = true OR is_submitted = 1) AND DATE(submitted_at) = '{$todayStr}' THEN 1 END) as today_archived,
                    COUNT(CASE WHEN (is_inspected = false OR is_inspected = 0 OR is_inspected IS NULL) AND cancelled_at IS NULL THEN 1 END) as pending_inspection,
                    COUNT(CASE WHEN (is_inspected = true OR is_inspected = 1) AND (is_audited = false OR is_audited = 0 OR is_audited IS NULL) AND cancelled_at IS NULL THEN 1 END) as pending_audit,
                    COUNT(CASE WHEN (is_audited = true OR is_audited = 1) AND (is_paid = false OR is_paid = 0 OR is_paid IS NULL) AND cancelled_at IS NULL THEN 1 END) as pending_payment,
                    COUNT(CASE WHEN (is_paid = true OR is_paid = 1) AND (is_booklet_completed = false OR is_booklet_completed = 0 OR is_booklet_completed IS NULL) AND cancelled_at IS NULL THEN 1 END) as pending_booklet,
                    COUNT(CASE WHEN (is_paid = true OR is_paid = 1) AND (is_booklet_completed = true OR is_booklet_completed = 1 OR transaction_type_id IN (5,6,8)) AND (is_submitted = false OR is_submitted = 0 OR is_submitted IS NULL) AND cancelled_at IS NULL THEN 1 END) as pending_submit,
                    COUNT(CASE WHEN (is_booklet_completed = true OR is_booklet_completed = 1) AND cancelled_at IS NULL THEN 1 END) as completed_booklets,
                    COUNT(CASE WHEN (is_booklet_completed = true OR is_booklet_completed = 1) AND end_date IS NOT NULL AND end_date < '{$todayStr}' AND cancelled_at IS NULL AND (is_cancelled IS NULL OR is_cancelled = 0) AND transaction_type_id IN (1, 2, 3, 4, 7) AND NOT EXISTS (
                        SELECT 1 FROM transactions as t_newer 
                        WHERE t_newer.plate_number = transactions.plate_number 
                          AND t_newer.id > transactions.id 
                          AND t_newer.deleted_at IS NULL
                    ) THEN 1 END) as expired_booklets,
                    COUNT(CASE WHEN (is_returned = true OR is_returned = 1) THEN 1 END) as returned_count,
                    COUNT(CASE WHEN (no_nusraw_mrur IS NULL OR no_nusraw_mrur = '0' OR no_nusraw_mrur = '') THEN 1 END) as unreturned_mrur,
                    COUNT(CASE WHEN (is_booklet_completed = true OR is_booklet_completed = 1) AND end_date >= '{$todayStr}' AND cancelled_at IS NULL AND (is_cancelled IS NULL OR is_cancelled = 0) AND transaction_type_id IN (1, 2, 3, 4, 7) AND NOT EXISTS (
                        SELECT 1 FROM transactions as t_newer 
                        WHERE t_newer.plate_number = transactions.plate_number 
                          AND t_newer.id > transactions.id 
                          AND t_newer.deleted_at IS NULL
                    ) THEN 1 END) as unexpired_booklets,
                    COUNT(CASE WHEN cancelled_at IS NOT NULL OR transaction_type_id = 5 THEN 1 END) as cancelled_booklets
                ")->first();

            return [
                'todayDataEntry'     => (int)($stats->today_data_entry ?? 0),
                'todayInspected'     => (int)($stats->today_inspected ?? 0),
                'todayAudited'       => (int)($stats->today_audited ?? 0),
                'todayPaid'          => (int)($stats->today_paid ?? 0),
                'todayBooklets'      => (int)($stats->today_booklets ?? 0),
                'todayArchived'      => (int)($stats->today_archived ?? 0),
                'pendingInspection'  => (int)($stats->pending_inspection ?? 0),
                'pendingAudit'       => (int)($stats->pending_audit ?? 0),
                'pendingPayment'     => (int)($stats->pending_payment ?? 0),
                'pendingBooklet'     => (int)($stats->pending_booklet ?? 0),
                'pendingSubmit'      => (int)($stats->pending_submit ?? 0),
                'completedBooklets'  => (int)($stats->completed_booklets ?? 0),
                'totalTransactions'  => (int)($stats->total_transactions ?? 0),
                'expiredBooklets'    => (int)($stats->expired_booklets ?? 0),
                'returnedCount'      => (int)($stats->returned_count ?? 0),
                'unreturnedMrur'     => (int)($stats->unreturned_mrur ?? 0),
                'unexpiredBooklets'  => (int)($stats->unexpired_booklets ?? 0),
                'cancelledBooklets'  => (int)($stats->cancelled_booklets ?? 0),
            ];
        });

        $todayDataEntryCount  = $pipelineData['todayDataEntry'];
        $todayInspectedCount  = $pipelineData['todayInspected'];
        $todayAuditedCount    = $pipelineData['todayAudited'];
        $todayPaidCount       = $pipelineData['todayPaid'];
        $todayBookletsCount   = $pipelineData['todayBooklets'];
        $todayArchivedCount   = $pipelineData['todayArchived'];
        $pendingInspection    = $pipelineData['pendingInspection'];
        $pendingAudit         = $pipelineData['pendingAudit'];
        $pendingPayment       = $pipelineData['pendingPayment'];
        $pendingBooklet       = $pipelineData['pendingBooklet'];
        $pendingSubmit        = $pipelineData['pendingSubmit'];
        $completedBooklets    = $pipelineData['completedBooklets'];
        $totalTransactions    = $pipelineData['totalTransactions'];
        $expiredBookletsCount = $pipelineData['expiredBooklets'];
        $returnedCount        = $pipelineData['returnedCount'];
        $unreturnedMrurCount   = $pipelineData['unreturnedMrur'];
        $unexpiredBookletsCount = $pipelineData['unexpiredBooklets'];
        $cancelledBookletsCount = $pipelineData['cancelledBooklets'];

        $returnedTransactions = $returnedCount > 0
            ? Transaction::where('is_returned', true)->latest()->take(10)->get()
            : collect();

        // 3. Financial Metrics (STRICTLY FOR CASHIER & ADMIN ONLY! Single cached query)
        $canSeeMoney = $user && ($user->isAdmin() || $user->role === 'cashier');
        $showAllTimeCards = $user && ($user->isAdmin() || $user->role === 'archiver' || ($user->isAdmin() && $request->get('view') === 'archiver'));
        $totalIncome = 0;
        $todayIncome = 0;

        if ($canSeeMoney) {
            $finData = \Illuminate\Support\Facades\Cache::remember('dashboard_financials_' . $todayStr, 60, function () use ($todayStr) {
                $fin = DB::table('transactions')
                    ->whereNull('deleted_at')
                    ->selectRaw("
                        SUM(CASE WHEN (is_paid = true OR is_paid = 1) THEN total_pay ELSE 0 END) as total_income,
                        SUM(CASE WHEN (is_paid = true OR is_paid = 1) AND (DATE(paid_at) = '{$todayStr}' OR (paid_at IS NULL AND DATE(transaction_date) = '{$todayStr}')) THEN total_pay ELSE 0 END) as today_income
                    ")->first();
                return [
                    'total' => (float)($fin->total_income ?? 0),
                    'today' => (float)($fin->today_income ?? 0),
                ];
            });
            $totalIncome = $finData['total'];
            $todayIncome = $finData['today'];
        }

        // 4. Role-Specific Queues & Action Lists
        $pendingInspectionList = collect();
        $pendingAuditList      = collect();
        $pendingPaymentList    = collect();
        $recentPaidTodayList   = collect();
        $pendingBookletList    = collect();
        $pendingSubmitList     = collect();

        if ($user->isAdmin() || $activeRole === 'inspector' || $user->hasPermission('transactions.inspect')) {
            $pendingInspectionList = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_inspected', false)
                ->whereNull('cancelled_at')
                ->latest()
                ->take(8)
                ->get();
        }

        if ($user->isAdmin() || $activeRole === 'auditor' || $user->hasPermission('transactions.audit')) {
            $pendingAuditList = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_inspected', true)
                ->where('is_audited', false)
                ->whereNull('cancelled_at')
                ->latest()
                ->take(8)
                ->get();
        }

        if ($user->isAdmin() || $activeRole === 'cashier' || $user->hasPermission('transactions.pay')) {
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

        if ($user->isAdmin() || $activeRole === 'booklet' || $user->hasPermission('transactions.complete_booklet')) {
            $pendingBookletList = Transaction::with(['transactionType', 'carMake', 'plateType'])
                ->where('is_paid', true)
                ->where('is_booklet_completed', false)
                ->whereNull('cancelled_at')
                ->latest()
                ->take(8)
                ->get();
        }

        if ($user->isAdmin() || $activeRole === 'archiver' || $user->hasPermission('transactions.submit')) {
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
        $allTxTypes = \Illuminate\Support\Facades\Cache::remember('all_transaction_types', 3600, fn() => TransactionType::all());
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
            'showAllTimeCards',
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
            'unreturnedMrurCount',
            'unexpiredBookletsCount',
            'cancelledBookletsCount',
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
