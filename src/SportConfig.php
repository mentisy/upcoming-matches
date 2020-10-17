<?php

namespace Avolle\UpcomingMatches;

use Avolle\UpcomingMatches\Services\ServicesConfig;

class SportConfig
{
    public string $sport;
    public string $teamName;
    public string $renderSubTitle;
    public ServicesConfig $serviceConfig;

    public function __construct(string $sport, string $teamName, string $renderSubTitle, ServicesConfig $serviceConfig)
    {
        $this->sport = $sport;
        $this->teamName = $teamName;
        $this->renderSubTitle = $renderSubTitle;
        $this->serviceConfig = $serviceConfig;
    }
}
