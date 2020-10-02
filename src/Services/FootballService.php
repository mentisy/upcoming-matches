<?php

namespace Avolle\WeeklyMatches\Services;

use Avolle\WeeklyMatches\SpreadsheetReader;

/**
 * Class FootballService
 *
 * @package Avolle\WeeklyMatches\Services
 */
class FootballService extends Service
{
    /**
     * Returns the converted API result into an array
     * In this instance it reads the spreadsheet file and creates an array of Match entities
     *
     * @return \Avolle\WeeklyMatches\Match[]
     * @throws \Avolle\WeeklyMatches\Exception\InvalidExcelConfiguration
     */
    public function toArray(): array
    {
        return $this->prepareSpreadsheetFile(fn(string $filename) => (new SpreadsheetReader($filename))->getMatches());
    }

    /**
     * Creates a temporary spreadsheet file from the API result.
     * Uses the $callback parameter to read and convert the spreadsheet data to an array of Match entities
     *
     * @param callable $callback The callback to read and convert spreadsheet data
     * @return \Avolle\WeeklyMatches\Match[]
     */
    protected function prepareSpreadsheetFile(callable $callback): array
    {
        $tmpFile = tmpfile();
        fwrite($tmpFile, $this->content);
        $filename = stream_get_meta_data($tmpFile)['uri'];

        $matches = $callback($filename);

        fclose($tmpFile);

        return $matches;
    }
}
