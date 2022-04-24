<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Services;

use Avolle\UpcomingMatches\Exception\InvalidResponseException;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Symfony\Contracts\Cache\ItemInterface;

abstract class Service implements ServicesInterface
{
    use CacheTrait;

    protected ServicesConfig $config;
    protected Client $client;
    protected ResponseInterface $result;
    protected array $results = [];
    protected bool $useCache = false;

    /**
     * Service constructor.
     *
     * @param \Avolle\UpcomingMatches\Services\ServicesConfig $config The config instance of the Service
     */
    public function __construct(ServicesConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Get the Service instance's config
     *
     * @return \Avolle\UpcomingMatches\Services\ServicesConfig
     */
    public function getConfig(): ServicesConfig
    {
        return $this->config;
    }

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
    public function fetch(string $dateFrom, string $dateTo): void
    {
        if (!$this->useCache) {
            $this->_fetch($dateFrom, $dateTo);

            return;
        }
        $this->results = $this->cache()->get(
            $this->cacheKey($dateFrom, $dateTo),
            function (ItemInterface $item) use ($dateFrom, $dateTo) {
                $item->expiresAfter(24 * 60 * 60);
                $this->_fetch($dateFrom, $dateTo);

                return $this->results;
            }
        );
    }

    /**
     * Enables caching of results
     *
     * @return self
     */
    public function useCache(): self
    {
        $this->useCache = true;

        return $this;
    }

    /**
     * Will actually fetch the data from the API, using the ServiceConfig's parameters combined with date period fields
     *
     * @param string $dateFrom Start date to get matches for
     * @param string $dateTo End date to get matches for
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Avolle\UpcomingMatches\Exception\InvalidResponseException
     * @return void
     */
    private function _fetch(string $dateFrom, string $dateTo): void
    {
        $client = $this->client();

        $configParams = $this->config->params;
        if ($this->isMultiResource($configParams)) {
            $params = $configParams;
        } else {
            $params[0] = $this->config->params;
        }

        foreach ($params as $paramsSet) {
            $result = $client->request('GET', $this->config->apiUri, [
                'query' => $this->buildParams($paramsSet, $dateFrom, $dateTo),
            ]);
            if ($result->getStatusCode() !== 200) {
                throw new InvalidResponseException();
            }
            $this->results[] = $result->getBody()->getContents();
        }
    }

    /**
     * Creates a HTTP Client on which to make requests
     *
     * @return \GuzzleHttp\Client
     */
    protected function client(): Client
    {
        return $this->client ?? new Client();
    }

    /**
     * Responsible for merging the ServiceConfig's extra parameters with the required `from` and `to` date fields
     * The fields to use in the query string for selecting the date period is decided by the ServiceConfig instance
     *
     * @param array $params Params to merge with dates
     * @param string $dateFrom Start date to get matches for
     * @param string $dateTo End date to get matches for
     * @return string Converted query string
     */
    protected function buildParams(array $params, string $dateFrom, string $dateTo): string
    {
        $dateParams = [
            $this->config->dateFields['from'] => $dateFrom,
            $this->config->dateFields['to'] => $dateTo,
        ];
        $params = $params + $dateParams;

        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Should we fetch more than one result from the API. If the params parameter contains more than one array,
     * we should fetch matches from more than one resource.
     *
     * @param array|array[] $params Parameters to use in request body. Either an array of keys or an array of arrays of keys
     * @return bool
     */
    protected function isMultiResource(array $params): bool
    {
        return isset($params[0]) && is_array($params[0]);
    }
}
