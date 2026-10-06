@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fa-solid fa-user-shield ml-2 text-amber-400"></i> دەسەڵاتی تا تایبەتی (سۆپەر ئەدمین) - بەڕێوەبردنی نرخ و ڕسوومات
            </h2>
            <p class="text-xs text-slate-400">دەستکاریکردنی بڕی پارەی ساڵەکان، پول، فۆڕم، کەشف و سزای دواکەوتنی مامەڵەکان.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl border border-slate-700 transition">
            <i class="fa-solid fa-arrow-right ml-1"></i> گەڕانەوە
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('settings.pricing.update') }}" method="POST">
        @csrf
        
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden p-6 space-y-6">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm text-slate-200">
                    <thead class="bg-slate-900/90 text-slate-400 text-xs uppercase border-b border-slate-700">
                        <tr>
                            <th class="p-3">جۆری مامەڵە</th>
                            <th class="p-3">١ ساڵ (د.ع)</th>
                            <th class="p-3">٢ ساڵ (د.ع)</th>
                            <th class="p-3">٣ ساڵ (د.ع)</th>
                            <th class="p-3">پول (d.ع)</th>
                            <th class="p-3">فۆڕم (د.ع)</th>
                            <th class="p-3">سزا (د.ع)</th>
                            <th class="p-3">کەشف (د.ع)</th>
                            <th class="p-3">کەشفى پاس</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($transactionTypes as $tt)
                            <tr class="hover:bg-slate-700/30 transition">
                                <td class="p-3 font-bold text-sky-400">
                                    {{ $tt->name_kurdish }}
                                    <span class="block text-[11px] text-slate-500 font-mono">{{ $tt->name_english }}</span>
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][pay_1_year]" value="{{ (int)$tt->pay_1_year }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-100">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][pay_2_year]" value="{{ (int)$tt->pay_2_year }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-100">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][pay_3_year]" value="{{ (int)$tt->pay_3_year }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-100">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][stamp_pay]" value="{{ (int)$tt->stamp_pay }}" class="w-20 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-100">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][form_pay]" value="{{ (int)$tt->form_pay }}" class="w-20 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-slate-100">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][fine_pay]" value="{{ (int)$tt->fine_pay }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-amber-400">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][inspection_pay]" value="{{ (int)$tt->inspection_pay }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-indigo-300">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="prices[{{ $tt->id }}][bus_inspection_pay]" value="{{ (int)$tt->bus_inspection_pay }}" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs font-mono text-indigo-300">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-end border-t border-slate-700 pt-4">
                <button type="submit" class="px-8 py-3 bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm rounded-xl shadow-xl shadow-amber-600/30 transition flex items-center">
                    <i class="fa-solid fa-save ml-2 text-lg"></i> پاشەکەوتکردن و نوێکردنەوەی سەرجەم نرخەکان
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
