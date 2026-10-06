@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ showUserModal: false, showEditModal: false, editingUser: { id: '', name: '', user_login: '', email: '', role: '', traffic_directorate_id: '' } }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fa-solid fa-users-gear ml-2 text-indigo-400"></i> بەڕێوەبردنی ئەکاونتی فەرمانبەران و دەسەڵاتەکان
            </h2>
            <p class="text-xs text-slate-400">دروستکردن، دەستکاریکردن، گۆڕینی پاسوۆرد و دیاریکردنی جۆری کاری فەرمانبەران.</p>
        </div>
        <button @click="showUserModal = true" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center">
            <i class="fa-solid fa-user-plus ml-2"></i> دروستکردنی فەرمانبەری نوێ
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm text-slate-200">
                <thead class="bg-slate-900/90 text-slate-400 text-xs uppercase border-b border-slate-700">
                    <tr>
                        <th class="p-3.5">#</th>
                        <th class="p-3.5">ناوی فەرمانبەر</th>
                        <th class="p-3.5">ناوی لۆگین (Username)</th>
                        <th class="p-3.5">ئیمەیڵ</th>
                        <th class="p-3.5">دەسەڵات / بەش (Role)</th>
                        <th class="p-3.5">بەڕێوەبەرایەتی</th>
                        <th class="p-3.5 text-center">باری ئەکاونت</th>
                        <th class="p-3.5 text-center">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-700/30 transition">
                            <td class="p-3.5 font-mono text-xs text-slate-500">{{ $user->id }}</td>
                            <td class="p-3.5 font-bold text-slate-100">{{ $user->name }}</td>
                            <td class="p-3.5 font-mono text-xs text-sky-400 font-semibold">{{ $user->user_login ?? '-' }}</td>
                            <td class="p-3.5 font-mono text-xs text-slate-300" dir="ltr">{{ $user->email }}</td>
                            <td class="p-3.5 text-xs font-bold text-indigo-300">{{ $user->role_name_kurdish }}</td>
                            <td class="p-3.5 text-xs text-slate-400">{{ $user->trafficDirectorate->name_kurdish ?? 'گشتی' }}</td>
                            <td class="p-3.5 text-center">
                                @if($user->is_active)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">چالاکە</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">ڕاگیراوە</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="editingUser = { id: {{ $user->id }}, name: '{{ $user->name }}', user_login: '{{ $user->user_login }}', email: '{{ $user->email }}', role: '{{ $user->role }}', traffic_directorate_id: '{{ $user->traffic_directorate_id }}' }; showEditModal = true;" 
                                            class="px-3 py-1 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-lg transition flex items-center gap-1">
                                        <i class="fa-solid fa-user-pen"></i> دەستکاری / پاسوۆرد
                                    </button>
                                    <form action="{{ route('users.toggle', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold rounded-lg transition">
                                            {{ $user->is_active ? 'ڕاگرتن' : 'چالاککردن' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create User Modal -->
    <div x-show="showUserModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span class="flex items-center"><i class="fa-solid fa-user-plus ml-2 text-indigo-400"></i> دروستکردنی ئەکاونتی فەرمانبەری نوێ</span>
                <button @click="showUserModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">ناوی کاملی فەرمانبەر *</label>
                        <input type="text" name="name" required placeholder="ناوی سێیانی فەرمانبەر" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">ناوی لۆگین (Username) *</label>
                        <input type="text" name="user_login" required placeholder="مثلاً: karwan2024" dir="ltr" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">ئیمەیڵ (Email) *</label>
                        <input type="email" name="email" required placeholder="karwan@customs.gov.krd" dir="ltr" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">وشەی تێپەڕ (Password) *</label>
                        <input type="password" name="password" required placeholder="••••••••" dir="ltr" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">بەش / دەسەڵاتی کارکردن (Role) *</label>
                        <select name="role" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100">
                            <option value="data_entry">١. کارمەندی داتا ئەنتەری (Step 1)</option>
                            <option value="inspector">٢. ئەندازیار / کەشف و تەخمین (Step 2)</option>
                            <option value="auditor">٣. کارمەندی وردبینی (Step 3)</option>
                            <option value="cashier">٤. ژمێریار / وەسڵبڕ (Step 4)</option>
                            <option value="booklet">٥. بەشی دەفتەر (Step 5)</option>
                            <option value="admin">سەرپەرشتیار / کارگێڕی (Admin)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">بەڕێوەبەرایەتی</label>
                        <select name="traffic_directorate_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100">
                            <option value="">گشتی (All)</option>
                            @foreach($directorates as $dir)
                                <option value="{{ $dir->id }}">{{ $dir->name_kurdish }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showUserModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 text-xs font-bold rounded-xl">پاشگەزبوونەوە</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg">تۆمارکردن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User & Password Reset Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-bold text-white flex items-center justify-between border-b border-slate-700 pb-3">
                <span class="flex items-center"><i class="fa-solid fa-user-pen ml-2 text-amber-400"></i> دەستکاریکردنی فەرمانبەر & گۆڕینی پاسوۆرد</span>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
            </h3>

            <form :action="'/users/' + editingUser.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">ناوی کاملی فەرمانبەر *</label>
                        <input type="text" name="name" x-model="editingUser.name" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">ناوی لۆگین (Username)</label>
                        <input type="text" name="user_login" x-model="editingUser.user_login" placeholder="مثلاً: karwan2024" dir="ltr" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">ئیمەیڵ (Email) *</label>
                    <input type="text" name="email" x-model="editingUser.email" required dir="ltr" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">بەش / دەسەڵاتی کارکردن (Role) *</label>
                        <select name="role" x-model="editingUser.role" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100">
                            <option value="data_entry">١. کارمەندی داتا ئەنتەری (Step 1)</option>
                            <option value="inspector">٢. ئەندازیار / کەشف و تەخمین (Step 2)</option>
                            <option value="auditor">٣. کارمەندی وردبینی (Step 3)</option>
                            <option value="cashier">٤. ژمێریار / وەسڵبڕ (Step 4)</option>
                            <option value="booklet">٥. بەشی دەفتەر (Step 5)</option>
                            <option value="admin">سەرپەرشتیار / کارگێڕی (Admin)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">بەڕێوەبەرایەتی</label>
                        <select name="traffic_directorate_id" x-model="editingUser.traffic_directorate_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100">
                            <option value="">گشتی (All)</option>
                            @foreach($directorates as $dir)
                                <option value="{{ $dir->id }}">{{ $dir->name_kurdish }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Password Reset Field -->
                <div class="bg-slate-800/60 p-4 rounded-xl border border-slate-700 space-y-2">
                    <label class="block text-xs font-bold text-amber-400">گۆڕینی پاسوۆردی فەرمانبەر (ئارەزوومەندانە)</label>
                    <input type="password" name="password" placeholder="پاسوۆردی نوێ بنووسە ئەگەر دەتوانی بگۆڕیت..." dir="ltr" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono">
                    <span class="text-[11px] text-slate-400 block">ئەگەر بە بەتاڵی جێی بهێڵیت، پاسوۆردە کۆنەکەی دەمێنێتەوە.</span>
                </div>

                <div class="flex justify-end space-x-2 space-x-reverse pt-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-800 text-slate-400 text-xs font-bold rounded-xl">پاشگەزبوونەوە</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-lg">پاشەکەوتکردن</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

