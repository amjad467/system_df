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
        $query = Transaction::with(['transactionType', 'trafficDirectorate', 'carMake', 'carColor', 'plateType']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('barcode', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhere('chassis_number', 'like', "%{$search}%")
                  ->orWhere('receipt_37a_number', 'like', "%{$search}%")
                  ->orWhere('booklet_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('transaction_type_id')) {
            $query->where('transaction_type_id', $request->transaction_type_id);
        }

        if ($request->filled('stage')) {
            switch ($request->stage) {
                case 'inspection':
                    $query->where('is_inspected', false)->where('is_cancelled', false);
                    break;
                case 'audit':
                    $query->where('is_inspected', true)->where('is_audited', false)->where('is_cancelled', false);
                    break;
                case 'payment':
                    $query->where('is_audited', true)->where('is_paid', false)->where('is_cancelled', false);
                    break;
                case 'booklet':
                    $query->where('is_paid', true)->where('is_booklet_completed', false)->where('is_cancelled', false);
                    break;
                case 'submit':
                    $query->where('is_paid', true)->where('is_submitted', false)->where('is_cancelled', false);
                    break;
                case 'completed':
                    $query->where('is_submitted', true)->where('is_cancelled', false);
                    break;
                case 'cancelled':
                    $query->where('is_cancelled', true);
                    break;
            }
        }

        $transactions = $query->latest()->paginate(15);
        $transactionTypes = TransactionType::all();

        return view('transactions.index', compact('transactions', 'transactionTypes'));
    }

    public function officialPrintIndex(Request $request)
    {
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
              ->where('is_submitted', false)
              ->where('is_booklet_completed', false);
        })->count();

        $printedCountTotal = (clone $baseQuery)->where(function($q) {
            $q->where('is_printed', true)
              ->orWhere('is_submitted', true)
              ->orWhere('is_booklet_completed', true);
        })->count();

        $totalPaidToday = Transaction::whereNull('cancelled_at')
            ->where('is_paid', true)
            ->whereDate('paid_at', now()->format('Y-m-d'))
            ->count();

        $query = clone $baseQuery;

        if ($tab === 'printed') {
            $query->where(function($q) {
                $q->where('is_printed', true)
                  ->orWhere('is_submitted', true)
                  ->orWhere('is_booklet_completed', true);
            });
        } else {
            $query->where(function($q) {
                $q->where('is_printed', false)
                  ->where('is_submitted', false)
                  ->where('is_booklet_completed', false);
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

    public function create()
    {
        $directorates = TrafficDirectorate::all();
        $directors = Director::all();
        $plateTypes = PlateType::all();
        $carMakes = CarMake::all();
        $carColors = CarColor::all();
        $transactionTypes = TransactionType::all();

        $defaultDirectorateId = TrafficDirectorate::where('name_kurdish', 'like', '%سلێمانی%')->first()?->id ?? $directorates->first()?->id;
        $defaultDirectorId = Director::first()?->id ?? 1;

        return view('transactions.create', compact(
            'directorates',
            'directors',
            'plateTypes',
            'carMakes',
            'carColors',
            'transactionTypes',
            'defaultDirectorateId',
            'defaultDirectorId'
        ));
    }

    public function store(Request $request)
    {
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
        ]);

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

        // Non-admin can only edit if canBeEditedByDataEntry
        if (!auth()->user()->isAdmin() && !$transaction->canBeEditedByDataEntry()) {
            $stageName = 'کەشف و تەخمین';
            if ($transaction->is_submitted) $stageName = 'سەبمیت و ئەرشیف';
            elseif ($transaction->is_booklet_completed) $stageName = 'چاپکردنی دەفتەر';
            elseif ($transaction->is_paid) $stageName = 'وەسڵ و پارەدان';
            elseif ($transaction->is_audited) $stageName = 'وردبینی';

            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', "دەستکاریکردنی ئەم مامەڵەیە قوفڵ بووە چونکە قۆناغی '{$stageName}'ی بۆ ئەنجامدراوە! تەنها ئەدمین دەتوانێت قۆناغەکە ڕێکبخاتەوە (Reset) تا ڕێگە بە دەستکاری بدات.");
        }

        $directorates = TrafficDirectorate::all();
        $directors = Director::all();
        $plateTypes = PlateType::all();
        $carMakes = CarMake::all();
        $carColors = CarColor::all();
        $transactionTypes = TransactionType::all();

        return view('transactions.edit', compact(
            'transaction',
            'directorates',
            'directors',
            'plateTypes',
            'carMakes',
            'carColors',
            'transactionTypes'
        ));
    }

    public function update(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && !$transaction->canBeEditedByDataEntry()) {
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

        AuditLogger::log('دەستکاریکردنی زانیاری مامەڵە (دوای گەڕاندنەوە)', Transaction::class, $transaction->id, "بارکۆد: {$transaction->barcode}");

        return redirect()->route('transactions.show', $transaction->id)
            ->with('success', 'زانیاری مامەڵەکە بە سەرکەوتوویی دەستکاری کرا و ڕاستکرایەوە.');
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
            'is_audited' => false,
        ]);

        AuditLogger::log('گەڕاندنەوەی مامەڵە بۆ چاککردنەوە', Transaction::class, $transaction->id, "هۆکار: {$validated['return_reason']}");

        return redirect()->route('transactions.show', $transaction->id)
            ->with('warning', 'مامەڵەکە گەڕێنراوەتەوە بۆ بەشی تەخمین/داتائەنتەری بۆ ڕاستکردنەوە.');
    }


    public function show(Transaction $transaction)
    {
        $transaction->load(['transactionType', 'trafficDirectorate', 'director', 'plateType', 'carMake', 'carColor']);
        return view('transactions.show', compact('transaction'));
    }

    public function scanBarcode(Request $request)
    {
        $barcode = trim($request->barcode);
        $transaction = Transaction::where('barcode', $barcode)->first();

        if (!$transaction) {
            return redirect()->back()->with('error', "هیچ مامەڵەیەک نەدۆزرایەوە بە بارکۆدی: {$barcode}");
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
        ]);

        AuditLogger::log('ئەنجامدانی کەشف و تەخمین', Transaction::class, $transaction->id, "تەخمین پەسەندکرا بۆ بارکۆد: {$transaction->barcode}");

        return redirect()->back()->with('success', 'کەشف و تەخمین بە سەرکەوتوویی پەسەندکرا. قۆناغی دەستکاریکردن قوفڵ بوو.');
    }

    public function audit(Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('transactions.audit')) {
            return redirect()->back()->with('error', 'تەنها کارمەندی وردبین (Auditor) دەتوانێت تێپەڕاندنی قۆناغی وردبینی ئەنجام بدات!');
        }

        if (!$transaction->is_inspected) {
            return redirect()->back()->with('error', 'کاری تەخمین ئەنجام بدە دواتر وەرە وردبینی! ناتوانرێت ستێپی تەخمین تێپەڕێندرێت.');
        }

        $transaction->update([
            'is_audited' => true,
            'audited_by' => auth()->user()?->name ?? 'وردبین',
            'audited_at' => now(),
        ]);

        AuditLogger::log('وردبینیکردنی مامەڵە', Transaction::class, $transaction->id, "وردبینی پەسەندکرا بۆ بارکۆد: {$transaction->barcode}");

        return redirect()->back()->with('success', 'وردبینی مامەڵەکە بە سەرکەوتوویی ئەنجامدرا.');
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
            return redirect()->back()->with('error', "ئەم ژمارە پسوولەی ۳۷/أ ({$receiptNum}) پووچەڵکراوەتەوە و بلۆککراوە لە سیستەمدا! ڕێگەپێدراو نییە بەکاربهێنرێتەوە. هۆکاری پووچەڵکردنەوە: {$reason}");
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

        AuditLogger::log('وەرگرتنی پارە و بڕینی وەسڵ', Transaction::class, $transaction->id, "وەسڵی ۳۷/أ: {$request->receipt_37a_number} - کۆی پارە: {$transaction->total_pay} د.ع");

        return redirect()->back()->with('success', 'پارەدانی مامەڵەکە و وەسڵی ۳۷/أ بە سەرکەوتوویی تۆمارکرا.');
    }

    public function completeBooklet(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('transactions.complete_booklet')) {
            return redirect()->back()->with('error', 'تەنها کارمەندی بەشی دەفتەر (Booklet Section) دەتوانێت ژمارەی دەفتەر تۆمار بکات!');
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

        return redirect()->back()->with('success', 'دەفتەر بە سەرکەوتوویی تەواو کرا و تۆمارکرا.');
    }

    public function printPaymentReceipt(Transaction $transaction)
    {
        if (!$transaction->is_paid) {
            return redirect()->back()->with('error', 'پسوولەی پارەدان شایەنی چاپکردن نییە چونکە مامەڵەکە پارەدانی بۆ ئەنجام نەدراوە.');
        }

        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director']);
        return view('transactions.print_payment_receipt', compact('transaction'));
    }

    public function submitArchive(Transaction $transaction)
    {
        if (!$transaction->is_paid) {
            return redirect()->back()->with('error', 'مامەڵەکە سەرجەم قۆناغەکانی تێنەپەڕاندووە (پارەدان تێنەپەڕیوە) بۆ سەبمیت.');
        }

        if ($transaction->requiresBooklet() && !$transaction->is_booklet_completed) {
            return redirect()->back()->with('error', 'ئەم مامەڵەیە پێویستی بە چاپی دەفتەر هەیە و دەفتەری بۆ تەواو نەکراوە!');
        }

        $transaction->update([
            'is_submitted' => true,
            'submitted_by' => auth()->user()?->name ?? 'کارمەند',
            'submitted_at' => now(),
        ]);

        AuditLogger::log('سەبمیتکردن و ئەرشیفکردنی مامەڵە (ستێپی کۆتایی)', Transaction::class, $transaction->id, "مامەڵەی بارکۆد {$transaction->barcode} بە تەواوەتی سەبمیت و ئەرشیف کرا");

        return redirect()->back()->with('success', 'مامەڵەکە بە سەرکەوتوویی نێردرا بۆ ستێپی کۆتایی (سەبمیت و ئەرشیفکرا).');
    }

    public function revertStep(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'تەنها ئەدمین ڕێگەپێدراوە بۆ ڕێکخستنەوەی قۆناغی مامەڵە (Revert Step)!');
        }

        $request->validate([
            'step' => 'required|in:payment,booklet,audit,inspection,all',
            'reversion_reason' => 'required|string|min:4',
        ]);

        $step = $request->step;
        $reason = $request->reversion_reason;
        $oldValues = [];
        $newValues = [];

        if ($step === 'payment' || $step === 'all') {
            $oldValues = ['is_paid' => $transaction->is_paid, 'receipt_37a_number' => $transaction->receipt_37a_number];
            
            if ($transaction->receipt_37a_number) {
                BlockedReceiptNumber::firstOrCreate(
                    ['receipt_number' => $transaction->receipt_37a_number],
                    [
                        'cancellation_reason' => $reason ?? 'ڕێکخستنەوەی قۆناغی پارەدان لەلایەن ئەدمین',
                        'blocked_by' => auth()->user()?->name ?? 'ئەدمین',
                        'transaction_id' => $transaction->id,
                    ]
                );
            }

            $transaction->update([
                'is_paid' => false,
                'receipt_37a_number' => null,
                'paid_by' => null,
                'paid_at' => null,
            ]);
            $newValues = ['is_paid' => false, 'receipt_37a_number' => null];
            $msg = "قۆناغی پارەدان ڕێکخرایەوە (Reset). پسوولەی ۳۷/أ پێشوو کۆتایی پێهات و بلۆککرا.";
        } elseif ($step === 'booklet') {
            $oldValues = ['is_booklet_completed' => $transaction->is_booklet_completed, 'booklet_number' => $transaction->booklet_number];
            $transaction->update([
                'is_booklet_completed' => false,
                'booklet_number' => null,
                'booklet_by' => null,
                'booklet_completed_at' => null,
            ]);
            $newValues = ['is_booklet_completed' => false, 'booklet_number' => null];
            $msg = "قۆناغی دەفتەر ڕێکخرایەوە (Reset). ژمارەی دەفتەرەکە ڕێسێت کرایەوە.";
        } elseif ($step === 'audit') {
            $oldValues = ['is_audited' => $transaction->is_audited];
            $transaction->update([
                'is_audited' => false,
                'audited_by' => null,
                'audited_at' => null,
            ]);
            $newValues = ['is_audited' => false];
            $msg = "قۆناغی وردبینی ڕێکخرایەوە (Reset).";
        } elseif ($step === 'inspection') {
            $oldValues = ['is_inspected' => $transaction->is_inspected];
            $transaction->update([
                'is_inspected' => false,
                'inspected_by' => null,
                'inspected_at' => null,
            ]);
            $newValues = ['is_inspected' => false];
            $msg = "قۆناغی کەشف و تەخمین ڕێکخرایەوە (Reset).";
        } elseif ($step === 'all') {
            $oldValues = ['is_inspected' => $transaction->is_inspected, 'is_audited' => $transaction->is_audited, 'is_paid' => $transaction->is_paid, 'receipt_37a_number' => $transaction->receipt_37a_number];
            
            if ($transaction->receipt_37a_number) {
                BlockedReceiptNumber::firstOrCreate(
                    ['receipt_number' => $transaction->receipt_37a_number],
                    [
                        'cancellation_reason' => $reason ?? 'ڕێکخستنەوەی سەرجەم قۆناغەکان لەلایەن ئەدمین',
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
                'is_returned' => false,
            ]);
            $newValues = ['all_reset' => true];
            $msg = "سەرجەم قۆناغەکانی مامەڵە گەڕێنرانەوە بۆ داتائەنتەری و سەرلەنوێ کرانەوە.";
        }

        AuditLogger::log('ڕێکخستنەوەی قۆناغ (Step Reversion)', Transaction::class, $transaction->id, "ڕێکخستنەوەی قۆناغی [{$step}] لەلایەن ئەدمین - هۆکار: {$reason}", $oldValues, $newValues);
        NotificationService::send(null, 'data_entry', 'قۆناغی مامەڵە ڕێکخرایەوە', "ئەدمین قۆناغی [{$step}] بۆ بارکۆدی {$transaction->barcode} ڕێکخستەوە. هۆکار: {$reason}", route('transactions.show', $transaction->id));

        return redirect()->route('transactions.show', $transaction->id)->with('success', $msg);
    }

    public function cancel(Request $request, Transaction $transaction)
    {
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

    public function printReceipt(Transaction $transaction)
    {
        $user = auth()->user();

        // 1. Only Estimator (inspector) or Admin can print
        if (!$user->isAdmin() && $user->role !== 'inspector') {
            return redirect()->back()->with('error', 'تەنها فەرمانبەری کەشف و تەخمین دەتوانێت پسولەی کەشف و خەمڵاندن چاپ بکات.');
        }

        // 2. Must be approved by Estimator first
        if (!$transaction->is_inspected && !$user->isAdmin()) {
            return redirect()->back()->with('error', 'پسولەی کەشف و خەمڵاندن تەنها دوای پەسەندکردنی کەشف و تەخمین چاڵاک دەبێت.');
        }

        // 3. Locked for Estimator once Auditor approves
        if ($transaction->is_audited && !$user->isAdmin() && $user->role === 'inspector') {
            return redirect()->back()->with('error', 'وردبینی بۆ ئەم مامەڵەیە ئەنجامدراوە. فەرمانبەری تەخمین ناتوانێت دوای وردبینی پسولەی کەشف چاپ بکات.');
        }

        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor']);
        return view('transactions.print_receipt', compact('transaction'));
    }

    public function printDataEntry(Transaction $transaction)
    {
        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director', 'parentTransaction', 'relatedTransactions']);
        return view('transactions.print_data_entry', compact('transaction'));
    }

    public function printBooklet(Transaction $transaction)
    {
        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director']);
        return view('transactions.print_booklet', compact('transaction'));
    }

    public function printPledge(Transaction $transaction)
    {
        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor']);
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
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'دەسەڵاتی سڕینەوەی کاتیت نییە!');
        }

        $barcode = $transaction->barcode;
        $name = $transaction->visitor_name;
        $transaction->delete(); // Soft Delete

        AuditLogger::log('سڕینەوەی کاتی (Soft Delete)', Transaction::class, $transaction->id, "مامەڵەی بارکۆد {$barcode} خرایە ئەرشیفی سڕدراوەکان");

        return redirect()->route('transactions.index')->with('success', "مامەڵەی بارکۆد ({$barcode}) بە سەرکەوتوویی خرایە ئەرشیفی سڕدراوەکانەوە (تەنەکەخۆڵ).");
    }

    public function restore($id)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'دەسەڵاتی گەڕاندنەوەی مامەڵەی سڕدراوەت نییە!');
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

    public function markPrinted(Transaction $transaction)
    {
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
        $transaction->update(['is_printed' => true]);
        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director']);
        return view('transactions.print_cancellation', compact('transaction'));
    }

    public function printRestriction(Transaction $transaction)
    {
        $transaction->update(['is_printed' => true]);
        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director']);
        return view('transactions.print_restriction', compact('transaction'));
    }

    public function printInspectionLetter(Transaction $transaction)
    {
        $transaction->update(['is_printed' => true]);
        $transaction->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor', 'director']);
        return view('transactions.print_inspection_letter', compact('transaction'));
    }
}

