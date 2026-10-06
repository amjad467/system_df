<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فیشەی بەڵێننامەی یاسایی - {{ $transaction->barcode }}</title>
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
        $linkedBooklet = $transaction->parentTransaction ?? $transaction->relatedTransactions->first();
    @endphp

    <!-- Top Action Bar (Hide in Print) -->
    <div class="no-print mb-4 max-w-4xl mx-auto flex items-center justify-between bg-slate-900 text-white p-3.5 rounded-2xl shadow-xl">
        <div class="flex items-center space-x-3 space-x-reverse text-sm font-bold">
            <span class="bg-amber-500/20 text-amber-300 px-3.5 py-1 rounded-xl border border-amber-500/40 flex items-center">
                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                فیشەی فەرمی بەڵێننامەی یاسایی
            </span>
            <span class="text-slate-300">بارکۆد: <strong class="font-mono text-amber-300 text-base">{{ $transaction->barcode }}</strong></span>
        </div>
        <div class="flex items-center space-x-3 space-x-reverse">
            @if($linkedBooklet)
                <a href="{{ route('transactions.print_data_entry', $linkedBooklet->id) }}" target="_blank" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-sm rounded-xl shadow-lg transition flex items-center">
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    چاپکردنی فیشەی دەفتەر ({{ $linkedBooklet->barcode }})
                </a>
            @endif
            <button onclick="window.print()" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-500 text-white font-black text-sm rounded-xl shadow-lg transition flex items-center">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                چاپکردنی بەڵێننامە (Print Pledge)
            </button>
            <button onclick="window.close()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-sm rounded-xl transition">
                داخستن
            </button>
        </div>
    </div>

    <!-- MAIN FORM PORTRAIT ROUTING CARD (Exact same frame & standard format as print_data_entry) -->
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
                    فیشەی بەڵێننامەی یاسایی
                </h2>
                <p class="text-xs font-bold text-gray-700 block">بەشی یاسایی و بەڵێننامەکان</p>
            </div>

            <div class="text-center space-y-1">
                <div class="border-2 border-black p-1 bg-white inline-block">
                    <svg id="barcodePledgeSvg" class="mx-auto max-h-14"></svg>
                </div>
                <p class="text-[11px] font-mono font-bold text-gray-600">بەرواری پێشکەشکردن: {{ $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d H:i') : date('Y-m-d') }}</p>
            </div>
        </div>

        <!-- Linked Transactions Notice -->
        @if($transaction->parentTransaction || $transaction->relatedTransactions->count() > 0)
            <div class="bg-blue-50 border-2 border-blue-600 p-2.5 rounded text-xs font-bold flex items-center justify-between">
                <div class="flex items-center space-x-2 space-x-reverse text-blue-950">
                    <span class="bg-blue-600 text-white px-2 py-0.5 rounded font-black">ئاگاداری بەستنەوە:</span>
                    <span>ئەم بەڵێننامەیە بەستراوەتەوە بە مامەڵەی بنەڕەتی (دەرهێنانی دەفتەر) بە بارکۆدی:</span>
                </div>
                <div class="flex items-center space-x-2 space-x-reverse font-mono text-sm">
                    @if($transaction->parentTransaction)
                        <span class="bg-white border border-blue-700 px-2 py-0.5 rounded font-black text-blue-900">{{ $transaction->parentTransaction->barcode }} (مامەڵەی بنەڕەتی)</span>
                    @endif
                    @foreach($transaction->relatedTransactions as $rel)
                        <span class="bg-white border border-blue-700 px-2 py-0.5 rounded font-black text-blue-900">{{ $rel->barcode }} (مامەڵەی بەستراوە)</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Section 1: Transaction & Driver Information Grid -->
        <div class="space-y-1.5">
            <h4 class="text-sm font-black border-b border-black pb-1 flex items-center">
                <span>زانیارییە سەرەکییەکانی بەڵێندەر و ئۆتۆمبێل</span>
            </h4>
            <table class="w-full border-collapse text-xs font-bold">
                <tbody>
                    <tr>
                        <td class="black-cell-border gray-label-bg p-2 w-1/6">ناوی بەڵێندەر (شۆفێر):</td>
                        <td class="black-cell-border p-2 font-black text-sm w-2/6">{{ $transaction->visitor_name }} @if($transaction->visitor_name_eng) ({{ $transaction->visitor_name_eng }}) @endif</td>
                        <td class="black-cell-border gray-label-bg p-2 w-1/6">جۆری مامەڵە:</td>
                        <td class="black-cell-border p-2 font-black text-sm w-2/6 text-amber-900">{{ $transaction->transactionType->name_kurdish ?? 'بەڵێننامە' }}</td>
                    </tr>
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
                        <td class="black-cell-border gray-label-bg p-2">شۆفێری دووەم:</td>
                        <td class="black-cell-border p-2 font-bold">{{ $transaction->second_driver_name ?? 'دیاری نەکراوە' }}</td>
                        <td class="black-cell-border gray-label-bg p-2">کارمەندی داخڵکار:</td>
                        <td class="black-cell-border p-2 font-bold">{{ $transaction->user_input }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 2: Official Legal Pledge Declaration Box -->
        <div class="border-2 border-black p-4 rounded-sm bg-gray-50/70 space-y-2">
            <h4 class="text-sm font-black border-b border-black pb-1 text-center text-black">
                دەقی بەڵێننامەی یاسایی و پابەندبوونی فەرمی
            </h4>
            <div class="text-xs leading-relaxed font-bold text-gray-900 space-y-2 text-justify">
                <p>
                    من کە ئیمزا و پەنجەمۆرم لە خوارەوە کراوە، (<strong>{{ $transaction->visitor_name }}</strong>) خاوەن / شۆفێری ئۆتۆمبێلی ژمارە (<strong>{{ $transaction->plate_number }}</strong>) شاسی (<strong>{{ $transaction->chassis_number }}</strong>)، بەڵێننامەی یاسایی دەدەم بە بەڕێوەبەرایەتی گومرگی سلێمانی بەوەی کە:
                </p>
                <ul class="list-decimal list-inside space-y-1 text-gray-800 pr-2">
                    <li>تەنها بۆ مەبەستی گەشتیاری و کاتی ئۆتۆمبێلەکەم دەبەمە دەرەوەی خاکی هەرێمی کوردستان / عێراق.</li>
                    <li>بەڵێن دەدەم لە ماوەی ڕێگەپێدراوی دیاریکراوی دەفتەرەکەدا ئۆتۆمبێلەکە بگەڕێنمەوە بۆ ناو خاکی هەرێمی کوردستان.</li>
                    <li>پابەندی تەواوی ڕێنماییە یاساییەکانی وەزارەتی دارایی و بەڕێوەبەرایەتی گومرگی سلێمانی دەبم، و بە پێچەوانەوە بەرپرسیارێتی تەواوی یاسایی و مادی دەگرمە ئەستۆ.</li>
                </ul>
            </div>
        </div>

        <!-- Section 3: Financial Receipt Breakdown Table -->
        <div class="space-y-1.5">
            <h4 class="text-sm font-black border-b border-black pb-1 flex items-center justify-between">
                <span>تەفسیلی ڕسوومات و کرێی بەڵێننامە</span>
                <span class="text-xs font-mono">کۆی وەرگیراو: {{ number_format($transaction->total_pay) }} د.ع</span>
            </h4>
            <table class="w-full border-collapse text-xs font-bold text-center">
                <thead class="gray-label-bg">
                    <tr>
                        <th class="black-cell-border p-1.5">کرێی پول (Stamp)</th>
                        <th class="black-cell-border p-1.5">کرێی فۆڕم (Form)</th>
                        <th class="black-cell-border p-1.5">سزای دواکەوتن</th>
                        <th class="black-cell-border p-1.5">کۆی گشتی ڕسوومات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="black-cell-border p-2 font-mono">{{ number_format($transaction->pay_stamp) }} د.ع</td>
                        <td class="black-cell-border p-2 font-mono">{{ number_format($transaction->pay_form) }} د.ع</td>
                        <td class="black-cell-border p-2 font-mono text-amber-900">{{ number_format($transaction->pay_fine) }} د.ع</td>
                        <td class="black-cell-border p-2 font-mono font-black text-sm bg-gray-100">{{ number_format($transaction->total_pay) }} دینار ({{ \App\Services\KurdishNumberToWords::convert($transaction->total_pay) }})</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 4: Official Signatures & Fingerprint Grid -->
        <div class="pt-4 border-t-2 border-black">
            <div class="grid grid-cols-2 gap-4 text-xs font-bold text-center">
                
                <!-- Box 1: Driver Fingerprint & Signature -->
                <div class="border-2 border-black p-3 space-y-2 rounded-sm bg-white">
                    <p class="font-black text-sm text-black">١. ئیمزا و پەنجەمۆری بەڵێندەر (شۆفێر)</p>
                    <div class="w-24 h-24 border-2 border-dashed border-gray-400 mx-auto my-2 flex flex-col items-center justify-center text-[10px] text-gray-500 bg-gray-50">
                        <span>پەنجەمۆری</span>
                        <span>شۆفێر</span>
                    </div>
                    <p class="text-black font-black">{{ $transaction->visitor_name }}</p>
                    <p class="text-[11px] text-gray-600 font-mono">بەروار: {{ date('Y-m-d') }}</p>
                </div>

                <!-- Box 2: Legal Officer Signature & Stamp -->
                <div class="border-2 border-black p-3 space-y-2 rounded-sm bg-white flex flex-col justify-between">
                    <div>
                        <p class="font-black text-sm text-black">٢. لێپرسراوی بەشی یاسایی و بەڵێننامەکان</p>
                        <p class="text-xs text-gray-700 mt-2">پەسەندکرانی بەڵێننامە و ئیمزای یاسایی</p>
                    </div>
                    <div class="py-6 border-t border-gray-300">
                        <p class="text-gray-500 text-[11px]">ئیمزا و مۆری فەرمی</p>
                        <p class="text-[11px] text-gray-400 mt-4">........................................</p>
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
            JsBarcode("#barcodePledgeSvg", "{{ $transaction->barcode }}", {
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
