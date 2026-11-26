<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\TransactionType;

class FeeCalculatorService
{
    public static function calculate(int $transactionTypeId, int $numYears = 1, bool $isBus = false, ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $numYears = min(max($numYears, 1), 10); // Flexible duration limit
        
        $payYears = 0;
        $stamp = 3000;
        $form = 4000;
        $fine = 0;
        $inspection = 0;

        $txType = TransactionType::find($transactionTypeId);

        // Types without start/end validity dates: 5 (Cancellation), 8 (Inspection), 6 (Pledge Form)
        $isNoDateType = in_array($transactionTypeId, [5, 6, 8]) || ($txType && (
            str_contains($txType->name_kurdish, 'پووچەڵ') || 
            str_contains($txType->name_kurdish, 'پشکنین') || 
            str_contains($txType->name_kurdish, 'بەڵێننامە')
        ));

        if ($isNoDateType) {
            $startDate = null;
            $endDate = null;
        } else {
            $startDate = $customStartDate ? Carbon::parse($customStartDate) : Carbon::today();
            $endDate = $customEndDate ? Carbon::parse($customEndDate) : null;
        }

        // Pledge Form (Type 6): Fixed fee rule = 8,000 Dinars (4,000 stamp + 4,000 form)
        if ($transactionTypeId == 6 || ($txType && str_contains($txType->name_kurdish, 'بەڵێننامە'))) {
            $payYears = 0;
            $stamp = 4000;
            $form = 4000;
            $fine = 0;
            $inspection = 0;
            $startDate = null;
            $endDate = null;
        } elseif ($txType) {
            $stamp = (float)$txType->stamp_pay;
            $form = (float)$txType->form_pay;
            $fine = (float)$txType->fine_pay;
            $busPay = (float)($txType->bus_inspection_pay ?: 30000);
            $inspection = $isBus ? $busPay : (float)$txType->inspection_pay;

            // Enforce explicit fine rules per transaction type:
            // ناو گۆڕین (3): ۲٥ هەزار | دەفتەرگۆڕین (4): ۲٥ هەزار | ناو و دەفتەرگۆڕین (7): ٥٠ هەزار
            if (in_array($transactionTypeId, [3, 4])) {
                $fine = 25000;
                $payYears = 0;
                $inspection = 0;
            } elseif ($transactionTypeId == 7) {
                $fine = 50000;
                $payYears = 0;
                $inspection = 0;
            } else {
                $fine = (float)$txType->fine_pay;
            }

            // Only Issue Booklet (1) and Renew Booklet (2) multiply fee by years
            if (in_array($transactionTypeId, [1, 2])) {
                if ($numYears == 1) $payYears = (float)$txType->pay_1_year;
                elseif ($numYears == 2) $payYears = (float)$txType->pay_2_year;
                elseif ($numYears == 3) $payYears = (float)$txType->pay_3_year;
                else $payYears = (float)$txType->pay_1_year * $numYears;
            } else {
                $payYears = 0;
            }

            // For booklet types with validity (1, 2, 3, 4, 7), numYears sets end_date
            if (!$isNoDateType && $startDate) {
                $endDate = $startDate->copy()->addYears($numYears);
            }
        } else {
            // Fallback rules
            switch ($transactionTypeId) {
                case 1: // New Issue
                case 2: // Renewal
                    if ($numYears == 1) $payYears = 80000;
                    elseif ($numYears == 2) $payYears = 120000;
                    elseif ($numYears == 3) $payYears = 140000;
                    else $payYears = 80000 * $numYears;

                    $stamp = 3000;
                    $form = 4000;
                    $inspection = $isBus ? 30000 : 20000;
                    $endDate = $startDate ? $startDate->copy()->addYears($numYears) : null;
                    break;

                case 3: // Name Change
                case 4: // Booklet Replacement
                    $stamp = 3000;
                    $form = 4000;
                    $fine = 25000;
                    $payYears = 0;
                    $endDate = $startDate ? $startDate->copy()->addYears($numYears) : null;
                    break;

                case 7: // Name & Replacement Combined
                    $stamp = 3000;
                    $form = 4000;
                    $fine = 50000;
                    $payYears = 0;
                    $endDate = $startDate ? $startDate->copy()->addYears($numYears) : null;
                    break;

                case 5: // Cancellation
                    $stamp = 3000;
                    $form = 4000;
                    $inspection = $isBus ? 30000 : 20000;
                    $payYears = 0;
                    $startDate = null;
                    $endDate = null;
                    break;

                case 6: // Pledge Form
                    $stamp = 4000;
                    $form = 4000;
                    $payYears = 0;
                    $startDate = null;
                    $endDate = null;
                    break;

                case 8: // Inspection
                    $stamp = 3000;
                    $form = 4000;
                    $inspection = $isBus ? 30000 : 20000;
                    $payYears = 0;
                    $startDate = null;
                    $endDate = null;
                    break;
            }
        }

        $totalPay = $payYears + $stamp + $form + $fine + $inspection;

        return [
            'num_years' => $isNoDateType ? 0 : $numYears,
            'pay_amount_years' => $payYears,
            'pay_stamp' => $stamp,
            'pay_form' => $form,
            'pay_fine' => $fine,
            'pay_inspection' => $inspection,
            'total_pay' => $totalPay,
            'start_date' => $startDate ? $startDate->toDateString() : null,
            'end_date' => $endDate ? $endDate->toDateString() : null,
        ];
    }
}
