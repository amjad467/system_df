@extends('layouts.app')

@section('content')
@php
    $txRates = $transactionTypes->mapWithKeys(function($t) {
        return [$t->id => [
            'pay_1_year' => (float)$t->pay_1_year,
            'pay_2_year' => (float)$t->pay_2_year,
            'pay_3_year' => (float)$t->pay_3_year,
            'stamp_pay' => (float)$t->stamp_pay,
            'form_pay' => (float)$t->form_pay,
            'fine_pay' => (float)$t->fine_pay,
            'inspection_pay' => (float)$t->inspection_pay,
            'bus_inspection_pay' => (float)$t->bus_inspection_pay,
        ]];
    });
@endphp

<div x-data="transactionForm({{ \Illuminate\Support\Js::from($fromTransaction) }}, {{ $targetTypeId ? (int)$targetTypeId : 'null' }}, {{ \Illuminate\Support\Js::from($txRates) }})" 
     @keydown.window.alt.s.prevent="submitForm()"
     class="max-w-5xl mx-auto space-y-6 pb-28">

    <!-- Top Navigation & Action Header -->
    <div class="bg-slate-900/90 backdrop-blur-md border border-slate-700/60 rounded-2xl p-4 sm:p-5 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 via-indigo-600 to-sky-400 flex items-center justify-center shadow-lg shadow-sky-500/20 shrink-0 text-white text-xl">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-lg sm:text-xl font-black text-white">تۆمارکردنی مامەڵەی نوێ</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-500/15 text-sky-400 border border-sky-500/30 font-sans" x-text="getTypeName()"></span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">سیستەمی تۆمارکردنی دەفتەری گەشتیاری و پسوولەی فەرمی گومرگ</p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <!-- View Mode Switcher: Wizard (Step-by-step) vs Full Form -->
            <div class="bg-slate-950 border border-slate-700/80 rounded-xl p-1 flex items-center gap-1 text-xs font-bold">
                <button type="button" 
                        @click="formViewMode = 'wizard'" 
                        :class="formViewMode === 'wizard' ? 'bg-sky-600 text-white shadow' : 'text-slate-400 hover:text-slate-200'" 
                        class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer"
                        title="بینینی فۆڕمەکە بە هەنگاوە زیرەکەکان بۆ کارکردنی ئاسان و بێ هەڵە">
                    <i class="fa-solid fa-wand-magic-sparkles text-[11px]"></i>
                    <span class="hidden sm:inline">هەنگاو بە هەنگاو</span>
                    <span class="sm:hidden">هەنگاو</span>
                </button>
                <button type="button" 
                        @click="formViewMode = 'all'" 
                        :class="formViewMode === 'all' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-slate-200'" 
                        class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer"
                        title="بینینی هەموو بەشەکانی فۆڕمەکە بەیەکەوە لەسەر یەک پەڕە">
                    <i class="fa-solid fa-list-check text-[11px]"></i>
                    <span class="hidden sm:inline">پەڕەی تەواو</span>
                    <span class="sm:hidden">تەواو</span>
                </button>
            </div>

            <!-- Shortcut Pill -->
            <span class="hidden xl:flex items-center gap-1 text-[11px] text-slate-400 bg-slate-800/80 border border-slate-700 px-2.5 py-1.5 rounded-xl font-mono">
                <kbd class="text-sky-300 font-bold">Alt + S</kbd> پاشەکەوتکردن
            </span>

            <a href="{{ auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver') ? route('transactions.index') : route('dashboard') }}" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white text-xs font-semibold rounded-xl border border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-arrow-right text-[11px]"></i>
                <span>گەڕانەوە</span>
            </a>
        </div>
    </div>

    <!-- Interactive Stepper Progress Bar (Works both as click-to-jump in Full mode, and step-switcher in Wizard mode) -->
    <div class="bg-slate-900/90 backdrop-blur-md border border-slate-700/60 rounded-2xl p-3 sm:p-4 shadow-xl">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
            <!-- Step 1 -->
            <button type="button" 
                    @click="setStep(1)" 
                    :class="currentStep === 1 ? 'border-sky-500 bg-sky-950/40 text-sky-300 shadow-lg shadow-sky-500/10' : (currentStep > 1 ? 'border-emerald-500/40 bg-emerald-950/20 text-emerald-300' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700')" 
                    class="p-2.5 sm:p-3 rounded-xl border transition text-right flex items-center gap-2.5 group cursor-pointer">
                <div :class="currentStep === 1 ? 'bg-sky-500 text-white' : (currentStep > 1 ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-slate-400 group-hover:bg-slate-700')" 
                     class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 transition">
                    <span x-show="currentStep <= 1">١</span>
                    <i x-show="currentStep > 1" class="fa-solid fa-check text-xs"></i>
                </div>
                <div class="min-w-0">
                    <span class="block text-xs font-extrabold truncate">جۆری مامەڵە & گەڕان</span>
                    <span class="text-[10px] text-slate-400 block truncate" x-text="getTypeName()"></span>
                </div>
            </button>

            <!-- Step 2 -->
            <button type="button" 
                    @click="setStep(2)" 
                    :class="currentStep === 2 ? 'border-sky-500 bg-sky-950/40 text-sky-300 shadow-lg shadow-sky-500/10' : (currentStep > 2 ? 'border-emerald-500/40 bg-emerald-950/20 text-emerald-300' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700')" 
                    class="p-2.5 sm:p-3 rounded-xl border transition text-right flex items-center gap-2.5 group cursor-pointer">
                <div :class="currentStep === 2 ? 'bg-sky-500 text-white' : (currentStep > 2 ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-slate-400 group-hover:bg-slate-700')" 
                     class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 transition">
                    <span x-show="currentStep <= 2">٢</span>
                    <i x-show="currentStep > 2" class="fa-solid fa-check text-xs"></i>
                </div>
                <div class="min-w-0">
                    <span class="block text-xs font-extrabold truncate">شۆفێر & پاسپۆرت</span>
                    <span class="text-[10px] text-slate-400 block truncate" x-text="visitorName ? visitorName : 'ناوی هاووڵاتی'"></span>
                </div>
            </button>

            <!-- Step 3 -->
            <button type="button" 
                    @click="setStep(3)" 
                    :class="currentStep === 3 ? 'border-sky-500 bg-sky-950/40 text-sky-300 shadow-lg shadow-sky-500/10' : (currentStep > 3 ? 'border-emerald-500/40 bg-emerald-950/20 text-emerald-300' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700')" 
                    class="p-2.5 sm:p-3 rounded-xl border transition text-right flex items-center gap-2.5 group cursor-pointer">
                <div :class="currentStep === 3 ? 'bg-sky-500 text-white' : (currentStep > 3 ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-slate-400 group-hover:bg-slate-700')" 
                     class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 transition">
                    <span x-show="currentStep <= 3">٣</span>
                    <i x-show="currentStep > 3" class="fa-solid fa-check text-xs"></i>
                </div>
                <div class="min-w-0">
                    <span class="block text-xs font-extrabold truncate">ئۆتۆمبێل & تابلۆ</span>
                    <span class="text-[10px] text-slate-400 block truncate" x-text="plateNumber ? plateNumber : 'تابلۆ و مارکە'"></span>
                </div>
            </button>

            <!-- Step 4 -->
            <button type="button" 
                    @click="setStep(4)" 
                    :class="currentStep === 4 ? 'border-emerald-500 bg-emerald-950/40 text-emerald-300 shadow-lg shadow-emerald-500/10' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700')" 
                    class="p-2.5 sm:p-3 rounded-xl border transition text-right flex items-center gap-2.5 group cursor-pointer">
                <div :class="currentStep === 4 ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-slate-400 group-hover:bg-slate-700'" 
                     class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 transition">
                    ٤
                </div>
                <div class="min-w-0">
                    <span class="block text-xs font-extrabold truncate">ماوە & بڕی پارە</span>
                    <span class="text-[10px] text-emerald-400 font-mono font-bold block truncate" x-text="Number(fees.total_pay).toLocaleString() + ' د.ع'"></span>
                </div>
            </button>
        </div>

        <!-- Progress Percentage Line in Step Wizard Mode -->
        <div x-show="formViewMode === 'wizard'" class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
            <div class="bg-gradient-to-r from-sky-500 to-emerald-400 h-full transition-all duration-300 rounded-full" 
                 :style="'width: ' + (currentStep * 25) + '%'"></div>
        </div>
    </div>

    <!-- Main Registration Form -->
    <form id="mainTransactionForm" action="/transactions" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="parent_transaction_id" :value="parentTransactionId">

        <!-- ==========================================
             SECTION 1: TRANSACTION TYPE & INTELLIGENCE SEARCH
             ========================================== -->
        <div id="section_step_1" 
             x-show="formViewMode === 'all' || currentStep === 1" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
            
            <!-- 1.1 Quick Visual Transaction Type Selector -->
            <div class="bg-slate-900/90 backdrop-blur-md border border-slate-700/60 rounded-2xl p-5 shadow-xl space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <label class="text-sm font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                        <i class="fa-solid fa-layer-group text-sky-400"></i>
                        <span>١. جۆری مامەڵە دیاری بکە:</span>
                    </label>
                    <span class="text-[11px] text-slate-400">کلیک بکە بۆ گۆڕینی ڕاستەوخۆ</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5">
                    @foreach($transactionTypes as $tt)
                        <button type="button" 
                                @click="typeId = {{ $tt->id }}; calculateFees();" 
                                :class="typeId == {{ $tt->id }} ? 'bg-gradient-to-r from-sky-600 to-indigo-600 text-white border-sky-400 shadow-lg shadow-sky-600/30 ring-2 ring-sky-400/50 font-black' : 'bg-slate-950/70 text-slate-300 border-slate-700/80 hover:bg-slate-800 hover:text-white'" 
                                class="p-3 rounded-xl border text-right transition flex items-center gap-2.5 relative group cursor-pointer">
                            <div :class="typeId == {{ $tt->id }} ? 'bg-white/20 text-white' : 'bg-slate-800 text-sky-400 group-hover:bg-slate-700'"
                                 class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm transition">
                                <i class="fa-solid {{ $tt->id == 1 ? 'fa-book' : ($tt->id == 2 ? 'fa-rotate-right' : ($tt->id == 3 ? 'fa-pen-to-square' : ($tt->id == 4 ? 'fa-id-card' : ($tt->id == 5 ? 'fa-ban' : ($tt->id == 6 ? 'fa-handshake' : ($tt->id == 7 ? 'fa-arrows-rotate' : 'fa-magnifying-glass')))))) }}"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-xs font-bold truncate">{{ $tt->name_kurdish }}</span>
                                <span class="text-[10px] text-slate-400 block truncate" x-show="typeId == {{ $tt->id }} && {{ $tt->id }} == 1">دەرهێنان + بەڵێننامە</span>
                                <span class="text-[10px] text-slate-400 block truncate" x-show="typeId == {{ $tt->id }} && {{ $tt->id }} != 1">هەڵبژێردراوە</span>
                            </div>
                            <span x-show="typeId == {{ $tt->id }}" class="absolute top-2 left-2 text-white text-[10px]">
                                <i class="fa-solid fa-circle-check"></i>
                            </span>
                        </button>
                    @endforeach
                </div>

                <!-- Dual Issuance Notice for Booklet -->
                <div x-show="typeId == 1" x-cloak class="p-3 bg-sky-950/40 border border-sky-500/40 rounded-xl flex items-start gap-3 text-xs text-sky-200">
                    <i class="fa-solid fa-wand-magic-sparkles text-sky-400 text-base mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="text-sky-300 block mb-0.5 font-extrabold">ئاسانکاری خۆکاری دەرهێنانی دەفتەر:</strong>
                        <span>سیستەم بە شێوەیەکی ئۆتۆماتیکی مامەڵەیەکی بەستراوەی <strong>بەڵێننامە</strong>ش بە بارکۆدی تایبەت دروست دەکات، و هەردوو فیشەکە بە یەک کلیک چاپ دەکرێن.</span>
                    </div>
                </div>
            </div>

            <!-- 1.2 Smart Intelligence Previous Lookup Card -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-indigo-950/70 border border-indigo-500/40 rounded-2xl p-5 shadow-xl space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-indigo-300 flex items-center gap-2">
                            <i class="fa-solid fa-bolt-lightning text-amber-400"></i>
                            <span>گەڕانی زیرەک بۆ بەکارهێنانەوەی زانیاری پێشوو (ئارەزوومەندانە):</span>
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            ئەگەر ئۆتۆمبێلەکە پێشتر هاتووە، بە ژمارەی تابلۆ، شاسی، یان ناوی خاوەنەکەی بگەڕێ تا هەموو فۆڕمەکە خۆکار پڕبێتەوە.
                        </p>
                    </div>

                    <div x-show="parentTransactionId" x-cloak class="flex items-center gap-2">
                        <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-300 text-[11px] font-bold rounded-lg border border-emerald-500/40 flex items-center gap-1 font-mono">
                            <i class="fa-solid fa-link text-[10px]"></i> بەستراوەتەوە
                        </span>
                        <button type="button" @click="clearPrevious()" class="px-2.5 py-1 bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 text-[11px] font-bold rounded-lg border border-rose-500/40 transition cursor-pointer">
                            لابردن
                        </button>
                    </div>
                </div>

                <!-- Search Input with Dropdown Autocomplete -->
                <div class="relative">
                    <div class="relative">
                        <input type="text" 
                               x-model="searchQuery" 
                               @input.debounce.300ms="searchPrevious()" 
                               placeholder="بۆ هێنانی زانیاری پێشوو لێرە بنووسە (تابلۆ، شاسی، ناو، دەفتەر، بارکۆد)..." 
                               class="w-full bg-slate-950 border border-indigo-500/50 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/30 rounded-xl pr-10 pl-10 py-2.5 text-xs text-slate-100 placeholder-slate-500 font-mono tracking-wide transition">
                        <i class="fa-solid fa-magnifying-glass absolute right-3.5 top-3 text-slate-400 text-xs"></i>
                        <span x-show="isSearching" class="absolute left-3 top-2.5 text-xs text-indigo-400">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </span>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div x-show="showResults && searchResults.length > 0" 
                         @click.away="showResults = false" 
                         x-cloak
                         class="absolute z-50 left-0 right-0 mt-2 bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl max-h-80 overflow-y-auto divide-y divide-slate-800">
                        <template x-for="item in searchResults" :key="item.id">
                            <div class="p-3 hover:bg-slate-800/80 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-slate-100 text-xs" x-text="item.visitor_name"></span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-950 text-emerald-400 border border-emerald-500/40" x-text="item.plate_number"></span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400" x-text="item.transaction_type ? item.transaction_type.name_kurdish : ''"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 flex flex-wrap items-center gap-3 font-mono">
                                        <span x-show="item.booklet_number" x-text="'دەفتەر: ' + item.booklet_number" class="text-amber-300"></span>
                                        <span x-show="item.chassis_number" x-text="'شاسی: ' + item.chassis_number"></span>
                                        <span x-show="item.car_make" x-text="'مارکە: ' + (item.car_make.name_kurdish || '')"></span>
                                        <span x-show="item.end_date" x-text="'بەسەرچوون: ' + (item.end_date ? item.end_date.split('T')[0] : '')" class="text-rose-400"></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" @click="populateFromPrevious(item, 2)" class="px-2.5 py-1.5 bg-sky-600 hover:bg-sky-500 text-white text-[11px] font-bold rounded-lg transition shadow cursor-pointer">
                                        <i class="fa-solid fa-rotate-right ml-1"></i> تازەکردنەوە
                                    </button>
                                    <button type="button" @click="populateFromPrevious(item, 5)" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white text-[11px] font-bold rounded-lg transition shadow cursor-pointer">
                                        <i class="fa-solid fa-ban ml-1"></i> پووچەڵکردنەوە
                                    </button>
                                    <button type="button" @click="populateFromPrevious(item)" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-slate-100 text-[11px] font-bold rounded-lg transition cursor-pointer">
                                        پڕکردنەوە
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty Notice -->
                    <div x-show="showResults && searchResults.length === 0 && !isSearching" 
                         x-cloak
                         class="absolute z-50 left-0 right-0 mt-2 bg-slate-900 border border-slate-700 rounded-xl p-4 text-center text-xs text-slate-400 shadow-xl">
                        هیچ مامەڵەیەکی پێشوو نەدۆزرایەوە بەم نووسینە. دەتوانیت زانیارییەکان لە خوارەوە داخڵ بکەیت.
                    </div>
                </div>

                <!-- Banner when selected -->
                <div x-show="parentTransactionId" x-cloak class="p-3 bg-emerald-950/30 border border-emerald-500/40 rounded-xl flex items-center justify-between text-xs text-emerald-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        <span>زانیاری پێشوو دیاریکرا: <strong class="text-white font-mono" x-text="parentTransactionSummary"></strong></span>
                    </div>
                    <span class="text-[10px] text-slate-400">دەتوانیت هەر فیلدێک پێویست بێت لە هەنگاوەکانی داهاتوو دەستکاری بکەیت.</span>
                </div>
            </div>

            <!-- 1.3 Directorate & Official Officer Selection -->
            <div class="bg-slate-900/90 backdrop-blur-md border border-slate-700/60 rounded-2xl p-5 shadow-xl space-y-4">
                <h3 class="text-xs font-bold text-slate-200 flex items-center justify-between border-b border-slate-800 pb-2.5">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-building-shield text-sky-400"></i>
                        <span>بەڕێوەبەرایەتی هاتوچۆ و بەرپرسی واژۆکەر:</span>
                    </span>
                    <span class="text-[10px] text-slate-400">(لە چاپ دەردەکەون)</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Directorate Dropdown -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-slate-300">بەڕێوەبەرایەتی هاتوچۆ *</label>
                            <button type="button" @click="showDirectorateModal = true" class="text-[11px] text-sky-400 hover:text-sky-300 font-bold flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-plus-circle"></i> نوێ
                            </button>
                        </div>
                        <select name="traffic_directorate_id" x-model="directorateId" class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none">
                            @foreach($directorates as $dir)
                                <option value="{{ $dir->id }}">{{ $dir->name_kurdish }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Director Dropdown -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-slate-300">بەڕێوەبەر / لێپرسراوی گومرگ *</label>
                            <button type="button" @click="showDirectorModal = true" class="text-[11px] text-sky-400 hover:text-sky-300 font-bold flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-plus-circle"></i> نوێ
                            </button>
                        </div>
                        <select name="director_id" x-model="directorId" class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none">
                            @foreach($directors as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Transaction Type Select Backup / Sync -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-slate-300">جۆری مامەڵە (لیست)</label>
                            <button type="button" @click="showTxTypeModal = true" class="text-[11px] text-sky-400 hover:text-sky-300 font-bold flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-plus-circle"></i> نوێ
                            </button>
                        </div>
                        <select name="transaction_type_id" x-model="typeId" @change="calculateFees()" class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none">
                            @foreach($transactionTypes as $tt)
                                <option value="{{ $tt->id }}">{{ $tt->name_kurdish }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 1 Next Button in Wizard Mode -->
            <div x-show="formViewMode === 'wizard'" class="flex justify-end pt-2">
                <button type="button" @click="setStep(2)" class="px-6 py-2.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-sky-600/20 transition flex items-center gap-2 cursor-pointer">
                    <span>هەنگاوی دواتر: زانیاری شۆفێر</span>
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                </button>
            </div>
        </div>

        <!-- ==========================================
             SECTION 2: DRIVER & CITIZEN IDENTITY
             ========================================== -->
        <div id="section_step_2" 
             x-show="formViewMode === 'all' || currentStep === 2" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
            
            <div class="bg-slate-900/90 backdrop-blur-md border border-slate-700/60 rounded-2xl p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <i class="fa-solid fa-user-check text-emerald-400"></i>
                        <span>٢. زانیارییەکانی شۆفێر و ناسنامەی هاووڵاتی:</span>
                    </h3>
                    <span class="text-[10px] text-slate-400">فیلدەکانی ئەستێرەدار (*) پڕکردنەوەیان پێویستە</span>
                </div>

                <!-- Spotlight: Chassis VIN & Quick Check (Top of Citizen section) -->
                <div class="p-4 bg-slate-950/80 border-2 border-sky-500/40 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black text-sky-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-barcode text-sm"></i>
                            <span>ژمارەی شاسی ئۆتۆمبێل (Chassis VIN) *</span>
                            <span class="text-[10px] text-sky-300/80 font-normal">(پشکنینی خێرا)</span>
                        </label>
                        <span x-show="isCheckingChassis" x-cloak class="text-[11px] text-sky-400 flex items-center gap-1 font-bold animate-pulse font-mono">
                            <i class="fa-solid fa-spinner fa-spin"></i> پشکنینی شاسی...
                        </span>
                    </div>

                    <input type="text" 
                           name="chassis_number" 
                           x-model="chassisNumber" 
                           @input="onChassisInput($event)" 
                           @change="lookupByChassis()" 
                           @blur="lookupByChassis()" 
                           @keydown.enter.prevent="lookupByChassis(); $refs.phoneInput ? $refs.phoneInput.focus() : null;" 
                           required 
                           autofocus
                           data-english-field="true"
                           placeholder="17-DIGIT CHASSIS VIN NUMBER" 
                           dir="ltr"
                           lang="en"
                           style="text-transform: uppercase;"
                           class="w-full bg-slate-900 border border-sky-500/60 focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 rounded-xl px-3.5 py-2.5 text-sm text-slate-100 font-mono font-bold tracking-widest uppercase transition">

                    <div x-show="chassisFound && chassisPreviousInfo" x-cloak class="p-2 bg-emerald-950/80 border border-emerald-500/50 rounded-lg text-xs text-emerald-300 flex items-center justify-between">
                        <span class="flex items-center gap-1.5 font-sans">
                            <i class="fa-solid fa-circle-check text-emerald-400"></i>
                            <span>ئەم شاسییە پێشتر تۆمارکراوە و زانیارییەکانی خۆکار پڕکرانەوە.</span>
                        </span>
                        <button type="button" @click="clearChassisAutoFill()" class="text-rose-400 hover:text-rose-300 font-bold underline text-[11px] transition cursor-pointer">
                            پاککردنەوە
                        </button>
                    </div>
                </div>

                <!-- Citizen Credentials Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
                    <!-- Phone Number -->
                    <div>
                        <label class="block text-xs font-bold text-emerald-400 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-phone text-xs"></i>
                            <span>ژمارەی مۆبایل *</span>
                        </label>
                        <input type="text" 
                               name="phone_number" 
                               x-ref="phoneInput" 
                               x-model="phoneNumber" 
                               required
                               placeholder="0770 123 4567" 
                               dir="ltr" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono tracking-wider transition">
                    </div>

                    <!-- First Driver Name (Kurdish) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-200">ناوی شۆفێری یەکەم (کوردی) *</label>
                            <button type="button" @click="toggleArabicLayout()" class="text-[10px] text-sky-400 hover:text-sky-300 bg-sky-500/10 border border-sky-500/30 px-2 py-0.5 rounded transition cursor-pointer">
                                <i class="fa-solid fa-keyboard"></i>
                                <span x-text="arabicInputMode === 'ar101' ? 'عەرەبی ١٠١' : 'فۆنەتیک'"></span>
                            </button>
                        </div>
                        <input type="text" 
                               name="visitor_name" 
                               x-model="visitorName" 
                               @input="onKurdishInput($event, 'visitorName')" 
                               @blur="if ((!visitorNameEng || visitorNameEng.trim() === '') && visitorName) { autoFillEnglishName('visitorName', 'visitorNameEng'); }"
                               required 
                               placeholder="ناوی سێیانی شۆفێری یەکەم بە کوردی" 
                               dir="rtl" 
                               lang="ckb" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 font-sans transition">
                    </div>

                    <!-- First Driver Name (English Standard) -->
                    <div x-show="isFullBookletForm()">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-amber-400">ناوی ئینگلیزی (English)</label>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="autoFillEnglishName('visitorName', 'visitorNameEng')" class="text-[10px] text-amber-400 hover:text-amber-300 font-bold underline flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> وەرگێڕان
                                </button>
                                <span class="text-[9px] text-amber-400/90 font-mono font-bold bg-amber-500/10 px-1.5 py-0.5 rounded">CAPITAL</span>
                            </div>
                        </div>
                        <input type="text" 
                               name="visitor_name_eng" 
                               x-model="visitorNameEng" 
                               @focus="if ((!visitorNameEng || visitorNameEng.trim() === '') && visitorName) { autoFillEnglishName('visitorName', 'visitorNameEng'); }"
                               @input="onEnglishInput($event, 'visitorNameEng')" 
                               data-english-field="true"
                               placeholder="FULL NAME IN ENGLISH (CAPITAL)" 
                               dir="ltr" 
                               lang="en" 
                               style="text-transform: uppercase;" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 rounded-xl px-3 py-2 text-xs text-slate-100 uppercase font-mono tracking-wider transition">
                    </div>

                    <!-- Second Driver Name (Kurdish) -->
                    <div x-show="isFullBookletForm()">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-300">ناوی شۆفێری دووەم (ئارەزوومەندانە)</label>
                            <span class="text-[10px] text-slate-500">کوردی</span>
                        </div>
                        <input type="text" 
                               name="second_driver_name" 
                               x-model="secondDriverName" 
                               @input="onKurdishInput($event, 'secondDriverName')" 
                               @blur="if ((!secondDriverNameEng || secondDriverNameEng.trim() === '') && secondDriverName) { autoFillEnglishName('secondDriverName', 'secondDriverNameEng'); }"
                               placeholder="ناوی شۆفێری دووەم بە کوردی" 
                               dir="rtl" 
                               lang="ckb" 
                               class="w-full bg-slate-950/60 border border-slate-700/60 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 font-sans transition">
                    </div>

                    <!-- Second Driver Name (English) -->
                    <div x-show="isFullBookletForm()">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-300">شۆفێری دووەم بە ئینگلیزی</label>
                            <button type="button" @click="autoFillEnglishName('secondDriverName', 'secondDriverNameEng')" class="text-[10px] text-amber-400 hover:text-amber-300 font-bold underline flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> وەرگێڕان
                            </button>
                        </div>
                        <input type="text" 
                               name="second_driver_name_eng" 
                               x-model="secondDriverNameEng" 
                               @focus="if ((!secondDriverNameEng || secondDriverNameEng.trim() === '') && secondDriverName) { autoFillEnglishName('secondDriverName', 'secondDriverNameEng'); }"
                               @input="onEnglishInput($event, 'secondDriverNameEng')" 
                               data-english-field="true"
                               placeholder="SECOND DRIVER IN ENGLISH (CAPITAL)" 
                               dir="ltr" 
                               lang="en" 
                               style="text-transform: uppercase;" 
                               class="w-full bg-slate-950/60 border border-slate-700/60 focus:border-amber-400 rounded-xl px-3 py-2 text-xs text-slate-100 uppercase font-mono tracking-wider transition">
                    </div>

                    <!-- Passport Number -->
                    <div x-show="isFullBookletForm()" x-cloak>
                        <label class="block text-xs font-bold text-amber-400 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-passport text-xs"></i>
                            <span>ژمارەی پاسپۆرت (Passport No.)</span>
                        </label>
                        <input type="text" 
                               name="passport_number" 
                               x-model="passportNumber" 
                               @input="onEnglishInput($event, 'passportNumber')" 
                               data-english-field="true"
                               placeholder="مثلاً: A12345678" 
                               dir="ltr" 
                               lang="en"
                               style="text-transform: uppercase;" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono tracking-wider transition">
                    </div>

                    <!-- Passport Issue Date -->
                    <div x-show="isFullBookletForm()" x-cloak>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-calendar-day text-xs text-amber-400"></i>
                            <span>بەرواری دەرچوونی پاسپۆرت</span>
                        </label>
                        <input type="date" 
                               name="passport_issue_date" 
                               x-model="passportIssueDate" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-amber-400 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono transition">
                    </div>

                    <!-- Passport Issue Place -->
                    <div x-show="isFullBookletForm()" x-cloak>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-300 flex items-center gap-1.5">
                                <i class="fa-solid fa-map-location-dot text-xs text-sky-400"></i>
                                <span>شوێنی دەرچوونی پاسپۆرت</span>
                            </label>
                            <button type="button" @click="showPassportPlaceModal = true" class="text-[11px] text-sky-400 hover:text-sky-300 font-bold flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-plus-circle"></i> شوێنی نوێ
                            </button>
                        </div>
                        <select name="passport_issue_place" x-model="passportIssuePlace" id="passportIssuePlaceSelect" class="w-full bg-slate-950 border border-slate-700 focus:border-sky-400 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none">
                            <option value="">-- هەڵبژاردنی شوێن --</option>
                            @foreach($passportIssuePlaces as $place)
                                <option value="{{ $place->name }}">{{ $place->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Address -->
                    <div x-show="isFullBookletForm()" x-cloak>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-house-chimney text-xs text-sky-400"></i>
                            <span>ناونیشانی نیشتەجێبوون</span>
                        </label>
                        <input type="text" 
                               name="address" 
                               x-model="address" 
                               placeholder="شار، گەڕەک، یان ناونیشان..." 
                               dir="rtl" 
                               lang="ckb" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-sky-400 rounded-xl px-3 py-2 text-xs text-slate-100 font-sans transition">
                    </div>
                </div>

                <!-- Customs Cancellation Letter Details (Only if Cancellation) -->
                <div x-show="isCancellation()" x-cloak class="p-4 bg-rose-950/40 border border-rose-500/40 rounded-xl space-y-3">
                    <div class="flex items-center justify-between border-b border-rose-500/30 pb-2">
                        <span class="text-xs font-bold text-rose-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-file-contract text-rose-400"></i>
                            <span>زانیاری نووسراوی گومرگ (تایبەت بە پووچەڵکردنەوە)</span>
                        </span>
                        <span class="text-[10px] text-rose-300 bg-rose-900/60 px-2 py-0.5 rounded">لە قۆناغی ئەرشیف دادەنرێت</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="text-rose-200 block mb-1">ژمارەی نووسراو:</label>
                            <input type="text" name="no_nusraw_puchal" disabled placeholder="لەلایەن کارمەندی ئەرشیف دادەنرێت" class="w-full bg-slate-900/50 border border-rose-700/40 rounded-xl px-3 py-2 text-xs text-slate-400 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="text-rose-200 block mb-1">بەرواری نووسراو:</label>
                            <input type="date" name="date_nusraw_puchal" disabled class="w-full bg-slate-900/50 border border-rose-700/40 rounded-xl px-3 py-2 text-xs text-slate-400 cursor-not-allowed">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2 Navigation Buttons in Wizard Mode -->
            <div x-show="formViewMode === 'wizard'" class="flex justify-between pt-2">
                <button type="button" @click="setStep(1)" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl border border-slate-700 transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                    <span>هەنگاوی پێشوو</span>
                </button>
                <button type="button" @click="setStep(3)" class="px-6 py-2.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-sky-600/20 transition flex items-center gap-2 cursor-pointer">
                    <span>هەنگاوی دواتر: ئۆتۆمبێل و تابلۆ</span>
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                </button>
            </div>
        </div>

        <!-- ==========================================
             SECTION 3: VEHICLE SPECIFICATIONS
             ========================================== -->
        <div id="section_step_3" 
             x-show="formViewMode === 'all' || currentStep === 3" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
            
            <div class="bg-slate-900/90 backdrop-blur-md border border-slate-700/60 rounded-2xl p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                        <i class="fa-solid fa-car text-sky-400"></i>
                        <span>٣. تایبەتمەندییەکانی ئۆتۆمبێل و تابلۆ:</span>
                    </h3>
                    <span class="text-[10px] text-slate-400">زانیارییەکانی دەفتەر</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Plate Number -->
                    <div>
                        <label class="block text-xs font-bold text-emerald-400 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-rectangle-ad text-xs"></i>
                            <span>ژمارەی تابلۆی ئۆتۆمبێل *</span>
                        </label>
                        <input type="text" 
                               name="plate_number" 
                               x-model="plateNumber" 
                               required 
                               placeholder="مثلاً: 22 A 12345" 
                               dir="ltr"
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono tracking-wider font-bold transition">
                    </div>

                    <!-- Plate Type -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-slate-300">جۆری تابلۆ *</label>
                            <button type="button" @click="showPlateTypeModal = true" class="text-[11px] text-sky-400 hover:text-sky-300 font-bold flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-plus-circle"></i> نوێ
                            </button>
                        </div>
                        <select name="plate_type_id" x-model="plateTypeId" @change="calculateFees()" class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none">
                            @foreach($plateTypes as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->name_kurdish }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Car Make -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-slate-300">جۆری ئۆتۆمبێل (مارکە) *</label>
                            <button type="button" @click="showCarMakeModal = true" class="text-[11px] text-sky-400 hover:text-sky-300 font-bold flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-magnifying-glass"></i> گەڕان & نوێ
                            </button>
                        </div>
                        <select name="car_make_id" x-model="carMakeId" @change="onCarMakeChange()" class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none">
                            <option value="">هەڵبژێرە...</option>
                            @foreach($carMakes as $cm)
                                <option value="{{ $cm->id }}" data-eng="{{ $cm->name_english }}">{{ $cm->name_kurdish }} ({{ $cm->name_english }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Auto English Car Make Preview -->
                    <div x-show="isFullBookletForm()">
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">مارکە بە ئینگلیزی (خۆکار)</label>
                        <input type="text" x-model="carMakeEng" readonly dir="ltr"
                               class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-3 py-2 text-xs text-sky-400 font-mono tracking-wider focus:outline-none">
                    </div>

                    <!-- Model Year -->
                    <div>
                        <label class="block text-xs font-bold text-slate-200 mb-1.5">مۆدێلی دروستکردن (ساڵ) *</label>
                        <input type="text" 
                               name="model_year" 
                               x-model="modelYear" 
                               required 
                               maxlength="4" 
                               minlength="4" 
                               pattern="\d{4}" 
                               placeholder="مثلاً: 2024" 
                               dir="ltr"
                               class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono tracking-wider transition">
                    </div>

                    <!-- Car Color -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-slate-300">ڕەنگی ئۆتۆمبێل *</label>
                            <button type="button" @click="showCarColorModal = true" class="text-[11px] text-sky-400 hover:text-sky-300 font-bold flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-plus-circle"></i> ڕەنگی نوێ
                            </button>
                        </div>
                        <select name="car_color_id" x-model="carColorId" @change="onCarColorChange()" class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none">
                            <option value="">هەڵبژێرە...</option>
                            @foreach($carColors as $cc)
                                <option value="{{ $cc->id }}" data-eng="{{ $cc->name_english }}">{{ $cc->name_kurdish }} ({{ $cc->name_english }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Auto English Color Preview -->
                    <div x-show="isFullBookletForm()">
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">ڕەنگ بە ئینگلیزی (خۆکار)</label>
                        <input type="text" x-model="carColorEng" readonly dir="ltr"
                               class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-3 py-2 text-xs text-sky-400 font-mono tracking-wider focus:outline-none">
                    </div>

                    <!-- Piston Count -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">ژمارەی بستۆن</label>
                        <input type="number" 
                               name="piston_count" 
                               x-model="pistonCount" 
                               min="1" 
                               max="16"
                               class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono transition">
                    </div>

                    <!-- Salana Number -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">ژمارەی ساڵانە (ساڵانەی فەرمی)</label>
                        <input type="text" 
                               name="salana_number" 
                               x-model="salanaNumber" 
                               placeholder="ژمارەی فەرمی ساڵانە" 
                               dir="ltr"
                               class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono transition">
                    </div>
                </div>

                <!-- Technical Conditions (Overload, Defects, General) -->
                <div x-show="isFullBookletForm()" class="pt-4 border-t border-slate-800/80 space-y-3">
                    <label class="text-xs font-bold text-slate-300 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-indigo-400"></i>
                        <span>باری تەکنیکی ئۆتۆمبێل (پشکنین):</span>
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Overload -->
                        <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-bold text-slate-200">زیادەبار</span>
                                <span class="text-[10px] text-slate-500 font-mono">OVERLOAD</span>
                            </div>
                            <select name="zedabar" x-model="zedabar" @change="onZedabarChange()" class="bg-slate-900 border border-slate-700 text-xs rounded-lg px-2.5 py-1.5 text-slate-100 font-bold focus:outline-none">
                                <option value="باشە">باشە (Good)</option>
                                <option value="باش نییە">باش نییە (NO)</option>
                            </select>
                            <input type="hidden" name="zedabar_eng" x-model="zedabarEng">
                        </div>

                        <!-- Defects -->
                        <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-bold text-slate-200">کەموکورتی</span>
                                <span class="text-[10px] text-slate-500 font-mono">DEFECTS</span>
                            </div>
                            <select name="kamukurty" x-model="kamukurty" @change="onKamukurtyChange()" class="bg-slate-900 border border-slate-700 text-xs rounded-lg px-2.5 py-1.5 text-slate-100 font-bold focus:outline-none">
                                <option value="باشە">باشە (Good)</option>
                                <option value="باش نییە">باش نییە (NO)</option>
                            </select>
                            <input type="hidden" name="kamukurty_eng" x-model="kamukurtyEng">
                        </div>

                        <!-- General -->
                        <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-bold text-slate-200">باری گشتی</span>
                                <span class="text-[10px] text-slate-500 font-mono">GENERAL</span>
                            </div>
                            <select name="bary_gshty" x-model="baryGshty" @change="onBaryGshtyChange()" class="bg-slate-900 border border-slate-700 text-xs rounded-lg px-2.5 py-1.5 text-slate-100 font-bold focus:outline-none">
                                <option value="باشە">باشە (Good)</option>
                                <option value="باش نییە">باش نییە (NO)</option>
                            </select>
                            <input type="hidden" name="bary_gshty_eng" x-model="baryGshtyEng">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3 Navigation Buttons in Wizard Mode -->
            <div x-show="formViewMode === 'wizard'" class="flex justify-between pt-2">
                <button type="button" @click="setStep(2)" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl border border-slate-700 transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                    <span>هەنگاوی پێشوو</span>
                </button>
                <button type="button" @click="setStep(4)" class="px-6 py-2.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-sky-600/20 transition flex items-center gap-2 cursor-pointer">
                    <span>هەنگاوی دواتر: ماوە و بڕی پارە</span>
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                </button>
            </div>
        </div>

        <!-- ==========================================
             SECTION 4: DURATION, DATES & LIVE FEES BREAKDOWN
             ========================================== -->
        <div id="section_step_4" 
             x-show="formViewMode === 'all' || currentStep === 4" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
            
            <!-- 4.1 Duration & Dates Card -->
            <div x-show="isFullBookletForm()" class="bg-slate-900/90 backdrop-blur-md border border-slate-700/60 rounded-2xl p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <i class="fa-solid fa-calendar-days text-amber-400"></i>
                        <span>٤. ماوەی بەسەرچوونی دەفتەر و بەروارەکان:</span>
                    </h3>
                    <span class="text-[10px] text-slate-400">بەروارەکان خۆکار هەژمار دەکرێن</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Duration (Years) Selector Buttons -->
                    <div>
                        <label class="block text-xs font-bold text-sky-300 mb-1.5">ماوەی دەفتەر (ساڵ)</label>
                        <div class="grid grid-cols-5 gap-1.5">
                            @for($y = 1; $y <= 5; $y++)
                                <button type="button" 
                                        @click="numYears = {{ $y }}; updateDates(); calculateFees();" 
                                        :class="numYears == {{ $y }} ? 'bg-sky-600 text-white font-black border-sky-400 ring-2 ring-sky-400/30' : 'bg-slate-950 text-slate-300 border-slate-700 hover:bg-slate-800'"
                                        class="py-2 text-center rounded-xl border text-xs transition cursor-pointer">
                                    {{ $y }} ساڵ
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="num_years" :value="numYears">
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">بەرواری دەستپێکردن</label>
                        <input type="date" 
                               name="start_date" 
                               x-model="startDate" 
                               @change="updateDates(); calculateFees();"
                               class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono transition">
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">بەرواری بەسەرچوون</label>
                        <input type="date" 
                               name="end_date" 
                               x-model="endDate" 
                               @change="calculateFees()"
                               class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono transition">
                    </div>
                </div>
            </div>

            <!-- 4.2 Dynamic Live Fee Breakdown Dashboard -->
            <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950 border-2 border-emerald-500/50 rounded-2xl p-5 sm:p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <span class="text-sm sm:text-base font-extrabold text-white flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-emerald-400"></i>
                        <span>هەژمارکردنی خۆکاری بڕی پارە و نرخەکان (د.ع):</span>
                    </span>
                    <span class="text-xs bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 px-3 py-1 rounded-full font-mono font-bold" 
                          x-text="'کۆی گشتی: ' + Number(fees.total_pay).toLocaleString() + ' د.ع'"></span>
                </div>

                <!-- Fee Chips Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-center">
                    <div class="p-3 bg-slate-900/90 border border-slate-800 rounded-xl">
                        <span class="text-[11px] text-slate-400 block mb-1">پارەی ساڵەکان</span>
                        <span class="text-sm font-extrabold text-sky-300 font-mono" x-text="Number(fees.pay_amount_years).toLocaleString()"></span>
                        <span class="text-[10px] text-slate-500 block">د.ع</span>
                    </div>

                    <div class="p-3 bg-slate-900/90 border border-slate-800 rounded-xl">
                        <span class="text-[11px] text-slate-400 block mb-1">پول (Stamp)</span>
                        <span class="text-sm font-extrabold text-slate-200 font-mono" x-text="Number(fees.pay_stamp).toLocaleString()"></span>
                        <span class="text-[10px] text-slate-500 block">د.ع</span>
                    </div>

                    <div class="p-3 bg-slate-900/90 border border-slate-800 rounded-xl">
                        <span class="text-[11px] text-slate-400 block mb-1">فۆڕم</span>
                        <span class="text-sm font-extrabold text-slate-200 font-mono" x-text="Number(fees.pay_form).toLocaleString()"></span>
                        <span class="text-[10px] text-slate-500 block">د.ع</span>
                    </div>

                    <div class="p-3 bg-slate-900/90 border border-slate-800 rounded-xl">
                        <span class="text-[11px] text-slate-400 block mb-1">سزای دواکەوتن</span>
                        <span class="text-sm font-extrabold text-amber-400 font-mono" x-text="Number(fees.pay_fine).toLocaleString()"></span>
                        <span class="text-[10px] text-slate-500 block">د.ع</span>
                    </div>

                    <div class="p-3 bg-slate-900/90 border border-slate-800 rounded-xl">
                        <span class="text-[11px] text-slate-400 block mb-1">کەشف (Inspection)</span>
                        <span class="text-sm font-extrabold text-indigo-300 font-mono" x-text="Number(fees.pay_inspection).toLocaleString()"></span>
                        <span class="text-[10px] text-slate-500 block">د.ع</span>
                    </div>

                    <div class="p-3 bg-emerald-500/15 border-2 border-emerald-500/50 rounded-xl shadow-inner">
                        <span class="text-[11px] text-emerald-300 block mb-1 font-black">کۆی وەرگیراو</span>
                        <span class="text-base font-black text-emerald-300 font-mono" x-text="Number(fees.total_pay).toLocaleString()"></span>
                        <span class="text-[10px] text-emerald-400 font-bold block">دیناری عێراقی</span>
                    </div>
                </div>
            </div>

            <!-- Final Confirmation Action Card -->
            <div class="bg-slate-900/90 backdrop-blur-md border border-slate-700/60 rounded-2xl p-5 sm:p-6 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-right">
                    <span class="text-sm sm:text-base font-extrabold text-white flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        <span>ئامادەی بۆ تۆمارکردن؟</span>
                    </span>
                    <span class="text-xs text-slate-400 block mt-0.5">
                        دوای تۆمارکردن بارکۆدی فەرمی دروست دەبێت و دەتوانیت فیشە و نوسراوەکان دەستبەجێ چاپ بکەیت.
                    </span>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" 
                            x-show="formViewMode === 'wizard'" 
                            @click="setStep(3)" 
                            class="px-4 py-3 bg-slate-800 hover:bg-slate-750 text-slate-300 font-bold text-xs rounded-xl border border-slate-700 transition cursor-pointer">
                        هەنگاوی پێشوو
                    </button>

                    <button type="button" 
                            @click="submitForm()" 
                            class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-sm rounded-xl shadow-xl shadow-emerald-600/30 transition active:scale-95 flex items-center justify-center gap-2 border border-emerald-400/50 cursor-pointer">
                        <i class="fa-solid fa-check-circle text-base"></i>
                        <span>تۆمارکردن و دەرهێنانی بارکۆد</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- ==========================================
         FLOATING BOTTOM LIVE SUMMARY DOCK
         Always in reach of the user without scrolling
         ========================================== -->
    <div class="fixed bottom-3 left-3 right-3 sm:left-6 sm:right-6 z-40 max-w-5xl mx-auto">
        <div class="bg-slate-950/95 backdrop-blur-xl border-2 border-slate-700/80 rounded-2xl p-3 sm:px-5 sm:py-3 shadow-2xl shadow-black/80 flex items-center justify-between gap-3 text-right">
            <!-- Left: Transaction Summary Chips -->
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center text-xs shrink-0 border border-sky-500/30">
                    <i class="fa-solid fa-receipt"></i>
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-xs font-bold text-white truncate" x-text="getTypeName()"></span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-800 text-sky-300 border border-slate-700 truncate" 
                              x-show="plateNumber" 
                              x-text="plateNumber"></span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-800 text-amber-300 border border-slate-700 truncate" 
                              x-show="chassisNumber" 
                              x-text="'شاسی: ' + chassisNumber.slice(-6)"></span>
                    </div>
                    <span class="text-[10px] text-slate-400 block truncate" x-text="visitorName ? visitorName : 'ناوی شۆفێر داخڵ بکە'"></span>
                </div>
            </div>

            <!-- Middle/Right: Total Fee & Quick Submit -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="text-left hidden sm:block">
                    <span class="text-[10px] text-slate-400 block font-semibold">کۆی بڕی پارە:</span>
                    <span class="text-base font-black text-emerald-400 font-mono leading-none" x-text="Number(fees.total_pay).toLocaleString() + ' د.ع'"></span>
                </div>

                <!-- Next or Submit Button -->
                <template x-if="formViewMode === 'wizard' && currentStep < 4">
                    <button type="button" 
                            @click="nextStep()" 
                            class="px-4 sm:px-5 py-2.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-sky-600/30 transition flex items-center gap-1.5 cursor-pointer">
                        <span>هەنگاوی دواتر</span>
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    </button>
                </template>

                <template x-if="formViewMode === 'all' || currentStep === 4">
                    <button type="button" 
                            @click="submitForm()" 
                            class="px-5 sm:px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5 border border-emerald-400/40 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>تۆمارکردن</span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- ==========================================
         INLINE MODALS (Manage Lookups)
         ========================================== -->

    <!-- INLINE MODAL 1: Transaction Types -->
    <div x-show="showTxTypeModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-list-check ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی جۆری مامەڵە</span>
                <button type="button" @click="showTxTypeModal = false" class="text-slate-400 hover:text-slate-200 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <div class="overflow-y-auto flex-1 space-y-2 max-h-48 border-b border-slate-800 pb-3">
                <table class="w-full text-right text-xs text-slate-200">
                    <thead class="bg-slate-800 text-slate-400">
                        <tr>
                            <th class="p-2">#</th>
                            <th class="p-2">ناوی مامەڵە (کوردی)</th>
                            <th class="p-2 text-center">دەستکاریکردن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($transactionTypes as $tt)
                            <tr>
                                <td class="p-2 font-mono text-slate-500">{{ $tt->id }}</td>
                                <td class="p-2 font-bold text-slate-100">
                                    <input type="text" id="tx_type_input_{{ $tt->id }}" value="{{ $tt->name_kurdish }}" class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs w-full">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="updateTransactionType({{ $tt->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold cursor-pointer">پاشەکەوت</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی جۆری نوێ</h4>
                <div class="flex gap-2">
                    <input type="text" x-model="newTxTypeName" placeholder="ناوی جۆری نوێی مامەڵە..." class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100">
                    <button type="button" @click="addTransactionType()" class="px-4 py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow cursor-pointer">زیادکردن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 2: Directorate -->
    <div x-show="showDirectorateModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-building-flag ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی بەڕێوەبەرایەتی</span>
                <button type="button" @click="showDirectorateModal = false" class="text-slate-400 hover:text-slate-200 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <div class="overflow-y-auto flex-1 space-y-2 max-h-48 border-b border-slate-800 pb-3">
                <table class="w-full text-right text-xs text-slate-200">
                    <thead class="bg-slate-800 text-slate-400">
                        <tr>
                            <th class="p-2">#</th>
                            <th class="p-2">ناوی بەڕێوەبەرایەتی</th>
                            <th class="p-2 text-center">دەستکاریکردن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($directorates as $dir)
                            <tr>
                                <td class="p-2 font-mono text-slate-500">{{ $dir->id }}</td>
                                <td class="p-2 font-bold text-slate-100">
                                    <input type="text" id="dir_input_{{ $dir->id }}" value="{{ $dir->name_kurdish }}" class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs w-full">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="updateDirectorate({{ $dir->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold cursor-pointer">پاشەکەوت</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی بەڕێوەبەرایەتی نوێ</h4>
                <div class="flex gap-2">
                    <input type="text" x-model="newDirectorateName" placeholder="ناوی بەڕێوەبەرایەتی نوێ..." class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100">
                    <button type="button" @click="addDirectorate()" class="px-4 py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow cursor-pointer">زیادکردن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 3: Director -->
    <div x-show="showDirectorModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-user-tie ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی بەڕێوەبەر / لێپرسراو</span>
                <button type="button" @click="showDirectorModal = false" class="text-slate-400 hover:text-slate-200 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <div class="overflow-y-auto flex-1 space-y-2 max-h-48 border-b border-slate-800 pb-3">
                <table class="w-full text-right text-xs text-slate-200">
                    <thead class="bg-slate-800 text-slate-400">
                        <tr>
                            <th class="p-2">#</th>
                            <th class="p-2">ناوی بەڕێوەبەر</th>
                            <th class="p-2 text-center">دەستکاریکردن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($directors as $d)
                            <tr>
                                <td class="p-2 font-mono text-slate-500">{{ $d->id }}</td>
                                <td class="p-2 font-bold text-slate-100">
                                    <input type="text" id="director_input_{{ $d->id }}" value="{{ $d->name }}" class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs w-full">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="updateDirector({{ $d->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold cursor-pointer">پاشەکەوت</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی بەڕێوەبەری نوێ</h4>
                <div class="flex gap-2">
                    <input type="text" x-model="newDirectorName" placeholder="ناوی سێیانی بەڕێوەبەر..." class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100">
                    <button type="button" @click="addDirector()" class="px-4 py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow cursor-pointer">زیادکردن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 4: Plate Type -->
    <div x-show="showPlateTypeModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-rectangle-ad ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی جۆری تابلۆ</span>
                <button type="button" @click="showPlateTypeModal = false" class="text-slate-400 hover:text-slate-200 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <div class="overflow-y-auto flex-1 space-y-2 max-h-48 border-b border-slate-800 pb-3">
                <table class="w-full text-right text-xs text-slate-200">
                    <thead class="bg-slate-800 text-slate-400">
                        <tr>
                            <th class="p-2">#</th>
                            <th class="p-2">جۆری تابلۆ</th>
                            <th class="p-2 text-center">دەستکاریکردن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($plateTypes as $pt)
                            <tr>
                                <td class="p-2 font-mono text-slate-500">{{ $pt->id }}</td>
                                <td class="p-2 font-bold text-slate-100">
                                    <input type="text" id="pt_input_{{ $pt->id }}" value="{{ $pt->name_kurdish }}" class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs w-full">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="updatePlateType({{ $pt->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold cursor-pointer">پاشەکەوت</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی جۆری تابلۆی نوێ</h4>
                <div class="flex gap-2">
                    <input type="text" x-model="newPlateTypeName" placeholder="جۆری تابلۆی نوێ..." class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100">
                    <button type="button" @click="addPlateType()" class="px-4 py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow cursor-pointer">زیادکردن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 5: Car Make -->
    <div x-show="showCarMakeModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-car-side ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی مارکەی ئۆتۆمبێل</span>
                <button type="button" @click="showCarMakeModal = false; carMakeSearch = ''" class="text-slate-400 hover:text-slate-200 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <!-- Search in Makes -->
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-sky-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                    گەڕان لە مارکە تۆمارکراوەکان:
                </label>
                <div class="relative">
                    <input type="text" 
                           x-model="carMakeSearch" 
                           placeholder="گەڕان بە کوردی یان ئینگلیزی (مثلاً: تۆیۆتا / Toyota / KIA)..." 
                           class="w-full bg-slate-800/90 border border-slate-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 rounded-xl pr-9 pl-8 py-2 text-xs text-slate-100 placeholder-slate-400 transition font-sans">
                    <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-slate-400 text-xs"></i>
                    <button type="button" 
                            x-show="carMakeSearch" 
                            @click="carMakeSearch = ''" 
                            class="absolute left-2.5 top-2 text-slate-400 hover:text-white text-xs transition cursor-pointer">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Existing Makes Table -->
            <div class="overflow-y-auto flex-1 space-y-2 max-h-52 border border-slate-800 rounded-xl p-1 bg-slate-950/40">
                <table class="w-full text-right text-xs text-slate-200">
                    <thead class="bg-slate-800 text-slate-400 sticky top-0 z-10">
                        <tr>
                            <th class="p-2">مارکە (کوردی)</th>
                            <th class="p-2">مارکە (ئینگلیزی)</th>
                            <th class="p-2 text-center">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @foreach($carMakes as $cm)
                            <tr x-show="!carMakeSearch || '{{ strtolower(addslashes($cm->name_kurdish)) }}'.includes(carMakeSearch.toLowerCase().trim()) || '{{ strtolower(addslashes($cm->name_english)) }}'.includes(carMakeSearch.toLowerCase().trim())" class="hover:bg-slate-800/50 transition">
                                <td class="p-1.5">
                                    <input type="text" id="cm_kurdish_{{ $cm->id }}" value="{{ $cm->name_kurdish }}" class="bg-slate-800 border border-slate-700 focus:border-sky-500 rounded px-2 py-1 text-xs w-full text-slate-100">
                                </td>
                                <td class="p-1.5">
                                    <input type="text" id="cm_english_{{ $cm->id }}" value="{{ $cm->name_english }}" dir="ltr" class="bg-slate-800 border border-slate-700 focus:border-sky-500 rounded px-2 py-1 text-xs font-mono w-full text-slate-100">
                                </td>
                                <td class="p-1.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" @click="selectCarMakeFromModal({{ $cm->id }}, '{{ addslashes($cm->name_english) }}')" class="px-2 py-1 bg-sky-600 hover:bg-sky-500 text-white rounded text-[10px] font-bold shadow transition cursor-pointer">
                                            هەڵبژاردن
                                        </button>
                                        <button type="button" @click="updateCarMake({{ $cm->id }})" class="px-2 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-[10px] font-bold shadow transition cursor-pointer">
                                            پاشەکەوت
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- New Car Make Addition Form -->
            <div class="space-y-2.5 pt-2 border-t border-slate-800">
                <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی مارکەی نوێ:</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] text-slate-400 block mb-1">ناوی کوردی:</label>
                        <input type="text" x-model="newMakeKurdish" placeholder="مثلاً: هۆندا" class="w-full bg-slate-800 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100">
                    </div>
                    <div>
                        <label class="text-[10px] text-slate-400 block mb-1">ناوی ئینگلیزی:</label>
                        <input type="text" x-model="newMakeEnglish" placeholder="e.g. Honda" dir="ltr" class="w-full bg-slate-800 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono">
                    </div>
                </div>
                <button type="button" @click="addCarMake()" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>زیادکردنی مارکەی نوێ و هەڵبژاردن</span>
                </button>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 6: Car Color -->
    <div x-show="showCarColorModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-palette ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی ڕەنگی ئۆتۆمبێل</span>
                <button type="button" @click="showCarColorModal = false" class="text-slate-400 hover:text-slate-200 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <div class="overflow-y-auto flex-1 space-y-2 max-h-48 border-b border-slate-800 pb-3">
                <table class="w-full text-right text-xs text-slate-200">
                    <thead class="bg-slate-800 text-slate-400">
                        <tr>
                            <th class="p-2">ڕەنگ (کوردی)</th>
                            <th class="p-2">ڕەنگ (ئینگلیزی)</th>
                            <th class="p-2 text-center">دەستکاریکردن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($carColors as $cc)
                            <tr>
                                <td class="p-2">
                                    <input type="text" id="cc_kurdish_{{ $cc->id }}" value="{{ $cc->name_kurdish }}" class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs w-full">
                                </td>
                                <td class="p-2">
                                    <input type="text" id="cc_english_{{ $cc->id }}" value="{{ $cc->name_english }}" dir="ltr" class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs font-mono w-full">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="updateCarColor({{ $cc->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold cursor-pointer">پاشەکەوت</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی ڕەنگی نوێ</h4>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" x-model="newColorKurdish" placeholder="کوردی (مثلاً: قاوەیی)" class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100">
                    <input type="text" x-model="newColorEnglish" placeholder="ئینگلیزی (e.g. Brown)" dir="ltr" class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono">
                </div>
                <button type="button" @click="addCarColor()" class="w-full py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow cursor-pointer">زیادکردن</button>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 7: Passport Issue Place -->
    <div x-show="showPassportPlaceModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-map-location-dot ml-2 text-sky-400"></i> بەڕێوەبردن و دەستکاریکردنی شوێنەکانی دەرچوون</span>
                <button type="button" @click="showPassportPlaceModal = false" class="text-slate-400 hover:text-slate-200 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <div class="overflow-y-auto flex-1 space-y-2 max-h-56 border-b border-slate-800 pb-3">
                <table class="w-full text-right text-xs text-slate-200">
                    <thead class="bg-slate-800 text-slate-400">
                        <tr>
                            <th class="p-2">#</th>
                            <th class="p-2">ناوی شوێن / پارێزگا</th>
                            <th class="p-2 text-center">پاشەکەوت</th>
                            <th class="p-2 text-center">سڕینەوە</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800" id="passportPlacesTableBody">
                        @foreach($passportIssuePlaces as $place)
                            <tr id="passport_place_row_{{ $place->id }}">
                                <td class="p-2 font-mono text-slate-500">{{ $loop->iteration }}</td>
                                <td class="p-2">
                                    <input type="text" id="place_input_{{ $place->id }}" value="{{ $place->name }}" class="bg-slate-800 border border-slate-700 rounded px-2.5 py-1 text-xs w-full text-white focus:border-sky-500 focus:outline-none font-bold">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="updatePassportPlace({{ $place->id }})" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-[11px] font-bold transition cursor-pointer">پاشەکەوت</button>
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="deletePassportPlace({{ $place->id }})" class="px-2.5 py-1 bg-rose-600/80 hover:bg-rose-600 text-white rounded text-[11px] font-bold transition cursor-pointer" title="سڕینەوە"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-2 pt-1">
                <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی شوێنی نوێ</h4>
                <div class="flex gap-2">
                    <input type="text" x-model="newPassportPlaceName" @keydown.enter.prevent="addPassportPlace()" placeholder="ناوی شوێنی دەرچوون (شار، پارێزگا)..." class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100 focus:border-sky-500 focus:outline-none">
                    <button type="button" @click="addPassportPlace()" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                        <i class="fa-solid fa-plus"></i> زیادکردن
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-end pt-2 border-t border-slate-800">
                <button type="button" @click="showPassportPlaceModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition cursor-pointer">
                    داخستن
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function transactionForm(initialTx = null, targetTypeId = null, txRates = {}) {
        let initialTypeId = targetTypeId ? parseInt(targetTypeId) : (initialTx ? 2 : 1);
        return {
            typeId: initialTypeId,
            txTypeRates: txRates || {},
            parentTransactionId: initialTx ? initialTx.id : '',
            parentTransactionSummary: initialTx ? ((initialTx.visitor_name || '') + ' | ' + (initialTx.plate_number || '') + (initialTx.booklet_number ? ' | دەفتەر: ' + initialTx.booklet_number : '') + (initialTx.barcode ? ' | بارکۆد: ' + initialTx.barcode : '')) : '',
            
            // UI Stepper & View Mode States
            currentStep: 1,
            formViewMode: 'wizard', // 'wizard' (Step-by-step) or 'all' (Full page)

            setStep(s) {
                this.currentStep = s;
                if (this.formViewMode === 'all') {
                    const el = document.getElementById('section_step_' + s);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            },
            nextStep() {
                if (this.currentStep < 4) this.setStep(this.currentStep + 1);
            },
            prevStep() {
                if (this.currentStep > 1) this.setStep(this.currentStep - 1);
            },
            submitForm() {
                const form = document.getElementById('mainTransactionForm');
                if (form) {
                    if (form.reportValidity()) {
                        form.submit();
                    } else {
                        // If validation fails, jump to step with the invalid input
                        const invalidInput = form.querySelector(':invalid');
                        if (invalidInput) {
                            const stepSection = invalidInput.closest('[id^="section_step_"]');
                            if (stepSection) {
                                const stepNum = parseInt(stepSection.id.replace('section_step_', ''));
                                if (!isNaN(stepNum)) this.setStep(stepNum);
                            }
                            invalidInput.focus();
                        }
                    }
                }
            },

            getTypeName() {
                const types = {
                    1: 'دەرهێنانی دەفتەر',
                    2: 'تازەکردنەوە',
                    3: 'ناوگۆڕین',
                    4: 'دەفتەرگۆڕین',
                    5: 'پووچەڵکردنەوە',
                    6: 'بەڵێننامە',
                    7: 'ناوگۆڕین و دەفتەرگۆڕین',
                    8: 'پشکنین'
                };
                return types[this.typeId] || ('مامەڵەی #' + this.typeId);
            },

            // Driver & Citizen inputs
            visitorName: initialTx ? (initialTx.visitor_name || '') : '',
            visitorNameEng: initialTx ? (initialTx.visitor_name_eng || '') : '',
            secondDriverName: initialTx ? (initialTx.second_driver_name || '') : '',
            secondDriverNameEng: initialTx ? (initialTx.second_driver_name_eng || '') : '',
            phoneNumber: initialTx ? (initialTx.phone_number || '') : '',
            passportNumber: initialTx ? (initialTx.passport_number || '') : '',
            address: initialTx ? (initialTx.address || '') : '',
            passportIssueDate: initialTx ? (initialTx.passport_issue_date || '') : '',
            passportIssuePlace: initialTx ? (initialTx.passport_issue_place || '') : '',

            // Vehicle inputs
            plateNumber: initialTx ? (initialTx.plate_number || '') : '',
            plateTypeId: initialTx ? (initialTx.plate_type_id || 1) : 1,
            carMakeId: initialTx ? (initialTx.car_make_id || '') : '',
            carMakeEng: initialTx && initialTx.car_make ? (initialTx.car_make.name_english || '') : '',
            carColorId: initialTx ? (initialTx.car_color_id || '') : '',
            carColorEng: initialTx && initialTx.car_color ? (initialTx.car_color.name_english || '') : '',
            modelYear: initialTx ? (initialTx.model_year || '') : '',
            chassisNumber: initialTx ? (initialTx.chassis_number || '') : '',
            pistonCount: initialTx ? (initialTx.piston_count || 4) : 4,
            salanaNumber: initialTx ? (initialTx.salana_number || '') : '',

            // Chassis fast lookup & auto-fill state
            isCheckingChassis: false,
            chassisFound: initialTx ? true : false,
            chassisPreviousInfo: initialTx || null,

            numYears: 1,
            directorateId: initialTx ? (initialTx.traffic_directorate_id || '{{ $defaultDirectorateId }}') : '{{ $defaultDirectorateId }}',
            directorId: initialTx ? (initialTx.director_id || '{{ $defaultDirectorId }}') : '{{ $defaultDirectorId }}',
            startDate: '{{ date("Y-m-d") }}',
            endDate: '{{ date("Y-m-d", strtotime("+1 year")) }}',

            zedabar: initialTx ? (initialTx.zedabar || 'باشە') : 'باشە',
            zedabarEng: initialTx ? (initialTx.zedabar_eng || 'good') : 'good',
            kamukurty: initialTx ? (initialTx.kamukurty || 'باشە') : 'باشە',
            kamukurtyEng: initialTx ? (initialTx.kamukurty_eng || 'good') : 'good',
            baryGshty: initialTx ? (initialTx.bary_gshty || 'باشە') : 'باشە',
            baryGshtyEng: initialTx ? (initialTx.bary_gshty_eng || 'good') : 'good',

            arabicInputMode: 'ar101',

            toggleArabicLayout() {
                this.arabicInputMode = (this.arabicInputMode === 'ar101') ? 'kurdish' : 'ar101';
            },

            onKurdishInput(e, field) {
                let val = e.target.value;
                if (/[a-zA-Z]/.test(val) && window.LangConvert) {
                    val = window.LangConvert.toArabic(val, this.arabicInputMode);
                }
                this[field] = val;
                e.target.value = val;

                if (field === 'visitorName' && (!this.visitorNameEng || this.visitorNameEng.trim() === '')) {
                    if (window.LangConvert) {
                        this.visitorNameEng = window.LangConvert.transliterate(val);
                    }
                }
                if (field === 'secondDriverName' && (!this.secondDriverNameEng || this.secondDriverNameEng.trim() === '')) {
                    if (window.LangConvert) {
                        this.secondDriverNameEng = window.LangConvert.transliterate(val);
                    }
                }
            },

            onEnglishInput(e, field) {
                let val = e.target.value;
                if (/[\u0600-\u06FF\u0750-\u077F\uFB50-\uFDFF\uFE70-\uFEFF]/.test(val) && window.LangConvert) {
                    val = window.LangConvert.toEnglish(val);
                }
                val = val.toUpperCase();
                this[field] = val;
                e.target.value = val;
            },

            onChassisInput(e) {
                let val = e.target.value;
                if (/[\u0600-\u06FF\u0750-\u077F\uFB50-\uFDFF\uFE70-\uFEFF]/.test(val) && window.LangConvert) {
                    val = window.LangConvert.toEnglish(val);
                }
                val = val.toUpperCase().replace(/[^A-Z0-9]/g, '');
                this.chassisNumber = val;
                e.target.value = val;
                if (this._chassisTimer) clearTimeout(this._chassisTimer);
                this._chassisTimer = setTimeout(() => {
                    this.lookupByChassis();
                }, 350);
            },

            autoFillEnglishName(sourceField, targetField) {
                const src = this[sourceField];
                if (src && src.trim() && window.LangConvert) {
                    this[targetField] = window.LangConvert.transliterate(src);
                }
            },

            searchQuery: '',
            searchResults: [],
            isSearching: false,
            showResults: false,

            searchPrevious() {
                if (!this.searchQuery || this.searchQuery.trim().length < 2) {
                    this.searchResults = [];
                    this.showResults = false;
                    return;
                }
                this.isSearching = true;
                fetch(`/transactions/api/lookup-previous?query=${encodeURIComponent(this.searchQuery.trim())}`)
                    .then(res => res.json())
                    .then(data => {
                        this.searchResults = data;
                        this.showResults = true;
                        this.isSearching = false;
                    })
                    .catch(() => {
                        this.isSearching = false;
                    });
            },

            populateFromPrevious(record, chosenType = null) {
                this.parentTransactionId = record.id;
                this.parentTransactionSummary = (record.visitor_name || '') + ' | ' + (record.plate_number || '') + (record.booklet_number ? ' | دەفتەر: ' + record.booklet_number : '') + (record.barcode ? ' | بارکۆد: ' + record.barcode : '');
                this.visitorName = record.visitor_name || '';
                this.visitorNameEng = record.visitor_name_eng || '';
                this.secondDriverName = record.second_driver_name || '';
                this.secondDriverNameEng = record.second_driver_name_eng || '';
                this.phoneNumber = record.phone_number || '';
                this.passportNumber = record.passport_number || '';
                this.address = record.address || '';
                this.passportIssueDate = record.passport_issue_date ? record.passport_issue_date.substring(0, 10) : '';
                this.passportIssuePlace = record.passport_issue_place || '';
                this.plateNumber = record.plate_number || '';
                this.plateTypeId = record.plate_type_id || 1;
                this.carMakeId = record.car_make_id || '';
                this.carMakeEng = (record.car_make && record.car_make.name_english) ? record.car_make.name_english : '';
                this.carColorId = record.car_color_id || '';
                this.carColorEng = (record.car_color && record.car_color.name_english) ? record.car_color.name_english : '';
                this.modelYear = record.model_year || '';
                this.chassisNumber = record.chassis_number || '';
                this.pistonCount = record.piston_count || 4;
                this.salanaNumber = record.salana_number || '';
                if (record.traffic_directorate_id) this.directorateId = record.traffic_directorate_id;
                if (record.director_id) this.directorId = record.director_id;

                if (chosenType) {
                    this.typeId = chosenType;
                }

                this.showResults = false;
                this.searchQuery = '';
                this.calculateFees();
            },

            clearPrevious() {
                this.parentTransactionId = '';
                this.parentTransactionSummary = '';
                this.chassisFound = false;
                this.chassisPreviousInfo = null;
                this.passportNumber = '';
                this.address = '';
                this.passportIssueDate = '';
                this.passportIssuePlace = '';
            },

            lookupByChassis() {
                const clean = (this.chassisNumber || '').trim();
                if (clean.length < 3) {
                    this.chassisFound = false;
                    this.chassisPreviousInfo = null;
                    return;
                }

                this.isCheckingChassis = true;
                fetch(`/transactions/api/lookup-previous?chassis_number=${encodeURIComponent(clean)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.isCheckingChassis = false;
                        if (data && data.found && data.record) {
                            this.chassisFound = true;
                            this.chassisPreviousInfo = data.record;
                            this.populateFromPrevious(data.record);
                        } else {
                            this.chassisFound = false;
                            this.chassisPreviousInfo = null;
                        }
                    })
                    .catch(() => {
                        this.isCheckingChassis = false;
                    });
            },

            clearChassisAutoFill() {
                this.clearPrevious();
            },

            isFullBookletForm() {
                return [1, 2, 3, 4, 7].includes(parseInt(this.typeId));
            },

            isCancellationOrInspection() {
                return [5, 6, 8].includes(parseInt(this.typeId));
            },

            isCancellation() {
                return parseInt(this.typeId) === 5;
            },

            showTxTypeModal: false,
            showDirectorateModal: false,
            showDirectorModal: false,
            showPlateTypeModal: false,
            showCarMakeModal: false,
            showCarColorModal: false,
            showPassportPlaceModal: false,

            carMakeSearch: '',

            newTxTypeName: '',
            newDirectorateName: '',
            newDirectorName: '',
            newPlateTypeName: '',
            newMakeKurdish: '',
            newMakeEnglish: '',
            newColorKurdish: '',
            newColorEnglish: '',
            newPassportPlaceName: '',

            fees: {
                pay_amount_years: 80000,
                pay_stamp: 3000,
                pay_form: 4000,
                pay_fine: 0,
                pay_inspection: 20000,
                total_pay: 107000,
                start_date: '{{ date("Y-m-d") }}',
                end_date: '{{ date("Y-m-d", strtotime("+1 year")) }}'
            },

            onCarMakeChange() {
                const sel = document.querySelector('select[name="car_make_id"]');
                const opt = sel ? sel.options[sel.selectedIndex] : null;
                this.carMakeEng = opt ? (opt.getAttribute('data-eng') || '') : '';
            },

            selectCarMakeFromModal(id, english) {
                this.carMakeId = id;
                this.carMakeEng = english || '';
                const sel = document.querySelector('select[name="car_make_id"]');
                if (sel) sel.value = id;
                this.showCarMakeModal = false;
                this.carMakeSearch = '';
            },

            onCarColorChange() {
                const sel = document.querySelector('select[name="car_color_id"]');
                const opt = sel.options[sel.selectedIndex];
                this.carColorEng = opt ? (opt.getAttribute('data-eng') || '') : '';
            },

            onZedabarChange() {
                this.zedabarEng = (this.zedabar === 'باشە') ? 'good' : 'NO';
            },
            onKamukurtyChange() {
                this.kamukurtyEng = (this.kamukurty === 'باشە') ? 'good' : 'NO';
            },
            onBaryGshtyChange() {
                this.baryGshtyEng = (this.baryGshty === 'باشە') ? 'good' : 'NO';
            },

            updateDates() {
                if ([5, 6, 8].includes(parseInt(this.typeId))) {
                    return;
                }
                if (!this.startDate) {
                    const today = new Date();
                    this.startDate = today.toISOString().split('T')[0];
                }
                const parts = this.startDate.split('-');
                if (parts.length === 3) {
                    const year = parseInt(parts[0]);
                    const month = parts[1];
                    const day = parts[2];
                    const endYear = year + parseInt(this.numYears || 1);
                    this.endDate = `${endYear}-${month}-${day}`;
                }
            },

            calculateFees() {
                const typeId = parseInt(this.typeId || 1);
                const years = Math.min(Math.max(parseInt(this.numYears || 1), 1), 10);
                const isBus = (parseInt(this.plateTypeId) === 4);

                let payYears = 0;
                let stamp = 3000;
                let form = 4000;
                let fine = 0;
                let inspection = 0;

                const r = (this.txTypeRates && this.txTypeRates[typeId]) ? this.txTypeRates[typeId] : null;

                if (typeId === 6) {
                    payYears = 0;
                    stamp = r ? parseFloat(r.stamp_pay) : 3000;
                    form = r ? parseFloat(r.form_pay) : 3000;
                    fine = 0;
                    inspection = 0;
                } else if (r) {
                    stamp = parseFloat(r.stamp_pay);
                    form = parseFloat(r.form_pay);
                    fine = parseFloat(r.fine_pay);
                    inspection = isBus ? parseFloat(r.bus_inspection_pay) : parseFloat(r.inspection_pay);

                    if (typeId === 1 || typeId === 2) {
                        if (years === 1) payYears = parseFloat(r.pay_1_year);
                        else if (years === 2) payYears = parseFloat(r.pay_2_year);
                        else if (years === 3) payYears = parseFloat(r.pay_3_year);
                        else payYears = parseFloat(r.pay_1_year) * years;
                    } else {
                        payYears = 0;
                    }
                } else {
                    if (typeId === 1 || typeId === 2) {
                        if (years === 1) payYears = 80000;
                        else if (years === 2) payYears = 120000;
                        else if (years === 3) payYears = 140000;
                        else payYears = 80000 * years;
                        inspection = isBus ? 27000 : 20000;
                    } else if (typeId === 3 || typeId === 4) {
                        fine = 25000;
                    } else if (typeId === 7) {
                        fine = 50000;
                    } else if (typeId === 5 || typeId === 8) {
                        inspection = 20000;
                    }
                }

                this.fees.pay_amount_years = payYears;
                this.fees.pay_stamp = stamp;
                this.fees.pay_form = form;
                this.fees.pay_fine = fine;
                this.fees.pay_inspection = inspection;
                this.fees.total_pay = payYears + stamp + form + fine + inspection;

                this.updateDates();

                fetch(`/transactions/api/calculate-fee?transaction_type_id=${typeId}&num_years=${years}&plate_type_id=${this.plateTypeId}&start_date=${this.startDate}&end_date=${this.endDate}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && typeof data.total_pay !== 'undefined') {
                            this.fees = data;
                            if (data.start_date) this.startDate = data.start_date;
                            if (data.end_date) this.endDate = data.end_date;
                        }
                    })
                    .catch(() => {});
            },

            handleResponse(res) {
                return res.json().then(data => {
                    if (!res.ok) {
                        let msg = data.message || 'خەتایەک ڕوویدا!';
                        if (data.errors) {
                            msg = Object.values(data.errors).flat().join('\n');
                        }
                        alert('تێبینی: ' + msg);
                        return { success: false, message: msg };
                    }
                    return data;
                });
            },

            updateTransactionType(id) {
                const name = document.getElementById('tx_type_input_' + id).value;
                fetch(`/lookup/transaction-type/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name_kurdish: name })
                }).then(res => this.handleResponse(res)).then(data => {
                    if (data.success) {
                        const opt = document.querySelector(`select[name="transaction_type_id"] option[value="${id}"]`);
                        if (opt) opt.text = name;
                        alert('ناوی مامەڵەکە بە سەرکەوتوویی ڕاستکرایەوە.');
                    }
                });
            },

            updateDirectorate(id) {
                const name = document.getElementById('dir_input_' + id).value;
                fetch(`/lookup/directorate/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name_kurdish: name })
                }).then(res => this.handleResponse(res)).then(data => {
                    if (data.success) {
                        const opt = document.querySelector(`select[name="traffic_directorate_id"] option[value="${id}"]`);
                        if (opt) opt.text = name;
                        alert('ناوی بەڕێوەبەرایەتی ڕاستکرایەوە.');
                    }
                });
            },

            updateDirector(id) {
                const name = document.getElementById('director_input_' + id).value;
                fetch(`/lookup/director/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: name })
                }).then(res => this.handleResponse(res)).then(data => {
                    if (data.success) {
                        const opt = document.querySelector(`select[name="director_id"] option[value="${id}"]`);
                        if (opt) opt.text = name;
                        alert('ناوی بەڕێوەبەر ڕاستکرایەوە.');
                    }
                });
            },

            updatePlateType(id) {
                const name = document.getElementById('pt_input_' + id).value;
                fetch(`/lookup/plate-type/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name_kurdish: name })
                }).then(res => this.handleResponse(res)).then(data => {
                    if (data.success) {
                        const opt = document.querySelector(`select[name="plate_type_id"] option[value="${id}"]`);
                        if (opt) opt.text = name;
                        alert('ناوی تابلۆ ڕاستکرایەوە.');
                    }
                });
            },

            updateCarMake(id) {
                const kurdish = document.getElementById('cm_kurdish_' + id).value;
                const english = document.getElementById('cm_english_' + id).value;
                fetch(`/lookup/car-make/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name_kurdish: kurdish, name_english: english })
                }).then(res => this.handleResponse(res)).then(data => {
                    if (data.success) {
                        const opt = document.querySelector(`select[name="car_make_id"] option[value="${id}"]`);
                        if (opt) {
                            opt.text = kurdish;
                            opt.setAttribute('data-eng', english);
                        }
                        if (this.carMakeId == id) this.carMakeEng = english;
                        alert('ناوی مارکە ڕاستکرایەوە.');
                    }
                });
            },

            updateCarColor(id) {
                const kurdish = document.getElementById('cc_kurdish_' + id).value;
                const english = document.getElementById('cc_english_' + id).value;
                fetch(`/lookup/car-color/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name_kurdish: kurdish, name_english: english })
                }).then(res => this.handleResponse(res)).then(data => {
                    if (data.success) {
                        const opt = document.querySelector(`select[name="car_color_id"] option[value="${id}"]`);
                        if (opt) {
                            opt.text = kurdish;
                            opt.setAttribute('data-eng', english);
                        }
                        if (this.carColorId == id) this.carColorEng = english;
                        alert('ناوی ڕەنگ ڕاستکرایەوە.');
                    }
                });
            },

            addCarMake() {
                if (!this.newMakeKurdish) return;
                fetch('/lookup/car-make', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name_kurdish: this.newMakeKurdish, name_english: this.newMakeEnglish })
                })
                .then(res => this.handleResponse(res))
                .then(data => {
                    if (data.success) {
                        const sel = document.querySelector('select[name="car_make_id"]');
                        const opt = document.createElement('option');
                        opt.value = data.item.id;
                        opt.text = data.item.name_kurdish + ' (' + (data.item.name_english || '') + ')';
                        opt.setAttribute('data-eng', data.item.name_english || '');
                        opt.selected = true;
                        sel.appendChild(opt);
                        this.carMakeId = data.item.id;
                        this.carMakeEng = data.item.name_english || '';
                        this.showCarMakeModal = false;
                        this.carMakeSearch = '';
                        this.newMakeKurdish = '';
                        this.newMakeEnglish = '';
                    }
                });
            },

            addCarColor() {
                if (!this.newColorKurdish) return;
                fetch('/lookup/car-color', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name_kurdish: this.newColorKurdish, name_english: this.newColorEnglish })
                })
                .then(res => this.handleResponse(res))
                .then(data => {
                    if (data.success) {
                        const sel = document.querySelector('select[name="car_color_id"]');
                        const opt = document.createElement('option');
                        opt.value = data.item.id;
                        opt.text = data.item.name_kurdish + ' (' + (data.item.name_english || '') + ')';
                        opt.setAttribute('data-eng', data.item.name_english || '');
                        opt.selected = true;
                        sel.appendChild(opt);
                        this.carColorId = data.item.id;
                        this.carColorEng = data.item.name_english || '';
                        this.showCarColorModal = false;
                        this.newColorKurdish = '';
                        this.newColorEnglish = '';
                    }
                });
            },

            addDirectorate() {
                if (!this.newDirectorateName) return;
                fetch('/lookup/directorate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name_kurdish: this.newDirectorateName })
                })
                .then(res => this.handleResponse(res))
                .then(data => {
                    if (data.success) {
                        const sel = document.querySelector('select[name="traffic_directorate_id"]');
                        const opt = document.createElement('option');
                        opt.value = data.item.id;
                        opt.text = data.item.name_kurdish;
                        opt.selected = true;
                        sel.appendChild(opt);
                        this.directorateId = data.item.id;
                        this.showDirectorateModal = false;
                        this.newDirectorateName = '';
                    }
                });
            },

            addDirector() {
                if (!this.newDirectorName) return;
                fetch('/lookup/director', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: this.newDirectorName })
                })
                .then(res => this.handleResponse(res))
                .then(data => {
                    if (data.success) {
                        const sel = document.querySelector('select[name="director_id"]');
                        const opt = document.createElement('option');
                        opt.value = data.item.id;
                        opt.text = data.item.name;
                        opt.selected = true;
                        sel.appendChild(opt);
                        this.directorId = data.item.id;
                        this.showDirectorModal = false;
                        this.newDirectorName = '';
                    }
                });
            },

            addPlateType() {
                if (!this.newPlateTypeName) return;
                fetch('/lookup/plate-type', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name_kurdish: this.newPlateTypeName })
                })
                .then(res => this.handleResponse(res))
                .then(data => {
                    if (data.success) {
                        const sel = document.querySelector('select[name="plate_type_id"]');
                        const opt = document.createElement('option');
                        opt.value = data.item.id;
                        opt.text = data.item.name_kurdish;
                        opt.selected = true;
                        sel.appendChild(opt);
                        this.plateTypeId = data.item.id;
                        this.showPlateTypeModal = false;
                        this.newPlateTypeName = '';
                        this.calculateFees();
                    }
                });
            },

            addTransactionType() {
                if (!this.newTxTypeName) return;
                fetch('/lookup/transaction-type', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name_kurdish: this.newTxTypeName })
                })
                .then(res => this.handleResponse(res))
                .then(data => {
                    if (data.success) {
                        const sel = document.querySelector('select[name="transaction_type_id"]');
                        const opt = document.createElement('option');
                        opt.value = data.item.id;
                        opt.text = data.item.name_kurdish;
                        opt.selected = true;
                        sel.appendChild(opt);
                        this.typeId = data.item.id;
                        this.showTxTypeModal = false;
                        this.newTxTypeName = '';
                        this.calculateFees();
                    }
                });
            },

            updatePassportPlace(id) {
                const input = document.getElementById('place_input_' + id);
                if (!input || !input.value.trim()) return;
                const newName = input.value.trim();
                const oldName = input.defaultValue;

                fetch(`/lookup/passport-issue-place/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: newName })
                })
                .then(res => this.handleResponse(res))
                .then(data => {
                    if (data && data.success) {
                        const sel = document.getElementById('passportIssuePlaceSelect');
                        if (sel) {
                            Array.from(sel.options).forEach(opt => {
                                if (opt.value === oldName || opt.text === oldName) {
                                    opt.value = newName;
                                    opt.text = newName;
                                }
                            });
                        }
                        if (this.passportIssuePlace === oldName) {
                            this.passportIssuePlace = newName;
                        }
                        input.defaultValue = newName;
                        alert('ناوی شوێنی دەرچوون بە سەرکەوتوویی نوێکرایەوە!');
                    }
                });
            },

            deletePassportPlace(id) {
                const input = document.getElementById('place_input_' + id);
                const name = input ? input.value : '';
                if (!confirm(`ئایا دڵنیایت لە سڕینەوەی شوێنی (${name})؟`)) return;

                fetch(`/lookup/passport-issue-place/${id}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => this.handleResponse(res))
                .then(data => {
                    if (data && data.success) {
                        const row = document.getElementById('passport_place_row_' + id);
                        if (row) row.remove();
                        const sel = document.getElementById('passportIssuePlaceSelect');
                        if (sel) {
                            Array.from(sel.options).forEach(opt => {
                                if (opt.value === name || opt.text === name) {
                                    opt.remove();
                                }
                            });
                        }
                        if (this.passportIssuePlace === name) {
                            this.passportIssuePlace = '';
                        }
                    }
                });
            },

            addPassportPlace() {
                if (!this.newPassportPlaceName.trim()) return;
                const nameToAdd = this.newPassportPlaceName.trim();
                fetch('/lookup/passport-issue-place', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: nameToAdd })
                })
                .then(res => this.handleResponse(res))
                .then(data => {
                    if (data && data.success && data.item) {
                        const sel = document.getElementById('passportIssuePlaceSelect');
                        if (sel) {
                            const opt = document.createElement('option');
                            opt.value = data.item.name;
                            opt.textContent = data.item.name;
                            opt.selected = true;
                            sel.appendChild(opt);
                        }
                        const tbody = document.getElementById('passportPlacesTableBody');
                        if (tbody) {
                            const count = tbody.children.length + 1;
                            const tr = document.createElement('tr');
                            tr.id = 'passport_place_row_' + data.item.id;
                            tr.innerHTML = `
                                <td class="p-2 font-mono text-slate-500">${count}</td>
                                <td class="p-2">
                                    <input type="text" id="place_input_${data.item.id}" value="${data.item.name}" class="bg-slate-800 border border-slate-700 rounded px-2.5 py-1 text-xs w-full text-white focus:border-sky-500 focus:outline-none font-bold">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="window.activeTxForm?.updatePassportPlace(${data.item.id})" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-[11px] font-bold transition cursor-pointer">پاشەکەوت</button>
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="window.activeTxForm?.deletePassportPlace(${data.item.id})" class="px-2.5 py-1 bg-rose-600/80 hover:bg-rose-600 text-white rounded text-[11px] font-bold transition cursor-pointer" title="سڕینەوە"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        }
                        this.passportIssuePlace = data.item.name;
                        this.newPassportPlaceName = '';
                        alert('شوێنی نوێ بە سەرکەوتوویی زیادکرا و دیاریکرا!');
                    }
                });
            },

            init() {
                window.activeTxForm = this;
                if (initialTx) {
                    this.populateFromPrevious(initialTx, targetTypeId ? parseInt(targetTypeId) : null);
                    this.chassisFound = true;
                    this.chassisPreviousInfo = initialTx;
                } else {
                    this.calculateFees();
                }
            }
        }
    }
</script>
@endsection