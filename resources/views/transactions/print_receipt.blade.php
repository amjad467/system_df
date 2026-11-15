<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پسولەی کەشف و خەمڵاندن - {{ $transaction->barcode }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&family=Courier+Prime:wght@700&display=swap" rel="stylesheet">
    <style id="dynamicPrintStyle">
        @page {
            size: A4 landscape;
            margin: 4mm 5mm;
        }
    </style>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #0b1120;
            color: #000000;
            margin: 0;
            padding: 0;
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
            border: 1.5px solid #000000 !important;
        }

        /* Default Screen & A4 Landscape Sheet */
        .sheet-container {
            background-color: #ffffff !important;
            border: 2px solid #000000 !important;
            padding: 5mm 7mm !important;
            border-radius: 2px;
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.5);
            width: 282mm;
            max-width: 282mm;
            height: 194mm;
            max-height: 194mm;
            margin: 15px auto 25px auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            box-sizing: border-box !important;
        }

        /* Portrait Mode Override */
        body.mode-portrait .sheet-container {
            width: 196mm !important;
            max-width: 196mm !important;
            height: 280mm !important;
            max-height: 280mm !important;
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
            .sheet-container {
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                margin: 0 auto !important;
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                overflow: hidden !important;
            }
            .sheet-container:last-of-type {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }
    </style>
</head>
<body class="p-0 sm:p-2">

    @php
        $mode = $mode ?? 'both';
        $hasLinked = !empty($linkedPledge);
        $showMain = ($mode === 'both' || $mode === 'main');
        $showPledge = ($hasLinked && ($mode === 'both' || $mode === 'pledge'));
    @endphp

    <!-- ========================================================================= -->
    <!-- FLOATING PRINT STUDIO & LAYOUT ADJUSTER (وەک تەواوی ڕاپۆرتەکانی تر)         -->
    <!-- ========================================================================= -->
    <x-print-layout-studio 
        docId="receipt_inspection" 
        targetId="studio-printable-target" 
        title="پسولەی کەشف و خەمڵاندن • {{ $transaction->barcode }}" 
        initialTop="0" 
        initialRight="0" 
        initialZoom="100">

        <!-- Custom Slot: Tabs & Orientation Switcher -->
        <div class="flex items-center gap-2 flex-wrap">
            @if($hasLinked)
                <!-- Mode Switcher Tabs -->
                <div class="bg-slate-950 p-0.5 rounded-xl border border-slate-800 flex items-center gap-1 text-[11px] font-bold">
                    <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'both']) }}" 
                       class="px-2.5 py-1 rounded-lg transition {{ $mode === 'both' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                        هەردوو پەڕە (۲)
                    </a>
                    <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'main']) }}" 
                       class="px-2.5 py-1 rounded-lg transition {{ $mode === 'main' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                        تەنها دەرهێنان
                    </a>
                    <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'pledge']) }}" 
                       class="px-2.5 py-1 rounded-lg transition {{ $mode === 'pledge' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                        تەنها بەڵێننامە
                    </a>
                </div>
            @endif

            <!-- Orientation Switcher -->
            <div class="bg-slate-950 p-0.5 rounded-xl border border-slate-800 flex items-center gap-1 text-[10px] font-bold">
                <button type="button" id="btnOrientLandscape" onclick="setOrientation('landscape')" 
                        class="px-2 py-1 rounded-lg bg-indigo-600 text-white transition flex items-center gap-1">
                    <span>🖼️</span>
                    <span>ئاسۆیی (A4)</span>
                </button>
                <button type="button" id="btnOrientPortrait" onclick="setOrientation('portrait')" 
                        class="px-2 py-1 rounded-lg text-slate-400 hover:text-white transition flex items-center gap-1">
                    <span>📄</span>
                    <span>ستوونی</span>
                </button>
            </div>
        </div>

    </x-print-layout-studio>

    <!-- Printable Sheets Container -->
    <div class="pt-3 pb-8">

        <!-- ========================================================================= -->
        <!-- SHEET 1: MAIN TRANSACTION INSPECTION SHEET (پەڕەی یەکەم: دەرهێنان)          -->
        <!-- ========================================================================= -->
        @if($showMain)
        <div id="receiptSheetMain" class="sheet-container studio-printable-target">
            <div>
                <!-- Header Box (Gray Full-width Banner) -->
                <div class="header-bg border-2 border-black px-3 py-1.5 mb-2 flex items-center justify-between text-black">
                    <!-- Left: Date Input Box -->
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <span class="font-extrabold text-sm text-gray-900">به‌روار:</span>
                        <div class="bg-white border-2 border-black px-3 py-0.5 font-mono font-black text-sm text-center min-w-[125px]">
                            {{ $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : date('Y-m-d') }}
                        </div>
                    </div>

                    <!-- Center: Header Title & Transaction Type Sub-box -->
                    <div class="text-center space-y-1">
                        <h1 class="text-lg font-black tracking-wide text-black leading-none">پسولەی کەشف و خەمڵاندن</h1>
                        <div class="bg-white border-2 border-black px-6 py-0.5 inline-block font-black text-sm text-black shadow-sm leading-tight">
                            {{ $transaction->transactionType->name_kurdish ?? '' }}
                        </div>
                    </div>

                    <!-- Right: Directorate Title -->
                    <div class="text-right">
                        <h2 class="text-sm font-black text-gray-900">به‌ڕێوه‌به‌رایه‌تی گومرگی سلێمانی</h2>
                    </div>
                </div>

                <!-- Top Right Barcode Box -->
                <div class="flex justify-end mb-2">
                    <div class="border-2 border-black px-2 py-1 bg-white text-center inline-block min-w-[220px]">
                        <svg id="barcodeSvgMain" class="mx-auto" style="height: 36px;"></svg>
                    </div>
                </div>

                <!-- Main Content 2-Column Section -->
                <div class="grid grid-cols-12 gap-5 items-start">
                    
                    <!-- LEFT COLUMN: Duration Box & Fee Table -->
                    <div class="col-span-6 space-y-2">
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
                            <div class="space-y-1 text-xs font-bold text-gray-900 bg-gray-50 border-2 border-black p-1.5 rounded-sm">
                                <div class="flex items-center justify-start space-x-2 space-x-reverse">
                                    <span class="min-w-[90px] text-left font-extrabold">تیبینى بۆ ماوهى:</span>
                                    <div class="bg-white border-2 border-black px-2.5 py-0.5 font-mono font-black text-center min-w-[36px]">
                                        {{ $transaction->num_years ?? 1 }}
                                    </div>
                                    <span class="font-extrabold">ساڵ</span>
                                </div>
                                <div class="flex items-center justify-start space-x-2 space-x-reverse">
                                    <span class="min-w-[90px] text-left font-extrabold">له به‌روارى:</span>
                                    <div class="bg-white border-2 border-black px-2.5 py-0.5 font-mono font-black text-center min-w-[120px]">
                                        {{ $transaction->start_date ? \Carbon\Carbon::parse($transaction->start_date)->format('Y-m-d') : date('Y-m-d') }}
                                    </div>
                                </div>
                                <div class="flex items-center justify-start space-x-2 space-x-reverse">
                                    <span class="min-w-[90px] text-left font-extrabold">بۆ به‌روارى:</span>
                                    <div class="bg-white border-2 border-black px-2.5 py-0.5 font-mono font-black text-center min-w-[120px]">
                                        {{ $transaction->end_date ? \Carbon\Carbon::parse($transaction->end_date)->format('Y-m-d') : date('Y-m-d', strtotime('+1 year')) }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Fees Breakdown Rows -->
                        <div class="space-y-1.5">
                            @if($transaction->pay_amount_years > 0)
                                <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-bold">
                                    <div class="gray-label-bg border-2 border-black px-4 py-1 text-center min-w-[110px] text-black font-extrabold">رسم</div>
                                    <div class="bg-white border-2 border-black px-4 py-1 text-center font-mono font-black text-sm min-w-[140px] text-black">
                                        {{ number_format($transaction->pay_amount_years) }}
                                    </div>
                                </div>
                            @endif

                            @if($isSpecialFineType)
                                @if($transaction->pay_fine > 0 || $transaction->pay_inspection > 0)
                                    @php
                                        $fineVal = $transaction->pay_fine > 0 ? $transaction->pay_fine : $transaction->pay_inspection;
                                    @endphp
                                    <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-bold">
                                        <div class="gray-label-bg border-2 border-black px-4 py-1 text-center min-w-[110px] text-black font-extrabold">سزا</div>
                                        <div class="bg-white border-2 border-black px-4 py-1 text-center font-mono font-black text-sm min-w-[140px] text-black">
                                            {{ number_format($fineVal) }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                @if($transaction->pay_fine > 0)
                                    <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-bold">
                                        <div class="gray-label-bg border-2 border-black px-4 py-1 text-center min-w-[110px] text-black font-extrabold">سزا</div>
                                        <div class="bg-white border-2 border-black px-4 py-1 text-center font-mono font-black text-sm min-w-[140px] text-black">
                                            {{ number_format($transaction->pay_fine) }}
                                        </div>
                                    </div>
                                @endif

                                @if($transaction->pay_inspection > 0)
                                    <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-bold">
                                        <div class="gray-label-bg border-2 border-black px-4 py-1 text-center min-w-[110px] text-black font-extrabold">اجورکشف</div>
                                        <div class="bg-white border-2 border-black px-4 py-1 text-center font-mono font-black text-sm min-w-[140px] text-black">
                                            {{ number_format($transaction->pay_inspection) }}
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if($transaction->pay_stamp > 0)
                                <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-bold">
                                    <div class="gray-label-bg border-2 border-black px-4 py-1 text-center min-w-[110px] text-black font-extrabold">پول</div>
                                    <div class="bg-white border-2 border-black px-4 py-1 text-center font-mono font-black text-sm min-w-[140px] text-black">
                                        {{ number_format($transaction->pay_stamp) }}
                                    </div>
                                </div>
                            @endif

                            @if($transaction->pay_form > 0)
                                <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-bold">
                                    <div class="gray-label-bg border-2 border-black px-4 py-1 text-center min-w-[110px] text-black font-extrabold">فۆرم</div>
                                    <div class="bg-white border-2 border-black px-4 py-1 text-center font-mono font-black text-sm min-w-[140px] text-black">
                                        {{ number_format($transaction->pay_form) }}
                                    </div>
                                </div>
                            @endif

                            {{-- Total Pay --}}
                            <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-black pt-1">
                                <div class="gray-label-bg border-2 border-black px-4 py-1.5 text-center min-w-[110px] text-black font-black text-sm">کۆی گشتی</div>
                                <div class="bg-white border-2 border-black px-4 py-1.5 text-center font-mono font-black text-base min-w-[140px] text-black">
                                    {{ number_format($transaction->total_pay) }}
                                </div>
                            </div>

                            <!-- Total in Words -->
                            <div class="border-2 border-black bg-emerald-50 px-2 py-1.5 rounded-sm">
                                <span class="text-[10px] font-bold text-gray-700 block leading-tight">کۆی گشتی بەپیت:</span>
                                <strong class="text-black font-black text-xs leading-snug block">
                                    {{ \App\Services\KurdishNumberToWords::convert($transaction->total_pay) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Vehicle & Citizen Details Table -->
                    <div class="col-span-6">
                        <table class="w-full border-collapse text-xs font-bold">
                            <tbody>
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 w-1/3 text-center font-extrabold">جۆری مامەڵە</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-black">{{ $transaction->transactionType->name_kurdish ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">ناوی هاووڵاتی</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-black text-sm">
                                        {{ $transaction->visitor_name }}
                                        @if($transaction->visitor_name_eng)
                                            <span class="block text-[11px] font-bold text-gray-700">({{ $transaction->visitor_name_eng }})</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($transaction->phone_number)
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">ژمارەی مۆبایل</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-mono font-bold">{{ $transaction->phone_number }}</td>
                                </tr>
                                @endif
                                @if($transaction->second_driver_name)
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">شۆفێری دووەم</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-bold">
                                        {{ $transaction->second_driver_name }}
                                        @if($transaction->second_driver_name_eng)
                                            <span class="block text-[10px] font-bold text-gray-700">({{ $transaction->second_driver_name_eng }})</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">ژمارەی ئۆتۆمبێل</td>
                                    <td class="black-cell-border py-1 px-2 text-center">
                                        <div class="flex items-center justify-between px-2">
                                            <span class="font-mono font-black text-base">{{ $transaction->plate_number }}</span>
                                            <span class="border border-black px-2 py-0.2 text-[10px] font-black bg-white">{{ $transaction->plateType->name_kurdish ?? 'تایبەت' }}</span>
                                        </div>
                                    </td>
                                </tr>

                                @if(str_contains($typeName, 'ناو گۆڕین') && empty($transaction->chassis_number))
                                    <tr>
                                        <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">تێبینی</td>
                                        <td class="black-cell-border py-1 px-2 text-center font-mono">{{ $transaction->notes ?? '-' }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">جۆری ئۆتۆمبێل</td>
                                        <td class="black-cell-border py-1 px-2 text-center font-bold">{{ $transaction->carMake->name_kurdish ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">شاسی</td>
                                        <td class="black-cell-border py-1 px-2 text-center font-mono font-black uppercase text-xs">{{ $transaction->chassis_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">مۆدێل</td>
                                        <td class="black-cell-border py-1 px-2 text-center font-mono font-black">{{ $transaction->model_year }}</td>
                                    </tr>
                                    <tr>
                                        <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">ڕەنگی ئۆتۆمبێل</td>
                                        <td class="black-cell-border py-1 px-2 text-center font-bold">{{ $transaction->carColor->name_kurdish ?? '' }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Bottom Signatures Section -->
            <div class="grid grid-cols-2 gap-8 pt-3 text-center text-xs font-black border-t-2 border-black mt-auto">
                <!-- Left Signature: Estimator (خەمڵێنەر) -->
                <div class="space-y-1 flex flex-col items-center">
                    <p class="text-xs text-gray-900 font-black">خەمڵێنەر</p>
                    <div class="bg-white border-2 border-black px-6 py-1 font-black text-center min-w-[180px] text-gray-900 text-xs">
                        {{ $transaction->inspected_by ?? (auth()->user()?->name ?? 'امجد صالح عبدالله') }}
                    </div>
                    <div class="bg-white border-2 border-black px-4 py-0.5 font-mono font-black text-center min-w-[130px] text-gray-900 text-xs">
                        {{ $transaction->inspected_at ? $transaction->inspected_at->format('Y-m-d') : date('Y-m-d') }}
                    </div>
                </div>

                <!-- Right Signature: Auditor (ووردبین) -->
                <div class="space-y-1 flex flex-col items-center">
                    <p class="text-xs text-gray-900 font-black">ووردبین</p>
                    <div class="bg-white border-2 border-black px-6 py-1 font-mono font-black text-center min-w-[180px] text-gray-900 text-xs mt-6">
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
        <div id="receiptSheetPledge" class="sheet-container studio-printable-target">
            <div>
                <!-- Header Box (Gray Full-width Banner) -->
                <div class="header-bg border-2 border-black px-3 py-1.5 mb-2 flex items-center justify-between text-black">
                    <!-- Left: Date Input Box -->
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <span class="font-extrabold text-sm text-gray-900">به‌روار:</span>
                        <div class="bg-white border-2 border-black px-3 py-0.5 font-mono font-black text-sm text-center min-w-[125px]">
                            {{ $linkedPledge->transaction_date ? $linkedPledge->transaction_date->format('Y-m-d') : date('Y-m-d') }}
                        </div>
                    </div>

                    <!-- Center: Header Title & Transaction Type Sub-box -->
                    <div class="text-center space-y-1">
                        <h1 class="text-lg font-black tracking-wide text-black leading-none">پسولەی کەشف و خەمڵاندن</h1>
                        <div class="bg-amber-100 border-2 border-black px-6 py-0.5 inline-block font-black text-sm text-black shadow-sm leading-tight">
                            بەڵێننامەی یاسایی (هاوپێچی دەرهێنان)
                        </div>
                    </div>

                    <!-- Right: Directorate Title -->
                    <div class="text-right">
                        <h2 class="text-sm font-black text-gray-900">به‌ڕێوه‌به‌رایه‌تی گومرگی سلێمانی</h2>
                    </div>
                </div>

                <!-- Top Right Barcode Box -->
                <div class="flex justify-end mb-2">
                    <div class="border-2 border-black px-2 py-1 bg-white text-center inline-block min-w-[220px]">
                        <svg id="barcodeSvgPledge" class="mx-auto" style="height: 36px;"></svg>
                    </div>
                </div>

                <!-- Main Content 2-Column Section -->
                <div class="grid grid-cols-12 gap-5 items-start">
                    
                    <!-- LEFT COLUMN: Fee Table for Pledge -->
                    <div class="col-span-6 space-y-2">
                        <!-- Fees Breakdown Rows -->
                        <div class="space-y-1.5">
                            @if($linkedPledge->pay_form > 0)
                                <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-bold">
                                    <div class="gray-label-bg border-2 border-black px-4 py-1 text-center min-w-[110px] text-black font-extrabold">فۆرم</div>
                                    <div class="bg-white border-2 border-black px-4 py-1 text-center font-mono font-black text-sm min-w-[140px] text-black">
                                        {{ number_format($linkedPledge->pay_form) }}
                                    </div>
                                </div>
                            @endif

                            @if($linkedPledge->pay_stamp > 0)
                                <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-bold">
                                    <div class="gray-label-bg border-2 border-black px-4 py-1 text-center min-w-[110px] text-black font-extrabold">پول</div>
                                    <div class="bg-white border-2 border-black px-4 py-1 text-center font-mono font-black text-sm min-w-[140px] text-black">
                                        {{ number_format($linkedPledge->pay_stamp) }}
                                    </div>
                                </div>
                            @endif

                            @if($linkedPledge->pay_inspection > 0)
                                <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-bold">
                                    <div class="gray-label-bg border-2 border-black px-4 py-1 text-center min-w-[110px] text-black font-extrabold">اجورکشف</div>
                                    <div class="bg-white border-2 border-black px-4 py-1 text-center font-mono font-black text-sm min-w-[140px] text-black">
                                        {{ number_format($linkedPledge->pay_inspection) }}
                                    </div>
                                </div>
                            @endif

                            {{-- Total Pay --}}
                            <div class="flex items-center justify-start space-x-2 space-x-reverse text-xs font-black pt-1">
                                <div class="gray-label-bg border-2 border-black px-4 py-1.5 text-center min-w-[110px] text-black font-black text-sm">کۆی گشتی</div>
                                <div class="bg-white border-2 border-black px-4 py-1.5 text-center font-mono font-black text-base min-w-[140px] text-black">
                                    {{ number_format($linkedPledge->total_pay) }}
                                </div>
                            </div>

                            <!-- Total in Words -->
                            <div class="border-2 border-black bg-emerald-50 px-2 py-1.5 rounded-sm">
                                <span class="text-[10px] font-bold text-gray-700 block leading-tight">کۆی گشتی بەپیت:</span>
                                <strong class="text-black font-black text-xs leading-snug block">
                                    {{ \App\Services\KurdishNumberToWords::convert($linkedPledge->total_pay) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Vehicle & Citizen Details Table -->
                    <div class="col-span-6">
                        <table class="w-full border-collapse text-xs font-bold">
                            <tbody>
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 w-1/3 text-center font-extrabold">جۆری مامەڵە</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-black">بەڵێننامەی یاسایی</td>
                                </tr>
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">ناوی هاووڵاتی</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-black text-sm">
                                        {{ $linkedPledge->visitor_name }}
                                        @if($linkedPledge->visitor_name_eng)
                                            <span class="block text-[11px] font-bold text-gray-700">({{ $linkedPledge->visitor_name_eng }})</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($linkedPledge->phone_number)
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">ژمارەی مۆبایل</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-mono font-bold">{{ $linkedPledge->phone_number }}</td>
                                </tr>
                                @endif
                                @if($linkedPledge->second_driver_name)
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">شۆفێری دووەم</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-bold">
                                        {{ $linkedPledge->second_driver_name }}
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">ژمارەی ئۆتۆمبێل</td>
                                    <td class="black-cell-border py-1 px-2 text-center">
                                        <div class="flex items-center justify-between px-2">
                                            <span class="font-mono font-black text-base">{{ $linkedPledge->plate_number }}</span>
                                            <span class="border border-black px-2 py-0.2 text-[10px] font-black bg-white">{{ $linkedPledge->plateType->name_kurdish ?? 'تایبەت' }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">جۆری ئۆتۆمبێل</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-bold">{{ $linkedPledge->carMake->name_kurdish ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">شاسی</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-mono font-black uppercase text-xs">{{ $linkedPledge->chassis_number }}</td>
                                </tr>
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">مۆدێل</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-mono font-black">{{ $linkedPledge->model_year }}</td>
                                </tr>
                                <tr>
                                    <td class="black-cell-border gray-label-bg py-1 px-2 text-center font-extrabold">ڕەنگی ئۆتۆمبێل</td>
                                    <td class="black-cell-border py-1 px-2 text-center font-bold">{{ $linkedPledge->carColor->name_kurdish ?? '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Bottom Signatures Section -->
            <div class="grid grid-cols-2 gap-8 pt-3 text-center text-xs font-black border-t-2 border-black mt-auto">
                <!-- Left Signature: Estimator (خەمڵێنەر) -->
                <div class="space-y-1 flex flex-col items-center">
                    <p class="text-xs text-gray-900 font-black">خەمڵێنەر</p>
                    <div class="bg-white border-2 border-black px-6 py-1 font-black text-center min-w-[180px] text-gray-900 text-xs">
                        {{ $linkedPledge->inspected_by ?? ($transaction->inspected_by ?? (auth()->user()?->name ?? 'امجد صالح عبدالله')) }}
                    </div>
                    <div class="bg-white border-2 border-black px-4 py-0.5 font-mono font-black text-center min-w-[130px] text-gray-900 text-xs">
                        {{ $linkedPledge->inspected_at ? $linkedPledge->inspected_at->format('Y-m-d') : date('Y-m-d') }}
                    </div>
                </div>

                <!-- Right Signature: Auditor (ووردبین) -->
                <div class="space-y-1 flex flex-col items-center">
                    <p class="text-xs text-gray-900 font-black">ووردبین</p>
                    <div class="bg-white border-2 border-black px-6 py-1 font-mono font-black text-center min-w-[180px] text-gray-900 text-xs mt-6">
                        {{ $linkedPledge->audited_at ? $linkedPledge->audited_at->format('Y-m-d') : ($linkedPledge->is_audited ? date('Y-m-d') : ' ') }}
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    <script>
        // Orientation Switcher Script
        function setOrientation(mode) {
            const style = document.getElementById('dynamicPrintStyle');
            const btnLand = document.getElementById('btnOrientLandscape');
            const btnPort = document.getElementById('btnOrientPortrait');

            if (mode === 'portrait') {
                document.body.classList.add('mode-portrait');
                style.innerHTML = '@page { size: A4 portrait; margin: 5mm; }';
                if (btnLand && btnPort) {
                    btnLand.className = 'px-2 py-1 rounded-lg text-slate-400 hover:text-white transition flex items-center gap-1';
                    btnPort.className = 'px-2 py-1 rounded-lg bg-indigo-600 text-white transition flex items-center gap-1';
                }
                localStorage.setItem('receipt_orient_preference', 'portrait');
            } else {
                document.body.classList.remove('mode-portrait');
                style.innerHTML = '@page { size: A4 landscape; margin: 4mm 5mm; }';
                if (btnLand && btnPort) {
                    btnLand.className = 'px-2 py-1 rounded-lg bg-indigo-600 text-white transition flex items-center gap-1';
                    btnPort.className = 'px-2 py-1 rounded-lg text-slate-400 hover:text-white transition flex items-center gap-1';
                }
                localStorage.setItem('receipt_orient_preference', 'landscape');
            }
        }

        // Restore Orientation Preference
        try {
            const savedOrient = localStorage.getItem('receipt_orient_preference');
            if (savedOrient === 'portrait') {
                setOrientation('portrait');
            }
        } catch(e) {}

        // Barcode Generation
        document.addEventListener("DOMContentLoaded", function() {
            @if($showMain)
            if (document.getElementById("barcodeSvgMain")) {
                JsBarcode("#barcodeSvgMain", "{{ $transaction->barcode }}", {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 2.0,
                    height: 36,
                    displayValue: true,
                    fontSize: 12,
                    fontOptions: "bold",
                    font: "Vazirmatn",
                    textMargin: 1
                });
            }
            @endif

            @if($showPledge)
            if (document.getElementById("barcodeSvgPledge")) {
                JsBarcode("#barcodeSvgPledge", "{{ $linkedPledge->barcode }}", {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 2.0,
                    height: 36,
                    displayValue: true,
                    fontSize: 12,
                    fontOptions: "bold",
                    font: "Vazirmatn",
                    textMargin: 1
                });
            }
            @endif
        });
    </script>
</body>
</html>