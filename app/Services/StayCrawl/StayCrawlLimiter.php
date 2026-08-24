<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class StayCrawlLimiter
{
    public static function run(
        Closure $callback,
        int $maxConcurrent = 3,
        int $blockSeconds = 120,
        int $lockTtlSeconds = 480,
        ?Closure $onBusyCallback = null
    ) {
        $maxConcurrent = max(1, (int) config('stay.crawl.max_concurrent_crawlers', $maxConcurrent));

        if (self::isRedisAvailable()) {
            try {
                $executed = false;
                $result = null;

                Redis::funnel('stay_crawl:concurrency_limiter')
                    ->limit($maxConcurrent)
                    ->block($blockSeconds)
                    ->then(function () use ($callback, &$executed, &$result) {
                        $executed = true;
                        $result = $callback();
                    }, function () use ($onBusyCallback, &$executed, &$result) {
                        $executed = true;
                        if ($onBusyCallback) {
                            $result = $onBusyCallback();
                        }
                    });

                if ($executed) {
                    return $result;
                }
            } catch (Throwable $e) {
                Log::warning('StayCrawlLimiter: Redis funnel failed, falling back to cache slots: ' . $e->getMessage());
            }
        }

        return self::runWithSlotLocks($callback, $maxConcurrent, $blockSeconds, $lockTtlSeconds, $onRusyCallback);
    }

    private static function runWithSlotLocks(
        Closure $callback,
        int $maxConcurrent,
        int $blockSeconds,
        int $lockTtlSeconds,
        ?Closure $onBusyCallback
    ) {
        $startTime = time();
        $acquiredSlot = null;
        $acquiredLock = null;

        while (true) {
            for ($slot = 1; $slot <= $maxConcurrent; $slot++) {
                try {
                    $lockKey = "stay_crawl_slot_lock_{$slot}";
                    $lock = Cache::lock($lockKey, $lockTtlSeconds);

                    if ($lock->get()) {
                        $acquiredSlot = $slot;
                        $acquiredLock = $lock;
                        break 2;
                    }
                } catch (Throwable $e) {
                }
            }

            if ((time() - $startTime) >= $blockSeconds) {
                break;
            }

            usleep(1500000);
        }

        if ($acquiredLock !== null) {
            try {
                return $callback();
            } finally {
                try {
                    $acquiredLock->release();
                } catch (Throwable) {
                }
            }
        }

        if ($onBusyCallback) {
            return $onBusyCallback();
        }

        Log::warning("StayCrawlLimiter: Da dat gioi han toi da {$maxConcurrent} luong dong thoi.");
        return null;
    }

    public static function isRedisAvailable(): bool
    {
        if (! class_exists(Redis::class)) {
            return false;
        }

        try {
            Redis::connection()->ping();
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
