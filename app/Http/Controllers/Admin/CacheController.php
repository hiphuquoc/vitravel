<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HtmlCacheService;
use Illuminate\Http\RedirectResponse;

class CacheController extends Controller
{
    public function clear(HtmlCacheService $cache): RedirectResponse
    {
        $count = $cache->clearAll();
        $cache->clearMenu();

        return redirect()
            ->back()
            ->with('success', "Đã xoá {$count} file cache HTML.");
    }
}
