<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Console (static Next.js export → public/he-thong)
|--------------------------------------------------------------------------
| Source: repo admin.vitravel.dev — build syncs into public/he-thong.
| Legacy Blade admin retired; /loginAdmin & /logout redirect to SPA login.
*/

Route::redirect('/logout', '/he-thong/login/', 302);
Route::redirect('/loginAdmin', '/he-thong/login/', 302);

Route::get('/he-thong/{any?}', function (?string $any = null) {
    $tail = ($any !== null && $any !== '') ? trim($any, '/') : '';
    $candidate = $tail !== ''
        ? public_path('he-thong/'.$tail.'/index.html')
        : public_path('he-thong/index.html');

    // Prefer exact static page from Next export; fall back to SPA shell.
    $index = is_file($candidate) ? $candidate : public_path('he-thong/index.html');

    if (! is_file($index)) {
        return response(
            '<!doctype html><meta charset="utf-8"><title>Admin chưa build</title>'.
            '<body style="font-family:system-ui;padding:2rem;max-width:40rem;line-height:1.5">'.
            '<h1>Admin Console chưa sẵn sàng</h1>'.
            '<p>Build từ repo <code>admin.vitravel.dev</code> rồi sync vào <code>public/he-thong</code>.</p>'.
            '<pre style="background:#f4f4f4;padding:1rem;border-radius:8px">cd /var/www/html/admin.vitravel.dev'."\n".
            'npm ci && npm run build</pre>'.
            '</body>',
            503
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    return response(file_get_contents($index), 200)
        ->header('Content-Type', 'text/html; charset=UTF-8')
        ->header('Cache-Control', 'no-cache');
})->where('any', '.*')->name('admin.spa');
