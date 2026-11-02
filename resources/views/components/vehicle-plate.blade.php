@props([
    'plate' => '',
    'type' => null,
    'size' => 'md', // 'sm', 'md', 'lg'
])

@php
    $rawPlate = trim((string)$plate);
    $typeName = is_object($type) ? ($type->name_kurdish ?? '') : (string)$type;
    
    // پۆلێنکردنی ڕەنگەکان بەپێی ڕێنمایی هاتوچۆی هەرێمی کوردستان:
    // ١. تەکسی و نەفەرات: سوور (Red)
    // ٢. بارهەڵگر: زەرد (Yellow)
    // ٣. تایبەت: سپی (White)
    $isTaxiOrPassenger = str_contains($typeName, 'کرێ') || 
                         str_contains($typeName, 'أجرة') || 
                         str_contains($typeName, 'تەکسی') || 
                         str_contains($typeName, 'نەفەر') || 
                         str_contains($typeName, 'پاس') || 
                         str_contains($typeName, 'حافلة') || 
                         str_contains(strtolower($typeName), 'taxi') || 
                         str_contains(strtolower($typeName), 'bus');

    $isCargo = str_contains($typeName, 'بار') || 
               str_contains($typeName, 'حمل') || 
               str_contains($typeName, 'بارهەڵگر') || 
               str_contains(strtolower($typeName), 'truck') || 
               str_contains(strtolower($typeName), 'cargo');
    
    if ($isTaxiOrPassenger) {
        // تەکسی و نەفەرات: تابلۆی سوور بە نووسینی سپی
        $bgClasses = 'bg-gradient-to-b from-red-600 via-rose-700 to-red-800 text-white border-red-900 shadow-md shadow-red-700/25';
        $stripClass = 'bg-red-950 text-white font-black';
        $screwClass = 'bg-red-900 border-red-950';
        $textColor = 'text-white';
        $letterColor = 'text-amber-300';
        $subTextColor = 'text-red-200';
        $typeLabel = 'تەکسی / نەفەرات';
    } elseif ($isCargo) {
        // بارهەڵگر: تابلۆی زەرد بە نووسینی ڕەش
        $bgClasses = 'bg-gradient-to-b from-amber-300 via-yellow-400 to-amber-500 text-slate-950 border-amber-600 shadow-md shadow-amber-500/20';
        $stripClass = 'bg-blue-800 text-white font-black';
        $screwClass = 'bg-amber-700/70 border-amber-800';
        $textColor = 'text-slate-950';
        $letterColor = 'text-rose-800';
        $subTextColor = 'text-slate-800';
        $typeLabel = 'بارهەڵگر';
    } else {
        // تایبەت: تابلۆی سپی کلاسیک بە لێواری ڕەش و مێتالیک
        $bgClasses = 'bg-gradient-to-b from-white via-slate-50 to-slate-200 text-slate-950 border-slate-400 shadow-md';
        $stripClass = 'bg-blue-700 text-white font-black';
        $screwClass = 'bg-slate-400/80 border-slate-500';
        $textColor = 'text-slate-950';
        $letterColor = 'text-rose-700';
        $subTextColor = 'text-slate-600';
        $typeLabel = 'تایبەت';
    }

    // دیاریکردنی چوار پارێزگاکەی هەرێمی کوردستان
    $provinces = [
        '21' => 'سلێمانی',
        '22' => 'هەولێر',
        '23' => 'هەڵەبجە',
        '24' => 'دهۆک',
    ];

    $isNewFormat = false;
    $provinceCode = null;
    $provinceName = null;
    $letter = '';
    $number = '';

    // پشکنینی سیستەمی نوێ: تابلۆکە بە کۆدی پارێزگا دەستپێدەکات (21, 22, 23, 24)
    if (preg_match('/^(2[1-4])\s*([a-zA-Z\x{0621}-\x{064A}]?)\s*[-_\/]?\s*(\d+)$/u', $rawPlate, $m)) {
        $isNewFormat = true;
        $provinceCode = $m[1];
        $letter = strtoupper($m[2]);
        $number = $m[3];
        $provinceName = $provinces[$provinceCode] ?? null;
    } elseif (preg_match('/^(2[1-4])\s*[-_\/]\s*([a-zA-Z\x{0621}-\x{064A}]?)\s*[-_\/]?\s*(\d+)$/u', $rawPlate, $m)) {
        $isNewFormat = true;
        $provinceCode = $m[1];
        $letter = strtoupper($m[2]);
        $number = $m[3];
        $provinceName = $provinces[$provinceCode] ?? null;
    } elseif (preg_match('/^([a-zA-Z\x{0621}-\x{064A}])\s*[-_\/]?\s*(\d+)$/u', $rawPlate, $m)) {
        // زانیارییە کۆنەکان: سەرەتا بە پیت دەستپێدەکات وەک B44592 یان A67499
        $isNewFormat = false;
        $letter = strtoupper($m[1]);
        $number = $m[2];
    } else {
        // زانیارییە کۆنەکان: بێ پیتە و تەنها ژمارەیە وەک 12345 یان هەر دەقێکی تر
        $isNewFormat = false;
        $number = $rawPlate;
    }
