<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پسوولەی پارە وەرگرتن ۱۳۷ - {{ $transaction->receipt_37a_number ?? $transaction->barcode }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f1f5f9;
            color: #000000;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Paper sheet size: 16.0cm width x 23.5cm height */
        .receipt-sheet {
            width: 16.0cm;
            height: 23.5cm;
            position: relative;
            margin: 20px auto;
            background-color: #ffffff;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }

        /* Modes: Show or Hide Background Template Frame */
        .mode-preprinted .bg-template-frame {
            display: none !important;
        }

        .mode-full .bg-template-frame {
            display: block !important;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            @page {
                size: 16.0cm 23.5cm;
                margin: 0;
            }

            .receipt-sheet {
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                page-break-after: always;
            }
        }

        /* Printable absolute positioning zones */
        .printable-content {
            position: absolute;
            inset: 0;
            z-index: 10;
        }
    </style>
</head>
<body class="mode-preprinted">

    @php
        // Helper to convert numbers to Kurdish Digits (ڕەنووسی کوردی)
        if (!function_exists('toKurdishDigits')) {
            function toKurdishDigits($number) {
                $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                $kurdish = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                return str_replace($western, $kurdish, (string)$number);
            }
        }

        // Helper to convert number to Kurdish Words (پارە بە وشەی کوردی)
        if (!function_exists('numberToKurdishWords')) {
            function numberToKurdishWords($number) {
                $number = (int)$number;
                if ($number == 0) return 'صفر';

                $units = ['', 'یەک', 'دوو', 'سێ', 'چوار', 'پێنج', 'شەش', 'حەوت', 'هەشت', 'نۆ'];
                $teens = ['دە', 'یازدە', 'دوانزدە', 'سێزدە', 'چواردە', 'پازدە', 'شانزدە', 'حەڤدە', 'هەژدە', 'نۆزدە'];
                $tens = ['', 'دە', 'بیست', 'سی', 'چل', 'پەنجا', 'شەست', 'حەفتا', 'هەشتا', 'نەوەد'];
                $hundreds = ['', 'سەد', 'دووسەد', 'سێسەد', 'چوارسەد', 'پێنجسەد', 'شەشسەد', 'حەوتسەد', 'هەشتسەد', 'نۆسەد'];

                if ($number >= 1000000) {
                    $millions = floor($number / 1000000);
                    $remainder = $number % 1000000;
                    $res = numberToKurdishWords($millions) . ' ملیۆن';
                    if ($remainder > 0) $res .= ' و ' . numberToKurdishWords($remainder);
                    return $res;
                }

                if ($number >= 1000) {
                    $thousands = floor($number / 1000);
                    $remainder = $number % 1000;
                    $res = ($thousands == 1 ? '' : numberToKurdishWords($thousands) . ' ') . 'هەزار';
                    if ($remainder > 0) $res .= ' و ' . numberToKurdishWords($remainder);
                    return $res;
                }

                if ($number >= 100) {
                    $h = floor($number / 100);
                    $remainder = $number % 100;
                    $res = $hundreds[$h];
                    if ($remainder > 0) $res .= ' و ' . numberToKurdishWords($remainder);
                    return $res;
                }

                if ($number >= 20) {
                    $t = floor($number / 10);
                    $remainder = $number % 10;
                    $res = $tens[$t];
                    if ($remainder > 0) $res .= ' و ' . $units[$remainder];
                    return $res;
                }

                if ($number >= 10) {
                    return $teens[$number - 10];
                }

                return $units[$number];
            }
        }

        $rasmAmount = (float)($transaction->pay_amount_years + $transaction->pay_inspection);
        $hasFine = $transaction->pay_fine > 0;
    @endphp

    <!-- Top Toolbar (Hidden during printing) -->
    <div class="no-print sticky top-0 z-50 bg-slate-900 text-white p-4 shadow-2xl border-b border-slate-700">
        <div class="max-w-5xl mx-auto flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-3 space-x-reverse">
                <span class="bg-emerald-500/20 text-emerald-300 px-3 py-1 rounded-xl border border-emerald-500/40 text-xs font-black flex items-center">
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    چاپی پسوولەی پارە وەرگرتن (۱۳۷)
                </span>
                <span class="text-xs text-slate-300 font-bold">
                    ژمارەی پسوولە: <strong class="text-emerald-400 font-mono text-sm">{{ $transaction->receipt_37a_number ?? '---' }}</strong>
                </span>
            </div>

            <!-- Print Mode Selectors & Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-slate-800 p-1 rounded-xl border border-slate-700 flex items-center text-xs">
                    <button type="button" id="btnModePreprinted" onclick="setPrintMode('preprinted')" 
                            class="px-3 py-1.5 rounded-lg font-bold transition bg-emerald-600 text-white shadow">
                        🖨️ چاپی سەر پسوولەی ئامادەکراو
                    </button>
                    <button type="button" id="btnModeFull" onclick="setPrintMode('full')" 
                            class="px-3 py-1.5 rounded-lg font-bold transition text-slate-400 hover:text-white">
                        📄 چاپی پسوولەی کامل (لەگەڵ چوارچێوە)
                    </button>
                </div>

                <!-- Manual Offset Fine-Tuning -->
                <div class="flex items-center space-x-2 space-x-reverse bg-slate-800 px-3 py-1 rounded-xl border border-slate-700 text-xs">
                    <span class="text-slate-400 text-[11px] font-bold">ڕێکخستنی شوێن (ملم):</span>
                    <label class="text-[11px] text-slate-300">سەرەوە:
                        <input type="number" id="offTop" value="0" step="1" onchange="adjustOffset()" class="w-12 bg-slate-900 border border-slate-600 rounded px-1 text-center font-mono text-white text-xs">
                    </label>
                    <label class="text-[11px] text-slate-300">ڕاست:
                        <input type="number" id="offRight" value="0" step="1" onchange="adjustOffset()" class="w-12 bg-slate-900 border border-slate-600 rounded px-1 text-center font-mono text-white text-xs">
                    </label>
                </div>

                <button onclick="window.print()" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center">
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    چاپکردن (Print)
                </button>

                <button onclick="window.close()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition">
                    داخستن
                </button>
            </div>
        </div>
    </div>

    <!-- RECEIPT SHEET CONTAINER (16.5cm x 23.0cm) -->
    <div id="receiptSheet" class="receipt-sheet">

        <!-- OPTIONAL BACKGROUND TEMPLATE FRAME (Showed only in Full mode) -->
        <div class="bg-template-frame absolute inset-0 border-2 border-black p-4 text-black text-xs font-bold pointer-events-none" style="display: none;">
            <!-- Pre-printed Header Text -->
            <div class="flex justify-between items-start border-b border-black pb-2">
                <div class="space-y-1 text-right">
                    <p class="font-extrabold text-sm">حکومەتی هەرێمی کوردستان</p>
                    <p>وەزارەتی دارایی و ئابووری</p>
                    <p>بەڕێوەبەرایەتی گشتی گومرگ</p>
                    <p>بەڕێوەبەرایەتی گومرگی سلێمانی</p>
                </div>
                <div class="text-center space-y-1">
                    <h2 class="text-base font-black border border-black px-2 py-0.5">پسوولەی پارە وەرگرتن / ژمێرکاری ۱۳۷ تایبەت بە گومرگەکان</h2>
                </div>
                <div class="space-y-1 text-left font-mono">
                    <p>ژمارە : </p>
                    <p>ڕێکەوت : ۲۰۲ / / </p>
                    <p class="text-[10px] text-gray-600">وێنەی پارەدەەر</p>
                </div>
            </div>

            <!-- Table Lines Structure -->
            <div class="mt-4 border-2 border-black rounded">
                <div class="flex border-b border-black bg-gray-100 font-black text-center py-1">
                    <div class="w-[70%] border-l border-black">وورده‌كاری</div>
                    <div class="w-[30%]">بڕی پارە</div>
                </div>
                <div class="flex h-[13cm]">
                    <div class="w-[70%] border-l border-black p-2"></div>
                    <div class="w-[30%] p-2"></div>
                </div>
                <div class="border-t border-black p-1 flex justify-between font-black">
                    <span>تەنها:</span>
                </div>
            </div>

            <!-- Signatures Footer -->
            <div class="flex justify-between text-center mt-6 pt-2 border-t border-black font-extrabold text-[11px]">
                <div>خەزنەدار</div>
                <div>ووردبینی</div>
                <div>لێپرسراو</div>
            </div>
        </div>

        <!-- PRINTABLE DATA CONTENT (EXACT ABSOLUTE POSITIONING OVER PRE-PRINTED FORM) -->
        <div id="printableArea" class="printable-content text-black font-bold">

            <!-- 1. Top-Right Barcode (بارکۆد لە سەرەوەی لای ڕاستی پەڕەکە) -->
            <div style="position: absolute; top: 0.8cm; right: 0.8cm; width: 5.2cm; text-align: center;">
                <svg id="paymentBarcodeSvg" style="max-height: 1.1cm; margin: 0 auto;"></svg>
            </div>

            <!-- 2. Top-Left Receipt Number (ژمارەی پسوولەکە دووبارە ژمارەکەی خۆی لە ئاستی ژمارە) -->
            <div style="position: absolute; top: 1.6cm; left: 0.8cm; width: 3.5cm; text-align: right;">
                <span style="font-size: 16px; font-weight: 900; font-family: monospace; color: #000000; letter-spacing: 0.5px;">
                    {{ toKurdishDigits($transaction->receipt_37a_number ?? $transaction->barcode) }}
                </span>
            </div>

            <!-- 3. Top-Left Date (لە ڕێکەوتەکە بەروار بنوسێت بە ڕەنووسی کوردی) -->
            <div style="position: absolute; top: 2.5cm; left: 0.8cm; width: 3.8cm; text-align: right;">
                <span style="font-size: 13px; font-weight: 800; font-family: monospace; color: #000000;">
                    {{ toKurdishDigits($transaction->paid_at ? $transaction->paid_at->format('Y / m / d') : date('Y / m / d')) }}
                </span>
            </div>

            <!-- 4. Citizen Full Name (لە ئاستی ناوی سیانی ناوی هاوڵاتیەکە بنوسێت) -->
            <div style="position: absolute; top: 4.4cm; right: 4.2cm; left: 0.8cm; text-align: right;">
                <span style="font-size: 15px; font-weight: 900; color: #000000;">
                    {{ $transaction->visitor_name }}
                </span>
            </div>

            <!-- 5A. Narrow Right Column (بڕی پارە) - ONLY numerical amounts written inside the narrow box -->
            <div style="position: absolute; top: 6.6cm; right: 0.8cm; width: 4.0cm; text-align: center; line-height: 2.1;">
                
                <!-- Fee amount -->
                <div style="font-family: monospace; font-size: 15px; font-weight: 900; color: #000000;">
                    {{ toKurdishDigits(number_format($rasmAmount)) }}
                </div>

                <!-- Penalty amount if fine > 0 -->
                @if($hasFine)
                    <div style="font-family: monospace; font-size: 15px; font-weight: 900; color: #000000;">
                        {{ toKurdishDigits(number_format($transaction->pay_fine)) }}
                    </div>
                @endif

                <!-- Stamp amount -->
                <div style="font-family: monospace; font-size: 15px; font-weight: 900; color: #000000;">
                    {{ toKurdishDigits(number_format($transaction->pay_stamp)) }}
                </div>

                <!-- Form amount -->
                <div style="font-family: monospace; font-size: 15px; font-weight: 900; color: #000000;">
                    {{ toKurdishDigits(number_format($transaction->pay_form)) }}
                </div>

            </div>

            <!-- 5B. Labels (ڕەسم، سزا، پول، فۆڕم) - Positioned on the left side of the narrow column boundary -->
            <div style="position: absolute; top: 6.6cm; right: 4.9cm; width: 1.8cm; text-align: right; line-height: 2.1; font-size: 13.5px; font-weight: 900; color: #000000;">
                <div>ڕەسم:</div>
                @if($hasFine)
                    <div>سزا:</div>
                @endif
                <div>پوول:</div>
                <div>فۆڕم:</div>
            </div>

            <!-- 6. Left Wide Column (وورده‌كاری) - Transaction details, years, car info -->
            <div style="position: absolute; top: 6.6cm; right: 6.8cm; left: 0.8cm; text-align: right; line-height: 2.0; font-size: 13.5px;">
                
                <!-- Transaction type & years -->
                <div style="font-weight: 900; color: #000000;">
                    جۆری مامەڵە: <span>{{ $transaction->transactionType->name_kurdish ?? '' }}</span>
                    @if($transaction->requiresBooklet() || $transaction->num_years > 0)
                        <span style="margin-right: 6px;">(بڕی ساڵ: <strong>{{ toKurdishDigits($transaction->num_years) }} ساڵ</strong>)</span>
                    @endif
                </div>

                <!-- Car Make, Plate Number & Type -->
                <div style="font-weight: 800; margin-top: 4px;">
                    جۆری ئۆتۆمبێل: <span>{{ $transaction->carMake->name_kurdish ?? '-' }}</span>
                </div>

                <div style="font-weight: 900; margin-top: 4px;">
                    ژمارە و تابلۆ: <span style="font-family: monospace; font-size: 14px;">{{ toKurdishDigits($transaction->plate_number) }}</span>
                    @if($transaction->plateType)
                        <span style="font-size: 13px; font-weight: 800;">({{ $transaction->plateType->name_kurdish }})</span>
                    @endif
                </div>

                @if($transaction->chassis_number)
                    <div style="font-size: 12px; font-weight: 700; color: #333333; margin-top: 2px;">
                        ژمارەی شاسی: <span style="font-family: monospace;">{{ $transaction->chassis_number }}</span>
                    </div>
                @endif

            </div>

            <!-- 7. Total Amount Line (تەنها: بڕی پارەکە بە ڕەنووسی کوردی و وشەی کوردی) -->
            <div style="position: absolute; top: 17.5cm; right: 2.2cm; left: 0.8cm; text-align: right;">
                <span style="font-size: 14px; font-weight: 900; color: #000000;">
                    <strong style="font-family: monospace; font-size: 15px; margin-left: 8px;">{{ toKurdishDigits(number_format($transaction->total_pay)) }}</strong>
                    ({{ numberToKurdishWords($transaction->total_pay) }} دیناری عێراقی)
                </span>
            </div>

            <!-- 8. Bottom Signatures & Names with Dates (ناوی خەملێنەر و وردبین و پارەوەربگر لەگەڵ بەروار) -->
            <div style="position: absolute; top: 19.8cm; right: 0.8cm; left: 0.8cm; display: flex; justify-content: space-between; text-align: center; font-size: 12px; font-weight: 900; color: #000000;">
                
                <!-- 8A. Cashier / Treasurer (خەزنەدار / پارەوەربگر) -->
                <div style="width: 4.8cm;">
                    <div style="font-size: 13px; font-weight: 900;">{{ $transaction->paid_by ?? auth()->user()->name }}</div>
                    <div style="font-size: 10px; font-family: monospace; font-weight: 800; color: #333333; margin-top: 2px;">
                        {{ toKurdishDigits($transaction->paid_at ? $transaction->paid_at->format('Y/m/d') : date('Y/m/d')) }}
                    </div>
                </div>

                <!-- 8B. Auditor (ووردبینی / وردبین) -->
                <div style="width: 4.8cm;">
                    <div style="font-size: 13px; font-weight: 900;">{{ $transaction->audited_by ?? 'کارمەندی وردبین' }}</div>
                    <div style="font-size: 10px; font-family: monospace; font-weight: 800; color: #333333; margin-top: 2px;">
                        {{ $transaction->audited_at ? toKurdishDigits($transaction->audited_at->format('Y/m/d')) : toKurdishDigits(date('Y/m/d')) }}
                    </div>
                </div>

                <!-- 8C. Estimator / Responsible (لێپرسراو / خەملێنەر) -->
                <div style="width: 4.8cm;">
                    <div style="font-size: 13px; font-weight: 900;">{{ $transaction->inspected_by ?? 'ئەندازیاری کەشف' }}</div>
                    <div style="font-size: 10px; font-family: monospace; font-weight: 800; color: #333333; margin-top: 2px;">
                        {{ $transaction->inspected_at ? toKurdishDigits($transaction->inspected_at->format('Y/m/d')) : toKurdishDigits(date('Y/m/d')) }}
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Render Barcode
            JsBarcode("#paymentBarcodeSvg", "{{ $transaction->barcode }}", {
                format: "CODE128",
                lineColor: "#000000",
                width: 1.5,
                height: 28,
                displayValue: true,
                fontSize: 10,
                fontOptions: "bold",
                font: "Vazirmatn",
                textMargin: 1
            });

            // Load stored offsets if any
            const savedTop = localStorage.getItem('receipt_off_top') || '0';
            const savedRight = localStorage.getItem('receipt_off_right') || '0';
            document.getElementById('offTop').value = savedTop;
            document.getElementById('offRight').value = savedRight;
            adjustOffset();

            // Load saved print mode
            const savedMode = localStorage.getItem('receipt_print_mode') || 'preprinted';
            setPrintMode(savedMode);
        });

        function setPrintMode(mode) {
            const body = document.body;
            const btnPre = document.getElementById('btnModePreprinted');
            const btnFull = document.getElementById('btnModeFull');

            if (mode === 'full') {
                body.classList.remove('mode-preprinted');
                body.classList.add('mode-full');
                btnFull.className = 'px-3 py-1.5 rounded-lg font-bold transition bg-emerald-600 text-white shadow';
                btnPre.className = 'px-3 py-1.5 rounded-lg font-bold transition text-slate-400 hover:text-white';
            } else {
                body.classList.remove('mode-full');
                body.classList.add('mode-preprinted');
                btnPre.className = 'px-3 py-1.5 rounded-lg font-bold transition bg-emerald-600 text-white shadow';
                btnFull.className = 'px-3 py-1.5 rounded-lg font-bold transition text-slate-400 hover:text-white';
            }
            localStorage.setItem('receipt_print_mode', mode);
        }

        function adjustOffset() {
            const topVal = parseFloat(document.getElementById('offTop').value) || 0;
            const rightVal = parseFloat(document.getElementById('offRight').value) || 0;
            const area = document.getElementById('printableArea');

            area.style.transform = `translate(${rightVal * -1}mm, ${topVal}mm)`;

            localStorage.setItem('receipt_off_top', topVal);
            localStorage.setItem('receipt_off_right', rightVal);
        }
    </script>
</body>
</html>
