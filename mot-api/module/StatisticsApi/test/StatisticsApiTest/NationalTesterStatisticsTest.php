<?php

namespace Dvsa\Mot\Api\StatisticsApiTest;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Storage\S3KeyGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\QueryResult\NationalStatisticsResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Repository\NationalStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Storage\NationalTesterPerformanceStatisticsStorage;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\Mocking\KeyValueStorage\KeyValueStorageFake;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;

class NationalTesterStatisticsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService */
    private $service;

    /** @var MethodSpy */
    private $repoStatisticsSpy;

    /** @var NationalStatisticsRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\QueryResult\NationalStatisticsResult */
    private $dbResult;

    private $nationalStorage;

    /** @var KeyValueStorageFake */
    private $storage;

    /** @var TimeSpan */
    private $timeoutPeriod;


    /** @var \DateTime Tells when the report should be already generated.
     * If not, then you report generation should be restarted
     */
    private $timeoutDateTime;

    /** @var TestDateTimeHolder */
    private $dateTimeHolder;

    private $year;

    private $month;

    public function setUp()
    {
        $this->timeoutPeriod = new TimeSpan(0, 1, 0, 0);
        $this->dateTimeHolder = new TestDateTimeHolder(new \DateTime("2016-06-22"));
        $this->timeoutDateTime = $this->timeoutPeriod->addDateTime($this->dateTimeHolder->getCurrent());
        $this->year = (int)$this->dateTimeHolder->getCurrent()->sub(new \DateInterval("P1M"))->format("Y");
        $this->month = (int)$this->dateTimeHolder->getCurrent()->sub(new \DateInterval("P1M"))->format("m");

        $this->repository = XMock::of(NationalStatisticsRepository::class);

        $this->repoStatisticsSpy = new MethodSpy($this->repository, 'getStatistics');

        $this->dbResult = (new NationalStatisticsResult())
            ->setGroupATotal(10)
            ->setGroupAFailed(5)
            ->setGroupACumulativeTestTime("10:10:10")
            ->setNumberOfGroupATesters(5)
            ->setGroupAAverageVehicleAgeInMonths(3)
            ->setGroupBTotal(8)
            ->setGroupBFailed(3)
            ->setGroupBCumulativeTestTime("0:10:10")
            ->setGroupBAverageVehicleAgeInMonths(6)
            ->setNumberOfGroupBTesters(2);

        $this->repoStatisticsSpy->mock()->willReturn($this->dbResult);

        $this->storage = new KeyValueStorageFake();
        $this->nationalStorage = XMock::of(NationalTesterPerformanceStatisticsStorage::class);

        $nationalStorage = new NationalTesterPerformanceStatisticsStorage($this->storage);

        $this->service = new NationalStatisticsService(
            $this->repository,
            $nationalStorage,
            $this->dateTimeHolder,
            $this->timeoutPeriod
        );
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetThrowsExceptionIfValidationFailed()
    {
        $this->service->get(1099, 13);
    }

    public function testTheStatisticsCalculationAreDoneForCorrectMonthTotalCount()
    {
        // GIVEN I need statistics for last month

        // WHEN I retrieve the statistics
        $this->service->get($this->year, $this->month);

        // THEN I only get them once from repository
        $this->assertEquals(1, $this->repoStatisticsSpy->invocationCount());

        // AND they are for correct year
        $this->assertEquals($this->year, $this->repoStatisticsSpy->paramsForLastInvocation()[0]);

        // AND correct month
        $this->assertEquals($this->month, $this->repoStatisticsSpy->paramsForLastInvocation()[1]);
    }

    public function testTheStatisticsCalculateTotalCount()
    {
        // GIVEN 20 group A testers did 100 tests
        // AND   10 group B testers did 30 tests
        $this->dbResult->setNumberOfGroupATesters(20)
            ->setGroupATotal(100)
            ->setNumberOfGroupBTesters(10)
            ->setGroupBTotal(30);

        // WHEN I retrieve the statistics
        $stats = $this->service->get($this->year, $this->month);

        // AND values are correct
        $this->assertEquals(100 / 20, $stats->getGroupA()->getTotal());
        $this->assertEquals(30 / 10, $stats->getGroupB()->getTotal());
    }

    public function testTotalCountHandlesDivisionByZero()
    {
        // GIVEN 0 group A testers did 0 tests
        // AND   0 group B testers did 0 tests
        $this->dbResult->setNumberOfGroupATesters(0)
            ->setGroupATotal(0)
            ->setNumberOfGroupBTesters(0)
            ->setGroupBTotal(0);

        // WHEN I retrieve the statistics
        $stats = $this->service->get($this->year, $this->month);

        // AND total count values are zeros
        $this->assertEquals(0, $stats->getGroupA()->getTotal());
        $this->assertEquals(0, $stats->getGroupB()->getTotal());
    }

    public function testCalculateAverageTime()
    {
        // GIVEN all the testers did 10 group A tests in 1 hour
        // AND 20 group B tests in 1 hour

        $this->dbResult->setGroupATotal(10);
        $this->dbResult->setGroupBTotal(20);

        $this->dbResult->setGroupACumulativeTestTime("3600");
        $this->dbResult->setGroupBCumulativeTestTime("3600");

        // WHEN I retrieve statistics
        $stats = $this->service->get($this->year, $this->month);

        // THEN the average group A test took 6 minutes
        $expected = new TimeSpan(0, 0, 6, 0);
        $average = $stats->getGroupA()->getAverageTime();
        $this->assertTrue($expected->equals($average));

        // AND the average group B test took 3 minutes
        $expected = new TimeSpan(0, 0, 3, 0);
        $average = $stats->getGroupB()->getAverageTime();
        $this->assertTrue($expected->equals($average));
    }

    public function testCalculateAverageTimeHandlesDivisionByZero()
    {
        // GIVEN testers didn't do any tests at all
        $this->dbResult->setGroupATotal(0);
        $this->dbResult->setGroupBTotal(0);

        $this->dbResult->setGroupACumulativeTestTime(0);
        $this->dbResult->setGroupBCumulativeTestTime(0);

        // WHEN I retrieve statistics
        $stats = $this->service->get($this->year, $this->month);

        // THEN the average group A test took 0 minutes
        $expected = new TimeSpan(0, 0, 0, 0);
        $average = $stats->getGroupA()->getAverageTime();
        $this->assertTrue($expected->equals($average));

        // AND the average group B test took 0 minutes
        $expected = new TimeSpan(0, 0, 0, 0);
        $average = $stats->getGroupB()->getAverageTime();
        $this->assertTrue($expected->equals($average));
    }

    public function testCalculateFailedPercentage()
    {
        // GIVEN testers did 100 tests in group A
        $this->dbResult->setGroupATotal(1000);

        // AND 200 tests in group B
        $this->dbResult->setGroupBTotal(200);

        // AND failed 10 test in group A
        $this->dbResult->setGroupAFailed(25);

        // AND failed 1000 test in group B
        $this->dbResult->setGroupBFailed(200);

        // WHEN I retrieve statistics
        $stats = $this->service->get($this->year, $this->month);

        // THEN the failed percentage is equal 2.5 in group A
        $this->assertEquals(2.5, $stats->getGroupA()->getPercentageFailed());

        // AND the failed percentage is equal 100 in group B
        $this->assertEquals(100, $stats->getGroupB()->getPercentageFailed());
    }

    public function testCalculateFailedPercentageHandlesDivisionByZero()
    {
        // GIVEN testers didn't do any tests at all
        $this->dbResult->setGroupATotal(0);
        $this->dbResult->setGroupBTotal(0);

        $this->dbResult->setGroupACumulativeTestTime(0);
        $this->dbResult->setGroupBCumulativeTestTime(0);

        // WHEN I retrieve statistics
        $stats = $this->service->get($this->year, $this->month);

        // THEN the failed percentage is equal 0 in group A
        $this->assertEquals(0, $stats->getGroupA()->getPercentageFailed());

        // AND the failed percentage is equal 0 in group B
        $this->assertEquals(0, $stats->getGroupB()->getPercentageFailed());
    }

    /**
     * @dataProvider dateProviderAverageVehicleAge
     * @param $groupAAge
     * @param $groupBAge
     * @param $isGroupAAgeAvailable
     * @param $isGroupBAgeAvailable
     */
    public function testThatDtosAreCorrectlyPopulatedWithAverageVehicleAge(
        $groupAAge, $groupBAge, $isGroupAAgeAvailable, $isGroupBAgeAvailable
    )
    {
        // GIVEN testers didn't do any tests at all
        $this->dbResult->setGroupATotal(0);
        $this->dbResult->setGroupBTotal(0);

        $this->dbResult->setGroupAAverageVehicleAgeInMonths($groupAAge);
        $this->dbResult->setIsGroupAAverageVehicleAgeAvailable($isGroupAAgeAvailable);
        $this->dbResult->setGroupBAverageVehicleAgeInMonths($groupBAge);
        $this->dbResult->setIsGroupBAverageVehicleAgeAvailable($isGroupBAgeAvailable);

        // WHEN I retrieve statistics
        $stats = $this->service->get($this->year, $this->month);

        // THEN the failed percentage is equal 0 in group A
        $this->assertEquals($groupAAge, $stats->getGroupA()->getAverageVehicleAgeInMonths());
        $this->assertEquals($isGroupAAgeAvailable, $stats->getGroupA()->getIsAverageVehicleAgeAvailable());

        // AND the failed percentage is equal 0 in group B
        $this->assertEquals($groupBAge, $stats->getGroupB()->getAverageVehicleAgeInMonths());
        $this->assertEquals($isGroupBAgeAvailable, $stats->getGroupB()->getIsAverageVehicleAgeAvailable());
    }

    public function testRepositoryIsNotCalledIfTheStatisticsAreStored()
    {
        $expectedMonths = 12;

        // GIVEN I already queried the repository for statistics
        $storedData = new NationalPerformanceReportDto();
        $storedData->getReportStatus()->setIsCompleted(true);
        $storedData->setGroupA((new MotTestingPerformanceDto())->setAverageVehicleAgeInMonths($expectedMonths));
        $storedData->getReportStatus()->setIsCompleted(true);
        $key = $this->getReportKey($this->year, $this->month);
        $this->storage->storeDto($key, $storedData);

        // WHEN I retrieve statistics
        $stats = $this->service->get($this->year, $this->month);

        // THEN the repository is not used
        $this->assertEquals(0, $this->repoStatisticsSpy->invocationCount());

        // AND the data comes from storage
        $this->assertEquals($expectedMonths, $stats->getGroupA()->getAverageVehicleAgeInMonths());

        // AND statistics are marked as available
        $this->assertTrue($stats->getReportStatus()->getIsCompleted());
    }

    public function testStoreNewStatistics()
    {
        // GIVEN The statistics are not stored
        $this->storage->clear();

        // WHEN I retrieve statistics
        $stats = $this->service->get($this->year, $this->month);

        // THEN the repo is called
        $this->assertEquals(1, $this->repoStatisticsSpy->invocationCount());

        // AND the report based on repository data is stored in storage
        /** @var NationalPerformanceReportDto $report */
        $report = $this->storage->getAsDto($this->getReportKey($this->year, $this->month), NationalPerformanceReportDto::class);

        $this->assertInstanceOf(NationalPerformanceReportDto::class, $report);

        // AND is has status "completed"
        $this->assertTrue($report->getReportStatus()->getIsCompleted());

        // AND data is marked as available
        $this->assertTrue($stats->getReportStatus()->getIsCompleted());
    }

    public function testNewStatisticGenerationCreatesATemporaryInProgressDocument()
    {
        /** @var KeyValueStorageInterface |\PHPUnit_Framework_MockObject_MockObject $storage */
        $storage = XMock::of(KeyValueStorageInterface::class);
        $storageSpy = new MethodSpy($storage, 'storeDto');
        $nationalStorage = new NationalTesterPerformanceStatisticsStorage($storage);
        $service = new NationalStatisticsService($this->repository, $nationalStorage, $this->dateTimeHolder, $this->timeoutPeriod);

        // WHEN I retrieve statistics
        $service->get($this->year, $this->month);

        // THEN the temporary statistics are created
        $this->assertGreaterThan(0, $storageSpy->invocationCount());
        /** @var NationalPerformanceReportDto $report */
        $report = $storageSpy->paramsForInvocation(0)[1];

        // with a complete flag set to false
        $this->assertFalse($report->getReportStatus()->getIsCompleted());

        // AND timeout set
        $expectedDate= $this->timeoutDateTime;
        $this->assertEquals($expectedDate, $report->getReportStatus()->getGenerationTimeoutDate());
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testExceptionIsThrownIfStatusIsInProgressButTimeoutIsNotSet()
    {
        // GIVEN the statistics report is being generated
        $report = new NationalPerformanceReportDto();
        $report->getReportStatus()->setIsCompleted(false);
        $this->storage->storeDto($this->getReportKey($this->year, $this->month), $report);
        // AND the timeout has not been set

        // WHEN I get statistics
        $this->service->get($this->year, $this->month);

        // THEN I get an exception
    }

    public function testUserGetsNotAvailableMessageWhenStatisticsAreGenerating()
    {
        // GIVEN the statistics report is being generated
        $report = new NationalPerformanceReportDto();
        $report->getReportStatus()->setIsCompleted(false);
        $timeoutDate = clone $this->timeoutDateTime;
        $timeoutDate->sub(new \DateInterval('PT1S'));
        $report->getReportStatus()->setGenerationTimeoutDate($timeoutDate);

        // AND generation time has not yet passed

        $this->storage->storeDto($this->getReportKey($this->year, $this->month), $report);

        // WHEN I get statistics
        $stats = $this->service->get($this->year, $this->month);

        // THEN repository is not called
        $this->assertEquals(0, $this->repoStatisticsSpy->invocationCount());

        // AND user gets information that the statistics are not available
        $this->assertFalse($stats->getReportStatus()->getIsCompleted());
    }

    public function testUserRestartsReportGenerationIfItTimeouts()
    {
        // GIVEN the statistics report is being generated,
        $report = new NationalPerformanceReportDto();
        $report->getReportStatus()->setIsCompleted(false);

        // AND the generation time has passed
        $tooLate = $this->dateTimeHolder->getCurrent();
        $tooLate->sub(new \DateInterval('PT1S'));

        $report->getReportStatus()->setGenerationTimeoutDate($tooLate);
        $this->storage->storeDto($this->getReportKey($this->year, $this->month), $report);

        // WHEN I get statistics
        $stats = $this->service->get($this->year, $this->month);

        // THEN the repo is used to generate stats
        $this->assertEquals(1, $this->repoStatisticsSpy->invocationCount());

        // AND the report is saved in storage
        /** @var NationalPerformanceReportDto $report */
        $report = $this->storage->getAsDto($this->getReportKey($this->year, $this->month), NationalPerformanceReportDto::class);

        $this->assertInstanceOf(NationalPerformanceReportDto::class, $report);

        // AND is has status "completed"
        $this->assertTrue($report->getReportStatus()->getIsCompleted());

        // AND data is marked as available
        $this->assertTrue($stats->getReportStatus()->getIsCompleted());
    }

    public function dateProviderAverageVehicleAge()
    {
        return [
            [0, 0, false, false],
            [4, 101.2, true, true],
            [0, 1.22, false, true],
            [33.3, 0, true, false],
        ];
    }

    private function getReportKey($year, $month)
    {
        $keyGenerator = new S3KeyGenerator();
        return $keyGenerator->generateForNationalTesterStatistics($year, $month);
    }
}
