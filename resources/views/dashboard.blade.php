@extends('layouts.app')

@section('content')
<div class="space-y-6 w-full max-w-full">

    <!-- ========================================================================= -->
    <!-- 1. EXECUTIVE WELCOME, FAST BARCODE SCANNER & LIVE OPERATIONAL SUMMARY -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-900 to-indigo-950 p-4 sm:p-5 rounded-2xl border border-slate-800 shadow-xl">
        <!-- Subtle Ambient Glow -->
        <div class="absolute -top-12 -left-12 w-56 h-56 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-56 h-56 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">
            <!-- Right Side (RTL): Identity & Balanced Greeting -->
            <div class="space-y-1.5 min-w-0 shrink-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-500/15 text-sky-300 border border-sky-500/25 flex items-center gap-1.5 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        بەڕێوەبەرایەتی گومرگی سلێمانی
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-800 text-slate-300 border border-slate-700/80 font-mono flex items-center gap-1.5 shadow-sm" title="کاتی ڕاستەوخۆی کۆمپیوتەرەکەت">
                        <i class="fa-regular fa-clock text-sky-400 text-[10px]"></i>
                        <span id="dashLiveDate">{{ now()->format('Y-m-d') }}</span>
                        <span class="text-slate-500">•</span>
                        <span id="dashLiveClock" class="font-bold text-sky-300">{{ now()->format('H:i:s') }}</span>
                    </span>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('dashboard', ['view' => 'data_entry']) }}" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 hover:bg-indigo-500/30 border border-indigo-500/40 transition flex items-center gap-1 shadow-sm" title="پشکنینی وێستگەی کارمەندی داتائەنتەری">
                            <i class="fa-solid fa-desktop text-xs"></i>
                            <span>دیمەنی داتائەنتەری</span>
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-2 flex-wrap pt-0.5">
                    <h1 class="text-base sm:text-lg font-bold text-white tracking-normal">
                        بەخێربێیت، <span class="text-sky-300 font-extrabold">{{ auth()->user()->clean_name }}</span>
                    </h1>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-indigo-500/15 text-indigo-300 border border-indigo-500/25">
                        {{ auth()->user()->clean_role_name }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
                    داشبۆردی سەرەکی چاودێری و ڕاپەڕاندنی مامەڵەکان.
                </p>
            </div>

            <!-- Center: Prominent, High-Visibility Barcode Scanner Station -->
            <div class="flex-1 max-w-xl w-full mx-auto px-0 xl:px-4 py-1">
                <!-- Top Micro Header: Visual Identity & Scanner Status -->
                <div class="flex items-center justify-between mb-1.5 px-1 text-xs">
                    <span class="flex items-center gap-1.5 font-bold text-sky-300">
                        <i class="fa-solid fa-barcode text-sky-400 text-sm"></i>
                        <span>وێستگەی سکانی خێرای مامەڵە</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 flex items-center gap-1 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        ئامادەیە بۆ سکان • Ready
                    </span>
                </div>

                <form action="{{ route('transactions.scan') }}" method="POST" class="relative group">
                    @csrf
                    <!-- Ambient Glow Halo (Highlights visibility across the screen) -->
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-sky-500 via-indigo-500 to-sky-400 rounded-2xl blur-sm opacity-35 group-hover:opacity-75 group-focus-within:opacity-100 transition duration-300 pointer-events-none"></div>

                    <div class="relative flex items-center bg-slate-950 rounded-2xl">
                        <!-- Barcode Scanner Icon with Highlight Plate -->
                        <div class="absolute right-3.5 sm:right-4 flex items-center gap-2 text-sky-400 group-focus-within:text-sky-300 transition pointer-events-none">
                            <div class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-300 flex items-center justify-center border border-sky-500/30">
                                <i class="fa-solid fa-barcode text-base sm:text-lg"></i>
                            </div>
                        </div>
                        
                        <!-- High Contrast, Large, Crystal Clear Barcode Input -->
                        <input type="text" 
                               name="barcode" 
                               id="dashBarcodeInput" 
                               placeholder="سکانی بارکۆد بکە یاخود لێرە بینوسە... (F2)" 
                               class="w-full bg-slate-950 text-white placeholder-slate-400 text-base sm:text-lg font-mono font-black rounded-2xl border-2 border-sky-500/50 hover:border-sky-400 focus:border-sky-300 focus:ring-4 focus:ring-sky-500/30 pr-15 pl-28 py-3 sm:py-3.5 shadow-2xl transition duration-200 tracking-wider text-right"
                               autocomplete="off"
                               autofocus
                               title="لێرە بارکۆد لێبدە یان بە ئامێری سکانەر لێیبدە (F2)">
                        
                        <!-- Left Action Button & F2 Shortcut Badge -->
                        <div class="absolute left-2.5 flex items-center gap-1.5">
                            <kbd class="hidden md:inline-flex items-center px-2 py-1 text-[11px] font-mono font-bold text-slate-300 bg-slate-800/90 border border-slate-700 rounded-lg shadow-sm" title="داگرتنی کلیلی F2 بۆ فۆکەس">
                                F2
                            </kbd>
                            <button type="submit" 
                                    class="px-4 py-2 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white text-xs sm:text-sm font-black rounded-xl shadow-lg shadow-sky-500/30 transition flex items-center gap-1.5 active:scale-95 hover:scale-102" 
                                    title="گەڕان بەپێی بارکۆد">
                                <span>گەڕان</span>
                                <i class="fa-solid fa-arrow-left text-xs"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Left Side (RTL): Live Daily Operational Summary -->
            <div class="flex items-center flex-wrap gap-2.5 shrink-0 pt-2 xl:pt-0 border-t xl:border-t-0 border-slate-800/80 justify-start xl:justify-end">
                <!-- Today's Transactions Metric Chip -->
                <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/70 shadow-sm">
                    <div class="w-7 h-7 rounded-lg bg-sky-500/15 text-sky-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 font-medium">مامەڵەی ئەمڕۆ</div>
                        <div class="text-sm font-bold font-mono text-white leading-none">{{ $todayTransactions }}</div>
                    </div>
                </div>

                <!-- Today's Income Metric Chip -->
                <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/70 shadow-sm">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 font-medium">داهاتی ئەمڕۆ</div>
                        <div class="text-sm font-bold font-mono text-emerald-400 leading-none">
                            {{ number_format($todayIncome) }} <span class="text-[10px] font-sans text-slate-400">د.ع</span>
                        </div>
                    </div>
                </div>

                <!-- Expired Booklets Alert Chip (If Any) -->
                @if($expiredBookletsCount > 0)
                    <a href="{{ route('transactions.expired_booklets') }}" 
                       class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-300 transition shadow-sm" 
                       title="کلیک بکە بۆ بینینی دەفتەرە بەسەرچووەکان">
                        <div class="w-7 h-7 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <div class="text-[10px] text-rose-300 font-medium">بەسەرچوو</div>
                            <div class="text-sm font-bold font-mono text-rose-200 leading-none">{{ $expiredBookletsCount }}</div>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. HIGH PRIORITY ALERT: RETURNED TRANSACTIONS (IF ANY) -->
    <!-- ========================================================================= -->
    @if($returnedCount > 0)
        <div class="bg-gradient-to-r from-rose-950/80 via-slate-900 to-rose-950/80 border-2 border-rose-500/50 rounded-2xl p-4 sm:p-5 shadow-2xl space-y-3.5 animate-fadeIn">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-lg font-black shrink-0 border border-rose-500/40 animate-pulse">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-rose-300 text-sm sm:text-base flex items-center gap-2">
                            <span>ئاگاداری بەپەلە: {{ $returnedCount }} مامەڵە بەهۆی هەڵەوە گەڕێنراونەتەوە!</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-500 text-white font-mono font-black">
                                پێویستی بە چاککردنە
                            </span>
                        </h3>
                        <p class="text-xs text-slate-300">
                            تکایە بەشی داتائەنتەری پێداچوونەوە بە هۆکاری گەڕانەوەکە بکەن و دووبارە ڕەوانەی بکەنەوە.
                        </p>
                    </div>
                </div>
                <a href="{{ route('transactions.index', ['stage' => 'returned']) }}" class="text-xs font-bold text-rose-300 hover:text-white underline">
                    بینینی سەرجەم گەڕێنراوەکان &larr;
                </a>
            </div>

            <!-- Returned Items Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 pt-2 border-t border-rose-500/20">
                @foreach($returnedTransactions->take(6) as $rt)
                    <div class="bg-slate-900/90 border border-rose-500/30 p-3 rounded-xl flex items-center justify-between text-xs gap-3 shadow hover:border-rose-500/60 transition">
                        <div class="space-y-0.5 min-w-0 pr-1">
                            <span class="font-mono text-sky-400 font-black block">{{ $rt->barcode }}</span>
                            <span class="font-bold text-white block truncate">{{ $rt->visitor_name }}</span>
                            <span class="text-[11px] text-rose-400 block truncate">
                                <i class="fa-solid fa-circle-exclamation ml-1"></i>{{ Str::limit($rt->return_reason ?? 'هۆکار دیاری نەکراوە', 30) }}
                            </span>
                        </div>
                        <a href="{{ route('transactions.show', $rt->id) }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs rounded-lg shadow shrink-0 transition flex items-center gap-1">
                            <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                            <span>ڕاستکردنەوە</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 3. TOP FINANCIAL & TRANSACTION VOLUME KPI CARDS -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        
        <!-- Card 1: Total Transactions -->
        <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl shadow-xl hover:border-sky-500/40 transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">کۆی مامەڵە تۆمارکراوەکان</span>
                <div class="w-11 h-11 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-sky-500/20 transition shadow-sm">
                    <i class="fa-solid fa-folder-tree text-lg"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-2xl sm:text-3xl font-black text-white font-mono tracking-tight">
                    {{ number_format($totalTransactions) }}
                </div>
                @if($todayTransactions > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        +{{ $todayTransactions }} ئەمڕۆ
                    </span>
                @else
                    <span class="text-[10px] text-slate-500 font-bold">سەرجەم تۆمارەکان</span>
                @endif
            </div>
            <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                <span>چالاک لە سیستەم:</span>
                <span class="text-sky-300 font-mono font-bold">{{ number_format($totalTransactions - $returnedCount) }} مامەڵە</span>
            </div>
        </div>

        <!-- Card 2: Total Revenue -->
        <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl shadow-xl hover:border-emerald-500/40 transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">کۆی داهاتی دارایی (IQD)</span>
                <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-500/20 transition shadow-sm">
                    <i class="fa-solid fa-coins text-lg"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono tracking-tight">
                    {{ number_format($totalIncome) }}
                    <span class="text-xs font-normal text-emerald-300/80">د.ع</span>
                </div>
                @if($todayIncome > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-mono">
                        +{{ number_format($todayIncome) }}
                    </span>
                @endif
            </div>
            <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                <span>وەرگیراوی وەسڵی ٣٧-ئـ:</span>
                <span class="text-emerald-300 font-bold">قاسەی گومرگ</span>
            </div>
        </div>

        <!-- Card 3: Completed Booklets -->
        <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl shadow-xl hover:border-indigo-500/40 transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">دەفتەری تەواوکراو و دەرکراو</span>
                <div class="w-11 h-11 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-500/20 transition shadow-sm">
                    <i class="fa-solid fa-passport text-lg"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-2xl sm:text-3xl font-black text-indigo-300 font-mono tracking-tight">
                    {{ number_format($completedBooklets) }}
                </div>
                @php
                    $completionRate = $totalTransactions > 0 ? round(($completedBooklets / $totalTransactions) * 100) : 0;
                @endphp
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-mono">
                    {{ $completionRate }}% ڕێژە
                </span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                <span>چاپکراو و ڕادەستکراو:</span>
                <span class="text-indigo-300 font-bold">دەفتەری فەرمی</span>
            </div>
        </div>

        <!-- Card 4: Expired / Alerts -->
        <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl shadow-xl hover:border-amber-500/40 transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">دەفتەرە بەسەرچووەکان</span>
                <div class="w-11 h-11 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-rose-500/20 transition shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-2xl sm:text-3xl font-black text-rose-400 font-mono tracking-tight">
                    {{ number_format($expiredBookletsCount) }}
                </div>
                <a href="{{ route('transactions.expired_booklets') }}" class="text-[10px] font-extrabold text-sky-400 hover:underline">
                    بینینی هەمووی &larr;
                </a>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                <span>پێویست بە تازەکردنەوە:</span>
                <span class="text-amber-400 font-bold">بەسەرچوون</span>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 4. VISUAL WORKFLOW PIPELINE (هێڵی قۆناغەکانی ڕاییبوونی مامەڵە) -->
    <!-- ========================================================================= -->
    <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl shadow-xl space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-bars-progress"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-white">هێڵی قۆناغەکانی ڕاییبوونی مامەڵە (Workflow Pipeline)</h3>
                    <p class="text-[11px] text-slate-400">ژمارەی ئەو مامەڵانەی ئێستا لە هەر قۆناغێکدا لە گومرگ چاوەڕێی جێبەجێکردنن</p>
                </div>
            </div>
            <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-sky-400 hover:text-sky-300">
                خشتەی تەواوی مامەڵەکان &larr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
            
            <!-- Step 1: Inspection -->
            <a href="{{ route('transactions.index', ['stage' => 'inspection']) }}" 
               class="p-4 rounded-xl bg-slate-950/70 border border-slate-800 hover:border-orange-500/60 hover:bg-slate-800/50 transition group flex flex-col justify-between space-y-3 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black text-orange-400 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-orange-500/20 text-orange-400 flex items-center justify-center text-[10px] font-black">١</span>
                        <span>کەشف و تەخمین</span>
                    </span>
                    <i class="fa-solid fa-magnifying-glass text-slate-500 group-hover:text-orange-400 transition text-sm"></i>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <div class="text-2xl font-black text-white font-mono">{{ $pendingInspection }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">پشکنینی ئۆتۆمبێل</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-orange-500/10 text-orange-300 border border-orange-500/30">
                        چاوەڕوان
                    </span>
                </div>
                <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-orange-500 h-full rounded-full" style="width: {{ $pendingInspection > 0 ? min(100, $pendingInspection * 10) : 0 }}%"></div>
                </div>
            </a>

            <!-- Step 2: Audit -->
            <a href="{{ route('transactions.index', ['stage' => 'audit']) }}" 
               class="p-4 rounded-xl bg-slate-950/70 border border-slate-800 hover:border-purple-500/60 hover:bg-slate-800/50 transition group flex flex-col justify-between space-y-3 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black text-purple-400 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center text-[10px] font-black">٢</span>
                        <span>وردبینی ژمێریاری</span>
                    </span>
                    <i class="fa-solid fa-clipboard-check text-slate-500 group-hover:text-purple-400 transition text-sm"></i>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <div class="text-2xl font-black text-white font-mono">{{ $pendingAudit }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">پەسەندکردنی داتاکان</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-purple-500/10 text-purple-300 border border-purple-500/30">
                        چاوەڕوان
                    </span>
                </div>
                <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-purple-500 h-full rounded-full" style="width: {{ $pendingAudit > 0 ? min(100, $pendingAudit * 10) : 0 }}%"></div>
                </div>
            </a>

            <!-- Step 3: Cashier / Payment -->
            <a href="{{ route('transactions.index', ['stage' => 'payment']) }}" 
               class="p-4 rounded-xl bg-slate-950/70 border border-slate-800 hover:border-amber-500/60 hover:bg-slate-800/50 transition group flex flex-col justify-between space-y-3 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black text-amber-400 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-[10px] font-black">٣</span>
                        <span>قاسە و پارەدان</span>
                    </span>
                    <i class="fa-solid fa-cash-register text-slate-500 group-hover:text-amber-400 transition text-sm"></i>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <div class="text-2xl font-black text-white font-mono">{{ $pendingPayment }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">بڕینی وەسڵی ۳۷-ئـ</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30">
                        چاوەڕوان
                    </span>
                </div>
                <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full" style="width: {{ $pendingPayment > 0 ? min(100, $pendingPayment * 10) : 0 }}%"></div>
                </div>
            </a>

            <!-- Step 4: Booklet Printing -->
            <a href="{{ route('transactions.index', ['stage' => 'booklet']) }}" 
               class="p-4 rounded-xl bg-slate-950/70 border border-slate-800 hover:border-sky-500/60 hover:bg-slate-800/50 transition group flex flex-col justify-between space-y-3 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black text-sky-400 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-sky-500/20 text-sky-400 flex items-center justify-center text-[10px] font-black">٤</span>
                        <span>چاپی دەفتەر</span>
                    </span>
                    <i class="fa-solid fa-print text-slate-500 group-hover:text-sky-400 transition text-sm"></i>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <div class="text-2xl font-black text-white font-mono">{{ $pendingBooklet }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">تۆماری ژمارەی دەفتەر</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-sky-500/10 text-sky-300 border border-sky-500/30">
                        چاوەڕوان
                    </span>
                </div>
                <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-sky-500 h-full rounded-full" style="width: {{ $pendingBooklet > 0 ? min(100, $pendingBooklet * 10) : 0 }}%"></div>
                </div>
            </a>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. MAIN CONTENT SPLIT: RECENT TRANSACTIONS (68%) & SIDEBAR (32%) -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left: Recent Transactions Table (8 Cols) -->
        <div class="lg:col-span-8 bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white">دوایین مامەڵە تۆمارکراوەکان</h3>
                            <p class="text-[11px] text-slate-400">نوێترین جموجۆڵەکانی دەروازەی دەفتەری ئۆتۆمبێل</p>
                        </div>
                    </div>
                    <a href="{{ route('transactions.index') }}" class="text-xs text-sky-400 hover:text-sky-300 hover:underline font-bold flex items-center gap-1">
                        <span>بینینی هەموو مامەڵەکان</span>
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    </a>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden sm:block overflow-x-auto rounded-xl border border-slate-800">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-950/80 text-slate-400 font-bold border-b border-slate-800">
                            <tr>
                                <th class="p-3">بارکۆد</th>
                                <th class="p-3">خاوەن مامەڵە / شۆفێر</th>
                                <th class="p-3">ژمارەی تابلۆ</th>
                                <th class="p-3">جۆری مامەڵە</th>
                                <th class="p-3 text-center">بڕی پارە</th>
                                <th class="p-3 text-center">بارودۆخ</th>
                                <th class="p-3 text-center">کردار</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($recentTransactions as $tx)
                                @php
                                    $cleanTypeName = trim(str_replace(['(نوێ)', '（نوێ）', '( نوێ )', 'نوێ'], '', $tx->transactionType->name_kurdish ?? '-'));
                                @endphp
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="p-3 font-mono font-bold text-sky-400 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-barcode text-slate-500 text-xs"></i>
                                            <span>{{ $tx->barcode }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3 font-bold text-white whitespace-nowrap">
                                        <a href="{{ route('transactions.show', $tx->id) }}" class="hover:text-sky-400 transition">
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
                                            {{ $cleanTypeName }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center font-mono font-bold text-emerald-400 whitespace-nowrap">
                                        {{ number_format($tx->total_pay) }} <span class="text-[10px] text-emerald-300/70 font-normal">د.ع</span>
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        @if($tx->is_cancelled || !empty($tx->cancelled_at))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">پووچەڵکراوە</span>
                                        @elseif($tx->is_booklet_completed)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">تەواوکراوە</span>
                                        @elseif($tx->is_paid)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-500/10 text-sky-400 border border-sky-500/30">پارەدراوە</span>
                                        @elseif($tx->is_audited)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">چاوەڕێی پارەدان</span>
                                        @elseif($tx->is_inspected)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-400 border border-purple-500/30">چاوەڕێی وردبینی</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">چاوەڕێی کەشف</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <a href="{{ route('transactions.show', $tx->id) }}" 
                                           class="px-2.5 py-1 bg-slate-800 hover:bg-sky-600 hover:text-white text-slate-300 rounded-lg text-xs font-bold transition border border-slate-700">
                                            بینین
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-slate-400 text-xs">هیچ مامەڵەیەک نەدۆزرایەوە.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="sm:hidden space-y-2.5">
                    @forelse($recentTransactions as $tx)
                        <div class="bg-slate-950 p-3.5 rounded-xl border border-slate-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs font-bold text-sky-400">{{ $tx->barcode }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $tx->created_at ? $tx->created_at->format('H:i') : '' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-white text-xs">{{ $tx->visitor_name }}</span>
                                <span class="font-mono text-emerald-400 font-bold text-xs">{{ number_format($tx->total_pay) }} د.ع</span>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-slate-800">
                                <x-vehicle-plate :plate="$tx->plate_number" :type="$tx->plateType" size="sm" />
                                <a href="{{ route('transactions.show', $tx->id) }}" class="px-2.5 py-1 bg-sky-600 text-white rounded-lg text-xs font-bold">
                                    بینین
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">هیچ مامەڵەیەک نییە.</p>
                    @endforelse
                </div>
            </div>

            <!-- Table Footer Indicator -->
            <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                <span>پیشاندانی دوایین {{ $recentTransactions->count() }} مامەڵە</span>
                <a href="{{ route('transactions.index') }}" class="text-sky-400 font-bold hover:underline">
                    گەڕان لە تەواوی مامەڵەکان &larr;
                </a>
            </div>
        </div>

        <!-- Right: Insights & Distribution & Audit Log (4 Cols) -->
        <div class="lg:col-span-4 space-y-5">
            
            <!-- Widget 1: Transaction Types Distribution -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3.5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-sky-400"></i>
                        <span>دابەشبوونی جۆرەکانی مامەڵە</span>
                    </h3>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $totalTransactions }} کۆی گشتی</span>
                </div>

                <div class="space-y-2.5">
                    @foreach($typeDistribution as $td)
                        @php
                            $cleanName = trim(str_replace(['(نوێ)', '（نوێ）', '( نوێ )', 'نوێ'], '', $td->name_kurdish ?? '-'));
                            $pct = $totalTransactions > 0 ? round(($td->transactions_count / $totalTransactions) * 100) : 0;
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-200">{{ $cleanName }}</span>
                                <div class="flex items-center gap-1.5 font-mono">
                                    <span class="font-black text-sky-400">{{ $td->transactions_count }}</span>
                                    <span class="text-slate-500 text-[10px]">({{ $pct }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-950 h-2 rounded-full overflow-hidden border border-slate-800/80">
                                <div class="bg-gradient-to-r from-sky-500 to-indigo-500 h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Widget 2: Audit Logs (Admin) or User Role Shortcuts (Non-Admin) -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3.5">
                @if(auth()->user()->isAdmin())
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                            <span>دوایین جموجۆڵەکانی سیستم (Audit)</span>
                        </h3>
                        <a href="{{ route('logs.index') }}" class="text-[10px] font-bold text-sky-400 hover:underline">
                            سەرجەمی &larr;
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($recentLogs as $log)
                            <div class="p-2.5 bg-slate-950/70 border border-slate-800/80 rounded-xl space-y-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-extrabold text-white">{{ $log->user_name }}</span>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sky-400 font-semibold text-[11px]">{{ $log->action }}</p>
                                @if($log->details)
                                    <p class="text-[10px] text-slate-400 truncate">{{ $log->details }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">هیچ لۆگێک تۆمار نەکراوە.</p>
                        @endforelse
                    </div>
                @else
                    <!-- Non-admin quick role summary -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                            <i class="fa-solid fa-user-check text-sky-400"></i>
                            <span>بەشی کارکردنی تۆ</span>
                        </h3>
                        <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">بەکارهێنەر:</span>
                                <span class="font-bold text-white">{{ auth()->user()->name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">پلە و دەسەڵات:</span>
                                <span class="font-black text-sky-400">{{ auth()->user()->role_name_kurdish }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            <a href="{{ route('transactions.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold flex items-center justify-between transition">
                                <span><i class="fa-solid fa-list-check ml-1.5 text-sky-400"></i> لیستی گشتی مامەڵەکان</span>
                                <i class="fa-solid fa-chevron-left text-[10px] text-slate-400"></i>
                            </a>
                            @if(auth()->user()->hasPermission('transactions.create'))
                                <a href="{{ route('transactions.create') }}" class="p-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center justify-between transition">
                                    <span><i class="fa-solid fa-plus-circle ml-1.5"></i> تۆمارکردنی نوێ</span>
                                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>

<!-- Live Clock Script (100% Synchronized with User's Computer) -->
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
    // Update immediately on page load
    updateClock();
    // Live update every second to match computer's clock in real time
    setInterval(updateClock, 1000);
</script>
@endsection
