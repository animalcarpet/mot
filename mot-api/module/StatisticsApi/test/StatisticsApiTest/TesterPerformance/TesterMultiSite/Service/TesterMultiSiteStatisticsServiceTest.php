<?php
namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\TesterMultiSite\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Mapper\TesterStatisticsMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\QueryResult\TesterMultiSitePerformanceResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Repository\TesterMultiSiteStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Service\TesterMultiSiteStatisticsService;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceReportDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;

class TesterMultiSiteStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    const TESTER_ID = 1;
    const YEAR = 2016;
    const MONTH = 8;
    const ADDRESS_LINE_1 = "address line 1";
    const TOWN = "town";
    const COUNTRY = "country";
    const POSTCODE = "postcode";
    const SITE_ID = 1;
    const SITE_NAME_1 = "site1";
    const SITE_NAME_2 = "site2";
    const SITE_NAME_3 = "site3";

    /** @var  TesterMultiSiteStatisticsRepository */
    private $repository;
    /** @var  TesterStatisticsMapper */
    private $mapper;

    /** @var  TesterMultiSiteStatisticsService */
    private $sut;

    public function setUp()
    {
        $this->repository = XMock::of(TesterMultiSiteStatisticsRepository::class);
        $this->repository->expects($this->once())
            ->method("get")
            ->willReturn($this->createRepositoryResult());

        $this->mapper = new TesterStatisticsMapper();

        $this->sut = new TesterMultiSiteStatisticsService(
            $this->repository,
            $this->mapper
        );
    }

    public function testGetReturnsDto()
    {
        $result = $this->sut->get(self::TESTER_ID, self::YEAR, self::MONTH);

        $this->assertInstanceOf(TesterMultiSitePerformanceReportDto::class, $result);
        $this->assertCount(2, $result->getA());
        $this->assertCount(1, $result->getB());

        $this->assertDtoForGroup($result->getA()[0], self::SITE_NAME_1);
        $this->assertDtoForGroup($result->getA()[1], self::SITE_NAME_2);
        $this->assertDtoForGroup($result->getB()[0], self::SITE_NAME_3);
    }

    private function createRepositoryResult()
    {
        return [
            $this->createResultRow(VehicleClassGroupCode::BIKES, self::SITE_NAME_1),
            $this->createResultRow(VehicleClassGroupCode::BIKES, self::SITE_NAME_2),
            $this->createResultRow(VehicleClassGroupCode::CARS_ETC, self::SITE_NAME_3),
        ];
    }

    private function createResultRow($groupCode, $siteName)
    {
        $address = new Address();
        $address->setAddressLine1(self::ADDRESS_LINE_1)
            ->setTown(self::TOWN)
            ->setCountry(self::COUNTRY)
            ->setPostcode(self::POSTCODE);

        $testerPerformanceResult = new TesterMultiSitePerformanceResult();

        $testerPerformanceResult->setVehicleClassGroup($groupCode)
            ->setSiteId(self::SITE_ID)
            ->setSiteName($siteName)
            ->setSiteAddress($address);

        return $testerPerformanceResult;
    }

    private function assertDtoForGroup(TesterMultiSitePerformanceDto $dto, $siteName)
    {
        $this->assertEquals(self::SITE_ID, $dto->getSiteId());
        $this->assertEquals($siteName, $dto->getSiteName());
        $this->assertEquals(self::ADDRESS_LINE_1, $dto->getSiteAddress()->getAddressLine1());
        $this->assertEquals(self::TOWN, $dto->getSiteAddress()->getTown());
        $this->assertEquals(self::COUNTRY, $dto->getSiteAddress()->getCountry());
        $this->assertEquals(self::POSTCODE, $dto->getSiteAddress()->getPostcode());
    }

}