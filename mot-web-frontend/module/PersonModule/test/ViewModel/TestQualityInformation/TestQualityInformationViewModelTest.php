<?php

use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\SiteRowViewModel;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\TestQualityInformationViewModel;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;
use DvsaCommonTest\Date\TestDateTimeHolder;
use Site\Form\TQIMonthRangeForm;

class TestQualityInformationViewModelTest extends \PHPUnit_Framework_TestCase
{
    const RETURN_LINK = 'http://link.com';
    const RETURN_LINK_TEXT = 'return';
    const COMPONENT_LINK_TEXT = 'component link';
    const COMPONENT_LINK_TEXT_GROUP = 'component link group';
    const NOT_AVAILABLE = 'Not available';
    const THREE_MONTHS_RANGE = 3;
    const PERSON_ID = 1;
    const GQR_REPORTS_3_MONTHS_OPTION = true;


    public function testTablePopulatesNationalStatistics()
    {
        $testQualityInformationViewModel = new TestQualityInformationViewModel(
            self::buildTesterPerformanceDto(false, false),
            [], [],
            self::buildNationalStatisticsPerformanceDto(),
            self::buildTesterAuthorisation(false, false),
            self::THREE_MONTHS_RANGE,
            self::RETURN_LINK,
            self::RETURN_LINK_TEXT,
            new TQIMonthRangeForm(self::GQR_REPORTS_3_MONTHS_OPTION),
            new TestDateTimeHolder(new \DateTime('2015-2-15')),
            self::PERSON_ID
        );

        $this->assertEquals(($testQualityInformationViewModel->getA()->getNationalTestCount()), 10);
        $this->assertEquals(($testQualityInformationViewModel->getA()->getNationalPercentageFailed()), '50%');
        $this->assertEquals(($testQualityInformationViewModel->getA()->getNationalAverageTestDuration()), 3002);

        $this->assertEquals(($testQualityInformationViewModel->getB()->getNationalTestCount()), 5);
        $this->assertEquals(($testQualityInformationViewModel->getB()->getNationalPercentageFailed()), '30%');
        $this->assertEquals(($testQualityInformationViewModel->getB()->getNationalAverageTestDuration()), 2);
    }

