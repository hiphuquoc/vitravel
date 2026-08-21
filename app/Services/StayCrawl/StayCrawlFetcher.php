<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Fetch chỗ nghỉ: Chrome (Puppeteer) mặc định — HTTP chỉ fallback.
 * Proxy tuỳ chọn (STAY_CRAWL_PROXY_*).
 */
final class StayCrawlFetcher
{
    public function __construct(private readonly StayCrawlBrowser $browser) {}

    /**
     * @return array{ok: bool, status: int, html: string, pack: array<string, mixed>, final_url: string, blocked: bool, reason: ?string, driver: string}
     */
    public function fetch(string $url, bool $respectRobots = false, bool $useProxy = false, array $options = []): array
    {
        $driver = (string) config('stay.crawl.driver', 'browser');

        if ($useProxy && ! $this->browser->proxyConfigured()) {
            throw new RuntimeException(
                'Đã bật proxy nhưng chưa cấu hình STAY_CRAWL_PROXY_HOST / STAY_CRAWL_PROXY_PORT trong .env.'
            );
        }

        if ($driver === 'http' && $respectRobots && ! $this->robotsAllows($url)) {
            return [
                'ok' => false,
                'status' => 0,
                'html' => '',
                'pack' => [],
                'final_url' => $url,
                'blocked' => true,
                'reason' => 'robots_disallow',
                'driver' => 'http',
                'images_dir' => null,
            ];
        }

        $delay = (int) config('stay.crawl.delay_ms', 1500);
        if ($delay > 0) {
            usleep($delay * 1000);
        }

        if ($driver !== 'http') {
            try {
                $result = $this->browser->fetch($url, $useProxy, $options);
                if ($result['html'] !== '' || ($result['pack'] ?? []) !== []) {
                    return $result;
                }
            } catch (RuntimeException $e) {
                if ($driver === 'browser') {
                    throw $e;
                }
            }
        }

        return $this->fetchViaHttp($url, $useProxy);
    }

    /**
     * @return array{ok: bool, status: int, html: string, final_url: string, blocked: bool, reason: ?string, driver: string}
     */
    private function fetchViaHttp(string $url, bool $useProxy): array
    {
        $cfg = config('stay.crawl', []);
        $request = Http::timeout((int) ($cfg['timeout'] ?? 35))
            ->withHeaders([
                'User-Agent' => (string) ($cfg['browser_user_agent'] ?? $cfg['user_agent'] ?? 'Mozilla/5.0'),
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => (string) ($cfg['accept_language'] ?? 'vi-VN,vi;q=0.9,en;q=0.8'),
            ])
            ->withOptions(['allow_redirects' => ['max' => 5]]);

        if ($useProxy) {
            $proxy = $this->browser->proxyConfig();
            if ($proxy) {
                $auth = ($proxy['username'] ?? '') !== ''
                    ? rawurlencode((string) $proxy['username']).':'.rawurlencode((string) ($proxy['password'] ?? '')).'@'
                    : '';
                $request = $request->withOptions([
                    'proxy' => 'http://'.$auth.$proxy['host'].':'.$proxy['port'],
                ]);
            }
        }

        $response = $request->get($url);
        $html = (string) $response->body();
        $max = (int) ($cfg['max_html_bytes'] ?? 1_800_000);
        if (strlen($html) > $max) {
            $html = substr($html, 0, $max);
        }

        $status = $response->status();
        $blocked = $status === 403 || $status === 429 || $status >= 500;

        return [
            'ok' => $response->successful(),
            'status' => $status,
            'html' => $html,
            'pack' => [],
            'final_url' => (string) $response->effectiveUri(),
            'blocked' => $blocked,
            'reason' => $blocked ? 'http_'.$status : null,
            'driver' => 'http',
            'images_dir' => null,
        ];
    }

    public function robotsAllows(string $url): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['host'])) {
            return false;
        }
        $origin = ($parts['scheme'] ?? 'https').'://'.$parts['host'];
        $path = (string) ($parts['path'] ?? '/');
        $ua = (string) config('stay.crawl.user_agent', 'ViTravelStayBot/1.0');
        $txt = $this->robotsTxt($origin);
        if ($txt === '') {
            return true;
        }

        $groups = $this->parseRobots($txt);
        $rules = $groups['*'] ?? [];
        foreach ($groups as $agent => $agentRules) {
            if ($agent !== '*' && stripos($ua, $agent) !== false) {
                $rules = $agentRules;
                break;
            }
        }

        $allowed = true;
        foreach ($rules as $rule) {
            [$type, $prefix] = $rule;
            if ($prefix !== '' && str_starts_with($path, $prefix)) {
                $allowed = $type === 'allow';
            }
        }

        return $allowed;
    }

    private function robotsTxt(string $origin): string
    {
        $key = 'stay_crawl_robots:'.md5($origin);
        try {
            return Cache::remember($key, 3600, function () use ($origin) {
                $res = Http::timeout(12)
                    ->withHeaders(['User-Agent' => (string) config('stay.crawl.user_agent')])
                    ->get($origin.'/robots.txt');

                return $res->successful() ? (string) $res->body() : '';
            });
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * @return array<string, list<array{0: string, 1: string}>>
     */
    private function parseRobots(string $txt): array
    {
        $groups = [];
        $current = ['*'];
        foreach (preg_split('/\r\n|\r|\n/', $txt) ?: [] as $line) {
            $line = trim(preg_replace('/#.*/', '', $line) ?? '');
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }
            [$k, $v] = array_map('trim', explode(':', $line, 2));
            $key = strtolower($k);
            if ($key === 'user-agent') {
                $ua = strtolower($v);
                $current = [$ua === '' ? '*' : $ua];
                $groups[$current[0]] ??= [];
                continue;
            }
            if ($key === 'disallow' || $key === 'allow') {
                foreach ($current as $ua) {
                    $groups[$ua][] = [$key, $v];
                }
            }
        }

        return $groups;
    }

    public function readFile(string $path): string
    {
        if (! is_readable($path)) {
            throw new RuntimeException('Không đọc được file HTML: '.$path);
        }
        $html = (string) file_get_contents($path);
        $max = (int) config('stay.crawl.max_html_bytes', 1_800_000);
        if (strlen($html) > $max) {
            return substr($html, 0, $max);
        }

        return $html;
    }
}
