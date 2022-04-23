<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Filterable;

use Avolle\UpcomingMatches\SportConfig;

class TeamNameFilter implements FilterableInterface
{
    /**
     * Filter matches that do not contain the SportConfig's team name
     *
     * @inheritDoc
     */
    public function filter(array $matches, SportConfig $sportConfig): array
    {
        $spacePos = strpos($sportConfig->teamName, ' ');
        $teamName = substr($sportConfig->teamName, 0, $spacePos ?: strlen($sportConfig->teamName));
        $filteredMatches = [];
        foreach ($matches as $match) {
            if (strpos($match->homeTeam, $teamName) !== false) {
                $filteredMatches[] = $match;
            } elseif (strpos($match->awayTeam, $teamName) !== false) {
                $filteredMatches[] = $match;
            }
        }

        return $filteredMatches;
    }
}
