<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>دەفتەری ئۆتۆمبێلی گەشتیاری - {{ $transaction->booklet_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; background: #f8fafc; color: #0f172a; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body class="p-8 max-w-4xl mx-auto">

    <div class="no-print mb-6 flex justify-end">
        <button onclick="window.print()" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg flex items-center">
            چاپکردنی دەفتەر (Print Booklet)
        </button>
    </div>

    <!-- Official Certificate Frame -->
    <div class="border-4 border-double border-indigo-900 p-8 rounded-2xl bg-white shadow-2xl space-y-6">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b-2 border-indigo-900 pb-4">
            <div class="text-right">
                <h1 class="text-2xl font-black text-indigo-950">بەڕێوەبەرایەتی گومرگی سلێمانی</h1>
                <h2 class="text-base font-bold text-indigo-900">دەفتەری مۆڵەتی ئۆتۆمبێلی گەشتیاری</h2>
                <p class="text-xs font-bold text-slate-500">INTERNATIONAL TOURIST CAR PASSPORT</p>
            </div>
            <div class="text-center font-mono flex flex-col items-center">
                <span class="text-xs text-slate-500 block mb-1">ژمارەی دەفتەر: <strong class="text-indigo-900 text-sm">{{ $transaction->booklet_number ?? 'DF-000000' }}</strong></span>
                <svg id="barcodeBookletSvg" class="max-h-16"></svg>
            </div>
        </div>

        <!-- Drivers Section -->
        <div class="grid grid-cols-2 gap-6 bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
            <div>
                <span class="text-xs font-bold text-indigo-900 block mb-1">ناوی خاوەن / شۆفێری یەکەم</span>
                <p class="text-base font-extrabold text-slate-900">{{ $transaction->visitor_name }}</p>
                <p class="text-xs font-mono text-slate-600 uppercase">{{ $transaction->visitor_name_eng }}</p>
            </div>
            <div>
                <span class="text-xs font-bold text-indigo-900 block mb-1">شۆفێری دووەم</span>
                <p class="text-base font-bold text-slate-900">{{ $transaction->second_driver_name ?? '-' }}</p>
                <p class="text-xs font-mono text-slate-600 uppercase">{{ $transaction->second_driver_name_eng ?? '-' }}</p>
            </div>
        </div>

        <!-- Vehicle Details Table -->
        <table class="w-full border-collapse border border-slate-300 text-sm">
            <tbody class="divide-y divide-slate-300">
                <tr>
                    <td class="bg-slate-100 p-3 font-bold w-1/4">ژمارەی تابلۆ:</td>
                    <td class="p-3 font-mono font-bold text-base text-indigo-900">{{ $transaction->plate_number }}</td>
                    <td class="bg-slate-100 p-3 font-bold w-1/4">جۆری تابلۆ:</td>
                    <td class="p-3 font-bold">{{ $transaction->plateType->name_kurdish ?? '' }}</td>
                </tr>
                <tr>
                    <td class="bg-slate-100 p-3 font-bold">مارکەی ئۆتۆمبێل:</td>
                    <td class="p-3 font-bold">{{ $transaction->carMake->name_kurdish ?? '' }} ({{ $transaction->carMake->name_english ?? '' }})</td>
                    <td class="bg-slate-100 p-3 font-bold">مۆدێل:</td>
                    <td class="p-3 font-mono font-bold">{{ $transaction->model_year }}</td>
                </tr>
                <tr>
                    <td class="bg-slate-100 p-3 font-bold">ژمارەی شاسی:</td>
                    <td class="p-3 font-mono font-bold uppercase text-indigo-950">{{ $transaction->chassis_number }}</td>
                    <td class="bg-slate-100 p-3 font-bold">ژمارەی سەرەندەر:</td>
                    <td class="p-3 font-bold">{{ $transaction->piston_count ?? 4 }} سەرەندەر</td>
                </tr>
                <tr>
                    <td class="bg-slate-100 p-3 font-bold">ماوەی مۆڵەت (ساڵ):</td>
                    <td class="p-3 font-bold text-emerald-700">{{ $transaction->num_years }} ساڵ</td>
                    <td class="bg-slate-100 p-3 font-bold">بەرواری دەرچوون:</td>
                    <td class="p-3 font-mono font-bold">{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer Signatures -->
        <div class="grid grid-cols-2 text-center text-xs font-bold pt-8 border-t border-slate-200">
            <div>
                <p>مۆری لێپرسراوی دەفتەری ئۆتۆمبێل</p>
                <div class="w-24 h-24 border-2 border-dashed border-slate-300 rounded-full mx-auto mt-4 flex items-center justify-center text-[10px] text-slate-400">
                    مۆری فەرمی
                </div>
            </div>
            <div>
                <p>ئیمزای بەڕێوەبەری گومرگی سلێمانی</p>
                <p class="mt-12 text-slate-700 font-extrabold text-sm">{{ $transaction->director->name ?? 'بەڕێوەبەر' }}</p>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            JsBarcode("#barcodeBookletSvg", "{{ $transaction->barcode }}", {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 40,
                displayValue: true,
                fontSize: 12,
                fontOptions: "bold",
                font: "Vazirmatn",
                textMargin: 2
            });
        });
    </script>
</body>
</html>
