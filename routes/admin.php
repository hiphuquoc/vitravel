<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Legacy Blade admin retired
|--------------------------------------------------------------------------
| Local live UI: ADMIN_DEV_URL=http://localhost:3100 + `cd admin && npm run dev`
| Opening /he-thong/* redirects to Next HMR. Clear ADMIN_DEV_URL for static build.
*/

Route::redirect('/logout', '/he-thong/login/', 302);
Route::redirect('/loginAdmin', '/he-thong/login/', 302);

Route::get('/he-thong/{any?}', function (?string $any = null) {
    $dev = rtrim((string) config('app.admin_dev_url'), '/');
    $dev = preg_replace('#/he-thong$#', '', $dev) ?: $dev;

    if ($dev !== '') {
        $tail = ($any !== null && $any !== '') ? '/'.trim($any, '/') : '';
        $target = $dev.'/he-thong'.$tail.'/';
        if ($qs = request()->getQueryString()) {
            $target .= '?'.$qs;
        }

        return redirect()->away($target);
    }

    $index = public_path('he-thong/index.html');

    if (! is_file($index)) {
        return response(
            '<!doctype html><meta charset="utf-8"><title>Admin chưa build</title>'.
            '<body style="font-family:system-ui;padding:2rem;max-width:40rem;line-height:1.5">'.
            '<h1>Admin Console chưa sẵn sàng</h1>'.
            '<p><strong>Live HMR:</strong></p>'.
            '<pre style="background:#f4f4f4;padding:1rem;border-radius:8px"># .env → ADMIN_DEV_URL=http://localhost:3100'."\n".
            'cd admin && npm run dev'."\n".
            '# mở http://localhost:3100/he-thong/</pre>'.
            '<p><strong>Hoặc build static:</strong> <code>cd admin && npm run build</code></p>'.
            '</body>',
            503
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    return response(file_get_contents($index), 200)
        ->header('Content-Type', 'text/html; charset=UTF-8')
        ->header('Cache-Control', 'no-cache');
})->where('any', '.*')->name('admin.spa');
