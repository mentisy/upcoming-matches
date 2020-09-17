<?php

namespace Avolle\WeeklyMatches;

class Match
{
    public \DateTime $date;
    public string $day;
    public string $time;
    public string $homeTeam;
    public string $awayTeam;
    public string $pitch;
    public string $tournament;

    public function __construct(
        \DateTime $date,
        string $day,
        string $time,
        string $homeTeam,
        string $awayTeam,
        string $pitch,
        string $tournament
    ) {
        $this->date = $date;
        $this->day = $day;
        $this->time = $time;
        $this->homeTeam = $homeTeam;
        $this->awayTeam = $awayTeam;
        $this->pitch = $pitch;
        $this->tournament = $tournament;
    }
}