@endphp

@if($size === 'sm')
    <!-- Small Plate (خشتەی مامەڵەکان و لیستەکان) -->
    <div class="inline-flex items-center select-none font-mono font-black border rounded-[5px] overflow-hidden {{ $bgClasses }} ring-1 ring-black/10 shadow-sm" dir="ltr" title="تابلۆ: {{ $rawPlate }} {{ $isNewFormat ? '(' . $provinceName . ')' : '' }} - {{ $typeLabel }}">
        <!-- Left Strip -->
        <div class="h-full px-1.5 py-0.5 {{ $isTaxiOrPassenger ? 'bg-red-950 text-white' : 'bg-blue-700 text-white' }} flex flex-col justify-center items-center text-[8px] font-black tracking-tighter leading-none border-r border-black/20">
            <span>KR</span>
            <span class="text-[6px] opacity-80">IRAQ</span>
        </div>
        
        <!-- Main Plate Content -->
        <div class="px-2 py-0.5 flex items-center gap-1 text-xs tracking-wider">
            @if($isNewFormat && $provinceCode)
                <span class="text-[10px] {{ $subTextColor }} font-bold font-mono">{{ $provinceCode }}</span>
            @endif
            @if($letter)
                <span class="text-[11px] font-black {{ $letterColor }}">{{ $letter }}</span>
            @endif
            <span class="font-black {{ $textColor }} font-mono tracking-widest">{{ $number }}</span>
            @if($isNewFormat && $provinceName)
                <span class="text-[9px] font-bold text-slate-500 mr-0.5 font-sans" dir="rtl">{{ $provinceName }}</span>
            @endif
        </div>
    </div>
