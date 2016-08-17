<?php
namespace Dvsa\Mot\Frontend\PersonModule\ViewModel;

use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use Dvsa\Mot\Frontend\TestQualityInformation\ViewModel\ComponentStatisticsTable;

class TestQualityComponentBreakdownViewModel
{
    static $subtitles = [
        VehicleClassGroupCode::BIKES => 'Class 1 and 2',
        VehicleClassGroupCode::CARS_ETC => 'Class 3, 4, 5 and 7',
    ];

    private $testerBreakdown;
    private $nationalBreakdown;
    private $table;
    private $returnUrl;
    private $returnLinkText;

    public function __construct(
        ComponentBreakdownDto $testerBreakdown,
        NationalComponentStatisticsDto $nationalBreakdown,
        $groupCode,
        $returnUrl,
        $returnLinkText
    ) {

        $this->table = new ComponentStatisticsTable(
            $testerBreakdown,
            $nationalBreakdown,
            static::$subtitles[$groupCode],
            $groupCode
        );

        $this->testerBreakdown = $testerBreakdown;;
        $this->nationalBreakdown = $nationalBreakdown;
        $this->returnUrl = $returnUrl;
        $this->returnLinkText = $returnLinkText;
    }

    /**
     * @return \Dvsa\Mot\Frontend\TestQualityInformation\ViewModel\ComponentStatisticsTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @return string
     */
    public function getReturnLinkText()
    {
        return $this->returnLinkText;
    }
}