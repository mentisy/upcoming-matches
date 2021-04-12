<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Services;

use ReflectionClass;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

trait CacheTrait
{
    /**
     * Cache instance
     *
     * @var \Symfony\Component\Cache\Adapter\FilesystemAdapter
     */
    protected FilesystemAdapter $cache;

    /**
     * Create a cache adapter, enabling caching of results
     *
     * @return \Symfony\Component\Cache\Adapter\FilesystemAdapter
     */
    public function cache(): FilesystemAdapter
    {
        return $this->cache ?? new FilesystemAdapter();
    }

    /**
     * Clear cache
     *
     * @return self
     */
    public function clearCache(): self
    {
        $this->cache()->clear();

        return $this;
    }

    /**
     * Generates a cache key based on the service and date period, ensuring unique cached results
     *
     * @param string $dateFrom The start date to fetch matches for
     * @param string $dateTo The end date to fetch matches for
     * @return string The key to use for storing results in cache
     */
    protected function cacheKey(string $dateFrom, string $dateTo): string
    {
        $className = (new ReflectionClass(static::class))->getShortName();

        return strtolower(sprintf("%s-%s-%s", $className, $dateFrom, $dateTo));
    }
}
