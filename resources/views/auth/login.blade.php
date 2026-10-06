<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چوونەنەژوورەوەی فەرمانبەران - بەڕێوەبەرایەتی گومرگی سلێمانی</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style> body { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950 min-h-screen flex items-center justify-center p-4 selection:bg-sky-500 selection:text-white">

    <div class="max-w-md w-full space-y-6">
        <!-- Logo Header -->
        <div class="text-center space-y-2">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center mx-auto shadow-2xl shadow-sky-500/30">
                <i class="fa-solid fa-passport text-white text-3xl"></i>
            </div>
            <h1 class="text-2xl font-black text-white">بەڕێوەبەرایەتی گومرگی سلێمانی</h1>
            <p class="text-xs text-slate-400">سیستەمی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری</p>
        </div>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-3 rounded-xl text-xs text-center">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-3 rounded-xl text-xs text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Login Form Card -->
        <div class="bg-slate-800/80 backdrop-blur border border-slate-700/80 rounded-2xl p-6 shadow-2xl space-y-4">
            <h2 class="text-sm font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center">
                <i class="fa-solid fa-right-to-bracket ml-2"></i> چوونەنەژوورەوەی فەرمانبەران
            </h2>

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ناوی بەکارهێنەر / ئیمەیڵ (Username / Email)</label>
                    <div class="relative">
                        <input type="text" name="email" required placeholder="admin یان admin@customs.gov.krd" dir="ltr"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl pr-10 pl-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:border-sky-500 focus:outline-none">
                        <i class="fa-solid fa-user absolute right-3 top-3.5 text-slate-500 text-xs"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">وشەی تێپەڕ (Password)</label>
                    <div class="relative">
                        <input type="password" name="password" required placeholder="••••••••" dir="ltr"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl pr-10 pl-4 py-2.5 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        <i class="fa-solid fa-lock absolute right-3 top-3.5 text-slate-500 text-xs"></i>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-700 text-sky-600 focus:ring-sky-500">
                        <span class="mr-2">لەبیرمهێنانەوە</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-xl shadow-sky-600/30 transition flex items-center justify-center">
                    <i class="fa-solid fa-sign-in-alt ml-2"></i> چوونەژوورەوە
                </button>
            </form>
        </div>
    </div>

</body>
</html>
