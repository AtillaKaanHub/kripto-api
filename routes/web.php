<?php

use Illuminate\Support\Facades\Route;

// Kullanıcılar /kripto adresine girdiğinde 'kripto' adındaki Blade dosyasını göster
Route::get('/kripto', function () {
    return view('kripto');
});
