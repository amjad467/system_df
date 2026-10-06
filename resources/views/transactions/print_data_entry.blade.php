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
                margin: 6mm;
            }
            .print-card {
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 14px !important;
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
<body class="p-4">

    @php
        $linkedPledge = $transaction->relatedTransactions->first() ?? ($transaction->parentTransaction && $transaction->parentTransaction->transaction_type_id == 4 ? $transaction->parentTransaction : null);
    @endphp

    <!-- Top Action Bar (Hide in Print) -->
    <div class="no-print mb-4 max-w-4xl mx-auto flex items-center justify-between bg-slate-900 text-white p-3.5 rounded-2xl shadow-xl">
        <div class="flex items-center space-x-3 space-x-reverse text-sm font-bold">
            <span class="bg-blue-500/20 text-blue-300 px-3.5 py-1 rounded-xl border border-blue-500/40 flex items-center">
                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                فیشەی ڕاپۆرتی داتائەنتەری و بەدواداچوون
            </span>
            <span class="text-slate-300">بارکۆد: <strong class="font-mono text-amber-300 text-base">{{ $transaction->barcode }}</strong></span>
        </div>
        <div class="flex items-center space-x-3 space-x-reverse">
            @if($linkedPledge)
                <a href="{{ route('transactions.print_pledge', $linkedPledge->id) }}" target="_blank" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 text-white font-extrabold text-sm rounded-xl shadow-lg transition flex items-center">
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    چاپکردنی فیشەی بەڵێننامەی بەستراوە ({{ $linkedPledge->barcode }})
                </a>
            @endif
            <button onclick="window.print()" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-xl shadow-lg transition flex items-center">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                چاپکردنی فیشە (Print Routing Slip)
            </button>
            <button onclick="window.close()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-sm rounded-xl transition">
                داخستن
            </button>
        </div>
    </div>

    <!-- MAIN FORM PORTRAIT ROUTING CARD -->
    <div class="print-card bg-white border-2 border-black p-6 rounded-md shadow-xl max-w-4xl mx-auto space-y-4">

        <!-- Header Section -->
        <div class="border-b-2 border-black pb-3 flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-lg font-black text-black">حکومەتی هەرێمی کوردستان - عێراق</h1>
                <h2 class="text-sm font-bold text-gray-800">وەزارەتی دارایی و ئابووری / بەڕێوەبەرایەتی گشتی گومرگ</h2>
                <h3 class="text-base font-black text-black">بەڕێوەبەرایەتی گومرگی سلێمانی</h3>
            </div>
            
            <div class="text-center space-y-1">
                <h2 class="text-xl font-black text-black bg-gray-200 border-2 border-black px-4 py-1 inline-block">
                    فیشەی بەدواداچوونی مامەڵە
                </h2>
                <p class="text-xs font-bold text-gray-700 block">داتائەنتەری و ڕایی کردن</p>
            </div>

            <div class="text-center space-y-1">
                <div class="border-2 border-black p-1 bg-white inline-block">
                    <svg id="barcodeSvg" class="mx-auto max-h-14"></svg>
                </div>
                <p class="text-[11px] font-mono font-bold text-gray-600">بەرواری داخڵکردن: {{ $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i') : date('Y-m-d') }}</p>
            </div>
        </div>

        <!-- Linked Transactions Notice (If Booklet Issue created linked Pledge) -->
        @if($transaction->parentTransaction || $transaction->relatedTransactions->count() > 0)
            <div class="bg-blue-50 border-2 border-blue-600 p-2.5 rounded text-xs font-bold flex items-center justify-between">
                <div class="flex items-center space-x-2 space-x-reverse text-blue-950">
                    <span class="bg-blue-600 text-white px-2 py-0.5 rounded font-black">ئاگاداری بەستنەوە:</span>
                    <span>ئەم مامەڵەیە بەستراوەتەوە بە مامەڵەی بەڵێننامەی هاوتای بە بارکۆدی:</span>
                </div>
                <div class="flex items-center space-x-2 space-x-reverse font-mono text-sm">
                    @if($transaction->parentTransaction)
                        <span class="bg-white border border-blue-700 px-2 py-0.5 rounded font-black text-blue-900">{{ $transaction->parentTransaction->barcode }} (مامەڵەی بنەڕەتی)</span>
                    @endif
                    @foreach($transaction->relatedTransactions as $rel)
                        <span class="bg-white border border-blue-700 px-2 py-0.5 rounded font-black text-blue-900">{{ $rel->barcode }} (بەڵێننامەی بەستراوە)</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Section 1: Transaction & Vehicle Information Grid -->
        <div class="space-y-1.5">
            <h4 class="text-sm font-black border-b border-black pb-1 flex items-center">
                <span>زانیارییە سەرەکییەکانی مامەڵە و هاووڵاتی (داتائەنتەری)</span>
            </h4>
            <table class="w-full border-collapse text-xs font-bold">
                <tbody>
                    <tr>
                        <td class="black-cell-border gray-label-bg p-2 w-1/6">ناوی هاووڵاتی:</td>
                        <td class="black-cell-border p-2 font-black text-sm w-2/6">{{ $transaction->visitor_name }} @if(!$transaction->isCancellationOrInspection() && $transaction->visitor_name_eng) ({{ $transaction->visitor_name_eng }}) @endif</td>
                        <td class="black-cell-border gray-label-bg p-2 w-1/6">جۆری مامەڵە:</td>
                        <td class="black-cell-border p-2 font-black text-sm w-2/6 text-blue-900">{{ $transaction->transactionType->name_kurdish ?? '' }}</td>
                    </tr>
                    @if($transaction->isCancellation() || $transaction->no_nusraw_puchal)
                    <tr>
                        <td class="black-cell-border gray-label-bg p-2">ژمارەی نووسراوی گومرگ:</td>
                        <td class="black-cell-border p-2 font-mono font-black text-sm">{{ $transaction->no_nusraw_puchal ?? '-' }}</td>
                        <td class="black-cell-border gray-label-bg p-2">بەرواری نووسراوی گومرگ:</td>
                        <td class="black-cell-border p-2 font-mono font-bold">{{ $transaction->date_nusraw_puchal ? $transaction->date_nusraw_puchal->format('Y-m-d') : '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="black-cell-border gray-label-bg p-2">ژمارەی تابلۆ:</td>
                        <td class="black-cell-border p-2 font-mono font-black text-sm">{{ $transaction->plate_number }} ({{ $transaction->plateType->name_kurdish ?? 'تایبەت' }})</td>
                        <td class="black-cell-border gray-label-bg p-2">مارکە و مۆدێل:</td>
                        <td class="black-cell-border p-2 font-bold">{{ $transaction->carMake->name_kurdish ?? '' }} - {{ $transaction->model_year }} ({{ $transaction->piston_count }} Piston)</td>
                    </tr>
                    <tr>
                        <td class="black-cell-border gray-label-bg p-2">ژمارەی شاسی:</td>
                        <td class="black-cell-border p-2 font-mono font-black uppercase text-sm">{{ $transaction->chassis_number }}</td>
                        <td class="black-cell-border gray-label-bg p-2">ڕەنگی ئۆتۆمبێل:</td>
                        <td class="black-cell-border p-2 font-bold">{{ $transaction->carColor->name_kurdish ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="black-cell-border gray-label-bg p-2">ماوەی دەفتەر:</td>
                        <td class="black-cell-border p-2 font-black">{{ $transaction->isCancellationOrInspection() ? '-' : ($transaction->num_years ?? 1) . ' ساڵ' }}</td>
                        <td class="black-cell-border gray-label-bg p-2">خەمڵاندنی تێکڕای نرخ:</td>
                        <td class="black-cell-border p-2 font-mono font-black text-sm">{{ number_format($transaction->total_pay) }} دینار ({{ \App\Services\KurdishNumberToWords::convert($transaction->total_pay) }})</td>
                    </tr>
                    <tr>
                        <td class="black-cell-border gray-label-bg p-2">کارمەندی داخڵکار:</td>
                        <td class="black-cell-border p-2 font-bold" colspan="3">{{ $transaction->user_input }} (لە بەرواری: {{ $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i') : date('Y-m-d') }})</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 2: Physical Stage Checkpoints Grid for Subsequent Officers -->
        <div class="space-y-2 pt-2">
            <h4 class="text-sm font-black border-b border-black pb-1">
                مەحەل و ستێپەکانی ڕایی کردنی مامەڵە (مۆر و ئیمزای بەشەکان)
            </h4>

            <div class="grid grid-cols-2 gap-3 text-xs">
                
                <!-- Box 1: Inspection & Estimation Stage -->
                <div class="border-2 border-black p-3 space-y-2 rounded-sm bg-white">
                    <div class="flex items-center justify-between border-b border-black pb-1">
                        <span class="font-black text-sm">١. کەشف و تەخمین</span>
                        @if($transaction->is_inspected)
                            <span class="bg-emerald-100 text-emerald-900 border border-emerald-500 px-2 py-0.5 rounded font-black">پەسەندکراوە</span>
                        @else
                            <span class="bg-amber-100 text-amber-900 border border-amber-500 px-2 py-0.5 rounded font-bold">چاوەڕێی کەشفە</span>
                        @endif
                    </div>
                    <div class="space-y-1 font-bold text-gray-800">
                        <p>ئەندازیاری کەشف: <span class="font-black text-black">{{ $transaction->inspected_by ?? '............................' }}</span></p>
                        <p>بەروار: <span class="font-mono">{{ $transaction->inspected_at ? $transaction->inspected_at->format('Y-m-d H:i') : '............................' }}</span></p>
                        <div class="pt-4 flex items-center justify-between text-gray-500 text-[11px]">
                            <span>ئیمزای ئەندازیاری تەخمین:</span>
                            <span>............................</span>
                        </div>
                    </div>
                </div>

                <!-- Box 2: Audit Stage -->
                <div class="border-2 border-black p-3 space-y-2 rounded-sm bg-white">
                    <div class="flex items-center justify-between border-b border-black pb-1">
                        <span class="font-black text-sm">٢. وردبینی</span>
                        @if($transaction->is_audited)
                            <span class="bg-emerald-100 text-emerald-900 border border-emerald-500 px-2 py-0.5 rounded font-black">پەسەندکراوە</span>
                        @else
                            <span class="bg-amber-100 text-amber-900 border border-amber-500 px-2 py-0.5 rounded font-bold">چاوەڕێی وردبینییە</span>
                        @endif
                    </div>
                    <div class="space-y-1 font-bold text-gray-800">
                        <p>کارمەندی وردبین: <span class="font-black text-black">{{ $transaction->audited_by ?? '............................' }}</span></p>
                        <p>بەروار: <span class="font-mono">{{ $transaction->audited_at ? $transaction->audited_at->format('Y-m-d H:i') : '............................' }}</span></p>
                        <div class="pt-4 flex items-center justify-between text-gray-500 text-[11px]">
                            <span>ئیمزای وردبین:</span>
                            <span>............................</span>
                        </div>
                    </div>
                </div>

                <!-- Box 3: Payment & Receipt 37/A Stage -->
                <div class="border-2 border-black p-3 space-y-2 rounded-sm bg-white">
                    <div class="flex items-center justify-between border-b border-black pb-1">
                        <span class="font-black text-sm">٣. وەسڵ و پارەدان</span>
                        @if($transaction->is_paid)
                            <span class="bg-emerald-100 text-emerald-900 border border-emerald-500 px-2 py-0.5 rounded font-black">پارەدراوە</span>
                        @else
                            <span class="bg-amber-100 text-amber-900 border border-amber-500 px-2 py-0.5 rounded font-bold">چاوەڕێی پارەدانە</span>
                        @endif
                    </div>
                    <div class="space-y-1 font-bold text-gray-800">
                        <p>ژمارەی پسوولەی ۳۷/أ: <span class="font-mono font-black text-black">{{ $transaction->receipt_37a_number ?? '............................' }}</span></p>
                        <p>ژمێریار / وەسڵبڕ: <span class="font-black text-black">{{ $transaction->paid_by ?? '............................' }}</span></p>
                        <div class="pt-2 flex items-center justify-between text-gray-500 text-[11px]">
                            <span>ئیمزا و مۆری وەسڵبڕ:</span>
                            <span>............................</span>
                        </div>
                    </div>
                </div>

                <!-- Box 4: Booklet Completion Stage -->
                <div class="border-2 border-black p-3 space-y-2 rounded-sm bg-white">
                    <div class="flex items-center justify-between border-b border-black pb-1">
                        <span class="font-black text-sm">٤. چاپی دەفتەر</span>
                        @if(!$transaction->requiresBooklet())
                            <span class="bg-gray-200 text-gray-700 border border-gray-400 px-2 py-0.5 rounded font-bold">نیازی دەفتەر نییە</span>
                        @elseif($transaction->is_booklet_completed)
                            <span class="bg-emerald-100 text-emerald-900 border border-emerald-500 px-2 py-0.5 rounded font-black">تەواوکراوە</span>
                        @else
                            <span class="bg-amber-100 text-amber-900 border border-amber-500 px-2 py-0.5 rounded font-bold">چاوەڕێی دەفتەرە</span>
                        @endif
                    </div>
                    <div class="space-y-1 font-bold text-gray-800">
                        <p>ژمارەی سەر دەفتەر: <span class="font-mono font-black text-black">{{ $transaction->booklet_number ?? '............................' }}</span></p>
                        <p>کارمەندی دەفتەر: <span class="font-black text-black">{{ $transaction->booklet_by ?? '............................' }}</span></p>
                        <div class="pt-2 flex items-center justify-between text-gray-500 text-[11px]">
                            <span>ئیمزای لێپرسراوی دەفتەر:</span>
                            <span>............................</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer Notice -->
        <div class="pt-4 border-t border-black flex items-center justify-between text-[11px] font-bold text-gray-600">
            <span>سیستەمی ئەلیکترۆنی بەڕێوەبردنی ئۆتۆمبێلی گەشتیاری - بەڕێوەبەرایەتی گومرگی سلێمانی</span>
            <span class="font-mono">کاتی چاپ: {{ date('Y-m-d H:i:s') }}</span>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            JsBarcode("#barcodeSvg", "{{ $transaction->barcode }}", {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 42,
                displayValue: true,
                fontSize: 13,
                fontOptions: "bold",
                font: "Vazirmatn",
                textMargin: 2
            });
        });
    </script>
</body>
</html>
