<?php

namespace Avolle\WeeklyMatches;

use Avolle\WeeklyMatches\Exception\InvalidResponseException;
use Cake\Collection\Collection;
use Cake\Collection\CollectionInterface;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class CollectMatches
{
    private int $clubId;
    private string $dateFrom;
    private string $dateTo;
    private CollectionInterface $matches;

    /**
     * CollectMatches constructor.
     *
     * @param string $url
     * @param int $clubId
     * @param string $dateFrom
     * @param string $dateTo
     * @throws \Avolle\WeeklyMatches\Exception\InvalidExcelConfiguration|\Psr\Cache\InvalidArgumentException
     */
    public function __construct(string $url, int $clubId, string $dateFrom, string $dateTo)
    {
        $this->clubId = $clubId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;

        $filename = $this->getExternalFile($url);

        $matches = (new SpreadsheetReader($filename))->getMatches();

        $this->matches = new Collection($matches);
    }

    /**
     * @param string $url
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getExternalFile(string $url): string
    {
        $query = [
            'clubId' => $this->clubId,
            'fromDate' => $this->dateFrom,
            'toDate' => $this->dateTo,
        ];

        // Will be first used as the cache key, so whenever a new clubId or date is requested, it will fetch new file
        $query = http_build_query($query, null, '&', PHP_QUERY_RFC3986);

        $cache = new FilesystemAdapter();
        return $cache->get($query, function (ItemInterface $item) use ($url, $query) {
            // Cache loading failed, so fetch new file from API
            $item->expiresAfter(24 * 60 * 60);

            $client = new Client();
            $res = $client->request('GET', $url, ['query' => $query]);

            if (!$res->getStatusCode() === 200) {
                throw new InvalidResponseException();
            }

            $fileBody = $res->getBody()->getContents();

            $filename = FILES . 'matches-' . time() . '.xlsx';
            file_put_contents($filename, $fileBody);

            return $filename;
        });
    }

    /**
     * @return \Cake\Collection\CollectionInterface
     */
    public function getMatches(): CollectionInterface
    {
        return $this->matches;
    }
}
