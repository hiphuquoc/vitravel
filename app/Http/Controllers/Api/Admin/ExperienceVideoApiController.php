<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ExperienceVideo;
use App\Models\ExperienceVideoTranslation;
use App\Models\Language;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExperienceVideoApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $query = ExperienceVideo::query()->with(['translations', 'thumbnail', 'videoFile', 'country.translations']);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('search')) {
            $q = $request->string('search')->toString();
            $query->where(function ($inner) use ($q) {
                $inner->where('youtube_id', 'like', "%{$q}%")
                    ->orWhere('video_url', 'like', "%{$q}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('title', 'like', "%{$q}%"));
            });
        }
        $paginator = $query->orderBy('sort')->orderByDesc('id')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));
        $media = app(MediaService::class);

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(function (ExperienceVideo $v) use ($locale, $media) {
                $t = $v->translation($locale);

                return [
                    'id' => $v->id,
                    'title' => $t?->title,
                    'youtube_id' => $v->youtube_id,
                    'provider' => $v->provider(),
                    'status' => $v->status,
                    'sort' => $v->sort,
                    'show_on_home' => $v->show_on_home,
                    'duration' => $v->duration,
                    'tag' => $v->tag,
                    'thumbnail' => $media->adminMediaPayload($v->thumbnail, 'thumb')
                        ?? ($v->resolvedYoutubeId()
                            ? [
                                'id' => null,
                                'url' => 'https://i.ytimg.com/vi/'.$v->resolvedYoutubeId().'/hqdefault.jpg',
                                'url_thumb' => 'https://i.ytimg.com/vi/'.$v->resolvedYoutubeId().'/mqdefault.jpg',
                            ]
                            : null),
                    'updated_at' => $v->updated_at?->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function meta(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $media = app(MediaService::class);
        $maxVideoKb = $media->effectiveVideoUploadMaxKb();

        return ApiResponse::success([
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'countries' => Country::query()->with('translations')->orderBy('sort')->get()->map(
                fn (Country $c) => ['id' => $c->id, 'name' => $c->translation($locale)?->name ?? $c->code]
            )->values(),
            'statuses' => [
                ['value' => 'draft', 'label' => 'Nháp'],
                ['value' => 'published', 'label' => 'Xuất bản'],
            ],
            'max_video_upload_kb' => $maxVideoKb,
            'video_upload_hint' => 'MP4, WebM, MOV — tối đa '.($maxVideoKb >= 1024
                ? round($maxVideoKb / 1024, 1).'MB'
                : $maxVideoKb.'KB'),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $v = ExperienceVideo::query()->with(['translations', 'thumbnail', 'videoFile', 'country'])->findOrFail($id);
        $t = $v->translation($locale);
        $media = app(MediaService::class);

        return ApiResponse::success([
            'id' => $v->id,
            'country_id' => $v->country_id,
            'youtube_id' => $v->youtube_id,
            'video_url' => $v->video_url,
            'duration' => $v->duration,
            'tag' => $v->tag,
            'sort' => $v->sort,
            'status' => $v->status,
            'show_on_home' => $v->show_on_home,
            'published_at' => $v->published_at?->toIso8601String(),
            'provider' => $v->provider(),
            'title' => $t?->title,
            'description' => $t?->description,
            'translated_locales' => $this->translatedLocaleCodes($v, 'title'),
            'thumbnail' => $media->adminMediaPayload($v->thumbnail, 'card'),
            'video_file' => $media->adminMediaPayload($v->videoFile, 'full'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->merge(['id' => $id]);

        return $this->save($request);
    }

    public function destroy(int $id): JsonResponse
    {
        $video = ExperienceVideo::query()->findOrFail($id);
        app(\App\Services\Purge\EntityPurgeService::class)->purge($video);

        return ApiResponse::success(null, 'Đã xóa video (kèm media)');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:experience_videos,id',
                'country_id' => 'nullable|integer|exists:countries,id',
                'source' => 'nullable|in:youtube,upload',
                'youtube_id' => 'nullable|string|max:255',
                'video_url' => 'nullable|string|max:500',
                'duration' => 'nullable|string|max:16',
                'tag' => 'nullable|string|max:120',
                'sort' => 'nullable|integer|min:0',
                'status' => 'nullable|in:draft,published',
                'show_on_home' => 'nullable|boolean',
                'published_at' => 'nullable|date',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'thumbnail_media_id' => 'nullable|integer|exists:media,id',
                'remove_thumbnail' => 'nullable|boolean',
                'video_media_id' => 'nullable|integer|exists:media,id',
                'remove_video_file' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $source = $validated['source'] ?? null;
        $existing = isset($validated['id'])
            ? ExperienceVideo::query()->find($validated['id'])
            : null;

        $youtubeId = ExperienceVideo::extractYoutubeId($validated['youtube_id'] ?? null);
        $videoUrl = $validated['video_url'] ?? null;
        $removeVideo = $request->boolean('remove_video_file');
        $videoMediaId = isset($validated['video_media_id']) ? (int) $validated['video_media_id'] : null;

        if ($source === 'youtube') {
            $removeVideo = true;
            $videoMediaId = null;
        } elseif ($source === 'upload') {
            $youtubeId = null;
            $videoUrl = null;
        }

        $hasUpload = $videoMediaId
            || ($existing?->video_media_id && ! $removeVideo);
        $hasYoutube = filled($youtubeId) || filled($videoUrl);

        if (! $hasUpload && ! $hasYoutube) {
            return ApiResponse::fromValidation(ValidationException::withMessages([
                'source' => 'Chọn YouTube hoặc upload file video.',
            ]));
        }

        $video = DB::transaction(function () use ($request, $validated, $locale, $youtubeId, $videoUrl, $videoMediaId, $removeVideo) {
            $video = isset($validated['id'])
                ? ExperienceVideo::query()->findOrFail($validated['id'])
                : new ExperienceVideo;
            $video->fill([
                'country_id' => $validated['country_id'] ?? null,
                'youtube_id' => $youtubeId,
                'video_url' => $videoUrl,
                'duration' => $validated['duration'] ?? null,
                'tag' => $validated['tag'] ?? null,
                'sort' => $validated['sort'] ?? 0,
                'status' => $validated['status'] ?? 'draft',
                'show_on_home' => $request->boolean('show_on_home'),
                'published_at' => $validated['published_at'] ?? null,
            ]);
            $video->save();
            $this->saveModelTranslation(
                $video,
                ExperienceVideoTranslation::class,
                'experience_video_id',
                $locale,
                ['title' => $validated['title'], 'description' => $validated['description'] ?? null],
                ['title', 'description'],
            );
            $media = app(MediaService::class);
            $media->syncDirectMediaId(
                $video,
                'thumbnail_media_id',
                isset($validated['thumbnail_media_id']) ? (int) $validated['thumbnail_media_id'] : null,
                $request->boolean('remove_thumbnail'),
            );
            $media->syncDirectMediaId(
                $video,
                'video_media_id',
                $videoMediaId,
                $removeVideo,
            );

            return $video->fresh(['translations', 'thumbnail', 'videoFile']);
        });

        return $this->show($request->merge(['locale' => $locale]), $video->id);
    }
}
