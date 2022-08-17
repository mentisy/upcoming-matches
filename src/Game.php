<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches;

use DateTime;

/**
 * Class Game
 *
 * @package Avolle\UpcomingMatches
 */
class Game
{
    /**
     * Date of match
     *
     * @var \DateTime
     */
    public DateTime $date;

    /**
     * Day of match
     *
     * @var string
     */
    public string $day;

    /**
     * Time of match
     *
     * @var string
     */
    public string $time;

    /**
     * Home team of match
     *
     * @var string
     */
    public string $homeTeam;

    /**
     * Away team of match
     *
     * @var string
     */
    public string $awayTeam;

    /**
     * Pitch match is played on
     *
     * @var string
     */
    public string $pitch;

    /**
     * Tournament of match
     *
     * @var string
     */
    public string $tournament;

    /**
     * Id of match
     *
     * @var string
     */
    public string $matchId;

    /**
     * Game constructor.
     *
     * @param \DateTime $date Date
     * @param string $day Day
     * @param string $time Time
     * @param string $homeTeam Home team
     * @param string $awayTeam Away team
     * @param string $pitch Pitch
     * @param string $tournament Tournament
     * @param string $matchId Match id
     */
    public function __construct(
        DateTime $date,
        string $day,
        string $time,
        string $homeTeam,
        string $awayTeam,
        string $pitch,
        string $tournament,
        string $matchId
    ) {
        $this->date = $date;
        $this->day = $day;
        $this->time = $time;
        $this->homeTeam = trim($homeTeam);
        $this->awayTeam = trim($awayTeam);
        $this->pitch = trim($pitch);
        $this->tournament = trim($tournament);
        $this->matchId = trim($matchId);
    }
}
