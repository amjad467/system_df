<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>دەستگەیشتن ڕاگیراوە • 403 Forbidden</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-900 border border-red-500/30 rounded-3xl p-8 text-center shadow-2xl relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-40 h-40 bg-red-600/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-12 w-40 h-40 bg-red-500/10 rounded-full blur-2xl"></div>

        <div class="w-20 h-20 bg-red-500/15 border border-red-500/30 rounded-2xl mx-auto flex items-center justify-center text-red-500 text-3xl mb-6 shadow-lg shadow-red-500/10">
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <h1 class="text-2xl font-black text-white mb-2">دەستگەیشتن بە سیستەم ڕاگیراوە</h1>
        <p class="text-sm text-slate-400 mb-6">
            ناونیشانی ئایپییەکەت لە لایەن قەڵغانی ئەمنیی بەڕێوەبەرایەتی گومرگەوە بە شێوەیەکی کاتی بلۆک کراوە.
        </p>

        <div class="bg-slate-950/80 rounded-2xl p-4 border border-slate-800 text-xs text-right space-y-2 mb-6">
            <div class="flex justify-between items-center text-slate-400">
                <span>ئایپی بلۆککراو:</span>
                <span class="font-mono text-red-400 font-bold">{{ $ip }}</span>
            </div>
            <div class="flex justify-between items-center text-slate-400">
                <span>هۆکار:</span>
                <span class="text-slate-200">{{ $reason }}</span>
            </div>
            <div class="flex justify-between items-center text-slate-400">
                <span>ماوەی ڕاگرتن:</span>
                <span class="text-amber-400 font-bold">{{ $until }}</span>
            </div>
        </div>

        <p class="text-[11px] text-slate-500 mb-6">
            ئەگەر پێت وایە ئەمە بە هەڵە ڕوویداوە، تکایە پەیوەندی بە کارگێڕی گشتی سیستەم لە بەڕێوەبەرایەتی گومرگی سلێمانی بکە.
        </p>

        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
            <i class="fa-solid fa-arrow-rotate-right"></i>
            <span>دووبارە تاقیکردنەوە</span>
        </a>
    </div>
</body>
</html>

