@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<div class="max-w-5xl mx-auto space-y-6" x-data="{ 
    showPayModal: false, 
    showBookletModal: false, 
    showCancelModal: false, 
    showReturnModal: false, 
    showRevertModal: false,
    showAuditModal: false,
    confirmAuditAmount: '',
    expectedAuditAmount: '{{ (int)$transaction->total_pay }}'
}">
    
    <!-- Top Action Banner -->
    <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="space-y-2">
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="bg-white px-2 py-1 rounded-xl shadow-md border border-slate-700">
                    <svg id="showBarcodeSvg" class="max-h-12"></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $transaction->visitor_name }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        جۆری مامەڵە: <strong class="text-slate-200">{{ $transaction->transactionType->name_kurdish ?? '' }}</strong> | 
                        ژمارەی تابلۆ: <strong class="text-emerald-400 font-mono">{{ $transaction->plate_number }}</strong>
                    </p>
                </div>
                @if($transaction->is_returned)
                    <span class="px-3 py-1 bg-rose-500/20 text-rose-300 text-xs font-extrabold rounded-full border border-rose-500/40 animate-pulse">
                        <i class="fa-solid fa-triangle-exclamation ml-1"></i> گەڕێنراوەتەوە - هەڵە هەیە
                    </span>
                @elseif($transaction->is_cancelled)
                    <span class="px-3 py-1 bg-rose-500/20 text-rose-400 text-xs font-bold rounded-full border border-rose-500/30">پووچەڵکراوە</span>
                @elseif($transaction->is_submitted)
                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 text-xs font-bold rounded-full border border-emerald-500/30">سەبمیت و ئەرشیفکراو (Step 6)</span>
                @endif
            </div>
        </div>

        <!-- Print & Edit Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Edit button (Data Entry only if not inspected or if returned; Admin always) -->
            @if((auth()->user()->isAdmin() || auth()->user()->role === 'data_entry') && $transaction->canBeEditedByDataEntry() && !$transaction->is_cancelled)
                <a href="{{ route('transactions.edit', $transaction->id) }}" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center">
                    <i class="fa-solid fa-pen-to-square ml-1.5"></i> دەستکاریکردن / ڕاستکردنەوە
                </a>
            @endif

            <!-- Admin Step Reversion (Reset) Button -->
            @if(auth()->check() && auth()->user()->isAdmin())
                <button type="button" @click="showRevertModal = true" class="px-3 py-2 bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/50 text-amber-300 text-xs font-bold rounded-xl transition flex items-center" title="ڕێکخستنەوەی قۆناغ لەلایەن ئەدمین">
                    <i class="fa-solid fa-arrow-rotate-left ml-1.5"></i> ڕێکخستنەوەی قۆناغ (Admin Reset)
                </button>
            @endif

            <!-- Print Data Entry Routing Slip (Data Entry and Admin ONLY) -->
            @if(auth()->user()->isAdmin() || auth()->user()->role === 'data_entry')
                <a href="{{ route('transactions.print_data_entry', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-blue-700 hover:bg-blue-600 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center">
                    <i class="fa-solid fa-file-invoice ml-1.5 text-sky-300"></i> چاپی فیشەی داتائەنتەری (بەدواداچوون)
                </a>
            @endif

            <!-- Estimator Inspection & Estimation Receipt Button (Inspector and Admin ONLY) -->
            @if(auth()->user()->isAdmin() || auth()->user()->role === 'inspector')
                @if($transaction->is_inspected && (!$transaction->is_audited || auth()->user()->isAdmin()))
                    <a href="{{ route('transactions.print_receipt', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-sky-600/30 transition flex items-center">
                        <i class="fa-solid fa-print ml-1.5 text-sky-200"></i> چاپی پسولەی کەشف و خەمڵاندن
                    </a>
                @elseif(!$transaction->is_inspected)
                    <button type="button" disabled class="px-3.5 py-2 bg-slate-800 text-slate-500 border border-slate-700 text-xs font-bold rounded-xl cursor-not-allowed opacity-60 flex items-center" title="پاش پەسەندکردنی کەشف و تەخمین ئەکتێف دەبێت">
                        <i class="fa-solid fa-lock ml-1.5 text-amber-400"></i> چاپی پسولەی کەشف (چاوەڕێی کەشفە)
                    </button>
                @elseif($transaction->is_audited)
                    <button type="button" disabled class="px-3.5 py-2 bg-slate-800 text-slate-500 border border-slate-700 text-xs font-bold rounded-xl cursor-not-allowed opacity-60 flex items-center" title="وردبینی پەسەندی کردووە - چاپی تەخمین قوفڵکراوە">
                        <i class="fa-solid fa-lock ml-1.5 text-rose-400"></i> چاپی پسولەی کەشف (قوفڵکراوە)
                    </button>
                @endif
            @endif

            <!-- Cashier Payment Receipt Button (Cashier and Admin ONLY) -->
            @if(!$transaction->is_cancelled && $transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->role === 'cashier'))
                <a href="{{ route('transactions.print_payment_receipt', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center">
                    <i class="fa-solid fa-receipt ml-1.5 text-emerald-200"></i> چاپی پسوولەی پارەدان (۳۷/أ)
                </a>
            @endif

            <!-- Booklet & Letters Printing (Booklet section and Admin ONLY) -->
            @if(!$transaction->is_cancelled && $transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet'))
                @if($transaction->isCancellation())
                    <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-rose-700 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center" title="چاپی نوسراوی پووچەڵکردنەوە">
                        <i class="fa-solid fa-file-circle-xmark ml-1.5"></i> نوسراوی پووچەڵکردنەوە
                    </a>
                @elseif($transaction->isInspection())
                    <a href="{{ route('transactions.print_inspection_letter', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center" title="چاپی نوسراوی پشکنین">
                        <i class="fa-solid fa-file-circle-check ml-1.5"></i> نوسراوی پشکنین
                    </a>
                @else
                    <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center" title="چاپی نوسراوی دانانی نیشانەی ڕەفتارنەکردن">
                        <i class="fa-solid fa-file-circle-exclamation ml-1.5"></i> ڕەفتارنەکردن
                    </a>
                @endif

                @if($transaction->requiresBooklet())
                    @if(!empty($transaction->booklet_number))
                        <a href="{{ route('transactions.print_booklet', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center" title="چاپی دەفتەری گەشتیاری ({{ $transaction->booklet_number }})">
                            <i class="fa-solid fa-passport ml-1.5"></i> چاپی دەفتەر ({{ $transaction->booklet_number }})
                        </a>
                    @else
                        <button type="button" disabled class="px-3 py-2 bg-slate-800 text-slate-500 border border-slate-700 text-xs font-bold rounded-xl cursor-not-allowed opacity-60 flex items-center" title="هەتا ژمارەی دەفتەر وەرنەگرێت، چاپی دەفتەر ئەکتیڤ نابێت">
                            <i class="fa-solid fa-ban ml-1.5 text-rose-400"></i> چاپی دەفتەر (ناچالاکە - بێ ژمارە)
                        </button>
                    @endif
                @endif
            @endif

            <!-- Follow-up Transaction Dropdown (Data Entry and Admin ONLY) -->
            @if(auth()->user()->isAdmin() || auth()->user()->role === 'data_entry')
                <div class="relative" x-data="{ openFollowUp: false }">
                    <button type="button" @click="openFollowUp = !openFollowUp" class="px-3.5 py-2 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center gap-1.5">
                        <i class="fa-solid fa-arrows-split-up-and-left"></i>
                        <span>مامەڵەی نوێ لەسەر ئەم ئۆتۆمبێلە</span>
                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>
                    <div x-show="openFollowUp" @click.away="openFollowUp = false" x-cloak class="absolute left-0 mt-2 w-56 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl p-1.5 z-50 space-y-1 text-xs">
                        <a href="{{ route('transactions.create', ['from_transaction' => $transaction->id, 'target_type' => 2]) }}" class="flex items-center gap-2 p-2 rounded-lg text-slate-200 hover:bg-sky-600 hover:text-white transition">
                            <i class="fa-solid fa-rotate-right text-sky-400"></i>
                            <span>تازەکردنەوەی دەفتەر</span>
                        </a>
                        <a href="{{ route('transactions.create', ['from_transaction' => $transaction->id, 'target_type' => 5]) }}" class="flex items-center gap-2 p-2 rounded-lg text-rose-300 hover:bg-rose-600 hover:text-white transition">
                            <i class="fa-solid fa-ban text-rose-400"></i>
                            <span>پووچەڵکردنەوەی دەفتەر</span>
                        </a>
                        <a href="{{ route('transactions.create', ['from_transaction' => $transaction->id, 'target_type' => 3]) }}" class="flex items-center gap-2 p-2 rounded-lg text-slate-200 hover:bg-indigo-600 hover:text-white transition">
                            <i class="fa-solid fa-user-pen text-indigo-400"></i>
                            <span>ناوگۆڕین</span>
                        </a>
                        <a href="{{ route('transactions.create', ['from_transaction' => $transaction->id, 'target_type' => 4]) }}" class="flex items-center gap-2 p-2 rounded-lg text-slate-200 hover:bg-indigo-600 hover:text-white transition">
                            <i class="fa-solid fa-book text-amber-400"></i>
                            <span>دەفتەرگۆڕین</span>
                        </a>
                        <a href="{{ route('transactions.create', ['from_transaction' => $transaction->id, 'target_type' => 7]) }}" class="flex items-center gap-2 p-2 rounded-lg text-slate-200 hover:bg-indigo-600 hover:text-white transition">
                            <i class="fa-solid fa-layer-group text-purple-400"></i>
                            <span>ناو و دەفتەرگۆڕین</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Admin Soft Delete -->
            @if(auth()->check() && auth()->user()->isAdmin())
                <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" onsubmit="return confirm('دڵنیایت لە سڕینەوەی کاتی (Soft Delete) ی ئەم مامەڵەیە؟')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" title="سڕینەوەی کاتی" class="px-3 py-2 bg-rose-950 hover:bg-rose-900 border border-rose-700 text-rose-300 text-xs font-bold rounded-xl transition">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Dual Record Print Banner (Booklet + Pledge) -->
    @php
        $isPledge = str_contains($transaction->transactionType->name_kurdish ?? '', 'بەڵێننامە') || in_array($transaction->transaction_type_id, [4, 6]);
        $linkedPledgeTx = ($transaction->transaction_type_id == 1) 
            ? ($transaction->relatedTransactions->firstWhere(fn($t) => str_contains($t->transactionType->name_kurdish ?? '', 'بەڵێننامە') || in_array($t->transaction_type_id, [4, 6])) ?? $transaction->relatedTransactions->first())
            : ($isPledge ? $transaction->parentTransaction : null);
        $bookletTx = ($transaction->transaction_type_id == 1) ? $transaction : ($transaction->parentTransaction ?? $transaction);
        $pledgeTx = $isPledge ? $transaction : $linkedPledgeTx;
    @endphp

    @if($bookletTx && $pledgeTx)
        <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 border-2 border-amber-500/60 rounded-2xl p-4 shadow-2xl space-y-3">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <span class="px-3 py-0.5 bg-amber-500 text-slate-950 text-xs font-black rounded-full">دوو تۆماری بەستراوە (Dual Records)</span>
                        <h4 class="text-sm font-extrabold text-white">دەرهێنانی دەفتەر (بارکۆد ١) + بەڵێننامە (بارکۆد ٢)</h4>
                    </div>
                    <p class="text-xs text-slate-300 font-bold">
                        تۆماری دەفتەر: <strong class="font-mono text-sky-300 text-sm">{{ $bookletTx->barcode }}</strong> | 
                        تۆماری بەڵێننامە: <strong class="font-mono text-amber-300 text-sm">{{ $pledgeTx->barcode }}</strong> (ڕسوومات: <span class="text-amber-400 font-mono">٦,٠٠٠ د.ع</span>)
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('transactions.print_data_entry', $bookletTx->id) }}" target="_blank" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center">
                        <i class="fa-solid fa-file-invoice ml-1.5 text-sky-200"></i> ۱. چاپی فیشەی دەفتەر ({{ $bookletTx->barcode }})
                    </a>
                    <a href="{{ route('transactions.print_pledge', $pledgeTx->id) }}" target="_blank" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center">
                        <i class="fa-solid fa-file-contract ml-1.5 text-amber-200"></i> ۲. چاپی فیشەی بەڵێننامە ({{ $pledgeTx->barcode }})
                    </a>
                    <button type="button" onclick="window.open('{{ route('transactions.print_data_entry', $bookletTx->id) }}'); setTimeout(() => window.open('{{ route('transactions.print_pledge', $pledgeTx->id) }}'), 500);" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-xl shadow-emerald-600/30 transition flex items-center">
                        <i class="fa-solid fa-print ml-1.5"></i> 🖨️ چاپکردنی هەردوو فیشەکە (دەفتەر + بەڵێننامە)
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Parent / Related Transactions Relationship Card -->
    @if($transaction->parentTransaction || $transaction->relatedTransactions->count() > 0)
        <div class="bg-slate-800/90 border border-sky-500/40 rounded-2xl p-4 shadow-xl space-y-3">
            <h4 class="text-xs font-bold text-sky-400 flex items-center border-b border-slate-700 pb-2">
                <i class="fa-solid fa-network-wired ml-1.5"></i> مامەڵە بەستراوەکان بە هەمان هاووڵاتی (Related Transactions):
            </h4>
            <div class="flex flex-wrap gap-3">
                @if($transaction->parentTransaction)
                    <a href="{{ route('transactions.show', $transaction->parentTransaction->id) }}" class="p-2.5 bg-slate-900 border border-sky-500/60 rounded-xl flex items-center space-x-2 space-x-reverse text-xs hover:border-sky-400 transition">
                        <span class="px-2 py-0.5 bg-sky-500/20 text-sky-300 rounded font-mono font-bold">{{ $transaction->parentTransaction->barcode }}</span>
                        <span class="font-bold text-slate-100">مامەڵەی بنەڕەتی: {{ $transaction->parentTransaction->transactionType->name_kurdish ?? '' }}</span>
                    </a>
                @endif

                @foreach($transaction->relatedTransactions as $rel)
                    <a href="{{ route('transactions.show', $rel->id) }}" class="p-2.5 bg-slate-900 border border-emerald-500/60 rounded-xl flex items-center space-x-2 space-x-reverse text-xs hover:border-emerald-400 transition">
                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded font-mono font-bold">{{ $rel->barcode }}</span>
                        <span class="font-bold text-slate-100">مامەڵەی بەستراوە: {{ $rel->transactionType->name_kurdish ?? '' }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Alert Banner for Returned Transactions -->
    @if($transaction->is_returned)
        <div class="bg-rose-950/80 border-2 border-rose-500/80 rounded-2xl p-4 shadow-2xl flex items-start space-x-3 space-x-reverse animate-pulse">
            <i class="fa-solid fa-triangle-exclamation text-rose-400 text-2xl mt-0.5"></i>
            <div class="space-y-1">
                <h4 class="text-sm font-extrabold text-rose-200">ئەم مامەڵەیە لەلایەن وردبینەوە گەڕێنراوەتەوە (Returned Status):</h4>
                <p class="text-xs text-rose-300 leading-relaxed font-bold">
                    هۆکاری گەڕاندنەوە: <span class="bg-rose-900/90 text-white px-2 py-0.5 rounded border border-rose-700 font-mono">{{ $transaction->return_reason }}</span>
                </p>
                <p class="text-[11px] text-rose-400">
                    * کات و بەکارهێنەر: {{ $transaction->returnedBy->name ?? 'وردبین' }} ({{ $transaction->updated_at->diffForHumans() }}) - بەشی داتائەنتەری دەتوانێت گۆڕانکاری بکات و بێنێرێتەوە.
                </p>
            </div>
        </div>
    @endif

    <!-- Lock Warning Banner for Data Entry -->
    @if(!$transaction->canBeEditedByDataEntry() && !$transaction->is_cancelled)
        <div class="bg-slate-800/90 border border-amber-500/40 rounded-2xl p-4 shadow-xl flex items-center justify-between">
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <i class="fa-solid fa-lock text-lg"></i>
                </div>
                <div class="text-xs">
                    <h4 class="font-bold text-slate-100">دەستکاریکردنی ئەم مامەڵەیە قوفڵ کراوە!</h4>
                    <p class="text-slate-400">
                        چونکە قۆناغی 
                        <strong class="text-amber-300 font-bold">
                            @if($transaction->is_submitted) سەبمیت و ئەرشیف (Step 6)
                            @elseif($transaction->is_booklet_completed) چاپکردنی دەفتەر (Step 5)
                            @elseif($transaction->is_paid) وەسڵ و پارەدان (Step 4)
                            @elseif($transaction->is_audited) وردبینی (Step 3)
                            @else کەشف و تەخمین (Step 2)
                            @endif
                        </strong>
                        ی بۆ ئەنجامدراوە. تەنها ئەدمین دەتوانێت قۆناغەکەی ڕێکبخاتەوە (Reset) تا ڕێگە بە دەستکاری بدات.
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-bold rounded-lg border border-amber-500/30">
                لە قۆناغی: 
                @if($transaction->is_submitted) سەبمیت کراو
                @elseif($transaction->is_booklet_completed) دەفتەری تەواوکراو
                @elseif($transaction->is_paid) پارەدراو
                @elseif($transaction->is_audited) وردبینی‌کراو
                @else کەشف‌کراو
                @endif
            </span>
        </div>
    @endif

    <!-- Current Stage Banner & Alert for Auditor -->
    @if(!$transaction->is_cancelled && !$transaction->is_submitted)
        <div class="bg-slate-800/90 border border-emerald-500/50 rounded-2xl p-4 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 font-bold">
                    <i class="fa-solid fa-layer-group text-lg"></i>
                </div>
                <div>
                    <h4 class="text-xs text-slate-400 font-bold">دۆخی ئێستای مامەڵە بە بارکۆدی: <span class="font-mono text-emerald-400 text-sm">{{ $transaction->barcode }}</span></h4>
                    <p class="text-sm font-extrabold text-white mt-0.5 flex items-center">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping ml-2"></span>
                        قۆناغی ئێستا: <span class="text-emerald-300 mr-1">{{ $transaction->getCurrentStageName() }}</span>
                    </p>
                </div>
            </div>
            <div class="text-xs text-slate-300 bg-slate-900/80 px-3.5 py-2 rounded-xl border border-slate-700">
                <span class="text-slate-400">جۆری قۆناغبەندی:</span> 
                @if($transaction->requiresBooklet())
                    <strong class="text-sky-300 font-bold">پێویستی بە چاپی دەفتەر هەیە (٦ ستێپ)</strong>
                @else
                    <strong class="text-amber-300 font-bold">چاپی دەفتەر ناکەن (٥ ستێپ)</strong>
                @endif
            </div>
        </div>
    @endif

    <!-- Specific Warning Box for Auditor if Estimation is Not Done Yet -->
    @if(!$transaction->is_inspected && !$transaction->is_cancelled)
        <div class="bg-amber-950/80 border-2 border-amber-500/80 rounded-2xl p-4 shadow-2xl flex items-start space-x-3 space-x-reverse">
            <i class="fa-solid fa-triangle-exclamation text-amber-400 text-2xl mt-0.5"></i>
            <div class="space-y-1">
                <h4 class="text-sm font-extrabold text-amber-200">
                    کاری تەخمین ئەنجام بدە دواتر وەرە وردبینی
                </h4>
                <p class="text-xs text-amber-300 leading-relaxed font-bold">
                    ئەم مامەڵەیە تائێستا کەشف و تەخمینی بۆ نەکراوە! کارمەندی وردبین ناتوانێت ستێپی تەخمین تێپەڕێنێت بۆ بەشی وردبینی تا کاتێک کارمەندی تەخمین کەشفەکە پەسەند نەکات.
                </p>
            </div>
        </div>
    @endif

    <!-- Workflow Progress Stepper Pipeline -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-4">
        <h3 class="text-sm font-bold text-slate-300 flex items-center justify-between border-b border-slate-700 pb-3">
            <span class="flex items-center">
                <i class="fa-solid fa-diagram-project ml-2 text-sky-400"></i> قۆناغ و ستێپەکانی کارکردنی مامەڵە (Workflow Stepper)
            </span>
            <span class="text-xs text-sky-400 font-mono">بارکۆد: {{ $transaction->barcode }}</span>
        </h3>

        @php
            $requiresBooklet = $transaction->requiresBooklet();
        @endphp

        <div class="grid grid-cols-1 {{ $requiresBooklet ? 'sm:grid-cols-6' : 'sm:grid-cols-5' }} gap-3 text-center">
            <!-- Step 1: Data Entry -->
            <div class="p-3 rounded-xl border bg-emerald-500/10 border-emerald-500/40 text-emerald-300">
                <i class="fa-solid fa-check-circle text-lg mb-1 block text-emerald-400"></i>
                <span class="text-xs font-bold block">١. داتا ئەنتەری</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $transaction->user_input }}</span>
            </div>

            <!-- Step 2: Inspection (Estimation / تەخمین) -->
            <div class="p-3 rounded-xl border {{ $transaction->is_inspected ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : 'bg-slate-900/60 border-slate-700/60 text-slate-500' }}">
                <i class="fa-solid {{ $transaction->is_inspected ? 'fa-check-circle text-emerald-400' : 'fa-circle-dot text-slate-500' }} text-lg mb-1 block"></i>
                <span class="text-xs font-bold block">٢. کەشف و تەخمین</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $transaction->inspected_by ?? 'نەکراوە' }}</span>
            </div>

            <!-- Step 3: Audit (وردبینی) -->
            <div class="p-3 rounded-xl border {{ $transaction->is_audited ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : 'bg-slate-900/60 border-slate-700/60 text-slate-500' }}">
                <i class="fa-solid {{ $transaction->is_audited ? 'fa-check-circle text-emerald-400' : 'fa-circle-dot text-slate-500' }} text-lg mb-1 block"></i>
                <span class="text-xs font-bold block">٣. وردبینی</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $transaction->audited_by ?? 'نەکراوە' }}</span>
            </div>

            <!-- Step 4: Payment (وەسڵ و پارەدان) -->
            <div class="p-3 rounded-xl border {{ $transaction->is_paid ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : 'bg-slate-900/60 border-slate-700/60 text-slate-500' }}">
                <i class="fa-solid {{ $transaction->is_paid ? 'fa-check-circle text-emerald-400' : 'fa-circle-dot text-slate-500' }} text-lg mb-1 block"></i>
                <span class="text-xs font-bold block">٤. وەسڵ و پارەدان</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $transaction->receipt_37a_number ? 'وەسڵ: ' . $transaction->receipt_37a_number : 'نەدراوە' }}</span>
            </div>

            <!-- Step 5: Booklet Complete (ONLY rendered if $requiresBooklet is true) -->
            @if($requiresBooklet)
                <div class="p-3 rounded-xl border {{ $transaction->is_booklet_completed ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : 'bg-slate-900/60 border-slate-700/60 text-slate-500' }}">
                    <i class="fa-solid {{ $transaction->is_booklet_completed ? 'fa-check-circle text-emerald-400' : 'fa-circle-dot text-slate-500' }} text-lg mb-1 block"></i>
                    <span class="text-xs font-bold block">٥. چاپکردنی دەفتەر</span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ $transaction->booklet_number ? 'دەفتەر: ' . $transaction->booklet_number : 'تەواونەکراوە' }}</span>
                </div>
            @endif

            <!-- Step 6 (or Step 5 if no booklet): Submit & Archive Stage -->
            <div class="p-3 rounded-xl border {{ $transaction->is_submitted ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : 'bg-slate-900/60 border-slate-700/60 text-slate-500' }}">
                <i class="fa-solid {{ $transaction->is_submitted ? 'fa-check-circle text-emerald-400' : 'fa-circle-dot text-slate-500' }} text-lg mb-1 block"></i>
                <span class="text-xs font-bold block">{{ $requiresBooklet ? '٦. سەبمیت و ئەرشیف' : '٥. سەبمیت و ئەرشیف' }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $transaction->submitted_by ?? 'نەنێردراوە' }}</span>
            </div>
        </div>

        <!-- Interactive Workflow Action Controls per Employee Role -->
        @if(!$transaction->is_cancelled)
            <div class="pt-4 border-t border-slate-700 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">

                    <!-- Step 2: Inspection (Inspector / Admin) -->
                    @if(!$transaction->is_inspected)
                        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.inspect'))
                            <form action="{{ route('transactions.inspect', $transaction->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-600/20 transition flex items-center">
                                    <i class="fa-solid fa-magnifying-glass ml-1.5"></i> ستێپی ٢: پەسەندکردنی کەشف و تەخمین
                                </button>
                            </form>
                            <button type="button" @click="showReturnModal = true" class="px-4 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/20 transition flex items-center">
                                <i class="fa-solid fa-rotate-left ml-1.5"></i> گەڕاندنەوە بۆ داتائەنتەری (هەڵەی زانیاری)
                            </button>
                        @else
                            <span class="px-3 py-1.5 bg-orange-500/10 text-orange-300 border border-orange-500/30 rounded-xl text-xs font-bold flex items-center">
                                <i class="fa-solid fa-clock ml-1.5 text-orange-400"></i> چاوەڕێی کەشف و تەخمینە (تەنها ئەندازیاری تەخمین دەتوانێت ئەم قۆناغە تێپەڕێنێت)
                            </span>
                        @endif
                    @elseif(!$transaction->is_audited && (auth()->user()->isAdmin() || auth()->user()->role === 'inspector' || auth()->user()->hasPermission('transactions.inspect')))
                        <!-- Point 12: Inspector can revert inspection if not audited yet -->
                        <form action="{{ route('transactions.revert_inspection', $transaction->id) }}" method="POST" class="inline" onsubmit="return confirm('ئایا دڵنیایت لە هەڵوەشاندنەوەی قۆناغی کەشف و تەخمین و گەڕاندنەوەی مامەڵەکە بۆ داتائەنتەری؟');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-amber-600/20 transition flex items-center">
                                <i class="fa-solid fa-rotate-left ml-1.5"></i> گەڕاندنەوەی هەڵە بۆ داتائەنتەری (هەڵوەشاندنەوەی تەخمین)
                            </button>
                        </form>
                    @endif

                    <!-- Step 3: Audit (Auditor / Admin) -->
                    @if(!$transaction->is_audited)
                        @if(!$transaction->is_inspected)
                            <!-- Estimation NOT done: Show disabled Audit button for auditor -->
                            <button type="button" disabled class="px-4 py-2 bg-slate-800 text-slate-500 border border-slate-700 text-xs font-bold rounded-xl cursor-not-allowed opacity-60 flex items-center" title="کاری تەخمین ئەنجام بدە دواتر وەرە وردبینی">
                                <i class="fa-solid fa-ban ml-1.5 text-rose-400"></i> ستێپی ٣: پەسەندکردنی وردبینی (لەکارخراوە - Disabled)
                            </button>
                        @elseif(auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.audit'))
                            <button type="button" @click="showAuditModal = true" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-purple-600/20 transition flex items-center">
                                <i class="fa-solid fa-clipboard-check ml-1.5"></i> ستێپی ٣: پەسەندکردنی وردبینی
                            </button>

                            <button type="button" @click="showReturnModal = true" class="px-4 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/20 transition flex items-center">
                                <i class="fa-solid fa-rotate-left ml-1.5"></i> گەڕاندنەوە بۆ تەخمین (هەڵە هەیە)
                            </button>
                        @else
                            <span class="px-3 py-1.5 bg-purple-500/10 text-purple-300 border border-purple-500/30 rounded-xl text-xs font-bold flex items-center">
                                <i class="fa-solid fa-clock ml-1.5 text-purple-400"></i> چاوەڕێی پەسەندکردنی وردبینییە (تەنها کارمەندی وردبین دەتوانێت پەسەندی بکات)
                            </span>
                        @endif
                    @endif

                    <!-- Step 4: Payment (Cashier / Admin) -->
                    @if($transaction->is_audited && !$transaction->is_paid)
                        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.pay'))
                            <button @click="showPayModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center">
                                <i class="fa-solid fa-receipt ml-1.5"></i> ستێپی ٤: تۆمارکردنی وەسڵی ۳۷/أ و پارەدان
                            </button>
                        @else
                            <span class="px-3 py-1.5 bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 rounded-xl text-xs font-bold flex items-center">
                                <i class="fa-solid fa-hand-holding-dollar ml-1.5 text-emerald-400"></i> چاوەڕێی وەرگرتنی پارەیە (تەنها ژمێریار / وەسڵبڕ دەتوانێت پارە وەربگرێت)
                            </span>
                        @endif
                    @endif

                    <!-- Step 5: Booklet Complete (Booklet / Admin) - ONLY if requiresBooklet() -->
                    @if($requiresBooklet && $transaction->is_paid)
                        @if(!$transaction->is_booklet_completed || empty($transaction->booklet_number))
                            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.complete_booklet'))
                                <button @click="showBookletModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-600/20 transition flex items-center">
                                    <i class="fa-solid fa-book-bookmark ml-1.5"></i> ستێپی ٥: تۆمارکردنی ژمارەی دەفتەر
                                </button>
                            @else
                                <span class="px-3 py-1.5 bg-blue-500/10 text-blue-300 border border-blue-500/30 rounded-xl text-xs font-bold flex items-center">
                                    <i class="fa-solid fa-clock ml-1.5 text-blue-400"></i> چاوەڕێی ژمارەی دەفتەرە (تەنها کارمەندی بەشی دەفتەر دەتوانێت دەفتەر تۆمار بکات)
                                </span>
                            @endif
                        @else
                            <a href="{{ route('transactions.print_booklet', $transaction->id) }}" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center">
                                <i class="fa-solid fa-passport ml-1.5"></i> چاپی دەفتەر ({{ $transaction->booklet_number }})
                            </a>
                            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.complete_booklet'))
                                <button @click="showBookletModal = true" class="px-2.5 py-2 bg-slate-800 text-slate-300 hover:text-amber-300 border border-slate-700 text-xs font-bold rounded-xl transition" title="دەستکاریکردنی ژمارەی دەفتەر">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            @endif
                        @endif
                    @endif

                    <!-- Final Submit & Archive Button -->
                    @if($transaction->is_paid && !$transaction->is_submitted && (!$requiresBooklet || $transaction->is_booklet_completed))
                        <form action="{{ route('transactions.submit', $transaction->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center">
                                <i class="fa-solid fa-box-archive ml-1.5"></i> {{ $requiresBooklet ? 'ستێپی ٦: سەبمیت و ئەرشیفکردنی مامەڵە' : 'ستێپی ٥: سەبمیت و ئەرشیفکردنی مامەڵە' }}
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Cancel Transaction Button -->
                <button @click="showCancelModal = true" class="px-3 py-2 bg-rose-950/60 hover:bg-rose-900 border border-rose-600/40 text-rose-300 text-xs font-bold rounded-xl transition flex items-center">
                    <i class="fa-solid fa-ban ml-1.5"></i> پووچەڵکردنەوەی مامەڵە
                </button>
            </div>
        @endif
    </div>

    <!-- Transaction Details Grid (Visible to all users) -->
    <!-- Transaction Details Grid (Visible to all users & Auditor) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Vehicle & Drivers Card -->
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center justify-between">
                <span class="flex items-center">
                    <i class="fa-solid fa-id-card ml-2"></i> زانیارییەکانی شۆفێر و هاووڵاتی (وردبینی زانیاری)
                </span>
                @if($transaction->phone_number)
                    <a href="tel:{{ $transaction->phone_number }}" class="text-xs bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 px-2.5 py-1 rounded-lg font-mono flex items-center gap-1 hover:bg-emerald-500/30 transition">
                        <i class="fa-solid fa-phone text-[10px]"></i> {{ $transaction->phone_number }}
                    </a>
                @endif
            </h3>

            <div class="space-y-2.5 text-sm">
                <!-- شۆفێری یەکەم بە کوردی -->
                <div class="flex justify-between items-center py-1.5 border-b border-slate-700/40">
                    <span class="text-slate-400">شۆفێری یەکەم (کوردی / عەرەبی):</span>
                    <span class="font-black text-white text-base">{{ $transaction->visitor_name }}</span>
                </div>

                <!-- شۆفێری یەکەم بە ئینگلیزی -->
                <div class="flex justify-between items-center py-1.5 border-b border-slate-700/40">
                    <span class="text-slate-400">شۆفێری یەکەم (ئینگلیزی):</span>
                    <span class="font-mono text-sky-300 font-bold" dir="ltr">{{ $transaction->visitor_name_eng ?: 'تۆمارنەکراوە' }}</span>
                </div>

                <!-- ژمارەی مۆبایل -->
                <div class="flex justify-between items-center py-1.5 border-b border-slate-700/40">
                    <span class="text-slate-400 flex items-center">
                        <i class="fa-solid fa-phone ml-1.5 text-emerald-400 text-xs"></i> ژمارەی مۆبایل:
                    </span>
                    @if($transaction->phone_number)
                        <a href="tel:{{ $transaction->phone_number }}" class="font-mono text-emerald-400 font-black hover:underline" dir="ltr">
                            {{ $transaction->phone_number }}
                        </a>
                    @else
                        <span class="text-slate-500 italic text-xs">تۆمارنەکراوە</span>
                    @endif
                </div>

                <!-- شۆفێری دووەم بە کوردی -->
                <div class="flex justify-between items-center py-1.5 border-b border-slate-700/40">
                    <span class="text-slate-400">شۆفێری دووەم (کوردی):</span>
                    <span class="font-bold text-slate-100">{{ $transaction->second_driver_name ?: 'دیارینەکراوە / نییە' }}</span>
                </div>

                <!-- شۆفێری دووەم بە ئینگلیزی -->
                <div class="flex justify-between items-center py-1.5 border-b border-slate-700/40">
                    <span class="text-slate-400">شۆفێری دووەم (ئینگلیزی):</span>
                    <span class="font-mono text-sky-300 font-bold" dir="ltr">{{ $transaction->second_driver_name_eng ?: 'دیارینەکراوە / نییە' }}</span>
                </div>

                @if($transaction->isCancellation() || $transaction->no_nusraw_puchal)
                    <div class="flex justify-between py-1 border-b border-rose-500/40 bg-rose-950/30 px-2 rounded">
                        <span class="text-rose-300 font-bold">ژمارەی نووسراوی گومرگ:</span>
                        <span class="font-mono text-white font-black">{{ $transaction->no_nusraw_puchal ?? '-' }}</span>
                    </div>
                    @if($transaction->date_nusraw_puchal)
                        <div class="flex justify-between py-1 border-b border-rose-500/40 bg-rose-950/30 px-2 rounded">
                            <span class="text-rose-300 font-bold">بەرواری نووسراوی گومرگ:</span>
                            <span class="font-mono text-white font-bold">{{ $transaction->date_nusraw_puchal->format('Y-m-d') }}</span>
                        </div>
                    @endif
                @endif
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">ژمارەی تابلۆ:</span>
                    <span class="font-mono text-emerald-400 font-bold">{{ $transaction->plate_number }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">جۆری تابلۆ:</span>
                    <span class="text-slate-200">{{ $transaction->plateType->name_kurdish ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">مارکەی ئۆتۆمبێل:</span>
                    <span class="text-slate-200 font-bold">{{ $transaction->carMake->name_kurdish ?? '-' }} <span class="font-mono text-sky-400 text-xs">({{ $transaction->carMake->name_english ?? '' }})</span></span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">ڕەنگ:</span>
                    <span class="text-slate-200 font-bold">{{ $transaction->carColor->name_kurdish ?? '-' }} <span class="font-mono text-sky-400 text-xs">({{ $transaction->carColor->name_english ?? '' }})</span></span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">ژمارەی شاسی (VIN):</span>
                    <span class="font-mono text-sky-300 font-bold uppercase">{{ $transaction->chassis_number }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">مۆدێل و بستۆن:</span>
                    <span class="font-mono text-slate-200">{{ $transaction->model_year }} ({{ $transaction->piston_count }} Piston)</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">ژمارەی ساڵانە:</span>
                    <span class="font-mono text-slate-200">{{ $transaction->salana_number ?? '-' }}</span>
                </div>
                @if(!$transaction->isCancellationOrInspection() && $transaction->start_date && $transaction->end_date)
                    <div class="flex justify-between py-1 border-b border-slate-700/40">
                        <span class="text-slate-400">بەرواری دەستپێکردن & بەسەرچوون:</span>
                        <span class="font-mono text-amber-300 font-bold text-xs">{{ $transaction->start_date->format('Y-m-d') }} ➔ {{ $transaction->end_date->format('Y-m-d') }}</span>
                    </div>
                @endif
                @if(!$transaction->isCancellationOrInspection())
                    <div class="flex justify-between py-1 text-xs">
                        <span class="text-slate-400">زیادەبار / کەموکورتی / باری گشتی:</span>
                        <span class="font-bold text-slate-200">{{ $transaction->zedabar ?? 'باشە' }} | {{ $transaction->kamukurty ?? 'باشە' }} | {{ $transaction->bary_gshty ?? 'باشە' }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Financial & Fee Breakdown Card -->
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-emerald-400 border-b border-slate-700 pb-2 flex items-center">
                <i class="fa-solid fa-receipt ml-2"></i> وردەکاری دارایی و نرخەکان
            </h3>

            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">ماوەی دەفتەر:</span>
                    <span class="font-bold text-sky-300">{{ $transaction->num_years }} ساڵ</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">بڕی پارەی ساڵەکان:</span>
                    <span class="font-mono text-slate-200">{{ number_format($transaction->pay_amount_years) }} د.ع</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">پول (Stamp):</span>
                    <span class="font-mono text-slate-200">{{ number_format($transaction->pay_stamp) }} د.ع</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">فۆڕم:</span>
                    <span class="font-mono text-slate-200">{{ number_format($transaction->pay_form) }} د.ع</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">سزای دواکەوتن:</span>
                    <span class="font-mono text-amber-400">{{ number_format($transaction->pay_fine) }} د.ع</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">کەشف و تەخمین:</span>
                    <span class="font-mono text-slate-200">{{ number_format($transaction->pay_inspection) }} د.ع</span>
                </div>
                <div class="flex justify-between py-2 font-bold text-base border-t border-slate-700">
                    <span class="text-white">کۆی تێکڕای پارەداوە:</span>
                    <span class="font-mono text-emerald-400 font-extrabold">{{ number_format($transaction->total_pay) }} د.ع</span>
                </div>
                <div class="flex justify-between py-1 text-xs">
                    <span class="text-slate-400">بارودۆخی پارەدان:</span>
                    @if($transaction->is_paid)
                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 font-bold rounded border border-emerald-500/30">پارەدراوە (وەسڵ: {{ $transaction->receipt_37a_number }})</span>
                    @else
                        <span class="px-2 py-0.5 bg-rose-500/20 text-rose-300 font-bold rounded border border-rose-500/30">نەدراوە (Unpaid)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: Payment Modal (Cashier) -->
    <div x-show="showPayModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-bold text-white flex items-center">
                <i class="fa-solid fa-receipt ml-2 text-emerald-400"></i> تۆمارکردنی وەسڵی ۳۷/أ و پارەدان
            </h3>
            <form action="{{ route('transactions.pay', $transaction->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">ژمارەی پسوولەی ۳۷/أ *</label>
                    <input type="text" name="receipt_37a_number" required placeholder="مثلاً: REC-998877" dir="ltr"
                           class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-emerald-500 focus:outline-none">
                </div>
                <div class="p-3 bg-emerald-950/50 border border-emerald-500/30 rounded-xl text-xs space-y-1">
                    <p class="text-emerald-300 font-bold">کۆی گشتی پارەی پێویست: {{ number_format($transaction->total_pay) }} د.ع</p>
                    <p class="text-slate-400">تێبینی: دوای تۆمارکردنی ژمارەی پسوولە، پارەدانەکە بە تەنها وەردەگیرێت.</p>
                </div>
                <div class="flex justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showPayModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl">پاشگەزبوونەوە</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg">تۆمار و وەرگرتن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Booklet Number Modal -->
    <div x-show="showBookletModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-bold text-white flex items-center">
                <i class="fa-solid fa-book-bookmark ml-2 text-blue-400"></i> تۆمارکردنی ژمارەی دەفتەر
            </h3>
            <form action="{{ route('transactions.complete_booklet', $transaction->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">ژمارەی سەر دەفتەر *</label>
                    <input type="text" name="booklet_number" required placeholder="مثلاً: DF-554433" dir="ltr"
                           class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:border-blue-500 focus:outline-none">
                </div>
                <div class="flex justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showBookletModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl">پاشگەزبوونەوە</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-lg">پەسەندکردن و چاپ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Cancellation Modal -->
    <div x-show="showCancelModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-bold text-rose-400 flex items-center">
                <i class="fa-solid fa-ban ml-2"></i> پووچەڵکردنەوەی مامەڵە
            </h3>
            <form action="{{ route('transactions.cancel', $transaction->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">ژمارەی نووسراوی پووچەڵکردنەوەی گومرگ</label>
                    <input type="text" name="no_nusraw_puchal" placeholder="ژمارەی نووسراوی فەرمی" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">هۆکاری پووچەڵکردنەوە *</label>
                    <textarea name="cancellation_reason" required rows="3" placeholder="هۆکاری پووچەڵکردنەوە بە دەقیقی بنووسە..." class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100"></textarea>
                </div>
                <div class="flex justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showCancelModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl">پاشگەزبوونەوە</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-lg">تەئکید و پووچەڵکردنەوە</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: Auditor Return Modal -->
    <div x-show="showReturnModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-base font-bold text-rose-400 flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-rotate-left ml-2"></i> گەڕاندنەوەی مامەڵە بۆ تەخمین / داتائەنتەری</span>
                <button @click="showReturnModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>
            <form action="{{ route('transactions.return', $transaction->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">تکایە هۆکاری گەڕاندنەوە و هەڵەکە بە ڕوونی بنووسە *</label>
                    <textarea name="return_reason" required rows="3" placeholder="مثلاً: ناوی هاووڵاتی یان ژمارەی شاسی بە هەڵە داخڵکراوە..." 
                              class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-xs text-slate-100 focus:border-rose-500 focus:outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showReturnModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl">پاشگەزبوونەوە</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-lg">پەسەندکردنی گەڕاندنەوە</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 5: Admin Step Reversion Modal -->
    <div x-show="showRevertModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-amber-500/50 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-base font-bold text-amber-400 flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-arrow-rotate-left ml-2"></i> ڕێکخستنەوەی قۆناغی مامەڵە (Admin Step Reversion)</span>
                <button @click="showRevertModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>
            <form action="{{ route('transactions.revert_step', $transaction->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">کام قۆناغ دەتەوێت ڕێکبخەیتەوە (Reset)؟ *</label>
                    <select name="step" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100 font-bold focus:border-amber-500 focus:outline-none">
                        <option value="payment">١. ڕێکخستنەوەی پارەدان (is_paid = false & پسوولەی 37/أ سڕینەوە)</option>
                        <option value="booklet">٢. ڕێکخستنەوەی دەفتەر (is_booklet_completed = false)</option>
                        <option value="audit">٣. ڕێکخستنەوەی وردبینی (is_audited = false)</option>
                        <option value="inspection">٤. ڕێکخستنەوەی کەشف و تەخمین (is_inspected = false)</option>
                        <option value="all">٥. گشت قۆناغەکان بگەڕێنەرەوە بۆ داتائەنتەری سەرلەنوێ</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">هۆکاری سەرەکی و روونکردنەوەی ئەدمین *</label>
                    <textarea name="reversion_reason" required rows="3" placeholder="تکایە هۆکاری ڕاسپاردن و ڕێکخستنەوەی پسوولە/دەفتەر بنووسە..." 
                              class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-xs text-slate-100 focus:border-amber-500 focus:outline-none"></textarea>
                    <p class="text-[11px] text-amber-400/90 mt-1.5 leading-normal">
                        * تێبینی: ئەگەر قۆناغی پارەدان ڕێسێت بکەیت، ژمارەی پسوولەی ۳۷/أ باطل دەبێتەوە و پاک دەکرێتەوە بۆ ئەوەی وەسڵبڕ سەرلەنوێ وەریبگرێتەوە.
                    </p>
                </div>
                <div class="flex items-center justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showRevertModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl">پاشگەزبوونەوە</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-lg">پەسەندکردن و ڕێکخستنەوە</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 6: Auditor Verification Confirmation Modal (پەسەندکردنی وردبینی) -->
    <div x-show="showAuditModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-purple-500/50 rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl" @click.away="showAuditModal = false">
            <div class="flex items-center justify-between border-b border-slate-700/80 pb-3">
                <h3 class="text-base font-black text-purple-400 flex items-center">
                    <i class="fa-solid fa-clipboard-check ml-2 text-lg"></i>
                    دڵنیابوونەوە و پەسەندکردنی وردبینی
                </h3>
                <button @click="showAuditModal = false" class="text-slate-400 hover:text-slate-200 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Transaction Details Summary Card -->
            <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-4 space-y-3">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">ناوی هاوڵاتی:</span>
                    <span class="text-slate-100 font-bold text-sm">{{ $transaction->visitor_name }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">جۆری مامەڵە:</span>
                    <span class="text-purple-300 font-bold">{{ $transaction->transactionType?->name_kurdish ?? 'دیارینەکراو' }}</span>
                </div>
                @if(in_array((int)$transaction->transaction_type_id, [1, 2]))
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">ماوەی ساڵانە (دەرهێنان / تازەکردنەوە):</span>
                        <span class="text-amber-300 font-bold">{{ $transaction->num_years ?? 1 }} ساڵ</span>
                    </div>
                @endif
                <div class="flex justify-between items-center text-xs pt-2 border-t border-slate-700">
                    <span class="text-slate-400 font-bold">کۆی گشتی بڕی پارەی پێویست:</span>
                    <span class="text-emerald-400 font-black text-base">{{ number_format($transaction->total_pay) }} دینار</span>
                </div>
            </div>

            <!-- Verification Textbox Form -->
            <form action="{{ route('transactions.audit', $transaction->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-200 mb-1.5">
                        <i class="fa-solid fa-pen-nib ml-1 text-purple-400"></i>
                        بۆ دڵنیابوونەوە، تکایە کۆی گشتی بڕی پارەکە بە دروستی بنووسەوە: *
                    </label>
                    <input type="text" 
                           name="confirm_total_pay" 
                           x-model="confirmAuditAmount" 
                           placeholder="بڕی پارەکە بنووسەوە (نموونە: {{ number_format($transaction->total_pay) }})" 
                           required 
                           autocomplete="off"
                           class="w-full bg-slate-950 border border-slate-700 focus:border-purple-500 rounded-xl px-3.5 py-2.5 text-sm font-bold text-slate-100 text-center tracking-wider focus:outline-none focus:ring-2 focus:ring-purple-500/20" />

                    <!-- Mismatch warning or match indicator -->
                    <template x-if="confirmAuditAmount.length > 0 && confirmAuditAmount.replace(/\D/g, '') !== expectedAuditAmount">
                        <p class="text-xs text-rose-400 font-bold mt-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            بڕی پارەی نووسراو یەکسان نییە بە کۆی پارەکە ({{ number_format($transaction->total_pay) }} دینار). تکایە بە دروستی بنووسە.
                        </p>
                    </template>
                    <template x-if="confirmAuditAmount.replace(/\D/g, '') === expectedAuditAmount">
                        <p class="text-xs text-emerald-400 font-bold mt-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check"></i>
                            بڕی پارەکە تەواو و دروستە. دەتوانیت پەسەندی بکەیت.
                        </p>
                    </template>
                </div>

                <div class="flex items-center justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showAuditModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition">
                        پاشگەزبوونەوە
                    </button>
                    <button type="submit" 
                            :disabled="confirmAuditAmount.replace(/\D/g, '') !== expectedAuditAmount"
                            :class="confirmAuditAmount.replace(/\D/g, '') === expectedAuditAmount ? 'bg-purple-600 hover:bg-purple-500 text-white cursor-pointer shadow-lg shadow-purple-600/30' : 'bg-slate-800 text-slate-500 border border-slate-700 cursor-not-allowed opacity-50'"
                            class="px-5 py-2 text-xs font-bold rounded-xl transition flex items-center">
                        <i class="fa-solid fa-clipboard-check ml-1.5"></i>
                        پەسەندکردنی کۆتایی وردبینی
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        JsBarcode("#showBarcodeSvg", "{{ $transaction->barcode }}", {
            format: "CODE128",
            lineColor: "#000",
            width: 1.8,
            height: 35,
            displayValue: true,
            fontSize: 11,
            fontOptions: "bold",
            font: "Vazirmatn",
            textMargin: 1
        });
    });
</script>
@endsection
