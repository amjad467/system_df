@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: 'roles',
    checkAllForRole(roleKey, checked) {
        document.querySelectorAll('input[data-role=\'' + roleKey + '\']').forEach(el => el.checked = checked);
    },
    checkAllForUser(checked) {
        document.querySelectorAll('input.user-perm-checkbox').forEach(el => el.checked = checked);
    }
}">

    <!-- سەرپەڕە و ناسنامە (Header) -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-700/80 rounded-2xl p-5 sm:p-6 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-sky-500 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25">
                    <i class="fa-solid fa-user-shield text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                        خشتەی پێرمشن و دەسەڵاتی کارمەندان
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-300">
                        ڕێکخستنی دەسەڵات، کار و فەرمانە ڕێگەپێدراوەکان بەپێی چێک بۆکس (تەنها بۆ ئەدمین).
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2 w-full md:w-auto">
            <a href="{{ route('users.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1.5">
                <i class="fa-solid fa-users"></i>
                <span>فەرمانبەران</span>
            </a>
            
            <form action="{{ route('settings.permissions.reset') }}" method="POST" onsubmit="return confirm('ئایا دڵنیایت لە گەڕاندنەوەی سەرجەم پێرمشنەکان بۆ باری بنەڕەتی فەرمی؟');" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 text-xs font-bold rounded-xl transition flex items-center gap-1.5" title="گەڕاندنەوە بۆ دەسەڵاتە فەرمییە بنەڕەتییەکان">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>باری بنەڕەتی</span>
                </button>
            </form>
        </div>
    </div>

    <!-- تابەکانی هەڵبژاردن (Tabs: بەشەکان / فەرمانبەری دیاریکراو) -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
        <button type="button" @click="activeTab = 'roles'" :class="activeTab === 'roles' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-table-cells"></i>
            <span>خشتەی دەسەڵاتی بەشەکان (Role Matrix)</span>
        </button>
        <button type="button" @click="activeTab = 'user'" :class="activeTab === 'user' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-user-gear"></i>
            <span>پێرمشنی تایبەتی فەرمانبەر (Custom User)</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- بەشی ١: خشتەی چێک بۆکسی بەشەکان (Role Permissions Checkbox Matrix) -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'roles'" class="space-y-4">
        <form action="{{ route('settings.permissions.roles') }}" method="POST">
            @csrf

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden backdrop-blur-sm">
                <!-- Info Header -->
                <div class="p-4 bg-slate-950/70 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="text-slate-300">
                        <i class="fa-solid fa-circle-info text-sky-400 ml-1"></i>
                        هەر خانەیەک چێک بکەیت، ئەو دەسەڵاتە دەدرێت بە سەرجەم کارمەندانی ئەو بەشە.
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-600/25 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>پاشەکەوتکردنی خشتەی دەسەڵاتەکان</span>
                    </button>
                </div>

                <div class="overflow-x-auto touch-scroll">
                    <table class="w-full text-right text-xs text-slate-300 border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-slate-950 text-slate-400 text-[11px] font-black uppercase tracking-wider border-b border-slate-800">
                                <th class="py-3 px-4 w-72 sticky right-0 bg-slate-950 z-20">کار و فەرمانەکان (Permissions)</th>
                                @foreach($roles as $roleKey => $roleLabel)
                                    <th class="py-3 px-3 text-center border-r border-slate-800/60 min-w-[110px]">
                                        <div class="font-bold text-white text-[11px]">{{ $roleLabel }}</div>
                                        <div class="font-mono text-[9px] text-sky-400 mt-0.5">{{ $roleKey }}</div>
                                        <div class="flex items-center justify-center gap-1 mt-1.5">
                                            <button type="button" @click="checkAllForRole('{{ $roleKey }}', true)" class="text-[9px] text-emerald-400 hover:underline">هەموو</button>
                                            <span class="text-slate-600">|</span>
                                            <button type="button" @click="checkAllForRole('{{ $roleKey }}', false)" class="text-[9px] text-rose-400 hover:underline">لابردن</button>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach($categories as $catKey => $category)
                                <!-- Category Section Header -->
                                <tr class="bg-slate-950/80 border-t-2 border-slate-800">
                                    <td colspan="{{ count($roles) + 1 }}" class="py-2 px-4 text-xs font-black text-sky-300 uppercase tracking-wide">
                                        <i class="{{ $category['icon'] }} ml-1.5"></i>
                                        {{ $category['label'] }}
                                    </td>
                                </tr>

                                @foreach($category['permissions'] as $permKey => $permLabel)
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <!-- Permission Name -->
                                        <td class="py-2.5 px-4 font-semibold text-slate-200 sticky right-0 bg-slate-900/95 z-10 border-b border-slate-800/40">
                                            <div class="text-xs text-white">{{ $permLabel }}</div>
                                            <div class="font-mono text-[10px] text-slate-500">{{ $permKey }}</div>
                                        </td>

                                        <!-- Role Checkboxes -->
                                        @foreach($roles as $roleKey => $roleLabel)
                                            @php
                                                $isChecked = isset($matrix[$roleKey][$permKey]) && $matrix[$roleKey][$permKey];
                                            @endphp
                                            <td class="py-2 px-3 text-center border-r border-slate-800/40">
                                                <label class="inline-flex items-center justify-center cursor-pointer p-1 rounded hover:bg-slate-800 transition">
                                                    <input type="checkbox" 
                                                           name="permissions[{{ $roleKey }}][{{ $permKey }}]" 
                                                           value="1" 
                                                           data-role="{{ $roleKey }}"
                                                           {{ $isChecked ? 'checked' : '' }}
                                                           class="w-4 h-4 rounded border-slate-700 text-sky-600 focus:ring-sky-500 bg-slate-950 cursor-pointer">
                                                </label>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer Action Bar -->
                <div class="p-4 bg-slate-950/80 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        <i class="fa-solid fa-lock text-amber-400 ml-1"></i> ئەدمین (Admin) بە تەواوەتی هەموو دەسەڵاتەکانی هەیە بەبێ سنووردارکردن.
                    </span>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i>
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
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/40 rounded-xl text-xs font-bold flex items-center gap-1">
                            <i class="fa-solid fa-sliders text-amber-400"></i>
                            پێرمشنی تایبەت (Custom)
                        </span>
                        <form action="{{ route('settings.permissions.user', $selectedUser->id) }}" method="POST" onsubmit="return confirm('ئایا دڵنیایت لە لابردنی پێرمشنە تایبەتەکان و گەڕاندنەوەی بۆ باری بنەڕەتی بەشەکەی؟');">
                            @csrf
                            <input type="hidden" name="reset_to_role" value="1">
                            <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold border border-slate-700 transition">
                                <i class="fa-solid fa-rotate-left ml-1"></i> گەڕاندنەوە بۆ دەسەڵاتی بەشەکەی
                            </button>
                        </form>
                    @else
                        <span class="px-3 py-1 bg-sky-500/20 text-sky-300 border border-sky-500/40 rounded-xl text-xs font-bold flex items-center gap-1">
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
                            <button type="button" @click="checkAllForUser(true)" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-emerald-400 rounded-lg text-xs font-bold">هەموو چێک بکە</button>
                            <button type="button" @click="checkAllForUser(false)" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-rose-400 rounded-lg text-xs font-bold">هەموو لابدە</button>
                            <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow transition">
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
                                                   class="user-perm-checkbox w-4 h-4 rounded border-slate-700 text-sky-600 focus:ring-sky-500 bg-slate-950">
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end pt-3 border-t border-slate-800">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2">
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

