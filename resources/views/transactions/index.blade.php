@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fa-solid fa-list-check ml-2 text-sky-400"></i> لیستی سەرجەم مامەڵەکان و قۆناغەکان
            </h2>
            <p class="text-xs text-slate-400">گەڕان، فلتەرکردن و بەدواداچوونی قۆناغەکانی مامەڵە.</p>
        </div>
        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'data_entry'))
            <a href="{{ route('transactions.create') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center">
                <i class="fa-solid fa-plus ml-2"></i> مامەڵەی نوێ
            </a>
        @endif
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 shadow-xl">
        <form action="{{ route('transactions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <!-- Search Text -->
            <div class="md:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="گەڕان بەپێی بارکۆد، ناوی شۆفێر، ژمارەی تابلۆ، وەسڵ یان دەفتەر..."
                       class="w-full bg-slate-900 border border-slate-700 rounded-xl pr-10 pl-4 py-2 text-sm text-slate-100 placeholder-slate-500 focus:border-sky-500 focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-slate-500"></i>
            </div>

            <!-- Transaction Type Filter -->
            <div>
                <select name="transaction_type_id" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                    <option value="">گشت جۆرەکانی مامەڵە</option>
                    @foreach($transactionTypes as $tt)
                        <option value="{{ $tt->id }}" {{ request('transaction_type_id') == $tt->id ? 'selected' : '' }}>{{ $tt->name_kurdish }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Stage Filter -->
            <div>
                <select name="stage" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                    <option value="">گشت قۆناغەکان</option>
                    <option value="inspection" {{ request('stage') == 'inspection' ? 'selected' : '' }}>چاوەڕوانی کەشف</option>
                    <option value="audit" {{ request('stage') == 'audit' ? 'selected' : '' }}>چاوەڕوانی وردبینی</option>
                    <option value="payment" {{ request('stage') == 'payment' ? 'selected' : '' }}>چاوەڕوانی وەسڵ/پارەدا</option>
                    <option value="booklet" {{ request('stage') == 'booklet' ? 'selected' : '' }}>چاوەڕوانی دەفتەر</option>
                    <option value="completed" {{ request('stage') == 'completed' ? 'selected' : '' }}>تەواوکراوەکان</option>
                    <option value="cancelled" {{ request('stage') == 'cancelled' ? 'selected' : '' }}>پووچەڵکراوەکان</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 text-xs uppercase border-b border-slate-700">
                    <tr>
                        <th class="p-3.5">بارکۆد</th>
                        <th class="p-3.5">ناوی شۆفێر / خاوەن</th>
                        <th class="p-3.5">مامەڵە</th>
                        <th class="p-3.5">تابلۆ</th>
                        <th class="p-3.5">کۆی پارە</th>
                        <th class="p-3.5">نووسراوی 37/A</th>
                        <th class="p-3.5">ژمارەی دەفتەر</th>
                        <th class="p-3.5 text-center">قۆناغ</th>
                        <th class="p-3.5">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-700/40 transition">
                            <td class="p-3.5 font-mono text-xs text-sky-400 font-bold">{{ $tx->barcode }}</td>
                            <td class="p-3.5 font-medium text-slate-100">
                                {{ $tx->visitor_name }}
                                @if($tx->visitor_name_eng)
                                    <span class="block text-[11px] text-slate-500 font-mono">{{ $tx->visitor_name_eng }}</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-xs text-slate-300 font-semibold">{{ $tx->transactionType->name_kurdish ?? '-' }}</td>
                            <td class="p-3.5 font-mono text-xs text-emerald-400 font-bold">{{ $tx->plate_number }}</td>
                            <td class="p-3.5 font-mono text-xs font-bold text-white">{{ number_format($tx->total_pay) }} د.ع</td>
                            <td class="p-3.5 font-mono text-xs text-slate-300">{{ $tx->receipt_37a_number ?? '-' }}</td>
                            <td class="p-3.5 font-mono text-xs text-blue-400 font-bold">{{ $tx->booklet_number ?? '-' }}</td>
                            <td class="p-3.5 text-center">
                                @if($tx->is_cancelled)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">پووچەڵکراوە</span>
                                @elseif($tx->is_booklet_completed)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">تەواوکراوە</span>
                                @elseif($tx->is_paid)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">پارەدراوە</span>
                                @elseif($tx->is_audited)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">چاوەڕێی پارەدا</span>
                                @elseif($tx->is_inspected)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">چاوەڕێی وردبینی</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-400 border border-orange-500/20">چاوەڕێی کەشف</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <a href="{{ route('transactions.show', $tx->id) }}" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-xs font-semibold shadow transition">
                                    بینین
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-500 text-sm">هیچ مامەڵەیەک نەدۆزرایەوە بەپێی ئەم فلتەرانە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-700">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
