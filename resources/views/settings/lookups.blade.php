@extends('layouts.app')

@section('title', 'بەڕێوەبردنی داتاکانی سەرەتایی و بەڕێوەبەرایەتییەکان')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'txTypes' }">

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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add Form -->
            <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-5 shadow-xl">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-emerald-400"></i> زیادکردنی بەڕێوەبەر / لێپرسراوی نوێ
                </h3>
                <form action="{{ route('lookup.director') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">ناوی تەواوی بەڕێوەبەر / پلەکەی *</label>
                        <input type="text" name="name" required placeholder="بۆ نموونە: عەمید هێمن مامەند"
                               class="w-full bg-slate-900 text-sm text-slate-100 rounded-xl border border-slate-700 px-3 py-2 focus:outline-none focus:border-indigo-500">
                    </div>
                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg transition">
                        تۆمارکردن
                    </button>
                </form>
            </div>

            <!-- List Table -->
            <div class="lg:col-span-2 bg-slate-800/90 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-700 font-bold text-sm text-white">لیستی بەڕێوەبەر و لێپرسراوان</div>
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-900/80 text-slate-400">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">ناوی بەڕێوەبەر</th>
                            <th class="p-3">بەرواری زیادکردن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @foreach($directors as $d)
                            <tr class="hover:bg-slate-700/30 text-slate-200">
                                <td class="p-3 font-mono text-slate-400">{{ $d->id }}</td>
                                <td class="p-3 font-bold text-white">{{ $d->name }}</td>
                                <td class="p-3 font-mono text-slate-400">{{ $d->created_at?->toDateString() ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
