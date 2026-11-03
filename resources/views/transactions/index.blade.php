@extends('layouts.app')

@section('content')
<div class="space-y-5" x-data="{ 
    showAdvancedSearch: {{ in_array($dateFilter, ['custom', 'yesterday', 'this_week', 'all']) || request()->filled('from_date') || request()->filled('to_date') ? 'true' : 'false' }},
    showDrawer: false,
    previewTx: null,
    plateInfo: null,
    openPreview(data) {
        this.previewTx = data;
        this.plateInfo = this.getPlateDetails(data.plate_number, data.plate_type);
        this.showDrawer = true;
    },
    getPlateDetails(plate, type) {
        let p = (plate || '').trim();
        let t = (type || '');
        let isTaxi = t.includes('کرێ') || t.includes('أجرة') || t.includes('تەکسی') || t.includes('نەفەر') || t.includes('پاس');
        let isCargo = t.includes('بار') || t.includes('حمل') || t.includes('بارهەڵگر');
        
        let colorClass = isTaxi ? 'bg-gradient-to-b from-red-600 via-rose-700 to-red-800 text-white border-red-900 shadow-md shadow-red-700/25' :
                         (isCargo ? 'bg-gradient-to-b from-amber-300 via-yellow-400 to-amber-500 text-slate-950 border-amber-600 shadow-md shadow-amber-500/20' :
                         'bg-gradient-to-b from-white via-slate-50 to-slate-200 text-slate-950 border-slate-400 shadow-md');
        
        let stripClass = isTaxi ? 'bg-red-950 text-white' : (isCargo ? 'bg-blue-800 text-white' : 'bg-blue-700 text-white');
        let textColor = isTaxi ? 'text-white' : 'text-slate-950';
        let subTextColor = isTaxi ? 'text-red-200' : (isCargo ? 'text-slate-800' : 'text-slate-600');
        let letterColor = isTaxi ? 'text-amber-300' : (isCargo ? 'text-rose-800' : 'text-rose-700');
        let typeLabel = isTaxi ? 'تەکسی / نەفەرات' : (isCargo ? 'بارهەڵگر' : 'تایبەت');

        let provinceCode = '';
        let provinceName = '';
        let letter = '';
        let number = p;
        
        let provinces = { '21': 'سلێمانی', '22': 'هەولێر', '23': 'هەڵەبجە', '24': 'دهۆک' };
        
        let m = p.match(/^(2[1-4])\s*([a-zA-Z\u0621-\u064A]?)\s*[-_\/]?\s*(\d+)$/);
        if (m) {
            provinceCode = m[1];
            letter = m[2].toUpperCase();
            number = m[3];
            provinceName = provinces[provinceCode] || '';
        } else {
            let m2 = p.match(/^([a-zA-Z\u0621-\u064A])\s*[-_\/]?\s*(\d+)$/);
            if (m2) {
                letter = m2[1].toUpperCase();
                number = m2[2];
            }
        }
        
        return { isTaxi, isCargo, colorClass, stripClass, textColor, subTextColor, letterColor, typeLabel, provinceCode, provinceName, letter, number, isNew: !!provinceCode };
    }
}" @keydown.escape.window="showDrawer = false">

    <!-- سەرپەڕە و کردارە سەرەکییەکان (Header & Actions) -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-900/60 border border-slate-800 p-4 rounded-2xl shadow-lg backdrop-blur-sm">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-sky-500/20">
                    <i class="fa-solid fa-list-check text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-white flex items-center gap-2">
                        بەڕێوەبردنی قۆناغەکانی مامەڵە
                    </h2>
                    <p class="text-xs text-slate-400">بەدواداچوون، پەسەندکردن و بەڕێکردنی مامەڵەکان بەپێی قۆناغی کارکردن.</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- نیشاندەری باری بەروار -->
            @if($dateFilter === 'today')
                <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    مامەڵەکانی ئەمڕۆ ({{ $today }})
                </span>
            @elseif($dateFilter === 'yesterday')
                <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-sky-500/10 text-sky-400 border border-sky-500/30 flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    مامەڵەکانی دوێنێ
                </span>
            @elseif($dateFilter === 'this_week')
                <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-calendar-week"></i>
                    ٧ ڕۆژی ڕابردوو
                </span>
            @elseif($dateFilter === 'all')
                <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-amber-500/10 text-amber-400 border border-amber-500/30 flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-database"></i>
                    گشت ڕۆژەکان و ئەرشیف
                </span>
            @else
                <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-purple-500/10 text-purple-400 border border-purple-500/30 flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-calendar-days"></i>
                    بەرواری دیاریکراو
                </span>
            @endif

            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('restore_records') }}" onclick="return confirm('ئایا دڵنیایت لە هاوردەکردن/گەڕاندنەوەی سەرجەم ٨٤ تۆمارە بنەڕەتییەکان و هەموو داتاکانی ئەکسێس؟');" class="px-3.5 py-2 bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-300 border border-indigo-500/40 font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5" title="گەڕاندنەوەی هەموو تۆمارە کۆنەکان و هاوردەکراوەکان">
                    <i class="fa-solid fa-cloud-arrow-down text-indigo-400"></i> گەڕاندنەوەی سەرجەم تۆمارەکان (84 تۆمار)
                </a>
                <a href="{{ route('transactions.trash') }}" class="px-3.5 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                    <i class="fa-solid fa-trash-can text-rose-400"></i> سڕاوەکان
                </a>
            @endif

            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'data_entry'))
                <a href="{{ route('transactions.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/25 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-plus-circle"></i> مامەڵەی نوێ
                </a>
            @endif
        </div>
    </div>

    <!-- تابەکانی قۆناغەکان بە جوانترین شێواز و ژمارەی ڕاستەوخۆ (Stage Navigation Tabs) -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-2.5 shadow-lg backdrop-blur-sm">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-thin">
            <!-- 1. All Stages -->
            @php
                $currentParams = request()->except(['stage', 'page']);
            @endphp
            <a href="{{ route('transactions.index', array_merge($currentParams, ['stage' => 'all'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 whitespace-nowrap {{ ($stage ?? 'all') === 'all' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-700/60' }}">
                <i class="fa-solid fa-layer-group text-sm"></i>
                <span>گشت قۆناغەکان</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono {{ ($stage ?? 'all') === 'all' ? 'bg-white/20 text-white' : 'bg-slate-700 text-slate-300' }}">
                    {{ number_format($stageCounts['all'] ?? 0) }}
                </span>
            </a>

            <!-- 2. Inspection -->
            <a href="{{ route('transactions.index', array_merge($currentParams, ['stage' => 'inspection'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 whitespace-nowrap {{ ($stage ?? '') === 'inspection' ? 'bg-orange-600 text-white shadow-md shadow-orange-600/30 ring-2 ring-orange-500/40' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-700/60' }}">
                <i class="fa-solid fa-clipboard-check text-orange-400"></i>
                <span>١. کەشف و تەخمین</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono {{ ($stage ?? '') === 'inspection' ? 'bg-white/20 text-white' : 'bg-orange-500/20 text-orange-300 border border-orange-500/30' }}">
                    {{ number_format($stageCounts['inspection'] ?? 0) }}
                </span>
            </a>

            <!-- 3. Audit -->
            <a href="{{ route('transactions.index', array_merge($currentParams, ['stage' => 'audit'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 whitespace-nowrap {{ ($stage ?? '') === 'audit' ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30 ring-2 ring-purple-500/40' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-700/60' }}">
                <i class="fa-solid fa-stamp text-purple-400"></i>
                <span>٢. وردبینی</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono {{ ($stage ?? '') === 'audit' ? 'bg-white/20 text-white' : 'bg-purple-500/20 text-purple-300 border border-purple-500/30' }}">
                    {{ number_format($stageCounts['audit'] ?? 0) }}
                </span>
            </a>

            <!-- 4. Payment / Cashier -->
            <a href="{{ route('transactions.index', array_merge($currentParams, ['stage' => 'payment'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 whitespace-nowrap {{ ($stage ?? '') === 'payment' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 ring-2 ring-emerald-500/40' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-700/60' }}">
                <i class="fa-solid fa-receipt text-emerald-400"></i>
                <span>٣. ژمێریاری (وەسڵی ۳۷/أ)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono {{ ($stage ?? '') === 'payment' ? 'bg-white/20 text-white' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                    {{ number_format($stageCounts['payment'] ?? 0) }}
                </span>
            </a>

            <!-- 5. Booklet Section -->
            <a href="{{ route('transactions.index', array_merge($currentParams, ['stage' => 'booklet'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 whitespace-nowrap {{ ($stage ?? '') === 'booklet' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 ring-2 ring-blue-500/40' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-700/60' }}">
                <i class="fa-solid fa-passport text-blue-400"></i>
                <span>٤. بەشی دەفتەر</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono {{ ($stage ?? '') === 'booklet' ? 'bg-white/20 text-white' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30' }}">
                    {{ number_format($stageCounts['booklet'] ?? 0) }}
                </span>
            </a>

            <!-- 6. Completed -->
            <a href="{{ route('transactions.index', array_merge($currentParams, ['stage' => 'completed'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 whitespace-nowrap {{ ($stage ?? '') === 'completed' ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30 ring-2 ring-teal-500/40' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-700/60' }}">
                <i class="fa-solid fa-circle-check text-teal-400"></i>
                <span>تەواوکراوەکان</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono {{ ($stage ?? '') === 'completed' ? 'bg-white/20 text-white' : 'bg-teal-500/20 text-teal-300 border border-teal-500/30' }}">
                    {{ number_format($stageCounts['completed'] ?? 0) }}
                </span>
            </a>

            <!-- 7. Cancelled -->
            <a href="{{ route('transactions.index', array_merge($currentParams, ['stage' => 'cancelled'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 whitespace-nowrap {{ ($stage ?? '') === 'cancelled' ? 'bg-rose-700 text-white shadow-md shadow-rose-700/30 ring-2 ring-rose-500/40' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-700/60' }}">
                <i class="fa-solid fa-ban text-rose-400"></i>
                <span>پووچەڵکراوەکان</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono {{ ($stage ?? '') === 'cancelled' ? 'bg-white/20 text-white' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
                    {{ number_format($stageCounts['cancelled'] ?? 0) }}
                </span>
            </a>
        </div>
    </div>

    <!-- فۆڕمی سەرەوە: گەڕان، دیاریکردنی بەروار و هەڵبژاردنی پەڕە (15، 30، 50) -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-xl space-y-3.5">
        <form action="{{ route('transactions.index') }}" method="GET" id="topFilterForm" class="space-y-3">
            <!-- پاراستنی باری قۆناغ و فلتەری بەروار لە فۆڕمدا -->
            <input type="hidden" name="stage" value="{{ $stage ?? 'all' }}">
            <input type="hidden" name="date_filter" id="activeDateFilterInput" value="{{ $dateFilter ?? 'today' }}">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-center">
                <!-- دوگمەکانی خێرای بەروار (ئەمڕۆ بەرامبەر ڕۆژانی تر) -->
                <div class="lg:col-span-4 flex items-center gap-1.5 bg-slate-950 p-1 rounded-xl border border-slate-800">
                    <button type="submit" onclick="document.getElementById('activeDateFilterInput').value='today'" 
                            class="flex-1 py-1.5 px-3 rounded-lg text-xs font-black transition flex items-center justify-center gap-1.5 {{ $dateFilter === 'today' ? 'bg-sky-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                        <i class="fa-solid fa-calendar-day"></i>
                        <span>تەنها بۆ ئەمڕۆ</span>
                    </button>
                    <button type="button" @click="showAdvancedSearch = !showAdvancedSearch"
                            class="flex-1 py-1.5 px-3 rounded-lg text-xs font-black transition flex items-center justify-center gap-1.5 {{ in_array($dateFilter, ['custom', 'yesterday', 'this_week', 'all']) ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                        <i class="fa-solid fa-sliders"></i>
                        <span>باقی ڕۆژەکان & فلتەر</span>
                        <i class="fa-solid fa-chevron-down text-[10px] transition transform" :class="showAdvancedSearch ? 'rotate-180' : ''"></i>
                    </button>
                </div>

                <!-- گەڕانی دەقی خێرا -->
                <div class="lg:col-span-5 relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="گەڕان بەپێی بارکۆد، ناوی شۆفێر، ژمارەی تابلۆ، دەفتەر..."
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl pr-10 pl-4 py-2 text-xs font-medium text-slate-100 placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none transition">
                    <button type="submit" class="absolute right-3 top-2.5 text-slate-400 hover:text-sky-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('transactions.index', ['stage' => $stage ?? 'all', 'date_filter' => $dateFilter]) }}" 
                           class="absolute left-3 top-2.5 text-slate-500 hover:text-rose-400 text-xs" title="سڕینەوەی گەڕان">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>

                <!-- پەڕە و ژمارەی پیشاندان لە سەرەوە (خاڵی ٤: 15، 30، 50) -->
                <div class="lg:col-span-3 flex items-center justify-end gap-2.5">
                    <div class="flex items-center gap-1.5 bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800 text-xs">
                        <label for="per_page_select" class="text-slate-400 whitespace-nowrap text-[11px] font-bold">پیشاندان:</label>
                        <select id="per_page_select" name="per_page" onchange="document.getElementById('topFilterForm').submit()" 
                                class="bg-transparent text-sky-400 font-mono font-black focus:outline-none cursor-pointer">
                            <option value="15" {{ ($perPage ?? 15) == 15 ? 'selected' : '' }} class="bg-slate-900 text-white">١٥ دانە</option>
                            <option value="30" {{ ($perPage ?? 15) == 30 ? 'selected' : '' }} class="bg-slate-900 text-white">٣٠ دانە</option>
                            <option value="50" {{ ($perPage ?? 15) == 50 ? 'selected' : '' }} class="bg-slate-900 text-white">٥٠ دانە</option>
                            <option value="100" {{ ($perPage ?? 15) == 100 ? 'selected' : '' }} class="bg-slate-900 text-white">١٠٠ دانە</option>
                        </select>
                    </div>

                    @if($transactions->hasPages())
                        <div class="flex items-center gap-1">
                            @if($transactions->onFirstPage())
                                <span class="px-2 py-1.5 bg-slate-950 text-slate-600 rounded-lg text-xs cursor-not-allowed border border-slate-800">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </span>
                            @else
                                <a href="{{ $transactions->previousPageUrl() }}" class="px-2 py-1.5 bg-slate-800 text-sky-400 hover:bg-slate-700 rounded-lg text-xs border border-slate-700 transition" title="پەڕەی پێشوو">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            @endif

                            <span class="px-2.5 py-1 text-slate-300 font-mono text-xs font-bold bg-slate-950 border border-slate-800 rounded-lg">
                                {{ $transactions->currentPage() }}/{{ $transactions->lastPage() }}
                            </span>

                            @if($transactions->hasMorePages())
                                <a href="{{ $transactions->nextPageUrl() }}" class="px-2 py-1.5 bg-slate-800 text-sky-400 hover:bg-slate-700 rounded-lg text-xs border border-slate-700 transition" title="پەڕەی دواتر">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </a>
                            @else
                                <span class="px-2 py-1.5 bg-slate-950 text-slate-600 rounded-lg text-xs cursor-not-allowed border border-slate-800">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- فۆڕمی تایبەت بۆ باقی ڕۆژەکان و گەڕانی پێشکەوتوو (Advanced Search Drawer) -->
            <div x-show="showAdvancedSearch" x-cloak x-transition.duration.200ms
                 class="pt-3.5 mt-2 border-t border-slate-800 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 bg-slate-950/70 p-3.5 rounded-xl border border-slate-800/80">
                
                <!-- هەڵبژاردنی بەرواری خێرا -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 mb-1">فلتەری کاتیی خێرا:</label>
                    <div class="grid grid-cols-3 gap-1">
                        <button type="submit" onclick="document.getElementById('activeDateFilterInput').value='yesterday'" class="py-1 px-1.5 rounded-lg text-[11px] font-bold border transition text-center {{ $dateFilter === 'yesterday' ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-300 hover:bg-slate-800' }}">دوێنێ</button>
                        <button type="submit" onclick="document.getElementById('activeDateFilterInput').value='this_week'" class="py-1 px-1.5 rounded-lg text-[11px] font-bold border transition text-center {{ $dateFilter === 'this_week' ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-300 hover:bg-slate-800' }}">٧ ڕۆژ</button>
                        <button type="submit" onclick="document.getElementById('activeDateFilterInput').value='all'" class="py-1 px-1.5 rounded-lg text-[11px] font-bold border transition text-center {{ $dateFilter === 'all' ? 'bg-amber-600 border-amber-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-300 hover:bg-slate-800' }}">گشت کات</button>
                    </div>
                </div>

                <!-- لە بەرواری -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 mb-1">لە بەرواری (From Date):</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" onchange="document.getElementById('activeDateFilterInput').value='custom'"
                           class="w-full bg-slate-900 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:border-sky-500 focus:outline-none">
                </div>

                <!-- تا بەرواری -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 mb-1">تا بەرواری (To Date):</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" onchange="document.getElementById('activeDateFilterInput').value='custom'"
                           class="w-full bg-slate-900 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:border-sky-500 focus:outline-none">
                </div>

                <!-- جۆری مامەڵە & دوگمەی ئەپلای -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 mb-1">جۆری مامەڵە:</label>
                    <div class="flex items-center gap-1.5">
                        <select name="transaction_type_id" class="w-full bg-slate-900 border border-slate-800 rounded-lg px-2 py-1.5 text-xs text-slate-200 focus:border-sky-500 focus:outline-none">
                            <option value="">گشت جۆرەکان</option>
                            @foreach($transactionTypes as $tt)
                                <option value="{{ $tt->id }}" {{ request('transaction_type_id') == $tt->id ? 'selected' : '' }}>{{ $tt->name_kurdish }}</option>
                            @endforeach
                        </select>
                        <button type="submit" onclick="if(document.querySelector('input[name=from_date]').value || document.querySelector('input[name=to_date]').value) document.getElementById('activeDateFilterInput').value='custom'" 
                                class="px-3.5 py-1.5 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-lg shadow transition whitespace-nowrap">
                            جێبەجێکردن
                        </button>
                        <a href="{{ route('transactions.index', ['stage' => $stage ?? 'all', 'date_filter' => 'today']) }}" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-lg text-xs transition" title="پاککردنەوە بۆ ئەمڕۆ">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- خشتەی ڕێکخراو و پاکی مامەڵەکان (Clean, Professional & Uncluttered Table) -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <!-- زانیاری پەڕە و ژمارە لە سەرەوەی خشتە -->
        <div class="px-4 py-2.5 bg-slate-950/60 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
            <div class="flex items-center gap-2 flex-wrap">
                <i class="fa-solid fa-table-list text-sky-400"></i>
                <span>پیشاندانی 
                    <strong class="font-mono text-white">{{ $transactions->firstItem() ?? 0 }}</strong> تا 
                    <strong class="font-mono text-white">{{ $transactions->lastItem() ?? 0 }}</strong> 
                    لە کۆی <strong class="font-mono text-emerald-400">{{ number_format($transactions->total()) }}</strong> مامەڵە
                </span>
                
                <!-- Active Date Filter Badge -->
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border flex items-center gap-1 {{ $dateFilter === 'today' ? 'bg-sky-500/10 text-sky-300 border-sky-500/30' : ($dateFilter === 'all' ? 'bg-amber-500/10 text-amber-300 border-amber-500/30' : 'bg-indigo-500/10 text-indigo-300 border-indigo-500/30') }}">
                    <i class="fa-solid fa-clock"></i>
                    @if($dateFilter === 'today')
                        تەنها ئەمڕۆ ({{ date('Y-m-d') }})
                    @elseif($dateFilter === 'yesterday')
                        دوێنێ ({{ now()->subDay()->format('Y-m-d') }})
                    @elseif($dateFilter === 'this_week')
                        ٧ ڕۆژی ڕابردوو
                    @elseif($dateFilter === 'custom')
                        دەستنیشانکراو ({{ request('from_date') ?? '...' }} تا {{ request('to_date') ?? '...' }})
                    @else
                        گشت کاتەکان (بێ سنووردارکردنی کات)
                    @endif
                </span>
            </div>
            <div class="text-[11px] text-slate-500">
                قۆناغ: <span class="text-sky-300 font-bold">
                    @if(($stage ?? 'all') === 'inspection') ١. کەشف و تەخمین
                    @elseif(($stage ?? '') === 'audit') ٢. وردبینی
                    @elseif(($stage ?? '') === 'payment') ٣. ژمێریاری
                    @elseif(($stage ?? '') === 'booklet') ٤. بەشی دەفتەر
                    @elseif(($stage ?? '') === 'completed') تەواوکراوەکان
                    @elseif(($stage ?? '') === 'cancelled') پووچەڵکراوەکان
                    @else گشت قۆناغەکان
                    @endif
                </span>
            </div>
        </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-table-list text-sky-400"></i>
                <span>پیشاندانی 
                    <strong class="font-mono text-white">{{ $transactions->firstItem() ?? 0 }}</strong> تا 
                    <strong class="font-mono text-white">{{ $transactions->lastItem() ?? 0 }}</strong> 
                    لە کۆی <strong class="font-mono text-emerald-400">{{ number_format($transactions->total()) }}</strong> مامەڵە
                </span>
            </div>
            <div class="text-[11px] text-slate-500">
                قۆناغ: <span class="text-sky-300 font-bold">
                    @if(($stage ?? 'all') === 'inspection') ١. کەشف و تەخمین
                    @elseif(($stage ?? '') === 'audit') ٢. وردبینی
                    @elseif(($stage ?? '') === 'payment') ٣. ژمێریاری
                    @elseif(($stage ?? '') === 'booklet') ٤. بەشی دەفتەر
                    @elseif(($stage ?? '') === 'completed') تەواوکراوەکان
                    @elseif(($stage ?? '') === 'cancelled') پووچەڵکراوەکان
                    @else گشت قۆناغەکان
                    @endif
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-300 border-collapse">
                <thead class="bg-slate-950 text-slate-400 text-[11px] font-black uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-2.5 px-3.5 text-center w-12">#</th>
                        <th class="py-2.5 px-3.5">بارکۆد & بەروار</th>
                        <th class="py-2.5 px-3.5">هاووڵاتی</th>
                        <th class="py-2.5 px-3.5">ئۆتۆمبێل & تابلۆ</th>
                        <th class="py-2.5 px-3.5">جۆری مامەڵە</th>
                        <th class="py-2.5 px-3.5">دەفتەر & بۆکس</th>
                        <th class="py-2.5 px-3.5">وەسڵ & بڕی پارە</th>
                        <th class="py-2.5 px-3.5 text-center">قۆناغ</th>
                        <th class="py-2.5 px-3.5 text-center w-28">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($transactions as $index => $tx)
                        @php
                            $txPayload = [
                                'id' => $tx->id,
                                'barcode' => $tx->barcode,
                                'visitor_name' => $tx->visitor_name,
                                'visitor_name_eng' => $tx->visitor_name_eng,
                                'phone_number' => $tx->phone_number,
                                'second_driver_name' => $tx->second_driver_name,
                                'plate_number' => $tx->plate_number,
                                'plate_type' => $tx->plateType->name_kurdish ?? 'تایبەت',
                                'car_make' => $tx->carMake->name_kurdish ?? '',
                                'car_color' => $tx->carColor->name_kurdish ?? '',
                                'model_year' => $tx->model_year,
                                'chassis_number' => $tx->chassis_number,
                                'transaction_type' => $tx->transactionType->name_kurdish ?? '',
                                'total_pay' => number_format($tx->total_pay),
                                'receipt_37a' => $tx->receipt_37a_number,
                                'booklet_number' => $tx->booklet_number,
                                'boxfile' => $tx->NO_boxfile,
                                'boxfile_puchal' => $tx->NO_boxfile_puchal,
                                'stage_name' => $tx->getCurrentStageName(),
                                'is_cancelled' => (bool)$tx->is_cancelled,
                                'is_inspected' => (bool)$tx->is_inspected,
                                'is_audited' => (bool)$tx->is_audited,
                                'is_paid' => (bool)$tx->is_paid,
                                'is_booklet_completed' => (bool)$tx->is_booklet_completed,
                                'is_submitted' => (bool)$tx->is_submitted,
                                'is_returned' => (bool)$tx->is_returned,
                                'return_reason' => $tx->return_reason,
                                'user_input' => $tx->user_input,
                                'date' => $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : ($tx->created_at ? $tx->created_at->format('Y-m-d') : ''),
                                'show_url' => route('transactions.show', $tx->id),
                            ];
                        @endphp
                        <tr class="hover:bg-slate-800/50 transition">
                            <!-- ژمارەی ڕیزبەند -->
                            <td class="py-3 px-3.5 text-center text-slate-500 font-mono text-[11px]">
                                {{ ($transactions->firstItem() ?? 1) + $index }}
                            </td>

                            <!-- بارکۆد و بەروار -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                <button type="button" @click="openPreview({{ json_encode($txPayload) }})" 
                                        class="font-mono text-xs font-black text-sky-400 bg-sky-950/60 hover:bg-sky-900 px-2 py-0.5 rounded border border-sky-800/50 transition cursor-pointer" 
                                        title="کلیک بکە بۆ پێشبینی خێرا">
                                    {{ $tx->barcode }}
                                </button>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5 flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[9px]"></i>
                                    {{ $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : ($tx->created_at ? $tx->created_at->format('Y-m-d') : '-') }}
                                </div>
                            </td>

                            <!-- ناوی هاووڵاتی -->
                            <td class="py-2.5 px-3.5 max-w-[180px]">
                                <button type="button" @click="openPreview({{ json_encode($txPayload) }})" 
                                        class="font-bold text-white text-xs hover:text-sky-400 transition text-right truncate block w-full cursor-pointer" 
                                        title="کلیک بکە بۆ پێشبینی خێرا">
                                    {{ $tx->visitor_name }}
                                </button>
                                @if($tx->phone_number)
                                    <span class="text-[10px] text-slate-400 font-mono flex items-center gap-1 mt-0.5 truncate" dir="ltr">
                                        <i class="fa-solid fa-phone text-[8px] text-emerald-400"></i>{{ $tx->phone_number }}
                                    </span>
                                @endif
                            </td>

                            <!-- ئۆتۆمبێل و تابلۆ -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                <div>
                                    <x-vehicle-plate :plate="$tx->plate_number" :type="$tx->plateType" size="sm" />
                                </div>
                                <div class="text-[11px] text-slate-300 font-semibold mt-0.5">
                                    {{ $tx->carMake->name_kurdish ?? 'ئۆتۆمبێل' }}
                                    @if($tx->model_year)
                                        <span class="text-[10px] font-mono text-slate-400 font-normal">({{ $tx->model_year }})</span>
                                    @endif
                                </div>
                            </td>

                            <!-- جۆری مامەڵە -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold bg-slate-800 text-slate-200 border border-slate-700">
                                    {{ $tx->transactionType->name_kurdish ?? '-' }}
                                </span>
                            </td>

                            <!-- ژمارەی دەفتەر و بۆکس -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                @if($tx->booklet_number)
                                    <div class="font-mono font-black text-sky-400 text-xs flex items-center gap-1">
                                        <i class="fa-solid fa-passport text-[10px]"></i>
                                        {{ $tx->booklet_number }}
                                    </div>
                                @else
                                    <span class="text-slate-600 font-mono text-xs">-</span>
                                @endif
                                @if($tx->NO_boxfile || $tx->NO_boxfile_puchal)
                                    <span class="text-[10px] font-mono text-slate-400 block mt-0.5">
                                        بۆکس: {{ $tx->NO_boxfile ?: $tx->NO_boxfile_puchal }}
                                    </span>
                                @endif
                            </td>

                            <!-- وەسڵ و کۆی پارە -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                <div class="font-mono font-black text-white text-xs">
                                    {{ number_format($tx->total_pay) }} <span class="text-[10px] text-slate-400 font-normal">د.ع</span>
                                </div>
                                @if($tx->receipt_37a_number)
                                    <div class="text-[10px] text-emerald-400 font-mono mt-0.5 font-bold">
                                        وەسڵ: {{ $tx->receipt_37a_number }}
                                    </div>
                                @endif
                            </td>

                            <!-- قۆناغی ئێستا (Stage Badge) -->
                            <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                                @if($tx->is_cancelled)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500/15 text-rose-400 border border-rose-500/30 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-ban text-[8px]"></i> پووچەڵکراوە
                                    </span>
                                @elseif($tx->is_submitted)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-teal-500/15 text-teal-300 border border-teal-500/30 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-box-archive text-[8px]"></i> ئەڕشیفکراو
                                    </span>
                                @elseif($tx->is_booklet_completed)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-check text-[8px]"></i> دەفتەر تەواو
                                    </span>
                                @elseif($tx->is_paid)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-500/15 text-blue-300 border border-blue-500/30 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-passport text-[8px]"></i> بەشی دەفتەر
                                    </span>
                                @elseif($tx->is_audited)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-receipt text-[8px]"></i> چاوەڕێی وەسڵ
                                    </span>
                                @elseif($tx->is_inspected)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-500/15 text-purple-300 border border-purple-500/30 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-stamp text-[8px]"></i> چاوەڕێی وردبینی
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-orange-500/15 text-orange-300 border border-orange-500/30 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-clipboard-check text-[8px]"></i> چاوەڕێی کەشف
                                    </span>
                                @endif
                            </td>

                            <!-- کردار (Action) -->
                            <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- دوگمەی چاو (پێشبینی خێرا) -->
                                    <button type="button" 
                                            @click="openPreview({{ json_encode($txPayload) }})" 
                                            class="px-2.5 py-1.5 bg-slate-800 hover:bg-sky-600 text-sky-400 hover:text-white rounded-xl text-xs font-bold border border-slate-700 hover:border-sky-500 transition shadow-sm cursor-pointer flex items-center gap-1" 
                                            title="پێشبینی خێرا بەبێ چوونە دەرەوە">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                        <span class="hidden xl:inline text-[11px]">پێشبینی</span>
                                    </button>

                                    <!-- دوگمەی چوونە ژوورەوەی سەرەکی (Show Page) -->
                                    <a href="{{ route('transactions.show', $tx->id) }}" 
                                       class="px-2.5 py-1.5 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-black shadow-md shadow-sky-600/20 transition inline-flex items-center gap-1"
                                       title="چوونە ناو مامەڵە">
                                        <span>بینین</span>
                                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center">
                                <div class="max-w-md mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-800 text-slate-500 flex items-center justify-center mx-auto text-xl">
                                        <i class="fa-solid fa-inbox"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-300">هیچ مامەڵەیەک نەدۆزرایەوە!</h4>
                                    @if($dateFilter === 'today')
                                        <p class="text-xs text-slate-500">
                                            بۆ ئەمڕۆ ({{ $today }}) هێشتا هیچ مامەڵەیەک لەم قۆناغە تۆمار نەکراوە.
                                        </p>
                                        <a href="{{ route('transactions.index', ['stage' => $stage ?? 'all', 'date_filter' => 'all']) }}" 
                                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-sky-400 text-xs font-bold rounded-xl border border-slate-700 transition">
                                            <i class="fa-solid fa-calendar-days"></i> بینینی گشت کاتەکان و مامەڵەکانی تر
                                        </a>
                                    @else
                                        <p class="text-xs text-slate-500">بەپێی ئەو فلتەر و گەڕانەی دیاریتکردووە هیچ داتایەک نەدۆزرایەوە.</p>
                                        <a href="{{ route('transactions.index', ['stage' => 'all', 'date_filter' => 'today']) }}" 
                                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl border border-slate-700 transition">
                                            <i class="fa-solid fa-rotate-left"></i> گەڕانەوە بۆ ئەمڕۆ
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- پەڕەبەندی خوارەوە (Bottom Pagination) -->
        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/60 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-400">
                    پەڕەی <span class="font-mono text-white font-bold">{{ $transactions->currentPage() }}</span> لە کۆی <span class="font-mono text-white font-bold">{{ $transactions->lastPage() }}</span>
                </div>
                <div>
                    {{ $transactions->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- پەنجەرەی پێشبینی خێرا (Quick Preview Drawer) -->
    <!-- ========================================== -->
    <div x-show="showDrawer" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-hidden" 
         aria-labelledby="slide-over-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop Blur overlay -->
        <div x-show="showDrawer"
             x-transition:enter="ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showDrawer = false"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-y-0 left-0 max-w-full flex pl-0 sm:pl-10">
            <div x-show="showDrawer"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="w-screen max-w-lg bg-slate-900 border-r border-slate-800 shadow-2xl flex flex-col justify-between overflow-y-auto">

                <!-- Drawer Header -->
                <div class="p-4 sm:p-5 border-b border-slate-800 bg-slate-950/80 flex items-center justify-between sticky top-0 z-10 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-sky-500/20 shrink-0">
                            <i class="fa-solid fa-file-invoice text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm sm:text-base font-black text-white truncate" id="slide-over-title">
                                    پێشبینی خێرای مامەڵە
                                </h3>
                                <span class="font-mono text-xs font-black text-sky-400 bg-sky-950 px-2 py-0.5 rounded border border-sky-800 shrink-0" x-text="previewTx?.barcode"></span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                                بەروار: <span class="font-mono text-slate-300" x-text="previewTx?.date"></span> | تۆمارکار: <span class="text-slate-300" x-text="previewTx?.user_input"></span>
                            </p>
                        </div>
                    </div>

                    <button @click="showDrawer = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-rose-600 transition flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Drawer Content Body -->
                <div class="p-4 sm:p-5 space-y-4 text-xs">
                    <!-- باری ئێستای قۆناغ -->
                    <div class="p-3 rounded-xl border flex items-center justify-between"
                         :class="{
                            'bg-rose-500/10 border-rose-500/30 text-rose-300': previewTx?.is_cancelled || previewTx?.is_returned,
                            'bg-emerald-500/10 border-emerald-500/30 text-emerald-300': previewTx?.is_submitted,
                            'bg-sky-500/10 border-sky-500/30 text-sky-300': !previewTx?.is_cancelled && !previewTx?.is_returned && !previewTx?.is_submitted
                         }">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid" :class="{
                                'fa-ban': previewTx?.is_cancelled,
                                'fa-triangle-exclamation animate-pulse': previewTx?.is_returned,
                                'fa-box-archive': previewTx?.is_submitted,
                                'fa-clock': !previewTx?.is_cancelled && !previewTx?.is_returned && !previewTx?.is_submitted
                            }"></i>
                            <span class="font-black text-sm" x-text="previewTx?.stage_name"></span>
                        </div>
                        <span class="font-black text-xs sm:text-sm px-3 py-1 rounded-xl bg-gradient-to-r from-sky-500/20 to-indigo-500/20 text-sky-200 border border-sky-400/40 shadow-sm flex items-center gap-1.5" x-text="previewTx?.transaction_type"></span>
                    </div>

                    <!-- ئاگاداری گەڕاندنەوە ئەگەر هەبێت -->
                    <template x-if="previewTx?.is_returned">
                        <div class="p-3 bg-rose-950/80 border border-rose-500/60 rounded-xl text-rose-200 space-y-1">
                            <span class="font-bold flex items-center gap-1.5 text-rose-300">
                                <i class="fa-solid fa-circle-exclamation"></i> هۆکاری گەڕاندنەوە لەلایەن وردبین:
                            </span>
                            <p class="text-xs font-medium" x-text="previewTx?.return_reason"></p>
                        </div>
                    </template>

                    <!-- تابلۆی فەرمی ئۆتۆمبێل (Kurdistan License Plate) -->
                    <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-2xl flex flex-col items-center justify-center space-y-2">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">تابلۆی فەرمی ئۆتۆمبێل</span>
                        
                        <!-- تابلۆی داینامیکی گرافیکی -->
                        <div class="inline-flex items-center select-none font-mono border-2 rounded-xl overflow-hidden ring-2 ring-black/20 shadow-lg relative p-0.5" 
                             :class="plateInfo?.colorClass" dir="ltr">
                            <!-- Screw Holes -->
                            <div class="absolute left-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full border shadow-inner opacity-80"
                                 :class="plateInfo?.isTaxi ? 'bg-red-900 border-red-950' : (plateInfo?.isCargo ? 'bg-amber-700 border-amber-800' : 'bg-slate-400 border-slate-500')"></div>
                            <div class="absolute right-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full border shadow-inner opacity-80"
                                 :class="plateInfo?.isTaxi ? 'bg-red-900 border-red-950' : (plateInfo?.isCargo ? 'bg-amber-700 border-amber-800' : 'bg-slate-400 border-slate-500')"></div>

                            <!-- Left Blue/Dark Band -->
                            <div class="h-11 px-2.5 flex flex-col justify-center items-center text-[9px] font-black tracking-wider leading-tight border-r border-black/30 rounded-l-lg ml-2"
                                 :class="plateInfo?.stripClass">
                                <span class="text-[7px] tracking-widest opacity-80">IRAQ</span>
                                <span class="text-xs font-black text-amber-300">KR</span>
                                <template x-if="plateInfo?.isNew && plateInfo?.provinceName">
                                    <span class="text-[7px] text-emerald-200 mt-0.5 font-sans font-bold" dir="rtl" x-text="plateInfo.provinceName"></span>
                                </template>
                            </div>

                            <!-- Center Plate -->
                            <div class="px-3.5 py-1 flex items-center gap-2" :class="plateInfo?.textColor">
                                <template x-if="plateInfo?.isNew && plateInfo?.provinceCode">
                                    <span class="text-base font-black border-r border-black/20 pr-1.5 font-mono" :class="plateInfo?.subTextColor" x-text="plateInfo.provinceCode"></span>
                                </template>
                                <template x-if="plateInfo?.letter">
                                    <span class="text-lg font-black px-1.5 py-0.5 rounded border font-mono" 
                                          :class="plateInfo?.letterColor + ' ' + (plateInfo?.isTaxi ? 'bg-black/20 border-white/30' : 'bg-rose-500/10 border-rose-300/60')" 
                                          x-text="plateInfo.letter"></span>
                                </template>
                                <span class="text-xl font-black font-mono tracking-widest drop-shadow-sm" :class="plateInfo?.textColor" x-text="plateInfo?.number"></span>
                            </div>

                            <!-- Right Tag -->
                            <div class="px-2.5 py-1 mr-2 text-[10px] font-black border-l border-black/20 flex flex-col items-center justify-center" :class="plateInfo?.subTextColor" dir="rtl">
                                <template x-if="plateInfo?.isNew && plateInfo?.provinceName">
                                    <span class="text-[11px] font-black text-sky-400" x-text="'IRAQ - ' + plateInfo.provinceName"></span>
                                </template>
                                <span class="text-[10px] font-black" x-text="plateInfo?.typeLabel"></span>
                            </div>
                        </div>

                        <!-- زانیاری ئۆتۆمبێل -->
                        <div class="text-center pt-1">
                            <span class="text-sm font-bold text-white" x-text="(previewTx?.car_make || '-') + (previewTx?.model_year ? ' (' + previewTx.model_year + ')' : '')"></span>
                            <span class="text-slate-400 font-semibold text-xs mr-1" x-text="previewTx?.car_color ? ' | ' + previewTx.car_color : ''"></span>
                            <template x-if="previewTx?.chassis_number && previewTx?.chassis_number !== '-'">
                                <div class="mt-1 text-[11px] font-mono text-slate-400 bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                                    <span class="text-slate-500">VIN:</span>
                                    <span class="text-sky-300 font-bold uppercase select-all" x-text="previewTx.chassis_number"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- زانیاری هاووڵاتی و پەیوەندی -->
                    <div class="bg-slate-950/60 border border-slate-800 p-3.5 rounded-xl space-y-2">
                        <span class="text-[11px] font-bold text-sky-400 block border-b border-slate-800 pb-1">
                            <i class="fa-solid fa-user ml-1"></i> زانیاری هاووڵاتی و شۆفێر
                        </span>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">ناوی هاووڵاتی:</span>
                            <span class="font-bold text-white text-sm" x-text="previewTx?.visitor_name"></span>
                        </div>
                        <template x-if="previewTx?.visitor_name_eng">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">ناوی ئینگلیزی:</span>
                                <span class="font-mono text-slate-300 text-xs" dir="ltr" x-text="previewTx.visitor_name_eng"></span>
                            </div>
                        </template>
                        <template x-if="previewTx?.phone_number">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">مۆبایل:</span>
                                <a :href="'tel:' + previewTx.phone_number" class="font-mono text-emerald-400 font-bold hover:underline flex items-center gap-1" dir="ltr">
                                    <i class="fa-solid fa-phone text-[10px]"></i>
                                    <span x-text="previewTx.phone_number"></span>
                                </a>
                            </div>
                        </template>
                        <template x-if="previewTx?.second_driver_name">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">شۆفێری دووەم:</span>
                                <span class="font-bold text-slate-200" x-text="previewTx.second_driver_name"></span>
                            </div>
                        </template>
                    </div>

                    <!-- زانیاری دارایی و دەفتەر و بۆکس -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="bg-slate-950/60 border border-slate-800 p-3 rounded-xl space-y-1">
                            <span class="text-slate-400 text-[11px] block">کۆی بڕی پارە:</span>
                            <div class="font-mono font-black text-emerald-400 text-base">
                                <span x-text="previewTx?.total_pay"></span> <span class="text-xs text-slate-400 font-normal">د.ع</span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                وەسڵ: <span class="font-mono font-bold text-white" x-text="previewTx?.receipt_37a || 'چاوەڕێیە'"></span>
                            </div>
                        </div>

                        <div class="bg-slate-950/60 border border-slate-800 p-3 rounded-xl space-y-1">
                            <span class="text-slate-400 text-[11px] block">دەفتەر & بۆکس:</span>
                            <div class="font-mono font-black text-blue-400 text-sm flex items-center gap-1">
                                <i class="fa-solid fa-passport text-xs"></i>
                                <span x-text="previewTx?.booklet_number || 'بێ دەفتەر'"></span>
                            </div>
                            <div class="text-[10px] font-mono mt-0.5 flex items-center gap-1">
                                <template x-if="previewTx?.boxfile">
                                    <span class="text-sky-300 font-bold" x-text="'بۆکس: ' + previewTx.boxfile"></span>
                                </template>
                                <template x-if="previewTx?.boxfile_puchal">
                                    <span class="text-rose-400 font-bold" x-text="'(پووچەڵ: ' + previewTx.boxfile_puchal + ')'"></span>
                                </template>
                                <template x-if="!previewTx?.boxfile && !previewTx?.boxfile_puchal">
                                    <span class="text-slate-500">-</span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- دۆخی ستێپەکان (Step Checklist) -->
                    <div class="bg-slate-950/60 border border-slate-800 p-3 rounded-xl space-y-1.5">
                        <span class="text-[11px] font-bold text-slate-400 block border-b border-slate-800 pb-1">
                            <i class="fa-solid fa-list-check ml-1"></i> دۆخی قۆناغەکانی کارکردن
                        </span>
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                                <span class="text-slate-300">١. داتائەنتەری</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid" :class="previewTx?.is_inspected ? 'fa-circle-check text-emerald-400' : 'fa-circle-dot text-slate-600'"></i>
                                <span :class="previewTx?.is_inspected ? 'text-slate-300' : 'text-slate-500'">٢. کەشف و تەخمین</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid" :class="previewTx?.is_audited ? 'fa-circle-check text-emerald-400' : 'fa-circle-dot text-slate-600'"></i>
                                <span :class="previewTx?.is_audited ? 'text-slate-300' : 'text-slate-500'">٣. وردبینی</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid" :class="previewTx?.is_paid ? 'fa-circle-check text-emerald-400' : 'fa-circle-dot text-slate-600'"></i>
                                <span :class="previewTx?.is_paid ? 'text-slate-300' : 'text-slate-500'">٤. وەسڵ و پارەدان</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid" :class="previewTx?.is_booklet_completed ? 'fa-circle-check text-emerald-400' : 'fa-circle-dot text-slate-600'"></i>
                                <span :class="previewTx?.is_booklet_completed ? 'text-slate-300' : 'text-slate-500'">٥. بەشی دەفتەر</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid" :class="previewTx?.is_submitted ? 'fa-circle-check text-emerald-400' : 'fa-circle-dot text-slate-600'"></i>
                                <span :class="previewTx?.is_submitted ? 'text-slate-300' : 'text-slate-500'">٦. ئەڕشیف</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Drawer Footer Actions -->
                <div class="p-4 border-t border-slate-800 bg-slate-950/90 flex items-center justify-between gap-2 sticky bottom-0 z-10 backdrop-blur">
                    <button type="button" @click="showDrawer = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition">
                        داخستن (Esc)
                    </button>

                    <a :href="previewTx?.show_url" class="px-5 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-black rounded-xl shadow-lg shadow-sky-600/30 transition flex items-center gap-2">
                        <span>چوونە ناو مامەڵە بە تەواوی</span>
                        <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
