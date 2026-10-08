@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Banner -->
    <div class="bg-slate-800/90 border border-rose-500/30 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 border border-rose-500/40 flex items-center justify-center text-rose-400 text-xl shadow-lg shadow-rose-500/10 shrink-0">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                        <span>ئەرشیفی سڕاوەکان (تەنەکەخۆڵ / Recycle Bin)</span>
                    </h1>
                    <p class="text-xs text-slate-400 mt-1">
                        مامەڵە سڕاوەکان لەم بەشەدا پارێزراون. دەتوانیت بیانگەڕێنیتەوە بۆ ناو سیستم یان بە یەکجاری بسڕدرێنەوە.
                    </p>
                </div>
            </div>
        </div>

        <!-- Fast Stats & Back Link -->
        <div class="flex items-center space-x-3 space-x-reverse w-full md:w-auto justify-between md:justify-end">
            <div class="bg-slate-900/90 border border-rose-500/40 px-4 py-2 rounded-xl text-center">
                <span class="text-xs text-rose-300 block font-bold">کۆی سڕاوەکان</span>
                <strong class="text-lg font-mono font-black text-rose-400">{{ $trashedCount }}</strong>
            </div>
            <a href="{{ route('transactions.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition flex items-center">
                <i class="fa-solid fa-arrow-right ml-1.5"></i> گەڕانەوە بۆ مامەڵەکان
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 shadow-lg flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('transactions.trash') }}" method="GET" class="w-full md:w-96 flex items-center relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="گەڕان بە ناوی هاووڵاتی، بارکۆد، پلاک، شاسی..."
                   class="w-full bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-xs rounded-xl pr-9 pl-3 py-2.5 focus:border-rose-500 focus:outline-none">
            <button type="submit" class="absolute right-3 text-slate-400 hover:text-rose-400">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>

        <div class="text-xs text-slate-400">
            <span>تێبینی: گەڕاندنەوەی هەر مامەڵەیەک، دەستبەجێ دەیخاتەوە نێو هەمان قۆناغی پێشووی خۆی.</span>
        </div>
    </div>

    <!-- Trashed Transactions List Table -->
    <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm text-slate-300">
                <thead class="bg-slate-900/90 text-slate-400 text-xs font-bold uppercase border-b border-slate-700">
                    <tr>
                        <th class="py-3.5 px-4">بارکۆد و کاتی سڕینەوە</th>
                        <th class="py-3.5 px-4">ناوی خاوەن / هاووڵاتی</th>
                        <th class="py-3.5 px-4">زانیاری ئۆتۆمبێل</th>
                        <th class="py-3.5 px-4">جۆری مامەڵە</th>
                        <th class="py-3.5 px-4">کۆی پارە</th>
                        <th class="py-3.5 px-4 text-center">کردارەکانی تەنەکەخۆڵ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-700/30 transition">
                            <!-- Barcode & Deleted Date -->
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-rose-300 block text-sm">{{ $tx->barcode }}</span>
                                <span class="text-[11px] text-slate-400 block font-mono" title="{{ $tx->deleted_at }}">
                                    سڕاوەتەوە لە: {{ $tx->deleted_at ? $tx->deleted_at->format('Y-m-d H:i') : '-' }}
                                    ({{ $tx->deleted_at?->diffForHumans() }})
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
                                <span class="text-[11px] font-mono text-slate-400 block uppercase">{{ Str::limit($tx->chassis_number, 14) }}</span>
                            </td>

                            <!-- Transaction Type Badge -->
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-900 text-sky-300 border border-slate-700">
                                    {{ $tx->transactionType->name_kurdish ?? '-' }}
                                </span>
                            </td>

                            <!-- Payment Amount -->
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-emerald-400 block">{{ number_format($tx->total_pay) }} د.ع</span>
                                @if($tx->is_paid)
                                    <span class="text-[11px] text-emerald-300 block">وەسڵ دراوە ({{ $tx->receipt_37a_number }})</span>
                                @else
                                    <span class="text-[11px] text-slate-400 block">پارە نەدراوە</span>
                                @endif
                            </td>

                            <!-- Actions: Restore & Force Delete -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <!-- Restore Form -->
                                    <form action="{{ route('transactions.restore', $tx->id) }}" method="POST" onsubmit="return confirm('دڵنیایت لە گەڕاندنەوەی ئەم مامەڵەیە بۆ ناو سیستم؟')">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-md transition flex items-center" title="گەڕاندنەوە بۆ سیستم">
                                            <i class="fa-solid fa-rotate-left ml-1.5 text-emerald-200"></i>
                                            گەڕاندنەوە (Restore)
                                        </button>
                                    </form>

                                    <!-- Force Delete Form -->
                                    <form action="{{ route('transactions.force_delete', $tx->id) }}" method="POST" onsubmit="return confirm('⚠️ ئاگاداربە! ئەم کردارە هەڵناوەشێتەوە.\nدڵنیایت لە سڕینەوەی یەکجاری و بنەڕەتی ئەم مامەڵەیە لە داتابەیس؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-2 bg-rose-950 hover:bg-rose-900 border border-rose-700/80 text-rose-300 hover:text-white text-xs font-bold rounded-xl transition flex items-center" title="سڕینەوەی یەکجاری لە داتابەیس">
                                            <i class="fa-solid fa-trash-can ml-1.5"></i>
                                            سڕینەوەی یەکجاری
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center text-slate-400">
                                <div class="max-w-md mx-auto space-y-3 bg-slate-900/60 p-6 rounded-2xl border border-slate-700/60">
                                    <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center mx-auto text-emerald-400 text-2xl">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-200">هیچ مامەڵەیەکی سڕاوە لە ئەرشیفی تەنەکەخۆڵدا نییە!</h4>
                                    <p class="text-xs text-slate-400 leading-relaxed">
                                        کاتێک مامەڵەیەک لە لایەن ئەدمینەوە بسڕدرێتەوە بە شێوەی کاتی، لەم بەشەدا کۆدەکرێتەوە بۆ ئەوەی هەرکات ویستت بتوانیت بیگەڕێنیتەوە.
                                    </p>
                                    <a href="{{ route('transactions.index') }}" class="inline-block mt-2 px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-xs rounded-xl shadow-lg transition">
                                        چوون بۆ لیستی سەرجەم مامەڵەکان
                                    </a>
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
