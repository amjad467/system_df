<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransactionType;
use App\Services\AuditLogger;

class SettingsController extends Controller
{
    public function pricing()
    {
        $transactionTypes = TransactionType::all();
        return view('settings.pricing', compact('transactionTypes'));
    }

    public function updatePricing(Request $request)
    {
        $prices = $request->input('prices', []);

        foreach ($prices as $id => $data) {
            $txType = TransactionType::find($id);
            if ($txType) {
                $txType->update([
                    'pay_1_year' => $data['pay_1_year'] ?? 0,
                    'pay_2_year' => $data['pay_2_year'] ?? 0,
                    'pay_3_year' => $data['pay_3_year'] ?? 0,
                    'stamp_pay' => $data['stamp_pay'] ?? 0,
                    'form_pay' => $data['form_pay'] ?? 0,
                    'fine_pay' => $data['fine_pay'] ?? 0,
                    'inspection_pay' => $data['inspection_pay'] ?? 0,
                    'bus_inspection_pay' => $data['bus_inspection_pay'] ?? 0,
                ]);
            }
        }

        AuditLogger::log('دەستکاریکردنی نرخی مامەڵەکان لایەن سۆپەر ئەدمین', null, null, 'گۆڕانکاری لە سزاکان، ڕسوومات و نرخەکان کرا');

        return redirect()->back()->with('success', 'نرخی سەرجەم مامەڵەکان بە سەرکەوتوویی لەلایەن سۆپەر ئەدمین نوێکرایەوە.');
    }
}
