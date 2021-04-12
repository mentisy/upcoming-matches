<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Services;

interface ServicesInterface
{
    /**
     * Service constructor.
     *
     * @param \Avolle\UpcomingMatches\Services\ServicesConfig $config The config instance of the Service
     */
    public function __construct(ServicesConfig $config);

    /**
     * Get the Service's config
     *
     * @return \Avolle\UpcomingMatches\Services\ServicesConfig
     */
    public function getConfig(): ServicesConfig;

    /**
     * Fetch data from the API. Will use cache if enabled.
     * If cache enabled and a cached result exists, it will read from cache.
     * Otherwise the data is fetched using the protected variant of this method and then stored to cache (if enabled)
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @throws \Avolle\UpcomingMatches\Exception\InvalidResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function fetch(string $dateFrom, string $dateTo): void;

    /**
     * The method that converts the API endpoint result into an array of entities to use in rendering/output
     *
     * @return array
     */
    public function toArray(): array;
}
