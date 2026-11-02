@props([
    'docId' => 'default_doc',
    'targetId' => 'printableArea',
    'title' => 'ڕێکخستنی شوێن و قەبارەی چاپ',
    'initialTop' => 0,
    'initialRight' => 0,
    'initialZoom' => 100,
])

<!-- ========================================================================= -->
<!-- FLOATING PRINT STUDIO & LAYOUT ADJUSTER (وەک ئیدیتەری دەفتەر) -->
<!-- ========================================================================= -->
<div id="printStudioBar_{{ $docId }}" class="no-print sticky top-0 z-50 bg-slate-900/95 backdrop-blur-md text-white border-b border-slate-700/80 shadow-2xl px-4 py-2 text-xs font-sans select-none" dir="rtl">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
        
        <!-- Document Title & Identity -->
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-500 to-indigo-600 flex items-center justify-center font-black text-white text-sm shadow-lg shadow-indigo-500/20">
                ⚙️
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-black text-slate-100 text-xs">{{ $title }}</span>
                    <span id="savedIndicator_{{ $docId }}" class="hidden px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        پاشەکەوتکراوە ✓
                    </span>
                </div>
                <p class="text-[10px] text-slate-400">ڕێکخستنی شوێن (ڕاست، چەپ، سەرەوە، خوارەوە) و قەبارە (گەورە/بچووک)</p>
            </div>
        </div>

        <!-- Position & Movement D-Pad Controls -->
        <div class="flex items-center gap-2 bg-slate-950/80 px-3 py-1.5 rounded-xl border border-slate-800">
            <span class="text-[11px] font-bold text-slate-400 ml-1">شوێن (ملم):</span>
            
            <!-- Step Size -->
            <div class="flex items-center gap-0.5 bg-slate-900 p-0.5 rounded border border-slate-700/80 text-[10px] font-bold">
                <button type="button" onclick="setStudioStep_{{ $docId }}(1)" id="stepBtn1_{{ $docId }}" class="px-1.5 py-0.5 rounded bg-indigo-600 text-white">١</button>
                <button type="button" onclick="setStudioStep_{{ $docId }}(2)" id="stepBtn2_{{ $docId }}" class="px-1.5 py-0.5 rounded text-slate-400 hover:text-white">٢</button>
                <button type="button" onclick="setStudioStep_{{ $docId }}(5)" id="stepBtn5_{{ $docId }}" class="px-1.5 py-0.5 rounded text-slate-400 hover:text-white">٥</button>
            </div>

            <!-- Directional Buttons -->
            <div class="flex items-center gap-1">
                <button type="button" onclick="nudgeStudio_{{ $docId }}(0, -1)" class="w-7 h-7 bg-slate-800 hover:bg-indigo-600 active:scale-95 text-white rounded-lg font-black transition flex items-center justify-center shadow" title="سەرەوە (Up)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                </button>
                <button type="button" onclick="nudgeStudio_{{ $docId }}(0, 1)" class="w-7 h-7 bg-slate-800 hover:bg-indigo-600 active:scale-95 text-white rounded-lg font-black transition flex items-center justify-center shadow" title="خوارەوە (Down)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                </button>
                <button type="button" onclick="nudgeStudio_{{ $docId }}(1, 0)" class="w-7 h-7 bg-slate-800 hover:bg-indigo-600 active:scale-95 text-white rounded-lg font-black transition flex items-center justify-center shadow" title="ڕاست (Right)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
                <button type="button" onclick="nudgeStudio_{{ $docId }}(-1, 0)" class="w-7 h-7 bg-slate-800 hover:bg-indigo-600 active:scale-95 text-white rounded-lg font-black transition flex items-center justify-center shadow" title="چەپ (Left)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </button>
            </div>

            <!-- Coordinate inputs -->
            <div class="flex items-center gap-1.5 text-[11px] font-mono mr-1">
                <label class="flex items-center gap-1 text-slate-400">
                    <span>Y:</span>
                    <input type="number" id="inputTop_{{ $docId }}" value="{{ $initialTop }}" step="0.5" onchange="onInputStudioChange_{{ $docId }}()" 
                           class="w-12 bg-slate-900 border border-slate-700 rounded px-1 py-0.5 text-center text-indigo-300 font-bold focus:outline-none focus:border-indigo-500">
                </label>
                <label class="flex items-center gap-1 text-slate-400">
                    <span>X:</span>
                    <input type="number" id="inputRight_{{ $docId }}" value="{{ $initialRight }}" step="0.5" onchange="onInputStudioChange_{{ $docId }}()" 
                           class="w-12 bg-slate-900 border border-slate-700 rounded px-1 py-0.5 text-center text-indigo-300 font-bold focus:outline-none focus:border-indigo-500">
                </label>
            </div>
        </div>

        <!-- Zoom / Size Controls (گەورە و بچوک) -->
        <div class="flex items-center gap-1.5 bg-slate-950/80 px-3 py-1.5 rounded-xl border border-slate-800">
            <span class="text-[11px] font-bold text-slate-400 ml-1">قەبارە:</span>
            <button type="button" onclick="adjustZoom_{{ $docId }}(-2)" class="w-7 h-7 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-black text-sm flex items-center justify-center shadow active:scale-95" title="بچووککردنەوە">
                -
            </button>
            <span id="zoomDisplay_{{ $docId }}" class="w-12 text-center font-mono font-black text-amber-300 text-xs">
                {{ $initialZoom }}%
            </span>
            <button type="button" onclick="adjustZoom_{{ $docId }}(2)" class="w-7 h-7 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-black text-sm flex items-center justify-center shadow active:scale-95" title="گەورەکردنەوە">
                +
            </button>
        </div>

        <!-- Action Buttons: Save, Reset, Print, Close -->
        <div class="flex items-center gap-2">
            <!-- Save button -->
            <button type="button" onclick="saveStudioConfig_{{ $docId }}()" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-bold rounded-xl shadow-lg shadow-amber-600/30 transition flex items-center gap-1.5" title="پاشەکەوتکردنی ئەم ڕێکخستنە بۆ هەموو جارێک لەم کۆمپیوتەرە">
                <svg class="w-4 h-4 ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span>پاشەکەوتکردن</span>
            </button>

            <!-- Reset button -->
            <button type="button" onclick="resetStudioConfig_{{ $docId }}()" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl border border-slate-700 transition text-[11px] font-bold" title="گێڕانەوە بۆ باری بنەڕەتی">
                <svg class="w-3.5 h-3.5 ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>ڕێسێت</span>
            </button>

            <!-- Print button -->
            <button type="button" onclick="window.print()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-black rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 000-4H9a2 2 0 000 4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>چاپکردن</span>
            </button>

            <!-- Close button -->
            <button type="button" onclick="window.close()" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-xl border border-slate-700 transition" title="داخستن">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const docKey = 'print_layout_cfg_' + '{{ $docId }}';
    const targetElementId = '{{ $targetId }}';
    
    let currentStep = 1;
    let currentTop = parseFloat('{{ $initialTop }}') || 0;
    let currentRight = parseFloat('{{ $initialRight }}') || 0;
    let currentZoom = parseFloat('{{ $initialZoom }}') || 100;

    // Load saved settings from localStorage on load
    try {
        const saved = localStorage.getItem(docKey);
        if (saved) {
            const parsed = JSON.parse(saved);
            if (typeof parsed.top === 'number') currentTop = parsed.top;
            if (typeof parsed.right === 'number') currentRight = parsed.right;
            if (typeof parsed.zoom === 'number') currentZoom = parsed.zoom;
            
            const badge = document.getElementById('savedIndicator_' + '{{ $docId }}');
            if (badge) badge.classList.remove('hidden');
        }
    } catch(e) {
        console.error('Failed to load print layout config', e);
    }

    function applyTransforms() {
        const targets = document.querySelectorAll('#' + targetElementId + ', .' + targetElementId);
        if (!targets || targets.length === 0) return;

        targets.forEach(target => {
            // Apply visual transform
            target.style.transform = `translate(${currentRight * -1}mm, ${currentTop}mm) scale(${currentZoom / 100})`;
            target.style.transformOrigin = 'top center';
            target.style.transition = 'transform 0.05s ease-out';
        });

        // Update inputs & display
        const inTop = document.getElementById('inputTop_' + '{{ $docId }}');
        const inRight = document.getElementById('inputRight_' + '{{ $docId }}');
        const zoomDisp = document.getElementById('zoomDisplay_' + '{{ $docId }}');

        if (inTop) inTop.value = currentTop.toFixed(1);
        if (inRight) inRight.value = currentRight.toFixed(1);
        if (zoomDisp) zoomDisp.innerText = Math.round(currentZoom) + '%';
    }

    window['setStudioStep_' + '{{ $docId }}'] = function(step) {
        currentStep = step;
        [1, 2, 5].forEach(s => {
            const btn = document.getElementById(`stepBtn${s}_` + '{{ $docId }}');
            if (btn) {
                if (s === step) {
                    btn.className = 'px-1.5 py-0.5 rounded bg-indigo-600 text-white';
                } else {
                    btn.className = 'px-1.5 py-0.5 rounded text-slate-400 hover:text-white';
                }
            }
        });
    };

    window['nudgeStudio_' + '{{ $docId }}'] = function(dx, dy) {
        currentRight += (dx * currentStep);
        currentTop += (dy * currentStep);
        applyTransforms();
    };

    window['adjustZoom_' + '{{ $docId }}'] = function(delta) {
        currentZoom = Math.max(50, Math.min(200, currentZoom + delta));
        applyTransforms();
    };

    window['onInputStudioChange_' + '{{ $docId }}'] = function() {
        const inTop = document.getElementById('inputTop_' + '{{ $docId }}');
        const inRight = document.getElementById('inputRight_' + '{{ $docId }}');
        if (inTop) currentTop = parseFloat(inTop.value) || 0;
        if (inRight) currentRight = parseFloat(inRight.value) || 0;
        applyTransforms();
    };

    window['saveStudioConfig_' + '{{ $docId }}'] = function() {
        try {
            const data = {
                top: currentTop,
                right: currentRight,
                zoom: currentZoom
            };
            localStorage.setItem(docKey, JSON.stringify(data));
            const badge = document.getElementById('savedIndicator_' + '{{ $docId }}');
            if (badge) {
                badge.classList.remove('hidden');
                badge.innerText = 'پاشەکەوتکرا ✓';
                setTimeout(() => { badge.innerText = 'پاشەکەوتکراوە ✓'; }, 1500);
            }
        } catch(e) {
            alert('نەتوانرا لە بیرگەی وێبگەڕ پاشەکەوت بکرێت');
        }
    };

    window['resetStudioConfig_' + '{{ $docId }}'] = function() {
        if (!confirm('ئایا دڵنیایت لە گێڕانەوەی ڕێکخستنی ئەم چاپە بۆ باری بنەڕەتی؟')) return;
        currentTop = parseFloat('{{ $initialTop }}') || 0;
        currentRight = parseFloat('{{ $initialRight }}') || 0;
        currentZoom = parseFloat('{{ $initialZoom }}') || 100;
        try {
            localStorage.removeItem(docKey);
            const badge = document.getElementById('savedIndicator_' + '{{ $docId }}');
            if (badge) badge.classList.add('hidden');
        } catch(e) {}
        applyTransforms();
    };

    // Keyboard Arrow Keys Shortcut Support (when not focused in inputs)
    document.addEventListener('keydown', function(e) {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            window['nudgeStudio_' + '{{ $docId }}'](0, -1);
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            window['nudgeStudio_' + '{{ $docId }}'](0, 1);
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            window['nudgeStudio_' + '{{ $docId }}'](1, 0);
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            window['nudgeStudio_' + '{{ $docId }}'](-1, 0);
        } else if (e.key === '+' || e.key === '=') {
            e.preventDefault();
            window['adjustZoom_' + '{{ $docId }}'](2);
        } else if (e.key === '-' || e.key === '_') {
            e.preventDefault();
            window['adjustZoom_' + '{{ $docId }}'](-2);
        }
    });

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyTransforms);
    } else {
        applyTransforms();
    }
})();
</script>
