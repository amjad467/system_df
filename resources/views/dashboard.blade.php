@extends('layouts.app')

@section('content')
<div class="space-y-6 w-full max-w-full overflow-hidden">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-700/80 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1 min-w-0">
            <h2 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-gauge-high text-sky-400"></i>
                بەخێربێن بۆ سیستەمی گومرگی سلێمانی
            </h2>
            <p class="text-xs sm:text-sm text-slate-300">
                بەڕێوەبردن و بەدواداچوونی وردی مامەڵەکانی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری.
            </p>
        </div>
        <div class="flex items-center flex-wrap gap-2 w-full md:w-auto shrink-0">
            <!-- محاسبة ٦٦ (Cashier & Admin ONLY) -->
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'cashier'))
                <a href="{{ route('reports.accounting_66') }}" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-amber-600/25 flex items-center transition">
                    <i class="fa-solid fa-file-invoice-dollar ml-1.5"></i> محاسبة ٦٦
                </a>
            @endif

            <!-- دەفتەرە بەسەرچووەکان (Booklet & Admin ONLY) -->
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet'))
                <a href="{{ route('transactions.expired_booklets') }}" class="px-3.5 py-2 bg-rose-600/90 hover:bg-rose-500 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-rose-600/25 flex items-center transition">
                    <i class="fa-solid fa-calendar-xmark ml-1.5"></i> دەفتەرە بەسەرچووەکان
                </a>
            @endif

            <!-- دروستکردنی مامەڵەی نوێ (Data Entry & Admin ONLY) -->
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'data_entry'))
                <a href="{{ route('transactions.create') }}" class="px-4 py-2 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-sky-500/25 flex items-center transition">
                    <i class="fa-solid fa-plus-circle ml-1.5"></i> مامەڵەی نوێ
                </a>
            @endif
        </div>
    </div>

    <!-- Alert Banner for Returned Transactions -->
    @if($returnedCount > 0)
        <div class="bg-rose-500/10 dark:bg-rose-500/20 border-2 border-rose-500/50 rounded-2xl p-4 sm:p-5 shadow-lg space-y-3">
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-500 dark:text-rose-300 flex items-center justify-center text-xl font-bold shrink-0 animate-pulse">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-black text-rose-600 dark:text-rose-200 text-sm sm:text-base">
                        ئاگاداری: {{ $returnedCount }} مامەڵە گەڕێنراوەتەوە بەهۆی هەڵەوە!
                    </h3>
                    <p class="text-xs text-rose-500/80 dark:text-rose-300">
                        تکایە بەشی داتائەنتەری و ئەندازیاری تەخمین پێداچوونەوە بکەن بۆ ڕاستکردنەوە.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 pt-2 border-t border-rose-500/20">
                @foreach($returnedTransactions as $rt)
                    <div class="bg-white dark:bg-slate-900/90 border border-rose-300 dark:border-rose-500/40 p-3 rounded-xl flex items-center justify-between text-xs min-w-0 shadow-sm">
                        <div class="space-y-0.5 min-w-0 pr-1">
                            <span class="font-mono text-sky-600 dark:text-sky-400 font-bold block">{{ $rt->barcode }}</span>
                            <span class="font-bold text-slate-800 dark:text-white block truncate">{{ $rt->visitor_name }}</span>
                            <span class="text-[11px] text-rose-500 dark:text-rose-300 block truncate">هۆکار: {{ Str::limit($rt->return_reason, 30) }}</span>
                        </div>
                        <a href="{{ route('transactions.show', $rt->id) }}" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-lg shadow shrink-0 transition">
                            ڕاستکردنەوە
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Statistics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Transactions -->
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/70 p-5 rounded-2xl shadow-sm dark:shadow-lg hover:border-sky-500/50 transition group min-w-0">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">کۆی مامەڵەکان</span>
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-500 dark:text-sky-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-folder-open text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-white">{{ number_format($totalTransactions) }}</span>
                <span class="text-xs text-slate-400 dark:text-slate-500 block mt-0.5">سەرجەم تۆمارکراوەکان</span>
            </div>
        </div>

        <!-- Total Income -->
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/70 p-5 rounded-2xl shadow-sm dark:shadow-lg hover:border-emerald-500/50 transition group min-w-0">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">کۆی داهات (IQD)</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-money-bill-wave text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($totalIncome) }}</span>
                <span class="text-xs text-slate-400 dark:text-slate-500 block mt-0.5">دیناری عێراقی وەرگیراو</span>
            </div>
        </div>

        <!-- Pending Stages -->
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/70 p-5 rounded-2xl shadow-sm dark:shadow-lg hover:border-amber-500/50 transition group min-w-0">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">چاوەڕوانی وەسڵ و پارەدان</span>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-receipt text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-black text-amber-500 dark:text-amber-400">{{ number_format($pendingPayment) }}</span>
                <span class="text-xs text-slate-400 dark:text-slate-500 block mt-0.5">چاوەڕێی بڕینی وەسڵی ۳۷/أ</span>
            </div>
        </div>

        <!-- Completed Booklets -->
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/70 p-5 rounded-2xl shadow-sm dark:shadow-lg hover:border-indigo-500/50 transition group min-w-0">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">دەفتەری تەواوکراو</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-500 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-book-bookmark text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-black text-indigo-600 dark:text-indigo-300">{{ number_format($completedBooklets) }}</span>
                <span class="text-xs text-slate-400 dark:text-slate-500 block mt-0.5">تەواوکراو و چاپکراو</span>
            </div>
        </div>
    </div>

    <!-- Workflow Status Quick Counters -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <a href="{{ route('transactions.index', ['stage' => 'inspection']) }}" class="p-3.5 bg-white dark:bg-slate-800/60 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 rounded-xl flex items-center justify-between transition min-w-0 shadow-sm">
            <div class="flex items-center space-x-3 space-x-reverse min-w-0">
                <div class="w-8 h-8 rounded-lg bg-orange-500/10 text-orange-500 dark:text-orange-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 truncate">چاوەڕوانی کەشف و تەخمین</h4>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">پشکنینی ئۆتۆمبێل</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-orange-500/10 text-orange-500 dark:text-orange-400 font-black rounded-lg text-xs shrink-0">{{ $pendingInspection }}</span>
        </a>

        <a href="{{ route('transactions.index', ['stage' => 'audit']) }}" class="p-3.5 bg-white dark:bg-slate-800/60 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 rounded-xl flex items-center justify-between transition min-w-0 shadow-sm">
            <div class="flex items-center space-x-3 space-x-reverse min-w-0">
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-500 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 truncate">چاوەڕوانی وردبینی</h4>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">پەسەندکردنی داتاکان</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-purple-500/10 text-purple-500 dark:text-purple-400 font-black rounded-lg text-xs shrink-0">{{ $pendingAudit }}</span>
        </a>

        <a href="{{ route('transactions.index', ['stage' => 'booklet']) }}" class="p-3.5 bg-white dark:bg-slate-800/60 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 rounded-xl flex items-center justify-between transition min-w-0 shadow-sm">
            <div class="flex items-center space-x-3 space-x-reverse min-w-0">
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-print"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 truncate">چاوەڕوانی چاپی دەفتەر</h4>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">تۆمارکردنی ژمارە</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-blue-500/10 text-blue-500 dark:text-blue-400 font-black rounded-lg text-xs shrink-0">{{ $pendingBooklet }}</span>
        </a>
    </div>

    <!-- Main Content Area Grid (Recent Transactions & System Audit Logs) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Recent Transactions Table -->
        <div class="{{ auth()->user()->isAdmin() ? 'lg:col-span-2' : 'lg:col-span-2' }} bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-5 shadow-sm dark:shadow-xl flex flex-col justify-between min-w-0">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 dark:text-white flex items-center">
                        <i class="fa-solid fa-clock ml-2 text-sky-500"></i> دوایین مامەڵە تۆمارکراوەکان
                    </h3>
                    <a href="{{ route('transactions.index') }}" class="text-xs text-sky-600 dark:text-sky-400 hover:underline font-bold">بینینی گشتی &larr;</a>
                </div>

                <div class="overflow-x-auto w-full rounded-xl border border-slate-200 dark:border-slate-700/70">
                    <table class="w-full text-right text-xs text-slate-700 dark:text-slate-300 min-w-[600px]">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase border-b border-slate-200 dark:border-slate-700 font-bold">
                            <tr>
                                <th class="p-2.5">بارکۆد</th>
                                <th class="p-2.5">خاوەن / شۆفێر</th>
                                <th class="p-2.5">جۆری مامەڵە</th>
                                <th class="p-2.5">ژمارەی تابلۆ</th>
                                <th class="p-2.5">بڕی پارە</th>
                                <th class="p-2.5 text-center">بارودۆخ</th>
                                <th class="p-2.5 text-center">کردار</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @forelse($recentTransactions as $tx)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                    <td class="p-2.5 font-mono text-xs text-sky-600 dark:text-sky-400 font-bold whitespace-nowrap">{{ $tx->barcode }}</td>
                                    <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100 max-w-[130px] truncate">{{ $tx->visitor_name }}</td>
                                    <td class="p-2.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $tx->transactionType->name_kurdish ?? '-' }}</td>
                                    <td class="p-2.5 font-mono text-emerald-600 dark:text-emerald-400 font-bold whitespace-nowrap">{{ $tx->plate_number }}</td>
                                    <td class="p-2.5 font-bold text-slate-800 dark:text-white whitespace-nowrap">{{ number_format($tx->total_pay) }} د.ع</td>
                                    <td class="p-2.5 text-center whitespace-nowrap">
                                        @if($tx->is_cancelled)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">پووچەڵکراوە</span>
                                        @elseif($tx->is_booklet_completed)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">تەواوکراوە</span>
                                        @elseif($tx->is_paid)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-500 border border-blue-500/20">پارەدراوە</span>
                                        @elseif($tx->is_audited)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">چاوەڕێی پارەدان</span>
                                        @elseif($tx->is_inspected)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-500 border border-purple-500/20">چاوەڕێی وردبینی</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">چاوەڕێی کەشف</span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-center whitespace-nowrap">
                                        <a href="{{ route('transactions.show', $tx->id) }}" class="px-2.5 py-1 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-xs font-bold transition">
                                            بینین
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-4 text-center text-slate-400 text-xs">هیچ مامەڵەیەک نەدۆزرایەوە.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Side Panel: Recent Audit Logs for Admin (Point 4), or Department Dashboard for other roles -->
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-5 shadow-sm dark:shadow-xl min-w-0">
            @if(auth()->user()->isAdmin())
                <!-- Admin ONLY: Audit Logs Panel -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 dark:text-white flex items-center">
                        <i class="fa-solid fa-shield-halved ml-2 text-emerald-500"></i> لۆگی جولەکان (Audit Log)
                    </h3>
                    <a href="{{ route('logs.index') }}" class="text-xs text-sky-600 dark:text-sky-400 hover:underline font-bold">سەرجەمی &larr;</a>
                </div>

                <div class="space-y-2.5">
                    @forelse($recentLogs as $log)
                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/50 rounded-xl space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $log->user_name }}</span>
                                <span class="text-slate-400 dark:text-slate-500 text-[10px] font-mono">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-sky-600 dark:text-sky-400 font-semibold">{{ $log->action }}</p>
                            @if($log->details)
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $log->details }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">هیچ لۆگێک تۆمار نەکراوە.</p>
                    @endforelse
                </div>
            @else
                <!-- Non-Admin: Department Quick Info & Workflow Summary -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-user-shield text-sky-500"></i>
                            زانیاری دەسەڵات و بەشەکەت
                        </h3>
                    </div>

                    <div class="p-3.5 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700/60 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">ناوی بەکارهێنەر:</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">دەسەڵاتی سیستەم:</span>
                            <span class="font-black text-sky-600 dark:text-sky-400">{{ auth()->user()->role_name_kurdish }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <span class="font-bold text-slate-700 dark:text-slate-300 block">بەستەرە خێراکان بەپێی بەشەکەت:</span>
                        
                        <a href="{{ route('transactions.index') }}" class="w-full p-2.5 rounded-xl bg-slate-100 dark:bg-slate-900 hover:bg-sky-600 hover:text-white transition flex items-center justify-between font-bold text-slate-700 dark:text-slate-200">
                            <span><i class="fa-solid fa-list-check ml-1.5 text-sky-500"></i> لیستی گشتی مامەڵەکان</span>
                            <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        </a>

                        @if(auth()->user()->role === 'data_entry')
                            <a href="{{ route('transactions.create') }}" class="w-full p-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white transition flex items-center justify-between font-bold">
                                <span><i class="fa-solid fa-plus-circle ml-1.5"></i> تۆمارکردنی مامەڵەی نوێ</span>
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </a>
                        @elseif(auth()->user()->role === 'cashier')
                            <a href="{{ route('reports.accounting_66') }}" class="w-full p-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white transition flex items-center justify-between font-bold">
                                <span><i class="fa-solid fa-file-invoice-dollar ml-1.5"></i> ڕاپۆرتی محاسبة ٦٦</span>
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </a>
                        @elseif(auth()->user()->role === 'booklet')
                            <a href="{{ route('transactions.expired_booklets') }}" class="w-full p-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white transition flex items-center justify-between font-bold">
                                <span><i class="fa-solid fa-calendar-xmark ml-1.5"></i> دەفتەرە بەسەرچووەکان</span>
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </a>
                            <a href="{{ route('transactions.official_prints') }}" class="w-full p-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white transition flex items-center justify-between font-bold">
                                <span><i class="fa-solid fa-print ml-1.5"></i> چاپی نوسراوە فەرمییەکان</span>
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
