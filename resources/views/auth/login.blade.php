<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title>چوونەنەژوورەوەی فەرمانبەران - بەڕێوەبەرایەتی گومرگی سلێمانی</title>
    
    <!-- PWA Meta Tags & Icons -->
    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="دەفتەری گومرگ">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style> 
        body { 
            font-family: 'Vazirmatn', sans-serif;
            padding-top: env(safe-area-inset-top, 0px);
            padding-bottom: env(safe-area-inset-bottom, 0px);
        } 
        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{
    deferredPrompt: null,
    showIosModal: false,
    canInstall: false,
    isInstalled: (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true,
    isIos: /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream,
    initPwa() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.canInstall = true;
        });
        window.addEventListener('appinstalled', () => {
            this.isInstalled = true;
            this.canInstall = false;
        });
    },
    async promptInstall() {
        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                this.isInstalled = true;
            }
            this.deferredPrompt = null;
        } else if (this.isIos) {
            this.showIosModal = true;
        } else {
            alert('بۆ دابەزاندنی ئەم ئەپە لەسەر شاشەی مۆبایلەکەت، لە مێنۆی وێبگەڕەکەت (سێ خاڵەکەی سەرەوە) کلیک لە [Install app / Add to Home screen] بکە.');
        }
    }
}" x-init="initPwa()" class="bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950 min-h-screen flex items-center justify-center p-4 selection:bg-sky-500 selection:text-white">

    <div class="max-w-md w-full space-y-4">
        <!-- PWA Install Card for Mobile & Desktop -->
        <div x-show="!isInstalled" x-cloak class="bg-gradient-to-r from-sky-950/90 via-indigo-950/90 to-slate-900/90 border border-sky-500/40 rounded-2xl p-3 shadow-xl flex items-center justify-between gap-2.5">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-sky-500/30">
                    <i class="fa-solid fa-mobile-screen-button text-lg"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-black text-white block truncate">دابەزاندنی ئەپڵیکەیشن (App)</span>
                    <span class="text-[10px] text-sky-200 block truncate">بۆ کارپێکردنی خێرا لەسەر مۆبایل و کۆمپیوتەر</span>
                </div>
            </div>
            <button type="button" @click="promptInstall()" class="px-3.5 py-2 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-black text-xs rounded-xl shadow-lg shadow-sky-500/30 transition flex items-center gap-1.5 shrink-0 active:scale-95 animate-pulse">
                <i class="fa-solid fa-download text-xs"></i>
                <span>دابەزاندنی ئەپ</span>
            </button>
        </div>

        <!-- iOS Install Helper Modal -->
        <div x-show="showIosModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl">
                <div class="w-14 h-14 rounded-2xl bg-sky-500/20 border border-sky-500/40 text-sky-400 flex items-center justify-center mx-auto text-2xl shadow-inner">
                    <i class="fa-brands fa-apple"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-white">دامەزراندن لەسەر iPhone و iPad</h3>
                    <p class="text-xs text-slate-400 mt-1">بۆ کارپێکردن وەک ئەپی سەربەخۆ لەسەر مۆبایلەکەت:</p>
                </div>
                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 text-right space-y-3 text-xs text-slate-200">
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">١</span>
                        <span>لە بنی وێبگەڕی Safari کلیک لە دوگمەی <strong>هاوبەشکردن (Share <i class="fa-solid fa-arrow-up-from-bracket text-sky-400"></i>)</strong> بکە.</span>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">٢</span>
                        <span>بڕۆ خوارەوە و کلیک لە <strong>زیادکردن بۆ شاشەی سەرەکی (Add to Home Screen <i class="fa-regular fa-square-plus text-emerald-400"></i>)</strong> بکە.</span>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">٣</span>
                        <span>لە سەرەوە کلیک لە <strong>Add</strong> بکە. پیرۆزە، وەک ئەپ زیاد بوو!</span>
                    </div>
                </div>
                <button type="button" @click="showIosModal = false" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition">
                    تێگەیشتم (داخستن)
                </button>
            </div>
        </div>
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

                <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5 pt-1 text-[11px]">
                    <a href="{{ route('quick_login', 'data_entry') }}" class="py-1.5 px-1 bg-slate-900 hover:bg-slate-700/80 border border-slate-700 text-slate-300 rounded-lg text-center transition font-semibold" title="کارمەندی داتائەنتەری">داتائەنتەری</a>
                    <a href="{{ route('quick_login', 'inspector') }}" class="py-1.5 px-1 bg-slate-900 hover:bg-slate-700/80 border border-slate-700 text-slate-300 rounded-lg text-center transition font-semibold" title="لێپرسراوی تەخمین">تەخمین</a>
                    <a href="{{ route('quick_login', 'legal') }}" class="py-1.5 px-1 bg-amber-950/70 hover:bg-amber-900/80 border border-amber-700/70 text-amber-300 rounded-lg text-center transition font-bold" title="فەرمانبەری یاسایی (بەڵێننامە)">یاسایی</a>
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

    <!-- Service Worker Registration for PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>
</html>
