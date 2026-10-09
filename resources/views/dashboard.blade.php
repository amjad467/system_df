@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-800 via-slate-800 to-sky-950 p-6 rounded-2xl border border-slate-700/80 shadow-xl flex flex-col md:flex-row items-center justify-between">
        <div class="space-y-1 mb-4 md:mb-0">
            <h2 class="text-2xl font-extrabold text-white">بەخێربێن بۆ بەشی کارگێڕی دەرهێنانی دەفتەر</h2>
            <p class="text-sm text-slate-400">چاودێریکردن و ئیدارەدانی گشت قۆناغەکانی مامەڵە، کەشف، وردبینی، ژمێریاری و لۆگی جولەکان.</p>
        </div>
        <div class="flex items-center space-x-3 space-x-reverse flex-wrap gap-2">
            <a href="{{ route('reports.accounting_66') }}" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-amber-600/25 flex items-center transition">
                <i class="fa-solid fa-file-invoice-dollar ml-2"></i> ڕاپۆرتی محاسبة ٦٦
            </a>
            <a href="{{ route('transactions.expired_booklets') }}" class="px-4 py-2.5 bg-rose-600/90 hover:bg-rose-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-rose-600/25 flex items-center transition">
                <i class="fa-solid fa-calendar-xmark ml-2"></i> دەفتەرە بەسەرچووەکان
            </a>
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'data_entry'))
                <a href="{{ route('transactions.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-sky-500/25 flex items-center transition">
                    <i class="fa-solid fa-plus ml-2"></i> دروستکردنی مامەڵەی نوێ
                </a>
            @endif
        </div>
    </div>


    <!-- Alert Banner for Returned Transactions -->
    @if($returnedCount > 0)
        <div class="bg-rose-500/20 border-2 border-rose-500/60 rounded-2xl p-5 shadow-xl space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 space-x-reverse">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/30 text-rose-300 flex items-center justify-center text-xl font-bold animate-pulse">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-white text-base">⚠️ ئاگاداری: {{ $returnedCount }} مامەڵە گەڕێنراوەتەوە لەلایەن وردبینەوە! (هەڵەی تێدایە)</h3>
                        <p class="text-xs text-rose-200">تکایە داتائەنتەری یان ئەندازیاری تەخمین زانیارییەکان ڕاست بکەنەوە.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-2 border-t border-rose-500/30">
                @foreach($returnedTransactions as $rt)
                    <div class="bg-slate-900/90 border border-rose-500/40 p-3 rounded-xl flex items-center justify-between text-xs">
                        <div class="space-y-0.5">
                            <span class="font-mono text-sky-400 font-bold block">{{ $rt->barcode }}</span>
                            <span class="font-bold text-white block">{{ $rt->visitor_name }}</span>
                            <span class="text-[11px] text-rose-300 block">هۆکار: {{ Str::limit($rt->return_reason, 35) }}</span>
                        </div>
                        <a href="{{ route('transactions.show', $rt->id) }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-lg shadow transition">
                            ڕاستکردنەوە
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Statistics Cards Grid -->

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Transactions -->
        <div class="bg-slate-800/80 border border-slate-700/70 p-5 rounded-2xl shadow-lg hover:border-sky-500/50 transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">کۆی مامەڵەکان</span>
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-folder-open text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black text-white">{{ number_format($totalTransactions) }}</span>
                <span class="text-xs text-slate-500 block mt-1">سەرجەم تۆمارەکان</span>
            </div>
        </div>

        <!-- Total Income -->
        <div class="bg-slate-800/80 border border-slate-700/70 p-5 rounded-2xl shadow-lg hover:border-emerald-500/50 transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">کۆی داهات (IQD)</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-money-bill-wave text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black text-emerald-400">{{ number_format($totalIncome) }}</span>
                <span class="text-xs text-slate-500 block mt-1">دیناری عێراقی وەرگیراو</span>
            </div>
        </div>

        <!-- Pending Stages -->
        <div class="bg-slate-800/80 border border-slate-700/70 p-5 rounded-2xl shadow-lg hover:border-amber-500/50 transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">چاوەڕوانی وەسڵ و پارەدا</span>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-receipt text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black text-amber-400">{{ number_format($pendingPayment) }}</span>
                <span class="text-xs text-slate-500 block mt-1">چاوەڕێی بڕینی وەسڵی ۳۷/أ</span>
            </div>
        </div>

        <!-- Completed Booklets -->
        <div class="bg-slate-800/80 border border-slate-700/70 p-5 rounded-2xl shadow-lg hover:border-indigo-500/50 transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">دەفتەری تەواوکراو</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-book-bookmark text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black text-indigo-300">{{ number_format($completedBooklets) }}</span>
                <span class="text-xs text-slate-500 block mt-1">تەواوکراو و چاپکراو</span>
            </div>
        </div>
    </div>

    <!-- Workflow Status Tabs Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('transactions.index', ['stage' => 'inspection']) }}" class="p-4 bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 rounded-xl flex items-center justify-between transition">
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-200">چاوەڕوانی کەشف و تەخمین</h4>
                    <p class="text-xs text-slate-500">پشکنینی سەرەتایی ئۆتۆمبێل</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-orange-500/10 text-orange-400 font-bold rounded-lg text-xs">{{ $pendingInspection }}</span>
        </a>

        <a href="{{ route('transactions.index', ['stage' => 'audit']) }}" class="p-4 bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 rounded-xl flex items-center justify-between transition">
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-200">چاوەڕوانی وردبینی</h4>
                    <p class="text-xs text-slate-500">پەسەندکردنی داتاکان</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-purple-500/10 text-purple-400 font-bold rounded-lg text-xs">{{ $pendingAudit }}</span>
        </a>

        <a href="{{ route('transactions.index', ['stage' => 'booklet']) }}" class="p-4 bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 rounded-xl flex items-center justify-between transition">
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center">
                    <i class="fa-solid fa-print"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-200">چاوەڕوانی چاپکردنی دەفتەر</h4>
                    <p class="text-xs text-slate-500">تۆمارکردنی ژمارەی دەفتەر</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-blue-500/10 text-blue-400 font-bold rounded-lg text-xs">{{ $pendingBooklet }}</span>
        </a>
    </div>

    <!-- Main Content Area Grid (Recent Transactions & System Audit Logs) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Transactions Table -->
        <div class="lg:col-span-2 bg-slate-800/80 border border-slate-700/80 rounded-2xl p-5 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <i class="fa-solid fa-clock ml-2 text-sky-400"></i> دوایین مامەڵە تۆمارکراوەکان
                    </h3>
                    <a href="{{ route('transactions.index') }}" class="text-xs text-sky-400 hover:text-sky-300 font-medium">بینینی گشتی &larr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm text-slate-300">
                        <thead class="bg-slate-900/60 text-slate-400 text-xs uppercase border-b border-slate-700">
                            <tr>
                                <th class="p-3">بارکۆد</th>
                                <th class="p-3">خاوەن / شۆفێر</th>
                                <th class="p-3">مامەڵە</th>
                                <th class="p-3">ژمارەی تابلۆ</th>
                                <th class="p-3">بڕی پارە</th>
                                <th class="p-3 text-center">بارودۆخ</th>
                                <th class="p-3">کارەکردنی خێرا</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($recentTransactions as $tx)
                                <tr class="hover:bg-slate-700/30 transition">
                                    <td class="p-3 font-mono text-xs text-sky-400 font-semibold">{{ $tx->barcode }}</td>
                                    <td class="p-3 font-medium text-slate-100">{{ $tx->visitor_name }}</td>
                                    <td class="p-3 text-xs text-slate-400">{{ $tx->transactionType->name_kurdish ?? '-' }}</td>
                                    <td class="p-3 font-mono text-xs text-emerald-400 font-bold">{{ $tx->plate_number }}</td>
                                    <td class="p-3 text-xs font-bold text-white">{{ number_format($tx->total_pay) }} د.ع</td>
                                    <td class="p-3 text-center">
                                        @if($tx->is_cancelled)
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">پووچەڵکراوە</span>
                                        @elseif($tx->is_booklet_completed)
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">تەواوکراوە</span>
                                        @elseif($tx->is_paid)
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">پارەدراوە</span>
                                        @elseif($tx->is_audited)
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">چاوەڕێی پارەدا</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-700 text-slate-300">لە قۆناغدا</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <a href="{{ route('transactions.show', $tx->id) }}" class="px-2.5 py-1 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-xs font-semibold transition">
                                            بینین
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-4 text-center text-slate-500 text-xs">هیچ مامەڵەیەک نەدۆزرایەوە.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Audit Logs -->
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-5 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fa-solid fa-shield-halved ml-2 text-emerald-400"></i> لۆگی جولەکان (Audit Log)
                </h3>
                <a href="{{ route('logs.index') }}" class="text-xs text-sky-400 hover:text-sky-300 font-medium">سەرجەمی &larr;</a>
            </div>

            <div class="space-y-3">
                @forelse($recentLogs as $log)
                    <div class="p-3 bg-slate-900/60 border border-slate-700/50 rounded-xl space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-200">{{ $log->user_name }}</span>
                            <span class="text-slate-500 text-[10px] font-mono">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-sky-400 font-semibold">{{ $log->action }}</p>
                        @if($log->details)
                            <p class="text-[11px] text-slate-400 truncate">{{ $log->details }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">هیچ لۆگێک تۆمار نەکراوە.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
