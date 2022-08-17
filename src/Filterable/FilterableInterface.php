<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Filterable;

use Avolle\UpcomingMatches\SportConfig;

interface FilterableInterface
{
    /**
     * Filter matches
     *
     * @param array<int, \Avolle\UpcomingMatches\Game> $matches Matches to filter
     * @param \Avolle\UpcomingMatches\SportConfig $sportConfig SportsConfig to use for filtering
     * @return array
     */
    public function filter(array $matches, SportConfig $sportConfig): array;
}
