<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پسوولەی وەرگرتنی پارەی ۳۷/أ - {{ $transaction->receipt_37a_number ?? $transaction->barcode }}</title>
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
            .no-print { display: none !important; }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 8mm;
            }
            .print-card {
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 16px !important;
                margin: 0 auto !important;
            }
        }
    </style>
</head>
<body class="p-4">

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="no-print mb-4 max-w-4xl mx-auto flex items-center justify-between bg-slate-900 text-white p-3.5 rounded-2xl shadow-xl">
        <div class="flex items-center space-x-3 space-x-reverse text-sm font-bold">
            <span class="bg-emerald-500/20 text-emerald-300 px-3.5 py-1 rounded-xl border border-emerald-500/40 flex items-center">
                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                پسوولەی وەرگرتنی پارەی فەرمی (۳۷/أ)
            </span>
            <span class="text-slate-300">ژمارەی پسوولە: <strong class="font-mono text-emerald-400 text-base">{{ $transaction->receipt_37a_number ?? '---' }}</strong></span>
        </div>
        <div class="flex items-center space-x-3 space-x-reverse">
            <button onclick="window.print()" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-sm rounded-xl shadow-lg transition flex items-center">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                چاپکردنی پسوولەی پارەدان (Print Receipt 37/A)
            </button>
            <button onclick="window.close()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-sm rounded-xl transition">
                داخستن
            </button>
        </div>
    </div>

    <!-- MAIN RECEIPT PRINT CARD -->
    <div class="print-card bg-white border-2 border-black p-6 rounded-md shadow-xl max-w-4xl mx-auto space-y-4 text-black">

        <!-- Header Section -->
        <div class="border-b-2 border-black pb-3 flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-lg font-black text-black">حکومەتی هەرێمی کوردستان - عێراق</h1>
                <h2 class="text-sm font-bold text-gray-800">وەزارەتی دارایی و ئابووری / بەڕێوەبەرایەتی گشتی گومرگ</h2>
                <h3 class="text-base font-black text-black">بەڕێوەبەرایەتی گومرگی سلێمانی - بەشی ژمێریاری</h3>
            </div>

            <div class="text-center space-y-1">
                <h2 class="text-xl font-black text-black bg-gray-200 border-2 border-black px-4 py-1 inline-block">
                    پسوولەی وەرگرتنی پارەی ۳۷/أ
                </h2>
                <p class="text-xs font-mono font-black text-red-700 block">ORIGINAL OFFICIAL RECEIPT</p>
            </div>

            <div class="text-center space-y-1">
                <div class="border-2 border-black p-1 bg-white inline-block">
                    <svg id="paymentBarcodeSvg" class="mx-auto max-h-12"></svg>
                </div>
                <p class="text-[11px] font-mono font-bold text-gray-700">ژمارەی پسوولە: <strong class="text-base text-black">{{ $transaction->receipt_37a_number }}</strong></p>
            </div>
        </div>

        <!-- Receipt Info Grid -->
        <table class="w-full border-2 border-black border-collapse text-sm font-bold">
            <tbody>
                <tr>
                    <td class="border border-black bg-gray-100 p-2 w-1/6">لە بەڕێز / وەربگیرا لە:</td>
                    <td class="border border-black p-2 text-base font-black w-2/6">{{ $transaction->visitor_name }}</td>
                    <td class="border border-black bg-gray-100 p-2 w-1/6">بەرواری پارەدان:</td>
                    <td class="border border-black p-2 font-mono text-sm w-2/6">{{ $transaction->paid_at ? $transaction->paid_at->format('Y-m-d H:i') : date('Y-m-d') }}</td>
                </tr>
                <tr>
                    <td class="border border-black bg-gray-100 p-2">جۆری مامەڵە:</td>
                    <td class="border border-black p-2 text-sm font-black">{{ $transaction->transactionType->name_kurdish ?? '' }}</td>
                    <td class="border border-black bg-gray-100 p-2">ژمارەی تابلۆ و شاسی:</td>
                    <td class="border border-black p-2 font-mono text-sm font-black">{{ $transaction->plate_number }} ({{ $transaction->chassis_number }})</td>
                </tr>
            </tbody>
        </table>

        <!-- Itemized Fee Breakdown Table -->
        <div class="space-y-1 pt-2">
            <h4 class="text-sm font-black border-b border-black pb-1">تەواوی بڕی ڕسووماتە وەرگیراوەکان:</h4>
            <table class="w-full border-2 border-black border-collapse text-sm font-bold text-center">
                <thead class="bg-gray-200 border-b-2 border-black">
                    <tr>
                        <th class="border border-black p-2 w-12">ژ</th>
                        <th class="border border-black p-2 text-right">جۆری ڕسوومات / خەرجی</th>
                        <th class="border border-black p-2 w-1/4">بڕی پارە (دیناری عێراقی)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-black p-2 font-mono">١</td>
                        <td class="border border-black p-2 text-right">مافی ڕێگا / بڕی سالانە ({{ $transaction->num_years }} ساڵ)</td>
                        <td class="border border-black p-2 font-mono text-base font-black">{{ number_format($transaction->pay_amount_years) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-mono">٢</td>
                        <td class="border border-black p-2 text-right">ڕسووماتی کەشف و خەمڵاندن (کەشفی ئۆتۆمبێل)</td>
                        <td class="border border-black p-2 font-mono text-base font-black">{{ number_format($transaction->pay_inspection) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-mono">٣</td>
                        <td class="border border-black p-2 text-right">ڕسووماتی فۆرم و داتائەنتەری</td>
                        <td class="border border-black p-2 font-mono text-base font-black">{{ number_format($transaction->pay_form) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-mono">٤</td>
                        <td class="border border-black p-2 text-right">پاشکۆ و پوولی بەڵێننامە (پوول)</td>
                        <td class="border border-black p-2 font-mono text-base font-black">{{ number_format($transaction->pay_stamp) }}</td>
                    </tr>
                    @if($transaction->pay_fine > 0)
                        <tr>
                            <td class="border border-black p-2 font-mono">٥</td>
                            <td class="border border-black p-2 text-right">غەرامەی دواکەوتن</td>
                            <td class="border border-black p-2 font-mono text-base font-black text-red-700">{{ number_format($transaction->pay_fine) }}</td>
                        </tr>
                    @endif
                    <tr class="bg-gray-100 font-black text-base">
                        <td colspan="2" class="border-2 border-black p-3 text-right">کۆی گشتی کۆکراوە (TOTAL):</td>
                        <td class="border-2 border-black p-3 font-mono text-lg text-emerald-900 bg-emerald-50">{{ number_format($transaction->total_pay) }} د.ع</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Cashier Signatures & Stamp -->
        <div class="grid grid-cols-2 text-center text-xs font-bold pt-8 border-t-2 border-black">
            <div>
                <p class="font-extrabold text-sm">ناوی ژمێریاری وەسڵبڕ</p>
                <p class="mt-4 text-sm font-black text-gray-900">{{ $transaction->paid_by ?? auth()->user()->name }}</p>
                <p class="text-[11px] text-gray-600 font-mono mt-1">بەشی ژمێریاری و وەسڵبڕین</p>
            </div>
            <div>
                <p class="font-extrabold text-sm">مۆری فەرمی ژمێریاری گومرگ</p>
                <div class="w-20 h-20 border-2 border-dashed border-gray-400 rounded-full mx-auto mt-2 flex items-center justify-center text-[10px] text-gray-400">
                    مۆری وەسڵبڕ
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="text-[11px] font-bold text-center border-t border-gray-300 pt-3">
            <p>تێبینی: ئەم پسوولەیە بەڵگەی فەرمی وەرگرتنی پارەیە لە لایەن بەڕێوەبەرایەتی گومرگی سلێمانی / ناو نیشان: ئەنیشت باخی بەختیاری.</p>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            JsBarcode("#paymentBarcodeSvg", "{{ $transaction->barcode }}", {
                format: "CODE128",
                lineColor: "#000",
                width: 1.8,
                height: 32,
                displayValue: true,
                fontSize: 10,
                fontOptions: "bold",
                font: "Vazirmatn",
                textMargin: 1
            });
        });
    </script>
</body>
</html>
