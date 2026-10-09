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
        $data = $this->prepareReportData($request);
        $transactionTypes = TransactionType::all();

        return view('transactions.accounting_report', array_merge($data, [
            'transactionTypes' => $transactionTypes,
        ]));
    }

    public function print(Request $request)
    {
        $data = $this->prepareReportData($request);
        return view('transactions.print_accounting_report', $data);
    }

    public function exportCsv(Request $request)
    {
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

        // Date Presets & Custom Filters
        $preset = $request->get('preset', '');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $exactDate = $request->get('exact_date');
        $filterMonth = $request->get('filter_month');
        $filterYear = $request->get('filter_year');

        $activePreset = $preset;

        if ($preset === 'today') {
            $today = Carbon::today()->toDateString();
            $query->whereDate(DB::raw('COALESCE(paid_at, transaction_date)'), $today);
        } elseif ($preset === 'yesterday') {
            $yesterday = Carbon::yesterday()->toDateString();
            $query->whereDate(DB::raw('COALESCE(paid_at, transaction_date)'), $yesterday);
        } elseif ($preset === 'this_week') {
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
            $query->whereBetween(DB::raw('COALESCE(paid_at, transaction_date)'), [$startOfWeek, $endOfWeek]);
        } elseif ($preset === 'this_month') {
            $query->whereYear(DB::raw('COALESCE(paid_at, transaction_date)'), Carbon::now()->year)
                  ->whereMonth(DB::raw('COALESCE(paid_at, transaction_date)'), Carbon::now()->month);
        } elseif ($preset === 'last_month') {
            $lastMonth = Carbon::now()->subMonth();
            $query->whereYear(DB::raw('COALESCE(paid_at, transaction_date)'), $lastMonth->year)
                  ->whereMonth(DB::raw('COALESCE(paid_at, transaction_date)'), $lastMonth->month);
        } elseif ($preset === 'this_year') {
            $query->whereYear(DB::raw('COALESCE(paid_at, transaction_date)'), Carbon::now()->year);
        } elseif ($preset === 'last_year') {
            $query->whereYear(DB::raw('COALESCE(paid_at, transaction_date)'), Carbon::now()->subYear()->year);
        } elseif ($exactDate) {
            $activePreset = 'exact';
            $query->whereDate(DB::raw('COALESCE(paid_at, transaction_date)'), $exactDate);
        } elseif ($filterMonth || $filterYear) {
            $activePreset = 'month_year';
            if ($filterYear) {
                $query->whereYear(DB::raw('COALESCE(paid_at, transaction_date)'), $filterYear);
            }
            if ($filterMonth) {
                $query->whereMonth(DB::raw('COALESCE(paid_at, transaction_date)'), $filterMonth);
            }
        } elseif ($dateFrom || $dateTo) {
            $activePreset = 'custom';
            if ($dateFrom) {
                $query->whereDate(DB::raw('COALESCE(paid_at, transaction_date)'), '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate(DB::raw('COALESCE(paid_at, transaction_date)'), '<=', $dateTo);
            }
        }

        // Transaction Type Filter
        if ($request->filled('transaction_type_id')) {
            $query->where('transaction_type_id', $request->transaction_type_id);
        }

        // Status Filter: all, paid, cancelled
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

        // General search term (name, plate, receipt, barcode)
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('visitor_name', 'like', "%{$term}%")
                  ->orWhere('visitor_name_eng', 'like', "%{$term}%")
                  ->orWhere('receipt_37a_number', 'like', "%{$term}%")
                  ->orWhere('plate_number', 'like', "%{$term}%")
                  ->orWhere('barcode', 'like', "%{$term}%");
            });
        }

        // Order by date & ID ascending for sequential audit listing
        $query->orderBy(DB::raw('COALESCE(paid_at, transaction_date)'), 'asc')
              ->orderBy('id', 'asc');

        $rawCollection = $query->get();

        // Parse formatted display date and natural numeric receipt
        $rawCollection->each(function ($tx) {
            $dateObj = $tx->paid_at ?? $tx->transaction_date ?? $tx->created_at;
            $tx->display_date = $dateObj ? Carbon::parse($dateObj)->toDateString() : '';
            // Extract numeric value from receipt for filtering and range
            $digits = preg_replace('/\D/', '', (string)$tx->receipt_37a_number);
            $tx->receipt_num_numeric = $digits !== '' ? (int)$digits : null;
        });

        // Filter by Receipt Range if provided (receipt_from / receipt_to)
        $receiptFrom = $request->get('receipt_from');
        $receiptTo = $request->get('receipt_to');

        if ($receiptFrom !== null && $receiptFrom !== '') {
            $fromVal = (int)preg_replace('/\D/', '', $receiptFrom);
            if ($fromVal > 0) {
                $rawCollection = $rawCollection->filter(function ($tx) use ($fromVal) {
                    return $tx->receipt_num_numeric !== null && $tx->receipt_num_numeric >= $fromVal;
                });
            }
        }

        if ($receiptTo !== null && $receiptTo !== '') {
            $toVal = (int)preg_replace('/\D/', '', $receiptTo);
            if ($toVal > 0) {
                $rawCollection = $rawCollection->filter(function ($tx) use ($toVal) {
                    return $tx->receipt_num_numeric !== null && $tx->receipt_num_numeric <= $toVal;
                });
            }
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
        $totalAmanat = $totalInspection; // In accounting 66 format: امانات is aligned with inspection/service fee

        // Fallback grand total logic if fees sum differs
        if ($grandTotal == 0 && ($totalDahat > 0 || $totalInspection > 0)) {
            $grandTotal = $totalDahat + $totalInspection + $totalAmanat;
        }

        $grandTotalWords = KurdishNumberToWords::convert($grandTotal);

        // Detect default receipt range if not manually filtered
        if (empty($receiptFrom) && $transactions->isNotEmpty()) {
            $firstNum = $transactions->firstWhere('receipt_num_numeric', '!=', null);
            $receiptFrom = $firstNum ? $firstNum->receipt_num_numeric : 1;
        }
        if (empty($receiptTo) && $transactions->isNotEmpty()) {
            $lastNum = $transactions->last(fn($t) => $t->receipt_num_numeric !== null);
            $receiptTo = $lastNum ? $lastNum->receipt_num_numeric : $totalReceiptsCount;
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
            'activePreset' => $activePreset,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'exactDate' => $exactDate,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
            'status' => $status,
            'search' => $request->get('search', ''),
            'transactionTypeId' => $request->get('transaction_type_id', ''),
        ];
    }
}
