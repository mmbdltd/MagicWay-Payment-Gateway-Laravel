<?php

use App\Http\Controllers\MoMagicPaymentController;
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

Route::get('/', function () {
    return view('welcome');
});

// MAGICWAY Start
Route::get('/payment_checkout', [MoMagicPaymentController::class, 'checkout']);

Route::post('/pay', [MoMagicPaymentController::class, 'order']);

Route::post('/success', [MoMagicPaymentController::class, 'success']);
Route::post('/fail', [MoMagicPaymentController::class, 'fail']);
Route::post('/cancel', [MoMagicPaymentController::class, 'cancel']);

Route::post('/ipn', [MoMagicPaymentController::class, 'ipn']);
//MAGICWAY END
