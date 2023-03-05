<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Services;

use Avolle\UpcomingMatches\Game;
use Cake\Chronos\Chronos;
use Cake\Collection\CollectionInterface;
use JsonException;

class HandballService extends Service
{
    /**
     * Converts the returned JSON API response into an array of Match entities
     *
     * @return \Avolle\UpcomingMatches\Game[]
     * @throws \JsonException
     */
    public function toArray(): array
    {
        $results = collection([]);
        foreach ($this->results as $result) {
            $results = $results->append($this->extractResult($result));
        }

        return $results
            ->sortBy(fn (Game $match) => $match->time, SORT_ASC)
            ->sortBy(fn (Game $match) => $match->date, SORT_ASC)
            ->toArray(false);
    }

    /**
     * Extract matches from an API result
     *
     * @param string $result API result in string
     * @return \Cake\Collection\CollectionInterface
     * @throws \JsonException
     */
    protected function extractResult(string $result): CollectionInterface
    {
        $decodedMatches = json_decode($result, true);

        if (is_null($decodedMatches)) {
            throw new JsonException('The JSON data could not be converted');
        }

        $matches = [];

        foreach ($decodedMatches as $match) {
            $date = new Chronos($match['Dato'] . ' ' . $match['Tid']);

            $matches[] = new Game(
                $date->toMutable(),
                strftime('%A', $date->getTimestamp()),
                $date->format('H:i'),
                $match['Hjemmelag'],
                $match['Bortelag'],
                $match['Bane'],
                $match['Turnering'],
                $match['Kampnr']
            );
        }

        return collection($matches);
    }
}
