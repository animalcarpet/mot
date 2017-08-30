<?php

namespace Site\ViewModel\TestQuality;

use Dvsa\Mot\Frontend\TestQualityInformation\ViewModel\ComponentStatisticsTable;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Enum\VehicleClassGroupCode;
use Site\Form\TQIMonthRangeForm;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;

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
    private $monthRange;
    private $returnLink;
    /**
     * @var TQIMonthRangeForm
     */
    private $monthRangeForm;

    public function __construct(
        ComponentBreakdownDto $userBreakdown,
        MotTestingPerformanceDto $nationalGroupPerformance,
        NationalComponentStatisticsDto $nationalComponentStatisticsDto,
        array $siteAverageBreakdown,
        $groupCode,
        $userId,
        $siteId,
        LastMonthsDateRange $monthRange,
        $returnLink,
        TQIMonthRangeForm $monthRangeForm
    ) {
        TypeCheck::assertCollectionOfClass($siteAverageBreakdown, ComponentDto::class);

        $this->table = new ComponentStatisticsTable(
            $userBreakdown,
            $nationalComponentStatisticsDto,
            $siteAverageBreakdown,
            static::$subtitles[$groupCode],
            $groupCode
        );

        $this->groupCode = $groupCode;
        $this->userId = $userId;
        $this->siteId = $siteId;
        $this->monthRange = $monthRange;
        $this->returnLink = $returnLink;
        $this->monthRangeForm = $monthRangeForm;
    }

    /**
     * @return TQIMonthRangeForm
     */
    public function getMonthRangeForm()
    {
        return $this->monthRangeForm;
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

    public function getReturnLink()
    {
        return $this->returnLink;
    }

    public function getMonthRange()
    {
        return $this->monthRange;
    }

}
