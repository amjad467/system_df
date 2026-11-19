@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    showUpdateModal: false, 
    selectedTxId: null, 
    selectedBarcode: '', 
    selectedCitizen: '',
    selectedPlate: '',
    selectedCar: '',
    selectedGumrg: '',
    currentBox: '',
    mrurNumber: '', 
    mrurDate: '{{ date('Y-m-d') }}',
    boxNumber: '',
    openModal(data) {
        this.selectedTxId = data.id;
        this.selectedBarcode = data.barcode;
        this.selectedCitizen = data.citizen;
        this.selectedPlate = data.plate;
        this.selectedCar = data.car;
        this.selectedGumrg = data.gumrg;
        this.currentBox = data.box || '-';
        this.mrurNumber = (data.mrur && data.mrur !== '0') ? data.mrur : '';
        this.mrurDate = data.mrurDate ? data.mrurDate.substring(0, 10) : '{{ date('Y-m-d') }}';
        this.boxNumber = data.box || '';
        this.showUpdateModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 print:hidden">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-file-circle-question"></i>
                </span>
                <span>ئامار و بەدواداچوونی نووسراوەکانی هاتوچۆ (مرور)</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                ئەو مامەڵانەی کە نووسراوی گومرگیان هەیە، لێرە دەتوانیت ژمارەی بۆکس، ناوی هاووڵاتی و تابلۆ ببینیت و پاش گەڕانەوە ژمارەی نووسراوی هاتوچۆیان لێبدەیت.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>چاپی لیست</span>
            </button>
            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-chart-pie"></i>
                <span>داشبۆرد</span>
            </a>
        </div>
    </div>

    <!-- Printable Header (Visible Only When Printing) -->
    <div class="hidden print:block text-center border-b pb-4 mb-4">
        <h1 class="text-xl font-bold">بەڕێوەبەرایەتی گشتی گومرگ - سیستەمی دەفتەری گەشتیاری</h1>
        <h2 class="text-base font-bold mt-1">
            لیستی بەدواداچوونی نووسراوەکانی هاتوچۆ
            @if($status === 'returned')
                (نووسراوە گەڕاوەکان)
            @elseif($status === 'all')
                (سەرجەم نووسراوەکان)
            @else
                (نووسراوە نەگەڕاوەکان - مرور 0)
            @endif
        </h2>
        <div class="text-xs text-slate-600 mt-1 flex justify-between px-6">
            <span>کۆی تۆمارەکان: {{ number_format($totalFiltered) }}</span>
            <span>بەرواری چاپ: {{ date('Y-m-d H:i') }}</span>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 print:hidden">
        <!-- Unreturned KPI -->
        <a href="{{ route('reports.unreturned_mrur', ['status' => 'unreturned']) }}" 
           class="p-4 rounded-2xl border transition group {{ $status === 'unreturned' ? 'bg-amber-500/15 border-amber-500 shadow-md' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-amber-400' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-700 dark:text-amber-400">نووسراوە نەگەڕاوەکان (مرور 0)</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ number_format($unreturnedCount) }}</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold">چاوەڕوانی وەڵام</span>
            </div>
        </a>

        <!-- Returned KPI -->
        <a href="{{ route('reports.unreturned_mrur', ['status' => 'returned']) }}" 
           class="p-4 rounded-2xl border transition group {{ $status === 'returned' ? 'bg-emerald-500/15 border-emerald-500 shadow-md' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-emerald-400' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">نووسراوە گەڕاوەکان (تۆمارکراو)</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($returnedCount) }}</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold">ژمارەدار و تەواوکراو</span>
            </div>
        </a>

        <!-- Total / Rate -->
        @php
            $sumBoth = max(1, $unreturnedCount + $returnedCount);
            $rate = round(($returnedCount / $sumBoth) * 100, 1);
        @endphp
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 dark:text-slate-300">ڕێژەی گەڕانەوەی هاتوچۆ</span>
                <div class="w-9 h-9 rounded-xl bg-sky-500/20 text-sky-500 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-sky-600 dark:text-sky-400 font-mono">{{ $rate }}%</span>
                <span class="text-[11px] text-slate-500 font-mono">{{ number_format($returnedCount) }} / {{ number_format($sumBoth) }}</span>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 print:hidden overflow-x-auto">
        <a href="{{ route('reports.unreturned_mrur', array_merge(request()->except('status', 'page'), ['status' => 'unreturned'])) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 {{ $status === 'unreturned' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <i class="fa-solid fa-hourglass-half"></i>
            <span>نووسراوە نەگەڕاوەکان (مرور 0)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black {{ $status === 'unreturned' ? 'bg-slate-950 text-amber-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-300' }}">
                {{ number_format($unreturnedCount) }}
            </span>
        </a>

        <a href="{{ route('reports.unreturned_mrur', array_merge(request()->except('status', 'page'), ['status' => 'returned'])) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 {{ $status === 'returned' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <i class="fa-solid fa-circle-check"></i>
            <span>نووسراوە گەڕاوەکان (تۆمارکراو)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black {{ $status === 'returned' ? 'bg-emerald-800 text-white' : 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300' }}">
                {{ number_format($returnedCount) }}
            </span>
        </a>

        <a href="{{ route('reports.unreturned_mrur', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 {{ $status === 'all' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <i class="fa-solid fa-list-check"></i>
            <span>سەرجەم مامەڵەکان (هەمووی)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black {{ $status === 'all' ? 'bg-sky-800 text-white' : 'bg-sky-500/20 text-sky-600 dark:text-sky-300' }}">
                {{ number_format($sumBoth) }}
            </span>
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm print:hidden">
        <form method="GET" action="{{ route('reports.unreturned_mrur') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <input type="hidden" name="status" value="{{ $status }}">

            <!-- General Search -->
            <div class="md:col-span-2">
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">گەڕان (بارکۆد، تابلۆ، ناو، شاسی، نووسراو)</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="بارکۆد، تابلۆ، ناوی هاووڵاتی..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:border-amber-500 pl-8">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-xs text-slate-400"></i>
                </div>
            </div>

            <!-- Specific Box Number Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                    <i class="fa-solid fa-box-archive text-amber-500 ml-1"></i>
                    <span>ژمارەی بۆکس</span>
                </label>
                <input type="number" name="box_number" value="{{ $boxNumber }}" placeholder="مثلاً: 18 یان 67" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 font-mono focus:outline-none focus:border-amber-500">
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

            <!-- Filter Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-filter"></i>
                    <span>فلتەرکردن</span>
                </button>
                @if($search || $directorateId || $year || $boxNumber)
                    <a href="{{ route('reports.unreturned_mrur', ['status' => $status]) }}" class="py-2 px-3 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center" title="پاککردنەوەی فلتەرەکان">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-3.5 bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-table-list text-amber-500"></i>
                <span>ئەنجامە دۆزراوەکان:</span>
                <span class="font-mono text-amber-600 dark:text-amber-400 font-black">{{ number_format($totalFiltered) }} مامەڵە</span>
            </div>
            @if($boxNumber)
                <div class="bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 px-2.5 py-0.5 rounded-lg font-mono">
                    فلتەری بۆکس: #{{ $boxNumber }}
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-400">
                        <th class="py-3 px-3">#</th>
                        <th class="py-3 px-3">بارکۆد و دەفتەر</th>
                        <th class="py-3 px-3">ناوی هاووڵاتی</th>
                        <th class="py-3 px-3">تابلۆ و ئۆتۆمبێل</th>
                        <th class="py-3 px-3 text-center">
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-solid fa-box-archive text-amber-500"></i>
                                <span>ژمارەی بۆکس</span>
                            </span>
                        </th>
                        <th class="py-3 px-3">نووسراوی گومرگ</th>
                        <th class="py-3 px-3">نووسراوی هاتوچۆ</th>
                        <th class="py-3 px-3">بەرواری مامەڵە</th>
                        <th class="py-3 px-3 text-center print:hidden">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs text-slate-800 dark:text-slate-200">
                    @forelse($transactions as $index => $tx)
                        @php
                            $isReturned = !empty($tx->no_nusraw_mrur) && $tx->no_nusraw_mrur !== '0';
                            $boxVal = $tx->NO_boxfile ?? $tx->NO_boxfile_puchal;
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-3 text-slate-400 text-[11px] font-mono">{{ $transactions->firstItem() + $index }}</td>
                            
                            <!-- Barcode & Booklet -->
                            <td class="py-3 px-3">
                                <a href="{{ route('transactions.show', $tx->id) }}" class="font-mono font-bold text-sky-600 dark:text-sky-400 hover:underline">
                                    {{ $tx->barcode }}
                                </a>
                                @if($tx->booklet_number)
                                    <span class="block text-[10px] text-slate-400 font-mono">دەفتەر: {{ $tx->booklet_number }}</span>
                                @endif
                            </td>

                            <!-- Citizen Name & Phone -->
                            <td class="py-3 px-3 font-semibold">
                                <div>{{ $tx->visitor_name }}</div>
                                @if($tx->phone_number)
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $tx->phone_number }}</div>
                                @endif
                            </td>

                            <!-- Plate & Car Info -->
                            <td class="py-3 px-3">
                                <div class="font-mono font-bold tracking-wider text-slate-900 dark:text-slate-100">{{ $tx->plate_number }}</div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                    {{ $tx->carMake?->name_kurdish ?? 'دیاری نەکراو' }} 
                                    @if($tx->trafficDirectorate)
                                        - <span class="text-sky-600 dark:text-sky-400">{{ $tx->trafficDirectorate->name_kurdish }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Box Number (ژمارەی بۆکس) -->
                            <td class="py-3 px-3 text-center">
                                @if($tx->NO_boxfile)
                                    <span class="inline-flex items-center gap-1.5 font-mono font-black text-amber-900 dark:text-amber-200 bg-amber-100/90 dark:bg-amber-900/40 border border-amber-300 dark:border-amber-700/60 px-3 py-1 rounded-xl text-xs shadow-sm" title="بۆکس فایلی ئەرشیف">
                                        <i class="fa-solid fa-box-archive text-amber-600 dark:text-amber-400 text-xs"></i>
                                        <span>بۆکس: {{ $tx->NO_boxfile }}</span>
                                    </span>
                                @elseif($tx->NO_boxfile_puchal)
                                    <span class="inline-flex items-center gap-1 font-mono font-bold text-rose-800 dark:text-rose-200 bg-rose-100 dark:bg-rose-900/30 border border-rose-300 dark:border-rose-700/50 px-2 py-0.5 rounded-lg text-xs" title="بۆکسی پووچەڵ">
                                        <i class="fa-solid fa-box-archive text-rose-500 text-[10px]"></i>
                                        <span>پووچەڵ: {{ $tx->NO_boxfile_puchal }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px] font-mono">-</span>
                                @endif
                            </td>

                            <!-- Customs Letter (نووسراوی گومرگ) -->
                            <td class="py-3 px-3">
                                @if($tx->no_nusraw_gumrg)
                                    <span class="inline-flex items-center gap-1 font-mono font-bold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md text-[11px]">
                                        <i class="fa-solid fa-stamp text-[9px] text-sky-500"></i>
                                        <span>{{ $tx->no_nusraw_gumrg }}</span>
                                    </span>
                                    @if($tx->date_nusraw_gumrg)
                                        <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ \Carbon\Carbon::parse($tx->date_nusraw_gumrg)->format('Y-m-d') }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- Traffic Letter (نووسراوی هاتوچۆ) -->
                            <td class="py-3 px-3">
                                @if($isReturned)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 font-mono">
                                        <i class="fa-solid fa-circle-check text-[10px] text-emerald-500"></i>
                                        <span>{{ $tx->no_nusraw_mrur }}</span>
                                    </span>
                                    @if($tx->date_nusraw_mrur)
                                        <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ \Carbon\Carbon::parse($tx->date_nusraw_mrur)->format('Y-m-d') }}</span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                                        <i class="fa-solid fa-hourglass-half text-[10px] text-amber-500"></i>
                                        <span>نەگەڕاوەتەوە (مرور 0)</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Transaction Date -->
                            <td class="py-3 px-3 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                {{ $tx->transaction_date ? \Carbon\Carbon::parse($tx->transaction_date)->format('Y-m-d') : '-' }}
                            </td>

                            <!-- Action -->
                            <td class="py-3 px-3 text-center print:hidden">
                                <button type="button" 
                                        @click="openModal({
                                            id: {{ $tx->id }},
                                            barcode: '{{ $tx->barcode }}',
                                            citizen: '{{ addslashes($tx->visitor_name ?? '') }}',
                                            plate: '{{ $tx->plate_number }}',
                                            car: '{{ addslashes($tx->carMake?->name_kurdish ?? '') }}',
                                            gumrg: '{{ $tx->no_nusraw_gumrg ?? '' }}',
                                            box: '{{ $boxVal ?? '' }}',
                                            mrur: '{{ $tx->no_nusraw_mrur ?? '' }}',
                                            mrurDate: '{{ $tx->date_nusraw_mrur ? \Carbon\Carbon::parse($tx->date_nusraw_mrur)->format('Y-m-d') : '' }}'
                                        })"
                                        class="px-3 py-1.5 rounded-lg text-[11px] font-bold shadow-sm transition flex items-center justify-center gap-1.5 mx-auto cursor-pointer {{ $isReturned ? 'bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 text-slate-800 dark:text-slate-200' : 'bg-amber-500 hover:bg-amber-600 text-slate-950 font-black' }}">
                                    <i class="fa-solid {{ $isReturned ? 'fa-pen-to-square' : 'fa-check' }} text-[10px]"></i>
                                    <span>{{ $isReturned ? 'گۆڕین / دەستکاری' : 'تۆمارکردنی نووسراو' }}</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-3xl block mb-2"></i>
                                هیچ نووسراوێک نەدۆزرایەوە بەپێی ئەم فلتەرە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 print:hidden">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Update Traffic Letter Modal -->
    <div x-show="showUpdateModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 print:hidden">
        <div @click.away="showUpdateModal = false" 
             x-transition.scale.duration.150ms
             class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </span>
                    <span>تۆمارکردن یان گۆڕینی نووسراوی وەڵامی هاتوچۆ</span>
                </h3>
                <button type="button" @click="showUpdateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Transaction Quick Information Card -->
            <div class="p-3.5 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-2xl grid grid-cols-2 gap-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px]">بارکۆدی مامەڵە:</span>
                    <span class="font-mono font-bold text-sky-600 dark:text-sky-400" x-text="selectedBarcode"></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">ناوی هاووڵاتی:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedCitizen"></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">تابلۆی ئۆتۆمبێل:</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white" x-text="selectedPlate"></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">نووسراوی گومرگ:</span>
                    <span class="font-mono font-bold text-slate-700 dark:text-slate-300" x-text="selectedGumrg"></span>
                </div>
                <div class="col-span-2 pt-1 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-[11px] text-slate-500">ژمارەی بۆکسی ئێستا:</span>
                    <span class="font-mono font-black text-amber-600 dark:text-amber-400" x-text="currentBox ? 'بۆکس: ' + currentBox : 'دیاری نەکراوە'"></span>
                </div>
            </div>

            <form :action="'/transactions/' + selectedTxId + '/update-traffic-letter'" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        ژمارەی نووسراوی وەڵامی هاتوچۆ (مرور) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="no_nusraw_mrur" 
                           x-model="mrurNumber" 
                           required 
                           placeholder="مثلاً: 22371" 
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 font-mono font-black focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                    <p class="text-[10px] text-slate-400 mt-1">
                        کاتێک ئەم ژمارەیە تۆمار دەکەیت، ڕاستەوخۆ دەبێتە نووسراوی گەڕاوە.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">بەرواری نووسراوی هاتوچۆ</label>
                        <input type="date" 
                               name="date_nusraw_mrur" 
                               x-model="mrurDate" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 font-mono focus:outline-none focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            <i class="fa-solid fa-box-archive text-amber-500 ml-1"></i>
                            <span>ژمارەی بۆکسی ئەرشیف</span>
                        </label>
                        <input type="number" 
                               name="NO_boxfile" 
                               x-model="boxNumber" 
                               placeholder="ژمارەی بۆکس..." 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-slate-100 font-mono focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="showUpdateModal = false" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition">
                        پاشگەزبوونەوە
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>تۆمارکردنی گۆڕانکارییەکان</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