@elseif($size === 'lg')
    <!-- Large Plate (کارتی سەرەکی سەرەوەی لاپەڕەی مامەڵە) -->
    <div class="inline-flex items-center select-none font-mono border-2 rounded-xl overflow-hidden {{ $bgClasses }} ring-2 ring-black/20 shadow-lg relative p-0.5" dir="ltr">
        <!-- Screw Holes for Realism -->
        <div class="absolute left-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full {{ $screwClass }} border shadow-inner"></div>
        <div class="absolute right-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full {{ $screwClass }} border shadow-inner"></div>

        <!-- Left Country & Province Blue Band -->
        <div class="h-12 px-2.5 {{ $isTaxiOrPassenger ? 'bg-red-950 text-white' : 'bg-gradient-to-b from-blue-700 via-blue-800 to-blue-900 text-white' }} flex flex-col justify-center items-center text-[10px] font-black tracking-wider leading-tight border-r border-black/30 rounded-l-lg ml-2">
            <span class="text-[8px] tracking-widest opacity-80">IRAQ</span>
            <span class="text-xs font-black text-amber-300">KR</span>
            @if($isNewFormat && $provinceName)
                <span class="text-[8px] text-emerald-200 mt-0.5 font-sans font-bold" dir="rtl">{{ $provinceName }}</span>
            @endif
        </div>

        <!-- Center Plate Content -->
        <div class="px-4 py-1.5 flex items-center gap-2.5 {{ $textColor }}">
            @if($isNewFormat && $provinceCode)
                <span class="text-lg font-black {{ $subTextColor }} border-r border-black/20 pr-2 font-mono" title="{{ $provinceName }}">
                    {{ $provinceCode }}
                </span>
            @endif
            @if($letter)
                <span class="text-xl font-black {{ $letterColor }} {{ $isTaxiOrPassenger ? 'bg-black/20 border-white/30' : 'bg-rose-500/10 border-rose-300/60' }} px-1.5 py-0.5 rounded border font-mono">
                    {{ $letter }}
                </span>
            @endif
            <span class="text-2xl font-black font-mono tracking-widest {{ $textColor }} drop-shadow-sm">
                {{ $number }}
            </span>
        </div>

        <!-- Right Type & Province Tag -->
        <div class="px-2.5 py-1 mr-2 text-[10px] font-black border-l border-black/20 flex flex-col items-center justify-center {{ $subTextColor }}" dir="rtl">
            @if($isNewFormat && $provinceName)
                <span class="text-[11px] font-black text-sky-700 dark:text-sky-300">IRAQ - {{ $provinceName }}</span>
            @else
                <span class="text-[10px] opacity-75 font-mono">IRAQ</span>
            @endif
            <span class="text-[10px] font-black mt-0.5">{{ $typeLabel }}</span>
        </div>
    </div>
@else
    <!-- Medium Plate (Default: Cards & Modals) -->
    <div class="inline-flex items-center select-none font-mono border-2 rounded-lg overflow-hidden {{ $bgClasses }} ring-1 ring-black/10 shadow relative" dir="ltr">
        <!-- Screw Holes -->
        <div class="absolute left-1 top-1/2 -translate-y-1/2 w-1 h-1 rounded-full {{ $screwClass }} border"></div>
        <div class="absolute right-1 top-1/2 -translate-y-1/2 w-1 h-1 rounded-full {{ $screwClass }} border"></div>

        <!-- Left Country Strip -->
        <div class="px-2 py-1 {{ $isTaxiOrPassenger ? 'bg-red-950 text-white' : 'bg-gradient-to-b from-blue-700 to-blue-800 text-white' }} flex flex-col justify-center items-center text-[9px] font-black leading-tight border-r border-black/20 ml-1.5">
            <span class="text-[7px] opacity-80">IRAQ</span>
            <span class="text-[10px] font-black text-amber-300">KR</span>
        </div>

        <!-- Main Content -->
        <div class="px-3 py-1 flex items-center gap-2 {{ $textColor }}">
            @if($isNewFormat && $provinceCode)
                <span class="text-xs font-black {{ $subTextColor }} font-mono">{{ $provinceCode }}</span>
            @endif
            @if($letter)
                <span class="text-sm font-black {{ $letterColor }} font-mono">{{ $letter }}</span>
            @endif
            <span class="text-base font-black font-mono tracking-wider {{ $textColor }}">{{ $number }}</span>
        </div>

        <!-- Type Badge & Province -->
        <div class="px-2 py-0.5 mr-1.5 text-[9px] font-black {{ $subTextColor }} border-l border-black/20" dir="rtl">
            @if($isNewFormat && $provinceName)
                <span>{{ $provinceName }}</span> - 
            @endif
            <span>{{ $typeLabel }}</span>
        </div>
    </div>
@endif
