@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

@php
    $typeId = (int)$transaction->transaction_type_id;
    $typeName = $transaction->transactionType?->name_kurdish ?? 'مامەڵە';

    $typeTheme = match($typeId) {
        1 => [ // دەرهێنانی دەفتەر (نوێ)
            'label' => 'دەرهێنانی دەفتەر (نوێ)',
            'accentGradient' => 'from-emerald-500 via-teal-400 to-emerald-600',
            'gradient' => 'from-emerald-950/95 via-slate-900 to-emerald-950/60',
            'border' => 'border-emerald-500/80 shadow-emerald-500/20 ring-1 ring-emerald-500/30',
            'pill' => 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 shadow-sm',
            'icon' => 'fa-solid fa-passport text-emerald-400',
            'textColor' => 'text-emerald-300',
            'iconBg' => 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400 shadow-emerald-500/30',
        ],
        2 => [ // نوێکردنەوە / تازەکردنەوە
            'label' => 'نوێکردنەوەی دەفتەر (تازەکردنەوە)',
            'accentGradient' => 'from-sky-500 via-blue-400 to-indigo-600',
            'gradient' => 'from-sky-950/95 via-slate-900 to-sky-950/60',
            'border' => 'border-sky-500/80 shadow-sky-500/20 ring-1 ring-sky-500/30',
            'pill' => 'bg-sky-500/15 text-sky-300 border border-sky-500/30 shadow-sm',
            'icon' => 'fa-solid fa-rotate-right text-sky-400',
            'textColor' => 'text-sky-300',
            'iconBg' => 'bg-sky-500/20 border-sky-500/40 text-sky-400 shadow-sky-500/30',
        ],
        3 => [ // ناوگۆڕین
            'label' => 'ناوگۆڕین (گۆڕینی خاوەنداریەتی)',
            'accentGradient' => 'from-purple-500 via-violet-400 to-purple-600',
            'gradient' => 'from-purple-950/95 via-slate-900 to-purple-950/60',
            'border' => 'border-purple-500/80 shadow-purple-500/20 ring-1 ring-purple-500/30',
            'pill' => 'bg-purple-500/15 text-purple-300 border border-purple-500/30 shadow-sm',
            'icon' => 'fa-solid fa-user-pen text-purple-400',
            'textColor' => 'text-purple-300',
            'iconBg' => 'bg-purple-500/20 border-purple-500/40 text-purple-400 shadow-purple-500/30',
        ],
        4 => [ // دەفتەرگۆڕین
            'label' => 'دەفتەرگۆڕین (گۆڕینی دەفتەر)',
            'accentGradient' => 'from-amber-500 via-orange-400 to-amber-600',
            'gradient' => 'from-amber-950/95 via-slate-900 to-amber-950/60',
            'border' => 'border-amber-500/80 shadow-amber-500/20 ring-1 ring-amber-500/30',
            'pill' => 'bg-amber-500/15 text-amber-300 border border-amber-500/30 shadow-sm',
            'icon' => 'fa-solid fa-book text-amber-400',
            'textColor' => 'text-amber-300',
            'iconBg' => 'bg-amber-500/20 border-amber-500/40 text-amber-400 shadow-amber-500/30',
        ],
        5 => [ // پووچەڵکردنەوەی دەفتەر
            'label' => 'پووچەڵکردنەوەی دەفتەر',
            'accentGradient' => 'from-rose-500 via-red-500 to-rose-700',
            'gradient' => 'from-rose-950/95 via-slate-900 to-rose-950/60',
            'border' => 'border-rose-500/90 shadow-rose-500/30 ring-2 ring-rose-500/40',
            'pill' => 'bg-rose-500/20 text-rose-300 border border-rose-500/40 shadow-sm',
            'icon' => 'fa-solid fa-ban text-rose-400',
            'textColor' => 'text-rose-300',
            'iconBg' => 'bg-rose-500/25 border-rose-500/50 text-rose-300 shadow-rose-500/30',
        ],
        6 => [ // بەڵێننامە
            'label' => 'بەڵێننامە',
            'accentGradient' => 'from-amber-500 via-yellow-400 to-orange-500',
            'gradient' => 'from-amber-950/95 via-yellow-950/60 to-slate-900',
            'border' => 'border-amber-500/80 shadow-amber-500/20 ring-1 ring-amber-500/30',
            'pill' => 'bg-amber-500/15 text-amber-300 border border-amber-500/30 shadow-sm',
            'icon' => 'fa-solid fa-file-contract text-amber-400',
            'textColor' => 'text-amber-300',
            'iconBg' => 'bg-amber-500/20 border-amber-500/40 text-amber-400 shadow-amber-500/30',
        ],
        7 => [ // ناوگۆڕین و دەفتەرگۆڕین
            'label' => 'ناوگۆڕین و دەفتەرگۆڕین',
            'accentGradient' => 'from-purple-500 via-amber-400 to-indigo-600',
            'gradient' => 'from-purple-950/95 via-slate-900 to-indigo-950/60',
            'border' => 'border-purple-500/80 shadow-purple-500/20 ring-1 ring-purple-500/30',
            'pill' => 'bg-purple-500/15 text-purple-300 border border-purple-500/30 shadow-sm',
            'icon' => 'fa-solid fa-file-pen text-purple-400',
            'textColor' => 'text-purple-300',
            'iconBg' => 'bg-purple-500/20 border-purple-500/40 text-purple-400 shadow-purple-500/30',
        ],
        8 => [ // پشکنین
            'label' => 'پشکنین',
            'accentGradient' => 'from-sky-500 via-teal-400 to-cyan-600',
            'gradient' => 'from-sky-950/95 via-slate-900 to-cyan-950/60',
            'border' => 'border-sky-500/80 shadow-sky-500/20 ring-1 ring-sky-500/30',
            'pill' => 'bg-sky-500/15 text-sky-300 border border-sky-500/30 shadow-sm',
            'icon' => 'fa-solid fa-file-circle-check text-sky-400',
            'textColor' => 'text-sky-300',
            'iconBg' => 'bg-sky-500/20 border-sky-500/40 text-sky-400 shadow-sky-500/30',
        ],
        default => [
            'label' => $typeName,
            'accentGradient' => 'from-indigo-500 via-sky-400 to-indigo-600',
            'gradient' => 'from-indigo-950/95 via-slate-900 to-indigo-950/60',
            'border' => 'border-indigo-500/80 shadow-indigo-500/20 ring-1 ring-indigo-500/30',
            'pill' => 'bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 shadow-sm',
            'icon' => 'fa-solid fa-folder-open text-indigo-400',
            'textColor' => 'text-indigo-300',
            'iconBg' => 'bg-indigo-500/20 border-indigo-500/40 text-indigo-400 shadow-indigo-500/30',
        ],
    };

    $linkedPledge = $linkedPledge ?? $transaction->getLinkedPledgeOrParent();
    $isArchiverOrAdmin = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('transactions.submit'));
    $canSubmitThisType = in_array($typeId, [1, 2, 3, 4, 5, 7, 8]);

    $isPledgeDirect = ($typeId === 6);
    $bookletTx = $isPledgeDirect ? $linkedPledge : $transaction;
    $pledgeTx = $isPledgeDirect ? $transaction : $linkedPledge;
    $hasCompanion = (bool)($bookletTx && $pledgeTx);
    $bookletFee = $bookletTx ? (float)$bookletTx->total_pay : 0;
    $pledgeFee = $pledgeTx ? (float)$pledgeTx->total_pay : 0;
    $totalCombinedPay = $hasCompanion ? ($bookletFee + $pledgeFee) : (float)$transaction->total_pay;
    $auditCombinedPay = (int)$totalCombinedPay;
@endphp

