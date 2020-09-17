<?php

namespace Avolle\WeeklyMatches;


use Avolle\WeeklyMatches\Exception\InvalidExcelConfiguration;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SpreadsheetReader
{
    protected array $options = [
        'hasHeaders' => true,
        'headers' => [
            'date' => 'B',
            'day' => 'C',
            'time' => 'D',
            'homeTeam' => 'E',
            'awayTeam' => 'G',
            'pitch' => 'H',
            'tournament' => 'I'
        ],
    ];

    private Spreadsheet $spreadsheet;

    /**
     * @var \Avolle\WeeklyMatches\Match[]
     */
    private array $matches;

    /**
     * SpreadsheetReader constructor.
     *
     * @param string $filename
     * @param array $options
     * @throws \Avolle\WeeklyMatches\Exception\InvalidExcelConfiguration
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
     * @return array
     * @throws \Avolle\WeeklyMatches\Exception\InvalidExcelConfiguration
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
                $matches[] = new Match(
                    $dateValue,
                    $spreadsheet->getCell($this->cell($this->options['headers']['day'], $row)),
                    $spreadsheet->getCell($this->cell($this->options['headers']['time'], $row)),
                    $spreadsheet->getCell($this->cell($this->options['headers']['homeTeam'], $row)),
                    $spreadsheet->getCell($this->cell($this->options['headers']['awayTeam'], $row)),
                    $spreadsheet->getCell($this->cell($this->options['headers']['pitch'], $row)),
                    $spreadsheet->getCell($this->cell($this->options['headers']['tournament'], $row)),
                );
            } catch (Exception $e) {
                throw new InvalidExcelConfiguration();
            }
        }

        return $matches;
    }

    private function firstRow(): int
    {
    	if ($this->options['hasHeaders']) {
    	    return 2;
        }

    	return 1;
    }

    private function lastRow(): int
    {
        return $this->spreadsheet->getActiveSheet()->getHighestDataRow();
    }

    private function cell(string $column, int $row)
    {
        return sprintf("%s%s", $column, $row);
    }

    /**
     * @return \Avolle\WeeklyMatches\Match[]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }
}