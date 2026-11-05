<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title>سیستەمی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری - بەڕێوەبەرایەتی گومرگی سلێمانی</title>
    
    <!-- PWA & Mobile Capabilities -->
    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="دەفتەری گومرگ">
    <meta name="application-name" content="دەفتەری گومرگ">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#0284c7" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">

    <!-- Icons -->
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Theme Init Script (Prevent Flash) -->
    <script>
        if (localStorage.getItem('color-theme') === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Vazirmatn', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            800: '#0c4a6e',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js & FontAwesome -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Vazirmatn', sans-serif;
            padding-top: env(safe-area-inset-top, 0px);
        }
        [x-cloak] { display: none !important; }
        
        /* Mobile Safe Area Padding */
        .safe-pb {
            padding-bottom: calc(7.5rem + env(safe-area-inset-bottom, 0px));
        }
        .safe-bottom-bar {
            padding-bottom: max(0.4rem, env(safe-area-inset-bottom, 0px));
        }

        /* Smooth, accessible scrolling & visible custom scrollbars */
        html {
            overflow-x: auto;
            overflow-y: scroll;
        }
        body {
            min-width: 100%;
        }

        /* Modern Visible Custom Scrollbars (Horizontal & Vertical) */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        ::-webkit-scrollbar-track {
            background: #090d16;
            border-radius: 6px;
        }
        ::-webkit-scrollbar-thumb {
            background: #0284c7;
            border-radius: 6px;
            border: 2px solid #090d16;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #38bdf8;
        }
        * {
            scrollbar-width: thin;
            scrollbar-color: #0284c7 #090d16;
        }

        /* Smooth touch scrolling */
        .touch-scroll {
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body x-data="{ 
    showMyPasswordModal: false, 
    mobileMenuOpen: false,
    showMobileBarcodeModal: false,
    isDark: document.documentElement.classList.contains('dark'),
    deferredPrompt: null,
    showIosModal: false,
    canInstall: false,
    isInstalled: (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true,
    isIos: /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream,
    showInstallBanner: false,
    initPwa() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.canInstall = true;
            if (!localStorage.getItem('pwa_banner_dismissed') && !this.isInstalled) {
                this.showInstallBanner = true;
            }
        });
        window.addEventListener('appinstalled', () => {
            this.isInstalled = true;
            this.showInstallBanner = false;
            this.canInstall = false;
            this.deferredPrompt = null;
        });
        if (this.isIos && !this.isInstalled && !localStorage.getItem('pwa_banner_dismissed')) {
            this.showInstallBanner = true;
        }
    },
    async promptInstall() {
        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                this.isInstalled = true;
                this.showInstallBanner = false;
            }
            this.deferredPrompt = null;
        } else if (this.isIos) {
            this.showIosModal = true;
        } else {
            alert('بۆ دابەزاندنی ئەم ئەپە، تکایە لە مێنۆی براوسەرەکەت (سێ خاڵەکە) کلیک لە [Install App / Add to Home screen] بکە.');
        }
    },
    dismissBanner() {
        this.showInstallBanner = false;
        localStorage.setItem('pwa_banner_dismissed', '1');
    },
    toggleTheme() {
        this.isDark = !this.isDark;
        if (this.isDark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        }
    }
}" x-init="initPwa()" class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col font-sans antialiased selection:bg-sky-500 selection:text-white transition-colors duration-200">

    <!-- PWA Install Banner for Mobile & Tablet -->
    <div x-show="showInstallBanner && !isInstalled" x-cloak x-transition.duration.300ms
         class="bg-gradient-to-r from-sky-900 via-indigo-900 to-slate-900 text-white border-b border-sky-500/40 px-3 py-2.5 shadow-xl relative z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center shrink-0 shadow-md">
                    <i class="fa-solid fa-mobile-screen-button text-sm text-white"></i>
                </div>
                <div class="min-w-0">
                    <strong class="font-black text-sky-300 block truncate">ئەپی دەفتەری گومرگی سلێمانی (PWA)</strong>
                    <span class="text-[11px] text-slate-300 block truncate">ئەپەکە دابەزێنە بۆ سەر شاشەی مۆبایل و تابلێتت بۆ خێرایی زیاتر</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" @click="promptInstall()" class="px-3 py-1.5 bg-sky-500 hover:bg-sky-400 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-sky-500/30 transition flex items-center gap-1">
                    <i class="fa-solid fa-download text-[11px]"></i>
                    <span>دامەزراندن</span>
                </button>
                <button type="button" @click="dismissBanner()" class="w-7 h-7 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- iOS Install Helper Modal -->
    <div x-show="showIosModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-md z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl animate-in fade-in zoom-in duration-200">
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

    <!-- Mobile Fast Barcode Modal -->
    <div x-show="showMobileBarcodeModal" x-cloak class="fixed inset-0 bg-slate-950/85 backdrop-blur-md z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-sm w-full p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-barcode text-sky-400"></i>
                    <span>سکانی خێرای بارکۆد</span>
                </h3>
                <button type="button" @click="showMobileBarcodeModal = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
            <form action="{{ route('transactions.scan') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">ژمارەی بارکۆدی ١٠ ڕەقەمی بنووسە یان سڕەوشت بکە:</label>
                    <input type="text" name="barcode" required autofocus placeholder="وەک: 2611030001" dir="ltr"
                           class="w-full bg-slate-950 border-2 border-sky-500 rounded-2xl px-4 py-3 text-base text-white font-mono text-center tracking-widest focus:outline-none focus:ring-2 focus:ring-sky-400">
                </div>
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-sky-600/30 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>دۆزینەوە و کردنەوەی مامەڵە</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Top Navigation Bar -->
    <header class="bg-white/95 dark:bg-slate-800/90 backdrop-blur border-b border-slate-200 dark:border-slate-700/80 sticky top-0 z-40 transition-colors duration-200">
        <div class="max-w-[1680px] w-full mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16 gap-2">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-2 sm:space-x-3 space-x-reverse min-w-0 shrink-0">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-xl bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 shrink-0">
                        <i class="fa-solid" :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'"></i>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-sky-500/20 shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-passport text-white text-base sm:text-xl"></i>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-xs sm:text-base font-black bg-clip-text text-transparent bg-gradient-to-r from-sky-600 dark:from-sky-400 to-indigo-700 dark:to-indigo-300 leading-tight truncate">
                                گومرگی سلێمانی
                            </h1>
                            <p class="text-[9px] sm:text-[11px] text-slate-500 dark:text-slate-400 truncate">دەفتەری ئۆتۆمبێل</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden lg:flex items-center space-x-1 space-x-reverse min-w-0 flex-wrap">
                    <a href="{{ route('dashboard') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('dashboard') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie ml-1"></i> داشبۆرد
                    </a>
                    <a href="{{ route('transactions.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('transactions.index') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white' }}">
                        <i class="fa-solid fa-list-check ml-1"></i> مامەڵەکان
                    </a>

                    <!-- چاپی نوسراوەکان (Allowed via Permission) -->
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasPermission('prints.official_index')))
                        <a href="{{ route('transactions.official_prints') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('transactions.official_prints') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-amber-600 dark:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-amber-700 dark:hover:text-white' }}">
                            <i class="fa-solid fa-print ml-1"></i> نوسراوەکان
                        </a>
                    @endif

                    <!-- محاسبة ٦٦ (Allowed via Permission) -->
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasPermission('reports.accounting_66')))
                        <a href="{{ route('reports.accounting_66') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('reports.accounting_66*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-amber-600 dark:text-amber-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-amber-700 dark:hover:text-white' }}">
                            <i class="fa-solid fa-file-invoice-dollar ml-1"></i> محاسبة ٦٦
                        </a>
                    @endif

                    <!-- دەفتەرە بەسەرچووەکان (Allowed via Permission) -->
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasPermission('reports.expired_booklets')))
                        <a href="{{ route('transactions.expired_booklets') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('transactions.expired_booklets') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'text-rose-600 dark:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-rose-700 dark:hover:text-white' }}">
                            <i class="fa-solid fa-calendar-xmark ml-1"></i> بەسەرچووەکان
                        </a>
                    @endif

                    <!-- مامەڵەی نوێ (Allowed via Permission) -->
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.create')))
                        <a href="{{ route('transactions.create') }}" class="px-3 py-1.5 rounded-lg text-xs font-extrabold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition">
                            <i class="fa-solid fa-plus-circle ml-1"></i> تۆماری نوێ
                        </a>
                    @endif

                    <!-- Admin Dropdown Menu -->
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <div class="relative" x-data="{ openAdminMenu: false }">
                            <button @click="openAdminMenu = !openAdminMenu" type="button" 
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 {{ request()->routeIs('settings.*') || request()->routeIs('users.*') || request()->routeIs('logs.*') || request()->routeIs('transactions.trash') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white border border-slate-300 dark:border-slate-700' }}">
                                <i class="fa-solid fa-gear text-indigo-400"></i>
                                <span>کارگێڕی</span>
                                <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openAdminMenu ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="openAdminMenu" @click.away="openAdminMenu = false" x-cloak 
                                 x-transition.origin.top.right.duration.200ms
                                 class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl p-2 z-50 space-y-1">
                                <a href="{{ route('settings.permissions') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('settings.permissions*') ? 'bg-purple-600 text-white' : 'text-purple-600 dark:text-purple-300 hover:bg-purple-500/10' }}">
                                    <i class="fa-solid fa-user-shield w-4 text-center"></i>
                                    <span>خشتەی پێرمشنەکان</span>
                                </a>
                                <a href="{{ route('users.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('users.index') ? 'bg-indigo-600 text-white' : 'text-indigo-600 dark:text-indigo-300 hover:bg-indigo-500/10' }}">
                                    <i class="fa-solid fa-users-gear w-4 text-center"></i>
                                    <span>فەرمانبەران</span>
                                </a>
                                <a href="{{ route('settings.lookups') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('settings.lookups') ? 'bg-teal-600 text-white' : 'text-teal-600 dark:text-teal-300 hover:bg-teal-500/10' }}">
                                    <i class="fa-solid fa-folder-tree w-4 text-center"></i>
                                    <span>پێناسەکان</span>
                                </a>
                                <a href="{{ route('settings.pricing') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('settings.pricing') ? 'bg-amber-600 text-white' : 'text-amber-600 dark:text-amber-300 hover:bg-amber-500/10' }}">
                                    <i class="fa-solid fa-sliders w-4 text-center"></i>
                                    <span>نرخەکان</span>
                                </a>
                                <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                                <a href="{{ route('transactions.trash') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('transactions.trash') ? 'bg-rose-600 text-white' : 'text-rose-600 dark:text-rose-300 hover:bg-rose-500/10' }}">
                                    <i class="fa-solid fa-trash-can w-4 text-center"></i>
                                    <span>مامەڵە سڕاوەکان</span>
                                </a>
                                <a href="{{ route('logs.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('logs.index') ? 'bg-sky-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <i class="fa-solid fa-clock-rotate-left w-4 text-center"></i>
                                    <span>لۆگی سیستم</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </nav>

                <!-- Action Section (Dark mode, Notifs, Profile) -->
                <div class="flex items-center space-x-1.5 sm:space-x-2 space-x-reverse">
                    <!-- Mobile Barcode Quick Trigger -->
                    <button type="button" @click="showMobileBarcodeModal = true" class="md:hidden p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-sky-600 dark:text-sky-400 border border-slate-200 dark:border-slate-700" title="سکانی خێرای بارکۆد">
                        <i class="fa-solid fa-barcode text-sm"></i>
                    </button>

                    <!-- Mobile App Install Quick Button -->
                    <button type="button" x-show="!isInstalled" @click="promptInstall()" class="md:hidden flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white text-[11px] font-bold shadow-md shadow-sky-600/25 transition active:scale-95" title="دابەزاندنی سیستەم وەک ئەپ">
                        <i class="fa-solid fa-download text-xs"></i>
                        <span>ئەپ</span>
                    </button>

                    <!-- PWA Desktop Install Button -->
                    <button type="button" x-show="!isInstalled" @click="promptInstall()" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white text-xs font-black shadow-md shadow-sky-600/30 transition active:scale-95 animate-pulse" title="دابەزاندنی سیستەم وەک بەرنامەی سەربەخۆ">
                        <i class="fa-solid fa-mobile-screen-button text-xs"></i>
                        <span>دابەزاندنی ئەپ</span>
                    </button>

                    <!-- Dark / Light Mode Toggle Button -->
                    <button @click="toggleTheme()" type="button" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-amber-500 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition border border-slate-200 dark:border-slate-700" title="گۆڕینی دۆخی ڕووناکی / تاریکی">
                        <i class="fa-solid fa-sun text-sm" x-show="!isDark"></i>
                        <i class="fa-solid fa-moon text-sm" x-show="isDark"></i>
                    </button>

                    <!-- User Profile & Logout -->
                    @auth
                        @php
                            $unreadNotifications = \App\Services\NotificationService::getUnreadForCurrentUser();
                        @endphp

                        <!-- Notification Bell -->
                        <div class="relative" x-data="{ openNotifs: false }">
                            <button @click="openNotifs = !openNotifs" class="relative p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition border border-slate-200 dark:border-slate-700">
                                <i class="fa-solid fa-bell text-sm text-sky-500"></i>
                                @if($unreadNotifications->count() > 0)
                                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-rose-600 text-white rounded-full text-[9px] font-extrabold flex items-center justify-center animate-pulse">
                                        {{ $unreadNotifications->count() }}
                                    </span>
                                @endif
                            </button>

                            <div x-show="openNotifs" @click.away="openNotifs = false" x-cloak class="absolute left-0 mt-2 w-72 sm:w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl p-3 z-50 space-y-2">
                                <h4 class="text-xs font-bold text-sky-500 dark:text-sky-400 border-b border-slate-100 dark:border-slate-800 pb-2 flex items-center justify-between">
                                    <span><i class="fa-solid fa-bell ml-1"></i> ئاگادارییەکانی دەسەڵاتی تۆ</span>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $unreadNotifications->count() }} نوێ</span>
                                </h4>
                                <div class="max-h-60 overflow-y-auto space-y-1.5 touch-scroll">
                                    @forelse($unreadNotifications as $notif)
                                        <a href="{{ route('notifications.read', $notif->id) }}" class="block p-2 bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 transition">
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $notif->title }}</span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">{{ $notif->message }}</span>
                                            <span class="text-[9px] text-sky-500 dark:text-sky-400 font-mono block mt-1">{{ $notif->created_at->diffForHumans() }}</span>
                                        </a>
                                    @empty
                                        <p class="text-xs text-slate-500 text-center py-3">هیچ ئاگادارییەکی نوێت نییە.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="mr-1 sm:mr-2 border-r border-slate-200 dark:border-slate-700 pr-2 sm:pr-3 flex items-center space-x-1.5 sm:space-x-2 space-x-reverse">
                            <div class="text-right hidden sm:block">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block truncate max-w-[120px]">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold block">{{ auth()->user()->role_name_kurdish }}</span>
                            </div>
                            <button type="button" @click="showMyPasswordModal = true" title="گۆڕینی پاسوۆردی کەسی" class="w-8 h-8 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-500 dark:text-amber-400 flex items-center justify-center transition">
                                <i class="fa-solid fa-key text-xs"></i>
                            </button>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" title="چوونەدەرەوە" class="w-8 h-8 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 dark:text-rose-400 flex items-center justify-center transition">
                                    <i class="fa-solid fa-power-off text-xs"></i>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div x-show="mobileMenuOpen" x-cloak class="lg:hidden py-4 border-t border-slate-200 dark:border-slate-700 space-y-3">
                @auth
                    <!-- User Profile Card in Drawer -->
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-sky-600/20 text-sky-400 flex items-center justify-center font-bold text-base">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white text-xs block">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold block">{{ auth()->user()->role_name_kurdish }}</span>
                            </div>
                        </div>
                        <button type="button" @click="promptInstall()" x-show="!isInstalled" class="px-2.5 py-1 bg-sky-600 text-white rounded-lg text-[10px] font-bold flex items-center gap-1 shadow">
                            <i class="fa-solid fa-download"></i> PWA ئەپ
                        </button>
                    </div>
                @endauth

                <!-- Fast Scanner Barcode Form for Mobile -->
                <form action="{{ route('transactions.scan') }}" method="POST" class="flex items-center relative">
                    @csrf
                    <input type="text" name="barcode" placeholder="سکانی بارکۆد بکە..." 
                           class="w-full bg-slate-50 dark:bg-slate-900 text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl border border-slate-200 dark:border-slate-700 pr-9 pl-3 py-2.5 focus:outline-none focus:border-sky-500 font-mono">
                    <button type="submit" class="absolute right-3 text-slate-400">
                        <i class="fa-solid fa-barcode"></i>
                    </button>
                </form>

                <!-- PWA Install Button inside Mobile Drawer -->
                <button type="button" x-show="!isInstalled" @click="promptInstall()" class="w-full p-2.5 rounded-xl bg-gradient-to-r from-sky-600 via-indigo-600 to-sky-700 hover:from-sky-500 hover:to-indigo-500 text-white flex items-center justify-between text-xs font-black shadow-lg shadow-sky-600/30 transition active:scale-[0.98]">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center">
                            <i class="fa-solid fa-mobile-screen-button text-sm"></i>
                        </div>
                        <span>دابەزاندنی سیستەم وەک ئەپ (Install App)</span>
                    </div>
                    <span class="px-2 py-0.5 bg-white/20 rounded-md text-[10px] font-bold">دابەزاندن</span>
                </button>

                <div class="grid grid-cols-2 gap-2 text-xs font-bold">
                    <a href="{{ route('dashboard') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-sky-600 hover:text-white">
                        <i class="fa-solid fa-chart-pie ml-1.5 text-sky-500"></i> داشبۆرد
                    </a>
                    <a href="{{ route('transactions.index') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-sky-600 hover:text-white">
                        <i class="fa-solid fa-list-check ml-1.5 text-emerald-500"></i> مامەڵەکان
                    </a>

                    <!-- چاپی نوسراوەکان (Allowed via Permission) -->
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasPermission('prints.official_index')))
                        <a href="{{ route('transactions.official_prints') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-amber-600 dark:text-amber-300 hover:bg-amber-600 hover:text-white">
                            <i class="fa-solid fa-print ml-1.5 text-amber-500"></i> چاپی نوسراوەکان
                        </a>
                    @endif

                    <!-- دەفتەرە بەسەرچووەکان (Allowed via Permission) -->
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasPermission('reports.expired_booklets')))
                        <a href="{{ route('transactions.expired_booklets') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-rose-300 dark:border-rose-500/40 text-rose-600 dark:text-rose-300 hover:bg-rose-600 hover:text-white flex items-center justify-center">
                            <i class="fa-solid fa-calendar-xmark ml-1.5 text-rose-500"></i> بەسەرچووەکان
                        </a>
                    @endif

                    <!-- محاسبة ٦٦ (Allowed via Permission) -->
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasPermission('reports.accounting_66')))
                        <a href="{{ route('reports.accounting_66') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-500/40 text-amber-600 dark:text-amber-300 hover:bg-amber-600 hover:text-white flex items-center justify-center">
                            <i class="fa-solid fa-file-invoice-dollar ml-1.5 text-amber-500"></i> محاسبة ٦٦
                        </a>
                    @endif

                    <!-- مامەڵەی نوێ (Allowed via Permission) -->
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.create')))
                        <a href="{{ route('transactions.create') }}" class="p-2.5 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow">
                            <i class="fa-solid fa-plus-circle ml-1.5"></i> مامەڵەی نوێ
                        </a>
                    @endif

                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('settings.lookups') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-teal-300 dark:border-teal-500/40 text-teal-600 dark:text-teal-300 flex items-center justify-center">
                            <i class="fa-solid fa-folder-tree ml-1.5"></i> پێناسەکان
                        </a>
                        <a href="{{ route('users.index') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-indigo-300 dark:border-indigo-500/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                            <i class="fa-solid fa-users-gear ml-1.5"></i> فەرمانبەران
                        </a>
                        <a href="{{ route('settings.permissions') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-purple-300 dark:border-purple-500/40 text-purple-600 dark:text-purple-300 flex items-center justify-center">
                            <i class="fa-solid fa-user-shield ml-1.5"></i> پێرمشنەکان
                        </a>
                        <a href="{{ route('settings.pricing') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-500/40 text-amber-600 dark:text-amber-300 flex items-center justify-center">
                            <i class="fa-solid fa-sliders ml-1.5"></i> نرخەکان
                        </a>
                        <a href="{{ route('transactions.trash') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-rose-300 dark:border-rose-500/40 text-rose-600 dark:text-rose-300 flex items-center justify-center">
                            <i class="fa-solid fa-trash-can ml-1.5"></i> سڕاوەکان
                        </a>
                        <a href="{{ route('logs.index') }}" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-700 dark:text-slate-300">
                            <i class="fa-solid fa-clock-rotate-left ml-1.5 text-slate-500"></i> لۆگ
                        </a>
                    @endif
                </div>

                @auth
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-700/80 flex items-center justify-between text-xs">
                        <button type="button" @click="showMyPasswordModal = true" class="px-3 py-2 bg-amber-500/20 text-amber-600 dark:text-amber-300 rounded-xl font-bold flex items-center">
                            <i class="fa-solid fa-key ml-1"></i> گۆڕینی پاسوۆرد
                        </button>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl font-bold flex items-center shadow">
                                <i class="fa-solid fa-power-off ml-1"></i> دەربچۆ
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Personal Password Change Modal -->
    <div x-show="showMyPasswordModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span><i class="fa-solid fa-key ml-2 text-amber-400"></i> گۆڕینی وشەی نهێنی (پاسوۆرد)</span>
                <button @click="showMyPasswordModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>
            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">پاسوۆردی ئێستات *</label>
                    <input type="password" name="current_password" required placeholder="••••••••" dir="ltr" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">پاسوۆردی نوێ *</label>
                    <input type="password" name="new_password" required placeholder="••••••••" dir="ltr" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">دووبارەکردنەوەی پاسوۆردی نوێ *</label>
                    <input type="password" name="new_password_confirmation" required placeholder="••••••••" dir="ltr" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono">
                </div>
                <div class="flex items-center justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showMyPasswordModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 text-xs font-bold rounded-xl">پاشگەزبوونەوە</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-lg">پاشەکەوتکردن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flash Notifications -->
    <div class="max-w-[1680px] mx-auto px-3 sm:px-6 lg:px-8 mt-3 sm:mt-4 w-full">
        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 dark:text-emerald-400 px-4 py-3 rounded-xl shadow-lg flex items-center justify-between mb-4 text-xs sm:text-sm">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-check text-lg sm:text-xl ml-3"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="bg-amber-500/10 border border-amber-500/30 text-amber-500 dark:text-amber-300 px-4 py-3 rounded-xl shadow-lg flex items-center justify-between mb-4 text-xs sm:text-sm">
                <div class="flex items-center">
                    <i class="fa-solid fa-triangle-exclamation text-lg sm:text-xl ml-3"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            </div>
        @endif

        @if (session('error') || session('duplicate_error'))
            <div x-data="{ openErrorModal: true }" 
                 x-show="openErrorModal" 
                 x-cloak 
                 class="fixed inset-0 bg-slate-950/85 backdrop-blur-md z-50 flex items-center justify-center p-4 transition-all">
                
                <div class="bg-slate-900 border-2 border-rose-500/70 rounded-3xl max-w-lg w-full p-6 shadow-2xl shadow-rose-950/80 space-y-5 text-center relative animate-in fade-in zoom-in duration-200">
                    
                    <button type="button" @click="openErrorModal = false" class="absolute left-4 top-4 text-slate-400 hover:text-white transition w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>

                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-rose-500/20 border-2 border-rose-500/50 flex items-center justify-center mx-auto text-rose-400 text-3xl sm:text-4xl shadow-inner">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-rose-400">
                            ئاگاداری / ئەم ژمارەیە کارپێنەکراوە!
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">
                            تکایە بە وردی زانیارییەکانی خوارەوە بخوێنەرەوە
                        </p>
                    </div>

                    <div class="bg-slate-950/90 border border-slate-800 rounded-2xl p-4 text-right text-xs sm:text-sm text-slate-200 leading-relaxed font-semibold shadow-inner">
                        <div class="flex items-start">
                            <i class="fa-solid fa-circle-info text-rose-400 text-base ml-2.5 mt-0.5 shrink-0"></i>
                            <div>
                                {{ session('duplicate_error') ?? session('error') }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="button" 
                                @click="openErrorModal = false" 
                                class="w-full py-3 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-lg shadow-rose-600/30 transition flex items-center justify-center space-x-2 space-x-reverse">
                            <i class="fa-solid fa-xmark text-base"></i>
                            <span>داخستن (لابردنی پەیامەکە)</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-4 py-3 rounded-xl shadow-lg flex items-center justify-between mb-4 text-xs sm:text-sm">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-xmark text-lg sm:text-xl ml-3"></i>
                    <span>{{ session('duplicate_error') ?? session('error') }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 max-w-[1680px] w-full mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6 safe-pb md:pb-8">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation Bar (PWA App Bar) -->
    @auth
        <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-t border-slate-200 dark:border-slate-800 shadow-[0_-4px_20px_rgba(0,0,0,0.15)] safe-bottom-bar">
            <div class="grid grid-cols-5 items-center text-center py-1">
                <!-- 1. داشبۆرد -->
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-1.5 text-[10px] font-bold transition {{ request()->routeIs('dashboard') ? 'text-sky-600 dark:text-sky-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie text-base mb-0.5"></i>
                    <span>داشبۆرد</span>
                </a>

                <!-- 2. مامەڵەکان -->
                <a href="{{ route('transactions.index') }}" class="flex flex-col items-center justify-center py-1.5 text-[10px] font-bold transition {{ request()->routeIs('transactions.index*') ? 'text-sky-600 dark:text-sky-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="fa-solid fa-list-check text-base mb-0.5"></i>
                    <span>مامەڵەکان</span>
                </a>

                <!-- 3. ناوەند: مامەڵەی نوێ یان سکانەر (Raised Center Action) -->
                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('transactions.create'))
                    <a href="{{ route('transactions.create') }}" class="flex flex-col items-center justify-center -mt-4 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/40 group-active:scale-95 transition">
                            <i class="fa-solid fa-plus text-xl"></i>
                        </div>
                        <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 mt-1">تۆماری نوێ</span>
                    </a>
                @else
                    <button type="button" @click="showMobileBarcodeModal = true" class="flex flex-col items-center justify-center -mt-4 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-sky-500/40 group-active:scale-95 transition">
                            <i class="fa-solid fa-barcode text-xl"></i>
                        </div>
                        <span class="text-[9px] font-black text-sky-600 dark:text-sky-400 mt-1">سکان</span>
                    </button>
                @endif

                <!-- 4. نوسراوەکان یان بەشەکەت -->
                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('prints.official_index'))
                    <a href="{{ route('transactions.official_prints') }}" class="flex flex-col items-center justify-center py-1.5 text-[10px] font-bold transition {{ request()->routeIs('transactions.official_prints*') ? 'text-amber-500 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <i class="fa-solid fa-print text-base mb-0.5"></i>
                        <span>نوسراوەکان</span>
                    </a>
                @elseif(auth()->user()->hasPermission('reports.accounting_66'))
                    <a href="{{ route('reports.accounting_66') }}" class="flex flex-col items-center justify-center py-1.5 text-[10px] font-bold transition {{ request()->routeIs('reports.accounting_66*') ? 'text-amber-500 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <i class="fa-solid fa-file-invoice-dollar text-base mb-0.5"></i>
                        <span>محاسبة ٦٦</span>
                    </a>
                @else
                    <button type="button" @click="showMobileBarcodeModal = true" class="flex flex-col items-center justify-center py-1.5 text-[10px] font-bold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                        <i class="fa-solid fa-barcode text-base mb-0.5"></i>
                        <span>سکان</span>
                    </button>
                @endif

                <!-- 5. مێنۆ و زیاتر -->
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="flex flex-col items-center justify-center py-1.5 text-[10px] font-bold transition" :class="mobileMenuOpen ? 'text-sky-600 dark:text-sky-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                    <i class="fa-solid text-base mb-0.5" :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'"></i>
                    <span>مێنۆ</span>
                </button>
            </div>
        </nav>
    @endauth

    <!-- Footer (Desktop) -->
    <footer class="hidden md:block bg-slate-800/60 border-t border-slate-800 py-4 text-center text-xs text-slate-500">
        <p>سیستەمی یەکگرتووی بەڕێوەبردنی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری &copy; {{ date('Y') }} - دروستکراوە  له‌لايه‌ن فه‌رمانبه‌رى ئاتى  امجد صالح عبدالله</p>
    </footer>

    <!-- Service Worker Registration for PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then((registration) => {
                    console.log('[PWA] ServiceWorker registered with scope:', registration.scope);
                }).catch((error) => {
                    console.log('[PWA] ServiceWorker registration failed:', error);
                });
            });
        }
    </script>
    <!-- Smart Bi-Directional Arabic/Kurdish/English Keyboard Converter -->
    <script>
        window.LangConvert = {
            arToEnMap: {
                'ض': 'Q', 'ص': 'W', 'ث': 'E', 'ق': 'R', 'ف': 'T', 'غ': 'Y', 'ع': 'U', 'ه': 'I', 'خ': 'O', 'ح': 'P', 'ج': '[', 'د': ']',
                'ش': 'A', 'س': 'S', 'ي': 'D', 'ب': 'F', 'ل': 'G', 'ا': 'H', 'ت': 'J', 'ن': 'K', 'م': 'L', 'ك': ';', 'ط': "'",
                'ئ': 'Z', 'ء': 'X', 'ؤ': 'C', 'ر': 'V', 'لا': 'B', 'ى': 'N', 'ة': 'M', 'و': ',', 'ز': '.', 'ظ': '/', 'ذ': '`',
                'پ': 'P', 'چ': 'C', 'ژ': 'J', 'گ': 'G', 'ڤ': 'V', 'ۆ': 'O', 'ێ': 'E', 'ڕ': 'R', 'ڵ': 'L', 'ک': 'K', 'ە': 'E', 'ی': 'Y'
            },
            enToAr101Map: {
                'q': 'ض', 'w': 'ص', 'e': 'ث', 'r': 'ق', 't': 'ف', 'y': 'غ', 'u': 'ع', 'i': 'ه', 'o': 'خ', 'p': 'ح', '[': 'ج', ']': 'د',
                'a': 'ش', 's': 'س', 'd': 'ي', 'f': 'ب', 'g': 'ل', 'h': 'ا', 'j': 'ت', 'k': 'ن', 'l': 'م', ';': 'ك', "'": 'ط',
                'z': 'ئ', 'x': 'ء', 'c': 'ؤ', 'v': 'ر', 'b': 'لا', 'n': 'ى', 'm': 'ة', ',': 'و', '.': 'ز', '/': 'ظ', '`': 'ذ',
                'Q': 'ض', 'W': 'ص', 'E': 'ث', 'R': 'ق', 'T': 'ف', 'Y': 'غ', 'U': 'ع', 'I': 'ه', 'O': 'خ', 'P': 'ح',
                'A': 'ش', 'S': 'س', 'D': 'ي', 'F': 'ب', 'G': 'ل', 'H': 'ا', 'J': 'ت', 'K': 'ن', 'L': 'م',
                'Z': 'ئ', 'X': 'ء', 'C': 'ؤ', 'V': 'ر', 'B': 'لا', 'N': 'ى', 'M': 'ة'
            },
            enToKuPhoneticMap: {
                'a': 'ا', 'b': 'ب', 'c': 'چ', 'd': 'د', 'e': 'ە', 'f': 'ف', 'g': 'گ', 'h': 'ه', 'i': 'ی', 'j': 'ژ',
                'k': 'ک', 'l': 'ل', 'm': 'م', 'n': 'ن', 'o': 'ۆ', 'p': 'پ', 'q': 'ق', 'r': 'ر', 's': 'س', 't': 'ت',
                'u': 'و', 'v': 'ڤ', 'w': 'و', 'x': 'خ', 'y': 'ی', 'z': 'ز',
                'A': 'ا', 'B': 'ب', 'C': 'چ', 'D': 'د', 'E': 'ێ', 'F': 'ف', 'G': 'گ', 'H': 'ح', 'I': 'ی', 'J': 'ژ',
                'K': 'ک', 'L': 'ڵ', 'M': 'م', 'N': 'ن', 'O': 'ۆ', 'P': 'پ', 'Q': 'ق', 'R': 'ڕ', 'S': 'ص', 'T': 'ط',
                'U': 'ع', 'V': 'ڤ', 'W': 'وو', 'X': 'خ', 'Y': 'ی', 'Z': 'ز'
            },
            nameDict: {
                'محمد': 'MOHAMMED', 'محەمەد': 'MOHAMMED', 'محەممەد': 'MOHAMMED',
                'احمد': 'AHMED', 'ئەحمەد': 'AHMED',
                'علی': 'ALI', 'عەلی': 'ALI',
                'حسن': 'HASAN', 'حەسەن': 'HASAN',
                'حسین': 'HUSSEIN', 'حوسێن': 'HUSSEIN',
                'عثمان': 'OSMAN', 'عوسمان': 'OSMAN',
                'عمر': 'OMAR', 'عومەر': 'OMAR',
                'بکر': 'BAKR', 'بەکر': 'BAKR',
                'صالح': 'SALIH', 'ساڵح': 'SALIH',
                'قادر': 'QADIR', 'فتاح': 'FATTAH', 'فەتاح': 'FATTAH',
                'رحمان': 'RAHMAN', 'رەحمان': 'RAHMAN',
                'کمال': 'KAMAL', 'کەمال': 'KAMAL',
                'جمال': 'JAMAL', 'جەمال': 'JAMAL',
                'خالد': 'KHALID', 'خالید': 'KHALID',
                'عبدالله': 'ABDULLAH', 'عەبدوڵڵا': 'ABDULLAH', 'عبد الله': 'ABDULLAH',
                'عبدالرحمن': 'ABDULRAHMAN', 'عەبدولڕەحمان': 'ABDULRAHMAN',
                'عبدالکریم': 'ABDULKARIM', 'عەبدولکەریم': 'ABDULKARIM',
                'ابراهیم': 'IBRAHIM', 'ئیبراهیم': 'IBRAHIM',
                'یوسف': 'YOUSIF', 'ئامانج': 'AMANJ', 'دانا': 'DANA',
                'هێمن': 'HEMIN', 'ئاراس': 'ARAS', 'سەردار': 'SARDAR',
                'ڕێبوار': 'REBWAR', 'دیار': 'DIYAR', 'بەرهەم': 'BARHAM',
                'شاخەوان': 'SHAKHAWAN', 'زانا': 'ZANA', 'گۆران': 'GORAN',
                'هۆشیار': 'HOSHYAR', 'ئاسۆ': 'ASO', 'شوان': 'SHWAN',
                'کاروان': 'KARWAN', 'هێرش': 'HERISH', 'هیوا': 'HIWA',
                'بەختیار': 'BAKHTIYAR', 'شەماڵ': 'SHAMAL', 'کامەران': 'KAMARAN',
                'ڕەوان': 'RAWAN', 'نەبەز': 'NABAZ', 'نەوزاد': 'NAWZAD',
                'هیدایەت': 'HIDAYAT', 'فەرهاد': 'FARHAD', 'سەباح': 'SABAH',
                'تەها': 'TAHA', 'جەعفەر': 'JAFAR', 'سەعید': 'SAEED',
                'هاوڕێ': 'HAWRE', 'شیروان': 'SHIRWAN', 'سەروەت': 'SARWAT',
                'مەحمود': 'MAHMOOD', 'محمود': 'MAHMOOD', 'ئازاد': 'AZAD',
                'رزگار': 'RIZGAR', 'ڕزگار': 'RIZGAR', 'وریا': 'WRYA',
                'سۆران': 'SORAN', 'پشتیوان': 'PISHTIWAN', 'بژار': 'BZHAR'
            },
            toEnglish(str) {
                if (!str) return '';
                return str.split('').map(ch => this.arToEnMap[ch] || ch).join('').toUpperCase();
            },
            toArabic(str, mode = 'ar101') {
                if (!str) return '';
                const map = (mode === 'kurdish') ? this.enToKuPhoneticMap : this.enToAr101Map;
                return str.split('').map(ch => map[ch] || ch).join('');
            },
            transliterate(str) {
                if (!str) return '';
                const charMap = {
                    'ا': 'A', 'آ': 'A', 'أ': 'A', 'إ': 'I', 'ئ': 'A', 'ء': '',
                    'ب': 'B', 'پ': 'P', 'ت': 'T', 'ث': 'TH', 'ج': 'J', 'چ': 'CH',
                    'ح': 'H', 'خ': 'KH', 'د': 'D', 'ذ': 'DH', 'ر': 'R', 'ڕ': 'R',
                    'ز': 'Z', 'ژ': 'ZH', 'س': 'S', 'ش': 'SH', 'ص': 'S', 'ض': 'D',
                    'ط': 'T', 'ظ': 'Z', 'ع': 'A', 'غ': 'GH', 'ف': 'F', 'ڤ': 'V',
                    'ق': 'Q', 'ک': 'K', 'ك': 'K', 'گ': 'G', 'ل': 'L', 'ڵ': 'L',
                    'م': 'M', 'ن': 'N', 'و': 'W', 'ۆ': 'O', 'ه': 'H', 'ە': 'A',
                    'ی': 'Y', 'ي': 'Y', 'ێ': 'E', 'ة': 'A'
                };
                return str.trim().split(/\s+/).map(w => {
                    if (this.nameDict[w]) return this.nameDict[w];
                    return w.split('').map(c => charMap[c] || c).join('');
                }).join(' ').toUpperCase();
            }
        };
    </script>
</body>
</html>
