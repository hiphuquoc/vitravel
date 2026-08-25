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
        return $this->readiness()['ready'];
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
            'progress_stream_path' => (string) ($options['progress_stream_path'] ?? ''),
        ];
        file_put_contents($inputFile, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));

        $timeout = $browserTimeoutSec + 40;
        // Gallery download can take longer (N images over Chrome session).
        if ($wantDownload) {
            $timeout += min(300, (int) ceil(((int) $payload['max_images']) * 1.5));
        }

        // Tự động quét dọn các thư mục proc-* Chrome cũ không còn sử dụng
        $this->sweepStaleProfiles();

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
            $this->nodeProcessEnv(),
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

    /**
     * Tự động dọn dẹp các thư mục proc-* Chrome cũ hơn 10 phút.
     */
    public function sweepStaleProfiles(): void
    {
        $profileDir = storage_path('app/stay-crawl-chrome-profile');
        if (! is_dir($profileDir)) {
            return;
        }
        $now = time();
        $dirs = glob($profileDir . DIRECTORY_SEPARATOR . 'proc-*', GLOB_ONLYDIR);
        if (is_array($dirs)) {
            foreach ($dirs as $dir) {
                $mtime = filemtime($dir);
                if ($mtime !== false && ($now - $mtime > 600)) {
                    $this->cleanupDir($dir);
                }
            }
        }
    }

    public function cleanupDir(?string $dir): void
    {
        if ($dir === null || $dir === '' || ! $this->safeIsDir($dir)) {
            return;
        }
        try {
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
        } catch (\Throwable) {
            // ignore
        }
    }

    public function cleanupImagesDir(?string $dir): void
    {
        if ($dir === null || $dir === '' || ! $this->safeIsDir($dir)) {
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

    /**
     * Đưa biến .env Laravel sang process Node (FPM/CLI không export sẵn STAY_CRAWL_*).
     *
     * @return array<string, string>
     */
    private function nodeProcessEnv(): array
    {
        $env = [];
        foreach ($_SERVER as $key => $value) {
            if (is_string($key) && is_string($value) && $key !== '' && $key !== 'argv') {
                $env[$key] = $value;
            }
        }
        foreach ($_ENV as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $env[$key] = $value;
            }
        }

        $chrome = trim((string) config('stay.crawl.chrome_bin', ''));
        if ($chrome !== '') {
            $env['STAY_CRAWL_CHROME'] = $chrome;
        }
        $userData = trim((string) config('stay.crawl.user_data_dir', ''));
        if ($userData !== '') {
            $env['STAY_CRAWL_USER_DATA_DIR'] = $userData;
        }
        $node = trim((string) config('stay.crawl.node_bin', ''));
        if ($node !== '') {
            $env['STAY_CRAWL_NODE'] = $node;
        }
        // HOME của www — Puppeteer cache / crash dumps
        if (empty($env['HOME'])) {
            $home = getenv('HOME');
            if (is_string($home) && $home !== '' && $this->safeIsDir($home)) {
                $env['HOME'] = $home;
            } elseif ($this->safeIsDir('/home/phupv')) {
                $env['HOME'] = '/home/phupv';
            } elseif ($this->safeIsDir('/www')) {
                $env['HOME'] = '/www';
            } else {
                $env['HOME'] = '/tmp';
            }
        }

        // Tự động chuyển tiếp các biến GUI WSLg/X11 sang Node khi chạy headed (STAY_CRAWL_HEADLESS=false)
        if (empty($env['DISPLAY'])) {
            $env['DISPLAY'] = getenv('DISPLAY') ?: ':0';
        }
        if (empty($env['WAYLAND_DISPLAY'])) {
            $env['WAYLAND_DISPLAY'] = getenv('WAYLAND_DISPLAY') ?: 'wayland-0';
        }
        if (empty($env['XDG_RUNTIME_DIR'])) {
            $xdg = getenv('XDG_RUNTIME_DIR');
            if (is_string($xdg) && $xdg !== '' && $this->safeIsDir($xdg)) {
                $env['XDG_RUNTIME_DIR'] = $xdg;
            } elseif ($this->safeIsDir('/run/user/1000')) {
                $env['XDG_RUNTIME_DIR'] = '/run/user/1000';
            } else {
                $env['XDG_RUNTIME_DIR'] = '/tmp';
            }
        }
        if (empty($env['PULSE_SERVER'])) {
            $env['PULSE_SERVER'] = getenv('PULSE_SERVER') ?: 'unix:/mnt/wslg/PulseServer';
        }
        if (empty($env['WSL2_GUI_APPS_ENABLED'])) {
            $env['WSL2_GUI_APPS_ENABLED'] = '1';
        }

        return $env;
    }

    public function findNode(): ?string
    {
        $configured = trim((string) config('stay.crawl.node_bin', ''));
        // .env tuyệt đối: tin tưởng — is_executable() bị open_basedir aaPanel chặn (/usr/bin ngoài allowed).
        if ($configured !== '' && str_starts_with($configured, '/')) {
            return $configured;
        }

        $candidates = array_values(array_filter([
            $configured !== '' ? $configured : null,
            '/usr/bin/node',
            '/usr/local/bin/node',
            '/opt/nodejs/bin/node',
            '/www/server/nodejs/bin/node',
        ]));

        // aaPanel: /www/server/nodejs/v20.x.x/bin/node (glob cũng có thể bị open_basedir)
        foreach (@glob('/www/server/nodejs/*/bin/node') ?: [] as $path) {
            $candidates[] = $path;
        }

        $seen = [];
        foreach ($candidates as $path) {
            if (! is_string($path) || $path === '' || isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            if ($this->binaryUsable($path)) {
                return $path;
            }
        }

        $which = trim((string) @shell_exec('command -v node 2>/dev/null'));
        if ($which !== '' && $this->binaryUsable($which)) {
            return $which;
        }

        // open_basedir: không kiểm tra được /usr/bin/node → vẫn trả candidate mặc định Linux
        if ($this->openBasedirActive() && is_file(base_path('scripts/stay-crawl/browser.cjs'))) {
            return '/usr/bin/node';
        }

        Log::warning('StayCrawlBrowser: không tìm thấy node');

        return null;
    }

    /**
     * Kiểm tra binary chạy được. Với open_basedir, is_executable() ngoài allowed path
     * ném warning / luôn false dù file tồn tại (aaPanel: chỉ /www/wwwroot/site + /tmp).
     */
    private function binaryUsable(string $path): bool
    {
        if ($path === '' || ! str_starts_with($path, '/')) {
            return false;
        }

        $ok = false;
        set_error_handler(static fn () => true);
        try {
            $ok = is_executable($path);
        } finally {
            restore_error_handler();
        }
        if ($ok) {
            return true;
        }

        if (! $this->openBasedirActive()) {
            return false;
        }

        // Path nằm ngoài open_basedir → PHP không đọc được; tin path hệ thống / nodejs aaPanel.
        if ($this->pathInsideOpenBasedir($path)) {
            return false;
        }

        return str_ends_with($path, '/node')
            || str_contains($path, '/nodejs/')
            || str_contains($path, '/chrome');
    }

    /**
     * Kiểm tra thư mục an toàn, tránh open_basedir warning/exception trên aaPanel/Production.
     */
    private function safeIsDir(?string $path): bool
    {
        if (! is_string($path) || $path === '') {
            return false;
        }

        if (! $this->pathInsideOpenBasedir($path)) {
            return false;
        }

        $ok = false;
        set_error_handler(static fn () => true);
        try {
            $ok = is_dir($path);
        } finally {
            restore_error_handler();
        }

        return $ok;
    }

    private function openBasedirActive(): bool
    {
        $basedir = (string) ini_get('open_basedir');

        return $basedir !== '';
    }

    private function pathInsideOpenBasedir(string $path): bool
    {
        $basedir = (string) ini_get('open_basedir');
        if ($basedir === '') {
            return true;
        }
        foreach (explode(PATH_SEPARATOR, $basedir) as $root) {
            $root = rtrim(trim($root), "/\\");
            if ($root !== '' && (str_starts_with($path, $root.'/') || $path === $root)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{ready: bool, node: ?string, puppeteer: bool, script: bool, hint: ?string} */
    public function readiness(): array
    {
        $node = $this->findNode();
        $script = is_file($this->scriptPath());
        $puppeteer = is_dir(base_path('scripts/stay-crawl/node_modules/puppeteer'))
            || is_dir(base_path('scripts/stay-crawl/node_modules/puppeteer-core'));
        $ready = $node !== null && $script && $puppeteer;
        $hint = null;
        if (! $ready) {
            $parts = [];
            if ($node === null) {
                $parts[] = 'Không tìm thấy Node — đặt STAY_CRAWL_NODE=/usr/bin/node trong .env rồi config:cache'
                    .($this->openBasedirActive()
                        ? ' (open_basedir đang bật: PHP không is_executable được /usr/bin — bắt buộc STAY_CRAWL_NODE).'
                        : '');
            }
            if (! $puppeteer) {
                $parts[] = 'Chưa npm ci: cd scripts/stay-crawl && sudo -u www npm ci';
            }
            if (! $script) {
                $parts[] = 'Thiếu scripts/stay-crawl/browser.cjs';
            }
            $hint = implode(' ', $parts);
        }

        return [
            'ready' => $ready,
            'node' => $node,
            'puppeteer' => $puppeteer,
            'script' => $script,
            'hint' => $hint,
        ];
    }

    private function scriptPath(): string
    {
        return base_path('scripts/stay-crawl/browser.cjs');
    }
}
