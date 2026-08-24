<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

final class StayCrawlImageImporter
{
    public function __construct(private readonly MediaService $media) {}

    /**
     * Ưu tiên file local (Chrome session download), fallback HTTP.
     * Luôn giữ `source_url` (Booking CDN) để re-crawl / audit sau này.
     *
     * @param  list<mixed>  $photos
     * @return list<array{url: string, alt: string, media_id: int|null, source_url?: string}>
     */
    public function importPhotos(array $photos, string $slug, string $role = 'gallery'): array
    {
        $max = (int) config('stay.crawl.max_images', 120);
        $out = [];
        $index = 0;
        foreach ($photos as $photo) {
            if (count($out) >= $max) {
                break;
            }
            $url = '';
            $alt = '';
            $local = '';
            if (is_string($photo)) {
                $url = $photo;
            } elseif (is_array($photo)) {
                $url = (string) ($photo['url'] ?? $photo['src'] ?? '');
                $alt = (string) ($photo['alt'] ?? '');
                $local = (string) ($photo['local_path'] ?? $photo['path'] ?? '');
            }
            $url = trim($url);
            $local = trim($local);
            if ($local === '' && ($url === '' || ! preg_match('#^https?://#i', $url))) {
                continue;
            }
            $index++;
            $media = null;
            if ($local !== '' && is_file($local) && filesize($local) >= 800) {
                $media = $this->storeLocal($local, $slug, $role.'-'.$index, $alt);
            }
            if ($media === null && $url !== '' && preg_match('#^https?://#i', $url)) {
                $media = $this->storeOne($url, $slug, $role.'-'.$index, $alt);
            }
            $row = [
                'url' => $media?->url('lg') ?: $url,
                'alt' => $alt,
                'media_id' => $media?->id,
            ];
            if ($url !== '') {
                $row['source_url'] = $url;
            }
            $out[] = $row;
        }

        return $out;
    }

    private function storeLocal(string $path, string $slug, string $role, string $alt): ?Media
    {
        try {
            $mime = @mime_content_type($path) ?: 'image/jpeg';
            $mime = explode(';', $mime)[0];
            if (! str_starts_with($mime, 'image/')) {
                $mime = 'image/jpeg';
            }
            $ext = match ($mime) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => 'jpg',
            };
            $upload = new UploadedFile($path, Str::slug($role).'.'.$ext, $mime, UPLOAD_ERR_OK, true);
            $media = $this->media->storeUploadedFile(
                $upload,
                'stays/'.(str_starts_with($role, 'cover') ? 'cover' : 'gallery'),
                null,
                $slug,
                $role,
            );
            if ($alt !== '') {
                $media->alt = Str::limit($alt, 255, '');
                $media->save();
            }

            return $media;
        } catch (Throwable) {
            return null;
        }
    }

    private function storeOne(string $url, string $slug, string $role, string $alt): ?Media
    {
        try {
            $response = Http::timeout(35)
                ->withHeaders([
                    'User-Agent' => (string) config('stay.crawl.browser_user_agent', 'Mozilla/5.0'),
                    'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer' => 'https://www.booking.com/',
                ])
                ->get($url);
            if (! $response->successful()) {
                return null;
            }
            $body = $response->body();
            if (strlen($body) < 800) {
                return null;
            }
            $mime = (string) ($response->header('Content-Type') ?: 'image/jpeg');
            $mime = explode(';', $mime)[0];
            if (! str_starts_with($mime, 'image/')) {
                $mime = 'image/jpeg';
            }
            $ext = match ($mime) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => 'jpg',
            };
            $tmp = tempnam(sys_get_temp_dir(), 'stayimg_');
            if ($tmp === false) {
                return null;
            }
            try {
                file_put_contents($tmp, $body);
                $upload = new UploadedFile($tmp, Str::slug($role).'.'.$ext, $mime, UPLOAD_ERR_OK, true);
                $media = $this->media->storeUploadedFile(
                    $upload,
                    'stays/'.($role === 'cover' ? 'cover' : 'gallery'),
                    null,
                    $slug,
                    $role,
                );
                if ($alt !== '') {
                    $media->alt = Str::limit($alt, 255, '');
                    $media->save();
                }

                return $media;
            } finally {
                @unlink($tmp);
            }
        } catch (Throwable) {
            return null;
        }
    }
}
