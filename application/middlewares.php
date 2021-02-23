<?php

defined('DS') or exit('No direct script access.');

/*
|--------------------------------------------------------------------------
| Middleware
|--------------------------------------------------------------------------
|
| Middleware menyediakan cara untuk melampirkan fungsionalitas ke rute anda.
| Middleware bawaan 'before' dan 'after' akan dipanggil sebelum dan sesudah
| setiap request direspon. Anda juga dapat membuat middleware baru tentunya.
|
| Mari kita lihat contohnya..
|
| <code>
|
|      // Pertama, definisikan middlewarenya:
|      Route::middleware('middlewareku', function () {
|          return 'Middlewareku sukses jalankan!';
|      });
|
|      // Lalu, tinggal lampirkan saja ke rute:
|      Route::get('/', ['before' => 'middlewareku', function () {
|          return Halo dunia!';
|      }]);
|
| <code>
|
*/

Route::middleware('before', function () {
    // ..
});

Route::middleware('after', function ($response) {
    // ..
});

Route::middleware('csrf', function () {
    if (Request::forged()) {
        return Response::error('500');
    }
});

Route::middleware('auth', function () {
    if (Auth::guest()) {
        return Redirect::to('login');
    }
});
