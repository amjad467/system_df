@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    syncing: false,
    syncMessage: '',
    async runSync() {
        this.syncing = true;
        this.syncMessage = 'پشکنین دەکرێت...';
        try {
            let res = await fetch('{{ route('booklet_notices.sync') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            let data = await res.json();
            if (data.success) {
                this.syncMessage = data.message;
                setTimeout(() => window.location.reload(), 1200);
            }
        } catch(e) {
            this.syncMessage = 'هەڵەیەک ڕوویدا لە کاتی نوێکردنەوە!';
        } finally {
            this.syncing = false;
        }
    }
}">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-sky-600 dark:text-sky-400"></i>
                <span>ئامار و بەدواداچوونی وەجبەکانی دەفتەرە بەسەرچووەکان</span>
            </h2>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                چاودێری هاتن و وەڵامدانەوەی هاووڵاتیان دوای ناردنی نامەی ئاگادارکردنەوە لە ڕێگەی واتسئەپ و ڤایبەر.
            </p>
        </div>
        
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" @click="runSync()" :disabled="syncing"
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                <i class="fa-solid fa-rotate" :class="syncing ? 'fa-spin' : ''"></i>
                <span x-text="syncing ? syncMessage : 'نوێکردنەوەی ئاماری هاتن (Sync)'"></span>
            </button>
            <a href="{{ route('transactions.expired_booklets') }}" 
               class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5">
                <i class="fa-solid fa-calendar-xmark text-rose-500"></i>
                <span>لیستی دەفتەرە بەسەرچووەکان</span>
            </a>
        </div>
    </div>

    <!-- Quick KPIs Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Sent -->
        <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 block mb-1">کۆی نێردراوەکان</span>
                    <span class="text-3xl font-black font-mono text-slate-900 dark:text-white">{{ number_format($totalNotices) }}</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
            </div>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block">کۆی ئەو دەفتەرانەی نامەیان بۆ ڕۆشتووە</span>
        </div>

        <!-- 2. Total Attended -->
        <div class="bg-white dark:bg-slate-900/90 border border-emerald-200 dark:border-emerald-500/30 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 block mb-1">سەردانیان کردووە (هاتووە)</span>
                    <span class="text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($totalAttended) }}</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <span class="text-[11px] text-emerald-700 dark:text-emerald-400/80 mt-2 block font-semibold">تازەکراوەتەوە یان پووچەڵکراوەتەوە</span>
        </div>

        <!-- 3. Total Unattended -->
        <div class="bg-white dark:bg-slate-900/90 border border-rose-200 dark:border-rose-500/30 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-rose-700 dark:text-rose-400 block mb-1">سەردانیان نەکردووە (نەهاتووە)</span>
                    <span class="text-3xl font-black font-mono text-rose-600 dark:text-rose-400">{{ number_format($totalUnattended) }}</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <span class="text-[11px] text-rose-700 dark:text-rose-400/80 mt-2 block font-semibold">چاوەڕوانن / سەرپێچیکار</span>
        </div>

        <!-- 4. Attendance Rate -->
        <div class="bg-white dark:bg-slate-900/90 border border-indigo-200 dark:border-indigo-500/30 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-indigo-700 dark:text-indigo-400 block mb-1">ڕێژەی سەرکەوتنی هاتن</span>
                    <span class="text-3xl font-black font-mono text-indigo-600 dark:text-indigo-400">%{{ $attendanceRate }}</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-percent"></i>
                </div>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 mt-2.5 overflow-hidden">
                <div class="bg-indigo-600 dark:bg-indigo-500 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $attendanceRate) }}%;"></div>
            </div>
        </div>
    </div>

    <!-- Batches List Section -->
    <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-sky-600 dark:text-sky-400"></i>
                <h3 class="text-sm font-black text-slate-900 dark:text-white">مێژووی وەجبەکانی ئاگادارکردنەوە (Batches)</h3>
            </div>
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                کۆی وەجبەکان: {{ $batches->total() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-800 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-400 text-[11px] font-black uppercase border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-3.5">کۆدی وەجبە & ناو</th>
                        <th class="p-3.5">بەرواری ناردن</th>
                        <th class="p-3.5">کەناڵ</th>
                        <th class="p-3.5 text-center">ژمارەی نێردراو</th>
                        <th class="p-3.5 text-center">هاتووە 🟢</th>
                        <th class="p-3.5 text-center">نەهاتووە 🔴</th>
                        <th class="p-3.5 text-center">ڕێژەی سەرکەوتن</th>
                        <th class="p-3.5 text-center">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                    @forelse($batches as $b)
                        @php
                            $rate = $b->total_count > 0 ? round(($b->attended_count / $b->total_count) * 100, 1) : 0;
                            $isSelected = ($selectedBatchCode === $b->batch_code);
                        @endphp
                        <tr class="transition {{ $isSelected ? 'bg-sky-50 dark:bg-sky-950/40 border-r-4 border-sky-500' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                            <td class="p-3.5">
                                <div class="font-black text-slate-900 dark:text-white">{{ $b->batch_name ?: $b->batch_code }}</div>
                                <div class="font-mono text-[10px] text-slate-500">{{ $b->batch_code }}</div>
                            </td>
                            <td class="p-3.5 font-mono text-slate-600 dark:text-slate-400">
                                {{ $b->sent_at ? \Carbon\Carbon::parse($b->sent_at)->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td class="p-3.5">
                                @if($b->channel === 'whatsapp')
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-500/40 inline-flex items-center gap-1">
                                        <i class="fa-brands fa-whatsapp"></i> واتسئەپ
                                    </span>
                                @elseif($b->channel === 'viber')
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-purple-100 dark:bg-purple-500/20 text-purple-800 dark:text-purple-300 border border-purple-300 dark:border-purple-500/40 inline-flex items-center gap-1">
                                        <i class="fa-brands fa-viber"></i> ڤایبەر
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">SMS</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center font-mono font-black text-slate-900 dark:text-white">
                                {{ $b->total_count }}
                            </td>
                            <td class="p-3.5 text-center font-mono font-black text-emerald-700 dark:text-emerald-400">
                                {{ $b->attended_count }}
                            </td>
                            <td class="p-3.5 text-center font-mono font-black text-rose-700 dark:text-rose-400">
                                {{ $b->unattended_count }}
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $rate }}%;"></div>
                                    </div>
                                    <span class="font-mono text-xs font-bold text-slate-900 dark:text-white">%{{ $rate }}</span>
                                </div>
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <a href="{{ route('booklet_notices.index', ['batch' => $b->batch_code]) }}" 
                                       class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 {{ $isSelected ? 'bg-sky-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800 hover:bg-sky-600 text-slate-700 dark:text-sky-400 hover:text-white border border-slate-300 dark:border-slate-700' }}"
                                       title="بینینی هاووڵاتیانی ئەم وەجبەیە">
                                        <i class="fa-solid fa-list-check"></i>
                                        <span>ناوەکان</span>
                                    </a>
                                    <a href="{{ route('booklet_notices.print_no_shows', $b->batch_code) }}" target="_blank"
                                       class="px-2.5 py-1.5 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-600 text-rose-700 dark:text-rose-300 hover:text-white rounded-xl text-xs font-bold border border-rose-300 dark:border-rose-500/40 transition flex items-center gap-1"
                                       title="چاپی فەرمی لیستی ئەوانەی نەهاتوون">
                                        <i class="fa-solid fa-print"></i>
                                        <span>چاپی سەرپێچیکاران</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-500">
                                هێشتا هیچ وەجبەیەکی ئاگادارکردنەوە تۆمار نەکراوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60">
                {{ $batches->links() }}
            </div>
        @endif
    </div>

    <!-- Selected Batch Details Section -->
    @if($selectedBatchCode && $batchNotices)
        <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden mt-6 animate-in fade-in duration-300">
            <!-- Header of Selected Batch -->
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-50 dark:bg-slate-950/60">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 dark:bg-sky-500/20 text-sky-800 dark:text-sky-300 border border-sky-300 dark:border-sky-500/40">
                            {{ $selectedBatchSummary['batch_code'] }}
                        </span>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">
                            {{ $selectedBatchSummary['batch_name'] ?: 'وردەکاری وەجبە' }}
                        </h3>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-600 dark:text-slate-400 mt-1">
                        <span>کۆی گشتی: <strong>{{ $selectedBatchSummary['total_count'] }}</strong></span>
                        <span>•</span>
                        <span class="text-emerald-700 dark:text-emerald-400 font-bold">هاتووە: {{ $selectedBatchSummary['attended_count'] }}</span>
                        <span>•</span>
                        <span class="text-rose-700 dark:text-rose-400 font-bold">نەهاتووە: {{ $selectedBatchSummary['unattended_count'] }}</span>
                        <span>•</span>
                        <span>ڕێژەی سەرکەوتن: <strong>%{{ $selectedBatchSummary['attendance_rate'] }}</strong></span>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Status Filter Tabs -->
                    <div class="flex items-center bg-slate-200/80 dark:bg-slate-800 rounded-xl p-1 gap-1 text-xs">
                        <a href="{{ route('booklet_notices.index', ['batch' => $selectedBatchCode]) }}" 
                           class="px-2.5 py-1 rounded-lg font-bold transition {{ !request('filter') ? 'bg-sky-600 text-white shadow' : 'text-slate-700 dark:text-slate-300 hover:text-white' }}">
                            هەمووی ({{ $selectedBatchSummary['total_count'] }})
                        </a>
                        <a href="{{ route('booklet_notices.index', ['batch' => $selectedBatchCode, 'filter' => 'attended']) }}" 
                           class="px-2.5 py-1 rounded-lg font-bold transition {{ request('filter') === 'attended' ? 'bg-emerald-600 text-white shadow' : 'text-slate-700 dark:text-slate-300 hover:text-white' }}">
                            هاتووە 🟢 ({{ $selectedBatchSummary['attended_count'] }})
                        </a>
                        <a href="{{ route('booklet_notices.index', ['batch' => $selectedBatchCode, 'filter' => 'unattended']) }}" 
                           class="px-2.5 py-1 rounded-lg font-bold transition {{ request('filter') === 'unattended' ? 'bg-rose-600 text-white shadow' : 'text-slate-700 dark:text-slate-300 hover:text-white' }}">
                            نەهاتووە 🔴 ({{ $selectedBatchSummary['unattended_count'] }})
                        </a>
                    </div>

                    <a href="{{ route('booklet_notices.print_no_shows', $selectedBatchCode) }}" target="_blank"
                       class="px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                        <i class="fa-solid fa-print"></i>
                        <span>چاپی فەرمی سەرپێچیکاران</span>
                    </a>
                </div>
            </div>

            <!-- Table of Citizens in this Batch -->
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs text-slate-800 dark:text-slate-300">
                    <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-400 text-[11px] font-black uppercase border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="p-3.5 w-12 text-center">#</th>
                            <th class="p-3.5">هاووڵاتی & پەیوەندی</th>
                            <th class="p-3.5">تابلۆ & ئۆتۆمبێل</th>
                            <th class="p-3.5">دەفتەر & بەسەرچوون</th>
                            <th class="p-3.5 text-center">دۆخی وەڵامدانەوە</th>
                            <th class="p-3.5">وردەکاری هاتن</th>
                            <th class="p-3.5 text-center">پەیوەندی دووبارە</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                        @forelse($batchNotices as $idx => $n)
                            @php
                                $isAttended = ($n->status === 'attended' || $n->attended_at);
                                $daysOverdue = $n->end_date ? abs(now()->startOfDay()->diffInDays($n->end_date->startOfDay(), false)) : 0;
                            @endphp
                            <tr class="transition {{ $isAttended ? 'bg-emerald-500/5 hover:bg-emerald-500/10' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                                <td class="p-3.5 text-center font-mono font-bold text-slate-600 dark:text-slate-400">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="p-3.5">
                                    <div class="font-black text-slate-900 dark:text-white">{{ $n->visitor_name }}</div>
                                    @if($n->phone_number)
                                        <div class="text-[11px] text-emerald-700 dark:text-emerald-400 font-mono flex items-center gap-1 mt-0.5" dir="ltr">
                                            <i class="fa-solid fa-phone text-[9px]"></i>
                                            <span>{{ $n->phone_number }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    <span class="font-mono font-black text-slate-900 dark:text-white bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-300 dark:border-slate-700">
                                        {{ $n->plate_number }}
                                    </span>
                                    <div class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5">
                                        {{ $n->transaction?->carMake?->name_kurdish ?? 'ئۆتۆمبێل' }}
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-mono font-black text-sky-700 dark:text-sky-400">
                                        {{ $n->booklet_number ?: '-' }}
                                    </div>
                                    <div class="text-[10px] text-rose-700 dark:text-rose-400 font-mono mt-0.5">
                                        بەسەرچوو: {{ $n->end_date ? $n->end_date->format('Y-m-d') : '-' }} ({{ $daysOverdue }} ڕۆژ)
                                    </div>
                                </td>
                                <td class="p-3.5 text-center">
                                    @if($isAttended)
                                        <span class="px-3 py-1 rounded-full text-[11px] font-black bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-500/40 inline-flex items-center gap-1.5 shadow-sm">
                                            <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                                            <span>هاتووە و مامەڵەی کردووە</span>
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[11px] font-black bg-rose-100 dark:bg-rose-500/20 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-500/40 inline-flex items-center gap-1.5 shadow-sm">
                                            <i class="fa-solid fa-clock text-rose-600 dark:text-rose-400"></i>
                                            <span>سەردانی نەکردووە (چاوەڕوانە)</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-xs">
                                    @if($isAttended)
                                        <div class="text-emerald-800 dark:text-emerald-300 font-bold">
                                            جۆر: {{ $n->attended_type ?: 'مامەڵەی نوێ' }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono">
                                            بەروار: {{ $n->attended_at ? $n->attended_at->format('Y-m-d') : '-' }}
                                        </div>
                                        @if($n->attended_transaction_id)
                                            <a href="{{ route('transactions.show', $n->attended_transaction_id) }}" class="text-[10px] text-sky-600 dark:text-sky-400 underline font-mono font-bold block mt-0.5">
                                                بینینی مامەڵە نوێیەکە ←
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($n->getWhatsAppUrl())
                                            <a href="{{ $n->getWhatsAppUrl() }}" target="_blank"
                                               class="w-8 h-8 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white flex items-center justify-center shadow transition"
                                               title="نامەی واتسئەپ">
                                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                            </a>
                                        @endif
                                        @if($n->getViberUrl())
                                            <a href="{{ $n->getViberUrl() }}" target="_blank"
                                               class="w-8 h-8 rounded-xl bg-purple-600 hover:bg-purple-500 text-white flex items-center justify-center shadow transition"
                                               title="نامەی ڤایبەر">
                                                <i class="fa-brands fa-viber text-sm"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">
                                    هیچ هاووڵاتییەک نەدۆزرایەوە بەپێی ئەم فلتەرە.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($batchNotices->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60">
                    {{ $batchNotices->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
