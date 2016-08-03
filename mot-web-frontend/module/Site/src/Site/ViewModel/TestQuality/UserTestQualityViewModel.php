<?php
namespace Site\ViewModel\TestQuality;

use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;

class UserTestQualityViewModel
{

    static $subtitles = [
        VehicleClassGroupCode::BIKES => 'Class 1 and 2',
        VehicleClassGroupCode::CARS_ETC => 'Class 3, 4, 5 and 7',
    ];

    protected $table;
    private $groupCode;
    private $userId;
    private $site;
    /** @var  \DateTime */
    private $viewedDate;
    private $csvFileSize;
    /** @var  bool */
    private $isReturnToAETQI;

    public function __construct(
        ComponentBreakdownDto $userBreakdown,
        MotTestingPerformanceDto $nationalGroupPerformance,
        NationalComponentStatisticsDto $nationalComponentStatisticsDto,
        $groupCode,
        $userId,
        VehicleTestingStationDto $site,
        $viewedDate,
        $csvFileSize,
        $isReturnToAETQI
    ) {
        $this->table = new ComponentStatisticsTable(
            $userBreakdown,
            $nationalComponentStatisticsDto,
            static::$subtitles[$groupCode],
            $groupCode
        );

        $this->groupCode = $groupCode;
        $this->userId = $userId;
        $this->site = $site;
        $this->viewedDate = $viewedDate;
        $this->csvFileSize = $csvFileSize;
        $this->isReturnToAETQI = $isReturnToAETQI;
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
        return $this->site->getId();
    }


    public function getMonth()
    {
        return (int)$this->viewedDate->format('m');
    }

    public function getYear()
    {
        return (int)$this->viewedDate->format('Y');
    }

    public function getCsvFileSize()
    {
        $kb = $this->csvFileSize / 1024;
        if ($kb > 1) {
            return round($kb) . 'KB';
        } else {
            return '1KB';
        }
    }

    public function getQueryParamsForReturnLink()
    {
        if ($this->isReturnToAETQI)
        {
            return [
                'query' => [
                    'returnToAETQI' => true,
                ],
            ];
        } else {
            return [];
        }
    }
}