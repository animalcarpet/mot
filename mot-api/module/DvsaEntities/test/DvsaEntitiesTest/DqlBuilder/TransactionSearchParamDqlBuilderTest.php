<?php

namespace DvsaEntitiesTest\DqlBuilder;

use Doctrine\ORM\EntityManager;
use DvsaEntities\DqlBuilder\SearchParam\TransactionSearchParam;
use DvsaEntities\DqlBuilder\TransactionSearchParamDqlBuilder;

class TransactionSearchParamDqlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionSearchParamDqlBuilder
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

        $params = new TransactionSearchParam();
        $params
            ->setDateFrom('2014-01-01')
            ->setDateTo('2014-01-02')
            ->setOrganisationId(1)
            ->setStatus(true);

        $this->dqlBuilder = new TransactionSearchParamDqlBuilder($emMock, $params);
    }

    public function testGenerate()
    {
        $result = $this->dqlBuilder->generate();

        $this->assertInstanceOf(\DvsaEntities\DqlBuilder\TransactionSearchParamDqlBuilder::class, $result);

        $searchDql =
            "SELECT transaction from DvsaEntities\Entity\TestSlotTransaction transaction ".
            "LEFT JOIN DvsaEntities\Entity\Payment p WITH transaction.payment = p.id ".
            "LEFT JOIN DvsaEntities\Entity\Organisation o WITH transaction.organisation = o.id ".
            'WHERE (transaction.organisation = :ORGANISATION_ID) '.
            'AND (transaction.status = :STATUS) '.
            'AND (transaction.completedOn >= :DATE_FROM) AND (transaction.completedOn <= :DATE_TO) '.
            'ORDER BY p.0 ';

        $this->assertEquals($searchDql, $this->dqlBuilder->getSearchDql());
    }
}
