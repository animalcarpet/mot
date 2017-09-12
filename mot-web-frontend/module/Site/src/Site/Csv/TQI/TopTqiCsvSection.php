<?php

namespace Site\Csv\TQI;

use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Site\Csv\TQI\Enum\TopRows;
use Site\ViewModel\TestQuality\UserTestQualityViewModel;

class TopTqiCsvSection
{
    /** @var VehicleTestingStationDto */
    private $vehicleTestingStationDto;
    /** @var LastMonthsDateRange */
    private $tqiDateRange;
    /** @var string */
    private $vehicleGroup;
    /** @var int */
    private $rowsNumber;
    /** @var int */
    private $colsNumber;

    const DATE_RANGE_ONE_MONTH = 1;
    const CSV_DATE_FORMAT = 'M-y';
    const COLUMN_NUMBER = 0;

    /**
     * TopTqiCsvSection constructor.
     * @param VehicleTestingStationDto $vehicleTestingStationDto
     * @param LastMonthsDateRange $tqiDateRange
     * @param string $vehicleGroup
     * @param int $colsNumber
     */
    public function __construct(
        VehicleTestingStationDto $vehicleTestingStationDto,
        LastMonthsDateRange $tqiDateRange,
        $vehicleGroup,
        int $colsNumber
    )
    {
        $this->vehicleTestingStationDto = $vehicleTestingStationDto;
        $this->tqiDateRange = $tqiDateRange;
        $this->vehicleGroup = $vehicleGroup;
        $this->colsNumber = $colsNumber;
        $this->rowsNumber = 7;
    }

    public function build(): array
    {
        $classString = UserTestQualityViewModel::$subtitles[$this->vehicleGroup];

        $csv = new Csv($this->rowsNumber, $this->colsNumber);
        $csv->addField(TopRows::SITE_NAME, self::COLUMN_NUMBER, $this->vehicleTestingStationDto->getName());
        $csv->addField(TopRows::SITE_NUMBER, self::COLUMN_NUMBER, $this->vehicleTestingStationDto->getSiteNumber());
        $csv->addField(TopRows::DATE_RANGE, self::COLUMN_NUMBER, $this->getDateString());
        $csv->addField(TopRows::VEHICLE_GROUP, self::COLUMN_NUMBER, 'Group ' . $this->vehicleGroup);
        $csv->addField(TopRows::VEHICLE_CLASSES, self::COLUMN_NUMBER, $classString);

        return $csv->toArray();
    }

    private function getDateString():string
    {
        if ($this->tqiDateRange->getNumberOfMonths() === self::DATE_RANGE_ONE_MONTH) {
            return $this->tqiDateRange->getStartDate()->format(self::CSV_DATE_FORMAT);
        }

        return $this->tqiDateRange->getStartDate()->format(self::CSV_DATE_FORMAT)
            . ' to ' . $this->tqiDateRange->getEndDate()->format(self::CSV_DATE_FORMAT);
    }
}