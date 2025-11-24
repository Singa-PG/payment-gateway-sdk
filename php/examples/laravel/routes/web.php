<?php

use App\Http\Controllers\SingaPayController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('singapay.dashboard');
});

// SingaPay Routes
Route::prefix('singapay')->group(function () {
    Route::get('/dashboard', [SingaPayController::class, 'dashboard'])->name('singapay.dashboard');

    // Account Management
    Route::get('/accounts', [SingaPayController::class, 'accounts'])->name('singapay.accounts');
    Route::post('/accounts', [SingaPayController::class, 'createAccount'])->name('singapay.accounts.create');

    // Payment Links
    Route::get('/payment-links', [SingaPayController::class, 'showPaymentLinkForm'])->name('singapay.payment-links');
    Route::post('/payment-links', [SingaPayController::class, 'createPaymentLink'])->name('singapay.payment-links.create');

    // Virtual Accounts
    Route::get('/virtual-accounts', [SingaPayController::class, 'showVirtualAccountForm'])->name('singapay.virtual-accounts');
    Route::post('/virtual-accounts', [SingaPayController::class, 'createVirtualAccount'])->name('singapay.virtual-accounts.create');

    // Disbursement Tools
    Route::get('/disbursement', [SingaPayController::class, 'disbursementTools'])->name('singapay.disbursement');
    Route::post('/check-beneficiary', [SingaPayController::class, 'checkBeneficiary'])->name('singapay.check-beneficiary');
    Route::post('/check-transfer-fee', [SingaPayController::class, 'checkTransferFee'])->name('singapay.check-transfer-fee');
});
