<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [\App\Http\Controllers\AuthController::class, 'store'])
    ->name('register');

Route::post('login', [\App\Http\Controllers\AuthController::class, 'login'])
    ->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])
        ->name('logout');

    Route::get('balance', [\App\Http\Controllers\BalanceController::class, 'show'])
        ->name('balance.show');

    Route::put('balance', [\App\Http\Controllers\BalanceController::class, 'update'])
        ->name('balance.add');

    Route::apiResource('products', \App\Http\Controllers\ProductController::class);

    Route::get('unavailable', [\App\Http\Controllers\ProductController::class, 'getOutOfStock'])
        ->name('products.unavailable');

    Route::get('cart', [\App\Http\Controllers\CartController::class, 'index'])
        ->name('cart.index');

    Route::post('cart', [\App\Http\Controllers\CartController::class, 'store'])
        ->name('cart.add');

    Route::delete('cart', [\App\Http\Controllers\CartController::class, 'destroy'])
        ->name('cart.remove');

    Route::post('checkout', [\App\Http\Controllers\CartController::class, 'checkout'])
        ->name('checkout');
});
