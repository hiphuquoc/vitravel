<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Chrome/Puppeteer fetch — cùng kiểu crawler doanh nghiệp hoptackinhdoanh.dev.
 */
final class StayCrawlBrowser
{
    public function isReady(): bool
    {
        return $this->findNode() !== null
            && is_file($this->scriptPath())
            && (
                is_dir(base_path('scripts/stay-crawl/node_modules/puppeteer'))
                || is_dir(base_path('scripts/stay-crawl/node_modules/puppeteer-core'))
            );
    }

    /**
     * @param  array{mode?: string, skip_html?: bool, room_index?: int|null, download_images?: bool}  $options
     * @return array{ok: bool, status: int, html: string, pack: array<string, mixed>, final_url: string, blocked: bool, reason: ?string, driver: string, images_dir: ?string}
     */
    public function fetch(string $url, bool $useProxy = false, array $options = []): array
    {
        if (! $this->isReady()) {
            throw new RuntimeException(
                'Chưa cài crawler Chrome. Chạy: cd scripts/stay-crawl && npm ci'
            );
        }

        $node = $this->findNode();
        if ($node === null) {
            throw new RuntimeException('Không tìm thấy Node.js để chạy crawler Chrome.');
        }

        $tmp = storage_path('app/tmp');
        if (! is_dir($tmp)) {
            mkdir($tmp, 0775, true);
        }

        $inputFile = tempnam($tmp, 'stay_crawl_in_');
        $outputFile = tempnam($tmp, 'stay_crawl_out_');
        chmod($inputFile, 0664);
        chmod($outputFile, 0664);

        $mode = (string) ($options['mode'] ?? 'basic');
        $wantDownload = in_array($mode, ['gallery', 'room'], true)
            && ($options['download_images'] ?? true);
        $imagesDir = null;
        if ($wantDownload) {
            $imagesDir = $tmp.DIRECTORY_SEPARATOR.'stay_crawl_img_'.bin2hex(random_bytes(8));
            mkdir($imagesDir, 0775, true);
        }

        $browserTimeoutSec = max(60, (int) config('stay.crawl.browser_timeout', 180));
        $isListingUrl = $mode === 'list' || ! preg_match('#/hotel/[a-z]{2}/#i', $url);
        if ($isListingUrl) {
            $browserTimeoutSec += max(60, (int) config('stay.crawl.list_browser_extra_sec', 240));
        }

        $payload = [
            'url' => $url,
            'timeout' => $browserTimeoutSec * 1000,
            'proxy' => $useProxy ? $this->proxyConfig() : null,
            'mode' => $isListingUrl ? 'list' : $mode,
            'skip_html' => (bool) ($options['skip_html'] ?? false),
            'room_index' => isset($options['room_index']) ? (int) $options['room_index'] : null,
            'room_name' => (string) ($options['room_name'] ?? ''),
            'room_hash' => (string) ($options['room_hash'] ?? ''),
            'download_images' => $wantDownload,
            'images_dir' => $imagesDir,
            'max_images' => (int) ($options['max_images'] ?? config('stay.crawl.max_images', 120)),
            'download_concurrency' => (int) ($options['download_concurrency'] ?? config('stay.crawl.download_concurrency', 8)),
            'headless' => array_key_exists('headless', $options)
                ? (bool) $options['headless']
                : (bool) config('stay.crawl.headless', true),
            'slow_mo' => (int) ($options['slow_mo'] ?? config('stay.crawl.slow_mo', 0)),
        ];
        file_put_contents($inputFile, json_encode($payload, JSON_UNESCAPED_SLASHES));

        $timeout = $browserTimeoutSec + 40;
        // Gallery download can take longer (N images over Chrome session).
        if ($wantDownload) {
            $timeout += min(300, (int) ceil(((int) $payload['max_images']) * 1.5));
        }

        // Nhả SingletonLock nếu Chrome bước trước chết bất thường (hay gặp sau basic → gallery).
        $profileDir = storage_path('app/stay-crawl-chrome-profile');
        foreach (['SingletonLock', 'SingletonSocket', 'SingletonCookie', 'lockfile'] as $lockName) {
            $lockPath = $profileDir.DIRECTORY_SEPARATOR.$lockName;
            if (is_file($lockPath) || is_link($lockPath)) {
                @unlink($lockPath);
            }
        }

        $process = new Process(
            [$node, $this->scriptPath(), $inputFile, $outputFile],
            base_path('scripts/stay-crawl'),
            null,
            null,
            $timeout,
        );

        try {
            $process->run();
            $raw = is_file($outputFile) ? (string) file_get_contents($outputFile) : '';
            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                $stderr = trim($process->getErrorOutput().' '.$process->getOutput());
                throw new RuntimeException(
                    'Crawler Chrome không trả JSON. '.($stderr !== '' ? substr($stderr, 0, 400) : 'Kiểm tra puppeteer.')
                );
            }
            $htmlSidecar = $outputFile.'.html';
            $packSidecar = $outputFile.'.pack.json';
            $html = is_file($htmlSidecar) ? (string) file_get_contents($htmlSidecar) : '';
            if ($html === '') {
                $html = (string) ($decoded['html'] ?? '');
            }
            $pack = [];
            if (is_file($packSidecar)) {
                $fromFile = json_decode((string) file_get_contents($packSidecar), true);
                $pack = is_array($fromFile) ? $fromFile : [];
            }
            if ($pack === [] && is_array($decoded['pack'] ?? null)) {
                $pack = $decoded['pack'];
            }
            if (! empty($decoded['error']) && $html === '' && $pack === []) {
                throw new RuntimeException((string) $decoded['error']);
            }
            $max = (int) config('stay.crawl.max_html_bytes', 6_000_000);
            if (strlen($html) > $max) {
                $html = substr($html, 0, $max);
            }

            $status = (int) ($decoded['status_code'] ?? 0);
            $hasPayload = $html !== '' || $pack !== [];

            return [
                'ok' => $hasPayload && $status < 500,
                'status' => $status ?: ($hasPayload ? 200 : 0),
                'html' => $html,
                'pack' => $pack,
                'final_url' => (string) ($decoded['final_url'] ?? $url),
                'blocked' => $status === 403 || $status === 429,
                'reason' => $hasPayload ? null : 'empty_html',
                'driver' => 'browser',
                'images_dir' => $imagesDir,
            ];
        } catch (\Throwable $e) {
            $this->cleanupImagesDir($imagesDir);
            throw $e;
        } finally {
            @unlink($inputFile);
            @unlink($outputFile);
            @unlink($outputFile.'.html');
            @unlink($outputFile.'.pack.json');
        }
    }

    public function cleanupImagesDir(?string $dir): void
    {
        if ($dir === null || $dir === '' || ! is_dir($dir)) {
            return;
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($files as $file) {
            $path = $file->getPathname();
            if ($file->isDir()) {
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    /** @return array{host: string, port: int|string, username: ?string, password: ?string}|null */
    public function proxyConfig(): ?array
    {
        $host = trim((string) config('stay.crawl.proxy.host', ''));
        $port = trim((string) config('stay.crawl.proxy.port', ''));
        if ($host === '' || $port === '') {
            return null;
        }

        return [
            'host' => $host,
            'port' => $port,
            'username' => config('stay.crawl.proxy.username') ?: null,
            'password' => config('stay.crawl.proxy.password') ?: null,
        ];
    }

    public function proxyConfigured(): bool
    {
        return $this->proxyConfig() !== null;
    }

    public function findNode(): ?string
    {
        $configured = trim((string) config('stay.crawl.node_bin', ''));
        $candidates = array_filter([
            $configured,
            '/usr/bin/node',
            '/usr/local/bin/node',
            '/opt/nodejs/bin/node',
        ]);
        foreach ($candidates as $path) {
            if (is_string($path) && $path !== '' && is_executable($path)) {
                return $path;
            }
        }

        $which = trim((string) shell_exec('command -v node 2>/dev/null'));
        if ($which !== '' && is_executable($which)) {
            return $which;
        }

        Log::warning('StayCrawlBrowser: không tìm thấy node');

        return null;
    }

    private function scriptPath(): string
    {
        return base_path('scripts/stay-crawl/browser.cjs');
    }
}
