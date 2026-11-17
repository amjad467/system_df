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

    $makesList = $carMakes->map(function($m) {
        return [
            'id' => $m->id,
            'name_kurdish' => $m->name_kurdish,
            'name_english' => $m->name_english ?? '',
        ];
    })->values();
@endphp

<div x-data="unifiedTransactionForm(
        {{ \Illuminate\Support\Js::from($fromTransaction) }}, 
        {{ $targetTypeId ? (int)$targetTypeId : 'null' }}, 
        {{ \Illuminate\Support\Js::from($txRates) }},
        {{ \Illuminate\Support\Js::from($makesList) }}
     )" 
     @keydown.window.alt.s.prevent="submitForm()"
     class="max-w-7xl mx-auto space-y-4 pb-12 text-slate-800 dark:text-slate-100">

    <!-- Validation Error Alert Banner -->
    <div x-show="validationError" 
         x-cloak 
         x-transition 
         class="p-3 bg-rose-500/15 border-2 border-rose-500/60 rounded-xl text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-base animate-bounce"></i>
            <span x-text="validationError"></span>
        </div>
        <button type="button" @click="validationError = ''" class="text-rose-400 hover:text-rose-200">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Top Compact Header & Type Switcher -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800/80 pb-2.5">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center shadow-md text-white text-base shrink-0">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>تۆمارکردنی مامەڵەی نوێ</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-500/30" x-text="getTypeName()"></span>
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">فۆرمی یەکگرتووی دەفتەری گەشتیاری و گومرگی سلێمانی</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg border border-slate-200 dark:border-slate-700 font-mono">
                    <kbd class="text-sky-600 dark:text-sky-400 font-bold">Alt + S</kbd> پاشەکەوت
                </span>
                <a href="{{ auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver') ? route('transactions.index') : route('dashboard') }}" 
                   class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    <span>گەڕانەوە</span>
                </a>
            </div>
        </div>

        <!-- Horizontal Scrollable/Grid Transaction Type Pills -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-1.5">
            @foreach($transactionTypes as $tt)
                <button type="button" 
                        @click="typeId = {{ $tt->id }}; calculateFees();" 
                        :class="typeId == {{ $tt->id }} ? 'bg-sky-600 text-white border-sky-500 shadow-md ring-2 ring-sky-500/40 font-bold' : 'bg-slate-50 hover:bg-slate-100 dark:bg-slate-950/60 dark:hover:bg-slate-800/80 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800'" 
                        class="p-2 rounded-xl border text-center transition flex flex-col items-center justify-center gap-1 cursor-pointer">
                    <i class="fa-solid {{ $tt->id == 1 ? 'fa-book' : ($tt->id == 2 ? 'fa-rotate-right' : ($tt->id == 3 ? 'fa-pen-to-square' : ($tt->id == 4 ? 'fa-id-card' : ($tt->id == 5 ? 'fa-ban' : ($tt->id == 6 ? 'fa-handshake' : ($tt->id == 7 ? 'fa-arrows-rotate' : 'fa-magnifying-glass')))))) }} text-xs"></i>
                    <span class="text-[11px] leading-tight truncate w-full">{{ $tt->name_kurdish }}</span>
                </button>
            @endforeach
        </div>

        <!-- Dual Issuance Notice -->
        <div x-show="typeId == 1" x-cloak class="p-2.5 bg-sky-50 dark:bg-sky-950/30 border border-sky-300 dark:border-sky-500/30 rounded-xl flex items-center justify-between text-xs text-sky-800 dark:text-sky-300">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-sky-600 dark:text-sky-400"></i>
                <span><strong>خۆکاری:</strong> لەگەڵ دەرهێنانی دەفتەر، مامەڵەی بەڵێننامەش دروست دەبێت و هەردووکیان لە <strong>یەک فیشەی داتائەنتەری</strong> چاپ دەکرێن.</span>
            </div>
            <span class="text-[10px] bg-sky-200/80 dark:bg-sky-900/60 text-sky-900 dark:text-sky-200 px-2 py-0.5 rounded-md font-bold">۲ لە ١</span>
        </div>
    </div>

    <!-- MAIN SINGLE COMPACT FORM (3 COLUMNS) -->
    <form action="{{ route('transactions.store') }}" 
          method="POST" 
          id="mainTransactionForm" 
          @submit.prevent="submitForm()" 
          class="grid grid-cols-1 lg:grid-cols-12 gap-3.5">
        @csrf
        <input type="hidden" name="transaction_type_id" :value="typeId">
        <input type="hidden" name="parent_transaction_id" :value="parentTransactionId">
        <input type="hidden" name="num_years" :value="numYears">
        <input type="hidden" name="start_date" :value="startDate">
        <input type="hidden" name="end_date" :value="endDate">
        <input type="hidden" name="car_make_id" :value="carMakeId">

        <!-- ==========================================
             COLUMN 1: VEHICLE & CHASSIS (Lg: 4 Cols)
             ========================================== -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm space-y-3 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-black text-sky-600 dark:text-sky-400 flex items-center gap-1.5 uppercase">
                        <i class="fa-solid fa-car"></i>
                        <span>١. زانیاری ئۆتۆمبێل و تابلۆ</span>
                    </h3>
                    <span class="text-[10px] text-slate-400 font-mono">VIN & Plate</span>
                </div>

                <!-- CHASSIS NUMBER (SPOTLIGHT WITH INSTANT LOOKUP) -->
                <div class="p-2.5 bg-slate-50 dark:bg-slate-950/80 border-2 rounded-xl space-y-1.5 transition"
                     :class="missingField === 'chassisNumber' ? 'border-rose-500 ring-2 ring-rose-500/30' : (chassisFound ? 'border-emerald-500/60' : 'border-sky-500/40')">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black text-slate-800 dark:text-sky-300 flex items-center gap-1">
                            <i class="fa-solid fa-barcode text-sky-500"></i>
                            <span>ژمارەی شاسی (Chassis VIN) *</span>
                        </label>
                        <span x-show="isCheckingChassis" x-cloak class="text-[11px] text-sky-500 flex items-center gap-1 font-bold animate-pulse font-mono">
                            <i class="fa-solid fa-spinner fa-spin"></i> پشکنین...
                        </span>
                    </div>

                    <input type="text" 
                           id="input_chassisNumber"
                           name="chassis_number" 
                           x-model="chassisNumber" 
                           @input="onChassisInput($event)" 
                           @blur="lookupByChassis()" 
                           @keydown.enter.prevent="lookupByChassis(); focusNext('visitorName')" 
                           required 
                           autofocus
                           dir="ltr"
                           lang="en"
                           style="text-transform: uppercase;"
                           placeholder="17-DIGIT CHASSIS VIN NUMBER" 
                           class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 focus:border-sky-500 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 font-mono font-bold tracking-widest uppercase transition">

                    <!-- Found Alert Badge -->
                    <div x-show="chassisFound && chassisPreviousInfo" x-cloak class="p-1.5 bg-emerald-500/15 border border-emerald-500/40 rounded-lg text-[11px] text-emerald-700 dark:text-emerald-300 flex items-center justify-between">
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            <span>شاسی دۆزرایەوە؛ زانیارییەکان هێنرانەوە.</span>
                        </span>
                        <button type="button" @click="clearChassisAutoFill()" class="text-rose-500 hover:text-rose-400 font-bold underline text-[10px] cursor-pointer">
                            سڕینەوە
                        </button>
                    </div>
                </div>

                <!-- Plate Number & Plate Type -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">ژمارەی تابلۆ *</label>
                        <input type="text" 
                               id="input_plateNumber"
                               name="plate_number" 
                               x-model="plateNumber" 
                               required 
                               placeholder="مثلاً: 22 A 12345" 
                               dir="ltr"
                               :class="missingField === 'plateNumber' ? 'border-rose-500 ring-2 ring-rose-500/30' : 'border-slate-300 dark:border-slate-700'"
                               class="w-full bg-white dark:bg-slate-950 border focus:border-sky-500 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 font-mono font-bold tracking-wider transition">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">جۆری تابلۆ *</label>
                        <select name="plate_type_id" 
                                x-model="plateTypeId" 
                                @change="calculateFees()" 
                                class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-sky-500 rounded-lg px-2 py-1.5 text-xs text-slate-900 dark:text-slate-100 focus:outline-none">
                            @foreach($plateTypes as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->clean_kurdish_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- CAR MAKE: SEARCHABLE COMBOBOX (Item 5) -->
                <div class="relative" x-data="{ openMakeDropdown: false, makeSearch: '' }">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-700 dark:text-slate-300">جۆری ئۆتۆمبێل (مارکە) *</label>
                        <button type="button" @click="showCarMakeModal = true" class="text-[10px] text-sky-600 dark:text-sky-400 hover:underline">
                            <i class="fa-solid fa-plus-circle"></i> نوێ
                        </button>
                    </div>

                    <!-- Searchable Input trigger -->
                    <div class="relative">
                        <input type="text" 
                               id="input_carMakeId"
                               x-model="selectedMakeKurdish" 
                               @focus="openMakeDropdown = true" 
                               @input="openMakeDropdown = true; filterMakes($event.target.value)" 
                               placeholder="بگەڕێ یان دیاریبکە (تۆیوتا، کیا...)" 
                               :class="missingField === 'carMakeId' ? 'border-rose-500 ring-2 ring-rose-500/30' : 'border-slate-300 dark:border-slate-700'"
                               class="w-full bg-white dark:bg-slate-950 border focus:border-sky-500 rounded-lg pr-2.5 pl-8 py-1.5 text-xs text-slate-900 dark:text-slate-100 font-bold transition">
                        
                        <button type="button" 
                                @click="openMakeDropdown = !openMakeDropdown" 
                                class="absolute left-2.5 top-2 text-slate-400 hover:text-sky-500 text-xs">
                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </button>
                    </div>

                    <!-- Searchable Dropdown List (Strictly Kurdish names, auto-fills English) -->
                    <div x-show="openMakeDropdown" 
                         @click.away="openMakeDropdown = false" 
                         x-cloak 
                         class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl shadow-2xl max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="make in filteredMakes" :key="make.id">
                            <div @click="selectCarMake(make); openMakeDropdown = false;" 
                                 class="px-3 py-2 hover:bg-sky-50 dark:hover:bg-slate-800 cursor-pointer flex items-center justify-between text-xs transition">
                                <span class="font-bold text-slate-900 dark:text-white" x-text="make.name_kurdish"></span>
                                <span class="text-[10px] font-mono text-slate-400" x-text="make.name_english"></span>
                            </div>
                        </template>
                        <div x-show="filteredMakes.length === 0" class="p-3 text-center text-xs text-slate-400">
                            هیچ مارکەیەک نەدۆزرایەوە.
                        </div>
                    </div>
                </div>

                <!-- Auto English Car Make Preview -->
                <div x-show="isFullBookletForm()">
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">مارکە بە ئینگلیزی (خۆکار)</label>
                    <input type="text" 
                           x-model="carMakeEng" 
                           readonly 
                           dir="ltr"
                           placeholder="ENGLISH MAKE AUTO-POPULATED"
                           class="w-full bg-slate-100 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-sky-600 dark:text-sky-400 font-mono font-bold tracking-wider">
                </div>

                <!-- Model Year & Color Grid -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">مۆدێل (ساڵ) *</label>
                        <input type="text" 
                               id="input_modelYear"
                               name="model_year" 
                               x-model="modelYear" 
                               required 
                               maxlength="4" 
                               placeholder="2024" 
                               dir="ltr"
                               :class="missingField === 'modelYear' ? 'border-rose-500 ring-2 ring-rose-500/30' : 'border-slate-300 dark:border-slate-700'"
                               class="w-full bg-white dark:bg-slate-950 border focus:border-sky-500 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 font-mono font-bold transition">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">ڕەنگ *</label>
                        <select name="car_color_id" 
                                id="input_carColorId"
                                x-model="carColorId" 
                                @change="onCarColorChange()" 
                                :class="missingField === 'carColorId' ? 'border-rose-500 ring-2 ring-rose-500/30' : 'border-slate-300 dark:border-slate-700'"
                                class="w-full bg-white dark:bg-slate-950 border focus:border-sky-500 rounded-lg px-2 py-1.5 text-xs text-slate-900 dark:text-slate-100 focus:outline-none">
                            <option value="">هەڵبژێرە...</option>
                            @foreach($carColors as $cc)
                                <option value="{{ $cc->id }}" data-eng="{{ $cc->name_english }}">{{ $cc->name_kurdish }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Pistons & Salana Number -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">ژمارەی پستۆن</label>
                        <input type="number" 
                               name="piston_count" 
                               x-model="pistonCount" 
                               min="1" 
                               max="16" 
                               class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 font-mono">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">ژمارەی ساڵانە</label>
                        <input type="text" 
                               name="salana_number" 
                               x-model="salanaNumber" 
                               placeholder="ئارەزوومەندانە" 
                               dir="ltr"
                               class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 font-mono">
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             COLUMN 2: CITIZEN & DRIVERS (Lg: 4 Cols)
             ========================================== -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm space-y-3 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-black text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 uppercase">
                        <i class="fa-solid fa-user-check"></i>
                        <span>٢. هاووڵاتی و شۆفێر</span>
                    </h3>
                    <span class="text-[10px] text-slate-400 font-mono">Citizen / Driver</span>
                </div>

                <!-- First Driver Name (Kurdish) -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">ناوی شۆفێری یەکەم (کوردی) *</label>
                    <input type="text" 
                           id="input_visitorName"
                           name="visitor_name" 
                           x-model="visitorName" 
                           @input="onKurdishInput($event, 'visitorName')" 
                           @blur="if ((!visitorNameEng || visitorNameEng.trim() === '') && visitorName) { autoFillEnglishName('visitorName', 'visitorNameEng'); }"
                           required 
                           placeholder="ناوی سیانی بە کوردی" 
                           dir="rtl" 
                           lang="ckb" 
                           :class="missingField === 'visitorName' ? 'border-rose-500 ring-2 ring-rose-500/30' : 'border-slate-300 dark:border-slate-700'"
                           class="w-full bg-white dark:bg-slate-950 border focus:border-sky-500 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 font-bold transition">
                </div>

                <!-- First Driver English Name -->
                <div x-show="isFullBookletForm()">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400">ناوی ئینگلیزی (خۆکار وەرگێڕدراو)</label>
                        <button type="button" @click="autoFillEnglishName('visitorName', 'visitorNameEng')" class="text-[10px] text-amber-500 hover:underline">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> وەرگێڕان
                        </button>
                    </div>
                    <input type="text" 
                           name="visitor_name_eng" 
                           x-model="visitorNameEng" 
                           @focus="if ((!visitorNameEng || visitorNameEng.trim() === '') && visitorName) { autoFillEnglishName('visitorName', 'visitorNameEng'); }"
                           @input="onEnglishInput($event, 'visitorNameEng')" 
                           placeholder="FULL NAME IN ENGLISH (CAPITAL)" 
                           dir="ltr" 
                           lang="en" 
                           style="text-transform: uppercase;" 
                           class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-amber-500 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 uppercase font-mono tracking-wider transition">
                </div>

                <!-- Phone Number -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">ژمارەی مۆبایل *</label>
                    <input type="text" 
                           id="input_phoneNumber"
                           name="phone_number" 
                           x-model="phoneNumber" 
                           required 
                           placeholder="0770 123 4567" 
                           dir="ltr" 
                           :class="missingField === 'phoneNumber' ? 'border-rose-500 ring-2 ring-rose-500/30' : 'border-slate-300 dark:border-slate-700'"
                           class="w-full bg-white dark:bg-slate-950 border focus:border-emerald-500 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 font-mono tracking-wider transition">
                </div>

                <!-- Passport / ID Number & Place Grid -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">ژمارەی پاسپۆرت / پێناس</label>
                        <input type="text" 
                               name="passport_number" 
                               x-model="passportNumber" 
                               placeholder="A12345678" 
                               dir="ltr" 
                               class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 font-mono">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">شوێنی دەرچوون</label>
                        <input type="text" 
                               name="passport_issue_place" 
                               x-model="passportIssuePlace" 
                               placeholder="سلێمانی، هەولێر..." 
                               class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100">
                    </div>
                </div>

                <!-- Second Driver Name (Optional) -->
                <div x-show="isFullBookletForm()" class="space-y-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">ناوی شۆفێری دووەم (ئارەزوومەندانە)</label>
                        <input type="text" 
                               name="second_driver_name" 
                               x-model="secondDriverName" 
                               @input="onKurdishInput($event, 'secondDriverName')" 
                               @blur="if ((!secondDriverNameEng || secondDriverNameEng.trim() === '') && secondDriverName) { autoFillEnglishName('secondDriverName', 'secondDriverNameEng'); }"
                               placeholder="ناوی شۆفێری دووەم بە کوردی" 
                               dir="rtl" 
                               class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100">
                    </div>

                    <div>
                        <input type="text" 
                               name="second_driver_name_eng" 
                               x-model="secondDriverNameEng" 
                               @input="onEnglishInput($event, 'secondDriverNameEng')" 
                               placeholder="SECOND DRIVER ENGLISH (CAPITAL)" 
                               dir="ltr" 
                               style="text-transform: uppercase;" 
                               class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 uppercase font-mono tracking-wider">
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             COLUMN 3: OFFICIAL & FEES (Lg: 4 Cols)
             ========================================== -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm space-y-3 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-black text-amber-600 dark:text-amber-400 flex items-center gap-1.5 uppercase">
                        <i class="fa-solid fa-receipt"></i>
                        <span>٣. ڕێکخستنی فەرمی و ڕسوومات</span>
                    </h3>
                    <span class="text-[10px] text-slate-400 font-mono">Rates & Officers</span>
                </div>

                <!-- Directorate & Director Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">بەڕێوەبەرایەتی هاتوچۆ</label>
                        <select name="traffic_directorate_id" 
                                x-model="directorateId" 
                                class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs text-slate-900 dark:text-slate-100 focus:outline-none">
                            @foreach($directorates as $dir)
                                <option value="{{ $dir->id }}">{{ $dir->name_kurdish }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">بەڕێوەبەر / لێپرسراو</label>
                        <select name="director_id" 
                                x-model="directorId" 
                                class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs text-slate-900 dark:text-slate-100 focus:outline-none">
                            @foreach($directors as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Validity Duration (Years) -->
                <div x-show="isFullBookletForm()" class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">ماوەی دەفتەر (ساڵ)</label>
                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" 
                                @click="numYears = 1; updateDates(); calculateFees();" 
                                :class="numYears == 1 ? 'bg-sky-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-800'"
                                class="py-1 rounded-lg text-xs transition cursor-pointer">
                            ١ ساڵ
                        </button>
                        <button type="button" 
                                @click="numYears = 2; updateDates(); calculateFees();" 
                                :class="numYears == 2 ? 'bg-sky-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-800'"
                                class="py-1 rounded-lg text-xs transition cursor-pointer">
                            ٢ ساڵ
                        </button>
                        <button type="button" 
                                @click="numYears = 3; updateDates(); calculateFees();" 
                                :class="numYears == 3 ? 'bg-sky-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-800'"
                                class="py-1 rounded-lg text-xs transition cursor-pointer">
                            ٣ ساڵ
                        </button>
                    </div>
                </div>

                <!-- Live Fee Calculation Breakdown Card -->
                <div class="p-3 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-xl space-y-2">
                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 pb-1 flex items-center justify-between">
                        <span>وردەکاری ڕسووماتەکان</span>
                        <span class="font-mono text-emerald-600 dark:text-emerald-400">IQD</span>
                    </div>

                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>ڕسووماتی مامەڵە:</span>
                            <span class="font-mono font-bold" x-text="formatNumber(fees.mainPay)"></span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>مۆری باج و گومرگ:</span>
                            <span class="font-mono" x-text="formatNumber(fees.stampPay)"></span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>فۆڕم و پسوولە:</span>
                            <span class="font-mono" x-text="formatNumber(fees.formPay)"></span>
                        </div>
                        <div x-show="typeId == 1" class="flex justify-between text-sky-600 dark:text-sky-400 font-semibold">
                            <span>بەڵێننامەی یاسایی:</span>
                            <span class="font-mono" x-text="formatNumber(fees.pledgeTotal)"></span>
                        </div>
                    </div>

                    <div class="pt-2 border-t-2 border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <span class="text-xs font-black text-slate-900 dark:text-white">کۆی کۆتایی:</span>
                        <div class="text-left">
                            <span class="text-base font-black font-mono text-emerald-600 dark:text-emerald-400" x-text="formatNumber(fees.totalPay + (typeId == 1 ? fees.pledgeTotal : 0))"></span>
                            <span class="text-[10px] text-slate-500 block">دیناری عێراقی</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button (Direct Executive Action) -->
            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk text-base"></i>
                    <span>پاشەکەوتکردنی مامەڵە (Alt + S)</span>
                </button>
            </div>
        </div>
    </form>

    <!-- INLINE MODAL: Quick Add Car Make -->
    <div x-show="showCarMakeModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl max-w-md w-full p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-car text-sky-500"></i>
                    <span>زیادکردنی مارکەی نوێ</span>
                </h3>
                <button type="button" @click="showCarMakeModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ناوی کوردی *</label>
                    <input type="text" x-model="newMakeKurdish" placeholder="مثلاً: تۆیوتا" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ناوی ئینگلیزی (English) *</label>
                    <input type="text" x-model="newMakeEnglish" placeholder="TOYOTA" dir="ltr" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 uppercase font-mono">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCarMakeModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold rounded-xl">داخستن</button>
                    <button type="button" @click="addCarMake()" class="px-5 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow">زیادکردن</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function unifiedTransactionForm(initialTx, targetTypeId, txRates, makesList) {
    return {
        typeId: targetTypeId || (initialTx ? initialTx.transaction_type_id : 1),
        parentTransactionId: initialTx ? initialTx.id : '',
        numYears: 1,
        startDate: '{{ date("Y-m-d") }}',
        endDate: '{{ date("Y-m-d", strtotime("+1 year")) }}',

        // Driver details
        visitorName: initialTx ? (initialTx.visitor_name || '') : '',
        visitorNameEng: initialTx ? (initialTx.visitor_name_eng || '') : '',
        phoneNumber: initialTx ? (initialTx.phone_number || '') : '',
        passportNumber: initialTx ? (initialTx.passport_number || '') : '',
        passportIssuePlace: initialTx ? (initialTx.passport_issue_place || '') : '',
        secondDriverName: initialTx ? (initialTx.second_driver_name || '') : '',
        secondDriverNameEng: initialTx ? (initialTx.second_driver_name_eng || '') : '',

        // Vehicle details
        chassisNumber: initialTx ? (initialTx.chassis_number || '') : '',
        plateNumber: initialTx ? (initialTx.plate_number || '') : '',
        plateTypeId: initialTx ? (initialTx.plate_type_id || 1) : 1,
        carMakeId: initialTx ? (initialTx.car_make_id || '') : '',
        carMakeEng: initialTx && initialTx.car_make ? (initialTx.car_make.name_english || '') : '',
        selectedMakeKurdish: '',
        modelYear: initialTx ? (initialTx.model_year || '') : '',
        carColorId: initialTx ? (initialTx.car_color_id || '') : '',
        pistonCount: initialTx ? (initialTx.piston_count || 4) : 4,
        salanaNumber: initialTx ? (initialTx.salana_number || '') : '',

        // Office details
        directorateId: initialTx ? (initialTx.traffic_directorate_id || '{{ $defaultDirectorateId }}') : '{{ $defaultDirectorateId }}',
        directorId: initialTx ? (initialTx.director_id || '{{ $defaultDirectorId }}') : '{{ $defaultDirectorId }}',

        // Makes list & Combobox search
        allMakes: makesList || [],
        filteredMakes: makesList || [],
        showCarMakeModal: false,
        newMakeKurdish: '',
        newMakeEnglish: '',

        // Chassis status
        isCheckingChassis: false,
        chassisFound: initialTx ? true : false,
        chassisPreviousInfo: initialTx || null,
        _chassisTimer: null,

        // Validation & Missing Field Indicator (Point 4)
        validationError: '',
        missingField: '',

        // Fees calculation state
        rates: txRates,
        fees: {
            mainPay: 0,
            stampPay: 0,
            formPay: 0,
            pledgeTotal: 25000,
            totalPay: 0
        },

        init() {
            // Initialize selected make Kurdish name if already set
            if (this.carMakeId) {
                const found = this.allMakes.find(m => m.id == this.carMakeId);
                if (found) {
                    this.selectedMakeKurdish = found.name_kurdish;
                    this.carMakeEng = found.name_english;
                }
            }
            this.calculateFees();
        },

        getTypeName() {
            const names = {
                1: 'دەرهێنانی دەفتەر',
                2: 'تازەکردنەوەی دەفتەر',
                3: 'ناوگۆڕینی دەفتەر',
                4: 'دەفتەر گۆڕین',
                5: 'پووچەڵکردنەوە',
                6: 'بەڵێننامە',
                7: 'ناو و دەفتەرگۆڕین',
                8: 'پشکنین'
            };
            return names[this.typeId] || ('مامەڵەی #' + this.typeId);
        },

        isFullBookletForm() {
            return [1, 2, 3, 4, 7].includes(parseInt(this.typeId));
        },

        updateDates() {
            const now = new Date();
            const y = now.getFullYear() + parseInt(this.numYears);
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const d = String(now.getDate()).padStart(2, '0');
            this.endDate = `${y}-${m}-${d}`;
        },

        filterMakes(query) {
            const q = (query || '').toLowerCase().trim();
            if (!q) {
                this.filteredMakes = this.allMakes;
                return;
            }
            this.filteredMakes = this.allMakes.filter(m => 
                (m.name_kurdish && m.name_kurdish.toLowerCase().includes(q)) ||
                (m.name_english && m.name_english.toLowerCase().includes(q))
            );
        },

        selectCarMake(make) {
            this.carMakeId = make.id;
            this.selectedMakeKurdish = make.name_kurdish; // Strictly Kurdish in field
            this.carMakeEng = make.name_english;         // Strictly English in companion field
            this.missingField = (this.missingField === 'carMakeId') ? '' : this.missingField;
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

        populateFromPrevious(record) {
            this.parentTransactionId = record.id;
            this.visitorName = record.visitor_name || '';
            this.visitorNameEng = record.visitor_name_eng || '';
            this.phoneNumber = record.phone_number || '';
            this.passportNumber = record.passport_number || '';
            this.passportIssuePlace = record.passport_issue_place || '';
            this.secondDriverName = record.second_driver_name || '';
            this.secondDriverNameEng = record.second_driver_name_eng || '';

            this.plateNumber = record.plate_number || '';
            if (record.plate_type_id) this.plateTypeId = record.plate_type_id;
            
            if (record.car_make_id) {
                this.carMakeId = record.car_make_id;
                const found = this.allMakes.find(m => m.id == record.car_make_id);
                if (found) {
                    this.selectedMakeKurdish = found.name_kurdish;
                    this.carMakeEng = found.name_english;
                } else {
                    this.selectedMakeKurdish = record.car_make_kurdish || '';
                    this.carMakeEng = record.car_make_english || '';
                }
            }

            if (record.car_color_id) this.carColorId = record.car_color_id;
            if (record.model_year) this.modelYear = record.model_year;
            if (record.piston_count) this.pistonCount = record.piston_count;
            if (record.salana_number) this.salanaNumber = record.salana_number;
            if (record.traffic_directorate_id) this.directorateId = record.traffic_directorate_id;
            if (record.director_id) this.directorId = record.director_id;

            this.calculateFees();
        },

        clearChassisAutoFill() {
            this.chassisFound = false;
            this.chassisPreviousInfo = null;
            this.parentTransactionId = '';
        },

        onKurdishInput(e, field) {
            let val = e.target.value;
            if (/[a-zA-Z]/.test(val) && window.LangConvert) {
                val = window.LangConvert.toArabic(val, 'ar101');
            }
            this[field] = val;
            e.target.value = val;

            if (field === 'visitorName' && (!this.visitorNameEng || this.visitorNameEng.trim() === '')) {
                if (window.LangConvert) {
                    this.visitorNameEng = window.LangConvert.transliterate(val);
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

        autoFillEnglishName(src, target) {
            const val = this[src];
            if (val && window.LangConvert) {
                this[target] = window.LangConvert.transliterate(val);
            }
        },

        onCarColorChange() {
            const sel = document.querySelector('select[name="car_color_id"]');
            if (sel && sel.selectedIndex >= 0) {
                const opt = sel.options[sel.selectedIndex];
                this.carColorEng = opt.getAttribute('data-eng') || '';
            }
        },

        calculateFees() {
            const r = this.rates[this.typeId] || {};
            let main = (parseInt(this.numYears) === 3 ? (r.pay_3_year || 0) : (parseInt(this.numYears) === 2 ? (r.pay_2_year || 0) : (r.pay_1_year || 0)));
            let stamp = r.stamp_pay || 0;
            let form = r.form_pay || 0;

            this.fees.mainPay = main;
            this.fees.stampPay = stamp;
            this.fees.formPay = form;
            this.fees.pledgeTotal = (parseInt(this.typeId) === 1) ? 25000 : 0;
            this.fees.totalPay = main + stamp + form;
        },

        formatNumber(n) {
            return new Intl.NumberFormat('en-US').format(n || 0);
        },

        focusNext(field) {
            const el = document.getElementById('input_' + field);
            if (el) el.focus();
        },

        // Smart Field Validation (Item 4)
        submitForm() {
            this.validationError = '';
            this.missingField = '';

            const requiredFields = [
                { id: 'chassisNumber', name: 'ژمارەی شاسی ئۆتۆمبێل (Chassis VIN)' },
                { id: 'plateNumber', name: 'ژمارەی تابلۆی ئۆتۆمبێل' },
                { id: 'carMakeId', name: 'جۆری ئۆتۆمبێل (مارکە)' },
                { id: 'modelYear', name: 'مۆدێلی دروستکردن (ساڵ)' },
                { id: 'carColorId', name: 'ڕەنگی ئۆتۆمبێل' },
                { id: 'visitorName', name: 'ناوی هاووڵاتی (شۆفێری یەکەم)' },
                { id: 'phoneNumber', name: 'ژمارەی مۆبایلی هاووڵاتی' },
            ];

            for (const f of requiredFields) {
                const val = this[f.id];
                if (!val || String(val).trim() === '') {
                    this.validationError = `تکایە خانەی [${f.name}] پڕبکەرەوە پێش پاشەکەوتکردن!`;
                    this.missingField = f.id;
                    const el = document.getElementById('input_' + f.id);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.focus();
                    }
                    return;
                }
            }

            // If valid, submit form
            document.getElementById('mainTransactionForm').submit();
        },

        addCarMake() {
            if (!this.newMakeKurdish || !this.newMakeEnglish) {
                alert('تکایە هەردوو ناوی کوردی و ئینگلیزی بنووسە!');
                return;
            }
            fetch("{{ route('lookup.car_make') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    name_kurdish: this.newMakeKurdish,
                    name_english: this.newMakeEnglish
                })
            })
            .then(res => res.json())
            .then(data => {
                const make = data.item || data.make;
                if (data.success && make) {
                    this.allMakes.unshift(make);
                    this.selectCarMake(make);
                    this.showCarMakeModal = false;
                    this.newMakeKurdish = '';
                    this.newMakeEnglish = '';
                } else {
                    alert(data.message || 'هەڵەیەک ڕوویدا لە زیادکردنی مارکە.');
                }
            })
            .catch(() => alert('هەڵەی پەیوەندی بە سێرڤەرەوە.'));
        }
    };
}
</script>
@endsection
