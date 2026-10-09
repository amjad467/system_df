@extends('layouts.app')

@section('title', 'ڕاپۆرتی ژمێریاری (محاسبة ٦٦)')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-800/90 border border-slate-700/80 p-5 rounded-2xl shadow-xl backdrop-blur">
        <div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-700 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                    <i class="fa-solid fa-file-invoice-dollar text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-2">
                        <span>ڕاپۆرتی فەرمی ژمێریاری (محاسبة ٦٦)</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 font-mono">بەشی وەسڵ و داهات</span>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-400 mt-0.5">بەڕێوەبەرایەتی گومرگی سلێمانی • بەدواداچوونی وەسڵەکان، داهات و پووچەڵکراوەکان</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('reports.accounting_66.print', request()->query()) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg shadow-sky-600/30 flex items-center gap-2 transition hover:scale-105 active:scale-95">
                <i class="fa-solid fa-print"></i>
                <span>چاپی فەرمی (محاسبة ٦٦)</span>
            </a>
            <a href="{{ route('reports.accounting_66.export', request()->query()) }}"
               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg shadow-emerald-600/20 flex items-center gap-2 transition hover:scale-105 active:scale-95">
                <i class="fa-solid fa-file-excel"></i>
                <span>داگرتنی Excel / CSV</span>
            </a>
        </div>
    </div>

    <!-- Quick Date Presets -->
    <div class="bg-slate-800/60 border border-slate-700/60 p-4 rounded-2xl shadow-lg">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-300 flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-400"></i>
                فلتەری کاتی خێرا:
            </span>
            @if(request()->anyFilled(['preset', 'date_from', 'date_to', 'exact_date', 'filter_month', 'filter_year', 'receipt_from', 'receipt_to', 'status', 'search', 'transaction_type_id']))
                <a href="{{ route('reports.accounting_66') }}" class="text-xs text-rose-400 hover:text-rose-300 flex items-center gap-1 font-semibold">
                    <i class="fa-solid fa-rotate-left"></i> پاقژکردنەوەی هەموو فلتەرەکان
                </a>
            @endif
        </div>
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <a href="{{ route('reports.accounting_66', array_merge(request()->except(['page', 'date_from', 'date_to', 'exact_date', 'filter_month', 'filter_year']), ['preset' => 'today'])) }}"
               class="px-3 py-1.5 rounded-lg border font-semibold transition {{ $activePreset === 'today' ? 'bg-amber-600 text-white border-amber-500 shadow-md shadow-amber-600/30' : 'bg-slate-900/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}">
                ئەمڕۆ
            </a>
            <a href="{{ route('reports.accounting_66', array_merge(request()->except(['page', 'date_from', 'date_to', 'exact_date', 'filter_month', 'filter_year']), ['preset' => 'yesterday'])) }}"
               class="px-3 py-1.5 rounded-lg border font-semibold transition {{ $activePreset === 'yesterday' ? 'bg-amber-600 text-white border-amber-500 shadow-md shadow-amber-600/30' : 'bg-slate-900/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}">
                دوێنێ
            </a>
            <a href="{{ route('reports.accounting_66', array_merge(request()->except(['page', 'date_from', 'date_to', 'exact_date', 'filter_month', 'filter_year']), ['preset' => 'this_week'])) }}"
               class="px-3 py-1.5 rounded-lg border font-semibold transition {{ $activePreset === 'this_week' ? 'bg-amber-600 text-white border-amber-500 shadow-md shadow-amber-600/30' : 'bg-slate-900/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}">
                ئەم هەفتەیە
            </a>
            <a href="{{ route('reports.accounting_66', array_merge(request()->except(['page', 'date_from', 'date_to', 'exact_date', 'filter_month', 'filter_year']), ['preset' => 'this_month'])) }}"
               class="px-3 py-1.5 rounded-lg border font-semibold transition {{ $activePreset === 'this_month' ? 'bg-amber-600 text-white border-amber-500 shadow-md shadow-amber-600/30' : 'bg-slate-900/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}">
                ئەم مانگە
            </a>
            <a href="{{ route('reports.accounting_66', array_merge(request()->except(['page', 'date_from', 'date_to', 'exact_date', 'filter_month', 'filter_year']), ['preset' => 'last_month'])) }}"
               class="px-3 py-1.5 rounded-lg border font-semibold transition {{ $activePreset === 'last_month' ? 'bg-amber-600 text-white border-amber-500 shadow-md shadow-amber-600/30' : 'bg-slate-900/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}">
                مانگی پێشوو
            </a>
            <a href="{{ route('reports.accounting_66', array_merge(request()->except(['page', 'date_from', 'date_to', 'exact_date', 'filter_month', 'filter_year']), ['preset' => 'this_year'])) }}"
               class="px-3 py-1.5 rounded-lg border font-semibold transition {{ $activePreset === 'this_year' ? 'bg-amber-600 text-white border-amber-500 shadow-md shadow-amber-600/30' : 'bg-slate-900/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}">
                ئەمساڵ ({{ date('Y') }})
            </a>
            <a href="{{ route('reports.accounting_66', array_merge(request()->except(['page', 'date_from', 'date_to', 'exact_date', 'filter_month', 'filter_year']), ['preset' => 'last_year'])) }}"
               class="px-3 py-1.5 rounded-lg border font-semibold transition {{ $activePreset === 'last_year' ? 'bg-amber-600 text-white border-amber-500 shadow-md shadow-amber-600/30' : 'bg-slate-900/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}">
                ساڵی پێشوو ({{ date('Y') - 1 }})
            </a>
            <a href="{{ route('reports.accounting_66') }}"
               class="px-3 py-1.5 rounded-lg border font-semibold transition {{ empty($activePreset) && !request()->anyFilled(['date_from', 'date_to', 'exact_date', 'filter_month', 'filter_year']) ? 'bg-sky-600 text-white border-sky-500 shadow-md shadow-sky-600/30' : 'bg-slate-900/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}">
                هەموو کاتەکان
            </a>
        </div>
    </div>

    <!-- Advanced Filter & Search Box -->
    <div class="bg-slate-800/80 border border-slate-700/80 p-5 rounded-2xl shadow-xl backdrop-blur">
        <form action="{{ route('reports.accounting_66') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Search Term -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 mb-1">گەڕان (ناو، تابلۆ، ژ. وەسڵ):</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="ناوی هاووڵاتی، تابلۆ یان بارکۆد..."
                               class="w-full bg-slate-900 text-sm text-slate-200 placeholder-slate-500 rounded-xl border border-slate-700 pr-9 pl-3 py-2 focus:outline-none focus:border-amber-500">
                        <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-slate-500 text-sm"></i>
                    </div>
                </div>

                <!-- Receipt Number Range: لە [ ] بۆ [ ] -->
                <div>
                    <label class="block text-xs font-bold text-amber-300 mb-1">لە ژمارە پسولەی:</label>
                    <input type="text" name="receipt_from" value="{{ request('receipt_from', $receiptFrom) }}" placeholder="1"
                           class="w-full bg-slate-900 text-sm text-amber-300 font-mono text-center font-bold rounded-xl border border-amber-500/40 py-2 focus:outline-none focus:border-amber-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-amber-300 mb-1">بۆ ژمارە پسولەی:</label>
                    <input type="text" name="receipt_to" value="{{ request('receipt_to', $receiptTo) }}" placeholder="50"
                           class="w-full bg-slate-900 text-sm text-amber-300 font-mono text-center font-bold rounded-xl border border-amber-500/40 py-2 focus:outline-none focus:border-amber-400">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">دۆخی پسولە:</label>
                    <select name="status" class="w-full bg-slate-900 text-sm text-slate-200 rounded-xl border border-slate-700 py-2 px-3 focus:outline-none focus:border-amber-500">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>هەموو پسولەکان</option>
                        <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>تەنها پارەدراوەکان</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>تەنها پووچەڵکراوەکان</option>
                    </select>
                </div>

                <!-- Transaction Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">جۆری مامەڵە:</label>
                    <select name="transaction_type_id" class="w-full bg-slate-900 text-sm text-slate-200 rounded-xl border border-slate-700 py-2 px-3 focus:outline-none focus:border-amber-500">
                        <option value="">هەموو جۆرەکان</option>
                        @foreach($transactionTypes as $tt)
                            <option value="{{ $tt->id }}" {{ $transactionTypeId == $tt->id ? 'selected' : '' }}>
                                {{ $tt->name_kurdish }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date Granular Filters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t border-slate-700/60">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">لە بەرواری (From Date):</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="w-full bg-slate-900 text-sm text-slate-200 rounded-xl border border-slate-700 py-2 px-3 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">بۆ بەرواری (To Date):</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="w-full bg-slate-900 text-sm text-slate-200 rounded-xl border border-slate-700 py-2 px-3 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">یان یەکسان بە بەرواری:</label>
                    <input type="date" name="exact_date" value="{{ $exactDate }}"
                           class="w-full bg-slate-900 text-sm text-slate-200 rounded-xl border border-slate-700 py-2 px-3 focus:outline-none focus:border-amber-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm shadow-md shadow-amber-600/30 flex items-center justify-center gap-2 transition">
                        <i class="fa-solid fa-filter"></i> فلتەر بکە
                    </button>
                    <a href="{{ route('reports.accounting_66') }}"
                       class="py-2 px-3 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold text-sm transition">
                        پاقژکردنەوە
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Official Accounting 66 KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Card 1: Total Receipts -->
        <div class="bg-slate-800/80 border border-slate-700/80 p-4 rounded-2xl shadow-lg">
            <span class="text-[11px] font-bold text-slate-400 block mb-1">کۆی پسولەکان</span>
            <div class="text-2xl font-black text-white font-mono">{{ number_format($totalReceiptsCount) }}</div>
            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2 pt-2 border-t border-slate-700/60">
                <span class="text-emerald-400 font-bold">پارەدراو: {{ $paidCount }}</span>
                <span class="text-rose-400 font-bold">پووچەڵ: {{ $cancelledCount }}</span>
            </div>
        </div>

        <!-- Card 2: Total Rasm -->
        <div class="bg-slate-800/80 border border-slate-700/80 p-4 rounded-2xl shadow-lg">
            <span class="text-[11px] font-bold text-slate-400 block mb-1">کۆی رەسم (ساڵانە)</span>
            <div class="text-xl font-black text-sky-400 font-mono">{{ number_format($totalRasm) }}</div>
            <span class="text-[10px] text-slate-500 block mt-2">دیناری عێراقی</span>
        </div>

        <!-- Card 3: Total Pul -->
        <div class="bg-slate-800/80 border border-slate-700/80 p-4 rounded-2xl shadow-lg">
            <span class="text-[11px] font-bold text-slate-400 block mb-1">کۆی پوول</span>
            <div class="text-xl font-black text-indigo-400 font-mono">{{ number_format($totalPul) }}</div>
            <span class="text-[10px] text-slate-500 block mt-2">دیناری عێراقی</span>
        </div>

        <!-- Card 4: Total Form -->
        <div class="bg-slate-800/80 border border-slate-700/80 p-4 rounded-2xl shadow-lg">
            <span class="text-[11px] font-bold text-slate-400 block mb-1">کۆی فۆرم</span>
            <div class="text-xl font-black text-violet-400 font-mono">{{ number_format($totalForm) }}</div>
            <span class="text-[10px] text-slate-500 block mt-2">دیناری عێراقی</span>
        </div>

        <!-- Card 5: Total Dahat -->
        <div class="bg-slate-800/80 border border-slate-700/80 p-4 rounded-2xl shadow-lg">
            <span class="text-[11px] font-bold text-amber-400 block mb-1">کۆی داهات (ڕەسم+پوول+فۆرم)</span>
            <div class="text-xl font-black text-amber-300 font-mono">{{ number_format($totalDahat) }}</div>
            <span class="text-[10px] text-amber-500/80 block mt-2">داهاتی فەرمی گومرگ</span>
        </div>

        <!-- Card 6: Inspection / Amanat -->
        <div class="bg-slate-800/80 border border-slate-700/80 p-4 rounded-2xl shadow-lg">
            <span class="text-[11px] font-bold text-teal-400 block mb-1">اجورکشف / امانات</span>
            <div class="text-xl font-black text-teal-300 font-mono">{{ number_format($totalInspection) }}</div>
            <span class="text-[10px] text-teal-500/80 block mt-2">امانات پشکنین</span>
        </div>
    </div>

    <!-- Grand Total Highlight Banner -->
    <div class="bg-gradient-to-r from-amber-950/60 via-slate-800/90 to-amber-950/60 border border-amber-500/40 p-4 sm:p-5 rounded-2xl shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 shrink-0">
                <i class="fa-solid fa-coins text-2xl"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-amber-300 block">کۆی گشتی بە نووسین (Tafqeet):</span>
                <span class="text-sm md:text-base font-extrabold text-white leading-tight">
                    {{ $grandTotalWords }}
                </span>
            </div>
        </div>
        <div class="text-left bg-slate-900/90 border border-amber-500/30 px-5 py-2.5 rounded-xl">
            <span class="text-[11px] font-semibold text-slate-400 block">کۆی گشتی بە ژمارە:</span>
            <span class="text-2xl font-black text-amber-400 font-mono tracking-wide">
                {{ number_format($grandTotal) }} <span class="text-xs font-normal text-amber-200">د.ع</span>
            </span>
        </div>
    </div>

    <!-- Transactions Accounting Table -->
    <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden backdrop-blur">
        <div class="p-4 border-b border-slate-700/80 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-list-ol text-amber-400"></i>
                <h2 class="text-base font-bold text-white">خشتەی وەسڵەکانی محاسبة ٦٦</h2>
                <span class="text-xs bg-slate-900 text-slate-300 px-2.5 py-1 rounded-lg border border-slate-700 font-mono">
                    {{ $transactions->count() }} مامەڵە
                </span>
            </div>
            <div class="text-xs text-slate-400 flex items-center gap-3">
                <span>لە وەسڵی: <strong class="text-amber-300 font-mono">{{ $receiptFrom ?? '-' }}</strong></span>
                <span>بۆ وەسڵی: <strong class="text-amber-300 font-mono">{{ $receiptTo ?? '-' }}</strong></span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-900/90 text-slate-300 font-bold border-b border-slate-700">
                    <tr>
                        <th class="py-3 px-3 text-center w-10">ز</th>
                        <th class="py-3 px-3">ناوی لێوەوەرگیراو</th>
                        <th class="py-3 px-3 text-center font-mono">ژ. پسولە</th>
                        <th class="py-3 px-3">جۆری مامەڵە</th>
                        <th class="py-3 px-3 text-center">بڕی ساڵ</th>
                        <th class="py-3 px-3 text-center font-mono">بەروار</th>
                        <th class="py-3 px-3 text-center font-mono">رەسم</th>
                        <th class="py-3 px-3 text-center font-mono">اجور</th>
                        <th class="py-3 px-3 text-center font-mono">پول</th>
                        <th class="py-3 px-3 text-center font-mono">فۆرم</th>
                        <th class="py-3 px-3 text-center font-mono">کۆی گشتی</th>
                        <th class="py-3 px-3 text-center">دۆخ</th>
                        <th class="py-3 px-3 text-center">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($transactions as $index => $tx)
                        @php
                            $isCanc = $tx->is_cancelled || !empty($tx->cancelled_at);
                        @endphp
                        <tr class="hover:bg-slate-700/30 transition {{ $isCanc ? 'bg-rose-950/20 text-slate-400' : 'text-slate-200' }}">
                            <td class="py-3 px-3 text-center font-mono font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3 px-3 font-semibold text-white">
                                <a href="{{ route('transactions.show', $tx->id) }}" class="hover:text-sky-400 transition">
                                    {{ $tx->visitor_name }}
                                </a>
                                @if($tx->plate_number)
                                    <span class="block text-[10px] text-slate-400 font-mono">{{ $tx->plate_number }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center font-mono font-black {{ $isCanc ? 'line-through text-rose-400' : 'text-amber-300' }}">
                                {{ $tx->receipt_37a_number ?? '-' }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-md bg-slate-900 border border-slate-700 text-[11px]">
                                    {{ $tx->transactionType->name_kurdish ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center font-mono">
                                {{ $isCanc ? '0' : ($tx->num_years ?? 1) }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-[11px] text-slate-400">
                                {{ $tx->display_date }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono {{ $isCanc ? 'text-slate-500' : 'text-slate-200' }}">
                                {{ $isCanc ? '0' : number_format($tx->pay_amount_years) }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono {{ $isCanc ? 'text-slate-500' : 'text-teal-400' }}">
                                {{ $isCanc ? '0' : number_format($tx->pay_inspection) }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono {{ $isCanc ? 'text-slate-500' : 'text-indigo-400' }}">
                                {{ $isCanc ? '0' : number_format($tx->pay_stamp) }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono {{ $isCanc ? 'text-slate-500' : 'text-violet-400' }}">
                                {{ $isCanc ? '0' : number_format($tx->pay_form) }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono font-bold {{ $isCanc ? 'text-rose-400 line-through' : 'text-amber-400' }}">
                                {{ $isCanc ? '0' : number_format($tx->total_pay) }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($isCanc)
                                    <span class="px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30 text-[10px] font-bold">
                                        پووچەڵکراوەتەوە
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold">
                                        پارەدراوە
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('transactions.show', $tx->id) }}" title="بینینی مامەڵە"
                                       class="p-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white transition">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('transactions.print_payment_receipt', $tx->id) }}" target="_blank" title="چاپی پسوولە"
                                       class="p-1.5 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-300 hover:text-white transition">
                                        <i class="fa-solid fa-receipt text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="py-12 text-center text-slate-400">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500">
                                    <i class="fa-solid fa-receipt text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-300 mb-1">هیچ پسوولەیەک بەم مەرجانە نەدۆزرایەوە</h3>
                                <p class="text-xs text-slate-500">تکایە فلتەرەکانت بگۆڕە یان مەودای ژمارەی پسولە و بەروار بەرفراوانتر بکە.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
