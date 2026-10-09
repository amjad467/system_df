@extends('layouts.app')

@section('content')
<div x-data="transactionEditForm()" class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fa-solid fa-pen-to-square ml-2 text-amber-400"></i> دەستکاریکردن و ڕاستکردنەوەی مامەڵە
            </h2>
            <p class="text-xs text-slate-400">بارکۆد: <strong class="font-mono text-sky-400">{{ $transaction->barcode }}</strong> (تەنها لە کاتی ئەنجامندنەدانی کەشف ڕێگەپێدراوە)</p>
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
                    <input type="text" name="no_nusraw_puchal" value="{{ old('no_nusraw_puchal', $transaction->no_nusraw_puchal) }}" placeholder="ژمارەی فەرمی نووسراوی گومرگ" dir="ltr"
                           class="w-full bg-slate-900 border border-rose-700/60 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-rose-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-rose-200 mb-1.5">بەرواری نووسراوی گومرگ</label>
                    <input type="date" name="date_nusraw_puchal" value="{{ old('date_nusraw_puchal', $transaction->date_nusraw_puchal?->format('Y-m-d')) }}"
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
</div>

<script>
    function transactionEditForm() {
        return {
            typeId: '{{ $transaction->transaction_type_id }}',
            numYears: '{{ $transaction->num_years }}',
            plateTypeId: '{{ $transaction->plate_type_id }}',
            carMakeId: '{{ $transaction->car_make_id }}',
            carColorId: '{{ $transaction->car_color_id }}',
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
            }
        }
    }
</script>
@endsection
