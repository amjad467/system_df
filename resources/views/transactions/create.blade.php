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
<div x-data="transactionForm({{ \Illuminate\Support\Js::from($fromTransaction) }}, {{ $targetTypeId ? (int)$targetTypeId : 'null' }}, {{ \Illuminate\Support\Js::from($txRates) }})" class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fa-solid fa-file-signature ml-2 text-sky-400"></i> فۆڕمی تۆمارکردنی مامەڵەی نوێ
            </h2>
            <p class="text-xs text-slate-400">تکایە زانیارییەکانی دەفتەر و ئۆتۆمبێل بە دەقیقی داخڵ بکە.</p>
        </div>
        <a href="{{ route('transactions.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl border border-slate-700 transition">
            <i class="fa-solid fa-arrow-right ml-1"></i> گەڕانەوە
        </a>
    </div>

    <!-- Smart Previous Transaction Lookup Card -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950/70 border border-indigo-500/40 rounded-2xl p-5 shadow-xl space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-700/80 pb-3">
            <div>
                <h3 class="text-sm font-bold text-indigo-300 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left ml-2 text-indigo-400"></i> بەکارهێنانەوەی زانیارییەکانی پێشوو (بۆ تازەکردنەوە، پووچەڵکردنەوە، ناوگۆڕین، دەفتەرگۆڕین)
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    ئەگەر هاووڵاتی دەفتەری لەم سیستەمە دەرهێناوە، بە تابلۆ، شاسی، ناو، یان بارکۆد بگەڕێ و فۆڕمەکە خۆکار پڕبکەرەوە. (ئەگەر لەدەرەوەی سیستەم بووە، دەتوانیت بە ئاسانی هەموو زانیارییەکان بە دەستی داخڵ بکەیت).
                </p>
            </div>
            
            <div x-show="parentTransactionId" class="flex items-center gap-2">
                <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-300 text-[11px] font-bold rounded-lg border border-emerald-500/40 flex items-center gap-1">
                    <i class="fa-solid fa-link text-[10px]"></i> بەستراوەتەوە بە مامەڵەی پێشوو
                </span>
                <button type="button" @click="clearPrevious()" class="px-2.5 py-1 bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 text-[11px] font-bold rounded-lg border border-rose-500/40 transition">
                    لابردن
                </button>
            </div>
        </div>

        <!-- Search Bar with Live Dropdown -->
        <div class="relative">
            <div class="relative">
                <input type="text" 
                       x-model="searchQuery" 
                       @input.debounce.300ms="searchPrevious()" 
                       placeholder="بۆ هێنانی زانیاری پێشوو بگەڕێ (ژمارەی تابلۆ، شاسی، ناوی هاووڵاتی، ژمارەی دەفتەر، بارکۆد)..." 
                       class="w-full bg-slate-900 border border-indigo-500/50 rounded-xl pr-10 pl-10 py-2.5 text-xs text-slate-100 placeholder-slate-500 focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400 font-mono">
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
                    <div class="p-3 hover:bg-slate-800 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
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
                            <!-- Quick action to apply for renewal -->
                            <button type="button" @click="populateFromPrevious(item, 2)" class="px-2.5 py-1.5 bg-sky-600 hover:bg-sky-500 text-white text-[11px] font-bold rounded-lg transition shadow">
                                <i class="fa-solid fa-rotate-right ml-1"></i> تازەکردنەوە
                            </button>
                            <!-- Quick action to apply for cancellation -->
                            <button type="button" @click="populateFromPrevious(item, 5)" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white text-[11px] font-bold rounded-lg transition shadow">
                                <i class="fa-solid fa-ban ml-1"></i> پووچەڵکردنەوە
                            </button>
                            <!-- Name change -->
                            <button type="button" @click="populateFromPrevious(item, 3)" class="px-2 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-bold rounded-lg transition shadow">
                                ناوگۆڕین
                            </button>
                            <!-- Populate keeping current type -->
                            <button type="button" @click="populateFromPrevious(item)" class="px-2 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-[11px] font-bold rounded-lg transition">
                                پڕکردنەوە
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty Results Notice -->
            <div x-show="showResults && searchResults.length === 0 && !isSearching" 
                 x-cloak
                 class="absolute z-50 left-0 right-0 mt-2 bg-slate-900 border border-slate-700 rounded-xl p-4 text-center text-xs text-slate-400 shadow-xl">
                هیچ مامەڵەیەکی پێشوو نەدۆزرایەوە بەم زانیارییە. دەتوانیت لە خوارەوە بە دەستی زانیارییەکان داخڵ بکەیت.
            </div>
        </div>

        <!-- Banner when previous transaction is selected -->
        <div x-show="parentTransactionId" x-cloak class="p-3 bg-indigo-950/40 border border-indigo-500/30 rounded-xl flex items-center justify-between text-xs text-indigo-200">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                <span>زانیارییەکان لە مامەڵەی پێشوو وەرگیران: <strong class="text-white" x-text="parentTransactionSummary"></strong></span>
            </div>
            <span class="text-[10px] text-slate-400">دەتوانیت هەر زانیارییەک پێویست بێت لە خوارەوە دەستکاری بکەیت.</span>
        </div>
    </div>

    <!-- Transaction Type Quick Selector Tabs -->
    <div class="bg-slate-800/90 border border-slate-700/90 rounded-2xl p-4 shadow-xl space-y-2">
        <label class="block text-xs font-bold text-sky-400 flex items-center">
            <i class="fa-solid fa-layer-group ml-1.5"></i> هەڵبژاردنی خێرای جۆری مامەڵە (گۆڕینی خێرای بەتن و فۆڕمەکە):
        </label>
        <div class="flex flex-wrap gap-2">
            @foreach($transactionTypes as $tt)
                <button type="button" 
                        @click="typeId = {{ $tt->id }}; calculateFees();" 
                        :class="typeId == {{ $tt->id }} ? 'bg-sky-600 text-white border-sky-400 font-extrabold shadow-lg shadow-sky-600/30 ring-2 ring-sky-400' : 'bg-slate-900 text-slate-300 border-slate-700 hover:bg-slate-750 hover:text-white'" 
                        class="px-4 py-2.5 rounded-xl border text-xs transition flex items-center gap-2">
                    <i class="fa-solid {{ $tt->id == 1 ? 'fa-book' : ($tt->id == 2 ? 'fa-rotate-right' : ($tt->id == 3 ? 'fa-ban' : ($tt->id == 4 ? 'fa-file-signature' : ($tt->id == 5 ? 'fa-magnifying-glass' : 'fa-pen-to-square')))) }}"></i>
                    <span>{{ $tt->name_kurdish }}</span>
                </button>
            @endforeach
        </div>

        <!-- Dynamic Notice for Booklet Issuance Dual Creation -->
        <div x-show="typeId == 1" class="mt-3 p-3 bg-sky-950/60 border border-sky-500/50 rounded-xl flex items-start space-x-3 space-x-reverse text-xs text-sky-200">
            <i class="fa-solid fa-wand-magic-sparkles text-sky-400 text-base mt-0.5 ml-2"></i>
            <div>
                <span class="font-extrabold text-sky-300 block mb-0.5">ئاسانکاری ئۆتۆماتیکی (دەرهێنانی دەفتەر):</span>
                <span>بۆ جۆری <strong>دەرهێنانی دەفتەر</strong>، سیستەم بە شێوەیەکی ئۆتۆماتیکی مامەڵەیەکی بەستراوەی <strong>بەڵێننامە</strong>ش بە بارکۆدێکی سەربەخۆ دروست دەکات. دوای تۆمارکردن، دەتوانیت فیشەی داتائەنتەری و فیشەی بەڵێننامەکە بە هەمان کلێشەی فەرمی فیشە چاپ بکەیت.</span>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <form action="/transactions" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="parent_transaction_id" :value="parentTransactionId">

        <!-- 1. Transaction & Directorate Details -->
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center">
                <i class="fa-solid fa-building-flag ml-2"></i> ١. جۆری مامەڵە و بەڕێوەبەرایەتی
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Transaction Type -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-300">جۆری مامەڵە *</label>
                        <button type="button" @click="showTxTypeModal = true" class="text-xs text-sky-400 hover:text-sky-300 font-bold flex items-center">
                            <i class="fa-solid fa-list-check ml-1"></i> بەڕێوەبردن & زیادکردن
                        </button>
                    </div>
                    <select name="transaction_type_id" x-model="typeId" @change="calculateFees()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        @foreach($transactionTypes as $tt)
                            <option value="{{ $tt->id }}">{{ $tt->name_kurdish }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Traffic Directorate -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-300">بەڕێوەبەرایەتی هاتوچۆ</label>
                        <button type="button" @click="showDirectorateModal = true" class="text-xs text-sky-400 hover:text-sky-300 font-bold flex items-center">
                            <i class="fa-solid fa-list-check ml-1"></i> بەڕێوەبردن & زیادکردن
                        </button>
                    </div>
                    <select name="traffic_directorate_id" x-model="directorateId" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        @foreach($directorates as $dir)
                            <option value="{{ $dir->id }}">{{ $dir->name_kurdish }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Director -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-300">بەڕێوەبەر / لێپرسراو</label>
                        <button type="button" @click="showDirectorModal = true" class="text-xs text-sky-400 hover:text-sky-300 font-bold flex items-center">
                            <i class="fa-solid fa-list-check ml-1"></i> بەڕێوەبردن & زیادکردن
                        </button>
                    </div>
                    <select name="director_id" x-model="directorId" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        @foreach($directors as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Drivers Information & Key Vehicle Identity -->
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center">
                <i class="fa-solid fa-user-gear ml-2"></i> ٢. زانیاری شۆفێر و هاووڵاتی
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- 1. ژمارەی شاسی (VIN) - لە پێش ژمارەی مۆبایلەوەیە -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-sky-400 flex items-center">
                            <i class="fa-solid fa-barcode ml-1 text-sky-400"></i> ژمارەی شاسی ئۆتۆمبێل (VIN) *
                        </label>
                        <span x-show="isCheckingChassis" class="text-[10px] text-sky-400 flex items-center gap-1 font-bold animate-pulse">
                            <i class="fa-solid fa-spinner fa-spin"></i> پشکنین...
                        </span>
                    </div>
                    <input type="text" 
                           name="chassis_number" 
                           x-model="chassisNumber" 
                           @input.debounce.400ms="lookupByChassis()" 
                           @change="lookupByChassis()" 
                           @blur="lookupByChassis()" 
                           @keydown.enter.prevent="lookupByChassis(); $refs.phoneInput ? $refs.phoneInput.focus() : null;" 
                           required 
                           autofocus
                           placeholder="17-Digit VIN Number" 
                           dir="ltr"
                           class="w-full bg-slate-900 border border-sky-500/60 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono uppercase focus:border-sky-400 focus:outline-none">

                    <!-- تێبینی کاتێک زانیاری پێشتری هەبێت -->
                    <div x-show="chassisFound && chassisPreviousInfo" x-cloak class="mt-1.5 p-1.5 bg-emerald-950/80 border border-emerald-500/50 rounded-lg text-[11px] text-emerald-300 flex items-center justify-between">
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-emerald-400"></i>
                            <span>زانیاری پێشتری هەیە (خۆکار پڕکرایەوە).</span>
                        </span>
                        <button type="button" @click="clearChassisAutoFill()" class="text-rose-400 hover:text-rose-300 font-bold underline mr-1 text-[10px]">
                            سڕینەوە
                        </button>
                    </div>
                </div>

                <!-- 2. ژمارەی مۆبایلی هاووڵاتی -->
                <div>
                    <label class="block text-xs font-bold text-emerald-400 mb-1.5 flex items-center">
                        <i class="fa-solid fa-phone ml-1 text-emerald-400"></i> ژمارەی مۆبایلی هاووڵاتی *
                    </label>
                    <input type="text" name="phone_number" x-ref="phoneInput" x-model="phoneNumber" placeholder="مثلاً: 0770 123 4567" dir="ltr" 
                           class="w-full bg-slate-900 border border-emerald-500/50 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-emerald-400 focus:outline-none">
                </div>

                <!-- 3. ناوی شۆفێری یەکەم بە کوردی -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-200">
                            ناوى شۆفێرى يه‌كه‌م بە کوردی / عەرەبی *
                        </label>
                        <button type="button" @click="toggleArabicLayout()" class="text-[10px] text-sky-400 bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 px-2 py-0.5 rounded font-sans flex items-center gap-1 transition" title="گۆڕینی دۆخی نووسینی کیبۆرد بۆ عەرەبی ١٠١ یان کوردی فۆنەتیک">
                            <i class="fa-solid fa-keyboard"></i>
                            <span x-text="arabicInputMode === 'ar101' ? 'کیبۆرد: عەرەبی ١٠١' : 'کیبۆرد: فۆنەتیک'"></span>
                        </button>
                    </div>
                    <input type="text" 
                           name="visitor_name" 
                           x-model="visitorName" 
                           @input="onKurdishInput($event, 'visitorName')" 
                           required 
                           placeholder="ناوی سێیانی شۆفێری یەکەم بە کوردی" 
                           dir="rtl" 
                           lang="ckb" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-sans focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none">
                </div>

                <!-- 4. ناوی شۆفێری یەکەم بە ئینگلیزی -->
                <div x-show="isFullBookletForm()">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-200">
                            ناوى شۆفێر يه‌كه‌م بە ئینگلیزی
                        </label>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="autoFillEnglishName('visitorName', 'visitorNameEng')" class="text-[10px] text-amber-400 hover:text-amber-300 font-bold underline flex items-center gap-1" title="وەرگێڕانی ناوە کوردییەکە بۆ ئینگلیزی بە شێوەیەکی خۆکار">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> وەرگێڕانی خۆکار
                            </button>
                            <span class="text-[10px] text-amber-400 bg-amber-500/10 border border-amber-500/30 px-2 py-0.5 rounded font-mono font-bold">
                                CAPITAL (LTR)
                            </span>
                        </div>
                    </div>
                    <input type="text" 
                           name="visitor_name_eng" 
                           x-model="visitorNameEng" 
                           @input="onEnglishInput($event, 'visitorNameEng')" 
                           placeholder="FULL NAME IN ENGLISH (CAPITAL)" 
                           dir="ltr" 
                           lang="en" 
                           style="text-transform: uppercase;" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 uppercase font-mono tracking-wider focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                </div>

                <!-- 5. شۆفێری دووەم بە کوردی -->
                <div x-show="isFullBookletForm()">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300">
                            ناوى شۆفێری دووه‌م بە کوردی (ئارەزوومەندانه)
                        </label>
                        <button type="button" @click="toggleArabicLayout()" class="text-[10px] text-sky-400 bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 px-2 py-0.5 rounded font-sans flex items-center gap-1 transition">
                            <i class="fa-solid fa-keyboard"></i>
                            <span x-text="arabicInputMode === 'ar101' ? 'عەرەبی ١٠١' : 'فۆنەتیک'"></span>
                        </button>
                    </div>
                    <input type="text" 
                           name="second_driver_name" 
                           x-model="secondDriverName" 
                           @input="onKurdishInput($event, 'secondDriverName')" 
                           placeholder="ناوی سێیانی شۆفێری دووەم بە کوردی" 
                           dir="rtl" 
                           lang="ckb" 
                           class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 font-sans focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none">
                </div>

                <!-- 6. شۆفێری دووەم بە ئینگلیزی -->
                <div x-show="isFullBookletForm()">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300">
                            ناوى شۆفێر دووەم بە ئینگلیزی (ئارەزوومەندانە)
                        </label>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="autoFillEnglishName('secondDriverName', 'secondDriverNameEng')" class="text-[10px] text-amber-400 hover:text-amber-300 font-bold underline flex items-center gap-1" title="وەرگێڕانی ناوە کوردییەکە بۆ ئینگلیزی">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> وەرگێڕان
                            </button>
                            <span class="text-[10px] text-amber-400 bg-amber-500/10 border border-amber-500/30 px-2 py-0.5 rounded font-mono font-bold">
                                CAPITAL (LTR)
                            </span>
                        </div>
                    </div>
                    <input type="text" 
                           name="second_driver_name_eng" 
                           x-model="secondDriverNameEng" 
                           @input="onEnglishInput($event, 'secondDriverNameEng')" 
                           placeholder="SECOND DRIVER IN ENGLISH (CAPITAL)" 
                           dir="ltr" 
                           lang="en" 
                           style="text-transform: uppercase;" 
                           class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 uppercase font-mono tracking-wider focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                </div>

                <!-- 7. ژمارەی پاسپۆرت -->
                <div x-show="isFullBookletForm()" x-cloak>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center">
                        <i class="fa-solid fa-passport ml-1.5 text-amber-400"></i> ژمارەی پاسپۆرت (Passport No.)
                    </label>
                    <input type="text" 
                           name="passport_number" 
                           x-model="passportNumber" 
                           @input="passportNumber = $event.target.value.toUpperCase()" 
                           placeholder="مثلاً: A12345678" 
                           dir="ltr" 
                           style="text-transform: uppercase;" 
                           class="w-full bg-slate-900 border border-slate-700 focus:border-amber-400 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:outline-none uppercase">
                </div>

                <!-- 8. بەرواری دەرچوونی ناسنامە / پاسپۆرت -->
                <div x-show="isFullBookletForm()" x-cloak>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center">
                        <i class="fa-solid fa-calendar-day ml-1.5 text-amber-400"></i> بەرواری دەرچوونی ناسنامە / پاسپۆرت
                    </label>
                    <input type="date" 
                           name="passport_issue_date" 
                           x-model="passportIssueDate" 
                           class="w-full bg-slate-900 border border-slate-700 focus:border-amber-400 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:outline-none">
                </div>

                <!-- 9. شوێنی دەرچوونی ناسنامە / پاسپۆرت -->
                <div x-show="isFullBookletForm()" x-cloak>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300 flex items-center">
                            <i class="fa-solid fa-building-columns ml-1.5 text-sky-400"></i> شوێنی دەرچوون (Place of Issue)
                        </label>
                        <button type="button" @click="showPassportPlaceModal = true" class="text-xs text-sky-400 hover:text-sky-300 font-bold flex items-center">
                            <i class="fa-solid fa-plus-circle ml-1"></i> زیادکردنی شوێن
                        </button>
                    </div>
                    <select name="passport_issue_place" x-model="passportIssuePlace" id="passportIssuePlaceSelect" class="w-full bg-slate-900 border border-slate-700 focus:border-sky-400 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none">
                        <option value="">-- هەڵبژاردنی شوێنی دەرچوون --</option>
                        @foreach($passportIssuePlaces as $place)
                            <option value="{{ $place->name }}">{{ $place->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 10. ناونیشان (Address) -->
                <div x-show="isFullBookletForm()" x-cloak>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center">
                        <i class="fa-solid fa-map-location-dot ml-1.5 text-sky-400"></i> ناونیشان (Address)
                    </label>
                    <input type="text" 
                           name="address" 
                           x-model="address" 
                           placeholder="ناونیشانی نیشتەجێبوون (شار، گەڕەک)..." 
                           dir="rtl" 
                           lang="ckb" 
                           class="w-full bg-slate-900 border border-slate-700 focus:border-sky-400 rounded-xl px-3 py-2 text-sm text-slate-100 font-sans focus:outline-none">
                </div>
            </div>
        </div>

        <!-- 2.1 Customs Letter Section (For Cancellation / پووچەڵکردنەوە) -->
        <div x-show="isCancellation()" class="bg-rose-950/40 border border-rose-500/50 rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-rose-500/30 pb-2">
                <h3 class="text-base font-bold text-rose-400 flex items-center">
                    <i class="fa-solid fa-file-contract ml-2"></i> زانیارییەکانی نووسراوی گومرگ (تایبەت بە پووچەڵکردنەوە)
                </h3>
                <span class="text-xs bg-rose-900/60 text-rose-300 border border-rose-700/50 px-2.5 py-1 rounded-lg">
                    <i class="fa-solid fa-lock ml-1 text-amber-400"></i> تایبەتە بە قۆناغی ئەرشیف و سەپمیت
                </span>
            </div>
            <p class="text-xs text-rose-300/80">تێبینی: ژمارە و بەرواری نووسراوی گومرگ لە قۆناغی کۆتایی لەلایەن فەرمانبەری ئەرشیف/سەپمیت پڕدەکرێتەوە (لە داتائەنتەری لەکارخراوە).</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-rose-200 mb-1.5">ژمارەی نووسراوی گومرگ (ناچالاککراوە لە داتائەنتەری)</label>
                    <input type="text" name="no_nusraw_puchal" disabled placeholder="لەلایەن کارمەندی ئەرشیف دیاری دەکرێت" dir="ltr"
                           class="w-full bg-slate-900/50 border border-rose-700/40 rounded-xl px-3 py-2 text-sm text-slate-400 font-mono cursor-not-allowed opacity-75">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-rose-200 mb-1.5">بەرواری نووسراوی گومرگ (ناچالاککراوە لە داتائەنتەری)</label>
                    <input type="date" name="date_nusraw_puchal" disabled
                           class="w-full bg-slate-900/50 border border-rose-700/40 rounded-xl px-3 py-2 text-sm text-slate-400 font-mono cursor-not-allowed opacity-75">
                </div>
            </div>
        </div>

        <!-- 3. Vehicle Specifications -->
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center">
                <i class="fa-solid fa-car ml-2"></i> ٣. تایبەتمەندییەکانی ئۆتۆمبێل
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ژمارەی تابلۆی ئۆتۆمبێل *</label>
                    <input type="text" name="plate_number" x-model="plateNumber" required placeholder="مثلاً: 22 A 12345" dir="ltr"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>

                <!-- Plate Type -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-300">جۆری تابلۆ *</label>
                        <button type="button" @click="showPlateTypeModal = true" class="text-xs text-sky-400 hover:text-sky-300 font-bold flex items-center">
                            <i class="fa-solid fa-list-check ml-1"></i> بەڕێوەبردن & زیادکردن
                        </button>
                    </div>
                    <select name="plate_type_id" x-model="plateTypeId" @change="calculateFees()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        @foreach($plateTypes as $pt)
                            <option value="{{ $pt->id }}">{{ $pt->name_kurdish }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Car Make -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-300">جۆری ئۆتۆمبێل (کوردی)</label>
                        <button type="button" @click="showCarMakeModal = true" class="text-xs text-sky-400 hover:text-sky-300 font-bold flex items-center">
                            <i class="fa-solid fa-list-check ml-1"></i> بەڕێوەبردن & زیادکردن
                        </button>
                    </div>
                    <select name="car_make_id" x-model="carMakeId" @change="onCarMakeChange()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        <option value="">هەڵبژێرە...</option>
                        @foreach($carMakes as $cm)
                            <option value="{{ $cm->id }}" data-eng="{{ $cm->name_english }}">{{ $cm->name_kurdish }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Auto-filled English Car Make preview -->
                <div x-show="isFullBookletForm()">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">جۆری ئۆتۆمبێل بە ئینگلیزی (خۆکار)</label>
                    <input type="text" x-model="carMakeEng" readonly dir="ltr"
                           class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl px-3 py-2 text-sm text-sky-400 font-mono focus:outline-none">
                </div>

                <!-- Model Year -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">مۆدێلی دروستکردن (٤ ژمارە دەبێت) *</label>
                    <input type="text" name="model_year" x-model="modelYear" required maxlength="4" minlength="4" pattern="\d{4}" placeholder="مثلاً: 2024" dir="ltr"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>

                <!-- Car Color -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-300">ڕەنگی ئۆتۆمبێل (کوردی)</label>
                        <button type="button" @click="showCarColorModal = true" class="text-xs text-sky-400 hover:text-sky-300 font-bold flex items-center">
                            <i class="fa-solid fa-list-check ml-1"></i> بەڕێوەبردن & زیادکردن
                        </button>
                    </div>
                    <select name="car_color_id" x-model="carColorId" @change="onCarColorChange()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        <option value="">هەڵبژێرە...</option>
                        @foreach($carColors as $cc)
                            <option value="{{ $cc->id }}" data-eng="{{ $cc->name_english }}">{{ $cc->name_kurdish }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Auto-filled English Color preview -->
                <div x-show="isFullBookletForm()">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">ڕەنگ بە ئینگلیزی (خۆکار)</label>
                    <input type="text" x-model="carColorEng" readonly dir="ltr"
                           class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl px-3 py-2 text-sm text-sky-400 font-mono focus:outline-none">
                </div>

                <!-- Piston count -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ژمارەی بستۆن</label>
                    <input type="number" name="piston_count" x-model="pistonCount" min="1" max="16"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>

                <!-- Salana Number -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ژمارەی ساڵانەی ئۆتۆمبێل</label>
                    <input type="text" name="salana_number" x-model="salanaNumber" placeholder="ژمارەی فەرمی ساڵانە" dir="ltr"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- 4. Dynamic Duration & Dates Section -->
        <div x-show="isFullBookletForm()" class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center">
                <i class="fa-solid fa-calendar-days ml-2"></i> ٤. ماوەی دەفتەر و بەروارەکان
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-sky-300 mb-1.5">ماوەی دەفتەر (ساڵ)</label>
                    <select name="num_years" x-model="numYears" @change="updateDates(); calculateFees();" class="w-full bg-sky-950 border border-sky-500/50 rounded-xl px-3 py-2 text-sm text-sky-200 font-bold focus:outline-none">
                        <option value="1">١ ساڵ</option>
                        <option value="2">٢ ساڵ</option>
                        <option value="3">٣ ساڵ</option>
                        <option value="4">٤ ساڵ</option>
                        <option value="5">٥ ساڵ</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">بەرواری دەستپێکردن (Start Date)</label>
                    <input type="date" name="start_date" x-model="startDate" @change="updateDates(); calculateFees();"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">بەرواری بەسەرچوون (End Date)</label>
                    <input type="date" name="end_date" x-model="endDate" @change="calculateFees()"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- 5. Vehicle Conditions -->
        <div x-show="isFullBookletForm()" class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center">
                <i class="fa-solid fa-list-check ml-2"></i> ٥. باری ئۆتۆمبێل (باشە / باش نییە)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 1. زیادەبار -->
                <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-700 space-y-2">
                    <label class="block text-xs font-bold text-slate-200">زیادەبار (Overload)</label>
                    <select name="zedabar" x-model="zedabar" @change="onZedabarChange()" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-xs text-slate-100">
                        <option value="باشە">باشە (Good)</option>
                        <option value="باش نییە">باش نییە (NO)</option>
                    </select>
                    <input type="hidden" name="zedabar_eng" x-model="zedabarEng">
                </div>

                <!-- 2. کەموکورتی -->
                <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-700 space-y-2">
                    <label class="block text-xs font-bold text-slate-200">کەموکورتی (Defects)</label>
                    <select name="kamukurty" x-model="kamukurty" @change="onKamukurtyChange()" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-xs text-slate-100">
                        <option value="باشە">باشە (Good)</option>
                        <option value="باش نییە">باش نییە (NO)</option>
                    </select>
                    <input type="hidden" name="kamukurty_eng" x-model="kamukurtyEng">
                </div>

                <!-- 3. باری گشتی -->
                <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-700 space-y-2">
                    <label class="block text-xs font-bold text-slate-200">باری گشتی (General Condition)</label>
                    <select name="bary_gshty" x-model="baryGshty" @change="onBaryGshtyChange()" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-xs text-slate-100">
                        <option value="باشە">باشە (Good)</option>
                        <option value="باش نییە">باش نییە (NO)</option>
                    </select>
                    <input type="hidden" name="bary_gshty_eng" x-model="baryGshtyEng">
                </div>
            </div>
        </div>


        <!-- 6. Dynamic Live Fee Breakdown Box -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 border-2 border-sky-500/50 rounded-2xl p-6 shadow-2xl space-y-4">
            <h3 class="text-base font-extrabold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span class="flex items-center">
                    <i class="fa-solid fa-calculator ml-2 text-emerald-400"></i> ٦. هەژمارکردنی خۆکاری بڕی پارە و نرخەکان
                </span>
                <span class="text-xs bg-emerald-500/20 text-emerald-300 px-3 py-1 rounded-full font-mono font-bold" x-text="'کۆی گشتی: ' + Number(fees.total_pay).toLocaleString() + ' د.ع'"></span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-center">
                <div class="p-3 bg-slate-900/80 border border-slate-700/60 rounded-xl">
                    <span class="text-[11px] text-slate-400 block mb-1">پارەی ساڵەکان</span>
                    <span class="text-sm font-extrabold text-sky-300" x-text="Number(fees.pay_amount_years).toLocaleString() + ' د.ع'"></span>
                </div>
                <div class="p-3 bg-slate-900/80 border border-slate-700/60 rounded-xl">
                    <span class="text-[11px] text-slate-400 block mb-1">پول (Stamp)</span>
                    <span class="text-sm font-extrabold text-slate-200" x-text="Number(fees.pay_stamp).toLocaleString() + ' د.ع'"></span>
                </div>
                <div class="p-3 bg-slate-900/80 border border-slate-700/60 rounded-xl">
                    <span class="text-[11px] text-slate-400 block mb-1">فۆڕم</span>
                    <span class="text-sm font-extrabold text-slate-200" x-text="Number(fees.pay_form).toLocaleString() + ' د.ع'"></span>
                </div>
                <div class="p-3 bg-slate-900/80 border border-slate-700/60 rounded-xl">
                    <span class="text-[11px] text-slate-400 block mb-1">سزای دواکەوتن</span>
                    <span class="text-sm font-extrabold text-amber-400" x-text="Number(fees.pay_fine).toLocaleString() + ' د.ع'"></span>
                </div>
                <div class="p-3 bg-slate-900/80 border border-slate-700/60 rounded-xl">
                    <span class="text-[11px] text-slate-400 block mb-1">کەشف (Inspection)</span>
                    <span class="text-sm font-extrabold text-indigo-300" x-text="Number(fees.pay_inspection).toLocaleString() + ' د.ع'"></span>
                </div>
                <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl">
                    <span class="text-[11px] text-emerald-400 block mb-1 font-bold">کۆی وەرگیراو</span>
                    <span class="text-base font-black text-emerald-300" x-text="Number(fees.total_pay).toLocaleString() + ' د.ع'"></span>
                </div>
            </div>
        </div>

        <!-- Submit Button Section (Optimized for Mobile & Desktop) -->
        <div class="pt-6 pb-36 sm:pb-16">
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 border-2 border-emerald-500/50 rounded-2xl p-5 sm:p-6 shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-right w-full sm:w-auto">
                    <span class="text-sm sm:text-base font-extrabold text-white flex items-center">
                        <i class="fa-solid fa-circle-check text-emerald-400 ml-2"></i> هەموو زانیارییەکان تەواون؟
                    </span>
                    <span class="text-xs text-slate-400 block mt-1">
                        دوای داگرتنی ئەم دوگمەیە، مامەڵەکە تۆمار دەکرێت و دەتوانیت بارکۆد و فیشەکەی چاپ بکەیت.
                    </span>
                </div>
                
                <button type="submit" 
                        class="w-full sm:w-auto px-10 py-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-sm sm:text-base rounded-2xl shadow-xl shadow-emerald-600/40 transition active:scale-95 flex items-center justify-center gap-2 border border-emerald-400/50 cursor-pointer">
                    <i class="fa-solid fa-check-circle text-xl"></i>
                    <span>تۆمارکردنی مامەڵە و دەرهێنانی بارکۆد</span>
                </button>
            </div>
        </div>
    </form>

    <!-- INLINE MODAL 1: Manage & Add Transaction Types -->
    <div x-show="showTxTypeModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-list-check ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی جۆری مامەڵە</span>
                <button @click="showTxTypeModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <!-- Existing list table with edit -->
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
                                    <button type="button" @click="updateTransactionType({{ $tt->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold">پاشەکەوت</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Form to add new -->
            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی جۆری نوێ</h4>
                <div class="flex gap-2">
                    <input type="text" x-model="newTxTypeName" placeholder="ناوی جۆری نوێی مامەڵە..." class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100">
                    <button type="button" @click="addTransactionType()" class="px-4 py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow">زیادکردن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 2: Manage & Add Directorate -->
    <div x-show="showDirectorateModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-building-flag ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی بەڕێوەبەرایەتی</span>
                <button @click="showDirectorateModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
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
                                    <button type="button" @click="updateDirectorate({{ $dir->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold">پاشەکەوت</button>
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
                    <button type="button" @click="addDirectorate()" class="px-4 py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow">زیادکردن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 3: Manage & Add Director -->
    <div x-show="showDirectorModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-user-tie ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی بەڕێوەبەر / لێپرسراو</span>
                <button @click="showDirectorModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
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
                                    <button type="button" @click="updateDirector({{ $d->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold">پاشەکەوت</button>
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
                    <button type="button" @click="addDirector()" class="px-4 py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow">زیادکردن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 4: Manage & Add Plate Type -->
    <div x-show="showPlateTypeModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-rectangle-ad ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی جۆری تابلۆ</span>
                <button @click="showPlateTypeModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
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
                                    <button type="button" @click="updatePlateType({{ $pt->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold">پاشەکەوت</button>
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
                    <button type="button" @click="addPlateType()" class="px-4 py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow">زیادکردن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 5: Manage & Add Car Make -->
    <div x-show="showCarMakeModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-car-side ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی مارکەی ئۆتۆمبێل</span>
                <button @click="showCarMakeModal = false; carMakeSearch = ''" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <!-- Live Search Box at Top of Modal (Kurdish & English) -->
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-sky-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                    گەڕان لە مارکە تۆمارکراوەکان (Search Existing Makes):
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
                            class="absolute left-2.5 top-2 text-slate-400 hover:text-white text-xs transition">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Existing Makes Table with instant live filtering -->
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
                                        <button type="button" @click="selectCarMakeFromModal({{ $cm->id }}, '{{ addslashes($cm->name_english) }}')" class="px-2 py-1 bg-sky-600 hover:bg-sky-500 text-white rounded text-[10px] font-bold shadow transition" title="هەڵبژاردنی ئەم مارکەیە بۆ فۆڕم">
                                            هەڵبژاردن
                                        </button>
                                        <button type="button" @click="updateCarMake({{ $cm->id }})" class="px-2 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-[10px] font-bold shadow transition" title="پاشەکەوتکردنی دەستکاری">
                                            پاشەکەوت
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- New Make Addition Form at Bottom -->
            <div class="space-y-2.5 pt-2 border-t border-slate-800">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی مارکەی نوێ (ئەگەر لە سەرەوە نەبوو):</h4>
                    <span x-show="carMakeSearch" class="text-[10px] text-slate-400">
                        دەتوانیت لەم خانانەی خوارەوە ناوی نوێ بنووسیت
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] text-slate-400 block mb-1">ناوی کوردی:</label>
                        <input type="text" x-model="newMakeKurdish" placeholder="مثلاً: هۆندا" class="w-full bg-slate-800 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100">
                    </div>
                    <div>
                        <label class="text-[10px] text-slate-400 block mb-1">ناوی ئینگلیزی (English):</label>
                        <input type="text" x-model="newMakeEnglish" placeholder="e.g. Honda" dir="ltr" class="w-full bg-slate-800 border border-slate-700 focus:border-sky-500 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono">
                    </div>
                </div>
                <button type="button" @click="addCarMake()" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>زیادکردنی مارکەی نوێ و هەڵبژاردن</span>
                </button>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 6: Manage & Add Car Color -->
    <div x-show="showCarColorModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-palette ml-2 text-sky-400"></i> بەڕێوەبردن و زیادکردنی ڕەنگی ئۆتۆمبێل</span>
                <button @click="showCarColorModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
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
                                    <button type="button" @click="updateCarColor({{ $cc->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold">پاشەکەوت</button>
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
                <button type="button" @click="addCarColor()" class="w-full py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow">زیادکردن</button>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL 7: Manage & Add Passport Issue Place -->
    <div x-show="showPassportPlaceModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-map-location-dot ml-2 text-sky-400"></i> بەڕێوەبردن و دەستکاریکردنی شوێنەکانی دەرچوون</span>
                <button type="button" @click="showPassportPlaceModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <!-- Existing Places List (Table with Edit and Delete) -->
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
                                    <button type="button" @click="updatePassportPlace({{ $place->id }})" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-[11px] font-bold transition">پاشەکەوت</button>
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="deletePassportPlace({{ $place->id }})" class="px-2.5 py-1 bg-rose-600/80 hover:bg-rose-600 text-white rounded text-[11px] font-bold transition" title="سڕینەوە"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Form to add new -->
            <div class="space-y-2 pt-1">
                <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی شوێنی نوێ</h4>
                <div class="flex gap-2">
                    <input type="text" x-model="newPassportPlaceName" @keydown.enter.prevent="addPassportPlace()" placeholder="ناوی شوێنی دەرچوون (شار، پارێزگا)..." class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100 focus:border-sky-500 focus:outline-none">
                    <button type="button" @click="addPassportPlace()" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-plus"></i> زیادکردن
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-end pt-2 border-t border-slate-800">
                <button type="button" @click="showPassportPlaceModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition">
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

            arabicInputMode: 'ar101', // 'ar101' (standard Arabic 101 keys) or 'kurdish' (phonetic)

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

                // Auto-suggest English name if English field is empty
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

            autoFillEnglishName(sourceField, targetField) {
                const src = this[sourceField];
                if (src && src.trim() && window.LangConvert) {
                    this[targetField] = window.LangConvert.transliterate(src);
                }
            },

            // Previous transactions live search
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
                // 1: دەرهێنانی دەفتەر, 2: تازەکردنەوە, 3: ناوگۆڕین, 4: دەفتەرگۆڕین, 7: ناوگۆڕین و دەفتەرگۆڕین
                return [1, 2, 3, 4, 7].includes(parseInt(this.typeId));
            },

            isCancellationOrInspection() {
                // 5: پووچەڵکردنەوە, 8: پشکنین, 6: بەڵێننامە
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

                if (typeId === 6) { // بەڵێننامە
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

                // Instant UI update (0ms response)
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

            // Helper for handling fetch response with duplicate checking
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

            // Inline Updates
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

            // Inline Additions
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
                        opt.text = data.item.name_kurdish;
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
                        opt.text = data.item.name_kurdish;
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
                                    <button type="button" onclick="window.activeTxForm?.updatePassportPlace(${data.item.id})" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-[11px] font-bold transition">پاشەکەوت</button>
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="window.activeTxForm?.deletePassportPlace(${data.item.id})" class="px-2.5 py-1 bg-rose-600/80 hover:bg-rose-600 text-white rounded text-[11px] font-bold transition" title="سڕینەوە"><i class="fa-solid fa-trash"></i></button>
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
