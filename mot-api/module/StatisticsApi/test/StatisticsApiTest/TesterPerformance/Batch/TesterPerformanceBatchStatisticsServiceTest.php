<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\Batch;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\TesterPerformanceBatchStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\Mocking\KeyValueStorage\KeyValueStorageFake;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;

class TesterPerformanceBatchStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  KeyValueStorageInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $s3Service;
    private $dateTimeHolder;
    private $nationalStatisticsService;
    /** @var FeatureToggles $featureToggles */
    private $featureToggles;

    public function setUp()
    {
        $this->s3Service = new KeyValueStorageFake();
        $this->dateTimeHolder = new TestDateTimeHolder(new \DateTime('2016-01-14'));
        $this->nationalStatisticsService = XMock::of(NationalStatisticsService::class);
        $this->featureToggles = XMock::of(FeatureToggles::class);
        $this->featureToggles->expects($this->any())
            ->method('isDisabled')
            ->willReturn(true);
    }

    public function testReportGeneration()
    {
        $statisticsService = new TesterPerformanceBatchStatisticsService(
            $this->s3Service,
            $this->dateTimeHolder,
            $this->nationalStatisticsService,
            $this->featureToggles
        );

        $dtos = $statisticsService->generateReports();
        $this->assertEquals('National tester performance batch task - 1', $dtos[0]->getName());
        $this->assertEquals('National tester performance batch task - 3', $dtos[1]->getName());
    }

    public function testReportGenerationWithFeatureToggle()
    {
        $this->featureToggles = XMock::of(FeatureToggles::class);

        // When GQR_DISABLE_3_MONTHS_ENDPOINTS is set to true
        $this->featureToggles->expects($this->any())
            ->method('isDisabled')
            ->willReturn(false);

        $statisticsService = new TesterPerformanceBatchStatisticsService(
            $this->s3Service,
            $this->dateTimeHolder,
            $this->nationalStatisticsService,
            $this->featureToggles
        );

        $dtos = $statisticsService->generateReports();
        $this->assertEquals('National tester performance batch task - 1', $dtos[0]->getName());
        // There should only be 1 month batch task
        $this->assertArrayNotHasKey(1, $dtos);
    }

    public function testReportGenerationWithSomeDataAlreadyGenerated()
    {
        $this->s3Service = XMock::of(KeyValueStorageInterface::class);
        $this->s3Service->method('listKeys')->willReturn(['tester-quality-information/tester-performance/national/2016-01-3.json']);

        $statisticsService = new TesterPerformanceBatchStatisticsService(
            $this->s3Service,
            $this->dateTimeHolder,
            $this->nationalStatisticsService,
            $this->featureToggles
        );

        $dtos = $statisticsService->generateReports();
        $this->assertCount(1, $dtos);
        $this->assertEquals('National tester performance batch task - 1', $dtos[0]->getName());
    }
}