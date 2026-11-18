@extends('layouts.app')

@section('title', 'بەڕێوەبردنی داتاکانی سەرەتایی و بەڕێوەبەرایەتییەکان')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ request('tab', 'txTypes') }}' }">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-800 via-slate-800 to-indigo-950 p-6 rounded-2xl border border-slate-700/80 shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-indigo-400"></i>
                بەڕێوەبردنی جۆری مامەڵە و بەڕێوەبەرایەتییەکان
            </h2>
            <p class="text-xs md:text-sm text-slate-400 mt-1">تایبەت بە ئەدمین • زیادکردن و دەستکاریکردنی جۆری مامەڵەکان، بەڕێوەبەرایەتییەکان و بەڕێوەبەرەکان</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settings.pricing') }}" class="px-4 py-2 bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 border border-amber-500/40 text-xs font-bold rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-sliders"></i> ڕێکخستنی نرخەکان
            </a>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-bold rounded-xl transition">
                داشبۆرد
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-700/80 pb-2">
        <button type="button" @click="activeTab = 'txTypes'"
                :class="activeTab === 'txTypes' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-list-check"></i>
            <span>جۆرەکانی مامەڵە ({{ $transactionTypes->count() }})</span>
        </button>

        <button type="button" @click="activeTab = 'directorates'"
                :class="activeTab === 'directorates' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-building-flag"></i>
            <span>بەڕێوەبەرایەتییەکان ({{ $directorates->count() }})</span>
        </button>

        <button type="button" @click="activeTab = 'directors'"
                :class="activeTab === 'directors' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-user-tie"></i>
            <span>بەڕێوەبەر و لێپرسراوان ({{ $directors->count() }})</span>
        </button>
    </div>

    <!-- Tab 1: Transaction Types -->
    <div x-show="activeTab === 'txTypes'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add Form -->
            <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-5 shadow-xl">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-emerald-400"></i> زیادکردنی جۆری مامەڵەی نوێ
                </h3>
                <form action="{{ route('lookup.transaction_type') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">ناوی جۆری مامەڵە بە کوردی *</label>
                        <input type="text" name="name_kurdish" required placeholder="بۆ نموونە: گۆڕینی تابلۆ"
                               class="w-full bg-slate-900 text-sm text-slate-100 rounded-xl border border-slate-700 px-3 py-2 focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">ناوی بە ئینگلیزی (ئارەزوومەندانە)</label>
                        <input type="text" name="name_english" placeholder="e.g. Plate Change" dir="ltr"
                               class="w-full bg-slate-900 text-sm text-slate-100 rounded-xl border border-slate-700 px-3 py-2 focus:outline-none focus:border-indigo-500">
                    </div>
                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg transition">
                        تۆمارکردن
                    </button>
                </form>
            </div>

            <!-- List Table -->
            <div class="lg:col-span-2 bg-slate-800/90 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-700 font-bold text-sm text-white">لیستی جۆرەکانی مامەڵە</div>
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-900/80 text-slate-400">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">ناوی مامەڵە (کوردی)</th>
                            <th class="p-3">ناوی مامەڵە (ئینگلیزی)</th>
                            <th class="p-3">پێویستی بە دەفتەرە؟</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @foreach($transactionTypes as $tt)
                            <tr class="hover:bg-slate-700/30 text-slate-200">
                                <td class="p-3 font-mono text-slate-400">{{ $tt->id }}</td>
                                <td class="p-3 font-bold text-white">{{ $tt->name_kurdish }}</td>
                                <td class="p-3 font-mono text-slate-400" dir="ltr">{{ $tt->name_english ?? '-' }}</td>
                                <td class="p-3">
                                    @if($tt->requiresBooklet())
                                        <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 text-[10px] font-bold">بەڵێ (دەفتەر)</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-slate-700 text-slate-400 text-[10px]">نەخێر</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: Traffic Directorates -->
    <div x-show="activeTab === 'directorates'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add Form -->
            <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-5 shadow-xl">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-emerald-400"></i> زیادکردنی بەڕێوەبەرایەتی نوێ
                </h3>
                <form action="{{ route('lookup.directorate') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">ناوی بەڕێوەبەرایەتی بە کوردی *</label>
                        <input type="text" name="name_kurdish" required placeholder="بۆ نموونە: بەڕێوەبەرایەتی هاتوچۆی هەولێر"
                               class="w-full bg-slate-900 text-sm text-slate-100 rounded-xl border border-slate-700 px-3 py-2 focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">ناوی بە ئینگلیزی (ئارەزوومەندانە)</label>
                        <input type="text" name="name_english" placeholder="e.g. Erbil Traffic Directorate" dir="ltr"
                               class="w-full bg-slate-900 text-sm text-slate-100 rounded-xl border border-slate-700 px-3 py-2 focus:outline-none focus:border-indigo-500">
                    </div>
                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg transition">
                        تۆمارکردن
                    </button>
                </form>
            </div>

            <!-- List Table -->
            <div class="lg:col-span-2 bg-slate-800/90 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-700 font-bold text-sm text-white">لیستی بەڕێوەبەرایەتییەکانی هاتوچۆ</div>
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-900/80 text-slate-400">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">ناوی بەڕێوەبەرایەتی</th>
                            <th class="p-3">ناوەکەی بە ئینگلیزی</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @foreach($directorates as $dir)
                            <tr class="hover:bg-slate-700/30 text-slate-200">
                                <td class="p-3 font-mono text-slate-400">{{ $dir->id }}</td>
                                <td class="p-3 font-bold text-white">{{ $dir->name_kurdish }}</td>
                                <td class="p-3 font-mono text-slate-400" dir="ltr">{{ $dir->name_english ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 3: Directors -->
    <div x-show="activeTab === 'directors'" x-cloak class="space-y-6">
        <!-- Notice banner -->
        <div class="p-4 bg-indigo-950/40 border border-indigo-700/50 rounded-2xl flex items-center gap-3 text-xs text-indigo-200">
            <i class="fa-solid fa-circle-info text-indigo-400 text-lg"></i>
            <div>
                <span class="font-bold text-white block">ڕێنمایی ناوی بەڕێوەبەر بۆ نووسراوە فەرمییەکان:</span>
                ناوی ئەو بەڕێوەبەرەی لێرە هەڵیبژێریت یان زیادی بکەیت، بە شێوەیەکی خۆکار لەسەر سەرجەم نووسراوە فەرمییەکان چاپ دەبێت (نووسراوی قەدەغەکردنی هاتووچۆ، پووچەڵکردنەوە، پشکنین، و بەڵێننامە) بەبێ پێویستی دەستکاری لە فۆرمی تۆماردا.
            </div>
        </div>

        <!-- Dedicated Form to Pick Active Director -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-850 to-indigo-950 p-5 rounded-2xl border-2 border-indigo-500/50 shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-700/80 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-base border border-indigo-500/30">
                        <i class="fa-solid fa-stamp"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-white">فۆڕمی هەڵبژاردن و دیاریکردنی بەڕێوەبەری سەرەکی نووسراوەکان</h4>
                        <p class="text-[11px] text-slate-400">بەڕێوەبەرێک لەم لیستە هەڵبژێرە و دوگمەی پاشەکەوتکردن دابگرە.</p>
                    </div>
                </div>

                @php $activeDir = $directors->firstWhere('is_active', true) ?? $directors->first(); @endphp
                @if($activeDir)
                    <div class="px-3 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-xs font-bold flex items-center gap-1.5 shadow">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        <span>بەڕێوەبەری چالاکی ئێستا:</span>
                        <strong class="text-white underline">{{ $activeDir->name }}</strong>
                    </div>
                @endif
            </div>

            <form action="{{ route('lookup.director.set_active_dropdown') }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                @csrf
                <div class="flex-1">
                    <select name="director_id" class="w-full bg-slate-900 border-2 border-indigo-500/60 focus:border-indigo-400 rounded-xl px-3.5 py-2.5 text-sm font-bold text-white focus:outline-none shadow-inner">
                        @foreach($directors as $d)
                            <option value="{{ $d->id }}" {{ $d->is_active ? 'selected' : '' }}>
                                {{ $d->name }} {{ $d->is_active ? ' ★ (بەڕێوەبەری سەرەکی ئێستا)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-sm"></i>
                    <span>پاشەکەوتکردن و دیاریکردن بۆ نووسراو</span>
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add Form -->
            <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-5 shadow-xl">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-emerald-400"></i> زیادکردنی بەڕێوەبەر / لێپرسراوی نوێ
                </h3>
                <form action="{{ route('lookup.director') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">ناوی تەواوی بەڕێوەبەر / پلەکەی *</label>
                        <input type="text" name="name" required placeholder="بۆ نموونە: عەمید هێمن مامەند"
                               class="w-full bg-slate-900 text-sm text-slate-100 rounded-xl border border-slate-700 px-3 py-2 focus:outline-none focus:border-indigo-500">
                    </div>
                    <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-700 text-indigo-600 focus:ring-0">
                        <span>یەکسەر وەک بەڕێوەبەری سەرەکی چالاکبێت</span>
                    </label>
                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg transition">
                        تۆمارکردن و زیادکردن
                    </button>
                </form>
            </div>

            <!-- List Table -->
            <div class="lg:col-span-2 bg-slate-800/90 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-700 font-bold text-sm text-white flex items-center justify-between">
                    <span>لیستی بەڕێوەبەر و لێپرسراوان</span>
                    <span class="text-xs text-slate-400 font-normal">کۆی گشتی: {{ $directors->count() }} بەڕێوەبەر</span>
                </div>
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-900/80 text-slate-400">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">ناوی بەڕێوەبەر / لێپرسراو</th>
                            <th class="p-3 text-center">دۆخی دەرکەوتن لەسەر نووسراوەکان</th>
                            <th class="p-3 text-left">کردارەکان</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @foreach($directors as $d)
                            <tr class="hover:bg-slate-700/30 text-slate-200 transition {{ $d->is_active ? 'bg-indigo-950/20' : '' }}">
                                <td class="p-3 font-mono text-slate-400">{{ $d->id }}</td>
                                <td class="p-3 font-bold text-white text-sm">
                                    {{ $d->name }}
                                </td>
                                <td class="p-3 text-center">
                                    @if($d->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-black text-xs shadow">
                                            <i class="fa-solid fa-stamp text-emerald-400"></i> بەڕێوەبەری سەرەکی لەسەر نووسراوەکان
                                        </span>
                                    @else
                                        <span class="text-slate-500 text-xs font-mono">ناچالاک</span>
                                    @endif
                                </td>
                                <td class="p-3 text-left">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(!$d->is_active)
                                            <form action="{{ route('lookup.director.set_active', $d->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow">
                                                    <i class="fa-solid fa-check"></i> دەستنیشانکردن بۆ نووسراو
                                                </button>
                                            </form>
                                            <form action="{{ route('lookup.director.destroy', $d->id) }}" method="POST" class="inline" onsubmit="return confirm('ئایا دڵنیایت لە سڕینەوەی ئەم بەڕێوەبەرە؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-xl text-xs transition" title="سڕینەوە">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-emerald-400 font-bold px-2">چالاکە</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
