<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\National\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryResult\ComponentFailRateResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Repository\NationalComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Storage\NationalComponentFailRateStorage;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\Mocking\KeyValueStorage\KeyValueStorageFake;
use DvsaCommonTest\TestUtils\XMock;

class NationalComponentStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var NationalComponentStatisticsRepository */
    private $repository;

    /** @var KeyValueStorageFake */
    private $storage;

    protected function setUp()
    {
        /** @var NationalComponentStatisticsRepository $repository */
        $this->repository = XMock::of(NationalComponentStatisticsRepository::class);
        $this
            ->repository
            ->expects($this->any())
            ->method("get")
            ->willReturn([$this->createComponentFailRateResult()]);

        $this
            ->repository
            ->expects($this->any())
            ->method("getNationalFailedMotTestCount")
            ->willReturn(1);

        $this->storage = new KeyValueStorageFake();
    }

    public function testGetReturnsDto()
    {
        $currentDate = $this->getDateTimeHolder()->getCurrentDate();
        $date = $currentDate->sub(new \DateInterval('P2M'));
        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $dto = $this->createService()->get($year, $month, VehicleClassGroupCode::CARS_ETC);
        $this->assertInstanceOf(NationalComponentStatisticsDto::class, $dto);

        $this->assertNationalComponentStatisticsDto(
            $dto,
            $year,
            $month,
            VehicleClassGroupCode::CARS_ETC
        );
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetThrowsExceptionIfValidationFailed()
    {
        $currentYear = (int)DateUtils::firstOfThisMonth()->format("Y");
        $date = $this->getDateTimeHolder()->getCurrentDate();
        $nextMonth = (int)$date->modify("next month")->format("m");

        $this->createService()->get($currentYear, $nextMonth, VehicleClassGroupCode::BIKES);
    }

    private function assertNationalComponentStatisticsDto(NationalComponentStatisticsDto $dto, $expectedYear, $expectedMonth, $expectedGroup)
    {
        $this->assertEquals($expectedYear, $dto->getYear());
        $this->assertEquals($expectedMonth, $dto->getMonth());
        $this->assertEquals($expectedGroup, $dto->getGroup());
        $this->assertCount(1, $dto->getComponents());
        $this->assertInstanceOf(ComponentDto::class, $dto->getComponents()[0]);
    }

    /** NationalComponentStatisticsService */
    private function createService()
    {
        return new NationalComponentStatisticsService(
            new NationalComponentFailRateStorage($this->storage),
            $this->repository,
            $this->getDateTimeHolder()
        );
    }

    private function createComponentFailRateResult()
    {
        $result = new ComponentFailRateResult();
        $result
            ->setFailedCount(1)
            ->setTestItemCategoryId(1)
            ->setTestItemCategoryName("Category name");

        return $result;
    }

    private function getDateTimeHolder()
    {
        return new TestDateTimeHolder(new \DateTime("2016-06-21"));
    }
}
