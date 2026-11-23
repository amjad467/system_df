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
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Self-heal: If database is empty on hosted service, automatically restore all 84 baseline records
        if (!\Illuminate\Support\Facades\Cache::get('db_seeded_confirmed', false)) {
            if (Transaction::count() === 0) {
                \App\Services\DataRestoreService::restoreAll();
            }
            \Illuminate\Support\Facades\Cache::forever('db_seeded_confirmed', true);
        }

        $today = now()->toDateString();

        // ڕێکخستنی بەروار و گەڕان: ئەگەر فلتەر دیارینەکرابوو، بە شێوەی بنەڕەتی 'all' (گشت کاتەکان) بەکاربێنە
        $dateFilter = $request->get('date_filter');
        if (!$dateFilter) {
            if ($request->filled('number_from') && $request->filled('number_to')) {
                $dateFilter = 'number_between';
            } elseif ($request->filled('number_exact_val')) {
                $dateFilter = 'number_exact';
            } elseif ($request->filled('exact_date')) {
                $dateFilter = 'exact';
            } elseif ($request->filled('from_date') || $request->filled('to_date')) {
                $dateFilter = 'custom';
            } else {
                $dateFilter = 'all';
            }
        }

        // کۆکردنەوەی ساڵە بەردەستەکان بۆ درۆپ داونی ساڵەکان
        try {
            if (config('database.default') === 'sqlite') {
                $availableYears = Transaction::selectRaw("DISTINCT strftime('%Y', transaction_date) as yr")
                    ->whereNotNull('transaction_date')
                    ->orderByDesc('yr')
                    ->pluck('yr')
                    ->filter()
                    ->values()
                    ->toArray();
            } else {
                $availableYears = Transaction::selectRaw("DISTINCT EXTRACT(YEAR FROM transaction_date) as yr")
                    ->whereNotNull('transaction_date')
                    ->orderByDesc('yr')
                    ->pluck('yr')
                    ->filter()
                    ->values()
                    ->toArray();
            }
        } catch (\Throwable $e) {
            $availableYears = [2026, 2025, 2024, 2023];
        }
        if (empty($availableYears)) {
            $availableYears = [2026, 2025, 2024, 2023];
        }

        // فانکشنی هاوبەش بۆ سەپاندنی هەموو مەرجەکانی فلتەر
        $applyCommonFilters = function($q) use ($request, $dateFilter, $today) {
            $isNumberFilter = in_array($dateFilter, ['number_exact', 'number_between'])
                              || $request->filled('number_exact_val')
                              || ($request->filled('number_from') && $request->filled('number_to'));

            // ئەگەر گەڕان بەپێی ژمارە نەبوو، مەرجەکانی بەروار و ساڵ دەسەپێندرێن
            if (!$isNumberFilter) {
                // ١. مەرجی بەروار
                if ($dateFilter === 'today') {
                    $q->where(function($sq) use ($today) {
                        $sq->whereDate('transaction_date', $today)
                           ->orWhereDate('created_at', $today);
                    });
                } elseif ($dateFilter === 'yesterday') {
                    $yesterday = now()->subDay()->toDateString();
                    $q->where(function($sq) use ($yesterday) {
                        $sq->whereDate('transaction_date', $yesterday)
                           ->orWhereDate('created_at', $yesterday);
                    });
                } elseif ($dateFilter === 'this_month') {
                    $startOfMonth = now()->startOfMonth()->toDateString();
                    $endOfMonth = now()->endOfMonth()->toDateString();
                    $q->whereBetween('transaction_date', [$startOfMonth, $endOfMonth]);
                } elseif ($dateFilter === 'last_month') {
                    $startOfLastMonth = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
                    $endOfLastMonth = now()->subMonthNoOverflow()->endOfMonth()->toDateString();
                    $q->whereBetween('transaction_date', [$startOfLastMonth, $endOfLastMonth]);
                } elseif ($dateFilter === 'this_year') {
                    $q->whereYear('transaction_date', now()->year);
                } elseif ($dateFilter === 'last_year') {
                    $q->whereYear('transaction_date', now()->subYear()->year);
                } elseif ($dateFilter === 'exact' && $request->filled('exact_date')) {
                    $q->whereDate('transaction_date', $request->exact_date);
                } elseif ($dateFilter === 'custom') {
                    if ($request->filled('from_date') && $request->filled('to_date')) {
                        $q->whereBetween('transaction_date', [$request->from_date, $request->to_date]);
                    } elseif ($request->filled('from_date')) {
                        $q->whereDate('transaction_date', '>=', $request->from_date);
                    } elseif ($request->filled('to_date')) {
                        $q->whereDate('transaction_date', '<=', $request->to_date);
                    }
                }

                // ٢. فلتەری ساڵ
                if ($request->filled('year') && $request->year !== 'all') {
                    $q->whereYear('transaction_date', $request->year);
                }
            } else {
                // ٣. مەرجی ژمارەیی تایبەت (بێ هیچ سنوردارکردنێکی بەروار و ساڵ)
                $digitMap = ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9'];
                $numberField = $request->get('number_field', 'all');

                if ($dateFilter === 'number_between' || ($request->filled('number_from') && $request->filled('number_to'))) {
                    $fromRaw = strtr(trim($request->number_from), $digitMap);
                    $toRaw = strtr(trim($request->number_to), $digitMap);
                    $from = (int)preg_replace('/[^0-9]/', '', $fromRaw);
                    $to = (int)preg_replace('/[^0-9]/', '', $toRaw);

                    if ($from > $to) {
                        $tmp = $from;
                        $from = $to;
                        $to = $tmp;
                    }

                    $q->where(function($sq) use ($numberField, $from, $to) {
                        $isSqlite = config('database.default') === 'sqlite';
                        $bookletCast = $isSqlite ? "CAST(booklet_number AS INTEGER)" : "CAST(NULLIF(regexp_replace(booklet_number, '[^0-9]', '', 'g'), '') AS BIGINT)";
                        $receiptCast = $isSqlite ? "CAST(receipt_37a_number AS INTEGER)" : "CAST(NULLIF(regexp_replace(receipt_37a_number, '[^0-9]', '', 'g'), '') AS BIGINT)";

                        if ($numberField === 'booklet_number') {
                            $sq->whereRaw("{$bookletCast} >= ? AND {$bookletCast} <= ?", [$from, $to]);
                        } elseif ($numberField === 'receipt_37a_number') {
                            $sq->whereRaw("{$receiptCast} >= ? AND {$receiptCast} <= ?", [$from, $to]);
                        } elseif ($numberField === 'id') {
                            $sq->whereBetween('id', [$from, $to]);
                        } else {
                            // all: لە نێوان دەفتەر یان وەسڵ یان زنجیرە
                            $sq->whereRaw("({$bookletCast} >= ? AND {$bookletCast} <= ?)", [$from, $to])
                               ->orWhereRaw("({$receiptCast} >= ? AND {$receiptCast} <= ?)", [$from, $to])
                               ->orWhereBetween('id', [$from, $to]);
                        }
                    });
                } elseif ($dateFilter === 'number_exact' || $request->filled('number_exact_val')) {
                    $valRaw = strtr(trim($request->number_exact_val), $digitMap);
                    $cleanVal = preg_replace('/[\s\-\_\/]+/', '', $valRaw);
                    $cleanValUpper = strtoupper($cleanVal);

                    $q->where(function($sq) use ($numberField, $cleanVal, $cleanValUpper, $valRaw) {
                        if ($numberField === 'booklet_number') {
                            $sq->where('booklet_number', $cleanVal)
                               ->orWhere('booklet_number', $valRaw);
                        } elseif ($numberField === 'receipt_37a_number') {
                            $sq->where('receipt_37a_number', $cleanVal)
                               ->orWhere('receipt_37a_number', $valRaw);
                        } elseif ($numberField === 'plate_number') {
                            $sq->where('plate_number', $cleanValUpper)
                               ->orWhere('plate_number', $valRaw);
                        } elseif ($numberField === 'id') {
                            $sq->where('id', (int)$cleanVal);
                        } elseif ($numberField === 'barcode') {
                            $sq->where('barcode', $cleanValUpper)
                               ->orWhere('barcode', $valRaw);
                        } else {
                            // all
                            $sq->where('booklet_number', $cleanVal)
                               ->orWhere('receipt_37a_number', $cleanVal)
                               ->orWhere('plate_number', $cleanValUpper)
                               ->orWhere('barcode', $cleanValUpper)
                               ->orWhere('id', (int)$cleanVal);
                        }
                    });
                }
            }

            // ٣. فلتەری جۆری مامەڵە
            if ($request->filled('transaction_type_id') && $request->transaction_type_id !== 'all') {
                $q->where('transaction_type_id', $request->transaction_type_id);
            }

            // ٤. گەڕانی دەقی خێرا (بە هەردوو شێوازی ئاسایی و بێ سپەیس/پێشگر)
            if ($request->filled('search')) {
                $search = trim($request->search);
                $digitMap = ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9'];
                $cleanedSearch = strtr(preg_replace('/[\s\-\_\/]+/', '', $search), $digitMap);
                $cleanSearchUpper = strtoupper($cleanedSearch);

                $q->where(function($sq) use ($search, $cleanedSearch, $cleanSearchUpper) {
                    $sq->where('barcode', 'like', "%{$search}%")
                      ->orWhere('visitor_name', 'like', "%{$search}%")
                      ->orWhere('visitor_name_eng', 'like', "%{$search}%")
                      ->orWhere('second_driver_name', 'like', "%{$search}%")
                      ->orWhere('second_driver_name_eng', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('plate_number', 'like', "%{$search}%")
                      ->orWhere('chassis_number', 'like', "%{$search}%")
                      ->orWhere('receipt_37a_number', 'like', "%{$search}%")
                      ->orWhere('booklet_number', 'like', "%{$search}%")
                      ->orWhere('salana_number', 'like', "%{$search}%")
                      ->orWhere('no_nusraw_mrur', 'like', "%{$search}%")
                      ->orWhere('no_nusraw_gumrg', 'like', "%{$search}%");

                    if (!empty($cleanSearchUpper) && $cleanSearchUpper !== $search) {
                        $sq->orWhere('plate_number', 'like', "%{$cleanSearchUpper}%")
                          ->orWhere('chassis_number', 'like', "%{$cleanSearchUpper}%")
                          ->orWhere('booklet_number', 'like', "%{$cleanSearchUpper}%")
                          ->orWhere('phone_number', 'like', "%{$cleanedSearch}%");
                    }
                });
            }
        };

        $query = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake', 'carColor', 'plateType']);
        $applyCommonFilters($query);

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

        // هەژمارکردنی ژمارەی مامەڵەکانی هەر قۆناغێک بەپێی فلتەرە چالاکەکان
        $baseCountQuery = Transaction::query();
        $applyCommonFilters($baseCountQuery);

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
            'today',
            'availableYears'
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
                  ->orWhere('booklet_number', 'like', "%{$search}%")
                  ->orWhere('salana_number', 'like', "%{$search}%")
                  ->orWhere('no_nusraw_mrur', 'like', "%{$search}%")
                  ->orWhere('no_nusraw_gumrg', 'like', "%{$search}%");
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
        $plateTypes = \Illuminate\Support\Facades\Cache::remember('all_plate_types', 3600, fn() => PlateType::all()->unique('clean_kurdish_name')->values());
        $carMakes = \Illuminate\Support\Facades\Cache::remember('all_car_makes', 3600, fn() => CarMake::all());
        $carColors = \Illuminate\Support\Facades\Cache::remember('all_car_colors', 3600, fn() => CarColor::orderBy('id', 'asc')->get());
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
        $plate = trim($request->get('plate_number', ''));
        $booklet = trim($request->get('booklet_number', ''));
        $phone = trim($request->get('phone_number', ''));
        $query = trim($request->get('query', ''));

        $record = null;
        $searchField = null;

        // ١. گەڕان بەپێی ژمارەی شاسی (Chassis VIN)
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
            if ($record) $searchField = 'chassis';
        }

        // ٢. گەڕان بەپێی ژمارەی تابلۆی ئۆتۆمبێل (Plate Number)
        if (!$record && !empty($plate) && strlen($plate) >= 3) {
            $cleanPlate = strtoupper(preg_replace('/[\s\-\_\/]+/', '', $plate));
            $cleanPlate = strtr($cleanPlate, [
                '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4',
                '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9',
                '۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4',
                '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9',
            ]);
            $record = Transaction::with(['transactionType', 'plateType', 'carMake', 'carColor', 'trafficDirectorate'])
                ->where(function($q) use ($plate, $cleanPlate) {
                    $q->whereRaw("UPPER(REPLACE(plate_number, ' ', '')) = ?", [$cleanPlate])
                      ->orWhere('plate_number', $plate)
                      ->orWhere('plate_number', 'like', "%{$cleanPlate}%");
                })
                ->latest('id')
                ->first();
            if ($record) $searchField = 'plate';
        }

        // ٣. گەڕان بەپێی ژمارەی دەفتەر (Booklet Number)
        if (!$record && !empty($booklet) && strlen($booklet) >= 3) {
            $cleanBooklet = preg_replace('/[\s\-]+/', '', $booklet);
            $cleanBooklet = strtr($cleanBooklet, [
                '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4',
                '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9',
                '۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4',
                '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9',
            ]);
            $record = Transaction::with(['transactionType', 'plateType', 'carMake', 'carColor', 'trafficDirectorate'])
                ->where('booklet_number', $cleanBooklet)
                ->orWhere('booklet_number', $booklet)
                ->latest('id')
                ->first();
            if ($record) $searchField = 'booklet';
        }

        // ٤. گەڕان بەپێی ژمارەی مۆبایل (Phone Number)
        if (!$record && !empty($phone) && strlen($phone) >= 6) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $cleanPhone = strtr($cleanPhone, [
                '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4',
                '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9',
                '۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4',
                '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9',
            ]);
            $record = Transaction::with(['transactionType', 'plateType', 'carMake', 'carColor', 'trafficDirectorate'])
                ->where(function($q) use ($phone, $cleanPhone) {
                    $q->whereRaw("REPLACE(REPLACE(phone_number, ' ', ''), '-', '') like ?", ["%{$cleanPhone}%"])
                      ->orWhere('phone_number', 'like', "%{$phone}%");
                })
                ->latest('id')
                ->first();
            if ($record) $searchField = 'phone';
        }

        // ٥. گەڕانی گشتی فرە-فێڵد (لە سەرجەم ٤ فێڵدەکەدا بە جارێک)
        if (!$record && !empty($query) && strlen($query) >= 2) {
            $cleanQuery = strtoupper(preg_replace('/[\s\-\_\/]+/', '', $query));
            $cleanQuery = strtr($cleanQuery, [
                '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4',
                '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9',
                '۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4',
                '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9',
            ]);

            $record = Transaction::with(['transactionType', 'plateType', 'carMake', 'carColor', 'trafficDirectorate'])
                ->where(function($q) use ($query, $cleanQuery) {
                    $q->where('chassis_number', 'like', "%{$cleanQuery}%")
                      ->orWhere('plate_number', 'like', "%{$cleanQuery}%")
                      ->orWhere('booklet_number', 'like', "%{$cleanQuery}%")
                      ->orWhere('phone_number', 'like', "%{$cleanQuery}%")
                      ->orWhere('barcode', $query)
                      ->orWhere('visitor_name', 'like', "%{$query}%");
                })
                ->latest('id')
                ->first();
            if ($record) $searchField = 'query';
        }

        if ($record) {
            // پشکنینی دەفتەری پووچەڵنەکراوەی پێشوو (Active Uncancelled Booklet)
            $activeBooklet = null;
            if (!empty($record->chassis_number)) {
                $chassisClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $record->chassis_number));
                $activeBooklet = Transaction::where(function($q) use ($record, $chassisClean) {
                    $q->whereRaw("UPPER(REPLACE(REPLACE(chassis_number, ' ', ''), '-', '')) = ?", [$chassisClean])
                      ->orWhere('chassis_number', $record->chassis_number);
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
            }

            $hasActiveBooklet = false;
            $activeBookletData = null;

            if ($activeBooklet) {
                $chassisClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $record->chassis_number));
                $isCancelledByLaterTx = Transaction::where(function($q) use ($record, $chassisClean) {
                    $q->whereRaw("UPPER(REPLACE(REPLACE(chassis_number, ' ', ''), '-', '')) = ?", [$chassisClean])
                      ->orWhere('chassis_number', $record->chassis_number);
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

            $formatted = $record->toArray();
            $formatted['car_make_kurdish'] = $record->carMake?->name_kurdish ?? '';
            $formatted['car_make_english'] = $record->carMake?->name_english ?? '';
            $formatted['car_color_kurdish'] = $record->carColor?->name_kurdish ?? '';
            $formatted['car_color_english'] = $record->carColor?->name_english ?? '';
            $formatted['clean_plate_type'] = $record->plateType?->clean_kurdish_name ?? '';

            return response()->json([
                'found' => true,
                'search_field' => $searchField,
                'record' => $formatted,
                'has_active_booklet' => $hasActiveBooklet,
                'active_booklet' => $activeBookletData,
                'active_booklet_warning' => $hasActiveBooklet ? 'تۆ دەفتەری پێشووت پووچەڵ نەکردووەتەوە' : null,
            ]);
        }

        return response()->json([
            'found' => false,
            'search_field' => null,
            'record' => null,
            'has_active_booklet' => false,
            'active_booklet' => null,
            'active_booklet_warning' => null,
        ]);
    }

    public function expiredBookletsReport(Request $request)
    {
        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'archiver') {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بینینی دەفتەرە بەسەرچووەکان تەنها بۆ ئەدمین و ئەرشیفە!');
        }

        $status = $request->get('status', 'expired'); // 'expired', 'expiring_soon', 'all'
        $noticeStatus = $request->get('notice_status', 'all'); // 'all', 'unnotified', 'notified', 'attended', 'unattended'
        $search = $request->get('search');
        $year = $request->get('year');
        $yearType = $request->get('year_type', 'end_date');

        // Booklet types: 1: New Issue, 2: Renewal, 3: Name Change, 4: Booklet Replacement, 7: Both
        $bookletTypeIds = [1, 2, 3, 4, 7];

        $baseQuery = Transaction::with(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'latestBookletNotice'])
            ->whereIn('transaction_type_id', $bookletTypeIds)
            ->whereNotNull('end_date')
            ->whereNull('cancelled_at')
            ->where(function($q) {
                $q->whereNull('is_cancelled')->orWhere('is_cancelled', false);
            })
            ->whereNotExists(function($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('transactions as t_newer')
                  ->whereColumn('t_newer.plate_number', 'transactions.plate_number')
                  ->where('t_newer.id', '>', \Illuminate\Support\Facades\DB::raw('transactions.id'))
                  ->whereNull('t_newer.deleted_at');
            });

        if ($year) {
            if ($yearType === 'transaction_date') {
                $baseQuery->whereYear('transaction_date', $year);
            } else {
                $baseQuery->whereYear('end_date', $year);
            }
        }

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

        // Notice counts across the current set
        $unnotifiedCount = (clone $baseQuery)->whereDoesntHave('bookletNotices')->count();
        $notifiedCount = (clone $baseQuery)->whereHas('bookletNotices')->count();
        $attendedNoticesCount = (clone $baseQuery)->whereHas('bookletNotices', fn($q) => $q->where('status', 'attended')->orWhereNotNull('attended_at'))->count();
        $unattendedNoticesCount = (clone $baseQuery)->whereHas('bookletNotices', fn($q) => $q->where('status', 'sent')->whereNull('attended_at'))->count();

        // Apply Notice Filter if requested
        if ($noticeStatus === 'unnotified') {
            $baseQuery->whereDoesntHave('bookletNotices');
        } elseif ($noticeStatus === 'notified') {
            $baseQuery->whereHas('bookletNotices');
        } elseif ($noticeStatus === 'attended') {
            $baseQuery->whereHas('bookletNotices', fn($q) => $q->where('status', 'attended')->orWhereNotNull('attended_at'));
        } elseif ($noticeStatus === 'unattended') {
            $baseQuery->whereHas('bookletNotices', fn($q) => $q->where('status', 'sent')->whereNull('attended_at'));
        }

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
            'noticeStatus',
            'expiredCount',
            'expiringSoonCount',
            'totalCount',
            'unnotifiedCount',
            'notifiedCount',
            'attendedNoticesCount',
            'unattendedNoticesCount',
            'search',
            'year',
            'yearType'
        ));
    }

    public function unreturnedTrafficLettersReport(Request $request)
    {
        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'archiver') {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بینینی نووسراوە بێ مرورەکان تەنها بۆ ئەدمین و ئەرشیفە!');
        }

        $search = $request->get('search');
        $directorateId = $request->get('directorate_id');
        $year = $request->get('year');
        $status = $request->get('status', 'unreturned'); // 'unreturned', 'returned', 'all'
        $boxNumber = $request->get('box_number');

        $query = Transaction::with(['trafficDirectorate', 'transactionType', 'carMake', 'carColor', 'plateType']);

        if ($status === 'returned') {
            $query->whereNotNull('no_nusraw_mrur')
                  ->where('no_nusraw_mrur', '!=', '0')
                  ->where('no_nusraw_mrur', '!=', '');
        } elseif ($status === 'all') {
            // No restriction on traffic letter
        } else {
            // Default: unreturned (0, null, or empty)
            $query->where(function($q) {
                $q->whereNull('no_nusraw_mrur')
                  ->orWhere('no_nusraw_mrur', '0')
                  ->orWhere('no_nusraw_mrur', '');
            });
        }

        if ($directorateId) {
            $query->where('traffic_directorate_id', $directorateId);
        }

        if ($year) {
            $query->whereYear('transaction_date', $year);
        }

        if ($boxNumber !== null && $boxNumber !== '') {
            $query->where(function($q) use ($boxNumber) {
                $q->where('NO_boxfile', (int)$boxNumber)
                  ->orWhere('NO_boxfile_puchal', (int)$boxNumber);
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('chassis_number', 'like', "%{$search}%")
                  ->orWhere('no_nusraw_gumrg', 'like', "%{$search}%")
                  ->orWhere('booklet_number', 'like', "%{$search}%")
                  ->orWhere('no_nusraw_mrur', 'like', "%{$search}%")
                  ->orWhere('NO_boxfile', 'like', "%{$search}%")
                  ->orWhere('NO_boxfile_puchal', 'like', "%{$search}%");
            });
        }

        $totalFiltered = (clone $query)->count();
        $transactions = $query->latest('id')->paginate(30)->withQueryString();
        $directorates = TrafficDirectorate::all();

        $unreturnedCount = Transaction::where(function($q) {
            $q->whereNull('no_nusraw_mrur')
              ->orWhere('no_nusraw_mrur', '0')
              ->orWhere('no_nusraw_mrur', '');
        })->count();

        $returnedCount = Transaction::whereNotNull('no_nusraw_mrur')
            ->where('no_nusraw_mrur', '!=', '0')
            ->where('no_nusraw_mrur', '!=', '')
            ->count();

        return view('transactions.reports.unreturned_mrur', compact(
            'transactions',
            'totalFiltered',
            'unreturnedCount',
            'returnedCount',
            'directorates',
            'search',
            'directorateId',
            'year',
            'status',
            'boxNumber'
        ));
    }

    public function updateTrafficLetter(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'no_nusraw_mrur' => 'required|string|max:100',
            'date_nusraw_mrur' => 'nullable|date',
            'NO_boxfile' => 'nullable|integer|min:0',
        ]);

        $updateData = [
            'no_nusraw_mrur' => trim($validated['no_nusraw_mrur']),
            'date_nusraw_mrur' => $validated['date_nusraw_mrur'] ?? now()->toDateString(),
        ];

        if ($request->has('NO_boxfile') && $request->NO_boxfile !== null && $request->NO_boxfile !== '') {
            $updateData['NO_boxfile'] = (int)$validated['NO_boxfile'];
        }

        $transaction->update($updateData);

        // Clear dashboard cache
        \Illuminate\Support\Facades\Cache::forget('dashboard_pipeline_metrics_' . today()->toDateString());

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_traffic_letter',
            'description' => "ژمارەی نووسراوی هاتوچۆ بۆ مامەڵەی [{$transaction->barcode}] تۆمارکرا بۆ [{$transaction->no_nusraw_mrur}]" . ($transaction->NO_boxfile ? " (بۆکس: {$transaction->NO_boxfile})" : ""),
            'ip_address' => $request->ip(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'ژمارەی نووسراوی هاتوچۆ بە سەرکەوتوویی تۆمارکرا.',
                'no_nusraw_mrur' => $transaction->no_nusraw_mrur,
                'NO_boxfile' => $transaction->NO_boxfile,
            ]);
        }

        return redirect()->back()->with('success', "ژمارەی نووسراوی هاتوچۆ [{$transaction->no_nusraw_mrur}] بۆ مامەڵەی [{$transaction->barcode}] بە سەرکەوتوویی تۆمارکرا.");
    }

    public function updateCustomsLetter(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'no_nusraw_puchal' => 'required|string|max:100',
            'date_nusraw_puchal' => 'required|date',
        ], [
            'no_nusraw_puchal.required' => 'تکایە ژمارەی نووسراوی گومرگ بنووسە.',
            'date_nusraw_puchal.required' => 'تکایە بەرواری نووسراوی گومرگ دیاری بکە.',
        ]);
        $transaction->update([
            'no_nusraw_puchal' => trim($validated['no_nusraw_puchal']),
            'date_nusraw_puchal' => $validated['date_nusraw_puchal'],
        ]);

        AuditLogger::log('نوێکردنەوەی نووسراوی گومرگ', Transaction::class, $transaction->id, "ژمارەی نووسراو: {$transaction->no_nusraw_puchal}");

        return redirect()->back()->with('success', 'زانیاری نووسراوی گومرگ بە سەرکەوتوویی تۆمارکرا / نوێکرایەوە.');
    }

    /**
     * Report of citizens who own multiple vehicles with registered booklets (Excluding Cancelled).
     */
    public function citizensMultipleCarsReport(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role !== 'archiver') {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بینینی ئەم ڕاپۆرتە تەنها بۆ ئەدمین و ئەرشیفە!');
        }

        $search = trim($request->get('search', ''));
        $minCars = max(2, (int)$request->get('min_cars', 2));
        $groupBy = in_array($request->get('group_by'), ['both', 'name', 'phone']) ? $request->get('group_by') : 'both';
        $status = $request->get('status', 'all'); // 'all', 'active', 'expired', 'only_active', 'only_expired'
        $sortBy = in_array($request->get('sort'), ['cars_desc', 'active_desc', 'expired_desc', 'booklets_desc', 'name_asc']) ? $request->get('sort') : 'cars_desc';
        $perPage = max(10, min(100, (int)$request->get('per_page', 25)));
        $today = now()->toDateString();

        // 1. First subquery: aggregate by plate_number to get latest status per car (strictly excluding cancellations)
        if ($groupBy === 'name') {
            $plateSub = DB::table('transactions')
                ->select(
                    'visitor_name',
                    'plate_number',
                    DB::raw("MIN(phone_number) as phone_number"),
                    DB::raw("GROUP_CONCAT(DISTINCT phone_number) as all_phones"),
                    DB::raw("MAX(end_date) as max_end_date"),
                    DB::raw("COUNT(id) as plate_booklets")
                )
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('is_cancelled')->orWhere('is_cancelled', false);
                })
                ->whereNull('cancelled_at')
                ->where('transaction_type_id', '!=', 5)
                ->whereNotNull('visitor_name')
                ->where('visitor_name', '!=', '')
                ->groupBy('visitor_name', 'plate_number');

            $query = DB::table(DB::raw("({$plateSub->toSql()}) as plates"))
                ->mergeBindings($plateSub)
                ->select(
                    'visitor_name',
                    DB::raw("MIN(phone_number) as phone_number"),
                    DB::raw("GROUP_CONCAT(DISTINCT all_phones) as all_phones"),
                    DB::raw('COUNT(plate_number) as car_count'),
                    DB::raw('SUM(plate_booklets) as total_booklets'),
                    DB::raw("SUM(CASE WHEN max_end_date >= '{$today}' THEN 1 ELSE 0 END) as active_cars"),
                    DB::raw("SUM(CASE WHEN max_end_date < '{$today}' OR max_end_date IS NULL THEN 1 ELSE 0 END) as expired_cars"),
                    DB::raw("GROUP_CONCAT(plate_number) as plates_list")
                )
                ->groupBy('visitor_name');
        } elseif ($groupBy === 'phone') {
            $plateSub = DB::table('transactions')
                ->select(
                    'phone_number',
                    'plate_number',
                    DB::raw("MIN(visitor_name) as visitor_name"),
                    DB::raw("GROUP_CONCAT(DISTINCT visitor_name) as all_names"),
                    DB::raw("MAX(end_date) as max_end_date"),
                    DB::raw("COUNT(id) as plate_booklets")
                )
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('is_cancelled')->orWhere('is_cancelled', false);
                })
                ->whereNull('cancelled_at')
                ->where('transaction_type_id', '!=', 5)
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '')
                ->where('phone_number', '!=', '0')
                ->groupBy('phone_number', 'plate_number');

            $query = DB::table(DB::raw("({$plateSub->toSql()}) as plates"))
                ->mergeBindings($plateSub)
                ->select(
                    'phone_number',
                    DB::raw("MIN(visitor_name) as visitor_name"),
                    DB::raw("GROUP_CONCAT(DISTINCT all_names) as all_names"),
                    DB::raw('COUNT(plate_number) as car_count'),
                    DB::raw('SUM(plate_booklets) as total_booklets'),
                    DB::raw("SUM(CASE WHEN max_end_date >= '{$today}' THEN 1 ELSE 0 END) as active_cars"),
                    DB::raw("SUM(CASE WHEN max_end_date < '{$today}' OR max_end_date IS NULL THEN 1 ELSE 0 END) as expired_cars"),
                    DB::raw("GROUP_CONCAT(plate_number) as plates_list")
                )
                ->groupBy('phone_number');
        } else {
            // Default: both (visitor_name & phone_number)
            $plateSub = DB::table('transactions')
                ->select(
                    'visitor_name',
                    'phone_number',
                    'plate_number',
                    DB::raw("MAX(end_date) as max_end_date"),
                    DB::raw("COUNT(id) as plate_booklets")
                )
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('is_cancelled')->orWhere('is_cancelled', false);
                })
                ->whereNull('cancelled_at')
                ->where('transaction_type_id', '!=', 5)
                ->whereNotNull('visitor_name')
                ->where('visitor_name', '!=', '')
                ->groupBy('visitor_name', 'phone_number', 'plate_number');

            $query = DB::table(DB::raw("({$plateSub->toSql()}) as plates"))
                ->mergeBindings($plateSub)
                ->select(
                    'visitor_name',
                    'phone_number',
                    DB::raw('COUNT(plate_number) as car_count'),
                    DB::raw('SUM(plate_booklets) as total_booklets'),
                    DB::raw("SUM(CASE WHEN max_end_date >= '{$today}' THEN 1 ELSE 0 END) as active_cars"),
                    DB::raw("SUM(CASE WHEN max_end_date < '{$today}' OR max_end_date IS NULL THEN 1 ELSE 0 END) as expired_cars"),
                    DB::raw("GROUP_CONCAT(plate_number) as plates_list")
                )
                ->groupBy('visitor_name', 'phone_number');
        }

        $query->havingRaw('COUNT(plate_number) >= ?', [$minCars]);

        // Status Filtering (active / expired)
        if ($status === 'active') {
            $query->havingRaw("SUM(CASE WHEN max_end_date >= '{$today}' THEN 1 ELSE 0 END) > 0");
        } elseif ($status === 'expired') {
            $query->havingRaw("SUM(CASE WHEN max_end_date < '{$today}' OR max_end_date IS NULL THEN 1 ELSE 0 END) > 0");
        } elseif ($status === 'only_active') {
            $query->havingRaw("SUM(CASE WHEN max_end_date < '{$today}' OR max_end_date IS NULL THEN 1 ELSE 0 END) = 0");
        } elseif ($status === 'only_expired') {
            $query->havingRaw("SUM(CASE WHEN max_end_date >= '{$today}' THEN 1 ELSE 0 END) = 0");
        }

        // Search filtering (searching in non-cancelled transactions)
        if ($search !== '') {
            $matchingCitizens = DB::table('transactions')
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('is_cancelled')->orWhere('is_cancelled', false);
                })
                ->whereNull('cancelled_at')
                ->where('transaction_type_id', '!=', 5)
                ->where(function ($q) use ($search) {
                    $q->where('visitor_name', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('plate_number', 'like', "%{$search}%")
                      ->orWhere('chassis_number', 'like', "%{$search}%")
                      ->orWhere('booklet_number', 'like', "%{$search}%");
                });

            if ($groupBy === 'phone') {
                $matchedPhones = $matchingCitizens->pluck('phone_number')->filter()->unique()->toArray();
                $query->whereIn('phone_number', $matchedPhones);
            } else {
                $matchedNames = $matchingCitizens->pluck('visitor_name')->filter()->unique()->toArray();
                $query->whereIn('visitor_name', $matchedNames);
            }
        }

        // Sorting
        if ($sortBy === 'active_desc') {
            $query->orderByDesc('active_cars')->orderByDesc('car_count');
        } elseif ($sortBy === 'expired_desc') {
            $query->orderByDesc('expired_cars')->orderByDesc('car_count');
        } elseif ($sortBy === 'booklets_desc') {
            $query->orderByDesc('total_booklets')->orderByDesc('car_count');
        } elseif ($sortBy === 'name_asc') {
            $query->orderBy('visitor_name', 'asc');
        } else {
            $query->orderByDesc('car_count')->orderByDesc('active_cars');
        }

        // KPI aggregates BEFORE pagination
        $subQuery = (clone $query);
        $kpis = DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->select(
                DB::raw('COUNT(*) as total_citizens'),
                DB::raw('COALESCE(SUM(car_count), 0) as total_cars'),
                DB::raw('COALESCE(SUM(active_cars), 0) as total_active_cars'),
                DB::raw('COALESCE(SUM(expired_cars), 0) as total_expired_cars'),
                DB::raw('COALESCE(SUM(total_booklets), 0) as total_booklets'),
                DB::raw('COALESCE(MAX(car_count), 0) as max_cars')
            )
            ->first();

        $citizens = $query->paginate($perPage)->withQueryString();

        return view('transactions.reports.citizens_multiple_cars', compact(
            'citizens',
            'kpis',
            'search',
            'minCars',
            'groupBy',
            'status',
            'sortBy',
            'perPage'
        ));
    }

    /**
     * API for fetching vehicle and booklet details for a specific citizen (strictly excluding cancelled).
     */
    public function citizenCarsDetailsApi(Request $request)
    {
        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'archiver') {
            return response()->json(['success' => false, 'message' => 'دەسەڵاتی بینینی ئەم زانیارییە تەنها بۆ ئەدمین و ئەرشیفە!'], 403);
        }

        $name = trim($request->get('name', ''));
        $phone = trim($request->get('phone', ''));

        if (!$name && !$phone) {
            return response()->json(['success' => false, 'message' => 'ناوی هاووڵاتی یان ژمارەی مۆبایل پێویستە.'], 422);
        }

        $query = Transaction::with(['carMake', 'carColor', 'plateType', 'transactionType'])
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('is_cancelled')->orWhere('is_cancelled', false);
            })
            ->whereNull('cancelled_at')
            ->where('transaction_type_id', '!=', 5);

        if ($name && $phone) {
            $query->where('visitor_name', $name)->where('phone_number', $phone);
        } elseif ($name) {
            $query->where('visitor_name', $name);
        } else {
            $query->where('phone_number', $phone);
        }

        $transactions = $query->orderByDesc('end_date')->orderByDesc('id')->get();

        $today = now()->startOfDay();
        $distinctPlates = $transactions->pluck('plate_number')->filter()->unique()->values();

        // Calculate latest status per distinct plate
        $plateStatus = [];
        foreach ($transactions as $t) {
            if (!isset($plateStatus[$t->plate_number])) {
                $isActive = $t->end_date && $t->end_date->gte($today);
                $plateStatus[$t->plate_number] = $isActive ? 'active' : 'expired';
            }
        }

        $activeCarsCount = count(array_filter($plateStatus, fn($s) => $s === 'active'));
        $expiredCarsCount = count(array_filter($plateStatus, fn($s) => $s === 'expired'));

        $formatted = $transactions->map(function ($tx) use ($today) {
            $isExpired = true;
            if ($tx->end_date && $tx->end_date->gte($today)) {
                $isExpired = false;
            }
            return [
                'id' => $tx->id,
                'barcode' => $tx->barcode,
                'plate_number' => $tx->plate_number,
                'plate_type' => $tx->plateType->name_kurdish ?? 'تایبەت',
                'car_make' => $tx->carMake->name_kurdish ?? 'نەزانراو',
                'car_color' => $tx->carColor->name_kurdish ?? '-',
                'model_year' => $tx->model_year,
                'chassis_number' => $tx->chassis_number,
                'booklet_number' => $tx->booklet_number,
                'transaction_type' => $tx->transactionType->name_kurdish ?? 'دەرهێنانی دەفتەر',
                'transaction_date' => $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : null,
                'start_date' => $tx->start_date ? $tx->start_date->format('Y-m-d') : null,
                'end_date' => $tx->end_date ? $tx->end_date->format('Y-m-d') : null,
                'is_cancelled' => false,
                'is_expired' => $isExpired,
                'box_number' => $tx->NO_boxfile,
                'show_url' => route('transactions.show', $tx->id),
            ];
        });

        return response()->json([
            'success' => true,
            'visitor_name' => $name ?: ($transactions->first()->visitor_name ?? ''),
            'phone_number' => $phone ?: ($transactions->first()->phone_number ?? ''),
            'distinct_cars_count' => $distinctPlates->count(),
            'active_cars_count' => $activeCarsCount,
            'expired_cars_count' => $expiredCarsCount,
            'total_booklets_count' => $transactions->count(),
            'distinct_plates' => $distinctPlates,
            'transactions' => $formatted,
        ]);
    }

    /**
     * Export multiple-car citizens report to CSV with UTF-8 BOM (excluding cancelled).
     */
    public function exportCitizensMultipleCarsCsv(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role !== 'archiver') {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی داگرتنی ئەم ڕاپۆرتەت نییە!');
        }

        $search = trim($request->get('search', ''));
        $minCars = max(2, (int)$request->get('min_cars', 2));
        $groupBy = in_array($request->get('group_by'), ['both', 'name', 'phone']) ? $request->get('group_by') : 'both';
        $status = $request->get('status', 'all');
        $sortBy = in_array($request->get('sort'), ['cars_desc', 'active_desc', 'expired_desc', 'booklets_desc', 'name_asc']) ? $request->get('sort') : 'cars_desc';
        $today = now()->toDateString();

        if ($groupBy === 'name') {
            $plateSub = DB::table('transactions')
                ->select(
                    'visitor_name',
                    'plate_number',
                    DB::raw("MIN(phone_number) as phone_number"),
                    DB::raw("GROUP_CONCAT(DISTINCT phone_number) as all_phones"),
                    DB::raw("MAX(end_date) as max_end_date"),
                    DB::raw("COUNT(id) as plate_booklets")
                )
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('is_cancelled')->orWhere('is_cancelled', false);
                })
                ->whereNull('cancelled_at')
                ->where('transaction_type_id', '!=', 5)
                ->whereNotNull('visitor_name')
                ->where('visitor_name', '!=', '')
                ->groupBy('visitor_name', 'plate_number');

            $query = DB::table(DB::raw("({$plateSub->toSql()}) as plates"))
                ->mergeBindings($plateSub)
                ->select(
                    'visitor_name',
                    DB::raw("MIN(phone_number) as phone_number"),
                    DB::raw("GROUP_CONCAT(DISTINCT all_phones) as all_phones"),
                    DB::raw('COUNT(plate_number) as car_count'),
                    DB::raw('SUM(plate_booklets) as total_booklets'),
                    DB::raw("SUM(CASE WHEN max_end_date >= '{$today}' THEN 1 ELSE 0 END) as active_cars"),
                    DB::raw("SUM(CASE WHEN max_end_date < '{$today}' OR max_end_date IS NULL THEN 1 ELSE 0 END) as expired_cars"),
                    DB::raw("GROUP_CONCAT(plate_number) as plates_list")
                )
                ->groupBy('visitor_name');
        } elseif ($groupBy === 'phone') {
            $plateSub = DB::table('transactions')
                ->select(
                    'phone_number',
                    'plate_number',
                    DB::raw("MIN(visitor_name) as visitor_name"),
                    DB::raw("GROUP_CONCAT(DISTINCT visitor_name) as all_names"),
                    DB::raw("MAX(end_date) as max_end_date"),
                    DB::raw("COUNT(id) as plate_booklets")
                )
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('is_cancelled')->orWhere('is_cancelled', false);
                })
                ->whereNull('cancelled_at')
                ->where('transaction_type_id', '!=', 5)
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '')
                ->where('phone_number', '!=', '0')
                ->groupBy('phone_number', 'plate_number');

            $query = DB::table(DB::raw("({$plateSub->toSql()}) as plates"))
                ->mergeBindings($plateSub)
                ->select(
                    'phone_number',
                    DB::raw("MIN(visitor_name) as visitor_name"),
                    DB::raw("GROUP_CONCAT(DISTINCT all_names) as all_names"),
                    DB::raw('COUNT(plate_number) as car_count'),
                    DB::raw('SUM(plate_booklets) as total_booklets'),
                    DB::raw("SUM(CASE WHEN max_end_date >= '{$today}' THEN 1 ELSE 0 END) as active_cars"),
                    DB::raw("SUM(CASE WHEN max_end_date < '{$today}' OR max_end_date IS NULL THEN 1 ELSE 0 END) as expired_cars"),
                    DB::raw("GROUP_CONCAT(plate_number) as plates_list")
                )
                ->groupBy('phone_number');
        } else {
            $plateSub = DB::table('transactions')
                ->select(
                    'visitor_name',
                    'phone_number',
                    'plate_number',
                    DB::raw("MAX(end_date) as max_end_date"),
                    DB::raw("COUNT(id) as plate_booklets")
                )
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('is_cancelled')->orWhere('is_cancelled', false);
                })
                ->whereNull('cancelled_at')
                ->where('transaction_type_id', '!=', 5)
                ->whereNotNull('visitor_name')
                ->where('visitor_name', '!=', '')
                ->groupBy('visitor_name', 'phone_number', 'plate_number');

            $query = DB::table(DB::raw("({$plateSub->toSql()}) as plates"))
                ->mergeBindings($plateSub)
                ->select(
                    'visitor_name',
                    'phone_number',
                    DB::raw('COUNT(plate_number) as car_count'),
                    DB::raw('SUM(plate_booklets) as total_booklets'),
                    DB::raw("SUM(CASE WHEN max_end_date >= '{$today}' THEN 1 ELSE 0 END) as active_cars"),
                    DB::raw("SUM(CASE WHEN max_end_date < '{$today}' OR max_end_date IS NULL THEN 1 ELSE 0 END) as expired_cars"),
                    DB::raw("GROUP_CONCAT(plate_number) as plates_list")
                )
                ->groupBy('visitor_name', 'phone_number');
        }

        $query->havingRaw('COUNT(plate_number) >= ?', [$minCars]);

        if ($status === 'active') {
            $query->havingRaw("SUM(CASE WHEN max_end_date >= '{$today}' THEN 1 ELSE 0 END) > 0");
        } elseif ($status === 'expired') {
            $query->havingRaw("SUM(CASE WHEN max_end_date < '{$today}' OR max_end_date IS NULL THEN 1 ELSE 0 END) > 0");
        } elseif ($status === 'only_active') {
            $query->havingRaw("SUM(CASE WHEN max_end_date < '{$today}' OR max_end_date IS NULL THEN 1 ELSE 0 END) = 0");
        } elseif ($status === 'only_expired') {
            $query->havingRaw("SUM(CASE WHEN max_end_date >= '{$today}' THEN 1 ELSE 0 END) = 0");
        }

        if ($search !== '') {
            $matchingCitizens = DB::table('transactions')
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('is_cancelled')->orWhere('is_cancelled', false);
                })
                ->whereNull('cancelled_at')
                ->where('transaction_type_id', '!=', 5)
                ->where(function ($q) use ($search) {
                    $q->where('visitor_name', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('plate_number', 'like', "%{$search}%")
                      ->orWhere('chassis_number', 'like', "%{$search}%")
                      ->orWhere('booklet_number', 'like', "%{$search}%");
                });

            if ($groupBy === 'phone') {
                $matchedPhones = $matchingCitizens->pluck('phone_number')->filter()->unique()->toArray();
                $query->whereIn('phone_number', $matchedPhones);
            } else {
                $matchedNames = $matchingCitizens->pluck('visitor_name')->filter()->unique()->toArray();
                $query->whereIn('visitor_name', $matchedNames);
            }
        }

        if ($sortBy === 'active_desc') {
            $query->orderByDesc('active_cars')->orderByDesc('car_count');
        } elseif ($sortBy === 'expired_desc') {
            $query->orderByDesc('expired_cars')->orderByDesc('car_count');
        } elseif ($sortBy === 'booklets_desc') {
            $query->orderByDesc('total_booklets')->orderByDesc('car_count');
        } elseif ($sortBy === 'name_asc') {
            $query->orderBy('visitor_name', 'asc');
        } else {
            $query->orderByDesc('car_count')->orderByDesc('active_cars');
        }

        $rows = $query->get();

        $filename = 'citizens_multiple_cars_active_expired_' . date('Y_m_d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['ڕاپۆرتی ئەو هاوڵاتیانەی زیاتر لە یەک ئۆتۆمبێلیان هەیە (کارا و بەسەرچوو - بێ پووچەڵکراو)']);
            fputcsv($handle, ['بەرواری دەرهێنان: ' . now()->format('Y-m-d H:i')]);
            fputcsv($handle, ['کۆی هاووڵاتیان: ' . count($rows)]);
            fputcsv($handle, []);
            fputcsv($handle, [
                '#',
                'ناوی هاووڵاتی',
                'ژمارەی مۆبایل',
                'کۆی ئۆتۆمبێلەکان',
                'ئۆتۆمبێلی کارا',
                'ئۆتۆمبێلی بەسەرچوو',
                'کۆی دەفتەرەکان',
                'تابلۆی ئۆتۆمبێلەکان',
            ]);

            $index = 1;
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $index++,
                    $row->visitor_name ?? ($row->all_names ?? ''),
                    $row->phone_number ?? ($row->all_phones ?? ''),
                    $row->car_count,
                    $row->active_cars,
                    $row->expired_cars,
                    $row->total_booklets,
                    $row->plates_list,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('transactions.create')) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی تۆمارکردنی مامەڵەی نوێت نییە بەپێی پێرمشنەکان!');
        }

        // ڕێکخستنی زیرەکانەی ژمارەی تابلۆ: لابردنی هەموو سپەیس و دانانی پێشگری پارێزگاکان (21 بۆ سلێمانی، 22 بۆ هەولێر، 23 بۆ هەڵەبجە، 24 بۆ دهۆک)
        if ($request->filled('plate_number')) {
            $cleanPlate = preg_replace('/[\s\-\_\/]+/', '', trim($request->plate_number));
            $cleanPlate = strtr($cleanPlate, [
                '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4',
                '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9',
                '۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4',
                '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9',
            ]);
            $cleanPlate = strtoupper($cleanPlate);

            $prefixMap = [
                2 => '21', // Sulaimaniyah
                1 => '22', // Erbil
                4 => '23', // Halabja
                3 => '24', // Duhok
            ];
            $dirId = (int)$request->traffic_directorate_id;
            if (isset($prefixMap[$dirId])) {
                if (!preg_match('/^(21|22|23|24)/', $cleanPlate)) {
                    $cleanPlate = $prefixMap[$dirId] . $cleanPlate;
                }
            }
            $request->merge(['plate_number' => $cleanPlate]);
        }

        if ($request->filled('chassis_number')) {
            $cleanChassis = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$request->chassis_number));
            $request->merge(['chassis_number' => $cleanChassis]);
        }

        $validated = $request->validate([
            'parent_transaction_id' => 'nullable|exists:transactions,id',
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'traffic_directorate_id' => 'nullable|exists:traffic_directorates,id',
            'director_id' => 'nullable|exists:directors,id',
            'plate_type_id' => 'nullable|exists:plate_types,id',
            'is_bus' => 'nullable|boolean',
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
            'chassis_number' => 'required|string|max:17',
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
            'no_nusraw_gumrg' => 'nullable|string',
            'date_nusraw_gumrg' => 'nullable|date',
            'no_nusraw_puchal' => 'nullable|string',
            'date_nusraw_puchal' => 'nullable|date',
            'NO_boxfile' => 'nullable|integer|min:0',
            'NO_boxfile_puchal' => 'nullable|integer|min:0',
            'is_booklet_lost' => 'nullable|boolean',
            'lost_letter_number' => 'nullable|string|max:100',
            'lost_letter_date' => 'nullable|date',
            'lost_directorate_name' => 'nullable|string|max:255',
        ], [
            'chassis_number.max' => 'ژمارەی شاسی نابێت لە ١٧ پیت و ژمارە زیاتربێت!',
            'chassis_number.required' => 'تکایە ژمارەی شاسی بنووسە!',
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
            $isBus = $request->boolean('is_bus') || ($request->input('plate_type_id') == 4);
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
                $validated['num_years'] = 0;
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

            if ($typeId === 5) {
                $validated['passport_number'] = null;
                $validated['passport_issue_date'] = null;
                $validated['passport_issue_place'] = null;
                $validated['address'] = null;
            }

            $transactionData = array_merge($validated, [
                'barcode' => $barcode,
                'is_bus' => $isBus,
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

            // Enforce customs letter (no_nusraw_puchal) is ONLY saved for Cancellation (Type 5)
            if ((int)$typeId !== 5) {
                $transactionData['no_nusraw_puchal'] = null;
                $transactionData['date_nusraw_puchal'] = null;
            }

            $tx = Transaction::create($transactionData);

            // Auto-resolve customs letter from previous booklet for Cancellation if not explicitly provided
            if ($typeId === 5 && empty($tx->no_nusraw_puchal)) {
                $resolvedNo = $tx->getResolvedCustomsLetterNumber();
                $resolvedDate = $tx->getResolvedCustomsLetterDate();
                if ($resolvedNo) {
                    $tx->update([
                        'no_nusraw_puchal' => $resolvedNo,
                        'date_nusraw_puchal' => $resolvedDate ?? now()->toDateString(),
                    ]);
                }
            }

            AuditLogger::log('دروستکردنی مامەڵەی نوێ', Transaction::class, $tx->id, "بارکۆد: {$barcode} - ناوی هاووڵاتی: {$tx->visitor_name}", null, $transactionData);
            NotificationService::send(null, 'inspector', 'مامەڵەی نوێ دروستکرا', "مامەڵەیەکی نوێ بە بارکۆدی {$barcode} تومارکرا و ئامادەیە بۆ کەشف.", route('transactions.show', $tx->id));

            // Auto-detect attendance from Expired Booklet Notices:
            if ($tx->chassis_number || $tx->plate_number) {
                \App\Models\BookletNotice::where('status', 'sent')
                    ->whereNull('attended_at')
                    ->where(function($q) use ($tx) {
                        if ($tx->chassis_number) {
                            $q->where('chassis_number', $tx->chassis_number);
                        }
                        if ($tx->plate_number) {
                            $q->orWhere('plate_number', $tx->plate_number);
                        }
                    })
                    ->update([
                        'status' => 'attended',
                        'attended_at' => now(),
                        'attended_transaction_id' => $tx->id,
                        'attended_type' => $tx->transactionType?->name_kurdish ?? 'مامەڵەی نوێ',
                    ]);
            }

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

        $user = auth()->user();
        if (!$transaction->canBeEditedByUser($user)) {
            $stageName = 'دواتر';
            if ($transaction->is_submitted) $stageName = 'سەبمیت و ئەرشیف';
            elseif ($transaction->is_booklet_completed) $stageName = 'چاپکردنی دەفتەر';
            elseif ($transaction->is_paid) $stageName = 'وەسڵ و پارەدان';
            elseif ($transaction->is_audited) $stageName = 'وردبینی';
            elseif ($transaction->is_inspected) $stageName = 'کەشف و تەخمین';

            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', "دەستکاریکردنی ئەم مامەڵەیە لەم قۆناغە قوفڵ بووە چونکە قۆناغی '{$stageName}'ی بۆ ئەنجامدراوە! تەنها ئەدمین یان گەڕاندنەوەی مامەڵە دەتوانێت ڕێگە بە دەستکاری بدات.");
        }

        $directorates = \Illuminate\Support\Facades\Cache::remember('all_traffic_directorates', 3600, fn() => TrafficDirectorate::all());
        $directors = \Illuminate\Support\Facades\Cache::remember('all_directors', 3600, fn() => Director::all());
        $plateTypes = \Illuminate\Support\Facades\Cache::remember('all_plate_types', 3600, fn() => PlateType::all()->unique('clean_kurdish_name')->values());
        $carMakes = \Illuminate\Support\Facades\Cache::remember('all_car_makes', 3600, fn() => CarMake::all());
        $carColors = \Illuminate\Support\Facades\Cache::remember('all_car_colors', 3600, fn() => CarColor::orderBy('id', 'asc')->get());
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
        $user = auth()->user();
        if (!$transaction->canBeEditedByUser($user)) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'دەستکاریکردنی ئەم مامەڵەیە لەم قۆناغەدا قوفڵ کراوە چونکە قۆناغی دوای خۆی بۆ ئەنجامدراوە.');
        }

        // ڕێکخستنی زیرەکانەی ژمارەی تابلۆ: لابردنی هەموو سپەیس و دانانی پێشگری پارێزگاکان (21 بۆ سلێمانی، 22 بۆ هەولێر، 23 بۆ هەڵەبجە، 24 بۆ دهۆک)
        if ($request->filled('plate_number')) {
            $cleanPlate = preg_replace('/[\s\-\_\/]+/', '', trim($request->plate_number));
            $cleanPlate = strtr($cleanPlate, [
                '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4',
                '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9',
                '۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4',
                '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9',
            ]);
            $cleanPlate = strtoupper($cleanPlate);

            $prefixMap = [
                2 => '21', // Sulaimaniyah
                1 => '22', // Erbil
                4 => '23', // Halabja
                3 => '24', // Duhok
            ];
            $dirId = (int)$request->traffic_directorate_id;
            if (isset($prefixMap[$dirId])) {
                if (!preg_match('/^(21|22|23|24)/', $cleanPlate)) {
                    $cleanPlate = $prefixMap[$dirId] . $cleanPlate;
                }
            }
            $request->merge(['plate_number' => $cleanPlate]);
        }

        $validated = $request->validate([
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'traffic_directorate_id' => 'nullable|exists:traffic_directorates,id',
            'director_id' => 'nullable|exists:directors,id',
            'plate_type_id' => 'nullable|exists:plate_types,id',
            'is_bus' => 'nullable|boolean',
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
            'no_nusraw_gumrg' => 'nullable|string',
            'date_nusraw_gumrg' => 'nullable|date',
            'no_nusraw_puchal' => 'nullable|string',
            'date_nusraw_puchal' => 'nullable|date',
            'NO_boxfile' => 'nullable|integer|min:0',
            'NO_boxfile_puchal' => 'nullable|integer|min:0',
            'is_booklet_lost' => 'nullable|boolean',
            'lost_letter_number' => 'nullable|string|max:100',
            'lost_letter_date' => 'nullable|date',
            'lost_directorate_name' => 'nullable|string|max:255',
        ]);

        $numYears = (int)($request->input('num_years', 1));
        $isBus = $request->boolean('is_bus') || ($request->input('plate_type_id') == 4);
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
            $validated['num_years'] = 0;
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

        if ($typeId === 5) {
            $validated['passport_number'] = null;
            $validated['passport_issue_date'] = null;
            $validated['passport_issue_place'] = null;
            $validated['address'] = null;
        }

        // Preserve archive fields if omitted by data entry
        if (!$request->has('no_nusraw_puchal')) {
            unset($validated['no_nusraw_puchal']);
        }
        if (!$request->has('date_nusraw_puchal')) {
            unset($validated['date_nusraw_puchal']);
        }
        if (!$request->has('no_nusraw_gumrg')) {
            unset($validated['no_nusraw_gumrg']);
        }
        if (!$request->has('date_nusraw_gumrg')) {
            unset($validated['date_nusraw_gumrg']);
        }
        if (!$request->has('NO_boxfile')) {
            unset($validated['NO_boxfile']);
        }
        if (!$request->has('NO_boxfile_puchal')) {
            unset($validated['NO_boxfile_puchal']);
        }

        $keepInspection = ($transaction->returned_from_stage === 'audit' && $transaction->is_inspected);

        // Enforce customs letter (no_nusraw_puchal) is ONLY saved for Cancellation (Type 5)
        if ((int)$transaction->transaction_type_id !== 5) {
            $validated['no_nusraw_puchal'] = null;
            $validated['date_nusraw_puchal'] = null;
        }

        $transaction->update(array_merge($validated, [
            'is_bus' => $isBus,
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
            'returned_from_stage' => null,
            'is_inspected' => $keepInspection ? true : false,
            'is_audited' => false,
        ]));

        // Auto-sync shared citizen and vehicle details with companion pledge/booklet (خاڵی ۱)
        $linkedPledge = $transaction->getLinkedPledgeOrParent();
        if ($linkedPledge) {
            $sharedFields = [
                'traffic_directorate_id' => $transaction->traffic_directorate_id,
                'director_id'            => $transaction->director_id,
                'plate_type_id'          => $transaction->plate_type_id,
                'is_bus'                 => $transaction->is_bus,
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
                'returned_from_stage'    => null,
                'is_inspected'           => ($keepInspection && $linkedPledge->is_inspected) ? true : false,
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

    public function updateLostBooklet(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'is_booklet_lost' => 'required|boolean',
            'lost_letter_number' => 'nullable|string|max:100',
            'lost_letter_date' => 'nullable|date',
            'lost_directorate_name' => 'nullable|string|max:255',
        ]);

        if (empty($validated['lost_directorate_name'])) {
            $validated['lost_directorate_name'] = 'بەڕێوەبەرایەتی ونبووەکان';
        }

        $transaction->update($validated);
        AuditLogger::log('نوێکردنەوەی دۆخی ونبوونی دەفتەر', Transaction::class, $transaction->id, "ژمارەی نووسراو: {$transaction->lost_letter_number}");

        return redirect()->back()->with('success', 'زانیاری ونبوونی دەفتەر و نووسراوی ونبووەکان بە سەرکەوتوویی نوێکرایەوە.');
    }

    public function returnTransaction(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('transactions.return') && !auth()->user()->hasPermission('transactions.inspect') && !auth()->user()->hasPermission('transactions.audit')) {
            return redirect()->back()->with('error', 'دەسەڵاتی گەڕاندنەوەی مامەڵەت نییە بەپێی پێرمشنەکان!');
        }

        $validated = $request->validate([
            'return_reason' => 'required|string|max:1000',
            'return_stage' => 'nullable|string|in:inspection,audit',
        ], [
            'return_reason.required' => 'تکایە هۆکاری گەڕاندنەوەکە بە ڕوونی بنووسە.'
        ]);

        // پشکنین: ئایا گەڕانەوەکە لە ستێپی وردبینییە یان تەخمین؟
        $stage = $request->input('return_stage');
        if (empty($stage)) {
            $stage = $transaction->is_inspected ? 'audit' : 'inspection';
        }

        if ($stage === 'audit') {
            // ئەگەر گەڕانەوەی هەڵەکە لە ستێپی وردبینی بوو: با بەس قۆناغی وردبینی هەڵوەشێتەوە و تەخمین وەک خۆی پارێزراو بێت
            $updateData = [
                'is_returned' => true,
                'returned_from_stage' => 'audit',
                'return_reason' => $validated['return_reason'],
                'returned_by_user_id' => auth()->id(),
                'is_audited' => false,
                'audited_by' => null,
                'audited_at' => null,
                // is_inspected stays true
            ];
            $stageKurdish = 'وردبینی';
            $warningMsg = "مامەڵەکە بەهۆی هەڵەوە لە قۆناغی وردبینی گەڕێندرایەوە بۆ داتائەنتەری. تەنها قۆناغی وردبینی هەڵوەشێنرایەوە و تەخمین وەک خۆی بە پەسەندکراوی ماوەتەوە.";
        } else {
            // ئەگەر گەڕانەوەی هەڵەکە لە ستێپی تەخمین بوو: تەنها قۆناغی تەخمین هەڵدەوەشێتەوە
            $updateData = [
                'is_returned' => true,
                'returned_from_stage' => 'inspection',
                'return_reason' => $validated['return_reason'],
                'returned_by_user_id' => auth()->id(),
                'is_inspected' => false,
                'inspected_by' => null,
                'inspected_at' => null,
                'is_audited' => false,
                'audited_by' => null,
                'audited_at' => null,
            ];
            $stageKurdish = 'تەخمین';
            $warningMsg = "مامەڵەکە بەهۆی هەڵەوە لە قۆناغی تەخمین گەڕێندرایەوە بۆ داتائەنتەری. قۆناغی تەخمین هەڵوەشێنرایەوە و دوای چاککردنی هەڵەکە دووبارە دەبێت تەخمین بۆ بکرێتەوە.";
        }

        $transaction->update($updateData);

        // Auto-return companion pledge or booklet so both move together
        $linked = $transaction->getLinkedPledgeOrParent();
        if ($linked) {
            $linkedUpdateData = $updateData;
            if ($stage === 'audit') {
                unset($linkedUpdateData['is_inspected'], $linkedUpdateData['inspected_by'], $linkedUpdateData['inspected_at']);
            }
            $linked->update($linkedUpdateData);
            AuditLogger::log('گەڕاندنەوەی مامەڵەی بەستراو', Transaction::class, $linked->id, "گەڕێنرایەوە لە قۆناغی {$stageKurdish} بەهۆی: {$validated['return_reason']}");
        }

        AuditLogger::log('گەڕاندنەوەی مامەڵە بۆ چاککردنەوە', Transaction::class, $transaction->id, "گەڕێنرایەوە لە قۆناغی {$stageKurdish} بەهۆی: {$validated['return_reason']}");

        NotificationService::send(
            $transaction->created_by,
            'data_entry',
            'ئاگاداری: مامەڵە گەڕێنراوەتەوە',
            "مامەڵەی ({$transaction->visitor_name}) بە بارکۆدی {$transaction->barcode} لە قۆناغی {$stageKurdish} بەهۆی هەڵەوە گەڕێنراوەتەوە. هۆکار: {$validated['return_reason']}",
            route('transactions.show', $transaction->id)
        );

        return redirect()->route('transactions.show', $transaction->id)
            ->with('warning', $warningMsg);
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

        // Check Role-specific workflow guards (allow access if user has permission for this transaction)
        if (auth()->check()) {
            $user = auth()->user();

            if (!$user->isAdmin()) {
                $canAccessType = false;
                $typeId = (int)$transaction->transaction_type_id;

                if ($typeId === 5 && ($user->hasPermission('prints.cancellation') || $user->hasPermission('transactions.cancel'))) {
                    $canAccessType = true;
                } elseif ($typeId === 6 && $user->hasPermission('prints.pledge')) {
                    $canAccessType = true;
                } elseif ($typeId === 8 && ($user->hasPermission('prints.inspection_letter') || $user->hasPermission('transactions.inspect'))) {
                    $canAccessType = true;
                } elseif (in_array($typeId, [1, 2, 3, 4, 7]) && ($user->hasPermission('prints.booklet') || $user->hasPermission('transactions.complete_booklet'))) {
                    $canAccessType = true;
                }

                // Booklet Desk Guard: Block Pledge (6), Cancellation (5), and Inspection (8) ONLY IF user lacks permission
                if ($user->role === 'booklet' && in_array($typeId, [5, 6, 8]) && !$canAccessType) {
                    $typeName = $transaction->transactionType->name_kurdish ?? 'دیاری نەکراو';
                    return redirect()->route('dashboard')->with('error', "لێرە کاری دەفتەری نییە! ئەم مامەڵەیە جۆری [{$typeName}]ـە.");
                }

                // Legal Officer Guard: Only Booklet Issue (1) is allowed in legal pledge desk UNLESS user has other permissions
                if ($user->isLegal() && $typeId !== 1 && !$canAccessType) {
                    $typeName = $transaction->transactionType->name_kurdish ?? 'دیاری نەکراو';
                    return redirect()->route('dashboard')->with('error', "کاری تۆ لێرە ئەنجام نادرێت! بەشی یاسایی تەنها تایبەتە بە مامەڵەی دەرهێنانی دەفتەر (بەڵێننامەی یاسایی). جۆری ئەم مامەڵەیە: [{$typeName}]ـە.");
                }
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
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('transactions.complete_booklet')) {
            return redirect()->back()->with('error', 'دەسەڵاتی تۆمارکردنی ژمارەی دەفتەرت نییە بەپێی پێرمشنەکان!');
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
        if ($transaction->is_submitted && !auth()->user()->isAdmin() && auth()->user()->role !== 'archiver' && !auth()->user()->hasPermission('prints.receipt')) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دوای تەواوبوونی سەبمیت تەنها ئەدمین یان دەسەڵاتدار دەتوانێت پسوولەی ۳۷/أ چاپ بکات.');
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
        // 4. Submit restricted strictly to Archiver and Admin or users with transactions.submit permission
        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'archiver' && !auth()->user()->hasPermission('transactions.submit')) {
            return redirect()->back()->with('error', 'سەبمیتکردنی کۆتایی مامەڵە لە دەسەڵاتی تۆدا نییە بەپێی پێرمشنەکان!');
        }

        // 4. Transaction types allowed for archiving: Booklet issue, renewal, name change, booklet change, cancellation, name & booklet change, inspection
        $allowedSubmitTypes = [1, 2, 3, 4, 5, 7, 8];
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

        // 5. Box file number and Customs letter validation: REQUIRED for all 5 main archive types (1, 2, 3, 4, 7)
        if (in_array((int)$transaction->transaction_type_id, [1, 2, 3, 4, 7])) {
            $request->validate([
                'NO_boxfile' => 'required|numeric|min:1',
                'no_nusraw_gumrg' => 'required|string|max:100',
                'date_nusraw_gumrg' => 'required|date',
            ], [
                'NO_boxfile.required' => 'داخڵکردنی ژمارەی بۆکس فایلی ئەرشیف داواکراوە (ئیجبارییە)!',
                'NO_boxfile.numeric' => 'ژمارەی بۆکس دەبێت بە ژمارە بێت.',
                'no_nusraw_gumrg.required' => 'تکایە ژمارەی نووسراوی گومرگ بنووسە لە قۆناغی ئەرشیف.',
                'date_nusraw_gumrg.required' => 'تکایە بەرواری نووسراوی گومرگ دیاری بکە لە قۆناغی ئەرشیف.',
            ]);
            $updateData['NO_boxfile'] = (int)$request->NO_boxfile;
            $updateData['no_nusraw_gumrg'] = trim($request->no_nusraw_gumrg);
            $updateData['date_nusraw_gumrg'] = $request->date_nusraw_gumrg;
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
            if ($request->filled('no_nusraw_gumrg')) {
                $updateData['no_nusraw_gumrg'] = trim($request->no_nusraw_gumrg);
            }
            if ($request->filled('date_nusraw_gumrg')) {
                $updateData['date_nusraw_gumrg'] = $request->date_nusraw_gumrg;
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
                'no_nusraw_gumrg' => $updateData['no_nusraw_gumrg'] ?? null,
                'date_nusraw_gumrg' => $updateData['date_nusraw_gumrg'] ?? null,
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

        if ((int)$transaction->transaction_type_id === 8 || ($transaction->transactionType && str_contains($transaction->transactionType->name_kurdish, 'پشکنین'))) {
            return redirect()->back()
                ->with('success', 'مامەڵەی پشکنین بە سەرکەوتوویی سەبمیت و ئەرشیفکرا.')
                ->with('archive_inspection_prompt_letter', true);
        }

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
        if ($transaction->is_submitted && !auth()->user()->isAdmin() && auth()->user()->role !== 'archiver' && !auth()->user()->hasPermission('prints.inspection')) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دەسەڵاتی چاپی ئەم پسوولەیەت نییە.');
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
        if ($transaction->is_submitted && !auth()->user()->isAdmin() && auth()->user()->role !== 'archiver' && !auth()->user()->hasPermission('prints.data_entry')) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دەسەڵاتی چاپی ئەم فیشەیەت نییە.');
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
        if ($transaction->is_submitted && !auth()->user()->isAdmin() && auth()->user()->role !== 'archiver' && !auth()->user()->hasPermission('prints.booklet')) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دەسەڵاتی چاپی دەفتەرت نییە.');
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
        if ($transaction->is_submitted && !auth()->user()->isAdmin() && auth()->user()->role !== 'archiver' && !auth()->user()->hasPermission('prints.cancellation')) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دەسەڵاتی چاپی نووسراوی پووچەڵکردنەوەت نییە.');
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
        if ($transaction->is_submitted && !auth()->user()->isAdmin() && auth()->user()->role !== 'archiver' && !auth()->user()->hasPermission('prints.restriction')) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دەسەڵاتی چاپی نووسراوی ڕەفتارنەکردنت نییە.');
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
        // Inspection letter available in Cashier and Archiver stages or to users with prints.inspection_letter permission
        if ($transaction->is_submitted && !auth()->user()->isAdmin() && auth()->user()->role !== 'archiver' && !auth()->user()->hasPermission('prints.inspection_letter')) {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیفکراوە؛ دەسەڵاتی چاپی نووسراوی پشکنینت نییە.');
        }

        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('prints.inspection_letter') && auth()->user()->role !== 'archiver' && auth()->user()->role !== 'cashier') {
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

