<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\Sitemap\SitemapGenerator;
use App\Support\ProjectContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SitemapController extends Controller
{
    public function __construct(private readonly SitemapGenerator $generator) {}

    public function index(): Response
    {
        return $this->serve('sitemap.xml');
    }

    /** /sitemap/{language}.xml — index types của 1 locale */
    public function language(string $language): Response
    {
        return $this->serve('sitemap/'.$language.'.xml');
    }

    /** /sitemap/{language}/{name}.xml — pages | type | type-N */
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
        $project = $this->resolveProject();
        if (! $project instanceof Project) {
            abort(404, 'Sitemap: chưa resolve được project từ Host.');
        }

        $storagePath = $this->generator->storagePathFor($project, $relativePath);
        $disk = Storage::disk((string) config('sitemap.disk', 'local'));

        if (! $disk->exists($storagePath)) {
            abort(404, 'Sitemap chưa generate hoặc thiếu file: '.$storagePath
                .' — chạy: php artisan sitemap:generate --project='.$project->code);
        }

        $content = $disk->get($storagePath);
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

    private function resolveProject(): ?Project
    {
        $project = ProjectContext::get();
        if ($project instanceof Project) {
            return $project;
        }

        // Fallback nếu middleware chưa set (hiếm) — vẫn theo Host
        $host = strtolower((string) request()->getHost());
        $host = preg_replace('/:\d+$/', '', $host) ?: $host;
        if ($host !== '') {
            $byDomain = Project::query()
                ->active()
                ->whereHas('domains', fn ($q) => $q->where('domain', $host))
                ->first();
            if ($byDomain) {
                ProjectContext::set($byDomain);

                return $byDomain;
            }
        }

        $defaultCode = trim((string) config('project.default_code', ''));
        if ($defaultCode !== '') {
            $byCode = Project::query()->active()->where('code', $defaultCode)->first();
            if ($byCode) {
                ProjectContext::set($byCode);

                return $byCode;
            }
        }

        $first = Project::query()->active()->orderBy('id')->first();
        if ($first) {
            ProjectContext::set($first);
        }

        return $first;
    }
}
