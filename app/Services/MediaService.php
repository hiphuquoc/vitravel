<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaAttachment;
use App\Support\ProjectContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class MediaService
{
    public function defaultDisk(): string
    {
        $disk = (string) config('media.disk', 'public');

        if ($disk === 'gcs' && ! $this->gcsConfigured()) {
            return 'public';
        }

        return $disk;
    }

    /**
     * Prefix storage folder with projects/{code}/ when ProjectContext is set.
     */
    public function projectPrefixedFolder(?string $folder): string
    {
        $folder = trim((string) $folder, '/');
        $code = ProjectContext::code();
        if ($code === null || $code === '') {
            return $folder;
        }

        $prefix = 'projects/'.$code;
        if ($folder === '' || $folder === $prefix || str_starts_with($folder, $prefix.'/')) {
            return $folder !== '' ? $folder : $prefix;
        }

        return $prefix.'/'.$folder;
    }

    public function gcsConfigured(): bool
    {
        return filled(config('services.gcs.bucket'))
            && filled(config('services.gcs.key_file'))
            && is_readable((string) config('services.gcs.key_file'));
    }

    public function storeUploadedFile(
        UploadedFile $file,
        ?string $folder = null,
        ?string $disk = null,
        ?string $slug = null,
        ?string $role = null,
    ): Media {
        $disk ??= $this->defaultDisk();
        $folder = $this->projectPrefixedFolder($folder ?? config('media.folder', 'vitravel/images'));

        $built = $this->buildOptimizedSet($file);
        $extension = $built['extension'];
        $mime = $built['mime'];
        $full = $built['full'];

        $stem = $this->allocateUniqueMediaStem(
            $disk,
            $folder,
            $this->buildMediaStem($slug, $role, $file->getClientOriginalName()),
            $extension,
        );

        $path = $folder.'/'.$stem.'.'.$extension;

        Storage::disk($disk)->put($path, $full['binary'], [
            'visibility' => 'public',
            'ContentType' => $mime,
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        $variantMeta = [];
        foreach ($built['variants'] as $name => $variant) {
            $variantPath = $folder.'/'.$stem.'-'.$name.'.'.$extension;
            Storage::disk($disk)->put($variantPath, $variant['binary'], [
                'visibility' => 'public',
                'ContentType' => $mime,
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]);
            $variantMeta[$name] = [
                'path' => $variantPath,
                'width' => $variant['width'],
                'height' => $variant['height'],
                'size_bytes' => strlen($variant['binary']),
            ];
        }

        $altSource = $slug ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return Media::query()->create([
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size_bytes' => strlen($full['binary']),
            'width' => $full['width'],
            'height' => $full['height'],
            'alt' => Str::limit(trim(str_replace(['-', '_'], ' ', (string) $altSource)), 255, ''),
            'meta' => [
                'variants' => $variantMeta,
                'seo_stem' => $stem,
                'seo_slug' => $this->normalizeMediaStem((string) ($slug ?: '')),
                'seo_role' => $this->normalizeMediaStem((string) ($role ?: '')) ?: null,
            ],
        ]);
    }

    /**
     * Stem SEO cho tên file: {slug}[-{role}] — an toàn path, không trùng.
     */
    public function buildMediaStem(?string $slug, ?string $role, ?string $originalName = null): string
    {
        $base = $this->normalizeMediaStem((string) ($slug ?: ''));
        if ($base === '') {
            $base = $this->normalizeMediaStem((string) pathinfo((string) $originalName, PATHINFO_FILENAME));
        }
        if ($base === '') {
            $base = 'image';
        }

        $roleStem = $this->normalizeMediaStem((string) ($role ?: ''));
        if ($roleStem !== '' && $roleStem !== $base && ! str_ends_with($base, '-'.$roleStem)) {
            $combined = $base.'-'.$roleStem;
            $base = Str::limit($combined, 96, '');
            $base = rtrim($base, '-');
            if ($base === '') {
                $base = 'image';
            }
        }

        return $base;
    }

    /** Chuẩn hoá slug file: a-z0-9-, tối đa 80, không path traversal. */
    public function normalizeMediaStem(string $raw): string
    {
        $raw = str_replace(['\\', '/', '..'], ' ', $raw);
        $stem = Str::slug($raw, '-');
        $stem = strtolower($stem);
        $stem = preg_replace('/[^a-z0-9\-]+/', '', $stem) ?? '';
        $stem = preg_replace('/-+/', '-', $stem) ?? '';
        $stem = trim($stem, '-');
        $stem = Str::limit($stem, 80, '');
        $stem = rtrim($stem, '-');

        return $stem;
    }

    /**
     * Chọn stem chưa bị chiếm (file full + variants trên disk / DB, kể cả soft-delete).
     */
    public function allocateUniqueMediaStem(
        string $disk,
        string $folder,
        string $preferredStem,
        string $extension,
    ): string {
        $stem = $this->normalizeMediaStem($preferredStem) ?: 'image';
        $folder = trim($folder, '/');
        $extension = strtolower($extension);

        for ($i = 0; $i < 250; $i++) {
            $candidate = $i === 0 ? $stem : $stem.'-'.($i + 1);
            if (! $this->mediaStemTaken($disk, $folder, $candidate, $extension)) {
                return $candidate;
            }
        }

        return $stem.'-'.Str::lower(Str::random(8));
    }

    /** @param list<string>|null $variantNames */
    public function mediaStemTaken(
        string $disk,
        string $folder,
        string $stem,
        string $extension,
        ?array $variantNames = null,
    ): bool {
        $folder = trim($folder, '/');
        $stem = $this->normalizeMediaStem($stem);
        if ($stem === '') {
            return true;
        }

        $variantNames ??= array_keys((array) config('media.variants', []));
        $paths = [$folder.'/'.$stem.'.'.$extension];
        foreach ($variantNames as $name) {
            $name = (string) $name;
            if ($name === '' || $name === 'full') {
                continue;
            }
            $paths[] = $folder.'/'.$stem.'-'.$name.'.'.$extension;
        }

        $storage = Storage::disk($disk);
        foreach ($paths as $path) {
            try {
                if ($storage->exists($path)) {
                    return true;
                }
            } catch (Throwable) {
                // Disk lỗi tạm — coi như chưa chiếm, vòng allocate vẫn an toàn nhờ random fallback.
            }
        }

        return Media::withTrashed()->whereIn('path', $paths)->exists();
    }

    /**
     * Lưu file video thô (không resize) — mp4 / webm / mov / …
     */
    public function storeUploadedVideo(UploadedFile $file, ?string $folder = null, ?string $disk = null): Media
    {
        $disk ??= $this->defaultDisk();
        $folder = $this->projectPrefixedFolder($folder ?? config('media.video_files', 'vitravel/video-files'));

        $extension = strtolower($file->getClientOriginalExtension() ?: 'mp4');
        $mime = $file->getMimeType() ?: match ($extension) {
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'm4v' => 'video/x-m4v',
            default => 'video/mp4',
        };

        $uuid = Str::uuid()->toString();
        $path = $folder.'/'.$uuid.'.'.$extension;

        Storage::disk($disk)->putFileAs($folder, $file, $uuid.'.'.$extension, [
            'visibility' => 'public',
            'ContentType' => $mime,
            'CacheControl' => 'public, max-age=31536000',
        ]);

        return Media::query()->create([
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size_bytes' => $file->getSize() ?: null,
            'width' => null,
            'height' => null,
            'alt' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'meta' => ['kind' => 'video'],
        ]);
    }

    /**
     * Sync video file trên cột FK (video_media_id).
     */
    public function syncDirectVideoColumn(
        Model $model,
        string $column,
        Request $request,
        string $fileField = 'video_file',
        string $removeField = 'remove_video_file',
        ?string $folder = null,
    ): void {
        $folder ??= config('media.video_files', 'vitravel/video-files');
        $currentId = $model->{$column} ?? null;
        $current = $currentId ? Media::query()->find($currentId) : null;

        if ($request->boolean($removeField) && $current) {
            $this->deleteMedia($current);
            $model->{$column} = null;
            $model->save();
            $current = null;
        }

        if (! $request->hasFile($fileField)) {
            return;
        }

        if ($current) {
            $this->deleteMedia($current);
        }

        $media = $this->storeUploadedVideo($request->file($fileField), $folder);
        $model->{$column} = $media->id;
        $model->save();
    }

    /**
     * Resolve public URL for a media row + optional variant (thumb|card|lg|full|aliases).
     */
    public function publicUrl(?Media $media, ?string $variant = null): ?string
    {
        if (! $media) {
            return null;
        }

        $variant = $this->resolveVariantName($variant);
        $path = $this->pathForVariant($media, $variant);

        return $this->urlForDiskPath($media->disk, $path);
    }

    /**
     * Responsive srcset string (e.g. "url1 400w, url2 800w, …").
     *
     * @param  list<string>|null  $variants
     */
    public function srcset(?Media $media, ?array $variants = null): ?string
    {
        if (! $media) {
            return null;
        }

        $variants ??= array_merge(array_keys(config('media.variants', [])), ['full']);
        $parts = [];

        foreach ($variants as $name) {
            $resolved = $this->resolveVariantName($name);
            $url = $this->publicUrl($media, $resolved);
            $width = $this->widthForVariant($media, $resolved);
            if (! $url || ! $width) {
                continue;
            }
            $parts[$width] = $url.' '.$width.'w';
        }

        ksort($parts);

        return $parts === [] ? null : implode(', ', array_values($parts));
    }

    /**
     * Payload dùng chung cho ViewData / Blade.
     *
     * @return array{src: ?string, srcset: ?string, width: ?int, height: ?int, alt: ?string, variant: string}
     */
    public function imagePayload(?Media $media, string $variant = 'card'): array
    {
        $variant = $this->resolveVariantName($variant);

        return [
            'src' => $this->publicUrl($media, $variant),
            'srcset' => $this->srcset($media),
            'width' => $this->widthForVariant($media, $variant),
            'height' => $this->heightForVariant($media, $variant),
            'alt' => $media?->alt,
            'variant' => $variant,
        ];
    }

    public function deleteMedia(?Media $media): void
    {
        if (! $media) {
            return;
        }

        $this->deleteMediaFiles($media);
        $media->delete();
    }

    /**
     * Xóa file trên disk (GCS/local) + hard-delete row media (không để soft-delete rác).
     * Dùng khi purge chỗ nghỉ / crawler replace.
     */
    public function destroyMedia(?Media $media): void
    {
        if (! $media) {
            return;
        }

        $this->deleteMediaFiles($media);
        if (method_exists($media, 'forceDelete')) {
            $media->forceDelete();
        } else {
            $media->delete();
        }
    }

    /**
     * Chỉ xóa file khi media không còn attachment / FK nào (ảnh dùng chung thư viện).
     */
    public function deleteMediaIfOrphan(?Media $media): void
    {
        if (! $media) {
            return;
        }

        if ($this->mediaIsReferenced((int) $media->id)) {
            return;
        }

        $this->deleteMedia($media);
    }

    /**
     * Orphan → xóa file GCS + forceDelete row (purge stay / replace crawl).
     */
    /**
     * Bulk destroy orphan media (x�a file v� DB theo l�, kh�ng l?p N query).
     *
     * @param list<int> $mediaIds
     */
    public function destroyOrphanMediaBatch(array $mediaIds): void
    {
        $mediaIds = array_values(array_unique(array_filter(
            array_map('intval', $mediaIds),
            fn (int $id) => $id > 0,
        )));

        if ($mediaIds === []) {
            return;
        }

        // 1. T�m c�c media_id dang du?c reference ? MediaAttachment
        $referenced = MediaAttachment::query()
            ->whereIn('media_id', $mediaIds)
            ->pluck('media_id')
            ->map('intval')
            ->toArray();
        $referencedSet = array_flip($referenced);

        // 2. T�m c�c media_id dang du?c reference ? foreign key tables
        foreach ($this->mediaForeignKeys() as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }
            $hits = DB::table($table)
                ->whereIn($column, $mediaIds)
                ->pluck($column)
                ->map('intval')
                ->toArray();
            foreach ($hits as $hid) {
                $referencedSet[$hid] = true;
            }
        }

        // 3. L?c ra c�c media_id th?c s? m? c�i (orphan)
        $orphanIds = [];
        foreach ($mediaIds as $id) {
            if (! isset($referencedSet[$id])) {
                $orphanIds[] = $id;
            }
        }

        if ($orphanIds === []) {
            return;
        }

        // 4. L?y Media models v� gom paths theo disk d? delete theo batch
        $medias = Media::query()->withTrashed()->whereIn('id', $orphanIds)->get();
        $pathsByDisk = [];

        foreach ($medias as $media) {
            $diskName = $media->disk ?: $this->defaultDisk();
            $pathsByDisk[$diskName] ??= [];
            if (! empty($media->path)) {
                $pathsByDisk[$diskName][] = $media->path;
            }
            foreach (($media->meta['variants'] ?? []) as $variant) {
                if (! empty($variant['path'])) {
                    $pathsByDisk[$diskName][] = $variant['path'];
                }
            }
        }

        foreach ($pathsByDisk as $diskName => $paths) {
            try {
                $disk = Storage::disk($diskName);
                $uniquePaths = array_values(array_unique($paths));
                if ($uniquePaths !== []) {
                    $disk->delete($uniquePaths);
                }
            } catch (\Throwable) {
                // ignore
            }
        }

        // 5. Force delete c�c b?n ghi Media trong DB
        Media::query()->withTrashed()->whereIn('id', $orphanIds)->forceDelete();
    }
    public function destroyMediaIfOrphan(?Media $media): void
    {
        if (! $media) {
            return;
        }

        if ($this->mediaIsReferenced((int) $media->id)) {
            return;
        }

        $this->destroyMedia($media);
    }

    private function deleteMediaFiles(Media $media): void
    {
        $disk = Storage::disk($media->disk);
        $paths = [$media->path];

        foreach (($media->meta['variants'] ?? []) as $variant) {
            if (! empty($variant['path'])) {
                $paths[] = $variant['path'];
            }
        }

        foreach (array_unique($paths) as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }

    private function mediaIsReferenced(int $mediaId): bool
    {
        if (MediaAttachment::query()->where('media_id', $mediaId)->exists()) {
            return true;
        }

        foreach ($this->mediaForeignKeys() as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }
            if (DB::table($table)->where($column, $mediaId)->exists()) {
                return true;
            }
        }

        return false;
    }

    /** @return list<array{0: string, 1: string}> */
    private function mediaForeignKeys(): array
    {
        return [
            ['countries', 'banner_media_id'],
            ['countries', 'listing_banner_media_id'],
            ['cruise_types', 'banner_media_id'],
            ['cruise_types', 'cover_media_id'],
            ['service_categories', 'banner_media_id'],
            ['service_categories', 'cover_media_id'],
            ['tour_categories', 'banner_media_id'],
            ['home_slides', 'image_media_id'],
            ['home_slides', 'image_mobile_media_id'],
            ['home_sections', 'image_media_id'],
            ['destinations', 'image_media_id'],
            ['listing_hubs', 'banner_media_id'],
            ['listing_hubs', 'cover_media_id'],
            ['company_profiles', 'about_banner_media_id'],
            ['company_profiles', 'reasons_image_id'],
            ['experience_videos', 'thumbnail_media_id'],
            ['experience_videos', 'video_media_id'],
            ['team_members', 'avatar_media_id'],
            ['team_member_activity_images', 'media_id'],
            ['articles', 'cover_media_id'],
            ['static_pages', 'cover_media_id'],
            ['seo_entries', 'og_image_id'],
        ];
    }

    /**
     * Sync cover image stored as morph media_attachments (role=cover).
     */
    public function syncCoverAttachment(
        Model $model,
        Request $request,
        string $fileField = 'image',
        string $removeField = 'remove_image',
        ?string $folder = null,
    ): void {
        $folder ??= config('media.folder', 'vitravel/images');

        $existing = $model->mediaAttachments()
            ->where('role', 'cover')
            ->with('media')
            ->first();

        if ($request->boolean($removeField) && $existing) {
            $old = $existing->media;
            $existing->delete();
            $this->deleteMediaIfOrphan($old);
            $existing = null;
        }

        if (! $request->hasFile($fileField)) {
            return;
        }

        if ($existing) {
            $old = $existing->media;
            $existing->delete();
            $this->deleteMediaIfOrphan($old);
        }

        $media = $this->storeUploadedFile(
            $request->file($fileField),
            $folder,
            null,
            $request->input('slug') ?: $request->input('seo_slug'),
            $request->input('media_role') ?: 'cover',
        );

        $model->mediaAttachments()->create([
            'media_id' => $media->id,
            'role' => 'cover',
            'sort' => 0,
        ]);
    }

    /**
     * Sync image stored on a direct FK column (e.g. banner_media_id, avatar_media_id).
     */
    public function syncDirectMediaColumn(
        Model $model,
        string $column,
        Request $request,
        string $fileField = 'image',
        string $removeField = 'remove_image',
        ?string $folder = null,
    ): void {
        $folder ??= config('media.folder', 'vitravel/images');
        $currentId = $model->{$column} ?? null;
        $current = $currentId ? Media::query()->find($currentId) : null;

        if ($request->boolean($removeField) && $current) {
            $this->deleteMedia($current);
            $model->{$column} = null;
            $model->save();
            $current = null;
        }

        if (! $request->hasFile($fileField)) {
            return;
        }

        if ($current) {
            $this->deleteMedia($current);
        }

        $media = $this->storeUploadedFile(
            $request->file($fileField),
            $folder,
            null,
            $request->input('slug') ?: $request->input('seo_slug'),
            $request->input('media_role') ?: $fileField,
        );
        $model->{$column} = $media->id;
        $model->save();
    }

    public function coverUrl(Model $model, ?string $variant = null): ?string
    {
        if (method_exists($model, 'coverMedia')) {
            return $this->publicUrl($model->coverMedia(), $variant);
        }

        $attachment = $model->relationLoaded('mediaAttachments')
            ? $model->mediaAttachments->firstWhere('role', 'cover')
            : (method_exists($model, 'mediaAttachments')
                ? $model->mediaAttachments()->where('role', 'cover')->with('media')->first()
                : null);

        return $this->publicUrl($attachment?->media, $variant);
    }

    /**
     * Regenerate variant files for an existing media row (backfill).
     */
    public function regenerateVariants(Media $media): bool
    {
        $disk = Storage::disk($media->disk);
        if (! $disk->exists($media->path)) {
            return false;
        }

        try {
            $binary = $disk->get($media->path);
        } catch (Throwable) {
            return false;
        }

        if (! is_string($binary) || $binary === '' || ! function_exists('imagecreatefromstring')) {
            return false;
        }

        $source = @imagecreatefromstring($binary);
        if ($source === false) {
            return false;
        }

        $extension = strtolower(pathinfo($media->path, PATHINFO_EXTENSION) ?: 'webp');
        $mime = $media->mime_type ?: match ($extension) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/webp',
        };
        $quality = (int) config('media.jpeg_quality', 82);

        // Remove old variant files
        foreach (($media->meta['variants'] ?? []) as $old) {
            if (! empty($old['path']) && $disk->exists($old['path'])) {
                $disk->delete($old['path']);
            }
        }

        $base = preg_replace('/\.[^.]+$/', '', $media->path) ?: $media->path;
        $variantMeta = [];

        foreach (config('media.variants', []) as $name => $cfg) {
            $edge = (int) ($cfg['max_edge'] ?? 800);
            $q = (int) ($cfg['quality'] ?? $quality);
            $encoded = $this->encodeFromGd($source, $edge, $q, $extension);
            if ($encoded === null) {
                continue;
            }

            $variantPath = $base.'-'.$name.'.'.$extension;
            $disk->put($variantPath, $encoded['binary'], [
                'visibility' => 'public',
                'ContentType' => $mime,
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]);
            $variantMeta[$name] = [
                'path' => $variantPath,
                'width' => $encoded['width'],
                'height' => $encoded['height'],
                'size_bytes' => strlen($encoded['binary']),
            ];
        }

        imagedestroy($source);

        $meta = $media->meta ?? [];
        $meta['variants'] = $variantMeta;
        $media->meta = $meta;
        $media->save();

        return true;
    }

    public function resolveVariantName(?string $variant): string
    {
        if ($variant === null || $variant === '' || $variant === 'full') {
            return 'full';
        }

        $aliases = config('media.aliases', []);
        if (isset($aliases[$variant])) {
            return (string) $aliases[$variant];
        }

        return $variant;
    }

    protected function pathForVariant(Media $media, string $variant): string
    {
        if ($variant === 'full') {
            return $media->path;
        }

        $metaPath = $media->meta['variants'][$variant]['path'] ?? null;
        if (is_string($metaPath) && $metaPath !== '') {
            return $metaPath;
        }

        // Legacy uploads without variants → full
        return $media->path;
    }

    protected function widthForVariant(?Media $media, string $variant): ?int
    {
        if (! $media) {
            return null;
        }

        if ($variant === 'full') {
            return $media->width;
        }

        return $media->meta['variants'][$variant]['width'] ?? $media->width;
    }

    protected function heightForVariant(?Media $media, string $variant): ?int
    {
        if (! $media) {
            return null;
        }

        if ($variant === 'full') {
            return $media->height;
        }

        return $media->meta['variants'][$variant]['height'] ?? $media->height;
    }

    protected function urlForDiskPath(string $disk, string $path): string
    {
        if ($disk === 'gcs') {
            $customBase = config('services.gcs.public_url');
            if ($customBase) {
                return rtrim((string) $customBase, '/').'/'.ltrim($path, '/');
            }

            $bucket = config('services.gcs.bucket');

            return 'https://storage.googleapis.com/'.$bucket.'/'.ltrim($path, '/');
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * @return array{
     *   extension: string,
     *   mime: string,
     *   full: array{binary: string, width: int, height: int},
     *   variants: array<string, array{binary: string, width: int, height: int}>
     * }
     */
    protected function buildOptimizedSet(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $originalExt = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $maxEdge = (int) config('media.max_edge', 1920);
        $quality = (int) config('media.jpeg_quality', 82);

        $preferWebp = function_exists('imagewebp') && in_array($originalExt, ['jpg', 'jpeg', 'png', 'webp'], true);
        $extension = $preferWebp ? 'webp' : (in_array($originalExt, ['png', 'gif'], true) ? $originalExt : 'jpg');
        $mime = match ($extension) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        if (! function_exists('imagecreatefromstring') || ! is_readable((string) $path)) {
            $fallback = $this->fallbackBinary($file, $extension);

            return [
                'extension' => $fallback['extension'],
                'mime' => $fallback['mime'],
                'full' => [
                    'binary' => $fallback['binary'],
                    'width' => (int) ($fallback['width'] ?? 0),
                    'height' => (int) ($fallback['height'] ?? 0),
                ],
                'variants' => [],
            ];
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            $fallback = $this->fallbackBinary($file, $extension);

            return [
                'extension' => $fallback['extension'],
                'mime' => $fallback['mime'],
                'full' => [
                    'binary' => $fallback['binary'],
                    'width' => (int) ($fallback['width'] ?? 0),
                    'height' => (int) ($fallback['height'] ?? 0),
                ],
                'variants' => [],
            ];
        }

        $source = @imagecreatefromstring($raw);
        if ($source === false) {
            $fallback = $this->fallbackBinary($file, $extension);

            return [
                'extension' => $fallback['extension'],
                'mime' => $fallback['mime'],
                'full' => [
                    'binary' => $fallback['binary'],
                    'width' => (int) ($fallback['width'] ?? 0),
                    'height' => (int) ($fallback['height'] ?? 0),
                ],
                'variants' => [],
            ];
        }

        $full = $this->encodeFromGd($source, $maxEdge, $quality, $extension);
        $variants = [];

        foreach (config('media.variants', []) as $name => $cfg) {
            $edge = (int) ($cfg['max_edge'] ?? 800);
            $q = (int) ($cfg['quality'] ?? $quality);

            // Skip variant larger than or equal to master (no benefit)
            if ($full && $edge >= max($full['width'], $full['height'], 1)) {
                continue;
            }

            $encoded = $this->encodeFromGd($source, $edge, $q, $extension);
            if ($encoded !== null) {
                $variants[$name] = $encoded;
            }
        }

        imagedestroy($source);

        if ($full === null) {
            $fallback = $this->fallbackBinary($file, $extension);

            return [
                'extension' => $fallback['extension'],
                'mime' => $fallback['mime'],
                'full' => [
                    'binary' => $fallback['binary'],
                    'width' => (int) ($fallback['width'] ?? 0),
                    'height' => (int) ($fallback['height'] ?? 0),
                ],
                'variants' => [],
            ];
        }

        return [
            'extension' => $extension,
            'mime' => $mime,
            'full' => $full,
            'variants' => $variants,
        ];
    }

    /**
     * @return array{binary: string, width: int, height: int}|null
     */
    protected function encodeFromGd(\GdImage $source, int $maxEdge, int $quality, string $extension): ?array
    {
        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $scale = min(1, $maxEdge / max($srcW, $srcH, 1));
        $dstW = max(1, (int) round($srcW * $scale));
        $dstH = max(1, (int) round($srcH * $scale));

        $canvas = imagecreatetruecolor($dstW, $dstH);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $dstW, $dstH, $transparent);
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        ob_start();
        match ($extension) {
            'webp' => imagewebp($canvas, null, $quality),
            'png' => imagepng($canvas, null, 6),
            'gif' => imagegif($canvas),
            default => imagejpeg($canvas, null, $quality),
        };
        $binary = (string) ob_get_clean();
        imagedestroy($canvas);

        if ($binary === '') {
            return null;
        }

        return [
            'binary' => $binary,
            'width' => $dstW,
            'height' => $dstH,
        ];
    }

    /**
     * @return array{binary: string, extension: string, mime: string, width: ?int, height: ?int}
     */
    protected function fallbackBinary(UploadedFile $file, string $extension): array
    {
        [$width, $height] = $this->probeDimensions($file->getRealPath());

        return [
            'binary' => (string) file_get_contents($file->getRealPath()),
            'extension' => $extension ?: 'jpg',
            'mime' => $file->getMimeType() ?: 'image/jpeg',
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    protected function probeDimensions(string $path): array
    {
        $size = @getimagesize($path);

        return $size ? [(int) $size[0], (int) $size[1]] : [null, null];
    }

    /**
     * Payload gọn cho Admin API (preview + id để gắn khi lưu entity).
     *
     * @return array<string, mixed>|null
     */
    public function adminMediaPayload(?Media $media, string $variant = 'card'): ?array
    {
        if (! $media) {
            return null;
        }

        return [
            'id' => $media->id,
            'url' => $this->publicUrl($media, $variant),
            'url_thumb' => $this->publicUrl($media, 'thumb'),
            'url_lg' => $this->publicUrl($media, 'lg'),
            'filename' => $media->filename,
            'mime_type' => $media->mime_type,
            'size_bytes' => $media->size_bytes,
            'width' => $media->width,
            'height' => $media->height,
            'alt' => $media->alt,
        ];
    }

    /**
     * Payload đầy đủ cho trang Thư viện Media.
     *
     * @return array<string, mixed>
     */
    public function libraryPayload(Media $media): array
    {
        $base = $this->adminMediaPayload($media, 'card') ?? [
            'id' => $media->id,
            'url' => null,
            'filename' => $media->filename,
        ];

        $kind = (($media->meta['kind'] ?? null) === 'video')
            || str_starts_with((string) $media->mime_type, 'video/')
            ? 'video'
            : 'image';

        return array_merge($base, [
            'url_full' => $this->publicUrl($media, 'full'),
            'disk' => $media->disk,
            'path' => $media->path,
            'credit' => $media->credit,
            'folder' => $this->guessFolderKeyFromPath((string) $media->path),
            'kind' => $kind,
            'has_variants' => $media->hasVariants(),
            'created_at' => $media->created_at?->toIso8601String(),
            'updated_at' => $media->updated_at?->toIso8601String(),
        ]);
    }

    /** Map path → folder key whitelist (vd. vitravel/team/... → team). */
    public function guessFolderKeyFromPath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        $bestKey = 'default';
        $bestLen = -1;

        foreach ($this->adminFolderMap() as $key => $folderPath) {
            $folder = trim(str_replace('\\', '/', (string) $folderPath), '/');
            if ($folder === '') {
                continue;
            }
            if ($path === $folder || str_starts_with($path, $folder.'/')) {
                $len = strlen($folder);
                if ($len > $bestLen) {
                    $bestLen = $len;
                    $bestKey = $key;
                }
            }
        }

        return $bestKey;
    }

    /** Gắn / thay / xóa cover (media_attachments role=cover) theo media_id đã upload. */
    public function syncCoverMediaId(Model $model, ?int $mediaId, bool $remove = false): void
    {
        if (! method_exists($model, 'mediaAttachments')) {
            return;
        }

        $existing = $model->mediaAttachments()
            ->where('role', 'cover')
            ->with('media')
            ->first();

        if ($remove) {
            if ($existing) {
                $old = $existing->media;
                $existing->delete();
                $this->deleteMediaIfOrphan($old);
            }

            return;
        }

        if (! $mediaId) {
            return;
        }

        if ($existing && (int) $existing->media_id === $mediaId) {
            return;
        }

        if ($existing) {
            $old = $existing->media;
            $existing->delete();
            $this->deleteMediaIfOrphan($old);
        }

        $media = Media::query()->find($mediaId);
        if (! $media) {
            return;
        }

        $model->mediaAttachments()->create([
            'media_id' => $media->id,
            'role' => 'cover',
            'sort' => 0,
        ]);
    }

    /**
     * Đồng bộ gallery (media_attachments role=gallery) theo danh sách media_id.
     * Xóa toàn bộ gallery cũ rồi tạo lại theo thứ tự — giữ media file (không xóa disk).
     *
     * @param  list<int|string>  $mediaIds
     */
    public function syncGalleryMediaIds(Model $model, array $mediaIds): void
    {
        if (! method_exists($model, 'mediaAttachments')) {
            return;
        }

        $model->mediaAttachments()->where('role', 'gallery')->delete();

        $sort = 0;
        $seen = [];
        foreach ($mediaIds as $mediaId) {
            $id = (int) $mediaId;
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }
            if (! Media::query()->whereKey($id)->exists()) {
                continue;
            }
            $seen[$id] = true;
            $model->mediaAttachments()->create([
                'media_id' => $id,
                'role' => 'gallery',
                'sort' => $sort++,
            ]);
        }
    }

    /**
     * Gallery cho Admin API — [{ id, media }].
     *
     * @return list<array{id: int, media: array<string, mixed>|null}>
     */
    public function adminGalleryPayload(Model $model, string $variant = 'card'): array
    {
        if (! method_exists($model, 'mediaAttachments')) {
            return [];
        }

        $attachments = $model->relationLoaded('mediaAttachments')
            ? $model->mediaAttachments->where('role', 'gallery')->values()
            : $model->mediaAttachments()->where('role', 'gallery')->with('media')->orderBy('sort')->get();

        return $attachments->map(fn ($att) => [
            'id' => (int) $att->id,
            'media' => $this->adminMediaPayload($att->media, $variant),
        ])->values()->all();
    }

    /**
     * Gallery admin cho chỗ nghỉ — chỉ media_attachments (GCS), đồng bộ từ gallery_media_ids nếu cần.
     *
     * @return list<array{id: int, media: array<string, mixed>|null}>
     */
    public function adminStayGalleryPayload(Model $model, string $variant = 'card'): array
    {
        $this->hydrateStayGalleryAttachments($model);

        return $this->adminGalleryPayload($model, $variant);
    }

    /**
     * Gắn lại gallery attachments từ media_id đã upload (attrs.gallery_media_ids / attrs.photos).
     */
    public function hydrateStayGalleryAttachments(Model $model): void
    {
        if (! method_exists($model, 'mediaAttachments')) {
            return;
        }

        $attrs = is_array($model->attrs ?? null) ? $model->attrs : [];
        $photos = is_array($attrs['photos'] ?? null) ? $attrs['photos'] : [];
        $mediaIds = is_array($attrs['gallery_media_ids'] ?? null)
            ? array_values(array_filter(array_map('intval', $attrs['gallery_media_ids'])))
            : [];

        if ($mediaIds === []) {
            $mediaIds = array_values(array_filter(array_map(
                fn ($photo) => is_array($photo) ? (int) ($photo['media_id'] ?? 0) : 0,
                $photos,
            )));
        }

        if ($mediaIds === []) {
            return;
        }

        $attachedCount = $model->relationLoaded('mediaAttachments')
            ? $model->mediaAttachments->where('role', 'gallery')->count()
            : $model->mediaAttachments()->where('role', 'gallery')->count();

        if ($attachedCount >= count($mediaIds)) {
            return;
        }

        $this->syncGalleryMediaIds($model, $mediaIds);
        $model->unsetRelation('mediaAttachments');
    }

    private function normalizeStayPhotoUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        return preg_replace('/-(thumb|card|sm|md|lg|xl)(\.[a-z0-9]+)$/i', '$2', $path) ?: $path;
    }

    /** Gắn / thay / xóa ảnh trên cột FK (banner_media_id, …). */
    public function syncDirectMediaId(Model $model, string $column, ?int $mediaId, bool $remove = false): void
    {
        $currentId = $model->{$column} ?? null;
        $current = $currentId ? Media::query()->find($currentId) : null;

        if ($remove) {
            $model->{$column} = null;
            $model->save();
            $this->deleteMediaIfOrphan($current);

            return;
        }

        if (! $mediaId) {
            return;
        }

        if ($current && (int) $current->id === $mediaId) {
            return;
        }

        if (! Media::query()->whereKey($mediaId)->exists()) {
            return;
        }

        $model->{$column} = $mediaId;
        $model->save();
        $this->deleteMediaIfOrphan($current);
    }

    /** Giới hạn upload thực tế (KB) = min(config, PHP upload_max_filesize). */
    public function effectiveUploadMaxKb(): int
    {
        $configKb = (int) config('media.max_upload_kb', 5120);
        $phpKb = $this->phpIniSizeToKb((string) ini_get('upload_max_filesize'));

        return max(100, min($configKb, $phpKb > 0 ? $phpKb : $configKb));
    }

    /** Giới hạn upload video (KB) = min(config video, PHP upload_max_filesize). */
    public function effectiveVideoUploadMaxKb(): int
    {
        $configKb = (int) config('media.max_video_upload_kb', 1048576);
        $phpKb = $this->phpIniSizeToKb((string) ini_get('upload_max_filesize'));

        return max(100, min($configKb, $phpKb > 0 ? $phpKb : $configKb));
    }

    protected function phpIniSizeToKb(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024,
            'm' => $number * 1024,
            'k' => $number,
            default => ((float) $value) / 1024,
        };
    }

    /**
     * Folder whitelist cho Admin upload (key → path config/media.php).
     *
     * @return array<string, string>
     */
    public function adminFolderMap(): array
    {
        return [
            'packages' => (string) config('media.packages'),
            'tour_categories' => (string) config('media.tour_categories'),
            'cruise_types' => (string) config('media.cruise_types'),
            'countries' => (string) config('media.countries'),
            'service_categories' => (string) config('media.service_categories'),
            'services' => (string) config('media.services'),
            'home_slider' => (string) config('media.home_slider', 'vitravel/home-slider'),
            'home_sections' => (string) config('media.home_sections', 'vitravel/home-sections'),
            'articles' => (string) config('media.articles'),
            'team' => (string) config('media.team'),
            'reviews' => (string) config('media.reviews'),
            'videos' => (string) config('media.videos', config('media.folder', 'vitravel/images')),
            'video_files' => (string) config('media.video_files', 'vitravel/video-files'),
            'company' => (string) config('media.company', config('media.folder', 'vitravel/images')),
            'default' => (string) config('media.folder', 'vitravel/images'),
        ];
    }
}