    public function testTablePopulatesStatistics()
    {
        $testQualityInformationViewModel = new TestQualityInformationViewModel(
            self::buildTesterPerformanceDto(true, true),
            [self::buildSiteRowViewModel()],
            [self::buildSiteRowViewModel()],
            self::buildNationalStatisticsPerformanceDto(),
            self::buildTesterAuthorisation(false, false),
            self::THREE_MONTHS_RANGE,
            self::RETURN_LINK,
            self::RETURN_LINK_TEXT,
            new TQIMonthRangeForm(self::GQR_REPORTS_3_MONTHS_OPTION),
            new TestDateTimeHolder(new \DateTime('2015-2-15')),
            self::PERSON_ID
        );

        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->getTestCount()), 1);
        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->getFailurePercentage()), '100%');
        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->getIsAverageVehicleAgeAvailable()), true);
        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->hasTests()), true);
        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->getAverageTestDuration()), 1501);

        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->getTestCount()), 200);
        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->getFailurePercentage()), '33%');
        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->getIsAverageVehicleAgeAvailable()), true);
        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->hasTests()), true);
        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->getAverageTestDuration()), 3002);
    }

    public function testTableNotAvailableText()
    {
        $testQualityInformationViewModel = new TestQualityInformationViewModel(
            self::buildTesterPerformanceDto(false, false),
            [], [],
            self::buildNationalStatisticsPerformanceDto(),
            self::buildTesterAuthorisation(false, false),
            self::THREE_MONTHS_RANGE,
            self::RETURN_LINK,
            self::RETURN_LINK_TEXT,
            new TQIMonthRangeForm(self::GQR_REPORTS_3_MONTHS_OPTION),
            new TestDateTimeHolder(new \DateTime('2015-2-15')),
            self::PERSON_ID
        );

        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->getTestCount()), 0);
        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->getFailurePercentage()), '0%');
        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->hasTests()), false);
        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->getAverageTestDuration()), null);
        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->getIsAverageVehicleAgeAvailable()), false);
        $this->assertEquals(($testQualityInformationViewModel->getA()->getAverageGroupStatisticsHeader()->getAverageVehicleAge()), null);

        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->getTestCount()), 0);
        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->getFailurePercentage()), '0%');
        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->hasTests()), false);
        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->getAverageTestDuration()), null);
        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->getIsAverageVehicleAgeAvailable()), false);
        $this->assertEquals(($testQualityInformationViewModel->getB()->getAverageGroupStatisticsHeader()->getAverageVehicleAge()), null);
    }

    /**
     * @dataProvider dataProviderTestGetTable
     *
     * @param $testerPerformance
     * @param $testerAuthorisation
     * @param $resultA
     * @param $resultB
     */
    public function testAreTablesViewable($testerPerformance, $testerAuthorisation, $resultA, $resultB)
    {
        $testQualityInformationViewModel = new TestQualityInformationViewModel(
            $testerPerformance,
            [], [],
            self::buildNationalStatisticsPerformanceDto(),
            $testerAuthorisation,
            self::THREE_MONTHS_RANGE,
            self::RETURN_LINK,
            self::RETURN_LINK_TEXT,
            new TQIMonthRangeForm(self::GQR_REPORTS_3_MONTHS_OPTION),
            new TestDateTimeHolder(new \DateTime('2015-2-15')),
            self::PERSON_ID
        );

        $this->assertEquals($resultA, $testQualityInformationViewModel->isAVisible());
        $this->assertEquals($resultB, $testQualityInformationViewModel->isBVisible());
    }

    public function dataProviderTestGetTable()
    {
        return [
            [
                'testerPerformance' => self::buildTesterPerformanceDto(false, false),
                'testerAuthorisation' => self::buildTesterAuthorisation(true, true),
                'resultA' => true,
                'resultB' => true,
            ],
            [
                'testerPerformance' => self::buildTesterPerformanceDto(false, false),
                'testerAuthorisation' => self::buildTesterAuthorisation(true, false),
                'resultA' => true,
                'resultB' => false,
            ],
            [
                'testerPerformance' => self::buildTesterPerformanceDto(false, false),
                'testerAuthorisation' => self::buildTesterAuthorisation(false, true),
                'resultA' => false,
                'resultB' => true,
            ],
            [
                'testerPerformance' => self::buildTesterPerformanceDto(false, false),
                'testerAuthorisation' => self::buildTesterAuthorisation(false, false),
                'resultA' => true,
                'resultB' => true,
            ],
            [
                'testerPerformance' => self::buildTesterPerformanceDto(true, false),
                'testerAuthorisation' => self::buildTesterAuthorisation(false, false),
                'resultA' => true,
                'resultB' => false,
            ],
            [
                'testerPerformance' => self::buildTesterPerformanceDto(true, true),
                'testerAuthorisation' => self::buildTesterAuthorisation(false, false),
                'resultA' => true,
                'resultB' => true,
            ],
            [
                'testerPerformance' => self::buildTesterPerformanceDto(false, true),
                'testerAuthorisation' => self::buildTesterAuthorisation(false, false),
                'resultA' => false,
                'resultB' => true,
            ],
            [
                'testerPerformance' => self::buildTesterPerformanceDto(true, true),
                'testerAuthorisation' => self::buildTesterAuthorisation(true, false),
                'resultA' => true,
                'resultB' => true,
            ],
        ];
    }

    public static function buildNationalStatisticsPerformanceDto()
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

    public static function buildTesterPerformanceDto($isGroupA, $isGroupB)
    {
        $tester = new TesterPerformanceDto();

        if ($isGroupA) {
            $stats1 = new EmployeePerformanceDto();

            $stats1->setUsername('Tester');
            $stats1->setTotal(1);
            $stats1->setAverageTime(new TimeSpan(1, 1, 1, 1));
            $stats1->setPercentageFailed(100);
            $stats1->setIsAverageVehicleAgeAvailable(true);

            $tester->setGroupAPerformance($stats1);
        }

        if ($isGroupB) {
            $stats2 = new EmployeePerformanceDto();

            $stats2->setUsername('Tester');
            $stats2->setTotal(200);
            $stats2->setAverageTime(new TimeSpan(2, 2, 2, 2));
            $stats2->setPercentageFailed(33.33);
            $stats2->setIsAverageVehicleAgeAvailable(true);

            $tester->setGroupBPerformance($stats2);
        }

        return $tester;
    }

    public static function buildTesterAuthorisation($isGroupAQualified, $isGroupBQualified)
    {
        if ($isGroupAQualified) {
            $groupA = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::QUALIFIED, '');
        } else {
            $groupA = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, '');
        }
        if ($isGroupBQualified) {
            $groupB = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::QUALIFIED, '');
        } else {
            $groupB = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED, '');
        }

        $testerAuthorisation = new TesterAuthorisation(
            $groupA,
            $groupB
        );

        return $testerAuthorisation;
    }

    public static function buildSiteRowViewModel()
    {
        return new SiteRowViewModel(1, 'site name', 'address', 1, true, 1, new TimeSpan(1,23,59,59), 20, "/url");
    }

}
