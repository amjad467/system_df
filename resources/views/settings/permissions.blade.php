@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: 'roles',
    selectedCategory: 'all',
    searchQuery: '',
    collapsedCategories: {},

    toggleCategory(catKey) {
        this.collapsedCategories[catKey] = !this.collapsedCategories[catKey];
    },

    checkAllForRole(roleKey, checked) {
        document.querySelectorAll('input[data-role=\'' + roleKey + '\']').forEach(el => {
            // Only toggle visible if category/search is active, or toggle all
            el.checked = checked;
        });
    },

    checkAllForCategory(catKey, checked) {
        document.querySelectorAll('input[data-cat=\'' + catKey + '\']').forEach(el => el.checked = checked);
    },

    checkAllForRow(permKey, checked) {
        document.querySelectorAll('input[data-perm=\'' + permKey + '\']').forEach(el => el.checked = checked);
    },

    checkAllForUser(checked) {
        document.querySelectorAll('input.user-perm-checkbox').forEach(el => el.checked = checked);
    },

    matchesSearch(label, key) {
        if (!this.searchQuery || this.searchQuery.trim() === '') return true;
        const q = this.searchQuery.toLowerCase().trim();
        return (label && label.toLowerCase().includes(q)) || (key && key.toLowerCase().includes(q));
    }
}">

    <!-- ========================================================================= -->
    <!-- سەرپەڕە و ناسنامە (Header) -->
    <!-- ========================================================================= -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-700/80 rounded-2xl p-5 sm:p-6 shadow-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 via-sky-500 to-emerald-400 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25">
                    <i class="fa-solid fa-shield-halved text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                        بەڕێوەبردنی دەسەڵات و پێرمشنەکان
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-300">
                        ڕێکخستنی دەسەڵات، کار و فەرمانە ڕێگەپێدراوەکان بۆ بەشەکان و کارمەندان (تەنها بۆ ئەدمین).
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2 w-full md:w-auto">
            <a href="{{ route('users.index') }}" class="px-3.5 py-2 bg-slate-800/90 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-users text-sky-400"></i>
                <span>فەرمانبەران</span>
            </a>
            
            <form action="{{ route('settings.permissions.reset') }}" method="POST" onsubmit="return confirm('ئایا دڵنیایت لە گەڕاندنەوەی سەرجەم پێرمشنەکان بۆ باری بنەڕەتی فەرمی؟');" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/40 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm cursor-pointer" title="گەڕاندنەوە بۆ دەسەڵاتە فەرمییە بنەڕەتییەکان">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>باری بنەڕەتی</span>
                </button>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- تابە سەرەکییەکان (Main Navigation Tabs) -->
    <!-- ========================================================================= -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-3 flex-wrap gap-3">
        <div class="flex items-center gap-2">
            <button type="button" 
                    @click="activeTab = 'roles'" 
                    :class="activeTab === 'roles' ? 'bg-gradient-to-r from-indigo-600 to-sky-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white hover:bg-slate-800'" 
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-table-cells"></i>
                <span>خشتەی دەسەڵاتی بەشەکان (Role Matrix)</span>
            </button>
            <button type="button" 
                    @click="activeTab = 'user'" 
                    :class="activeTab === 'user' ? 'bg-gradient-to-r from-indigo-600 to-sky-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white hover:bg-slate-800'" 
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-user-gear"></i>
                <span>پێرمشنی تایبەتی فەرمانبەر (Custom User)</span>
            </button>
        </div>

        <!-- گەڕانی خێرا لە نێو دەسەڵاتەکاندا (Search in Permissions) -->
        <div class="relative w-full sm:w-72">
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="گەڕان لە نێو دەسەڵاتەکان (ناو یان کۆد)..." 
                   class="w-full bg-slate-900 border border-slate-700/80 rounded-xl pr-9 pl-3 py-2 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-xs text-slate-400"></i>
            <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute left-3 top-2.5 text-xs text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- بەشی ١: خشتەی چێک بۆکسی بەشەکان (Role Permissions Checkbox Matrix) -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'roles'" class="space-y-5">
        <form action="{{ route('settings.permissions.roles') }}" method="POST" id="rolePermissionsForm">
            @csrf

            <!-- هێڵی دەستنیشانکردنی بەشەکان (Category Navigation Pills) -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-2.5 shadow-lg flex items-center gap-2 overflow-x-auto touch-scroll">
                <button type="button" 
                        @click="selectedCategory = 'all'" 
                        :class="selectedCategory === 'all' ? 'bg-sky-600 text-white shadow' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>هەموو بەشەکان</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-950/60 font-mono">{{ count(\App\Services\PermissionService::getAllPermissions()) }}</span>
                </button>

                @foreach($categories as $catKey => $category)
                    <button type="button" 
                            @click="selectedCategory = '{{ $catKey }}'" 
                            :class="selectedCategory === '{{ $catKey }}' ? 'bg-sky-600 text-white shadow' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                        <i class="{{ $category['icon'] }}"></i>
                        <span>{{ explode('(', $category['label'])[0] }}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-950/60 font-mono">{{ count($category['permissions']) }}</span>
                    </button>
                @endforeach
            </div>

            <!-- سەرجەم بەشەکان و خشتەکان (Category Cards Stack) -->
            <div class="space-y-6 mt-4">
                @foreach($categories as $catKey => $category)
                    <div x-show="(selectedCategory === 'all' || selectedCategory === '{{ $catKey }}')" 
                         x-transition
                         class="bg-slate-900/95 border border-slate-800 rounded-2xl shadow-xl overflow-hidden backdrop-blur-sm">
                        
                        <!-- Category Header Bar -->
                        <div class="p-3.5 sm:p-4 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center text-sm shadow-inner">
                                    <i class="{{ $category['icon'] }}"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                                        <span>{{ $category['label'] }}</span>
                                    </h3>
                                    <span class="text-[11px] text-slate-400 font-mono">کۆی گشتی: {{ count($category['permissions']) }} دەسەڵاتی جیاواز</span>
                                </div>
                            </div>

                            <!-- Category Quick Bulk Actions -->
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="checkAllForCategory('{{ $catKey }}', true)" 
                                        class="px-2.5 py-1 bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold rounded-lg transition flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-check-double text-[10px]"></i>
                                    <span>هەموو چێک بکە</span>
                                </button>
                                <button type="button" 
                                        @click="checkAllForCategory('{{ $catKey }}', false)" 
                                        class="px-2.5 py-1 bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 text-[11px] font-bold rounded-lg transition flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-ban text-[10px]"></i>
                                    <span>هەموو لابدە</span>
                                </button>
                                <button type="button" 
                                        @click="toggleCategory('{{ $catKey }}')" 
                                        class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs transition cursor-pointer" 
                                        title="کورتکردنەوە / کردنەوە">
                                    <i class="fa-solid" :class="collapsedCategories['{{ $catKey }}'] ? 'fa-chevron-down' : 'fa-chevron-up'"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Category Table -->
                        <div x-show="!collapsedCategories['{{ $catKey }}']" class="overflow-x-auto touch-scroll">
                            <table class="w-full text-right text-xs text-slate-300 border-collapse min-w-[950px]">
                                <thead>
                                    <tr class="bg-slate-950 text-slate-400 text-[11px] font-black uppercase tracking-wider border-b border-slate-800">
                                        <th class="py-3 px-4 w-72 sticky right-0 bg-slate-950 z-20 border-b border-slate-800 shadow-sm">
                                            <span>کار و فەرمانەکان (Permissions)</span>
                                        </th>
                                        @foreach($roles as $roleKey => $roleLabel)
                                            <th class="py-3 px-3 text-center border-r border-slate-800/60 min-w-[110px] bg-slate-950 border-b border-slate-800 shadow-sm">
                                                <div class="font-extrabold text-white text-[11px]">{{ $roleLabel }}</div>
                                                <div class="font-mono text-[9px] text-sky-400 mt-0.5">{{ $roleKey }}</div>
                                                <div class="flex items-center justify-center gap-1 mt-1.5">
                                                    <button type="button" 
                                                            @click="checkAllForRole('{{ $roleKey }}', true)" 
                                                            class="text-[9px] font-bold text-emerald-400 hover:text-emerald-300 hover:underline cursor-pointer">
                                                        هەموو
                                                    </button>
                                                    <span class="text-slate-600">|</span>
                                                    <button type="button" 
                                                            @click="checkAllForRole('{{ $roleKey }}', false)" 
                                                            class="text-[9px] font-bold text-rose-400 hover:text-rose-300 hover:underline cursor-pointer">
                                                        لابردن
                                                    </button>
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60">
                                    @foreach($category['permissions'] as $permKey => $permLabel)
                                        <tr x-show="matchesSearch('{{ $permLabel }}', '{{ $permKey }}')" 
                                            class="hover:bg-slate-800/40 transition">
                                            <!-- Permission Title & Row Bulk Toggle -->
                                            <td class="py-2.5 px-4 font-semibold text-slate-200 sticky right-0 bg-slate-900/95 z-10 border-b border-slate-800/40 shadow-sm">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div>
                                                        <div class="text-xs text-white font-bold">{{ $permLabel }}</div>
                                                        <div class="font-mono text-[10px] text-slate-500">{{ $permKey }}</div>
                                                    </div>
                                                    <!-- Row Quick Toggles -->
                                                    <div class="flex items-center gap-1 text-[9px] opacity-75 hover:opacity-100 transition">
                                                        <button type="button" 
                                                                @click="checkAllForRow('{{ $permKey }}', true)" 
                                                                class="px-1.5 py-0.5 bg-slate-800 hover:bg-emerald-600 hover:text-white text-slate-300 rounded transition cursor-pointer"
                                                                title="پێدانی ئەم دەسەڵاتە بە سەرجەم ڕۆڵەکان">
                                                            هەموو
                                                        </button>
                                                        <button type="button" 
                                                                @click="checkAllForRow('{{ $permKey }}', false)" 
                                                                class="px-1.5 py-0.5 bg-slate-800 hover:bg-rose-600 hover:text-white text-slate-300 rounded transition cursor-pointer"
                                                                title="لابردنی ئەم دەسەڵاتە لە سەرجەم ڕۆڵەکان">
                                                            لابردن
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Role Checkboxes -->
                                            @foreach($roles as $roleKey => $roleLabel)
                                                @php
                                                    $isChecked = isset($matrix[$roleKey][$permKey]) && $matrix[$roleKey][$permKey];
                                                @endphp
                                                <td class="py-2 px-3 text-center border-r border-slate-800/40">
                                                    <label class="inline-flex items-center justify-center cursor-pointer p-1 rounded-lg hover:bg-slate-800/80 transition">
                                                        <input type="checkbox" 
                                                               name="permissions[{{ $roleKey }}][{{ $permKey }}]" 
                                                               value="1" 
                                                               data-role="{{ $roleKey }}"
                                                               data-cat="{{ $catKey }}"
                                                               data-perm="{{ $permKey }}"
                                                               {{ $isChecked ? 'checked' : '' }}
                                                               class="w-4 h-4 rounded border-slate-700 text-sky-500 focus:ring-sky-500 focus:ring-offset-slate-900 bg-slate-950 cursor-pointer">
                                                    </label>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- ========================================================================= -->
            <!-- هێڵی چەسپاوی خوارەوە بۆ پاشەکەوتکردن (Floating Sticky Bottom Action Bar) -->
            <!-- ========================================================================= -->
            <div class="sticky bottom-4 z-40 bg-slate-900/95 border-2 border-indigo-500/40 rounded-2xl p-4 shadow-2xl backdrop-blur-md flex flex-wrap items-center justify-between gap-4 mt-6">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-lock-open text-sm"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-white block">
                            ئەدمین (Admin) بە تەواوەتی هەموو دەسەڵاتەکانی هەیە بەبێ سنووردارکردن.
                        </span>
                        <span class="text-[11px] text-slate-400">
                            گۆڕانکارییەکان بە چێک بۆکس دەستنیشان بکە و پاشەکەوتی بکە.
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-base"></i>
                        <span>پاشەکەوتکردنی دەسەڵاتەکان</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- بەشی ٢: دیاریکردنی پێرمشنی تایبەت بە هەر فەرمانبەرێک (Per-User Permissions) -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'user'" x-cloak class="space-y-4">
        <!-- User Selection Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="w-full md:w-auto">
                <label class="block text-xs font-bold text-slate-300 mb-1">فەرمانبەر هەڵبژێرە بۆ ڕێکخستنی دەسەڵاتی تایبەت:</label>
                <form action="{{ route('settings.permissions') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="user">
                    <select name="user_id" onchange="this.form.submit()" class="bg-slate-950 border border-slate-700 text-white text-xs sm:text-sm rounded-xl px-3 py-2 focus:border-sky-500 focus:outline-none min-w-[260px]">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $selectedUser && $selectedUser->id == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->user_login ?? $u->email }}) - {{ $u->role_name_kurdish }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            @if($selectedUser)
                <div class="flex items-center gap-2 flex-wrap">
                    @if($selectedUser->custom_permissions !== null)
                        <span class="px-3 py-1.5 bg-amber-500/20 text-amber-300 border border-amber-500/40 rounded-xl text-xs font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-sliders text-amber-400"></i>
                            پێرمشنی تایبەت (Custom)
                        </span>
                        <form action="{{ route('settings.permissions.user', $selectedUser->id) }}" method="POST" onsubmit="return confirm('ئایا دڵنیایت لە لابردنی پێرمشنە تایبەتەکان و گەڕاندنەوەی بۆ باری بنەڕەتی بەشەکەی؟');">
                            @csrf
                            <input type="hidden" name="reset_to_role" value="1">
                            <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold border border-slate-700 transition cursor-pointer">
                                <i class="fa-solid fa-rotate-left ml-1"></i> گەڕاندنەوە بۆ دەسەڵاتی بەشەکەی
                            </button>
                        </form>
                    @else
                        <span class="px-3 py-1.5 bg-sky-500/20 text-sky-300 border border-sky-500/40 rounded-xl text-xs font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-sky-400"></i>
                            دەسەڵاتی بنەڕەتی بەشەکەی وەرگرتووە ({{ $selectedUser->role_name_kurdish }})
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @if($selectedUser)
            <!-- User Permissions Form -->
            <form action="{{ route('settings.permissions.user', $selectedUser->id) }}" method="POST">
                @csrf

                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden p-5 space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-3">
                        <div>
                            <h3 class="text-sm font-black text-white flex items-center gap-2">
                                <i class="fa-solid fa-user-check text-sky-400"></i>
                                دیاریکردنی دەسەڵاتەکان بۆ: <span class="text-sky-300">{{ $selectedUser->name }}</span>
                            </h3>
                            <p class="text-xs text-slate-400">دەتوانیت هەر کارێک بۆ ئەم کارمەندە زیاد یان کەم بکەیت بە چێک بۆکس.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="checkAllForUser(true)" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-emerald-400 rounded-lg text-xs font-bold cursor-pointer">هەموو چێک بکە</button>
                            <button type="button" @click="checkAllForUser(false)" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-rose-400 rounded-lg text-xs font-bold cursor-pointer">هەموو لابدە</button>
                            <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow transition cursor-pointer">
                                <i class="fa-solid fa-floppy-disk ml-1"></i> پاشەکەوتکردن
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($categories as $catKey => $category)
                            <div class="bg-slate-950/70 border border-slate-800 rounded-2xl p-4 space-y-3">
                                <h4 class="text-xs font-black text-sky-300 border-b border-slate-800/80 pb-2 flex items-center gap-2">
                                    <i class="{{ $category['icon'] }}"></i>
                                    <span>{{ $category['label'] }}</span>
                                </h4>
                                <div class="space-y-2">
                                    @foreach($category['permissions'] as $permKey => $permLabel)
                                        @php
                                            $checked = isset($userEffectivePerms[$permKey]) && $userEffectivePerms[$permKey];
                                        @endphp
                                        <label class="flex items-center justify-between p-2 rounded-xl bg-slate-900/60 hover:bg-slate-800/60 border border-slate-800/50 cursor-pointer transition">
                                            <span class="text-xs text-slate-200">{{ $permLabel }}</span>
                                            <input type="checkbox" 
                                                   name="user_permissions[{{ $permKey }}]" 
                                                   value="1" 
                                                   {{ $checked ? 'checked' : '' }}
                                                   class="user-perm-checkbox w-4 h-4 rounded border-slate-700 text-sky-600 focus:ring-sky-500 bg-slate-950 cursor-pointer">
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end pt-3 border-t border-slate-800">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>پاشەکەوتکردنی دەسەڵاتەکانی ({{ $selectedUser->name }})</span>
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
