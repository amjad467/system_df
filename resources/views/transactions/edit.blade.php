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
<div x-data="transactionEditForm({{ \Illuminate\Support\Js::from($txRates) }})" class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fa-solid fa-pen-to-square ml-2 text-amber-400"></i> دەستکاریکردن و ڕاستکردنەوەی مامەڵە
            </h2>
            <div class="text-xs text-slate-400 flex items-center gap-2 mt-1 flex-wrap">
                <span>بارکۆد: <strong class="font-mono text-sky-400">{{ $transaction->barcode }}</strong></span>
                <span>|</span>
                <span class="px-2.5 py-0.5 rounded-lg text-xs font-black bg-amber-500/20 text-amber-300 border border-amber-500/40">
                    جۆری مامەڵە: {{ $transaction->transactionType?->name_kurdish }}
                </span>
            </div>
        </div>
        <a href="{{ route('transactions.show', $transaction->id) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl border border-slate-700 transition">
            <i class="fa-solid fa-arrow-right ml-1"></i> گەڕانەوە
        </a>
    </div>

    <!-- Main Form -->
    <form action="{{ route('transactions.update', $transaction->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Transaction & Directorate Details -->
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center">
                <i class="fa-solid fa-layer-group ml-2"></i> ١. جۆری مامەڵە و بەڕێوەبەرایەتی
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Transaction Type -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">جۆری مامەڵە *</label>
                    <select name="transaction_type_id" x-model="typeId" @change="calculateFees()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        @foreach($transactionTypes as $tt)
                            <option value="{{ $tt->id }}" {{ $transaction->transaction_type_id == $tt->id ? 'selected' : '' }}>{{ $tt->name_kurdish }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Traffic Directorate -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">بەڕێوەبەرایەتی هاتوچۆ</label>
                    <select name="traffic_directorate_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        @foreach($directorates as $dir)
                            <option value="{{ $dir->id }}" {{ $transaction->traffic_directorate_id == $dir->id ? 'selected' : '' }}>{{ $dir->name_kurdish }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Director -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">بەڕێوەبەر / لێپرسراو</label>
                    <select name="director_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        @foreach($directors as $d)
                            <option value="{{ $d->id }}" {{ $transaction->director_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-emerald-400 mb-1.5 flex items-center">
                        <i class="fa-solid fa-phone ml-1 text-emerald-400"></i> ژمارەی مۆبایلی هاووڵاتی *
                    </label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $transaction->phone_number) }}" placeholder="مثلاً: 0770 123 4567" dir="ltr" 
                           class="w-full bg-slate-900 border border-emerald-500/50 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-emerald-400 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ناوى شۆفێرى يه‌كه‌م بە کوردی / عەرەبی *</label>
                    <input type="text" name="visitor_name" value="{{ old('visitor_name', $transaction->visitor_name) }}" required 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                </div>

                <div x-show="isFullBookletForm()">
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ناوى شۆفێر يه‌كه‌م بە ئینگلیزی</label>
                    <input type="text" name="visitor_name_eng" value="{{ old('visitor_name_eng', $transaction->visitor_name_eng) }}" dir="ltr" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                </div>

                <div x-show="isFullBookletForm()">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">ناوى شۆفێری دووه‌م بە کوردی (ئارەزوومەندانه)</label>
                    <input type="text" name="second_driver_name" value="{{ old('second_driver_name', $transaction->second_driver_name) }}" 
                           class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                </div>

                <div x-show="isFullBookletForm()">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">ناوى شۆفێر دووەم بە ئینگلیزی (ئارەزوومەندانە)</label>
                    <input type="text" name="second_driver_name_eng" value="{{ old('second_driver_name_eng', $transaction->second_driver_name_eng) }}" dir="ltr" 
                           class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                </div>

                <!-- ژمارەی پاسپۆرت (تەنها لەگەڵ دەرهێنانی دەفتەر) -->
                <div x-show="parseInt(typeId) === 1" x-cloak>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center">
                        <i class="fa-solid fa-passport ml-1.5 text-amber-400"></i> ژمارەی پاسپۆرت (Passport No.)
                    </label>
                    <input type="text" name="passport_number" value="{{ old('passport_number', $transaction->passport_number) }}" placeholder="مثلاً: A12345678" dir="ltr" 
                           class="w-full bg-slate-900 border border-slate-700 focus:border-amber-400 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:outline-none uppercase">
                </div>

                <!-- ناونیشان (تەنها لەگەڵ دەرهێنانی دەفتەر) -->
                <div x-show="parseInt(typeId) === 1" x-cloak>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center">
                        <i class="fa-solid fa-map-location-dot ml-1.5 text-sky-400"></i> ناونیشان (Address)
                    </label>
                    <input type="text" name="address" value="{{ old('address', $transaction->address) }}" placeholder="ناونیشانی نیشتەجێبوون (شار، گەڕەک)..." 
                           class="w-full bg-slate-900 border border-slate-700 focus:border-sky-400 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none">
                </div>

                <!-- بەرواری دەرچوونی ناسنامە / پاسپۆرت (تەنها لەگەڵ دەرهێنانی دەفتەر) -->
                <div x-show="parseInt(typeId) === 1" x-cloak>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center">
                        <i class="fa-solid fa-calendar-day ml-1.5 text-amber-400"></i> بەرواری دەرچوونی ناسنامە / پاسپۆرت
                    </label>
                    <input type="date" name="passport_issue_date" value="{{ old('passport_issue_date', $transaction->passport_issue_date?->format('Y-m-d')) }}" 
                           class="w-full bg-slate-900 border border-slate-700 focus:border-amber-400 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:outline-none">
                </div>

                <!-- شوێنی دەرچوونی ناسنامە / پاسپۆرت (تەنها لەگەڵ دەرهێنانی دەفتەر) -->
                <div x-show="parseInt(typeId) === 1" x-cloak>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300 flex items-center">
                            <i class="fa-solid fa-building-columns ml-1.5 text-sky-400"></i> شوێنی دەرچوون (Place of Issue)
                        </label>
                        <button type="button" @click="showPassportPlaceModal = true" class="text-xs text-sky-400 hover:text-sky-300 font-bold flex items-center">
                            <i class="fa-solid fa-plus-circle ml-1"></i> زیادکردنی شوێن
                        </button>
                    </div>
                    <select name="passport_issue_place" x-model="passportIssuePlace" id="passportIssuePlaceSelectEdit" class="w-full bg-slate-900 border border-slate-700 focus:border-sky-400 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none">
                        <option value="">-- دیاریکردنی شوێنی دەرچوون --</option>
                        @foreach($passportIssuePlaces as $place)
                            <option value="{{ $place->name }}" {{ old('passport_issue_place', $transaction->passport_issue_place) == $place->name ? 'selected' : '' }}>{{ $place->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2.1 Customs Letter Section (For Cancellation / پووچەڵکردنەوە) -->
        <div x-show="isCancellation()" class="bg-rose-950/40 border border-rose-500/50 rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-rose-500/30 pb-2">
                <h3 class="text-base font-bold text-rose-400 flex items-center">
                    <i class="fa-solid fa-file-contract ml-2"></i> زانیارییەکانی نووسراوی گومرگ (تایبەت بە پووچەڵکردنەوە)
                </h3>
                @if(auth()->user()?->role === 'data_entry')
                    <span class="text-xs bg-rose-900/60 text-rose-300 border border-rose-700/50 px-2.5 py-1 rounded-lg">
                        <i class="fa-solid fa-lock ml-1 text-amber-400"></i> تەنها لەلایەن کارمەندی ئەرشیف/سەپمیت دەستکاری دەکرێت
                    </span>
                @endif
            </div>
            @if(auth()->user()?->role === 'data_entry')
                <p class="text-xs text-rose-300/80">تێبینی: ژمارە و بەرواری نووسراوی گومرگ لە قۆناغی کۆتایی لەلایەن فەرمانبەری ئەرشیف/سەپمیت پڕدەکرێتەوە.</p>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-rose-200 mb-1.5">ژمارەی نووسراوی گومرگ @if(auth()->user()?->role === 'data_entry')(ناچالاککراوە لە داتائەنتەری)@endif</label>
                    <input type="text" name="no_nusraw_puchal" value="{{ old('no_nusraw_puchal', $transaction->no_nusraw_puchal) }}" 
                           @if(auth()->user()?->role === 'data_entry') disabled @endif
                           placeholder="ژمارەی فەرمی نووسراوی گومرگ" dir="ltr"
                           class="w-full bg-slate-900 border border-rose-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-rose-500 focus:outline-none @if(auth()->user()?->role === 'data_entry') cursor-not-allowed opacity-70 @endif">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-rose-200 mb-1.5">بەرواری نووسراوی گومرگ @if(auth()->user()?->role === 'data_entry')(ناچالاککراوە لە داتائەنتەری)@endif</label>
                    <input type="date" name="date_nusraw_puchal" value="{{ old('date_nusraw_puchal', $transaction->date_nusraw_puchal?->format('Y-m-d')) }}"
                           @if(auth()->user()?->role === 'data_entry') disabled @endif
                           class="w-full bg-slate-900 border border-rose-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-rose-500 focus:outline-none @if(auth()->user()?->role === 'data_entry') cursor-not-allowed opacity-70 @endif">
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
                    <input type="text" name="plate_number" value="{{ old('plate_number', $transaction->plate_number) }}" required dir="ltr"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">جۆری تابلۆ *</label>
                    <select name="plate_type_id" x-model="plateTypeId" @change="calculateFees()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        @foreach($plateTypes as $pt)
                            <option value="{{ $pt->id }}" {{ $transaction->plate_type_id == $pt->id ? 'selected' : '' }}>{{ $pt->name_kurdish }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">جۆری ئۆتۆمبێل (مارکە)</label>
                    <select name="car_make_id" x-model="carMakeId" @change="onCarMakeChange()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        @foreach($carMakes as $cm)
                            <option value="{{ $cm->id }}" data-eng="{{ $cm->name_english }}" {{ $transaction->car_make_id == $cm->id ? 'selected' : '' }}>{{ $cm->name_kurdish }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">مۆدێلی دروستکردن (٤ ژمارە دەبێت) *</label>
                    <input type="text" name="model_year" value="{{ old('model_year', $transaction->model_year) }}" required maxlength="4" minlength="4" pattern="\d{4}" dir="ltr"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ڕەنگی ئۆتۆمبێل</label>
                    <select name="car_color_id" x-model="carColorId" @change="onCarColorChange()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        @foreach($carColors as $cc)
                            <option value="{{ $cc->id }}" data-eng="{{ $cc->name_english }}" {{ $transaction->car_color_id == $cc->id ? 'selected' : '' }}>{{ $cc->name_kurdish }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ژمارەی شاسی (VIN) *</label>
                    <input type="text" name="chassis_number" value="{{ old('chassis_number', $transaction->chassis_number) }}" required dir="ltr"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono uppercase focus:border-sky-500 focus:outline-none">
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
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">بەرواری دەستپێکردن</label>
                    <input type="date" name="start_date" x-model="startDate" @change="updateDates(); calculateFees();"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">بەرواری بەسەرچوون</label>
                    <input type="date" name="end_date" x-model="endDate" @change="calculateFees()"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-sky-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end space-x-3 space-x-reverse">
            <button type="submit" class="px-8 py-3 bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm rounded-xl shadow-xl shadow-amber-600/30 transition flex items-center">
                <i class="fa-solid fa-save ml-2 text-lg"></i> پاشەکەوتکردنی گۆڕانکارییەکان
            </button>
        </div>
    </form>

    <!-- INLINE MODAL: Manage & Add Passport Issue Place -->
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
                    <tbody class="divide-y divide-slate-800" id="passportPlacesTableBodyEdit">
                        @foreach($passportIssuePlaces as $place)
                            <tr id="passport_place_row_edit_{{ $place->id }}">
                                <td class="p-2 font-mono text-slate-500">{{ $loop->iteration }}</td>
                                <td class="p-2">
                                    <input type="text" id="place_input_edit_{{ $place->id }}" value="{{ $place->name }}" class="bg-slate-800 border border-slate-700 rounded px-2.5 py-1 text-xs w-full text-white focus:border-sky-500 focus:outline-none font-bold">
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
    function transactionEditForm(txRates = {}) {
        return {
            txTypeRates: txRates || {},
            typeId: '{{ $transaction->transaction_type_id }}',
            numYears: '{{ $transaction->num_years }}',
            plateTypeId: '{{ $transaction->plate_type_id }}',
            carMakeId: '{{ $transaction->car_make_id }}',
            carColorId: '{{ $transaction->car_color_id }}',
            passportIssuePlace: '{{ old("passport_issue_place", $transaction->passport_issue_place) }}',
            showPassportPlaceModal: false,
            newPassportPlaceName: '',
            startDate: '{{ $transaction->start_date?->format("Y-m-d") ?? date("Y-m-d") }}',
            endDate: '{{ $transaction->end_date?->format("Y-m-d") ?? date("Y-m-d", strtotime("+1 year")) }}',

            fees: {
                total_pay: {{ $transaction->total_pay }}
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

            updatePassportPlace(id) {
                const input = document.getElementById('place_input_edit_' + id);
                if (!input || !input.value.trim()) return;
                const newName = input.value.trim();
                const oldName = input.defaultValue;

                fetch(`/lookup/passport-issue-place/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: newName })
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        const sel = document.getElementById('passportIssuePlaceSelectEdit');
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
                const input = document.getElementById('place_input_edit_' + id);
                const name = input ? input.value : '';
                if (!confirm(`ئایا دڵنیایت لە سڕینەوەی شوێنی (${name})؟`)) return;

                fetch(`/lookup/passport-issue-place/${id}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        const row = document.getElementById('passport_place_row_edit_' + id);
                        if (row) row.remove();
                        const sel = document.getElementById('passportIssuePlaceSelectEdit');
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
                fetch('{{ route("lookup.passport_issue_place") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: nameToAdd })
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success && data.item) {
                        const sel = document.getElementById('passportIssuePlaceSelectEdit');
                        if (sel) {
                            const opt = document.createElement('option');
                            opt.value = data.item.name;
                            opt.textContent = data.item.name;
                            opt.selected = true;
                            sel.appendChild(opt);
                        }
                        const tbody = document.getElementById('passportPlacesTableBodyEdit');
                        if (tbody) {
                            const count = tbody.children.length + 1;
                            const tr = document.createElement('tr');
                            tr.id = 'passport_place_row_edit_' + data.item.id;
                            tr.innerHTML = `
                                <td class="p-2 font-mono text-slate-500">${count}</td>
                                <td class="p-2">
                                    <input type="text" id="place_input_edit_${data.item.id}" value="${data.item.name}" class="bg-slate-800 border border-slate-700 rounded px-2.5 py-1 text-xs w-full text-white focus:border-sky-500 focus:outline-none font-bold">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="window.activeEditForm?.updatePassportPlace(${data.item.id})" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-[11px] font-bold transition">پاشەکەوت</button>
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="window.activeEditForm?.deletePassportPlace(${data.item.id})" class="px-2.5 py-1 bg-rose-600/80 hover:bg-rose-600 text-white rounded text-[11px] font-bold transition" title="سڕینەوە"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        }
                        this.passportIssuePlace = data.item.name;
                        this.newPassportPlaceName = '';
                        alert('شوێنی نوێ بە سەرکەوتوویی زیادکرا و دیاریکرا!');
                    } else if (data && data.errors && data.errors.name) {
                        alert(data.errors.name[0]);
                    }
                })
                .catch(() => alert('هەڵەیەک ڕوویدا لە کاتی زیادکردنی شوێن!'));
            },

            init() {
                window.activeEditForm = this;
            }
        }
    }
</script>
@endsection
