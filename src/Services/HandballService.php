<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Services;

use Avolle\UpcomingMatches\Match;
use Cake\Chronos\Chronos;
use JsonException;

class HandballService extends Service
{
    /**
     * Converts the returned JSON API response into an array of Match entities
     *
     * @return \Avolle\UpcomingMatches\Match[]
     * @throws \JsonException
     */
    public function toArray(): array
    {
        $decodedMatches = json_decode($this->content, true);

        if (is_null($decodedMatches)) {
            throw new JsonException('The JSON data could not be converted');
        }

        $matches = [];

        foreach ($decodedMatches as $match) {
            $date = new Chronos($match['Dato'] . ' ' . $match['Tid']);

            $matches[] = new Match(
                $date->toMutable(),
                strftime('%A', $date->getTimestamp()),
                $date->format('H:i'),
                $match['Hjemmelag'],
                $match['Bortelag'],
                $match['Bane'],
                $match['Turnering']
            );
        }

        return $matches;
    }
}
