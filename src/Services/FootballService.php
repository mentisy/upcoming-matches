<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\Services;

use Avolle\UpcomingMatches\SpreadsheetReader;

/**
 * Class FootballService
 *
 * @package Avolle\UpcomingMatches\Services
 */
class FootballService extends Service
{
    /**
     * Returns the converted API result into an array
     * In this instance it reads the spreadsheet file and creates an array of Match entities
     *
     * @return \Avolle\UpcomingMatches\Match[]
     * @throws \Avolle\UpcomingMatches\Exception\InvalidExcelConfiguration
     */
    public function toArray(): array
    {
        $results = collection([]);
        foreach ($this->results as $result) {
            $results = $results->append($this->prepareSpreadsheetFile(
                $result,
                fn(string $filename) => (new SpreadsheetReader($filename))->getMatches(),
            ));
        }

        return $results->toList();
    }

    /**
     * Creates a temporary spreadsheet file from the API result.
     * Uses the $callback parameter to read and convert the spreadsheet data to an array of Match entities
     *
     * @param callable $callback The callback to read and convert spreadsheet data
     * @return \Avolle\UpcomingMatches\Match[]
     */
    protected function prepareSpreadsheetFile(string $result, callable $callback): array
    {
        $tmpFile = tmpfile();
        fwrite($tmpFile, $result);
        $filename = stream_get_meta_data($tmpFile)['uri'];

        $matches = $callback($filename);

        fclose($tmpFile);

        return $matches;
    }
}
