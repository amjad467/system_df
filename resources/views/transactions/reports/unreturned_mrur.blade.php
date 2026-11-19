@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    showUpdateModal: false, 
    selectedTxId: null, 
    selectedBarcode: '', 
    mrurNumber: '', 
    mrurDate: '{{ date('Y-m-d') }}',
    openModal(id, barcode) {
        this.selectedTxId = id;
        this.selectedBarcode = barcode;
        this.mrurNumber = '';
        this.showUpdateModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-file-circle-question"></i>
                </span>
                <span>نووسراوە نەگەڕاوەکانی هاتوچۆ (مرور سفر)</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                ئەو مامەڵانەی کە نووسراوی گومرگیان بۆ کراوە بەڵام ژمارەی نووسراوی وەڵامی هاتوچۆیان (مرور) سفرە یان بەتاڵە و تا ئێستا نەگەڕاونەتەوە.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-chart-pie"></i>
                <span>داشبۆرد</span>
            </a>
            <a href="{{ route('transactions.official_prints') }}" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>چاپی نوسراوەکان</span>
            </a>
        </div>
    </div>

    <!-- Summary Banner -->
    <div class="p-5 rounded-2xl bg-gradient-to-l from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/30 dark:border-amber-500/20 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-lg shadow-amber-500/30">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-amber-700 dark:text-amber-400 block">کۆی نووسراوە نەگەڕاوەکان (مرور سفر)</span>
                <span class="text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight">{{ number_format($totalUnreturned) }}</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mr-2">مامەڵەی چاوەڕوانی وەڵامی هاتوچۆ</span>
            </div>
        </div>
        <div class="text-xs text-slate-600 dark:text-slate-300 bg-white/80 dark:bg-slate-900/80 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800">
            <i class="fa-solid fa-circle-info text-amber-500 ml-1"></i>
            کاتێک نووسراوەکە لە هاتوچۆوە گەڕایەوە، دەتوانیت بە دوگمەی <strong class="text-amber-600 dark:text-amber-400">تۆمارکردن</strong> ژمارەکەی لێبدەیت تا لەم لیستە لابرێت.
        </div>
    </div>

    <!-- Filters -->
    <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
        <form method="GET" action="{{ route('reports.unreturned_mrur') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <!-- Search -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">گەڕان (بارکۆد، تابلۆ، ناو، شاسی، نووسراو)</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="بارکۆد، تابلۆ، ناوی هاووڵاتی..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:border-amber-500 pl-8">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-xs text-slate-400"></i>
                </div>
            </div>

            <!-- Traffic Directorate -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">بەڕێوەبەرایەتی هاتوچۆ</label>
                <select name="directorate_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:border-amber-500">
                    <option value="">هەموو هاتوچۆکان</option>
                    @foreach($directorates as $d)
                        <option value="{{ $d->id }}" {{ $directorateId == $d->id ? 'selected' : '' }}>{{ $d->name_kurdish }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Year -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">ساڵی مامەڵە</label>
                <select name="year" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:border-amber-500">
                    <option value="">هەموو ساڵەکان</option>
                    @for($y = 2026; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-filter"></i>
                    <span>فلتەرکردن</span>
                </button>
                @if($search || $directorateId || $year)
                    <a href="{{ route('reports.unreturned_mrur') }}" class="py-2 px-3 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-400">
                        <th class="py-3 px-3">#</th>
                        <th class="py-3 px-3">بارکۆد</th>
                        <th class="py-3 px-3">ناوی هاووڵاتی</th>
                        <th class="py-3 px-3">تابلۆ و ئۆتۆمبێل</th>
                        <th class="py-3 px-3">نووسراوی گومرگ</th>
                        <th class="py-3 px-3">نووسراوی هاتوچۆ</th>
                        <th class="py-3 px-3">بەرواری مامەڵە</th>
                        <th class="py-3 px-3 text-center">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs text-slate-800 dark:text-slate-200">
                    @forelse($transactions as $index => $tx)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-3 text-slate-400 text-[11px] font-mono">{{ $transactions->firstItem() + $index }}</td>
                            <td class="py-3 px-3">
                                <a href="{{ route('transactions.show', $tx->id) }}" class="font-mono font-bold text-sky-600 dark:text-sky-400 hover:underline">
                                    {{ $tx->barcode }}
                                </a>
                                @if($tx->booklet_number)
                                    <span class="block text-[10px] text-slate-400 font-mono">دەفتەر: {{ $tx->booklet_number }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-semibold">
                                <div>{{ $tx->visitor_name }}</div>
                                @if($tx->phone_number)
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $tx->phone_number }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-mono font-bold tracking-wider text-slate-900 dark:text-slate-100">{{ $tx->plate_number }}</div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                    {{ $tx->carMake?->name_kurdish ?? 'دیاری نەکراو' }} 
                                    @if($tx->trafficDirectorate)
                                        - <span class="text-sky-600 dark:text-sky-400">{{ $tx->trafficDirectorate->name_kurdish }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                @if($tx->no_nusraw_gumrg)
                                    <span class="inline-flex items-center gap-1 font-mono font-bold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md text-[11px]">
                                        <i class="fa-solid fa-stamp text-[9px] text-sky-500"></i>
                                        <span>{{ $tx->no_nusraw_gumrg }}</span>
                                    </span>
                                    @if($tx->date_nusraw_gumrg)
                                        <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ $tx->date_nusraw_gumrg?->format('Y-m-d') }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                                    <i class="fa-solid fa-hourglass-half text-[10px] text-amber-500"></i>
                                    <span>نەگەڕاوەتەوە (مرور 0)</span>
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                {{ $tx->transaction_date ? \Carbon\Carbon::parse($tx->transaction_date)->format('Y-m-d') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <button type="button" 
                                        @click="openModal({{ $tx->id }}, '{{ $tx->barcode }}')"
                                        class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-900 rounded-lg text-[11px] font-bold shadow-sm transition flex items-center justify-center gap-1 mx-auto cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                    <span>تۆمارکردن</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-3xl block mb-2"></i>
                                هیچ نووسراوێکی نەگەڕاوەی هاتوچۆ نەدۆزرایەوە بەپێی ئەم فلتەرە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Update Traffic Letter Modal -->
    <div x-show="showUpdateModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div @click.away="showUpdateModal = false" 
             x-transition.scale.duration.150ms
             class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-amber-500"></i>
                    <span>تۆمارکردنی نووسراوی وەڵامی هاتوچۆ</span>
                </h3>
                <button type="button" @click="showUpdateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="'/transactions/' + selectedTxId + '/update-traffic-letter'" method="POST" class="space-y-4">
                @csrf
                <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 rounded-xl text-xs text-amber-800 dark:text-amber-200">
                    تۆمارکردنی ژمارەی نووسراوی وەڵامی هاتوچۆ بۆ مامەڵەی: <span class="font-mono font-bold" x-text="selectedBarcode"></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ژمارەی نووسراوی هاتوچۆ (مرور) *</label>
                    <input type="text" name="no_nusraw_mrur" x-model="mrurNumber" required placeholder="مثلاً: 22371" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 font-mono font-bold focus:outline-none focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">بەرواری نووسراوی هاتوچۆ</label>
                    <input type="date" name="date_nusraw_mrur" x-model="mrurDate" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 font-mono focus:outline-none focus:border-amber-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="showUpdateModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition">
                        پاشگەزبوونەوە
                    </button>
                    <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>تۆمارکردن و دەرهێنان لە لیست</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
