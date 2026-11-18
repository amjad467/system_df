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

    $colorsList = $carColors->map(function($c) {
        return [
            'id' => $c->id,
            'name_kurdish' => $c->name_kurdish,
            'name_english' => $c->name_english ?? '',
        ];
    })->values();

    $typeStyles = [
        1 => [
            'icon' => 'fa-book-bookmark',
            'label' => 'دەرهێنانی دەفتەر',
            'sub' => 'نوێ + بەڵێننامە',
            'day_inactive' => 'bg-emerald-50/90 hover:bg-emerald-100 text-emerald-950 border-emerald-300 hover:border-emerald-500 shadow-sm shadow-emerald-200/50',
            'dark_inactive' => 'dark:bg-emerald-950/25 dark:hover:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-800/60',
            'active' => 'bg-gradient-to-br from-emerald-600 via-teal-600 to-emerald-700 text-white border-emerald-400 shadow-lg shadow-emerald-600/35 ring-2 ring-emerald-400',
            'icon_color' => 'text-emerald-600 dark:text-emerald-400',
            'badge' => 'bg-emerald-200/80 text-emerald-900 dark:bg-emerald-900/80 dark:text-emerald-200',
        ],
        2 => [
            'icon' => 'fa-rotate-right',
            'label' => 'تازەکردنەوەی دەفتەر',
            'sub' => 'نوێکردنەوە',
            'day_inactive' => 'bg-sky-50/90 hover:bg-sky-100 text-sky-950 border-sky-300 hover:border-sky-500 shadow-sm shadow-sky-200/50',
            'dark_inactive' => 'dark:bg-sky-950/25 dark:hover:bg-sky-900/40 dark:text-sky-300 dark:border-sky-800/60',
            'active' => 'bg-gradient-to-br from-sky-600 via-blue-600 to-sky-700 text-white border-sky-400 shadow-lg shadow-sky-600/35 ring-2 ring-sky-400',
            'icon_color' => 'text-sky-600 dark:text-sky-400',
            'badge' => 'bg-sky-200/80 text-sky-900 dark:bg-sky-900/80 dark:text-sky-200',
        ],
        3 => [
            'icon' => 'fa-signature',
            'label' => 'ناوگۆڕینی دەفتەر',
            'sub' => 'خاوەندارییەتی',
            'day_inactive' => 'bg-indigo-50/90 hover:bg-indigo-100 text-indigo-950 border-indigo-300 hover:border-indigo-500 shadow-sm shadow-indigo-200/50',
            'dark_inactive' => 'dark:bg-indigo-950/25 dark:hover:bg-indigo-900/40 dark:text-indigo-300 dark:border-indigo-800/60',
            'active' => 'bg-gradient-to-br from-indigo-600 via-violet-600 to-indigo-700 text-white border-indigo-400 shadow-lg shadow-indigo-600/35 ring-2 ring-indigo-400',
            'icon_color' => 'text-indigo-600 dark:text-indigo-400',
            'badge' => 'bg-indigo-200/80 text-indigo-900 dark:bg-indigo-900/80 dark:text-indigo-200',
        ],
        4 => [
            'icon' => 'fa-id-card-clip',
            'label' => 'دەفتەر گۆڕین',
            'sub' => 'گۆڕینی بەرگ',
            'day_inactive' => 'bg-amber-50/90 hover:bg-amber-100 text-amber-950 border-amber-300 hover:border-amber-500 shadow-sm shadow-amber-200/50',
            'dark_inactive' => 'dark:bg-amber-950/25 dark:hover:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800/60',
            'active' => 'bg-gradient-to-br from-amber-500 via-orange-600 to-amber-700 text-white border-amber-400 shadow-lg shadow-amber-500/35 ring-2 ring-amber-400',
            'icon_color' => 'text-amber-600 dark:text-amber-400',
            'badge' => 'bg-amber-200/80 text-amber-900 dark:bg-amber-900/80 dark:text-amber-200',
        ],
        5 => [
            'icon' => 'fa-ban',
            'label' => 'پووچەڵکردنەوە',
            'sub' => 'هەڵوەشاندنەوە',
            'day_inactive' => 'bg-rose-50/90 hover:bg-rose-100 text-rose-950 border-rose-300 hover:border-rose-500 shadow-sm shadow-rose-200/50',
            'dark_inactive' => 'dark:bg-rose-950/25 dark:hover:bg-rose-900/40 dark:text-rose-300 dark:border-rose-800/60',
            'active' => 'bg-gradient-to-br from-rose-600 via-red-600 to-rose-700 text-white border-rose-400 shadow-lg shadow-rose-600/35 ring-2 ring-rose-400',
            'icon_color' => 'text-rose-600 dark:text-rose-400',
            'badge' => 'bg-rose-200/80 text-rose-900 dark:bg-rose-900/80 dark:text-rose-200',
        ],
        6 => [
            'icon' => 'fa-handshake-angle',
            'label' => 'بەڵێننامە',
            'sub' => 'یاسایی و گومرگی',
            'day_inactive' => 'bg-purple-50/90 hover:bg-purple-100 text-purple-950 border-purple-300 hover:border-purple-500 shadow-sm shadow-purple-200/50',
            'dark_inactive' => 'dark:bg-purple-950/25 dark:hover:bg-purple-900/40 dark:text-purple-300 dark:border-purple-800/60',
            'active' => 'bg-gradient-to-br from-purple-600 via-fuchsia-600 to-purple-700 text-white border-purple-400 shadow-lg shadow-purple-600/35 ring-2 ring-purple-400',
            'icon_color' => 'text-purple-600 dark:text-purple-400',
            'badge' => 'bg-purple-200/80 text-purple-900 dark:bg-purple-900/80 dark:text-purple-200',
        ],
        7 => [
            'icon' => 'fa-arrows-rotate',
            'label' => 'ناو و دەفتەرگۆڕین',
            'sub' => 'گۆڕینی هەردووکیان',
            'day_inactive' => 'bg-violet-50/90 hover:bg-violet-100 text-violet-950 border-violet-300 hover:border-violet-500 shadow-sm shadow-violet-200/50',
            'dark_inactive' => 'dark:bg-violet-950/25 dark:hover:bg-violet-900/40 dark:text-violet-300 dark:border-violet-800/60',
            'active' => 'bg-gradient-to-br from-violet-600 via-pink-600 to-violet-700 text-white border-violet-400 shadow-lg shadow-violet-600/35 ring-2 ring-violet-400',
            'icon_color' => 'text-violet-600 dark:text-violet-400',
            'badge' => 'bg-violet-200/80 text-violet-900 dark:bg-violet-900/80 dark:text-violet-200',
        ],
        8 => [
            'icon' => 'fa-magnifying-glass-location',
            'label' => 'پشکنین',
            'sub' => 'کەشف و ڕاپۆرت',
            'day_inactive' => 'bg-teal-50/90 hover:bg-teal-100 text-teal-950 border-teal-300 hover:border-teal-500 shadow-sm shadow-teal-200/50',
            'dark_inactive' => 'dark:bg-teal-950/25 dark:hover:bg-teal-900/40 dark:text-teal-300 dark:border-teal-800/60',
            'active' => 'bg-gradient-to-br from-teal-600 via-cyan-600 to-teal-700 text-white border-teal-400 shadow-lg shadow-teal-600/35 ring-2 ring-teal-400',
            'icon_color' => 'text-teal-600 dark:text-teal-400',
            'badge' => 'bg-teal-200/80 text-teal-900 dark:bg-teal-900/80 dark:text-teal-200',
        ],
    ];
