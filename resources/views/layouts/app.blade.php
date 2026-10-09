<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستەمی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری - Customs Tourist Booklet System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
        body { font-family: 'Vazirmatn', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ showMyPasswordModal: false, mobileMenuOpen: false }" class="bg-slate-900 text-slate-100 min-h-screen flex flex-col font-sans antialiased selection:bg-sky-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="bg-slate-800/90 backdrop-blur border-b border-slate-700/80 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3 space-x-reverse">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-xl bg-slate-900 text-slate-300 hover:text-white border border-slate-700">
                        <i class="fa-solid" :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'"></i>
                    </button>
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-sky-500/20 shrink-0">
                        <i class="fa-solid fa-passport text-white text-lg sm:text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-sm sm:text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-sky-400 to-indigo-300 leading-tight">
                            بەڕێوەبەرایەتی گومرگی سلێمانی
                        </h1>
                        <p class="text-[10px] sm:text-xs text-slate-400">سیستەمی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری</p>
                    </div>
                </div>

                <!-- Fast Barcode Scanner Form (Desktop) -->
                <form action="{{ route('transactions.scan') }}" method="POST" class="hidden md:flex items-center relative">
                    @csrf
                    <input type="text" name="barcode" placeholder="سکانی بارکۆد بکە (YYMMDDXXXX)..." 
                           class="w-56 lg:w-64 bg-slate-900/90 text-sm text-slate-200 placeholder-slate-500 rounded-xl border border-slate-700 pr-10 pl-4 py-2 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition">
                    <button type="submit" class="absolute right-3 text-slate-400 hover:text-sky-400">
                        <i class="fa-solid fa-barcode"></i>
                    </button>
                </form>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden lg:flex items-center space-x-1 space-x-reverse">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie ml-1.5"></i> داشبۆرد
                    </a>
                    <a href="{{ route('transactions.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('transactions.index') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <i class="fa-solid fa-list-check ml-1.5"></i> مامەڵەکان
                    </a>
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet'))
                        <a href="{{ route('transactions.official_prints') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('transactions.official_prints') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-amber-400 hover:bg-slate-700 hover:text-white' }}">
                            <i class="fa-solid fa-print ml-1.5"></i> چاپی نوسراوەکان
                        </a>
                    @endif
                    <a href="{{ route('transactions.expired_booklets') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('transactions.expired_booklets') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'text-rose-400 hover:bg-slate-700 hover:text-white' }}">
                        <i class="fa-solid fa-calendar-xmark ml-1.5"></i> دەفتەرە بەسەرچووەکان
                    </a>
                    <a href="{{ route('reports.accounting_66') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('reports.accounting_66*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-amber-300 hover:bg-slate-700 hover:text-white' }}">
                        <i class="fa-solid fa-file-invoice-dollar ml-1.5"></i> محاسبة ٦٦
                    </a>
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'data_entry'))
                        <a href="{{ route('transactions.create') }}" class="px-3 py-2 rounded-lg text-sm font-medium bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition">
                            <i class="fa-solid fa-plus-circle ml-1.5"></i> مامەڵەی نوێ
                        </a>
                    @endif

                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('users.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium border border-indigo-500/40 text-indigo-300 hover:bg-indigo-500/10 transition {{ request()->routeIs('users.index') ? 'bg-indigo-600 text-white' : '' }}">
                            <i class="fa-solid fa-users-gear ml-1.5"></i> فەرمانبەران
                        </a>
                        <a href="{{ route('settings.pricing') }}" class="px-3 py-2 rounded-lg text-sm font-medium border border-amber-500/40 text-amber-300 hover:bg-amber-500/10 transition {{ request()->routeIs('settings.pricing') ? 'bg-amber-600 text-white' : '' }}">
                            <i class="fa-solid fa-sliders ml-1.5"></i> نرخەکان
                        </a>
                        <a href="{{ route('transactions.trash') }}" class="px-3 py-2 rounded-lg text-sm font-medium border border-rose-500/40 text-rose-300 hover:bg-rose-500/10 transition {{ request()->routeIs('transactions.trash') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : '' }}">
                            <i class="fa-solid fa-trash-can ml-1.5"></i> سڕاوەکان
                        </a>
                    @endif

                    <a href="{{ route('logs.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('logs.index') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <i class="fa-solid fa-clock-rotate-left ml-1.5"></i> لۆگ
                    </a>

                    <!-- User Profile & Logout -->
                    @auth
                        @php
                            $unreadNotifications = \App\Services\NotificationService::getUnreadForCurrentUser();
                        @endphp

                        <!-- Notification Bell -->
                        <div class="relative mr-2" x-data="{ openNotifs: false }">
                            <button @click="openNotifs = !openNotifs" class="relative p-2 rounded-lg bg-slate-800 text-slate-300 hover:text-white transition border border-slate-700">
                                <i class="fa-solid fa-bell text-sm text-sky-400"></i>
                                @if($unreadNotifications->count() > 0)
                                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-rose-600 text-white rounded-full text-[9px] font-extrabold flex items-center justify-center animate-pulse">
                                        {{ $unreadNotifications->count() }}
                                    </span>
                                @endif
                            </button>

                            <div x-show="openNotifs" @click.away="openNotifs = false" x-cloak class="absolute left-0 mt-2 w-80 bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl p-3 z-50 space-y-2">
                                <h4 class="text-xs font-bold text-sky-400 border-b border-slate-800 pb-2 flex items-center justify-between">
                                    <span><i class="fa-solid fa-bell ml-1"></i> ئاگادارییەکانی دەسەڵاتی تۆ</span>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $unreadNotifications->count() }} نوێ</span>
                                </h4>
                                <div class="max-h-60 overflow-y-auto space-y-1.5">
                                    @forelse($unreadNotifications as $notif)
                                        <a href="{{ route('notifications.read', $notif->id) }}" class="block p-2 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 transition">
                                            <span class="text-xs font-bold text-slate-100 block">{{ $notif->title }}</span>
                                            <span class="text-[11px] text-slate-400 block mt-0.5">{{ $notif->message }}</span>
                                            <span class="text-[9px] text-sky-400 font-mono block mt-1">{{ $notif->created_at->diffForHumans() }}</span>
                                        </a>
                                    @empty
                                        <p class="text-xs text-slate-500 text-center py-3">هیچ ئاگادارییەکی نوێت نییە.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="mr-2 border-r border-slate-700 pr-3 flex items-center space-x-2 space-x-reverse">
                            <div class="text-right hidden sm:block">
                                <span class="text-xs font-bold text-slate-100 block">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-sky-400 font-semibold block">{{ auth()->user()->role_name_kurdish }}</span>
                            </div>
                            <button type="button" @click="showMyPasswordModal = true" title="گۆڕینی پاسوۆردی کەسی" class="w-8 h-8 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 flex items-center justify-center transition">
                                <i class="fa-solid fa-key text-xs"></i>
                            </button>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" title="چوونەدەرەوە" class="w-8 h-8 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 flex items-center justify-center transition">
                                    <i class="fa-solid fa-power-off text-xs"></i>
                                </button>
                            </form>
                        </div>
                    @endauth

                </nav>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div x-show="mobileMenuOpen" x-cloak class="lg:hidden py-4 border-t border-slate-700 space-y-3">
                <!-- Fast Scanner Barcode Form for Mobile -->
                <form action="{{ route('transactions.scan') }}" method="POST" class="flex items-center relative mb-3">
                    @csrf
                    <input type="text" name="barcode" placeholder="سکانی بارکۆد بکە..." 
                           class="w-full bg-slate-900 text-xs text-slate-200 placeholder-slate-500 rounded-xl border border-slate-700 pr-9 pl-3 py-2.5 focus:outline-none focus:border-sky-500">
                    <button type="submit" class="absolute right-3 text-slate-400">
                        <i class="fa-solid fa-barcode"></i>
                    </button>
                </form>

                <div class="grid grid-cols-2 gap-2 text-xs font-bold">
                    <a href="{{ route('dashboard') }}" class="p-2.5 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center text-slate-200 hover:bg-sky-600 hover:text-white">
                        <i class="fa-solid fa-chart-pie ml-1.5 text-sky-400"></i> داشبۆرد
                    </a>
                    <a href="{{ route('transactions.index') }}" class="p-2.5 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center text-slate-200 hover:bg-sky-600 hover:text-white">
                        <i class="fa-solid fa-list-check ml-1.5 text-emerald-400"></i> مامەڵەکان
                    </a>
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'booklet'))
                        <a href="{{ route('transactions.official_prints') }}" class="p-2.5 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center text-amber-300 hover:bg-amber-600 hover:text-white">
                            <i class="fa-solid fa-print ml-1.5 text-amber-400"></i> چاپی نوسراوەکان
                        </a>
                    @endif
                    <a href="{{ route('transactions.expired_booklets') }}" class="p-2.5 rounded-xl bg-slate-900 border border-rose-500/40 text-rose-300 hover:bg-rose-600 hover:text-white flex items-center justify-center">
                        <i class="fa-solid fa-calendar-xmark ml-1.5 text-rose-400"></i> بەسەرچووەکان
                    </a>
                    <a href="{{ route('reports.accounting_66') }}" class="p-2.5 rounded-xl bg-slate-900 border border-amber-500/40 text-amber-300 hover:bg-amber-600 hover:text-white flex items-center justify-center">
                        <i class="fa-solid fa-file-invoice-dollar ml-1.5 text-amber-400"></i> محاسبة ٦٦
                    </a>
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'data_entry'))
                        <a href="{{ route('transactions.create') }}" class="p-2.5 rounded-xl bg-emerald-600 text-white flex items-center justify-center">
                            <i class="fa-solid fa-plus-circle ml-1.5"></i> مامەڵەی نوێ
                        </a>
                    @endif
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('users.index') }}" class="p-2.5 rounded-xl bg-slate-900 border border-indigo-500/40 text-indigo-300 flex items-center justify-center">
                            <i class="fa-solid fa-users-gear ml-1.5"></i> فەرمانبەران
                        </a>
                        <a href="{{ route('settings.pricing') }}" class="p-2.5 rounded-xl bg-slate-900 border border-amber-500/40 text-amber-300 flex items-center justify-center">
                            <i class="fa-solid fa-sliders ml-1.5"></i> نرخەکان
                        </a>
                        <a href="{{ route('transactions.trash') }}" class="p-2.5 rounded-xl bg-slate-900 border border-rose-500/40 text-rose-300 flex items-center justify-center">
                            <i class="fa-solid fa-trash-can ml-1.5"></i> سڕاوەکان
                        </a>
                    @endif
                    <a href="{{ route('logs.index') }}" class="p-2.5 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center text-slate-300">
                        <i class="fa-solid fa-clock-rotate-left ml-1.5 text-slate-400"></i> لۆگ
                    </a>
                </div>

                @auth
                    <div class="pt-3 border-t border-slate-700/80 flex items-center justify-between text-xs">
                        <div class="text-right">
                            <span class="font-bold text-white block">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] text-sky-400 font-semibold block">{{ auth()->user()->role_name_kurdish }}</span>
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <button type="button" @click="showMyPasswordModal = true" class="px-3 py-1.5 bg-amber-500/20 text-amber-300 rounded-lg font-bold text-xs flex items-center">
                                <i class="fa-solid fa-key ml-1"></i> گۆڕینی پاسوۆرد
                            </button>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-rose-600 text-white rounded-lg font-bold text-xs flex items-center">
                                    <i class="fa-solid fa-power-off ml-1"></i> دەربچۆ
                                </button>
                            </form>
                        </div>
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl shadow-lg flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-check text-xl ml-3"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="bg-amber-500/10 border border-amber-500/30 text-amber-300 px-4 py-3 rounded-xl shadow-lg flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <i class="fa-solid fa-triangle-exclamation text-xl ml-3"></i>
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
                    
                    <!-- Close Button X at top left -->
                    <button type="button" @click="openErrorModal = false" class="absolute left-4 top-4 text-slate-400 hover:text-white transition w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>

                    <!-- Warning Icon -->
                    <div class="w-20 h-20 rounded-full bg-rose-500/20 border-2 border-rose-500/50 flex items-center justify-center mx-auto text-rose-400 text-4xl shadow-inner">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <!-- Modal Title -->
                    <div>
                        <h3 class="text-xl font-black text-rose-400">
                            ئاگاداری / ئەم ژمارەیە کارپێنەکراوە!
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">
                            تکایە بە وردی زانیارییەکانی خوارەوە بخوێنەرەوە
                        </p>
                    </div>

                    <!-- Error Message Content Box -->
                    <div class="bg-slate-950/90 border border-slate-800 rounded-2xl p-4 text-right text-sm text-slate-200 leading-relaxed font-semibold shadow-inner">
                        <div class="flex items-start">
                            <i class="fa-solid fa-circle-info text-rose-400 text-base ml-2.5 mt-0.5 shrink-0"></i>
                            <div>
                                {{ session('duplicate_error') ?? session('error') }}
                            </div>
                        </div>
                    </div>

                    <!-- Close Action Button (داخستن) -->
                    <div class="pt-2">
                        <button type="button" 
                                @click="openErrorModal = false" 
                                class="w-full py-3.5 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-rose-600/30 transition flex items-center justify-center space-x-2 space-x-reverse">
                            <i class="fa-solid fa-xmark text-lg"></i>
                            <span>داخستن (لابردنی پەیامەکە)</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Also show standard banner for fallback -->
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-4 py-3 rounded-xl shadow-lg flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-xmark text-xl ml-3"></i>
                    <span>{{ session('duplicate_error') ?? session('error') }}</span>
                </div>
            </div>
        @endif
    </div>


    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-800/60 border-t border-slate-800 py-4 text-center text-xs text-slate-500">
        <p>سیستەمی یەکگرتووی بەڕێوەبردنی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری &copy; {{ date('Y') }} - دروستکراوە بە PHP / Laravel & Tailwind CSS</p>
    </footer>

</body>
</html>
