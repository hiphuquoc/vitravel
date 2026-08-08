<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Legacy admin URL redirects
|--------------------------------------------------------------------------
| Admin Console chạy trên host riêng (ADMIN_APP_URL), không còn phục vụ
| static tại /he-thong trên domain public. URL cũ → redirect sang admin app.
*/

Route::get('/logout', function () {
    return redirect()->away(admin_app_url('/login/'), 302);
})->name('admin.logout.redirect');

Route::get('/loginAdmin', function () {
    return redirect()->away(admin_app_url('/login/'), 302);
})->name('admin.login.redirect');

Route::get('/he-thong/{any?}', function (?string $any = null) {
    $tail = ($any !== null && $any !== '') ? trim($any, '/') : '';
    $target = $tail !== '' ? admin_app_url($tail.'/') : admin_app_url('/');

    return redirect()->away($target, 302);
})->where('any', '.*')->name('admin.spa');
