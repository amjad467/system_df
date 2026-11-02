<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ویزاردی دیزاین و چاپی پسوولە ۱۳۷ (۳۷/أ) - {{ $transaction->receipt_37a_number ?? $transaction->barcode }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&family=Courier+Prime:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --offset-x: 0mm;
            --offset-y: 0mm;
            --bg-offset-x: 0mm;
            --bg-offset-y: 0mm;
            --bg-opacity: 1;
            --receipt-font-size: 13.5px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background: #0b1120;
            color: #f1f5f9;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Physical Sheet: 16.0cm width x 23.5cm height (160mm x 235mm) */
        .sheet-container {
            width: 160mm;
            height: 235mm;
            position: relative;
            background: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            margin: 15px auto;
            overflow: hidden;
            box-sizing: border-box;
            border-radius: 4px;
        }

        /* Background template for alignment */
        .sheet-bg-template {
            position: absolute;
            top: var(--bg-offset-y, 0mm);
            left: var(--bg-offset-x, 0mm);
            width: 160mm;
            height: 235mm;
            pointer-events: none;
            opacity: var(--bg-opacity, 1);
            transition: opacity 0.2s ease, left 0.15s ease, top 0.15s ease;
            z-index: 10;
        }

        /* Millimeter Grid overlay */
        .sheet-ruler-grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 160mm;
            height: 235mm;
            pointer-events: none;
            z-index: 25;
            display: none;
            background-size: 10mm 10mm;
            background-image: 
                linear-gradient(to right, rgba(99, 102, 241, 0.15) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(99, 102, 241, 0.15) 1px, transparent 1px);
        }

        body.show-ruler-grid .sheet-ruler-grid {
            display: block;
        }

        /* Printable Layer */
        .printable-content-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 160mm;
            height: 235mm;
            transform: translate(var(--offset-x), var(--offset-y));
            z-index: 30;
            direction: rtl !important;
        }

        /* Calibrated Draggable & Clickable Field */
        .calibrated-field {
            position: absolute;
            font-weight: 800;
            color: #000000;
            line-height: 1.25;
            white-space: nowrap;
            user-select: none;
            padding: 1.5px 5px;
            border-radius: 4px;
            cursor: grab;
            font-size: var(--receipt-font-size);
            transition: background 0.15s ease, box-shadow 0.15s ease, outline 0.15s ease;
        }

        .calibrated-field:active {
            cursor: grabbing;
        }

        .calibrated-field:hover {
            background: rgba(59, 130, 246, 0.25);
            outline: 1.5px dashed #2563eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15);
        }

        .calibrated-field.selected-field {
            background: rgba(245, 158, 11, 0.35) !important;
            outline: 2px solid #d97706 !important;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.25) !important;
            z-index: 50 !important;
        }

        .calibrated-field.field-hidden {
            display: none !important;
        }

        /* Coordinate Tooltip while dragging */
        #dragTooltip {
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            background: #1e1b4b;
            color: #a5b4fc;
            border: 1px solid #6366f1;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-family: monospace;
            font-weight: bold;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
            display: none;
        }

        /* Studio Sidebars Collapse & Transitions */
        #studioLeftSidebar, #studioRightSidebar {
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), 
                        padding 0.25s cubic-bezier(0.4, 0, 0.2, 1), 
                        opacity 0.2s ease;
        }

        #studioLeftSidebar.collapsed {
            width: 0 !important;
            min-width: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
            border-left: none !important;
            overflow: hidden !important;
        }

        #studioRightSidebar.collapsed {
            width: 0 !important;
            min-width: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
            border-right: none !important;
            overflow: hidden !important;
        }

        /* Floating Edge Dock buttons when sidebars are collapsed */
        .edge-dock-btn {
            position: fixed;
            top: 72px;
            z-index: 45;
            background: #1e293b;
            color: #f8fafc;
            border: 1px solid #4f46e5;
            padding: 7px 14px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 800;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.6);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .edge-dock-btn:hover {
            background: #4f46e5;
            color: #ffffff;
            transform: scale(1.06);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.6);
        }

        #leftDockBtn {
            left: 16px;
        }

        #rightDockBtn {
            right: 16px;
        }

        /* Print Media Setup */
        @media print {
            .no-print,
            .edge-dock-btn,
            #dragTooltip,
            .sheet-ruler-grid {
                display: none !important;
            }

            @page {
                size: 160mm 235mm !important;
                margin: 0mm !important;
            }

            html, body {
                width: 160mm !important;
                height: 235mm !important;
                margin: 0mm !important;
                padding: 0mm !important;
                background: #ffffff !important;
                overflow: visible !important;
                display: block !important;
            }

            #canvasWorkspace {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                width: 160mm !important;
                height: auto !important;
                background: transparent !important;
            }

            .sheet-container {
                position: relative !important;
                box-shadow: none !important;
                margin: 0mm !important;
                padding: 0mm !important;
                width: 160mm !important;
                height: 235mm !important;
                display: block !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                border-radius: 0 !important;
                top: 0 !important;
                left: 0 !important;
            }

            .sheet-bg-template {
                display: none !important;
            }

            body.show-template-on-print .sheet-bg-template {
                display: block !important;
                opacity: 0.35 !important;
            }

            .printable-content-layer {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 160mm !important;
                height: 235mm !important;
                transform: translate(var(--offset-x), var(--offset-y)) !important;
            }

            .calibrated-field {
                color: #000000 !important;
                background: transparent !important;
                outline: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

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

        $rasmAmount = ($transaction->pay_amount_years ?? 0) + ($transaction->pay_inspection ?? 0);
        $hasFine = ($transaction->pay_fine > 0);
    @endphp

    <!-- Floating Tooltip for Dragging Coordinates -->
    <div id="dragTooltip">X: 0mm | Y: 0mm</div>

    <!-- ========================================================================= -->
    <!-- TOP ACTION / WIZARD STUDIO TOOLBAR -->
    <!-- ========================================================================= -->
    <header class="no-print sticky top-0 z-50 bg-slate-900 border-b border-slate-700/80 px-4 py-2.5 shadow-2xl flex flex-wrap items-center justify-between gap-3">
        
        <!-- Left: Identity -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-indigo-600 flex items-center justify-center font-black text-white text-base shadow-lg shadow-indigo-500/30">
                🧾
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-sm font-black text-white">ویزاردی دیزاین و چاپی پسوولە ۱۳۷ (۳۷/أ)</h1>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-mono">
                        {{ $transaction->receipt_37a_number ?? $transaction->barcode }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-400">هاووڵاتی: <strong class="text-slate-200">{{ $transaction->visitor_name }}</strong> • تابلۆ: <strong class="text-amber-300 font-mono">{{ $transaction->plate_number }}</strong></p>
            </div>
        </div>

        <!-- Right: Studio Actions & Print -->
        <div class="flex items-center gap-2">
            
            <!-- Master Tools Visibility Toggle -->
            <button id="toggleAllToolsBtn" onclick="toggleAllPanels()" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-indigo-300 hover:text-white text-xs font-bold rounded-xl border border-indigo-500/40 transition flex items-center gap-1.5 shadow" title="شاردنەوە یان دەرخستنەوەی پەنجەرەکانی دیزاین (کلیلی H)">
                <span id="allToolsIcon">👁️</span>
                <span id="allToolsText">شاردنەوەی پەنجەرەکان</span>
            </button>

            <!-- Ruler Grid Toggle -->
            <button id="toggleRulerBtn" onclick="toggleRulerGrid()" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1" title="پیشاندان یان شاردنەوەی ڕاستەی میلیمه‌تری">
                <span>📏</span>
                <span>ڕاستە</span>
            </button>

            <!-- Cliché Overlay Toggle -->
            <button id="topBgToggleBtn" onclick="toggleTemplateOverlay()" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-300 hover:text-white text-xs font-bold rounded-xl border border-amber-500/40 transition flex items-center gap-1" title="پیشاندان یان شاردنەوەی کلیشەی پسوولەی ئامادەکراو">
                <span>🖼️</span>
                <span id="topBgToggleText">کلیشە: دیارە</span>
            </button>

            <!-- Save To LocalStorage -->
            <button onclick="savePositions()" id="saveServerBtn" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-amber-600/30 transition flex items-center gap-1.5" title="پاشەکەوتکردنی شوێنی هەموو فێڵدەکان بۆ هەموو پسوولەکانی تر">
                <span>💾</span>
                <span>پاشەکەوتکردن</span>
            </button>

            <!-- Reset to Defaults -->
            <button onclick="resetDefaults()" class="px-2.5 py-1.5 bg-slate-800 hover:bg-rose-900/40 text-slate-300 hover:text-rose-200 text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1" title="گێڕانەوەی شوێنی هەموو خانەکان بۆ باری سەرەتایی کارگە">
                <span>🔄</span>
                <span>ڕێسێت</span>
            </button>

            <!-- Print Button -->
            <button onclick="triggerPrint()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>چاپکردن</span>
            </button>

            <!-- Close Button -->
            <button onclick="window.close()" class="px-2.5 py-1.5 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white text-xs font-bold rounded-xl transition">
                داخستن
            </button>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- MAIN WORKSPACE LAYOUT (3-COLUMN STUDIO) -->
    <!-- ========================================================================= -->
    <div class="flex-1 flex overflow-hidden relative">
        
        <!-- Floating Edge Pills to reopen sidebars when collapsed -->
        <button id="leftDockBtn" onclick="toggleLeftSidebar(true)" class="edge-dock-btn no-print hidden" title="کردنەوەی پەنجەرەی پشکنەر (کلیلی H)">
            <span>🎯</span>
            <span>پشکنەر</span>
            <span>◀</span>
        </button>

        <button id="rightDockBtn" onclick="toggleRightSidebar(true)" class="edge-dock-btn no-print hidden" title="کردنەوەی لیستی خانەکان (کلیلی H)">
            <span>▶</span>
            <span>📑</span>
            <span>خانەکان</span>
        </button>

        <!-- ===================================================================== -->
        <!-- LEFT SIDEBAR: PROPERTY INSPECTOR (پشکنەری تایبەتمەندییەکان) -->
        <!-- ===================================================================== -->
        <aside id="studioLeftSidebar" class="no-print w-72 bg-slate-900 border-l border-slate-800 p-3.5 overflow-y-auto space-y-3.5 shadow-2xl flex-shrink-0 text-xs">
            
            <!-- SECTION 1: ACTIVE FIELD INSPECTOR -->
            <div class="bg-slate-950/70 rounded-2xl border border-indigo-500/30 p-3 shadow-inner space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <span class="font-black text-indigo-300 flex items-center gap-1.5">
                        <span>🎯</span> پشکنەری خانەی هەڵبژێردراو
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span id="selectedFieldBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-400">
                            هیچ خانەیەک هەڵنەبژێردراوە
                        </span>
                        <button onclick="toggleLeftSidebar(false)" class="w-5 h-5 flex items-center justify-center rounded-lg bg-slate-800 hover:bg-rose-900/60 text-slate-400 hover:text-white transition text-[11px]" title="شاردنەوەی پشکنەر">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- When NO field is selected -->
                <div id="noFieldSelectedNotice" class="text-slate-400 text-[11px] text-center py-4 space-y-1">
                    <p class="text-base">👆</p>
                    <p class="font-bold text-slate-300">خانەیەک هەڵبژێرە</p>
                    <p class="text-[10px] text-slate-500">کلیک لەسەر نووسینێک بکە لەسەر پسوولەکە، یان لە لیستی خانەکانی لای ڕاستەوە هەڵیبژێرە.</p>
                </div>

                <!-- When a FIELD IS SELECTED -->
                <div id="fieldInspectorControls" class="hidden space-y-3">
                    
                    <!-- Field Value Preview -->
                    <div class="bg-slate-900 p-2 rounded-xl border border-slate-800">
                        <div class="text-[10px] text-slate-400 font-bold" id="inspFieldLabel">ناوی خانە</div>
                        <div class="text-xs font-black text-amber-300 truncate mt-0.5" id="inspFieldValue">بەهای خانە</div>
                    </div>

                    <!-- Coordinates (Top & Left/Right in mm) -->
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Top (Vertical) -->
                        <div class="bg-slate-900 p-2 rounded-xl border border-slate-800 space-y-1">
                            <span class="text-[10px] text-slate-400 font-bold flex items-center justify-between">
                                <span>سەرەوە (Top):</span>
                            </span>
                            <div class="flex items-center justify-between gap-1">
                                <button onclick="stepActiveField('top', -1)" class="w-6 h-6 bg-slate-800 hover:bg-slate-700 text-white rounded font-bold">-</button>
                                <input type="number" step="0.5" id="inspFieldTopInput" onchange="updateActiveFieldFromInput()" class="w-14 bg-slate-950 text-center font-mono text-xs font-bold text-indigo-300 rounded border border-slate-700 py-0.5">
                                <button onclick="stepActiveField('top', 1)" class="w-6 h-6 bg-slate-800 hover:bg-slate-700 text-white rounded font-bold">+</button>
                            </div>
                        </div>

                        <!-- Horizontal (Left or Right) -->
                        <div class="bg-slate-900 p-2 rounded-xl border border-slate-800 space-y-1">
                            <span class="text-[10px] text-slate-400 font-bold flex items-center justify-between">
                                <span id="inspHorizLabel">ئاسۆیی:</span>
                                <button onclick="toggleActiveFieldHorizMode()" id="inspHorizToggleBtn" class="text-[9px] text-indigo-400 hover:underline">چەپ/ڕاست</button>
                            </span>
                            <div class="flex items-center justify-between gap-1">
                                <button onclick="stepActiveField('horiz', -1)" class="w-6 h-6 bg-slate-800 hover:bg-slate-700 text-white rounded font-bold">-</button>
                                <input type="number" step="0.5" id="inspFieldHorizInput" onchange="updateActiveFieldFromInput()" class="w-14 bg-slate-950 text-center font-mono text-xs font-bold text-indigo-300 rounded border border-slate-700 py-0.5">
                                <button onclick="stepActiveField('horiz', 1)" class="w-6 h-6 bg-slate-800 hover:bg-slate-700 text-white rounded font-bold">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- D-Pad Directional Arrow Controls -->
                    <div class="bg-slate-900 p-2 rounded-xl border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span class="font-bold">جوڵاندنی خێرا بە تیرەکان:</span>
                            <!-- Step Size Picker -->
                            <div class="flex items-center gap-1 bg-slate-950 px-1 py-0.5 rounded border border-slate-800">
                                <button onclick="setNudgeStep(0.5)" id="btnStep05" class="step-btn px-1 text-[9px] font-bold rounded bg-indigo-600 text-white">0.5</button>
                                <button onclick="setNudgeStep(1.0)" id="btnStep10" class="step-btn px-1 text-[9px] font-bold rounded text-slate-400">1.0</button>
                                <button onclick="setNudgeStep(5.0)" id="btnStep50" class="step-btn px-1 text-[9px] font-bold rounded text-slate-400">5.0</button>
                                <span class="text-[9px] text-slate-500">mm</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-1 max-w-[130px] mx-auto text-center">
                            <div></div>
                            <button onclick="dpadNudge(0, -1)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95" title="سەرەوە">⬆️</button>
                            <div></div>
                            <button onclick="dpadNudge(-1, 0)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95" title="ڕاست">➡️</button>
                            <div class="flex items-center justify-center text-[10px] text-slate-500">🎯</div>
                            <button onclick="dpadNudge(1, 0)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95" title="چەپ">⬅️</button>
                            <div></div>
                            <button onclick="dpadNudge(0, 1)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95" title="خوارەوە">⬇️</button>
                            <div></div>
                        </div>
                    </div>

                    <!-- Typography & Font Controls -->
                    <div class="bg-slate-900 p-2 rounded-xl border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold">
                            <span>فۆنت و نووسین:</span>
                            <span id="inspFontSizeDisplay" class="text-indigo-300 font-mono font-bold">14px</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button onclick="adjustActiveFontSize(-1)" class="w-6 h-6 bg-slate-800 hover:bg-slate-700 text-white rounded font-bold">-</button>
                            <input type="range" min="9" max="30" id="inspFontSizeSlider" oninput="onActiveFontSizeSlider(this.value)" class="flex-1 accent-indigo-500">
                            <button onclick="adjustActiveFontSize(1)" class="w-6 h-6 bg-slate-800 hover:bg-slate-700 text-white rounded font-bold">+</button>
                        </div>
                        
                        <!-- Font Weight Picker -->
                        <div class="grid grid-cols-3 gap-1 pt-1">
                            <button onclick="setActiveFontWeight('500')" class="px-1 py-1 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-normal">ئاسایی</button>
                            <button onclick="setActiveFontWeight('700')" class="px-1 py-1 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-bold">ئەستوور</button>
                            <button onclick="setActiveFontWeight('900')" class="px-1 py-1 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-black">ڕەش</button>
                        </div>
                    </div>

                    <!-- Deselect Button -->
                    <button onclick="deselectField()" class="w-full py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition">
                        لابردنی هەڵبژاردن
                    </button>
                </div>
            </div>

            <!-- SECTION 2: BACKGROUND CLICHÉ SETTINGS (بارکگراوند) -->
            <div class="bg-slate-950/70 rounded-2xl border border-amber-600/30 p-3 shadow-inner space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <span class="font-black text-amber-300 flex items-center gap-1.5">
                        <span>🖼️</span> وێنەی کلیشە (چوارچێوە)
                    </span>
                    <button onclick="toggleTemplateOverlay()" id="inspBgToggleBtn" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        👁️ دیارە
                    </button>
                </div>

                <!-- Cliché Opacity Slider -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold">
                        <span>ڕوونی (Opacity):</span>
                        <span id="bgOpacityVal" class="text-amber-300 font-mono">100%</span>
                    </div>
                    <input type="range" min="10" max="100" value="100" id="bgOpacitySlider" oninput="setBgOpacity(this.value)" class="w-full accent-amber-500">
                </div>

                <!-- Template Nudge Horizontal (Left / Right) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold">
                        <span>جوڵاندن بۆ چەپ/ڕاست:</span>
                        <span id="bgOffsetXDisplay" class="text-amber-300 font-mono font-bold">0 mm</span>
                    </div>
                    <div class="grid grid-cols-4 gap-1 text-center">
                        <button onclick="nudgeBgOffset('x', -5)" class="py-1 bg-amber-900/60 hover:bg-amber-800 text-amber-200 rounded font-bold text-[10px]" title="٥ ملم بۆ چەپ">⬅️ -5</button>
                        <button onclick="nudgeBgOffset('x', -1)" class="py-1 bg-slate-800 hover:bg-slate-700 text-amber-200 rounded font-bold text-[10px]" title="١ ملم بۆ چەپ">-1</button>
                        <button onclick="nudgeBgOffset('x', 1)" class="py-1 bg-slate-800 hover:bg-slate-700 text-amber-200 rounded font-bold text-[10px]" title="١ ملم بۆ ڕاست">+1</button>
                        <button onclick="nudgeBgOffset('x', 5)" class="py-1 bg-amber-900/60 hover:bg-amber-800 text-amber-200 rounded font-bold text-[10px]" title="٥ ملم بۆ ڕاست">+5 ➡️</button>
                    </div>
                </div>

                <!-- Template Nudge Vertical (Up / Down) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold">
                        <span>جوڵاندن بۆ سەرەوە/خوارەوە:</span>
                        <span id="bgOffsetYDisplay" class="text-amber-300 font-mono font-bold">0 mm</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1 text-center">
                        <button onclick="nudgeBgOffset('y', -1)" class="py-1 bg-slate-800 hover:bg-slate-700 text-amber-200 rounded font-bold text-[10px]">⬆️ سەرەوە (-1)</button>
                        <button onclick="nudgeBgOffset('y', 1)" class="py-1 bg-slate-800 hover:bg-slate-700 text-amber-200 rounded font-bold text-[10px]">⬇️ خوارەوە (+1)</button>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: TEST PRINT & GLOBAL OFFSET -->
            <div class="bg-slate-950/70 rounded-2xl border border-slate-800 p-3 shadow-inner space-y-2.5">
                <span class="font-bold text-slate-300 block border-b border-slate-800 pb-1.5">
                    تەنزیمی تەواوی پەڕە
                </span>
                
                <label class="flex items-center gap-2 cursor-pointer text-[11px] text-slate-300">
                    <input type="checkbox" id="testPrintCheck" onchange="toggleTestPrint(this.checked)" class="rounded text-indigo-600">
                    <span>چاپی ئەزموونی لەگەڵ چوارچێوە</span>
                </label>

                <!-- Global Offset Summary -->
                <div class="text-[10px] text-slate-400 flex items-center justify-between">
                    <span>تەنزیمی گشتی چاپ (X, Y):</span>
                    <span id="offsetSummary" class="text-indigo-300 font-mono font-bold">0mm, 0mm</span>
                </div>
            </div>

        </aside>

        <!-- ===================================================================== -->
        <!-- CENTER WORKSPACE CANVAS: RECEIPT SHEET (160mm x 235mm) -->
        <!-- ===================================================================== -->
        <main id="canvasWorkspace" class="flex-1 bg-slate-950 p-6 overflow-auto flex flex-col items-center justify-start">
            
            <section id="receiptSheet" class="sheet-container">
                
                <!-- BACKGROUND TEMPLATE FRAME (Visual receipt layout) -->
                <div id="bgTemplateFrame" class="sheet-bg-template border-2 border-black p-4 text-black text-xs font-bold pointer-events-none select-none">
                    <div class="flex justify-between items-start border-b border-black pb-2">
                        <div class="space-y-0.5 text-right text-[11px]">
                            <p class="font-black text-xs">حکومەتی هەرێمی کوردستان</p>
                            <p>وەزارەتی دارایی و ئابووری</p>
                            <p>بەڕێوەبەرایەتی گشتی گومرگ</p>
                            <p>بەڕێوەبەرایەتی گومرگی سلێمانی</p>
                        </div>
                        <div class="text-center space-y-1">
                            <h2 class="text-xs font-black border border-black px-2 py-0.5 bg-gray-100">پسوولەی پارە وەرگرتن / ژمێرکاری ۱۳۷ تایبەت بە گومرگەکان</h2>
                        </div>
                        <div class="space-y-0.5 text-left font-mono text-[10px]">
                            <p>ژمارە : </p>
                            <p>ڕێکەوت : ۲۰۲ / / </p>
                            <p class="text-[9px] text-gray-500">وێنەی پارەدەر</p>
                        </div>
                    </div>

                    <!-- Table Lines Structure -->
                    <div class="mt-3 border border-black rounded">
                        <div class="flex border-b border-black bg-gray-100 font-black text-center py-1 text-xs">
                            <div class="w-[68%] border-l border-black">وورده‌كاری</div>
                            <div class="w-[32%]">بڕی پارە</div>
                        </div>
                        <div class="flex h-[130mm]">
                            <div class="w-[68%] border-l border-black p-2"></div>
                            <div class="w-[32%] p-2"></div>
                        </div>
                        <div class="border-t border-black p-1.5 flex justify-between font-black text-xs">
                            <span>تەنها:</span>
                        </div>
                    </div>

                    <!-- Signatures Footer -->
                    <div class="flex justify-between text-center mt-5 pt-2 border-t border-black font-extrabold text-[11px]">
                        <div class="w-1/3">خەزنەدار</div>
                        <div class="w-1/3">ووردبینی</div>
                        <div class="w-1/3">لێپرسراو</div>
                    </div>
                </div>

                <div class="sheet-ruler-grid"></div>

                <!-- CALIBRATED PRINTABLE DATA CONTENT LAYER -->
                <div class="printable-content-layer">

                    <!-- 1. بارکۆد -->
                    <div id="f-rcpt-barcode" class="calibrated-field" data-fid="f-rcpt-barcode" data-name-ku="بارکۆدی پسوولە" style="top: 8mm; right: 6mm;">
                        <svg id="paymentBarcodeSvg" style="max-height: 11mm;"></svg>
                    </div>

                    <!-- 2. ژمارەی پسوولە -->
                    <div id="f-rcpt-number" class="calibrated-field font-mono font-black" data-fid="f-rcpt-number" data-name-ku="ژمارەی پسوولە" style="top: 16mm; left: 6mm; font-size: 16px;">
                        {{ toKurdishDigits($transaction->receipt_37a_number ?? $transaction->barcode) }}
                    </div>

                    <!-- 3. بەرواری پسوولە -->
                    <div id="f-rcpt-date" class="calibrated-field font-mono font-black" data-fid="f-rcpt-date" data-name-ku="بەرواری پسوولە" style="top: 25mm; left: 6mm; font-size: 13px;">
                        {{ toKurdishDigits($transaction->paid_at ? $transaction->paid_at->format('Y / m / d') : date('Y / m / d')) }}
                    </div>

                    <!-- 4. ناوی سیانی هاووڵاتی -->
                    <div id="f-rcpt-name" class="calibrated-field font-black" data-fid="f-rcpt-name" data-name-ku="ناوی سیانی هاووڵاتی" style="top: 44mm; right: 39mm; font-size: 15px;">
                        {{ $transaction->visitor_name }}
                    </div>

                    <!-- 5. بڕی پارەی ڕەسم -->
                    <div id="f-rcpt-rasm" class="calibrated-field font-mono font-black" data-fid="f-rcpt-rasm" data-name-ku="بڕی پارەی ڕەسم" style="top: 66mm; right: 14mm; font-size: 15px;">
                        {{ toKurdishDigits(number_format($rasmAmount)) }}
                    </div>

                    <!-- 6. وشەی (ڕەسم:) -->
                    <div id="f-lbl-rasm" class="calibrated-field font-black" data-fid="f-lbl-rasm" data-name-ku="وشەی (ڕەسم:)" style="top: 66mm; right: 44mm; font-size: 13.5px;">
                        ڕەسم:
                    </div>

                    <!-- 7. بڕی پارەی سزا (ئەگەر هەبێت) -->
                    @if($hasFine)
                        <div id="f-rcpt-fine" class="calibrated-field font-mono font-black" data-fid="f-rcpt-fine" data-name-ku="بڕی پارەی سزا" style="top: 75mm; right: 14mm; font-size: 15px;">
                            {{ toKurdishDigits(number_format($transaction->pay_fine)) }}
                        </div>
                        <div id="f-lbl-fine" class="calibrated-field font-black" data-fid="f-lbl-fine" data-name-ku="وشەی (سزا:)" style="top: 75mm; right: 44mm; font-size: 13.5px;">
                            سزا:
                        </div>
                    @endif

                    <!-- 8. بڕی پارەی پوول -->
                    <div id="f-rcpt-stamp" class="calibrated-field font-mono font-black" data-fid="f-rcpt-stamp" data-name-ku="بڕی پارەی پوول" style="top: {{ $hasFine ? '84mm' : '75mm' }}; right: 14mm; font-size: 15px;">
                        {{ toKurdishDigits(number_format($transaction->pay_stamp)) }}
                    </div>

                    <!-- 9. وشەی (پوول:) -->
                    <div id="f-lbl-stamp" class="calibrated-field font-black" data-fid="f-lbl-stamp" data-name-ku="وشەی (پوول:)" style="top: {{ $hasFine ? '84mm' : '75mm' }}; right: 44mm; font-size: 13.5px;">
                        پوول:
                    </div>

                    <!-- 10. بڕی پارەی فۆڕم -->
                    <div id="f-rcpt-form" class="calibrated-field font-mono font-black" data-fid="f-rcpt-form" data-name-ku="بڕی پارەی فۆڕم" style="top: {{ $hasFine ? '93mm' : '84mm' }}; right: 14mm; font-size: 15px;">
                        {{ toKurdishDigits(number_format($transaction->pay_form)) }}
                    </div>

                    <!-- 11. وشەی (فۆڕم:) -->
                    <div id="f-lbl-form" class="calibrated-field font-black" data-fid="f-lbl-form" data-name-ku="وشەی (فۆڕم:)" style="top: {{ $hasFine ? '93mm' : '84mm' }}; right: 44mm; font-size: 13.5px;">
                        فۆڕم:
                    </div>

                    <!-- 12. ووردەکاری: جۆری مامەڵە و ساڵ -->
                    <div id="f-dtl-txtype" class="calibrated-field font-black" data-fid="f-dtl-txtype" data-name-ku="ووردەکاری: جۆری مامەڵە" style="top: 66mm; right: 63mm; font-size: 13.5px;">
                        جۆری مامەڵە: <span>{{ $transaction->transactionType->name_kurdish ?? '' }}</span>
                        @if($transaction->requiresBooklet() || $transaction->num_years > 0)
                            <span class="mr-1">(بڕی ساڵ: <strong>{{ toKurdishDigits($transaction->num_years) }} ساڵ</strong>)</span>
                        @endif
                    </div>

                    <!-- 13. ووردەکاری: جۆری ئۆتۆمبێل -->
                    <div id="f-dtl-carmake" class="calibrated-field font-bold" data-fid="f-dtl-carmake" data-name-ku="ووردەکاری: جۆری ئۆتۆمبێل" style="top: 75mm; right: 63mm; font-size: 13.5px;">
                        جۆری ئۆتۆمبێل: <span>{{ $transaction->carMake->name_kurdish ?? '-' }}</span>
                    </div>

                    <!-- 14. ووردەکاری: ژمارەی تابلۆ -->
                    <div id="f-dtl-plate" class="calibrated-field font-black" data-fid="f-dtl-plate" data-name-ku="ووردەکاری: ژمارەی تابلۆ" style="top: 84mm; right: 63mm; font-size: 13.5px;">
                        ژمارە و تابلۆ: <span class="font-mono text-sm">{{ toKurdishDigits($transaction->plate_number) }}</span>
                        @if($transaction->plateType)
                            <span class="text-xs font-bold">({{ $transaction->plateType->name_kurdish }})</span>
                        @endif
                    </div>

                    <!-- 15. ووردەکاری: ژمارەی شاسی -->
                    @if($transaction->chassis_number)
                        <div id="f-dtl-chassis" class="calibrated-field font-bold font-mono" data-fid="f-dtl-chassis" data-name-ku="ووردەکاری: ژمارەی شاسی" style="top: 93mm; right: 63mm; font-size: 12px;">
                            ژمارەی شاسی: <span>{{ $transaction->chassis_number }}</span>
                        </div>
                    @endif

                    <!-- 16. کۆی گشتی پارە (بە ژمارە و بە وشەی کوردی) -->
                    <div id="f-rcpt-total" class="calibrated-field font-black" data-fid="f-rcpt-total" data-name-ku="کۆی گشتی پارە (ژمارە و وشە)" style="top: 179mm; right: 20mm; font-size: 14px;">
                        <strong class="font-mono text-base ml-2">{{ toKurdishDigits(number_format($transaction->total_pay)) }}</strong>
                        ({{ numberToKurdishWords($transaction->total_pay) }} دیناری عێراقی)
                    </div>

                    <!-- 17. خەزنەدار / پارەوەربگر -->
                    <div id="f-sign-cashier" class="calibrated-field text-center font-black" data-fid="f-sign-cashier" data-name-ku="ئیمزای خەزنەدار و بەروار" style="top: 203mm; right: 6mm; min-width: 44mm; font-size: 13px;">
                        <div>{{ $transaction->paid_by ?? auth()->user()?->name ?? 'کارمەندی خەزنە' }}</div>
                        <div class="text-[10px] font-mono text-gray-700 mt-0.5">{{ toKurdishDigits($transaction->paid_at ? $transaction->paid_at->format('Y/m/d') : date('Y/m/d')) }}</div>
                    </div>

                    <!-- 18. ووردبینی / وردبین -->
                    <div id="f-sign-auditor" class="calibrated-field text-center font-black" data-fid="f-sign-auditor" data-name-ku="ئیمزای وردبین و بەروار" style="top: 203mm; right: 58mm; min-width: 44mm; font-size: 13px;">
                        <div>{{ $transaction->audited_by ?? 'کارمەندی وردبین' }}</div>
                        <div class="text-[10px] font-mono text-gray-700 mt-0.5">{{ $transaction->audited_at ? toKurdishDigits($transaction->audited_at->format('Y/m/d')) : toKurdishDigits(date('Y/m/d')) }}</div>
                    </div>

                    <!-- 19. لێپرسراو / خەملێنەر -->
                    <div id="f-sign-estimator" class="calibrated-field text-center font-black" data-fid="f-sign-estimator" data-name-ku="ئیمزای خەمڵێنەر و بەروار" style="top: 203mm; right: 110mm; min-width: 44mm; font-size: 13px;">
                        <div>{{ $transaction->inspected_by ?? 'ئەندازیاری کەشف' }}</div>
                        <div class="text-[10px] font-mono text-gray-700 mt-0.5">{{ $transaction->inspected_at ? toKurdishDigits($transaction->inspected_at->format('Y/m/d')) : toKurdishDigits(date('Y/m/d')) }}</div>
                    </div>

                </div>

            </section>

        </main>

        <!-- ===================================================================== -->
        <!-- RIGHT SIDEBAR: FIELDS EXPLORER (لیستی خانەکان) -->
        <!-- ===================================================================== -->
        <aside id="studioRightSidebar" class="no-print w-64 bg-slate-900 border-r border-slate-800 p-3 overflow-y-auto flex-shrink-0 text-xs flex flex-col gap-2.5 shadow-2xl">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <span class="font-black text-slate-200 flex items-center gap-1.5">
                    <span>📑</span> خانەکانی پسوولە
                </span>
                <div class="flex items-center gap-1.5">
                    <span id="fieldsCountBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300">
                        19 خانە
                    </span>
                    <button onclick="toggleRightSidebar(false)" class="w-5 h-5 flex items-center justify-center rounded-lg bg-slate-800 hover:bg-rose-900/60 text-slate-400 hover:text-white transition text-[11px]" title="شاردنەوەی لیستی خانەکان">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Search Filter -->
            <div class="relative">
                <input type="text" id="fieldsSearchInput" oninput="filterFieldsList(this.value)" placeholder="گەڕان بەدوای خانەدا..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-2.5 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>

            <!-- Fields Dynamic List -->
            <div id="fieldsListContainer" class="flex-1 space-y-1.5 overflow-y-auto pr-0.5">
                <!-- Dynamically populated by JavaScript -->
            </div>

            <!-- Quick Tips Footer -->
            <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800/80 text-[10px] text-slate-500 space-y-1">
                <p class="font-bold text-slate-400">💡 فێڵە خێراکان:</p>
                <p>• بە ماوس دەقەکە ڕابکێشە جێگای تەواوی خۆی.</p>
                <p>• بە تیرەکانی کیبۆرد (Arrow Keys) ورد جوڵە بکە.</p>
                <p>• بە Shift + تیرەکان جوڵەی گەورەتر (2mm) بکە.</p>
            </div>
        </aside>

    </div>

    <!-- ========================================================================= -->
    <!-- WIZARD STUDIO JAVASCRIPT ENGINE -->
    <!-- ========================================================================= -->
    <script>
        const STORAGE_KEY = 'payment_receipt_custom_positions';
        const STORAGE_GLOBAL_KEY = 'payment_receipt_global_offsets';

        let showTemplateOnScreen = true;
        let testPrintWithBg = false;
        let showRulerGrid = false;
        let selectedField = null;
        let nudgeStep = 0.5; // mm

        // Offsets
        let offsetX = 0;
        let offsetY = 0;
        let bgOffsetX = 0;
        let bgOffsetY = 0;
        let bgOpacity = 100;
        let defaultFontSize = 13.5;

        // Custom positions dictionary
        let customPositions = {};

        function loadInitialState() {
            try {
                const globalSaved = localStorage.getItem(STORAGE_GLOBAL_KEY);
                if (globalSaved) {
                    const parsed = JSON.parse(globalSaved);
                    if (parsed.offsetX !== undefined) offsetX = parsed.offsetX;
                    if (parsed.offsetY !== undefined) offsetY = parsed.offsetY;
                    if (parsed.bgOffsetX !== undefined) bgOffsetX = parsed.bgOffsetX;
                    if (parsed.bgOffsetY !== undefined) bgOffsetY = parsed.bgOffsetY;
                    if (parsed.bgOpacity !== undefined) bgOpacity = parsed.bgOpacity;
                }
                customPositions = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            } catch(e) {
                console.error(e);
            }

            applyOffsets();
            applyBgOffsets();
            setBgOpacity(bgOpacity);
            applyAllCustomPositions();

            if (localStorage.getItem('receipt_left_collapsed') === 'true') {
                toggleLeftSidebar(false);
            }
            if (localStorage.getItem('receipt_right_collapsed') === 'true') {
                toggleRightSidebar(false);
            }

            buildFieldsExplorerList();
            initDragAndDrop();
            initBarcode();
        }

        function initBarcode() {
            try {
                JsBarcode("#paymentBarcodeSvg", "{{ $transaction->barcode }}", {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 1.5,
                    height: 28,
                    displayValue: true,
                    fontSize: 10,
                    fontOptions: "bold",
                    font: "Vazirmatn",
                    textMargin: 1
                });
            } catch(e) {}
        }

        function applyAllCustomPositions() {
            document.querySelectorAll('.calibrated-field').forEach(field => {
                const fid = field.getAttribute('data-fid');
                if (customPositions[fid]) {
                    const pos = customPositions[fid];
                    if (pos.top) field.style.top = pos.top;
                    if (pos.left) { field.style.left = pos.left; field.style.right = ''; }
                    if (pos.right) { field.style.right = pos.right; field.style.left = ''; }
                    if (pos.fontSize) field.style.fontSize = pos.fontSize;
                    if (pos.fontWeight) field.style.fontWeight = pos.fontWeight;
                    if (pos.hidden) field.classList.add('field-hidden');
                }
            });
        }

        function applyOffsets() {
            document.documentElement.style.setProperty('--offset-x', `${offsetX}mm`);
            document.documentElement.style.setProperty('--offset-y', `${offsetY}mm`);
            const sumEl = document.getElementById('offsetSummary');
            if (sumEl) sumEl.textContent = `${offsetX}mm, ${offsetY}mm`;
            saveGlobalOffsets();
        }

        function applyBgOffsets() {
            document.documentElement.style.setProperty('--bg-offset-x', `${bgOffsetX}mm`);
            document.documentElement.style.setProperty('--bg-offset-y', `${bgOffsetY}mm`);
            const xDisp = document.getElementById('bgOffsetXDisplay');
            const yDisp = document.getElementById('bgOffsetYDisplay');
            if (xDisp) xDisp.textContent = `${bgOffsetX > 0 ? '+' : ''}${bgOffsetX} mm`;
            if (yDisp) yDisp.textContent = `${bgOffsetY > 0 ? '+' : ''}${bgOffsetY} mm`;
            saveGlobalOffsets();
        }

        function nudgeBgOffset(axis, delta) {
            if (axis === 'x') bgOffsetX += delta;
            if (axis === 'y') bgOffsetY += delta;
            applyBgOffsets();
        }

        function setBgOpacity(val) {
            bgOpacity = parseInt(val, 10);
            document.documentElement.style.setProperty('--bg-opacity', bgOpacity / 100);
            const valEl = document.getElementById('bgOpacityVal');
            const sliderEl = document.getElementById('bgOpacitySlider');
            if (valEl) valEl.textContent = `${bgOpacity}%`;
            if (sliderEl) sliderEl.value = bgOpacity;
            saveGlobalOffsets();
        }

        function saveGlobalOffsets() {
            try {
                localStorage.setItem(STORAGE_GLOBAL_KEY, JSON.stringify({
                    offsetX, offsetY, bgOffsetX, bgOffsetY, bgOpacity
                }));
            } catch(e) {}
        }

        function toggleTemplateOverlay() {
            showTemplateOnScreen = !showTemplateOnScreen;
            const t = document.getElementById('bgTemplateFrame');
            if (t) t.style.display = showTemplateOnScreen ? 'block' : 'none';
            const btn = document.getElementById('inspBgToggleBtn');
            const topBtnText = document.getElementById('topBgToggleText');
            if (btn) btn.textContent = showTemplateOnScreen ? '👁️ دیارە' : '🙈 شاردراوە';
            if (topBtnText) topBtnText.textContent = showTemplateOnScreen ? 'کلیشە: دیارە' : 'کلیشە: شاردراوە';
        }

        function toggleRulerGrid() {
            showRulerGrid = !showRulerGrid;
            document.body.classList.toggle('show-ruler-grid', showRulerGrid);
            const btn = document.getElementById('toggleRulerBtn');
            if (btn) {
                btn.classList.toggle('bg-indigo-600', showRulerGrid);
                btn.classList.toggle('text-white', showRulerGrid);
            }
        }

        let leftSidebarCollapsed = false;
        let rightSidebarCollapsed = false;

        function toggleLeftSidebar(expand) {
            const sidebar = document.getElementById('studioLeftSidebar');
            const dockBtn = document.getElementById('leftDockBtn');
            if (expand === undefined) {
                leftSidebarCollapsed = !leftSidebarCollapsed;
            } else {
                leftSidebarCollapsed = !expand;
            }

            if (sidebar) sidebar.classList.toggle('collapsed', leftSidebarCollapsed);
            if (dockBtn) dockBtn.classList.toggle('hidden', !leftSidebarCollapsed);

            localStorage.setItem('receipt_left_collapsed', leftSidebarCollapsed);
            updateAllToolsBtnState();
        }

        function toggleRightSidebar(expand) {
            const sidebar = document.getElementById('studioRightSidebar');
            const dockBtn = document.getElementById('rightDockBtn');
            if (expand === undefined) {
                rightSidebarCollapsed = !rightSidebarCollapsed;
            } else {
                rightSidebarCollapsed = !expand;
            }

            if (sidebar) sidebar.classList.toggle('collapsed', rightSidebarCollapsed);
            if (dockBtn) dockBtn.classList.toggle('hidden', !rightSidebarCollapsed);

            localStorage.setItem('receipt_right_collapsed', rightSidebarCollapsed);
            updateAllToolsBtnState();
        }

        function toggleAllPanels() {
            if (leftSidebarCollapsed && rightSidebarCollapsed) {
                toggleLeftSidebar(true);
                toggleRightSidebar(true);
            } else {
                toggleLeftSidebar(false);
                toggleRightSidebar(false);
            }
        }

        function updateAllToolsBtnState() {
            const icon = document.getElementById('allToolsIcon');
            const text = document.getElementById('allToolsText');
            const bothCollapsed = leftSidebarCollapsed && rightSidebarCollapsed;

            if (bothCollapsed) {
                if (icon) icon.textContent = '🛠️';
                if (text) text.textContent = 'پیشاندانی پەنجەرەکان';
            } else {
                if (icon) icon.textContent = '👁️';
                if (text) text.textContent = 'شاردنەوەی پەنجەرەکان';
            }
        }

        function toggleTestPrint(isChecked) {
            testPrintWithBg = isChecked;
            document.body.classList.toggle('show-template-on-print', testPrintWithBg);
        }

        function selectField(field) {
            deselectField();
            selectedField = field;
            field.classList.add('selected-field');

            if (leftSidebarCollapsed) {
                toggleLeftSidebar(true);
            }

            document.getElementById('noFieldSelectedNotice')?.classList.add('hidden');
            document.getElementById('fieldInspectorControls')?.classList.remove('hidden');

            const fid = field.getAttribute('data-fid');
            const nameKu = field.getAttribute('data-name-ku') || fid;

            document.getElementById('selectedFieldBadge').textContent = nameKu;
            document.getElementById('selectedFieldBadge').className = 'text-[10px] font-black px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30';
            document.getElementById('inspFieldLabel').textContent = nameKu;
            document.getElementById('inspFieldValue').textContent = field.textContent.trim().substring(0, 35);

            const topVal = parseFloat(field.style.top) || 0;
            document.getElementById('inspFieldTopInput').value = topVal.toFixed(1);

            const isLeft = !!field.style.left;
            const horizVal = isLeft ? parseFloat(field.style.left) || 0 : parseFloat(field.style.right) || 0;
            document.getElementById('inspHorizLabel').textContent = isLeft ? 'چەپ (Left):' : 'ڕاست (Right):';
            document.getElementById('inspFieldHorizInput').value = horizVal.toFixed(1);

            const currentFs = parseInt(window.getComputedStyle(field).fontSize, 10) || defaultFontSize;
            document.getElementById('inspFontSizeDisplay').textContent = `${currentFs}px`;
            document.getElementById('inspFontSizeSlider').value = currentFs;

            document.querySelectorAll('.field-list-item').forEach(item => {
                if (item.getAttribute('data-fid') === fid) {
                    item.classList.add('bg-indigo-600/30', 'border-indigo-500', 'text-white');
                    item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    item.classList.remove('bg-indigo-600/30', 'border-indigo-500', 'text-white');
                }
            });
        }

        function deselectField() {
            if (selectedField) {
                selectedField.classList.remove('selected-field');
                selectedField = null;
            }
            document.getElementById('noFieldSelectedNotice')?.classList.remove('hidden');
            document.getElementById('fieldInspectorControls')?.classList.add('hidden');
            document.getElementById('selectedFieldBadge').textContent = 'هیچ خانەیەک هەڵنەبژێردراوە';
            document.getElementById('selectedFieldBadge').className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-400';
            document.querySelectorAll('.field-list-item').forEach(item => {
                item.classList.remove('bg-indigo-600/30', 'border-indigo-500', 'text-white');
            });
        }

        function stepActiveField(axis, direction) {
            if (!selectedField) return;
            const step = nudgeStep * direction;
            if (axis === 'top') {
                const current = parseFloat(selectedField.style.top) || 0;
                const next = (current + step).toFixed(1);
                selectedField.style.top = `${next}mm`;
                document.getElementById('inspFieldTopInput').value = next;
            } else if (axis === 'horiz') {
                if (selectedField.style.left) {
                    const current = parseFloat(selectedField.style.left) || 0;
                    const next = (current + step).toFixed(1);
                    selectedField.style.left = `${next}mm`;
                    document.getElementById('inspFieldHorizInput').value = next;
                } else {
                    const current = parseFloat(selectedField.style.right) || 0;
                    const next = (current - step).toFixed(1);
                    selectedField.style.right = `${next}mm`;
                    document.getElementById('inspFieldHorizInput').value = next;
                }
            }
            recordFieldPosition(selectedField);
            updateFieldListItemBadge(selectedField);
        }

        function updateActiveFieldFromInput() {
            if (!selectedField) return;
            const top = parseFloat(document.getElementById('inspFieldTopInput').value) || 0;
            const horiz = parseFloat(document.getElementById('inspFieldHorizInput').value) || 0;
            selectedField.style.top = `${top.toFixed(1)}mm`;
            if (selectedField.style.left) {
                selectedField.style.left = `${horiz.toFixed(1)}mm`;
            } else {
                selectedField.style.right = `${horiz.toFixed(1)}mm`;
            }
            recordFieldPosition(selectedField);
            updateFieldListItemBadge(selectedField);
        }

        function toggleActiveFieldHorizMode() {
            if (!selectedField) return;
            if (selectedField.style.left) {
                const val = parseFloat(selectedField.style.left) || 0;
                selectedField.style.right = `${val}mm`;
                selectedField.style.left = '';
            } else {
                const val = parseFloat(selectedField.style.right) || 0;
                selectedField.style.left = `${val}mm`;
                selectedField.style.right = '';
            }
            selectField(selectedField);
            recordFieldPosition(selectedField);
        }

        function setNudgeStep(step) {
            nudgeStep = step;
            document.querySelectorAll('.step-btn').forEach(btn => {
                btn.className = 'step-btn px-1 text-[9px] font-bold rounded text-slate-400';
            });
            if (step === 0.5) document.getElementById('btnStep05').className = 'step-btn px-1 text-[9px] font-bold rounded bg-indigo-600 text-white';
            if (step === 1.0) document.getElementById('btnStep10').className = 'step-btn px-1 text-[9px] font-bold rounded bg-indigo-600 text-white';
            if (step === 5.0) document.getElementById('btnStep50').className = 'step-btn px-1 text-[9px] font-bold rounded bg-indigo-600 text-white';
        }

        function dpadNudge(dx, dy) {
            if (!selectedField) return;
            const top = parseFloat(selectedField.style.top) || 0;
            selectedField.style.top = `${(top + (dy * nudgeStep)).toFixed(1)}mm`;

            if (selectedField.style.left) {
                const left = parseFloat(selectedField.style.left) || 0;
                selectedField.style.left = `${(left + (dx * nudgeStep)).toFixed(1)}mm`;
            } else {
                const right = parseFloat(selectedField.style.right) || 0;
                selectedField.style.right = `${(right - (dx * nudgeStep)).toFixed(1)}mm`;
            }

            selectField(selectedField);
            recordFieldPosition(selectedField);
            updateFieldListItemBadge(selectedField);
        }

        function adjustActiveFontSize(delta) {
            if (!selectedField) return;
            const current = parseInt(window.getComputedStyle(selectedField).fontSize, 10) || defaultFontSize;
            const next = Math.max(9, Math.min(36, current + delta));
            selectedField.style.fontSize = `${next}px`;
            document.getElementById('inspFontSizeDisplay').textContent = `${next}px`;
            document.getElementById('inspFontSizeSlider').value = next;
            recordFieldPosition(selectedField);
        }

        function onActiveFontSizeSlider(val) {
            if (!selectedField) return;
            selectedField.style.fontSize = `${val}px`;
            document.getElementById('inspFontSizeDisplay').textContent = `${val}px`;
            recordFieldPosition(selectedField);
        }

        function setActiveFontWeight(weight) {
            if (!selectedField) return;
            selectedField.style.fontWeight = weight;
            recordFieldPosition(selectedField);
        }

        function recordFieldPosition(field) {
            const fid = field.getAttribute('data-fid');
            customPositions[fid] = {
                top: field.style.top,
                left: field.style.left || null,
                right: field.style.right || null,
                fontSize: field.style.fontSize || null,
                fontWeight: field.style.fontWeight || null
            };
            savePositions(false);
        }

        function savePositions(showAlert = true) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(customPositions));
                saveGlobalOffsets();
                if (showAlert) {
                    alert('ڕێکخستنی شوێنی فێڵدەکان بە سەرکەوتوویی پاشەکەوتکرا! ✓');
                }
            } catch(e) {
                if (showAlert) alert('خەتایەک لە پاشەکەوتکردن ڕوویدا');
            }
        }

        function resetDefaults() {
            if (!confirm('ئایا دڵنیایت لە گێڕانەوەی تەواوی شوێنی فێڵدەکان بۆ باری بنەڕەتی سەرەتایی؟')) return;
            try {
                localStorage.removeItem(STORAGE_KEY);
                localStorage.removeItem(STORAGE_GLOBAL_KEY);
                location.reload();
            } catch(e) {}
        }

        function buildFieldsExplorerList() {
            const container = document.getElementById('fieldsListContainer');
            if (!container) return;
            container.innerHTML = '';

            const fields = document.querySelectorAll('.calibrated-field');
            const countBadge = document.getElementById('fieldsCountBadge');
            if (countBadge) countBadge.textContent = `${fields.length} خانە`;

            fields.forEach(f => {
                const fid = f.getAttribute('data-fid');
                const nameKu = f.getAttribute('data-name-ku') || fid;
                const val = f.textContent.trim().substring(0, 25);

                const item = document.createElement('div');
                item.className = 'field-list-item p-2 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-indigo-500/60 transition cursor-pointer flex flex-col gap-1';
                item.setAttribute('data-fid', fid);
                item.innerHTML = `
                    <div class="flex items-center justify-between">
                        <strong class="font-bold text-slate-200">${nameKu}</strong>
                        <span class="field-coords-badge text-[9px] font-mono text-indigo-400 bg-indigo-950 px-1.5 py-0.5 rounded border border-indigo-800">
                            ${f.style.top || '0mm'}
                        </span>
                    </div>
                    <div class="text-[10px] text-slate-500 truncate">${val}</div>
                `;

                item.addEventListener('click', () => {
                    selectField(f);
                });

                container.appendChild(item);
            });
        }

        function updateFieldListItemBadge(field) {
            const fid = field.getAttribute('data-fid');
            const item = document.querySelector(`.field-list-item[data-fid="${fid}"]`);
            if (item) {
                const badge = item.querySelector('.field-coords-badge');
                if (badge) {
                    const horiz = field.style.left ? `L:${field.style.left}` : `R:${field.style.right}`;
                    badge.textContent = `T:${field.style.top} ${horiz}`;
                }
            }
        }

        function filterFieldsList(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('.field-list-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(q) ? 'flex' : 'none';
            });
        }

        let isDragging = false;
        let startX, startY, origTop, origLeft, origRight;
        const tooltip = document.getElementById('dragTooltip');

        function initDragAndDrop() {
            document.querySelectorAll('.calibrated-field').forEach(field => {
                field.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectField(field);
                });

                field.addEventListener('mousedown', (e) => {
                    isDragging = true;
                    selectField(field);
                    startX = e.clientX;
                    startY = e.clientY;
                    origTop = parseFloat(field.style.top) || 0;
                    origLeft = field.style.left ? parseFloat(field.style.left) : null;
                    origRight = field.style.right ? parseFloat(field.style.right) : null;

                    if (tooltip) tooltip.style.display = 'block';

                    function onMouseMove(moveEvent) {
                        if (!isDragging) return;
                        // 1mm = ~3.7795px at 96 DPI
                        const dx = (moveEvent.clientX - startX) / 3.7795;
                        const dy = (moveEvent.clientY - startY) / 3.7795;

                        const newTop = (origTop + dy).toFixed(1);
                        field.style.top = `${newTop}mm`;
                        document.getElementById('inspFieldTopInput').value = newTop;

                        let horizDisplay = '';
                        if (origLeft !== null) {
                            const newLeft = (origLeft + dx).toFixed(1);
                            field.style.left = `${newLeft}mm`;
                            document.getElementById('inspFieldHorizInput').value = newLeft;
                            horizDisplay = `L: ${newLeft}mm`;
                        } else {
                            const newRight = (origRight - dx).toFixed(1);
                            field.style.right = `${newRight}mm`;
                            document.getElementById('inspFieldHorizInput').value = newRight;
                            horizDisplay = `R: ${newRight}mm`;
                        }

                        if (tooltip) {
                            tooltip.style.left = `${moveEvent.clientX + 14}px`;
                            tooltip.style.top = `${moveEvent.clientY - 24}px`;
                            tooltip.textContent = `T: ${newTop}mm | ${horizDisplay}`;
                        }
                    }

                    function onMouseUp() {
                        if (isDragging) {
                            isDragging = false;
                            if (tooltip) tooltip.style.display = 'none';
                            recordFieldPosition(field);
                            updateFieldListItemBadge(field);
                            window.removeEventListener('mousemove', onMouseMove);
                            window.removeEventListener('mouseup', onMouseUp);
                        }
                    }

                    window.addEventListener('mousemove', onMouseMove);
                    window.addEventListener('mouseup', onMouseUp);
                });
            });

            document.getElementById('canvasWorkspace')?.addEventListener('click', (e) => {
                if (!e.target.closest('.calibrated-field')) {
                    deselectField();
                }
            });
        }

        // Keyboard Arrow Keys Shortcut Support
        document.addEventListener('keydown', (e) => {
            if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;

            if (e.key === 'h' || e.key === 'H') {
                toggleAllPanels();
                return;
            }

            if (!selectedField) return;

            const step = e.shiftKey ? 2.0 : nudgeStep;

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                dpadNudge(0, -1);
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                dpadNudge(0, 1);
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                dpadNudge(1, 0);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                dpadNudge(-1, 0);
            } else if (e.key === '+' || e.key === '=') {
                e.preventDefault();
                adjustActiveFontSize(1);
            } else if (e.key === '-' || e.key === '_') {
                e.preventDefault();
                adjustActiveFontSize(-1);
            } else if (e.key === 'Escape') {
                deselectField();
            }
        });

        function triggerPrint() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', loadInitialState);
    </script>
</body>
</html>
