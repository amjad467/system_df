@extends('layouts.app')

@section('content')
<div class="space-y-6 w-full max-w-full">

    <!-- ========================================================================= -->
    <!-- 1. WORKSTATION HEADER, FAST SCANNER & OPERATIONAL SUMMARY -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-900 to-indigo-950 p-4 sm:p-5 rounded-3xl border border-slate-800 shadow-xl">
        <!-- Subtle Ambient Glow -->
        <div class="absolute -top-12 -left-12 w-56 h-56 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-56 h-56 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">
            <!-- Right Side (RTL): Desk Identity & Live Date/Time -->
            <div class="space-y-1.5 min-w-0 shrink-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-sky-500/15 text-sky-300 border border-sky-500/30 flex items-center gap-1.5 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        بەڕێوەبەرایەتی گومرگی سلێمانی
                    </span>

                    <span class="px-3 py-1 rounded-2xl text-xs sm:text-sm font-medium bg-slate-950/90 text-slate-200 border border-slate-700/80 font-mono flex items-center gap-2 shadow-inner" title="کاتی ڕاستەوخۆی کۆمپیوتەرەکەت">
                        <i class="fa-regular fa-calendar-days text-sky-400 text-xs"></i>
                        <span class="app-live-date text-slate-200">{{ now()->format('Y-m-d') }}</span>
                        <span class="text-slate-600 font-bold">•</span>
                        <i class="fa-regular fa-clock text-emerald-400 text-xs"></i>
                        <span class="app-live-clock font-black text-emerald-400 tracking-wider">{{ now()->format('H:i:s') }}</span>
                    </span>

                    @if(auth()->user()->isAdmin())
                        <div class="flex items-center gap-1 bg-slate-800/90 p-1 rounded-xl border border-slate-700 text-[11px]">
                            <span class="text-slate-400 px-1 font-bold">دیمەن:</span>
                            <a href="{{ route('dashboard') }}" class="px-2 py-0.5 rounded-lg font-bold {{ !request()->has('view') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:text-white' }}">گشتی</a>
                            <a href="{{ route('dashboard', ['view' => 'cashier']) }}" class="px-2 py-0.5 rounded-lg font-bold {{ request('view') === 'cashier' ? 'bg-amber-600 text-white' : 'text-slate-300 hover:text-white' }}">قاسە</a>
                            <a href="{{ route('dashboard', ['view' => 'archiver']) }}" class="px-2 py-0.5 rounded-lg font-bold {{ request('view') === 'archiver' ? 'bg-purple-600 text-white' : 'text-slate-300 hover:text-white' }}">ئەرشیف</a>
                            <a href="{{ route('dashboard', ['view' => 'data_entry']) }}" class="px-2 py-0.5 rounded-lg font-bold text-slate-300 hover:text-white">داتائەنتەری</a>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2 flex-wrap pt-0.5">
                    <h1 class="text-base sm:text-xl font-black text-white tracking-normal">
                        داشبۆردی سەرەکی • <span class="text-sky-300">{{ auth()->user()->clean_role_name }}</span>
                    </h1>
                </div>
                <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
                    چاودێری ڕاستەوخۆ و ڕاپەڕاندنی خێرای مامەڵەکانی دەفتەری ئۆتۆمبێل.
                </p>
            </div>

            <!-- Center: Prominent Fast Barcode Scanner Station -->
            <div class="flex-1 max-w-xl w-full mx-auto px-0 xl:px-4 py-1">
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
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-sky-500 via-indigo-500 to-sky-400 rounded-2xl blur-sm opacity-35 group-hover:opacity-75 group-focus-within:opacity-100 transition duration-300 pointer-events-none"></div>

                    <div class="relative flex items-center bg-slate-950 rounded-2xl">
                        <div class="absolute right-3.5 sm:right-4 flex items-center gap-2 text-sky-400 group-focus-within:text-sky-300 transition pointer-events-none">
                            <div class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-300 flex items-center justify-center border border-sky-500/30">
                                <i class="fa-solid fa-barcode text-base sm:text-lg"></i>
                            </div>
                        </div>
                        
                        <input type="text" 
                               name="barcode" 
                               id="dashBarcodeInput" 
                               placeholder="سکانی بارکۆد بکە یاخود لێرە بینوسە... (F2)" 
                               class="w-full bg-slate-950 text-white placeholder-slate-400 text-base sm:text-lg font-mono font-black rounded-2xl border-2 border-sky-500/50 hover:border-sky-400 focus:border-sky-300 focus:ring-4 focus:ring-sky-500/30 pr-15 pl-28 py-3 sm:py-3.5 shadow-2xl transition duration-200 tracking-wider text-right"
                               autocomplete="off"
                               autofocus
                               title="لێرە بارکۆد لێبدە یان بە ئامێری سکانەر لێیبدە (F2)">
                        
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
                
                <!-- Quick Metric 1: Today's Registered Slips -->
                <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/70 shadow-sm">
                    <div class="w-7 h-7 rounded-lg bg-sky-500/15 text-sky-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 font-medium">تۆمارکراوی ئەمڕۆ</div>
                        <div class="text-sm font-bold font-mono text-white leading-none">{{ $todayDataEntryCount }}</div>
                    </div>
                </div>

                <!-- Quick Metric 2: Role-tailored today metric -->
                @if($canSeeMoney)
                    <!-- Today's Income (ONLY CASHIER & ADMIN) -->
                    <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/70 shadow-sm">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 font-medium">داهاتی وەرگیراوی ئەمڕۆ</div>
                            <div class="text-sm font-bold font-mono text-emerald-400 leading-none">
                                {{ number_format($todayIncome) }} <span class="text-[10px] font-sans text-slate-400">د.ع</span>
                            </div>
                        </div>
                    </div>
                @elseif($activeRole === 'inspector')
                    <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/70 shadow-sm">
                        <div class="w-7 h-7 rounded-lg bg-orange-500/15 text-orange-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 font-medium">تەخمینی ئەمڕۆ</div>
                            <div class="text-sm font-bold font-mono text-orange-400 leading-none">{{ $todayInspectedCount }}</div>
                        </div>
                    </div>
                @elseif($activeRole === 'auditor')
                    <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/70 shadow-sm">
                        <div class="w-7 h-7 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 font-medium">وردبینی ئەمڕۆ</div>
                            <div class="text-sm font-bold font-mono text-purple-400 leading-none">{{ $todayAuditedCount }}</div>
                        </div>
                    </div>
                @elseif($activeRole === 'booklet')
                    <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/70 shadow-sm">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-passport"></i>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 font-medium">دەفتەری ئەمڕۆ</div>
                            <div class="text-sm font-bold font-mono text-indigo-400 leading-none">{{ $todayBookletsCount }}</div>
                        </div>
                    </div>
                @elseif($activeRole === 'archiver')
                    <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/70 shadow-sm">
                        <div class="w-7 h-7 rounded-lg bg-teal-500/15 text-teal-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 font-medium">ئەرشیفکراوی ئەمڕۆ</div>
                            <div class="text-sm font-bold font-mono text-teal-400 leading-none">{{ $todayArchivedCount }}</div>
                        </div>
                    </div>
                @endif

                <!-- Expired Booklets Alert Chip (Admin & Archiver ONLY) -->
                @if($showAllTimeCards && $expiredBookletsCount > 0)
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
                            تکایە بەشی پەیوەندیدار پێداچوونەوە بە هۆکاری گەڕانەوەکە بکەن و دووبارە ڕەوانەی بکەنەوە.
                        </p>
                    </div>
                </div>
                <a href="{{ route('transactions.index', ['stage' => 'returned']) }}" class="text-xs font-bold text-rose-300 hover:text-white underline">
                    بینینی سەرجەم گەڕێنراوەکان &larr;
                </a>
            </div>

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
    <!-- 3. TOP KPI CARDS: ALL-TIME (FOR ADMIN & ARCHIVER) VS. STRICTLY TODAY (FOR ALL OTHERS) -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @if($showAllTimeCards)
            <!-- ========================================================================= -->
            <!-- 1. ADMIN & ARCHIVER VIEW: ALL-TIME METRICS & TOTALS                      -->
            <!-- ========================================================================= -->

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
                    @if($todayDataEntryCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            +{{ $todayDataEntryCount }} ئەمڕۆ
                        </span>
                    @else
                        <span class="text-[10px] text-slate-500 font-bold">سەرجەم تۆمارەکان</span>
                    @endif
                </div>
                <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                    <span>تەواوکراوی گشتی:</span>
                    <span class="text-sky-300 font-mono font-bold">{{ number_format($completedBooklets) }} دەفتەر</span>
                </div>
            </div>

            <!-- Card 2: Financial Revenue (Admin) or Total Archived (Archiver) -->
            @if(auth()->user()->isAdmin())
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
                        <span>وەرگیراوی وەسڵی ۳۷/أ:</span>
                        <span class="text-emerald-300 font-bold">{{ $todayPaidCount }} مامەڵەی ئەمڕۆ</span>
                    </div>
                </div>
            @else
                <!-- Archiver Card 2 (NO MONEY) -->
                <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl shadow-xl hover:border-teal-500/40 transition group">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">کۆی مامەڵە ئەرشیفکراوەکان</span>
                        <div class="w-11 h-11 rounded-2xl bg-teal-500/10 border border-teal-500/20 text-teal-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-teal-500/20 transition shadow-sm">
                            <i class="fa-solid fa-box-archive text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline justify-between">
                        <div class="text-2xl sm:text-3xl font-black text-teal-400 font-mono tracking-tight">
                            {{ number_format($archiverTotalCount) }}
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-teal-500/20 text-teal-300 border border-teal-500/30">
                            +{{ $todayArchivedCount }} ئەمڕۆ
                        </span>
                    </div>
                    <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                        <span>سەپمیتکراو بە فەرمی:</span>
                        <span class="text-teal-300 font-bold">بۆکس فایل</span>
                    </div>
                </div>
            @endif

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
                    <span>چاپکراوی ئەمڕۆ:</span>
                    <span class="text-indigo-300 font-bold font-mono">{{ $todayBookletsCount }} دەفتەر</span>
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

        @else
            <!-- ========================================================================= -->
            <!-- 2. ALL OTHER USERS (INSPECTOR, AUDITOR, CASHIER, BOOKLET, ETC.)           -->
            <!-- STRICTLY TODAY'S INFORMATION ONLY (تەنها زانیاری بەگوێرەی ئەمڕۆ)         -->
            <!-- ========================================================================= -->

            <!-- Today Card 1: Today's Registered Transactions -->
            <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl shadow-xl hover:border-sky-500/40 transition group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400">مامەڵەی تۆمارکراوی ئەمڕۆ</span>
                    <div class="w-11 h-11 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-sky-500/20 transition shadow-sm">
                        <i class="fa-solid fa-file-circle-plus text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-2xl sm:text-3xl font-black text-sky-400 font-mono tracking-tight">
                        {{ number_format($todayDataEntryCount) }}
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-sky-500/20 text-sky-300 border border-sky-500/30">
                        ئەمڕۆ
                    </span>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                    <span>بەشی تۆمارکردن:</span>
                    <span class="text-slate-300 font-bold">مامەڵەی نوێی ئەمڕۆ</span>
                </div>
            </div>

            <!-- Today Card 2: Today's Completed in Your Step -->
            <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl shadow-xl hover:border-emerald-500/40 transition group">
                <div class="flex items-center justify-between">
                    @if($activeRole === 'inspector')
                        <span class="text-xs font-bold text-slate-400">تەخمینی تەواوکراوی ئەمڕۆ</span>
                        <div class="w-11 h-11 rounded-2xl bg-orange-500/10 border border-orange-500/20 text-orange-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-orange-500/20 transition shadow-sm">
                            <i class="fa-solid fa-magnifying-glass text-lg"></i>
                        </div>
                    @elseif($activeRole === 'auditor')
                        <span class="text-xs font-bold text-slate-400">وردبینی تەواوکراوی ئەمڕۆ</span>
                        <div class="w-11 h-11 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-purple-500/20 transition shadow-sm">
                            <i class="fa-solid fa-clipboard-check text-lg"></i>
                        </div>
                    @elseif($activeRole === 'cashier')
                        <span class="text-xs font-bold text-slate-400">مامەڵەی پارەدراوی ئەمڕۆ</span>
                        <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500/20 transition shadow-sm">
                            <i class="fa-solid fa-receipt text-lg"></i>
                        </div>
                    @elseif($activeRole === 'booklet')
                        <span class="text-xs font-bold text-slate-400">دەفتەری چاپکراوی ئەمڕۆ</span>
                        <div class="w-11 h-11 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-500/20 transition shadow-sm">
                            <i class="fa-solid fa-passport text-lg"></i>
                        </div>
                    @else
                        <span class="text-xs font-bold text-slate-400">تەواوکراوی ئەمڕۆی بەشەکەت</span>
                        <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-500/20 transition shadow-sm">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                        </div>
                    @endif
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight {{ $activeRole === 'cashier' ? 'text-amber-400' : 'text-emerald-400' }}">
                        @if($activeRole === 'inspector')
                            {{ number_format($todayInspectedCount) }}
                        @elseif($activeRole === 'auditor')
                            {{ number_format($todayAuditedCount) }}
                        @elseif($activeRole === 'cashier')
                            {{ number_format($todayPaidCount) }}
                        @elseif($activeRole === 'booklet')
                            {{ number_format($todayBookletsCount) }}
                        @else
                            {{ number_format($todayDataEntryCount) }}
                        @endif
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        ئەمڕۆ
                    </span>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                    @if($activeRole === 'cashier')
                        <span>داهاتی وەرگیراوی ئەمڕۆ:</span>
                        <span class="text-emerald-300 font-bold font-mono">{{ number_format($todayIncome) }} د.ع</span>
                    @else
                        <span>دۆخی جێبەجێکردن:</span>
                        <span class="text-emerald-300 font-bold">پەسەندکراوی ئەمڕۆ</span>
                    @endif
                </div>
            </div>

            <!-- Today Card 3: Pending Queue for Your Desk Today -->
            <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl shadow-xl hover:border-amber-500/40 transition group">
                <div class="flex items-center justify-between">
                    @if($activeRole === 'inspector')
                        <span class="text-xs font-bold text-slate-400">چاوەڕوانی کەشف و تەخمین</span>
                        <div class="w-11 h-11 rounded-2xl bg-orange-500/10 border border-orange-500/20 text-orange-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-orange-500/20 transition shadow-sm">
                            <i class="fa-solid fa-car-side text-lg"></i>
                        </div>
                    @elseif($activeRole === 'auditor')
                        <span class="text-xs font-bold text-slate-400">چاوەڕوانی وردبینی ژمێریاری</span>
                        <div class="w-11 h-11 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-purple-500/20 transition shadow-sm">
                            <i class="fa-solid fa-file-signature text-lg"></i>
                        </div>
                    @elseif($activeRole === 'cashier')
                        <span class="text-xs font-bold text-slate-400">چاوەڕوانی وەرگرتنی پارە</span>
                        <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500/20 transition shadow-sm">
                            <i class="fa-solid fa-cash-register text-lg"></i>
                        </div>
                    @elseif($activeRole === 'booklet')
                        <span class="text-xs font-bold text-slate-400">چاوەڕوانی چاپی دەفتەر</span>
                        <div class="w-11 h-11 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-sky-500/20 transition shadow-sm">
                            <i class="fa-solid fa-print text-lg"></i>
                        </div>
                    @else
                        <span class="text-xs font-bold text-slate-400">چاوەڕوانی لە نۆرەدا</span>
                        <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500/20 transition shadow-sm">
                            <i class="fa-solid fa-hourglass-half text-lg"></i>
                        </div>
                    @endif
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-2xl sm:text-3xl font-black text-amber-400 font-mono tracking-tight">
                        @if($activeRole === 'inspector')
                            {{ number_format($pendingInspection) }}
                        @elseif($activeRole === 'auditor')
                            {{ number_format($pendingAudit) }}
                        @elseif($activeRole === 'cashier')
                            {{ number_format($pendingPayment) }}
                        @elseif($activeRole === 'booklet')
                            {{ number_format($pendingBooklet) }}
                        @else
                            {{ number_format($pendingInspection) }}
                        @endif
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        لە نۆرەدا
                    </span>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                    <span>قۆناغی ئێستا:</span>
                    <span class="text-amber-300 font-bold">ئامادەیە بۆ ڕاییبوون</span>
                </div>
            </div>

            <!-- Today Card 4: Today's Completed Booklets Across Customs -->
            <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl shadow-xl hover:border-indigo-500/40 transition group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400">دەفتەری چاپکراوی ئەمڕۆ</span>
                    <div class="w-11 h-11 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-500/20 transition shadow-sm">
                        <i class="fa-solid fa-book-bookmark text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-2xl sm:text-3xl font-black text-indigo-300 font-mono tracking-tight">
                        {{ number_format($todayBookletsCount) }}
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        ئەمڕۆ
                    </span>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                    <span>مامەڵەی گەڕێنراوە:</span>
                    @if($returnedCount > 0)
                        <span class="text-rose-400 font-bold">{{ $returnedCount }} مامەڵە</span>
                    @else
                        <span class="text-emerald-400 font-bold">هیچ نییە</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- 4. CASHIER WORKSTATION & ACCOUNTING 66 SEARCH/PRINT STATION -->
    <!-- ========================================================================= -->
    @if(auth()->user()->isAdmin() || $activeRole === 'cashier')
        <div class="bg-gradient-to-r from-amber-950/40 via-slate-900 to-slate-900 border-2 border-amber-500/40 rounded-3xl p-5 shadow-2xl space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3 border-b border-amber-500/20 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg font-black border border-amber-500/30 shadow-sm">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                            <span>وێستگەی ژمێریار و چاپی محاسبة ٦٦ بۆ هەموو کاتەکان</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold">ژمێریاری و پارەدان</span>
                        </h2>
                        <p class="text-xs text-slate-400">
                            گەڕانی خێرا لە نێوان پسوولە و مامەڵەکان و چاپی دەستبەجێی فۆڕمی فەرمی محاسبة ٦٦ بۆ هەر بەروارێک.
                        </p>
                    </div>
                </div>

                <!-- Daily Revenue Snapshot Pill -->
                <div class="flex items-center gap-3 bg-slate-950/90 px-4 py-2 rounded-2xl border border-amber-500/30 shadow-inner">
                    <div class="text-right">
                        <span class="text-[10px] text-amber-300 font-bold block">وەرگرتنی پارەی ئەمڕۆ</span>
                        <span class="text-base sm:text-lg font-black font-mono text-emerald-400 leading-tight">
                            {{ number_format($todayIncome) }} <span class="text-xs text-slate-400 font-normal">د.ع</span>
                        </span>
                    </div>
                    <div class="h-8 w-px bg-slate-800"></div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 font-medium block">وەسڵی ۳۷/أ بڕاو</span>
                        <span class="text-base font-black font-mono text-white leading-tight">{{ $todayPaidCount }}</span>
                    </div>
                </div>
            </div>

            <!-- Accounting 66 Direct Filter and Search Bar -->
            <form action="{{ route('reports.accounting_66') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-slate-950/70 p-4 rounded-2xl border border-slate-800">
                <div class="md:col-span-4 space-y-1">
                    <label class="block text-xs font-bold text-slate-300">
                        <i class="fa-solid fa-magnifying-glass text-amber-400 ml-1"></i>
                        گەڕان بەپێی ژمارەی پسوولەی ۳۷/أ، بارکۆد، تابلۆ، یان ناو:
                    </label>
                    <input type="text" name="search" placeholder="وەک: 1040 یان 2611030001..." 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400">
                </div>

                <div class="md:col-span-3 space-y-1">
                    <label class="block text-xs font-bold text-slate-300">لە بەرواری:</label>
                    <input type="date" name="date_from" value="{{ today()->format('Y-m-d') }}" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-400">
                </div>

                <div class="md:col-span-3 space-y-1">
                    <label class="block text-xs font-bold text-slate-300">بۆ بەرواری:</label>
                    <input type="date" name="date_to" value="{{ today()->format('Y-m-d') }}" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-400">
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-black text-xs rounded-xl shadow-lg shadow-amber-600/30 transition flex items-center justify-center gap-1.5 active:scale-95">
                        <i class="fa-solid fa-print text-xs"></i>
                        <span>کردنەوە و چاپی ٦٦</span>
                    </button>
                </div>
            </form>

            <!-- Quick Action Preset Buttons for Accounting 66 -->
            <div class="flex items-center justify-between flex-wrap gap-2 text-xs pt-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-slate-400 font-bold">فلتەری خێرا:</span>
                    <a href="{{ route('reports.accounting_66', ['date_from' => today()->format('Y-m-d'), 'date_to' => today()->format('Y-m-d')]) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition font-bold">
                        محاسبة ٦٦ ی ئەمڕۆ
                    </a>
                    <a href="{{ route('reports.accounting_66', ['date_from' => today()->subDay()->format('Y-m-d'), 'date_to' => today()->subDay()->format('Y-m-d')]) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition font-bold">
                        دوێنێ
                    </a>
                    <a href="{{ route('reports.accounting_66', ['date_from' => today()->startOfMonth()->format('Y-m-d'), 'date_to' => today()->format('Y-m-d')]) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition font-bold">
                        ئەم مانگە
                    </a>
                    <a href="{{ route('reports.accounting_66') }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition font-bold">
                        سەرجەم کاتەکان
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.accounting_66.export') }}" class="px-3 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600/50 text-indigo-200 border border-indigo-500/40 transition font-bold flex items-center gap-1">
                        <i class="fa-solid fa-file-excel text-xs"></i>
                        <span>داگرتنی داتا (CSV)</span>
                    </a>
                </div>
            </div>

            <!-- Pending Payments Queue Waiting for Cashier -->
            @if(isset($pendingPaymentList) && $pendingPaymentList->count() > 0)
                <div class="pt-2 border-t border-amber-500/20 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-extrabold text-amber-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span>مامەڵەکانی چاوەڕوانی وەرگرتنی پارە ({{ $pendingPayment }} مامەڵە لە نۆرەدا):</span>
                        </span>
                        <a href="{{ route('transactions.index', ['stage' => 'payment']) }}" class="text-amber-400 hover:underline font-bold">
                            بینینی تەواوی نۆرەکە &larr;
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                        @foreach($pendingPaymentList->take(4) as $pTx)
                            <div class="bg-slate-950 p-3 rounded-xl border border-amber-500/30 flex items-center justify-between text-xs gap-2 shadow-sm">
                                <div class="min-w-0">
                                    <span class="font-mono text-sky-400 font-bold block">{{ $pTx->barcode }}</span>
                                    <span class="font-bold text-white block truncate">{{ $pTx->visitor_name }}</span>
                                    <span class="font-mono text-emerald-400 font-black block mt-0.5">{{ number_format($pTx->total_pay) }} د.ع</span>
                                </div>
                                <a href="{{ route('transactions.show', $pTx->id) }}" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white font-black text-xs rounded-lg shadow shrink-0 transition flex items-center gap-1">
                                    <i class="fa-solid fa-cash-register text-[10px]"></i>
                                    <span>وەرگرتن</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 5. ARCHIVER & SUBMIT WORKSTATION: FULL BOOKLET STATISTICS (NO MONEY!) -->
    <!-- ========================================================================= -->
    @if(auth()->user()->isAdmin() || $activeRole === 'archiver')
        <div class="bg-gradient-to-r from-teal-950/40 via-slate-900 to-indigo-950/40 border-2 border-teal-500/40 rounded-3xl p-5 shadow-2xl space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3 border-b border-teal-500/20 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-teal-500/20 text-teal-400 flex items-center justify-center text-lg font-black border border-teal-500/30 shadow-sm">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                            <span>وێستگەی کارمەندی ئەرشیف و سەپمیت • ئاماری دەفتەرەکان</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] bg-teal-500/20 text-teal-300 border border-teal-500/30 font-bold">بێ پارە • Volume Only</span>
                        </h2>
                        <p class="text-xs text-slate-400">
                            ئاماری وردی سەرجەم جۆرەکانی دەفتەر بەپێی بەروار و بۆکس فایل بەبێ پیشاندانی داتای دارایی.
                        </p>
                    </div>
                </div>

                <!-- Archiver Daily Activity Pill -->
                <div class="flex items-center gap-3 bg-slate-950/90 px-4 py-2 rounded-2xl border border-teal-500/30 shadow-inner">
                    <div class="text-right">
                        <span class="text-[10px] text-teal-300 font-bold block">ئەرشیفکراوی ئەمڕۆ</span>
                        <span class="text-base sm:text-lg font-black font-mono text-teal-300 leading-tight">
                            {{ $todayArchivedCount }} <span class="text-xs text-slate-400 font-normal">مامەڵە</span>
                        </span>
                    </div>
                    <div class="h-8 w-px bg-slate-800"></div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 font-medium block">چاوەڕوانی سەپمیت</span>
                        <span class="text-base font-black font-mono text-amber-400 leading-tight">{{ $pendingSubmit }}</span>
                    </div>
                </div>
            </div>

            <!-- Date Range Filter for Archiver Statistics -->
            <form action="{{ route('dashboard') }}" method="GET" class="flex items-center justify-between flex-wrap gap-3 bg-slate-950/70 p-3.5 rounded-2xl border border-slate-800">
                <input type="hidden" name="view" value="{{ $activeRole }}">
                <div class="flex items-center gap-2 flex-wrap text-xs">
                    <span class="text-slate-300 font-bold">فلتەری کات:</span>
                    <a href="{{ route('dashboard', ['view' => $activeRole, 'archiver_filter' => 'today']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ ($archiverFilter ?? 'today') === 'today' && empty($archiverFrom) ? 'bg-teal-600 text-white' : 'bg-slate-800 text-slate-300 hover:text-white' }}">
                        ئەمڕۆ
                    </a>
                    <a href="{{ route('dashboard', ['view' => $activeRole, 'archiver_filter' => 'week']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ ($archiverFilter ?? '') === 'week' ? 'bg-teal-600 text-white' : 'bg-slate-800 text-slate-300 hover:text-white' }}">
                        ئەم هەفتەیە
                    </a>
                    <a href="{{ route('dashboard', ['view' => $activeRole, 'archiver_filter' => 'month']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ ($archiverFilter ?? '') === 'month' ? 'bg-teal-600 text-white' : 'bg-slate-800 text-slate-300 hover:text-white' }}">
                        ئەم مانگە
                    </a>
                    <a href="{{ route('dashboard', ['view' => $activeRole, 'archiver_filter' => 'all']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ ($archiverFilter ?? '') === 'all' ? 'bg-teal-600 text-white' : 'bg-slate-800 text-slate-300 hover:text-white' }}">
                        سەرجەم کاتەکان
                    </a>
                </div>

                <!-- Custom Range Inputs -->
                <div class="flex items-center gap-2 text-xs flex-wrap">
                    <span class="text-slate-400">یان دیاریکردنی بەروار:</span>
                    <input type="date" name="from_date" value="{{ $archiverFrom ?? '' }}" class="bg-slate-900 border border-slate-700 rounded-xl px-2.5 py-1 text-xs text-white focus:outline-none focus:border-teal-400">
                    <span class="text-slate-500">بۆ</span>
                    <input type="date" name="to_date" value="{{ $archiverTo ?? '' }}" class="bg-slate-900 border border-slate-700 rounded-xl px-2.5 py-1 text-xs text-white focus:outline-none focus:border-teal-400">
                    <button type="submit" class="px-3 py-1 bg-teal-600 hover:bg-teal-500 text-white font-bold rounded-xl transition">
                        فلتەر
                    </button>
                </div>
            </form>

            <!-- Booklet Statistics by Type Breakdown Grid (STRICTLY NO MONEY) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach($allTxTypes as $type)
                    @php
                        $cleanTypeName = trim(str_replace(['(نوێ)', '（نوێ）', '( نوێ )', 'نوێ'], '', $type->name_kurdish ?? '-'));
                        $countForType = $archiverCountsRaw[$type->id] ?? 0;
                    @endphp
                    <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800 hover:border-teal-500/50 transition text-right space-y-1 shadow-sm">
                        <span class="text-[11px] font-bold text-slate-300 block truncate" title="{{ $type->name_kurdish }}">
                            {{ $cleanTypeName }}
                        </span>
                        <div class="flex items-baseline justify-between pt-1">
                            <span class="text-xl sm:text-2xl font-black font-mono text-teal-400">{{ number_format($countForType) }}</span>
                            <span class="text-[10px] text-slate-500 font-sans">مامەڵە</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Grand Total Count for Archiver -->
            <div class="flex items-center justify-between bg-slate-950 px-4 py-2.5 rounded-2xl border border-slate-800 text-xs">
                <span class="font-bold text-slate-300 flex items-center gap-2">
                    <i class="fa-solid fa-chart-column text-teal-400"></i>
                    <span>کۆی گشتی سەرجەم دەفتەرە ئەرشیفکراوەکان لەم ماوەیەدا:</span>
                </span>
                <span class="font-mono font-black text-sm text-teal-300">{{ number_format($archiverTotalCount) }} مامەڵەی فەرمی</span>
            </div>

            <!-- Pending Archive List Ready for Boxfile Submission -->
            @if(isset($pendingSubmitList) && $pendingSubmitList->count() > 0)
                <div class="pt-2 border-t border-teal-500/20 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-extrabold text-teal-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span>چاوەڕوانی سەپمیت و ئەرشیف ({{ $pendingSubmit }} مامەڵە ئامادەن بۆ بۆکس فایل):</span>
                        </span>
                        <a href="{{ route('transactions.index', ['stage' => 'archive']) }}" class="text-teal-400 hover:underline font-bold">
                            بینینی تەواوی ئەرشیف &larr;
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                        @foreach($pendingSubmitList->take(4) as $sTx)
                            <div class="bg-slate-950 p-3 rounded-xl border border-teal-500/30 flex items-center justify-between text-xs gap-2 shadow-sm">
                                <div class="min-w-0">
                                    <span class="font-mono text-sky-400 font-bold block">{{ $sTx->barcode }}</span>
                                    <span class="font-bold text-white block truncate">{{ $sTx->visitor_name }}</span>
                                    <span class="text-[10px] text-slate-400 block truncate">{{ $sTx->carMake->name_kurdish ?? '' }}</span>
                                </div>
                                <a href="{{ route('transactions.show', $sTx->id) }}" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-500 text-white font-black text-xs rounded-lg shadow shrink-0 transition flex items-center gap-1">
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                    <span>سەپمیت</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 6. VISUAL WORKFLOW PIPELINE -->
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
            @if(!auth()->check() || auth()->user()->role !== 'data_entry')
                <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-sky-400 hover:text-sky-300">
                    خشتەی تەواوی مامەڵەکان &larr;
                </a>
            @endif
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
                        <span class="text-[10px] text-slate-400 block mt-0.5">پشکنین • ئەمڕۆ: <strong class="text-orange-400 font-mono">{{ $todayInspectedCount }}</strong></span>
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
                        <span class="text-[10px] text-slate-400 block mt-0.5">وردبینی • ئەمڕۆ: <strong class="text-purple-400 font-mono">{{ $todayAuditedCount }}</strong></span>
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
                        <span class="text-[10px] text-slate-400 block mt-0.5">وەسڵی ۳۷/أ • ئەمڕۆ: <strong class="text-amber-400 font-mono">{{ $todayPaidCount }}</strong></span>
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
                        <span class="text-[10px] text-slate-400 block mt-0.5">دەفتەر • ئەمڕۆ: <strong class="text-sky-400 font-mono">{{ $todayBookletsCount }}</strong></span>
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
    <!-- 7. MAIN CONTENT SPLIT: RECENT TRANSACTIONS & AUDIT LOGS -->
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
                    @if(!auth()->check() || auth()->user()->role !== 'data_entry')
                        <a href="{{ route('transactions.index') }}" class="text-xs text-sky-400 hover:text-sky-300 hover:underline font-bold flex items-center gap-1">
                            <span>بینینی هەموو مامەڵەکان</span>
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        </a>
                    @endif
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
                                @if($canSeeMoney)
                                    <th class="p-3 text-center">بڕی پارە</th>
                                @else
                                    <th class="p-3 text-center">بەروار</th>
                                @endif
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
                                    @if($canSeeMoney)
                                        <td class="p-3 text-center font-mono font-bold text-emerald-400 whitespace-nowrap">
                                            {{ number_format($tx->total_pay) }} <span class="text-[10px] text-emerald-300/70 font-normal">د.ع</span>
                                        </td>
                                    @else
                                        <td class="p-3 text-center font-mono text-slate-300 whitespace-nowrap">
                                            {{ $tx->created_at ? $tx->created_at->format('Y-m-d') : '-' }}
                                        </td>
                                    @endif
                                    <td class="p-3 text-center whitespace-nowrap">
                                        @if($tx->is_cancelled || !empty($tx->cancelled_at))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">پووچەڵکراوە</span>
                                        @elseif($tx->is_submitted)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/10 text-teal-400 border border-teal-500/30">ئەرشیفکراوە</span>
                                        @elseif($tx->is_booklet_completed)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/30">دەفتەر دەرکراوە</span>
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
                                @if($canSeeMoney)
                                    <span class="font-mono text-emerald-400 font-bold text-xs">{{ number_format($tx->total_pay) }} د.ع</span>
                                @else
                                    <span class="font-mono text-slate-400 text-xs">{{ $tx->created_at ? $tx->created_at->format('Y-m-d') : '' }}</span>
                                @endif
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
                @if(!auth()->check() || auth()->user()->role !== 'data_entry')
                    <a href="{{ route('transactions.index') }}" class="text-sky-400 font-bold hover:underline">
                        گەڕان لە تەواوی مامەڵەکان &larr;
                    </a>
                @endif
            </div>
        </div>

        <!-- Right: Insights & Audit Log (4 Cols) -->
        <div class="lg:col-span-4 space-y-5">
            
            <!-- Widget 1: Transaction Types Distribution -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3.5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-sky-400"></i>
                        <span>دابەشبوونی جۆرەکانی مامەڵە</span>
                    </h3>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $showAllTimeCards ? $totalTransactions . ' کۆی گشتی' : $todayDataEntryCount . ' تۆمارکراوی ئەمڕۆ' }}</span>
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
                                <span class="font-bold text-white">{{ auth()->user()->clean_name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">پلە و دەسەڵات:</span>
                                <span class="font-black text-sky-400">{{ auth()->user()->clean_role_name }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            @if(auth()->user()->role !== 'data_entry')
                                <a href="{{ route('transactions.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold flex items-center justify-between transition">
                                    <span><i class="fa-solid fa-list-check ml-1.5 text-sky-400"></i> لیستی گشتی مامەڵەکان</span>
                                    <i class="fa-solid fa-chevron-left text-[10px] text-slate-400"></i>
                                </a>
                            @endif
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
@endsection
