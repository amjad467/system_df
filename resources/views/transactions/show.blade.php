@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

@php
    $typeId = (int)$transaction->transaction_type_id;
    $typeName = $transaction->transactionType?->name_kurdish ?? 'مامەڵە';

    $typeTheme = match($typeId) {
        1 => [ // دەرهێنانی دەفتەر (نوێ)
            'label' => 'دەرهێنانی دەفتەر (نوێ)',
            'gradient' => 'from-emerald-950/95 via-slate-900 to-emerald-950/60',
            'border' => 'border-emerald-500/80 shadow-emerald-500/20 ring-1 ring-emerald-500/30',
            'pill' => 'bg-emerald-500/25 text-emerald-200 border-emerald-400/60 shadow-sm',
            'icon' => 'fa-solid fa-passport text-emerald-400',
            'textColor' => 'text-emerald-300',
            'iconBg' => 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400 shadow-emerald-500/30',
        ],
        2 => [ // نوێکردنەوە / تازەکردنەوە
            'label' => 'نوێکردنەوەی دەفتەر (تازەکردنەوە)',
            'gradient' => 'from-sky-950/95 via-slate-900 to-sky-950/60',
            'border' => 'border-sky-500/80 shadow-sky-500/20 ring-1 ring-sky-500/30',
            'pill' => 'bg-sky-500/25 text-sky-200 border-sky-400/60 shadow-sm',
            'icon' => 'fa-solid fa-rotate-right text-sky-400',
            'textColor' => 'text-sky-300',
            'iconBg' => 'bg-sky-500/20 border-sky-500/40 text-sky-400 shadow-sky-500/30',
        ],
        5 => [ // پووچەڵکردنەوەی دەفتەر
            'label' => 'پووچەڵکردنەوەی دەفتەر',
            'gradient' => 'from-rose-950/95 via-slate-900 to-rose-950/60',
            'border' => 'border-rose-500/90 shadow-rose-500/30 ring-2 ring-rose-500/40',
            'pill' => 'bg-rose-500/30 text-rose-100 border-rose-400/70 shadow-sm',
            'icon' => 'fa-solid fa-ban text-rose-400',
            'textColor' => 'text-rose-300',
            'iconBg' => 'bg-rose-500/25 border-rose-500/50 text-rose-300 shadow-rose-500/30',
        ],
        3 => [ // ناوگۆڕین
            'label' => 'ناوگۆڕین (گۆڕینی خاوەنداریەتی)',
            'gradient' => 'from-purple-950/95 via-slate-900 to-purple-950/60',
            'border' => 'border-purple-500/80 shadow-purple-500/20 ring-1 ring-purple-500/30',
            'pill' => 'bg-purple-500/25 text-purple-200 border-purple-400/60 shadow-sm',
            'icon' => 'fa-solid fa-user-pen text-purple-400',
            'textColor' => 'text-purple-300',
            'iconBg' => 'bg-purple-500/20 border-purple-500/40 text-purple-400 shadow-purple-500/30',
        ],
        4 => [ // دەفتەرگۆڕین
            'label' => 'دەفتەرگۆڕین (گۆڕینی دەفتەر)',
            'gradient' => 'from-amber-950/95 via-slate-900 to-amber-950/60',
            'border' => 'border-amber-500/80 shadow-amber-500/20 ring-1 ring-amber-500/30',
            'pill' => 'bg-amber-500/25 text-amber-200 border-amber-400/60 shadow-sm',
            'icon' => 'fa-solid fa-book text-amber-400',
            'textColor' => 'text-amber-300',
            'iconBg' => 'bg-amber-500/20 border-amber-500/40 text-amber-400 shadow-amber-500/30',
        ],
        6 => [ // بەڵێننامە
            'label' => 'بەڵێننامە',
            'gradient' => 'from-amber-950/95 via-yellow-950/60 to-slate-900',
            'border' => 'border-amber-500/80 shadow-amber-500/20 ring-1 ring-amber-500/30',
            'pill' => 'bg-amber-500/25 text-amber-200 border-amber-400/60 shadow-sm',
            'icon' => 'fa-solid fa-file-contract text-amber-400',
            'textColor' => 'text-amber-300',
            'iconBg' => 'bg-amber-500/20 border-amber-500/40 text-amber-400 shadow-amber-500/30',
        ],
        default => [
            'label' => $typeName,
            'gradient' => 'from-indigo-950/95 via-slate-900 to-indigo-950/60',
            'border' => 'border-indigo-500/80 shadow-indigo-500/20 ring-1 ring-indigo-500/30',
            'pill' => 'bg-indigo-500/25 text-indigo-200 border-indigo-400/60 shadow-sm',
            'icon' => 'fa-solid fa-folder-open text-indigo-400',
            'textColor' => 'text-indigo-300',
            'iconBg' => 'bg-indigo-500/20 border-indigo-500/40 text-indigo-400 shadow-indigo-500/30',
        ],
    };
@endphp

