@extends('layouts.app')

@section('title', 'ئاسایش و مۆنیتەرینگ • بەڕێوەبەرایەتی گومرگی سلێمانی')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'incidents', testStatus: null, testingTelegram: false }">

    <!-- Top Executive Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-red-950/40 to-slate-900 border border-red-500/30 p-6 shadow-2xl">
        <div class="absolute -top-16 -right-16 w-48 h-48 bg-red-600/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-indigo-600/10 rounded-full blur-3xl"></div>

        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-rose-700 flex items-center justify-center text-white text-2xl shadow-lg shadow-red-500/20 border border-red-400/30">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-white">ناوەندی ئاسایش و مۆنیتەرینگی سیستەم</h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            قەڵغانی چالاک • Active Shield
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        چاودێری ڕاستەوخۆی پەلامارەکان، تێپەڕەوشەی هەڵە، هێرشی دەرەکی، جوڵەی نامۆی ناوخۆ، و ئاگادارکردنەوەی تەلەگرام
                    </p>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex items-center gap-2">
                @if($stats['unresolved'] > 0)
                    <form action="{{ route('security.resolve_all') }}" method="POST" onsubmit="return confirm('ئایا دڵنیایت لە چارەسەرکردنی سەرجەم ئاگادارییەکان؟');">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition flex items-center gap-1.5 border border-slate-700">
                            <i class="fa-solid fa-check-double text-emerald-400"></i>
                            <span>چارەسەرکردنی هەموو ({{ $stats['unresolved'] }})</span>
                        </button>
                    </form>
                @endif
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-black transition flex items-center gap-1.5 shadow-md shadow-red-600/30">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>گەڕانەوە بۆ داشبۆرد</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 4 High-Impact KPI Status Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Unresolved Incidents -->
        <div class="bg-slate-900/90 rounded-2xl p-5 border {{ $stats['unresolved'] > 0 ? 'border-red-500/50 shadow-lg shadow-red-500/10' : 'border-slate-800' }} relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400">ئاگاداریی نوێ</span>
                <div class="w-8 h-8 rounded-xl bg-red-500/15 text-red-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-bell"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono {{ $stats['unresolved'] > 0 ? 'text-red-400' : 'text-slate-200' }}">
                    {{ number_format($stats['unresolved']) }}
                </span>
                <span class="text-[11px] text-slate-400">ڕووداوی هەڵپەسێردراو</span>
            </div>
        </div>

        <!-- 2. Blocked Attacks Today -->
        <div class="bg-slate-900/90 rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400">هێرشی ڕاگیراو (ئەمڕۆ)</span>
                <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-hand"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-amber-400">
                    {{ number_format($stats['today_attacks']) }}
                </span>
                <span class="text-[11px] text-slate-400">پەلاماری بلۆککراو</span>
            </div>
        </div>

        <!-- 3. Blocked IPs -->
        <div class="bg-slate-900/90 rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400">ئایپییە بلۆککراوەکان</span>
                <div class="w-8 h-8 rounded-xl bg-purple-500/15 text-purple-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-ban"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-purple-400">
                    {{ number_format($stats['blocked_ips_count']) }}
                </span>
                <span class="text-[11px] text-slate-400">لە لیستی ڕەشدا</span>
            </div>
        </div>

        <!-- 4. Failed Logins Today -->
        <div class="bg-slate-900/90 rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400">پاسوۆردی هەڵە (ئەمڕۆ)</span>
                <div class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-key"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-sky-400">
                    {{ number_format($stats['today_failed_logins']) }}
                </span>
                <span class="text-[11px] text-slate-400">هەوڵی نەگونجاو</span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
        <button @click="activeTab = 'incidents'" type="button"
                :class="activeTab === 'incidents' ? 'bg-red-600 text-white shadow-md shadow-red-600/20' : 'bg-slate-900 text-slate-400 hover:text-white'"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-list-check"></i>
            <span>تۆماری ڕووداوە ئەمنییەکان</span>
            @if($stats['unresolved'] > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-red-400 text-slate-950 font-black">
                    {{ $stats['unresolved'] }}
                </span>
            @endif
        </button>

        <button @click="activeTab = 'blocked_ips'" type="button"
                :class="activeTab === 'blocked_ips' ? 'bg-purple-600 text-white shadow-md shadow-purple-600/20' : 'bg-slate-900 text-slate-400 hover:text-white'"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-ban"></i>
            <span>لیستی ڕەشی ئایپییەکان ({{ $stats['blocked_ips_count'] }})</span>
        </button>

        <button @click="activeTab = 'settings'" type="button"
                :class="activeTab === 'settings' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-slate-900 text-slate-400 hover:text-white'"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2">
            <i class="fa-brands fa-telegram"></i>
            <span>ڕێکخستنی تەلەگرام و بەرگری</span>
        </button>

        <button @click="activeTab = 'health'" type="button"
                :class="activeTab === 'health' ? 'bg-teal-600 text-white shadow-md shadow-teal-600/20' : 'bg-slate-900 text-slate-400 hover:text-white'"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-heart-pulse"></i>
            <span>تەندروستی سێرڤەر</span>
        </button>
    </div>

    <!-- TAB 1: Live Incidents Feed -->
    <div x-show="activeTab === 'incidents'" class="space-y-4">
        <!-- Search & Filters -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
            <form method="GET" action="{{ route('security.hub') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-5 relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="گەڕان بەپێی ناونیشانی ئایپی، ناوی بەکارهێنەر، یان وشە..." 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-red-500">
                </div>

                <div class="sm:col-span-3">
                    <select name="severity" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-red-500">
                        <option value="">هەموو ئاستەکانی مەترسی</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>زۆر مەترسیدار (Critical)</option>
                        <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>بەرز (High)</option>
                        <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>مامناوەند (Medium)</option>
                        <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>ئاگاداری (Low)</option>
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <select name="type" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-red-500">
                        <option value="">هەموو جۆرەکانی هێرش</option>
                        <option value="brute_force" {{ request('type') == 'brute_force' ? 'selected' : '' }}>پەلاماری پاسوۆرد (Brute-Force)</option>
                        <option value="sqli_attempt" {{ request('type') == 'sqli_attempt' ? 'selected' : '' }}>دەرزی لێدانی داتابەیس (SQLi)</option>
                        <option value="off_hours_access" {{ request('type') == 'off_hours_access' ? 'selected' : '' }}>کاتی نائاسایی (شەوانە/پشوو)</option>
                        <option value="new_device" {{ request('type') == 'new_device' ? 'selected' : '' }}>ئامێر یان ئایپی نوێ</option>
                        <option value="bot_trap" {{ request('type') == 'bot_trap' ? 'selected' : '' }}>تەڵەی بۆت و هاککەر</option>
                        <option value="manual_block" {{ request('type') == 'manual_block' ? 'selected' : '' }}>بلۆککردنی دەستی</option>
                    </select>
                </div>

                <div class="sm:col-span-1 flex gap-1">
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center" title="فلتەر">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    @if(request()->hasAny(['search', 'severity', 'type']))
                        <a href="{{ route('security.hub') }}" class="p-2 bg-slate-800 hover:bg-rose-900/40 text-slate-400 hover:text-rose-300 rounded-xl text-xs flex items-center justify-center transition" title="پاککردنەوە">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Incidents List -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-950/80 text-slate-400 text-[11px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="p-3.5">ئاست</th>
                            <th class="p-3.5">جۆری ڕووداو</th>
                            <th class="p-3.5">ناونیشانی ئایپی</th>
                            <th class="p-3.5">بەکارهێنەر</th>
                            <th class="p-3.5">وردەکاری و ڕوونکردنەوە</th>
                            <th class="p-3.5">کات</th>
                            <th class="p-3.5 text-center">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        @forelse($incidents as $inc)
                            <tr class="hover:bg-slate-800/40 transition {{ !$inc->is_resolved ? 'bg-red-950/10' : '' }}">
                                <td class="p-3.5 whitespace-nowrap">
                                    {!! $inc->severity_badge !!}
                                </td>
                                <td class="p-3.5 font-bold text-white whitespace-nowrap">
                                    {{ $inc->type_name_kurdish }}
                                </td>
                                <td class="p-3.5 font-mono text-slate-300 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-[11px]">
                                        {{ $inc->ip_address }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-300 whitespace-nowrap">
                                    {{ $inc->user_name ?? '—' }}
                                </td>
                                <td class="p-3.5 text-slate-300 max-w-md">
                                    <p class="leading-relaxed">{{ $inc->details }}</p>
                                    @if($inc->payload)
                                        <div class="mt-1 font-mono text-[10px] text-amber-400/90 bg-slate-950 p-1.5 rounded border border-slate-800/80 overflow-x-auto" dir="ltr">
                                            {{ Str::limit($inc->payload, 120) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="p-3.5 text-slate-400 font-mono text-[11px] whitespace-nowrap">
                                    {{ $inc->created_at->diffForHumans() }}
                                    <div class="text-[10px] text-slate-500">{{ $inc->created_at->format('H:i:s - Y/m/d') }}</div>
                                </td>
                                <td class="p-3.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if(!$inc->is_resolved)
                                            <form action="{{ route('security.resolve', $inc) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold transition flex items-center gap-1">
                                                    <i class="fa-solid fa-check"></i>
                                                    <span>چارەسەرکرا</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[11px] text-slate-500 flex items-center gap-1">
                                                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                                <span>{{ $inc->resolved_by ?? 'چارەسەرکراوە' }}</span>
                                            </span>
                                        @endif

                                        <form action="{{ route('security.block_ip') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="ip_address" value="{{ $inc->ip_address }}">
                                            <input type="hidden" name="reason" value="بلۆککردن لەسەر ڕووداوی: {{ $inc->type_name_kurdish }}">
                                            <input type="hidden" name="duration_hours" value="24">
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-500/30 text-[11px] font-bold transition" title="بلۆککردنی ئەم ئایپییە بۆ ٢٤ کاتژمێر">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center text-slate-500">
                                    <i class="fa-solid fa-shield-check text-4xl text-emerald-500/30 mb-3 block"></i>
                                    هیچ ڕووداوێکی مەترسیدار یان پەلامارێک تۆمار نەکراوە. سیستەمەکە پارێزراوە.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($incidents->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $incidents->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: Blocked IPs Manager -->
    <div x-show="activeTab === 'blocked_ips'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Manual Block Form -->
        <div class="lg:col-span-4 bg-slate-900/90 border border-slate-800 rounded-3xl p-6 h-fit shadow-xl">
            <h3 class="text-sm font-black text-white mb-2 flex items-center gap-2">
                <i class="fa-solid fa-ban text-red-400"></i>
                <span>بلۆککردنی دەستی ناونیشانی ئایپی</span>
            </h3>
            <p class="text-xs text-slate-400 mb-4">
                دەتوانیت هەر ئایپییەکی گوماناوی لێرەوە ڕابگریت بۆ ئەوەی نەتوانێت هیچ پەڕەیەکی سیستەمەکە بکاتەوە.
            </p>

            <form action="{{ route('security.block_ip') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">ناونیشانی ئایپی (IP Address):</label>
                    <input type="text" name="ip_address" required placeholder="نموونە: 185.12.34.56" 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs font-mono text-white placeholder-slate-600 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">هۆکاری بلۆککردن:</label>
                    <input type="text" name="reason" required placeholder="نموونە: پەلاماری هەڵەی پاسوۆرد، گومانی هێرش" 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">ماوەی بلۆککردن:</label>
                    <select name="duration_hours" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-red-500">
                        <option value="1">١ کاتژمێر</option>
                        <option value="6">٦ کاتژمێر</option>
                        <option value="24" selected>٢٤ کاتژمێر (١ ڕۆژ)</option>
                        <option value="168">٧ ڕۆژ (١ هەفتە)</option>
                        <option value="">هەمیشەیی (Permanent)</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-black transition flex items-center justify-center gap-2 shadow-lg shadow-red-600/20">
                    <i class="fa-solid fa-lock"></i>
                    <span>بلۆککردنی ئەم ئایپییە</span>
                </button>
            </form>
        </div>

        <!-- Blocked IPs Table -->
        <div class="lg:col-span-8 bg-slate-900/90 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
            <div class="p-4 bg-slate-950/60 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-xs font-black text-white flex items-center gap-2">
                    <i class="fa-solid fa-shield-virus text-purple-400"></i>
                    <span>لیستی ڕەشی چالاک (ئایپییە ڕاگیراوەکان)</span>
                </h3>
                <span class="text-[11px] text-slate-400 font-mono">{{ $blockedIps->count() }} ئایپی بلۆککراوە</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-950/40 text-slate-400 text-[11px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="p-3">ئایپی</th>
                            <th class="p-3">هۆکار</th>
                            <th class="p-3">بلۆککراوە لە لایەن</th>
                            <th class="p-3">بەسەرچوونی بلۆک</th>
                            <th class="p-3 text-center">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($blockedIps as $bIp)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-3 font-mono font-bold text-red-400">
                                    {{ $bIp->ip_address }}
                                </td>
                                <td class="p-3 text-slate-300">
                                    {{ $bIp->reason }}
                                </td>
                                <td class="p-3 text-slate-400 text-[11px]">
                                    {{ $bIp->blocked_by ?? 'سیستەم' }}
                                </td>
                                <td class="p-3 text-slate-300 text-[11px] font-mono">
                                    @if($bIp->blocked_until)
                                        {{ $bIp->blocked_until->format('Y-m-d H:i') }}
                                        <span class="text-[10px] text-amber-400">({{ $bIp->blocked_until->diffForHumans() }})</span>
                                    @else
                                        <span class="text-rose-400 font-bold">هەمیشەیی</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <form action="{{ route('security.unblock_ip', $bIp) }}" method="POST" onsubmit="return confirm('ئایا دڵنیایت لە لابردنی بلۆکی ئەم ئایپییە؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold transition flex items-center gap-1 mx-auto">
                                            <i class="fa-solid fa-lock-open"></i>
                                            <span>لابردنی بلۆک</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">
                                    هیچ ئایپییەک لە لیستی ڕەشدا نییە.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: Telegram & Defense Settings -->
    <div x-show="activeTab === 'settings'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Settings Form -->
        <div class="lg:col-span-7 bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl">
            <h3 class="text-sm font-black text-white mb-2 flex items-center gap-2">
                <i class="fa-brands fa-telegram text-sky-400 text-base"></i>
                <span>ڕێکخستنی ئاگادارییەکانی تەلەگرام بۆ مۆبایلی ئەدمین</span>
            </h3>
            <p class="text-xs text-slate-400 mb-6">
                کاتێک ئەم بەشە چالاک دەکەیت، ڕاستەوخۆ بە کورتەنامە لە تەلەگرامی مۆبایلت هەموو هێرش و هەوڵێکی گوماناوی پێتدەگات.
            </p>

            <form action="{{ route('security.settings.update') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Toggle Telegram Alerts -->
                <div class="flex items-center justify-between p-3.5 bg-slate-950 rounded-2xl border border-slate-800">
                    <div>
                        <div class="text-xs font-bold text-white">چالاککردنی ئاگاداری تەلەگرام</div>
                        <div class="text-[11px] text-slate-400">ناردنی پەیامی بەپەلە لە کاتی ڕووداوە ئەمنییەکان</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="telegram_alerts_enabled" value="1" {{ $settings['telegram_alerts_enabled'] == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">کلیلی بۆتی تەلەگرام (Bot Token):</label>
                    <input type="text" id="tg_token" name="telegram_bot_token" value="{{ $settings['telegram_bot_token'] }}" 
                           placeholder="نموونە: 7123456789:AAHk..." 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs font-mono text-white placeholder-slate-600 focus:outline-none focus:border-sky-500" dir="ltr">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">ناسنامەی چاتی تەلەگرام (Chat ID):</label>
                    <input type="text" id="tg_chat" name="telegram_chat_id" value="{{ $settings['telegram_chat_id'] }}" 
                           placeholder="نموونە: 123456789" 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs font-mono text-white placeholder-slate-600 focus:outline-none focus:border-sky-500" dir="ltr">
                </div>

                <!-- Test Connection Button -->
                <div class="pt-2">
                    <button type="button" 
                            @click="
                                testingTelegram = true; testStatus = null;
                                fetch('{{ route('security.test_telegram') }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: JSON.stringify({ telegram_bot_token: document.getElementById('tg_token').value, telegram_chat_id: document.getElementById('tg_chat').value })
                                })
                                .then(r => r.json())
                                .then(data => { testingTelegram = false; testStatus = data; })
                                .catch(e => { testingTelegram = false; testStatus = { success: false, message: 'کێشە لە پەیوەندی بە سێرڤەر: ' + e }; });
                            "
                            class="px-4 py-2 rounded-xl bg-sky-600/20 hover:bg-sky-600/30 text-sky-300 border border-sky-500/30 text-xs font-bold transition flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane" :class="testingTelegram ? 'animate-bounce' : ''"></i>
                        <span>تاقیکردنەوەی ناردنی نامە بۆ تەلەگرامەکەم</span>
                    </button>

                    <!-- AJAX Feedback Alert -->
                    <div x-show="testStatus" class="mt-2 text-xs p-3 rounded-xl border" 
                         :class="testStatus?.success ? 'bg-emerald-950/40 text-emerald-300 border-emerald-500/30' : 'bg-red-950/40 text-red-300 border-red-500/30'" x-cloak>
                        <span x-text="testStatus?.message"></span>
                    </div>
                </div>

                <div class="border-t border-slate-800 my-4"></div>

                <h4 class="text-xs font-black text-white mb-3">قەڵغان و یاساکانی بەرگری:</h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">سنووری هەوڵی هەڵەی پاسوۆرد:</label>
                        <input type="number" name="brute_force_threshold" value="{{ $settings['brute_force_threshold'] }}" min="3" max="20"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">ماوەی قوفڵکردن (بە خولەک):</label>
                        <input type="number" name="brute_force_lockout_minutes" value="{{ $settings['brute_force_lockout_minutes'] }}" min="5" max="1440"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>
                </div>

                <!-- Toggle Off-hours Alert -->
                <div class="flex items-center justify-between p-3.5 bg-slate-950 rounded-2xl border border-slate-800">
                    <div>
                        <div class="text-xs font-bold text-white">ئاگاداری لە کاتی نائاسایی (Off-Hours)</div>
                        <div class="text-[11px] text-slate-400">ئاگاداری کاتێک کارمەند شەوانە یان ڕۆژی هەینی دەچێتە ژوورەوە</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="off_hours_alerts_enabled" value="1" {{ $settings['off_hours_alerts_enabled'] == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
                    </label>
                </div>

                <!-- Toggle SQL Injection Defense -->
                <div class="flex items-center justify-between p-3.5 bg-slate-950 rounded-2xl border border-slate-800">
                    <div>
                        <div class="text-xs font-bold text-white">بەرگری لە SQL Injection و XSS</div>
                        <div class="text-[11px] text-slate-400">پشکنینی داواکارییە زیانبەخشەکان و بلۆککردنی دەستبەجێ</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sql_injection_defense_enabled" value="1" {{ $settings['sql_injection_defense_enabled'] == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-black transition shadow-lg shadow-indigo-600/20">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> پاشەکەوتکردنی ڕێکخستنەکان
                </button>
            </form>
        </div>

        <!-- How to get Telegram Bot Info -->
        <div class="lg:col-span-5 bg-slate-900/90 border border-slate-800 rounded-3xl p-6 h-fit shadow-xl space-y-4">
            <h3 class="text-sm font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-circle-question text-sky-400"></i>
                <span>فێرکاری: چۆن بە ١ خولەک بۆتی تەلەگرام دابنێیت؟</span>
            </h3>

            <div class="text-xs text-slate-300 space-y-3 leading-relaxed">
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800/80">
                    <div class="font-bold text-white mb-1">١. دەستکەوتنی Bot Token:</div>
                    لە تەلەگرام بچۆ بۆ بۆتی فەرمی: <strong class="text-sky-400" dir="ltr">@BotFather</strong><br>
                    فەرمانی <code class="text-amber-400" dir="ltr">/newbot</code> بنووسە و ناوێکی پێبدە. لە کۆتاییدا دەقێکی درێژت دەداتێ (Token)، لە بۆکسی یەکەم دایبنێ.
                </div>

                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800/80">
                    <div class="font-bold text-white mb-1">٢. دەستکەوتنی Chat ID:</div>
                    لە تەلەگرام بچۆ بۆ بۆتی: <strong class="text-sky-400" dir="ltr">@userinfobot</strong><br>
                    کرتە لەسەر Start بکە؛ ژمارەی ناسنامەکەت (Id) پیشان دەدات (نموونە: <code class="text-amber-400">123456789</code>). لە بۆکسی دووەم دایبنێ.
                </div>

                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800/80">
                    <div class="font-bold text-white mb-1">٣. کرتە لە «تاقیکردنەوە» بکە:</div>
                    دوای پڕکردنەوەی هەردوو بۆکسەکە، کرتە لە دوگمەی تاقیکردنەوە بکە. دەستبەجێ نامەی سەرکەوتن بۆ مۆبایلت دێت!
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: Server Health & Monitoring -->
    <div x-show="activeTab === 'health'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold border-b border-slate-800 pb-2">
                <span>ژینگەی سێرڤەر (Environment)</span>
                <i class="fa-solid fa-server text-teal-400"></i>
            </div>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">وەشانی PHP:</span>
                    <span class="font-mono font-bold text-white">{{ $serverHealth['php_version'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">وەشانی Laravel:</span>
                    <span class="font-mono font-bold text-white">{{ $serverHealth['laravel_version'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">کاتی سێرڤەر:</span>
                    <span class="font-mono font-bold text-slate-300">{{ $serverHealth['server_time'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold border-b border-slate-800 pb-2">
                <span>بنکەدراوە (Database Health)</span>
                <i class="fa-solid fa-database text-sky-400"></i>
            </div>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">جۆری بنکەدراوە:</span>
                    <span class="font-mono font-bold text-emerald-400 uppercase">{{ $serverHealth['db_driver'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">قەبارەی پەڕگەی داتابەیس:</span>
                    <span class="font-mono font-bold text-white">{{ $serverHealth['db_size'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">کۆی تۆماری مامەڵەکان:</span>
                    <span class="font-mono font-bold text-amber-400">{{ $serverHealth['transactions_count'] }} تۆمار</span>
                </div>
            </div>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold border-b border-slate-800 pb-2">
                <span>بەردەوامی و پارێزراوی (Uptime & SSL)</span>
                <i class="fa-solid fa-lock text-emerald-400"></i>
            </div>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">باری سێرڤەر:</span>
                    <span class="text-emerald-400 font-bold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        چالاکە و ناپچڕێت (Online)
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">کۆدکردنی کەناڵ:</span>
                    <span class="font-mono font-bold text-white">TLS 1.3 / HTTPS (SSL)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">بەکارهێنەرانی فەرمی:</span>
                    <span class="font-mono font-bold text-white">{{ $serverHealth['users_count'] }} فەرمانبەر</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
