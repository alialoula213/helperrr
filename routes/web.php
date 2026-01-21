<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
Route::get('logout', [App\Http\Controllers\HomeController::class, 'logout'])->name('logout');
Route::get('r/{uuid}', [App\Http\Controllers\HomeController::class, 'ref'])->name('ref');
//IPN
Route::any('ipn/success', [App\Http\Controllers\IpnController::class, 'success'])->name('ipn.success');
Route::any('ipn/fail', [App\Http\Controllers\IpnController::class, 'fail'])->name('ipn.fail');
Route::post('ipn/coinpayments', [App\Http\Controllers\IpnController::class, 'ipnCoinpayments'])->name('ipn.coinpayments');
Route::post('ipn/paykassa', [App\Http\Controllers\IpnController::class, 'ipnPaykassa'])->name('ipn.paykassa');
Route::post('ipn/paykassa-success', [\App\Http\Controllers\IpnController::class, 'paykassaSuccess'])->name('ipn.paykassa_success');
Route::post('ipn/gourl', [App\Http\Controllers\IpnController::class, 'ipnGourl'])->name('ipn.gourl');
Route::post('ipn/coingate', [App\Http\Controllers\IpnController::class, 'ipnCoingate'])->name('ipn.coingate');
Route::get('ipn/cryptapi', [App\Http\Controllers\IpnController::class, 'ipnCryptApi'])->name('ipn.cryptapi');
Route::post('ipn/sendbit/{invoice}', [App\Http\Controllers\IpnController::class, 'ipnSendBit'])->name('ipn.sendbit');
Route::post('ipn/faucetpay', [App\Http\Controllers\IpnController::class, 'ipnFaucetPay'])->name('ipn.faucetpay');
//Auth routes
Route::middleware(['auth', 'active_user'])->group(function(){
    //Account
    Route::get('account', [App\Http\Controllers\AccountController::class, 'index'])->name('account');
    Route::get('my-history', [App\Http\Controllers\AccountController::class, 'history'])->name('history');
    Route::get('my-deposits', [App\Http\Controllers\AccountController::class, 'deposits'])->name('my-deposits');
    Route::get('my-withdrawals', [App\Http\Controllers\AccountController::class, 'withdrawals'])->name('my-withdrawals');
    Route::get('my-commissions', [App\Http\Controllers\AccountController::class, 'commissions'])->name('my-commissions');
    Route::get('referral-tools', [App\Http\Controllers\AccountController::class, 'referralTools'])->name('referral-tools');
    Route::get('news', [App\Http\Controllers\AccountController::class, 'news'])->name('news');
    //Payments
    Route::get('payment/{invoice}', [App\Http\Controllers\PurchaseController::class, 'index'])->name('payment');
    //Support Center
    Route::post('tickets/comment/{ticket}', [App\Http\Controllers\SupportController::class, 'comment'])->name('tickets.comment');
    Route::resource('tickets', App\Http\Controllers\SupportController::class)->except('edit', 'destroy')->parameters(['tickets' => 'ticket:ticket_id']);
});