@endphp

<style>
/* ========================================================
   Interactive Field Micro-Animations & Color Glow (Req 1)
   ======================================================== */
#mainTransactionForm input:not([type="hidden"]),
#mainTransactionForm select,
#mainTransactionForm textarea,
.modal-interactive input {
    transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), 
                box-shadow 0.22s ease, 
                border-color 0.2s ease, 
                background-color 0.2s ease;
    will-change: transform, box-shadow;
}

/* Day Mode Hover: smooth upward lift, subtle border glow, gentle sky/slate illumination */
#mainTransactionForm input:not([type="hidden"]):hover,
#mainTransactionForm select:hover,
#mainTransactionForm textarea:hover,
.modal-interactive input:hover {
    transform: translateY(-2px) scale(1.008);
    border-color: #0284c7 !important; /* sky-600 */
    background-color: #f0f9ff !important; /* light sky tint */
    box-shadow: 0 6px 16px -2px rgba(2, 132, 199, 0.2), 0 2px 6px -2px rgba(2, 132, 199, 0.12);
    z-index: 10;
}

/* Day Mode Focus */
#mainTransactionForm input:not([type="hidden"]):focus,
#mainTransactionForm select:focus,
#mainTransactionForm textarea:focus,
.modal-interactive input:focus {
    transform: translateY(-2.5px) scale(1.01);
    border-color: #0284c7 !important;
    background-color: #ffffff !important;
    box-shadow: 0 8px 22px -3px rgba(2, 132, 199, 0.28), 0 0 0 3px rgba(14, 165, 233, 0.25) !important;
    z-index: 20;
}

