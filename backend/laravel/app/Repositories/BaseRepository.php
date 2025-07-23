<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Cache;

class BaseRepository
{
    const RESPONSE_TYPE_PAGINATE = 'paginate';
    const RESPONSE_TYPE_EXPORT = 'export';
    const RESPONSE_TYPE_COUNT = 'count';

    /**
     * @param string $key
     * @param callable $callable
     * @param bool $clear_cache
     * @return mixed
     */
    protected function cacheForever(string $key, callable $callable, bool $clear_cache = false)
    {
        if (! app()->environment('production') || $clear_cache) {
            Cache::forget($key);
        }

        return Cache::rememberForever($key, $callable);
    }

    /**
     * @param string $key
     * @param callable $callable
     * @param \DateTimeInterface|\DateInterval|int $ttl
     * @return mixed
     */
    protected function remember(string $key, callable $callable, $ttl = 1800, bool $clear_cache = false)
    {
        if (! app()->environment('production') || $clear_cache) {
            Cache::forget($key);
        }

        return Cache::remember($key, $ttl, $callable);
    }
}