<div class="max-w-5xl mx-auto space-y-6 pb-24" x-data="{ 
    showPayModal: false, 
    showBookletModal: false, 
    showCancelModal: false, 
    showReturnModal: false, 
    returnStage: '{{ $transaction->is_inspected ? 'audit' : 'inspection' }}', 
    showRevertModal: false,
    showDeleteModal: false,
    showAuditModal: false,
    showSubmitModal: false,
    showCustomsLetterModal: false,
    showArchiveInspectionPromptModal: {{ session('archive_inspection_prompt_letter') ? 'true' : 'false' }},
    showAuditPromptModal: false,
    showPaymentPromptModal: {{ session('payment_completed_prompt_receipt') ? 'true' : 'false' }},
    showInspectionPromptModal: {{ session('inspection_completed_prompt_print') ? 'true' : 'false' }},
    showDataEntryPromptModal: {{ session('data_entry_completed_prompt_print') ? 'true' : 'false' }},
    mainReceipt: '',
    linkedReceipt: '',
    hasLinked: {{ ($hasCompanion && !$pledgeTx->is_paid) ? 'true' : 'false' }},
    printChoice: '{{ session('payment_print_choice', 'both') }}',
    confirmAuditAmount: '',
    expectedAuditAmount: '{{ $auditCombinedPay }}',
    expectedSingleAuditAmount: '{{ (int)$transaction->total_pay }}'
}">
    
    <!-- ========================================================================= -->
    <!-- 1. SLEEK ACTION & NAVIGATION TOOLBAR                                      -->
    <!-- ========================================================================= -->
    <div class="relative z-40 bg-slate-900/90 border border-slate-800 rounded-2xl p-2 sm:p-2.5 shadow-xl flex flex-wrap md:flex-nowrap items-center justify-between gap-2.5 backdrop-blur-md">
        <div class="flex items-center space-x-2 space-x-reverse shrink-0">
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver'))
                <a href="{{ route('transactions.index') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-750 text-slate-200 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm border border-slate-700/60" title="گەڕانەوە بۆ لیستی مامەڵەکان">
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                    <span>گەڕانەوە</span>
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-750 text-slate-200 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm border border-slate-700/60" title="گەڕانەوە بۆ داشبۆرد">
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                    <span>گەڕانەوە</span>
                </a>
            @endif

            <div class="h-5 w-px bg-slate-800 hidden sm:block"></div>

            <!-- Barcode Graphic Box -->
            <div class="bg-white/95 px-2 py-0.5 rounded-lg shadow-sm border border-slate-300 flex items-center justify-center shrink-0" title="بارکۆدی ئەلیکترۆنی مامەڵە">
                <svg id="showBarcodeSvg" class="h-6"></svg>
            </div>

            <!-- Barcode Number with One-Click Copy -->
            <button type="button" 
                    onclick="navigator.clipboard.writeText('{{ $transaction->barcode }}');" 
                    class="group inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-sky-950/60 hover:bg-sky-900/60 border border-sky-800/50 text-sky-300 font-mono text-xs font-black transition active:scale-95" 
                    title="بارکۆدی مامەڵە (کلیک بکە بۆ کۆپیکردن)">
                <i class="fa-solid fa-barcode text-[11px] text-sky-400"></i>
                <span class="tracking-wider">{{ $transaction->barcode }}</span>
                <i class="fa-regular fa-copy text-[10px] text-slate-400 group-hover:text-white transition"></i>
            </button>

            <!-- Date -->
            <span class="hidden md:inline-flex items-center gap-1.5 text-[11px] text-slate-400 font-mono bg-slate-950/50 px-2.5 py-1.5 rounded-xl border border-slate-800/80">
                <i class="fa-regular fa-calendar-days text-slate-500 text-[10px]"></i>
                <span>{{ $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : ($transaction->created_at ? $transaction->created_at->format('Y-m-d') : '-') }}</span>
            </span>
        </div>

        <!-- Action Suite Toolbar -->
        <div class="flex items-center flex-nowrap sm:flex-wrap gap-1.5 justify-end">
            <!-- 1. Dedicated Primary Contextual Actions -->
            @if($transaction->is_inspected && (!$transaction->is_audited || auth()->user()->isAdmin()))
                <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'both']) }}" target="_blank" 
                   class="px-3 py-1.5 bg-teal-600 hover:bg-teal-500 text-white text-xs font-black rounded-xl shadow-lg shadow-teal-600/30 transition flex items-center gap-1.5 shrink-0" 
                   title="چاپی پسولەی کەشف و خەمڵاندن">
                    <i class="fa-solid fa-file-waveform text-xs"></i>
                    <span>{{ $linkedPledge ? 'چاپی هەردوو کەشف (۲ پەڕە)' : 'چاپی کەشف و تەخمین' }}</span>
                </a>
            @endif

            @if($transaction->requiresBooklet() && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet' || auth()->user()->hasPermission('prints.booklet')) && $transaction->is_paid && !empty($transaction->booklet_number) && (!$transaction->is_submitted || auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.booklet')))
                <a href="{{ route('transactions.print_booklet', $transaction->id) }}" target="_blank" 
                   class="px-3 py-1.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5 shrink-0" 
                   title="چاپی دەفتەری گەشتیاری">
                    <i class="fa-solid fa-passport text-xs"></i>
                    <span>چاپی دەفتەر</span>
                </a>
            @elseif(!$transaction->is_cancelled && ($transaction->is_paid || ($hasCompanion && $bookletTx->is_paid)) && (!$transaction->is_submitted || auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.receipt')) && (auth()->user()->isAdmin() || auth()->user()->role === 'cashier' || auth()->user()->hasPermission('prints.receipt')))
                <a href="{{ route('transactions.print_payment_receipt', $hasCompanion ? [$bookletTx->id, 'mode' => 'both'] : $transaction->id) }}" target="_blank" 
                   class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5 shrink-0" 
                   title="چاپی وەسڵی فەرمی پارەدان">
                    <i class="fa-solid fa-receipt text-xs"></i>
                    <span>{{ ($hasCompanion && $bookletTx->is_paid && $pledgeTx->is_paid) ? 'چاپی هەردوو ۳۷/أ (پێکەوە)' : 'چاپی ۳۷/أ' }}</span>
                </a>
            @elseif((!$transaction->is_submitted || auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.data_entry')) && (auth()->user()->isAdmin() || in_array(auth()->user()->role, ['data_entry', 'inspector']) || auth()->user()->hasPermission('prints.data_entry')))
                <a href="{{ route('transactions.print_data_entry', ['transaction' => $transaction->id, 'mode' => 'both']) }}" target="_blank" 
                   class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-sky-600/30 transition flex items-center gap-1.5 shrink-0" 
                   title="{{ $linkedPledge ? 'چاپی هەردوو فیشەی دەرهێنان و بەڵێننامە' : 'چاپی فیشەی داتائەنتەری' }}">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>{{ $linkedPledge ? 'چاپی هەردوو فیشە (۲ پەڕە)' : 'چاپی فیشە' }}</span>
                </a>
            @endif

            <!-- 2. Primary Edit/Modify Button (دەستکاری یان گۆڕین - دیار و بەرچاو لە تەنیشت چاپی فیشە) -->
            @php
                $canUserEditThis = $transaction->canBeEditedByUser(auth()->user());
            @endphp
            @if($canUserEditThis && !$transaction->is_audited && !$transaction->is_paid)
                <a href="{{ route('transactions.edit', $transaction->id) }}" 
                   class="px-3.5 py-1.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-white text-xs font-black rounded-xl shadow-lg shadow-amber-500/25 transition-all duration-200 flex items-center gap-1.5 shrink-0 border border-amber-300/40 hover:scale-[1.03] active:scale-95 group" 
                   title="دەستکاریکردن یان گۆڕینی زانیارییەکانی ئەم مامەڵەیە">
                    <i class="fa-solid fa-pen-to-square text-xs group-hover:rotate-6 transition-transform"></i>
                    <span>دەستکاری مامەڵە</span>
                </a>
            @endif

            <!-- 2. Master Prints Dropdown (Never Closes Unexpectedly & Complete Options) -->
            <div class="relative" x-data="{ openPrintsMenu: false }" :class="openPrintsMenu ? 'z-50' : 'z-20'">
                <button type="button" @click.stop="openPrintsMenu = !openPrintsMenu" 
                        class="px-3 py-1.5 bg-slate-800 hover:bg-slate-750 text-indigo-300 hover:text-white border border-indigo-500/40 hover:border-indigo-500/70 text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5 shrink-0">
                    <i class="fa-solid fa-print text-indigo-400 text-xs"></i>
                    <span>چاپەکان</span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openPrintsMenu ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="openPrintsMenu" x-on:click.outside="openPrintsMenu = false" x-cloak 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute left-0 top-full mt-2 w-72 sm:w-80 bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl p-2.5 z-[100] space-y-2 text-xs text-right ring-1 ring-white/10">
                    
                    <!-- قۆناغی سەرەتایی، کەشف و یاسایی -->
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 px-2 block">فیشە، کەشف و یاسایی:</span>
                        @if($linkedPledge)
                            <a href="{{ route('transactions.print_data_entry', ['transaction' => $transaction->id, 'mode' => 'both']) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-sky-300 font-bold hover:bg-slate-800 hover:text-white transition">
                                <i class="fa-solid fa-file-invoice text-sky-400 w-4 text-center"></i>
                                <span>چاپی هەردوو فیشە (دەرهێنان + بەڵێننامە - ۲ پەڕە)</span>
                            </a>
                            <a href="{{ route('transactions.print_data_entry', ['transaction' => $transaction->id, 'mode' => 'main']) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-1 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition text-[11px] pr-6">
                                <i class="fa-solid fa-angle-left text-slate-500"></i>
                                <span>تەنها فیشەی دەرهێنان (۱ پەڕە)</span>
                            </a>
                            <a href="{{ route('transactions.print_data_entry', ['transaction' => $transaction->id, 'mode' => 'pledge']) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-1 rounded-xl text-amber-300 hover:bg-slate-800 hover:text-white transition text-[11px] pr-6">
                                <i class="fa-solid fa-angle-left text-amber-500"></i>
                                <span>تەنها فیشەی بەڵێننامە (۱ پەڕە)</span>
                            </a>
                        @else
                            <a href="{{ route('transactions.print_data_entry', ['transaction' => $transaction->id, 'mode' => 'both']) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-slate-200 hover:bg-slate-800 hover:text-white transition">
                                <i class="fa-solid fa-file-invoice text-sky-400 w-4 text-center"></i>
                                <span>فیشەی داتائەنتەری (بەدواداچوون)</span>
                            </a>
                        @endif

                        @if($transaction->is_inspected)
                            @if($linkedPledge)
                                <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'both']) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-teal-300 font-bold hover:bg-slate-800 hover:text-white transition">
                                    <i class="fa-solid fa-file-waveform text-teal-400 w-4 text-center"></i>
                                    <span>چاپی هەردوو کەشف (دەرهێنان + بەڵێننامە - ۲ پەڕە)</span>
                                </a>
                                <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'main']) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition text-[11px] pr-6">
                                    <i class="fa-solid fa-angle-left text-slate-500"></i>
                                    <span>تەنها کەشفی دەرهێنان (۱ پەڕە)</span>
                                </a>
                                <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'pledge']) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1 rounded-xl text-amber-300 hover:bg-slate-800 hover:text-white transition text-[11px] pr-6">
                                    <i class="fa-solid fa-angle-left text-amber-500"></i>
                                    <span>تەنها کەشفی بەڵێننامە (۱ پەڕە)</span>
                                </a>
                            @else
                                <a href="{{ route('transactions.print_receipt', $transaction->id) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-teal-300 hover:bg-slate-800 hover:text-white transition">
                                    <i class="fa-solid fa-file-waveform text-teal-400 w-4 text-center"></i>
                                    <span>پسولەی کەشف و خەمڵاندن (A4 Landscape)</span>
                                </a>
                            @endif
                        @endif

                        @if(!$transaction->is_cancelled && ($typeId === 1 || $typeId === 6 || $linkedPledge))
                            <a href="{{ route('transactions.print_pledge', $transaction->id) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-amber-300 hover:bg-slate-800 hover:text-white transition">
                                <i class="fa-solid fa-file-contract text-amber-400 w-4 text-center"></i>
                                <span>چاپی بەڵێننامەی یاسایی</span>
                            </a>
                        @endif
                    </div>

                    <!-- قۆناغی دارایی و وەسڵ -->
                    @if(!$transaction->is_cancelled && ($transaction->is_paid || ($hasCompanion && $bookletTx->is_paid)))
                        <div class="space-y-1 pt-1.5 border-t border-slate-800">
                            <span class="text-[10px] font-bold text-slate-400 px-2 block">دارایی و وەسڵ:</span>
                            @if($hasCompanion && $bookletTx->is_paid && $pledgeTx->is_paid)
                                <a href="{{ route('transactions.print_payment_receipt', [$bookletTx->id, 'mode' => 'both']) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-emerald-300 font-bold hover:bg-slate-800 hover:text-white transition">
                                    <i class="fa-solid fa-receipt text-emerald-400 w-4 text-center"></i>
                                    <span>چاپی هەردوو پسوولەی ۳۷/أ (پێکەوە)</span>
                                </a>
                                <a href="{{ route('transactions.print_payment_receipt', [$bookletTx->id, 'mode' => 'main']) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1 rounded-xl text-sky-300 hover:bg-slate-800 hover:text-white transition text-[11px] pr-6">
                                    <i class="fa-solid fa-angle-left text-sky-500"></i>
                                    <span>تەنها وەسڵی دەرهێنان ({{ $bookletTx->receipt_37a_number }})</span>
                                </a>
                                <a href="{{ route('transactions.print_payment_receipt', [$bookletTx->id, 'mode' => 'pledge']) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1 rounded-xl text-amber-300 hover:bg-slate-800 hover:text-white transition text-[11px] pr-6">
                                    <i class="fa-solid fa-angle-left text-amber-500"></i>
                                    <span>تەنها وەسڵی بەڵێننامە ({{ $pledgeTx->receipt_37a_number }})</span>
                                </a>
                            @else
                                <a href="{{ route('transactions.print_payment_receipt', $transaction->id) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-emerald-300 hover:bg-slate-800 hover:text-white transition">
                                    <i class="fa-solid fa-receipt text-emerald-400 w-4 text-center"></i>
                                    <span>پسوولەی پارەدان (۳۷/أ)</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- دەفتەر و نوسراوە فەرمییەکان -->
                    <div class="space-y-1 pt-1.5 border-t border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 px-2 block">دەفتەر و نوسراوە فەرمییەکان:</span>
                        @if(($transaction->is_submitted || auth()->user()->isAdmin() || auth()->user()->role === 'archiver') && ((int)$transaction->transaction_type_id === 8 || ($transaction->transactionType && str_contains($transaction->transactionType->name_kurdish, 'پشکنین')) || auth()->user()->hasPermission('prints.inspection_letter')))
                            <a href="{{ route('transactions.print_inspection_letter', $transaction->id) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-sky-300 hover:bg-slate-800 hover:text-white transition">
                                <i class="fa-solid fa-file-circle-check text-sky-400 w-4 text-center"></i>
                                <span>نووسراوی پشکنین</span>
                            </a>
                        @endif
                        @if($transaction->booklet_number)
                            <a href="{{ route('transactions.print_booklet', $transaction->id) }}" target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-indigo-300 font-bold hover:bg-indigo-600 hover:text-white transition">
                                <i class="fa-solid fa-passport text-indigo-400 w-4 text-center"></i>
                                <span>چاپی دەفتەر ({{ $transaction->booklet_number }})</span>
                            </a>
                        @endif

                        @if(!$transaction->is_cancelled)
                            @if($transaction->isCancellation())
                                <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-rose-300 hover:bg-slate-800 hover:text-white transition">
                                    <i class="fa-solid fa-file-circle-xmark text-rose-400 w-4 text-center"></i>
                                    <span>نووسراوی پووچەڵکردنەوە</span>
                                </a>
                            @elseif($transaction->isBookletIssue())
                                <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-amber-300 hover:bg-slate-800 hover:text-white transition">
                                    <i class="fa-solid fa-file-circle-exclamation text-amber-400 w-4 text-center"></i>
                                    <span>نیشانەی ڕەفتارنەکردن</span>
                                </a>
                            @else
                                <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-amber-300 hover:bg-slate-800 hover:text-white transition">
                                    <i class="fa-solid fa-file-circle-exclamation text-amber-400 w-4 text-center"></i>
                                    <span>نیشانەی ڕەفتارنەکردن</span>
                                </a>
                                <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" 
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-rose-300 hover:bg-slate-800 hover:text-white transition">
                                    <i class="fa-solid fa-file-circle-xmark text-rose-400 w-4 text-center"></i>
                                    <span>نووسراوی پووچەڵکردنەوە</span>
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>


            <!-- 4. Follow-up Transaction Dropdown -->
            @if((auth()->user()->isAdmin() || auth()->user()->role === 'data_entry') && auth()->user()->role !== 'inspector')
                <div class="relative" x-data="{ openFollowUp: false }" :class="openFollowUp ? 'z-50' : 'z-20'">
                    <button type="button" @click.stop="openFollowUp = !openFollowUp" 
                            class="px-3 py-1.5 bg-slate-800 hover:bg-slate-750 text-sky-300 hover:text-white border border-slate-700 text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-arrows-split-up-and-left text-sky-400 text-xs"></i>
                        <span>مامەڵەی نوێ</span>
                        <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openFollowUp ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="openFollowUp" x-on:click.outside="openFollowUp = false" x-cloak 
                         class="absolute left-0 top-full mt-2 w-56 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl p-1.5 z-[100] space-y-1 text-xs text-right ring-1 ring-white/10">
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

            <!-- 5. Auditor Quick Revert / Return -->
            @if($transaction->is_audited && !$transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->role === 'auditor' || auth()->user()->hasPermission('transactions.audit')))
                <form action="{{ route('transactions.revert_audit', $transaction->id) }}" method="POST" class="inline" onsubmit="return confirm('ئایا دڵنیایت لە هەڵوەشاندنەوەی پەسەندی وردبینی؟ مامەڵەکە دەگەڕێتەوە بۆ چاوەڕوانی وردبینی.');">
                    @csrf
                    <button type="submit" class="px-2.5 py-1.5 bg-purple-700/80 hover:bg-purple-600 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1 shrink-0" title="هەڵوەشاندنەوەی وردبینی">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                        <span>هەڵوەشاندنەوە</span>
                    </button>
                </form>
                <button type="button" @click="returnStage = 'audit'; showReturnModal = true" class="px-2.5 py-1.5 bg-rose-600/80 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1 shrink-0" title="گەڕاندنەوە بۆ پێشتر">
                    <i class="fa-solid fa-arrow-turn-down-left"></i>
                    <span>گەڕاندنەوە</span>
                </button>
            @endif

            <!-- 6. Dedicated Admin Delete Button -->
            @if(auth()->check() && auth()->user()->isAdmin())
                <button type="button" @click="showDeleteModal = true" 
                        class="px-2.5 py-1.5 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/40 text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5 shrink-0" 
                        title="{{ $hasCompanion ? 'سڕینەوەی دەرهێنان و بەڵێننامە پێکەوە' : 'سڕینەوەی ئەم مامەڵەیە (تەنها بۆ بەڕێوەبەر)' }}">
                    <i class="fa-solid fa-trash-can text-xs text-rose-400"></i>
                    <span>{{ $hasCompanion ? 'سڕینەوەی هەردووکیان' : 'سڕینەوە' }}</span>
                </button>

                <!-- 7. Admin Actions Dropdown (Reset & Soft Delete) -->
                <div class="relative" x-data="{ openAdminMenu: false }" :class="openAdminMenu ? 'z-50' : 'z-20'">
                    <button type="button" @click.stop="openAdminMenu = !openAdminMenu" 
                            class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white border border-slate-700 text-xs font-bold rounded-xl shadow transition flex items-center gap-1 shrink-0" 
                            title="کردارەکانی بەڕێوەبەر">
                        <i class="fa-solid fa-ellipsis-vertical text-slate-400"></i>
                    </button>

                    <div x-show="openAdminMenu" x-on:click.outside="openAdminMenu = false" x-cloak 
                         class="absolute left-0 top-full mt-2 w-52 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl p-1.5 z-[100] space-y-1 text-xs text-right ring-1 ring-white/10">
                        <button type="button" @click="showRevertModal = true; openAdminMenu = false" 
                                class="w-full text-right flex items-center gap-2 p-2 rounded-lg text-amber-400 hover:bg-slate-800 transition">
                            <i class="fa-solid fa-arrow-rotate-left w-4 text-center"></i>
                            <span>ڕێکخستنەوە (Reset)</span>
                        </button>
                        <button type="button" @click="showDeleteModal = true; openAdminMenu = false" 
                                class="w-full text-right flex items-center gap-2 p-2 rounded-lg text-rose-400 hover:bg-rose-950/50 transition">
                            <i class="fa-solid fa-trash-can w-4 text-center"></i>
                            <span>{{ $hasCompanion ? 'سڕینەوەی هەردوو مامەڵە' : 'سڕینەوەی مامەڵە' }}</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Alert 1: Booklet Desk Guard (خاڵی ۳: هۆشیارکردنەوە بۆ چاپی دەفتەر) -->
    @if(auth()->check() && auth()->user()->role === 'booklet' && in_array($typeId, [5, 6, 8]))
        <div class="p-4 rounded-2xl bg-amber-500/15 border-2 border-amber-500/60 flex items-center justify-between text-amber-200 shadow-xl backdrop-blur-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 text-lg shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h4 class="font-bold text-base text-amber-100">لێرە کاری دەفتەری نییە!</h4>
                    <p class="text-xs text-amber-300/90">ئەم مامەڵەیە جۆری [{{ $typeName }}]ـە و کاری دەفتەری لەسەر ئەنجام نادرێت.</p>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl transition shadow">
                گەڕانەوە بۆ داشبۆرد
            </a>
        </div>
    @endif

    <!-- Alert 2: Legal Desk Guard (خاڵی ٦: هۆشیارکردنەوە بۆ بەشی یاسا) -->
    @if(auth()->check() && auth()->user()->isLegal() && $typeId !== 1)
        <div class="p-4 rounded-2xl bg-rose-500/15 border-2 border-rose-500/60 flex items-center justify-between text-rose-200 shadow-xl backdrop-blur-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-500/20 border border-rose-500/40 flex items-center justify-center text-rose-400 text-lg shrink-0">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <h4 class="font-bold text-base text-rose-100">کاری تۆ لێرە ئەنجام نادرێت!</h4>
                    <p class="text-xs text-rose-300/90">بەشی یاسایی تەنها تایبەتە بە مامەڵەی دەرهێنانی دەفتەر (بەڵێننامەی یاسایی). جۆری ئەم مامەڵەیە: [{{ $typeName }}]ـە.</p>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl transition shadow">
                گەڕانەوە بۆ داشبۆرد
            </a>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 2. EXECUTIVE CUSTOMS DOSSIER (ناسنامەی فەرمی و دەستەڵاتداری مامەڵە)           -->
    <!-- ========================================================================= -->
    <div class="bg-slate-900/95 border border-slate-800 rounded-2xl shadow-xl overflow-hidden relative backdrop-blur-xl">
        
        <!-- A. LUXURY ACCENT TOP BAR -->
        <div class="h-1 w-full bg-gradient-to-r {{ $typeTheme['accentGradient'] ?? 'from-indigo-500 via-sky-400 to-indigo-600' }}"></div>

        <!-- B. CARD HEADER: STATUS & TRANSACTION METADATA STRIP -->
        <div class="px-4 py-2 sm:px-5 bg-slate-950/70 border-b border-slate-800/80 flex flex-wrap items-center justify-between gap-2.5">
            <!-- Right: Transaction Type & Current Stage -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Transaction Type Pill (Large & Clear) -->
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-sm sm:text-base font-black border-2 shadow-md {{ $typeTheme['pill'] ?? 'bg-indigo-500/15 text-indigo-300 border-indigo-500/40' }}">
                    <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-folder-open text-indigo-400' }} text-sm"></i>
                    <span>جۆری مامەڵە: {{ $typeName }}</span>
                    <span class="text-xs font-mono px-1.5 py-0.5 rounded bg-black/30">#{{ $transaction->transaction_type_id }}</span>
                </span>

                <!-- Workflow Stage Pill -->
                @if($transaction->is_returned)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-500/20 text-rose-300 border border-rose-500/40 text-xs font-bold rounded-lg animate-pulse">
                        <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                        <span>گەڕێنراوەتەوە (Returned)</span>
                    </span>
                @elseif($transaction->is_cancelled)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-500/20 text-rose-300 border border-rose-500/40 text-xs font-bold rounded-lg">
                        <i class="fa-solid fa-ban text-xs"></i>
                        <span>مامەڵەی پووچەڵکراوە</span>
                    </span>
                @elseif($transaction->is_submitted)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-xs font-bold rounded-lg">
                        <i class="fa-solid fa-circle-check text-xs"></i>
                        <span>تەواوکراو و ئەرشیفکراو</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-sky-500/15 text-sky-300 border border-sky-500/30 text-xs font-bold rounded-lg shadow-sm">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-500"></span>
                        </span>
                        <span>قۆناغی ئێستا: {{ $transaction->getCurrentStageName() }}</span>
                    </span>
                @endif
            </div>

            <!-- Left: Quick Barcode & Date Metadata -->
            <div class="flex items-center gap-2 text-xs">
                @if($transaction->parentTransaction)
                    <a href="{{ route('transactions.show', $transaction->parentTransaction->id) }}" 
                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-bold transition text-[11px]">
                        <i class="fa-solid fa-link text-[10px]"></i>
                        <span>بنەڕەتی:</span>
                        <span class="font-mono text-white underline">{{ $transaction->parentTransaction->barcode }}</span>
                    </a>
                @endif

                <div class="flex items-center gap-2 text-slate-400 font-mono text-[11px] bg-slate-900/90 px-2.5 py-1 rounded-lg border border-slate-800">
                    <i class="fa-regular fa-calendar text-slate-500"></i>
                    <span>{{ $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : ($transaction->created_at ? $transaction->created_at->format('Y-m-d') : '-') }}</span>
                    <span class="text-slate-600">•</span>
                    <i class="fa-solid fa-barcode text-slate-500"></i>
                    <span class="text-sky-400 font-bold select-all">{{ $transaction->barcode }}</span>
                </div>
            </div>
        </div>

        <!-- C. CARD MAIN CONTENT: 3 BALANCED COMPACT PANELS -->
        <div class="p-3 sm:p-4 grid grid-cols-1 lg:grid-cols-12 gap-3.5 items-stretch relative">
            
            <!-- Ambient subtle background lighting -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-sky-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <!-- PANEL 1: CITIZEN DOSSIER (5 Cols) -->
            <div class="lg:col-span-5 flex flex-col justify-between p-3 sm:p-3.5 rounded-xl bg-slate-950/50 border border-slate-800/70 shadow-sm space-y-2">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[11px] font-bold text-sky-400/90 flex items-center gap-1.5 uppercase tracking-wider">
                            <i class="fa-solid fa-id-card text-xs"></i>
                            <span>خاوەن مامەڵە / هاووڵاتی</span>
                        </span>
                    </div>

                    <!-- Citizen Name -->
                    <h1 class="text-lg sm:text-xl font-black text-white tracking-wide leading-tight">
                        {{ $transaction->visitor_name }}
                    </h1>
                    
                    <!-- English Name -->
                    @if($transaction->visitor_name_eng)
                        <div class="text-[11px] font-semibold text-slate-400 tracking-wider uppercase mt-0.5 flex items-center gap-1.5" dir="ltr">
                            <span class="text-[10px] text-slate-500 font-mono">ENG:</span>
                            <span>{{ $transaction->visitor_name_eng }}</span>
                        </div>
                    @endif
                </div>

                <!-- Citizen Metadata Chips (Phone & Directorate) -->
                <div class="pt-2 border-t border-slate-800/80 space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($transaction->phone_number)
                            <a href="tel:{{ $transaction->phone_number }}" 
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-mono font-bold transition shadow-sm" dir="ltr" title="ژمارەی مۆبایل">
                                <i class="fa-solid fa-phone text-[10px] text-emerald-400"></i>
                                <span>{{ $transaction->phone_number }}</span>
                            </a>
                        @endif

                        @if($transaction->trafficDirectorate)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-900 text-slate-300 border border-slate-800 text-xs font-medium" title="بەڕێوەبەرایەتی هاتوچۆ">
                                <i class="fa-solid fa-building-columns text-[10px] text-slate-400"></i>
                                <span>{{ $transaction->trafficDirectorate->name_kurdish }}</span>
                            </span>
                        @endif
                    </div>

                    @if($transaction->second_driver_name)
                        <div class="flex items-center gap-1.5 text-xs bg-slate-900/90 px-2.5 py-1 rounded-lg border border-slate-800 text-slate-300">
                            <i class="fa-solid fa-user-group text-slate-400 text-xs"></i>
                            <span class="text-slate-400 text-[11px]">شۆفێری دووەم:</span>
                            <span class="font-bold text-slate-200">{{ $transaction->second_driver_name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- PANEL 2: VEHICLE IDENTITY SHOWCASE (4 Cols) -->
            <div class="lg:col-span-4 flex flex-col items-center justify-between text-center p-3 sm:p-3.5 rounded-xl bg-slate-950/50 border border-slate-800/70 shadow-sm space-y-2">
                <span class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 self-start">
                    <i class="fa-solid fa-car text-xs text-slate-400"></i>
                    <span>ناسنامەی ئۆتۆمبێل</span>
                </span>

                <!-- Kurdistan License Plate in Perfectly Proportioned Medium Size -->
                <div class="py-0.5 transform transition hover:scale-105 duration-200 drop-shadow-md">
                    <x-vehicle-plate :plate="$transaction->plate_number" :type="$transaction->plateType" size="md" />
                </div>

                <!-- Vehicle Model & Color Details -->
                <div class="w-full space-y-1">
                    <div class="text-xs sm:text-sm font-black text-white flex items-center justify-center gap-1.5 flex-wrap">
                        <span>{{ $transaction->carMake->name_kurdish ?? '-' }}</span>
                        @if($transaction->model_year)
                            <span class="font-mono text-slate-300 text-xs px-1.5 py-0.5 rounded bg-slate-900 border border-slate-800">
                                {{ $transaction->model_year }}
                            </span>
                        @endif
                        @if($transaction->carColor)
                            <span class="text-xs text-sky-400 font-bold px-2 py-0.5 rounded bg-sky-950/40 border border-sky-800/40">
                                {{ $transaction->carColor->name_kurdish }}
                            </span>
                        @endif
                    </div>

                    <!-- VIN Number Badge -->
                    @if($transaction->chassis_number && $transaction->chassis_number !== '-')
                        <div class="inline-flex items-center gap-1.5 text-xs font-mono bg-slate-900 px-2.5 py-0.5 rounded-lg border border-slate-800 text-slate-300" dir="ltr" title="ژمارەی شاسی (VIN)">
                            <span class="text-slate-500 text-[10px] font-bold">VIN:</span>
                            <span class="text-sky-300 font-bold uppercase tracking-wider select-all">{{ $transaction->chassis_number }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- PANEL 3: FINANCIALS & DOSSIER STATUS (3 Cols - Compact & Unified) -->
            <div class="lg:col-span-3 flex flex-col justify-between p-3 sm:p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/80 shadow-sm space-y-2">
                
                <!-- Amount & Payment Status -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">کۆی بڕی رسومات:</span>
                        @if($transaction->is_paid)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/30">
                                <i class="fa-solid fa-check text-[9px]"></i> وەرگیراوە
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/30">
                                <i class="fa-regular fa-clock text-[9px]"></i> نەدراوە
                            </span>
                        @endif
                    </div>

                    <div class="flex items-baseline justify-between py-0.5">
                        <span class="text-xl sm:text-2xl font-black font-mono text-emerald-400">
                            {{ number_format($transaction->total_pay) }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">دینار</span>
                    </div>
                </div>

                <!-- Structured Details: Receipt, Booklet, Boxfile -->
                <div class="space-y-1.5 pt-2 border-t border-slate-800/80 text-xs">
                    <!-- وەسڵی 37/أ -->
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">وەسڵی ۳۷/أ:</span>
                        @if($transaction->receipt_37a_number)
                            <span class="font-mono font-black text-white bg-emerald-950 px-2 py-0.5 rounded border border-emerald-600 text-[11px]">
                                {{ $transaction->receipt_37a_number }}
                            </span>
                        @else
                            <span class="text-[11px] text-amber-400 font-bold">نەبڕدراوە</span>
                        @endif
                    </div>

                    <!-- دەفتەری گەشتیاری -->
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 text-[11px] flex items-center gap-1">
                            <i class="fa-solid fa-passport text-indigo-400 text-[10px]"></i>
                            <span>دەفتەری گەشتیاری:</span>
                        </span>
                        @if($transaction->booklet_number)
                            <span class="font-mono font-black text-indigo-300 bg-indigo-950/60 px-2 py-0.5 rounded border border-indigo-700/60 text-[11px]">
                                {{ $transaction->booklet_number }}
                            </span>
                        @else
                            <span class="text-slate-500 text-[11px]">چاوەڕوانە</span>
                        @endif
                    </div>

                    <!-- شوێنی بۆکس -->
                    <div class="flex items-center justify-between pt-1 border-t border-slate-800/50">
                        <span class="text-slate-400 text-[11px] flex items-center gap-1">
                            <i class="fa-solid fa-box-archive text-amber-400 text-[10px]"></i>
                            <span>شوێنی بۆکس:</span>
                        </span>
                        <div class="font-mono text-[11px]">
                            @if($transaction->NO_boxfile)
                                <span class="font-bold text-sky-400">ب: {{ $transaction->NO_boxfile }}</span>
                            @endif
                            @if($transaction->NO_boxfile_puchal)
                                <span class="font-bold text-rose-400 mr-1">(پ: {{ $transaction->NO_boxfile_puchal }})</span>
                            @endif
                            @if(!$transaction->NO_boxfile && !$transaction->NO_boxfile_puchal)
                                <span class="text-slate-500 text-[11px]">تۆمار نەکراوە</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- D. RELATED TRANSACTIONS TRAY (IF ANY) -->
        @if($transaction->relatedTransactions->count() > 0)
            <div class="px-4 py-2 bg-slate-950/70 border-t border-slate-800/80 flex items-center flex-wrap gap-2 text-xs">
                <span class="text-[11px] font-bold text-sky-400 flex items-center gap-1.5 shrink-0">
                    <i class="fa-solid fa-network-wired text-[10px]"></i>
                    <span>مامەڵە پەیوەندیدارەکان:</span>
                </span>
                @foreach($transaction->relatedTransactions as $rel)
                    <a href="{{ route('transactions.show', $rel->id) }}" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-slate-900 hover:bg-slate-800 border border-slate-700 hover:border-sky-500/50 rounded-lg text-sky-300 hover:text-white transition shadow-sm text-xs">
                        <span class="font-mono font-black">{{ $rel->barcode }}</span>
                        <span class="text-slate-300">({{ $rel->transactionType->name_kurdish ?? 'مامەڵە' }})</span>
                        <i class="fa-solid fa-arrow-left text-[9px] text-slate-400"></i>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Alert Banner for Returned Transactions -->
    @if($transaction->is_returned)
        @php
            $isReturnedFromAudit = ($transaction->returned_from_stage === 'audit' || ($transaction->is_inspected && $transaction->returned_from_stage !== 'inspection'));
        @endphp
        <div class="bg-rose-950/80 border-2 border-rose-500/80 rounded-2xl p-4 shadow-2xl flex items-start space-x-3 space-x-reverse animate-pulse">
            <i class="fa-solid fa-triangle-exclamation text-rose-400 text-2xl mt-0.5"></i>
            <div class="space-y-1">
                <h4 class="text-sm font-extrabold text-rose-200">
                    ئەم مامەڵەیە بەهۆی هەڵەوە لە قۆناغی ({{ $isReturnedFromAudit ? 'وردبینی' : 'تەخمین' }}) گەڕێنراوەتەوە:
                </h4>
                <p class="text-xs text-rose-300 leading-relaxed font-bold">
                    هۆکاری گەڕاندنەوە: <span class="bg-rose-900/90 text-white px-2 py-0.5 rounded border border-rose-700 font-mono">{{ $transaction->return_reason }}</span>
                </p>
                <p class="text-[11px] text-rose-300">
                    * کات و بەکارهێنەر: {{ $transaction->returnedBy->name ?? 'کارمەند' }} ({{ $transaction->updated_at->diffForHumans() }})
                    @if($isReturnedFromAudit)
                        — <strong class="text-emerald-300">قۆناغی تەخمین پارێزراوە؛</strong> دوای چاککردنی هەڵەکە لەلایەن داتائەنتەری، ڕاستەوخۆ دەگەڕێتەوە بۆ بەردەستی وردبین.
                    @else
                        — دوای چاککردنی هەڵەکە لەلایەن داتائەنتەری، پێویستە سەرەتا تەخمین و پاشان وردبینی بۆ بکرێتەوە.
                    @endif
                </p>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- DEDICATED AUDIT STAGE WORKSPACE (فۆرمی قۆناغی وردبینی - پیشاندانی ئاسان) -->
    <!-- ========================================================================= -->
    @if($transaction->is_inspected && !$transaction->is_audited)
    <div class="bg-gradient-to-b from-slate-900 via-slate-900 to-purple-950/40 border-2 border-purple-500 rounded-2xl p-3.5 sm:p-5 shadow-2xl space-y-3.5 relative ring-4 ring-purple-500/15">
        
        <!-- جۆری مامەڵە بە گەورەیی و ڕوونی لە سەرەوەی فۆرمی وردبینی -->
        <div class="p-3.5 rounded-xl border-2 {{ $typeTheme['border'] ?? 'border-purple-500/80 shadow-purple-500/20' }} bg-slate-950/95 flex flex-wrap items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl {{ $typeTheme['iconBg'] ?? 'bg-purple-500/20 text-purple-400' }} shrink-0">
                    <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-folder-open' }}"></i>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                    <span class="text-lg sm:text-xl font-black text-white tracking-wide">{{ $typeName }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-black px-3 py-1 rounded-lg bg-purple-500/20 text-purple-300 border border-purple-500/40">
                    قۆناغی ٣: پەسەندکردنی وردبینی
                </span>
                <span class="font-mono text-sm font-black text-sky-400 bg-slate-900 px-3 py-1 rounded-lg border border-slate-700">
                    #{{ $transaction->barcode }}
                </span>
            </div>
        </div>

        <!-- بەشی سەرەوە: ئەو زانیارییانەی پێویستە ببینرێت لە سەرەوەی فۆرمەکە دەربکەوێت -->
        <div class="bg-slate-950/95 border border-purple-500/40 rounded-xl p-3 sm:p-3.5 shadow-xl flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-600 via-indigo-600 to-purple-500 flex items-center justify-center text-white text-lg shadow-lg shadow-purple-600/30 shrink-0">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm sm:text-base font-black text-white truncate">
                        {{ $transaction->visitor_name }} 
                        <span class="text-xs font-normal text-slate-400">({{ $transaction->plate_number }} - {{ $transaction->plateType->clean_kurdish_name ?? $transaction->plateType->name_kurdish ?? 'تایبەت' }})</span>
                    </h3>
                </div>
            </div>

            <!-- بڕی پارەی پێویست بۆ وردبینی لە سەرەوە -->
            <div class="flex items-center flex-wrap gap-4 w-full lg:w-auto justify-between lg:justify-end border-t lg:border-t-0 border-slate-800 pt-2 lg:pt-0">
                <div class="text-right bg-emerald-950/40 border border-emerald-500/40 px-3.5 py-1.5 rounded-xl">
                    <span class="text-[10px] text-emerald-300 block font-bold">کۆی بڕی پارەی پێویست:</span>
                    <span class="text-base sm:text-lg font-black font-mono text-emerald-400">
                        {{ number_format($transaction->total_pay) }} <span class="text-xs font-sans text-emerald-300 font-normal">دینار</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- ئاگاداری فەرمی ونبوونی دەفتەر بۆ وردبین -->
        @if($transaction->is_booklet_lost || !empty($transaction->lost_letter_number))
        <div class="bg-gradient-to-r from-amber-950/90 via-amber-900/80 to-amber-950/90 border-2 border-amber-500 rounded-xl p-3.5 sm:p-4 shadow-xl flex items-start gap-3.5 text-amber-200 ring-2 ring-amber-500/30">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-xl font-black shrink-0 shadow-lg shadow-amber-500/30">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="flex-1 space-y-1">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h4 class="text-sm font-black text-amber-300 flex items-center gap-2">
                        <span>ئاگاداری بۆ وردبین: دەفتەری ئەم ئۆتۆمبێلە ونبووە!</span>
                    </h4>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-500 text-slate-950 shadow-sm">
                        دەفتەری ونبوو
                    </span>
                </div>
                <p class="text-xs font-bold text-slate-100 leading-relaxed">
                    ئاماژە بە ونبوونی دەفتەری ئۆتۆمبێلی ناوبراو، پاڵپشت بە نووسراوی 
                    <span class="text-amber-300 font-black underline">{{ $transaction->lost_directorate_name ?: 'بەڕێوەبەرایەتی ونبووەکان' }}</span>
                    ژمارە ( <span class="font-mono text-white text-sm font-black px-1.5 py-0.5 bg-black/50 rounded border border-amber-500/50">{{ $transaction->lost_letter_number ?: '-' }}</span> ) 
                    لە ڕێکەوتی ( <span class="font-mono text-white text-sm font-black px-1.5 py-0.5 bg-black/50 rounded border border-amber-500/50">{{ $transaction->lost_letter_date ? $transaction->lost_letter_date->format('Y-m-d') : '-' }}</span> ).
                </p>
            </div>
        </div>
        @endif

        <!-- دابەشکردنی خوارەوە: زۆربەی زانیارییەکان لە لایەکی فۆرمەکە (لای ڕاست) و بڕیاری وردبینی لە لایەکەی تر (لای چەپ) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 items-start">
            
            <!-- لای ڕاست (8 لە 12): زۆربەی ئەو زانیارییانەی پێویستە وردبینی بکرێن -->
            <div class="lg:col-span-8 bg-slate-950/80 border border-slate-800 rounded-xl p-3 sm:p-3.5 space-y-3 shadow-xl">
                
                <!-- هێڵی ژمارەی شاسی و تابلۆ بە یەک هێڵی spotlight پێشەنگ -->
                <div class="bg-gradient-to-r from-sky-950/70 via-slate-900 to-indigo-950/50 border-2 border-sky-500/60 p-2.5 sm:p-3 rounded-xl flex items-center justify-between flex-wrap gap-2 shadow-inner">
                    <!-- Chassis VIN -->
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid fa-fingerprint"></i>
                        </span>
                        <div>
                            <span class="text-[10px] text-sky-400 font-bold block uppercase tracking-wider">ژمارەی شاسی (CHASSIS VIN)</span>
                            <span class="font-mono text-sm sm:text-base font-black text-amber-300 uppercase tracking-widest select-all">{{ $transaction->chassis_number }}</span>
                        </div>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $transaction->chassis_number }}'); alert('ژمارەی شاسی کۆپی کرا');" class="mr-1 px-2 py-0.5 bg-sky-600/30 hover:bg-sky-600/50 text-sky-300 border border-sky-500/40 rounded text-[11px] font-bold transition flex items-center gap-1" title="کۆپیکردنی شاسی">
                            <i class="fa-regular fa-copy"></i>
                            <span>کۆپیکردن</span>
                        </button>
                    </div>

                    <!-- Plate & Directorate Spotlight -->
                    <div class="flex items-center gap-2 text-xs">
                        <div class="bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-700 text-center font-mono font-black text-white text-sm" dir="ltr">
                            {{ $transaction->plate_number }}
                        </div>
                        <div class="text-[11px] text-slate-300 text-right leading-tight">
                            <span class="font-bold block">{{ $transaction->trafficDirectorate->name_kurdish ?? '' }}</span>
                            <span class="text-[10px] text-slate-400">{{ $transaction->plateType->clean_kurdish_name ?? $transaction->plateType->name_kurdish ?? 'تایبەت' }}</span>
                        </div>
                    </div>
                </div>

                <!-- 2-COLUMN SIDE-BY-SIDE GRID: هەردوو بەشەکە پێکەوە هاوتەریب دەبینرێن بەبێ پێویستی بە سکرۆڵ -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-stretch">
                    
                    <!-- کۆڵۆمی 1: زانیارییەکانی ئۆتۆمبێل (فۆنتی سپی بۆڵد) -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-xl p-3.5 space-y-2">
                        <h4 class="text-xs font-black text-sky-400 border-b border-slate-800 pb-2 flex items-center gap-1.5 mb-2">
                            <i class="fa-solid fa-car-side"></i>
                            <span>زانیارییەکانی ئۆتۆمبێل</span>
                        </h4>
                        
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-center py-1 border-b border-slate-800/60">
                                <span class="text-slate-400 text-xs font-medium">مارکە و جۆر:</span>
                                <span class="text-white font-black text-xs sm:text-sm text-left">
                                    {{ $transaction->carMake->name_kurdish ?? '-' }}
                                    @if($transaction->carMake && $transaction->carMake->name_english)
                                        <span class="text-slate-300 font-mono text-[11px] block font-bold">({{ $transaction->carMake->name_english }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-800/60">
                                <span class="text-slate-400 text-xs font-medium">مۆدێل و پستۆن:</span>
                                <span class="font-mono text-white font-black text-xs sm:text-sm">
                                    {{ $transaction->model_year }} <span class="text-slate-300 text-[11px] font-bold">({{ $transaction->piston_count }} Piston)</span>
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-800/60">
                                <span class="text-slate-400 text-xs font-medium">ڕەنگی ئۆتۆمبێل:</span>
                                <span class="text-white font-black text-xs sm:text-sm">
                                    {{ $transaction->carColor->name_kurdish ?? '-' }}
                                    @if($transaction->carColor && $transaction->carColor->name_english)
                                        <span class="text-slate-300 font-mono text-[11px] font-bold">({{ $transaction->carColor->name_english }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-800/60">
                                <span class="text-slate-400 text-xs font-medium">ژمارەی ساڵانە:</span>
                                <span class="font-mono text-white font-black text-xs sm:text-sm">{{ $transaction->salana_number ?: 'نییە' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-slate-400 text-xs font-medium">ماوەی دەفتەر:</span>
                                <span class="text-white font-black text-xs sm:text-sm text-left">
                                    {{ $transaction->isCancellationOrInspection() ? '-' : ($transaction->num_years ?? 1) . ' ساڵ' }}
                                    @if($transaction->start_date && $transaction->end_date)
                                        <span class="text-slate-300 font-mono text-[11px] block font-bold">{{ $transaction->start_date->format('Y-m-d') }} ➔ {{ $transaction->end_date->format('Y-m-d') }}</span>
                                        @php
                                            $todayDay = now()->startOfDay();
                                            $endDay = $transaction->end_date->startOfDay();
                                            $diffDays = (int)$todayDay->diffInDays($endDay, false);
                                        @endphp
                                        @if($diffDays < 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                                <i class="fa-solid fa-triangle-exclamation"></i> بەسەرچووە (بە {{ abs($diffDays) }} ڕۆژ)
                                            </span>
                                        @elseif($diffDays === 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                                <i class="fa-solid fa-clock"></i> ئەمڕۆ بەسەردەچێت (ڕۆژی کۆتایی)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                <i class="fa-solid fa-calendar-check"></i> ماوە: {{ $diffDays }} ڕۆژ
                                            </span>
                                        @endif
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- کۆڵۆمی 2: زانیاری هاووڵاتی و شۆفێر (فۆنتی سپی بۆڵد) -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-xl p-3.5 space-y-2">
                        <h4 class="text-xs font-black text-emerald-400 border-b border-slate-800 pb-2 flex items-center gap-1.5 mb-2">
                            <i class="fa-solid fa-user-check"></i>
                            <span>زانیاری هاووڵاتی و شۆفێر</span>
                        </h4>
                        
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-start py-1 border-b border-slate-800/60">
                                <span class="text-slate-400 text-xs font-medium">خاوەن مامەڵە:</span>
                                <span class="text-white font-black text-sm sm:text-base text-left">
                                    {{ $transaction->visitor_name }}
                                    @if($transaction->visitor_name_eng)
                                        <span class="text-slate-300 font-mono text-[11px] block uppercase font-bold" dir="ltr">ENG: {{ $transaction->visitor_name_eng }}</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-800/60">
                                <span class="text-slate-400 text-xs font-medium">ژمارەی مۆبایل:</span>
                                @if($transaction->phone_number)
                                    <a href="tel:{{ $transaction->phone_number }}" class="font-mono text-white font-black text-xs sm:text-sm hover:underline" dir="ltr">{{ $transaction->phone_number }}</a>
                                @else
                                    <span class="text-slate-400 text-xs">تۆمارنەکراوە</span>
                                @endif
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-800/60">
                                <span class="text-slate-400 text-xs font-medium">ناونیشان:</span>
                                <span class="text-white font-black text-xs sm:text-sm truncate max-w-[180px]">{{ $transaction->address ?: 'تۆمارنەکراوە' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-800/60">
                                <span class="text-slate-400 text-xs font-medium">پاسپۆرت:</span>
                                <span class="font-mono text-white font-black text-xs sm:text-sm">
                                    {{ $transaction->passport_number ?: 'تۆمارنەکراوە' }}
                                    @if($transaction->passport_issue_date)
                                        <span class="text-slate-300 font-bold mr-1 text-[11px]">({{ $transaction->passport_issue_date->format('Y-m-d') }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-slate-400 text-xs font-medium">شۆفێری دووەم:</span>
                                <span class="text-white font-black text-xs sm:text-sm text-left">
                                    {{ $transaction->second_driver_name ?: 'دیارینەکراوە' }}
                                    @if($transaction->second_driver_name_eng)
                                        <span class="text-slate-300 font-mono text-[11px] block font-bold" dir="ltr">{{ $transaction->second_driver_name_eng }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- لای چەپ (4 لە 12): بڕیاری وردبینی، پەسەندکردن، و ڕسوومات -->
            <div class="lg:col-span-4 bg-slate-950/90 border-2 border-purple-500/50 rounded-xl p-3.5 sm:p-4 space-y-3 shadow-xl sticky top-20">
                <div class="border-b border-slate-800 pb-2.5 flex items-center justify-between">
                    <h4 class="text-xs sm:text-sm font-black text-purple-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>بڕیاری وردبینی</span>
                    </h4>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/40">
                        ئامادەیە بۆ پەسەندکردن
                    </span>
                </div>

                @if($transaction->is_booklet_lost || !empty($transaction->lost_letter_number))
                <div class="bg-amber-500/20 border-2 border-amber-500/60 rounded-xl p-2.5 text-xs font-bold text-amber-200 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-amber-400 text-base shrink-0"></i>
                    <div class="leading-tight">
                        <span class="font-black text-amber-300 block">دەفتەری ئەم ئۆتۆمبێلە ونبووە:</span>
                        <span class="text-[11px] font-mono text-slate-200">نووسراوی ژمارە {{ $transaction->lost_letter_number ?? '-' }} لە {{ $transaction->lost_letter_date ? $transaction->lost_letter_date->format('Y-m-d') : '-' }}</span>
                    </div>
                </div>
                @endif

                <!-- پوختەی پارە و ڕسوومات -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-2.5 space-y-1.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400 text-[11px]">ڕسووماتی مامەڵە:</span>
                        <span class="font-mono font-bold text-white">{{ number_format($transaction->total_pay) }} د.ع</span>
                    </div>
                    @if($linkedPledge)
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400 text-[11px]">ڕسووماتی بەڵێننامە:</span>
                        <span class="font-mono font-bold text-amber-300">{{ number_format($linkedPledge->total_pay) }} د.ع</span>
                    </div>
                    <div class="pt-1.5 border-t border-slate-800 flex justify-between items-center text-xs">
                        <span class="text-slate-300 font-bold text-[11px]">کۆی هەردووکیان:</span>
                        <span class="font-mono font-black text-emerald-400 text-sm">{{ number_format($transaction->total_pay + $linkedPledge->total_pay) }} د.ع</span>
                    </div>
                    @endif
                </div>

                <!-- فۆرمی فەرمی پەسەندکردنی وردبینی -->
                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.audit'))
                <form action="{{ route('transactions.audit', $transaction->id) }}" method="POST" class="space-y-2.5">
                    @csrf
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[11px] font-bold text-slate-300">
                                دووپاتکردنەوەی بڕی پارە (دینار):
                            </label>
                            <button type="button" @click="confirmAuditAmount = expectedAuditAmount" class="text-[10px] text-purple-400 hover:text-purple-300 font-bold hover:underline">
                                خۆکار پڕی بکەوە
                            </button>
                        </div>
                        <input type="text" 
                               name="confirm_total_pay" 
                               x-model="confirmAuditAmount" 
                               placeholder="{{ number_format($transaction->total_pay) }}" 
                               required 
                               autocomplete="off"
                               class="w-full bg-slate-950 border border-slate-700 focus:border-purple-500 rounded-xl px-3 py-2 text-sm font-bold text-slate-100 text-center tracking-wider focus:outline-none focus:ring-2 focus:ring-purple-500/20" />
                        
                        <template x-if="confirmAuditAmount.length > 0 && confirmAuditAmount.replace(/\D/g, '') !== expectedAuditAmount">
                            <p class="text-[10px] text-rose-400 font-bold mt-1 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                بڕی پارەکە یەکسان نییە ({{ number_format($transaction->total_pay) }} دینار)
                            </p>
                        </template>
                    </div>

                    <button type="submit" 
                            :disabled="confirmAuditAmount.length > 0 && confirmAuditAmount.replace(/\D/g, '') !== expectedAuditAmount"
                            class="w-full py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg shadow-purple-600/30 transition flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-clipboard-check text-base"></i>
                        <span>پەسەندکردنی فەرمی وردبینی</span>
                    </button>
                </form>

                <!-- دوگمەی گەڕاندنەوە بۆ تەخمین لە کاتی بوونی هەڵە -->
                <button type="button" @click="returnStage = 'audit'; showReturnModal = true" class="w-full py-2 bg-rose-950/60 hover:bg-rose-900 border border-rose-600/50 text-rose-300 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>گەڕاندنەوە بۆ تەخمین (هەڵەی زانیاری)</span>
                </button>
                @endif

                <!-- چاپی نووسراوە ئیدارییەکان لە قۆناغی وردبینی -->
                <div class="pt-2 border-t border-slate-800 space-y-1">
                    <span class="text-[10px] text-slate-400 font-bold block">نووسراوە ئیدارییە پەیوەندیدارەکان:</span>
                    @if($transaction->isCancellation())
                        <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank" class="w-full py-1 px-2.5 bg-slate-900 hover:bg-slate-800 text-rose-300 border border-rose-600/40 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-file-circle-xmark"></i> چاپی نووسراوی پووچەڵکردنەوە
                        </a>
                    @elseif($transaction->isBookletIssue())
                        <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank" class="w-full py-1 px-2.5 bg-slate-900 hover:bg-slate-800 text-amber-300 border border-amber-600/40 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-file-circle-exclamation"></i> چاپی نیشانەی ڕەفتارنەکردن
                        </a>
                    @endif
                    @if($linkedPledge)
                        <a href="{{ route('transactions.print_pledge', $transaction->id) }}" target="_blank" class="w-full py-1 px-2.5 bg-slate-900 hover:bg-slate-800 text-amber-300 border border-amber-600/40 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-file-contract"></i> چاپی بەڵێننامەی یاسایی
                        </a>
                    @endif
                </div>

            </div>

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
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white text-sm shadow">
                    <i class="fa-solid fa-diagram-project"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white flex items-center gap-2 flex-wrap">
                        <span>قۆناغەکانی کارکردنی مامەڵە</span>
                        <span class="text-sm font-black px-3 py-1 rounded-xl border-2 {{ $typeTheme['pill'] ?? 'bg-indigo-500/15 text-indigo-300 border-indigo-500/30' }} inline-flex items-center gap-1.5 shadow-sm">
                            <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-folder-open text-indigo-400' }} text-xs"></i>
                            <span>{{ $typeName }}</span>
                        </span>
                    </h3>
                </div>
            </div>

            <!-- دۆخی قۆناغی ئێستا بە پوختی -->
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
                            <button type="button" @click="returnStage = 'inspection'; showReturnModal = true" class="px-4 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/20 transition flex items-center">
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

                            @if($transaction->is_booklet_lost || !empty($transaction->lost_letter_number))
                                <span class="px-2.5 py-1.5 bg-amber-500/20 text-amber-300 border border-amber-500/50 rounded-xl text-xs font-black flex items-center gap-1.5" title="دەفتەری ئەم ئۆتۆمبێلە ونبووە">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-400"></i>
                                    دەفتەری ونبوو
                                </span>
                            @endif

                            <button type="button" @click="returnStage = 'audit'; showReturnModal = true" class="px-4 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/20 transition flex items-center">
                                <i class="fa-solid fa-rotate-left ml-1.5"></i> گەڕاندنەوە بەهۆی بوونی هەڵە
                            </button>
                        @else
                            <span class="px-3 py-1.5 bg-purple-500/10 text-purple-300 border border-purple-500/30 rounded-xl text-xs font-bold flex items-center">
                                <i class="fa-solid fa-clock ml-1.5 text-purple-400"></i> چاوەڕێی پەسەندکردنی وردبینییە (تەنها کارمەندی وردبین دەتوانێت پەسەندی بکات)
                            </span>
                        @endif
                    @endif

                    <!-- If Audited, but before Payment: Auditor/Admin can Revert Audit or Return to Previous Step -->
                    @if($transaction->is_audited && !$transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->role === 'auditor' || auth()->user()->hasPermission('transactions.audit')))
                        <form action="{{ route('transactions.revert_audit', $transaction->id) }}" method="POST" class="inline" onsubmit="return confirm('ئایا دڵنیایت لە هەڵوەشاندنەوەی پەسەندی وردبینی؟ مامەڵەکە دەگەڕێتەوە بۆ چاوەڕوانی وردبینی.');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-purple-700 hover:bg-purple-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-purple-700/25 transition flex items-center">
                                <i class="fa-solid fa-arrow-rotate-left ml-1.5 text-purple-200"></i> هەڵوەشاندنەوەی پەسەندی وردبینی (Revert Audit)
                            </button>
                        </form>

                        <button type="button" @click="returnStage = 'audit'; showReturnModal = true" class="px-4 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/20 transition flex items-center">
                            <i class="fa-solid fa-arrow-turn-down-left ml-1.5"></i> گەڕاندنەوەی هەڵە بۆ داتائەنتەری (هەڵوەشاندنەوەی وردبینی)
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

                    <!-- قۆناغی پارەدان - چاپی پسوولە -->
                    @if(($transaction->is_paid || ($hasCompanion && $bookletTx->is_paid)) && (auth()->user()->isAdmin() || auth()->user()->role === 'cashier' || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.receipt')))
                        @if(!$transaction->is_submitted || auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.receipt'))
                            @if(auth()->user()->isAdmin() || auth()->user()->role === 'cashier' || auth()->user()->hasPermission('prints.receipt'))
                                @if($hasCompanion && $bookletTx->is_paid && $pledgeTx->is_paid)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('transactions.print_payment_receipt', [$bookletTx->id, 'mode' => 'both']) }}" target="_blank" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center gap-1.5" title="چاپی هەردوو پسوولەی ۳۷/أ پێکەوە">
                                            <i class="fa-solid fa-receipt"></i>
                                            <span>چاپی هەردوو ۳۷/أ (پێکەوە)</span>
                                        </a>
                                        <a href="{{ route('transactions.print_payment_receipt', [$bookletTx->id, 'mode' => 'main']) }}" target="_blank" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-sky-300 hover:text-white text-xs font-bold rounded-xl border border-sky-500/30 transition flex items-center gap-1" title="تەنها وەسڵی دەرهێنان">
                                            <i class="fa-solid fa-book-open text-[11px]"></i>
                                            <span>وەسڵی دەرهێنان</span>
                                        </a>
                                        <a href="{{ route('transactions.print_payment_receipt', [$bookletTx->id, 'mode' => 'pledge']) }}" target="_blank" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-amber-300 hover:text-white text-xs font-bold rounded-xl border border-amber-500/30 transition flex items-center gap-1" title="تەنها وەسڵی بەڵێننامە">
                                            <i class="fa-solid fa-file-contract text-[11px]"></i>
                                            <span>وەسڵی بەڵێننامە</span>
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('transactions.print_payment_receipt', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center">
                                        <i class="fa-solid fa-receipt ml-1.5"></i> چاپی پسوولەی ۳۷/أ
                                    </a>
                                @endif
                            @endif
                        @endif
                    @endif

                    <!-- قۆناغی ئەرشیف - چاپی نووسراوی پشکنین (تەنها لە ئەرشیف نەک لە قۆناغی پارەدان) -->
                    @if($transaction->is_submitted && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.inspection_letter')))
                        <a href="{{ route('transactions.print_inspection_letter', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center" title="چاپی نووسراوی پشکنین">
                            <i class="fa-solid fa-file-circle-check ml-1.5"></i> چاپی نووسراوی پشکنین
                        </a>
                    @endif

                    <!-- Step 5: Booklet Complete (Booklet / Admin) - ONLY if requiresBooklet() & NOT pledge/cancel/inspect -->
                    @if($requiresBooklet && !in_array($typeId, [5, 6, 8]) && $transaction->is_paid)
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
                            @if((!$transaction->is_submitted || auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.booklet')) && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet' || auth()->user()->hasPermission('prints.booklet')))
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

                    <!-- Final Submit & Archive Button (Archiver & Admin ONLY - For types 1, 2, 3, 4, 5, 7) (خاڵی ٤) -->
                    @if($isArchiverOrAdmin && $canSubmitThisType && $transaction->is_paid && (!$requiresBooklet || $transaction->is_booklet_completed))
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

                @if($transaction->isCancellation())
                    @php
                        $resolvedCustomsNo = $transaction->getResolvedCustomsLetterNumber();
                        $resolvedCustomsDate = $transaction->getResolvedCustomsLetterDate();
                    @endphp
                    <div class="p-2.5 rounded-xl border border-rose-500/40 bg-rose-950/30 space-y-2 my-1">
                        <div class="flex items-center justify-between">
                            <span class="text-rose-300 font-black text-xs flex items-center gap-1.5">
                                <i class="fa-solid fa-file-contract text-rose-400"></i>
                                <span>نووسراوی گومرگ (کاتی دەرهێنانی دەفتەر):</span>
                            </span>
                            <button type="button" @click="showCustomsLetterModal = true" class="text-[11px] font-bold text-rose-300 hover:text-white bg-rose-600/40 hover:bg-rose-600 px-2 py-0.5 rounded-lg border border-rose-500/40 transition flex items-center gap-1">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>{{ $resolvedCustomsNo ? 'دەستکاری نووسراو' : 'تۆمارکردنی نووسراو' }}</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                            <div class="flex justify-between py-1 px-2 rounded bg-black/30 border border-rose-500/20">
                                <span class="text-slate-400">ژمارەی نووسراو:</span>
                                <span class="font-mono text-white font-black">{{ $resolvedCustomsNo ?? 'تۆمار نەکراوە' }}</span>
                            </div>
                            <div class="flex justify-between py-1 px-2 rounded bg-black/30 border border-rose-500/20">
                                <span class="text-slate-400">بەرواری نووسراو:</span>
                                <span class="font-mono text-white font-bold">{{ $resolvedCustomsDate ? $resolvedCustomsDate->format('Y-m-d') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($transaction->is_booklet_lost || $transaction->lost_letter_number)
                    <div class="p-2.5 rounded-xl border border-amber-500/60 bg-amber-950/30 space-y-1 my-1">
                        <div class="flex items-center justify-between text-xs font-black text-amber-300">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-file-circle-question text-amber-400"></i>
                                <span>دەفتەری ونبوو (نووسراوی ونبووەکان)</span>
                            </span>
                            <span class="text-[10px] bg-amber-500 text-slate-900 px-1.5 py-0.2 rounded font-bold">ونبوو</span>
                        </div>
                        <div class="text-[11px] text-slate-300 space-y-0.5">
                            <p>ژمارەی نووسراو: <strong class="font-mono text-white">{{ $transaction->lost_letter_number ?? '-' }}</strong></p>
                            @if($transaction->lost_letter_date)
                                <p>بەرواری نووسراو: <strong class="font-mono text-white">{{ $transaction->lost_letter_date->format('Y-m-d') }}</strong></p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- نووسراوی گومرگ (دەرهێنان) -->
                @if($transaction->no_nusraw_gumrg || in_array((int)$transaction->transaction_type_id, [1, 2, 3, 4, 7]))
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-700/40 bg-slate-900/40 px-2 rounded">
                        <span class="text-slate-300 font-bold flex items-center">
                            <i class="fa-solid fa-file-contract text-emerald-400 ml-1.5"></i> نووسراوی گومرگ (دەرهێنان):
                        </span>
                        <div class="font-mono text-xs flex items-center gap-2">
                            <span class="text-white font-black">{{ $transaction->no_nusraw_gumrg ?? 'دیارینەکراوە' }}</span>
                            @if($transaction->date_nusraw_gumrg)
                                <span class="text-slate-400">({{ $transaction->date_nusraw_gumrg->format('Y-m-d') }})</span>
                            @endif
                        </div>
                    </div>
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
                @if($transaction->pay_fine > 0)
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-rose-400 font-bold">
                        @if((int)$transaction->transaction_type_id === 3)
                            سزای ناوگۆڕین:
                        @elseif((int)$transaction->transaction_type_id === 4)
                            سزای دەفتەرگۆڕین:
                        @elseif((int)$transaction->transaction_type_id === 7)
                            سزای ناو و دەفتەرگۆڕین:
                        @else
                            سزا (غەرامە):
                        @endif
                    </span>
                    <span class="font-mono text-rose-300 font-bold">{{ number_format($transaction->pay_fine) }} د.ع</span>
                </div>
                @endif
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span class="text-slate-400">کەشف و تەخمین:</span>
                    <span class="font-mono text-slate-200">{{ number_format($transaction->pay_inspection) }} د.ع</span>
                </div>
                @if($hasCompanion)
                    <div class="flex justify-between py-1 text-xs border-t border-slate-700/60 text-slate-300">
                        <span>رسوماتی دەرهێنانی دەفتەر:</span>
                        <span class="font-mono text-sky-400 font-bold">{{ number_format($bookletFee) }} د.ع</span>
                    </div>
                    <div class="flex justify-between py-1 text-xs text-amber-300">
                        <span>رسوماتی بەڵێننامەی یاسایی:</span>
                        <span class="font-mono font-bold">{{ number_format($pledgeFee) }} د.ع</span>
                    </div>
                    <div class="flex justify-between py-2 font-black text-base border-t border-slate-700 text-emerald-400">
                        <span>کۆی گشتی هەردوو مامەڵە:</span>
                        <span class="font-mono text-xl font-black text-emerald-300">{{ number_format($totalCombinedPay) }} د.ع</span>
                    </div>
                @else
                    <div class="flex justify-between py-2 font-bold text-base border-t border-slate-700">
                        <span class="text-white">کۆی تێکڕای پارەداوە:</span>
                        <span class="font-mono text-emerald-400 font-extrabold">{{ number_format($transaction->total_pay) }} د.ع</span>
                    </div>
                @endif
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
    <div x-show="showPayModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-md z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-slate-900 border border-slate-700/80 rounded-2xl max-w-lg w-full shadow-2xl max-h-[90vh] flex flex-col overflow-hidden text-right ring-2 ring-emerald-500/20" @click.away="showPayModal = false">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-800 bg-slate-950/40 shrink-0">
                <h3 class="text-base font-black text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-receipt"></i>
                    </span>
                    <span>تۆمارکردنی وەسڵی ۳۷/أ و وەرگرتنی پارە</span>
                </h3>
                <button type="button" @click="showPayModal = false" class="text-slate-400 hover:text-white p-1 transition"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="{{ route('transactions.pay', $hasCompanion ? $bookletTx->id : $transaction->id) }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <div class="p-5 overflow-y-auto space-y-3.5 flex-1 touch-scroll">
                    <!-- جۆری مامەڵە بە بارزی لە مۆداڵی پارەدان -->
                    <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] ?? 'border-emerald-500/80 shadow-emerald-500/20' }} bg-slate-950/95 flex items-center justify-between shadow-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] ?? 'bg-emerald-500/20 text-emerald-400' }} shrink-0">
                                <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-receipt' }}"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                                <span class="text-lg font-black text-white">{{ $typeName }}</span>
                            </div>
                        </div>
                        <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-black/50 text-sky-300 border border-slate-700">
                            #{{ $transaction->barcode }}
                        </span>
                    </div>

                    <!-- کارتی سەرەکی پارەدان (پوخت و ڕوون) -->
                    @if($hasCompanion)
                        <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-3.5 space-y-2.5">
                            <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-800/80">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span class="font-bold text-slate-300">مامەڵەی {{ $typeName }}</span>
                                </div>
                                <span class="font-mono text-[11px] text-sky-400 font-bold bg-sky-950/60 px-2.5 py-0.5 rounded-lg border border-sky-800/50">
                                    {{ $transaction->barcode }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="bg-slate-900/90 p-2.5 rounded-lg border border-slate-800 flex flex-col justify-between">
                                    <div class="text-[11px] text-slate-400 flex items-center justify-between">
                                        <span>دەرهێنانی دەفتەر:</span>
                                        <span class="font-mono text-[10px] text-slate-500">{{ $bookletTx->barcode }}</span>
                                    </div>
                                    <div class="mt-1 text-left font-mono font-bold text-sky-300 text-sm">
                                        {{ number_format($bookletFee) }} <span class="text-[10px] text-slate-400 font-sans">د.ع</span>
                                    </div>
                                </div>
                                <div class="bg-slate-900/90 p-2.5 rounded-lg border border-slate-800 flex flex-col justify-between">
                                    <div class="text-[11px] text-slate-400 flex items-center justify-between">
                                        <span>بەڵێننامەی یاسایی:</span>
                                        <span class="font-mono text-[10px] text-slate-500">{{ $pledgeTx->barcode }}</span>
                                    </div>
                                    <div class="mt-1 text-left font-mono font-bold text-amber-300 text-sm">
                                        {{ number_format($pledgeFee) }} <span class="text-[10px] text-slate-400 font-sans">د.ع</span>
                                    </div>
                                </div>
                            </div>

                            <!-- کۆی گشتی پارەی وەرگیراو بە گەورەیی -->
                            <div class="bg-gradient-to-l from-emerald-950/90 to-teal-950/80 border border-emerald-500/50 rounded-xl p-3 flex items-center justify-between shadow-inner">
                                <div>
                                    <span class="text-xs font-black text-emerald-200 block">کۆی گشتی بۆ وەرگرتن:</span>
                                    <span class="text-[10px] text-emerald-400/80 font-medium">بڕی کۆکراوەی هەردوو مامەڵەکە</span>
                                </div>
                                <div class="text-left font-mono">
                                    <span class="text-2xl sm:text-3xl font-black text-emerald-300 tracking-tight">{{ number_format($totalCombinedPay) }}</span>
                                    <span class="text-xs font-bold text-emerald-400 mr-1 font-sans">دیناری عێراقی</span>
                                </div>
                            </div>
                        </div>

                        <!-- ژمارەی وەسڵی دەرهێنانی دەفتەر -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <label class="font-bold text-sky-300 flex items-center gap-1.5">
                                    <i class="fa-solid fa-receipt text-sky-400"></i>
                                    <span>ژمارەی وەسڵی ۳۷/أ بۆ دەرهێنانی دەفتەر ({{ $bookletTx->barcode }})</span>
                                    <span class="text-rose-400">*</span>
                                </label>
                                <span class="text-[11px] font-mono text-sky-400 font-bold bg-sky-950/50 px-2 py-0.5 rounded border border-sky-800/40">
                                    {{ number_format($bookletFee) }} د.ع
                                </span>
                            </div>
                            <input type="text" name="receipt_37a_number" required placeholder="مثلاً: 15222" dir="ltr"
                                   x-model="mainReceipt"
                                   @input="if(hasLinked) { linkedReceipt = (!isNaN(parseInt(mainReceipt)) ? (parseInt(mainReceipt) + 1) : '') }"
                                   class="w-full bg-slate-950 border border-slate-700 focus:border-sky-500 rounded-xl px-3.5 py-2 text-sm text-white font-mono focus:outline-none focus:ring-1 focus:ring-sky-500 transition">
                        </div>

                        @if($pledgeTx && !$pledgeTx->is_paid)
                        <!-- ژمارەی وەسڵی بەڵێننامەی یاسایی -->
                        <div class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between text-xs">
                                <label class="font-bold text-amber-300 flex items-center gap-1.5">
                                    <i class="fa-solid fa-link text-amber-400"></i>
                                    <span>ژمارەی وەسڵی ۳۷/أ بۆ بەڵێننامە ({{ $pledgeTx->barcode }})</span>
                                </label>
                                <span class="text-[11px] font-mono text-amber-400 font-bold bg-amber-950/50 px-2 py-0.5 rounded border border-amber-800/40">
                                    {{ number_format($pledgeFee) }} د.ع
                                </span>
                            </div>
                            <input type="text" name="linked_receipt_37a_number" placeholder="مثلاً: 15223" dir="ltr"
                                   x-model="linkedReceipt"
                                   class="w-full bg-slate-950 border border-amber-500/50 focus:border-amber-400 rounded-xl px-3.5 py-2 text-sm text-amber-200 font-mono focus:outline-none focus:ring-1 focus:ring-amber-400 transition">
                            <p class="text-[11px] text-amber-300/80 flex items-center gap-1 pt-0.5">
                                <i class="fa-solid fa-circle-info text-[10px]"></i>
                                <span>بە یەک کردار هەردوو وەسڵەکە یەک لەدوای یەک تۆمار دەکرێن و بەڵێننامەکەش پارەدار دەبێت.</span>
                            </p>
                        </div>
                        @endif
                    @else
                        <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-3.5 space-y-2.5">
                            <div class="flex items-center justify-between text-xs pb-1.5 border-b border-slate-800/80">
                                <span class="font-bold text-slate-300">مامەڵەی {{ $typeName }}</span>
                                <span class="font-mono text-[11px] text-emerald-400 font-bold bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-800/50">
                                    {{ $transaction->barcode }}
                                </span>
                            </div>
                            <div class="bg-gradient-to-l from-emerald-950/90 to-teal-950/80 border border-emerald-500/50 rounded-xl p-3 flex items-center justify-between">
                                <span class="text-xs font-black text-emerald-200">بڕی پارەی پێویست بۆ وەرگرتن:</span>
                                <div class="text-left font-mono">
                                    <span class="text-2xl font-black text-emerald-300">{{ number_format($transaction->total_pay) }}</span>
                                    <span class="text-xs font-bold text-emerald-400 mr-1 font-sans">دیناری عێراقی</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <label class="font-bold text-slate-300">
                                    ژمارەی پسوولەی ۳۷/أ (مامەڵەی سەرەکی) *
                                </label>
                                <span class="text-[11px] font-mono font-bold text-emerald-400">{{ number_format($transaction->total_pay) }} د.ع</span>
                            </div>
                            <input type="text" name="receipt_37a_number" required placeholder="مثلاً: 100234" dir="ltr"
                                   x-model="mainReceipt"
                                   class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2 text-sm text-slate-100 font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        </div>
                    @endif
                </div>

                <!-- Modal Footer (داککراو و هەمیشە دیار لە خوارەوە بەبێ بڕین) -->
                <div class="px-5 py-3.5 bg-slate-950/90 border-t border-slate-800 flex items-center justify-between gap-3 shrink-0">
                    <button type="button" @click="showPayModal = false" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold rounded-xl transition">
                        پاشگەزبوونەوە
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-lg shadow-emerald-900/40 hover:shadow-emerald-900/60 transition flex items-center gap-2">
                        <i class="fa-solid fa-check text-sm"></i>
                        <span>تۆمار و وەرگرتن</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Prompt Official Letter Print after Audit (قۆناغی وردبینی - داوای چاپی نووسراو بۆ دەرهێنان و پووچەڵکردنەوە) (خاڵی ۲) -->
    <div x-show="showAuditPromptModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-md z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-slate-900 border-2 border-purple-500/60 rounded-2xl max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto touch-scroll" @click.away="showAuditPromptModal = false">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="text-base font-black text-purple-300 flex items-center gap-2">
                    <i class="fa-solid fa-stamp text-purple-400 text-lg"></i>
                    <span>وردبینی مامەڵەکە بە سەرکەوتوویی پەسەندکرا</span>
                </h3>
                <button type="button" @click="showAuditPromptModal = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-4 bg-purple-950/40 border border-purple-500/40 rounded-xl space-y-2">
                <p class="text-sm font-bold text-white">
                    تۆماری دارایی و وردبینی پەسەندکرا. ئایا دەتەوێت نووسراوە فەرمییەکانی پەیوەندیدار چاپ بکەیت؟
                </p>
                <p class="text-xs text-purple-200/80">
                    دەتوانیت هەر ئێستا نووسراوەکە بکەیتەوە و چاپی بکەیت، یان لە کاتێکی تردا چاپی بکەیت.
                </p>
            </div>

            <div class="space-y-2.5">
                @if((int)$transaction->transaction_type_id === 1)
                    <!-- دەرهێنانی دەفتەر: تەنها نووسراوی ئاگادارکردنەوە / نیشانەی ڕەفتارنەکردن (ڕێگەپێدان) -->
                    <a href="{{ route('transactions.print_restriction', $transaction->id) }}" target="_blank"
                       @click="showAuditPromptModal = false"
                       class="w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-file-shield text-base"></i>
                            <span>چاپی نووسراوی دانانی نیشانەی ڕەفتارنەکردن (ڕێگەپێدان)</span>
                        </span>
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                @elseif((int)$transaction->transaction_type_id === 5)
                    <!-- پووچەڵکردنەوەی دەفتەر: نووسراوی پووچەڵکردنەوە -->
                    <a href="{{ route('transactions.print_cancellation', $transaction->id) }}" target="_blank"
                       @click="showAuditPromptModal = false"
                       class="w-full px-4 py-3 bg-rose-600 hover:bg-rose-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-file-circle-xmark text-base"></i>
                            <span>چاپی نووسراوی فەرمی پووچەڵکردنەوە</span>
                        </span>
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                @endif
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-700/60">
                <button type="button" @click="showAuditPromptModal = false" class="px-5 py-2 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white text-xs font-bold rounded-xl transition">
                    داخستن
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: Prompt Payment Receipt Print after Cashier Payment (خاڵی گرنگ: مۆداڵی داوای چاپی پسوولەکان دوای پارەدان) -->
    <div x-show="showPaymentPromptModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-md z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-slate-900 border-2 border-emerald-500/60 rounded-2xl max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto touch-scroll" @click.away="showPaymentPromptModal = false">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="text-base font-black text-emerald-300 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                    <span>پارەدانی وەسڵی ۳۷/أ بە سەرکەوتوویی تۆمارکرا</span>
                </h3>
                <button type="button" @click="showPaymentPromptModal = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-4 bg-emerald-950/40 border border-emerald-500/40 rounded-xl space-y-2">
                <p class="text-sm font-bold text-white">
                    پارەی مامەڵەکە بە سەرکەوتوویی وەرگیرا و ژمارەی پسوولەکان تۆمارکران:
                </p>
                <div class="text-xs text-emerald-200/90 space-y-1 font-mono">
                    @if($bookletTx && $bookletTx->is_paid)
                        <p>• وەسڵی دەرهێنانی دەفتەر ({{ number_format($bookletFee) }} د.ع): <strong class="text-white">{{ $bookletTx->receipt_37a_number }}</strong></p>
                    @endif
                    @if($pledgeTx && $pledgeTx->is_paid)
                        <p>• وەسڵی بەڵێننامەی یاسایی ({{ number_format($pledgeFee) }} د.ع): <strong class="text-amber-300">{{ $pledgeTx->receipt_37a_number }}</strong></p>
                    @endif
                    @if(!$hasCompanion)
                        <p>• وەسڵی سەرەکی ({{ $typeName }}): <strong class="text-white">{{ $transaction->receipt_37a_number }}</strong></p>
                    @endif
                </div>
            </div>

            @if($hasCompanion && $bookletTx->is_paid && $pledgeTx->is_paid)
                <!-- هەڵبژاردنی شێوازی چاپی پسوولەکان: بەجیا یان پێکەوە (خاڵی داواکراوی بەکارهێنەر) -->
                <div class="p-4 bg-slate-950 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-inner">
                    <div class="text-center space-y-0.5">
                        <span class="text-sm font-black text-white block">ئایا بەجیا پسوولەکان چاپ دەکەیت یان هەردووکیان پێکەوە؟</span>
                        <span class="text-[11px] text-slate-400 block">شێوازی چاپی دڵخوازی خۆت دیاری بکە:</span>
                    </div>

                    <div class="space-y-2.5">
                        <!-- هەڵبژاردنی ١: چاپی هەردووکیان پێکەوە -->
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-emerald-400 block px-1">بژاردەی یەکەم (پێشنیارکراو):</span>
                            <a href="{{ route('transactions.print_payment_receipt', [$bookletTx->id, 'mode' => 'both']) }}" target="_blank"
                               @click="showPaymentPromptModal = false"
                               class="w-full px-4 py-3 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-black rounded-xl shadow-lg shadow-emerald-900/40 transition flex items-center justify-between border border-emerald-400/40">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-copy text-base text-emerald-200"></i>
                                    <span>چاپی هەردووکیان پێکەوە (۲ پەڕەی جیا)</span>
                                </span>
                                <span class="px-2 py-0.5 rounded bg-black/40 text-[11px] font-mono text-emerald-200">دەرهێنان + بەڵێننامە</span>
                            </a>
                        </div>

                        <!-- هەڵبژاردنی ٢: چاپی پسوولەکان بەجیا -->
                        <div class="space-y-1 pt-1.5 border-t border-slate-800">
                            <span class="text-[10px] font-bold text-amber-300 block px-1 flex items-center gap-1">
                                <i class="fa-solid fa-scissors text-[9px]"></i>
                                بژاردەی دووەم (چاپی بەجیا و سەربەخۆ):
                            </span>
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('transactions.print_payment_receipt', [$bookletTx->id, 'mode' => 'main']) }}" target="_blank"
                                   @click="showPaymentPromptModal = false"
                                   class="px-3 py-2.5 bg-slate-800 hover:bg-slate-700 text-sky-300 hover:text-white text-[11px] font-black rounded-xl border border-sky-500/40 transition text-center flex flex-col items-center justify-center gap-0.5 shadow">
                                    <span>تەنها وەسڵی دەرهێنان</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ number_format($bookletFee) }} د.ع</span>
                                </a>
                                <a href="{{ route('transactions.print_payment_receipt', [$bookletTx->id, 'mode' => 'pledge']) }}" target="_blank"
                                   @click="showPaymentPromptModal = false"
                                   class="px-3 py-2.5 bg-amber-950/60 hover:bg-amber-900/70 text-amber-300 hover:text-white text-[11px] font-black rounded-xl border border-amber-500/40 transition text-center flex flex-col items-center justify-center gap-0.5 shadow">
                                    <span>تەنها وەسڵی بەڵێننامە</span>
                                    <span class="text-[10px] text-amber-200/80 font-mono">{{ number_format($pledgeFee) }} د.ع</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('transactions.print_payment_receipt', $transaction->id) }}" target="_blank"
                   @click="showPaymentPromptModal = false"
                   class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-print text-base"></i>
                        <span>چاپی پسوولەی فەرمی ۳۷/أ</span>
                    </span>
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            @endif

            <div class="flex justify-end pt-2 border-t border-slate-700/60">
                <button type="button" @click="showPaymentPromptModal = false" class="px-5 py-2 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white text-xs font-bold rounded-xl transition">
                    داخستن
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: Prompt Data Entry Print after Save or Update (خاڵی ۲) -->
    <div x-show="showDataEntryPromptModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-slate-900 border border-blue-500/50 rounded-2xl max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto touch-scroll">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="text-base font-black text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center">
                        <i class="fa-solid fa-file-invoice"></i>
                    </span>
                    <span>چاپی فیشەی داتائەنتەری و بەدواداچوون</span>
                </h3>
                <button type="button" @click="showDataEntryPromptModal = false" class="text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-3 bg-blue-950/40 border border-blue-500/30 rounded-xl text-blue-200 text-xs font-bold leading-relaxed">
                زانیاری مامەڵەکە بە سەرکەوتوویی تۆمارکرا / ڕاستکرایەوە. دەتوانیت ڕاستەوخۆ فیشەکان چاپ بکەیت:
            </div>

            <div class="space-y-2 pt-1">
                @if($linkedPledge)
                    <a href="{{ route('transactions.print_data_entry', ['transaction' => $transaction->id, 'mode' => 'both']) }}" target="_blank"
                       @click="showDataEntryPromptModal = false"
                       class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-print text-base"></i>
                            <span>چاپی هەردوو فیشە (۲ پەڕەی جیا - دەرهێنان + بەڵێننامە)</span>
                        </span>
                        <span class="px-2 py-0.5 rounded bg-black/30 text-[11px] font-mono">۲ پەڕە</span>
                    </a>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a href="{{ route('transactions.print_data_entry', ['transaction' => $transaction->id, 'mode' => 'main']) }}" target="_blank"
                           @click="showDataEntryPromptModal = false"
                           class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold rounded-xl border border-slate-700 transition text-center flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-file-lines text-sky-400"></i>
                            <span>تەنها فیشەی دەرهێنان</span>
                        </a>
                        <a href="{{ route('transactions.print_data_entry', ['transaction' => $transaction->id, 'mode' => 'pledge']) }}" target="_blank"
                           @click="showDataEntryPromptModal = false"
                           class="px-3 py-2 bg-amber-950/60 hover:bg-amber-900/60 text-amber-300 text-[11px] font-bold rounded-xl border border-amber-600/40 transition text-center flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-file-contract text-amber-400"></i>
                            <span>تەنها فیشەی بەڵێننامە</span>
                        </a>
                    </div>
                @else
                    <a href="{{ route('transactions.print_data_entry', ['transaction' => $transaction->id, 'mode' => 'both']) }}" target="_blank"
                       @click="showDataEntryPromptModal = false"
                       class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-print text-base"></i>
                            <span>چاپی فیشەی داتائەنتەری</span>
                        </span>
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                @endif
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-700/60">
                <button type="button" @click="showDataEntryPromptModal = false" class="px-5 py-2 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white text-xs font-bold rounded-xl transition">
                    داخستن
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: Prompt Inspection Print after Estimation Approval -->
    <div x-show="showInspectionPromptModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-teal-500/50 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="text-base font-black text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-teal-500/20 text-teal-400 border border-teal-500/30 flex items-center justify-center">
                        <i class="fa-solid fa-file-waveform"></i>
                    </span>
                    <span>چاپی پسوولەی کەشف و خەمڵاندن (تەخمین)</span>
                </h3>
                <button type="button" @click="showInspectionPromptModal = false" class="text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-3 bg-teal-950/40 border border-teal-500/30 rounded-xl text-teal-200 text-xs font-bold leading-relaxed">
                کەشف و تەخمین بە سەرکەوتوویی پەسەندکرا. دەتوانیت ڕاستەوخۆ پسوولەی کەشف چاپ بکەیت:
            </div>

            <div class="space-y-2 pt-1">
                @if($linkedPledge)
                    <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'both']) }}" target="_blank"
                       @click="showInspectionPromptModal = false"
                       class="w-full px-4 py-3 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-print text-base"></i>
                            <span>چاپی هەردوو پەڕەی کەشف (۲ پەڕەی جیا - دەرهێنان + بەڵێننامە)</span>
                        </span>
                        <span class="px-2 py-0.5 rounded bg-black/30 text-[11px] font-mono">۲ پەڕە</span>
                    </a>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'main']) }}" target="_blank"
                           @click="showInspectionPromptModal = false"
                           class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold rounded-xl border border-slate-700 transition text-center">
                            تەنها کەشفی {{ $typeName }}
                        </a>
                        <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'pledge']) }}" target="_blank"
                           @click="showInspectionPromptModal = false"
                           class="px-3 py-2 bg-amber-950/60 hover:bg-amber-900/60 text-amber-300 text-[11px] font-bold rounded-xl border border-amber-600/40 transition text-center">
                            تەنها کەشفی بەڵێننامە
                        </a>
                    </div>
                @else
                    <a href="{{ route('transactions.print_receipt', ['transaction' => $transaction->id, 'mode' => 'both']) }}" target="_blank"
                       @click="showInspectionPromptModal = false"
                       class="w-full px-4 py-3 bg-teal-600 hover:bg-teal-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-print text-base"></i>
                            <span>چاپی پسوولەی کەشف و تەخمین</span>
                        </span>
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                @endif
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-700/60">
                <button type="button" @click="showInspectionPromptModal = false" class="px-5 py-2 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white text-xs font-bold rounded-xl transition">
                    داخستن
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: Prompt Inspection Letter Print after Archive Submit (خاڵی داواکراو: داوای چاپی نووسراوی پشکنین لە کاتی سەبمیتی ئەرشیف) -->
    <div x-show="showArchiveInspectionPromptModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border-2 border-sky-500/60 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl" @click.away="showArchiveInspectionPromptModal = false">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="text-base font-black text-sky-300 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-400 border border-sky-500/30 flex items-center justify-center">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </span>
                    <span>سەبمیتی پشکنین تەواوبوو - چاپی نووسراوی فەرمی</span>
                </h3>
                <button type="button" @click="showArchiveInspectionPromptModal = false" class="text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-3.5 bg-sky-950/40 border border-sky-500/30 rounded-xl text-sky-200 text-xs font-bold leading-relaxed space-y-1">
                <p>مامەڵەی پشکنین بە سەرکەوتوویی سەبمیت و ئەرشیفکرا.</p>
                <p class="text-[11px] text-sky-300/80 font-normal">تکایە ئێستا نووسراوی فەرمی پشکنین چاپ بکە بۆ ئەوەی ڕەوانەی لایەنی پەیوەندیدار بکرێت:</p>
            </div>

            <div class="space-y-2 pt-1">
                <a href="{{ route('transactions.print_inspection_letter', $transaction->id) }}" target="_blank"
                   @click="showArchiveInspectionPromptModal = false"
                   class="w-full px-4 py-3 bg-sky-600 hover:bg-sky-500 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-print text-base"></i>
                        <span>چاپی نووسراوی فەرمی پشکنین</span>
                    </span>
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-700/60">
                <button type="button" @click="showArchiveInspectionPromptModal = false" class="px-5 py-2 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white text-xs font-bold rounded-xl transition">
                    داخستن
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: Update / Enter Customs Letter for Cancellation (تۆمارکردن یان دەستکاریکردنی نووسراوی گومرگ) -->
    <div x-show="showCustomsLetterModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-rose-500/50 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl" @click.away="showCustomsLetterModal = false">
            <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-file-contract text-rose-400"></i>
                    <span>تۆمارکردن / دەستکاریکردنی نووسراوی گومرگ</span>
                </h3>
                <button type="button" @click="showCustomsLetterModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- جۆری مامەڵە بە بارزی -->
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] ?? 'border-rose-500/80 shadow-rose-500/20' }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] ?? 'bg-rose-500/20 text-rose-400' }}">
                        <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-file-contract' }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                        <span class="text-base font-black text-white">{{ $typeName }}</span>
                    </div>
                </div>
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-black/50 text-sky-300 border border-slate-700">
                    #{{ $transaction->barcode }}
                </span>
            </div>

            <form action="{{ route('transactions.update_customs_letter', $transaction->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="p-3 bg-rose-950/40 border border-rose-500/30 rounded-xl text-rose-200 text-xs leading-relaxed">
                    ئەم ژمارە و بەروارە لەسەر نووسراوی فەرمی پووچەڵکردنەوە دادەنرێت لە ڕستەی (دوابەدوای نووسراومان ژمارە ... لە ...).
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-200 mb-1">ژمارەی نووسراوی گومرگ *</label>
                    <input type="text" name="no_nusraw_puchal" required
                           value="{{ old('no_nusraw_puchal', $transaction->no_nusraw_puchal ?: $transaction->getResolvedCustomsLetterNumber()) }}"
                           placeholder="نموونە: 29357" dir="ltr"
                           class="w-full bg-slate-950 border border-slate-700 focus:border-rose-400 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-100 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-200 mb-1">بەرواری نووسراوی گومرگ *</label>
                    <input type="date" name="date_nusraw_puchal" required
                           value="{{ old('date_nusraw_puchal', $transaction->date_nusraw_puchal?->format('Y-m-d') ?: ($transaction->getResolvedCustomsLetterDate()?->format('Y-m-d') ?: date('Y-m-d'))) }}"
                           class="w-full bg-slate-950 border border-slate-700 focus:border-rose-400 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-100 focus:outline-none">
                </div>

                <div class="flex items-center justify-end space-x-2 space-x-reverse pt-2 border-t border-slate-800">
                    <button type="button" @click="showCustomsLetterModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl transition">داخستن</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                        <i class="fa-solid fa-save"></i>
                        <span>پاشەکەوتکردن</span>
                    </button>
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
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] ?? 'border-blue-500/80 shadow-blue-500/20' }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] ?? 'bg-blue-500/20 text-blue-400' }}">
                        <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-book-bookmark' }}"></i>
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
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] ?? 'border-rose-500/80 shadow-rose-500/20' }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] ?? 'bg-rose-500/20 text-rose-400' }}">
                        <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-ban' }}"></i>
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
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] ?? 'border-rose-500/80 shadow-rose-500/20' }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] ?? 'bg-rose-500/20 text-rose-400' }}">
                        <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-ban' }}"></i>
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
                <input type="hidden" name="return_stage" :value="returnStage">

                <!-- ئاگاداری تایبەت بە گەڕاندنەوە لە ستێپی وردبینی (تەنها قۆناغی وردبینی هەڵدەوەشێتەوە، تەخمین پارێزراوە) -->
                <div x-show="returnStage === 'audit' || (!returnStage && {{ $transaction->is_inspected ? 'true' : 'false' }})" class="p-3.5 bg-purple-500/15 border-2 border-purple-500/40 rounded-xl space-y-2 text-purple-200">
                    <div class="font-black flex items-center gap-2 text-purple-300 text-xs">
                        <i class="fa-solid fa-triangle-exclamation text-amber-400 text-sm animate-pulse"></i>
                        <span>ئاگاداری: گەڕاندنەوە لە ستێپی وردبینی</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-slate-200">
                        ئەم مامەڵەیە لە قۆناغی <strong>وردبینی</strong> دایە. بە گەڕاندنەوەی بۆ داتائەنتەری، 
                        <span class="text-rose-400 font-bold underline">تەنها قۆناغی وردبینی هەڵدەوەشێتەوە</span>، 
                        و <span class="text-emerald-300 font-bold underline">قۆناغی تەخمین وەک خۆی پارێزراو دەبێت</span>. دوای ئەوەی داتائەنتەری هەڵەکە چاک دەکاتەوە، مامەڵەکە ڕاستەوخۆ دەگەڕێتەوە بەردەستی وردبین بۆ پەسەندکردن.
                    </p>
                    <label class="flex items-center gap-2.5 pt-1.5 p-2 bg-slate-900/90 rounded-lg border border-purple-500/30 text-white font-bold cursor-pointer hover:bg-slate-800 transition">
                        <input type="checkbox" name="confirm_revoke_audit" value="1" required class="w-4 h-4 rounded bg-slate-800 border-purple-500 text-purple-500 focus:ring-0 cursor-pointer">
                        <span class="text-[11px] text-purple-200">بەڵێ، ڕازیم بەس قۆناغی وردبینی هەڵدەوەشێتەوە و تەخمین پارێزراو بێت</span>
                    </label>
                </div>

                <!-- ئاگاداری تایبەت بە گەڕاندنەوە لە ستێپی تەخمین (قۆناغی تەخمین هەڵدەوەشێتەوە) -->
                <div x-show="returnStage === 'inspection' || (!returnStage && !{{ $transaction->is_inspected ? 'true' : 'false' }})" class="p-3.5 bg-amber-500/15 border-2 border-amber-500/40 rounded-xl space-y-2 text-amber-200">
                    <div class="font-black flex items-center gap-2 text-amber-300 text-xs">
                        <i class="fa-solid fa-triangle-exclamation text-amber-400 text-sm animate-pulse"></i>
                        <span>ئاگاداری: گەڕاندنەوە لە ستێپی تەخمین</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-slate-200">
                        ئەم مامەڵەیە لە قۆناغی <strong>تەخمین</strong> دایە. بە گەڕاندنەوەی بۆ داتائەنتەری، 
                        <span class="text-rose-400 font-bold underline">قۆناغی تەخمین هەڵدەوەشێتەوە</span>، 
                        و پێویستە دوای چاککردنی هەڵەکە لەلایەن داتائەنتەری، دووبارە لە سەرەتاوە تەخمین بۆ بکرێتەوە.
                    </p>
                    <label class="flex items-center gap-2.5 pt-1.5 p-2 bg-slate-900/90 rounded-lg border border-amber-500/30 text-white font-bold cursor-pointer hover:bg-slate-800 transition">
                        <input type="checkbox" name="confirm_revoke_inspection" value="1" :required="returnStage === 'inspection'" class="w-4 h-4 rounded bg-slate-800 border-amber-500 text-amber-500 focus:ring-0 cursor-pointer">
                        <span class="text-[11px] text-amber-200">بەڵێ، ڕازیم قۆناغی تەخمین هەڵدەوەشێتەوە و دووبارە پەسەند دەکرێتەوە</span>
                    </label>
                </div>

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
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] ?? 'border-rose-500/80 shadow-rose-500/20' }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] ?? 'bg-rose-500/20 text-rose-400' }}">
                        <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-ban' }}"></i>
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

    <!-- MODAL: Admin Delete Transaction Confirmation Modal (سڕینەوەی مامەڵە بۆ ئەدمین) -->
    @if(auth()->check() && auth()->user()->isAdmin())
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-md z-[120] flex items-center justify-center p-4">
        <div class="bg-slate-900 border-2 border-rose-500/80 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl text-right ring-4 ring-rose-500/20" @click.away="showDeleteModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-rose-400 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>سڕینەوەی مامەڵە (تەنها ئەدمین)</span>
                </h3>
                <button @click="showDeleteModal = false" class="text-slate-400 hover:text-slate-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- جۆری مامەڵە بە بارزی -->
            <div class="p-3 rounded-xl border-2 {{ $typeTheme['border'] ?? 'border-rose-500/80 shadow-rose-500/20' }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] ?? 'bg-rose-500/20 text-rose-400' }}">
                        <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-trash-can' }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                        <span class="text-base font-black text-white">{{ $typeName }}</span>
                    </div>
                </div>
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-black/50 text-sky-300 border border-slate-700">
                    #{{ $transaction->barcode }}
                </span>
            </div>

            <div class="p-3.5 bg-rose-950/40 border border-rose-500/40 rounded-xl space-y-2.5 text-xs">
                @if($hasCompanion)
                    <div class="text-rose-200 font-bold leading-relaxed flex items-center gap-1.5">
                        <i class="fa-solid fa-link text-amber-400"></i>
                        <span>ئەم مامەڵەیە دەرهێنان و بەڵێننامەی پێکەوەییە (۲ لە ۱)!</span>
                    </div>
                    <div class="p-3 bg-amber-500/10 border border-amber-500/40 rounded-xl space-y-2 text-xs text-amber-200">
                        <p class="text-[11px] font-bold leading-relaxed">
                            بە سڕینەوەی ئەم مامەڵەیە، <strong>هەردوو مامەڵەکە پێکەوە دەسڕدرێنەوە</strong>:
                        </p>
                        <div class="space-y-1.5 font-mono text-xs">
                            <div class="flex items-center justify-between p-2 bg-slate-950/80 rounded-lg border border-slate-800">
                                <span class="text-sky-300 font-bold">١. دەرهێنانی دەفتەر:</span>
                                <span class="text-white font-black bg-sky-950/70 border border-sky-600/40 px-2 py-0.5 rounded">{{ $bookletTx->barcode }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-slate-950/80 rounded-lg border border-slate-800">
                                <span class="text-amber-300 font-bold">٢. بەڵێننامەی یاسایی:</span>
                                <span class="text-white font-black bg-amber-950/70 border border-amber-600/40 px-2 py-0.5 rounded">{{ $pledgeTx->barcode }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-rose-200 font-bold leading-relaxed">
                        ئایا دڵنیایت لە سڕینەوەی ئەم مامەڵەیە؟ ئەگەر بە هەڵە تۆمارکرابێت یان پێویست نەبێت لێرەوە دەسڕدرێتەوە.
                    </div>
                    <div class="text-slate-300 font-mono text-[11px] bg-slate-950/80 p-2.5 rounded-lg border border-slate-800 space-y-1">
                        <div>بارکۆد: <strong class="text-sky-400">{{ $transaction->barcode }}</strong></div>
                        <div>هاووڵاتی: <strong class="text-white">{{ $transaction->visitor_name }}</strong></div>
                        <div>تابلۆ: <strong class="text-amber-400">{{ $transaction->plate_number }}</strong></div>
                        <div>جۆری مامەڵە: <strong class="text-slate-200">{{ $typeName }}</strong></div>
                    </div>
                @endif
                <p class="text-slate-400 text-[11px] leading-relaxed">
                    تێبینی: ئەم مامەڵەیە دەخرێتە ئەرشیفی سڕدراوەکان (تەنەکەخۆڵ) و لە هەموو لیستەکان نامێنێت.
                </p>
            </div>

            <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-750 text-slate-300 text-xs font-bold rounded-xl transition">
                        پاشگەزبوونەوە
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/30 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>{{ $hasCompanion ? 'بەڵێ، هەردوو مامەڵەکە بسڕەوە' : 'بەڵێ، مامەڵەکە بسڕەوە' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL 6: Auditor Verification Confirmation Modal (پەسەندکردنی وردبینی) -->
    <div x-show="showAuditModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-md z-50 flex items-center justify-center p-3 sm:p-5 overflow-y-auto">
        <div class="bg-slate-900 border-2 border-purple-500/80 rounded-3xl max-w-5xl w-full p-5 sm:p-6 space-y-5 shadow-2xl text-right overflow-y-auto max-h-[90vh] touch-scroll ring-4 ring-purple-500/20" @click.away="showAuditModal = false">
            
            <!-- سەردێڕ و دوگمەی داخستن -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/40 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">فۆرمی پەسەندکردن و وردبینی مامەڵە</h3>
                        <p class="text-xs text-slate-400">وردبینی وردی زانیارییەکانی هاووڵاتی و ئۆتۆمبێل پێش وەرگرتنی رسومات</p>
                    </div>
                </div>
                <button @click="showAuditModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg bg-slate-800">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- سەرەوەی فۆرمەکە: جۆری مامەڵە بە گەورەیی و ڕوونی -->
            <div class="p-3.5 rounded-2xl border-2 {{ $typeTheme['border'] ?? 'border-purple-500/80 shadow-purple-500/20' }} bg-slate-950/95 shadow-lg flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl {{ $typeTheme['iconBg'] ?? 'bg-purple-500/20 text-purple-400' }} shrink-0">
                        <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-clipboard-check' }}"></i>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block font-bold">جۆری مامەڵە:</span>
                        <span class="text-lg sm:text-xl font-black text-white">{{ $typeName }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="font-mono text-sm font-black text-sky-400 bg-slate-900 px-3 py-1.5 rounded-xl border border-slate-700">
                        #{{ $transaction->barcode }}
                    </span>
                    <div class="text-xs bg-emerald-950/50 border border-emerald-500/50 px-3 py-1.5 rounded-xl text-left">
                        <span class="text-slate-300 font-bold">کۆی بڕی پارەی پەسەندکراو:</span>
                        <strong class="font-mono text-emerald-400 text-base font-black mr-1">{{ number_format($auditCombinedPay) }} دینار</strong>
                        @if($linkedPledge)
                            <span class="text-[11px] text-slate-400 block font-normal">(دەفتەر: {{ number_format($transaction->total_pay) }} + بەڵێننامە: {{ number_format($linkedPledge->total_pay) }})</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ئاگاداری ونبوونی دەفتەر لە مۆدالی وردبینی -->
            @if($transaction->is_booklet_lost || !empty($transaction->lost_letter_number))
            <div class="bg-gradient-to-r from-amber-950/90 via-amber-900/80 to-amber-950/90 border-2 border-amber-500 rounded-xl p-3 sm:p-3.5 shadow-xl flex items-start gap-3 text-amber-200 ring-2 ring-amber-500/30">
                <div class="w-9 h-9 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center text-lg font-black shrink-0 shadow">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="flex-1 space-y-0.5">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h4 class="text-xs sm:text-sm font-black text-amber-300 flex items-center gap-2">
                            <span>ئاگاداری بۆ وردبین: دەفتەری ئەم مامەڵەیە ونبووە!</span>
                        </h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950">
                            دەفتەری ونبوو
                        </span>
                    </div>
                    <p class="text-[11px] font-bold text-slate-100 leading-relaxed">
                        ئاماژە بە ونبوونی دەفتەری ئۆتۆمبێلی ناوبراو، پاڵپشت بە نووسراوی 
                        <span class="text-amber-300 font-black underline">{{ $transaction->lost_directorate_name ?: 'بەڕێوەبەرایەتی ونبووەکان' }}</span>
                        ژمارە ( <span class="font-mono text-white text-xs font-black px-1.5 py-0.2 bg-black/40 rounded border border-amber-500/40">{{ $transaction->lost_letter_number ?: '-' }}</span> ) 
                        لە ڕێکەوتی ( <span class="font-mono text-white text-xs font-black px-1.5 py-0.2 bg-black/40 rounded border border-amber-500/40">{{ $transaction->lost_letter_date ? $transaction->lost_letter_date->format('Y-m-d') : '-' }}</span> ).
                    </p>
                </div>
            </div>
            @endif

            <!-- دابەشکردنی ناوەوە: زۆربەی زانیارییەکان لە لایەک (ڕاست) و بڕیاری وردبینی لە لایەکەی تر (چەپ) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start text-xs">
                
                <!-- لای ڕاست (7 لە 12): زۆربەی ئەو زانیارییانەی دەبینرێت -->
                <div class="lg:col-span-7 bg-slate-950/70 border border-slate-800 rounded-2xl p-4 space-y-3.5">
                    
                    <!-- ژمارەی شاسی دیار -->
                    <div class="bg-slate-900 border-2 border-sky-500/60 p-2.5 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-sky-400 font-bold block">ژمارەی شاسی (VIN):</span>
                            <span class="font-mono text-sm sm:text-base font-black text-white tracking-widest select-all uppercase">{{ $transaction->chassis_number }}</span>
                        </div>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $transaction->chassis_number }}');" class="text-[10px] bg-sky-500/20 text-sky-300 px-2 py-1 rounded border border-sky-500/40 hover:bg-sky-500/30">
                            کۆپی
                        </button>
                    </div>

                    <!-- خشتەی زانیاری ئۆتۆمبێل -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-slate-900 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400 text-[10px] block">تابلۆ:</span>
                            <strong class="font-mono text-white text-xs block" dir="ltr">{{ $transaction->plate_number }}</strong>
                            <span class="text-[10px] text-slate-400 font-bold">({{ $transaction->plateType->clean_kurdish_name ?? $transaction->plateType->name_kurdish ?? 'تایبەت' }})</span>
                        </div>
                        <div class="bg-slate-900 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400 text-[10px] block">مارکە:</span>
                            <strong class="text-white text-xs block">{{ $transaction->carMake->name_kurdish ?? '-' }}</strong>
                            <span class="text-[10px] font-mono text-slate-400">({{ $transaction->carMake->name_english ?? '-' }})</span>
                        </div>
                        <div class="bg-slate-900 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400 text-[10px] block">مۆدێل و بستۆن:</span>
                            <strong class="text-white text-xs block">{{ $transaction->model_year }} ({{ $transaction->piston_count }} Piston)</strong>
                        </div>
                        <div class="bg-slate-900 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400 text-[10px] block">ڕەنگ:</span>
                            <strong class="text-white text-xs block">{{ $transaction->carColor->name_kurdish ?? '-' }} ({{ $transaction->carColor->name_english ?? '-' }})</strong>
                        </div>
                        <div class="bg-slate-900 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400 text-[10px] block">ماوەی ساڵانە:</span>
                            <strong class="text-amber-300 text-xs block">{{ $transaction->num_years ?? 1 }} ساڵ</strong>
                        </div>
                        <div class="bg-slate-900 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400 text-[10px] block">ژمارەی ساڵانە:</span>
                            <strong class="font-mono text-white text-xs block">{{ $transaction->salana_number ?: 'نییە' }}</strong>
                        </div>
                    </div>

                    <!-- زانیاری هاووڵاتی و شۆفێر -->
                    <div class="pt-2 border-t border-slate-800 space-y-2">
                        <div class="flex justify-between items-center bg-slate-900 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400">هاووڵاتی:</span>
                            <span class="text-white font-bold">{{ $transaction->visitor_name }} @if($transaction->visitor_name_eng) ({{ $transaction->visitor_name_eng }}) @endif</span>
                        </div>
                        <div class="flex justify-between items-center bg-slate-900 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400">مۆبایل و پاسپۆرت:</span>
                            <span class="font-mono text-emerald-400 font-bold" dir="ltr">{{ $transaction->phone_number ?: '-' }} | پ: {{ $transaction->passport_number ?: '-' }}</span>
                        </div>
                        @if($transaction->second_driver_name)
                        <div class="flex justify-between items-center bg-slate-900 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400">شۆفێری دووەم:</span>
                            <span class="text-slate-200">{{ $transaction->second_driver_name }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- لای چەپ (5 لە 12): بڕیاری وردبینی و پەسەندکردن -->
                <div class="lg:col-span-5 bg-slate-950/90 border border-purple-500/40 rounded-2xl p-4 space-y-4">
                    
                    <!-- ڕسوومات -->
                    <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 space-y-1.5">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">بڕی تێچووی سەرەکی:</span>
                            <strong class="font-mono text-white">{{ number_format($transaction->total_pay) }} د.ع</strong>
                        </div>
                        @if($linkedPledge)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">ڕسووماتی بەڵێننامە:</span>
                            <strong class="font-mono text-amber-300">{{ number_format($linkedPledge->total_pay) }} د.ع</strong>
                        </div>
                        <div class="pt-1.5 border-t border-slate-800 flex justify-between items-center">
                            <span class="text-slate-200 font-bold">کۆی گشتی:</span>
                            <strong class="font-mono text-emerald-400 text-sm font-black">{{ number_format($transaction->total_pay + $linkedPledge->total_pay) }} د.ع</strong>
                        </div>
                        @endif
                    </div>

                    @if($transaction->is_booklet_lost || !empty($transaction->lost_letter_number))
                    <div class="bg-amber-500/20 border-2 border-amber-500/60 rounded-xl p-2.5 text-xs font-bold text-amber-200 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-400 text-base shrink-0"></i>
                        <div class="leading-tight">
                            <span class="font-black text-amber-300 block">دەفتەری ئەم مامەڵەیە ونبووە:</span>
                            <span class="text-[11px] font-mono text-slate-200">نووسراوی ژمارە {{ $transaction->lost_letter_number ?? '-' }} لە {{ $transaction->lost_letter_date ? $transaction->lost_letter_date->format('Y-m-d') : '-' }}</span>
                        </div>
                    </div>
                    @endif

                    <!-- فۆرمی پەسەندکردنی کۆتایی -->
                    <form action="{{ route('transactions.audit', $transaction->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-300">
                                    دووپاتکردنەوەی بڕی پارەکە: *
                                </label>
                                <button type="button" @click="confirmAuditAmount = expectedAuditAmount" class="text-[10px] text-purple-400 hover:text-purple-300 font-bold hover:underline">
                                    خۆکار پڕی بکەوە ({{ number_format($auditCombinedPay) }})
                                </button>
                            </div>
                            <input type="text" 
                                   name="confirm_total_pay" 
                                   x-model="confirmAuditAmount" 
                                   placeholder="نموونە: {{ number_format($auditCombinedPay) }}" 
                                   required 
                                   autocomplete="off"
                                   class="w-full bg-slate-950 border border-slate-700 focus:border-purple-500 rounded-xl px-3 py-2 text-sm font-bold text-slate-100 text-center tracking-wider focus:outline-none focus:ring-2 focus:ring-purple-500/20" />

                            <template x-if="confirmAuditAmount.length > 0 && confirmAuditAmount.replace(/\D/g, '') !== expectedAuditAmount && confirmAuditAmount.replace(/\D/g, '') !== expectedSingleAuditAmount">
                                <p class="text-[10px] text-rose-400 font-bold mt-1.5 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    بڕی پارەکە یەکسان نییە ({{ number_format($auditCombinedPay) }} دینار)
                                </p>
                            </template>
                        </div>

                        <div class="space-y-2 pt-1">
                            <button type="submit" 
                                    :disabled="confirmAuditAmount.length > 0 && confirmAuditAmount.replace(/\D/g, '') !== expectedAuditAmount && confirmAuditAmount.replace(/\D/g, '') !== expectedSingleAuditAmount"
                                    class="w-full py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-black text-xs rounded-xl shadow-lg shadow-purple-600/30 transition flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-clipboard-check"></i>
                                <span>پەسەندکردنی کۆتایی وردبینی</span>
                            </button>

                            <button type="button" @click="returnStage = 'audit'; showAuditModal = false; showReturnModal = true;" class="w-full py-2 bg-rose-950/60 hover:bg-rose-900 border border-rose-600/50 text-rose-300 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>گەڕاندنەوە بۆ تەخمین (هەڵەی زانیاری)</span>
                            </button>
                        </div>
                    </form>

                </div>

            </div>

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
            <div class="p-3.5 rounded-xl border-2 {{ $typeTheme['border'] ?? 'border-indigo-500/80 shadow-indigo-500/20' }} bg-slate-950/90 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $typeTheme['iconBg'] ?? 'bg-indigo-500/20 text-indigo-400' }}">
                        <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-box-archive' }}"></i>
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

                @if(in_array((int)$transaction->transaction_type_id, [1, 2, 3, 4, 7]))
                    <!-- Types 1, 2, 3, 4, 7: Box file number and Customs Letter are MANDATORY -->
                    <div class="p-4 bg-indigo-950/40 border border-indigo-500/40 rounded-xl space-y-3">
                        <div class="flex items-center gap-2 text-indigo-300 text-xs font-bold">
                            <i class="fa-solid fa-box-archive text-base"></i>
                            <span>تایبەت بە [{{ $typeName }}]: داخڵکردنی ژمارەی بۆکس و نووسراوی گومرگ (ئیجبارییە)</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-200 mb-1.5">
                                ژمارەی بۆکسی ئەرشیف (Box File Number) *
                            </label>
                            <input type="number" name="NO_boxfile" required min="1"
                                   value="{{ old('NO_boxfile', $transaction->NO_boxfile) }}"
                                   placeholder="نموونە: 125" dir="ltr"
                                   class="w-full bg-slate-950 border border-indigo-500/60 focus:border-indigo-400 rounded-xl px-3.5 py-2.5 text-sm font-mono font-bold text-slate-100 text-center tracking-wider focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-indigo-500/30">
                            <div>
                                <label class="block text-xs font-bold text-slate-200 mb-1">ژمارەی نووسراوی گومرگ *</label>
                                <input type="text" name="no_nusraw_gumrg" required
                                       value="{{ old('no_nusraw_gumrg', $transaction->no_nusraw_gumrg) }}"
                                       placeholder="نموونە: 29357" dir="ltr"
                                       class="w-full bg-slate-950 border border-indigo-500/60 focus:border-indigo-400 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-200 mb-1">بەرواری نووسراوی گومرگ *</label>
                                <input type="date" name="date_nusraw_gumrg" required
                                       value="{{ old('date_nusraw_gumrg', $transaction->date_nusraw_gumrg?->format('Y-m-d') ?? ($transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : date('Y-m-d'))) }}"
                                       class="w-full bg-slate-950 border border-indigo-500/60 focus:border-indigo-400 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            </div>
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
                                       value="{{ old('no_nusraw_puchal', $transaction->no_nusraw_puchal ?: $transaction->getResolvedCustomsLetterNumber()) }}"
                                       placeholder="ژمارەی فەرمی نووسراو" dir="ltr"
                                       class="w-full bg-slate-950 border border-rose-500/60 focus:border-rose-400 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500/30">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-200 mb-1">بەرواری نووسراوی گومرگ *</label>
                                <input type="date" name="date_nusraw_puchal" required
                                       value="{{ old('date_nusraw_puchal', $transaction->date_nusraw_puchal?->format('Y-m-d') ?: ($transaction->getResolvedCustomsLetterDate()?->format('Y-m-d') ?: date('Y-m-d'))) }}"
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
                    <!-- Other types (Pledge 6, Inspection 8): Box file number is NOT required (خاڵی ٥: ژمارەی بۆکسی ناوێت) -->
                    <div class="p-4 bg-slate-800/80 border border-slate-700 rounded-xl space-y-3">
                        <p class="text-xs text-slate-300">سەرجەم قۆناغەکانی ئەم مامەڵەیە تەواو بوون. بۆ ئەم جۆرە مامەڵەیە داخڵکردنی ژمارەی بۆکس ئیجباری نییە:</p>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">ژمارەی بۆکس (ئارەزوومەندانە)</label>
                            <input type="number" name="NO_boxfile" min="1"
                                   value="{{ old('NO_boxfile', $transaction->NO_boxfile) }}"
                                   placeholder="ژمارەی بۆکس (ئەگەر پێویست بوو)..." dir="ltr"
                                   class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-100 text-center focus:outline-none">
                        </div>
                    </div>
                @endif

                @if((int)$transaction->transaction_type_id === 8 || ($transaction->transactionType && str_contains($transaction->transactionType->name_kurdish, 'پشکنین')))
                    <div class="p-3 bg-sky-500/10 border border-sky-500/30 rounded-xl text-sky-300 text-xs font-semibold flex items-center gap-2">
                        <i class="fa-solid fa-file-circle-check text-sky-400 text-sm"></i>
                        <span>تێبینی: دوای پەسەندکردن و سەبمیت، داوای چاپی نووسراوی فەرمی پشکنینت لێدەکرێت.</span>
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
                    <span class="px-2.5 py-1 rounded-lg text-xs font-black border flex items-center gap-1.5 {{ $typeTheme['pill'] ?? 'bg-indigo-500/15 text-indigo-300 border border-indigo-500/30' }}" title="جۆری مامەڵە">
                        <i class="{{ $typeTheme['icon'] ?? 'fa-solid fa-folder-open text-indigo-400' }} text-[10px]"></i>
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

                @if($linkedPledge)
                    <span class="px-2.5 py-1 bg-slate-950 text-emerald-400 border border-slate-800 rounded-lg text-xs font-mono font-extrabold hidden sm:inline-block" title="کۆی گشتی: دەفتەر ({{ number_format($transaction->total_pay) }}) + بەڵێننامە ({{ number_format($linkedPledge->total_pay) }})">
                        {{ number_format($transaction->total_pay + $linkedPledge->total_pay) }} د.ع
                    </span>
                @else
                    <span class="px-2.5 py-1 bg-slate-950 text-emerald-400 border border-slate-800 rounded-lg text-xs font-mono font-extrabold hidden sm:inline-block">
                        {{ number_format($transaction->total_pay) }} د.ع
                    </span>
                @endif
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
                        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.return') || auth()->user()->hasPermission('transactions.inspect'))
                            <button type="button" @click="returnStage = 'inspection'; showReturnModal = true" class="px-3 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1">
                                <i class="fa-solid fa-rotate-left"></i> گەڕاندنەوە
                            </button>
                        @endif
                    @endif

                    <!-- 2. چاپی پسولەی کەشف -->
                    @if($transaction->is_inspected && (!$transaction->is_submitted || auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.inspection')) && !$transaction->is_audited && (auth()->user()->isAdmin() || in_array(auth()->user()->role, ['data_entry', 'inspector']) || auth()->user()->hasPermission('prints.inspection')))
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
                        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.return') || auth()->user()->hasPermission('transactions.audit'))
                            <button type="button" @click="returnStage = 'audit'; showReturnModal = true" class="px-3 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1">
                                <i class="fa-solid fa-rotate-left"></i> گەڕاندنەوە
                            </button>
                        @endif
                    @endif

                    <!-- نوسراوە ئیدارییەکان بۆ وردبینی (نیشانەی ڕەفتارنەکردن & نووسراوی پووچەڵکردنەوە) -->
                    @if(auth()->user()->isAdmin() || auth()->user()->isLegal() || auth()->user()->hasPermission('prints.pledge'))
                        <a href="{{ route('transactions.print_pledge', $transaction->id) }}" target="_blank" class="px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-black rounded-xl shadow transition flex items-center gap-1" title="چاپی بەڵێننامەی یاسایی">
                            <i class="fa-solid fa-file-contract"></i> بەڵێننامە (یاسایی)
                        </a>
                    @endif

                    <!-- 4. وەرگرتنی پارە و وەسڵی ۳۷/أ (Cashier / Admin) -->
                    @if($transaction->is_audited && !$transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.pay')))
                        <button type="button" @click="showPayModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-receipt"></i>
                            <span>وەرگرتنی پارە (۳۷/أ)</span>
                        </button>
                    @endif
                    @if($transaction->is_audited && !$transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.return') || auth()->user()->hasPermission('transactions.audit')))
                        <button type="button" @click="returnStage = 'audit'; showReturnModal = true" class="px-3 py-2 bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1 shadow">
                            <i class="fa-solid fa-rotate-left"></i> گەڕاندنەوە بەهۆی هەڵە
                        </button>
                    @endif

                    <!-- 5. چاپی وەسڵی ۳۷/أ -->
                    @if($transaction->is_paid && (auth()->user()->isAdmin() || auth()->user()->role === 'cashier' || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.receipt')))
                        @if((!$transaction->is_submitted || auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.receipt')) && (auth()->user()->isAdmin() || auth()->user()->role === 'cashier' || auth()->user()->hasPermission('prints.receipt')))
                            <a href="{{ route('transactions.print_payment_receipt', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow transition flex items-center gap-1">
                                <i class="fa-solid fa-receipt"></i> {{ ($linkedPledge && $linkedPledge->is_paid) ? 'چاپی هەردوو ۳۷/أ (۲ پەڕە)' : 'چاپی ۳۷/أ' }}
                            </a>
                        @endif
                    @endif

                    <!-- نووسراوی پشکنین (تەنها لە قۆناغی ئەرشیف نەک لە قۆناغی پارەدان) -->
                    @if($transaction->is_submitted && (auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.inspection_letter')))
                        <a href="{{ route('transactions.print_inspection_letter', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1" title="چاپی نووسراوی پشکنین">
                            <i class="fa-solid fa-file-circle-check"></i> نوسراوی پشکنین
                        </a>
                    @endif

                    <!-- 6. تۆمارکردنی دەفتەر (Booklet / Admin - ڕێگری لە پووچەڵ، بەڵێننامە و پشکنین) -->
                    @if($requiresBooklet && !in_array($typeId, [5, 6, 8]) && $transaction->is_paid && (!$transaction->is_booklet_completed || empty($transaction->booklet_number)) && (auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.complete_booklet')))
                        <button type="button" @click="showBookletModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-black rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-book-bookmark"></i>
                            <span>تۆمارکردنی ژمارەی دەفتەر</span>
                        </button>
                    @endif

                    <!-- 7. چاپی دەفتەر (Booklet / Admin - Excluded for data_entry - قفڵکراوە دوای سەبمیت) -->
                    @if($requiresBooklet && !in_array($typeId, [5, 6, 8]) && $transaction->is_paid && $transaction->is_booklet_completed && !empty($transaction->booklet_number) && (!$transaction->is_submitted || auth()->user()->isAdmin() || auth()->user()->role === 'archiver' || auth()->user()->hasPermission('prints.booklet')) && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet' || auth()->user()->hasPermission('prints.booklet')))
                        <a href="{{ route('transactions.print_booklet', $transaction->id) }}" target="_blank" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1">
                            <i class="fa-solid fa-passport"></i> چاپی دەفتەر ({{ $transaction->booklet_number }})
                        </a>
                    @endif

                    <!-- 8. سەبمیت و ئەرشیف (Archiver / Admin ONLY بۆ جۆرەکانی ١، ٢، ٣، ٤، ٥، ٧) (خاڵی ٤) -->
                    @if($isArchiverOrAdmin && $canSubmitThisType && $transaction->is_paid && (!$requiresBooklet || $transaction->is_booklet_completed))
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
            width: 1.4,
            height: 26,
            displayValue: false
        });
    });
</script>
@endsection
