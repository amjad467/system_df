<?php
// دروستکردنی کاتی بۆ ئەدمینی سەرەتایی
Route::get('/create-admin', function () {
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'admin@gmail.com'],
        [
            'name' => 'بەڕێوەبەری سیستەم',
            'username' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
            'role' => 'admin',
            'is_active' => true,
        ]
    );

    // ئەگەر پێشتر هەبووبێت، پاسۆردەکەی بۆ 12345678 نوێ دەکاتەوە
    $user->update([
        'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
        'is_active' => true,
    ]);

    return 'بەکارهێنەر ئامادەیە! ناوی بەکارهێنەر: admin یان ئیمەیڵ: admin@gmail.com | پاسۆرد: 12345678';
});





});

