@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fa-solid fa-clock-rotate-left ml-2 text-emerald-400"></i> لۆگی سەرجەم جولە و چالاکییەکان
            </h2>
            <p class="text-xs text-slate-400">تۆماری ته‌واوى کردارەکان، گۆڕانکاری و بڕیارەکان بەپێی بەکارهێنەر.</p>
        </div>
    </div>

    <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 text-xs uppercase border-b border-slate-700">
                    <tr>
                        <th class="p-3.5">#</th>
                        <th class="p-3.5">بەکارهێنەر</th>
                        <th class="p-3.5">جولە / کردار</th>
                        <th class="p-3.5">تێبینی و فۆرمات</th>
                        <th class="p-3.5">IP Address</th>
                        <th class="p-3.5">کات و بەروار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-700/30 transition">
                            <td class="p-3.5 font-mono text-xs text-slate-500">{{ $log->id }}</td>
                            <td class="p-3.5 font-bold text-slate-100">{{ $log->user_name }}</td>
                            <td class="p-3.5 font-semibold text-sky-400">{{ $log->action }}</td>
                            <td class="p-3.5 text-xs text-slate-300">{{ $log->details ?? '-' }}</td>
                            <td class="p-3.5 font-mono text-xs text-slate-500">{{ $log->ip_address }}</td>
                            <td class="p-3.5 font-mono text-xs text-slate-400" dir="ltr">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-500 text-sm">هیچ لۆگێک تۆمار نەکراوە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
