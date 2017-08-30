<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\Batch\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\BatchPersonTestQualityInformationService;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\TqiRfrCountRepository;
use DvsaEntities\Repository\TqiTestCountRepository;

class BatchPersonTestQualityInformationServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  TqiTestCountRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $tqiTestCountRepository;
    /** @var  TqiRfrCountRepository | \PHPUnit_Framework_MockObject_MockObject*/
    private $tqiRfrCountRepository;
    /** @var  BatchPersonTestQualityInformationService */
    private $service;

    public function setUp(){
        $this->tqiTestCountRepository = XMock::of(TqiTestCountRepository::class);
        $this->tqiRfrCountRepository = XMock::of(TqiRfrCountRepository::class);

        $this->service = new BatchPersonTestQualityInformationService(
            new TestDateTimeHolder(new \DateTime('2015-10-21')),
            $this->tqiTestCountRepository,
            $this->tqiRfrCountRepository
        );
    }

    /**
     * @dataProvider dataProviderMonths
     */
    public function testMonthValidation($moth, $isInvalid)
    {
        if($isInvalid){
            $this->expectException(\InvalidArgumentException::class);
        }


        $this->tqiTestCountRepository->method('populateTableWithData')->willReturn(true);
        $this->tqiRfrCountRepository->method('populateTableWithData')->willReturn(true);

        $this->service->generatePersonTestStatistics($moth);
        $this->service->generatePersonComponentStatistics($moth);
    }

    public function testThatServiceVerifiesExistingDataBeforeGeneratingNewOnes()
    {
        $this->tqiTestCountRepository->method('checkIfThereAreDataForPeriod')->willReturn(1);
        $this->tqiRfrCountRepository->method('checkIfThereAreDataForPeriod')->willReturn(1);

        $this->tqiTestCountRepository->expects($this->never())->method('populateTableWithData');
        $this->tqiRfrCountRepository->expects($this->never())->method('populateTableWithData');

        $this->service->generatePersonTestStatistics(1);
        $this->service->generatePersonComponentStatistics(1);
    }

    /**
     * @dataProvider dataProviderDates
     */
    public function testThatServiceIsUsingCorrectStartEndDates($monthsAgo, $expectedDate)
    {
        $this->tqiTestCountRepository->method('checkIfThereAreDataForPeriod')->willReturn(0);
        $this->tqiRfrCountRepository->method('checkIfThereAreDataForPeriod')->willReturn(0);

        $this->tqiTestCountRepository->expects($this->once())->method('populateTableWithData')->with($expectedDate)->willReturn(true);
        $this->tqiRfrCountRepository->expects($this->once())->method('populateTableWithData')->with($expectedDate)->willReturn(true);

        $this->service->generatePersonTestStatistics($monthsAgo);
        $this->service->generatePersonComponentStatistics($monthsAgo);
    }

    public function testAlertingAboutTestJobFailure()
    {
        $this->tqiTestCountRepository->method('checkIfThereAreDataForPeriod')->willReturn(0);
        $this->tqiTestCountRepository->method('populateTableWithData')->willReturn(false);
        $this->expectException(\Exception::class);

        $this->service->generatePersonTestStatistics(1);
    }

    public function testAlertingAboutRfrJobFailure()
    {
        $this->tqiRfrCountRepository->method('checkIfThereAreDataForPeriod')->willReturn(0);
        $this->tqiRfrCountRepository->method('populateTableWithData')->willReturn(false);
        $this->expectException(\Exception::class);

        $this->service->generatePersonComponentStatistics(1);
    }

    public function testClearingDbOfOldData()
    {
        $this->tqiTestCountRepository->method('populateTableWithData')->willReturn(true);
        $this->tqiRfrCountRepository->method('populateTableWithData')->willReturn(true);

        $this->tqiTestCountRepository->expects($this->once())->method('deleteStatsOlderThan')->with(new \DateTime('2015-7-01'));
        $this->tqiRfrCountRepository->expects($this->once())->method('deleteStatsOlderThan')->with(new \DateTime('2015-7-01'));

        $this->service->generatePersonTestStatistics(1);
        $this->service->generatePersonComponentStatistics(1);
    }

    public function dataProviderMonths()
    {
        return [
            [1, false],
            [2, false],
            [3, false],
            [0, true],
            [-1, true],
            [4, true],
            [5, true],
            [100, true],
        ];
    }

    public function dataProviderDates()
    {
        return [
            [1, new \DateTime('2015-09-01')],
            [2, new \DateTime('2015-08-01')],
            [3, new \DateTime('2015-07-01')],
        ];
    }
}