@extends('layouts.app')

@section('title', 'لۆگی سیستەم • بەڕێوەبەرایەتی گومرگی سلێمانی')

@section('content')
<div class="space-y-6">

    <!-- Hero Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950/40 to-slate-900 border border-indigo-500/30 p-6 shadow-2xl">
        <div class="absolute -top-16 -right-16 w-48 h-48 bg-indigo-600/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-sky-600/10 rounded-full blur-3xl"></div>

        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-700 flex items-center justify-center text-white text-2xl shadow-lg shadow-indigo-500/20 border border-indigo-400/30">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-white">تۆماری چالاکی و لۆگی سیستەم</h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            <i class="fa-solid fa-fingerprint text-[10px]"></i>
                            ووردبینی هەموو جوڵەکان • Audit Trail
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        تۆماری چرکە بە چرکەی هەموو چوونەژوورەوە، زیادکردن، دەستکاریکردن، وەرگرتنی پارە، و چاپکردن لە لایەن فەرمانبەرانەوە
                    </p>
                </div>
            </div>

            <!-- Export to Excel/CSV Button -->
            <div class="flex items-center gap-2">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" 
                   class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black transition flex items-center gap-2 shadow-md shadow-emerald-600/30">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>داگرتنی ڕاپۆرت (Excel / CSV)</span>
                </a>
                <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition flex items-center gap-1.5 border border-slate-700">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>داشبۆرد</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 4 KPI Overview Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900/90 rounded-2xl p-5 border border-slate-800 shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400">کۆی گشتی تۆمارەکان</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-database"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-white">{{ number_format($stats['total_logs']) }}</span>
                <span class="text-[11px] text-slate-400">چالاکی</span>
            </div>
        </div>

        <div class="bg-slate-900/90 rounded-2xl p-5 border border-slate-800 shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400">چالاکییەکانی ئەمڕۆ</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-emerald-400">{{ number_format($stats['today_logs']) }}</span>
                <span class="text-[11px] text-slate-400">جوڵەی ئەمڕۆ</span>
            </div>
        </div>

        <div class="bg-slate-900/90 rounded-2xl p-5 border border-slate-800 shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400">فەرمانبەرانی چالاک ئەمڕۆ</span>
                <div class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-sky-400">{{ number_format($stats['active_users_today']) }}</span>
                <span class="text-[11px] text-slate-400">بەکارهێنەر</span>
            </div>
        </div>

        <div class="bg-slate-900/90 rounded-2xl p-5 border border-slate-800 shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400">کردارە هەستیارەکان</span>
                <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-amber-400">{{ number_format($stats['sensitive_actions']) }}</span>
                <span class="text-[11px] text-slate-400">سڕینەوە و دەستکاری</span>
            </div>
        </div>
    </div>

    <!-- Advanced Filter & Search Bar -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl">
        <form method="GET" action="{{ route('logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
            
            <!-- 1. Search Query -->
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-bold text-slate-400 mb-1">گەڕان بەپێی وشە، وردەکاری یان ئایپی:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="بۆ نموونە: شاسی، پارە، ناوی کارمەند، ئایپی..." 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-500 text-xs"></i>
                </div>
            </div>

            <!-- 2. Filter by User -->
            <div class="lg:col-span-3">
                <label class="block text-[11px] font-bold text-slate-400 mb-1">فلتەر بەپێی بەکارهێنەر / فەرمانبەر:</label>
                <select name="user_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">هەموو بەکارهێنەرەکان</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Filter by Action Type -->
            <div class="lg:col-span-3">
                <label class="block text-[11px] font-bold text-slate-400 mb-1">فلتەر بەپێی جۆری کردار:</label>
                <select name="action" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">هەموو جۆرەکانی کردار</option>
                    @foreach($distinctActions as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>
                            {{ $act }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Date From -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-400 mb-1">لە بەرواری:</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 font-mono">
            </div>

            <!-- 5. Date To -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-400 mb-1">تا بەرواری:</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 font-mono">
            </div>

            <!-- 6. Action Buttons -->
            <div class="lg:col-span-2 flex items-end gap-1.5">
                <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-md shadow-indigo-600/20">
                    <i class="fa-solid fa-filter"></i>
                    <span>فلتەرکردن</span>
                </button>
                @if(request()->hasAny(['search', 'user_id', 'action', 'date_from', 'date_to']))
                    <a href="{{ route('logs.index') }}" class="p-2 bg-slate-800 hover:bg-rose-900/40 text-slate-400 hover:text-rose-300 rounded-xl text-xs flex items-center justify-center transition border border-slate-700" title="پاککردنەوەی هەموو فلتەرەکان">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Activity Logs Table -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950/80 text-slate-400 text-[11px] font-bold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 text-center w-12">#</th>
                        <th class="p-3.5">بەکارهێنەر</th>
                        <th class="p-3.5">جولە / کردار</th>
                        <th class="p-3.5">وردەکاری و زانیاری</th>
                        <th class="p-3.5">ناونیشانی ئایپی (IP)</th>
                        <th class="p-3.5">کات و بەروار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($logs as $log)
                        @php
                            $actionLower = mb_strtolower($log->action);
                            $badgeClass = 'bg-sky-500/15 text-sky-400 border-sky-500/30';
                            $icon = 'fa-solid fa-circle-dot';

                            if (str_contains($actionLower, 'دروستکردن') || str_contains($actionLower, 'نوێ')) {
                                $badgeClass = 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30';
                                $icon = 'fa-solid fa-plus-circle';
                            } elseif (str_contains($actionLower, 'سڕینەوە')) {
                                $badgeClass = 'bg-rose-500/15 text-rose-400 border-rose-500/30';
                                $icon = 'fa-solid fa-trash-can';
                            } elseif (str_contains($actionLower, 'دەستکاری') || str_contains($actionLower, 'گەڕاندنەوە')) {
                                $badgeClass = 'bg-amber-500/15 text-amber-400 border-amber-500/30';
                                $icon = 'fa-solid fa-pen-to-square';
                            } elseif (str_contains($actionLower, 'پارە') || str_contains($actionLower, 'وەسڵ') || str_contains($actionLower, 'دەفتەر')) {
                                $badgeClass = 'bg-teal-500/15 text-teal-300 border-teal-500/30';
                                $icon = 'fa-solid fa-receipt';
                            } elseif (str_contains($actionLower, 'چوونەژوورەوە') || str_contains($actionLower, 'چوونەنەژوورەوە')) {
                                $badgeClass = 'bg-indigo-500/15 text-indigo-300 border-indigo-500/30';
                                $icon = 'fa-solid fa-right-to-bracket';
                            } elseif (str_contains($actionLower, 'چوونەدەرەوە')) {
                                $badgeClass = 'bg-slate-500/15 text-slate-400 border-slate-500/30';
                                $icon = 'fa-solid fa-right-from-bracket';
                            } elseif (str_contains($actionLower, 'چاپ')) {
                                $badgeClass = 'bg-purple-500/15 text-purple-300 border-purple-500/30';
                                $icon = 'fa-solid fa-print';
                            }
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-3.5 text-center font-mono text-[11px] text-slate-500">
                                {{ $log->id }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 text-xs font-bold">
                                        <i class="fa-solid fa-user text-[11px]"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $log->user_name ?? 'سیستەم' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border flex items-center gap-1.5 w-fit {{ $badgeClass }}">
                                    <i class="{{ $icon }}"></i>
                                    <span>{{ $log->action }}</span>
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-300 max-w-md">
                                <p class="leading-relaxed">{{ $log->details ?? '—' }}</p>
                            </td>
                            <td class="p-3.5 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                @if($log->ip_address)
                                    <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800">
                                        {{ $log->ip_address }}
                                    </span>
                                @else
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-400 whitespace-nowrap font-mono text-[11px]" dir="ltr">
                                <div>{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                                <div class="text-[10px] text-slate-500" dir="rtl">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-500">
                                <i class="fa-solid fa-clock-rotate-left text-4xl text-slate-700 mb-3 block"></i>
                                هیچ تۆمارێکی چالاکی بەپێی ئەم فلتەرانە نەدۆزرایەوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
