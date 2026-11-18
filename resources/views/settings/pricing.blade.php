@extends('layouts.app')

@section('title', 'ڕێکخستنەکانی سیستەم - بەڕێوەبەر و نرخەکان')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-900/80 p-5 rounded-2xl border border-slate-800 shadow-xl">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-sliders text-amber-400"></i>
                <span>ڕێکخستنەکانی سیستەم (سۆپەر ئەدمین)</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">دیاریکردنی بەڕێوەبەری سەرەکی بۆ نووسراوە فەرمییەکان، دەستکاریکردنی بڕی رسومات، پول، فۆڕم و کەشف.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settings.lookups', ['tab' => 'directors']) }}" class="px-3.5 py-2 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-user-tie"></i> لیستی سەرجەم بەڕێوەبەران
            </a>
            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1">
                <i class="fa-solid fa-arrow-right ml-1"></i> گەڕانەوە
            </a>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('settings.pricing.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- ==========================================
             SECTION 1: DIRECTOR SELECTION (ڕێکخستنی هەڵبژاردنی بەڕێوەبەر)
             ========================================== -->
        <div class="bg-gradient-to-br from-slate-900 via-slate-850 to-indigo-950/60 border-2 border-indigo-500/50 rounded-2xl shadow-2xl p-5 sm:p-6 space-y-4 relative overflow-hidden">
            <div class="absolute -top-12 -left-12 w-36 h-36 bg-indigo-600/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-700/80 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-lg border border-indigo-500/30 shadow-inner">
                        <i class="fa-solid fa-stamp"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-black text-white flex items-center gap-2">
                            <span>ڕێکخستنی هەڵبژاردنی بەڕێوەبەر بۆ نووسراوە فەرمییەکان</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-bold">داواکراو</span>
                        </h3>
                        <p class="text-xs text-slate-400">ناوی ئەو بەڕێوەبەرەی لێرە دیاری دەکەیت، لەسەر هەموو نووسراوەکان دەردەکەوێت (قەدەغەکردنی هاتووچۆ، پووچەڵکردنەوە، پشکنین، بەڵێننامە).</p>
                    </div>
                </div>

                @if($activeDirector)
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-black shadow">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        <span>بەڕێوەبەری سەرەکی ئێستا:</span>
                        <span class="text-white underline">{{ $activeDirector->name }}</span>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end pt-2">
                <!-- Dropdown Selector -->
                <div class="lg:col-span-7 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-user-tie text-indigo-400"></i>
                        <span>هەڵبژاردنی بەڕێوەبەر لە لیستەکە:</span>
                    </label>
                    <div class="relative">
                        <select name="active_director_id" class="w-full bg-slate-900 border-2 border-indigo-500/60 focus:border-indigo-400 rounded-xl px-3.5 py-2.5 text-sm font-bold text-white focus:outline-none transition shadow-inner">
                            @foreach($directors as $dir)
                                <option value="{{ $dir->id }}" {{ $dir->is_active ? 'selected' : '' }}>
                                    {{ $dir->name }} {{ $dir->is_active ? ' ★ (بەڕێوەبەری سەرەکی چالاک)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Quick Add New Director Name -->
                <div class="lg:col-span-5 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-user-plus text-emerald-400"></i>
                        <span>یان ناوی بەڕێوەبەری نوێ بنووسە:</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="new_director_name" placeholder="بۆ نموونە: عەمید هێمن مامەند"
                               class="flex-1 bg-slate-900 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition">
                        <label class="flex items-center gap-1 px-2.5 bg-slate-800 border border-slate-700 rounded-xl text-[11px] text-slate-300 cursor-pointer select-none" title="ئەگەر چێک بکرێت، یەکسەر دەبێتە بەڕێوەبەری سەرەکی چالاک">
                            <input type="checkbox" name="make_new_director_active" value="1" checked class="rounded border-slate-700 text-indigo-600 focus:ring-0">
                            <span>چالاکبێت</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between text-[11px] text-slate-400 border-t border-slate-800">
                <span class="flex items-center gap-1.5 text-indigo-300">
                    <i class="fa-solid fa-circle-info"></i>
                    دەتوانیت دوگمەی خوارەوە دابگریت بۆ پاشەکەوتکردنی بەڕێوەبەر و نرخەکان بەیەکەوە.
                </span>
                <a href="{{ route('settings.lookups', ['tab' => 'directors']) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-bold underline">
                    بەڕێوەبردنی تەواوی لیستەکە و سڕینەوە ←
                </a>
            </div>
        </div>

        <!-- ==========================================
             SECTION 2: TRANSACTION FEES (ڕسوومات و نرخەکان)
             ========================================== -->
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="text-sm sm:text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-amber-400"></i>
                    <span>بڕی رسومات، فۆڕم، پول و کەشفی جۆرەکانی مامەڵە</span>
                </h3>
                <span class="text-xs text-slate-400 font-mono">بە دیناری عێراقی (IQD)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm text-slate-200">
                    <thead class="bg-slate-900/90 text-slate-400 text-xs uppercase border-b border-slate-700">
                        <tr>
                            <th class="p-3">جۆری مامەڵە</th>
                            <th class="p-3">١ ساڵ (د.ع)</th>
                            <th class="p-3">٢ ساڵ (د.ع)</th>
                            <th class="p-3">٣ ساڵ (د.ع)</th>
                            <th class="p-3">پول (d.ع)</th>
                            <th class="p-3">فۆڕم (د.ع)</th>
                            <th class="p-3">سزا (د.ع)</th>
                            <th class="p-3">کەشف (د.ع)</th>
                            <th class="p-3">کەشفى پاس</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($transactionTypes as $tt)
                            <tr class="hover:bg-slate-700/30 transition">
                                <td class="p-3 font-bold text-sky-400">
                                    {{ $tt->name_kurdish }}
                                    <span class="block text-[11px] text-slate-500 font-mono">{{ $tt->name_english }}</span>
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][pay_1_year]" value="{{ (int)$tt->pay_1_year }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-100">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][pay_2_year]" value="{{ (int)$tt->pay_2_year }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-100">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][pay_3_year]" value="{{ (int)$tt->pay_3_year }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-100">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][stamp_pay]" value="{{ (int)$tt->stamp_pay }}" class="w-20 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-100">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][form_pay]" value="{{ (int)$tt->form_pay }}" class="w-20 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-100">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][fine_pay]" value="{{ (int)$tt->fine_pay }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-amber-400">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][inspection_pay]" value="{{ (int)$tt->inspection_pay }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-indigo-300">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][bus_inspection_pay]" value="{{ (int)$tt->bus_inspection_pay }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-indigo-300">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-700 pt-4">
                <div class="text-xs text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                    <span>سەرجەم گۆڕانکارییەکان لە لۆگی سیستەمدا بە ناوی ئەدمین تۆمار دەکرێن.</span>
                </div>
                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-sm rounded-xl shadow-xl shadow-amber-600/30 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-lg"></i>
                    <span>پاشەکەوتکردن و نوێکردنەوەی تەواوی ڕێکخستنەکان</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
