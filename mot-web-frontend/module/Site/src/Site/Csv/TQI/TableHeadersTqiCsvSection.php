<?php
namespace Site\Csv\TQI;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\Formatting\PersonFullNameFormatter;
use Site\Csv\TQI\Enum\TableHeaderRows;

class TableHeadersTqiCsvSection
{
    /** @var EmployeePerformanceDto[] */
    private $statistics;

    /** @var int */
    private $colsNumber;

    const SITE_AVERAGE_COLUMN_NAME = 'Site average';
    const NATIONAL_AVERAGE_COLUMN_NAME = 'National average';

    /**
     * TableHeadersTqiCsvSection constructor.
     * @param EmployeePerformanceDto[] $statistics
     * @param int $colsNumber
     */
    public function __construct(array $statistics, int $colsNumber)
    {
        $this->statistics = $statistics;
        $this->colsNumber = $colsNumber;
    }

    public function build(): array
    {
        $csv = new Csv(3, $this->colsNumber);

        $column = 1;
        foreach ($this->statistics as $employeePerformance) {
            $personFullName = (new PersonFullNameFormatter())->format(
                $employeePerformance->getFirstName(),
                $employeePerformance->getMiddleName(),
                $employeePerformance->getFamilyName());

            $csv->addField(TableHeaderRows::PRIMARY, $column, $personFullName);
            $csv->addField(TableHeaderRows::SECONDARY, $column, $employeePerformance->getUsername());

            $column++;
        }

        $csv->addField(TableHeaderRows::PRIMARY, $column, self::SITE_AVERAGE_COLUMN_NAME);

        $column++;
        $csv->addField(TableHeaderRows::PRIMARY, $column, self::NATIONAL_AVERAGE_COLUMN_NAME);

        return $csv->toArray();
    }
}