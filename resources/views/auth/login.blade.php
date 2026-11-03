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

    <div class="max-w-md w-full space-y-5">
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
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-3 rounded-xl text-xs text-center font-bold">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-3.5 rounded-xl text-xs text-center font-bold space-y-1">
                <div><i class="fa-solid fa-circle-exclamation ml-1"></i> {{ $errors->first() }}</div>
                <div class="text-[11px] text-amber-300 font-normal">بۆ چوونەژوورەوەی ڕاستەوخۆ دەتوانیت لە خوارەوە یەکێک لە ئەکاونتەکان کلیک بکەیت.</div>
            </div>
        @endif

        <!-- Login Form Card -->
        <div class="bg-slate-800/80 backdrop-blur border border-slate-700/80 rounded-2xl p-6 shadow-2xl space-y-4">
            <h2 class="text-sm font-bold text-sky-400 border-b border-slate-700 pb-2 flex items-center justify-between">
                <span class="flex items-center"><i class="fa-solid fa-right-to-bracket ml-2"></i> چوونەنەژوورەوەی فەرمانبەران</span>
                <span class="text-[11px] font-normal text-slate-400">وشەی تێپەڕ: <strong>123456</strong></span>
            </h2>

            <form action="{{ route('login') }}" method="POST" class="space-y-4" id="loginForm">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">ناوی بەکارهێنەر / ئیمەیڵ (Username / Email)</label>
                    <div class="relative">
                        <input type="text" id="emailInput" name="email" required placeholder="admin یان admin@customs.gov.krd" dir="ltr"
                               autocapitalize="none" autocomplete="username" spellcheck="false"
                               value="{{ old('email') }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl pr-10 pl-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:border-sky-500 focus:outline-none">
                        <i class="fa-solid fa-user absolute right-3 top-3.5 text-slate-500 text-xs"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">وشەی تێپەڕ (Password)</label>
                    <div class="relative">
                        <input type="password" id="passwordInput" name="password" required placeholder="••••••••" dir="ltr"
                               autocomplete="current-password"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl pr-10 pl-4 py-2.5 text-sm text-slate-100 focus:border-sky-500 focus:outline-none">
                        <i class="fa-solid fa-lock absolute right-3 top-3.5 text-slate-500 text-xs"></i>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" checked class="rounded border-slate-700 text-sky-600 focus:ring-sky-500">
                        <span class="mr-2">لەبیرمهێنانەوە</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-xl shadow-sky-600/30 transition flex items-center justify-center">
                    <i class="fa-solid fa-sign-in-alt ml-2"></i> چوونەژوورەوە
                </button>
            </form>

            <!-- Quick Auto-Fill / 1-Click Role Logins -->
            <div class="pt-3 border-t border-slate-700/80 space-y-2.5">
                <div class="text-[11px] font-bold text-slate-400 flex items-center justify-between">
                    <span><i class="fa-solid fa-bolt text-amber-400 ml-1"></i> چوونەژوورەوەی خێرا (1-Click Login):</span>
                    <span class="font-mono text-emerald-400">پاسۆرد: 123456</span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('quick_login', 'admin') }}" class="p-2 bg-indigo-950/70 hover:bg-indigo-900/80 border border-indigo-700/60 text-indigo-300 rounded-xl font-bold flex items-center justify-center gap-1.5 transition shadow">
                        <i class="fa-solid fa-shield-halved text-indigo-400"></i>
                        <span>ئەدمین (Admin)</span>
                    </a>
                    <a href="{{ route('quick_login', 'amjad') }}" class="p-2 bg-sky-950/70 hover:bg-sky-900/80 border border-sky-700/60 text-sky-300 rounded-xl font-bold flex items-center justify-center gap-1.5 transition shadow">
                        <i class="fa-solid fa-user-gear text-sky-400"></i>
                        <span>ئەمجەد (Amjad)</span>
                    </a>
                </div>

                <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5 pt-1 text-[11px]">
                    <a href="{{ route('quick_login', 'data_entry') }}" class="py-1.5 px-1 bg-slate-900 hover:bg-slate-700/80 border border-slate-700 text-slate-300 rounded-lg text-center transition font-semibold" title="کارمەندی داتائەنتەری">داتائەنتەری</a>
                    <a href="{{ route('quick_login', 'inspector') }}" class="py-1.5 px-1 bg-slate-900 hover:bg-slate-700/80 border border-slate-700 text-slate-300 rounded-lg text-center transition font-semibold" title="لێپرسراوی تەخمین">تەخمین</a>
                    <a href="{{ route('quick_login', 'auditor') }}" class="py-1.5 px-1 bg-slate-900 hover:bg-slate-700/80 border border-slate-700 text-slate-300 rounded-lg text-center transition font-semibold" title="وردبینی">وردبینی</a>
                    <a href="{{ route('quick_login', 'cashier') }}" class="py-1.5 px-1 bg-slate-900 hover:bg-slate-700/80 border border-slate-700 text-slate-300 rounded-lg text-center transition font-semibold" title="ژمێریار و وەسڵبڕ">وەسڵبڕ</a>
                    <a href="{{ route('quick_login', 'booklet') }}" class="py-1.5 px-1 bg-slate-900 hover:bg-slate-700/80 border border-slate-700 text-slate-300 rounded-lg text-center transition font-semibold" title="بەشی دەفتەر">دەفتەر</a>
                    <a href="{{ route('quick_login', 'archiver') }}" class="py-1.5 px-1 bg-slate-900 hover:bg-slate-700/80 border border-slate-700 text-slate-300 rounded-lg text-center transition font-semibold" title="کارمەندی ئەرشیف و سەپمیت">ئەرشیف</a>
                </div>
            </div>
        </div>

        <!-- Footer Help -->
        <div class="text-center text-[11px] text-slate-500">
            سیستەمی هاتوچۆ و گومرگی دەرهێنانی دەفتەری ئۆتۆمبێل &copy; {{ date('Y') }}
        </div>
    </div>

</body>
</html>
