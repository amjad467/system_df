@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fa-solid fa-calendar-xmark ml-2 text-rose-400"></i> ڕاپۆرتی دەفتەرە بەسەرچووەکان و بەدواداچوون
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                ئەو دەفتەرانەی بەرواری بەسەرچوونیان تەواو بووە یان لە ماوەی ٣٠ ڕۆژی داهاتوو بەسەردەچن و تا ئێستا تازە نەکراونەتەوە یان پووچەڵ نەکراونەتەوە.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('transactions.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center">
                <i class="fa-solid fa-plus-circle ml-1.5"></i> تۆمارکردنی مامەڵەی نوێ
            </a>
            <a href="{{ route('transactions.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700 transition flex items-center">
                <i class="fa-solid fa-list-check ml-1.5"></i> هەموو مامەڵەکان
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Expired Count -->
        <a href="{{ route('transactions.expired_booklets', ['status' => 'expired', 'search' => request('search')]) }}" 
           class="p-5 rounded-2xl border transition {{ $status === 'expired' ? 'bg-rose-950/40 border-rose-500 ring-2 ring-rose-500/50 shadow-xl shadow-rose-900/30' : 'bg-slate-800/80 border-slate-700/80 hover:border-rose-500/50' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-rose-300 block mb-1">بەسەرچووەکان (دەستبەجێ پێویست بە تازەکردنەوە/پووچەڵکردنەوە)</span>
                    <span class="text-3xl font-black text-rose-400 font-mono">{{ number_format($expiredCount) }}</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <span class="text-[11px] text-rose-300/80 mt-2 block">بەرواری بەسەرچوونیان پێش ئەمڕۆ بووە</span>
        </a>

        <!-- Expiring Soon Count -->
        <a href="{{ route('transactions.expired_booklets', ['status' => 'expiring_soon', 'search' => request('search')]) }}" 
           class="p-5 rounded-2xl border transition {{ $status === 'expiring_soon' ? 'bg-amber-950/40 border-amber-500 ring-2 ring-amber-500/50 shadow-xl shadow-amber-900/30' : 'bg-slate-800/80 border-slate-700/80 hover:border-amber-500/50' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-amber-300 block mb-1">لە ٣٠ ڕۆژی داهاتوودا بەسەردەچن</span>
                    <span class="text-3xl font-black text-amber-400 font-mono">{{ number_format($expiringSoonCount) }}</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <span class="text-[11px] text-amber-300/80 mt-2 block">ئاگاداری بۆ تازەکردنەوەی پێشوەختە</span>
        </a>

        <!-- Total Count -->
        <a href="{{ route('transactions.expired_booklets', ['status' => 'all', 'search' => request('search')]) }}" 
           class="p-5 rounded-2xl border transition {{ $status === 'all' ? 'bg-sky-950/40 border-sky-500 ring-2 ring-sky-500/50 shadow-xl shadow-sky-900/30' : 'bg-slate-800/80 border-slate-700/80 hover:border-sky-500/50' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-sky-300 block mb-1">کۆی گشتی بەدواداچوونی دەفتەرەکان</span>
                    <span class="text-3xl font-black text-sky-400 font-mono">{{ number_format($totalCount) }}</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
            </div>
            <span class="text-[11px] text-sky-300/80 mt-2 block">بەسەرچوو + لە ٣٠ ڕۆژدا بەسەرچوو</span>
        </a>
    </div>

    <!-- Search and Filter Bar -->
    <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-4 shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('transactions.expired_booklets', ['status' => 'expired', 'search' => request('search')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'expired' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'bg-slate-900 text-slate-300 hover:text-white border border-slate-700' }}">
                <i class="fa-solid fa-circle-exclamation text-xs"></i> بەسەرچووەکان ({{ $expiredCount }})
            </a>
            <a href="{{ route('transactions.expired_booklets', ['status' => 'expiring_soon', 'search' => request('search')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'expiring_soon' ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/30' : 'bg-slate-900 text-slate-300 hover:text-white border border-slate-700' }}">
                <i class="fa-solid fa-clock text-xs"></i> لە ٣٠ ڕۆژدا ({{ $expiringSoonCount }})
            </a>
            <a href="{{ route('transactions.expired_booklets', ['status' => 'all', 'search' => request('search')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'all' ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'bg-slate-900 text-slate-300 hover:text-white border border-slate-700' }}">
                <i class="fa-solid fa-layer-group text-xs"></i> هەمووی ({{ $totalCount }})
            </a>
        </div>

        <!-- Search Input -->
        <form action="{{ route('transactions.expired_booklets') }}" method="GET" class="w-full md:w-80 flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative w-full">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="گەڕان (تابلۆ، هاووڵاتی، شاسی، دەفتەر)..." 
                       class="w-full bg-slate-900 text-xs text-slate-100 placeholder-slate-500 rounded-xl border border-slate-700 pr-9 pl-3 py-2.5 focus:border-sky-500 focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-xs text-slate-400"></i>
            </div>
            @if(request('search'))
                <a href="{{ route('transactions.expired_booklets', ['status' => $status]) }}" class="px-3 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-bold rounded-xl transition">
                    پاککردنەوە
                </a>
            @else
                <button type="submit" class="px-3.5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow transition">
                    بگەڕێ
                </button>
            @endif
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-200">
                <thead class="bg-slate-900 text-slate-400 uppercase border-b border-slate-700">
                    <tr>
                        <th class="p-3.5 font-bold"># بارکۆد</th>
                        <th class="p-3.5 font-bold">هاووڵاتی</th>
                        <th class="p-3.5 font-bold">ژمارەی تابلۆ</th>
                        <th class="p-3.5 font-bold">جۆری ئۆتۆمبێل / مۆدێل</th>
                        <th class="p-3.5 font-bold">ژمارەی دەفتەر</th>
                        <th class="p-3.5 font-bold">بەرواری بەسەرچوون</th>
                        <th class="p-3.5 font-bold">ڕەوش و ماوە</th>
                        <th class="p-3.5 font-bold text-center">کرداری خێرا (بەدواداچوون)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($transactions as $tx)
                        @php
                            $isPast = $tx->end_date && $tx->end_date->isPast();
                            $daysDiff = $tx->end_date ? abs(now()->startOfDay()->diffInDays($tx->end_date->startOfDay(), false)) : 0;
                        @endphp
                        <tr class="hover:bg-slate-750/50 transition">
                            <!-- Barcode -->
                            <td class="p-3.5 font-mono text-sky-400 font-bold">
                                <a href="{{ route('transactions.show', $tx->id) }}" class="hover:underline flex items-center gap-1">
                                    <i class="fa-solid fa-barcode text-xs"></i>
                                    <span>{{ $tx->barcode }}</span>
                                </a>
                            </td>

                            <!-- Visitor Name -->
                            <td class="p-3.5">
                                <div class="font-bold text-slate-100">{{ $tx->visitor_name }}</div>
                                @if($tx->visitor_name_eng)
                                    <div class="text-[11px] text-slate-400 font-mono" dir="ltr">{{ $tx->visitor_name_eng }}</div>
                                @endif
                                @if($tx->phone_number)
                                    <a href="tel:{{ $tx->phone_number }}" class="text-[11px] text-emerald-400 font-mono flex items-center gap-1 hover:underline mt-0.5">
                                        <i class="fa-solid fa-phone text-[9px]"></i> {{ $tx->phone_number }}
                                    </a>
                                @endif
                            </td>

                            <!-- Plate -->
                            <td class="p-3.5">
                                <span class="font-mono font-bold text-emerald-400 bg-emerald-950/60 border border-emerald-500/30 px-2 py-0.5 rounded text-xs">
                                    {{ $tx->plate_number }}
                                </span>
                                <span class="text-[11px] text-slate-400 block mt-0.5">{{ $tx->plateType->name_kurdish ?? '' }}</span>
                            </td>

                            <!-- Car Make / Model -->
                            <td class="p-3.5">
                                <div class="font-bold text-slate-200">{{ $tx->carMake->name_kurdish ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">مۆدێل: {{ $tx->model_year ?? '-' }}</div>
                            </td>

                            <!-- Booklet Number -->
                            <td class="p-3.5 font-mono font-bold text-amber-300">
                                {{ $tx->booklet_number ?? 'تۆمارنەکراوە' }}
                            </td>

                            <!-- End Date -->
                            <td class="p-3.5 font-mono text-xs">
                                @if($tx->end_date)
                                    <div class="font-bold {{ $isPast ? 'text-rose-400' : 'text-amber-400' }}">
                                        {{ $tx->end_date->format('Y-m-d') }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        دەستپێکردن: {{ $tx->start_date ? $tx->start_date->format('Y-m-d') : '-' }}
                                    </div>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td class="p-3.5">
                                @if($isPast)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> بەسەرچووە ({{ $daysDiff }} ڕۆژ لەمەوبەر)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        <i class="fa-solid fa-clock text-[10px]"></i> ماوە: {{ $daysDiff }} ڕۆژ
                                    </span>
                                @endif
                            </td>

                            <!-- Quick Action Buttons -->
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <!-- Renew (تازەکردنەوە - Type 2) -->
                                    <a href="{{ route('transactions.create', ['from_transaction' => $tx->id, 'target_type' => 2]) }}" 
                                       title="تازەکردنەوەی دەفتەر بە زانیاری پێشوو" 
                                       class="px-2.5 py-1.5 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow">
                                        <i class="fa-solid fa-rotate-right"></i>
                                        <span>تازەکردنەوە</span>
                                    </a>

                                    <!-- Cancel (پووچەڵکردنەوە - Type 5) -->
                                    <a href="{{ route('transactions.create', ['from_transaction' => $tx->id, 'target_type' => 5]) }}" 
                                       title="پووچەڵکردنەوەی دەفتەر بە زانیاری پێشوو" 
                                       class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow">
                                        <i class="fa-solid fa-ban"></i>
                                        <span>پووچەڵکردنەوە</span>
                                    </a>

                                    <!-- More Actions Dropdown / Direct View -->
                                    <a href="{{ route('transactions.show', $tx->id) }}" 
                                       title="بینینی وردەکاری تەواوی مامەڵە" 
                                       class="px-2 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-xs transition">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                <div class="w-16 h-16 rounded-full bg-slate-900 mx-auto flex items-center justify-center text-2xl text-slate-600 mb-3">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <span class="font-bold block text-sm text-slate-300">هیچ دەفتەرێکی بەسەرچوو نەدۆزرایەوە!</span>
                                <span class="text-xs text-slate-500 block mt-1">هەموو دەفتەرەکان لە کاتی خۆیاندا تازە کراونەتەوە یان پووچەڵ کراونەتەوە.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-700 bg-slate-900/50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
