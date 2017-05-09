<?php

namespace DvsaEntitiesTest\DqlBuilder;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaEntities\DqlBuilder\SearchParam\SiteSlotUsageParam;
use DvsaEntities\DqlBuilder\SiteSlotUsageParamDqlBuilder;

class SiteSlotUsageParamDqlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SiteSlotUsageParamDqlBuilder
     */
    private $dqlBuilder;

    public function setUp()
    {
        $doctrineConfigMock = $this->getMockBuilder(\Doctrine\ORM\Configuration::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultQueryHints', 'isSecondLevelCacheEnabled'])
            ->getMock();

        $doctrineConfigMock->expects($this->any())
            ->method('isSecondLevelCacheEnabled')
            ->will($this->returnValue(false));

        $emMock = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $emMock->expects($this->any())->method('getConfiguration')->willReturn($doctrineConfigMock);

        $emMock->expects($this->any())
            ->method('createQuery')
            ->will($this->returnValue(new \Doctrine\ORM\Query($emMock)));

        $params = new SiteSlotUsageParam();
        $params
            ->setDateFrom('2014-01-01')
            ->setDateTo('2014-01-02');

        $this->dqlBuilder = new SiteSlotUsageParamDqlBuilder($emMock, $params);
    }

    public function testGenerateWithDefaultSort()
    {
        $result = $this->dqlBuilder->generate();
        $searchDql = 'SELECT t from DvsaEntities\\Entity\\MotTest t '
            .'LEFT JOIN DvsaEntities\\Entity\\Person p WITH t.tester = p.id '
            .'LEFT JOIN DvsaEntities\\Entity\\Vehicle v WITH v.id = t.vehicle '
            .'LEFT JOIN DvsaEntities\\Entity\\MotTestType tt WITH t.motTestType = tt.id '
            .'LEFT JOIN DvsaEntities\\Entity\\MotTestStatus ts WITH t.status = ts.id '
            ."WHERE (ts.name = '".MotTestStatusName::PASSED."') "
            .'AND (tt.code IN (:TEST_TYPES)) '
            .'AND (t.vehicleTestingStation = :SITE_ID) '
            .'AND (t.completedDate >= :DATE_FROM) '
            .'AND (t.completedDate <= :DATE_TO) '
            .'ORDER BY t.completedDate ';

        $this->assertInstanceOf(\DvsaEntities\DqlBuilder\SiteSlotUsageParamDqlBuilder::class, $result);
        $this->assertEquals($searchDql, $this->dqlBuilder->getSearchDql());
    }

    public function testGenerateWithTesterSort()
    {
        $this->dqlBuilder->getParams()
            ->setSortColumnId(SiteSlotUsageParam::SORT_COL_TESTER)
            ->setSortDirection(SearchParamConst::SORT_DIRECTION_DESC);

        $this->dqlBuilder->generate();

        $searchDql = 'SELECT t from DvsaEntities\\Entity\\MotTest t '
            .'LEFT JOIN DvsaEntities\\Entity\\Person p WITH t.tester = p.id '
            .'LEFT JOIN DvsaEntities\\Entity\\Vehicle v WITH v.id = t.vehicle '
            .'LEFT JOIN DvsaEntities\\Entity\\MotTestType tt WITH t.motTestType = tt.id '
            .'LEFT JOIN DvsaEntities\\Entity\\MotTestStatus ts WITH t.status = ts.id '
            ."WHERE (ts.name = '".MotTestStatusName::PASSED."') "
            .'AND (tt.code IN (:TEST_TYPES)) '
            .'AND (t.vehicleTestingStation = :SITE_ID) '
            .'AND (t.completedDate >= :DATE_FROM) '
            .'AND (t.completedDate <= :DATE_TO) '
            .'ORDER BY p.firstName DESC, p.familyName';

        $this->assertEquals($searchDql, $this->dqlBuilder->getSearchDql());
    }

    public function testGenerateWithVrnSort()
    {
        $this->dqlBuilder->getParams()->setSortColumnId(SiteSlotUsageParam::SORT_COL_VRN);

        $this->dqlBuilder->generate();

        $searchDql = 'SELECT t from DvsaEntities\\Entity\\MotTest t '
            .'LEFT JOIN DvsaEntities\\Entity\\Person p WITH t.tester = p.id '
            .'LEFT JOIN DvsaEntities\\Entity\\Vehicle v WITH v.id = t.vehicle '
            .'LEFT JOIN DvsaEntities\\Entity\\MotTestType tt WITH t.motTestType = tt.id '
            .'LEFT JOIN DvsaEntities\\Entity\\MotTestStatus ts WITH t.status = ts.id '
            ."WHERE (ts.name = '".MotTestStatusName::PASSED."') "
            .'AND (tt.code IN (:TEST_TYPES)) '
            .'AND (t.vehicleTestingStation = :SITE_ID) '
            .'AND (t.completedDate >= :DATE_FROM) '
            .'AND (t.completedDate <= :DATE_TO) '
            .'ORDER BY v.registration ';

        $this->assertEquals($searchDql, $this->dqlBuilder->getSearchDql());
    }
}
