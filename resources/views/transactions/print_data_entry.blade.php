@php
    $linked = $transaction->getLinkedPledgeOrParent();
    if ($linked && !$linked->relationLoaded('transactionType')) {
        $linked->load(['transactionType', 'trafficDirectorate', 'plateType', 'carMake', 'carColor']);
    }

    if ($linked) {
        if ((int)$transaction->transaction_type_id === 6) {
            $pledgeTx = $transaction;
            $bookletTx = $linked;
        } else {
            $bookletTx = $transaction;
            $pledgeTx = $linked;
        }
    } else {
        $bookletTx = $transaction;
        $pledgeTx = null;
    }

    $mainTx = $bookletTx ?? $transaction;
@endphp
<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فیشەی بەدواداچوونی داتائەنتەری - {{ $transaction->barcode }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f8fafc;
            color: #000000;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 6mm 8mm;
            }
            .print-card {
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 12px 16px !important;
                margin: 0 auto !important;
            }
        }

        .header-bg {
            background-color: #f1f5f9 !important;
        }
        .gray-label-bg {
            background-color: #f8fafc !important;
        }
        .black-cell-border {
            border: 1.5px solid #000000 !important;
        }
    </style>
</head>
<body class="p-3 md:p-6 bg-slate-100">

    <!-- Top Action Bar (Hide in Print) -->
    <div class="no-print mb-4 max-w-4xl mx-auto flex items-center justify-between bg-slate-900 text-white p-3.5 rounded-2xl shadow-xl flex-wrap gap-3">
        <div class="flex items-center space-x-3 space-x-reverse text-sm font-bold">
            <span class="bg-blue-500/20 text-blue-300 px-3.5 py-1 rounded-xl border border-blue-500/40 flex items-center">
                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                فیشەی یەکگرتووی داتائەنتەری (چاپکردنی ١ پەڕە)
            </span>
            @if($pledgeTx)
                <span class="text-xs bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 px-2.5 py-0.5 rounded-lg">
                    دەفتەر + بەڵێننامە (دوو بارکۆد)
                </span>
            @endif
        </div>

        <div class="flex items-center space-x-3 space-x-reverse">
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-xl shadow-lg transition flex items-center">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                چاپکردنی فیشە (Print)
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-800 hover:bg-slate-750 text-slate-300 font-bold text-sm rounded-xl transition">
                داخستن
            </button>
        </div>
    </div>

    <!-- UNIFIED SINGLE-PAGE DATA ENTRY SLIP -->
    <div class="print-card bg-white border-2 border-black p-5 rounded-md shadow-xl max-w-4xl mx-auto space-y-3">

        <!-- Header Section -->
        <div class="border-b-2 border-black pb-2.5 flex items-center justify-between">
            <div class="space-y-0.5">
                <h1 class="text-base font-black text-black">حکومەتی هەرێمی کوردستان - عێراق</h1>
                <h2 class="text-xs font-bold text-gray-800">وەزارەتی دارایی و ئابووری / بەڕێوەبەرایەتی گشتی گومرگ</h2>
                <h3 class="text-sm font-black text-black">بەڕێوەبەرایەتی گومرگی سلێمانی</h3>
            </div>
            
            <div class="text-center space-y-0.5">
                <h2 class="text-lg font-black text-black bg-gray-200 border-2 border-black px-4 py-1 inline-block">
                    فیشەی بەدواداچوونی مامەڵە
                </h2>
                <p class="text-[11px] font-bold text-gray-700 block">
                    @if($pledgeTx)
                        دەرهێنانی دەفتەری گەشتیاری و بەڵێننامەی یاسایی
                    @else
                        {{ $transaction->transactionType->name_kurdish ?? 'داتائەنتەری و ڕایی کردن' }}
                    @endif
                </p>
            </div>

            <div class="text-left space-y-0.5 text-xs font-bold text-gray-700">
                <p>بەروار: <span class="font-mono text-black">{{ $mainTx->created_at ? $mainTx->created_at->format('Y-m-d') : date('Y-m-d') }}</span></p>
                <p>کات: <span class="font-mono text-black">{{ $mainTx->created_at ? $mainTx->created_at->format('H:i') : date('H:i') }}</span></p>
                <p class="text-[10px] text-gray-500 font-mono">ID: #{{ $mainTx->id }}</p>
            </div>
        </div>

        <!-- BARCODES SECTION: Side-by-side barcodes with titles above each -->
        <div class="border-2 border-black p-2.5 bg-slate-50 rounded-sm">
            @if($pledgeTx)
                <div class="grid grid-cols-2 gap-3 text-center">
                    <!-- Barcode 1: Booklet Issue -->
                    <div class="bg-white border-2 border-black p-2 rounded flex flex-col items-center justify-between">
                        <div class="w-full bg-blue-700 text-white text-xs font-black py-1 px-2 rounded mb-1.5 flex items-center justify-center gap-1">
                            <span>جۆری مامەڵە:</span>
                            <span class="text-amber-200 font-black">دەرهێنانی دەفتەر</span>
                        </div>
                        <svg id="barcodeSvgBooklet" class="max-h-12 w-full"></svg>
                    </div>

                    <!-- Barcode 2: Legal Pledge -->
                    <div class="bg-white border-2 border-black p-2 rounded flex flex-col items-center justify-between">
                        <div class="w-full bg-amber-600 text-white text-xs font-black py-1 px-2 rounded mb-1.5 flex items-center justify-center gap-1">
                            <span>جۆری مامەڵە:</span>
                            <span class="text-white font-black">بەڵێننامەی یاسایی</span>
                        </div>
                        <svg id="barcodeSvgPledge" class="max-h-12 w-full"></svg>
                    </div>
                </div>
            @else
                <!-- Single Barcode for Standalone Transaction -->
                <div class="max-w-md mx-auto bg-white border-2 border-black p-2 rounded text-center">
                    <div class="bg-slate-900 text-white text-xs font-black py-1 px-3 rounded mb-1.5 inline-block">
                        جۆری مامەڵە: {{ $transaction->transactionType->name_kurdish ?? 'مامەڵەی گومرگی' }}
                    </div>
                    <svg id="barcodeSvgMain" class="max-h-12 mx-auto"></svg>
                </div>
            @endif
        </div>

        <!-- Section 1: Citizen and Vehicle Data Table -->
        <div class="space-y-1">
            <h4 class="text-xs font-black border-b border-black pb-0.5 flex items-center justify-between">
                <span>زانیارییە سەرەکییەکانی هاووڵاتی و ئۆتۆمبێل (داتائەنتەری)</span>
                <span class="text-[11px] font-normal text-gray-600">داخڵکار: <strong class="text-black">{{ $mainTx->user_input }}</strong></span>
            </h4>
            <table class="w-full border-collapse text-xs font-bold">
                <tbody>
                    <tr>
                        <td class="black-cell-border gray-label-bg p-1.5 w-1/6">ناوی هاووڵاتی:</td>
                        <td class="black-cell-border p-1.5 font-black text-sm w-2/6">
                            {{ $mainTx->visitor_name }}
                            @if($mainTx->visitor_name_eng)
                                <span class="text-[11px] font-mono font-normal text-gray-700 block">({{ $mainTx->visitor_name_eng }})</span>
                            @endif
                            @if($mainTx->phone_number)
                                <span class="text-[11px] font-mono font-semibold text-gray-800 block">مۆبایل: {{ $mainTx->phone_number }}</span>
                            @endif
                        </td>
                        <td class="black-cell-border gray-label-bg p-1.5 w-1/6">جۆری مامەڵە:</td>
                        <td class="black-cell-border p-1.5 font-black text-xs w-2/6 text-blue-900">
                            @if($pledgeTx)
                                دەرهێنانی دەفتەری گەشتیاری + بەڵێننامەی یاسایی
                            @else
                                {{ $mainTx->transactionType->name_kurdish ?? '' }}
                            @endif
                            @if($mainTx->second_driver_name)
                                <div class="text-[11px] font-normal text-black mt-0.5">شۆفێری دووەم: {{ $mainTx->second_driver_name }} @if($mainTx->second_driver_name_eng) ({{ $mainTx->second_driver_name_eng }}) @endif</div>
                            @endif
                        </td>
                    </tr>
                    @if($mainTx->isCancellation())
                    <tr>
                        <td class="black-cell-border gray-label-bg p-1.5">ژمارەی نووسراوی گومرگ:</td>
                        <td class="black-cell-border p-1.5 font-mono font-black text-sm">{{ $mainTx->no_nusraw_puchal ?? '-' }}</td>
                        <td class="black-cell-border gray-label-bg p-1.5">بەرواری نووسراوی گومرگ:</td>
                        <td class="black-cell-border p-1.5 font-mono font-bold">{{ $mainTx->date_nusraw_puchal ? $mainTx->date_nusraw_puchal->format('Y-m-d') : '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="black-cell-border gray-label-bg p-1.5">ژمارەی تابلۆ:</td>
                        <td class="black-cell-border p-1.5 font-mono font-black text-base">
                            {{ $mainTx->plate_number }}
                        </td>
                        <td class="black-cell-border gray-label-bg p-1.5">مارکە و مۆدێل:</td>
                        <td class="black-cell-border p-1.5 font-bold">
                            {{ $mainTx->carMake->name_kurdish ?? '' }} - {{ $mainTx->model_year }}
                            @if($mainTx->piston_count)
                                <span class="font-mono text-xs">({{ $mainTx->piston_count }} Piston)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="black-cell-border gray-label-bg p-1.5">ژمارەی شاسی:</td>
                        <td class="black-cell-border p-1.5 font-mono font-black uppercase text-sm text-slate-900 tracking-wider">
                            {{ $mainTx->chassis_number }}
                        </td>
                        <td class="black-cell-border gray-label-bg p-1.5">ڕەنگی ئۆتۆمبێل:</td>
                        <td class="black-cell-border p-1.5 font-bold">{{ $mainTx->carColor->name_kurdish ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="black-cell-border gray-label-bg p-1.5">ماوەی دەفتەر:</td>
                        <td class="black-cell-border p-1.5 font-black">
                            {{ $mainTx->isCancellationOrInspection() ? '-' : ($mainTx->num_years ?? 1) . ' ساڵ' }}
                        </td>
                        <td class="black-cell-border gray-label-bg p-1.5">بڕی رسومات و خەرجی:</td>
                        <td class="black-cell-border p-1.5 font-mono font-black text-xs">
                            @if($pledgeTx)
                                <div class="flex items-center justify-between text-[11px]">
                                    <span>دەفتەر: {{ number_format($bookletTx->total_pay) }} د.ع</span>
                                    <span>بەڵێننامە: {{ number_format($pledgeTx->total_pay) }} د.ع</span>
                                </div>
                                <div class="text-sm font-black text-blue-900 pt-0.5 border-t border-gray-300 mt-0.5">
                                    کۆی گشتی: {{ number_format($bookletTx->total_pay + $pledgeTx->total_pay) }} دینار
                                </div>
                            @else
                                <span class="text-sm text-blue-900">{{ number_format($mainTx->total_pay) }} دینار</span>
                                <span class="text-[11px] font-sans font-bold text-gray-600 block">({{ \App\Services\KurdishNumberToWords::convert($mainTx->total_pay) }})</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($mainTx->is_booklet_lost || !empty($mainTx->lost_letter_number))
            <div class="border-2 border-black p-2 my-1.5 bg-amber-50 text-xs font-black text-black">
                <span class="underline">تێبینی ونبوونی دەفتەر:</span>
                <span>ئاماژە بە ونبوونی دەفتەری گومرگی ئۆتۆمبێلی ناوبراو، پاڵپشت بە نووسراوی {{ $mainTx->lost_directorate_name ?: 'بەڕێوەبەرایەتی ونبووەکان' }} ژمارە ( <strong class="font-mono">{{ $mainTx->lost_letter_number }}</strong> ) لە ڕێکەوتی ( <strong class="font-mono">{{ $mainTx->lost_letter_date ? $mainTx->lost_letter_date->format('Y-m-d') : '' }}</strong> ).</span>
            </div>
        @endif

        <!-- Section 2: Physical Stage Checkpoints Grid -->
        <div class="space-y-1 pt-1">
            <h4 class="text-xs font-black border-b border-black pb-0.5">
                مەحەل و ستێپەکانی ڕایی کردنی مامەڵە (مۆر و ئیمزای بەشەکان)
            </h4>

            <div class="grid grid-cols-4 gap-2 text-xs">
                
                <!-- Box 1: Inspection & Estimation Stage -->
                <div class="border-2 border-black p-2 space-y-1.5 rounded-sm bg-white flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-black pb-1">
                            <span class="font-black text-xs">١. کەشف و تەخمین</span>
                            @if($mainTx->is_inspected)
                                <span class="bg-emerald-100 text-emerald-900 border border-emerald-500 px-1.5 py-0.2 rounded text-[10px] font-black">پەسەندکرا</span>
                            @else
                                <span class="bg-amber-100 text-amber-900 border border-amber-500 px-1.5 py-0.2 rounded text-[10px] font-bold">چاوەڕێیە</span>
                            @endif
                        </div>
                        <div class="space-y-0.5 font-bold text-gray-800 text-[11px] mt-1">
                            <p>ئەندازیار: <span class="font-black text-black">{{ $mainTx->inspected_by ?? '..................' }}</span></p>
                            <p>بەروار: <span class="font-mono text-[10px]">{{ $mainTx->inspected_at ? $mainTx->inspected_at->format('Y-m-d') : '..................' }}</span></p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-dashed border-gray-400 text-gray-500 text-[10px] text-center">
                        ئیمزای ئەندازیاری کەشف
                    </div>
                </div>

                <!-- Box 2: Audit Stage -->
                <div class="border-2 border-black p-2 space-y-1.5 rounded-sm bg-white flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-black pb-1">
                            <span class="font-black text-xs">٢. وردبینی</span>
                            @if($mainTx->is_audited)
                                <span class="bg-emerald-100 text-emerald-900 border border-emerald-500 px-1.5 py-0.2 rounded text-[10px] font-black">پەسەندکرا</span>
                            @else
                                <span class="bg-amber-100 text-amber-900 border border-amber-500 px-1.5 py-0.2 rounded text-[10px] font-bold">چاوەڕێیە</span>
                            @endif
                        </div>
                        <div class="space-y-0.5 font-bold text-gray-800 text-[11px] mt-1">
                            <p>وردبین: <span class="font-black text-black">{{ $mainTx->audited_by ?? '..................' }}</span></p>
                            <p>بەروار: <span class="font-mono text-[10px]">{{ $mainTx->audited_at ? $mainTx->audited_at->format('Y-m-d') : '..................' }}</span></p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-dashed border-gray-400 text-gray-500 text-[10px] text-center">
                        ئیمزای وردبین
                    </div>
                </div>

                <!-- Box 3: Payment & Receipt 37/A Stage -->
                <div class="border-2 border-black p-2 space-y-1.5 rounded-sm bg-white flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-black pb-1">
                            <span class="font-black text-xs">٣. وەسڵ و پارەدان</span>
                            @if($mainTx->is_paid)
                                <span class="bg-emerald-100 text-emerald-900 border border-emerald-500 px-1.5 py-0.2 rounded text-[10px] font-black">پارەدرا</span>
                            @else
                                <span class="bg-amber-100 text-amber-900 border border-amber-500 px-1.5 py-0.2 rounded text-[10px] font-bold">چاوەڕێیە</span>
                            @endif
                        </div>
                        <div class="space-y-0.5 font-bold text-gray-800 text-[11px] mt-1">
                            <p>پسوولە: <span class="font-mono font-black text-black">{{ $mainTx->receipt_37a_number ?? '..................' }}</span></p>
                            <p>وەسڵبڕ: <span class="font-black text-black">{{ $mainTx->paid_by ?? '..................' }}</span></p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-dashed border-gray-400 text-gray-500 text-[10px] text-center">
                        ئیمزا و مۆری خەزنە
                    </div>
                </div>

                <!-- Box 4: Booklet Completion Stage -->
                <div class="border-2 border-black p-2 space-y-1.5 rounded-sm bg-white flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-black pb-1">
                            <span class="font-black text-xs">٤. چاپی دەفتەر</span>
                            @if(!$mainTx->requiresBooklet())
                                <span class="bg-gray-200 text-gray-700 border border-gray-400 px-1.5 py-0.2 rounded text-[10px] font-bold">پێویست نییە</span>
                            @elseif($mainTx->is_booklet_completed)
                                <span class="bg-emerald-100 text-emerald-900 border border-emerald-500 px-1.5 py-0.2 rounded text-[10px] font-black">تەواوکرا</span>
                            @else
                                <span class="bg-amber-100 text-amber-900 border border-amber-500 px-1.5 py-0.2 rounded text-[10px] font-bold">چاوەڕێیە</span>
                            @endif
                        </div>
                        <div class="space-y-0.5 font-bold text-gray-800 text-[11px] mt-1">
                            <p>ژمارەی دەفتەر: <span class="font-mono font-black text-black">{{ $mainTx->booklet_number ?? '..................' }}</span></p>
                            <p>کارمەند: <span class="font-black text-black">{{ $mainTx->booklet_by ?? '..................' }}</span></p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-dashed border-gray-400 text-gray-500 text-[10px] text-center">
                        ئیمزای لێپرسراوی دەفتەر
                    </div>
                </div>

            </div>
        </div>

        <!-- Bottom Committee Signatures (لیژنە: ئەندام - ئەندام - سەرۆکی لیژنە) -->
        <div class="border-t-2 border-black pt-2.5 my-2">
            <div class="grid grid-cols-3 gap-6 text-center">
                <!-- Right: ئەندام -->
                <div class="space-y-1 flex flex-col items-center">
                    <p class="text-xs font-black text-gray-900">ئەندام</p>
                    <div class="h-6 flex items-center justify-center">
                        <!-- بۆشایی واژۆ -->
                    </div>
                    <p class="text-xs font-mono font-bold text-gray-800" dir="ltr">
                        <span>{{ date('Y') }}</span>&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                </div>

                <!-- Middle: ئەندام -->
                <div class="space-y-1 flex flex-col items-center">
                    <p class="text-xs font-black text-gray-900">ئەندام</p>
                    <div class="h-6 flex items-center justify-center">
                        <!-- بۆشایی واژۆ -->
                    </div>
                    <p class="text-xs font-mono font-bold text-gray-800" dir="ltr">
                        <span>{{ date('Y') }}</span>&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                </div>

                <!-- Left: سەرۆکی لیژنە -->
                <div class="space-y-1 flex flex-col items-center">
                    <p class="text-xs font-black text-gray-900">سەرۆکی لیژنە</p>
                    <div class="h-6 flex items-center justify-center">
                        <!-- بۆشایی واژۆ -->
                    </div>
                    <p class="text-xs font-mono font-bold text-gray-800" dir="ltr">
                        <span>{{ date('Y') }}</span>&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="pt-2 border-t border-black flex items-center justify-between text-[10px] font-bold text-gray-600">
            <span>سیستەمی ئەلیکترۆنی بەڕێوەبردنی دەفتەری ئۆتۆمبێلی گەشتیاری - بەڕێوەبەرایەتی گومرگی سلێمانی</span>
            <span class="font-mono">کاتی چاپ: {{ date('Y-m-d H:i:s') }}</span>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            @if($pledgeTx)
                if (document.querySelector("#barcodeSvgBooklet")) {
                    JsBarcode("#barcodeSvgBooklet", "{{ $bookletTx->barcode }}", {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 2,
                        height: 38,
                        displayValue: true,
                        fontSize: 12,
                        fontOptions: "bold",
                        font: "Vazirmatn",
                        textMargin: 2
                    });
                }
                if (document.querySelector("#barcodeSvgPledge")) {
                    JsBarcode("#barcodeSvgPledge", "{{ $pledgeTx->barcode }}", {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 2,
                        height: 38,
                        displayValue: true,
                        fontSize: 12,
                        fontOptions: "bold",
                        font: "Vazirmatn",
                        textMargin: 2
                    });
                }
            @else
                if (document.querySelector("#barcodeSvgMain")) {
                    JsBarcode("#barcodeSvgMain", "{{ $transaction->barcode }}", {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 2,
                        height: 40,
                        displayValue: true,
                        fontSize: 13,
                        fontOptions: "bold",
                        font: "Vazirmatn",
                        textMargin: 2
                    });
                }
            @endif
        });
    </script>
</body>
</html>
