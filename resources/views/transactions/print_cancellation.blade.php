<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نوسراوی پووچەڵکردنەوە - {{ $transaction->barcode }}</title>
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

        .border-black-thick {
            border: 2px solid #000000;
        }
        .border-black-thin {
            border: 1px solid #000000;
        }
    </style>
</head>
<body class="p-4">

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="no-print mb-4 max-w-4xl mx-auto flex items-center justify-between bg-slate-900 text-white p-3.5 rounded-2xl shadow-xl">
        <div class="flex items-center space-x-3 space-x-reverse text-sm font-bold">
            <span class="bg-rose-500/20 text-rose-300 px-3.5 py-1 rounded-xl border border-rose-500/40 flex items-center">
                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                نوسراوی فەرمی پووچەڵکردنەوە
            </span>
            <span class="text-slate-300">بارکۆد: <strong class="font-mono text-amber-300 text-base">{{ $transaction->barcode }}</strong></span>
        </div>
        <div class="flex items-center space-x-3 space-x-reverse">
            <button onclick="window.print()" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-black text-sm rounded-xl shadow-lg transition flex items-center">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                چاپکردنی نوسراو (Print)
            </button>
            <button onclick="window.close()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-sm rounded-xl transition">
                داخستن
            </button>
        </div>
    </div>

    <!-- MAIN REPORT PRINT CARD -->
    <div class="print-card bg-white border-2 border-black p-6 rounded-md shadow-xl max-w-4xl mx-auto space-y-4 text-black">

        <!-- Emblem & Header Grid (Matching Image 1 exact header) -->
        <div class="border-2 border-black p-3 rounded-none relative">
            <div class="grid grid-cols-3 items-center text-center">
                
                <!-- Right Title: Kurdish -->
                <div class="text-right text-[13px] font-bold leading-tight">
                    <p class="font-black text-sm">حكومەتی هەرێمی كوردستان \عیراق</p>
                    <p>سەرۆكایەتی ئەنجومەنی وەزیران</p>
                    <p>وەزارەتی دارایی و ئابوری</p>
                    <p>بەڕێوەبەرایەتی گشتی گومرگ</p>
                    <p class="font-black">بەڕێوەبەرایەتی گومرگی سلیمانی</p>
                    <p>هۆبەی ئۆتۆمبیل</p>
                </div>

                <!-- Center Emblem & English Title -->
                <div class="flex flex-col items-center justify-center space-y-1">
                    <!-- Eagle Emblem -->
                    <div class="w-16 h-16 flex items-center justify-center">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/Coat_of_arms_of_Kurdistan_Region.svg/250px-Coat_of_arms_of_Kurdistan_Region.svg.png" 
                             alt="Kurdish Region Emblem" 
                             class="max-h-16 object-contain"
                             onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'><text y=\'50%\' x=\'50%\' text-anchor=\'middle\' font-size=\'40\'>🦅</text></svg>';">
                    </div>
                    <div class="text-[10px] font-bold text-center leading-tight">
                        <p class="font-extrabold text-[11px]">Kurdish Region</p>
                        <p>Presidency council of ministries</p>
                        <p>ministry of finance economy</p>
                        <p>General directorate customs</p>
                        <p class="font-bold">Department of (Automobiles)</p>
                    </div>
                </div>

                <!-- Left Title: Arabic -->
                <div class="text-left text-[13px] font-bold leading-tight" dir="rtl">
                    <p class="font-black text-sm">حكومة اقليم كردستان \العراق</p>
                    <p>رئاسة مجلس الوزراء</p>
                    <p>وزارة المالية والاقتصاد</p>
                    <p>المديرية العامة للكمارك</p>
                    <p class="font-black">مديرية كمرك السليمانية</p>
                    <p>قسم السيارات</p>
                </div>

            </div>
        </div>

        <!-- Date & Number Bar -->
        <div class="border-b-2 border-dashed border-black pb-2 flex items-center justify-between text-sm font-bold">
            <div>
                <span>ژمارە: </span>
                <span class="font-mono text-base">{{ $transaction->no_nusraw_puchal ?? $transaction->barcode }}</span>
            </div>
            <div>
                <span>ڕێکەوتی: </span>
                <span class="font-mono text-base">
                    {{ $transaction->date_nusraw_puchal ? $transaction->date_nusraw_puchal->format('Y/MM/d') : date('Y/m/d') }}
                </span>
            </div>
        </div>

        <!-- Title & Subject -->
        <div class="text-center space-y-1.5 pt-2">
            <h2 class="text-xl font-black">
                بۆ\ بەڕێوەبەرایەتی هاتووچۆی {{ $transaction->trafficDirectorate->name_kurdish ?? 'دهۆك' }}
            </h2>
            <h3 class="text-lg font-extrabold underline underline-offset-4">
                بابەت\ پووچەڵکردنەوە
            </h3>
        </div>

        <!-- Official Letter Body -->
        <div class="text-base font-bold leading-relaxed px-2 py-3 text-justify">
            @php
                $customsNo = $transaction->no_nusraw_puchal ?? '2020000';
                $customsDate = $transaction->date_nusraw_puchal ? $transaction->date_nusraw_puchal->format('Y-m-d') : ($transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : '2026-05-23');
            @endphp
            <p>
                دوابەدوای نوسراومان ژمارە ( <span class="font-mono text-lg font-black">{{ $customsNo }}</span> ) لە ( <span class="font-mono text-lg font-black">{{ $customsDate }}</span> ) نیشانەی ڕەفتارنەکردن هەڵبگرن لەسەر ئۆتۆمبێلی کە خەسڵەتەکانی لەخوارەوە دیاریکراوە و خاوەنەکەی سەردانی بەڕێوەبەرایەتی کردووە دەفتەری ڕێگەپێدانی کەچووچەڵکردۆتەوە ئازادە لەهەر کارێکی یاسایی ئەنجامی بدات بۆ ئۆتۆمبێلەکەی بۆ ئاگاداریتان .
            </p>
            <p class="text-center font-black mt-3">له‌گه‌ڵ ڕێزدا...</p>
        </div>

        <!-- Details Grid Table -->
        <div class="my-4">
            <table class="w-full border-2 border-black border-collapse text-sm font-bold text-center">
                <tbody>
                    <tr>
                        <td class="border-2 border-black p-2.5 bg-gray-100 w-1/3">به‌ناوی</td>
                        <td class="border-2 border-black p-2.5 text-base font-black w-2/3">{{ $transaction->visitor_name }}</td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black p-2.5 bg-gray-100">ژمارەی ئۆتۆمبێل</td>
                        <td class="border-2 border-black p-2.5 font-mono text-base font-black">{{ $transaction->plate_number }} ({{ $transaction->plateType->name_kurdish ?? '' }})</td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black p-2.5 bg-gray-100">جۆری ئۆتۆمبێل</td>
                        <td class="border-2 border-black p-2.5 text-base font-black">{{ $transaction->carMake->name_kurdish ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black p-2.5 bg-gray-100">ڕەنگ و مۆدێل</td>
                        <td class="border-2 border-black p-2.5 font-black">{{ $transaction->carColor->name_kurdish ?? 'سپى' }} - {{ $transaction->model_year }}</td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black p-2.5 bg-gray-100">ژمارەی شاسی</td>
                        <td class="border-2 border-black p-2.5 font-mono text-base font-black uppercase tracking-wider">{{ $transaction->chassis_number }}</td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black p-2.5 bg-gray-100">ژمارەی پەستۆن</td>
                        <td class="border-2 border-black p-2.5 font-black">{{ $transaction->piston_count ?? 8 }}</td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black p-2.5 bg-gray-100">ژمارەی سالانە</td>
                        <td class="border-2 border-black p-2.5 font-mono font-black text-base">{{ $transaction->salana_number ?? '158458' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Director Signature Block -->
        <div class="flex justify-end pt-4 pb-2 pl-8">
            <div class="text-center font-black space-y-1">
                <p class="text-lg">{{ $transaction->director->name ?? 'قادر پیرۆت قادر' }}</p>
                <p class="text-base">بەڕێوەبەری گومرگی سلیمانی</p>
                <p class="font-mono text-sm font-bold pt-1">{{ date('Y-m-d') }}</p>
            </div>
        </div>

        <!-- Copies to (وێنەیەک بۆ...) -->
        <div class="text-xs font-bold space-y-1.5 pt-4 border-t border-gray-300">
            <p class="font-black text-sm underline">وێنەیەک بۆ...</p>
            <p class="leading-relaxed">
                * هۆبەی یاسا بەڕێوەبەرایەتی گومرگی سلیمانی\تکایه بەپێی نامه بەڵێن نامه وەربگرن لە خاوەنی ئۆتۆمبێلی ئاماژەپێکراو بەمەبەستی گەڕانەوەی ئۆتۆمبێلە کەى لەماوەی دیاری کراودا ... لەگەڵ ڕێزدا
            </p>
            <p>* هۆبەی ئۆتۆمبێل</p>
            <p>* دۆسیەی گشتی</p>
        </div>

        <!-- Footer Barcode & KRG Address -->
        <div class="pt-4 flex items-end justify-between border-t-2 border-black">
            <div class="text-center space-y-1">
                <svg id="cancellationBarcodeSvg" class="max-h-14"></svg>
                <div class="flex items-center justify-between text-[11px] font-mono font-bold px-1">
                    <span>{{ date('Y-m-d') }}</span>
                    <span>#{{ $transaction->barcode }}</span>
                </div>
            </div>
            
            <div class="text-xs font-bold text-left">
                <p>حکومەتی هەرێمی کوردستان\ ناو نیشان سلیمانی ئەنیشت باخی بەختیاری</p>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            JsBarcode("#cancellationBarcodeSvg", "*{{ $transaction->barcode }}*", {
                format: "CODE128",
                lineColor: "#000",
                width: 1.8,
                height: 38,
                displayValue: true,
                fontSize: 11,
                fontOptions: "bold",
                font: "Vazirmatn",
                textMargin: 2
            });
        });
    </script>
</body>
</html>
