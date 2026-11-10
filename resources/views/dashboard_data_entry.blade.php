@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<div class="space-y-6 w-full max-w-full pb-16" 
     x-data="{
        term: '{{ $searchTerm ?? '' }}',
        isLoading: false,
        errorMsg: '{{ $searchNotFound ? "هیچ مامەڵەیەک نەدۆزرایەوە بەم بارکۆد یان گەڕانە!" : "" }}',
        liveTx: null,
        init() {
            // Render barcodes for any SVGs on page load
            this.$nextTick(() => {
                this.renderAllBarcodes();
            });

            // Alt + N shortcut to jump to New Transaction
            window.addEventListener('keydown', (e) => {
                if (e.altKey && (e.key === 'n' || e.key === 'N')) {
                    e.preventDefault();
                    window.location.href = '{{ route('transactions.create') }}';
                }
            });
        },
        async searchBarcode() {
            const clean = this.term.trim();
            if (!clean) return;
            this.isLoading = true;
            this.errorMsg = '';
            try {
                const res = await fetch(`{{ route('transactions.api_lookup') }}?term=${encodeURIComponent(clean)}`);
                const data = await res.json();
                if (data.found) {
                    this.liveTx = data;
                    this.errorMsg = '';
                    this.$nextTick(() => {
                        this.renderBarcodeSvg('liveBarcodeSvg', data.barcode);
                    });
                } else {
                    this.liveTx = null;
                    this.errorMsg = data.message || 'هیچ مامەڵەیەک نەدۆزرایەوە بەم بارکۆدە!';
                }
            } catch (err) {
                console.error(err);
                this.errorMsg = 'هەڵەیەک ڕوویدا لە پەیوەندی کردن بە سێرڤەر!';
            } finally {
                this.isLoading = false;
            }
        },
        renderBarcodeSvg(elementId, code) {
            const el = document.getElementById(elementId);
            if (el && code && window.JsBarcode) {
                try {
                    JsBarcode('#' + elementId, code, {
                        format: 'CODE128',
                        width: 1.6,
                        height: 38,
                        displayValue: false,
                        margin: 0
                    });
                } catch(e) {
                    console.warn(e);
                }
            }
        },
        renderAllBarcodes() {
            @if(isset($searchedTransaction) && $searchedTransaction)
                this.renderBarcodeSvg('ssrBarcodeSvg', '{{ $searchedTransaction->barcode }}');
            @endif
        }
     }">

    <!-- ========================================================================= -->
    <!-- 1. DEDICATED WORKSTATION HEADER (NO REVENUE / NO STATS) -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-900 to-indigo-950 p-4 sm:p-5 rounded-3xl border border-slate-800 shadow-xl">
        <!-- Subtle Glow -->
        <div class="absolute -top-12 -left-12 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <!-- Left Info (Identity) -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-sky-500/20 text-sky-300 border border-sky-500/30 flex items-center gap-1.5 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span>وێستگەی کارمەندی داتائەنتەری</span>
                    </span>

                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-slate-800 text-slate-300 border border-slate-700/80 flex items-center gap-1.5 shadow-sm">
                        <i class="fa-regular fa-clock text-sky-400 text-xs"></i>
                        <span id="dashLiveDate">{{ now()->format('Y-m-d') }}</span>
                        <span class="text-slate-500">•</span>
                        <span id="dashLiveClock" class="font-bold text-sky-300">{{ now()->format('H:i:s') }}</span>
                    </span>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('dashboard') }}" class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-500/20 text-purple-300 hover:bg-purple-500/30 border border-purple-500/40 transition flex items-center gap-1" title="گەڕانەوە بۆ داشبۆردی گشتی کارگێڕی">
                            <i class="fa-solid fa-chart-pie text-xs"></i>
                            <span>داشبۆردی سەرەکی (Admin)</span>
                        </a>
                    @endif
                </div>

                <div class="pt-1">
                    <h1 class="text-base sm:text-xl font-black text-white">
                        بەخێربێیت، <span class="text-sky-300">{{ auth()->user()->clean_name }}</span>
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        فۆڕمی کارکردنی خێرا: سکانکردنی بارکۆد، زانینی شوێنی ئێستای مامەڵە، و چاپی دەستبەجێی فیشە.
                    </p>
                </div>
            </div>

            <!-- Right: Prominent New Transaction Button (Main Clerk Action) -->
            <div class="shrink-0 flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('transactions.create') }}" 
                   class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-sm shadow-xl shadow-emerald-600/30 hover:shadow-emerald-600/50 flex items-center justify-center gap-2.5 transition active:scale-95 group">
                    <div class="w-7 h-7 rounded-xl bg-white/20 flex items-center justify-center group-hover:rotate-90 transition duration-300">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </div>
                    <span>تۆمارکردنی مامەڵەی نوێ</span>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-black/20 text-emerald-100 hidden sm:inline-block border border-white/20">Alt + N</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. HIGH PRIORITY ALERT: RETURNED TRANSACTIONS (IF ANY) -->
    <!-- ========================================================================= -->
    @if(isset($myReturnedTransactions) && $myReturnedTransactions->count() > 0)
        <div class="bg-gradient-to-r from-rose-950/90 via-slate-900 to-rose-950/90 border-2 border-rose-500/60 rounded-3xl p-4 sm:p-5 shadow-2xl space-y-3">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-lg font-black shrink-0 border border-rose-500/40 animate-pulse">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-rose-200 text-sm sm:text-base flex items-center gap-2">
                            <span>ئاگاداری: {{ $myReturnedTransactions->count() }} مامەڵە بەهۆی هەڵەوە گەڕێنراوەتەوە بۆ چاککردن!</span>
                        </h3>
                        <p class="text-xs text-rose-300/80">
                            ئەم مامەڵانە لەلایەن کەشف یان وردبینی گەڕێنراونەتەوە، تکایە ڕاستیان بکەرەوە تا ڕایی ببن.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 pt-2 border-t border-rose-500/20">
                @foreach($myReturnedTransactions->take(6) as $rt)
                    <div class="bg-slate-900/90 border border-rose-500/40 p-3 rounded-2xl flex items-center justify-between text-xs gap-3 shadow-md hover:border-rose-400 transition">
                        <div class="space-y-0.5 min-w-0 pr-1">
                            <span class="font-mono text-sky-400 font-black block">{{ $rt->barcode }}</span>
                            <span class="font-bold text-white block truncate">{{ $rt->visitor_name }}</span>
                            <span class="text-[11px] text-rose-400 block truncate" title="{{ $rt->return_reason }}">
                                <i class="fa-solid fa-circle-exclamation ml-1"></i>{{ Str::limit($rt->return_reason ?? 'هۆکار دیاری نەکراوە', 35) }}
                            </span>
                        </div>
                        <a href="{{ route('transactions.edit', $rt->id) }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs rounded-xl shadow shrink-0 transition flex items-center gap-1">
                            <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                            <span>چاککردن</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 3. BARCODE SEARCH STATION (SEARCH BY BARCODE, PLATE, OR CHASSIS) -->
    <!-- ========================================================================= -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl relative">
        <div class="max-w-2xl mx-auto space-y-3">
            <div class="text-center space-y-1">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/15 text-sky-300 border border-sky-500/30">
                    <i class="fa-solid fa-barcode text-sm"></i>
                    <span>گەڕان بەدوای بارکۆد و زانینی شوێنی مامەڵە</span>
                </span>
                <h2 class="text-lg sm:text-xl font-black text-white">
                    بارکۆدی مامەڵە لێبدە بۆ بینینی زانیاری، شوێن و چاپی فیشە
                </h2>
                <p class="text-xs text-slate-400">
                    دەتوانیت بە ئامێری سکانەر بارکۆد لێبدەیت، یان ژمارەی بارکۆد / تابلۆ لێرە بنووسیت
                </p>
            </div>

            <!-- Big Search Input Box -->
            <form action="{{ route('dashboard') }}" method="GET" @submit.prevent="searchBarcode()" class="relative">
                <div class="relative flex items-center bg-slate-950 rounded-2xl border-2 border-sky-500/60 hover:border-sky-400 focus-within:border-sky-300 focus-within:ring-4 focus-within:ring-sky-500/30 transition shadow-2xl">
                    <!-- Scanner Icon -->
                    <div class="absolute right-4 text-sky-400 pointer-events-none">
                        <i class="fa-solid fa-barcode text-xl" :class="isLoading ? 'animate-pulse text-amber-400' : ''"></i>
                    </div>

                    <input type="text" 
                           name="search" 
                           x-model="term"
                           id="barcodeWorkstationInput"
                           placeholder="بارکۆدی ١٠ ڕەقەمی بنووسە یان سکانی بکە... (F2)" 
                           class="w-full bg-transparent text-white placeholder-slate-500 text-base sm:text-lg font-mono font-black pr-14 pl-32 py-3.5 sm:py-4 focus:outline-none text-right tracking-wider"
                           autocomplete="off"
                           autofocus
                           @keydown.window.f2.prevent="$el.focus(); $el.select()">

                    <!-- Left Action Buttons -->
                    <div class="absolute left-2.5 flex items-center gap-1.5">
                        <kbd class="hidden sm:inline-flex items-center px-2 py-1 text-[11px] font-mono font-bold text-slate-400 bg-slate-800 border border-slate-700 rounded-lg shadow-sm" title="داگرتنی F2 بۆ فۆکەس">
                            F2
                        </kbd>
                        <button type="submit" 
                                :disabled="isLoading"
                                class="px-4 py-2 sm:py-2.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-lg shadow-sky-500/30 transition flex items-center gap-1.5 disabled:opacity-50">
                            <i class="fa-solid fa-magnifying-glass" :class="isLoading ? 'fa-spin' : ''"></i>
                            <span x-text="isLoading ? 'دەگەڕێت...' : 'گەڕان'">گەڕان</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Error Banner (If not found) -->
            <div x-show="errorMsg" x-cloak class="p-3 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs font-bold text-center flex items-center justify-center gap-2 animate-shake">
                <i class="fa-solid fa-circle-exclamation text-rose-400 text-sm"></i>
                <span x-text="errorMsg"></span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. SEARCHED RECORD & EXACT LOCATION TRACKER (ئێستا لەکوێدایە؟) -->
    <!-- ========================================================================= -->

    <!-- A) CLIENT-SIDE LIVE RESULT (Instant AJAX Match) -->
    <template x-if="liveTx">
        <div class="bg-slate-900 border-2 border-sky-500/60 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in duration-200">
            <!-- Header of Found Record -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-3">
                <div class="flex items-center gap-3">
                    <div class="bg-white p-1.5 rounded-xl border border-slate-700 shadow shrink-0">
                        <svg id="liveBarcodeSvg" class="max-h-9"></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono text-base font-black text-sky-400" x-text="liveTx.barcode"></span>
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30" x-text="liveTx.transaction_type"></span>
                        </div>
                        <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-2">
                            <span>خاوەن مامەڵە: <strong class="text-white text-sm" x-text="liveTx.visitor_name"></strong></span>
                            <span>•</span>
                            <span>بەروار: <span class="font-mono text-slate-300" x-text="liveTx.transaction_date"></span></span>
                        </div>
                    </div>
                </div>

                <!-- Primary Action: 1-Click Print Receipt Button -->
                <div class="flex items-center gap-2 shrink-0">
                    <a :href="liveTx.print_url" target="_blank"
                       class="px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-600 hover:from-emerald-500 hover:to-sky-500 text-white font-black text-sm shadow-xl shadow-emerald-600/30 hover:scale-102 transition flex items-center gap-2">
                        <i class="fa-solid fa-print text-base"></i>
                        <span>چاپکردنی فیشەی داتائەنتەری</span>
                    </a>
                </div>
            </div>

            <!-- "لەکوێدایە؟" - HIGH-VISIBILITY WHERE IS IT NOW TRACKER -->
            <div class="rounded-2xl p-4 sm:p-5 border space-y-3"
                 :class="{
                     'bg-rose-950/40 border-rose-500/50 text-rose-200': liveTx.location.badge_color === 'rose',
                     'bg-amber-950/40 border-amber-500/50 text-amber-200': liveTx.location.badge_color === 'amber',
                     'bg-purple-950/40 border-purple-500/50 text-purple-200': liveTx.location.badge_color === 'purple',
                     'bg-orange-950/40 border-orange-500/50 text-orange-200': liveTx.location.badge_color === 'orange',
                     'bg-sky-950/40 border-sky-500/50 text-sky-200': liveTx.location.badge_color === 'sky',
                     'bg-indigo-950/40 border-indigo-500/50 text-indigo-200': liveTx.location.badge_color === 'indigo',
                     'bg-emerald-950/40 border-emerald-500/50 text-emerald-200': liveTx.location.badge_color === 'emerald',
                 }">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-base bg-white/10 border border-white/20">
                            <i class="fa-solid" :class="liveTx.location.icon"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold opacity-80 block">شوێنی ئێستای مامەڵە (لەکوێدایە):</span>
                            <h3 class="text-base sm:text-lg font-black" x-text="liveTx.location.title"></h3>
                        </div>
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-black bg-white/15 border border-white/20" x-text="liveTx.current_stage_name"></span>
                </div>

                <p class="text-xs sm:text-sm font-medium leading-relaxed opacity-90 pt-1 border-t border-white/10" x-text="liveTx.location.description"></p>

                <!-- 5-Stage Visual Stepper -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 pt-2">
                    <!-- Step 1: Data Entry -->
                    <div class="p-2.5 rounded-xl border text-center text-xs space-y-1 bg-emerald-500/10 border-emerald-500/40 text-emerald-300">
                        <i class="fa-solid fa-circle-check text-sm block"></i>
                        <strong class="block font-bold">١. داتائەنتەری</strong>
                        <span class="text-[10px] opacity-80 block truncate" x-text="liveTx.user_input || 'تۆمارکراوە'"></span>
                    </div>

                    <!-- Step 2: Inspection -->
                    <div class="p-2.5 rounded-xl border text-center text-xs space-y-1"
                         :class="liveTx.is_inspected ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : (liveTx.location.step === 2 ? 'bg-amber-500/20 border-amber-400 text-amber-200 ring-2 ring-amber-400/30 font-black animate-pulse' : 'bg-slate-950/60 border-slate-800 text-slate-500')">
                        <i class="fa-solid text-sm block" :class="liveTx.is_inspected ? 'fa-circle-check' : (liveTx.location.step === 2 ? 'fa-location-dot' : 'fa-hourglass')"></i>
                        <strong class="block">٢. کەشف و تەخمین</strong>
                        <span class="text-[10px] block truncate" x-text="liveTx.is_inspected ? (liveTx.inspected_by || 'پەسەندکراوە') : (liveTx.location.step === 2 ? '📍 ئێستا لێرەیە' : 'چاوەڕوان')"></span>
                    </div>

                    <!-- Step 3: Audit -->
                    <div class="p-2.5 rounded-xl border text-center text-xs space-y-1"
                         :class="liveTx.is_audited ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : (liveTx.location.step === 3 ? 'bg-purple-500/20 border-purple-400 text-purple-200 ring-2 ring-purple-400/30 font-black animate-pulse' : 'bg-slate-950/60 border-slate-800 text-slate-500')">
                        <i class="fa-solid text-sm block" :class="liveTx.is_audited ? 'fa-circle-check' : (liveTx.location.step === 3 ? 'fa-location-dot' : 'fa-hourglass')"></i>
                        <strong class="block">٣. وردبینی</strong>
                        <span class="text-[10px] block truncate" x-text="liveTx.is_audited ? (liveTx.audited_by || 'پەسەندکراوە') : (liveTx.location.step === 3 ? '📍 ئێستا لێرەیە' : 'چاوەڕوان')"></span>
                    </div>

                    <!-- Step 4: Cashier -->
                    <div class="p-2.5 rounded-xl border text-center text-xs space-y-1"
                         :class="liveTx.is_paid ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : (liveTx.location.step === 4 ? 'bg-amber-500/20 border-amber-400 text-amber-200 ring-2 ring-amber-400/30 font-black animate-pulse' : 'bg-slate-950/60 border-slate-800 text-slate-500')">
                        <i class="fa-solid text-sm block" :class="liveTx.is_paid ? 'fa-circle-check' : (liveTx.location.step === 4 ? 'fa-location-dot' : 'fa-hourglass')"></i>
                        <strong class="block">٤. قاسە و پارەدان</strong>
                        <span class="text-[10px] block truncate" x-text="liveTx.is_paid ? ('وەسڵ: ' + (liveTx.receipt_37a_number || 'بڕاوە')) : (liveTx.location.step === 4 ? '📍 ئێستا لێرەیە' : 'چاوەڕوان')"></span>
                    </div>

                    <!-- Step 5: Booklet -->
                    <div class="p-2.5 rounded-xl border text-center text-xs space-y-1 col-span-2 sm:col-span-1"
                         :class="liveTx.is_booklet_completed ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : (liveTx.location.step === 5 ? 'bg-sky-500/20 border-sky-400 text-sky-200 ring-2 ring-sky-400/30 font-black animate-pulse' : 'bg-slate-950/60 border-slate-800 text-slate-500')">
                        <i class="fa-solid text-sm block" :class="liveTx.is_booklet_completed ? 'fa-circle-check' : (liveTx.location.step === 5 ? 'fa-location-dot' : 'fa-hourglass')"></i>
                        <strong class="block">٥. بەشی دەفتەر</strong>
                        <span class="text-[10px] block truncate" x-text="liveTx.is_booklet_completed ? ('دەفتەر: ' + liveTx.booklet_number) : (liveTx.location.step === 5 ? '📍 ئێستا لێرەیە' : 'چاوەڕوان')"></span>
                    </div>
                </div>
            </div>

            <!-- Driver & Vehicle Details Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-950/80 p-4 rounded-2xl border border-slate-800 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">ناوی خاوەن مامەڵە:</span>
                    <strong class="text-white block font-bold text-sm" x-text="liveTx.visitor_name"></strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">ژمارەی تەلەفۆن:</span>
                    <span class="font-mono text-sky-300 block font-bold" x-text="liveTx.phone_number || '-'"></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">ژمارەی پاسپۆرت / پێناس:</span>
                    <span class="font-mono text-slate-300 block font-bold" x-text="liveTx.passport_number || '-'"></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">ژمارەی تابلۆ:</span>
                    <span class="font-bold text-amber-300 block text-sm" x-text="liveTx.plate_number + ' (' + liveTx.plate_type + ')'"></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">جۆری ئۆتۆمبێل:</span>
                    <strong class="text-slate-200 block" x-text="liveTx.car_make + ' - ' + liveTx.model_year"></strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">ڕەنگی ئۆتۆمبێل:</span>
                    <span class="text-slate-300 block" x-text="liveTx.car_color || '-'"></span>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-400 block text-[11px]">ژمارەی شاسی:</span>
                    <span class="font-mono text-slate-300 block font-bold tracking-wider" x-text="liveTx.chassis_number"></span>
                </div>
            </div>

            <!-- Secondary Actions -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-800 flex-wrap gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <a :href="liveTx.show_url" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5 font-bold">
                        <i class="fa-solid fa-eye"></i>
                        <span>بینینی تەواوی فایل</span>
                    </a>
                    <a :href="liveTx.edit_url" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 hover:text-white transition flex items-center gap-1.5 font-bold">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>دەستکاریکردن</span>
                    </a>
                </div>

                <a :href="liveTx.print_url" target="_blank"
                   class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>چاپی فیشەی داتائەنتەری</span>
                </a>
            </div>
        </div>
    </template>

    <!-- B) SSR RESULT (When accessed via direct search or form submission) -->
    @if(isset($searchedTransaction) && $searchedTransaction)
        @php
            $loc = $searchedTransaction->getCurrentLocationDescription();
        @endphp
        <div x-show="!liveTx" class="bg-slate-900 border-2 border-sky-500/60 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-5">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-3">
                <div class="flex items-center gap-3">
                    <div class="bg-white p-1.5 rounded-xl border border-slate-700 shadow shrink-0">
                        <svg id="ssrBarcodeSvg" class="max-h-9"></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono text-base font-black text-sky-400">{{ $searchedTransaction->barcode }}</span>
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                {{ $searchedTransaction->transactionType?->name_kurdish }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-2">
                            <span>خاوەن مامەڵە: <strong class="text-white text-sm">{{ $searchedTransaction->visitor_name }}</strong></span>
                            <span>•</span>
                            <span>بەروار: <span class="font-mono text-slate-300">{{ $searchedTransaction->transaction_date ? $searchedTransaction->transaction_date->format('Y-m-d') : ($searchedTransaction->created_at ? $searchedTransaction->created_at->format('Y-m-d') : '') }}</span></span>
                        </div>
                    </div>
                </div>

                <!-- Primary Action: 1-Click Print Receipt Button -->
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('transactions.print_data_entry', $searchedTransaction->id) }}" target="_blank"
                       class="px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-600 hover:from-emerald-500 hover:to-sky-500 text-white font-black text-sm shadow-xl shadow-emerald-600/30 hover:scale-102 transition flex items-center gap-2">
                        <i class="fa-solid fa-print text-base"></i>
                        <span>چاپکردنی فیشەی داتائەنتەری</span>
                    </a>
                </div>
            </div>

            <!-- "لەکوێدایە؟" - HIGH-VISIBILITY WHERE IS IT NOW TRACKER -->
            @php
                $badgeClasses = match($loc['badge_color']) {
                    'rose' => 'bg-rose-950/40 border-rose-500/50 text-rose-200',
                    'amber' => 'bg-amber-950/40 border-amber-500/50 text-amber-200',
                    'purple' => 'bg-purple-950/40 border-purple-500/50 text-purple-200',
                    'orange' => 'bg-orange-950/40 border-orange-500/50 text-orange-200',
                    'sky' => 'bg-sky-950/40 border-sky-500/50 text-sky-200',
                    'indigo' => 'bg-indigo-950/40 border-indigo-500/50 text-indigo-200',
                    default => 'bg-emerald-950/40 border-emerald-500/50 text-emerald-200',
                };
            @endphp
            <div class="rounded-2xl p-4 sm:p-5 border space-y-3 {{ $badgeClasses }}">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-base bg-white/10 border border-white/20">
                            <i class="fa-solid {{ $loc['icon'] }}"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold opacity-80 block">شوێنی ئێستای مامەڵە (لەکوێدایە):</span>
                            <h3 class="text-base sm:text-lg font-black">{{ $loc['title'] }}</h3>
                        </div>
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-black bg-white/15 border border-white/20">
                        {{ $searchedTransaction->getCurrentStageName() }}
                    </span>
                </div>

                <p class="text-xs sm:text-sm font-medium leading-relaxed opacity-90 pt-1 border-t border-white/10">
                    {{ $loc['description'] }}
                </p>

                <!-- 5-Stage Visual Stepper -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 pt-2">
                    <!-- Step 1: Data Entry -->
                    <div class="p-2.5 rounded-xl border text-center text-xs space-y-1 bg-emerald-500/10 border-emerald-500/40 text-emerald-300">
                        <i class="fa-solid fa-circle-check text-sm block"></i>
                        <strong class="block font-bold">١. داتائەنتەری</strong>
                        <span class="text-[10px] opacity-80 block truncate">{{ $searchedTransaction->user_input ?? 'تۆمارکراوە' }}</span>
                    </div>

                    <!-- Step 2: Inspection -->
                    <div class="p-2.5 rounded-xl border text-center text-xs space-y-1 {{ $searchedTransaction->is_inspected ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : ($loc['step'] === 2 ? 'bg-amber-500/20 border-amber-400 text-amber-200 ring-2 ring-amber-400/30 font-black animate-pulse' : 'bg-slate-950/60 border-slate-800 text-slate-500') }}">
                        <i class="fa-solid {{ $searchedTransaction->is_inspected ? 'fa-circle-check' : ($loc['step'] === 2 ? 'fa-location-dot' : 'fa-hourglass') }} text-sm block"></i>
                        <strong class="block">٢. کەشف و تەخمین</strong>
                        <span class="text-[10px] block truncate">{{ $searchedTransaction->is_inspected ? ($searchedTransaction->inspected_by ?? 'پەسەندکراوە') : ($loc['step'] === 2 ? '📍 ئێستا لێرەیە' : 'چاوەڕوان') }}</span>
                    </div>

                    <!-- Step 3: Audit -->
                    <div class="p-2.5 rounded-xl border text-center text-xs space-y-1 {{ $searchedTransaction->is_audited ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : ($loc['step'] === 3 ? 'bg-purple-500/20 border-purple-400 text-purple-200 ring-2 ring-purple-400/30 font-black animate-pulse' : 'bg-slate-950/60 border-slate-800 text-slate-500') }}">
                        <i class="fa-solid {{ $searchedTransaction->is_audited ? 'fa-circle-check' : ($loc['step'] === 3 ? 'fa-location-dot' : 'fa-hourglass') }} text-sm block"></i>
                        <strong class="block">٣. وردبینی</strong>
                        <span class="text-[10px] block truncate">{{ $searchedTransaction->is_audited ? ($searchedTransaction->audited_by ?? 'پەسەندکراوە') : ($loc['step'] === 3 ? '📍 ئێستا لێرەیە' : 'چاوەڕوان') }}</span>
                    </div>

                    <!-- Step 4: Cashier -->
                    <div class="p-2.5 rounded-xl border text-center text-xs space-y-1 {{ $searchedTransaction->is_paid ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : ($loc['step'] === 4 ? 'bg-amber-500/20 border-amber-400 text-amber-200 ring-2 ring-amber-400/30 font-black animate-pulse' : 'bg-slate-950/60 border-slate-800 text-slate-500') }}">
                        <i class="fa-solid {{ $searchedTransaction->is_paid ? 'fa-circle-check' : ($loc['step'] === 4 ? 'fa-location-dot' : 'fa-hourglass') }} text-sm block"></i>
                        <strong class="block">٤. قاسە و پارەدان</strong>
                        <span class="text-[10px] block truncate">{{ $searchedTransaction->is_paid ? ('وەسڵ: ' . ($searchedTransaction->receipt_37a_number ?? 'بڕاوە')) : ($loc['step'] === 4 ? '📍 ئێستا لێرەیە' : 'چاوەڕوان') }}</span>
                    </div>

                    <!-- Step 5: Booklet -->
                    <div class="p-2.5 rounded-xl border text-center text-xs space-y-1 col-span-2 sm:col-span-1 {{ $searchedTransaction->is_booklet_completed ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : ($loc['step'] === 5 ? 'bg-sky-500/20 border-sky-400 text-sky-200 ring-2 ring-sky-400/30 font-black animate-pulse' : 'bg-slate-950/60 border-slate-800 text-slate-500') }}">
                        <i class="fa-solid {{ $searchedTransaction->is_booklet_completed ? 'fa-circle-check' : ($loc['step'] === 5 ? 'fa-location-dot' : 'fa-hourglass') }} text-sm block"></i>
                        <strong class="block">٥. بەشی دەفتەر</strong>
                        <span class="text-[10px] block truncate">{{ $searchedTransaction->is_booklet_completed ? ('دەفتەر: ' . $searchedTransaction->booklet_number) : ($loc['step'] === 5 ? '📍 ئێستا لێرەیە' : 'چاوەڕوان') }}</span>
                    </div>
                </div>
            </div>

            <!-- Driver & Vehicle Details Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-950/80 p-4 rounded-2xl border border-slate-800 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">ناوی خاوەن مامەڵە:</span>
                    <strong class="text-white block font-bold text-sm">{{ $searchedTransaction->visitor_name }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">ژمارەی تەلەفۆن:</span>
                    <span class="font-mono text-sky-300 block font-bold">{{ $searchedTransaction->phone_number ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">ژمارەی پاسپۆرت / پێناس:</span>
                    <span class="font-mono text-slate-300 block font-bold">{{ $searchedTransaction->passport_number ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">ژمارەی تابلۆ:</span>
                    <div class="mt-1">
                        <x-vehicle-plate :plate="$searchedTransaction->plate_number" :type="$searchedTransaction->plateType" size="sm" />
                    </div>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">جۆری ئۆتۆمبێل:</span>
                    <strong class="text-slate-200 block">{{ $searchedTransaction->carMake?->name_kurdish ?? '-' }} - {{ $searchedTransaction->model_year }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">ڕەنگی ئۆتۆمبێل:</span>
                    <span class="text-slate-300 block">{{ $searchedTransaction->carColor?->name_kurdish ?? '-' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-400 block text-[11px]">ژمارەی شاسی:</span>
                    <span class="font-mono text-slate-300 block font-bold tracking-wider">{{ $searchedTransaction->chassis_number }}</span>
                </div>
            </div>

            <!-- Secondary Actions -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-800 flex-wrap gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <a href="{{ route('transactions.show', $searchedTransaction->id) }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5 font-bold">
                        <i class="fa-solid fa-eye"></i>
                        <span>بینینی تەواوی فایل</span>
                    </a>
                    <a href="{{ route('transactions.edit', $searchedTransaction->id) }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 hover:text-white transition flex items-center gap-1.5 font-bold">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>دەستکاریکردن</span>
                    </a>
                </div>

                <a href="{{ route('transactions.print_data_entry', $searchedTransaction->id) }}" target="_blank"
                   class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>چاپی فیشەی داتائەنتەری</span>
                </a>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 5. RECENT SLIPS FOR 1-CLICK QUICK REPRINT (دوایین فیشە تۆمارکراوەکان بۆ چاپی خێرا) -->
    <!-- ========================================================================= -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-2xl space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center font-bold text-base">
                    <i class="fa-solid fa-print"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-white">دوایین فیشە تۆمارکراوەکان (چاپی دەستبەجێ)</h3>
                    <p class="text-[11px] text-slate-400">ئەو مامەڵانەی ئەمڕۆ لێرەوە داخڵکراون؛ بە یەک کلیک فیشەکەیان چاپ بکەرەوە</p>
                </div>
            </div>

            <span class="text-xs font-mono text-slate-400 bg-slate-950 px-3 py-1 rounded-xl border border-slate-800">
                کۆی گشتی: {{ $recentMyTransactions->count() }} مامەڵەی ئەمڕۆ
            </span>
        </div>

        @if($recentMyTransactions->count() > 0)
            <div class="overflow-x-auto rounded-2xl border border-slate-800">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-950 text-slate-400 font-bold border-b border-slate-800">
                        <tr>
                            <th class="p-3">بارکۆد</th>
                            <th class="p-3">خاوەن مامەڵە / شۆفێر</th>
                            <th class="p-3">ژمارەی تابلۆ</th>
                            <th class="p-3">جۆری مامەڵە</th>
                            <th class="p-3 text-center">شوێنی ئێستا (لەکوێدایە؟)</th>
                            <th class="p-3 text-center">کرداری چاپی فیشە</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($recentMyTransactions as $tx)
                            @php
                                $loc = $tx->getCurrentLocationDescription();
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-3 font-mono font-black text-sky-400 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-slate-500 text-xs"></i>
                                        <span>{{ $tx->barcode }}</span>
                                    </div>
                                </td>
                                <td class="p-3 font-bold text-white whitespace-nowrap">
                                    <a href="{{ route('transactions.show', $tx->id) }}" class="hover:text-sky-300 transition">
                                        {{ $tx->visitor_name }}
                                    </a>
                                    @if($tx->carMake)
                                        <span class="block text-[10px] text-slate-400 font-normal">{{ $tx->carMake->name_kurdish }}</span>
                                    @endif
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <x-vehicle-plate :plate="$tx->plate_number" :type="$tx->plateType" size="sm" />
                                </td>
                                <td class="p-3 text-slate-300 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-950 text-slate-300 border border-slate-800 text-[11px]">
                                        {{ $tx->transactionType?->name_kurdish ?? '-' }}
                                    </span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-flex items-center gap-1
                                        @if($loc['badge_color'] === 'rose') bg-rose-500/20 text-rose-300 border-rose-500/40
                                        @elseif($loc['badge_color'] === 'amber') bg-amber-500/20 text-amber-300 border-amber-500/40
                                        @elseif($loc['badge_color'] === 'purple') bg-purple-500/20 text-purple-300 border-purple-500/40
                                        @elseif($loc['badge_color'] === 'sky') bg-sky-500/20 text-sky-300 border-sky-500/40
                                        @elseif($loc['badge_color'] === 'orange') bg-orange-500/20 text-orange-300 border-orange-500/40
                                        @else bg-emerald-500/20 text-emerald-300 border-emerald-500/40 @endif">
                                        <i class="fa-solid {{ $loc['icon'] }} text-[10px]"></i>
                                        <span>{{ $loc['title'] }}</span>
                                    </span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <a href="{{ route('transactions.print_data_entry', $tx->id) }}" target="_blank"
                                       class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs rounded-xl shadow-md shadow-emerald-600/30 transition inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-print text-xs"></i>
                                        <span>چاپی فیشە</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center bg-slate-950/60 rounded-2xl border border-slate-800 space-y-2">
                <i class="fa-solid fa-file-invoice text-3xl text-slate-600"></i>
                <p class="text-xs text-slate-400 font-bold">هیچ فیشەیەکی نوێ ئەمڕۆ داخڵ نەکراوە.</p>
                <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-1 text-xs text-emerald-400 hover:underline font-bold mt-1">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>کلیک بکە بۆ تۆمارکردنی یەکەم مامەڵە</span>
                </a>
            </div>
        @endif
    </div>

</div>

<!-- Live Real-Time Clock Script -->
<script>
    function updateClock() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        const dateEl = document.getElementById('dashLiveDate');
        const clockEl = document.getElementById('dashLiveClock');
        if (dateEl) dateEl.textContent = `${year}-${month}-${day}`;
        if (clockEl) clockEl.textContent = `${hours}:${minutes}:${seconds}`;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endsection

