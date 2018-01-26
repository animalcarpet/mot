<?php

namespace SiteTest\ViewModel;

use DateTime;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use Dvsa\Mot\Frontend\TestQualityInformation\ViewModel\ComponentStatisticsRow;
use Site\Form\TQIMonthRangeForm;
use Site\ViewModel\TestQuality\UserTestQualityViewModel;

class UserTestQualityViewModelTest extends \PHPUnit_Framework_TestCase
{
    const RETURN_LINK = '/vehicle-testing-station/1/test-quality';
    const COMPONENT_ONE_ID = 1;
    const COMPONENT_TWO_ID = 2;
    const COMPONENT_USER_EMPTY_ID = 3;
    const USERNAME = 'tester';
    const DISPLAY_NAME = 'John Smith';
    const USER_ID = 10;
    const VTS_ID = 11;
    const CSV_FILE_SIZE = 10000;
    const IS_RETURN_TO_AE_TQI = false;
    const THREE_MONTHS_RANGE = 3;
    const GQR_REPORTS_3_MONTHS_OPTION = true;

    /** @var UserTestQualityViewModel */
    protected $userTestQualityViewModelB;
    /** @var UserTestQualityViewModel */
    private $userTestQualityViewModelA;

    public function setUp()
    {
        $this->userTestQualityViewModelA = new UserTestQualityViewModel(
            self::buildUserPerformanceDto(),
            $this->buildNationalStatisticsPerformanceDto()->getGroupA(),
            self::buildNationalComponentStatisticsDto(),
            [],
            VehicleClassGroupCode::BIKES,
            self::USER_ID,
            self::buildVehicleTestingStationDto(),
            new LastMonthsDateRange(new DateTimeHolder()),
            self::RETURN_LINK,
            new TQIMonthRangeForm(self::GQR_REPORTS_3_MONTHS_OPTION)
        );

        $this->userTestQualityViewModelB = new UserTestQualityViewModel(
            self::buildUserPerformanceDto(),
            $this->buildNationalStatisticsPerformanceDto()->getGroupB(),
            self::buildNationalComponentStatisticsDto(),
            [],
            VehicleClassGroupCode::CARS_ETC,
            self::USER_ID,
            self::buildVehicleTestingStationDto(),
            new LastMonthsDateRange(new DateTimeHolder()),
            self::RETURN_LINK,
            new TQIMonthRangeForm(self::GQR_REPORTS_3_MONTHS_OPTION)
        );
    }

    public function testTablePopulatesWithRows()
    {
        $this->assertTitlesAreCorrect();
        $components = self::buildUserPerformanceDto()->getComponents();
        $rowCount = count($components);
        $this->assertCount($rowCount, $this->userTestQualityViewModelA->getTable()->getComponentRows());
        $this->assertCount($rowCount, $this->userTestQualityViewModelB->getTable()->getComponentRows());
        $this->checkRowFormatting($this->userTestQualityViewModelA->getTable()->getComponentRows());
    }

    public static function buildUserPerformanceDto()
    {
        $group = new MotTestingPerformanceDto();
        $group->setAverageTime(new TimeSpan(0, 0, 1, 1))
            ->setPercentageFailed(10.123)
            ->setTotal(5);

        $componentOne = new ComponentDto();
        $componentOne->setId(self::COMPONENT_ONE_ID)
            ->setName('Component ONE')
            ->setPercentageFailed(20.12);

        $componentTwo = new ComponentDto();
        $componentTwo->setId(self::COMPONENT_TWO_ID)
            ->setName('Second COMPONENT')
            ->setPercentageFailed(40.1234);

        $breakdown = new ComponentBreakdownDto();
        $breakdown->setGroupPerformance($group)
            ->setComponents([$componentOne, $componentTwo])
            ->setUserName(self::USERNAME)
            ->setDisplayName(self::DISPLAY_NAME);

        return $breakdown;
    }

    public static function buildNationalComponentStatisticsDto()
    {
        $national = new NationalComponentStatisticsDto();
        $national->setMonthRange(self::THREE_MONTHS_RANGE);

        $brakes = new ComponentDto();
        $brakes->setId(self::COMPONENT_ONE_ID);
        $brakes->setPercentageFailed(50.123123);
        $brakes->setName('Brakes');

        $tyres = new ComponentDto();
        $tyres->setId(self::COMPONENT_TWO_ID);
        $tyres->setPercentageFailed(30.5523);
        $tyres->setName('Tyres');

        $userEmpty = new ComponentDto();
        $userEmpty->setId(self::COMPONENT_USER_EMPTY_ID);
        $userEmpty->setPercentageFailed(11.12312);
        $userEmpty->setName('Component that is missing in user stats');

        $national->setComponents([$brakes, $tyres, $userEmpty]);

        return $national;
    }

    /**
     * @param ComponentStatisticsRow[] $componentRows
     */
    private function checkRowFormatting($componentRows)
    {
        foreach ($componentRows as $componentRow) {
            if ($componentRow->getCategoryId() == self::COMPONENT_USER_EMPTY_ID) {
                $this->assertEquals($componentRow->getTesterAverage(), 0);
            }
            $this->assertGreaterThan(0, strpos((string) $componentRow->getTesterAverage(), '.'));
        }
    }

    public static function buildEmptyGroupPerformance()
    {
        $group = new MotTestingPerformanceDto();
        $breakdown = new ComponentBreakdownDto();
        $breakdown->setGroupPerformance($group)->setComponents([]);

        return $breakdown;
    }

    private function assertTitlesAreCorrect()
    {
        $this->assertEquals(
            UserTestQualityViewModel::$subtitles[VehicleClassGroupCode::BIKES],
            $this->userTestQualityViewModelA->getTable()->getAverageGroupStatisticsHeader()->getGroupDescription());
        $this->assertEquals(
            UserTestQualityViewModel::$subtitles[VehicleClassGroupCode::CARS_ETC],
            $this->userTestQualityViewModelB->getTable()->getAverageGroupStatisticsHeader()->getGroupDescription());

        $this->assertEquals(
            VehicleClassGroupCode::BIKES,
            $this->userTestQualityViewModelA->getTable()->getAverageGroupStatisticsHeader()->getGroupCode());
        $this->assertEquals(
            VehicleClassGroupCode::CARS_ETC,
            $this->userTestQualityViewModelB->getTable()->getAverageGroupStatisticsHeader()->getGroupCode());
    }

    public static function buildVehicleTestingStationDto()
    {
        $vtsDto = new VehicleTestingStationDto();
        $vtsDto->setId(self::VTS_ID);

        return $vtsDto;
    }

    public static function buildViewedDate()
    {
        return new DateTime();
    }
    public function buildNationalStatisticsPerformanceDto()
    {
        $national = new NationalPerformanceReportDto();
        $national->setMonth(4);
        $national->setYear(2016);

        $groupA = new MotTestingPerformanceDto();
        $groupA->setAverageTime(new TimeSpan(2, 2, 2, 2));
        $groupA->setPercentageFailed(50);
        $groupA->setTotal(10);

        $national->setGroupA($groupA);

        $groupB = new MotTestingPerformanceDto();
        $groupB->setAverageTime(new TimeSpan(0, 0, 2, 2));
        $groupB->setPercentageFailed(30);
        $groupB->setTotal(5);

        $national->setGroupB($groupB);

        $national->getReportStatus()->setIsCompleted(true);

        return $national;
    }
}