<div class="max-w-5xl mx-auto space-y-6 pb-24" x-data="{ 
    showPayModal: false, 
    showBookletModal: false, 
    showCancelModal: false, 
    showReturnModal: false, 
    showRevertModal: false,
    showAuditModal: false,
    showSubmitModal: false,
    confirmAuditAmount: '',
    expectedAuditAmount: '{{ (int)$transaction->total_pay }}'
}">
    
    <!-- Top Navigation & Action Toolbar -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-xl flex flex-col md:flex-row items-center justify-between gap-4 backdrop-blur-sm">
        <div class="flex items-center space-x-3 space-x-reverse w-full md:w-auto">
            @if(auth()->check() && auth()->user()->role === 'data_entry' && !auth()->user()->isAdmin())
                <a href="{{ route('dashboard') }}" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow" title="گەڕانەوە بۆ وێستگەی داتائەنتەری">
                    <i class="fa-solid fa-arrow-right"></i>
                    <span class="hidden sm:inline">گەڕانەوە</span>
                </a>
            @else
                <a href="{{ route('transactions.index') }}" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow" title="گەڕانەوە بۆ لیستی مامەڵەکان">
                    <i class="fa-solid fa-arrow-right"></i>
                    <span class="hidden sm:inline">لیست</span>
                </a>
            @endif

            <div class="bg-white px-2 py-1 rounded-xl shadow-md border border-slate-700 flex items-center justify-center shrink-0">
                <svg id="showBarcodeSvg" class="max-h-10"></svg>
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-xs font-black text-sky-400 bg-sky-950/60 px-2 py-0.5 rounded border border-sky-800/50">
                        {{ $transaction->barcode }}
                    </span>
                    <!-- جۆری مامەڵە بە بارزی و گەورەیی لە تەنیشت بارکۆد -->
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-black border flex items-center gap-1.5 {{ $typeTheme['pill'] }}" title="جۆری مامەڵەی ئێستا">
                        <i class="{{ $typeTheme['icon'] }} text-xs"></i>
                        <span>{{ $typeName }}</span>
                    </span>
                    @if($transaction->is_returned)
                        <span class="px-2.5 py-0.5 bg-rose-500/20 text-rose-300 text-xs font-black rounded-full border border-rose-500/40 animate-pulse flex items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation"></i> گەڕێنراوەتەوە
                        </span>
                    @elseif($transaction->is_cancelled)
                        <span class="px-2.5 py-0.5 bg-rose-500/20 text-rose-400 text-xs font-black rounded-full border border-rose-500/30 flex items-center gap-1">
                            <i class="fa-solid fa-ban"></i> پووچەڵکراوە
                        </span>
                    @elseif($transaction->is_submitted)
                        <span class="px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 text-xs font-black rounded-full border border-emerald-500/30 flex items-center gap-1">
                            <i class="fa-solid fa-box-archive"></i> ئەرشیفکراو
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 bg-sky-500/15 text-sky-300 text-xs font-bold rounded-full border border-sky-500/30 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-ping"></span>
                            {{ $transaction->getCurrentStageName() }}
                        </span>
                    @endif
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                    <span>بەروار: <strong class="text-slate-300 font-mono">{{ $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : ($transaction->created_at ? $transaction->created_at->format('Y-m-d') : '-') }}</strong></span>
                    <span>|</span>
                    <span>تۆمارکار: <strong class="text-slate-300">{{ $transaction->user_input }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Organized Action Suite Toolbar -->
        <div class="flex items-center flex-wrap gap-2 w-full md:w-auto justify-end pt-3 md:pt-0 border-t md:border-t-0 border-slate-800">
            <!-- 1. Dedicated Primary Print Action (If Booklet is Ready or Receipt is Ready) -->
            @if($transaction->requiresBooklet() && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet') && $transaction->is_paid && !empty($transaction->booklet_number) && (!$transaction->is_submitted || auth()->user()->isAdmin()))
                <a href="{{ route('transactions.print_booklet', $transaction->id) }}" target="_blank" 
                   class="px-3.5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5" 
                   title="چاپی دەفتەری گەشتیاری">
                    <i class="fa-solid fa-passport"></i>
                    <span>چاپی دەفتەر ({{ $transaction->booklet_number }})</span>
                </a>
            @elseif(!$transaction->is_cancelled && $transaction->is_paid && (!$transaction->is_submitted || auth()->user()->isAdmin()) && (auth()->user()->isAdmin() || auth()->user()->role === 'cashier'))
                <a href="{{ route('transactions.print_payment_receipt', $transaction->id) }}" target="_blank" 
                   class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5" 
                   title="چاپی وەسڵی فەرمی پارەدان">
                    <i class="fa-solid fa-receipt"></i>
                    <span>چاپی ۳۷/أ</span>
                </a>
            @endif

            <!-- 2. Master "چاپکردنی بەڵگەنامەکان" Dropdown (Consolidates all official prints cleanly) -->
            <div class="relative" x-data="{ openPrintsMenu: false }">
                <button type="button" @click="openPrintsMenu = !openPrintsMenu" 
                        class="px-3.5 py-2 bg-slate-800 hover:bg-slate-750 text-indigo-300 hover:text-white border border-indigo-500/40 hover:border-indigo-500/70 text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                    <i class="fa-solid fa-print text-indigo-400"></i>
                    <span>بەڵگەنامە و چاپەکان</span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openPrintsMenu ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="openPrintsMenu" @click.away="openPrintsMenu = false" x-cloak 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute left-0 mt-2 w-72 sm:w-80 bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl p-2 z-50 space-y-2 text-xs text-right">
                    
                    <!-- قۆناغی سەرەتایی و پشکنین -->
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 px-2 block">فیشە، کەشف و یاسایی:</span>
                        @if((!$transaction->is_submitted || auth()->user()->isAdmin()) && (auth()->user()->isAdmin() || auth()->user()->role === 'data_entry'))
                            <a href="{{ route('transactions.print_data_entry', $transaction->id) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-200 hover:bg-slate-800 hover:text-white transition">
                                <i class="fa-solid fa-file-invoice text-sky-400 w-4 text-center"></i>
                                <span>فیشەی داتائەنتەری (بەدواداچوون)</span>
                            </a>
                        @endif

                        @if((!$transaction->is_submitted || auth()->user()->isAdmin()) && (auth()->user()->isAdmin() || auth()->user()->role === 'inspector'))
                            @if($transaction->is_inspected && (!$transaction->is_audited || auth()->user()->isAdmin()))
                                <a href="{{ route('transactions.print_receipt', $transaction->id) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-200 hover:bg-slate-800 hover:text-white transition">
                                    <i class="fa-solid fa-file-waveform text-teal-400 w-4 text-center"></i>
                                    <span>پسولەی کەشف و خەمڵاندن</span>
                                </a>
                            @endif
                        @endif

                        @if(!$transaction->is_cancelled && (auth()->user()->isAdmin() || auth()->user()->isLegal()))
                            <a href="{{ route('transactions.print_pledge', $transaction->id) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-200 hover:bg-slate-800 hover:text-white transition">
                                <i class="fa-solid fa-file-contract text-amber-400 w-4 text-center"></i>
                                <span>چاپی بەڵێننامەی یاسایی</span>
                            </a>
                        @endif
                    </div>

                    <!-- قۆناغی دارایی و پشکنین -->
                    @if(!$transaction->is_cancelled && $transaction->is_paid && (!$transaction->is_submitted || auth()->user()->isAdmin()) && (auth()->user()->isAdmin() || auth()->user()->role === 'cashier'))
                        <div class="space-y-1 pt-1.5 border-t border-slate-800">
                            <span class="text-[10px] font-bold text-slate-400 px-2 block">دارایی و وەسڵ:</span>
                            <a href="{{ route('transactions.print_payment_receipt', $transaction->id) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-emerald-300 hover:bg-slate-800 hover:text-white transition">
                                <i class="fa-solid fa-receipt text-emerald-400 w-4 text-center"></i>
                                <span>پسوولەی پارەدان (۳۷/أ)</span>
                            </a>
                            <a href="{{ route('transactions.print_inspection_letter', $transaction->id) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-200 hover:bg-slate-800 hover:text-white transition">
                                <i class="fa-solid fa-file-circle-check text-sky-400 w-4 text-center"></i>
                                <span>نووسراوی پشکنین</span>
                            </a>
                        </div>
                    @endif

                    <!-- دەفتەر و نوسراوە فەرمییەکان -->
                    <div class="space-y-1 pt-1.5 border-t border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 px-2 block">دەفتەر و نوسراوە فەرمییەکان:</span>
                        @if($transaction->requiresBooklet() && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet') && $transaction->is_paid && !empty($transaction->booklet_number))
                            <a href="{{ route('transactions.print_booklet', $transaction->id) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-indigo-300 font-bold hover:bg-indigo-600 hover:text-white transition">
                                <i class="fa-solid fa-passport text-indigo-400 w-4 text-center"></i>
                                <span>چاپی دەفتەر ({{ $transaction->booklet_number }})</span>
                            </a>
                        @endif

                        @if(!$transaction->is_cancelled && (!$transaction->is_submitted || auth()->user()->isAdmin()) && (auth()->user()->isAdmin() || auth()->user()->role === 'auditor' || auth()->user()->role === 'booklet' || auth()->user()->hasPermission('transactions.audit')) && ($transaction->is_inspected || $transaction->is_audited || $transaction->is_paid))
                            <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-amber-300 hover:bg-slate-800 hover:text-white transition">
                                <i class="fa-solid fa-file-circle-exclamation text-amber-400 w-4 text-center"></i>
                                <span>نیشانەی ڕەفتارنەکردن</span>
                            </a>
                            <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-rose-300 hover:bg-slate-800 hover:text-white transition">
                                <i class="fa-solid fa-file-circle-xmark text-rose-400 w-4 text-center"></i>
                                <span>نووسراوی پووچەڵکردنەوە</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 3. Edit Action Button -->
            @if((auth()->user()->isAdmin() || auth()->user()->role === 'data_entry') && $transaction->canBeEditedByDataEntry() && !$transaction->is_cancelled)
                <a href="{{ route('transactions.edit', $transaction->id) }}" 
                   class="px-3.5 py-2 bg-amber-600/90 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5" 
                   title="دەستکاریکردن یان ڕاستکردنەوە">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>دەستکاریکردن</span>
                </a>
            @endif

            <!-- 4. Follow-up Transaction Dropdown -->
            @if(auth()->user()->isAdmin() || auth()->user()->role === 'data_entry')
                <div class="relative" x-data="{ openFollowUp: false }">
                    <button type="button" @click="openFollowUp = !openFollowUp" 
                            class="px-3 py-2 bg-slate-800 hover:bg-slate-750 text-sky-300 hover:text-white border border-slate-700 text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                        <i class="fa-solid fa-arrows-split-up-and-left text-sky-400"></i>
                        <span>مامەڵەی نوێ</span>
                        <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openFollowUp ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="openFollowUp" @click.away="openFollowUp = false" x-cloak 
                         class="absolute left-0 mt-2 w-56 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl p-1.5 z-50 space-y-1 text-xs text-right">
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

            <!-- 5. Auditor Quick Revert / Return (If Applicable) -->
            @if($transaction->is_audited && !$transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->role === 'auditor' || auth()->user()->hasPermission('transactions.audit')))
                <form action="{{ route('transactions.revert_audit', $transaction->id) }}" method="POST" class="inline" onsubmit="return confirm('ئایا دڵنیایت لە هەڵوەشاندنەوەی پەسەندی وردبینی؟ مامەڵەکە دەگەڕێتەوە بۆ چاوەڕوانی وردبینی.');">
                    @csrf
                    <button type="submit" class="px-2.5 py-2 bg-purple-700/80 hover:bg-purple-600 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1" title="هەڵوەشاندنەوەی وردبینی">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                        <span>هەڵوەشاندنەوە</span>
                    </button>
                </form>
                <button type="button" @click="showReturnModal = true" class="px-2.5 py-2 bg-rose-600/80 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1" title="گەڕاندنەوە بۆ پێشتر">
                    <i class="fa-solid fa-arrow-turn-down-left"></i>
                    <span>گەڕاندنەوە</span>
                </button>
            @endif

            <!-- 6. Admin Reset Modal Trigger -->
            @if(auth()->check() && auth()->user()->isAdmin())
                <button type="button" @click="showRevertModal = true" 
                        class="px-3 py-2 bg-slate-800 hover:bg-slate-750 text-amber-400 hover:text-amber-300 border border-amber-500/30 text-xs font-bold rounded-xl transition flex items-center gap-1.5" 
                        title="ڕێکخستنەوەی قۆناغی مامەڵە">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                    <span>ڕێکخستنەوە (Reset)</span>
                </button>
            @endif

            <!-- 7. Admin Soft Delete -->
            @if(auth()->check() && auth()->user()->isAdmin())
                <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" onsubmit="return confirm('دڵنیایت لە سڕینەوەی کاتی (Soft Delete) ی ئەم مامەڵەیە؟')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" title="سڕینەوەی کاتی مامەڵە" class="p-2 bg-rose-950/40 hover:bg-rose-900 border border-rose-800/40 text-rose-400 hover:text-rose-200 text-xs font-bold rounded-xl transition">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- HERO: Prominent Transaction Type Showcase -->
    <div class="bg-gradient-to-r {{ $typeTheme['gradient'] }} border-2 {{ $typeTheme['border'] }} rounded-2xl p-4 sm:p-5 shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Right Side: Type & Identity -->
        <div class="flex items-center gap-3.5 w-full md:w-auto">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl border flex items-center justify-center shrink-0 shadow-lg {{ $typeTheme['iconBg'] }}">
                <i class="{{ $typeTheme['icon'] }} text-xl sm:text-2xl"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold text-slate-400">جۆری مامەڵە:</span>
                    <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded-md bg-black/40 text-slate-300 border border-slate-700">کۆد: {{ $transaction->transaction_type_id }}</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-white tracking-normal mt-0.5 flex items-center gap-2">
                    <span>{{ $typeName }}</span>
                </h2>
            </div>
        </div>

        <!-- Left Side: Status Chips -->
        <div class="flex items-center gap-2.5 w-full md:w-auto justify-end flex-wrap border-t md:border-t-0 pt-2.5 md:pt-0 border-slate-800">
            <!-- قۆناغی ئێستا -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-950/80 border border-slate-700/80 shadow-inner">
                <span class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></span>
                <div>
                    <span class="text-[10px] text-slate-400 block font-medium">قۆناغی ئێستا</span>
                    <span class="text-xs sm:text-[13px] font-bold text-white leading-none">{{ $transaction->getCurrentStageName() }}</span>
                </div>
            </div>

            <!-- کۆی تێچوو -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-950/80 border border-emerald-900/60 shadow-inner">
                <i class="fa-solid fa-coins text-emerald-400 text-xs"></i>
                <div>
                    <span class="text-[10px] text-slate-400 block font-medium">کۆی بڕی تێچوو</span>
                    <span class="text-xs sm:text-[13px] font-bold font-mono text-emerald-400 leading-none">
                        {{ number_format($transaction->total_pay) }} <span class="text-[10px] font-sans text-slate-400">د.ع</span>
                    </span>
                </div>
            </div>

            @if($transaction->booklet_number)
                <!-- ژمارەی دەفتەر -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-950/80 border border-indigo-900/60 shadow-inner">
                    <i class="fa-solid fa-passport text-indigo-400 text-xs"></i>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-medium">دەفتەری گەشتیاری</span>
                        <span class="text-xs sm:text-[13px] font-bold font-mono text-indigo-300 leading-none">{{ $transaction->booklet_number }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Comprehensive Header Summary Card (کارتی سەرەکی ناسینەوە و کورتەی زانیارییەکان) -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-900/95 to-slate-950 border border-slate-700/80 rounded-2xl p-5 shadow-2xl relative overflow-hidden backdrop-blur-sm">
        <!-- Background decorative ambient glow -->
        <div class="absolute -top-12 -left-12 w-48 h-48 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-center relative z-10">
            <!-- کۆڵۆمی ١: زانیاری شۆفێر و هاووڵاتی (4 cols) -->
            <div class="lg:col-span-4 space-y-2.5 border-b lg:border-b-0 lg:border-l border-slate-800 pb-4 lg:pb-0 lg:pl-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">زانیاری هاووڵاتی / شۆفێر</span>
                    <span class="px-3 py-1 rounded-xl text-xs sm:text-sm font-black border shadow-md {{ $typeTheme['pill'] }} mr-auto flex items-center gap-1.5">
                        <i class="{{ $typeTheme['icon'] }} text-xs"></i>
                        <span>{{ $typeName }}</span>
                    </span>
                </div>

                <div>
                    <h1 class="text-lg sm:text-xl font-black text-white leading-tight">
                        {{ $transaction->visitor_name }}
                    </h1>
                    @if($transaction->visitor_name_eng)
                        <span class="text-xs text-slate-400 font-mono tracking-wide block mt-0.5" dir="ltr">
                            {{ $transaction->visitor_name_eng }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2 text-xs pt-1">
                    @if($transaction->phone_number)
                        <a href="tel:{{ $transaction->phone_number }}" 
                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-950/60 text-emerald-300 border border-emerald-800/60 font-mono font-bold hover:bg-emerald-900/60 transition shadow-sm" dir="ltr" title="پەیوەندیکردن">
                            <i class="fa-solid fa-phone text-[10px] text-emerald-400"></i>
                            <span>{{ $transaction->phone_number }}</span>
                        </a>
                    @endif

                    @if($transaction->second_driver_name)
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-800/80 text-slate-300 border border-slate-700 text-[11px]" title="شۆفێری دووەم">
                            <i class="fa-solid fa-user-group text-[9px] text-slate-400"></i>
                            <span class="text-slate-400">ش. دووەم:</span>
                            <span class="font-bold truncate max-w-[120px]">{{ $transaction->second_driver_name }}</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- کۆڵۆمی ٢: تابلۆی مۆدێرن و ئۆتۆمبێل (4 cols) -->
            <div class="lg:col-span-4 flex flex-col items-center justify-center text-center space-y-2 border-b lg:border-b-0 lg:border-l border-slate-800 pb-4 lg:pb-0 lg:pl-4">
                <!-- تابلۆی ئۆتۆمبێل بە دیزاینی فەرمی -->
                <div>
                    <x-vehicle-plate :plate="$transaction->plate_number" :type="$transaction->plateType" size="lg" />
                </div>

                <div class="space-y-0.5">
                    <div class="text-sm font-bold text-slate-200">
                        {{ $transaction->carMake->name_kurdish ?? '-' }}
                        @if($transaction->model_year)
                            <span class="font-mono text-slate-400 text-xs font-normal">({{ $transaction->model_year }})</span>
                        @endif
                        @if($transaction->carColor)
                            <span class="text-xs text-sky-300 font-semibold mr-1.5">| {{ $transaction->carColor->name_kurdish }}</span>
                        @endif
                    </div>

                    @if($transaction->chassis_number && $transaction->chassis_number !== '-')
                        <div class="inline-flex items-center gap-1.5 text-[11px] font-mono text-slate-400 bg-slate-950 px-2.5 py-0.5 rounded-lg border border-slate-800" title="ژمارەی شاسی (VIN)">
                            <span class="text-slate-500 font-bold">VIN:</span>
                            <span class="text-sky-300 font-bold uppercase select-all">{{ $transaction->chassis_number }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- کۆڵۆمی ٣: دۆخی دارایی، دەفتەر و بۆکسەکان (4 cols) -->
            <div class="lg:col-span-4 space-y-2.5">
                <!-- بڕی پارەی گشتی -->
                <div class="bg-slate-950/80 border border-slate-800 p-2.5 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">کۆی بڕی پارە:</span>
                        <span class="text-base sm:text-lg font-black font-mono text-emerald-400">
                            {{ number_format($transaction->total_pay) }} <span class="text-xs font-normal text-slate-400">د.ع</span>
                        </span>
                    </div>
                    <div class="text-left">
                        <span class="text-[10px] text-slate-400 block font-bold">وەسڵی ۳۷/أ:</span>
                        @if($transaction->receipt_37a_number)
                            <span class="text-xs font-black font-mono text-white bg-emerald-950 px-2 py-0.5 rounded border border-emerald-800">
                                {{ $transaction->receipt_37a_number }}
                            </span>
                        @else
                            <span class="text-[11px] text-amber-400 font-bold bg-amber-950/40 px-2 py-0.5 rounded border border-amber-800/40">
                                نەبڕدراوە
                            </span>
                        @endif
                    </div>
                </div>

                <!-- دەفتەر و بۆکسەکان -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <!-- ژمارەی دەفتەر -->
                    <div class="bg-slate-950/60 border border-slate-800/80 p-2 rounded-xl">
                        <span class="text-[10px] text-slate-400 block">ژمارەی دەفتەر:</span>
                        @if($transaction->booklet_number)
                            <span class="font-mono font-black text-blue-400 text-xs flex items-center gap-1 mt-0.5">
                                <i class="fa-solid fa-passport text-[10px]"></i>
                                {{ $transaction->booklet_number }}
                            </span>
                        @else
                            <span class="text-slate-500 font-mono text-xs mt-0.5 block">- چاوەڕێیە -</span>
                        @endif
                    </div>

                    <!-- بۆکسی دەرهێنان / پووچەڵ -->
                    <div class="bg-slate-950/60 border border-slate-800/80 p-2 rounded-xl">
                        <span class="text-[10px] text-slate-400 block">شوێنی بۆکس:</span>
                        <div class="mt-0.5 flex items-center gap-1 font-mono text-[11px]">
                            @if($transaction->NO_boxfile)
                                <span class="font-bold text-sky-300" title="بۆکسی دەرهێنان">ب: {{ $transaction->NO_boxfile }}</span>
                            @endif
                            @if($transaction->NO_boxfile_puchal)
                                <span class="font-bold text-rose-400 mr-1" title="بۆکسی پووچەڵ">(پ: {{ $transaction->NO_boxfile_puchal }})</span>
                            @endif
                            @if(!$transaction->NO_boxfile && !$transaction->NO_boxfile_puchal)
                                <span class="text-slate-500">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    @php
        $requiresBooklet = $transaction->requiresBooklet();
        $isCancelled = $transaction->is_cancelled;
        $isSubmitted = $transaction->is_submitted;

        $s1Done = true;
        $s2Done = (bool)$transaction->is_inspected;
        $s3Done = (bool)$transaction->is_audited;
        $s4Done = (bool)$transaction->is_paid;
        $s5Done = $requiresBooklet ? (bool)$transaction->is_booklet_completed : (bool)$transaction->is_submitted;
        $s6Done = (bool)$transaction->is_submitted;

        if ($isCancelled) {
            $activeStep = 0;
        } elseif (!$s2Done) {
            $activeStep = 2;
        } elseif (!$s3Done) {
            $activeStep = 3;
        } elseif (!$s4Done) {
            $activeStep = 4;
        } elseif ($requiresBooklet && !$s5Done) {
            $activeStep = 5;
        } elseif (!$s6Done) {
            $activeStep = $requiresBooklet ? 6 : 5;
        } else {
            $activeStep = 7;
        }
    @endphp

    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4 backdrop-blur-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-800 pb-3.5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white text-sm shadow">
                    <i class="fa-solid fa-diagram-project"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white flex items-center gap-2 flex-wrap">
                        <span>قۆناغ و ستێپەکانی کارکردنی مامەڵە (Workflow Stepper)</span>
                        <span class="text-xs font-black px-2.5 py-0.5 rounded-lg border {{ $typeTheme['pill'] }} inline-flex items-center gap-1">
                            <i class="{{ $typeTheme['icon'] }} text-[10px]"></i>
                            <span>{{ $typeName }}</span>
                        </span>
                    </h3>
                    <p class="text-[11px] text-slate-400">بەدواداچوونی ڕاستەوخۆ بۆ هەنگاوەکانی تەواوبوونی مامەڵە.</p>
                </div>
            </div>

            <!-- دۆخی قۆناغی ئێستا بە ڕوونی -->
            <div>
                @if($isCancelled)
                    <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-rose-500/15 text-rose-300 border border-rose-500/30 flex items-center gap-1.5">
                        <i class="fa-solid fa-ban"></i> ئەم مامەڵەیە پووچەڵکراوەتەوە
                    </span>
                @elseif($isSubmitted)
                    <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i> سەرجەم قۆناغەکان تەواوبوون و ئەرشیفکرا
                    </span>
                @else
                    <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-sky-500/15 text-sky-300 border border-sky-500/30 flex items-center gap-1.5 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></span>
                        ئێستا چاوەڕێی: <strong class="text-white mr-1">{{ $transaction->getCurrentStageName() }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 {{ $requiresBooklet ? 'sm:grid-cols-6' : 'sm:grid-cols-5' }} gap-3 relative">
            <!-- Step 1: Data Entry -->
            <div class="p-3.5 rounded-2xl border transition relative {{ $s1Done ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 shadow-sm' : 'bg-slate-900/60 border-slate-800 text-slate-500' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black {{ $s1Done ? 'bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/40' : 'bg-slate-800 text-slate-400' }}">
                        <i class="fa-solid fa-check text-[10px]"></i>
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $s1Done ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-500' }}">
                        تەواوە
                    </span>
                </div>
                <h4 class="text-xs font-black text-white block">١. داتائەنتەری</h4>
                <p class="text-[11px] text-slate-400 mt-1 truncate" title="{{ $transaction->user_input }}">
                    <i class="fa-solid fa-user-pen text-[9px] text-slate-500 ml-1"></i>{{ $transaction->user_input ?: 'تۆمارکراو' }}
                </p>
            </div>

            <!-- Step 2: Inspection -->
            <div class="p-3.5 rounded-2xl border transition relative {{ $s2Done ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 shadow-sm' : ($activeStep === 2 ? 'bg-gradient-to-b from-orange-500/15 to-slate-900 border-orange-500/60 text-orange-200 ring-2 ring-orange-500/30 shadow-lg' : 'bg-slate-900/60 border-slate-800 text-slate-500') }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black {{ $s2Done ? 'bg-emerald-500 text-slate-950 shadow-sm' : ($activeStep === 2 ? 'bg-orange-500 text-white animate-pulse shadow-md shadow-orange-500/40' : 'bg-slate-800 text-slate-400') }}">
                        @if($s2Done)
                            <i class="fa-solid fa-check text-[10px]"></i>
                        @elseif($activeStep === 2)
                            <i class="fa-solid fa-arrow-down text-[10px]"></i>
                        @else
                            ۲
                        @endif
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $s2Done ? 'bg-emerald-500/20 text-emerald-300' : ($activeStep === 2 ? 'bg-orange-500/20 text-orange-300 font-black' : 'bg-slate-800 text-slate-500') }}">
                        {{ $s2Done ? 'تەواوە' : ($activeStep === 2 ? 'لە کاردایە' : 'چاوەڕێیە') }}
                    </span>
                </div>
                <h4 class="text-xs font-black {{ $activeStep === 2 ? 'text-orange-300' : 'text-white' }} block">٢. کەشف و تەخمین</h4>
                <p class="text-[11px] text-slate-400 mt-1 truncate" title="{{ $transaction->inspected_by ?? 'نەکراوە' }}">
                    <i class="fa-solid fa-magnifying-glass text-[9px] text-slate-500 ml-1"></i>{{ $transaction->inspected_by ?: 'پشکنین' }}
                </p>
            </div>

            <!-- Step 3: Audit -->
            <div class="p-3.5 rounded-2xl border transition relative {{ $s3Done ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 shadow-sm' : ($activeStep === 3 ? 'bg-gradient-to-b from-purple-500/15 to-slate-900 border-purple-500/60 text-purple-200 ring-2 ring-purple-500/30 shadow-lg' : 'bg-slate-900/60 border-slate-800 text-slate-500') }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black {{ $s3Done ? 'bg-emerald-500 text-slate-950 shadow-sm' : ($activeStep === 3 ? 'bg-purple-600 text-white animate-pulse shadow-md shadow-purple-600/40' : 'bg-slate-800 text-slate-400') }}">
                        @if($s3Done)
                            <i class="fa-solid fa-check text-[10px]"></i>
                        @elseif($activeStep === 3)
                            <i class="fa-solid fa-arrow-down text-[10px]"></i>
                        @else
                            ۳
                        @endif
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $s3Done ? 'bg-emerald-500/20 text-emerald-300' : ($activeStep === 3 ? 'bg-purple-500/20 text-purple-300 font-black' : 'bg-slate-800 text-slate-500') }}">
                        {{ $s3Done ? 'تەواوە' : ($activeStep === 3 ? 'لە کاردایە' : 'چاوەڕێیە') }}
                    </span>
                </div>
                <h4 class="text-xs font-black {{ $activeStep === 3 ? 'text-purple-300' : 'text-white' }} block">٣. وردبینی</h4>
                <p class="text-[11px] text-slate-400 mt-1 truncate" title="{{ $transaction->audited_by ?? 'نەکراوە' }}">
                    <i class="fa-solid fa-clipboard-check text-[9px] text-slate-500 ml-1"></i>{{ $transaction->audited_by ?: 'پەسەندکردن' }}
                </p>
            </div>

            <!-- Step 4: Payment -->
            <div class="p-3.5 rounded-2xl border transition relative {{ $s4Done ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 shadow-sm' : ($activeStep === 4 ? 'bg-gradient-to-b from-emerald-500/15 to-slate-900 border-emerald-500/60 text-emerald-200 ring-2 ring-emerald-500/30 shadow-lg' : 'bg-slate-900/60 border-slate-800 text-slate-500') }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black {{ $s4Done ? 'bg-emerald-500 text-slate-950 shadow-sm' : ($activeStep === 4 ? 'bg-emerald-600 text-white animate-pulse shadow-md shadow-emerald-600/40' : 'bg-slate-800 text-slate-400') }}">
                        @if($s4Done)
                            <i class="fa-solid fa-check text-[10px]"></i>
                        @elseif($activeStep === 4)
                            <i class="fa-solid fa-arrow-down text-[10px]"></i>
                        @else
                            ٤
                        @endif
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $s4Done ? 'bg-emerald-500/20 text-emerald-300' : ($activeStep === 4 ? 'bg-emerald-500/20 text-emerald-300 font-black' : 'bg-slate-800 text-slate-500') }}">
                        {{ $s4Done ? 'تەواوە' : ($activeStep === 4 ? 'لە کاردایە' : 'چاوەڕێیە') }}
                    </span>
                </div>
                <h4 class="text-xs font-black {{ $activeStep === 4 ? 'text-emerald-300' : 'text-white' }} block">٤. وەسڵ و پارەدان</h4>
                <p class="text-[11px] text-slate-400 mt-1 font-mono truncate">
                    <i class="fa-solid fa-receipt text-[9px] text-slate-500 ml-1"></i>{{ $transaction->receipt_37a_number ? 'وەسڵ: ' . $transaction->receipt_37a_number : '۳۷/أ' }}
                </p>
            </div>

            <!-- Step 5: Booklet (only if requires booklet) -->
            @if($requiresBooklet)
                <div class="p-3.5 rounded-2xl border transition relative {{ $s5Done ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 shadow-sm' : ($activeStep === 5 ? 'bg-gradient-to-b from-blue-500/15 to-slate-900 border-blue-500/60 text-blue-200 ring-2 ring-blue-500/30 shadow-lg' : 'bg-slate-900/60 border-slate-800 text-slate-500') }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black {{ $s5Done ? 'bg-emerald-500 text-slate-950 shadow-sm' : ($activeStep === 5 ? 'bg-blue-600 text-white animate-pulse shadow-md shadow-blue-600/40' : 'bg-slate-800 text-slate-400') }}">
                            @if($s5Done)
                                <i class="fa-solid fa-check text-[10px]"></i>
                            @elseif($activeStep === 5)
                                <i class="fa-solid fa-arrow-down text-[10px]"></i>
                            @else
                                ٥
                            @endif
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $s5Done ? 'bg-emerald-500/20 text-emerald-300' : ($activeStep === 5 ? 'bg-blue-500/20 text-blue-300 font-black' : 'bg-slate-800 text-slate-500') }}">
                            {{ $s5Done ? 'تەواوە' : ($activeStep === 5 ? 'لە کاردایە' : 'چاوەڕێیە') }}
                        </span>
                    </div>
                    <h4 class="text-xs font-black {{ $activeStep === 5 ? 'text-blue-300' : 'text-white' }} block">٥. بەشی دەفتەر</h4>
                    <p class="text-[11px] text-slate-400 mt-1 font-mono truncate">
                        <i class="fa-solid fa-passport text-[9px] text-slate-500 ml-1"></i>{{ $transaction->booklet_number ? 'دەفتەر: ' . $transaction->booklet_number : 'تۆمارکردن' }}
                    </p>
                </div>
            @endif

            <!-- Step 6 (or Step 5): Submit & Archive -->
            <div class="p-3.5 rounded-2xl border transition relative {{ $s6Done ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 shadow-sm' : ($activeStep === ($requiresBooklet ? 6 : 5) ? 'bg-gradient-to-b from-indigo-500/15 to-slate-900 border-indigo-500/60 text-indigo-200 ring-2 ring-indigo-500/30 shadow-lg' : 'bg-slate-900/60 border-slate-800 text-slate-500') }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black {{ $s6Done ? 'bg-emerald-500 text-slate-950 shadow-sm' : ($activeStep === ($requiresBooklet ? 6 : 5) ? 'bg-indigo-600 text-white animate-pulse shadow-md shadow-indigo-600/40' : 'bg-slate-800 text-slate-400') }}">
                        @if($s6Done)
                            <i class="fa-solid fa-check text-[10px]"></i>
                        @elseif($activeStep === ($requiresBooklet ? 6 : 5))
                            <i class="fa-solid fa-arrow-down text-[10px]"></i>
                        @else
                            {{ $requiresBooklet ? '٦' : '٥' }}
                        @endif
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $s6Done ? 'bg-emerald-500/20 text-emerald-300' : ($activeStep === ($requiresBooklet ? 6 : 5) ? 'bg-indigo-500/20 text-indigo-300 font-black' : 'bg-slate-800 text-slate-500') }}">
                        {{ $s6Done ? 'تەواوە' : ($activeStep === ($requiresBooklet ? 6 : 5) ? 'لە کاردایە' : 'چاوەڕێیە') }}
                    </span>
                </div>
                <h4 class="text-xs font-black {{ $activeStep === ($requiresBooklet ? 6 : 5) ? 'text-indigo-300' : 'text-white' }} block">{{ $requiresBooklet ? '٦. سەبمیت و ئەرشیف' : '٥. سەبمیت و ئەرشیف' }}</h4>
                <p class="text-[11px] text-slate-400 mt-1 truncate">
                    <i class="fa-solid fa-box-archive text-[9px] text-slate-500 ml-1"></i>{{ $transaction->submitted_by ?: 'ئەرشیف' }}
                </p>
            </div>
        </div>

        <!-- Interactive Workflow Action Controls per Employee Role -->
        @if(!$transaction->is_cancelled)
            <div class="pt-4 border-t border-slate-700 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">

                    <!-- فەرمانبەری یاسایی: چاپی بەڵێننامە (ڕاستەوخۆ دوای داتائەنتەری) -->
                    @if(auth()->user()->isAdmin() || auth()->user()->isLegal())
                        <a href="{{ route('transactions.print_pledge', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-black rounded-xl shadow-lg shadow-amber-600/30 transition flex items-center gap-1.5" title="چاپی بەڵێننامەی یاسایی لەلایەن فەرمانبەری یاسایی">
                            <i class="fa-solid fa-file-contract text-amber-200"></i> چاپی بەڵێننامەی یاسایی
                        </a>
                    @endif

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

                            <!-- چاپی دوو نوسراوە ئیدارییەکە لە قۆناغی وردبینی (نیشانەی ڕەفتارنەکردن & نووسراوی پووچەڵکردنەوە) -->
                            @if($transaction->isCancellation())
                                <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-rose-700 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow transition flex items-center" title="چاپی نووسراوی پووچەڵکردنەوەی دەفتەر لە قۆناغی وردبینی">
                                    <i class="fa-solid fa-file-circle-xmark ml-1.5"></i> نووسراوی پووچەڵکردنەوە
                                </a>
                                <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-600/40 text-xs font-bold rounded-xl transition flex items-center" title="چاپی نووسراوی دانانی نیشانەی ڕەفتارنەکردن">
                                    <i class="fa-solid fa-file-circle-exclamation ml-1.5"></i> نیشانەی ڕەفتارنەکردن
                                </a>
                            @else
                                <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center" title="چاپی نووسراوی دانانی نیشانەی ڕەفتارنەکردن لە قۆناغی وردبینی">
                                    <i class="fa-solid fa-file-circle-exclamation ml-1.5"></i> نیشانەی ڕەفتارنەکردن
                                </a>
                                <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-rose-300 border border-rose-600/40 text-xs font-bold rounded-xl transition flex items-center" title="چاپی نووسراوی پووچەڵکردنەوەی دەفتەر">
                                    <i class="fa-solid fa-file-circle-xmark ml-1.5"></i> نووسراوی پووچەڵکردنەوە
                                </a>
                            @endif

                            <button type="button" @click="showReturnModal = true" class="px-4 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/20 transition flex items-center">
                                <i class="fa-solid fa-rotate-left ml-1.5"></i> گەڕاندنەوە بۆ تەخمین (هەڵە هەیە)
                            </button>
                        @else
                            <span class="px-3 py-1.5 bg-purple-500/10 text-purple-300 border border-purple-500/30 rounded-xl text-xs font-bold flex items-center">
                                <i class="fa-solid fa-clock ml-1.5 text-purple-400"></i> چاوەڕێی پەسەندکردنی وردبینییە (تەنها کارمەندی وردبین دەتوانێت پەسەندی بکات)
                            </span>
                        @endif
                    @endif

                    <!-- If Audited, but before Payment: Auditor/Admin can Revert Audit or Return to Previous Step -->
                    @if($transaction->is_audited && !$transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->role === 'auditor' || auth()->user()->hasPermission('transactions.audit')))
                        <!-- دوو نوسراوە ئیدارییەکە پاش پەسەندکردنی وردبینی (نیشانەی ڕەفتارنەکردن & نووسراوی پووچەڵکردنەوە) -->
                        @if($transaction->isCancellation())
                            <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-rose-700 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow transition flex items-center" title="چاپی نووسراوی پووچەڵکردنەوە">
                                <i class="fa-solid fa-file-circle-xmark ml-1.5"></i> نووسراوی پووچەڵکردنەوە
                            </a>
                            <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-600/40 text-xs font-bold rounded-xl transition flex items-center" title="چاپی نووسراوی دانانی نیشانەی ڕەفتارنەکردن">
                                <i class="fa-solid fa-file-circle-exclamation ml-1.5"></i> نیشانەی ڕەفتارنەکردن
                            </a>
                        @else
                            <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center" title="چاپی نووسراوی دانانی نیشانەی ڕەفتارنەکردن">
                                <i class="fa-solid fa-file-circle-exclamation ml-1.5"></i> نیشانەی ڕەفتارنەکردن
                            </a>
                            <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-rose-300 border border-rose-600/40 text-xs font-bold rounded-xl transition flex items-center" title="چاپی نووسراوی پووچەڵکردنەوە">
                                <i class="fa-solid fa-file-circle-xmark ml-1.5"></i> نووسراوی پووچەڵکردنەوە
                            </a>
                        @endif

                        <form action="{{ route('transactions.revert_audit', $transaction->id) }}" method="POST" class="inline" onsubmit="return confirm('ئایا دڵنیایت لە هەڵوەشاندنەوەی پەسەندی وردبینی؟ مامەڵەکە دەگەڕێتەوە بۆ چاوەڕوانی وردبینی.');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-purple-700 hover:bg-purple-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-purple-700/25 transition flex items-center">
                                <i class="fa-solid fa-arrow-rotate-left ml-1.5 text-purple-200"></i> هەڵوەشاندنەوەی پەسەندی وردبینی (Revert Audit)
                            </button>
                        </form>

                        <button type="button" @click="showReturnModal = true" class="px-4 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/20 transition flex items-center">
                            <i class="fa-solid fa-arrow-turn-down-left ml-1.5"></i> گەڕاندنەوەی هەڵە بۆ ستێپی پێشتر (تەخمین / داتائەنتەری)
                        </button>
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

                    <!-- قۆناغی پارەدان - دوای وەرگرتنی پارە (چاپی پسوولە و نووسراوی پشکنین بۆ ژمێریار و ئەدمین) -->
                    @if($transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->role === 'cashier'))
                        @if(!$transaction->is_submitted || auth()->user()->isAdmin())
                            <a href="{{ route('transactions.print_payment_receipt', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center">
                                <i class="fa-solid fa-receipt ml-1.5"></i> چاپی پسوولەی ۳۷/أ
                            </a>
                            <a href="{{ route('transactions.print_inspection_letter', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center" title="چاپی نووسراوی پشکنین لە قۆناغی پارەدان">
                                <i class="fa-solid fa-file-circle-check ml-1.5"></i> چاپی نووسراوی پشکنین
                            </a>
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
                            @if((!$transaction->is_submitted || auth()->user()->isAdmin()) && auth()->user()->role !== 'data_entry' && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet' || auth()->user()->hasPermission('transactions.print_booklet')))
                                <a href="{{ route('transactions.print_booklet', $transaction->id) }}" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center">
                                    <i class="fa-solid fa-passport ml-1.5"></i> چاپی دەفتەر ({{ $transaction->booklet_number }})
                                </a>
                            @endif
                            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.complete_booklet'))
                                <button @click="showBookletModal = true" class="px-2.5 py-2 bg-slate-800 text-slate-300 hover:text-amber-300 border border-slate-700 text-xs font-bold rounded-xl transition" title="دەستکاریکردنی ژمارەی دەفتەر">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            @endif
                        @endif
                    @endif

                    <!-- Final Submit & Archive Button (Archiver, Booklet, Admin - Excluded for data_entry) -->
                    @if($transaction->is_paid && (!$requiresBooklet || $transaction->is_booklet_completed) && auth()->user()->role !== 'data_entry' && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->role === 'booklet' || auth()->user()->hasPermission('transactions.submit')))
                        @if(!$transaction->is_submitted)
                            <button type="button" @click="showSubmitModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center">
                                <i class="fa-solid fa-box-archive ml-1.5"></i> {{ $requiresBooklet ? 'ستێپی ٦: سەبمیت و ئەرشیفکردنی مامەڵە' : 'ستێپی ٥: سەبمیت و ئەرشیفکردنی مامەڵە' }}
                            </button>
                        @else
                            <button type="button" @click="showSubmitModal = true" class="px-3 py-2 bg-slate-800 text-indigo-300 hover:text-white border border-indigo-500/40 text-xs font-bold rounded-xl transition flex items-center">
                                <i class="fa-solid fa-pen-to-square ml-1.5"></i> دەستکاری زانیاری ئەرشیف
                            </button>
                        @endif
                    @endif
                </div>

                <!-- Cancel Transaction Button - Admin ONLY (خاڵی ۳) -->
                @if(auth()->check() && auth()->user()->isAdmin())
                    <button @click="showCancelModal = true" class="px-3 py-2 bg-rose-950/60 hover:bg-rose-900 border border-rose-600/40 text-rose-300 text-xs font-bold rounded-xl transition flex items-center">
                        <i class="fa-solid fa-ban ml-1.5"></i> پووچەڵکردنەوەی مامەڵە (ئەدمین)
                    </button>
                @endif
            </div>
        @endif
    </div>

    <!-- Transaction Details Grid (Visible to all users) -->
    <!-- Transaction Details Grid (Visible to all users & Auditor) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Vehicle & Drivers Card (Highlighted for Auditor) -->
        <div class="bg-slate-800/80 rounded-2xl p-6 shadow-xl space-y-4 transition {{ (auth()->user()->role === 'auditor' || $activeStep === 3) ? 'border-2 border-purple-500/80 ring-2 ring-purple-500/20' : 'border border-slate-700/80' }}">
            <h3 class="text-sm font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center justify-between">
                <span class="flex items-center">
                    <i class="fa-solid fa-id-card ml-2"></i> زانیارییەکانی شۆفێر و هاووڵاتی (وردبینی زانیاری)
                    @if(auth()->user()->role === 'auditor' || $activeStep === 3)
                        <span class="mr-2 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/40">
                            <i class="fa-solid fa-magnifying-glass-chart ml-1"></i> فۆکەسی وردبینی
                        </span>
                    @endif
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

                @if($transaction->passport_number)
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-700/40">
                        <span class="text-slate-400 flex items-center">
                            <i class="fa-solid fa-passport ml-1.5 text-amber-400 text-xs"></i> ژمارەی پاسپۆرت:
                        </span>
                        <span class="font-mono text-amber-300 font-bold uppercase" dir="ltr">{{ $transaction->passport_number }}</span>
                    </div>
                @endif

                @if($transaction->address)
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-700/40">
                        <span class="text-slate-400 flex items-center">
                            <i class="fa-solid fa-map-location-dot ml-1.5 text-sky-400 text-xs"></i> ناونیشان:
                        </span>
                        <span class="font-bold text-slate-100">{{ $transaction->address }}</span>
                    </div>
                @endif

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

                <!-- ژمارەی بۆکسی دەرهێنان و بۆکسی پووچەڵ -->
                <div class="flex justify-between items-center py-1.5 border-b border-slate-700/40 bg-slate-900/40 px-2 rounded">
                    <span class="text-slate-300 font-bold flex items-center">
                        <i class="fa-solid fa-box text-sky-400 ml-1.5"></i> ژمارەی بۆکسی دەرهێنان:
                    </span>
                    <span class="font-mono text-sky-300 font-black text-sm">{{ $transaction->NO_boxfile ?? 'دیارینەکراوە' }}</span>
                </div>

                @if($transaction->is_cancelled || $transaction->NO_boxfile_puchal)
                    <div class="flex justify-between items-center py-1.5 border-b border-rose-500/40 bg-rose-950/30 px-2 rounded">
                        <span class="text-rose-300 font-bold flex items-center">
                            <i class="fa-solid fa-box-archive text-rose-400 ml-1.5"></i> ژمارەی بۆکسی پووچەڵ:
                        </span>
                        <span class="font-mono text-rose-300 font-black text-sm">{{ $transaction->NO_boxfile_puchal ?? 'دیارینەکراوە' }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center py-1.5 border-b border-slate-700/40">
                    <span class="text-slate-400">ژمارەی تابلۆ:</span>
                    <div>
                        <x-vehicle-plate :plate="$transaction->plate_number" :type="$transaction->plateType" size="sm" />
                    </div>
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

        <!-- Financial & Fee Breakdown Card (Highlighted for Cashier) -->
        <div class="bg-slate-800/80 rounded-2xl p-6 shadow-xl space-y-4 transition {{ (auth()->user()->role === 'cashier' || $activeStep === 4) ? 'border-2 border-emerald-500/80 ring-2 ring-emerald-500/20' : 'border border-slate-700/80' }}">
            <h3 class="text-sm font-bold text-emerald-400 border-b border-slate-700 pb-2 flex items-center justify-between">
                <span class="flex items-center">
                    <i class="fa-solid fa-receipt ml-2"></i> وردەکاری دارایی و نرخەکان
                    @if(auth()->user()->role === 'cashier' || $activeStep === 4)
                        <span class="mr-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                            <i class="fa-solid fa-hand-holding-dollar ml-1"></i> فۆکەسی وەسڵبڕین
                        </span>
                    @endif
                </span>
                @if($transaction->is_paid)
                    <span class="text-xs font-mono font-bold text-emerald-300 bg-emerald-950 px-2 py-0.5 rounded border border-emerald-800">
                        وەسڵی ۳۷/أ: {{ $transaction->receipt_37a_number }}
                    </span>
                @endif
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
            <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fa-solid fa-receipt ml-2 text-emerald-400"></i> تۆمارکردنی وەسڵی ۳۷/أ و پارەدان
                </h3>
                <button type="button" @click="showPayModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- جۆری مامەڵە بە بارزی لە مۆداڵی پارەدان -->
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] }}">
                        <i class="{{ $typeTheme['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                        <span class="text-base font-black text-white">{{ $typeName }}</span>
                    </div>
                </div>
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-black/50 text-sky-300 border border-slate-700">
                    {{ $transaction->barcode }}
                </span>
            </div>

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
            <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fa-solid fa-book-bookmark ml-2 text-blue-400"></i> تۆمارکردنی ژمارەی دەفتەر
                </h3>
                <button type="button" @click="showBookletModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- جۆری مامەڵە بە بارزی لە مۆداڵی دەفتەر -->
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] }}">
                        <i class="{{ $typeTheme['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                        <span class="text-base font-black text-white">{{ $typeName }}</span>
                    </div>
                </div>
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-black/50 text-sky-300 border border-slate-700">
                    {{ $transaction->barcode }}
                </span>
            </div>

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

    <!-- MODAL 3: Cancellation Modal - Admin ONLY (خاڵی ۳) -->
    @if(auth()->check() && auth()->user()->isAdmin())
    <div x-show="showCancelModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                <h3 class="text-lg font-bold text-rose-400 flex items-center">
                    <i class="fa-solid fa-ban ml-2"></i> پووچەڵکردنەوەی مامەڵە (تەنها ئەدمین)
                </h3>
                <button type="button" @click="showCancelModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- جۆری مامەڵە بە بارزی -->
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] }}">
                        <i class="{{ $typeTheme['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                        <span class="text-base font-black text-white">{{ $typeName }}</span>
                    </div>
                </div>
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-black/50 text-sky-300 border border-slate-700">
                    {{ $transaction->barcode }}
                </span>
            </div>

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
    @endif

    <!-- MODAL 4: Auditor Return Modal -->
    <div x-show="showReturnModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-base font-bold text-rose-400 flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-rotate-left ml-2"></i> گەڕاندنەوەی مامەڵە بۆ تەخمین / داتائەنتەری</span>
                <button @click="showReturnModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <!-- جۆری مامەڵە بە بارزی -->
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] }}">
                        <i class="{{ $typeTheme['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                        <span class="text-base font-black text-white">{{ $typeName }}</span>
                    </div>
                </div>
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-black/50 text-sky-300 border border-slate-700">
                    {{ $transaction->barcode }}
                </span>
            </div>

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
        <div class="bg-slate-900 border border-amber-500/50 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl"
             x-data="{ revertStep: 'payment' }">
            <h3 class="text-base font-bold text-amber-400 flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-arrow-rotate-left ml-2"></i> هەڵوەشاندنەوە و ڕێکخستنەوەی قۆناغ (Admin Step Reversion)</span>
                <button @click="showRevertModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <!-- جۆری مامەڵە بە بارزی -->
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] }}">
                        <i class="{{ $typeTheme['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                        <span class="text-base font-black text-white">{{ $typeName }}</span>
                    </div>
                </div>
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-black/50 text-sky-300 border border-slate-700">
                    {{ $transaction->barcode }}
                </span>
            </div>
            <form action="{{ route('transactions.revert_step', $transaction->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">کام قۆناغ دەتەوێت هەڵبوەشێنیتەوە (Reset)؟ *</label>
                    <select name="step" x-model="revertStep" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100 font-bold focus:border-amber-500 focus:outline-none">
                        <option value="payment">١. هەڵوەشاندنەوەی وەسڵ و پارەدان (گەڕاندنەوە بۆ وردبینی & پووچەڵکردنی پسوولەی 37/أ)</option>
                        <option value="booklet">٢. هەڵوەشاندنەوەی دەفتەر (پووچەڵکردن و سڕینەوەی ژمارەی دەفتەر)</option>
                        <option value="audit">٣. هەڵوەشاندنەوەی وردبینی (گەڕاندنەوە بۆ قۆناغی پێشتر)</option>
                        <option value="inspection">٤. هەڵوەشاندنەوەی کەشف و تەخمین (گەڕاندنەوە بۆ سەرەتا)</option>
                        <option value="archive">٥. هەڵوەشاندنەوەی سەبمیت و ئەرشیف (کردنەوەی مامەڵە بۆ چاپی ئاسایی)</option>
                        <option value="all">٦. سەرجەم قۆناغەکان بگەڕێنەرەوە بۆ سەرەتا (داتائەنتەری)</option>
                    </select>
                </div>

                <!-- Input for Receipt 37-A when reverting payment -->
                <div x-show="revertStep === 'payment'" class="p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl space-y-1.5">
                    <label class="block text-xs font-bold text-amber-300">
                        <i class="fa-solid fa-receipt ml-1"></i> ژمارەی پسوولەی ۳۷/أ بۆ هەڵوەشاندنەوە *
                    </label>
                    <input type="text" name="receipt_number" :required="revertStep === 'payment'"
                           value="{{ $transaction->receipt_37a_number }}"
                           placeholder="ژمارەی پسوولەی ۳۷/أ بنووسە..."
                           class="w-full bg-slate-800 border border-amber-500/50 rounded-xl px-3 py-2 text-xs font-mono text-amber-200 focus:border-amber-400 focus:outline-none">
                    <p class="text-[11px] text-amber-400/90 leading-relaxed">
                        * ئەم ژمارە پسوولەیە پووچەڵ (بلۆک) دەکرێت و جارێکی تر لە سیستەم بەکارناهێنرێتەوە.
                    </p>
                </div>

                <!-- Input for Booklet Number when reverting booklet -->
                <div x-show="revertStep === 'booklet'" class="p-3 bg-blue-500/10 border border-blue-500/30 rounded-xl space-y-1.5">
                    <label class="block text-xs font-bold text-blue-300">
                        <i class="fa-solid fa-passport ml-1"></i> ژمارەی دەفتەر بۆ هەڵوەشاندنەوە *
                    </label>
                    <input type="text" name="booklet_number" :required="revertStep === 'booklet'"
                           value="{{ $transaction->booklet_number }}"
                           placeholder="ژمارەی دەفتەر بنووسە..."
                           class="w-full bg-slate-800 border border-blue-500/50 rounded-xl px-3 py-2 text-xs font-mono text-blue-200 focus:border-blue-400 focus:outline-none">
                    <p class="text-[11px] text-blue-400/90 leading-relaxed">
                        * ژمارەی ئەم دەفتەرە لەسەر مامەڵەکە دەسڕێتەوە و پووچەڵ دەکرێتەوە.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">هۆکاری هەڵوەشاندنەوە و تێبینی ئەدمین *</label>
                    <textarea name="reversion_reason" required rows="3" placeholder="تکایە هۆکاری هەڵوەشاندنەوە بە ڕوونی بنووسە..." 
                              class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-xs text-slate-100 focus:border-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Info Notice Box -->
                <div class="p-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-[11px] text-slate-300 space-y-1">
                    <div class="flex items-center gap-1.5 text-amber-300 font-bold">
                        <i class="fa-solid fa-triangle-exclamation"></i> تێبینییە یاسایی و کارگێڕییەکان:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-300">
                        <li x-show="revertStep === 'payment'">لە کاتی هەڵوەشاندنەوەی پارەدان، <strong>قۆناغی وردبینی چالاک دەبێتەوە</strong> بۆ سەرلەنوێ وردبینیکردن.</li>
                        <li x-show="revertStep === 'booklet'">لە کاتی هەڵوەشاندنەوەی دەفتەر، ژمارەی دەفتەر دەسڕێتەوە و ئامادە دەبێتەوە بۆ پێدانی دەفتەری نوێ.</li>
                        @if($transaction->is_submitted)
                            <li class="text-rose-300 font-bold">ئەم مامەڵەیە پێشتر سەبمیت کراوە؛ بە هەڵوەشاندنەوەی ئەم قۆناغە دەبێت لە کۆتاییدا <strong>سەرلەنوێ سەبمیت بکرێتەوە</strong>.</li>
                        @endif
                    </ul>
                </div>

                <div class="flex items-center justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showRevertModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl">پاشگەزبوونەوە</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-lg">پەسەندکردن و هەڵوەشاندنەوە</button>
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

            <!-- جۆری مامەڵە بە بارزی لە مۆداڵی وردبینی -->
            <div class="p-3.5 rounded-xl border-2 {{ $typeTheme['border'] }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] }}">
                        <i class="{{ $typeTheme['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">جۆری مامەڵەی ژێر وردبینی:</span>
                        <span class="text-lg font-black text-white">{{ $typeName }}</span>
                    </div>
                </div>
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-black/50 text-sky-300 border border-slate-700">
                    {{ $transaction->barcode }}
                </span>
            </div>

            <!-- Transaction Details Summary Card -->
            <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-4 space-y-2.5">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">ناوی هاوڵاتی:</span>
                    <span class="text-slate-100 font-bold text-sm">{{ $transaction->visitor_name }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">ژمارەی تابلۆ:</span>
                    <span class="font-mono text-sky-400 font-bold" dir="ltr">{{ $transaction->plate_number }}</span>
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

    <!-- MODAL 7: Submit & Archive Modal (سەبمیت و ئەرشیفکردنی مامەڵە) -->
    <div x-show="showSubmitModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-indigo-500/50 rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl" @click.away="showSubmitModal = false">
            <div class="flex items-center justify-between border-b border-slate-700/80 pb-3">
                <h3 class="text-base font-black text-indigo-400 flex items-center">
                    <i class="fa-solid fa-box-archive ml-2 text-lg"></i>
                    سەبمیت و ئەرشیفکردنی مامەڵە (قۆناغی کۆتایی)
                </h3>
                <button @click="showSubmitModal = false" class="text-slate-400 hover:text-slate-200 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- جۆری مامەڵە بە بارزی لە مۆداڵی سەبمیت و ئەرشیف -->
            <div class="p-3.5 rounded-xl border-2 {{ $typeTheme['border'] }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] }}">
                        <i class="{{ $typeTheme['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                        <span class="text-lg font-black text-white">{{ $typeName }}</span>
                    </div>
                </div>
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-black/50 text-sky-300 border border-slate-700">
                    {{ $transaction->barcode }}
                </span>
            </div>

            <!-- Transaction Summary -->
            <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-3.5 space-y-2 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">ناوی هاووڵاتی:</span>
                    <span class="text-slate-100 font-bold text-sm">{{ $transaction->visitor_name }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">ژمارەی تابلۆ:</span>
                    <span class="font-mono text-sky-400 font-bold" dir="ltr">{{ $transaction->plate_number }}</span>
                </div>
                @if($transaction->booklet_number)
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">ژمارەی دەفتەر:</span>
                        <span class="font-mono text-emerald-300 font-bold">{{ $transaction->booklet_number }}</span>
                    </div>
                @endif
            </div>

            <form action="{{ route('transactions.submit', $transaction->id) }}" method="POST" class="space-y-4">
                @csrf

                @if((int)$transaction->transaction_type_id === 1)
                    <!-- Type 1: دەرهێنانی دەفتەر -> ژمارەی بۆکسی دەرهێنان -->
                    <div class="p-4 bg-indigo-950/40 border border-indigo-500/40 rounded-xl space-y-3">
                        <div class="flex items-center gap-2 text-indigo-300 text-xs font-bold">
                            <i class="fa-solid fa-box text-base"></i>
                            <span>تایبەت بە [دەرهێنانی دەفتەر]: تکایە ژمارەی بۆکسی دەرهێنان بنووسە</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-200 mb-1.5">
                                ژمارەی بۆکسی دەرهێنان (Box File Number) *
                            </label>
                            <input type="number" name="NO_boxfile" required min="1"
                                   value="{{ old('NO_boxfile', $transaction->NO_boxfile) }}"
                                   placeholder="نموونە: 125" dir="ltr"
                                   class="w-full bg-slate-950 border border-indigo-500/60 focus:border-indigo-400 rounded-xl px-3.5 py-2.5 text-sm font-mono font-bold text-slate-100 text-center tracking-wider focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                    </div>
                @elseif((int)$transaction->transaction_type_id === 5)
                    <!-- Type 5: پووچەڵکردنەوەی دەفتەر -> نووسراوی گومرگ و بەروار و بۆکسی پووچەڵ -->
                    <div class="p-4 bg-rose-950/40 border border-rose-500/40 rounded-xl space-y-3">
                        <div class="flex items-center gap-2 text-rose-300 text-xs font-bold">
                            <i class="fa-solid fa-file-contract text-base"></i>
                            <span>تایبەت بە [پووچەڵکردنەوەی دەفتەر]: زانیاری نووسراوی گومرگ و بۆکسی پووچەڵ بنووسە</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-200 mb-1">ژمارەی نووسراوی گومرگ *</label>
                                <input type="text" name="no_nusraw_puchal" required
                                       value="{{ old('no_nusraw_puchal', $transaction->no_nusraw_puchal) }}"
                                       placeholder="ژمارەی فەرمی نووسراو" dir="ltr"
                                       class="w-full bg-slate-950 border border-rose-500/60 focus:border-rose-400 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500/30">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-200 mb-1">بەرواری نووسراوی گومرگ *</label>
                                <input type="date" name="date_nusraw_puchal" required
                                       value="{{ old('date_nusraw_puchal', $transaction->date_nusraw_puchal?->format('Y-m-d') ?? date('Y-m-d')) }}"
                                       class="w-full bg-slate-950 border border-rose-500/60 focus:border-rose-400 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500/30">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-200 mb-1">ژمارەی بۆکسی پووچەڵ (Box File Cancellation) *</label>
                            <input type="number" name="NO_boxfile_puchal" required min="1"
                                   value="{{ old('NO_boxfile_puchal', $transaction->NO_boxfile_puchal) }}"
                                   placeholder="نموونە: 48" dir="ltr"
                                   class="w-full bg-slate-950 border border-rose-500/60 focus:border-rose-400 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-100 text-center tracking-wider focus:outline-none focus:ring-2 focus:ring-rose-500/30">
                        </div>
                    </div>
                @else
                    <!-- Other types: optional boxfile numbers -->
                    <div class="p-4 bg-slate-800/80 border border-slate-700 rounded-xl space-y-3">
                        <p class="text-xs text-slate-300">سەرجەم قۆناغەکانی ئەم مامەڵەیە تەواو بوون. دەتوانیت ژمارەی بۆکس تۆمار بکەیت و مامەڵەکە بە تەواوەتی سەبمیت و ئەرشیف بکەیت:</p>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">ژمارەی بۆکس (ئارەزوومەندانە)</label>
                            <input type="number" name="NO_boxfile" min="1"
                                   value="{{ old('NO_boxfile', $transaction->NO_boxfile) }}"
                                   placeholder="ژمارەی بۆکس..." dir="ltr"
                                   class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-100 text-center focus:outline-none">
                        </div>
                    </div>
                @endif

                <div class="p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl text-[11px] text-amber-300 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-amber-400 text-sm"></i>
                    <span>تێبینی: دوای ئەنجامدانی سەبمیت، مامەڵەکە ئەرشیف دەبێت و چاپەکان تەنها لە دەسەڵاتی ئەدمین دەمێننەوە.</span>
                </div>

                <div class="flex items-center justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showSubmitModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl transition">پاشگەزبوونەوە</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>پەسەندکردن و سەبمیتکردنی کۆتایی</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sticky Bottom Action Bar for Easy Employee Operations (هەڵواسراوی خێرای قۆناغەکان بۆ کارمەندان) -->
    <div class="fixed bottom-0 inset-x-0 z-40 bg-slate-900/95 backdrop-blur-md border-t border-slate-700/80 px-4 py-3 shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.5)]">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <!-- لای ڕاست: کورتەی مامەڵە و ناسنامە -->
            <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
                <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-sky-400 bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-800 text-xs">
                        <i class="fa-solid fa-barcode text-[10px] ml-1"></i>{{ $transaction->barcode }}
                    </span>
                    <!-- جۆری مامەڵە بە بارزی لە باری خێرای قۆناغەکان -->
                    <span class="px-2.5 py-1 rounded-lg text-xs font-black border flex items-center gap-1.5 {{ $typeTheme['pill'] }}" title="جۆری مامەڵە">
                        <i class="{{ $typeTheme['icon'] }} text-[10px]"></i>
                        <span>{{ $typeName }}</span>
                    </span>
                    <x-vehicle-plate :plate="$transaction->plate_number" :type="$transaction->plateType" size="sm" />
                </div>
                <div class="hidden lg:block text-xs text-slate-300">
                    <span class="font-bold text-white">{{ $transaction->visitor_name }}</span>
                    <span class="text-slate-500 mr-1 font-mono">({{ $transaction->carMake->name_kurdish ?? '' }})</span>
                </div>
            </div>

            <!-- ناوەڕاست: دۆخی قۆناغی ئێستا و بڕی پارە -->
            <div class="flex items-center gap-2 shrink-0">
                @if($isCancelled)
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 flex items-center gap-1">
                        <i class="fa-solid fa-ban"></i> مامەڵە پووچەڵکراوەتەوە
                    </span>
                @elseif($isSubmitted)
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                        <i class="fa-solid fa-circle-check"></i> تەواوبوو و ئەرشیفکراوە
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30 flex items-center gap-1.5 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></span>
                        <span>قۆناغ: <strong class="text-white">{{ $transaction->getCurrentStageName() }}</strong></span>
                    </span>
                @endif

                <span class="px-2.5 py-1 bg-slate-950 text-emerald-400 border border-slate-800 rounded-lg text-xs font-mono font-extrabold hidden sm:inline-block">
                    {{ number_format($transaction->total_pay) }} د.ع
                </span>
            </div>

            <!-- لای چەپ: بەتنە سەرەکییەکانی کارکردن بەپێی قۆناغ و دەسەڵاتی کارمەند -->
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap">
                @if(!$isCancelled)
                    <!-- 1. کەشف و تەخمین (Inspector / Admin) -->
                    @if(!$transaction->is_inspected && (auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.inspect')))
                        <form action="{{ route('transactions.inspect', $transaction->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white text-xs font-black rounded-xl shadow-lg shadow-orange-600/30 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <span>پەسەندکردنی تەخمین</span>
                            </button>
                        </form>
                        <button type="button" @click="showReturnModal = true" class="px-3 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1">
                            <i class="fa-solid fa-rotate-left"></i> گەڕاندنەوە
                        </button>
                    @endif

                    <!-- 2. چاپی پسولەی کەشف (تەنها داتائەنتەری، تەخمین و ئەدمین - قفڵکراوە دوای سەبمیت) -->
                    @if($transaction->is_inspected && (!$transaction->is_submitted || auth()->user()->isAdmin()) && !$transaction->is_audited && (auth()->user()->isAdmin() || in_array(auth()->user()->role, ['data_entry', 'inspector'])))
                        <a href="{{ route('transactions.print_receipt', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1">
                            <i class="fa-solid fa-print"></i> چاپی کەشف
                        </a>
                    @endif

                    <!-- 3. پەسەندکردنی وردبینی (Auditor / Admin) -->
                    @if($transaction->is_inspected && !$transaction->is_audited && (auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.audit')))
                        <button type="button" @click="showAuditModal = true" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-black rounded-xl shadow-lg shadow-purple-600/30 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span>پەسەندکردنی وردبینی</span>
                        </button>
                        <button type="button" @click="showReturnModal = true" class="px-3 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1">
                            <i class="fa-solid fa-rotate-left"></i> گەڕاندنەوە
                        </button>
                    @endif

                    <!-- نوسراوە ئیدارییەکان بۆ وردبینی (نیشانەی ڕەفتارنەکردن & نووسراوی پووچەڵکردنەوە) -->
                    @if(auth()->user()->isAdmin() || auth()->user()->isLegal())
                        <a href="{{ route('transactions.print_pledge', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-black rounded-xl shadow transition flex items-center gap-1" title="چاپی بەڵێننامەی یاسایی">
                            <i class="fa-solid fa-file-contract"></i> بەڵێننامە (یاسایی)
                        </a>
                    @endif

                    @if($transaction->is_inspected && (!$transaction->is_submitted || auth()->user()->isAdmin()) && auth()->user()->role !== 'data_entry' && (auth()->user()->isAdmin() || auth()->user()->role === 'auditor' || auth()->user()->role === 'booklet'))
                        @if($transaction->isCancellation())
                            <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-rose-700 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1" title="چاپی نووسراوی پووچەڵکردنەوە">
                                <i class="fa-solid fa-file-circle-xmark"></i> نووسراوی پووچەڵکردنەوە
                            </a>
                            <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-600/40 text-xs font-bold rounded-xl transition flex items-center gap-1" title="چاپی نووسراوی دانانی نیشانەی ڕەفتارنەکردن">
                                <i class="fa-solid fa-file-circle-exclamation"></i> نیشانەی ڕەفتارنەکردن
                            </a>
                        @else
                            <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1" title="چاپی نووسراوی دانانی نیشانەی ڕەفتارنەکردن">
                                <i class="fa-solid fa-file-circle-exclamation"></i> نیشانەی ڕەفتارنەکردن
                            </a>
                            <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-rose-300 border border-rose-600/40 text-xs font-bold rounded-xl transition flex items-center gap-1" title="چاپی نووسراوی پووچەڵکردنەوە">
                                <i class="fa-solid fa-file-circle-xmark"></i> نووسراوی پووچەڵکردنەوە
                            </a>
                        @endif
                    @endif

                    <!-- 4. وەرگرتنی پارە و وەسڵی ۳۷/أ (Cashier / Admin) -->
                    @if($transaction->is_audited && !$transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.pay')))
                        <button type="button" @click="showPayModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-receipt"></i>
                            <span>وەرگرتنی پارە (۳۷/أ)</span>
                        </button>
                    @endif

                    <!-- 5. چاپی وەسڵی ۳۷/أ و نووسراوی پشکنین (Cashier / Admin - قفڵکراوە دوای سەبمیت) -->
                    @if($transaction->is_paid && (!$transaction->is_submitted || auth()->user()->isAdmin()) && (auth()->user()->isAdmin() || auth()->user()->role === 'cashier'))
                        <a href="{{ route('transactions.print_payment_receipt', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow transition flex items-center gap-1">
                            <i class="fa-solid fa-receipt"></i> چاپی ۳۷/أ
                        </a>
                        <a href="{{ route('transactions.print_inspection_letter', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1" title="چاپی نووسراوی پشکنین">
                            <i class="fa-solid fa-file-circle-check"></i> نوسراوی پشکنین
                        </a>
                    @endif

                    <!-- 6. تۆمارکردنی دەفتەر (Booklet / Admin) -->
                    @if($requiresBooklet && $transaction->is_paid && (!$transaction->is_booklet_completed || empty($transaction->booklet_number)) && (auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.complete_booklet')))
                        <button type="button" @click="showBookletModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-black rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-book-bookmark"></i>
                            <span>تۆمارکردنی ژمارەی دەفتەر</span>
                        </button>
                    @endif

                    <!-- 7. چاپی دەفتەر (Booklet / Admin - Excluded for data_entry - قفڵکراوە دوای سەبمیت) -->
                    @if($requiresBooklet && $transaction->is_paid && $transaction->is_booklet_completed && !empty($transaction->booklet_number) && (!$transaction->is_submitted || auth()->user()->isAdmin()) && auth()->user()->role !== 'data_entry' && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet'))
                        <a href="{{ route('transactions.print_booklet', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1">
                            <i class="fa-solid fa-passport"></i> چاپی دەفتەر ({{ $transaction->booklet_number }})
                        </a>
                    @endif

                    <!-- 8. سەبمیت و ئەرشیف (Archiver / Admin / Booklet - Excluded for data_entry) -->
                    @if($transaction->is_paid && (!$requiresBooklet || $transaction->is_booklet_completed) && auth()->user()->role !== 'data_entry' && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->role === 'booklet' || auth()->user()->hasPermission('transactions.submit')))
                        @if(!$transaction->is_submitted)
                            <button type="button" @click="showSubmitModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-box-archive"></i>
                                <span>سەبمیت و ئەرشیف</span>
                            </button>
                        @else
                            <button type="button" @click="showSubmitModal = true" class="px-3 py-2 bg-slate-800 text-indigo-300 hover:text-white border border-indigo-500/40 text-xs font-bold rounded-xl transition flex items-center gap-1">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>دەستکاری ئەرشیف</span>
                            </button>
                        @endif
                    @endif
                @endif

                <!-- Scroll to Top button -->
                <button type="button" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-xl border border-slate-700 text-xs transition" title="بڕۆ بۆ سەرەوە">
                    <i class="fa-solid fa-arrow-up"></i>
                </button>
            </div>
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
