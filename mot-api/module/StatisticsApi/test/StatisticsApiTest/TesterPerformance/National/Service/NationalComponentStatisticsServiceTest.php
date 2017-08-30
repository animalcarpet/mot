<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\National\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryResult\ComponentFailRateResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Repository\NationalComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Storage\NationalComponentFailRateStorage;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\Mocking\KeyValueStorage\KeyValueStorageFake;
use DvsaCommonTest\TestUtils\XMock;

class NationalComponentStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    const THREE_MONTHS_RANGE = 3;

    /** @var NationalComponentStatisticsRepository */
    private $repository;

    /** @var KeyValueStorageFake */
    private $storage;

    /** @var TestDateTimeHolder */
    private $dateTimeHolder;

    protected function setUp()
    {
        /* @var NationalComponentStatisticsRepository $repository */
        $this->repository = XMock::of(NationalComponentStatisticsRepository::class);
        $this
            ->repository
            ->expects($this->any())
            ->method('get')
            ->willReturn([$this->createComponentFailRateResult()]);

        $this
            ->repository
            ->expects($this->any())
            ->method('getNationalFailedMotTestCount')
            ->willReturn(1);

        $this->storage = new KeyValueStorageFake();
    }

    public function testGetReturnsDto()
    {
        $dto = $this->createService()->get(self::THREE_MONTHS_RANGE, VehicleClassGroupCode::CARS_ETC);
        $this->assertInstanceOf(NationalComponentStatisticsDto::class, $dto);

        $this->assertNationalComponentStatisticsDto(
            $dto,
            self::THREE_MONTHS_RANGE,
            VehicleClassGroupCode::CARS_ETC
        );
    }

    private function assertNationalComponentStatisticsDto(NationalComponentStatisticsDto $dto, $expectedMonthRange, $expectedGroup)
    {
        $this->assertEquals($expectedGroup, $dto->getGroup());
        $this->assertEquals($expectedMonthRange, $dto->getMonthRange());
        $this->assertCount(1, $dto->getComponents());
        $this->assertInstanceOf(ComponentDto::class, $dto->getComponents()[0]);
    }

    /** NationalComponentStatisticsService */
    private function createService()
    {
        return new NationalComponentStatisticsService(
            new NationalComponentFailRateStorage($this->storage),
            $this->repository,
            new LastMonthsDateRange($this->getDateTimeHolder())
        );
    }

    private function createComponentFailRateResult()
    {
        $result = new ComponentFailRateResult();
        $result
            ->setFailedCount(1)
            ->setTestItemCategoryId(1)
            ->setTestItemCategoryName('Category name');

        return $result;
    }

    private function getDateTimeHolder()
    {
        if (is_null($this->dateTimeHolder)) {
            $this->dateTimeHolder =  new TestDateTimeHolder(new \DateTime());
        }

        return $this->dateTimeHolder;
    }
}
