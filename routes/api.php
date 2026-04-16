<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CryptoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// laravelde yol tanımlar , {coin} gelecek kelimeyi yakalar ve controllere gönderir.
Route::get('/fiyat/{coin}', [CryptoController::class, 'getPrice']);

// kullanıcının adrese giderek verileri görebilmesi
Route::get('/gecmis/{coin}', [CryptoController::class, 'getHistory']);