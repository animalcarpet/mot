<?php

namespace Site\ViewModel\TestQuality;

use Dvsa\Mot\Frontend\TestQualityInformation\ViewModel\ComponentStatisticsTable;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Enum\VehicleClassGroupCode;

class UserTestQualityViewModel
{
    public static $subtitles = [
        VehicleClassGroupCode::BIKES => 'Class 1 and 2',
        VehicleClassGroupCode::CARS_ETC => 'Class 3, 4, 5 and 7',
    ];

    protected $table;
    private $groupCode;
    private $userId;
    private $siteId;
    /** @var \DateTime */
    private $viewedDate;
    private $csvFileSize;
    private $returnLink;
    private $showCsvLink;

    public function __construct(
        ComponentBreakdownDto $userBreakdown,
        MotTestingPerformanceDto $nationalGroupPerformance,
        NationalComponentStatisticsDto $nationalComponentStatisticsDto,
        $groupCode,
        $userId,
        $siteId,
        $viewedDate,
        $csvFileSize,
        $returnLink,
        $showCsvLink
    ) {
        $this->table = new ComponentStatisticsTable(
            $userBreakdown,
            $nationalComponentStatisticsDto,
            static::$subtitles[$groupCode],
            $groupCode
        );

        $this->groupCode = $groupCode;
        $this->userId = $userId;
        $this->siteId = $siteId;
        $this->viewedDate = $viewedDate;
        $this->csvFileSize = $csvFileSize;
        $this->returnLink = $returnLink;
        $this->showCsvLink = $showCsvLink;
    }

    /**
     * @return ComponentStatisticsTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param ComponentStatisticsTable $table
     *
     * @return UserTestQualityViewModel
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getSiteId()
    {
        return $this->siteId;
    }

    public function getMonth()
    {
        return (int) $this->viewedDate->format('m');
    }

    public function getYear()
    {
        return (int) $this->viewedDate->format('Y');
    }

    public function getCsvFileSize()
    {
        $kb = $this->csvFileSize / 1024;
        if ($kb > 1) {
            return round($kb).'KB';
        } else {
            return '1KB';
        }
    }

    public function getReturnLink()
    {
        return $this->returnLink;
    }

    public function showCsvSection()
    {
        return $this->showCsvLink;
    }
}
