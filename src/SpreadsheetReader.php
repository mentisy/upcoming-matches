<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches;

use Avolle\UpcomingMatches\Exception\InvalidExcelConfiguration;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Class SpreadsheetReader
 *
 * @package Avolle\UpcomingMatches
 */
class SpreadsheetReader
{
    /**
     * Options for the spreadsheet reader
     *
     * - hasHeaders - bool: Whether the spreadsheet has headers, indicating that data reading should start at row two
     * - headers - array: Maps the different Match entity properties to columns in the spreadsheet
     *
     * @var array
     */
    protected array $options = [
        'hasHeaders' => true,
        'headers' => [
            'date' => 'B',
            'day' => 'C',
            'time' => 'D',
            'homeTeam' => 'E',
            'awayTeam' => 'G',
            'pitch' => 'H',
            'tournament' => 'I',
            'matchId' => 'J',
        ],
    ];

    /**
     * Spreadsheet instance
     *
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    private Spreadsheet $spreadsheet;

    /**
     * An array of Match entities
     *
     * @var \Avolle\UpcomingMatches\Game[]
     */
    private array $matches;

    /**
     * SpreadsheetReader constructor.
     *
     * @param string $filename Filename to read spreadsheet from
     * @param array $options Options to use in the reader
     * @throws \Avolle\UpcomingMatches\Exception\InvalidExcelConfiguration
     */
    public function __construct(string $filename, $options = [])
    {
        $this->options = $options + $this->options;
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);

        $this->spreadsheet = $reader->load($filename);

        $this->matches = $this->compileMatches();
    }

    /**
     * Read matches from the spreadsheet and compile them into an array of Match entities
     *
     * @return array
     * @throws \Avolle\UpcomingMatches\Exception\InvalidExcelConfiguration
     */
    private function compileMatches(): array
    {
        $firstRow = $this->firstRow();
        $lastRow = $this->lastRow();

        $matches = [];

        $spreadsheet = $this->spreadsheet->getActiveSheet();

        for ($row = $firstRow; $row <= $lastRow; $row++) {
            try {
                $dateValue = Date::excelToDateTimeObject(
                    $spreadsheet->getCell($this->cell($this->options['headers']['date'], $row))->getValue()
                );
                $matches[] = new Game(
                    $dateValue,
                    $spreadsheet->getCell($this->cell($this->options['headers']['day'], $row))->getValue(),
                    $spreadsheet->getCell($this->cell($this->options['headers']['time'], $row))->getValue(),
                    $spreadsheet->getCell($this->cell($this->options['headers']['homeTeam'], $row))->getValue(),
                    $spreadsheet->getCell($this->cell($this->options['headers']['awayTeam'], $row))->getValue(),
                    $spreadsheet->getCell($this->cell($this->options['headers']['pitch'], $row))->getValue(),
                    $spreadsheet->getCell($this->cell($this->options['headers']['tournament'], $row))->getValue(),
                    $spreadsheet->getCell($this->cell($this->options['headers']['matchId'], $row))->getValue(),
                );
            } catch (Exception $e) {
                throw new InvalidExcelConfiguration();
            }
        }

        return $matches;
    }

    /**
     * Evaluate which row contains data, so the spreadsheet can start reading from that row
     *
     * @return int
     */
    private function firstRow(): int
    {
        if ($this->options['hasHeaders']) {
            return 2;
        }

        return 1;
    }

    /**
     * Evaluate which row is the last to contain data
     *
     * @return int
     */
    private function lastRow(): int
    {
        return $this->spreadsheet->getActiveSheet()->getHighestDataRow();
    }

    /**
     * Create a cell string to be read, compiled from the input row and column
     *
     * @param string $column Column of cell
     * @param int $row Row of cell
     * @return string
     */
    private function cell(string $column, int $row): string
    {
        return sprintf("%s%s", $column, $row);
    }

    /**
     * Returns the compiled array of Match entities
     *
     * @return \Avolle\UpcomingMatches\Game[]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }
}
