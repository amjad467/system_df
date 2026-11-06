<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ویزاردی دیزاین و چاپی محاسبة ٦٦ - بەڕێوەبەرایەتی گومرگی سلێمانی</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --offset-x: 0mm;
            --offset-y: 0mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background: #0b1120;
            color: #f1f5f9;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* A4 Landscape Page (297mm x 210mm) */
        .page {
            width: 297mm;
            min-height: 210mm;
            padding: 12mm 15mm;
            margin: 15px auto;
            background: #ffffff;
            color: #000000;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            position: relative;
            page-break-after: always;
            box-sizing: border-box;
            border-radius: 4px;
        }

        /* Calibrated Sections */
        .calibrated-field {
            position: relative;
            transition: background 0.15s ease, outline 0.15s ease, box-shadow 0.15s ease;
            cursor: grab;
            border-radius: 4px;
        }

        .calibrated-field:active {
            cursor: grabbing;
        }

        .calibrated-field:hover {
            background: rgba(59, 130, 246, 0.12);
            outline: 1.5px dashed #2563eb;
        }

        .calibrated-field.selected-field {
            background: rgba(245, 158, 11, 0.2) !important;
            outline: 2px solid #d97706 !important;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.25) !important;
            z-index: 50 !important;
        }

        /* Millimeter Grid overlay */
        .sheet-ruler-grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
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

        /* Tooltip */
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

        /* Sidebars Collapse */
        #studioLeftSidebar, #studioRightSidebar {
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), 
                        padding 0.25s cubic-bezier(0.4, 0, 0.2, 1), 
                        opacity 0.2s ease;
        }

        #studioLeftSidebar.collapsed {
            width: 0 !important;
            min-width: 0 !important;
            padding: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
            border-left: none !important;
            overflow: hidden !important;
        }

        #studioRightSidebar.collapsed {
            width: 0 !important;
            min-width: 0 !important;
            padding: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
            border-right: none !important;
            overflow: hidden !important;
        }

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
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .edge-dock-btn:hover {
            background: #4f46e5;
            color: #ffffff;
            transform: scale(1.06);
        }

        #leftDockBtn { left: 16px; }
        #rightDockBtn { right: 16px; }

        /* Report Header */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: bold;
        }

        .header-left {
            text-align: right;
            width: 30%;
        }
        .header-title-code {
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .header-datetime {
            font-size: 12px;
            font-family: monospace;
            font-weight: normal;
        }

        .header-center {
            text-align: center;
            width: 40%;
        }
        .header-dept-title {
            font-size: 17px;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .header-right {
            text-align: left;
            width: 30%;
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            gap: 15px;
        }

        .range-box-container {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        .range-box {
            border: 1px solid #4b5563;
            padding: 2px 16px;
            font-family: monospace;
            font-size: 14px;
            font-weight: bold;
            min-width: 90px;
            text-align: center;
            background: #fafafa;
        }

        .page-counter {
            font-size: 13px;
            font-weight: bold;
            font-family: monospace;
            margin-left: 10px;
        }

        /* Accounting Table */
        .accounting-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 11px;
            text-align: center;
            margin-top: 5px;
        }

        .accounting-table th, 
        .accounting-table td {
            border: 1px solid #000000;
            padding: 2.5px 3px;
            line-height: 1.15;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            vertical-align: middle;
        }

        .accounting-table tr {
            height: 22px !important;
            max-height: 22px !important;
        }

        .accounting-table th {
            background-color: #f1f5f9;
            font-weight: 800;
            font-size: 11px;
            color: #000000;
            height: 26px;
        }

        .accounting-table td.col-name {
            text-align: right;
            padding-right: 6px;
            font-weight: 600;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            max-width: 190px;
        }

        .accounting-table td.col-type {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            max-width: 100px;
        }

        .accounting-table td.col-notes {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            max-width: 90px;
        }

        .accounting-table td.font-num {
            font-family: monospace;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap !important;
        }

        .accounting-table tr.cancelled-row {
            color: #374151;
        }

        /* Summary Section - ALIGNED TO RIGHT SIDE IN RTL */
        .summary-container {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* In RTL dir="rtl", flex-start is strictly the RIGHT side! */
            font-size: 12px;
            font-weight: bold;
            width: 100%;
        }

        .summary-header-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: 900;
            text-align: right;
        }

        .summary-grid {
            width: 380px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3.5px 10px;
            font-size: 12px;
            border-bottom: 1px dotted #cbd5e1;
        }
        .summary-item.highlight-gray {
            background-color: #f1f5f9;
            font-weight: 800;
            font-size: 13px;
        }

        .summary-item .amount {
            font-family: monospace;
            font-size: 13px;
            font-weight: bold;
        }

        .tafqeet-line {
            margin-top: 15px;
            font-size: 13px;
            font-weight: 800;
            text-align: right;
            width: 380px;
        }

        /* Print Media Styles */
        @media print {
            .no-print,
            .edge-dock-btn,
            #dragTooltip,
            .sheet-ruler-grid {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            @page {
                size: A4 landscape !important;
                margin: 5mm !important;
            }

            #canvasWorkspace {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                width: 100% !important;
                background: transparent !important;
            }

            .page {
                box-shadow: none !important;
                margin: 0 auto 20px auto !important;
                padding: 6mm 8mm !important;
                width: 100% !important;
                min-height: auto !important;
                page-break-after: always !important;
                border-radius: 0 !important;
            }

            .calibrated-field {
                outline: none !important;
                background: transparent !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Floating Tooltip for Dragging Coordinates -->
    <div id="dragTooltip">X: 0mm | Y: 0mm</div>

    <!-- ========================================================================= -->
    <!-- TOP ACTION / WIZARD STUDIO TOOLBAR -->
    <!-- ========================================================================= -->
    <header class="no-print sticky top-0 z-50 bg-slate-900 border-b border-slate-700/80 px-4 py-2.5 shadow-2xl flex flex-wrap items-center justify-between gap-3">
        
        <!-- Left: Identity -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center font-black text-white text-base shadow-lg shadow-indigo-500/30">
                📊
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-sm font-black text-white">ویزاردی دیزاین و چاپی محاسبة ٦٦</h1>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        گومرگی سلێمانی
                    </span>
                </div>
                <p class="text-[10px] text-slate-400">کۆی پسولەکان: <strong class="text-white">{{ $totalReceiptsCount }}</strong> • کۆی گشتی: <strong class="text-amber-300 font-mono">{{ number_format($grandTotal) }} د.ع</strong></p>
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

            <!-- Return to report table -->
            <a href="{{ route('reports.accounting_66', request()->query()) }}" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl border border-slate-700 transition">
                گەڕانەوە بۆ خشتە
            </a>

            <!-- Save To LocalStorage -->
            <button onclick="savePositions()" id="saveServerBtn" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-amber-600/30 transition flex items-center gap-1.5" title="پاشەکەوتکردنی شوێنی هەموو بەشەکان">
                <span>💾</span>
                <span>پاشەکەوتکردن</span>
            </button>

            <!-- Reset to Defaults -->
            <button onclick="resetDefaults()" class="px-2.5 py-1.5 bg-slate-800 hover:bg-rose-900/40 text-slate-300 hover:text-rose-200 text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1" title="گێڕانەوە بۆ باری بنەڕەتی">
                <span>🔄</span>
                <span>ڕێسێت</span>
            </button>

            <!-- Print Button -->
            <button onclick="window.print()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
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

        <button id="rightDockBtn" onclick="toggleRightSidebar(true)" class="edge-dock-btn no-print hidden" title="کردنەوەی لیستی بەشەکان (کلیلی H)">
            <span>▶</span>
            <span>📑</span>
            <span>بەشەکان</span>
        </button>

        <!-- ===================================================================== -->
        <!-- LEFT SIDEBAR: PROPERTY INSPECTOR (پشکنەری تایبەتمەندییەکان) -->
        <!-- ===================================================================== -->
        <aside id="studioLeftSidebar" class="no-print w-72 bg-slate-900 border-l border-slate-800 p-3.5 overflow-y-auto space-y-3.5 shadow-2xl flex-shrink-0 text-xs">
            
            <!-- SECTION 1: ACTIVE FIELD INSPECTOR -->
            <div class="bg-slate-950/70 rounded-2xl border border-indigo-500/30 p-3 shadow-inner space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <span class="font-black text-indigo-300 flex items-center gap-1.5">
                        <span>🎯</span> پشکنەری بەشی هەڵبژێردراو
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span id="selectedFieldBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-400">
                            هیچ بەشێک هەڵنەبژێردراوە
                        </span>
                        <button onclick="toggleLeftSidebar(false)" class="w-5 h-5 flex items-center justify-center rounded-lg bg-slate-800 hover:bg-rose-900/60 text-slate-400 hover:text-white transition text-[11px]" title="شاردنەوەی پشکنەر">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- When NO field is selected -->
                <div id="noFieldSelectedNotice" class="text-slate-400 text-[11px] text-center py-4 space-y-1">
                    <p class="text-base">👆</p>
                    <p class="font-bold text-slate-300">بەشێک هەڵبژێرە</p>
                    <p class="text-[10px] text-slate-500">کلیک لەسەر هەر بەشێکی ڕاپۆرتەکە بکە (سەردێڕ، خشتە، پوختە)، یان لە لیستی بەشەکانی لای ڕاستەوە هەڵیبژێرە.</p>
                </div>

                <!-- When a FIELD IS SELECTED -->
                <div id="fieldInspectorControls" class="hidden space-y-3">
                    
                    <!-- Field Value Preview -->
                    <div class="bg-slate-900 p-2 rounded-xl border border-slate-800">
                        <div class="text-[10px] text-slate-400 font-bold" id="inspFieldLabel">ناوی بەش</div>
                        <div class="text-xs font-black text-amber-300 truncate mt-0.5" id="inspFieldValue">بەشی دیاریکراو</div>
                    </div>

                    <!-- Coordinates (Top & Left/Right in mm) -->
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Top (Vertical) -->
                        <div class="bg-slate-900 p-2 rounded-xl border border-slate-800 space-y-1">
                            <span class="text-[10px] text-slate-400 font-bold flex items-center justify-between">
                                <span>سەرەوە (Y mm):</span>
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
                                <span id="inspHorizLabel">ئاسۆیی (X mm):</span>
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
                            <button onclick="dpadNudge(1, 0)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95" title="ڕاست">➡️</button>
                            <div class="flex items-center justify-center text-[10px] text-slate-500">🎯</div>
                            <button onclick="dpadNudge(-1, 0)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95" title="چەپ">⬅️</button>
                            <div></div>
                            <button onclick="dpadNudge(0, 1)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95" title="خوارەوە">⬇️</button>
                            <div></div>
                        </div>
                    </div>

                    <!-- Typography & Font Controls -->
                    <div class="bg-slate-900 p-2 rounded-xl border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold">
                            <span>قەبارەی فۆنت:</span>
                            <span id="inspFontSizeDisplay" class="text-indigo-300 font-mono font-bold">11px</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button onclick="adjustActiveFontSize(-1)" class="w-6 h-6 bg-slate-800 hover:bg-slate-700 text-white rounded font-bold">-</button>
                            <input type="range" min="8" max="24" id="inspFontSizeSlider" oninput="onActiveFontSizeSlider(this.value)" class="flex-1 accent-indigo-500">
                            <button onclick="adjustActiveFontSize(1)" class="w-6 h-6 bg-slate-800 hover:bg-slate-700 text-white rounded font-bold">+</button>
                        </div>
                    </div>

                    <!-- Deselect Button -->
                    <button onclick="deselectField()" class="w-full py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition">
                        لابردنی هەڵبژاردن
                    </button>
                </div>
            </div>

            <!-- SECTION 2: GLOBAL OFFSET -->
            <div class="bg-slate-950/70 rounded-2xl border border-slate-800 p-3 shadow-inner space-y-2.5">
                <span class="font-bold text-slate-300 block border-b border-slate-800 pb-1.5">
                    تەنزیمی تەواوی ڕاپۆرت
                </span>
                
                <!-- Global Offset Summary -->
                <div class="text-[10px] text-slate-400 flex items-center justify-between">
                    <span>تەنزیمی گشتی چاپ (X, Y):</span>
                    <span id="offsetSummary" class="text-indigo-300 font-mono font-bold">0mm, 0mm</span>
                </div>
            </div>

        </aside>

        <!-- ===================================================================== -->
        <!-- CENTER WORKSPACE CANVAS: A4 REPORT SHEETS -->
        <!-- ===================================================================== -->
        <main id="canvasWorkspace" class="flex-1 bg-slate-950 p-6 overflow-auto flex flex-col items-center justify-start">
            
            @php
                $rowsPerPage = 25;
                $chunks = $transactions->chunk($rowsPerPage);
                $totalChunks = $chunks->count();
                $totalPages = $totalChunks > 0 ? $totalChunks + 1 : 1;
                $globalIndex = 1;
            @endphp

            @if($chunks->isEmpty())
                <!-- Empty State Page -->
                <div class="page">
                    <div class="sheet-ruler-grid"></div>
                    <div class="report-header calibrated-field" data-fid="acct-header" data-name-ku="سەردێڕی محاسبة ٦٦">
                        <div class="header-left">
                            <div class="header-title-code">محاسبة ٦٦</div>
                            <div class="header-datetime">{{ now()->format('Y-m-d H:i:s') }}</div>
                        </div>
                        <div class="header-center">
                            <div class="header-dept-title">بەڕێوەبەرایەتی گومرگی سلێمانی</div>
                        </div>
                        <div class="header-right">
                            <div class="page-counter">1 - 1</div>
                        </div>
                    </div>
                    <div style="text-align: center; padding: 100px 0; font-size: 16px; color: #64748b;">
                        هیچ مامەڵە و پسوولەیەک بەم مەرجانە بوونی نییە.
                    </div>
                </div>
            @else
                <!-- Data Table Pages (Chunked into 25 rows per page) -->
                @foreach($chunks as $pageIndex => $chunk)
                    <div class="page">
                        <div class="sheet-ruler-grid"></div>

                        <!-- Header -->
                        <div class="report-header calibrated-field" data-fid="acct-header" data-name-ku="سەردێڕی فەرمی محاسبة ٦٦">
                            <div class="header-left">
                                <div class="header-title-code">محاسبة ٦٦</div>
                                <div class="header-datetime">{{ now()->format('Y-m-d H:i:s') }}</div>
                            </div>

                            <div class="header-center">
                                <div class="header-dept-title">بەڕێوەبەرایەتی گومرگی سلێمانی</div>
                            </div>

                            <div class="header-right">
                                <div class="range-box-container">
                                    <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span>لە</span>
                                            <div class="range-box">{{ $receiptFrom ?? 1 }}</div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span>بۆ</span>
                                            <div class="range-box">{{ $receiptTo ?? $totalReceiptsCount }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="page-counter">{{ ($pageIndex + 1) }} - {{ $totalPages }}</div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="calibrated-field" data-fid="acct-table" data-name-ku="خشتەی داتاکانی محاسبە">
                            <table class="accounting-table">
                                <thead>
                                    <tr>
                                        <th style="width: 32px;">ز</th>
                                        <th style="width: 220px;">ناوی لێوەوەرگیراو</th>
                                        <th style="width: 75px;">ژ. پسولە</th>
                                        <th style="width: 120px;">جۆری مامەڵە</th>
                                        <th style="width: 55px;">بڕی ساڵ</th>
                                        <th style="width: 90px;">بەروار</th>
                                        <th style="width: 80px;">رەسم</th>
                                        <th style="width: 80px;">اجور</th>
                                        <th style="width: 65px;">پول</th>
                                        <th style="width: 65px;">فۆرم</th>
                                        <th style="width: 90px;">کۆی گشتی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($chunk as $tx)
                                        @php
                                            $cleanTypeName = trim(str_replace(['(نوێ)', '（نوێ）', '( نوێ )', 'نوێ'], '', $tx->transactionType->name_kurdish ?? '-'));
                                        @endphp
                                        <tr>
                                            <td class="font-num">{{ $globalIndex++ }}</td>
                                            <td class="col-name" title="{{ $tx->visitor_name }}">{{ $tx->visitor_name }}</td>
                                            <td class="font-num">{{ $tx->receipt_37a_number ?? '-' }}</td>
                                            <td class="col-type" title="{{ $cleanTypeName }}">{{ $cleanTypeName }}</td>
                                            <td class="font-num">{{ $tx->num_years ?? 1 }}</td>
                                            <td class="font-num">{{ $tx->display_date }}</td>
                                            <td class="font-num">{{ number_format($tx->pay_amount_years ?? 0) }}</td>
                                            <td class="font-num">{{ number_format($tx->pay_inspection ?? 0) }}</td>
                                            <td class="font-num">{{ number_format($tx->pay_stamp ?? 0) }}</td>
                                            <td class="font-num">{{ number_format($tx->pay_form ?? 0) }}</td>
                                            <td class="font-num" style="font-weight: 800;">{{ number_format($tx->total_pay ?? 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                <!-- Final Page: Official Accounting 66 Summary Page -->
                <div class="page">
                    <div class="sheet-ruler-grid"></div>

                    <div class="report-header calibrated-field" data-fid="acct-header" data-name-ku="سەردێڕی فەرمی محاسبة ٦٦">
                        <div class="header-left">
                            <div class="header-title-code">محاسبة ٦٦</div>
                            <div class="header-datetime">{{ now()->format('Y-m-d H:i:s') }}</div>
                        </div>

                        <div class="header-center">
                            <div class="header-dept-title">بەڕێوەبەرایەتی گومرگی سلێمانی</div>
                        </div>

                        <div class="header-right">
                            <div class="range-box-container">
                                <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span>لە</span>
                                        <div class="range-box">{{ $receiptFrom ?? 1 }}</div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span>بۆ</span>
                                        <div class="range-box">{{ $receiptTo ?? $totalReceiptsCount }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="page-counter">{{ $totalPages }} - {{ $totalPages }}</div>
                        </div>
                    </div>

                    <!-- Table Header Template -->
                    <div class="calibrated-field" data-fid="acct-table" data-name-ku="خشتەی پسوولەکان">
                        <table class="accounting-table" style="margin-bottom: 20px;">
                            <thead>
                                <tr>
                                    <th style="width: 32px;">ز</th>
                                    <th style="width: 220px;">ناوی لێوەوەرگیراو</th>
                                    <th style="width: 75px;">ژ. پسولە</th>
                                    <th style="width: 120px;">جۆری مامەڵە</th>
                                    <th style="width: 55px;">بڕی ساڵ</th>
                                    <th style="width: 90px;">بەروار</th>
                                    <th style="width: 80px;">رەسم</th>
                                    <th style="width: 80px;">اجور</th>
                                    <th style="width: 65px;">پول</th>
                                    <th style="width: 65px;">فۆرم</th>
                                    <th style="width: 90px;">کۆی گشتی</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <!-- Summary block -->
                    <div class="summary-container calibrated-field" data-fid="acct-summary-box" data-name-ku="بلۆکی خولاسەی دارایی">
                        <div class="summary-header-row">
                            <span style="font-size: 14px; font-weight: 900;">کۆی پسولەکان:</span>
                            <span style="font-family: monospace; font-size: 16px; font-weight: 900; border: 1px solid #000; padding: 2px 14px; background: #fafafa; border-radius: 4px;">{{ $totalReceiptsCount }}</span>
                            <span style="font-size: 12px; font-weight: bold; color: #475569;">پسوولە</span>
                        </div>

                        <div class="summary-grid">
                            <div class="summary-item">
                                <span>کۆی رەسم :</span>
                                <span class="amount">{{ number_format($totalRasm) }}</span>
                            </div>
                            <div class="summary-item">
                                <span>کۆی پوول:</span>
                                <span class="amount">{{ number_format($totalPul) }}</span>
                            </div>
                            <div class="summary-item">
                                <span>کۆی فۆرم:</span>
                                <span class="amount">{{ number_format($totalForm) }}</span>
                            </div>
                            <div class="summary-item highlight-gray">
                                <span>کۆی داهات:</span>
                                <span class="amount">{{ number_format($totalDahat) }}</span>
                            </div>
                            <div class="summary-item">
                                <span>اجورکشف :</span>
                                <span class="amount">{{ number_format($totalInspection) }}</span>
                            </div>
                            <div class="summary-item highlight-gray">
                                <span>کۆی امانات:</span>
                                <span class="amount">{{ number_format($totalAmanat) }}</span>
                            </div>
                            <div class="summary-item highlight-gray" style="font-size: 14px;">
                                <span>کۆی گشتی بە ژمارە:</span>
                                <span class="amount">{{ number_format($grandTotal) }}</span>
                            </div>
                        </div>

                        <div class="tafqeet-line">
                            کۆی گشتی بە نووسین: 
                            <span style="font-weight: 900; text-decoration: underline;">{{ $grandTotalWords }}</span>
                        </div>
                    </div>
                </div>
            @endif

        </main>

        <!-- ===================================================================== -->
        <!-- RIGHT SIDEBAR: FIELDS EXPLORER (لیستی بەشەکان) -->
        <!-- ===================================================================== -->
        <aside id="studioRightSidebar" class="no-print w-64 bg-slate-900 border-r border-slate-800 p-3 overflow-y-auto flex-shrink-0 text-xs flex flex-col gap-2.5 shadow-2xl">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <span class="font-black text-slate-200 flex items-center gap-1.5">
                    <span>📑</span> بەشەکانی محاسبە ٦٦
                </span>
                <div class="flex items-center gap-1.5">
                    <span id="fieldsCountBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300">
                        بەشەکان
                    </span>
                    <button onclick="toggleRightSidebar(false)" class="w-5 h-5 flex items-center justify-center rounded-lg bg-slate-800 hover:bg-rose-900/60 text-slate-400 hover:text-white transition text-[11px]" title="شاردنەوەی لیستی بەشەکان">
                        ✕
                    </button>
                </div>
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
                <p>• هەموو لاپەڕەکان بەیەکەوە هاوتا دەکرێن.</p>
            </div>
        </aside>

    </div>

    <!-- ========================================================================= -->
    <!-- WIZARD STUDIO JAVASCRIPT ENGINE -->
    <!-- ========================================================================= -->
    <script>
        const STORAGE_KEY = 'accounting_66_custom_positions';
        const STORAGE_GLOBAL_KEY = 'accounting_66_global_offsets';

        let showRulerGrid = false;
        let selectedFid = null;
        let nudgeStep = 1.0; // mm

        let customPositions = {};

        function loadInitialState() {
            try {
                customPositions = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            } catch(e) {
                console.error(e);
            }

            applyAllCustomPositions();

            if (localStorage.getItem('acct_left_collapsed') === 'true') {
                toggleLeftSidebar(false);
            }
            if (localStorage.getItem('acct_right_collapsed') === 'true') {
                toggleRightSidebar(false);
            }

            buildFieldsExplorerList();
            initDragAndDrop();
        }

        function applyAllCustomPositions() {
            Object.keys(customPositions).forEach(fid => {
                const pos = customPositions[fid];
                const elements = document.querySelectorAll(`.calibrated-field[data-fid="${fid}"]`);
                elements.forEach(el => {
                    const x = pos.x || 0;
                    const y = pos.y || 0;
                    el.style.transform = `translate(${x}mm, ${y}mm)`;
                    if (pos.fontSize) el.style.fontSize = pos.fontSize;
                });
            });
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

            localStorage.setItem('acct_left_collapsed', leftSidebarCollapsed);
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

            localStorage.setItem('acct_right_collapsed', rightSidebarCollapsed);
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

        function selectFieldByFid(fid) {
            deselectField();
            selectedFid = fid;

            const targets = document.querySelectorAll(`.calibrated-field[data-fid="${fid}"]`);
            targets.forEach(t => t.classList.add('selected-field'));

            if (leftSidebarCollapsed) {
                toggleLeftSidebar(true);
            }

            document.getElementById('noFieldSelectedNotice')?.classList.add('hidden');
            document.getElementById('fieldInspectorControls')?.classList.remove('hidden');

            const firstEl = targets[0];
            const nameKu = firstEl ? (firstEl.getAttribute('data-name-ku') || fid) : fid;

            document.getElementById('selectedFieldBadge').textContent = nameKu;
            document.getElementById('selectedFieldBadge').className = 'text-[10px] font-black px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30';
            document.getElementById('inspFieldLabel').textContent = nameKu;
            document.getElementById('inspFieldValue').textContent = `تەواوی لاپەڕەکان (${targets.length} شوێن)`;

            const currentPos = customPositions[fid] || { x: 0, y: 0 };
            document.getElementById('inspFieldTopInput').value = (currentPos.y || 0).toFixed(1);
            document.getElementById('inspFieldHorizInput').value = (currentPos.x || 0).toFixed(1);

            const currentFs = parseInt(window.getComputedStyle(firstEl).fontSize, 10) || 12;
            document.getElementById('inspFontSizeDisplay').textContent = `${currentFs}px`;
            document.getElementById('inspFontSizeSlider').value = currentFs;

            document.querySelectorAll('.field-list-item').forEach(item => {
                if (item.getAttribute('data-fid') === fid) {
                    item.classList.add('bg-indigo-600/30', 'border-indigo-500', 'text-white');
                } else {
                    item.classList.remove('bg-indigo-600/30', 'border-indigo-500', 'text-white');
                }
            });
        }

        function deselectField() {
            if (selectedFid) {
                document.querySelectorAll(`.calibrated-field[data-fid="${selectedFid}"]`).forEach(t => t.classList.remove('selected-field'));
                selectedFid = null;
            }
            document.getElementById('noFieldSelectedNotice')?.classList.remove('hidden');
            document.getElementById('fieldInspectorControls')?.classList.add('hidden');
            document.getElementById('selectedFieldBadge').textContent = 'هیچ بەشێک هەڵنەبژێردراوە';
            document.getElementById('selectedFieldBadge').className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-400';
            document.querySelectorAll('.field-list-item').forEach(item => {
                item.classList.remove('bg-indigo-600/30', 'border-indigo-500', 'text-white');
            });
        }

        function stepActiveField(axis, direction) {
            if (!selectedFid) return;
            if (!customPositions[selectedFid]) customPositions[selectedFid] = { x: 0, y: 0 };

            if (axis === 'top') {
                customPositions[selectedFid].y = (customPositions[selectedFid].y || 0) + (nudgeStep * direction);
                document.getElementById('inspFieldTopInput').value = customPositions[selectedFid].y.toFixed(1);
            } else if (axis === 'horiz') {
                customPositions[selectedFid].x = (customPositions[selectedFid].x || 0) + (nudgeStep * direction);
                document.getElementById('inspFieldHorizInput').value = customPositions[selectedFid].x.toFixed(1);
            }

            applyPositionToFid(selectedFid);
            savePositions(false);
            updateFieldListItemBadge(selectedFid);
        }

        function updateActiveFieldFromInput() {
            if (!selectedFid) return;
            if (!customPositions[selectedFid]) customPositions[selectedFid] = { x: 0, y: 0 };

            const top = parseFloat(document.getElementById('inspFieldTopInput').value) || 0;
            const horiz = parseFloat(document.getElementById('inspFieldHorizInput').value) || 0;

            customPositions[selectedFid].y = top;
            customPositions[selectedFid].x = horiz;

            applyPositionToFid(selectedFid);
            savePositions(false);
            updateFieldListItemBadge(selectedFid);
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
            if (!selectedFid) return;
            if (!customPositions[selectedFid]) customPositions[selectedFid] = { x: 0, y: 0 };

            customPositions[selectedFid].x = (customPositions[selectedFid].x || 0) + (dx * nudgeStep);
            customPositions[selectedFid].y = (customPositions[selectedFid].y || 0) + (dy * nudgeStep);

            document.getElementById('inspFieldTopInput').value = customPositions[selectedFid].y.toFixed(1);
            document.getElementById('inspFieldHorizInput').value = customPositions[selectedFid].x.toFixed(1);

            applyPositionToFid(selectedFid);
            savePositions(false);
            updateFieldListItemBadge(selectedFid);
        }

        function applyPositionToFid(fid) {
            const pos = customPositions[fid] || { x: 0, y: 0 };
            document.querySelectorAll(`.calibrated-field[data-fid="${fid}"]`).forEach(el => {
                el.style.transform = `translate(${pos.x || 0}mm, ${pos.y || 0}mm)`;
                if (pos.fontSize) el.style.fontSize = pos.fontSize;
            });
        }

        function adjustActiveFontSize(delta) {
            if (!selectedFid) return;
            const elements = document.querySelectorAll(`.calibrated-field[data-fid="${selectedFid}"]`);
            if (elements.length === 0) return;
            const current = parseInt(window.getComputedStyle(elements[0]).fontSize, 10) || 12;
            const next = Math.max(8, Math.min(28, current + delta));
            if (!customPositions[selectedFid]) customPositions[selectedFid] = { x: 0, y: 0 };
            customPositions[selectedFid].fontSize = `${next}px`;
            applyPositionToFid(selectedFid);
            document.getElementById('inspFontSizeDisplay').textContent = `${next}px`;
            document.getElementById('inspFontSizeSlider').value = next;
            savePositions(false);
        }

        function onActiveFontSizeSlider(val) {
            if (!selectedFid) return;
            if (!customPositions[selectedFid]) customPositions[selectedFid] = { x: 0, y: 0 };
            customPositions[selectedFid].fontSize = `${val}px`;
            applyPositionToFid(selectedFid);
            document.getElementById('inspFontSizeDisplay').textContent = `${val}px`;
            savePositions(false);
        }

        function savePositions(showAlert = true) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(customPositions));
                if (showAlert) alert('ڕێکخستنی شوێنی بەشەکان پاشەکەوتکرا! ✓');
            } catch(e) {
                if (showAlert) alert('خەتایەک لە پاشەکەوتکردن ڕوویدا');
            }
        }

        function resetDefaults() {
            if (!confirm('ئایا دڵنیایت لە گێڕانەوەی تەواوی ڕێکخستنەکان بۆ باری بنەڕەتی سەرەتایی؟')) return;
            try {
                localStorage.removeItem(STORAGE_KEY);
                location.reload();
            } catch(e) {}
        }

        function buildFieldsExplorerList() {
            const container = document.getElementById('fieldsListContainer');
            if (!container) return;
            container.innerHTML = '';

            const uniqueFids = [];
            document.querySelectorAll('.calibrated-field').forEach(el => {
                const fid = el.getAttribute('data-fid');
                if (fid && !uniqueFids.includes(fid)) uniqueFids.push(fid);
            });

            document.getElementById('fieldsCountBadge').textContent = `${uniqueFids.length} بەش`;

            uniqueFids.forEach(fid => {
                const el = document.querySelector(`.calibrated-field[data-fid="${fid}"]`);
                const nameKu = el ? (el.getAttribute('data-name-ku') || fid) : fid;
                const pos = customPositions[fid] || { x: 0, y: 0 };

                const item = document.createElement('div');
                item.className = 'field-list-item p-2 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-indigo-500/60 transition cursor-pointer flex flex-col gap-1';
                item.setAttribute('data-fid', fid);
                item.innerHTML = `
                    <div class="flex items-center justify-between">
                        <strong class="font-bold text-slate-200">${nameKu}</strong>
                        <span class="field-coords-badge text-[9px] font-mono text-indigo-400 bg-indigo-950 px-1.5 py-0.5 rounded border border-indigo-800">
                            X:${(pos.x || 0).toFixed(0)} Y:${(pos.y || 0).toFixed(0)}
                        </span>
                    </div>
                `;

                item.addEventListener('click', () => {
                    selectFieldByFid(fid);
                });

                container.appendChild(item);
            });
        }

        function updateFieldListItemBadge(fid) {
            const item = document.querySelector(`.field-list-item[data-fid="${fid}"]`);
            if (item) {
                const badge = item.querySelector('.field-coords-badge');
                const pos = customPositions[fid] || { x: 0, y: 0 };
                if (badge) {
                    badge.textContent = `X:${(pos.x || 0).toFixed(0)} Y:${(pos.y || 0).toFixed(0)}`;
                }
            }
        }

        let isDragging = false;
        let startX, startY, origX, origY;
        const tooltip = document.getElementById('dragTooltip');

        function initDragAndDrop() {
            document.querySelectorAll('.calibrated-field').forEach(el => {
                const fid = el.getAttribute('data-fid');
                
                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectFieldByFid(fid);
                });

                el.addEventListener('mousedown', (e) => {
                    isDragging = true;
                    selectFieldByFid(fid);
                    startX = e.clientX;
                    startY = e.clientY;
                    const pos = customPositions[fid] || { x: 0, y: 0 };
                    origX = pos.x || 0;
                    origY = pos.y || 0;

                    if (tooltip) tooltip.style.display = 'block';

                    function onMouseMove(moveEvent) {
                        if (!isDragging) return;
                        const dx = (moveEvent.clientX - startX) / 3.7795;
                        const dy = (moveEvent.clientY - startY) / 3.7795;

                        if (!customPositions[fid]) customPositions[fid] = { x: 0, y: 0 };
                        customPositions[fid].x = origX + dx;
                        customPositions[fid].y = origY + dy;

                        applyPositionToFid(fid);

                        document.getElementById('inspFieldTopInput').value = customPositions[fid].y.toFixed(1);
                        document.getElementById('inspFieldHorizInput').value = customPositions[fid].x.toFixed(1);

                        if (tooltip) {
                            tooltip.style.left = `${moveEvent.clientX + 14}px`;
                            tooltip.style.top = `${moveEvent.clientY - 24}px`;
                            tooltip.textContent = `X: ${customPositions[fid].x.toFixed(1)}mm | Y: ${customPositions[fid].y.toFixed(1)}mm`;
                        }
                    }

                    function onMouseUp() {
                        if (isDragging) {
                            isDragging = false;
                            if (tooltip) tooltip.style.display = 'none';
                            savePositions(false);
                            updateFieldListItemBadge(fid);
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

            if (!selectedFid) return;

            const step = e.shiftKey ? 3.0 : nudgeStep;

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                dpadNudge(0, -1);
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                dpadNudge(0, 1);
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                dpadNudge(-1, 0);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                dpadNudge(1, 0);
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

        document.addEventListener('DOMContentLoaded', loadInitialState);
    </script>
</body>
</html>
