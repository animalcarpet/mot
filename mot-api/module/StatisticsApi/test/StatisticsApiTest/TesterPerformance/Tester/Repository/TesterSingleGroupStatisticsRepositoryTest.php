<?php
namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\Tester\Repository;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Repository\TesterSingleGroupStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterPerformanceResult;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\TestUtils\XMock;

class TesterSingleGroupStatisticsRepositoryTest /* extends \PHPUnit_Framework_TestCase */
{
    const TESTER_ID = 1;
    const GROUP_CODE = VehicleClassGroupCode::BIKES;
    const YEAR = 2010;
    const MONTH = 10;

    /** @var TesterSingleGroupStatisticsRepository */
    private $sut;
    /** @var  EntityManager */
    private $entityManager;

    public function setUp()
    {
        $this->entityManager = XMock::of(EntityManager::class);
        $this->sut = new TesterSingleGroupStatisticsRepository($this->entityManager);
    }

    /*
    public function testGetReturnsResult()
    {
        $result = $this->sut->get(self::TESTER_ID, self::GROUP_CODE, self::YEAR, self::MONTH);
        $this->assertInstanceOf(TesterPerformanceResult::class, $result);
    }
    */
}