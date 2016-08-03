<?php
namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\QueryResult\ComponentFailRateResult;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Repository\ComponentStatisticsRepository;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;

class ComponentStatisticsRepositoryTest extends \PHPUnit_Framework_TestCase
{

    /** @var  EntityManager */
    private $entityManagerMock;
    /** @var  ComponentStatisticsRepository */
    private $sut;

    public function setUp()
    {
        $this->markTestSkipped();
        $this->entityManagerMock = $this->getEntityManagerMock();

        $this->sut = new ComponentStatisticsRepository($this->entityManagerMock);
    }

    public function testGetResultMapsResults()
    {
        $sql = '';
        $params = [
            'groupCode' => null,
            'startDate' => null,
            'endDate'   => null,
        ];

        /** @var ComponentFailRateResult[] $result */
        $results = $this->sut->getResult($sql, $params);
        $this->assertEquals(2, count($results));
        $this->assertEquals($results[0]->getTestItemCategoryId(), 1);
        $this->assertEquals($results[0]->getTestItemCategoryName(), 'Test item category 1');
        $this->assertEquals($results[0]->getFailedCount(), 5);
    }

    private function getEntityManagerMock()
    {
        /** @var EntityManager */
        $entityManagerMock = XMock::of(EntityManager::class);
        $entityManagerMock
            ->expects($this->any())
            ->method('createNativeQuery')
            ->willReturn($this->getNativeQueryMock());

        return $entityManagerMock;
    }

    private function getNativeQueryMock()
    {
        $nativeQueryMock = XMock::of(AbstractQuery::class);
        $nativeQueryMock
            ->expects($this->any())
            ->method('getScalarResult')
            ->willReturn([
                0 => [
                    'testItemCategoryId'   => 1,
                    'testItemCategoryName' => 'Test item category 1',
                    'failedCount'          => 2,
                ],
                1 => [
                    'testItemCategoryId'   => 2,
                    'testItemCategoryName' => 'Test item category 2',
                    'failedCount'          => 5,
                ],
            ]);

        return $nativeQueryMock;
    }
}
