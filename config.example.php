<?php

use Avolle\WeeklyMatches\Render\ImageRender;

/*
 * Config
 *
 * teamName - Your team name which will be placed on the rendering
 * url - The URL in which the Excel sheet will be downloaded from. Dates will be appended by the request data
 * clubId - Your club id, which is given in FIKS Fotball
 * renderClass - Which class that will be responsible for rendering the match output. Must implement RenderInterface
 * debug - Whether you are in development mode. If true, stack trace is shown. If false, generic error message is shown
 */
return [
    'teamName' => '',
    'url' => 'https://www.fotball.no/footballapi/Calendar/DownloadClubExcelCalendar',
    'clubId' => 0,
    'renderClass' => ImageRender::class,
    'debug' => false,
];
