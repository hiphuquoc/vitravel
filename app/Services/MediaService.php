<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $optimized = $this->optimizeImage($file);
        $extension = $optimized['extension'];
        $binary = $optimized['binary'];
        $mime = $optimized['mime'];
        $width = $optimized['width'];
        $height = $optimized['height'];

        $filename = Str::uuid()->toString().'.'.$extension;
        $path = $folder.'/'.$filename;

        Storage::disk($disk)->put($path, $binary, [
            'visibility' => 'public',
            'ContentType' => $mime,
        ]);

        return Media::query()->create([
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size_bytes' => strlen($binary),
            'width' => $width,
            'height' => $height,
            'alt' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        ]);
    }

    public function publicUrl(?Media $media): ?string
    {
        if (! $media) {
            return null;
        }

        if ($media->disk === 'gcs') {
            $customBase = config('services.gcs.public_url');
            if ($customBase) {
                return rtrim((string) $customBase, '/').'/'.ltrim($media->path, '/');
            }

            $bucket = config('services.gcs.bucket');

            return 'https://storage.googleapis.com/'.$bucket.'/'.ltrim($media->path, '/');
        }

        return Storage::disk($media->disk)->url($media->path);
    }

    public function deleteMedia(?Media $media): void
    {
        if (! $media) {
            return;
        }

        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
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

    public function coverUrl(Model $model): ?string
    {
        if (method_exists($model, 'coverMedia')) {
            return $this->publicUrl($model->coverMedia());
        }

        $attachment = $model->relationLoaded('mediaAttachments')
            ? $model->mediaAttachments->firstWhere('role', 'cover')
            : (method_exists($model, 'mediaAttachments')
                ? $model->mediaAttachments()->where('role', 'cover')->with('media')->first()
                : null);

        return $this->publicUrl($attachment?->media);
    }

    /**
     * Resize (max edge) + re-encode JPEG/WebP for smaller uploads.
     *
     * @return array{binary: string, extension: string, mime: string, width: ?int, height: ?int}
     */
    protected function optimizeImage(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $originalExt = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $maxEdge = (int) config('media.max_edge', 1920);
        $quality = (int) config('media.jpeg_quality', 82);

        if (! function_exists('imagecreatefromstring') || ! is_readable($path)) {
            return $this->fallbackBinary($file, $originalExt);
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return $this->fallbackBinary($file, $originalExt);
        }

        $source = @imagecreatefromstring($raw);
        if ($source === false) {
            return $this->fallbackBinary($file, $originalExt);
        }

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

        $preferWebp = function_exists('imagewebp') && in_array($originalExt, ['jpg', 'jpeg', 'png', 'webp'], true);
        $extension = $preferWebp ? 'webp' : (in_array($originalExt, ['png', 'gif'], true) ? $originalExt : 'jpg');
        $mime = match ($extension) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        ob_start();
        match ($extension) {
            'webp' => imagewebp($canvas, null, $quality),
            'png' => imagepng($canvas, null, 6),
            'gif' => imagegif($canvas),
            default => imagejpeg($canvas, null, $quality),
        };
        $binary = (string) ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        if ($binary === '') {
            return $this->fallbackBinary($file, $originalExt);
        }

        return [
            'binary' => $binary,
            'extension' => $extension,
            'mime' => $mime,
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
}
