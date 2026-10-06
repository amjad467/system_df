<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پسولەی کەشف و خەمڵاندن - {{ $transaction->barcode }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #ffffff;
            color: #000000;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
            @page {
                size: A4 landscape;
                margin: 6mm;
            }
        }

        .header-bg {
            background-color: #e5e7eb !important;
        }
        .gray-label-bg {
            background-color: #f3f4f6 !important;
        }
        .black-cell-border {
            border: 1.5px solid #000000 !important;
        }
    </style>
</head>
<body class="p-4 max-w-[1100px] mx-auto">

    <!-- Top Action Bar (Hide in Print) -->
    <div class="no-print mb-4 flex items-center justify-between bg-slate-800 text-white p-3 rounded-xl shadow-lg">
        <div class="flex items-center space-x-3 space-x-reverse text-sm font-bold">
            <span class="bg-emerald-500/20 text-emerald-300 px-3 py-1 rounded-lg border border-emerald-500/40">
                جۆری مامەڵە: {{ $transaction->transactionType->name_kurdish ?? '' }} (A4 Landscape)
            </span>
            <span class="text-slate-300">بارکۆد: <strong class="font-mono text-amber-300">{{ $transaction->barcode }}</strong></span>
        </div>
        <div class="flex items-center space-x-2 space-x-reverse">
            <button onclick="window.print()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow transition flex items-center">
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                چاپکردنی پسولە (Print Landscape)
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 font-bold text-sm rounded-xl transition">
                داخستن
            </button>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <div class="bg-white border-2 border-slate-400 p-4 rounded-md shadow-sm">

        <!-- Header Box (Gray Full-width Banner) -->
        <div class="header-bg border-2 border-gray-400 p-2.5 mb-3 flex items-center justify-between text-black">
            <!-- Left: Date Input Box -->
            <div class="flex items-center space-x-2 space-x-reverse">
                <span class="font-bold text-sm text-gray-900">به‌روار</span>
                <div class="bg-white border-2 border-gray-500 px-3 py-1 font-mono font-bold text-sm text-center min-w-[130px]">
                    {{ $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : date('Y-m-d') }}
                </div>
            </div>

            <!-- Center: Header Title & Transaction Type Sub-box -->
            <div class="text-center space-y-1">
                <h1 class="text-xl font-extrabold tracking-wide text-black">پسولەی کەشف و خەمڵاندن</h1>
                <div class="bg-white border-2 border-gray-500 px-8 py-1 inline-block font-black text-lg text-black shadow-sm">
                    {{ $transaction->transactionType->name_kurdish ?? '' }}
                </div>
            </div>

            <!-- Right: Directorate Title -->
            <div class="text-right">
                <h2 class="text-base font-black text-gray-900">به‌ڕێوه‌به‌رایه‌تی گومرگی سلێمانی</h2>
            </div>
        </div>

        <!-- Top Right Barcode Box -->
        <div class="flex justify-end mb-3">
            <div class="border-2 border-gray-400 p-1 bg-white text-center inline-block min-w-[240px]">
                <svg id="barcodeSvg" class="mx-auto max-h-14"></svg>
            </div>
        </div>

        <!-- Main Content 2-Column Section -->
        <div class="grid grid-cols-12 gap-6 items-start mb-4">
            
            <!-- LEFT COLUMN: Duration Box & Fee Table -->
            <div class="col-span-6 space-y-3">
                
                @php
                    $typeId = (int)$transaction->transaction_type_id;
                    $typeName = $transaction->transactionType->name_kurdish ?? '';
                    $hasDuration = in_array($typeId, [1, 2]) || str_contains($typeName, 'دەرهێنان') || str_contains($typeName, 'تازە') || str_contains($typeName, 'تازه');
                    
                    $isFineType = in_array($typeId, [3, 4, 7]) || str_contains($typeName, 'ناو') || str_contains($typeName, 'دەفتەر');
                @endphp

                <!-- Duration / Validity Box (If applicable) -->
                @if($hasDuration)
                    <div class="space-y-1 text-xs font-bold text-gray-900">
                        <div class="flex items-center justify-start space-x-2 space-x-reverse">
                            <span class="min-w-[100px] text-left">تیبینى بۆ ماوهى</span>
                            <div class="bg-white border-2 border-gray-500 px-3 py-1 font-mono font-bold text-center min-w-[40px]">
                                {{ $transaction->num_years ?? 1 }}
                            </div>
                            <span>ساڵ</span>
                        </div>
                        <div class="flex items-center justify-start space-x-2 space-x-reverse">
                            <span class="min-w-[100px] text-left">له به‌روارى</span>
                            <div class="bg-white border-2 border-gray-500 px-3 py-1 font-mono font-bold text-center min-w-[130px]">
                                {{ $transaction->start_date ? \Carbon\Carbon::parse($transaction->start_date)->format('Y-m-d') : date('Y-m-d') }}
                            </div>
                        </div>
                        <div class="flex items-center justify-start space-x-2 space-x-reverse">
                            <span class="min-w-[100px] text-left">بۆ به‌روارى</span>
                            <div class="bg-white border-2 border-gray-500 px-3 py-1 font-mono font-bold text-center min-w-[130px]">
                                {{ $transaction->end_date ? \Carbon\Carbon::parse($transaction->end_date)->format('Y-m-d') : date('Y-m-d', strtotime('+1 year')) }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Fees Breakdown Rows (Gray Label Box and White Amount Box sit close together) -->
                <div class="space-y-2 pt-1">
                    
                    {{-- 1. Pay Amount Years / رسم --}}
                    @if($transaction->pay_amount_years > 0)
                        <div class="flex items-center justify-start space-x-2 space-x-reverse text-sm font-bold">
                            <div class="gray-label-bg border-2 border-gray-400 px-4 py-1 text-center min-w-[130px]">
                                رسم
                            </div>
                            <div class="bg-white border-2 border-gray-400 px-4 py-1 text-center font-mono font-extrabold min-w-[140px]">
                                {{ number_format($transaction->pay_amount_years) }}
                            </div>
                        </div>
                    @endif

                    {{-- 2. Fine / سزای ناوگۆڕین or سزا --}}
                    @if($transaction->pay_fine > 0)
                        <div class="flex items-center justify-start space-x-2 space-x-reverse text-sm font-bold">
                            <div class="gray-label-bg border-2 border-gray-400 px-4 py-1 text-center min-w-[130px]">
                                {{ $isFineType ? ($typeId == 3 ? 'سزای ناوگۆڕین' : 'سزا') : 'سزا' }}
                            </div>
                            <div class="bg-white border-2 border-gray-400 px-4 py-1 text-center font-mono font-extrabold min-w-[140px]">
                                {{ number_format($transaction->pay_fine) }}
                            </div>
                        </div>
                    @endif

                    {{-- 3. Inspection Pay / اجورکشف --}}
                    @if($transaction->pay_inspection > 0)
                        <div class="flex items-center justify-start space-x-2 space-x-reverse text-sm font-bold">
                            <div class="gray-label-bg border-2 border-gray-400 px-4 py-1 text-center min-w-[130px]">
                                اجورکشف
                            </div>
                            <div class="bg-white border-2 border-gray-400 px-4 py-1 text-center font-mono font-extrabold min-w-[140px]">
                                {{ number_format($transaction->pay_inspection) }}
                            </div>
                        </div>
                    @endif

                    {{-- 4. Stamp / پول --}}
                    @if($transaction->pay_stamp > 0)
                        <div class="flex items-center justify-start space-x-2 space-x-reverse text-sm font-bold">
                            <div class="gray-label-bg border-2 border-gray-400 px-4 py-1 text-center min-w-[130px]">
                                پول
                            </div>
                            <div class="bg-white border-2 border-gray-400 px-4 py-1 text-center font-mono font-extrabold min-w-[140px]">
                                {{ number_format($transaction->pay_stamp) }}
                            </div>
                        </div>
                    @endif

                    {{-- 5. Form / فۆرم --}}
                    @if($transaction->pay_form > 0)
                        <div class="flex items-center justify-start space-x-2 space-x-reverse text-sm font-bold">
                            <div class="gray-label-bg border-2 border-gray-400 px-4 py-1 text-center min-w-[130px]">
                                فۆرم
                            </div>
                            <div class="bg-white border-2 border-gray-400 px-4 py-1 text-center font-mono font-extrabold min-w-[140px]">
                                {{ number_format($transaction->pay_form) }}
                            </div>
                        </div>
                    @endif

                    {{-- 6. Total Pay / کۆی گشتی --}}
                    <div class="flex items-center justify-start space-x-2 space-x-reverse text-sm font-extrabold pt-1">
                        <div class="gray-label-bg border-2 border-gray-500 px-4 py-1.5 text-center min-w-[130px]">
                            کۆی گشتی
                        </div>
                        <div class="bg-white border-2 border-gray-500 px-4 py-1.5 text-center font-mono font-black text-base min-w-[140px]">
                            {{ number_format($transaction->total_pay) }}
                        </div>
                    </div>

                    <!-- Total Amount in Kurdish Written Words (بەڕێنوسی کوردی) -->
                    <div class="pt-1.5 pr-2 text-xs font-bold text-gray-900 border-r-4 border-emerald-600 bg-emerald-50/60 p-2 rounded">
                        <span>کۆی گشتی بە پیت: </span>
                        <strong class="text-black text-sm">{{ \App\Services\KurdishNumberToWords::convert($transaction->total_pay) }}</strong>
                    </div>

                </div>
            </div>

            <!-- RIGHT COLUMN: Vehicle & Citizen Details Table -->
            <div class="col-span-6">
                <table class="w-full border-collapse text-sm font-bold">
                    <tbody>
                        <tr>
                            <td class="black-cell-border gray-label-bg p-2 w-1/3 text-center">جۆری مامەڵە</td>
                            <td class="black-cell-border p-2 text-center">{{ $transaction->transactionType->name_kurdish ?? '' }}</td>
                        </tr>
                        <tr>
                            <td class="black-cell-border gray-label-bg p-2 text-center">ناوی هاووڵاتی</td>
                            <td class="black-cell-border p-2 text-center font-extrabold text-base">{{ $transaction->visitor_name }}</td>
                        </tr>
                        <tr>
                            <td class="black-cell-border gray-label-bg p-2 text-center">ژمارەی ئۆتۆمبێل</td>
                            <td class="black-cell-border p-2 text-center">
                                <div class="flex items-center justify-between px-2">
                                    <span class="font-mono font-bold text-base">{{ $transaction->plate_number }}</span>
                                    <span class="border border-black px-2 py-0.5 text-xs font-bold">{{ $transaction->plateType->name_kurdish ?? 'تایبەت' }}</span>
                                </div>
                            </td>
                        </tr>

                        @if(str_contains($typeName, 'ناو گۆڕین') && empty($transaction->chassis_number))
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2 text-center">تێبینی</td>
                                <td class="black-cell-border p-2 text-center font-mono">{{ $transaction->notes ?? '-' }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2 text-center">جۆری ئۆتۆمبێل</td>
                                <td class="black-cell-border p-2 text-center">{{ $transaction->carMake->name_kurdish ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2 text-center">شاسی</td>
                                <td class="black-cell-border p-2 text-center font-mono uppercase">{{ $transaction->chassis_number }}</td>
                            </tr>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2 text-center">مۆدێل</td>
                                <td class="black-cell-border p-2 text-center font-mono">{{ $transaction->model_year }}</td>
                            </tr>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2 text-center">ڕەنگی ئۆتۆمبێل</td>
                                <td class="black-cell-border p-2 text-center">{{ $transaction->carColor->name_kurdish ?? '' }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Bottom Signatures Section -->
        <div class="grid grid-cols-2 gap-8 pt-4 text-center text-sm font-extrabold border-t border-gray-300">
            
            <!-- Left Signature: Estimator (خەمڵێنەر) -->
            <div class="space-y-1.5 flex flex-col items-center">
                <p class="text-base text-gray-900 font-black">خەمڵێنەر</p>
                <div class="bg-white border-2 border-gray-500 px-6 py-1 font-bold text-center min-w-[200px] text-gray-900">
                    {{ $transaction->inspected_by ?? (auth()->user()?->name ?? 'امجد صالح عبدالله') }}
                </div>
                <div class="bg-white border-2 border-gray-500 px-6 py-1 font-mono font-bold text-center min-w-[150px] text-gray-900">
                    {{ $transaction->inspected_at ? $transaction->inspected_at->format('Y-m-d') : date('Y-m-d') }}
                </div>
            </div>

            <!-- Right Signature: Auditor (ووردبین) -->
            <div class="space-y-1.5 flex flex-col items-center">
                <p class="text-base text-gray-900 font-black">ووردبین</p>
                <div class="bg-white border-2 border-gray-500 px-6 py-1 font-mono font-bold text-center min-w-[160px] text-gray-900 mt-8">
                    {{ $transaction->audited_at ? $transaction->audited_at->format('Y-m-d') : ($transaction->is_audited ? date('Y-m-d') : ' ') }}
                </div>
            </div>

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
