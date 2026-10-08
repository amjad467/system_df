@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Title & Fast Stats -->
    <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white flex items-center">
                <i class="fa-solid fa-print ml-3 text-sky-400"></i>
                لیستی چاپی نوسراوە فەرمییەکان و دەفتەر
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                چاپی نوسراوی (پووچەڵکردنەوە، دانانی نیشانەی ڕەفتارنەکردن، پشکنین، و دەفتەر) پاش ئەنجامدانی پارەدان
            </p>
        </div>

        <!-- Dashboard Fast Stats -->
        <div class="flex items-center space-x-3 space-x-reverse">
            <div class="bg-slate-900/90 border border-amber-500/40 px-4 py-2.5 rounded-xl text-center">
                <span class="text-xs text-amber-300 block">گشتی چاوەڕوانی چاپ</span>
                <strong class="text-lg font-mono font-bold text-amber-400">{{ $pendingCountTotal }}</strong>
            </div>
            <div class="bg-slate-900/90 border border-emerald-500/40 px-4 py-2.5 rounded-xl text-center">
                <span class="text-xs text-emerald-300 block">گشتی چاپکراوەکان</span>
                <strong class="text-lg font-mono font-bold text-emerald-400">{{ $printedCountTotal }}</strong>
            </div>
            <div class="bg-slate-900/90 border border-sky-500/40 px-4 py-2.5 rounded-xl text-center">
                <span class="text-xs text-sky-300 block">پارەدانی ئەمرۆ</span>
                <strong class="text-lg font-mono font-bold text-sky-400">{{ $totalPaidToday }}</strong>
            </div>
        </div>
    </div>

    <!-- Filters & Navigation Tabs -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 shadow-lg flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Two Main Tabs (دوو بەشەکە) -->
        <div class="flex items-center bg-slate-900 p-1.5 rounded-xl border border-slate-700/80 w-full md:w-auto">
            <a href="{{ route('transactions.official_prints', ['tab' => 'pending', 'date' => $selectedDate, 'search' => $search]) }}"
               class="flex-1 md:flex-none px-5 py-2.5 rounded-lg text-sm font-extrabold transition flex items-center justify-center {{ $tab === 'pending' ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <i class="fa-solid fa-clock ml-2"></i>
                چاوەڕوانی چاپ (Pending Print)
                <span class="mr-2.5 bg-amber-950 text-amber-200 border border-amber-500/50 text-xs px-2.5 py-0.5 rounded-full font-mono font-black">
                    {{ $pendingCountTotal }}
                </span>
            </a>
            
            <a href="{{ route('transactions.official_prints', ['tab' => 'printed', 'date' => $selectedDate, 'search' => $search]) }}"
               class="flex-1 md:flex-none px-5 py-2.5 rounded-lg text-sm font-extrabold transition flex items-center justify-center {{ $tab === 'printed' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <i class="fa-solid fa-circle-check ml-2"></i>
                چاپکراوەکان (Printed Archive)
                <span class="mr-2.5 bg-emerald-950 text-emerald-200 border border-emerald-500/50 text-xs px-2.5 py-0.5 rounded-full font-mono font-black">
                    {{ $printedCountTotal }}
                </span>
            </a>
        </div>

        <!-- Date & Search Filters -->
        <form action="{{ route('transactions.official_prints') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="hidden" name="tab" value="{{ $tab }}">
            
            <!-- Quick Date Filters -->
            <div class="flex items-center space-x-2 space-x-reverse">
                <input type="date" name="date" value="{{ $selectedDate === 'all' ? '' : $selectedDate }}" 
                       class="bg-slate-900 border border-slate-700 text-white text-xs font-mono rounded-xl px-3 py-2 focus:border-sky-500 focus:outline-none">
                
                <a href="{{ route('transactions.official_prints', ['tab' => $tab, 'date' => date('Y-m-d'), 'search' => $search]) }}" 
                   class="px-3 py-2 {{ $selectedDate === date('Y-m-d') ? 'bg-sky-600 text-white font-black' : 'bg-sky-600/20 text-sky-300 hover:bg-sky-600 hover:text-white' }} border border-sky-500/40 text-xs font-bold rounded-xl transition">
                    ئەمرۆ
                </a>
                
                <a href="{{ route('transactions.official_prints', ['tab' => $tab, 'date' => 'all', 'search' => $search]) }}" 
                   class="px-3 py-2 {{ $selectedDate === 'all' ? 'bg-indigo-600 text-white font-black' : 'bg-slate-900 hover:bg-slate-700 text-slate-300' }} border border-slate-700 text-xs font-bold rounded-xl transition">
                    هەموو ڕێکەوتەکان
                </a>
            </div>

            <!-- Search Field -->
            <div class="relative flex-1 md:w-60">
                <input type="text" name="search" value="{{ $search }}" placeholder="گەڕان بە ناوی هاووڵاتی، بارکۆد، پلاک، پسولە..."
                       class="w-full bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-xs rounded-xl pr-9 pl-3 py-2 focus:border-sky-500 focus:outline-none">
                <button type="submit" class="absolute right-3 top-2.5 text-slate-400 hover:text-sky-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Alert Error / Alert Success Messages -->
    @if(session('success'))
        <div class="bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 p-4 rounded-xl text-sm font-bold flex items-center justify-between shadow-lg">
            <div class="flex items-center">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg ml-3"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-950/80 border border-rose-500/50 text-rose-200 p-4 rounded-xl text-sm font-bold flex items-center justify-between shadow-lg">
            <div class="flex items-center">
                <i class="fa-solid fa-triangle-exclamation text-rose-400 text-lg ml-3"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Transactions List Table -->
    <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm text-slate-300">
                <thead class="bg-slate-900/90 text-slate-400 text-xs font-bold uppercase border-b border-slate-700">
                    <tr>
                        <th class="py-3.5 px-4">بارکۆد و بەروار</th>
                        <th class="py-3.5 px-4">ناوی خاوەن / هاووڵاتی</th>
                        <th class="py-3.5 px-4">زانیاری ئۆتۆمبێل</th>
                        <th class="py-3.5 px-4">جۆری مامەڵە</th>
                        <th class="py-3.5 px-4">ژمارەی پسولە (37/أ)</th>
                        <th class="py-3.5 px-4 text-center">کرداری چاپی نوسراو</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($transactions as $tx)
                        @php
                            $typeName = $tx->transactionType->name_kurdish ?? '';
                            $isCancellation = $tx->isCancellation();
                            $isInspection = $tx->isInspection();
                            $requiresBooklet = $tx->requiresBooklet();
                        @endphp
                        <tr class="hover:bg-slate-700/30 transition" x-data="{ hasPrinted: false, showBookletModal: false }">
                            <!-- Barcode & Date -->
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-amber-300 block text-sm">{{ $tx->barcode }}</span>
                                <span class="text-[11px] text-slate-400 block font-mono">
                                    {{ $tx->paid_at ? $tx->paid_at->format('Y-m-d H:i') : ($tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : '') }}
                                </span>
                            </td>

                            <!-- Visitor Name -->
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $tx->visitor_name }}</span>
                                @if($tx->salana_number)
                                    <span class="text-[11px] text-slate-400 block">ساڵانە: {{ $tx->salana_number }}</span>
                                @endif
                            </td>

                            <!-- Car Info -->
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-emerald-400 block">{{ $tx->plate_number }} ({{ $tx->plateType->name_kurdish ?? 'تایبەت' }})</span>
                                <span class="text-xs text-slate-300 block">{{ $tx->carMake->name_kurdish ?? '' }} - {{ $tx->model_year }}</span>
                                <span class="text-[11px] font-mono text-slate-400 block uppercase">{{ Str::limit($tx->chassis_number, 12) }}</span>
                            </td>

                            <!-- Transaction Type Badge -->
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-950 text-sky-300 border border-sky-600/40">
                                    {{ $typeName }}
                                </span>
                            </td>

                            <!-- Payment Receipt Info & Booklet Info -->
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-sky-400 block">پسولە: {{ $tx->receipt_37a_number ?? '---' }}</span>
                                <span class="text-xs text-slate-400 block">بڕی: {{ number_format($tx->total_pay) }} د.ع</span>
                                @if($requiresBooklet)
                                    @if(!empty($tx->booklet_number))
                                        <span class="inline-flex items-center gap-1 text-[11px] font-mono font-bold text-indigo-300 bg-indigo-950/70 border border-indigo-700/60 px-2 py-0.5 rounded mt-1">
                                            <i class="fa-solid fa-passport"></i> دەفتەر: {{ $tx->booklet_number }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-300 bg-amber-950/70 border border-amber-600/60 px-2 py-0.5 rounded mt-1 animate-pulse">
                                            <i class="fa-solid fa-triangle-exclamation"></i> بێ ژمارەی دەفتەر
                                        </span>
                                    @endif
                                @endif
                            </td>

                            <!-- Official Print Actions -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    
                                    @if(!$tx->is_printed)
                                        <!-- PENDING TAB ACTIONS -->
                                        @if($isCancellation)
                                            <!-- Cancellation Report (پووچەڵکردنەوە) -->
                                            <template x-if="!hasPrinted">
                                                <a href="{{ route('transactions.print_cancellation', $tx->id) }}" target="_blank"
                                                   @click="hasPrinted = true"
                                                   class="px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center">
                                                    <i class="fa-solid fa-print ml-1.5"></i>
                                                    چاپی نوسراوی پووچەڵکردنەوە
                                                </a>
                                            </template>
                                        @elseif($isInspection)
                                            <!-- Inspection Report (پشکنین) -->
                                            <template x-if="!hasPrinted">
                                                <a href="{{ route('transactions.print_inspection_letter', $tx->id) }}" target="_blank"
                                                   @click="hasPrinted = true"
                                                   class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center">
                                                    <i class="fa-solid fa-print ml-1.5"></i>
                                                    چاپی نوسراوی پشکنین
                                                </a>
                                            </template>
                                        @else
                                            <!-- Booklet Issuance (دەرهێنانی دەفتەر) -->
                                            @if(!empty($tx->booklet_number))
                                                <!-- ACTIVE Print Booklet Button because booklet number is present -->
                                                <template x-if="!hasPrinted">
                                                    <a href="{{ route('transactions.print_booklet', $tx->id) }}" target="_blank"
                                                       @click="hasPrinted = true"
                                                       class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center"
                                                       title="چاپی دەفتەری گەشتیاری ({{ $tx->booklet_number }})">
                                                        <i class="fa-solid fa-passport ml-1.5"></i>
                                                        چاپی دەفتەر ({{ $tx->booklet_number }})
                                                    </a>
                                                </template>

                                                <!-- Button to edit booklet number if needed -->
                                                <button type="button" @click="showBookletModal = true"
                                                        class="p-2 text-slate-400 hover:text-amber-300 transition"
                                                        title="دەستکاریکردنی ژمارەی دەفتەر">
                                                    <i class="fa-solid fa-pen text-xs"></i>
                                                </button>
                                            @else
                                                <!-- INACTIVE / DISABLED Print Booklet Button because booklet number is missing -->
                                                <button type="button" disabled
                                                        class="px-3.5 py-2 bg-slate-800 text-slate-500 border border-slate-700 text-xs font-bold rounded-xl cursor-not-allowed opacity-60 flex items-center"
                                                        title="هەتا ژمارەی دەفتەر وەرنەگرێت، چاپی دەفتەر ئەکتیڤ نابێت">
                                                    <i class="fa-solid fa-ban ml-1.5 text-rose-400"></i>
                                                    چاپی دەفتەر (ناچالاکە)
                                                </button>

                                                <!-- Primary Button to enter booklet number -->
                                                <button type="button" @click="showBookletModal = true"
                                                        class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-black rounded-xl shadow-lg shadow-amber-600/30 transition flex items-center animate-pulse">
                                                    <i class="fa-solid fa-book-bookmark ml-1.5"></i>
                                                    تۆمارکردنی ژمارەی دەفتەر
                                                </button>
                                            @endif
                                        @endif

                                        <!-- Disabled state after print command clicked -->
                                        <template x-if="hasPrinted">
                                            <span class="px-3 py-1.5 bg-slate-700 text-slate-400 border border-slate-600 text-xs font-bold rounded-xl cursor-not-allowed opacity-60 flex items-center">
                                                <i class="fa-solid fa-check ml-1.5 text-emerald-400"></i> چاپکرا (ئامادەی گواستنەوە)
                                            </span>
                                        </template>

                                        @if(!$requiresBooklet || !empty($tx->booklet_number))
                                            <!-- Action: Complete Print / Move to Printed List -->
                                            <form action="{{ route('transactions.mark_printed', $tx->id) }}" method="POST" class="inline" onsubmit="return confirm('دڵنیایت لە ئەنجامدانی چاپ و گواستنەوەی ئەم مامەڵەیە بۆ بەشی چاپکراوەکان؟')">
                                                @csrf
                                                <button type="submit" 
                                                        :class="hasPrinted ? 'bg-emerald-600 hover:bg-emerald-500 ring-2 ring-emerald-400 animate-pulse' : 'bg-emerald-700/80 hover:bg-emerald-600'"
                                                        class="px-4 py-2 text-white text-xs font-black rounded-xl shadow-md transition flex items-center">
                                                    <i class="fa-solid fa-check-double ml-1.5 text-emerald-200"></i>
                                                    تەواوکردنی چاپ و گواستنەوە
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" disabled
                                                    class="px-3 py-2 bg-slate-800 text-slate-500 border border-slate-700 text-xs font-bold rounded-xl cursor-not-allowed opacity-50 flex items-center"
                                                    title="سەرەتا دەبێت ژمارەی دەفتەر تۆمار بکرێت">
                                                <i class="fa-solid fa-lock ml-1.5 text-slate-400"></i>
                                                چاوەڕێی دەفتەرە
                                            </button>
                                        @endif
                                    @else
                                        <!-- PRINTED ARCHIVE TAB ACTIONS (تەواوکراوەکان - چاپی دووبارە) -->
                                        <span class="px-3 py-1.5 bg-emerald-950 text-emerald-300 border border-emerald-600/40 text-xs font-bold rounded-xl flex items-center" title="چاپکراوە لە {{ $tx->booklet_completed_at?->format('Y-m-d H:i') }}">
                                            <i class="fa-solid fa-check-circle ml-1.5 text-emerald-400"></i> چاپکراوە (لە ئەرشیفە)
                                        </span>

                                        <!-- Reprint Buttons (چاپی دووبارە) -->
                                        @if($isCancellation)
                                            <a href="{{ route('transactions.print_cancellation', $tx->id) }}" target="_blank"
                                               class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-extrabold rounded-xl shadow-md transition flex items-center"
                                               title="چاپی دووبارەی نوسراوی پووچەڵکردنەوە">
                                                <i class="fa-solid fa-rotate-right ml-1.5 text-amber-200"></i>
                                                چاپی دووبارەی پووچەڵکردنەوە
                                            </a>
                                        @elseif($isInspection)
                                            <a href="{{ route('transactions.print_inspection_letter', $tx->id) }}" target="_blank"
                                               class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-extrabold rounded-xl shadow-md transition flex items-center"
                                               title="چاپی دووبارەی نوسراوی پشکنین">
                                                <i class="fa-solid fa-rotate-right ml-1.5 text-amber-200"></i>
                                                چاپی دووبارەی پشکنین
                                            </a>
                                        @else
                                            @if(!empty($tx->booklet_number))
                                                <a href="{{ route('transactions.print_booklet', $tx->id) }}" target="_blank"
                                                   class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-extrabold rounded-xl shadow-md transition flex items-center"
                                                   title="چاپی دووبارەی دەفتەر ({{ $tx->booklet_number }})">
                                                    <i class="fa-solid fa-rotate-right ml-1.5 text-amber-200"></i>
                                                    چاپی دووبارەی دەفتەر ({{ $tx->booklet_number }})
                                                </a>
                                            @else
                                                <button type="button" disabled
                                                        class="px-3.5 py-1.5 bg-slate-800 text-slate-500 border border-slate-700 text-xs font-bold rounded-xl cursor-not-allowed opacity-60 flex items-center"
                                                        title="بێ ژمارەی دەفتەر">
                                                    <i class="fa-solid fa-ban ml-1.5 text-rose-400"></i>
                                                    بێ ژمارەی دەفتەر
                                                </button>
                                            @endif
                                        @endif
                                    @endif

                                    <!-- Modal to Enter Booklet Number for this transaction -->
                                    <div x-show="showBookletModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                                        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl text-right" @click.away="showBookletModal = false">
                                            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                                                <h3 class="text-base font-bold text-white flex items-center">
                                                    <i class="fa-solid fa-book-bookmark ml-2 text-indigo-400"></i>
                                                    تۆمارکردنی ژمارەی سەر دەفتەر
                                                </h3>
                                                <button type="button" @click="showBookletModal = false" class="text-slate-400 hover:text-white">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </div>
                                            <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700 text-xs space-y-1">
                                                <p class="text-slate-200 font-bold">هاووڵاتی: <span class="text-amber-300">{{ $tx->visitor_name }}</span></p>
                                                <p class="text-slate-300 font-mono">تابلۆ: {{ $tx->plate_number }} | شاسی: {{ $tx->chassis_number }}</p>
                                            </div>
                                            <form action="{{ route('transactions.complete_booklet', $tx->id) }}" method="POST" class="space-y-4">
                                                @csrf
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-200 mb-1.5">ژمارەی سەر دەفتەری فیعلی بنووسە *</label>
                                                    <input type="text" name="booklet_number" value="{{ $tx->booklet_number }}" required placeholder="مثلاً: DF-554433 یان 12345" dir="ltr" autofocus
                                                           class="w-full bg-slate-800 border border-slate-600 focus:border-indigo-500 rounded-xl px-3.5 py-2.5 text-sm text-white font-mono focus:outline-none">
                                                    <p class="text-[11px] text-slate-400 mt-1">
                                                        تێبینی: بە داخڵکردنی ئەم ژمارەیە، چاپی دەفتەر لە سیستەمدا ئەکتیڤ دەبێت.
                                                    </p>
                                                </div>
                                                <div class="flex justify-end space-x-2 space-x-reverse pt-2">
                                                    <button type="button" @click="showBookletModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 hover:bg-slate-700 text-xs font-bold rounded-xl">پاشگەزبوونەوە</button>
                                                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg">تۆمارکردن و ئەکتیڤکردنی چاپ</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="max-w-md mx-auto space-y-3 bg-slate-900/60 p-6 rounded-2xl border border-slate-700/60">
                                    <i class="fa-solid fa-print text-4xl text-slate-500"></i>
                                    @if($tab === 'printed')
                                        <p class="text-base font-bold text-slate-200">هیچ مامەڵەیەکی چاپکراو لەم بەشەدا نەدۆزرایەوە.</p>
                                        <p class="text-xs text-slate-400">مامەڵە نوێیەکان لە بەشی <strong>(چاوەڕوانی چاپ)</strong> دا هەن، دوای پەسەندکردنی چاپەکە دەگوازرێنەوە بۆ ئێرە.</p>
                                        <a href="{{ route('transactions.official_prints', ['tab' => 'pending', 'date' => 'all']) }}" class="inline-block mt-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white font-extrabold text-xs rounded-xl shadow-lg transition">
                                            چوون بۆ بەشی چاوەڕوانی چاپ ({{ $pendingCountTotal }} مامەڵە)
                                        </a>
                                    @else
                                        <p class="text-base font-bold text-slate-200">هیچ مامەڵەیەکی پارەدراو بۆ چاوەڕوانی چاپ نەدۆزرایەوە.</p>
                                        <p class="text-xs text-slate-400">تکایە دڵنیابەرەوە مامەڵەکان قۆناغی پارەدانیان بڕیبێت، یان دەکرێت لە فلتەری سەرەوە بەرواری تر هەڵبژێریت.</p>
                                        <a href="{{ route('transactions.official_prints', ['tab' => 'pending', 'date' => 'all']) }}" class="inline-block mt-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs rounded-xl shadow-lg transition">
                                            پیشاندانی هەموو ڕێکەوتەکان
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-700 bg-slate-900/50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
