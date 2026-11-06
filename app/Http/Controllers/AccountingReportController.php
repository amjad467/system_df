<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionType;
use App\Services\KurdishNumberToWords;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('reports.accounting_66')) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بینینی ڕاپۆرتی محاسبة ٦٦ت نییە بەپێی پێرمشنەکان!');
        }

        $data = $this->prepareReportData($request);
        $transactionTypes = TransactionType::all();

        return view('transactions.accounting_report', array_merge($data, [
            'transactionTypes' => $transactionTypes,
        ]));
    }

    public function print(Request $request)
    {
        if (!auth()->user()->hasPermission('reports.accounting_66')) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی چاپی محاسبة ٦٦ت نییە بەپێی پێرمشنەکان!');
        }

        $data = $this->prepareReportData($request);
        return view('transactions.print_accounting_report', $data);
    }

    public function exportCsv(Request $request)
    {
        if (!auth()->user()->hasPermission('reports.accounting_export')) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی داگرتنی داتای محاسبة ٦٦ت نییە بەپێی پێرمشنەکان!');
        }
        $data = $this->prepareReportData($request);
        $transactions = $data['transactions'];

        $filename = 'accounting_66_report_' . date('Y_m_d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions, $data) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel Arabic/Kurdish support
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ڕاپۆرتی ژمێریاری محاسبة ٦٦ - بەڕێوەبەرایەتی گومرگی سلێمانی']);
            fputcsv($handle, ["بەرواری دەرهێنان: " . now()->format('Y-m-d H:i')]);
            fputcsv($handle, ["لە پسولەی: " . ($data['receiptFrom'] ?? 'سەرەتا') . " بۆ: " . ($data['receiptTo'] ?? 'کۆتایی')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'ز',
                'ناوی لێوەوەرگیراو',
                'ژ. پسولە',
                'جۆری مامەڵە',
                'بڕی ساڵ',
                'بەروار',
                'رەسم',
                'اجور',
                'پول',
                'فۆرم',
                'کۆی گشتی',
                'دۆخ',
            ]);

            foreach ($transactions as $index => $tx) {
                $isCanc = $tx->is_cancelled || !empty($tx->cancelled_at);
                fputcsv($handle, [
                    $index + 1,
                    $tx->visitor_name,
                    $tx->receipt_37a_number ?? '-',
                    $tx->transactionType->name_kurdish ?? '-',
                    $isCanc ? 0 : ($tx->num_years ?? 1),
                    $tx->display_date,
                    $isCanc ? 0 : $tx->pay_amount_years,
                    $isCanc ? 0 : $tx->pay_inspection,
                    $isCanc ? 0 : $tx->pay_stamp,
                    $isCanc ? 0 : $tx->pay_form,
                    $isCanc ? 0 : $tx->total_pay,
                    $isCanc ? 'پووچەڵکراوەتەوە' : 'پارەدراوە',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['کورتەی دارایی']);
            fputcsv($handle, ['کۆی پسولەکان', $data['totalReceiptsCount']]);
            fputcsv($handle, ['پسولەی پوچەڵکراو', $data['cancelledCount']]);
            fputcsv($handle, ['پسولەی پارەی وەرگیراو', $data['paidCount']]);
            fputcsv($handle, ['کۆی رەسم', $data['totalRasm']]);
            fputcsv($handle, ['کۆی پوول', $data['totalPul']]);
            fputcsv($handle, ['کۆی فۆرم', $data['totalForm']]);
            fputcsv($handle, ['کۆی داهات', $data['totalDahat']]);
            fputcsv($handle, ['اجورکشف', $data['totalInspection']]);
            fputcsv($handle, ['کۆی امانات', $data['totalAmanat']]);
            fputcsv($handle, ['کۆی گشتی بە ژمارە', $data['grandTotal']]);
            fputcsv($handle, ['کۆی گشتی بە نووسین', $data['grandTotalWords']]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function prepareReportData(Request $request): array
    {
        $query = Transaction::with(['transactionType', 'trafficDirectorate'])
            ->where(function ($q) {
                $q->whereNotNull('receipt_37a_number')
                  ->orWhere('is_paid', true);
            });

        // 1. Helper function to normalize Eastern Arabic / Persian / Kurdish numerals to ASCII 0-9
        $normalizeNum = function ($val) {
            if ($val === null || $val === '') return '';
            $eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            return str_replace($eastern, $western, (string)$val);
        };

        // 2. Date Filtering Logic
        $preset = $request->get('preset', '');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $dateType = $request->get('date_type', 'any'); // 'any', 'paid_at', 'transaction_date'

        // Quick presets calculate exact start/end dates if not manually overridden
        if ($preset && empty($dateFrom) && empty($dateTo)) {
            if ($preset === 'today') {
                $dateFrom = Carbon::today()->toDateString();
                $dateTo = Carbon::today()->toDateString();
            } elseif ($preset === 'yesterday') {
                $dateFrom = Carbon::yesterday()->toDateString();
                $dateTo = Carbon::yesterday()->toDateString();
            } elseif ($preset === 'this_week') {
                $dateFrom = Carbon::now()->startOfWeek()->toDateString();
                $dateTo = Carbon::now()->endOfWeek()->toDateString();
            } elseif ($preset === 'this_month') {
                $dateFrom = Carbon::now()->startOfMonth()->toDateString();
                $dateTo = Carbon::now()->endOfMonth()->toDateString();
            } elseif ($preset === 'last_month') {
                $lastM = Carbon::now()->subMonth();
                $dateFrom = $lastM->startOfMonth()->toDateString();
                $dateTo = $lastM->endOfMonth()->toDateString();
            } elseif ($preset === 'this_year') {
                $dateFrom = Carbon::now()->startOfYear()->toDateString();
                $dateTo = Carbon::now()->endOfYear()->toDateString();
            } elseif ($preset === 'last_year') {
                $lastY = Carbon::now()->subYear();
                $dateFrom = $lastY->startOfYear()->toDateString();
                $dateTo = $lastY->endOfYear()->toDateString();
            }
        }

        // Auto-swap dates if from > to
        if (!empty($dateFrom) && !empty($dateTo) && $dateFrom > $dateTo) {
            $tmp = $dateFrom;
            $dateFrom = $dateTo;
            $dateTo = $tmp;
        }

        $activePreset = $preset;
        if (!empty($dateFrom) || !empty($dateTo)) {
            $startDate = $dateFrom ?: '2000-01-01';
            $endDate = $dateTo ?: '2099-12-31';

            if ($dateType === 'paid_at') {
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('paid_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                      ->orWhere(function($sub) use ($startDate, $endDate) {
                          $sub->whereNull('paid_at')
                              ->whereBetween('transaction_date', [$startDate, $endDate]);
                      });
                });
            } elseif ($dateType === 'transaction_date') {
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            } else {
                // 'any' (Default): Matches if either transaction_date OR paid_at is in range
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('transaction_date', [$startDate, $endDate])
                      ->orWhereBetween('paid_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                });
            }
        }

        // 3. Status Filter: all, paid, cancelled
        $status = $request->get('status', 'all');
        if ($status === 'paid') {
            $query->where(function($q) {
                $q->whereNull('cancelled_at')
                  ->where(function($sub) {
                      $sub->where('is_cancelled', false)->orWhereNull('is_cancelled');
                  });
            });
        } elseif ($status === 'cancelled') {
            $query->where(function($q) {
                $q->whereNotNull('cancelled_at')
                  ->orWhere('is_cancelled', true);
            });
        }

        // 4. Transaction Type Filter
        if ($request->filled('transaction_type_id')) {
            $query->where('transaction_type_id', $request->transaction_type_id);
        }

        // 5. General Search Term (Name, Plate, Receipt, Barcode, Chassis)
        if ($request->filled('search')) {
            $rawTerm = trim($request->search);
            $normTerm = trim($normalizeNum($rawTerm));
            $query->where(function ($q) use ($rawTerm, $normTerm) {
                $q->where('visitor_name', 'like', "%{$rawTerm}%")
                  ->orWhere('visitor_name_eng', 'like', "%{$rawTerm}%")
                  ->orWhere('receipt_37a_number', 'like', "%{$normTerm}%")
                  ->orWhere('plate_number', 'like', "%{$rawTerm}%")
                  ->orWhere('plate_number', 'like', "%{$normTerm}%")
                  ->orWhere('barcode', 'like', "%{$normTerm}%")
                  ->orWhere('chassis_number', 'like', "%{$normTerm}%");
            });
        }

        // 6. Number Range Extraction (Supports both Eastern and Western Digits)
        $numberType = $request->get('number_type', 'auto'); // 'auto', 'receipt', 'sequence'
        $numFromRaw = $request->get('num_from', $request->get('receipt_from'));
        $numToRaw = $request->get('num_to', $request->get('receipt_to'));

        $numFrom = null;
        $numTo = null;

        if ($numFromRaw !== null && $numFromRaw !== '') {
            $d = preg_replace('/\D/', '', $normalizeNum($numFromRaw));
            if ($d !== '') $numFrom = (int)$d;
        }
        if ($numToRaw !== null && $numToRaw !== '') {
            $d = preg_replace('/\D/', '', $normalizeNum($numToRaw));
            if ($d !== '') $numTo = (int)$d;
        }

        // Auto-swap min and max if entered in reverse
        if ($numFrom !== null && $numTo !== null && $numFrom > $numTo) {
            $temp = $numFrom;
            $numFrom = $numTo;
            $numTo = $temp;
        }

        $rawCollection = $query->get();

        // Parse formatted display date and natural numeric receipt
        $rawCollection->each(function ($tx) {
            $dateObj = $tx->paid_at ?? $tx->transaction_date ?? $tx->created_at;
            $tx->display_date = $dateObj ? Carbon::parse($dateObj)->toDateString() : '';
            
            $eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $cleanReceipt = str_replace($eastern, $western, (string)$tx->receipt_37a_number);
            $digits = preg_replace('/\D/', '', $cleanReceipt);
            $tx->receipt_num_numeric = $digits !== '' ? (int)$digits : null;
        });

        // Apply Number Range Filter
        if ($numFrom !== null || $numTo !== null) {
            $minNum = $numFrom ?? 0;
            $maxNum = $numTo ?? PHP_INT_MAX;

            $rawCollection = $rawCollection->filter(function ($tx) use ($minNum, $maxNum, $numberType) {
                $receiptMatch = false;
                if ($tx->receipt_num_numeric !== null) {
                    $receiptMatch = ($tx->receipt_num_numeric >= $minNum && $tx->receipt_num_numeric <= $maxNum);
                }
                $seqMatch = ($tx->id >= $minNum && $tx->id <= $maxNum);

                if ($numberType === 'receipt') {
                    return $receiptMatch;
                } elseif ($numberType === 'sequence') {
                    return $seqMatch;
                } else { // 'auto'
                    return ($tx->receipt_num_numeric !== null) ? $receiptMatch : $seqMatch;
                }
            });
        }

        // Sort items sequentially
        if ($numberType === 'receipt' || ($numFrom !== null && $numberType !== 'sequence')) {
            $rawCollection = $rawCollection->sortBy([
                ['receipt_num_numeric', 'asc'],
                ['id', 'asc'],
            ]);
        } else {
            $rawCollection = $rawCollection->sortBy([
                ['display_date', 'asc'],
                ['id', 'asc'],
            ]);
        }

        $transactions = $rawCollection->values();

        // Calculate Accounting 66 Summaries
        $totalReceiptsCount = $transactions->count();
        $cancelledCount = 0;
        $paidCount = 0;

        $totalRasm = 0;
        $totalPul = 0;
        $totalForm = 0;
        $totalInspection = 0;
        $grandTotal = 0;

        foreach ($transactions as $tx) {
            $isCancelled = $tx->is_cancelled || !empty($tx->cancelled_at);
            if ($isCancelled) {
                $cancelledCount++;
            } else {
                $paidCount++;
                $totalRasm += (float)$tx->pay_amount_years;
                $totalPul += (float)$tx->pay_stamp;
                $totalForm += (float)$tx->pay_form;
                $totalInspection += (float)$tx->pay_inspection;
                $grandTotal += (float)$tx->total_pay;
            }
        }

        $totalDahat = $totalRasm + $totalPul + $totalForm;
        $totalAmanat = $totalInspection;

        if ($grandTotal == 0 && ($totalDahat > 0 || $totalInspection > 0)) {
            $grandTotal = $totalDahat + $totalInspection + $totalAmanat;
        }

        $grandTotalWords = KurdishNumberToWords::convert($grandTotal);

        // Detect default display range for print header
        $receiptFrom = $numFrom;
        $receiptTo = $numTo;

        if (empty($receiptFrom) && $transactions->isNotEmpty()) {
            $firstWithNum = $transactions->firstWhere('receipt_num_numeric', '!=', null);
            $receiptFrom = $firstWithNum ? $firstWithNum->receipt_num_numeric : $transactions->first()->id;
        }
        if (empty($receiptTo) && $transactions->isNotEmpty()) {
            $lastWithNum = $transactions->last(fn($t) => $t->receipt_num_numeric !== null);
            $receiptTo = $lastWithNum ? $lastWithNum->receipt_num_numeric : $transactions->last()->id;
        }

        return [
            'transactions' => $transactions,
            'totalReceiptsCount' => $totalReceiptsCount,
            'cancelledCount' => $cancelledCount,
            'paidCount' => $paidCount,
            'totalRasm' => $totalRasm,
            'totalPul' => $totalPul,
            'totalForm' => $totalForm,
            'totalDahat' => $totalDahat,
            'totalInspection' => $totalInspection,
            'totalAmanat' => $totalAmanat,
            'grandTotal' => $grandTotal,
            'grandTotalWords' => $grandTotalWords,
            'receiptFrom' => $receiptFrom,
            'receiptTo' => $receiptTo,
            'numFrom' => $numFromRaw,
            'numTo' => $numToRaw,
            'numberType' => $numberType,
            'activePreset' => $activePreset,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'dateType' => $dateType,
            'status' => $status,
            'search' => $request->get('search', ''),
            'transactionTypeId' => $request->get('transaction_type_id', ''),
        ];
    }
}
