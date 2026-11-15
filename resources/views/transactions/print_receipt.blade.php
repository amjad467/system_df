<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پسولەی کەشف و خەمڵاندن - {{ $transaction->barcode }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #0f172a;
            color: #000000;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .header-bg {
            background-color: #e5e7eb !important;
        }
        .gray-label-bg {
            background-color: #f3f4f6 !important;
        }
        .black-cell-border {
            border: 2px solid #000000 !important;
        }

        .sheet-container {
            background-color: #ffffff !important;
            border: 2px solid #000000 !important;
            padding: 20px !important;
            border-radius: 4px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            max-width: 287mm;
            min-height: 195mm;
            margin: 0 auto 24px auto;
            display: flex;
            flex-col: column;
            justify-content: space-between;
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
                size: A4 landscape;
                margin: 4mm;
            }
            .sheet-container {
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                width: 100% !important;
                max-width: 287mm !important;
                min-height: 195mm !important;
                padding: 12px !important;
                margin: 0 auto !important;
                page-break-after: always;
                break-after: page;
            }
            .sheet-container:last-of-type {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-6">

    @php
        $mode = $mode ?? 'both';
        $hasLinked = !empty($linkedPledge);
        $showMain = ($mode === 'both' || $mode === 'main');
        $showPledge = ($hasLinked && ($mode === 'both' || $mode === 'pledge'));
    @endphp

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="no-print mb-6 max-w-[287mm] mx-auto bg-slate-900 border border-slate-700/80 text-white p-3.5 sm:p-4 rounded-2xl shadow-2xl flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3 flex-wrap text-sm font-bold">
            <span class="bg-emerald-500/20 text-emerald-300 px-3.5 py-1.5 rounded-xl border border-emerald-500/40 flex items-center gap-2">
                <span>پسولەی کەشف و خەمڵاندن</span>
                <span class="text-xs font-mono text-emerald-400 font-black">A4 Landscape</span>
            </span>
            <span class="text-slate-300">بارکۆدی سەرەکی: <strong class="font-mono text-sky-400 text-base font-black">{{ $transaction->barcode }}</strong></span>
            @if($hasLinked)
                <span class="text-slate-400">| بەڵێننامە: <strong class="font-mono text-amber-400 text-base font-black">{{ $linkedPledge->barcode }}</strong></span>
            @endif
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if($hasLinked)
                <!-- Mode Switcher Tabs -->
                <div class="bg-slate-950 p-1 rounded-xl border border-slate-800 flex items-center gap-1 text-xs font-bold">
                    <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'both']) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $mode === 'both' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                        هەردوو پەڕە (۲ پەڕە)
                    </a>
                    <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'main']) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $mode === 'main' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                        تەنها دەرهێنان
                    </a>
                    <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'pledge']) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $mode === 'pledge' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                        تەنها بەڵێننامە
                    </a>
                </div>
            @endif

            <button onclick="window.print()" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>چاپکردن (Print)</span>
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs sm:text-sm rounded-xl transition">
                داخستن
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SHEET 1: MAIN TRANSACTION INSPECTION SHEET (پەڕەی یەکەم: دەرهێنان)          -->
    <!-- ========================================================================= -->
    @if($showMain)
    <div id="receiptSheetMain" class="sheet-container flex flex-col justify-between">
        <div>
            <!-- Header Box (Gray Full-width Banner) -->
            <div class="header-bg border-2 border-black p-3 mb-3 flex items-center justify-between text-black">
                <!-- Left: Date Input Box -->
                <div class="flex items-center space-x-2 space-x-reverse">
                    <span class="font-extrabold text-base text-gray-900">به‌روار:</span>
                    <div class="bg-white border-2 border-black px-4 py-1 font-mono font-black text-base text-center min-w-[140px]">
                        {{ $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : date('Y-m-d') }}
                    </div>
                </div>

                <!-- Center: Header Title & Transaction Type Sub-box -->
                <div class="text-center space-y-1.5">
                    <h1 class="text-2xl font-black tracking-wide text-black">پسولەی کەشف و خەمڵاندن</h1>
                    <div class="bg-white border-2 border-black px-10 py-1 inline-block font-black text-xl text-black shadow-sm">
                        {{ $transaction->transactionType->name_kurdish ?? '' }}
                    </div>
                </div>

                <!-- Right: Directorate Title -->
                <div class="text-right">
                    <h2 class="text-lg font-black text-gray-900">به‌ڕێوه‌به‌رایه‌تی گومرگی سلێمانی</h2>
                </div>
            </div>

            <!-- Top Right Barcode Box -->
            <div class="flex justify-end mb-4">
                <div class="border-2 border-black p-1.5 bg-white text-center inline-block min-w-[260px]">
                    <svg id="barcodeSvgMain" class="mx-auto max-h-16"></svg>
                </div>
            </div>

            <!-- Main Content 2-Column Section -->
            <div class="grid grid-cols-12 gap-8 items-start mb-4">
                
                <!-- LEFT COLUMN: Duration Box & Fee Table -->
                <div class="col-span-6 space-y-4">
                    @php
                        $typeId = (int)$transaction->transaction_type_id;
                        $typeName = $transaction->transactionType->name_kurdish ?? '';

                        $isSpecialFineType = in_array($typeId, [3, 4, 7]) || 
                                              str_contains($typeName, 'ناوگۆڕین') || 
                                              str_contains($typeName, 'ناو گۆڕین') || 
                                              str_contains($typeName, 'دەفتەر گۆڕین') || 
                                              str_contains($typeName, 'دەفتەرگۆڕین');

                        $hasDuration = $transaction->isFullBookletForm() && $transaction->start_date && $transaction->end_date;
                    @endphp

                    <!-- Duration / Validity Box (If applicable) -->
                    @if($hasDuration)
                        <div class="space-y-1.5 text-sm font-bold text-gray-900 bg-gray-50 border-2 border-black p-2.5 rounded-sm">
                            <div class="flex items-center justify-start space-x-2 space-x-reverse">
                                <span class="min-w-[110px] text-left font-extrabold">تیبینى بۆ ماوهى:</span>
                                <div class="bg-white border-2 border-black px-3 py-1 font-mono font-black text-center min-w-[45px]">
                                    {{ $transaction->num_years ?? 1 }}
                                </div>
                                <span class="font-extrabold">ساڵ</span>
                            </div>
                            <div class="flex items-center justify-start space-x-2 space-x-reverse">
                                <span class="min-w-[110px] text-left font-extrabold">له به‌روارى:</span>
                                <div class="bg-white border-2 border-black px-3 py-1 font-mono font-black text-center min-w-[140px]">
                                    {{ $transaction->start_date ? \Carbon\Carbon::parse($transaction->start_date)->format('Y-m-d') : date('Y-m-d') }}
                                </div>
                            </div>
                            <div class="flex items-center justify-start space-x-2 space-x-reverse">
                                <span class="min-w-[110px] text-left font-extrabold">بۆ به‌روارى:</span>
                                <div class="bg-white border-2 border-black px-3 py-1 font-mono font-black text-center min-w-[140px]">
                                    {{ $transaction->end_date ? \Carbon\Carbon::parse($transaction->end_date)->format('Y-m-d') : date('Y-m-d', strtotime('+1 year')) }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Fees Breakdown Rows -->
                    <div class="space-y-2.5 pt-1">
                        @if($transaction->pay_amount_years > 0)
                            <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-bold">
                                <div class="gray-label-bg border-2 border-black px-5 py-1.5 text-center min-w-[140px] text-black font-extrabold">رسم</div>
                                <div class="bg-white border-2 border-black px-5 py-1.5 text-center font-mono font-black text-lg min-w-[160px] text-black">
                                    {{ number_format($transaction->pay_amount_years) }}
                                </div>
                            </div>
                        @endif

                        @if($isSpecialFineType)
                            @if($transaction->pay_fine > 0 || $transaction->pay_inspection > 0)
                                @php
                                    $fineVal = $transaction->pay_fine > 0 ? $transaction->pay_fine : $transaction->pay_inspection;
                                @endphp
                                <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-bold">
                                    <div class="gray-label-bg border-2 border-black px-5 py-1.5 text-center min-w-[140px] text-black font-extrabold">سزا</div>
                                    <div class="bg-white border-2 border-black px-5 py-1.5 text-center font-mono font-black text-lg min-w-[160px] text-black">
                                        {{ number_format($fineVal) }}
                                    </div>
                                </div>
                            @endif
                        @else
                            @if($transaction->pay_fine > 0)
                                <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-bold">
                                    <div class="gray-label-bg border-2 border-black px-5 py-1.5 text-center min-w-[140px] text-black font-extrabold">سزا</div>
                                    <div class="bg-white border-2 border-black px-5 py-1.5 text-center font-mono font-black text-lg min-w-[160px] text-black">
                                        {{ number_format($transaction->pay_fine) }}
                                    </div>
                                </div>
                            @endif

                            @if($transaction->pay_inspection > 0)
                                <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-bold">
                                    <div class="gray-label-bg border-2 border-black px-5 py-1.5 text-center min-w-[140px] text-black font-extrabold">اجورکشف</div>
                                    <div class="bg-white border-2 border-black px-5 py-1.5 text-center font-mono font-black text-lg min-w-[160px] text-black">
                                        {{ number_format($transaction->pay_inspection) }}
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($transaction->pay_stamp > 0)
                            <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-bold">
                                <div class="gray-label-bg border-2 border-black px-5 py-1.5 text-center min-w-[140px] text-black font-extrabold">پول</div>
                                <div class="bg-white border-2 border-black px-5 py-1.5 text-center font-mono font-black text-lg min-w-[160px] text-black">
                                    {{ number_format($transaction->pay_stamp) }}
                                </div>
                            </div>
                        @endif

                        @if($transaction->pay_form > 0)
                            <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-bold">
                                <div class="gray-label-bg border-2 border-black px-5 py-1.5 text-center min-w-[140px] text-black font-extrabold">فۆرم</div>
                                <div class="bg-white border-2 border-black px-5 py-1.5 text-center font-mono font-black text-lg min-w-[160px] text-black">
                                    {{ number_format($transaction->pay_form) }}
                                </div>
                            </div>
                        @endif

                        {{-- Total Pay --}}
                        <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-black pt-2">
                            <div class="gray-label-bg border-2 border-black px-5 py-2 text-center min-w-[140px] text-black font-black text-lg">کۆی گشتی</div>
                            <div class="bg-white border-2 border-black px-5 py-2 text-center font-mono font-black text-xl min-w-[160px] text-black">
                                {{ number_format($transaction->total_pay) }}
                            </div>
                        </div>

                        <!-- Total in Words -->
                        <div class="mt-3 border-2 border-black bg-emerald-50 p-2.5 rounded-sm">
                            <span class="text-xs font-bold text-gray-700 block mb-0.5">کۆی گشتی بەپیت:</span>
                            <strong class="text-black font-black text-base leading-snug">
                                {{ \App\Services\KurdishNumberToWords::convert($transaction->total_pay) }}
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Vehicle & Citizen Details Table -->
                <div class="col-span-6">
                    <table class="w-full border-collapse text-base font-bold">
                        <tbody>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 w-1/3 text-center font-extrabold">جۆری مامەڵە</td>
                                <td class="black-cell-border p-2.5 text-center font-black">{{ $transaction->transactionType->name_kurdish ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">ناوی هاووڵاتی</td>
                                <td class="black-cell-border p-2.5 text-center font-black text-lg">
                                    {{ $transaction->visitor_name }}
                                    @if($transaction->visitor_name_eng)
                                        <span class="block text-sm font-bold text-gray-700">({{ $transaction->visitor_name_eng }})</span>
                                    @endif
                                </td>
                            </tr>
                            @if($transaction->phone_number)
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">ژمارەی مۆبایل</td>
                                <td class="black-cell-border p-2.5 text-center font-mono font-bold text-base">{{ $transaction->phone_number }}</td>
                            </tr>
                            @endif
                            @if($transaction->second_driver_name)
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">شۆفێری دووەم</td>
                                <td class="black-cell-border p-2.5 text-center font-bold text-base">
                                    {{ $transaction->second_driver_name }}
                                    @if($transaction->second_driver_name_eng)
                                        <span class="block text-sm font-bold text-gray-700">({{ $transaction->second_driver_name_eng }})</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">ژمارەی ئۆتۆمبێل</td>
                                <td class="black-cell-border p-2.5 text-center">
                                    <div class="flex items-center justify-between px-3">
                                        <span class="font-mono font-black text-xl">{{ $transaction->plate_number }}</span>
                                        <span class="border-2 border-black px-3 py-0.5 text-xs font-black bg-white">{{ $transaction->plateType->name_kurdish ?? 'تایبەت' }}</span>
                                    </div>
                                </td>
                            </tr>

                            @if(str_contains($typeName, 'ناو گۆڕین') && empty($transaction->chassis_number))
                                <tr>
                                    <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">تێبینی</td>
                                    <td class="black-cell-border p-2.5 text-center font-mono text-base">{{ $transaction->notes ?? '-' }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">جۆری ئۆتۆمبێل</td>
                                    <td class="black-cell-border p-2.5 text-center font-bold">{{ $transaction->carMake->name_kurdish ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">شاسی</td>
                                    <td class="black-cell-border p-2.5 text-center font-mono font-black text-base uppercase">{{ $transaction->chassis_number }}</td>
                                </tr>
                                <tr>
                                    <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">مۆدێل</td>
                                    <td class="black-cell-border p-2.5 text-center font-mono font-black text-base">{{ $transaction->model_year }}</td>
                                </tr>
                                <tr>
                                    <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">ڕەنگی ئۆتۆمبێل</td>
                                    <td class="black-cell-border p-2.5 text-center font-bold">{{ $transaction->carColor->name_kurdish ?? '' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bottom Signatures Section -->
        <div class="grid grid-cols-2 gap-12 pt-6 text-center text-base font-black border-t-2 border-black mt-auto">
            <!-- Left Signature: Estimator (خەمڵێنەر) -->
            <div class="space-y-2 flex flex-col items-center">
                <p class="text-lg text-gray-900 font-black">خەمڵێنەر</p>
                <div class="bg-white border-2 border-black px-8 py-1.5 font-black text-center min-w-[220px] text-gray-900 text-base">
                    {{ $transaction->inspected_by ?? (auth()->user()?->name ?? 'امجد صالح عبدالله') }}
                </div>
                <div class="bg-white border-2 border-black px-6 py-1 font-mono font-black text-center min-w-[160px] text-gray-900 text-base">
                    {{ $transaction->inspected_at ? $transaction->inspected_at->format('Y-m-d') : date('Y-m-d') }}
                </div>
            </div>

            <!-- Right Signature: Auditor (ووردبین) -->
            <div class="space-y-2 flex flex-col items-center">
                <p class="text-lg text-gray-900 font-black">ووردبین</p>
                <div class="bg-white border-2 border-black px-6 py-1.5 font-mono font-black text-center min-w-[180px] text-gray-900 text-base mt-10">
                    {{ $transaction->audited_at ? $transaction->audited_at->format('Y-m-d') : ($transaction->is_audited ? date('Y-m-d') : ' ') }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- SHEET 2: COMPANION PLEDGE INSPECTION SHEET (پەڕەی دووەم: بەڵێننامەی هاوپێچ)  -->
    <!-- ========================================================================= -->
    @if($showPledge)
    <div id="receiptSheetPledge" class="sheet-container flex flex-col justify-between">
        <div>
            <!-- Header Box (Gray Full-width Banner) -->
            <div class="header-bg border-2 border-black p-3 mb-3 flex items-center justify-between text-black">
                <!-- Left: Date Input Box -->
                <div class="flex items-center space-x-2 space-x-reverse">
                    <span class="font-extrabold text-base text-gray-900">به‌روار:</span>
                    <div class="bg-white border-2 border-black px-4 py-1 font-mono font-black text-base text-center min-w-[140px]">
                        {{ $linkedPledge->transaction_date ? $linkedPledge->transaction_date->format('Y-m-d') : date('Y-m-d') }}
                    </div>
                </div>

                <!-- Center: Header Title & Transaction Type Sub-box -->
                <div class="text-center space-y-1.5">
                    <h1 class="text-2xl font-black tracking-wide text-black">پسولەی کەشف و خەمڵاندن</h1>
                    <div class="bg-amber-100 border-2 border-black px-10 py-1 inline-block font-black text-xl text-black shadow-sm">
                        بەڵێننامەی یاسایی (هاوپێچی دەرهێنان)
                    </div>
                </div>

                <!-- Right: Directorate Title -->
                <div class="text-right">
                    <h2 class="text-lg font-black text-gray-900">به‌ڕێوه‌به‌رایه‌تی گومرگی سلێمانی</h2>
                </div>
            </div>

            <!-- Top Right Barcode Box -->
            <div class="flex justify-end mb-4">
                <div class="border-2 border-black p-1.5 bg-white text-center inline-block min-w-[260px]">
                    <svg id="barcodeSvgPledge" class="mx-auto max-h-16"></svg>
                </div>
            </div>

            <!-- Main Content 2-Column Section -->
            <div class="grid grid-cols-12 gap-8 items-start mb-4">
                
                <!-- LEFT COLUMN: Fee Table for Pledge -->
                <div class="col-span-6 space-y-4">
                    <!-- Fees Breakdown Rows -->
                    <div class="space-y-2.5 pt-1">
                        @if($linkedPledge->pay_form > 0)
                            <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-bold">
                                <div class="gray-label-bg border-2 border-black px-5 py-1.5 text-center min-w-[140px] text-black font-extrabold">فۆرم</div>
                                <div class="bg-white border-2 border-black px-5 py-1.5 text-center font-mono font-black text-lg min-w-[160px] text-black">
                                    {{ number_format($linkedPledge->pay_form) }}
                                </div>
                            </div>
                        @endif

                        @if($linkedPledge->pay_stamp > 0)
                            <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-bold">
                                <div class="gray-label-bg border-2 border-black px-5 py-1.5 text-center min-w-[140px] text-black font-extrabold">پول</div>
                                <div class="bg-white border-2 border-black px-5 py-1.5 text-center font-mono font-black text-lg min-w-[160px] text-black">
                                    {{ number_format($linkedPledge->pay_stamp) }}
                                </div>
                            </div>
                        @endif

                        @if($linkedPledge->pay_inspection > 0)
                            <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-bold">
                                <div class="gray-label-bg border-2 border-black px-5 py-1.5 text-center min-w-[140px] text-black font-extrabold">اجورکشف</div>
                                <div class="bg-white border-2 border-black px-5 py-1.5 text-center font-mono font-black text-lg min-w-[160px] text-black">
                                    {{ number_format($linkedPledge->pay_inspection) }}
                                </div>
                            </div>
                        @endif

                        {{-- Total Pay --}}
                        <div class="flex items-center justify-start space-x-3 space-x-reverse text-base font-black pt-2">
                            <div class="gray-label-bg border-2 border-black px-5 py-2 text-center min-w-[140px] text-black font-black text-lg">کۆی گشتی</div>
                            <div class="bg-white border-2 border-black px-5 py-2 text-center font-mono font-black text-xl min-w-[160px] text-black">
                                {{ number_format($linkedPledge->total_pay) }}
                            </div>
                        </div>

                        <!-- Total in Words -->
                        <div class="mt-3 border-2 border-black bg-emerald-50 p-2.5 rounded-sm">
                            <span class="text-xs font-bold text-gray-700 block mb-0.5">کۆی گشتی بەپیت:</span>
                            <strong class="text-black font-black text-base leading-snug">
                                {{ \App\Services\KurdishNumberToWords::convert($linkedPledge->total_pay) }}
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Vehicle & Citizen Details Table -->
                <div class="col-span-6">
                    <table class="w-full border-collapse text-base font-bold">
                        <tbody>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 w-1/3 text-center font-extrabold">جۆری مامەڵە</td>
                                <td class="black-cell-border p-2.5 text-center font-black">بەڵێننامەی یاسایی</td>
                            </tr>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">ناوی هاووڵاتی</td>
                                <td class="black-cell-border p-2.5 text-center font-black text-lg">
                                    {{ $linkedPledge->visitor_name }}
                                    @if($linkedPledge->visitor_name_eng)
                                        <span class="block text-sm font-bold text-gray-700">({{ $linkedPledge->visitor_name_eng }})</span>
                                    @endif
                                </td>
                            </tr>
                            @if($linkedPledge->phone_number)
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">ژمارەی مۆبایل</td>
                                <td class="black-cell-border p-2.5 text-center font-mono font-bold text-base">{{ $linkedPledge->phone_number }}</td>
                            </tr>
                            @endif
                            @if($linkedPledge->second_driver_name)
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">شۆفێری دووەم</td>
                                <td class="black-cell-border p-2.5 text-center font-bold text-base">
                                    {{ $linkedPledge->second_driver_name }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">ژمارەی ئۆتۆمبێل</td>
                                <td class="black-cell-border p-2.5 text-center">
                                    <div class="flex items-center justify-between px-3">
                                        <span class="font-mono font-black text-xl">{{ $linkedPledge->plate_number }}</span>
                                        <span class="border-2 border-black px-3 py-0.5 text-xs font-black bg-white">{{ $linkedPledge->plateType->name_kurdish ?? 'تایبەت' }}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">جۆری ئۆتۆمبێل</td>
                                <td class="black-cell-border p-2.5 text-center font-bold">{{ $linkedPledge->carMake->name_kurdish ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">شاسی</td>
                                <td class="black-cell-border p-2.5 text-center font-mono font-black text-base uppercase">{{ $linkedPledge->chassis_number }}</td>
                            </tr>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">مۆدێل</td>
                                <td class="black-cell-border p-2.5 text-center font-mono font-black text-base">{{ $linkedPledge->model_year }}</td>
                            </tr>
                            <tr>
                                <td class="black-cell-border gray-label-bg p-2.5 text-center font-extrabold">ڕەنگی ئۆتۆمبێل</td>
                                <td class="black-cell-border p-2.5 text-center font-bold">{{ $linkedPledge->carColor->name_kurdish ?? '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bottom Signatures Section -->
        <div class="grid grid-cols-2 gap-12 pt-6 text-center text-base font-black border-t-2 border-black mt-auto">
            <!-- Left Signature: Estimator (خەمڵێنەر) -->
            <div class="space-y-2 flex flex-col items-center">
                <p class="text-lg text-gray-900 font-black">خەمڵێنەر</p>
                <div class="bg-white border-2 border-black px-8 py-1.5 font-black text-center min-w-[220px] text-gray-900 text-base">
                    {{ $linkedPledge->inspected_by ?? ($transaction->inspected_by ?? (auth()->user()?->name ?? 'امجد صالح عبدالله')) }}
                </div>
                <div class="bg-white border-2 border-black px-6 py-1 font-mono font-black text-center min-w-[160px] text-gray-900 text-base">
                    {{ $linkedPledge->inspected_at ? $linkedPledge->inspected_at->format('Y-m-d') : date('Y-m-d') }}
                </div>
            </div>

            <!-- Right Signature: Auditor (ووردبین) -->
            <div class="space-y-2 flex flex-col items-center">
                <p class="text-lg text-gray-900 font-black">ووردبین</p>
                <div class="bg-white border-2 border-black px-6 py-1.5 font-mono font-black text-center min-w-[180px] text-gray-900 text-base mt-10">
                    {{ $linkedPledge->audited_at ? $linkedPledge->audited_at->format('Y-m-d') : ($linkedPledge->is_audited ? date('Y-m-d') : ' ') }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            @if($showMain)
            if (document.getElementById("barcodeSvgMain")) {
                JsBarcode("#barcodeSvgMain", "{{ $transaction->barcode }}", {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 2.2,
                    height: 48,
                    displayValue: true,
                    fontSize: 14,
                    fontOptions: "bold",
                    font: "Vazirmatn",
                    textMargin: 2
                });
            }
            @endif

            @if($showPledge)
            if (document.getElementById("barcodeSvgPledge")) {
                JsBarcode("#barcodeSvgPledge", "{{ $linkedPledge->barcode }}", {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 2.2,
                    height: 48,
                    displayValue: true,
                    fontSize: 14,
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
