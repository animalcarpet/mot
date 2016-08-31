<?php
namespace SiteTest\ViewModel;


use DateTime;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use Dvsa\Mot\Frontend\TestQualityInformation\ViewModel\ComponentStatisticsRow;
use Dvsa\Mot\Frontend\TestQualityInformation\ViewModel\ComponentStatisticsTable;
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

    /** @var  UserTestQualityViewModel */
    protected $userTestQualityViewModelB;
    /** @var  UserTestQualityViewModel */
    private $userTestQualityViewModelA;

    public function setUp()
    {
        $this->userTestQualityViewModelA = new UserTestQualityViewModel(
            self::buildUserPerformanceDto(),
            SiteTestQualityViewModelTest::buildNationalStatisticsPerformanceDto()->getGroupA(),
            self::buildNationalComponentStatisticsDto(),
            VehicleClassGroupCode::BIKES,
            self::USER_ID,
            self::buildVehicleTestingStationDto(),
            self::buildViewedDate(),
            self::CSV_FILE_SIZE,
            self::IS_RETURN_TO_AE_TQI
        );

        $this->userTestQualityViewModelB = new UserTestQualityViewModel(
            self::buildUserPerformanceDto(),
            SiteTestQualityViewModelTest::buildNationalStatisticsPerformanceDto()->getGroupB(),
            self::buildNationalComponentStatisticsDto(),
            VehicleClassGroupCode::CARS_ETC,
            self::USER_ID,
            self::buildVehicleTestingStationDto(),
            self::buildViewedDate(),
            self::CSV_FILE_SIZE,
            self::IS_RETURN_TO_AE_TQI
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
        $national->setMonth(4);
        $national->setYear(2016);

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
            $this->assertGreaterThan(0, strpos((string)$componentRow->getTesterAverage(), '.'));
        }
    }

    public static function buildEmptyGroupPerformance()
    {
        $group = new MotTestingPerformanceDto();
        $breakdown = new ComponentBreakdownDto();
        $breakdown->setGroupPerformance($group)->setComponents([]);

        return $breakdown;
    }

    /**
     * @param ComponentStatisticsTable $getTable
     */
    private function checkEmptyHeaderFormatting($getTable)
    {
        $this->assertEquals(ComponentStatisticsTable::TEXT_EMPTY, $getTable->getAverageTestDuration());
        $this->assertEquals(0, $getTable->getTestCount());
        $this->assertEquals('0%', $getTable->getFailurePercentage());
    }

    private function assertTitlesAreCorrect()
    {
        $this->assertEquals(
            UserTestQualityViewModel::$subtitles[VehicleClassGroupCode::BIKES],
            $this->userTestQualityViewModelA->getTable()->getGroupDescription());
        $this->assertEquals(
            UserTestQualityViewModel::$subtitles[VehicleClassGroupCode::CARS_ETC],
            $this->userTestQualityViewModelB->getTable()->getGroupDescription());

        $this->assertEquals(
            'Group ' . VehicleClassGroupCode::BIKES,
            $this->userTestQualityViewModelA->getTable()->getGroupName());
        $this->assertEquals(
            'Group ' . VehicleClassGroupCode::CARS_ETC,
            $this->userTestQualityViewModelB->getTable()->getGroupName());
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
}