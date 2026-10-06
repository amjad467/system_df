@extends('layouts.app')

@section('content')
<div x-data="transactionForm()" class="max-w-5xl mx-auto space-y-6">
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
    <form action="{{ route('transactions.store') }}" method="POST" class="space-y-6">
        @csrf

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

        <!-- 2. Drivers Information -->
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center">
                <i class="fa-solid fa-user-gear ml-2"></i> ٢. زانیاری شۆفێر و هاووڵاتی
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ناوى شۆفێرى يه‌كه‌م بە کوردی / عەرەبی *</label>
                    <input type="text" name="visitor_name" required placeholder="ناوی سێیانی شۆفێری یەکەم" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                </div>

                <div x-show="isFullBookletForm()">
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ناوى شۆفێر يه‌كه‌م بە ئینگلیزی</label>
                    <input type="text" name="visitor_name_eng" placeholder="Full name in English" dir="ltr" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                </div>

                <div x-show="isFullBookletForm()">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">ناوى شۆفێری دووه‌م بە کوردی (ئارەزوومەندانه)</label>
                    <input type="text" name="second_driver_name" placeholder="ناوی سێیانی شۆفێری دووەم" 
                           class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                </div>

                <div x-show="isFullBookletForm()">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">ناوى شۆفێر دووەم بە ئینگلیزی (ئارەزوومەندانە)</label>
                    <input type="text" name="second_driver_name_eng" placeholder="Second Driver in English" dir="ltr" 
                           class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- 2.1 Customs Letter Section (For Cancellation / پووچەڵکردنەوە) -->
        <div x-show="isCancellation()" class="bg-rose-950/40 border border-rose-500/50 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-bold text-rose-400 border-b border-rose-500/30 pb-2 flex items-center">
                <i class="fa-solid fa-file-contract ml-2"></i> زانیارییەکانی نووسراوی گومرگ (تایبەت بە پووچەڵکردنەوە)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-rose-200 mb-1.5">ژمارەی نووسراوی گومرگ</label>
                    <input type="text" name="no_nusraw_puchal" placeholder="ژمارەی فەرمی نووسراوی گومرگ" dir="ltr"
                           class="w-full bg-slate-900 border border-rose-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-rose-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-rose-200 mb-1.5">بەرواری نووسراوی گومرگ</label>
                    <input type="date" name="date_nusraw_puchal"
                           class="w-full bg-slate-900 border border-rose-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-rose-500 focus:outline-none">
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
                    <input type="text" name="plate_number" required placeholder="مثلاً: 22 A 12345" dir="ltr"
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
                    <input type="text" name="model_year" required maxlength="4" minlength="4" pattern="\d{4}" placeholder="مثلاً: 2024" dir="ltr"
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

                <!-- Chassis Number (VIN) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ژمارەی شاسی (VIN) *</label>
                    <input type="text" name="chassis_number" required placeholder="17-Digit VIN Number" dir="ltr"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono uppercase focus:border-sky-500 focus:outline-none">
                </div>

                <!-- Piston count -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ژمارەی بستۆن</label>
                    <input type="number" name="piston_count" value="4" min="1" max="16"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>

                <!-- Salana Number -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ژمارەی ساڵانەی ئۆتۆمبێل</label>
                    <input type="text" name="salana_number" placeholder="ژمارەی فەرمی ساڵانە" dir="ltr"
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
                    <i class="fa-solid fa-calculator ml-2 text-emerald-400"></i> هەژمارکردنی خۆکاری بڕی پارە و نرخەکان
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

        <!-- Submit Button -->
        <div class="flex items-center justify-end space-x-3 space-x-reverse">
            <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm rounded-xl shadow-xl shadow-emerald-600/30 transition flex items-center">
                <i class="fa-solid fa-check-circle ml-2 text-lg"></i> تۆمارکردنی مامەڵە و دەرهێنانی بارکۆد
            </button>
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
                <button @click="showCarMakeModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <div class="overflow-y-auto flex-1 space-y-2 max-h-48 border-b border-slate-800 pb-3">
                <table class="w-full text-right text-xs text-slate-200">
                    <thead class="bg-slate-800 text-slate-400">
                        <tr>
                            <th class="p-2">مارکە (کوردی)</th>
                            <th class="p-2">مارکە (ئینگلیزی)</th>
                            <th class="p-2 text-center">دەستکاریکردن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($carMakes as $cm)
                            <tr>
                                <td class="p-2">
                                    <input type="text" id="cm_kurdish_{{ $cm->id }}" value="{{ $cm->name_kurdish }}" class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs w-full">
                                </td>
                                <td class="p-2">
                                    <input type="text" id="cm_english_{{ $cm->id }}" value="{{ $cm->name_english }}" dir="ltr" class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs font-mono w-full">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="updateCarMake({{ $cm->id }})" class="px-2 py-1 bg-amber-600 text-white rounded text-[11px] font-bold">پاشەکەوت</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-bold text-sky-400">+ زیادکردنی مارکەی نوێ</h4>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" x-model="newMakeKurdish" placeholder="کوردی (مثلاً: هۆندا)" class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100">
                    <input type="text" x-model="newMakeEnglish" placeholder="ئینگلیزی (e.g. Honda)" dir="ltr" class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100 font-mono">
                </div>
                <button type="button" @click="addCarMake()" class="w-full py-2 bg-sky-600 text-white text-xs font-bold rounded-xl shadow">زیادکردن</button>
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
</div>

<script>
    function transactionForm() {
        return {
            typeId: 1,
            numYears: 1,
            directorateId: '{{ $defaultDirectorateId }}',
            directorId: '{{ $defaultDirectorId }}',
            plateTypeId: 1,
            carMakeId: '',
            carMakeEng: '',
            carColorId: '',
            carColorEng: '',
            startDate: '{{ date("Y-m-d") }}',
            endDate: '{{ date("Y-m-d", strtotime("+1 year")) }}',

            zedabar: 'باشە',
            zedabarEng: 'good',
            kamukurty: 'باشە',
            kamukurtyEng: 'good',
            baryGshty: 'باشە',
            baryGshtyEng: 'good',

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

            newTxTypeName: '',
            newDirectorateName: '',
            newDirectorName: '',
            newPlateTypeName: '',
            newMakeKurdish: '',
            newMakeEnglish: '',
            newColorKurdish: '',
            newColorEnglish: '',

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
                const opt = sel.options[sel.selectedIndex];
                this.carMakeEng = opt ? (opt.getAttribute('data-eng') || '') : '';
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
                fetch(`{{ route('transactions.calculate_api') }}?transaction_type_id=${this.typeId}&num_years=${this.numYears}&plate_type_id=${this.plateTypeId}&start_date=${this.startDate}&end_date=${this.endDate}`)
                    .then(res => res.json())
                    .then(data => {
                        this.fees = data;
                        if (data.start_date) this.startDate = data.start_date;
                        if (data.end_date) this.endDate = data.end_date;
                    });
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
                fetch('{{ route("lookup.car_make") }}', {
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
                        this.newMakeKurdish = '';
                        this.newMakeEnglish = '';
                    }
                });
            },

            addCarColor() {
                if (!this.newColorKurdish) return;
                fetch('{{ route("lookup.car_color") }}', {
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
                fetch('{{ route("lookup.directorate") }}', {
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
                fetch('{{ route("lookup.director") }}', {
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
                fetch('{{ route("lookup.plate_type") }}', {
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
                fetch('{{ route("lookup.transaction_type") }}', {
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

            init() {
                this.calculateFees();
            }
        }
    }
</script>
@endsection
