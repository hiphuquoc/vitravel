<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ExperienceVideo;
use App\Models\ExperienceVideoTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ExperienceVideoController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(Request $request): View
    {
        $status = $request->string('status')->toString();
        $q = $request->string('q')->toString();

        $videos = ExperienceVideo::query()
            ->with(['translations', 'thumbnail', 'videoFile', 'country.translations'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('youtube_id', 'like', "%{$q}%")
                        ->orWhere('video_url', 'like', "%{$q}%")
                        ->orWhere('tag', 'like', "%{$q}%")
                        ->orWhereHas('translations', fn ($t) => $t->where('title', 'like', "%{$q}%"));
                });
            })
            ->orderBy('sort')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $title = 'Video trải nghiệm';

        return view('admin.experience-video.list', compact('videos', 'title', 'status', 'q'));
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $id = $request->integer('id');

        $video = $id > 0
            ? ExperienceVideo::query()->with(['translations', 'thumbnail', 'videoFile', 'country'])->findOrFail($id)
            : null;

        $languages = $this->activeLanguages();
        $translation = $video?->translation($locale);
        $countries = Country::query()->active()->with('translations')->orderBy('sort')->get();
        $title = $video ? 'Chỉnh sửa video' : 'Thêm video mới';
        $uploadMaxKb = $this->effectiveUploadMaxKb();
        $uploadMaxLabel = ini_get('upload_max_filesize') ?: round($uploadMaxKb / 1024, 1).'MB';
        $videoMaxKb = $this->effectiveVideoUploadMaxKb();
        $videoMaxLabel = $this->formatUploadMaxLabel($videoMaxKb);

        return view('admin.experience-video.view', compact(
            'video', 'locale', 'languages', 'translation', 'countries', 'title',
            'uploadMaxKb', 'uploadMaxLabel', 'videoMaxKb', 'videoMaxLabel',
        ));
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();

        if ((int) $request->input('id') <= 0) {
            $request->merge(['id' => null]);
        }

        $this->assertUploadedFileOk($request, 'image');
        $this->assertUploadedFileOk($request, 'video_file');
        $maxKb = $this->effectiveUploadMaxKb();
        $videoMaxKb = $this->effectiveVideoUploadMaxKb();

        $validated = $request->validate([
            'id' => 'nullable|integer|min:1|exists:experience_videos,id',
            'country_id' => 'nullable|integer|exists:countries,id',
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
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:'.$maxKb,
            'remove_image' => 'nullable|boolean',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v|max:'.$videoMaxKb,
            'remove_video_file' => 'nullable|boolean',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề video.',
            'image.image' => 'File tải lên phải là ảnh hợp lệ.',
            'image.max' => 'Ảnh vượt quá '.round($maxKb / 1024, 1).'MB (giới hạn máy chủ).',
            'video_file.mimetypes' => 'Chỉ chấp nhận video MP4, WebM hoặc MOV.',
            'video_file.max' => 'Video vượt quá '.round($videoMaxKb / 1024, 1).'MB. Vui lòng nén nhỏ hơn hoặc tăng upload_max_filesize trên máy chủ.',
        ]);

        $youtubeInput = $validated['youtube_id'] ?? $validated['video_url'] ?? null;
        $youtubeId = ExperienceVideo::extractYoutubeId($youtubeInput);
        $videoUrl = $validated['video_url'] ?? null;

        $existing = isset($validated['id'])
            ? ExperienceVideo::query()->find($validated['id'])
            : null;

        $hasSource = $request->hasFile('video_file')
            || ($existing?->video_media_id && ! $request->boolean('remove_video_file'))
            || $youtubeId
            || filled($videoUrl)
            || filled($validated['youtube_id'] ?? null);

        if (! $hasSource) {
            return redirect()->back()->withInput()->withErrors([
                'video_file' => 'Upload file video, hoặc nhập YouTube / Vimeo / link MP4.',
            ]);
        }

        try {
            $video = DB::transaction(function () use ($request, $validated, $locale, $youtubeId, $videoUrl) {
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
                    'status' => $validated['status'] ?? 'published',
                    'show_on_home' => $request->boolean('show_on_home'),
                    'published_at' => $validated['published_at'] ?? now(),
                ]);
                $video->save();

                $this->saveModelTranslation(
                    $video,
                    ExperienceVideoTranslation::class,
                    'experience_video_id',
                    $locale,
                    [
                        'title' => $validated['title'],
                        'description' => $validated['description'] ?? null,
                    ],
                    ['title', 'description'],
                );

                $this->syncDirectCover($video, 'thumbnail_media_id', $request, config('media.videos'));
                $this->mediaService()->syncDirectVideoColumn(
                    $video,
                    'video_media_id',
                    $request,
                    'video_file',
                    'remove_video_file',
                    config('media.video_files'),
                );

                return $video->fresh(['thumbnail', 'videoFile', 'translations']);
            });
        } catch (Throwable $e) {
            Log::error('Experience video save failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->withErrors([
                'video_file' => 'Không lưu được video: '.$e->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.videos.view', ['id' => $video->id, 'language' => $locale])
            ->with('success', 'Đã lưu video thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $video = ExperienceVideo::query()->findOrFail($request->integer('id'));
        app(\App\Services\Purge\EntityPurgeService::class)->purge($video);

        return redirect()->route('admin.videos.list')->with('success', 'Đã xóa video thành công.');
    }

    protected function effectiveVideoUploadMaxKb(): int
    {
        $configKb = (int) config('media.max_video_upload_kb', 1048576);
        $phpKb = $this->phpIniSizeToKb((string) ini_get('upload_max_filesize'));

        return max(100, min($configKb, $phpKb > 0 ? $phpKb : $configKb));
    }

    protected function formatUploadMaxLabel(int $maxKb): string
    {
        if ($maxKb >= 1048576) {
            return round($maxKb / 1048576, 1).'GB';
        }

        return round($maxKb / 1024, 1).'MB';
    }
}
