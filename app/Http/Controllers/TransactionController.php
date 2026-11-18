<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TrafficDirectorate;
use App\Models\Director;
use App\Models\PlateType;
use App\Models\CarMake;
use App\Models\CarColor;
use App\Models\TransactionType;
use App\Services\FeeCalculatorService;
use App\Services\BarcodeService;
use App\Services\NotificationService;
use App\Services\AuditLogger;
use App\Models\BlockedReceiptNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // Only Admin and Archiver can access the general transactions index
        if (auth()->check() && !auth()->user()->isAdmin() && auth()->user()->role !== 'archiver') {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بینینی لیستی گشتی مامەڵەکان تەنها بۆ ئەدمین و کارمەندی ئەرشیفە.');
        }

        // Self-heal: If database is empty on hosted service, automatically restore all 84 baseline records
        if (!\Illuminate\Support\Facades\Cache::get('db_seeded_confirmed', false)) {
            if (Transaction::count() === 0) {
                \App\Services\DataRestoreService::restoreAll();
            }
            \Illuminate\Support\Facades\Cache::forever('db_seeded_confirmed', true);
        }

        $today = now()->toDateString();

        // ڕێکخستنی بەروار: ئەگەر فلتەر دیارینەکرابوو، ئەگەر ئەمڕۆ تۆمار هەبوو today بەکاربێنە، دەنا all بۆ ئەوەی تۆمارەکان بە بەتاڵی دەرنەکەون
        $dateFilter = $request->get('date_filter');
        if (!$dateFilter) {
            if ($request->filled('from_date') || $request->filled('to_date')) {
                $dateFilter = 'custom';
            } elseif ($request->has('all_dates')) {
                $dateFilter = 'all';
            } else {
                $hasToday = Transaction::whereDate('transaction_date', $today)->orWhereDate('created_at', $today)->exists();
                $dateFilter = $hasToday ? 'today' : 'all';
            }
        }

        $query = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake', 'carColor', 'plateType']);

        // سەپاندنی فلتەری بەروار
        if ($dateFilter === 'today') {
            $query->where(function($q) use ($today) {
                $q->whereDate('transaction_date', $today)
                  ->orWhereDate('created_at', $today);
            });
        } elseif ($dateFilter === 'yesterday') {
            $yesterday = now()->subDay()->toDateString();
            $query->where(function($q) use ($yesterday) {
                $q->whereDate('transaction_date', $yesterday)
                  ->orWhereDate('created_at', $yesterday);
            });
        } elseif ($dateFilter === 'this_week') {
            $lastWeek = now()->subDays(7)->toDateString();
            $query->where(function($q) use ($lastWeek, $today) {
                $q->whereBetween('transaction_date', [$lastWeek, $today])
                  ->orWhereBetween('created_at', [$lastWeek, now()]);
            });
        } elseif ($dateFilter === 'custom') {
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('transaction_date', [$request->from_date, $request->to_date]);
            } elseif ($request->filled('from_date')) {
                $query->whereDate('transaction_date', '>=', $request->from_date);
            } elseif ($request->filled('to_date')) {
                $query->whereDate('transaction_date', '<=', $request->to_date);
            } else {
                $query->where(function($q) use ($today) {
                    $q->whereDate('transaction_date', $today)
                      ->orWhereDate('created_at', $today);
                });
                $dateFilter = 'today';
            }
        }
        // ئەگەر all بێت هیچ مەرجێکی بەروار نایەتە پێشەوە تا گشت کاتەکان پیشان بدات

        // گەڕانی دەقی
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('barcode', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%")
                  ->orWhere('visitor_name_eng', 'like', "%{$search}%")
                  ->orWhere('second_driver_name', 'like', "%{$search}%")
                  ->orWhere('second_driver_name_eng', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhere('chassis_number', 'like', "%{$search}%")
                  ->orWhere('receipt_37a_number', 'like', "%{$search}%")
                  ->orWhere('booklet_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('transaction_type_id')) {
            $query->where('transaction_type_id', $request->transaction_type_id);
        }

        // فلتەری قۆناغ
        $stage = $request->get('stage', 'all');
        if ($stage && $stage !== 'all') {
            switch ($stage) {
                case 'inspection':
                    $query->where('is_inspected', false)->whereNull('cancelled_at');
                    break;
                case 'audit':
                    $query->where('is_inspected', true)->where('is_audited', false)->whereNull('cancelled_at');
                    break;
                case 'payment':
                    $query->where('is_audited', true)->where('is_paid', false)->whereNull('cancelled_at');
                    break;
                case 'booklet':
                    $query->where('is_paid', true)->where('is_booklet_completed', false)->whereNull('cancelled_at');
                    break;
                case 'completed':
                    $query->where(function($q) {
                        $q->where('is_submitted', true)->orWhere('is_booklet_completed', true);
                    })->whereNull('cancelled_at');
                    break;
                case 'cancelled':
                    $query->whereNotNull('cancelled_at');
                    break;
            }
        }

        // هەژمارکردنی ژمارەی مامەڵەکانی هەر قۆناغێک بەپێی بەرواری هەڵبژێردراو
        $baseCountQuery = Transaction::query();
        if ($dateFilter === 'today') {
            $baseCountQuery->where(function($q) use ($today) {
                $q->whereDate('transaction_date', $today)
                  ->orWhereDate('created_at', $today);
            });
        } elseif ($dateFilter === 'yesterday') {
            $yesterday = now()->subDay()->toDateString();
            $baseCountQuery->where(function($q) use ($yesterday) {
                $q->whereDate('transaction_date', $yesterday)
                  ->orWhereDate('created_at', $yesterday);
            });
        } elseif ($dateFilter === 'this_week') {
            $lastWeek = now()->subDays(7)->toDateString();
            $baseCountQuery->where(function($q) use ($lastWeek, $today) {
                $q->whereBetween('transaction_date', [$lastWeek, $today])
                  ->orWhereBetween('created_at', [$lastWeek, now()]);
            });
        } elseif ($dateFilter === 'custom') {
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $baseCountQuery->whereBetween('transaction_date', [$request->from_date, $request->to_date]);
            } elseif ($request->filled('from_date')) {
                $baseCountQuery->whereDate('transaction_date', '>=', $request->from_date);
            } elseif ($request->filled('to_date')) {
                $baseCountQuery->whereDate('transaction_date', '<=', $request->to_date);
            } else {
                $baseCountQuery->where(function($q) use ($today) {
                    $q->whereDate('transaction_date', $today)
                      ->orWhereDate('created_at', $today);
                });
            }
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $baseCountQuery->where(function($q) use ($search) {
                $q->where('barcode', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhere('booklet_number', 'like', "%{$search}%");
            });
        }
        if ($request->filled('transaction_type_id')) {
            $baseCountQuery->where('transaction_type_id', $request->transaction_type_id);
        }

        // هەژمارکردنی خێرای ژمارەی مامەڵەکان بە یەک کوێری لەبری ٧ کوێری جیاواز
        $stats = (clone $baseCountQuery)->selectRaw("
            COUNT(*) as total_all,
            COUNT(CASE WHEN (is_inspected = false OR is_inspected IS NULL) AND cancelled_at IS NULL THEN 1 END) as total_inspection,
            COUNT(CASE WHEN is_inspected = true AND (is_audited = false OR is_audited IS NULL) AND cancelled_at IS NULL THEN 1 END) as total_audit,
            COUNT(CASE WHEN is_audited = true AND (is_paid = false OR is_paid IS NULL) AND cancelled_at IS NULL THEN 1 END) as total_payment,
            COUNT(CASE WHEN is_paid = true AND (is_booklet_completed = false OR is_booklet_completed IS NULL) AND cancelled_at IS NULL THEN 1 END) as total_booklet,
            COUNT(CASE WHEN (is_submitted = true OR is_booklet_completed = true) AND cancelled_at IS NULL THEN 1 END) as total_completed,
            COUNT(CASE WHEN cancelled_at IS NOT NULL THEN 1 END) as total_cancelled
        ")->first();

        $stageCounts = [
            'all' => (int)($stats->total_all ?? 0),
            'inspection' => (int)($stats->total_inspection ?? 0),
            'audit' => (int)($stats->total_audit ?? 0),
            'payment' => (int)($stats->total_payment ?? 0),
            'booklet' => (int)($stats->total_booklet ?? 0),
            'completed' => (int)($stats->total_completed ?? 0),
            'cancelled' => (int)($stats->total_cancelled ?? 0),
        ];

        // هەڵبژاردنی ژمارەی تۆمارەکان لە هەر پەڕەیەکدا (15, 30, 50, 100)
        $perPage = (int)$request->get('per_page', 15);
        if (!in_array($perPage, [15, 30, 50, 100])) {
            $perPage = 15;
        }

        $transactions = $query->latest('id')->paginate($perPage)->withQueryString();
        $transactionTypes = TransactionType::all();

        return view('transactions.index', compact(
            'transactions',
            'transactionTypes',
            'stageCounts',
            'stage',
            'perPage',
            'dateFilter',
            'today'
        ));
    }

    public function officialPrintIndex(Request $request)
    {
        if (!auth()->user()->hasPermission('prints.official_index')) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بینینی چاپی نوسراوە فەرمییەکانت نییە بەپێی پێرمشنەکان!');
        }

        $tab = $request->get('tab', 'pending');
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $search = $request->get('search');

        // Filter ONLY the 3 allowed transaction categories:
        // 1. دەرهێنان (Booklet creation / issuing: types 1, 2, 3, 4, 7)
        // 2. پووچەڵکردنەوە (Cancellation letter: type 5)
        // 3. پشکنین (Inspection letter: type 8)
        $allowedTypeIds = [1, 2, 3, 4, 5, 7, 8];

        $baseQuery = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake', 'carColor', 'plateType'])
            ->whereNull('cancelled_at')
            ->where('is_paid', true)
            ->whereIn('transaction_type_id', $allowedTypeIds);

        if ($selectedDate) {
            $baseQuery->whereDate('paid_at', $selectedDate);
        }

        if ($search) {
            $baseQuery->where(function($q) use ($search) {
                $q->where('barcode', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhere('chassis_number', 'like', "%{$search}%")
                  ->orWhere('receipt_37a_number', 'like', "%{$search}%")
                  ->orWhere('booklet_number', 'like', "%{$search}%");
            });
        }

        $pendingCountTotal = (clone $baseQuery)->where(function($q) {
            $q->where('is_printed', false)
              ->where('is_submitted', false);
        })->count();

        $printedCountTotal = (clone $baseQuery)->where(function($q) {
            $q->where('is_printed', true)
              ->orWhere('is_submitted', true);
        })->count();

        $totalPaidToday = Transaction::whereNull('cancelled_at')
            ->where('is_paid', true)
            ->whereDate('paid_at', now()->format('Y-m-d'))
            ->count();

        $query = clone $baseQuery;

        if ($tab === 'printed') {
            $query->where(function($q) {
                $q->where('is_printed', true)
                  ->orWhere('is_submitted', true);
            });
        } else {
            $query->where(function($q) {
                $q->where('is_printed', false)
                  ->where('is_submitted', false);
            });
        }

        $transactions = $query->latest('paid_at')->paginate(15)->withQueryString();

        return view('transactions.official_prints', compact(
            'transactions',
            'tab',
            'selectedDate',
            'search',
            'pendingCountTotal',
            'printedCountTotal',
            'totalPaidToday'
        ));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->hasPermission('transactions.create')) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی تۆمارکردنی مامەڵەی نوێت نییە بەپێی پێرمشنەکان!');
        }

        $directorates = \Illuminate\Support\Facades\Cache::remember('all_traffic_directorates', 3600, fn() => TrafficDirectorate::all());
        $directors = \Illuminate\Support\Facades\Cache::remember('all_directors', 3600, fn() => Director::all());
        $plateTypes = \Illuminate\Support\Facades\Cache::remember('all_plate_types', 3600, fn() => PlateType::all());
        $carMakes = \Illuminate\Support\Facades\Cache::remember('all_car_makes', 3600, fn() => CarMake::all());
        $carColors = \Illuminate\Support\Facades\Cache::remember('all_car_colors', 3600, fn() => CarColor::all());
        $transactionTypes = \Illuminate\Support\Facades\Cache::remember('all_transaction_types', 3600, fn() => TransactionType::all());
        $passportIssuePlaces = \Illuminate\Support\Facades\Cache::remember('all_passport_issue_places', 3600, fn() => \App\Models\PassportIssuePlace::orderBy('name')->get());

        $defaultDirectorateId = $directorates->first(fn($d) => str_contains($d->name_kurdish ?? '', 'سلێمانی'))?->id ?? $directorates->first()?->id;
        $defaultDirectorId = Director::getActiveDirector()?->id ?? ($directors->first()?->id ?? 1);

        $directorateCodeMap = [
            '21' => $directorates->first(fn($d) => str_contains($d->name_kurdish ?? '', 'سلێمانی'))?->id,
            '22' => $directorates->first(fn($d) => str_contains($d->name_kurdish ?? '', 'هەولێر') || str_contains($d->name_kurdish ?? '', 'هه ولير'))?->id,
            '23' => $directorates->first(fn($d) => str_contains($d->name_kurdish ?? '', 'هەڵەبجە') || str_contains($d->name_kurdish ?? '', 'هةلَةبجة'))?->id,
            '24' => $directorates->first(fn($d) => str_contains($d->name_kurdish ?? '', 'دهۆک') || str_contains($d->name_kurdish ?? '', 'دهوك'))?->id,
        ];

        $fromTransaction = null;
        if ($request->filled('from_transaction')) {
            $fromTransaction = Transaction::with(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor'])->find($request->from_transaction);
        }

        $targetTypeId = $request->get('target_type');

        return view('transactions.create', compact(
            'directorates',
            'directors',
            'plateTypes',
            'carMakes',
            'carColors',
            'transactionTypes',
            'passportIssuePlaces',
            'defaultDirectorateId',
            'defaultDirectorId',
            'directorateCodeMap',
            'fromTransaction',
            'targetTypeId'
        ));
    }

    public function lookupPreviousApi(Request $request)
    {
        $chassis = trim($request->get('chassis_number', ''));
        if (!empty($chassis) && strlen($chassis) >= 3) {
            $chassisClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $chassis));
            $record = Transaction::with(['transactionType', 'plateType', 'carMake', 'carColor', 'trafficDirectorate'])
                ->where(function($q) use ($chassis, $chassisClean) {
                    $q->whereRaw("UPPER(REPLACE(REPLACE(chassis_number, ' ', ''), '-', '')) = ?", [$chassisClean])
                      ->orWhere('chassis_number', $chassis);
                    if (strlen($chassisClean) >= 5) {
                        $q->orWhere('chassis_number', 'like', "%{$chassisClean}%");
                    }
                })
                ->latest('id')
                ->first();

            // Check if there is an active booklet (Type 1) for this chassis that hasn't been cancelled
            $activeBooklet = Transaction::where(function($q) use ($chassis, $chassisClean) {
                $q->whereRaw("UPPER(REPLACE(REPLACE(chassis_number, ' ', ''), '-', '')) = ?", [$chassisClean])
                  ->orWhere('chassis_number', $chassis);
            })
            ->where('transaction_type_id', 1)
            ->where(function($q) {
                $q->whereNull('cancelled_at');
                if (\Illuminate\Support\Facades\Schema::hasColumn('transactions', 'is_cancelled')) {
                    $q->where('is_cancelled', false);
                }
            })
            ->latest('id')
            ->first();

            $hasActiveBooklet = false;
            $activeBookletData = null;

            if ($activeBooklet) {
                // Check if there's any subsequent cancellation transaction (Type 5)
                $isCancelledByLaterTx = Transaction::where(function($q) use ($chassis, $chassisClean) {
                    $q->whereRaw("UPPER(REPLACE(REPLACE(chassis_number, ' ', ''), '-', '')) = ?", [$chassisClean])
                      ->orWhere('chassis_number', $chassis);
                })
                ->where('transaction_type_id', 5)
                ->where('id', '>', $activeBooklet->id)
                ->exists();

                if (!$isCancelledByLaterTx) {
                    $hasActiveBooklet = true;
                    $activeBookletData = [
                        'id' => $activeBooklet->id,
                        'barcode' => $activeBooklet->barcode,
                        'visitor_name' => $activeBooklet->visitor_name,
                        'plate_number' => $activeBooklet->plate_number,
                        'transaction_date' => $activeBooklet->transaction_date ? \Carbon\Carbon::parse($activeBooklet->transaction_date)->format('Y-m-d') : $activeBooklet->created_at?->format('Y-m-d'),
                    ];
                }
            }

            $formatted = null;
            if ($record) {
                $formatted = $record->toArray();
                $formatted['car_make_kurdish'] = $record->carMake?->name_kurdish ?? '';
                $formatted['car_make_english'] = $record->carMake?->name_english ?? '';
                $formatted['car_color_kurdish'] = $record->carColor?->name_kurdish ?? '';
                $formatted['car_color_english'] = $record->carColor?->name_english ?? '';
                $formatted['clean_plate_type'] = $record->plateType?->clean_kurdish_name ?? '';
            }

            return response()->json([
                'found' => (bool)$record,
                'record' => $formatted,
                'has_active_booklet' => $hasActiveBooklet,
                'active_booklet' => $activeBookletData,
                'active_booklet_warning' => $hasActiveBooklet ? 'تۆ دەفتەری پێشووت پووچەڵ نەکردووەتەوە' : null,
            ]);
        }

        $query = trim($request->get('query', ''));
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        $results = Transaction::with(['transactionType', 'plateType', 'carMake', 'carColor', 'trafficDirectorate'])
            ->where(function($q) use ($query) {
                $q->where('plate_number', 'like', "%{$query}%")
                  ->orWhere('chassis_number', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%")
                  ->orWhere('visitor_name', 'like', "%{$query}%")
                  ->orWhere('second_driver_name', 'like', "%{$query}%")
                  ->orWhere('phone_number', 'like', "%{$query}%")
                  ->orWhere('passport_number', 'like', "%{$query}%")
                  ->orWhere('salana_number', 'like', "%{$query}%")
                  ->orWhere('booklet_number', 'like', "%{$query}%");
            })
            ->latest()
            ->limit(10)
            ->get();

        return response()->json($results);
    }

    public function expiredBookletsReport(Request $request)
    {
        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'booklet') {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بینینی دەفتەرە بەسەرچووەکان تەنها بۆ بەشی دەفتەر و ئەدمینە!');
        }

        $status = $request->get('status', 'expired'); // 'expired', 'expiring_soon', 'all'
        $search = $request->get('search');

        // Booklet types: 1: New Issue, 2: Renewal, 3: Name Change, 4: Booklet Replacement, 7: Both
        $bookletTypeIds = [1, 2, 3, 4, 7];

        $baseQuery = Transaction::with(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor'])
            ->whereIn('transaction_type_id', $bookletTypeIds)
            ->whereNotNull('end_date')
            ->whereNull('cancelled_at');

        if ($search) {
            $baseQuery->where(function($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('chassis_number', 'like', "%{$search}%")
                  ->orWhere('salana_number', 'like', "%{$search}%")
                  ->orWhere('booklet_number', 'like', "%{$search}%");
            });
        }

        $now = now()->startOfDay();
        $soon = now()->addDays(30)->endOfDay();

        $expiredCount = (clone $baseQuery)->where('end_date', '<', $now)->count();
        $expiringSoonCount = (clone $baseQuery)->where('end_date', '>=', $now)->where('end_date', '<=', $soon)->count();
        $totalCount = $expiredCount + $expiringSoonCount;

        $query = clone $baseQuery;

        if ($status === 'expiring_soon') {
            $query->where('end_date', '>=', $now)->where('end_date', '<=', $soon);
        } elseif ($status === 'expired') {
            $query->where('end_date', '<', $now);
        } else {
            $query->where('end_date', '<=', $soon);
        }

        $transactions = $query->orderBy('end_date', 'asc')->paginate(15)->withQueryString();

        return view('transactions.expired_booklets', compact(
            'transactions',
            'status',
            'expiredCount',
            'expiringSoonCount',
            'totalCount',
            'search'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('transactions.create')) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی تۆمارکردنی مامەڵەی نوێت نییە بەپێی پێرمشنەکان!');
        }

        $validated = $request->validate([
            'parent_transaction_id' => 'nullable|exists:transactions,id',
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'traffic_directorate_id' => 'nullable|exists:traffic_directorates,id',
            'director_id' => 'nullable|exists:directors,id',
            'plate_type_id' => 'nullable|exists:plate_types,id',
            'car_make_id' => 'nullable|exists:car_makes,id',
            'car_color_id' => 'nullable|exists:car_colors,id',
            'visitor_name' => 'required|string|max:255',
            'visitor_name_eng' => 'nullable|string|max:255',
            'second_driver_name' => 'nullable|string|max:255',
            'second_driver_name_eng' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'passport_issue_date' => 'nullable|date',
            'passport_issue_place' => 'nullable|string|max:255',
            'plate_number' => 'required|string|max:100',
            'model_year' => 'required|digits:4',
            'chassis_number' => 'required|string|max:100',
            'piston_count' => 'nullable|integer',
            'salana_number' => 'nullable|string|max:100',
            'num_years' => 'nullable|integer|min:1|max:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'zedabar' => 'nullable|string',
            'zedabar_eng' => 'nullable|string',
            'kamukurty' => 'nullable|string',
            'kamukurty_eng' => 'nullable|string',
            'bary_gshty' => 'nullable|string',
            'bary_gshty_eng' => 'nullable|string',
            'no_nusraw_puchal' => 'nullable|string',
            'date_nusraw_puchal' => 'nullable|date',
            'NO_boxfile' => 'nullable|integer|min:0',
            'NO_boxfile_puchal' => 'nullable|integer|min:0',
        ]);

        // 1. Prevent duplicate Booklet Issue (دەرهێنانی دەفتەر) if previous booklet is active and not cancelled
        if ((int)$request->transaction_type_id === 1) {
            $chassis = trim($request->chassis_number ?? '');
            $cleanChassis = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $chassis));
            if (!empty($cleanChassis)) {
                $activeBooklet = Transaction::where(function($q) use ($chassis, $cleanChassis) {
                    $q->whereRaw("UPPER(REPLACE(REPLACE(chassis_number, ' ', ''), '-', '')) = ?", [$cleanChassis])
                      ->orWhere('chassis_number', $chassis);
                })
                ->where('transaction_type_id', 1)
                ->where(function($q) {
                    $q->whereNull('cancelled_at');
                    if (\Illuminate\Support\Facades\Schema::hasColumn('transactions', 'is_cancelled')) {
                        $q->where('is_cancelled', false);
                    }
                })
                ->latest('id')
                ->first();

                if ($activeBooklet) {
                    $isCancelledByLater = Transaction::where(function($q) use ($chassis, $cleanChassis) {
                        $q->whereRaw("UPPER(REPLACE(REPLACE(chassis_number, ' ', ''), '-', '')) = ?", [$cleanChassis])
                          ->orWhere('chassis_number', $chassis);
                    })
                    ->where('transaction_type_id', 5)
                    ->where('id', '>', $activeBooklet->id)
                    ->exists();

                    if (!$isCancelledByLater) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', "تۆ دەفتەری پێشووت پووچەڵ نەکردووەتەوە! ئەم شاسییە خاوەنی دەفتەری پێشووە بە بارکۆدی [{$activeBooklet->barcode}]. ناتوانیت دەرهێنانی نوێ ئەنجام بدەیت هەتاوەکو دەفتەری پێشوو پووچەڵ نەکرێتەوە، بەڵام دەتوانیت مامەڵەی پووچەڵکردنەوە، تازەکردنەوە، یان جۆرەکانی تر ئەنجام بدەیت.");
                    }
                }
            }
        }

        // 2. Auto-assign designated active Director if not explicitly selected
        if (empty($validated['director_id'])) {
            $activeDirector = Director::getActiveDirector();
            $validated['director_id'] = $activeDirector?->id;
        }

        // 3. Fallback for traffic directorate if not selected
        if (empty($validated['traffic_directorate_id'])) {
            $validated['traffic_directorate_id'] = TrafficDirectorate::where('name_kurdish', 'like', '%سلێمانی%')->value('id') ?? 1;
        }

        $transaction = DB::transaction(function () use ($validated, $request) {
            $barcode = BarcodeService::generateNextBarcode();
            $numYears = (int)($request->input('num_years', 1));
            $isBus = ($request->input('plate_type_id') == 4);
            $typeId = (int)$request->transaction_type_id;

            $fees = FeeCalculatorService::calculate(
                $typeId,
                $numYears,
                $isBus,
                $request->input('start_date'),
                $request->input('end_date')
            );

            // Clean up fields for Cancellation (5) and Inspection (8) / Pledge (6)
            if (in_array($typeId, [5, 6, 8])) {
                $validated['visitor_name_eng'] = null;
                $validated['second_driver_name'] = null;
                $validated['second_driver_name_eng'] = null;
                $validated['start_date'] = null;
                $validated['end_date'] = null;
                $validated['zedabar'] = null;
                $validated['zedabar_eng'] = null;
                $validated['kamukurty'] = null;
                $validated['kamukurty_eng'] = null;
                $validated['bary_gshty'] = null;
                $validated['bary_gshty_eng'] = null;
            }

            $transactionData = array_merge($validated, [
                'barcode' => $barcode,
                'num_years' => $fees['num_years'],
                'pay_amount_years' => $fees['pay_amount_years'],
                'pay_stamp' => $fees['pay_stamp'],
                'pay_form' => $fees['pay_form'],
                'pay_fine' => $fees['pay_fine'],
                'pay_inspection' => $fees['pay_inspection'],
                'total_pay' => $fees['total_pay'],
                'start_date' => $fees['start_date'],
                'end_date' => $fees['end_date'],
                'transaction_date' => now(),
                'user_input' => auth()->user()?->name ?? 'کارمەند',
                'is_returned' => false,
            ]);

            $tx = Transaction::create($transactionData);

            AuditLogger::log('دروستکردنی مامەڵەی نوێ', Transaction::class, $tx->id, "بارکۆد: {$barcode} - ناوی هاووڵاتی: {$tx->visitor_name}", null, $transactionData);
            NotificationService::send(null, 'inspector', 'مامەڵەی نوێ دروستکرا', "مامەڵەیەکی نوێ بە بارکۆدی {$barcode} تومارکرا و ئامادەیە بۆ کەشف.", route('transactions.show', $tx->id));

            // Automatic Dual Creation: If Type 1 (New Booklet Issue / دەرهێنانی دەفتەر), auto-create linked Pledge Form (بەڵێننامە)
            if ((int)$request->transaction_type_id === 1) {
                $pledgeBarcode = BarcodeService::generateNextBarcode();
                $pledgeType = TransactionType::where('name_kurdish', 'like', '%بەڵێننامە%')->first();
                $pledgeTypeId = $pledgeType ? $pledgeType->id : 6;
                $pledgeFees = FeeCalculatorService::calculate($pledgeTypeId, 1, false, null, null);

                $pledgeData = [
                    'parent_transaction_id' => $tx->id,
                    'transaction_type_id' => $pledgeTypeId, // بەڵێننامە
                    'traffic_directorate_id' => $tx->traffic_directorate_id,
                    'director_id' => $tx->director_id,
                    'plate_type_id' => $tx->plate_type_id,
                    'car_make_id' => $tx->car_make_id,
                    'car_color_id' => $tx->car_color_id,
                    'visitor_name' => $tx->visitor_name,
                    'visitor_name_eng' => $tx->visitor_name_eng,
                    'second_driver_name' => $tx->second_driver_name,
                    'second_driver_name_eng' => $tx->second_driver_name_eng,
                    'phone_number' => $tx->phone_number,
                    'passport_number' => $tx->passport_number,
                    'address' => $tx->address,
                    'passport_issue_date' => $tx->passport_issue_date,
                    'passport_issue_place' => $tx->passport_issue_place,
                    'plate_number' => $tx->plate_number,
                    'model_year' => $tx->model_year,
                    'chassis_number' => $tx->chassis_number,
                    'piston_count' => $tx->piston_count ?? 4,
                    'salana_number' => $tx->salana_number,
                    'barcode' => $pledgeBarcode,
                    'num_years' => 1,
                    'pay_amount_years' => $pledgeFees['pay_amount_years'],
                    'pay_stamp' => $pledgeFees['pay_stamp'],
                    'pay_form' => $pledgeFees['pay_form'],
                    'pay_fine' => $pledgeFees['pay_fine'],
                    'pay_inspection' => $pledgeFees['pay_inspection'],
                    'total_pay' => $pledgeFees['total_pay'],
                    'transaction_date' => now(),
                    'user_input' => auth()->user()?->name ?? 'کارمەند',
                    'is_returned' => false,
                ];

                $pledgeTx = Transaction::create($pledgeData);
                AuditLogger::log('دروستکردنی بەڵێننامەی بەستراوەی ئۆتۆماتیکی', Transaction::class, $pledgeTx->id, "دروستکردنی ئۆتۆماتیکی بۆ دەرهێنانی دەفتەر بە بارکۆدی: {$pledgeBarcode}", null, $pledgeData);
                
                $tx->created_pledge_barcode = $pledgeBarcode;
            }

            return $tx;
        });

        $msg = "مامەڵەکە بە سەرکەوتوویی تۆمارکرا. بارکۆد: {$transaction->barcode}";
        if (isset($transaction->created_pledge_barcode)) {
            $msg .= " | بەڵێننامەی بەستراوەی ئۆتۆماتیکیش دروستکرا بە بارکۆدی: {$transaction->created_pledge_barcode}";
        }

        session()->flash('data_entry_completed_prompt_print', true);

        return redirect()->route('transactions.show', $transaction->id)->with('success', $msg);
    }



    public function edit(Transaction $transaction)
    {
        if ($transaction->is_cancelled) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'ئەم مامەڵەیە پووچەڵکراوەتەوە و دەستکاریکردنی ڕاگیراوە.');
        }

        // Editing allowed ONLY for Data Entry, Inspector (تەخمین), and Admin
        if (!auth()->user()->isAdmin() && !in_array(auth()->user()->role, ['data_entry', 'inspector'])) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'تەنها فەرمانبەری داتائەنتەری، ئەندازیاری تەخمین، یان ئەدمین دەتوانێت دەستکاری زانیاری مامەڵە بکات.');
        }

        // Non-admin can edit if canBeEditedByDataEntry, or Inspector before audit/payment
        $canEdit = auth()->user()->isAdmin()
            || $transaction->canBeEditedByDataEntry()
            || (auth()->user()->role === 'inspector' && !$transaction->is_audited && !$transaction->is_paid && !$transaction->is_cancelled && !$transaction->is_submitted);

        if (!$canEdit) {
            $stageName = 'وردبینی';
            if ($transaction->is_submitted) $stageName = 'سەبمیت و ئەرشیف';
            elseif ($transaction->is_booklet_completed) $stageName = 'چاپکردنی دەفتەر';
            elseif ($transaction->is_paid) $stageName = 'وەسڵ و پارەدان';

            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', "دەستکاریکردنی ئەم مامەڵەیە قوفڵ بووە چونکە قۆناغی '{$stageName}'ی بۆ ئەنجامدراوە! تەنها ئەدمین دەتوانێت قۆناغەکە ڕێکبخاتەوە (Reset) تا ڕێگە بە دەستکاری بدات.");
        }

        $directorates = \Illuminate\Support\Facades\Cache::remember('all_traffic_directorates', 3600, fn() => TrafficDirectorate::all());
        $directors = \Illuminate\Support\Facades\Cache::remember('all_directors', 3600, fn() => Director::all());
        $plateTypes = \Illuminate\Support\Facades\Cache::remember('all_plate_types', 3600, fn() => PlateType::all());
        $carMakes = \Illuminate\Support\Facades\Cache::remember('all_car_makes', 3600, fn() => CarMake::all());
        $carColors = \Illuminate\Support\Facades\Cache::remember('all_car_colors', 3600, fn() => CarColor::all());
        $transactionTypes = \Illuminate\Support\Facades\Cache::remember('all_transaction_types', 3600, fn() => TransactionType::all());
        $passportIssuePlaces = \Illuminate\Support\Facades\Cache::remember('all_passport_issue_places', 3600, fn() => \App\Models\PassportIssuePlace::orderBy('name')->get());

        return view('transactions.edit', compact(
            'transaction',
            'directorates',
            'directors',
            'plateTypes',
            'carMakes',
            'carColors',
            'transactionTypes',
            'passportIssuePlaces'
        ));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $canEdit = auth()->user()->isAdmin()
            || $transaction->canBeEditedByDataEntry()
            || (auth()->user()->role === 'inspector' && !$transaction->is_audited && !$transaction->is_paid && !$transaction->is_cancelled && !$transaction->is_submitted);

        if (!$canEdit) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'دەستکاریکردنی ئەم مامەڵەیە قوفڵ کراوە.');
        }

        $validated = $request->validate([
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'traffic_directorate_id' => 'nullable|exists:traffic_directorates,id',
            'director_id' => 'nullable|exists:directors,id',
            'plate_type_id' => 'nullable|exists:plate_types,id',
            'car_make_id' => 'nullable|exists:car_makes,id',
            'car_color_id' => 'nullable|exists:car_colors,id',
            'visitor_name' => 'required|string|max:255',
            'visitor_name_eng' => 'nullable|string|max:255',
            'second_driver_name' => 'nullable|string|max:255',
            'second_driver_name_eng' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'passport_issue_date' => 'nullable|date',
            'passport_issue_place' => 'nullable|string|max:255',
            'plate_number' => 'required|string|max:100',
            'model_year' => 'required|digits:4',
            'chassis_number' => 'required|string|max:100',
            'piston_count' => 'nullable|integer',
            'salana_number' => 'nullable|string|max:100',
            'num_years' => 'nullable|integer|min:1|max:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'zedabar' => 'nullable|string',
            'zedabar_eng' => 'nullable|string',
            'kamukurty' => 'nullable|string',
            'kamukurty_eng' => 'nullable|string',
            'bary_gshty' => 'nullable|string',
            'bary_gshty_eng' => 'nullable|string',
            'no_nusraw_puchal' => 'nullable|string',
            'date_nusraw_puchal' => 'nullable|date',
            'NO_boxfile' => 'nullable|integer|min:0',
            'NO_boxfile_puchal' => 'nullable|integer|min:0',
        ]);

        $numYears = (int)($request->input('num_years', 1));
        $isBus = ($request->input('plate_type_id') == 4);
        $typeId = (int)$request->transaction_type_id;

        $fees = FeeCalculatorService::calculate(
            $typeId,
            $numYears,
            $isBus,
            $request->input('start_date'),
            $request->input('end_date')
        );

        // Clean up fields for Cancellation (5) and Inspection (8) / Pledge (6)
        if (in_array($typeId, [5, 6, 8])) {
            $validated['visitor_name_eng'] = null;
            $validated['second_driver_name'] = null;
            $validated['second_driver_name_eng'] = null;
            $validated['start_date'] = null;
            $validated['end_date'] = null;
            $validated['zedabar'] = null;
            $validated['zedabar_eng'] = null;
            $validated['kamukurty'] = null;
            $validated['kamukurty_eng'] = null;
            $validated['bary_gshty'] = null;
            $validated['bary_gshty_eng'] = null;
        }

        // Preserve archive fields if omitted by data entry
        if (!$request->has('no_nusraw_puchal')) {
            unset($validated['no_nusraw_puchal']);
        }
        if (!$request->has('date_nusraw_puchal')) {
            unset($validated['date_nusraw_puchal']);
        }
        if (!$request->has('NO_boxfile')) {
            unset($validated['NO_boxfile']);
        }
        if (!$request->has('NO_boxfile_puchal')) {
            unset($validated['NO_boxfile_puchal']);
        }

        $transaction->update(array_merge($validated, [
            'num_years' => $fees['num_years'],
            'pay_amount_years' => $fees['pay_amount_years'],
            'pay_stamp' => $fees['pay_stamp'],
            'pay_form' => $fees['pay_form'],
            'pay_fine' => $fees['pay_fine'],
            'pay_inspection' => $fees['pay_inspection'],
            'total_pay' => $fees['total_pay'],
            'start_date' => $fees['start_date'],
            'end_date' => $fees['end_date'],
            'user_edit_input' => auth()->user()?->name ?? 'کارمەند',
            'user_edit_date' => now(),
            'is_returned' => false,
            'return_reason' => null,
            'is_inspected' => false, // Require re-inspection/re-audit after fix
            'is_audited' => false,
        ]));

        // Auto-sync shared citizen and vehicle details with companion pledge/booklet (خاڵی ۱)
        $linkedPledge = $transaction->getLinkedPledgeOrParent();
        if ($linkedPledge) {
            $sharedFields = [
                'traffic_directorate_id' => $transaction->traffic_directorate_id,
                'director_id'            => $transaction->director_id,
                'plate_type_id'          => $transaction->plate_type_id,
                'car_make_id'            => $transaction->car_make_id,
                'car_color_id'           => $transaction->car_color_id,
                'visitor_name'           => $transaction->visitor_name,
                'visitor_name_eng'       => $transaction->visitor_name_eng,
                'second_driver_name'     => $transaction->second_driver_name,
                'second_driver_name_eng' => $transaction->second_driver_name_eng,
                'phone_number'           => $transaction->phone_number,
                'passport_number'        => $transaction->passport_number,
                'address'                => $transaction->address,
                'passport_issue_date'    => $transaction->passport_issue_date,
                'passport_issue_place'   => $transaction->passport_issue_place,
                'plate_number'           => $transaction->plate_number,
                'model_year'             => $transaction->model_year,
                'chassis_number'         => $transaction->chassis_number,
                'piston_count'           => $transaction->piston_count,
                'salana_number'          => $transaction->salana_number,
                'user_edit_input'        => auth()->user()?->name ?? 'کارمەند',
                'user_edit_date'         => now(),
                'is_returned'            => false,
                'return_reason'          => null,
                'is_inspected'           => false,
                'is_audited'             => false,
            ];
            $linkedPledge->update($sharedFields);
            AuditLogger::log('هاوکاتکردنی ئۆتۆماتیکی زانیاری بەڵێننامەی بەستراو', Transaction::class, $linkedPledge->id, "هاوکاتکرا لەگەڵ دەرهێنانی دەفتەر بە بارکۆدی: {$transaction->barcode}", null, $sharedFields);
        }

        AuditLogger::log('دەستکاریکردنی زانیاری مامەڵە', Transaction::class, $transaction->id, "بارکۆد: {$transaction->barcode}");

        // Clear return notifications when fixed (خاڵی ۲)
        NotificationService::clearReturnNotificationsForTransaction($transaction->id, $linkedPledge?->id);

        session()->flash('data_entry_completed_prompt_print', true);

        $successMsg = 'زانیاری مامەڵەکە بە سەرکەوتوویی دەستکاری کرا و ڕاستکرایەوە.';
        if ($linkedPledge) {
            $successMsg .= ' (هەروەها زانیارییەکانی بەڵێننامەی بەستراویش بە خۆکاری هاوکات و نوێکرانەوە).';
        }

        return redirect()->route('transactions.show', $transaction->id)
            ->with('success', $successMsg);
    }

    public function returnTransaction(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'return_reason' => 'required|string|max:1000',
        ], [
            'return_reason.required' => 'تکایە هۆکاری گەڕاندنەوەکە بە ڕوونی بنووسە.'
        ]);

        $transaction->update([
            'is_returned' => true,
            'return_reason' => $validated['return_reason'],
            'returned_by_user_id' => auth()->id(),
            'is_inspected' => false,
            'inspected_by' => null,
            'inspected_at' => null,
            'is_audited' => false,
            'audited_by' => null,
            'audited_at' => null,
        ]);

        // Auto-return companion pledge or booklet so both move together
        $linked = $transaction->getLinkedPledgeOrParent();
        if ($linked) {
            $linked->update([
                'is_returned' => true,
                'return_reason' => $validated['return_reason'],
                'returned_by_user_id' => auth()->id(),
                'is_inspected' => false,
                'inspected_by' => null,
                'inspected_at' => null,
                'is_audited' => false,
                'audited_by' => null,
                'audited_at' => null,
            ]);
            AuditLogger::log('گەڕاندنەوەی مامەڵەی بەستراو', Transaction::class, $linked->id, "هۆکار: {$validated['return_reason']}");
        }

        AuditLogger::log('گەڕاندنەوەی مامەڵە بۆ چاککردنەوە', Transaction::class, $transaction->id, "هۆکار: {$validated['return_reason']}");

        NotificationService::send(
            $transaction->created_by,
            'data_entry',
            'ئاگاداری: مامەڵە گەڕێنراوەتەوە',
            "مامەڵەی ({$transaction->visitor_name}) بە بارکۆدی {$transaction->barcode} بەهۆی هەڵەوە گەڕێنراوەتەوە. هۆکار: {$validated['return_reason']}",
            route('transactions.show', $transaction->id)
        );

        return redirect()->route('transactions.show', $transaction->id)
            ->with('warning', 'مامەڵەکە گەڕێنراوەتەوە بۆ بەشی تەخمین/داتائەنتەری بۆ ڕاستکردنەوە.');
    }


    public function show(Transaction $transaction)
    {
        $transaction->load([
            'transactionType',
            'trafficDirectorate',
            'director',
            'plateType',
            'carMake',
            'carColor',
            'relatedTransactions.transactionType'
        ]);
        return view('transactions.show', compact('transaction'));
    }

    public function scanBarcode(Request $request)
    {
        $term = trim($request->barcode);
        if (empty($term)) {
            return redirect()->back();
        }

        // 1. Direct barcode match
        $transaction = Transaction::where('barcode', $term)->first();

        // 2. Direct exact match by plate, phone, chassis, booklet, receipt
        if (!$transaction) {
            $matches = Transaction::where('plate_number', $term)
                ->orWhere('phone_number', $term)
                ->orWhere('chassis_number', $term)
                ->orWhere('booklet_number', $term)
                ->orWhere('receipt_37a_number', $term)
                ->latest()
                ->get();

            if ($matches->count() === 1) {
                return redirect()->route('transactions.show', $matches->first()->id);
            } elseif ($matches->count() > 1) {
                if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver')) {
                    return redirect()->route('transactions.index', ['search' => $term]);
                }
                return redirect()->route('transactions.show', $matches->first()->id);
            }
        }

        // 3. Fallback partial search into transactions index
        if (!$transaction) {
            $partialMatches = Transaction::where('visitor_name', 'like', "%{$term}%")
                ->orWhere('plate_number', 'like', "%{$term}%")
                ->orWhere('phone_number', 'like', "%{$term}%")
                ->latest()
                ->get();

            if ($partialMatches->count() > 0) {
                if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver')) {
                    return redirect()->route('transactions.index', ['search' => $term]);
                }
                return redirect()->route('transactions.show', $partialMatches->first()->id);
            }

            return redirect()->back()->with('error', "هیچ مامەڵەیەک نەدۆزرایەوە بە گەڕانی: {$term}");
        }

        // Check Role-specific workflow guards
        if (auth()->check()) {
            $user = auth()->user();

            // 1. Booklet Desk Guard: Block Pledge (6), Cancellation (5), and Inspection (8)
            if ($user->role === 'booklet' && in_array((int)$transaction->transaction_type_id, [5, 6, 8])) {
                $typeName = $transaction->transactionType->name_kurdish ?? 'دیاری نەکراو';
                return redirect()->route('dashboard')->with('error', "لێرە کاری دەفتەری نییە! ئەم مامەڵەیە جۆری [{$typeName}]ـە.");
            }

            // 2. Legal Officer Guard: Only Booklet Issue (1) is allowed in legal pledge desk
            if ($user->isLegal() && (int)$transaction->transaction_type_id !== 1) {
                $typeName = $transaction->transactionType->name_kurdish ?? 'دیاری نەکراو';
                return redirect()->route('dashboard')->with('error', "کاری تۆ لێرە ئەنجام نادرێت! بەشی یاسایی تەنها تایبەتە بە مامەڵەی دەرهێنانی دەفتەر (بەڵێننامەی یاسایی). جۆری ئەم مامەڵەیە: [{$typeName}]ـە.");
            }
        }

        return redirect()->route('transactions.show', $transaction->id);
    }

    public function inspect(Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('transactions.inspect')) {
            return redirect()->back()->with('error', 'تەنها کارمەندی کەشف و تەخمین (Inspector) دەتوانێت تێپەڕاندنی قۆناغی تەخمین ئەنجام بدات!');
        }

        $transaction->update([
            'is_inspected' => true,
            'inspected_by' => auth()->user()?->name ?? 'ئەندازیاری کەشف',
            'inspected_at' => now(),
            'is_returned' => false,
            'return_reason' => null,
        ]);

        // Auto-inspect companion pledge or booklet to keep workflow in sync
        $linked = $transaction->getLinkedPledgeOrParent();
        if ($linked) {
            $linked->update([
                'is_inspected' => true,
                'inspected_by' => auth()->user()?->name ?? 'ئەندازیاری کەشف',
                'inspected_at' => now(),
                'is_returned' => false,
                'return_reason' => null,
            ]);
            AuditLogger::log('ئەنجامدانی کەشف بۆ مامەڵەی بەستراو', Transaction::class, $linked->id, "کەشف پەسەندکرا بۆ مامەڵەی بەستراوی: {$linked->barcode}");
        }

        // Clear any return/error notifications in data entry desk
        NotificationService::clearReturnNotificationsForTransaction($transaction->id, $linked?->id);

        AuditLogger::log('ئەنجامدانی کەشف و تەخمین', Transaction::class, $transaction->id, "تەخمین پەسەندکرا بۆ بارکۆد: {$transaction->barcode}");

        NotificationService::send(
            null,
            'auditor',
            'تەخمین پەسەندکرا',
            "مامەڵەی ({$transaction->visitor_name}) بە بارکۆدی {$transaction->barcode} کەشفی تەواو بوو و ئامادەیە بۆ وردبینی.",
            route('transactions.show', $transaction->id)
        );

        session()->flash('inspection_completed_prompt_print', true);

        return redirect()->back()->with('success', 'کەشف و تەخمین بە سەرکەوتوویی پەسەندکرا.');
    }

    public function revertInspection(Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'inspector' && !auth()->user()->hasPermission('transactions.inspect')) {
            return redirect()->route('transactions.show', $transaction->id)->with('error', 'تەنها ئەندازیاری کەشف و تەخمین دەتوانێت ئەم قۆناغە هەڵوەشێنێتەوە!');
        }

        if ($transaction->is_audited) {
            return redirect()->route('transactions.show', $transaction->id)->with('error', 'ئەم مامەڵەیە وردبینی بۆ کراوە و لە قۆناغی وردبینییە یان دواتر! ناتوانرێت هەڵبوەشێنرێتەوە.');
        }

        $transaction->update([
            'is_inspected' => false,
            'inspected_by' => null,
            'inspected_at' => null,
            'is_returned' => true,
            'return_reason' => 'هەڵوەشاندنەوەی تەخمین لەلایەن ئەندازیار بۆ چاککردنەوە لە داتائەنتەری',
            'returned_by_user_id' => auth()->id(),
        ]);

        // Auto-revert companion pledge or booklet so both move together (خاڵی ۱)
        $linked = $transaction->getLinkedPledgeOrParent();
        if ($linked) {
            $linked->update([
                'is_inspected' => false,
                'inspected_by' => null,
                'inspected_at' => null,
                'is_returned' => true,
                'return_reason' => 'هەڵوەشاندنەوەی تەخمین لەلایەن ئەندازیار بۆ چاککردنەوە لە داتائەنتەری',
                'returned_by_user_id' => auth()->id(),
            ]);
            AuditLogger::log('هەڵوەشاندنەوەی قۆناغی تەخمین بۆ مامەڵەی بەستراو', Transaction::class, $linked->id, "تەخمین هەڵوەشێنرایەوە بۆ بارکۆدی بەستراو: {$linked->barcode}");
        }

        AuditLogger::log('هەڵوەشاندنەوەی قۆناغی تەخمین', Transaction::class, $transaction->id, "تەخمین هەڵوەشێنرایەوە بۆ بارکۆد: {$transaction->barcode}");

        return redirect()->route('transactions.show', $transaction->id)->with('warning', 'قۆناغی کەشف و تەخمین بۆ دەرهێنانی دەفتەر و بەڵێننامەی بەستراو هەڵوەشێنرایەوە و گەڕێنرایەوە بۆ داتائەنتەری.');
    }

    public function audit(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('transactions.audit') && auth()->user()->role !== 'auditor') {
            return redirect()->back()->with('error', 'تەنها کارمەندی وردبین (Auditor) دەتوانێت تێپەڕاندنی قۆناغی وردبینی ئەنجام بدات!');
        }

        if (!$transaction->is_inspected) {
            return redirect()->back()->with('error', 'کاری تەخمین ئەنجام بدە دواتر وەرە وردبینی! ناتوانرێت ستێپی تەخمین تێپەڕێندرێت.');
        }

        if ($request->filled('confirm_total_pay')) {
            $entered = (float)preg_replace('/[^\d.]/', '', (string)$request->confirm_total_pay);
            $actual = (float)$transaction->total_pay;
            if (abs($entered - $actual) > 0.01) {
                return redirect()->back()->with('error', 'بڕی پارەی دووپاتکراوە (' . number_format($entered) . ' د.ع) یەکسان نییە لەگەڵ کۆی گشتی مامەڵەکە (' . number_format($actual) . ' د.ع)!');
            }
        }

        $transaction->update([
            'is_audited' => true,
            'audited_by' => auth()->user()?->name ?? 'وردبین',
            'audited_at' => now(),
            'is_returned' => false,
            'return_reason' => null,
        ]);

        $linked = $transaction->getLinkedPledgeOrParent();
        $auditMsg = 'وردبینی مامەڵەکە بە سەرکەوتوویی ئەنجامدرا.';
        if ($linked && !$linked->is_audited) {
            $linked->update([
                'is_inspected' => true,
                'inspected_by' => $linked->inspected_by ?? (auth()->user()?->name ?? 'ئەندازیار'),
                'inspected_at' => $linked->inspected_at ?? now(),
                'is_audited' => true,
                'audited_by' => auth()->user()?->name ?? 'وردبین',
                'audited_at' => now(),
                'is_returned' => false,
                'return_reason' => null,
            ]);
            AuditLogger::log('وردبینی هاوبەشی بەڵێننامە و دەرهێنان', Transaction::class, $linked->id, "وردبینی هاوبەش ئەنجامدرا بۆ: {$linked->barcode}");
            $auditMsg = 'وردبینی مامەڵەی سەرەکی و بەڵێننامەی بەستراوە بە یەک کردار بە سەرکەوتوویی پەسەندکرا.';
        }

        // Prompt official letter print for Booklet Issue (Type 1) and Cancellation (Type 5)
        if (in_array((int)$transaction->transaction_type_id, [1, 5])) {
            session()->flash('audit_completed_prompt_letter', true);
        }

        AuditLogger::log('وردبینیکردنی مامەڵە', Transaction::class, $transaction->id, "وردبینی پەسەندکرا بۆ بارکۆد: {$transaction->barcode}");

        NotificationService::send(
            null,
            'cashier',
            'وردبینی پەسەندکرا',
            "مامەڵەی ({$transaction->visitor_name}) بە بارکۆدی {$transaction->barcode} وردبینی کرا و ئامادەیە بۆ وەرگرتنی رسومات و بڕینی وەسڵ.",
            route('transactions.show', $transaction->id)
        );

        return redirect()->route('transactions.show', $transaction->id)->with('success', $auditMsg);
    }

    public function revertAudit(Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('transactions.audit') && auth()->user()->role !== 'auditor') {
            return redirect()->route('transactions.show', $transaction->id)->with('error', 'دەسەڵاتی هەڵوەشاندنەوەی وردبینی تەنها بۆ کارمەندی وردبین و ئەدمینە.');
        }

        if ($transaction->is_paid) {
            return redirect()->route('transactions.show', $transaction->id)->with('error', 'ناتوانرێت وردبینی هەڵوەشێنرێتەوە چونکە وەسڵی پارەدان ۳۷/أ بۆ بڕاوە و پارە دراوە! (تەنها ئەدمین دەتوانێت لە ڕێگەی Reset قۆناغی پارەدان ڕێکبخاتەوە).');
        }

        $transaction->update([
            'is_audited' => false,
            'audited_by' => null,
            'audited_at' => null,
        ]);

        // Auto-revert companion pledge or booklet audit status
        $linked = $transaction->getLinkedPledgeOrParent();
        if ($linked && $linked->is_audited) {
            $linked->update([
                'is_audited' => false,
                'audited_by' => null,
                'audited_at' => null,
            ]);
            AuditLogger::log('هەڵوەشاندنەوەی وردبینی بەڵێننامەی بەستراو', Transaction::class, $linked->id, "وردبینی هەڵوەشێنرایەوە بۆ بارکۆدی: {$linked->barcode}");
        }

        AuditLogger::log('هەڵوەشاندنەوەی پەسەندکردنی وردبینی', Transaction::class, $transaction->id, "وردبینی هەڵوەشێنرایەوە بۆ بارکۆد: {$transaction->barcode}");

        return redirect()->route('transactions.show', $transaction->id)->with('warning', 'قۆناغی وردبینی بۆ دەرهێنانی دەفتەر و بەڵێننامەی بەستراو هەڵوەشێنرایەوە.');
    }

    public function pay(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('transactions.pay')) {
            return redirect()->back()->with('error', 'تەنها ژمێریار / وەسڵبڕ (Cashier) دەتوانێت پارە وەربگرێت و وەسڵ ببڕێت!');
        }

        if (!$transaction->is_audited) {
            return redirect()->back()->with('error', 'مامەڵەکە هێشتا وردبینی بۆ نەکراوە! وەسڵ‌بڕ ناتوانێت پارە وەربگیرێت تا وردبینی نەکرێت.');
        }

        $request->validate([
            'receipt_37a_number' => 'required|string|max:100',
        ]);

        $receiptNum = trim($request->receipt_37a_number);

        $blocked = BlockedReceiptNumber::where('receipt_number', $receiptNum)->first();
        if ($blocked) {
            $reason = $blocked->cancellation_reason ?? 'دیاری نەکراو';
            return redirect()->back()->with('error', "ئەم ژمارەی پسوولەیە پێشتر بەکارهاتووە و پووچەڵکراوەتەوە! ڕێگەپێدراو نییە بەکاربهێنرێتەوە. (هۆکاری پووچەڵکردنەوە: {$reason})");
        }

        $existing = Transaction::with('transactionType')
            ->where('receipt_37a_number', $receiptNum)
            ->where('id', '!=', $transaction->id)
            ->first();

        if ($existing) {
            $visitorName = $existing->visitor_name ?? 'دیاری نەکراو';
            $plateNumber = $existing->plate_number ?? 'دیاری نەکراو';
            $typeName = $existing->transactionType->name_kurdish ?? 'دیاری نەکراو';
            return redirect()->back()->with('error', "ئەم ژمارەی پسوولەیە ({$receiptNum}) پێشتر دراوە بە مامەڵەی بەناوی ({$visitorName}) و ژمارەی تابلۆی ({$plateNumber}) و جۆری مامەڵەی ({$typeName})!");
        }

        $transaction->update([
            'is_paid' => true,
            'receipt_37a_number' => $receiptNum,
            'paid_by' => auth()->user()?->name ?? 'وەسڵبڕ',
            'paid_at' => now(),
        ]);

        AuditLogger::log('وەرگرتنی پارە و بڕینی وەسڵ', Transaction::class, $transaction->id, "وەسڵی ۳۷/أ: {$receiptNum} - کۆی پارە: {$transaction->total_pay} د.ع");

        // Sequential/Dual payment for linked companion pledge if Booklet Issue
        $linked = $transaction->getLinkedPledgeOrParent();
        $linkedPaidInfo = '';
        if ($linked && !$linked->is_paid && $request->filled('linked_receipt_37a_number')) {
            $linkedReceiptNum = trim($request->linked_receipt_37a_number);
            $blockedLinked = BlockedReceiptNumber::where('receipt_number', $linkedReceiptNum)->first();
            $existingLinked = Transaction::where('receipt_37a_number', $linkedReceiptNum)->where('id', '!=', $linked->id)->first();

            if (!$blockedLinked && !$existingLinked) {
                $linked->update([
                    'is_inspected' => true,
                    'inspected_by' => $linked->inspected_by ?? (auth()->user()?->name ?? 'ئەندازیار'),
                    'inspected_at' => $linked->inspected_at ?? now(),
                    'is_audited' => true,
                    'audited_by' => $linked->audited_by ?? (auth()->user()?->name ?? 'وردبین'),
                    'audited_at' => $linked->audited_at ?? now(),
                    'is_paid' => true,
                    'receipt_37a_number' => $linkedReceiptNum,
                    'paid_by' => auth()->user()?->name ?? 'وەسڵبڕ',
                    'paid_at' => now(),
                ]);
                AuditLogger::log('وەرگرتنی پارە و بڕینی وەسڵ بۆ بەڵێننامەی بەستراو', Transaction::class, $linked->id, "وەسڵی ۳۷/أ: {$linkedReceiptNum} - کۆی پارە: {$linked->total_pay} د.ع");
                $linkedPaidInfo = " و وەسڵی بەڵێننامە ({$linkedReceiptNum})";
            }
        }

        NotificationService::send(
            null,
            'booklet',
            'پارەدانی مامەڵە تەواو بوو',
            "مامەڵەی ({$transaction->visitor_name}) بە بارکۆدی {$transaction->barcode} پارەکەی وەرگیرا و وەسڵی ۳۷/أ بڕا. ئامادەیە بۆ بەشی دەفتەر.",
            route('transactions.show', $transaction->id)
        );

        session()->flash('payment_completed_prompt_receipt', true);

        return redirect()->back()->with('success', "پارەدانی مامەڵەکە و وەسڵی ۳۷/أ ({$receiptNum}){$linkedPaidInfo} بە سەرکەوتوویی تۆمارکرا.");
    }

    public function completeBooklet(Request $request, Transaction $transaction)
    {
        if (auth()->user()->role === 'data_entry') {
            return redirect()->back()->with('error', 'کارمەندی داتائەنتەری دەسەڵاتی تۆمارکردنی ژمارەی دەفتەری نییە!');
        }

        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('transactions.complete_booklet')) {
            return redirect()->back()->with('error', 'تەنها کارمەندی بەشی دەفتەر (Booklet Section) دەتوانێت ژمارەی دەفتەر تۆمار بکات!');
        }

        // Guard: Block Pledge (6), Cancellation (5), and Inspection (8) from booklet desk
        if (in_array((int)$transaction->transaction_type_id, [5, 6, 8])) {
            $typeName = $transaction->transactionType->name_kurdish ?? 'دیاری نەکراو';
            return redirect()->back()->with('error', "لێرە کاری دەفتەری نییە! ئەم مامەڵەیە جۆری [{$typeName}]ـە.");
        }

        if (!$transaction->requiresBooklet()) {
            return redirect()->back()->with('error', 'ئەم جۆرە مامەڵەیە پێویستی بە چاپی دەفتەر نییە!');
        }

        if (!$transaction->is_paid) {
            return redirect()->back()->with('error', 'پارەی مامەڵەکە وەرنەگیراوە و وەسڵی ۳۷/أ نەبراوە! ناتوانرێت دەفتەر دەربکرێت.');
        }

        $request->validate([
            'booklet_number' => 'required|string|max:100',
        ]);

        $bookletNum = trim($request->booklet_number);

        $blocked = BlockedReceiptNumber::where('receipt_number', $bookletNum)->first();
        if ($blocked) {
            $reason = $blocked->cancellation_reason ?? 'دیاری نەکراو';
            return redirect()->back()->with('error', "ئەم ژمارەی دەفتەرە ({$bookletNum}) پووچەڵکراوەتەوە و بلۆککراوە لە سیستەمدا! ڕێگەپێدراو نییە بەکاربهێنرێتەوە. هۆکاری پووچەڵکردنەوە: {$reason}");
        }

        $existing = Transaction::with('transactionType')
            ->where('booklet_number', $bookletNum)
            ->where('id', '!=', $transaction->id)
            ->first();

        if ($existing) {
            $visitorName = $existing->visitor_name ?? 'دیاری نەکراو';
            $plateNumber = $existing->plate_number ?? 'دیاری نەکراو';
            $typeName = $existing->transactionType->name_kurdish ?? 'دیاری نەکراو';
            return redirect()->back()->with('error', "ئەم ژمارەی دەفتەرە ({$bookletNum}) پێشتر دراوە بە مامەڵەی بەناوی ({$visitorName}) و ژمارەی تابلۆی ({$plateNumber}) و جۆری مامەڵەی ({$typeName})!");
        }

        $transaction->update([
            'is_booklet_completed' => true,
            'booklet_number' => $bookletNum,
            'booklet_by' => auth()->user()?->name ?? 'کارمەندی دەفتەر',
            'booklet_completed_at' => now(),
        ]);

        AuditLogger::log('تەواوکردنی دەفتەر', Transaction::class, $transaction->id, "ژمارەی سەر دەفتەر: {$bookletNum}");

        NotificationService::send(
            null,
            'archiver',
            'دەفتەری مامەڵە تۆمارکرا',
            "مامەڵەی ({$transaction->visitor_name}) دەفتەری ژمارە ({$bookletNum}) بۆ دروستکرا و ئامادەیە بۆ سەبمیت و ئەرشیف.",
            route('transactions.show', $transaction->id)
        );

        return redirect()->back()->with('success', "ژمارەی دەفتەر ({$bookletNum}) بە سەرکەوتوویی تۆمارکرا و چاپی دەفتەر ئەکتیڤ بوو.");
    }

    public function printPaymentReceipt(Request $request, Transaction $transaction)
    {
        if ($transaction->is_submitted && !auth()->user()->isAdmin()) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دوای تەواوبوونی سەبمیت تەنها ئەدمین دەتوانێت پسوولەی ۳۷/أ چاپ بکات.');
        }

        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('prints.receipt')) {
            return redirect()->back()->with('error', 'دەسەڵاتی چاپی پسوولەی ۳۷/أ (پارەدان)ت نییە بەپێی پێرمشنەکان!');
        }

        if (!$transaction->is_paid) {
            return redirect()->back()->with('error', 'پسوولەی پارەدان شایەنی چاپکردن نییە چونکە مامەڵەکە پارەدانی بۆ ئەنجام نەدراوە.');
        }

        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director', 'parentTransaction', 'relatedTransactions']);

        $linkedPledge = $transaction->getLinkedPledgeOrParent();
        if ($linkedPledge && $linkedPledge->is_paid) {
            $linkedPledge->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director']);
        } else {
            $linkedPledge = null;
        }

        return view('transactions.print_payment_receipt', compact('transaction', 'linkedPledge'));
    }

    public function submitArchive(Request $request, Transaction $transaction)
    {
        // 4. Submit restricted strictly to Archiver and Admin only
        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'archiver') {
            return redirect()->back()->with('error', 'سەبمیتکردنی کۆتایی مامەڵە تەنها لە دەسەڵاتی کارمەندی ئەرشیف و ئەدمیندایە!');
        }

        // 4. Transaction types allowed for archiving: Booklet issue, renewal, name change, booklet change, cancellation, name & booklet change
        $allowedSubmitTypes = [1, 2, 3, 4, 5, 7];
        if (!in_array((int)$transaction->transaction_type_id, $allowedSubmitTypes)) {
            $typeName = $transaction->transactionType->name_kurdish ?? 'دیاری نەکراو';
            return redirect()->back()->with('error', "ئەم جۆرە مامەڵەیە [{$typeName}] سەبمیتی کۆتایی ئەرشیفی نییە.");
        }

        if (!$transaction->is_paid) {
            return redirect()->back()->with('error', 'مامەڵەکە سەرجەم قۆناغەکانی تێنەپەڕاندووە (پارەدان تێنەپەڕیوە) بۆ سەبمیت.');
        }

        if ($transaction->requiresBooklet() && !$transaction->is_booklet_completed) {
            return redirect()->back()->with('error', 'ئەم مامەڵەیە پێویستی بە چاپی دەفتەر هەیە و دەفتەری بۆ تەواو نەکراوە!');
        }

        $updateData = [
            'is_submitted' => true,
            'submitted_by' => auth()->user()?->name ?? 'کارمەند',
            'submitted_at' => now(),
        ];

        // 5. Box file number validation: REQUIRED for all 6 main archive types (1, 2, 3, 4, 5, 7)
        if (in_array((int)$transaction->transaction_type_id, [1, 2, 3, 4, 7])) {
            $request->validate([
                'NO_boxfile' => 'required|numeric|min:1',
            ], [
                'NO_boxfile.required' => 'داخڵکردنی ژمارەی بۆکس فایلی ئەرشیف داواکراوە (ئیجبارییە)!',
                'NO_boxfile.numeric' => 'ژمارەی بۆکس دەبێت بە ژمارە بێت.',
            ]);
            $updateData['NO_boxfile'] = (int)$request->NO_boxfile;
        } elseif ((int)$transaction->transaction_type_id === 5) {
            // Type 5: پووچەڵکردنەوەی دەفتەر -> نووسراوی گومرگ و بەروار و بۆکسی پووچەڵ
            $request->validate([
                'no_nusraw_puchal' => 'required|string|max:100',
                'date_nusraw_puchal' => 'required|date',
                'NO_boxfile_puchal' => 'required|numeric|min:1',
            ], [
                'no_nusraw_puchal.required' => 'تکایە ژمارەی نووسراوی گومرگ بنووسە.',
                'date_nusraw_puchal.required' => 'تکایە بەرواری نووسراوی گومرگ دیاری بکە.',
                'NO_boxfile_puchal.required' => 'تکایە ژمارەی بۆکسی پووچەڵ بنووسە.',
                'NO_boxfile_puchal.numeric' => 'ژمارەی بۆکسی پووچەڵ دەبێت بە ژمارە بێت.',
            ]);
            $updateData['no_nusraw_puchal'] = trim($request->no_nusraw_puchal);
            $updateData['date_nusraw_puchal'] = $request->date_nusraw_puchal;
            $updateData['NO_boxfile_puchal'] = (int)$request->NO_boxfile_puchal;
        } else {
            // For pledge (6) or inspection (8): Box number is not required
            if ($request->filled('NO_boxfile')) {
                $updateData['NO_boxfile'] = (int)$request->NO_boxfile;
            }
        }

        $transaction->update($updateData);

        // Auto-archive companion pledge if Booklet Issue was submitted
        $linked = $transaction->getLinkedPledgeOrParent();
        if ($linked && !$linked->is_submitted) {
            $linked->update([
                'is_submitted' => true,
                'submitted_by' => auth()->user()?->name ?? 'کارمەند',
                'submitted_at' => now(),
                'NO_boxfile' => $updateData['NO_boxfile'] ?? null,
            ]);
            AuditLogger::log('ئەرشیفکردنی بەڵێننامەی بەستراو', Transaction::class, $linked->id, "بەڵێننامەی بەستراو ئەرشیفکرا لەگەڵ دەرهێنانی دەفتەر");
        }

        AuditLogger::log('سەبمیتکردن و ئەرشیفکردنی مامەڵە (ستێپی کۆتایی)', Transaction::class, $transaction->id, "مامەڵەی بارکۆد {$transaction->barcode} لەلایەن " . (auth()->user()?->name ?? 'کارمەند') . " سەبمیت و ئەرشیف کرا");

        NotificationService::send(
            $transaction->created_by,
            'data_entry',
            'مامەڵە بە سەرکەوتوویی ئەرشیفکرا',
            "مامەڵەی ({$transaction->visitor_name}) بە بارکۆدی {$transaction->barcode} بە تەواوەتی سەبمیت و ئەرشیفکرا.",
            route('transactions.show', $transaction->id)
        );

        return redirect()->back()->with('success', 'مامەڵەکە بە سەرکەوتوویی نێردرا بۆ ستێپی کۆتایی (سەبمیت و ئەرشیفکرا).');
    }

    public function revertStep(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'تەنها ئەدمین ڕێگەپێدراوە بۆ ڕێکخستنەوەی قۆناغی مامەڵە (Revert Step)!');
        }

        $request->validate([
            'step' => 'required|in:payment,booklet,audit,inspection,archive,all',
            'reversion_reason' => 'required|string|min:4',
            'receipt_number' => 'nullable|string',
            'booklet_number' => 'nullable|string',
        ]);

        $step = $request->step;
        $reason = trim($request->reversion_reason);
        $oldValues = [];
        $newValues = [];

        if ($step === 'payment') {
            if ($transaction->receipt_37a_number) {
                $enteredReceipt = trim($request->receipt_number ?? '');
                if (empty($enteredReceipt)) {
                    return redirect()->back()->with('error', 'تکایە ژمارەی پسوولەی ۳۷/أ بنووسە بۆ هەڵوەشاندنەوە!');
                }
                if ($enteredReceipt !== trim($transaction->receipt_37a_number)) {
                    return redirect()->back()->with('error', "ژمارەی پسوولەی داواکراو بۆ هەڵوەشاندنەوە ({$enteredReceipt}) هاوتای ژمارەی پسوولەی تۆمارکراو نییە ({$transaction->receipt_37a_number})!");
                }
            }

            $oldValues = [
                'is_paid' => $transaction->is_paid,
                'receipt_37a_number' => $transaction->receipt_37a_number,
                'is_audited' => $transaction->is_audited,
                'is_submitted' => $transaction->is_submitted,
            ];

            $receiptToBlock = trim($request->receipt_number ?: $transaction->receipt_37a_number);
            if ($receiptToBlock) {
                BlockedReceiptNumber::firstOrCreate(
                    ['receipt_number' => $receiptToBlock],
                    [
                        'cancellation_reason' => "هەڵوەشاندنەوەی قۆناغی پارەدان لەلایەن ئەدمین - هۆکار: {$reason}",
                        'blocked_by' => auth()->user()?->name ?? 'ئەدمین',
                        'transaction_id' => $transaction->id,
                    ]
                );
            }

            // خاڵی ٦.١: هەڵوەشاندنەوەی پارەدان وردبینی چالاک دەکاتەوە بۆ سەرلەنوێ وردبینیکردن
            // خاڵی ٦.٤: هەڵوەشاندنەوەی سەبمیت ئەگەر پێشتر سەبمیت کرابوو
            $transaction->update([
                'is_paid' => false,
                'receipt_37a_number' => null,
                'paid_by' => null,
                'paid_at' => null,
                'is_audited' => false,
                'audited_by' => null,
                'audited_at' => null,
                'is_submitted' => false,
                'submitted_by' => null,
                'submitted_at' => null,
            ]);

            $newValues = [
                'is_paid' => false,
                'receipt_37a_number' => null,
                'is_audited' => false,
                'is_submitted' => false,
            ];

            $msg = "قۆناغی پارەدان هەڵوەشێنرایەوە و پسوولەی ۳۷/أ پووچەڵکرا و ناگەڕێتەوە. قۆناغی وردبینی چالاککرایەوە بۆ سەرلەنوێ وردبینیکردن و سەبمیت ڕێکخرایەوە.";

        } elseif ($step === 'booklet') {
            if ($transaction->booklet_number) {
                $enteredBooklet = trim($request->booklet_number ?? '');
                if (empty($enteredBooklet)) {
                    return redirect()->back()->with('error', 'تکایە ژمارەی دەفتەر بنووسە بۆ هەڵوەشاندنەوە!');
                }
                if ($enteredBooklet !== trim($transaction->booklet_number)) {
                    return redirect()->back()->with('error', "ژمارەی دەفتەری داواکراو بۆ هەڵوەشاندنەوە ({$enteredBooklet}) هاوتای ژمارەی دەفتەری تۆمارکراو نییە ({$transaction->booklet_number})!");
                }
            }

            $oldValues = [
                'is_booklet_completed' => $transaction->is_booklet_completed,
                'booklet_number' => $transaction->booklet_number,
                'is_submitted' => $transaction->is_submitted,
            ];

            $bookletToBlock = trim($request->booklet_number ?: $transaction->booklet_number);
            if ($bookletToBlock) {
                BlockedReceiptNumber::firstOrCreate(
                    ['receipt_number' => $bookletToBlock],
                    [
                        'cancellation_reason' => "هەڵوەشاندنەوەی دەفتەر لەلایەن ئەدمین - هۆکار: {$reason}",
                        'blocked_by' => auth()->user()?->name ?? 'ئەدمین',
                        'transaction_id' => $transaction->id,
                    ]
                );
            }

            // خاڵی ٦.٤: هەڵوەشاندنەوەی سەبمیت ئەگەر گەشتبووە سەبمیت
            $transaction->update([
                'is_booklet_completed' => false,
                'booklet_number' => null,
                'booklet_by' => null,
                'booklet_completed_at' => null,
                'is_submitted' => false,
                'submitted_by' => null,
                'submitted_at' => null,
            ]);

            $newValues = [
                'is_booklet_completed' => false,
                'booklet_number' => null,
                'is_submitted' => false,
            ];

            $msg = "قۆناغی دەفتەر هەڵوەشێنرایەوە و ژمارەی دەفتەر پووچەڵکرایەوە. سەبمیت ڕێکخرایەوە بۆ سەرلەنوێ سەبمیتکردنەوە.";

        } elseif ($step === 'audit') {
            $oldValues = [
                'is_audited' => $transaction->is_audited,
                'is_submitted' => $transaction->is_submitted,
            ];

            $transaction->update([
                'is_audited' => false,
                'audited_by' => null,
                'audited_at' => null,
                'is_submitted' => false,
                'submitted_by' => null,
                'submitted_at' => null,
            ]);

            $newValues = ['is_audited' => false, 'is_submitted' => false];
            $msg = "قۆناغی وردبینی ڕێکخرایەوە و سەبمیت هەڵوەشێنرایەوە.";

        } elseif ($step === 'inspection') {
            $oldValues = [
                'is_inspected' => $transaction->is_inspected,
                'is_submitted' => $transaction->is_submitted,
            ];

            $transaction->update([
                'is_inspected' => false,
                'inspected_by' => null,
                'inspected_at' => null,
                'is_submitted' => false,
                'submitted_by' => null,
                'submitted_at' => null,
            ]);

            $newValues = ['is_inspected' => false, 'is_submitted' => false];
            $msg = "قۆناغی کەشف و تەخمین ڕێکخرایەوە و سەبمیت هەڵوەشێنرایەوە.";

        } elseif ($step === 'archive') {
            $oldValues = [
                'is_submitted' => $transaction->is_submitted,
                'submitted_by' => $transaction->submitted_by,
                'submitted_at' => $transaction->submitted_at,
            ];

            $transaction->update([
                'is_submitted' => false,
                'submitted_by' => null,
                'submitted_at' => null,
            ]);

            $newValues = ['is_submitted' => false, 'submitted_by' => null, 'submitted_at' => null];
            $msg = "سەبمیت و ئەرشیفکردنی مامەڵەکە هەڵوەشێنرایەوە و مامەڵەکە کرایەوە بۆ دەستکاری و چاپی نوێ.";

        } elseif ($step === 'all') {
            $oldValues = [
                'is_inspected' => $transaction->is_inspected,
                'is_audited' => $transaction->is_audited,
                'is_paid' => $transaction->is_paid,
                'receipt_37a_number' => $transaction->receipt_37a_number,
                'is_booklet_completed' => $transaction->is_booklet_completed,
                'booklet_number' => $transaction->booklet_number,
                'is_submitted' => $transaction->is_submitted,
            ];

            if ($transaction->receipt_37a_number) {
                BlockedReceiptNumber::firstOrCreate(
                    ['receipt_number' => $transaction->receipt_37a_number],
                    [
                        'cancellation_reason' => "ڕێکخستنەوەی سەرجەم قۆناغەکان لەلایەن ئەدمین - هۆکار: {$reason}",
                        'blocked_by' => auth()->user()?->name ?? 'ئەدمین',
                        'transaction_id' => $transaction->id,
                    ]
                );
            }

            if ($transaction->booklet_number) {
                BlockedReceiptNumber::firstOrCreate(
                    ['receipt_number' => $transaction->booklet_number],
                    [
                        'cancellation_reason' => "ڕێکخستنەوەی سەرجەم قۆناغەکان لەلایەن ئەدمین - هۆکار: {$reason}",
                        'blocked_by' => auth()->user()?->name ?? 'ئەدمین',
                        'transaction_id' => $transaction->id,
                    ]
                );
            }

            $transaction->update([
                'is_inspected' => false,
                'inspected_by' => null,
                'inspected_at' => null,
                'is_audited' => false,
                'audited_by' => null,
                'audited_at' => null,
                'is_paid' => false,
                'receipt_37a_number' => null,
                'paid_by' => null,
                'paid_at' => null,
                'is_booklet_completed' => false,
                'booklet_number' => null,
                'booklet_by' => null,
                'booklet_completed_at' => null,
                'is_submitted' => false,
                'submitted_by' => null,
                'submitted_at' => null,
                'is_returned' => false,
            ]);

            $newValues = ['all_reset' => true, 'is_submitted' => false];
            $msg = "سەرجەم قۆناغەکانی مامەڵە گەڕێنرانەوە بۆ سەرەتا (داتائەنتەری) و سەرلەنوێ کرانەوە.";
        }

        // Auto-sync revertStep for companion pledge or booklet (خاڵی ۳ - هەر جوڵەیەک پێکەوە بێت)
        $linked = $transaction->getLinkedPledgeOrParent();
        if ($linked) {
            if ($step === 'payment') {
                $linked->update([
                    'is_paid' => false,
                    'receipt_37a_number' => null,
                    'paid_by' => null,
                    'paid_at' => null,
                    'is_audited' => false,
                    'audited_by' => null,
                    'audited_at' => null,
                    'is_submitted' => false,
                    'submitted_by' => null,
                    'submitted_at' => null,
                ]);
            } elseif ($step === 'audit') {
                $linked->update([
                    'is_audited' => false,
                    'audited_by' => null,
                    'audited_at' => null,
                    'is_submitted' => false,
                    'submitted_by' => null,
                    'submitted_at' => null,
                ]);
            } elseif ($step === 'inspection') {
                $linked->update([
                    'is_inspected' => false,
                    'inspected_by' => null,
                    'inspected_at' => null,
                    'is_submitted' => false,
                    'submitted_by' => null,
                    'submitted_at' => null,
                ]);
            } elseif ($step === 'archive') {
                $linked->update([
                    'is_submitted' => false,
                    'submitted_by' => null,
                    'submitted_at' => null,
                ]);
            } elseif ($step === 'all') {
                $linked->update([
                    'is_inspected' => false,
                    'inspected_by' => null,
                    'inspected_at' => null,
                    'is_audited' => false,
                    'audited_by' => null,
                    'audited_at' => null,
                    'is_paid' => false,
                    'receipt_37a_number' => null,
                    'paid_by' => null,
                    'paid_at' => null,
                    'is_submitted' => false,
                    'submitted_by' => null,
                    'submitted_at' => null,
                    'is_returned' => false,
                ]);
            }
            AuditLogger::log('ڕێکخستنەوەی قۆناغی بەڵێننامەی بەستراو', Transaction::class, $linked->id, "قۆناغی [{$step}] ڕێکخرایەوە لەگەڵ دەرهێنانی دەفتەر");
        }

        AuditLogger::log('ڕێکخستنەوەی قۆناغ (Step Reversion)', Transaction::class, $transaction->id, "ڕێکخستنەوەی قۆناغی [{$step}] لەلایەن ئەدمین - هۆکار: {$reason}", $oldValues, $newValues);
        NotificationService::send(null, 'data_entry', 'قۆناغی مامەڵە ڕێکخرایەوە', "ئەدمین قۆناغی [{$step}] بۆ بارکۆدی {$transaction->barcode} ڕێکخستەوە. هۆکار: {$reason}", route('transactions.show', $transaction->id));

        return redirect()->route('transactions.show', $transaction->id)->with('success', $msg);
    }

    public function cancel(Request $request, Transaction $transaction)
    {
        if (!auth()->check() || !auth()->user()->hasPermission('transactions.cancel')) {
            return redirect()->back()->with('error', 'دەسەڵاتی پووچەڵکردنەوەی مامەڵەت نییە بەپێی پێرمشنەکان.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string',
            'no_nusraw_puchal' => 'nullable|string',
        ]);

        if ($transaction->receipt_37a_number) {
            BlockedReceiptNumber::firstOrCreate(
                ['receipt_number' => $transaction->receipt_37a_number],
                [
                    'cancellation_reason' => $request->cancellation_reason,
                    'blocked_by' => auth()->user()?->name ?? 'ئەدمین',
                    'transaction_id' => $transaction->id,
                ]
            );
        }

        // Ensure is_cancelled column exists on transactions table prior to update
        if (!\Illuminate\Support\Facades\Schema::hasColumn('transactions', 'is_cancelled')) {
            try {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE transactions ADD COLUMN is_cancelled INTEGER NOT NULL DEFAULT 0');
            } catch (\Throwable $e) {}
        }

        $transaction->update([
            'is_cancelled' => true,
            'cancellation_code' => 'CNCL-' . strtoupper(Str::random(6)),
            'cancellation_reason' => $request->cancellation_reason,
            'no_nusraw_puchal' => $request->no_nusraw_puchal,
            'cancelled_by' => auth()->user()?->name ?? 'بەکار‌هێنەر',
            'cancelled_at' => now(),
        ]);

        AuditLogger::log('پووچەڵکردنەوەی مامەڵە', Transaction::class, $transaction->id, "هۆکار: {$request->cancellation_reason}");

        return redirect()->back()->with('success', 'مامەڵەکە بە سەرکەوتوویی پووچەڵکرایەوە.');
    }

    public function printReceipt(Request $request, Transaction $transaction)
    {
        if ($transaction->is_submitted && !auth()->user()->isAdmin()) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دوای تەواوبوونی سەبمیت تەنها ئەدمین دەتوانێت پسوولەی کەشف و تەخمین چاپ بکات.');
        }

        $user = auth()->user();

        // 1. Only Estimator (inspector) or Admin, or user with permission
        if (!$user->isAdmin() && !$user->hasPermission('prints.inspection')) {
            return redirect()->back()->with('error', 'دەسەڵاتی چاپی پسولەی کەشف و خەمڵاندنت نییە بەپێی پێرمشنەکان.');
        }

        // 2. Must be approved by Estimator first
        if (!$transaction->is_inspected && !$user->isAdmin()) {
            return redirect()->back()->with('error', 'پسولەی کەشف و خەمڵاندن تەنها دوای پەسەندکردنی کەشف و تەخمین چاڵاک دەبێت.');
        }

        // 3. Locked for Estimator once Auditor approves
        if ($transaction->is_audited && !$user->isAdmin() && $user->role === 'inspector') {
            return redirect()->back()->with('error', 'وردبینی بۆ ئەم مامەڵەیە ئەنجامدراوە. فەرمانبەری تەخمین ناتوانێت دوای وردبینی پسولەی کەشف چاپ بکات.');
        }

        $mode = $request->query('mode', 'both'); // 'both', 'main', 'pledge'
        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor']);

        $linkedPledge = $transaction->getLinkedPledgeOrParent();
        if ($linkedPledge) {
            $linkedPledge->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor']);
        }

        return view('transactions.print_receipt', compact('transaction', 'linkedPledge', 'mode'));
    }

    public function printDataEntry(Request $request, Transaction $transaction)
    {
        if ($transaction->is_submitted && !auth()->user()->isAdmin()) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دوای تەواوبوونی سەبمیت تەنها ئەدمین دەتوانێت فیشەی داتائەنتەری چاپ بکات.');
        }

        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('prints.data_entry')) {
            return redirect()->back()->with('error', 'دەسەڵاتی چاپی فیشەی داتائەنتەریت نییە بەپێی پێرمشنەکان!');
        }

        $mode = $request->query('mode', 'both'); // 'both', 'main', 'pledge'

        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director', 'parentTransaction', 'relatedTransactions']);
        $linkedPledge = $transaction->getLinkedPledgeOrParent();
        if ($linkedPledge) {
            $linkedPledge->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor']);
        }
        return view('transactions.print_data_entry', compact('transaction', 'linkedPledge', 'mode'));
    }

    public function printBooklet(Transaction $transaction)
    {
        if ($transaction->is_submitted && !auth()->user()->isAdmin()) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دوای تەواوبوونی سەبمیت تەنها ئەدمین دەتوانێت دەفتەر چاپ بکات.');
        }

        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('prints.booklet')) {
            return redirect()->back()->with('error', 'دەسەڵاتی چاپی دەفتەرت نییە بەپێی پێرمشنەکان!');
        }

        if (empty($transaction->booklet_number)) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'هەتا ژمارەی دەفتەر (Booklet Number) وەرنەگرێت، چاپی دەفتەر ئەکتیڤ نابێت و ناتوانرێت چاپ بکرێت!');
        }

        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director']);

        $templatePath = storage_path('app/booklet_template.json');
        $savedTemplate = file_exists($templatePath) ? json_decode(file_get_contents($templatePath), true) : null;

        return view('transactions.print_booklet', compact('transaction', 'savedTemplate'));
    }

    public function saveBookletTemplate(Request $request)
    {
        $validated = $request->validate([
            'template' => 'required|array',
        ]);

        $path = storage_path('app/booklet_template.json');
        file_put_contents($path, json_encode($validated['template'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json([
            'status' => 'success',
            'message' => 'دیزاینی دەفتەرەکە لەسەر سێرڤەر بە سەرکەوتوویی پاشەکەوت کرا و بۆ هەموو کۆمپیوتەرەکان جێبەجێ بوو!'
        ]);
    }

    public function resetBookletTemplate()
    {
        $path = storage_path('app/booklet_template.json');
        if (file_exists($path)) {
            unlink($path);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'دیزاینی دەفتەرەکە لەسەر سێرڤەر گەڕایەوە بۆ باری بنەڕەتی کارگە!'
        ]);
    }

    public function printPledge(Transaction $transaction)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->hasPermission('prints.pledge')) {
            return redirect()->back()->with('error', 'دەسەڵاتی چاپی بەڵێننامەت نییە بەپێی پێرمشنەکان.');
        }

        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director', 'parentTransaction']);
        return view('transactions.print_pledge', compact('transaction'));
    }

    public function createRelatedPledge(Transaction $transaction)
    {
        $newTransaction = DB::transaction(function () use ($transaction) {
            $barcode = BarcodeService::generateNextBarcode();
            $pledgeType = TransactionType::where('name_kurdish', 'like', '%بەڵێننامە%')->first();
            $pledgeTypeId = $pledgeType ? $pledgeType->id : 6;
            $fees = FeeCalculatorService::calculate($pledgeTypeId, 1, false, null, null);

            $pledgeData = [
                'parent_transaction_id' => $transaction->id,
                'transaction_type_id' => $pledgeTypeId, // بەڵێننامە
                'traffic_directorate_id' => $transaction->traffic_directorate_id,
                'director_id' => $transaction->director_id,
                'plate_type_id' => $transaction->plate_type_id,
                'car_make_id' => $transaction->car_make_id,
                'car_color_id' => $transaction->car_color_id,
                'visitor_name' => $transaction->visitor_name,
                'visitor_name_eng' => $transaction->visitor_name_eng,
                'second_driver_name' => $transaction->second_driver_name,
                'second_driver_name_eng' => $transaction->second_driver_name_eng,
                'phone_number' => $transaction->phone_number,
                'passport_number' => $transaction->passport_number,
                'address' => $transaction->address,
                'passport_issue_date' => $transaction->passport_issue_date,
                'passport_issue_place' => $transaction->passport_issue_place,
                'plate_number' => $transaction->plate_number,
                'model_year' => $transaction->model_year,
                'chassis_number' => $transaction->chassis_number,
                'piston_count' => $transaction->piston_count,
                'salana_number' => $transaction->salana_number,
                'barcode' => $barcode,
                'num_years' => 1,
                'pay_amount_years' => $fees['pay_amount_years'],
                'pay_stamp' => $fees['pay_stamp'],
                'pay_form' => $fees['pay_form'],
                'pay_fine' => $fees['pay_fine'],
                'pay_inspection' => $fees['pay_inspection'],
                'total_pay' => $fees['total_pay'],
                'transaction_date' => now(),
                'user_input' => auth()->user()?->name ?? 'کارمەند',
                'is_returned' => false,
            ];

            $newTx = Transaction::create($pledgeData);

            AuditLogger::log('دروستکردنی بەڵێننامەی بەستراوە', Transaction::class, $newTx->id, "بەستنەوە لەگەڵ بارکۆدی مامەڵەی بنەڕەتی: {$transaction->barcode}", null, $pledgeData);
            NotificationService::send(null, 'inspector', 'مامەڵەی بەڵێننامەی بەستراوە دروستکرا', "مامەڵەی بەڵێننامە بۆ {$transaction->visitor_name} دروستکرا بە بارکۆدی {$barcode}.", route('transactions.show', $newTx->id));

            return $newTx;
        });

        return redirect()->route('transactions.show', $newTransaction->id)
            ->with('success', "مامەڵەی بەڵێننامەی بەستراوە بە سەرکەوتوویی دروستکرا بە بارکۆدی نوێ: {$newTransaction->barcode}");
    }

    public function trashIndex(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'تەنها ئەدمین دەسەڵاتی بینینی ئەرشیفی سڕاوەکانی هەیە.');
        }

        $query = Transaction::onlyTrashed()->with(['transactionType', 'plateType', 'carMake', 'carColor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('barcode', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhere('chassis_number', 'like', "%{$search}%");
            });
        }

        $trashedCount = Transaction::onlyTrashed()->count();
        $transactions = $query->latest('deleted_at')->paginate(15)->withQueryString();

        return view('transactions.trash', compact('transactions', 'trashedCount'));
    }

    public function destroy(Transaction $transaction)
    {
        if (!auth()->user()->hasPermission('transactions.delete')) {
            return redirect()->back()->with('error', 'دەسەڵاتی سڕینەوەی کاتیت نییە بەپێی پێرمشنەکان!');
        }

        $barcode = $transaction->barcode;
        $name = $transaction->visitor_name;
        $transaction->delete(); // Soft Delete

        AuditLogger::log('سڕینەوەی کاتی (Soft Delete)', Transaction::class, $transaction->id, "مامەڵەی بارکۆد {$barcode} خرایە ئەرشیفی سڕدراوەکان");

        if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver')) {
            return redirect()->route('transactions.index')->with('success', "مامەڵەی بارکۆد ({$barcode}) بە سەرکەوتوویی خرایە ئەرشیفی سڕدراوەکانەوە (تەنەکەخۆڵ).");
        }
        return redirect()->route('dashboard')->with('success', "مامەڵەی بارکۆد ({$barcode}) بە سەرکەوتوویی خرایە ئەرشیفی سڕدراوەکانەوە.");
    }

    public function restore($id)
    {
        if (!auth()->user()->hasPermission('transactions.restore')) {
            return redirect()->back()->with('error', 'دەسەڵاتی گەڕاندنەوەی مامەڵەی سڕدراوەت نییە بەپێی پێرمشنەکان!');
        }

        $transaction = Transaction::onlyTrashed()->findOrFail($id);
        $transaction->restore();

        AuditLogger::log('گەڕاندنەوەی مامەڵەی سڕدراوە لە تەنەکەخۆڵ', Transaction::class, $transaction->id, "بارکۆد: {$transaction->barcode} - ناوی هاووڵاتی: {$transaction->visitor_name}");

        return redirect()->route('transactions.trash')->with('success', "مامەڵەی بارکۆد ({$transaction->barcode}) بە سەرکەوتوویی گەڕێنرایەوە بۆ ناو سیستم.");
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'دەسەڵاتی سڕینەوەی یەکجاریت نییە!');
        }

        $transaction = Transaction::onlyTrashed()->findOrFail($id);
        $barcode = $transaction->barcode;
        $name = $transaction->visitor_name;

        AuditLogger::log('سڕینەوەی بنەڕەتی لە داتابەیس (Force Delete)', Transaction::class, $transaction->id, "مامەڵەی بارکۆد: {$barcode} - هاووڵاتی: {$name} بە یەکجاری سڕایەوە");

        $transaction->forceDelete();

        return redirect()->route('transactions.trash')->with('success', "مامەڵەی بارکۆد ({$barcode}) بە یەکجاری و بنەڕەتی لە داتابەیس سڕایەوە.");
    }

    public function calculateApi(Request $request)
    {
        $fees = FeeCalculatorService::calculate(
            (int)$request->get('transaction_type_id', 1),
            (int)$request->get('num_years', 1),
            ($request->get('plate_type_id') == 4),
            $request->get('start_date'),
            $request->get('end_date')
        );

        return response()->json($fees);
    }

    /**
     * API endpoint for instant live search / scan lookup by barcode or plate/chassis.
     */
    public function apiLookup(Request $request)
    {
        $term = trim($request->get('term') ?? $request->get('barcode') ?? '');
        if (empty($term)) {
            return response()->json(['found' => false]);
        }

        $tx = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake', 'carColor', 'plateType', 'director'])
            ->where('barcode', $term)
            ->orWhere('plate_number', $term)
            ->orWhere('chassis_number', $term)
            ->orWhere('phone_number', $term)
            ->orWhere('booklet_number', $term)
            ->latest()
            ->first();

        if (!$tx) {
            return response()->json([
                'found' => false, 
                'message' => "هیچ مامەڵەیەک نەدۆزرایەوە بە گەڕانی: {$term}"
            ]);
        }

        $location = $tx->getCurrentLocationDescription();

        return response()->json([
            'found' => true,
            'id' => $tx->id,
            'barcode' => $tx->barcode,
            'visitor_name' => $tx->visitor_name,
            'visitor_name_eng' => $tx->visitor_name_eng,
            'second_driver_name' => $tx->second_driver_name,
            'phone_number' => $tx->phone_number,
            'passport_number' => $tx->passport_number,
            'plate_number' => $tx->plate_number,
            'plate_type' => $tx->plateType?->name_kurdish ?? '',
            'car_make' => $tx->carMake?->name_kurdish ?? '',
            'car_color' => $tx->carColor?->name_kurdish ?? '',
            'model_year' => $tx->model_year,
            'chassis_number' => $tx->chassis_number,
            'transaction_type' => $tx->transactionType?->name_kurdish ?? '',
            'transaction_date' => $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : ($tx->created_at ? $tx->created_at->format('Y-m-d') : ''),
            'created_at' => $tx->created_at?->format('Y-m-d H:i') ?? '',
            'user_input' => $tx->user_input,
            'total_pay' => number_format($tx->total_pay),
            'receipt_37a_number' => $tx->receipt_37a_number,
            'booklet_number' => $tx->booklet_number,
            
            // Where is it?
            'location' => $location,
            'current_stage_name' => $tx->getCurrentStageName(),

            // Pipeline checks
            'is_returned' => (bool)$tx->is_returned,
            'return_reason' => $tx->return_reason,
            'is_cancelled' => (bool)$tx->is_cancelled,
            'is_inspected' => (bool)$tx->is_inspected,
            'inspected_by' => $tx->inspected_by,
            'inspected_at' => $tx->inspected_at?->format('Y-m-d H:i'),
            'is_audited' => (bool)$tx->is_audited,
            'audited_by' => $tx->audited_by,
            'audited_at' => $tx->audited_at?->format('Y-m-d H:i'),
            'is_paid' => (bool)$tx->is_paid,
            'paid_by' => $tx->paid_by,
            'paid_at' => $tx->paid_at?->format('Y-m-d H:i'),
            'is_booklet_completed' => (bool)$tx->is_booklet_completed,
            'booklet_by' => $tx->booklet_by,
            'booklet_completed_at' => $tx->booklet_completed_at?->format('Y-m-d H:i'),
            'is_submitted' => (bool)$tx->is_submitted,
            'submitted_by' => $tx->submitted_by,
            'requires_booklet' => $tx->requiresBooklet(),

            // URLs
            'print_url' => route('transactions.print_data_entry', $tx->id),
            'show_url' => route('transactions.show', $tx->id),
            'edit_url' => route('transactions.edit', $tx->id),
        ]);
    }

    public function markPrinted(Transaction $transaction)
    {
        if ($transaction->requiresBooklet() && empty($transaction->booklet_number)) {
            return redirect()->back()->with('error', 'ناتوانرێت مامەڵەکە تەواو و ئەرشیف بکرێت! سەرەتا دەبێت ژمارەی دەفتەر تۆمار بکرێت پاشان چاپ بکرێت.');
        }

        $transaction->update([
            'is_printed' => true,
            'is_submitted' => true,
            'submitted_by' => auth()->user()?->name ?? 'کارمەندی چاپ',
            'submitted_at' => now(),
        ]);

        AuditLogger::log('تەواوکردنی چاپی نوسراوی فەرمی', Transaction::class, $transaction->id, "مامەڵەی بارکۆد {$transaction->barcode} گوێزرایەوە بۆ بەشی چاپکراوەکان");

        return redirect()->route('transactions.official_prints', ['tab' => 'printed'])->with('success', 'نووسراوەکە بە سەرکەوتوویی گوێزرایەوە بۆ بەشی چاپکراوەکان (Printed Archive).');
    }

    public function printCancellation(Transaction $transaction)
    {
        if ($transaction->is_submitted && !auth()->user()->isAdmin()) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دوای تەواوبوونی سەبمیت تەنها ئەدمین دەتوانێت نوسراوی پووچەڵکردنەوە چاپ بکات.');
        }

        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('prints.cancellation')) {
            return redirect()->back()->with('error', 'دەسەڵاتی چاپی نووسراوی پووچەڵکردنەوەت نییە بەپێی پێرمشنەکان!');
        }

        $transaction->update(['is_printed' => true]);
        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director']);
        return view('transactions.print_cancellation', compact('transaction'));
    }

    public function printRestriction(Transaction $transaction)
    {
        if ($transaction->is_submitted && !auth()->user()->isAdmin()) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دوای تەواوبوونی سەبمیت تەنها ئەدمین دەتوانێت نوسراوی ڕەفتارنەکردن چاپ بکات.');
        }

        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('prints.restriction')) {
            return redirect()->back()->with('error', 'دەسەڵاتی چاپی نووسراوی ڕەفتارنەکردنت نییە بەپێی پێرمشنەکان!');
        }

        $transaction->update(['is_printed' => true]);
        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director']);
        return view('transactions.print_restriction', compact('transaction'));
    }

    public function printInspectionLetter(Transaction $transaction)
    {
        // 8. Inspection letter available in Cashier and Archiver stages
        if ($transaction->is_submitted && !auth()->user()->isAdmin() && auth()->user()->role !== 'archiver') {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دوای تەواوبوونی سەبمیت تەنها کارمەندی ئەرشیف و ئەدمین دەتوانن نوسراوی پشکنین چاپ بکەن.');
        }

        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'archiver' && auth()->user()->role !== 'cashier' && !auth()->user()->hasPermission('prints.inspection_letter')) {
            return redirect()->back()->with('error', 'دەسەڵاتی چاپی نووسراوی پشکنینت نییە بەپێی پێرمشنەکان!');
        }

        if (!$transaction->is_paid) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'نووسراوی پشکنین تەنها لە قۆناغی پارەدان چاپ دەکرێت دوای وەرگرتنی پارە، لەبەر تۆمارکردنی ژمارەی پسوولەی ۳۷/أ و بەرواری پارەدان.');
        }

        $transaction->update(['is_printed' => true]);
        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director']);
        return view('transactions.print_inspection_letter', compact('transaction'));
    }
}

