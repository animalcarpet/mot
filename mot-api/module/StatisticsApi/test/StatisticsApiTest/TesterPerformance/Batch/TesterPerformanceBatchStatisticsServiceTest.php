<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\Batch;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\TesterPerformanceBatchStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\Mocking\KeyValueStorage\KeyValueStorageFake;
use DvsaCommonTest\TestUtils\XMock;

class TesterPerformanceBatchStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  KeyValueStorageInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $s3Service;
    private $dateTimeHolder;
    private $nationalStatisticsService;

    public function setUp()
    {
        $this->s3Service = new KeyValueStorageFake();
        $this->dateTimeHolder = new TestDateTimeHolder(new \DateTime('2016-01-14'));
        $this->nationalStatisticsService = XMock::of(NationalStatisticsService::class);
    }

    public function testReportGeneration()
    {
        $statisticsService = new TesterPerformanceBatchStatisticsService(
            $this->s3Service,
            $this->dateTimeHolder,
            $this->nationalStatisticsService
        );

        $dtos = $statisticsService->generateReports();
        $this->assertEquals('National tester performance batch task - 1', $dtos[0]->getName());
        $this->assertEquals('National tester performance batch task - 3', $dtos[1]->getName());
    }

    public function testReportGenerationWithSomeDataAlreadyGenerated()
    {
        $this->s3Service = XMock::of(KeyValueStorageInterface::class);
        $this->s3Service->method('listKeys')->willReturn(['tester-quality-information/tester-performance/national/2016-01-3.json']);

        $statisticsService = new TesterPerformanceBatchStatisticsService(
            $this->s3Service,
            $this->dateTimeHolder,
            $this->nationalStatisticsService
        );

        $dtos = $statisticsService->generateReports();
        $this->assertCount(1, $dtos);
        $this->assertEquals('National tester performance batch task - 1', $dtos[0]->getName());
    }
}