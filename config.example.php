<?php

use Avolle\UpcomingMatches\Render\ImageRender;
use Avolle\UpcomingMatches\Services\ServicesConfig;

/*
 * Config
 *
 * teamName - Your team name which will be placed on the rendering
 * url - The URL in which the Excel sheet will be downloaded from. Dates will be appended by the request data
 * renderClass - Which class that will be responsible for rendering the match output. Must implement RenderInterface
 * debug - Whether you are in development mode. If true, stack trace is shown. If false, generic error message is shown
 * services - An array of services that are available to use.
 *      Each key represents a service and each service must be an instance of ServicesConfig
 */
return [
    'teamName' => '',
    'renderClass' => ImageRender::class,
    'debug' => false,
    'services' => [
        'football' => new ServicesConfig(
            'https://some-api.com/request/',
            ['from' => 'datePeriodFrom', 'to' => 'datePeriodTo'],
            ['token' => 'some-random-token-provided-by-api', 'clubId' => 666]
        ),
    ],
];
