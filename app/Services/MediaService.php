<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

    public function gcsConfigured(): bool
    {
        return filled(config('services.gcs.bucket'))
            && filled(config('services.gcs.key_file'))
            && is_readable((string) config('services.gcs.key_file'));
    }

    public function storeUploadedFile(UploadedFile $file, ?string $folder = null, ?string $disk = null): Media
    {
        $disk ??= $this->defaultDisk();
        $folder = trim($folder ?? config('media.folder', 'vitravel/images'), '/');

        $built = $this->buildOptimizedSet($file);
        $uuid = Str::uuid()->toString();
        $extension = $built['extension'];
        $mime = $built['mime'];

        $full = $built['full'];
        $path = $folder.'/'.$uuid.'.'.$extension;

        Storage::disk($disk)->put($path, $full['binary'], [
            'visibility' => 'public',
            'ContentType' => $mime,
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        $variantMeta = [];
        foreach ($built['variants'] as $name => $variant) {
            $variantPath = $folder.'/'.$uuid.'-'.$name.'.'.$extension;
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

        return Media::query()->create([
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size_bytes' => strlen($full['binary']),
            'width' => $full['width'],
            'height' => $full['height'],
            'alt' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'meta' => ['variants' => $variantMeta],
        ]);
    }

    /**
     * Lưu file video thô (không resize) — mp4 / webm / mov / …
     */
    public function storeUploadedVideo(UploadedFile $file, ?string $folder = null, ?string $disk = null): Media
    {
        $disk ??= $this->defaultDisk();
        $folder = trim($folder ?? config('media.video_files', 'vitravel/video-files'), '/');

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

        $media->delete();
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
            $this->deleteMedia($existing->media);
            $existing->delete();
            $existing = null;
        }

        if (! $request->hasFile($fileField)) {
            return;
        }

        if ($existing) {
            $this->deleteMedia($existing->media);
            $existing->delete();
        }

        $media = $this->storeUploadedFile($request->file($fileField), $folder);

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

        $media = $this->storeUploadedFile($request->file($fileField), $folder);
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
                $this->deleteMedia($existing->media);
                $existing->delete();
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
            $this->deleteMedia($existing->media);
            $existing->delete();
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

    /** Gắn / thay / xóa ảnh trên cột FK (banner_media_id, …). */
    public function syncDirectMediaId(Model $model, string $column, ?int $mediaId, bool $remove = false): void
    {
        $currentId = $model->{$column} ?? null;
        $current = $currentId ? Media::query()->find($currentId) : null;

        if ($remove) {
            if ($current) {
                $this->deleteMedia($current);
            }
            $model->{$column} = null;
            $model->save();

            return;
        }

        if (! $mediaId) {
            return;
        }

        if ($current && (int) $current->id === $mediaId) {
            return;
        }

        if ($current) {
            $this->deleteMedia($current);
        }

        if (! Media::query()->whereKey($mediaId)->exists()) {
            return;
        }

        $model->{$column} = $mediaId;
        $model->save();
    }

    /** Giới hạn upload thực tế (KB) = min(config, PHP upload_max_filesize). */
    public function effectiveUploadMaxKb(): int
    {
        $configKb = (int) config('media.max_upload_kb', 5120);
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
            'company' => (string) config('media.company', config('media.folder', 'vitravel/images')),
            'default' => (string) config('media.folder', 'vitravel/images'),
        ];
    }
}
