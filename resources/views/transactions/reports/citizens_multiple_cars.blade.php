@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    showDetailsModal: false,
    loadingDetails: false,
    citizenDetails: null,
    
    viewDetails(name, phone) {
        this.loadingDetails = true;
        this.showDetailsModal = true;
        this.citizenDetails = null;

        const params = new URLSearchParams();
        if (name) params.append('name', name);
        if (phone) params.append('phone', phone);

        fetch('{{ route('reports.citizens_multiple_cars.details') }}?' + params.toString())
            .then(res => res.json())
            .then(data => {
                this.loadingDetails = false;
                if (data.success) {
                    this.citizenDetails = data;
                } else {
                    alert(data.message || 'هەڵەیەک ڕوویدا لە بارکردنی زانیارییەکان');
                    this.showDetailsModal = false;
                }
            })
            .catch(err => {
                this.loadingDetails = false;
                alert('هەڵەی پەیوەندی بە سێرڤەر ڕوویدا');
                this.showDetailsModal = false;
            });
    }
}">
    <!-- Page Header (Hidden when printing) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 print:hidden">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-500 flex items-center justify-center text-lg shadow-sm">
                    <i class="fa-solid fa-users-viewfinder"></i>
                </span>
                <span>ڕاپۆرتی هاوڵاتیانی خاوەن چەند ئۆتۆمبێل (کارا و بەسەرچوو)</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                ئاماری ئەو هاووڵاتیانەی زیاتر لە یەک ئۆتۆمبێلیان هەیە بە جیاکردنەوەی دەفتەرە <span class="font-bold text-emerald-600 dark:text-emerald-400">کاراکان</span> و <span class="font-bold text-rose-600 dark:text-rose-400">بەسەرچووەکان</span> (بەبێ هەژمارکردنی دەفتەرە پووچەڵکراوەکان).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.citizens_multiple_cars.export', request()->all()) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-file-excel"></i>
                <span>داگرتنی CSV (ئێکسڵ)</span>
            </a>
            <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>چاپی لیست</span>
            </button>
            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-chart-pie"></i>
                <span>داشبۆرد</span>
            </a>
        </div>
    </div>

    <!-- Printable Header (Visible Only When Printing) -->
    <div class="hidden print:block text-center border-b pb-4 mb-4">
        <h1 class="text-xl font-bold">بەڕێوەبەرایەتی گشتی گومرگ - سیستەمی دەفتەری گەشتیاری</h1>
        <h2 class="text-base font-bold mt-1">
            لیستی ئەو هاووڵاتیانەی زیاتر لە یەک ئۆتۆمبێلیان بەناوەوەیە (کارا و بەسەرچوو)
        </h2>
        <div class="text-xs text-slate-600 mt-2 flex justify-between px-6">
            <span>کۆی هاووڵاتیان: {{ number_format($kpis->total_citizens ?? 0) }}</span>
            <span>ئۆتۆمبێلی کارا: {{ number_format($kpis->total_active_cars ?? 0) }}</span>
            <span>ئۆتۆمبێلی بەسەرچوو: {{ number_format($kpis->total_expired_cars ?? 0) }}</span>
            <span>کۆی ئۆتۆمبێلەکان: {{ number_format($kpis->total_cars ?? 0) }}</span>
            <span>بەرواری چاپ: {{ date('Y-m-d H:i') }}</span>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 print:hidden">
        <!-- 1. Total Citizens -->
        <a href="{{ route('reports.citizens_multiple_cars', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
           class="p-4 rounded-2xl border transition group {{ ($status ?? 'all') === 'all' ? 'bg-indigo-500/10 border-indigo-500 shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-indigo-400' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">کۆی هاووڵاتیانی فرە ئۆتۆمبێل</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/15 text-indigo-500 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 font-mono">{{ number_format($kpis->total_citizens ?? 0) }}</span>
                <span class="text-[11px] text-slate-500 font-bold">هاووڵاتی</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 flex justify-between">
                <span>بەرزترین بۆ ١ کەس:</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 font-mono">{{ $kpis->max_cars ?? 0 }} ئۆتۆمبێل</span>
            </div>
        </a>

        <!-- 2. Active Cars (کارا) -->
        <a href="{{ route('reports.citizens_multiple_cars', array_merge(request()->except('status', 'page'), ['status' => 'active'])) }}"
           class="p-4 rounded-2xl border transition group {{ ($status ?? '') === 'active' ? 'bg-emerald-500/15 border-emerald-500 shadow-md ring-2 ring-emerald-500/20' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-emerald-400' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">ئۆتۆمبێلە کاراکان (Active)</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($kpis->total_active_cars ?? 0) }}</span>
                <span class="text-[11px] text-emerald-600/80 font-bold">کارا و مۆڵەتدار</span>
            </div>
            <div class="mt-2 pt-2 border-t border-emerald-500/20 text-[10px] text-emerald-600 dark:text-emerald-400 flex justify-between font-bold">
                <span>ڕێژەی کارا:</span>
                <span class="font-mono">{{ ($kpis->total_cars ?? 0) > 0 ? round(($kpis->total_active_cars / $kpis->total_cars) * 100, 1) : 0 }}%</span>
            </div>
        </a>

        <!-- 3. Expired Cars (بەسەرچوو) -->
        <a href="{{ route('reports.citizens_multiple_cars', array_merge(request()->except('status', 'page'), ['status' => 'expired'])) }}"
           class="p-4 rounded-2xl border transition group {{ ($status ?? '') === 'expired' ? 'bg-rose-500/15 border-rose-500 shadow-md ring-2 ring-rose-500/20' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-rose-400' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-700 dark:text-rose-400">ئۆتۆمبێلە بەسەرچووەکان (Expired)</span>
                <div class="w-9 h-9 rounded-xl bg-rose-500/20 text-rose-500 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ number_format($kpis->total_expired_cars ?? 0) }}</span>
                <span class="text-[11px] text-rose-600/80 font-bold">بەسەرچوو</span>
            </div>
            <div class="mt-2 pt-2 border-t border-rose-500/20 text-[10px] text-rose-600 dark:text-rose-400 flex justify-between font-bold">
                <span>ڕێژەی بەسەرچوو:</span>
                <span class="font-mono">{{ ($kpis->total_cars ?? 0) > 0 ? round(($kpis->total_expired_cars / $kpis->total_cars) * 100, 1) : 0 }}%</span>
            </div>
        </a>

        <!-- 4. Total Distinct Cars (Total) -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">کۆی ئۆتۆمبێلەکان (کارا + بەسەرچوو)</span>
                <div class="w-9 h-9 rounded-xl bg-sky-500/15 text-sky-500 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-car-side"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-sky-600 dark:text-sky-400 font-mono">{{ number_format($kpis->total_cars ?? 0) }}</span>
                <span class="text-[11px] text-slate-500 font-bold">ئۆتۆمبێلی جیاواز</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 flex justify-between">
                <span>پووچەڵکراو:</span>
                <span class="font-bold text-slate-400">سفر (لابراوە)</span>
            </div>
        </div>

        <!-- 5. Total Booklets Issued -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">کۆی دەفتەرە تۆمارکراوەکان</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-book"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ number_format($kpis->total_booklets ?? 0) }}</span>
                <span class="text-[11px] text-slate-500 font-bold">دەفتەری دەرهێنراو</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 flex justify-between">
                <span>تێکڕا بۆ ١ کەس:</span>
                <span class="font-bold font-mono text-slate-600 dark:text-slate-300">{{ ($kpis->total_citizens ?? 0) > 0 ? round($kpis->total_booklets / $kpis->total_citizens, 1) : 0 }} دەفتەر</span>
            </div>
        </div>
    </div>

    <!-- Filters and Search Toolbar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm print:hidden">
        <form method="GET" action="{{ route('reports.citizens_multiple_cars') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-end">
            <!-- Search Input -->
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    <i class="fa-solid fa-magnifying-glass text-indigo-500 ml-1"></i> گەڕان (ناو، مۆبایل، تابلۆ، شاسی)
                </label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" 
                           placeholder="ناوی هاووڵاتی، 0770...، ژمارەی تابلۆ..." 
                           class="w-full pl-3 pr-9 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="absolute right-3 top-2.5 text-slate-400 text-xs">
                        <i class="fa-solid fa-search"></i>
                    </span>
                </div>
            </div>

            <!-- Status Filter: Active vs Expired -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    <i class="fa-solid fa-filter-circle-dollar text-emerald-500 ml-1"></i> دۆخی ئۆتۆمبێلەکان
                </label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>هەمووی (کارا + بەسەرچوو)</option>
                    <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>لانی کەم ۱ کارای هەبێت</option>
                    <option value="expired" {{ ($status ?? '') === 'expired' ? 'selected' : '' }}>لانی کەم ۱ بەسەرچووی هەبێت</option>
                    <option value="only_active" {{ ($status ?? '') === 'only_active' ? 'selected' : '' }}>تەنها کارا (هەموویان کاران)</option>
                    <option value="only_expired" {{ ($status ?? '') === 'only_expired' ? 'selected' : '' }}>تەنها بەسەرچوو (هەموویان بەسەرچوون)</option>
                </select>
            </div>

            <!-- Min Cars Filter -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    <i class="fa-solid fa-car text-sky-500 ml-1"></i> لانی کەم چەند ئۆتۆمبێل؟
                </label>
                <select name="min_cars" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="2" {{ $minCars == 2 ? 'selected' : '' }}>۲ ئۆتۆمبێل و زیاتر (سەرەتایی)</option>
                    <option value="3" {{ $minCars == 3 ? 'selected' : '' }}>۳ ئۆتۆمبێل و زیاتر</option>
                    <option value="4" {{ $minCars == 4 ? 'selected' : '' }}>۴ ئۆتۆمبێل و زیاتر</option>
                    <option value="5" {{ $minCars == 5 ? 'selected' : '' }}>۵ ئۆتۆمبێل و زیاتر</option>
                </select>
            </div>

            <!-- Group By Filter -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    <i class="fa-solid fa-layer-group text-slate-500 ml-1"></i> گرووپکردن
                </label>
                <select name="group_by" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="both" {{ $groupBy == 'both' ? 'selected' : '' }}>ناو و مۆبایل پێکەوە</option>
                    <option value="name" {{ $groupBy == 'name' ? 'selected' : '' }}>تەنها ناوی هاووڵاتی</option>
                    <option value="phone" {{ $groupBy == 'phone' ? 'selected' : '' }}>تەنها ژمارەی مۆبایل</option>
                </select>
            </div>

            <!-- Sort By -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    <i class="fa-solid fa-arrow-down-short-wide text-amber-500 ml-1"></i> ڕیزبەندی
                </label>
                <select name="sort" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="cars_desc" {{ $sortBy == 'cars_desc' ? 'selected' : '' }}>زۆرترین ئۆتۆمبێل</option>
                    <option value="active_desc" {{ $sortBy == 'active_desc' ? 'selected' : '' }}>زۆرترین کارا</option>
                    <option value="expired_desc" {{ $sortBy == 'expired_desc' ? 'selected' : '' }}>زۆرترین بەسەرچوو</option>
                    <option value="booklets_desc" {{ $sortBy == 'booklets_desc' ? 'selected' : '' }}>زۆرترین دەفتەر</option>
                    <option value="name_asc" {{ $sortBy == 'name_asc' ? 'selected' : '' }}>ناوی هاووڵاتی (ئەلفوبێ)</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="md:col-span-1 flex items-center gap-1.5">
                <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1 shadow-sm">
                    <i class="fa-solid fa-filter"></i>
                    <span>فلتەر</span>
                </button>
                <a href="{{ route('reports.citizens_multiple_cars') }}" title="سڕینەوەی فلتەرەکان" class="py-2 px-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl transition flex items-center justify-center">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <span>لیستی هاووڵاتیان بەپێی ئۆتۆمبێلە کارا و بەسەرچووەکانیان</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-mono">
                    {{ number_format($citizens->total()) }} هاووڵاتی
                </span>
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">
                پەڕەی {{ $citizens->currentPage() }} لە {{ $citizens->lastPage() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-400">
                        <th class="py-3 px-3 w-12 text-center">#</th>
                        <th class="py-3 px-4">ناوی هاووڵاتی</th>
                        <th class="py-3 px-4">ژمارەی مۆبایل</th>
                        <th class="py-3 px-4 text-center">کۆی ئۆتۆمبێلەکان</th>
                        <th class="py-3 px-4 text-center">دۆخی ئۆتۆمبێلەکان (کارا / بەسەرچوو)</th>
                        <th class="py-3 px-4 text-center">کۆی دەفتەرەکان</th>
                        <th class="py-3 px-4">تابلۆی ئۆتۆمبێلەکان</th>
                        <th class="py-3 px-4 text-center print:hidden">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-xs">
                    @forelse($citizens as $citizen)
                        @php
                            $rowName = $citizen->visitor_name ?? ($citizen->all_names ?? 'نەزانراو');
                            $rowPhone = $citizen->phone_number ?? ($citizen->all_phones ?? '-');
                            $plates = array_filter(array_map('trim', explode(',', $citizen->plates_list ?? '')));
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <!-- Index -->
                            <td class="py-3 px-3 text-center font-mono text-slate-400 text-xs">
                                {{ ($citizens->currentPage() - 1) * $citizens->perPage() + $loop->iteration }}
                            </td>

                            <!-- Citizen Name -->
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs flex-shrink-0">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <span>{{ $rowName }}</span>
                                </div>
                            </td>

                            <!-- Phone Number -->
                            <td class="py-3 px-4 font-mono text-slate-700 dark:text-slate-300">
                                @if($rowPhone && $rowPhone !== '-')
                                    <div class="flex items-center gap-1.5">
                                        <a href="tel:{{ $rowPhone }}" class="hover:text-indigo-600 hover:underline">
                                            {{ $rowPhone }}
                                        </a>
                                        @if(preg_match('/^07[5789]\d{8}$/', $rowPhone))
                                            <a href="https://wa.me/964{{ substr($rowPhone, 1) }}" target="_blank" title="ناردنی نامەی واتسئەپ" class="text-emerald-500 hover:text-emerald-600 print:hidden text-xs">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Total Car Count -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black bg-sky-500/15 text-sky-700 dark:text-sky-300 border border-sky-500/30">
                                    <i class="fa-solid fa-car text-[10px]"></i>
                                    <span class="font-mono">{{ $citizen->car_count }}</span>
                                    <span class="text-[10px]">ئۆتۆمبێل</span>
                                </span>
                            </td>

                            <!-- Active vs Expired Status Badges -->
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                    @if($citizen->active_cars > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-black bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                                            <i class="fa-solid fa-check text-[9px]"></i>
                                            <span class="font-mono">{{ $citizen->active_cars }}</span>
                                            <span>کارا</span>
                                        </span>
                                    @endif
                                    @if($citizen->expired_cars > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-black bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-500/30">
                                            <i class="fa-solid fa-clock-rotate-left text-[9px]"></i>
                                            <span class="font-mono">{{ $citizen->expired_cars }}</span>
                                            <span>بەسەرچوو</span>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Booklet Count -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                                    <i class="fa-solid fa-book text-[10px]"></i>
                                    <span class="font-mono">{{ $citizen->total_booklets }}</span>
                                    <span class="text-[10px]">دەفتەر</span>
                                </span>
                            </td>

                            <!-- Plates Preview -->
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap gap-1.5 max-w-md">
                                    @foreach(array_slice($plates, 0, 4) as $plt)
                                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-mono text-[11px] font-bold border border-slate-200 dark:border-slate-700">
                                            {{ $plt }}
                                        </span>
                                    @endforeach
                                    @if(count($plates) > 4)
                                        <span class="px-1.5 py-0.5 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono text-[10px] font-bold">
                                            +{{ count($plates) - 4 }} تریش
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-center print:hidden">
                                <button type="button" 
                                        @click="viewDetails('{{ addslashes($citizen->visitor_name ?? '') }}', '{{ addslashes($citizen->phone_number ?? '') }}')"
                                        class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 font-bold text-xs transition flex items-center gap-1.5 mx-auto border border-indigo-200 dark:border-indigo-800 cursor-pointer shadow-sm">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    <span>وردەکاری دەفتەرەکان</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-car-tunnel text-4xl mb-3 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm font-bold">هیچ هاووڵاتییەک بەپێی ئەم فلتەرە نەدۆزرایەوە.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($citizens->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex justify-center print:hidden">
                {{ $citizens->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- CITIZEN CARS & BOOKLETS DETAILS MODAL -->
    <!-- ========================================================================= -->
    <div x-show="showDetailsModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        
        <div @click.away="showDetailsModal = false" 
             class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            
            <!-- Modal Header -->
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/15 text-indigo-500 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white" x-text="citizenDetails ? citizenDetails.visitor_name : 'بارکردنی زانیارییەکان...'"></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5" x-text="citizenDetails ? ('ژمارەی مۆبایل: ' + citizenDetails.phone_number) : ''"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="window.print()" class="px-3 py-1.5 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-print"></i>
                        <span>چاپ</span>
                    </button>
                    <button type="button" @click="showDetailsModal = false" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-rose-500 hover:text-white dark:bg-slate-800 text-slate-500 transition flex items-center justify-center">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-6 overflow-y-auto space-y-4">
                <!-- Loading State -->
                <template x-if="loadingDetails">
                    <div class="py-16 text-center text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                        <p class="text-xs font-bold">تکایە چاوەڕێ بکە، زانیارییەکان باردەکرێن...</p>
                    </div>
                </template>

                <!-- Loaded State -->
                <template x-if="!loadingDetails && citizenDetails">
                    <div class="space-y-4">
                        <!-- Quick Stats inside modal -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="p-3 rounded-xl bg-sky-500/10 border border-sky-500/20">
                                <span class="text-[11px] font-bold text-sky-600 dark:text-sky-400">کۆی ئۆتۆمبێلەکان</span>
                                <div class="text-xl font-black text-sky-700 dark:text-sky-300 font-mono mt-1" x-text="citizenDetails.distinct_cars_count + ' ئۆتۆمبێل'"></div>
                            </div>
                            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">ئۆتۆمبێلی کارا (Active)</span>
                                <div class="text-xl font-black text-emerald-700 dark:text-emerald-300 font-mono mt-1" x-text="(citizenDetails.active_cars_count || 0) + ' کارا'"></div>
                            </div>
                            <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20">
                                <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400">ئۆتۆمبێلی بەسەرچوو (Expired)</span>
                                <div class="text-xl font-black text-rose-700 dark:text-rose-300 font-mono mt-1" x-text="(citizenDetails.expired_cars_count || 0) + ' بەسەرچوو'"></div>
                            </div>
                            <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20">
                                <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400">کۆی دەفتەرەکان</span>
                                <div class="text-xl font-black text-amber-700 dark:text-amber-300 font-mono mt-1" x-text="citizenDetails.total_booklets_count + ' دەفتەر'"></div>
                            </div>
                        </div>

                        <!-- Vehicles and Booklets Table -->
                        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 font-bold text-xs text-slate-700 dark:text-slate-300 flex items-center justify-between">
                                <span>دەفتەرە تۆمارکراوەکان بەپێی بەسەرچوون و کارابوون (پووچەڵکراو پیشان نادرێت)</span>
                                <span class="text-[11px] font-mono font-bold text-slate-500" x-text="'کۆی تۆمارەکان: ' + citizenDetails.transactions.length"></span>
                            </div>

                            <div class="overflow-x-auto max-h-80">
                                <table class="w-full text-right text-xs border-collapse">
                                    <thead class="bg-slate-100 dark:bg-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-400 sticky top-0 z-10">
                                        <tr class="border-b border-slate-200 dark:border-slate-700">
                                            <th class="py-2.5 px-3">دەفتەر</th>
                                            <th class="py-2.5 px-3">تابلۆ</th>
                                            <th class="py-2.5 px-3">جۆر و مۆدێلی ئۆتۆمبێل</th>
                                            <th class="py-2.5 px-3">ژمارەی شاسی</th>
                                            <th class="py-2.5 px-3">جۆری مامەڵە</th>
                                            <th class="py-2.5 px-3">بەسەرچوون</th>
                                            <th class="py-2.5 px-3 text-center">بارودۆخ</th>
                                            <th class="py-2.5 px-3 text-center">کردار</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <template x-for="tx in citizenDetails.transactions" :key="tx.id">
                                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                                <!-- Booklet No -->
                                                <td class="py-2.5 px-3 font-mono font-black text-indigo-600 dark:text-indigo-400" x-text="tx.booklet_number || '-'"></td>

                                                <!-- Plate -->
                                                <td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-white" x-text="tx.plate_number"></td>

                                                <!-- Car Make & Model -->
                                                <td class="py-2.5 px-3 text-slate-800 dark:text-slate-200">
                                                    <span x-text="tx.car_make"></span>
                                                    <span class="text-slate-400 font-mono" x-show="tx.model_year" x-text="' (' + tx.model_year + ')'"></span>
                                                </td>

                                                <!-- Chassis -->
                                                <td class="py-2.5 px-3 font-mono text-[11px] text-slate-500" x-text="tx.chassis_number"></td>

                                                <!-- Transaction Type -->
                                                <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300" x-text="tx.transaction_type"></td>

                                                <!-- Expiry -->
                                                <td class="py-2.5 px-3 font-mono text-[11px] text-slate-600 dark:text-slate-400" x-text="tx.end_date || '-'"></td>

                                                <!-- Status Badge: Strictly Active or Expired -->
                                                <td class="py-2.5 px-3 text-center">
                                                    <template x-if="tx.is_expired">
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30">
                                                            <i class="fa-solid fa-clock-rotate-left ml-1 text-[9px]"></i>بەسەرچوو
                                                        </span>
                                                    </template>
                                                    <template x-if="!tx.is_expired">
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                                            <i class="fa-solid fa-circle-check ml-1 text-[9px]"></i>کارا
                                                        </span>
                                                    </template>
                                                </td>

                                                <!-- Action Link -->
                                                <td class="py-2.5 px-3 text-center">
                                                    <a :href="tx.show_url" target="_blank" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-indigo-600 hover:text-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-bold transition inline-flex items-center gap-1">
                                                        <span>مامەڵە</span>
                                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                <button type="button" @click="showDetailsModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl transition">
                    داخستن
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
