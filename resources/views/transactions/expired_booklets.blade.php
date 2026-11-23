@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    selectedIds: [],
    selectAll: false,
    showBatchModal: false,
    selectingUnnotified: false,
    channel: 'whatsapp',
    batchName: 'وەجبەی ' + new Date().toISOString().slice(0, 10),
    messageTemplate: 'سڵاو بەڕێز {ناو}،\nئاگادارتان دەکەینەوە لە بەڕێوەبەرایەتی گومرگی سلێمانی کە دەفتەری گومرگی ئۆتۆمبێلەکەتان بە تابلۆی ({تابلۆ}) و ژمارەی دەفتەری ({دەفتەر}) لە بەرواری ({بەروار}) بەسەرچووە.\nتکایە بە زووترین کات سەردانی بەڕێوەبەرایەتی گومرگی سلێمانی بکەن بۆ تازەکردنەوە یان پووچەڵکردنەوەی دەفتەرەکەتان، تاکو دووچاری سزای یاسایی و دارایی نەبنەوە.\n\n— بەڕێوەبەرایەتی گومرگی سلێمانی',
    batchSending: false,
    batchResult: null,
    activeQueueIndex: 0,

    pageIds: {{ json_encode($transactions->pluck('id')) }},

    toggleSelectAll() {
        if (this.selectAll) {
            this.selectedIds = [...new Set([...this.selectedIds, ...this.pageIds])];
        } else {
            this.selectedIds = this.selectedIds.filter(id => !this.pageIds.includes(id));
        }
    },

    async selectNext100() {
        this.selectingUnnotified = true;
        try {
            let res = await fetch('{{ route('booklet_notices.select_unnotified') }}?limit=100');
            let data = await res.json();
            if (data.success && data.ids.length > 0) {
                this.selectedIds = data.ids;
                alert('سەرکەوتوو بوو! (' + data.count + ') هاووڵاتی نەنێردراو لە کۆنترین دەفتەرە بەسەرچووەکان دەستنیشانکران.');
            } else {
                alert('هیچ دەفتەرێکی بەسەرچووی نەنێردراو نەدۆزرایەوە!');
            }
        } catch(e) {
            alert('هەڵەیەک ڕوویدا لە کاتی هەڵبژاردنی نەنێردراوەکان');
        } finally {
            this.selectingUnnotified = false;
        }
    },

    openBatchModal() {
        if (this.selectedIds.length === 0) {
            alert('تکایە سەرەتا یەک یان چەند هاووڵاتییەک دیاری بکە بۆ ناردنی وەجبە.');
            return;
        }
        this.batchResult = null;
        this.activeQueueIndex = 0;
        this.showBatchModal = true;
    },

    async commitBatch() {
        this.batchSending = true;
        try {
            let res = await fetch('{{ route('booklet_notices.store_batch') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    transaction_ids: this.selectedIds,
                    channel: this.channel,
                    batch_name: this.batchName,
                    message_template: this.messageTemplate
                })
            });
            let data = await res.json();
            if (data.success) {
                this.batchResult = data;
                alert(data.message);
            } else {
                alert(data.message || 'هەڵەیەک ڕوویدا');
            }
        } catch(e) {
            alert('هەڵەیەک لە پەیوەندی بە سێرڤەر ڕوویدا');
        } finally {
            this.batchSending = false;
        }
    },

    sendQueueItem(index) {
        if (!this.batchResult || !this.batchResult.notices[index]) return;
        let item = this.batchResult.notices[index];
        let url = (this.channel === 'whatsapp' ? item.whatsapp_url : item.viber_url);
        if (url) {
            window.open(url, '_blank');
            this.activeQueueIndex = index + 1;
        } else {
            alert('ئەم هاووڵاتییە ژمارەی تەلەفۆنی دروستی نییە: ' + item.visitor_name);
        }
    }
}">

    <!-- Top Navigation Tabs (Expired Booklets vs Batch Analytics) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('transactions.expired_booklets') }}" 
               class="px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-black shadow-md shadow-rose-600/30 flex items-center gap-2">
                <i class="fa-solid fa-calendar-xmark"></i>
                <span>لیستی دەفتەرە بەسەرچووەکان</span>
            </a>
            <a href="{{ route('booklet_notices.index') }}" 
               class="px-4 py-2 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-800 transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-chart-pie text-sky-600 dark:text-sky-400"></i>
                <span>ئامار و بەدواداچوونی وەجبەکان</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-sky-100 dark:bg-sky-500/20 text-sky-800 dark:text-sky-300 font-mono font-bold">WhatsApp/Viber</span>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('transactions.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تۆمارکردنی نوێ</span>
            </a>
            <a href="{{ route('transactions.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5">
                <i class="fa-solid fa-list-check"></i>
                <span>گشت مامەڵەکان</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards (High-contrast dual theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Expired Count -->
        <a href="{{ route('transactions.expired_booklets', ['status' => 'expired', 'search' => request('search'), 'notice_status' => request('notice_status')]) }}" 
           class="p-5 rounded-2xl border transition {{ $status === 'expired' ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-500 ring-2 ring-rose-500/50 shadow-md' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-rose-400' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-rose-700 dark:text-rose-400 block mb-1">بەسەرچووەکان (دەستبەجێ پێویست بە تازەکردنەوە)</span>
                    <span class="text-3xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ number_format($expiredCount) }}</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block">بەرواری بەسەرچوونیان پێش ئەمڕۆ بووە</span>
        </a>

        <!-- Expiring Soon Count -->
        <a href="{{ route('transactions.expired_booklets', ['status' => 'expiring_soon', 'search' => request('search'), 'notice_status' => request('notice_status')]) }}" 
           class="p-5 rounded-2xl border transition {{ $status === 'expiring_soon' ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-500 ring-2 ring-amber-500/50 shadow-md' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-amber-400' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-400 block mb-1">لە ٣٠ ڕۆژی داهاتوودا بەسەردەچن</span>
                    <span class="text-3xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ number_format($expiringSoonCount) }}</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block">ئاگاداری بۆ تازەکردنەوەی پێشوەختە</span>
        </a>

        <!-- Total Count -->
        <a href="{{ route('transactions.expired_booklets', ['status' => 'all', 'search' => request('search'), 'notice_status' => request('notice_status')]) }}" 
           class="p-5 rounded-2xl border transition {{ $status === 'all' ? 'bg-sky-50 dark:bg-sky-950/40 border-sky-500 ring-2 ring-sky-500/50 shadow-md' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-sky-400' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-sky-700 dark:text-sky-400 block mb-1">کۆی گشتی بەدواداچوون</span>
                    <span class="text-3xl font-black text-sky-600 dark:text-sky-400 font-mono">{{ number_format($totalCount) }}</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
            </div>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block">بەسەرچوو + لە ٣٠ ڕۆژدا بەسەرچوو</span>
        </a>
    </div>

    <!-- Notice Campaign Filter Bar (ئاگادارکراو / نەنێردراو) -->
    <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-black text-slate-700 dark:text-slate-300 ml-1">
                <i class="fa-solid fa-paper-plane text-sky-600 dark:text-sky-400"></i> فلتەری نامەکان:
            </span>

            <a href="{{ route('transactions.expired_booklets', ['status' => $status, 'search' => request('search'), 'year' => request('year'), 'notice_status' => 'all']) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($noticeStatus ?? 'all') === 'all' ? 'bg-slate-800 text-white dark:bg-white dark:text-slate-900 shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200' }}">
                گشت دەفتەرەکان ({{ $totalCount }})
            </a>

            <!-- Unnotified Leads (Fresh) -->
            <a href="{{ route('transactions.expired_booklets', ['status' => $status, 'search' => request('search'), 'year' => request('year'), 'notice_status' => 'unnotified']) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-black transition flex items-center gap-1.5 {{ ($noticeStatus ?? '') === 'unnotified' ? 'bg-sky-600 text-white shadow' : 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-400 border border-sky-300 dark:border-sky-800 hover:bg-sky-100' }}">
                <i class="fa-solid fa-sparkles"></i>
                <span>نەنێردراوەکان / نوێ ({{ $unnotifiedCount }})</span>
            </a>

            <!-- Notified -->
            <a href="{{ route('transactions.expired_booklets', ['status' => $status, 'search' => request('search'), 'year' => request('year'), 'notice_status' => 'notified']) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($noticeStatus ?? '') === 'notified' ? 'bg-indigo-600 text-white shadow' : 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-300 dark:border-indigo-800 hover:bg-indigo-100' }}">
                ئاگادارکراوەکان ({{ $notifiedCount }})
            </a>

            <!-- Attended (Green) -->
            <a href="{{ route('transactions.expired_booklets', ['status' => $status, 'search' => request('search'), 'year' => request('year'), 'notice_status' => 'attended']) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 {{ ($noticeStatus ?? '') === 'attended' ? 'bg-emerald-600 text-white shadow' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800 hover:bg-emerald-100' }}">
                <i class="fa-solid fa-circle-check"></i>
                <span>هاتووە 🟢 ({{ $attendedNoticesCount }})</span>
            </a>

            <!-- Unattended (Red) -->
            <a href="{{ route('transactions.expired_booklets', ['status' => $status, 'search' => request('search'), 'year' => request('year'), 'notice_status' => 'unattended']) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 {{ ($noticeStatus ?? '') === 'unattended' ? 'bg-rose-600 text-white shadow' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-300 dark:border-rose-800 hover:bg-rose-100' }}">
                <i class="fa-solid fa-clock"></i>
                <span>نەهاتووە 🔴 ({{ $unattendedNoticesCount }})</span>
            </a>
        </div>

        <!-- Batch Dispatch Actions -->
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" @click="selectNext100()" :disabled="selectingUnnotified"
                    class="px-3.5 py-1.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white text-xs font-black rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                <i class="fa-solid fa-bolt" :class="selectingUnnotified ? 'animate-bounce' : ''"></i>
                <span x-text="selectingUnnotified ? 'دیاریکردن...' : '⚡ دەستنیشانکردنی ١٠٠ کەسی نوێ'"></span>
            </button>

            <button type="button" @click="openBatchModal()" 
                    class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer"
                    :class="selectedIds.length > 0 ? 'ring-2 ring-emerald-400 ring-offset-2' : ''">
                <i class="fa-brands fa-whatsapp"></i>
                <span>ناردنی وەجبە</span>
                <span class="px-1.5 py-0.2 rounded-full bg-white/20 text-white font-mono" x-text="selectedIds.length"></span>
            </button>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-2 w-full lg:w-auto overflow-x-auto pb-1 lg:pb-0">
            <a href="{{ route('transactions.expired_booklets', ['status' => 'expired', 'search' => request('search'), 'year' => request('year'), 'year_type' => request('year_type'), 'notice_status' => request('notice_status')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'expired' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-white border border-slate-300 dark:border-slate-700' }}">
                <i class="fa-solid fa-circle-exclamation text-xs"></i> بەسەرچووەکان ({{ $expiredCount }})
            </a>
            <a href="{{ route('transactions.expired_booklets', ['status' => 'expiring_soon', 'search' => request('search'), 'year' => request('year'), 'year_type' => request('year_type'), 'notice_status' => request('notice_status')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'expiring_soon' ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-white border border-slate-300 dark:border-slate-700' }}">
                <i class="fa-solid fa-clock text-xs"></i> لە ٣٠ ڕۆژدا ({{ $expiringSoonCount }})
            </a>
            <a href="{{ route('transactions.expired_booklets', ['status' => 'all', 'search' => request('search'), 'year' => request('year'), 'year_type' => request('year_type'), 'notice_status' => request('notice_status')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'all' ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-white border border-slate-300 dark:border-slate-700' }}">
                <i class="fa-solid fa-layer-group text-xs"></i> هەمووی ({{ $totalCount }})
            </a>
        </div>

        <!-- Filter Form (Search + Year + Year Type) -->
        <form action="{{ route('transactions.expired_booklets') }}" method="GET" class="w-full lg:w-auto flex flex-wrap items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="notice_status" value="{{ $noticeStatus }}">

            <!-- Year select -->
            <select name="year" onchange="this.form.submit()" class="bg-slate-100 dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-200 rounded-xl border border-slate-300 dark:border-slate-700 px-3 py-2.5 focus:border-sky-500 focus:outline-none">
                <option value="">هەموو ساڵەکان</option>
                @foreach(['2026', '2025', '2024', '2023'] as $y)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>ساڵی {{ $y }}</option>
                @endforeach
            </select>

            <!-- Year Type Toggle -->
            <select name="year_type" onchange="this.form.submit()" class="bg-slate-100 dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-200 rounded-xl border border-slate-300 dark:border-slate-700 px-3 py-2.5 focus:border-sky-500 focus:outline-none">
                <option value="end_date" {{ request('year_type', 'end_date') == 'end_date' ? 'selected' : '' }}>بەپێی بەرواری کۆتا (بەسەرچوون)</option>
                <option value="transaction_date" {{ request('year_type') == 'transaction_date' ? 'selected' : '' }}>بەپێی بەرواری سەرەتا (دەرهێنان)</option>
            </select>

            <div class="relative flex-1 sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="گەڕان (تابلۆ، هاووڵاتی، شاسی، دەفتەر)..." 
                       class="w-full bg-slate-100 dark:bg-slate-800 text-xs text-slate-900 dark:text-slate-100 placeholder-slate-500 rounded-xl border border-slate-300 dark:border-slate-700 pr-9 pl-3 py-2.5 focus:border-sky-500 focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-xs text-slate-400"></i>
            </div>
            
            <button type="submit" class="px-3.5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow transition">
                بگەڕێ
            </button>
            @if(request('search') || request('year') || request('notice_status'))
                <a href="{{ route('transactions.expired_booklets', ['status' => $status]) }}" class="px-3 py-2.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl transition">
                    پاککردنەوە
                </a>
            @endif
        </form>
    </div>

    <!-- Results Table with Checkbox & WhatsApp Integration -->
    <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-800 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-400 uppercase border-b border-slate-200 dark:border-slate-800 text-[11px] font-black">
                    <tr>
                        <th class="p-3.5 text-center w-10">
                            <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 cursor-pointer">
                        </th>
                        <th class="p-3.5"># بارکۆد</th>
                        <th class="p-3.5">هاووڵاتی</th>
                        <th class="p-3.5">ژمارەی تابلۆ</th>
                        <th class="p-3.5">جۆری ئۆتۆمبێل / مۆدێل</th>
                        <th class="p-3.5">ژمارەی دەفتەر</th>
                        <th class="p-3.5">بەرواری بەسەرچوون</th>
                        <th class="p-3.5">ڕەوش و ماوە</th>
                        <th class="p-3.5 text-center">دۆخی ئاگادارکردنەوە</th>
                        <th class="p-3.5 text-center">کرداری خێرا</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                    @forelse($transactions as $tx)
                        @php
                            $todayDay = now()->startOfDay();
                            $endDay = $tx->end_date ? $tx->end_date->startOfDay() : null;
                            $rawDiff = $endDay ? (int)$todayDay->diffInDays($endDay, false) : 0;
                            $isPast = $endDay && $endDay->lt($todayDay);
                            $isToday = $endDay && $endDay->isSameDay($todayDay);
                            $daysDiff = abs($rawDiff);
                            $normPhone = \App\Models\BookletNotice::normalizePhoneNumber($tx->phone_number);
                            $waMsg = urlencode(\App\Models\BookletNotice::generateDefaultMessage($tx->visitor_name ?? '', $tx->plate_number ?? '', $tx->booklet_number, $tx->end_date?->format('Y-m-d')));
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition" :class="selectedIds.includes({{ $tx->id }}) ? 'bg-sky-50/70 dark:bg-sky-950/30' : ''">
                            <!-- Checkbox -->
                            <td class="p-3.5 text-center">
                                <input type="checkbox" value="{{ $tx->id }}" x-model="selectedIds" class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 cursor-pointer">
                            </td>

                            <!-- Barcode -->
                            <td class="p-3.5 font-mono text-sky-700 dark:text-sky-400 font-bold whitespace-nowrap">
                                <a href="{{ route('transactions.show', $tx->id) }}" class="hover:underline flex items-center gap-1">
                                    <i class="fa-solid fa-barcode text-xs"></i>
                                    <span>{{ $tx->barcode }}</span>
                                </a>
                            </td>

                            <!-- Visitor Name -->
                            <td class="p-3.5">
                                <div class="font-black text-slate-950 dark:text-white">{{ $tx->visitor_name }}</div>
                                @if($tx->visitor_name_eng)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono" dir="ltr">{{ $tx->visitor_name_eng }}</div>
                                @endif
                                @if($tx->phone_number)
                                    <div class="text-[11px] text-emerald-700 dark:text-emerald-400 font-mono flex items-center gap-1 font-bold mt-0.5" dir="ltr">
                                        <i class="fa-solid fa-phone text-[9px]"></i> {{ $tx->phone_number }}
                                    </div>
                                @endif
                            </td>

                            <!-- Plate -->
                            <td class="p-3.5 whitespace-nowrap">
                                <span class="font-mono font-black text-slate-900 dark:text-emerald-400 bg-slate-100 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-500/30 px-2 py-0.5 rounded text-xs">
                                    {{ $tx->plate_number }}
                                </span>
                                <span class="text-[11px] text-slate-600 dark:text-slate-400 block mt-0.5">{{ $tx->plateType->name_kurdish ?? '' }}</span>
                            </td>

                            <!-- Car Make / Model -->
                            <td class="p-3.5">
                                <div class="font-bold text-slate-900 dark:text-slate-200">{{ $tx->carMake->name_kurdish ?? '-' }}</div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-400 font-mono">مۆدێل: {{ $tx->model_year ?? '-' }}</div>
                            </td>

                            <!-- Booklet Number -->
                            <td class="p-3.5 font-mono font-black text-amber-700 dark:text-amber-400 whitespace-nowrap">
                                {{ $tx->booklet_number ?? 'تۆمارنەکراوە' }}
                            </td>

                            <!-- End Date -->
                            <td class="p-3.5 font-mono text-xs whitespace-nowrap">
                                @if($tx->end_date)
                                    <div class="font-bold {{ $isPast ? 'text-rose-700 dark:text-rose-400' : 'text-amber-700 dark:text-amber-400' }}">
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
                            <td class="p-3.5 whitespace-nowrap">
                                @if($isPast)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 dark:bg-rose-500/20 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-500/30">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> بەسەرچووە ({{ $daysDiff }} ڕۆژ)
                                    </span>
                                @elseif($isToday)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-500/30">
                                        <i class="fa-solid fa-clock text-[10px]"></i> ئەمڕۆ بەسەردەچێت
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-500/30">
                                        <i class="fa-solid fa-calendar-check text-[10px]"></i> ماوە: {{ $daysDiff }} ڕۆژ
                                    </span>
                                @endif
                            </td>

                            <!-- Notice Status Tracking Column -->
                            <td class="p-3.5 text-center whitespace-nowrap">
                                @if($tx->latestBookletNotice)
                                    @if($tx->latestBookletNotice->status === 'attended' || $tx->latestBookletNotice->attended_at)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-500/40 inline-flex items-center gap-1" 
                                              title="سەردانی کردووە بەرواری: {{ $tx->latestBookletNotice->attended_at?->format('Y-m-d') }}">
                                            <i class="fa-solid fa-circle-check"></i> هاتووە
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 dark:bg-rose-500/20 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-500/40 inline-flex items-center gap-1"
                                              title="نامەی بۆ چووە لە بەرواری: {{ $tx->latestBookletNotice->sent_at?->format('Y-m-d') }}">
                                            <i class="fa-solid fa-clock"></i> نەهاتووە
                                        </span>
                                    @endif
                                    <div class="text-[9px] text-slate-500 font-mono mt-0.5 max-w-[120px] truncate mx-auto">
                                        {{ $tx->latestBookletNotice->batch_name ?: $tx->latestBookletNotice->batch_code }}
                                    </div>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-700">
                                        نەنێردراو
                                    </span>
                                @endif
                            </td>

                            <!-- Quick Action Buttons -->
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <!-- One-Click WhatsApp Direct -->
                                    @if($normPhone)
                                        <a href="https://wa.me/{{ $normPhone }}?text={{ $waMsg }}" target="_blank"
                                           title="ناردنی نامەی ڕاستەوخۆ لە واتسئەپ" 
                                           class="w-7 h-7 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-white flex items-center justify-center shadow transition text-xs">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                        <a href="viber://chat?number=%2B{{ $normPhone }}" target="_blank"
                                           title="ناردنی نامەی ڕاستەوخۆ لە ڤایبەر" 
                                           class="w-7 h-7 rounded-lg bg-purple-600 hover:bg-purple-500 text-white flex items-center justify-center shadow transition text-xs">
                                            <i class="fa-brands fa-viber"></i>
                                        </a>
                                    @endif

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

                                    <a href="{{ route('transactions.show', $tx->id) }}" 
                                       title="بینینی تەواوی مامەڵە" 
                                       class="px-2 py-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 rounded-lg text-xs transition">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-8 text-center text-slate-500">
                                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 mx-auto flex items-center justify-center text-2xl text-slate-500 mb-3">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <span class="font-bold block text-sm text-slate-700 dark:text-slate-300">هیچ دەفتەرێکی بەسەرچوو نەدۆزرایەوە!</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================== -->
    <!-- Batch Dispatch Modal (ناردنی وەجبە بە واتسئەپ و ڤایبەر) -->
    <!-- ============================================== -->
    <div x-show="showBatchModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">ئامادەکردنی ناردنی وەجبەی نوێ</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">ناردنی ئاگادارکردنەوە بۆ دەفتەرە بەسەرچووەکان و چاودێری هاتنیان</p>
                    </div>
                </div>
                <button type="button" @click="showBatchModal = false" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Settings Form (Before Commit) -->
            <template x-if="!batchResult">
                <div class="space-y-4">
                    <div class="bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 p-3 rounded-2xl flex items-center justify-between text-xs text-sky-800 dark:text-sky-300">
                        <span class="font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-users"></i> ژمارەی هاووڵاتیانی دەستنیشانکراو:
                        </span>
                        <strong class="font-mono text-base font-black" x-text="selectedIds.length + ' کەس'"></strong>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ناوی وەجبە:</label>
                            <input type="text" x-model="batchName" class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">کەناڵی پەیوەندی:</label>
                            <select x-model="channel" class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-sky-500">
                                <option value="whatsapp">واتسئەپ (WhatsApp Web / App)</option>
                                <option value="viber">ڤایبەر (Viber App)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">دەقی نامەی فەرمی:</label>
                            <span class="text-[10px] text-slate-500">تاگەکان بە خۆکار لە نامەکەدا دەگۆڕدرێن</span>
                        </div>
                        <textarea x-model="messageTemplate" rows="5" class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-3 text-xs text-slate-900 dark:text-white leading-relaxed focus:outline-none focus:border-sky-500"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showBatchModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl transition">
                            داخستن
                        </button>
                        <button type="button" @click="commitBatch()" :disabled="batchSending"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2 disabled:opacity-50">
                            <i class="fa-solid fa-check-double" :class="batchSending ? 'fa-spin' : ''"></i>
                            <span x-text="batchSending ? 'تۆمارکردن...' : 'تۆمارکردن و دەستپێکردنی ناردن'"></span>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Interactive Queue Dispatch (After Commit) -->
            <template x-if="batchResult">
                <div class="space-y-4 animate-in fade-in duration-200">
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 rounded-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                                <div>
                                    <h4 class="text-xs font-black text-emerald-900 dark:text-emerald-200">وەجبەکە لە سیستەم تۆمارکرا و کۆدی وەرگرت!</h4>
                                    <span class="font-mono text-[10px] text-emerald-700 dark:text-emerald-400" x-text="batchResult.batch_code"></span>
                                </div>
                            </div>
                            <span class="text-xs font-black text-emerald-800 dark:text-emerald-300 font-mono" x-text="batchResult.notices.length + ' کەس'"></span>
                        </div>
                    </div>

                    <!-- Step-by-step Fast Dispatcher -->
                    <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 text-center space-y-3">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400 block">
                            پێشکەوتنی ناردنی خێرا (Step-by-step Queue):
                        </span>
                        
                        <div class="text-lg font-black text-slate-900 dark:text-white" x-text="'کەسی ' + (activeQueueIndex + 1) + ' لە ' + batchResult.notices.length"></div>

                        <template x-if="batchResult.notices[activeQueueIndex]">
                            <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 inline-block text-xs text-right min-w-[280px]">
                                <div>ناو: <strong class="text-slate-900 dark:text-white" x-text="batchResult.notices[activeQueueIndex].visitor_name"></strong></div>
                                <div>تابلۆ: <span class="font-mono font-bold text-sky-600" x-text="batchResult.notices[activeQueueIndex].plate_number"></span></div>
                                <div>تەلەفۆن: <span class="font-mono font-bold text-emerald-600" dir="ltr" x-text="batchResult.notices[activeQueueIndex].phone_number || 'نییە'"></span></div>
                            </div>
                        </template>

                        <div class="flex items-center justify-center gap-2 pt-2">
                            <button type="button" @click="sendQueueItem(activeQueueIndex)" 
                                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2 cursor-pointer">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                <span>ناردن لە WhatsApp / Viber 🚀</span>
                            </button>
                            <button type="button" @click="activeQueueIndex = Math.min(activeQueueIndex + 1, batchResult.notices.length - 1)" 
                                    class="px-3.5 py-2.5 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition">
                                تێپەڕاندن (Next)
                            </button>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-between border-t border-slate-200 dark:border-slate-800">
                        <a :href="'{{ route('booklet_notices.index') }}?batch=' + batchResult.batch_code" 
                           class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>بڕۆ بۆ پەڕەی ئاماری ئەم وەجبەیە</span>
                        </a>
                        <button type="button" @click="showBatchModal = false; window.location.reload();" 
                                class="px-4 py-2 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl">
                            تەواو و نوێکردنەوەی پەڕە
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
