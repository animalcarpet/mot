<?php

namespace DvsaEntitiesTest\DqlBuilder;

use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\DqlBuilder\NativeQueryBuilder;

/**
 * Class NativeQueryBuilderTest.
 */
class NativeQueryBuilderTest extends AbstractServiceTestCase
{
    /**
     * @var NativeQueryBuilder
     */
    protected $queryBuilder;

    public function setup()
    {
        $this->queryBuilder = new NativeQueryBuilder();
    }

    public function testResetPart()
    {
        $this->queryBuilder->select('testSelect1', 'select1')
            ->select('testSelect2', 'select2')
            ->from('testFrom1', 'from1')
            ->from('testFrom2')
            ->join('testJoin1', 'join1', 'cond')
            ->join('testJoin2', null, 'cond')
            ->join('testJoin3', null, 'cond')
            ->andWhere('testWhere1')
            ->andWhere('testWhere2', 'where2')
            ->orderBy('testOrder1', 'orderBy1')
            ->orderBy('testOrder2')
            ->setLimit(100)
            ->setOffset(99);

        $expect =
            'SELECT testSelect1, testSelect2 '.
            'FROM testFrom1 AS from1, testFrom2 '.
            'INNER JOIN testJoin1 AS join1 ON cond '.
            'INNER JOIN testJoin2 ON cond '.
            'INNER JOIN testJoin3 ON cond '.
            'WHERE 1=1 AND testWhere1 AND testWhere2 '.
            'ORDER BY testOrder1, testOrder2 '.
            'LIMIT 100 OFFSET 99';

        $this->assertEquals($expect, $this->queryBuilder->getSql());

        //  logic block:: remove parts by key
        $this->queryBuilder
            ->resetPart('select', 'select1')
            ->resetPart('from', 'testFrom2')
            ->resetPart('join', 'join1')
            ->resetPart('join', 'testJoin3')
            ->resetPart('where', 'where2')
            ->resetPart('orderBy', 'orderBy1')
            ->setOffset(0);

        $expect =
            'SELECT testSelect2 '.
            'FROM testFrom1 AS from1 '.
            'INNER JOIN testJoin2 ON cond '.
            'WHERE 1=1 AND testWhere1 '.
            'ORDER BY testOrder2 '.
            'LIMIT 100';

        $this->assertEquals($expect, $this->queryBuilder->getSql());

        //  logic block:: remove parts and Substitute
        $this->queryBuilder
            ->resetPart('select')
            ->select('testSelectX')
            ->resetPart('from')
            ->from('testFromX')
            ->resetPart('join')
            ->resetPart('where')
            ->resetPart('orderBy')
            ->setLimit(0);

        $this->assertEquals(''.'SELECT testSelectX FROM testFromX', $this->queryBuilder->getSql());
    }

    public function testParameters()
    {
        $params = [
            [':mtnumber' => 20],
            [':mtstatus' => 30],
        ];

        //  logic block:: set few parameters
        $this->queryBuilder->setParameters($params);
        $this->assertEquals($params, $this->queryBuilder->getParameters());

        //  logic block:: add parameter
        $this->queryBuilder->setParameter('dateTime', new \DateTime('2013-12-11 23:24:25'));

        $this->assertEquals(
            [':dateTime' => '2013-12-11 23:24:25'] + $params,
            $this->queryBuilder->getParameters()
        );
    }
}