/* Dark Mode Hover */
.dark #mainTransactionForm input:not([type="hidden"]):hover,
.dark #mainTransactionForm select:hover,
.dark #mainTransactionForm textarea:hover,
.dark .modal-interactive input:hover {
    transform: translateY(-2px) scale(1.008);
    border-color: #38bdf8 !important; /* sky-400 */
    background-color: #0f172a !important; /* slate-900 */
    box-shadow: 0 6px 18px -2px rgba(56, 189, 248, 0.25), 0 2px 6px -2px rgba(56, 189, 248, 0.15);
    z-index: 10;
}

/* Dark Mode Focus */
.dark #mainTransactionForm input:not([type="hidden"]):focus,
.dark #mainTransactionForm select:focus,
.dark #mainTransactionForm textarea:focus,
.dark .modal-interactive input:focus {
    transform: translateY(-2.5px) scale(1.01);
    border-color: #38bdf8 !important;
    background-color: #090d16 !important;
    box-shadow: 0 8px 22px -3px rgba(56, 189, 248, 0.35), 0 0 0 3px rgba(56, 189, 248, 0.25) !important;
    z-index: 20;
}
</style>

<div x-data="unifiedTransactionForm(
        {{ \Illuminate\Support\Js::from($fromTransaction) }}, 
        {{ $targetTypeId ? (int)$targetTypeId : 'null' }}, 
        {{ \Illuminate\Support\Js::from($txRates) }},
        {{ \Illuminate\Support\Js::from($makesList) }},
        {{ \Illuminate\Support\Js::from($colorsList) }},
        {{ \Illuminate\Support\Js::from($directorateCodeMap) }}
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

        <!-- Horizontal Grid Transaction Type Pills (Distinct Vibrant Day Mode Design) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2">
            @foreach($transactionTypes as $tt)
                @php
                    $style = $typeStyles[$tt->id] ?? [
                        'icon' => 'fa-file-lines',
                        'label' => $tt->name_kurdish,
                        'sub' => '',
                        'day_inactive' => 'bg-slate-50 hover:bg-slate-100 text-slate-800 border-slate-300',
                        'dark_inactive' => 'dark:bg-slate-900 dark:hover:bg-slate-800 dark:text-slate-300 dark:border-slate-800',
                        'active' => 'bg-sky-600 text-white border-sky-400 shadow-lg ring-2 ring-sky-400',
                        'icon_color' => 'text-sky-500',
                        'badge' => 'bg-slate-200 text-slate-800',
                    ];
                @endphp
                <button type="button" 
                        @click="typeId = {{ $tt->id }}; calculateFees();" 
                        :class="typeId == {{ $tt->id }} 
                            ? '{{ $style['active'] }} scale-[1.03] font-black' 
                            : '{{ $style['day_inactive'] }} {{ $style['dark_inactive'] }} hover:scale-[1.02] font-bold'" 
                        class="p-2.5 rounded-2xl border text-center transition-all duration-200 flex flex-col items-center justify-between gap-1.5 cursor-pointer min-h-[72px] relative group overflow-hidden shadow-sm">
                    
                    <div class="flex items-center justify-center w-7 h-7 rounded-xl transition shadow-xs"
                         :class="typeId == {{ $tt->id }} ? 'bg-white/20 text-white' : '{{ $style['badge'] }}'">
                        <i class="fa-solid {{ $style['icon'] }} text-xs" :class="typeId == {{ $tt->id }} ? 'text-white' : '{{ $style['icon_color'] }}'"></i>
                    </div>

                    <div class="w-full">
                        <span class="text-[11px] leading-tight block truncate w-full" :class="typeId == {{ $tt->id }} ? 'text-white' : ''">
                            {{ $style['label'] }}
                        </span>
                        @if(!empty($style['sub']))
                            <span class="text-[9px] block opacity-80 truncate pt-0.5" :class="typeId == {{ $tt->id }} ? 'text-white/90' : 'text-slate-500 dark:text-slate-400'">
                                {{ $style['sub'] }}
                            </span>
                        @endif
                    </div>

                    <!-- Active glowing indicator -->
                    <div x-show="typeId == {{ $tt->id }}" x-cloak class="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
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

                    <!-- Active Booklet Warning Alert (Req 1) -->
                    <div x-show="hasActiveBooklet && typeId == 1" x-cloak class="p-2.5 bg-rose-500/15 border-2 border-rose-500/80 rounded-xl text-rose-700 dark:text-rose-300 space-y-1 shadow-sm">
                        <div class="flex items-center justify-between font-black text-xs">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation text-rose-500 text-sm"></i>
                                <span>تۆ دەفتەری پێشووت پووچەڵ نەکردووەتەوە!</span>
                            </span>
                            <span class="text-[10px] bg-rose-600 text-white px-2 py-0.5 rounded-md font-mono" x-text="activeBooklet ? activeBooklet.barcode : ''"></span>
                        </div>
                        <p class="text-[11px] font-medium leading-relaxed">
                            ئەم شاسییە خاوەنی مامەڵەی دەرهێنانی دەفتەری پێشووە کە هێشتا پووچەڵ نەکراوەتەوە. ناتوانیت دەرهێنانی نوێ بکەیت پێش پووچەڵکردنەوە.
                        </p>
                    </div>

                    <!-- Found Alert Badge -->
                    <div x-show="chassisFound && chassisPreviousInfo && (!hasActiveBooklet || typeId != 1)" x-cloak class="p-1.5 bg-emerald-500/15 border border-emerald-500/40 rounded-lg text-[11px] text-emerald-700 dark:text-emerald-300 flex items-center justify-between">
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
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[11px] font-bold text-slate-700 dark:text-slate-300">ژمارەی تابلۆ *</label>
                            <span x-show="detectedDirectorateName" x-cloak class="text-[10px] text-sky-600 dark:text-sky-400 font-bold flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-[9px]"></i>
                                <span x-text="detectedDirectorateName"></span>
                            </span>
                        </div>
                        <input type="text" 
                               id="input_plateNumber"
                               name="plate_number" 
                               x-model="plateNumber" 
                               @input="onPlateInput($event.target.value)"
                               required 
                               placeholder="مثلاً: 21 A 12345" 
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

                <!-- Car Make English - Editable (Req 2B) -->
                <div x-show="isFullBookletForm()">
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">مارکە بە ئینگلیزی (با کارمەند خۆی بینوسێت)</label>
                    <input type="text" 
                           x-model="carMakeEng" 
                           dir="ltr"
                           placeholder="ناوی ئینگلیزی مارکە بنووسە..."
                           class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-sky-500 rounded-lg px-2.5 py-1.5 text-xs text-sky-600 dark:text-sky-400 font-mono font-bold tracking-wider">
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
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[11px] font-bold text-slate-700 dark:text-slate-300">ڕەنگ *</label>
                            <button type="button" @click="showCarColorModal = true" class="text-[10px] text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1 font-bold">
                                <i class="fa-solid fa-plus-circle"></i> نوێ
                            </button>
                        </div>
                        <select name="car_color_id" 
                                id="input_carColorId"
                                x-model="carColorId" 
                                @change="onCarColorChange()" 
                                :class="missingField === 'carColorId' ? 'border-rose-500 ring-2 ring-rose-500/30' : 'border-slate-300 dark:border-slate-700'"
                                class="w-full bg-white dark:bg-slate-950 border focus:border-sky-500 rounded-lg px-2 py-1.5 text-xs text-slate-900 dark:text-slate-100 focus:outline-none">
                            <option value="">هەڵبژێرە...</option>
                            <template x-for="c in allColors" :key="c.id">
                                <option :value="c.id" :selected="carColorId == c.id" x-text="c.name_kurdish"></option>
                            </template>
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

                <!-- Traffic Directorate (Auto-selected from plate code: 21, 22, 23, 24) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-700 dark:text-slate-300">بەڕێوەبەرایەتی هاتوچۆ</label>
                        <span class="text-[10px] text-slate-400 font-mono">دەستنیشانکردنی خۆکار (21، 22، 23، 24)</span>
                    </div>
                    <select name="traffic_directorate_id" 
                            x-model="directorateId" 
                            class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 focus:outline-none">
                        @foreach($directorates as $dir)
                            <option value="{{ $dir->id }}">{{ $dir->name_kurdish }}</option>
                        @endforeach
                    </select>
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

    <!-- INLINE MODAL: Quick Add & Search Car Make (Req 2B) -->
    <div x-show="showCarMakeModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="modal-interactive bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl max-w-lg w-full p-5 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-car text-sky-500"></i>
                    <span>جۆری ئۆتۆمبێل (مارکە)</span>
                </h3>
                <button type="button" @click="showCarMakeModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- Existing Makes Search & List (سێرچ و لیستی پێشوو) -->
            <div class="space-y-2 bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between text-xs">
                    <label class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-magnifying-glass text-sky-500"></i>
                        <span>گەڕان لە لیستی مارکە هەبووەکانی پێشوو</span>
                    </label>
                    <span class="text-[10px] text-slate-400 font-mono" x-text="allMakes.length + ' مارکە'"></span>
                </div>
                <input type="text" 
                       x-model="modalMakeSearch" 
                       placeholder="بگەڕێ لە مارکەکانی پێشوو (تۆیۆتا، نیسان، BMW...)" 
                       class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 font-bold focus:border-sky-500">
                
                <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto pt-1">
                    <template x-for="m in modalFilteredMakes" :key="m.id">
                        <button type="button" 
                                @click="selectCarMake(m); showCarMakeModal = false;" 
                                class="text-[11px] px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-sky-50 dark:hover:bg-sky-950/50 hover:border-sky-400 text-slate-800 dark:text-slate-200 flex items-center gap-1.5 transition cursor-pointer">
                            <span class="font-bold" x-text="m.name_kurdish"></span>
                            <span class="text-[10px] text-slate-400 font-mono" x-text="m.name_english"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Add Truly New Make Form (با کارمەند خۆی بینوسێت) -->
            <div class="space-y-3 pt-1 border-t border-slate-200 dark:border-slate-800">
                <div class="text-xs font-black text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-plus-circle text-emerald-500"></i>
                    <span>یاخود تۆمارکردنی جۆری نوێ (ئەگەر لە سەرەوە نەبوو):</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ناوی کوردی مارکە *</label>
                    <input type="text" x-model="newMakeKurdish" placeholder="مثلاً: چێری یان جێتۆر" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ناوی ئینگلیزی (با کارمەند خۆی بینوسێت) *</label>
                    <input type="text" x-model="newMakeEnglish" placeholder="مثلاً: CHERY یان JETOUR" dir="ltr" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 uppercase font-mono font-bold">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCarMakeModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold rounded-xl">داخستن</button>
                    <button type="button" @click="addCarMake()" class="px-5 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow">تۆمارکردنی مارکە</button>
                </div>
            </div>
        </div>
    </div>

    <!-- INLINE MODAL: Quick Add Car Color (Req 2A) -->
    <div x-show="showCarColorModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="modal-interactive bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl max-w-md w-full p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-palette text-amber-500"></i>
                    <span>زیادکردنی ڕەنگی نوێ</span>
                </h3>
                <button type="button" @click="showCarColorModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ناوی کوردی ڕەنگ *</label>
                    <input type="text" x-model="newColorKurdish" placeholder="بۆ نموونە: ڕەساسی تۆخ، مەرەکەبی..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ناوی ئینگلیزی ڕەنگ (با کارمەند خۆی بینوسێت)</label>
                    <input type="text" x-model="newColorEnglish" placeholder="DARK GREY" dir="ltr" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 uppercase font-mono font-bold">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCarColorModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold rounded-xl">داخستن</button>
                    <button type="button" @click="addCarColor()" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow">زیادکردنی ڕەنگ</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function unifiedTransactionForm(initialTx, targetTypeId, txRates, makesList, colorsList, dirCodeMap) {
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

        // Directorate & Plate prefix code map (Req 4: 21, 22, 23, 24)
        directorateId: initialTx ? (initialTx.traffic_directorate_id || '{{ $defaultDirectorateId }}') : '{{ $defaultDirectorateId }}',
        codeMap: dirCodeMap || {},
        detectedDirectorateName: '',

        // Makes list & modal search (Req 2B)
        allMakes: makesList || [],
        filteredMakes: makesList || [],
        showCarMakeModal: false,
        modalMakeSearch: '',
        newMakeKurdish: '',
        newMakeEnglish: '',

        // Colors list & modal (Req 2A)
        allColors: colorsList || [],
        showCarColorModal: false,
        newColorKurdish: '',
        newColorEnglish: '',

        // Chassis status & Active Booklet Duplicate Check (Req 1)
        isCheckingChassis: false,
        chassisFound: initialTx ? true : false,
        chassisPreviousInfo: initialTx || null,
        hasActiveBooklet: false,
        activeBooklet: null,
        _chassisTimer: null,

        // Validation & Missing Field Indicator
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

        get modalFilteredMakes() {
            const q = (this.modalMakeSearch || '').trim().toLowerCase();
            if (!q) return this.allMakes.slice(0, 20);
            return this.allMakes.filter(m => 
                (m.name_kurdish && m.name_kurdish.toLowerCase().includes(q)) ||
                (m.name_english && m.name_english.toLowerCase().includes(q))
            ).slice(0, 30);
        },

        init() {
            // Initialize selected make Kurdish name if already set
            if (this.carMakeId) {
                const found = this.allMakes.find(m => m.id == this.carMakeId);
                if (found) {
                    this.selectedMakeKurdish = found.name_kurdish;
                    if (!this.carMakeEng) this.carMakeEng = found.name_english;
                }
            }
            if (this.plateNumber) {
                this.onPlateInput(this.plateNumber);
            }
            this.calculateFees();
        },

        onPlateInput(val) {
            if (!val) {
                this.detectedDirectorateName = '';
                return;
            }
            // Normalize Kurdish/Arabic-Indic digits to ASCII (e.g. ٢١ -> 21)
            const norm = String(val).trim()
                .replace(/[٠-٩]/g, d => "٠١٢٣٤٥٦٧٨٩".indexOf(d))
                .replace(/[۰-۹]/g, d => "۰۱۲۳۴۵۶۷۸۹".indexOf(d));

            if (norm.startsWith('21') && this.codeMap['21']) {
                this.directorateId = this.codeMap['21'];
                this.detectedDirectorateName = 'هاتووچۆی سلێمانی (21)';
            } else if (norm.startsWith('22') && this.codeMap['22']) {
                this.directorateId = this.codeMap['22'];
                this.detectedDirectorateName = 'هاتووچۆی هەولێر (22)';
            } else if (norm.startsWith('23') && this.codeMap['23']) {
                this.directorateId = this.codeMap['23'];
                this.detectedDirectorateName = 'هاتووچۆی هەڵەبجە (23)';
            } else if (norm.startsWith('24') && this.codeMap['24']) {
                this.directorateId = this.codeMap['24'];
                this.detectedDirectorateName = 'هاتووچۆی دهۆک (24)';
            } else {
                this.detectedDirectorateName = '';
            }
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
            this.selectedMakeKurdish = make.name_kurdish;
            this.carMakeEng = make.name_english;
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
                this.hasActiveBooklet = false;
                this.activeBooklet = null;
                return;
            }

            this.isCheckingChassis = true;
            fetch(`/transactions/api/lookup-previous?chassis_number=${encodeURIComponent(clean)}`)
                .then(res => res.json())
                .then(data => {
                    this.isCheckingChassis = false;
                    this.hasActiveBooklet = Boolean(data && data.has_active_booklet);
                    this.activeBooklet = (data && data.active_booklet) ? data.active_booklet : null;

                    if (this.hasActiveBooklet && this.typeId == 1) {
                        alert('تۆ دەفتەری پێشووت پووچەڵ نەکردووەتەوە!\n\nئەم شاسییە خاوەنی دەفتەری پێشووی چالاکە بە بارکۆدی: ' + (this.activeBooklet ? this.activeBooklet.barcode : '') + '\nناتوانیت دووبارە دەرهێنان بکەیت هەتاوەکو پووچەڵ نەکرێتەوە.');
                    }

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

            if (record.plate_number) {
                this.plateNumber = record.plate_number;
                this.onPlateInput(this.plateNumber);
            }
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

            this.calculateFees();
        },

        clearChassisAutoFill() {
            this.chassisFound = false;
            this.chassisPreviousInfo = null;
            this.parentTransactionId = '';
            this.hasActiveBooklet = false;
            this.activeBooklet = null;
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
            // Can be used for extra callbacks if needed
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

        // Smart Field Validation
        submitForm() {
            this.validationError = '';
            this.missingField = '';

            // Requirement 1: Check active booklet duplicate blocking
            if (this.typeId == 1 && this.hasActiveBooklet) {
                this.validationError = 'تۆ دەفتەری پێشووت پووچەڵ نەکردووەتەوە! ناتوانیت مامەڵەی دەرهێنانی نوێ تۆمار بکەیت پێش ئەوەی دەفتەری پێشوو پووچەڵ بکرێتەوە.';
                alert('تۆ دەفتەری پێشووت پووچەڵ نەکردووەتەوە!\n\nئەم شاسییە خاوەنی دەفتەری پێشووی چالاکە بە بارکۆدی: ' + (this.activeBooklet ? this.activeBooklet.barcode : '') + '\nناتوانیت دووبارە دەرهێنان بکەیت هەتاوەکو دەفتەری پێشوو پووچەڵ نەکرێتەوە.');
                const el = document.getElementById('input_chassisNumber');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.focus();
                }
                return;
            }

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
                alert('تکایە هەردوو ناوی کوردی و ئینگلیزی مارکە بنووسە!');
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
                    this.modalMakeSearch = '';
                } else {
                    alert(data.message || 'هەڵەیەک ڕوویدا لە زیادکردنی مارکە.');
                }
            })
            .catch(() => alert('هەڵەی پەیوەندی بە سێرڤەرەوە.'));
        },

        addCarColor() {
            if (!this.newColorKurdish) {
                alert('تکایە ناوی کوردی ڕەنگ بنووسە!');
                return;
            }
            fetch("{{ route('lookup.car_color') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    name_kurdish: this.newColorKurdish,
                    name_english: this.newColorEnglish || this.newColorKurdish
                })
            })
            .then(res => res.json())
            .then(data => {
                const color = data.item || data.color;
                if (data.success && color) {
                    this.allColors.unshift(color);
                    this.carColorId = color.id;
                    this.showCarColorModal = false;
                    this.newColorKurdish = '';
                    this.newColorEnglish = '';
                } else {
                    alert(data.message || 'هەڵەیەک ڕوویدا لە زیادکردنی ڕەنگ.');
                }
            })
            .catch(() => alert('هەڵەی پەیوەندی بە سێرڤەر بۆ زیادکردنی ڕەنگ.'));
        }
    };
}
</script>
@endsection
