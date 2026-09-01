<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\Sitemap\SitemapGenerator;
use App\Support\ProjectHostResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToCreateDirectory;

class SitemapController extends Controller
{
    public function __construct(private readonly SitemapGenerator $generator) {}

    public function index(): Response
    {
        return $this->serve('sitemap.xml');
    }

    public function language(string $language): Response
    {
        return $this->serve('sitemap/'.$language.'.xml');
    }

    public function languageFile(string $language, string $name): Response
    {
        return $this->serve('sitemap/'.$language.'/'.$name.'.xml');
    }

    public function robots(Request $request): Response
    {
        $base = rtrim($request->getSchemeAndHttpHost(), '/');
        $body = "User-agent: *\nDisallow:\n\nSitemap: {$base}/sitemap.xml\n";

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age='.(int) config('sitemap.cache_max_age', 3600),
        ]);
    }

    private function serve(string $relativePath): Response
    {
        $project = ProjectHostResolver::resolveFromRequest(request());
        if (! $project instanceof Project) {
            abort(404, 'Sitemap: không resolve được project từ Host.');
        }

        $storagePath = $this->generator->storagePathFor($project, $relativePath);
        $disk = Storage::disk((string) config('sitemap.disk', 'sitemap'));
        $readDisk = $disk;
        $readPath = $storagePath;

        foreach ($this->generator->storagePathCandidates($project, $relativePath) as $candidate) {
            if ($disk->exists($candidate)) {
                $readPath = $candidate;
                $readDisk = $disk;
                break;
            }
        }

        if (! $readDisk->exists($readPath)) {
            foreach ($this->generator->storagePathCandidates($project, $relativePath) as $candidate) {
                $legacyDisk = Storage::disk('local');
                if ($legacyDisk->exists($candidate)) {
                    $readDisk = $legacyDisk;
                    $readPath = $candidate;
                    break;
                }
            }
        }

        if (! $readDisk->exists($readPath)) {
            $readDisk = $disk;
            $readPath = $storagePath;
        }

        if (! $readDisk->exists($readPath) && $relativePath === 'sitemap.xml' && config('sitemap.generate_on_miss', false)) {
            try {
                $lock = Cache::lock('sitemap:generate:'.$project->id, 120);
                if ($lock->get()) {
                    try {
                        $this->generator->generateForProject($project, request()->getSchemeAndHttpHost());
                    } finally {
                        $lock->release();
                    }
                }
            } catch (UnableToCreateDirectory|\RuntimeException $e) {
                Log::warning('sitemap.generate_on_miss_failed', ['message' => $e->getMessage()]);
            } catch (\Throwable $e) {
                Log::warning('sitemap.generate_on_miss_failed', ['message' => $e->getMessage()]);
            }

            foreach ($this->generator->storagePathCandidates($project, $relativePath) as $candidate) {
                if ($disk->exists($candidate)) {
                    $readDisk = $disk;
                    $readPath = $candidate;
                    break;
                }
            }
        }

        if (! $readDisk->exists($readPath)) {
            $hint = 'php artisan sitemap:generate --project='.$project->code;
            if (config('app.debug')) {
                abort(404, "Sitemap thiếu file [{$readPath}] — {$hint}");
            }
            abort(404);
        }

        $content = $readDisk->get($readPath);
        $headers = [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age='.(int) config('sitemap.cache_max_age', 3600),
        ];

        $accept = (string) request()->header('Accept-Encoding', '');
        if (str_contains($accept, 'gzip')) {
            $gzipped = gzencode($content, 6);
            if ($gzipped !== false) {
                return response($gzipped, 200, $headers + [
                    'Content-Encoding' => 'gzip',
                    'Content-Length' => (string) strlen($gzipped),
                    'Vary' => 'Accept-Encoding',
                ]);
            }
        }

        return response($content, 200, $headers);
    }
}
