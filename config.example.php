<?php

use Avolle\UpcomingMatches\Render\ImageRender;
use Avolle\UpcomingMatches\Services\ServicesConfig;
use Avolle\UpcomingMatches\SportConfig;

/*
 * Config
 *
 * url - The URL in which the Excel sheet will be downloaded from. Dates will be appended by the request data
 * renderClass - Which class that will be responsible for rendering the match output. Must implement RenderInterface
 * debug - Whether you are in development mode. If true, stack trace is shown. If false, generic error message is shown
 * sports - An array of sports that are available to use.
 *      Each key represents a sport and each sport must be an instance of SportConfig
 */
return [
    'renderClass' => ImageRender::class,
    'debug' => false,
    'sports' => [
        'football' => new SportConfig(
            'Fotball',
            'Aksla IL',
            'Fotballkamper',
            new ServicesConfig(
                'https://www.fotball.no/footballapi/Calendar/DownloadClubExcelCalendar',
                ['from' => 'fromDate', 'to' => 'toDate'],
                ['clubId' => 997]
            )
        ),
    ],
];
