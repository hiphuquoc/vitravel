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
     * @return array{ok: bool, status: int, html: string, pack: array<string, mixed>, final_url: string, blocked: bool, reason: ?string, driver: string}
     */
    public function fetch(string $url, bool $useProxy = false): array
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

        $payload = [
            'url' => $url,
            'timeout' => (int) config('stay.crawl.browser_timeout', 90) * 1000,
            'proxy' => $useProxy ? $this->proxyConfig() : null,
        ];
        file_put_contents($inputFile, json_encode($payload, JSON_UNESCAPED_SLASHES));

        $timeout = max(60, (int) config('stay.crawl.browser_timeout', 180)) + 40;
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

            return [
                'ok' => $html !== '' && $status < 500,
                'status' => $status ?: ($html !== '' ? 200 : 0),
                'html' => $html,
                'pack' => $pack,
                'final_url' => (string) ($decoded['final_url'] ?? $url),
                'blocked' => $status === 403 || $status === 429,
                'reason' => $html === '' ? 'empty_html' : null,
                'driver' => 'browser',
            ];
        } finally {
            @unlink($inputFile);
            @unlink($outputFile);
            @unlink($outputFile.'.html');
            @unlink($outputFile.'.pack.json');
        }
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
