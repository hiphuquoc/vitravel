<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoEntry;
use App\Services\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HelperController extends Controller
{
    public function convertStrToSlug(Request $request, SeoService $seoService): JsonResponse
    {
        $text = (string) $request->input('str', $request->input('string', ''));
        $slug = Str::slug($text);
        $parentSlugFull = (string) $request->input('parent_slug_full', '');
        $parentId = $request->input('parent_id');

        if ($parentId) {
            $parent = SeoEntry::query()->with('translations')->find((int) $parentId);
            $parentSlugFull = $parent?->translation()?->slug_full ?? $parentSlugFull;
        }

        $slugFull = $parentSlugFull
            ? rtrim($parentSlugFull, '/').'/'.$slug
            : '/'.$slug;

        return response()->json([
            'success' => true,
            'slug' => $slug,
            'slug_full' => $slugFull,
        ]);
    }
}
