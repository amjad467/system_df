<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ویزاردی دیزاینی ڕاپۆرت و چاپی دەفتەر - {{ $transaction->booklet_number ?? $transaction->barcode }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700;800;900&family=Courier+Prime:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --offset-x: 0mm;
            --offset-y: 0mm;
            --bg-offset-x: 0mm;
            --bg-offset-y: 0mm;
            --bg-opacity: 1;
            --booklet-font-size: 15px;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background: #0b1120;
            color: #f1f5f9;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Physical A4 Landscape Sheet (297mm x 210mm) */
        .sheet-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            margin: 15px auto;
            overflow: hidden;
            page-break-after: always;
            box-sizing: border-box;
            border-radius: 4px;
        }

        /* Background template for alignment */
        .sheet-bg-template {
            position: absolute;
            top: var(--bg-offset-y, 0mm);
            left: var(--bg-offset-x, 0mm);
            width: 297mm;
            height: 210mm;
            object-fit: fill;
            pointer-events: none;
            opacity: var(--bg-opacity, 1);
            transition: opacity 0.2s ease, left 0.15s ease, top 0.15s ease;
            z-index: 10;
        }

        /* Fold line indicator in the center */
        .center-fold-line {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 148.5mm;
            width: 2px;
            background: rgba(239, 68, 68, 0.4);
            border-left: 2px dashed rgba(239, 68, 68, 0.8);
            z-index: 40;
            pointer-events: none;
        }

        .center-fold-line::after {
            content: "هێڵی قەدکردن (148.5mm)";
            position: absolute;
            top: 8px;
            left: 6px;
            font-size: 9px;
            background: #ef4444;
            color: white;
            padding: 1px 5px;
            border-radius: 3px;
            white-space: nowrap;
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

        /* Dynamic printable layer */
        .printable-content-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            transform: translate(var(--offset-x), var(--offset-y));
            z-index: 30;
            direction: ltr !important;
        }

        /* Absolute positioned field values */
        .calibrated-field {
            position: absolute;
            font-weight: 800;
            color: #000000;
            line-height: 1.2;
            white-space: nowrap;
            user-select: none;
            padding: 2px 6px;
            border-radius: 4px;
            cursor: grab;
            font-size: var(--booklet-font-size);
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

        .field-en {
            font-family: 'Courier Prime', monospace, sans-serif;
            direction: ltr;
            text-align: left;
        }

        .field-ku {
            direction: rtl;
            text-align: right;
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

        /* Studio Clean Print Preview Mode */
        body.clean-preview-mode #studioLeftSidebar,
        body.clean-preview-mode #studioRightSidebar,
        body.clean-preview-mode .edge-dock-btn {
            display: none !important;
        }

        /* Print exact media setup */
        @media print {
            .no-print {
                display: none !important;
            }

            @page {
                size: A4 landscape !important;
                margin: 0mm !important;
            }

            html, body {
                width: 297mm !important;
                height: 210mm !important;
                margin: 0mm !important;
                padding: 0mm !important;
                background: #ffffff !important;
                overflow: visible !important;
                display: block !important;
            }

            /* Critical: Any hidden sheet MUST stay completely hidden in print! */
            .hidden, 
            section.hidden, 
            .booklet-sheet.hidden,
            .sheet-container.hidden {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
            }

            #canvasWorkspace {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                width: 297mm !important;
                height: auto !important;
                background: transparent !important;
            }

            .sheet-container:not(.hidden) {
                position: relative !important;
                box-shadow: none !important;
                margin: 0mm !important;
                padding: 0mm !important;
                width: 297mm !important;
                height: 210mm !important;
                display: block !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                border-radius: 0 !important;
                top: 0 !important;
                left: 0 !important;
            }

            .center-fold-line,
            .sheet-ruler-grid,
            .edge-dock-btn,
            #dragTooltip {
                display: none !important;
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
                width: 297mm !important;
                height: 210mm !important;
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

    <!-- Floating Tooltip for Dragging Coordinates -->
    <div id="dragTooltip">X: 0mm | Y: 0mm</div>

    <!-- ========================================================================= -->
    <!-- TOP ACTION / WIZARD STUDIO TOOLBAR -->
    <!-- ========================================================================= -->
    <header class="no-print sticky top-0 z-50 bg-slate-900 border-b border-slate-700/80 px-4 py-2.5 shadow-2xl flex flex-wrap items-center justify-between gap-3">
        
        <!-- Left: Logo & Transaction Identity -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center font-black text-white text-base shadow-lg shadow-indigo-500/30">
                🎨
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-sm font-black text-white">ویزاردی دیزاینی ڕاپۆرت ({{ $transaction->booklet_number ?? 'بێ ژمارە' }})</h1>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        {{ $transaction->plate_number }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-400">سیستەمی چاپی دەفتەری گومرگی سلێمانی • شۆفێر: <span class="text-slate-200 font-bold">{{ $transaction->visitor_name }}</span></p>
            </div>
        </div>

        <!-- Center: Sheet Selector Tabs -->
        <div class="flex items-center bg-slate-950/90 p-1 rounded-2xl border border-slate-800 shadow-inner">
            <button onclick="switchTab('sheet-driver-ku')" id="btn-tab-driver-ku" class="tab-btn px-3 py-1.5 text-xs font-bold rounded-xl transition bg-indigo-600 text-white shadow">
                ١. کەسی (کوردی)
            </button>
            <button onclick="switchTab('sheet-car-en')" id="btn-tab-car-en" class="tab-btn px-3 py-1.5 text-xs font-bold rounded-xl transition text-slate-400 hover:text-white">
                ٢. ئۆتۆمبێل (ئینگلیزی)
            </button>
            <button onclick="switchTab('sheet-car-ku')" id="btn-tab-car-ku" class="tab-btn px-3 py-1.5 text-xs font-bold rounded-xl transition text-slate-400 hover:text-white">
                ٣. ئۆتۆمبێل (کوردی)
            </button>
            <button onclick="switchTab('sheet-driver-en')" id="btn-tab-driver-en" class="tab-btn px-3 py-1.5 text-xs font-bold rounded-xl transition text-slate-400 hover:text-white">
                ٤. کەسی (ئینگلیزی)
            </button>
            <button onclick="switchTab('all-sheets')" id="btn-tab-all" class="tab-btn px-3 py-1.5 text-xs font-bold rounded-xl transition text-slate-400 hover:text-white">
                هەمووی
            </button>
        </div>

        <!-- Right: Studio Actions & Print -->
        <div class="flex items-center gap-2">
            
            <!-- Master Tools Visibility Toggle (Hide / Show Panels) -->
            <button id="toggleAllToolsBtn" onclick="toggleAllPanels()" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-indigo-300 hover:text-white text-xs font-bold rounded-xl border border-indigo-500/40 transition flex items-center gap-1.5 shadow" title="شاردنەوە یان دەرخستنەوەی هەموو پەنجەرەکانی دیزاین (کورتکراوە: کلیلی H یان Tab)">
                <span id="allToolsIcon">👁️</span>
                <span id="allToolsText">شاردنەوەی پەنجەرەکان</span>
            </button>

            <!-- Ruler Grid Toggle -->
            <button id="toggleRulerBtn" onclick="toggleRulerGrid()" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1" title="پیشاندان یان شاردنەوەی ڕاستەی میلیمه‌تری">
                <span>📏</span>
                <span>ڕاستە</span>
            </button>

            <!-- Save To Server (Primary Action) -->
            <button onclick="saveTemplateToServer()" id="saveServerBtn" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-amber-600/30 transition flex items-center gap-1.5" title="پاشەکەوتکردنی تەواوی ئەم دیزاینە لەسەر سێرڤەر بۆ هەموو کۆمپیوتەرەکان">
                <span>💾</span>
                <span>پاشەکەوتکردن</span>
            </button>

            <!-- Export / Import Template Menu -->
            <div class="relative inline-block text-left">
                <button onclick="toggleExportMenu()" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1">
                    <span>⚙️</span>
                    <span>زیاتر</span>
                </button>
                <div id="exportMenuDropdown" class="hidden absolute left-0 mt-2 w-48 rounded-xl bg-slate-800 border border-slate-700 shadow-2xl py-1 z-50 text-xs">
                    <button onclick="exportTemplateJson()" class="w-full text-right px-3 py-2 text-slate-200 hover:bg-indigo-600/40 hover:text-white flex items-center gap-2">
                        <span>📤</span> هەناردەکردن (Export JSON)
                    </button>
                    <label class="w-full text-right px-3 py-2 text-slate-200 hover:bg-indigo-600/40 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span>📥</span> ھاوردەکردن (Import JSON)
                        <input type="file" id="importJsonFileInput" accept=".json" onchange="importTemplateJson(this)" class="hidden">
                    </label>
                    <div class="border-t border-slate-700 my-1"></div>
                    <button onclick="resetAllDefaults()" class="w-full text-right px-3 py-2 text-rose-300 hover:bg-rose-600/30 hover:text-rose-100 flex items-center gap-2">
                        <span>🔄</span> گەڕاندنەوە بۆ بنەڕەتی کارگە
                    </button>
                </div>
            </div>

            <!-- Print Button -->
            <button onclick="triggerPrint()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
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
        <button id="leftDockBtn" onclick="toggleLeftSidebar(true)" class="edge-dock-btn no-print hidden" title="کردنەوەی پەنجەرەی پشکنەر (کورتکراوە: H)">
            <span>🎯</span>
            <span>پشکنەر</span>
            <span>◀</span>
        </button>

        <button id="rightDockBtn" onclick="toggleRightSidebar(true)" class="edge-dock-btn no-print hidden" title="کردنەوەی لیستی خانەکان (کورتکراوە: H)">
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
                        <span>🎯</span> پشکنەری خانە
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
                    <p class="text-[10px] text-slate-500">کلیک لەسەر نووسینێک بکە لەسەر پەڕەکە، یان لە لیستی خانەکانی لای ڕاستەوە هەڵیبژێرە.</p>
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
                            <button onclick="dpadNudge(0, -1)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95">⬆️</button>
                            <div></div>
                            <button onclick="dpadNudge(-1, 0)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95">⬅️</button>
                            <div class="flex items-center justify-center text-[10px] text-slate-500">🎯</div>
                            <button onclick="dpadNudge(1, 0)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95">➡️</button>
                            <div></div>
                            <button onclick="dpadNudge(0, 1)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 rounded-lg text-white font-bold shadow active:scale-95">⬇️</button>
                            <div></div>
                        </div>
                    </div>

                    <!-- Typography & Font Controls -->
                    <div class="bg-slate-900 p-2 rounded-xl border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold">
                            <span>فۆنت و نووسین:</span>
                            <span id="inspFontSizeDisplay" class="text-indigo-300 font-mono font-bold">15px</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button onclick="adjustActiveFontSize(-1)" class="w-6 h-6 bg-slate-800 hover:bg-slate-700 text-white rounded font-bold">-</button>
                            <input type="range" min="10" max="32" id="inspFontSizeSlider" oninput="onActiveFontSizeSlider(this.value)" class="flex-1 accent-indigo-500">
                            <button onclick="adjustActiveFontSize(1)" class="w-6 h-6 bg-slate-800 hover:bg-slate-700 text-white rounded font-bold">+</button>
                        </div>
                        
                        <!-- Font Weight Picker -->
                        <div class="grid grid-cols-3 gap-1 pt-1">
                            <button onclick="setActiveFontWeight('400')" class="px-1 py-1 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-normal">ئاسایی</button>
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
                        <span>🖼️</span> وێنەی کلیشە (بارکگراوند)
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
                    <span>چاپی ئەزموونی لەگەڵ کلیشە</span>
                </label>

                <!-- Global Offset Summary -->
                <div class="text-[10px] text-slate-400 flex items-center justify-between">
                    <span>تەنزیمی چاپ (X, Y):</span>
                    <span id="offsetSummary" class="text-indigo-300 font-mono font-bold">0mm, 0mm</span>
                </div>
            </div>

        </aside>

        <!-- ===================================================================== -->
        <!-- CENTER WORKSPACE CANVAS: A4 SHEET -->
        <!-- ===================================================================== -->
        <main id="canvasWorkspace" class="flex-1 bg-slate-950 p-6 overflow-auto flex flex-col items-center justify-start">
            
            <!-- SHEET 1: DRIVER & OWNER (KURDISH/ARABIC) -->
            <section id="sheet-driver-ku" class="sheet-container booklet-sheet">
                <img src="{{ asset('images/booklet/sheet_driver_ku.jpg') }}" alt="Booklet Driver KU Template" class="sheet-bg-template">
                <div class="center-fold-line"></div>
                <div class="sheet-ruler-grid"></div>

                <div class="printable-content-layer">
                    <!-- ژمارەی دەفتەر -->
                    <div id="f-ku-booklet" class="calibrated-field field-ku font-black font-mono" data-fid="f-ku-booklet" data-name-ku="ژمارەی دەفتەر" data-name-en="Booklet Number" style="top: 02mm; right: 150mm;">
                        {{ $transaction->booklet_number ?? '51040' }}
                    </div>

                    <!-- ناوی خاوەن موڵک -->
                    <div id="f-ku-owner" class="calibrated-field field-ku font-black" data-fid="f-ku-owner" data-name-ku="ناوی خاوەن موڵک" data-name-en="Owner Name" style="top: 16mm; right: 80mm; min-width: 80mm;">
                        {{ $transaction->visitor_name }}
                    </div>

                    <!-- ناوی شۆفێر -->
                    <div id="f-ku-driver" class="calibrated-field field-ku font-bold" data-fid="f-ku-driver" data-name-ku="ناوی شۆفێر" data-name-en="Driver Name" style="top: 33mm; right: 80mm; min-width: 80mm;">
                        {{ $transaction->second_driver_name ?: $transaction->visitor_name }}
                    </div>

                    <!-- لایەنی دەرکردە -->
                    <div id="f-ku-directorate" class="calibrated-field field-ku font-bold" data-fid="f-ku-directorate" data-name-ku="لایەنی دەرکردە" data-name-en="Customs Directorate" style="top: 48mm; right: 80mm; min-width: 80mm;">
                        بەڕێوەبەرایەتی گومرگی سلێمانی
                    </div>

                    <!-- بەرواری تەواوبوون -->
                    <div id="f-ku-expire" class="calibrated-field field-ku font-black font-mono" data-fid="f-ku-expire" data-name-ku="بەرواری بەسەرچوون" data-name-en="Date of Expire" style="top: 65mm; right: 80mm;">
                        {{ $transaction->end_date ? $transaction->end_date->format('Y-m-d') : ($transaction->transaction_date ? $transaction->transaction_date->copy()->addYears($transaction->num_years ?? 1)->format('Y-m-d') : '') }}
                    </div>

                    <!-- بەرواری دەرچوون -->
                    <div id="f-ku-issue" class="calibrated-field field-ku font-black font-mono" data-fid="f-ku-issue" data-name-ku="بەرواری دەرچوون" data-name-en="Date of Issue" style="top: 75mm; right: 80mm;">
                        {{ $transaction->start_date ? $transaction->start_date->format('Y-m-d') : ($transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : date('Y-m-d')) }}
                    </div>

                    <!-- ناوی ڕێکخەری ڕێگەپێدان -->
                    <div id="f-ku-user" class="calibrated-field field-ku font-black" data-fid="f-ku-user" data-name-ku="کارمەندی ڕێگەپێدان" data-name-en="Officer Name" style="top: 145mm; right: 35mm; font-size: 20px;">
                        {{ $transaction->user_input ?? 'کارمەندی مۆڵەت' }}
                    </div>
                </div>
            </section>

            <!-- SHEET 2: CAR DETAILS (ENGLISH) -->
            <section id="sheet-car-en" class="sheet-container booklet-sheet hidden">
                <img src="{{ asset('images/booklet/sheet_car_en.jpg') }}" alt="Booklet Car EN Template" class="sheet-bg-template">
                <div class="center-fold-line"></div>
                <div class="sheet-ruler-grid"></div>

                <div class="printable-content-layer">
                    <!-- ژمارەی دەفتەر -->
                    <div id="f-cen-booklet" class="calibrated-field field-en font-black" data-fid="f-cen-booklet" data-name-ku="ژمارەی دەفتەر (ئینگلیزی)" data-name-en="Booklet No." style="top: 12mm; left: 28mm;">
                        {{ $transaction->booklet_number ?? '51040' }}
                    </div>

                    <!-- Type & Model -->
                    <div id="f-cen-make" class="calibrated-field field-en font-black uppercase" data-fid="f-cen-make" data-name-ku="جۆر و مۆدێل (ئینگلیزی)" data-name-en="Make & Model" style="top: 24.5mm; left: 45mm; min-width: 80mm;">
                        {{ $transaction->carMake->name_english ?? $transaction->carMake->name_kurdish ?? 'CAR' }} - {{ $transaction->model_year }}
                    </div>

                    <!-- Plate Number -->
                    <div id="f-cen-plate" class="calibrated-field field-en font-black" data-fid="f-cen-plate" data-name-ku="تابلۆ (ئینگلیزی)" data-name-en="Plate No." style="top: 37mm; left: 30mm; min-width: 80mm;">
                        {{ $transaction->plate_number }} ({{ $transaction->plateType->name_english ?? 'Private' }})
                    </div>

                    <!-- Chassis No. -->
                    <div id="f-cen-chassis" class="calibrated-field field-en font-black uppercase tracking-wider" data-fid="f-cen-chassis" data-name-ku="شاسی (ئینگلیزی)" data-name-en="Chassis No." style="top: 51mm; left: 40mm; min-width: 85mm;">
                        {{ $transaction->chassis_number }}
                    </div>

                    <!-- Engine No. -->
                    <div id="f-cen-engine" class="calibrated-field field-en font-black uppercase tracking-wider" data-fid="f-cen-engine" data-name-ku="بزوێنەر (ئینگلیزی)" data-name-en="Engine Cylinders" style="top: 65mm; left: 40mm; min-width: 85mm;">
                        {{ $transaction->piston_count ? ($transaction->piston_count . ' CYLINDERS') : 'ORIGINAL' }}
                    </div>

                    <!-- Color -->
                    <div id="f-cen-color" class="calibrated-field field-en font-bold uppercase" data-fid="f-cen-color" data-name-ku="ڕەنگ (ئینگلیزی)" data-name-en="Color" style="top: 79mm; left: 35mm; min-width: 70mm;">
                        {{ $transaction->carColor->name_english ?? $transaction->carColor->name_kurdish ?? 'BLACK' }}
                    </div>

                    <!-- Extensions -->
                    <div id="f-cen-zedabar" class="calibrated-field field-en font-bold uppercase" data-fid="f-cen-zedabar" data-name-ku="زێدەبار (ئینگلیزی)" data-name-en="Extensions" style="top: 93mm; left: 40mm; min-width: 80mm;">
                        {{ $transaction->zedabar_eng ?: ($transaction->zedabar ?: 'NONE') }}
                    </div>

                    <!-- Missing -->
                    <div id="f-cen-kamukurty" class="calibrated-field field-en font-bold uppercase" data-fid="f-cen-kamukurty" data-name-ku="کەم و کوڕی (ئینگلیزی)" data-name-en="Missing" style="top: 107mm; left: 35mm; min-width: 80mm;">
                        {{ $transaction->kamukurty_eng ?: ($transaction->kamukurty ?: 'NONE') }}
                    </div>

                    <!-- General Status -->
                    <div id="f-cen-barygshty" class="calibrated-field field-en font-bold uppercase" data-fid="f-cen-barygshty" data-name-ku="باری گشتی (ئینگلیزی)" data-name-en="General Status" style="top: 121mm; left: 45mm; min-width: 80mm;">
                        {{ $transaction->bary_gshty_eng ?: ($transaction->bary_gshty ?: 'GOOD') }}
                    </div>
                </div>
            </section>

            <!-- SHEET 3: CAR DETAILS (KURDISH/ARABIC) -->
            <section id="sheet-car-ku" class="sheet-container booklet-sheet hidden">
                <img src="{{ asset('images/booklet/sheet_car_ku.jpg') }}" alt="Booklet Car KU Template" class="sheet-bg-template">
                <div class="center-fold-line"></div>
                <div class="sheet-ruler-grid"></div>

                <div class="printable-content-layer">
                    <!-- ژمارەی دەفتەر -->
                    <div id="f-cku-booklet" class="calibrated-field field-ku font-black" data-fid="f-cku-booklet" data-name-ku="ژمارەی دەفتەر" data-name-en="Booklet No." style="top: 13mm; right: 65mm;">
                        {{ $transaction->booklet_number ?? '51040' }}
                    </div>

                    <!-- جۆرو مۆدێلی ئۆتۆمبێل -->
                    <div id="f-cku-make" class="calibrated-field field-ku font-black" data-fid="f-cku-make" data-name-ku="جۆر و مۆدێلی ئۆتۆمبێل" data-name-en="Car Make & Model" style="top: 24.5mm; right: 75mm; min-width: 65mm;">
                        {{ $transaction->carMake->name_kurdish ?? '' }} ({{ $transaction->model_year }})
                    </div>

                    <!-- ژمارەی ئۆتۆمبێل -->
                    <div id="f-cku-plate" class="calibrated-field field-ku font-black font-mono" data-fid="f-cku-plate" data-name-ku="ژمارەی تابلۆ" data-name-en="Plate Number" style="top: 37mm; right: 55mm; min-width: 80mm;">
                        {{ $transaction->plate_number }} ({{ $transaction->plateType->name_kurdish ?? '' }})
                    </div>

                    <!-- ژمارەی شاسی -->
                    <div id="f-cku-chassis" class="calibrated-field field-en font-black tracking-wider" data-fid="f-cku-chassis" data-name-ku="ژمارەی شاسی" data-name-en="Chassis No." style="top: 51mm; right: 48mm; direction: ltr; min-width: 90mm;">
                        {{ $transaction->chassis_number }}
                    </div>

                    <!-- ژمارەی بزوێنەر -->
                    <div id="f-cku-engine" class="calibrated-field field-en font-black tracking-wider" data-fid="f-cku-engine" data-name-ku="ژمارەی بزوێنەر (سلندەر)" data-name-en="Engine / Cylinders" style="top: 65mm; right: 48mm; direction: ltr; min-width: 85mm;">
                        {{ $transaction->piston_count ? ($transaction->piston_count . ' Piston') : 'Original' }}
                    </div>

                    <!-- ڕەنگ -->
                    <div id="f-cku-color" class="calibrated-field field-ku font-bold" data-fid="f-cku-color" data-name-ku="ڕەنگ" data-name-en="Color" style="top: 79mm; right: 40mm; min-width: 70mm;">
                        {{ $transaction->carColor->name_kurdish ?? 'ڕەش' }}
                    </div>

                    <!-- زێدەبار -->
                    <div id="f-cku-zedabar" class="calibrated-field field-ku font-bold" data-fid="f-cku-zedabar" data-name-ku="زێدەبار" data-name-en="Extensions" style="top: 93mm; right: 40mm; min-width: 80mm;">
                        {{ $transaction->zedabar ?: 'نییە' }}
                    </div>

                    <!-- کەم و کوڕی -->
                    <div id="f-cku-kamukurty" class="calibrated-field field-ku font-bold" data-fid="f-cku-kamukurty" data-name-ku="کەم و کوڕی" data-name-en="Missing Items" style="top: 107mm; right: 40mm; min-width: 80mm;">
                        {{ $transaction->kamukurty ?: 'نییە' }}
                    </div>

                    <!-- باری گشتی -->
                    <div id="f-cku-barygshty" class="calibrated-field field-ku font-bold" data-fid="f-cku-barygshty" data-name-ku="باری گشتی" data-name-en="General Status" style="top: 121mm; right: 40mm; min-width: 80mm;">
                        {{ $transaction->bary_gshty ?: 'باشە / سالم' }}
                    </div>
                </div>
            </section>

            <!-- SHEET 4: DRIVER & OWNER (ENGLISH) -->
            <section id="sheet-driver-en" class="sheet-container booklet-sheet hidden">
                <img src="{{ asset('images/booklet/sheet_driver_en.jpg') }}" alt="Booklet Driver EN Template" class="sheet-bg-template">
                <div class="center-fold-line"></div>
                <div class="sheet-ruler-grid"></div>

                <div class="printable-content-layer">
                    <!-- Registry No. -->
                    <div id="f-en-booklet" class="calibrated-field field-en font-black text-sm" data-fid="f-en-booklet" data-name-ku="ژمارەی دەفتەر (ئینگلیزی)" data-name-en="Registry No." style="top: 3mm; left: 100mm;">
                        {{ $transaction->booklet_number ?? '51040' }}
                    </div>

                    <!-- Vehicle No. -->
                    <div id="f-en-vehicleno" class="calibrated-field field-en font-black" data-fid="f-en-vehicleno" data-name-ku="ژمارەی تابلۆ (ئینگلیزی)" data-name-en="Vehicle No." style="top: 17mm; left: 100mm;">
                        {{ $transaction->plate_number }} ({{ $transaction->plateType->name_english ?? 'Private' }})
                    </div>

                    <!-- Owner Name -->
                    <div id="f-en-owner" class="calibrated-field field-en font-bold uppercase" data-fid="f-en-owner" data-name-ku="ناوی خاوەن موڵک (ئینگلیزی)" data-name-en="Owner Name" style="top: 38mm; left: 100mm;">
                        {{ $transaction->visitor_name_eng ?: $transaction->visitor_name }}
                    </div>

                    <!-- Driver Name -->
                    <div id="f-en-driver" class="calibrated-field field-en font-bold uppercase" data-fid="f-en-driver" data-name-ku="ناوی شۆفێر (ئینگلیزی)" data-name-en="Driver Name" style="top: 62mm; left: 100mm;">
                        {{ $transaction->second_driver_name_eng ?: ($transaction->visitor_name_eng ?: $transaction->visitor_name) }}
                    </div>

                    <!-- Place of Issue -->
                    <div id="f-en-directorate" class="calibrated-field field-en font-bold uppercase" data-fid="f-en-directorate" data-name-ku="شوێنی دەرکردن (ئینگلیزی)" data-name-en="Place of Issue" style="top: 82mm; left: 100mm;">
                        SULAIMANIYAH CUSTOMS
                    </div>

                    <!-- Date of Issue -->
                    <div id="f-en-issue" class="calibrated-field field-en font-bold" data-fid="f-en-issue" data-name-ku="بەرواری دەرچوون (ئینگلیزی)" data-name-en="Date of Issue" style="top: 110mm; left: 100mm;">
                        {{ $transaction->start_date ? $transaction->start_date->format('d/m/Y') : ($transaction->transaction_date ? $transaction->transaction_date->format('d/m/Y') : date('d/m/Y')) }}
                    </div>

                    <!-- Date of Expire -->
                    <div id="f-en-expire" class="calibrated-field field-en font-bold" data-fid="f-en-expire" data-name-ku="بەرواری بەسەرچوون (ئینگلیزی)" data-name-en="Date of Expire" style="top: 133mm; left: 100mm;">
                        {{ $transaction->end_date ? $transaction->end_date->format('d/m/Y') : ($transaction->transaction_date ? $transaction->transaction_date->copy()->addYears($transaction->num_years ?? 1)->format('d/m/Y') : '') }}
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
                    <span>📑</span> خانەکانی لاپەڕە
                </span>
                <div class="flex items-center gap-1.5">
                    <span id="fieldsCountBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300">
                        7 خانە
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
                <!-- Dynamically populated by JavaScript based on active sheet -->
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
        // Server Template Injection (if saved globally on server)
        const serverTemplate = @json($savedTemplate ?? null);

        // State
        let currentTab = 'sheet-driver-ku';
        let showTemplateOnScreen = true;
        let testPrintWithBg = false;
        let showRulerGrid = false;
        let isStudioMode = true;
        let selectedField = null;
        let nudgeStep = 0.5; // mm

        // Offsets
        let offsetX = 0;
        let offsetY = 0;
        let bgOffsetX = 0;
        let bgOffsetY = 0;
        let bgOpacity = 100;
        let defaultFontSize = 15;

        // Custom positions dictionary
        let customPositions = {};

        // Load Initial State (Priority: Server Template > LocalStorage > Defaults)
        function loadInitialState() {
            // LocalStorage fallbacks
            offsetX = parseFloat(localStorage.getItem('booklet_offset_x')) || 0;
            offsetY = parseFloat(localStorage.getItem('booklet_offset_y')) || 0;
            bgOffsetX = parseFloat(localStorage.getItem('booklet_bg_offset_x')) || 0;
            bgOffsetY = parseFloat(localStorage.getItem('booklet_bg_offset_y')) || 0;
            bgOpacity = parseInt(localStorage.getItem('booklet_bg_opacity'), 10) || 100;
            defaultFontSize = parseInt(localStorage.getItem('booklet_font_size'), 10) || 15;
            customPositions = JSON.parse(localStorage.getItem('booklet_custom_positions') || '{}');

            // Override with Server Template if exists
            if (serverTemplate && typeof serverTemplate === 'object') {
                if (serverTemplate.offsetX !== undefined) offsetX = serverTemplate.offsetX;
                if (serverTemplate.offsetY !== undefined) offsetY = serverTemplate.offsetY;
                if (serverTemplate.bgOffsetX !== undefined) bgOffsetX = serverTemplate.bgOffsetX;
                if (serverTemplate.bgOffsetY !== undefined) bgOffsetY = serverTemplate.bgOffsetY;
                if (serverTemplate.bgOpacity !== undefined) bgOpacity = serverTemplate.bgOpacity;
                if (serverTemplate.defaultFontSize !== undefined) defaultFontSize = serverTemplate.defaultFontSize;
                if (serverTemplate.positions && typeof serverTemplate.positions === 'object') {
                    customPositions = Object.assign({}, customPositions, serverTemplate.positions);
                }
            }

            applyOffsets();
            applyBgOffsets();
            setBgOpacity(bgOpacity);
            applyAllCustomPositions();

            // Restore collapsed panels state if user had hidden them
            if (localStorage.getItem('booklet_left_collapsed') === 'true') {
                toggleLeftSidebar(false);
            }
            if (localStorage.getItem('booklet_right_collapsed') === 'true') {
                toggleRightSidebar(false);
            }
        }

        // Apply all custom positions to DOM elements
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

        // Apply printable layer offsets
        function applyOffsets() {
            document.documentElement.style.setProperty('--offset-x', `${offsetX}mm`);
            document.documentElement.style.setProperty('--offset-y', `${offsetY}mm`);
            const sumEl = document.getElementById('offsetSummary');
            if (sumEl) sumEl.textContent = `${offsetX}mm, ${offsetY}mm`;
            localStorage.setItem('booklet_offset_x', offsetX);
            localStorage.setItem('booklet_offset_y', offsetY);
        }

        // Apply background template offsets
        function applyBgOffsets() {
            document.documentElement.style.setProperty('--bg-offset-x', `${bgOffsetX}mm`);
            document.documentElement.style.setProperty('--bg-offset-y', `${bgOffsetY}mm`);
            const xDisp = document.getElementById('bgOffsetXDisplay');
            const yDisp = document.getElementById('bgOffsetYDisplay');
            if (xDisp) xDisp.textContent = `${bgOffsetX > 0 ? '+' : ''}${bgOffsetX} mm`;
            if (yDisp) yDisp.textContent = `${bgOffsetY > 0 ? '+' : ''}${bgOffsetY} mm`;
            localStorage.setItem('booklet_bg_offset_x', bgOffsetX);
            localStorage.setItem('booklet_bg_offset_y', bgOffsetY);
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
            localStorage.setItem('booklet_bg_opacity', bgOpacity);
        }

        // Template Overlay Toggle
        function toggleTemplateOverlay() {
            showTemplateOnScreen = !showTemplateOnScreen;
            const templates = document.querySelectorAll('.sheet-bg-template');
            templates.forEach(t => {
                t.style.display = showTemplateOnScreen ? 'block' : 'none';
            });
            const btn = document.getElementById('inspBgToggleBtn');
            if (btn) btn.textContent = showTemplateOnScreen ? '👁️ دیارە' : '🙈 شاردراوە';
        }

        // Ruler Grid Toggle
        function toggleRulerGrid() {
            showRulerGrid = !showRulerGrid;
            document.body.classList.toggle('show-ruler-grid', showRulerGrid);
            const btn = document.getElementById('toggleRulerBtn');
            if (btn) {
                btn.classList.toggle('bg-indigo-600', showRulerGrid);
                btn.classList.toggle('text-white', showRulerGrid);
            }
        }

        // =========================================================================
        // SIDEBARS COLLAPSE & AUTO-HIDE / EXPAND
        // =========================================================================
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

            localStorage.setItem('booklet_left_collapsed', leftSidebarCollapsed);
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

            localStorage.setItem('booklet_right_collapsed', rightSidebarCollapsed);
            updateAllToolsBtnState();
        }

        function toggleAllPanels() {
            // If both are collapsed, expand both; otherwise collapse both!
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

        // Test Print
        function toggleTestPrint(isChecked) {
            testPrintWithBg = isChecked;
            document.body.classList.toggle('show-template-on-print', testPrintWithBg);
        }

        // Switch Tabs (Sheets)
        function switchTab(tabId) {
            currentTab = tabId;
            const sheets = document.querySelectorAll('.booklet-sheet');
            const tabBtns = document.querySelectorAll('.tab-btn');

            tabBtns.forEach(btn => {
                btn.classList.remove('bg-indigo-600', 'text-white', 'shadow');
                btn.classList.add('text-slate-400');
            });

            if (tabId === 'all-sheets') {
                sheets.forEach(s => s.classList.remove('hidden'));
                document.getElementById('btn-tab-all')?.classList.add('bg-indigo-600', 'text-white', 'shadow');
                document.getElementById('btn-tab-all')?.classList.remove('text-slate-400');
            } else {
                sheets.forEach(s => {
                    s.classList.toggle('hidden', s.id !== tabId);
                });

                if (tabId === 'sheet-driver-ku') document.getElementById('btn-tab-driver-ku')?.classList.add('bg-indigo-600', 'text-white', 'shadow');
                if (tabId === 'sheet-driver-en') document.getElementById('btn-tab-driver-en')?.classList.add('bg-indigo-600', 'text-white', 'shadow');
                if (tabId === 'sheet-car-ku') document.getElementById('btn-tab-car-ku')?.classList.add('bg-indigo-600', 'text-white', 'shadow');
                if (tabId === 'sheet-car-en') document.getElementById('btn-tab-car-en')?.classList.add('bg-indigo-600', 'text-white', 'shadow');
            }

            deselectField();
            buildFieldsExplorerList();
        }

        // =========================================================================
        // FIELD SELECTION & PROPERTY INSPECTOR
        // =========================================================================
        function selectField(field) {
            deselectField();
            selectedField = field;
            field.classList.add('selected-field');

            // Auto-open left inspector if it was collapsed
            if (leftSidebarCollapsed) {
                toggleLeftSidebar(true);
            }

            document.getElementById('noFieldSelectedNotice')?.classList.add('hidden');
            document.getElementById('fieldInspectorControls')?.classList.remove('hidden');

            const fid = field.getAttribute('data-fid');
            const nameKu = field.getAttribute('data-name-ku') || fid;
            const nameEn = field.getAttribute('data-name-en') || '';

            // Update Inspector labels
            document.getElementById('selectedFieldBadge').textContent = nameKu;
            document.getElementById('selectedFieldBadge').className = 'text-[10px] font-black px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30';
            document.getElementById('inspFieldLabel').textContent = `${nameKu} (${nameEn})`;
            document.getElementById('inspFieldValue').textContent = field.textContent.trim().substring(0, 35);

            // Coordinates in Inspector
            const topVal = parseFloat(field.style.top) || 0;
            document.getElementById('inspFieldTopInput').value = topVal.toFixed(1);

            const isLeft = !!field.style.left;
            const horizVal = isLeft ? parseFloat(field.style.left) || 0 : parseFloat(field.style.right) || 0;
            document.getElementById('inspHorizLabel').textContent = isLeft ? 'چەپ (Left):' : 'ڕاست (Right):';
            document.getElementById('inspFieldHorizInput').value = horizVal.toFixed(1);

            // Font size
            const currentFs = parseInt(window.getComputedStyle(field).fontSize, 10) || defaultFontSize;
            document.getElementById('inspFontSizeDisplay').textContent = `${currentFs}px`;
            document.getElementById('inspFontSizeSlider').value = currentFs;

            // Highlight in Right Sidebar
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
                    const next = (current - step).toFixed(1); // moving right/left in RTL
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
            const next = Math.max(10, Math.min(36, current + delta));
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
            localStorage.setItem('booklet_custom_positions', JSON.stringify(customPositions));
        }

        // =========================================================================
        // RIGHT SIDEBAR: FIELDS EXPLORER LIST
        // =========================================================================
        function buildFieldsExplorerList() {
            const container = document.getElementById('fieldsListContainer');
            if (!container) return;
            container.innerHTML = '';

            const activeSheet = currentTab === 'all-sheets' ? document.querySelector('.booklet-sheet:not(.hidden)') : document.getElementById(currentTab);
            if (!activeSheet) return;

            const fields = activeSheet.querySelectorAll('.calibrated-field');
            const countBadge = document.getElementById('fieldsCountBadge');
            if (countBadge) countBadge.textContent = `${fields.length} خانە`;

            fields.forEach(f => {
                const fid = f.getAttribute('data-fid');
                const nameKu = f.getAttribute('data-name-ku') || fid;
                const nameEn = f.getAttribute('data-name-en') || '';
                const val = f.textContent.trim().substring(0, 20);

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
                    <div class="text-[10px] text-slate-500 truncate">${nameEn ? nameEn + ' • ' : ''}${val}</div>
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

        // =========================================================================
        // DRAG & DROP WITH MOUSE
        // =========================================================================
        let isDragging = false;
        let startX, startY, origTop, origLeft, origRight;
        const tooltip = document.getElementById('dragTooltip');

        function initDragAndDrop() {
            document.querySelectorAll('.calibrated-field').forEach(field => {
                // Click to select
                field.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectField(field);
                });

                // Mouse down to drag
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
                        const dxMm = (moveEvent.clientX - startX) / 3.7795;
                        const dyMm = (moveEvent.clientY - startY) / 3.7795;

                        const newTop = (origTop + dyMm).toFixed(1);
                        field.style.top = `${newTop}mm`;

                        let horizStr = '';
                        if (origLeft !== null) {
                            const newLeft = (origLeft + dxMm).toFixed(1);
                            field.style.left = `${newLeft}mm`;
                            horizStr = `Left: ${newLeft}mm`;
                        } else if (origRight !== null) {
                            const newRight = (origRight - dxMm).toFixed(1);
                            field.style.right = `${newRight}mm`;
                            horizStr = `Right: ${newRight}mm`;
                        }

                        if (tooltip) {
                            tooltip.style.left = `${moveEvent.clientX + 15}px`;
                            tooltip.style.top = `${moveEvent.clientY - 25}px`;
                            tooltip.textContent = `Top: ${newTop}mm | ${horizStr}`;
                        }

                        selectField(field);
                    }

                    function onMouseUp() {
                        if (isDragging) {
                            isDragging = false;
                            if (tooltip) tooltip.style.display = 'none';
                            recordFieldPosition(field);
                            updateFieldListItemBadge(field);
                            document.removeEventListener('mousemove', onMouseMove);
                            document.removeEventListener('mouseup', onMouseUp);
                        }
                    }

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
            });

            // Click outside to deselect
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.calibrated-field') && 
                    !e.target.closest('#studioLeftSidebar') && 
                    !e.target.closest('#studioRightSidebar') &&
                    !e.target.closest('header')) {
                    deselectField();
                }
            });

            // Keyboard Shortcuts
            document.addEventListener('keydown', (e) => {
                const isInputActive = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);
                
                // Press 'H' or 'h' to toggle sidebars visibility
                if ((e.key === 'h' || e.key === 'H') && !isInputActive) {
                    toggleAllPanels();
                    e.preventDefault();
                    return;
                }

                if (!selectedField) return;
                let step = e.shiftKey ? 2 : (e.altKey ? 0.1 : 0.5);
                if (e.key === 'ArrowUp') { dpadNudge(0, -step / nudgeStep); e.preventDefault(); }
                if (e.key === 'ArrowDown') { dpadNudge(0, step / nudgeStep); e.preventDefault(); }
                if (e.key === 'ArrowLeft') { dpadNudge(-step / nudgeStep, 0); e.preventDefault(); }
                if (e.key === 'ArrowRight') { dpadNudge(step / nudgeStep, 0); e.preventDefault(); }
                if (e.key === 'Escape') { deselectField(); }
            });
        }

        // =========================================================================
        // SERVER TEMPLATE SAVE & RESET (AJAX)
        // =========================================================================
        async function saveTemplateToServer() {
            const btn = document.getElementById('saveServerBtn');
            const origText = btn.innerHTML;
            btn.innerHTML = '<span>⏳ پاشەکەوتکردن...</span>';

            const payload = {
                template: {
                    offsetX: offsetX,
                    offsetY: offsetY,
                    bgOffsetX: bgOffsetX,
                    bgOffsetY: bgOffsetY,
                    bgOpacity: bgOpacity,
                    defaultFontSize: defaultFontSize,
                    positions: customPositions
                }
            };

            // Save to LocalStorage immediately
            localStorage.setItem('booklet_custom_positions', JSON.stringify(customPositions));
            localStorage.setItem('booklet_offset_x', offsetX);
            localStorage.setItem('booklet_offset_y', offsetY);
            localStorage.setItem('booklet_bg_offset_x', bgOffsetX);
            localStorage.setItem('booklet_bg_offset_y', bgOffsetY);

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('{{ route("transactions.save_booklet_template") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (response.ok) {
                    btn.innerHTML = '<span>✅ پاشەکەوتکرا لە سێرڤەر!</span>';
                    btn.classList.remove('bg-amber-600', 'hover:bg-amber-500');
                    btn.classList.add('bg-emerald-600');
                    setTimeout(() => {
                        btn.innerHTML = origText;
                        btn.classList.add('bg-amber-600', 'hover:bg-amber-500');
                        btn.classList.remove('bg-emerald-600');
                    }, 2500);
                } else {
                    alert('هەڵەیەک ڕوویدا لە پاشەکەوتکردن: ' + (data.message || 'Error'));
                    btn.innerHTML = origText;
                }
            } catch (err) {
                console.error(err);
                btn.innerHTML = '<span>💾 پاشەکەوتکرا (ناوخۆیی)!</span>';
                setTimeout(() => { btn.innerHTML = origText; }, 2000);
            }
        }

        async function resetAllDefaults() {
            if (confirm('ئایا دڵنیایت لە سڕینەوەی هەموو تەنزیم و دیزاینە دەستکردەکان لەسەر سێرڤەر و کۆمپیوتەرەکەت و گەڕاندنەوە بۆ باری بنەڕەتی کارگە؟')) {
                localStorage.removeItem('booklet_custom_positions');
                localStorage.removeItem('booklet_offset_x');
                localStorage.removeItem('booklet_offset_y');
                localStorage.removeItem('booklet_bg_offset_x');
                localStorage.removeItem('booklet_bg_offset_y');
                localStorage.removeItem('booklet_bg_opacity');
                localStorage.removeItem('booklet_font_size');

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    await fetch('{{ route("transactions.reset_booklet_template") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    });
                } catch(e) {}

                location.reload();
            }
        }

        // Export Template to Downloadable JSON
        function exportTemplateJson() {
            const data = {
                exportDate: new Date().toISOString(),
                offsetX, offsetY, bgOffsetX, bgOffsetY, bgOpacity, defaultFontSize,
                positions: customPositions
            };
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `booklet_template_${new Date().toISOString().slice(0, 10)}.json`;
            a.click();
            URL.revokeObjectURL(url);
            toggleExportMenu();
        }

        // Import Template from JSON File
        function importTemplateJson(input) {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const imported = JSON.parse(e.target.result);
                    if (imported.positions) {
                        customPositions = imported.positions;
                        if (imported.offsetX !== undefined) offsetX = imported.offsetX;
                        if (imported.offsetY !== undefined) offsetY = imported.offsetY;
                        if (imported.bgOffsetX !== undefined) bgOffsetX = imported.bgOffsetX;
                        if (imported.bgOffsetY !== undefined) bgOffsetY = imported.bgOffsetY;
                        saveTemplateToServer();
                        location.reload();
                    } else {
                        alert('فایلەکە دروست نییە!');
                    }
                } catch (err) {
                    alert('هەڵە لە خوێندنەوەی فایلی JSON!');
                }
            };
            reader.readAsText(file);
        }

        function toggleExportMenu() {
            const menu = document.getElementById('exportMenuDropdown');
            menu?.classList.toggle('hidden');
        }

        // Print Trigger
        function triggerPrint() {
            // Guarantee that only the active tab sheet is visible during print
            if (currentTab !== 'all-sheets') {
                document.querySelectorAll('.booklet-sheet').forEach(sheet => {
                    if (sheet.id === currentTab) {
                        sheet.classList.remove('hidden');
                    } else {
                        sheet.classList.add('hidden');
                    }
                });
            }
            window.print();
        }

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', () => {
            loadInitialState();
            initDragAndDrop();
            buildFieldsExplorerList();
        });
    </script>
</body>
</html>
