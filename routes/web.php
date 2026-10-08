<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// دروستکردنی کاتی بۆ ئەدمینی سەرەتایی (لە دەرەوەی auth)
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

    $user->update([
        'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
        'is_active' => true,
    ]);

    return 'بەکارهێنەر ئامادەیە! ناوی بەکارهێنەر: admin یان ئیمەیڵ: admin@gmail.com | پاسۆرد: 12345678';
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/official-prints', [TransactionController::class, 'officialPrintIndex'])->name('transactions.official_prints');
    Route::get('/transactions/trash/bin', [TransactionController::class, 'trashIndex'])->name('transactions.trash');
    Route::get('/transactions/reports/expired-booklets', [TransactionController::class, 'expiredBookletsReport'])->name('transactions.expired_booklets');
    Route::get('/transactions/api/lookup-previous', [TransactionController::class, 'lookupPreviousApi'])->name('transactions.lookup_previous_api');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');

    Route::post('/transactions/scan', [TransactionController::class, 'scanBarcode'])->name('transactions.scan');
    Route::get('/transactions/api/calculate-fee', [TransactionController::class, 'calculateApi'])->name('transactions.calculate_api');

    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{transaction}/inspect', [TransactionController::class, 'inspect'])->name('transactions.inspect');
    Route::post('/transactions/{transaction}/audit', [TransactionController::class, 'audit'])->name('transactions.audit');
    Route::post('/transactions/{transaction}/pay', [TransactionController::class, 'pay'])->name('transactions.pay');
    Route::post('/transactions/{transaction}/complete-booklet', [TransactionController::class, 'completeBooklet'])->name('transactions.complete_booklet');
    Route::post('/transactions/{transaction}/submit', [TransactionController::class, 'submitArchive'])->name('transactions.submit');
    Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

    Route::post('/transactions/{transaction}/return', [TransactionController::class, 'returnTransaction'])->name('transactions.return');
    Route::post('/transactions/{transaction}/revert-step', [TransactionController::class, 'revertStep'])->name('transactions.revert_step');
    Route::post('/transactions/{transaction}/create-pledge', [TransactionController::class, 'createRelatedPledge'])->name('transactions.create_pledge');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::post('/transactions/{id}/restore', [TransactionController::class, 'restore'])->name('transactions.restore');
    Route::delete('/transactions/{id}/force-delete', [TransactionController::class, 'forceDelete'])->name('transactions.force_delete');

    // Notifications
    Route::get('/notifications/read/{notification}', function (\App\Models\Notification $notification) {
        $notification->update(['is_read' => true]);
        return redirect($notification->link ?? route('dashboard'));
    })->name('notifications.read');

    // Print views & Official Reports Actions
    Route::post('/transactions/{transaction}/mark-printed', [TransactionController::class, 'markPrinted'])->name('transactions.mark_printed');
    Route::get('/transactions/{transaction}/print-data-entry', [TransactionController::class, 'printDataEntry'])->name('transactions.print_data_entry');
    Route::get('/transactions/{transaction}/print-receipt', [TransactionController::class, 'printReceipt'])->name('transactions.print_receipt');
    Route::get('/transactions/{transaction}/print-payment-receipt', [TransactionController::class, 'printPaymentReceipt'])->name('transactions.print_payment_receipt');
    Route::get('/transactions/{transaction}/print-booklet', [TransactionController::class, 'printBooklet'])->name('transactions.print_booklet');
    Route::get('/transactions/{transaction}/print-pledge', [TransactionController::class, 'printPledge'])->name('transactions.print_pledge');
    Route::get('/transactions/{transaction}/print-cancellation', [TransactionController::class, 'printCancellation'])->name('transactions.print_cancellation');
    Route::get('/transactions/{transaction}/print-restriction', [TransactionController::class, 'printRestriction'])->name('transactions.print_restriction');
    Route::get('/transactions/{transaction}/print-inspection-letter', [TransactionController::class, 'printInspectionLetter'])->name('transactions.print_inspection_letter');

    // Inline Lookup Additions & Updates
    Route::post('/lookup/directorate', [LookupController::class, 'storeDirectorate'])->name('lookup.directorate');
    Route::put('/lookup/directorate/{directorate}', [LookupController::class, 'updateDirectorate'])->name('lookup.directorate.update');

    Route::post('/lookup/director', [LookupController::class, 'storeDirector'])->name('lookup.director');
    Route::put('/lookup/director/{director}', [LookupController::class, 'updateDirector'])->name('lookup.director.update');

    Route::post('/lookup/plate-type', [LookupController::class, 'storePlateType'])->name('lookup.plate_type');
    Route::put('/lookup/plate-type/{plateType}', [LookupController::class, 'updatePlateType'])->name('lookup.plate_type.update');

    Route::post('/lookup/car-make', [LookupController::class, 'storeCarMake'])->name('lookup.car_make');
    Route::put('/lookup/car-make/{carMake}', [LookupController::class, 'updateCarMake'])->name('lookup.car_make.update');

    Route::post('/lookup/car-color', [LookupController::class, 'storeCarColor'])->name('lookup.car_color');
    Route::put('/lookup/car-color/{carColor}', [LookupController::class, 'updateCarColor'])->name('lookup.car_color.update');

    Route::post('/lookup/transaction-type', [LookupController::class, 'storeTransactionType'])->name('lookup.transaction_type');
    Route::put('/lookup/transaction-type/{transactionType}', [LookupController::class, 'updateTransactionType'])->name('lookup.transaction_type.update');

    // Super Admin Settings & User Management
    Route::get('/settings/pricing', [SettingsController::class, 'pricing'])->name('settings.pricing');
    Route::post('/settings/pricing', [SettingsController::class, 'updatePricing'])->name('settings.pricing.update');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
    Route::post('/profile/password', [UserController::class, 'changeMyPassword'])->name('profile.password');

    // Activity Logs
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
});
