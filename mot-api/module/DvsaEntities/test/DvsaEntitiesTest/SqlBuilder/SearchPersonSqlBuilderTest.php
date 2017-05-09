<?php

namespace DvsaEntitiesTest\SqlBuilder;

use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommon\Model\SearchPersonModel;
use DvsaEntities\SqlBuilder\SearchPersonSqlBuilder;

/**
 * Unit tests for SearchPersonSqlBuilder.
 */
class SearchPersonSqlBuilderTest extends AbstractServiceTestCase
{
    const FAKE_SQL_STATEMENT = 'FAKE SQL STATEMENT';

    public function test_getParams_oneParam_shouldReturnArrayWithOneParam()
    {
        $entityManagerMock = $this->setupEntityManagerMock();
        $sqlBuilder = new SearchPersonSqlBuilder(
            $entityManagerMock,
            new SearchPersonModel(1, null, null, null, null, null, null)
        );
        $params = $sqlBuilder->getParams();

        $this->assertCount(1, $params);
        $this->assertArrayHasKey('username', $params);
    }

    public function test_getParams_twoParams_shouldReturnArrayWithTwoParam()
    {
        $entityManagerMock = $this->setupEntityManagerMock();
        $sqlBuilder = new SearchPersonSqlBuilder(
            $entityManagerMock,
            new SearchPersonModel(1, null, null, '2222-10-10', null, null, null)
        );
        $params = $sqlBuilder->getParams();

        $this->assertCount(2, $params);
        $this->assertArrayHasKey('username', $params);
        $this->assertArrayHasKey('dateOfBirth', $params);
    }

    public function test_getParams_allParams_shouldReturnArrayWithFiveParam()
    {
        $entityManagerMock = $this->setupEntityManagerMock();
        $sqlBuilder = new SearchPersonSqlBuilder(
            $entityManagerMock,
            new SearchPersonModel('username', 'First', 'Surname', '2222-10-10', 'Stoke Gifford', 'MY PSC', null)
        );
        $params = $sqlBuilder->getParams();

        $this->assertCount(6, $params);
        $this->assertArrayHasKey('username', $params);
        $this->assertArrayHasKey('firstName', $params);
        $this->assertArrayHasKey('lastName', $params);
        $this->assertArrayHasKey('dateOfBirth', $params);
        $this->assertArrayHasKey('town', $params);
        $this->assertArrayHasKey('postcode', $params);
    }

    public function test_getSql()
    {
        $entityManagerMock = $this->setupEntityManagerMock();
        $sqlBuilder = new SearchPersonSqlBuilder(
            $entityManagerMock,
            new SearchPersonModel(1, null, null, null, null, null, null)
        );
        $sql = $sqlBuilder->getSql();

        $this->assertEquals(self::FAKE_SQL_STATEMENT, $sql);
    }

    private function setupEntityManagerMock()
    {
        $entityManagerMock = $this->getMockEntityManager();
        $dbalConnectionMock = $this->getMockWithDisabledConstructor(\Doctrine\DBAL\Connection::class);
        $queryBuilderMock = $this->getMockWithDisabledConstructor(\Doctrine\DBAL\Query\QueryBuilder::class);

        $this->expectMethodAtAnyTimeReturnSelf($queryBuilderMock, 'select');
        $this->expectMethodAtAnyTimeReturnSelf($queryBuilderMock, 'from');
        $this->expectMethodAtAnyTimeReturnSelf($queryBuilderMock, 'where');
        $this->expectMethodAtAnyTimeReturnSelf($queryBuilderMock, 'andWhere');
        $this->expectMethodAtAnyTimeReturnSelf($queryBuilderMock, 'join');
        $this->expectMethodAtAnyTimeReturnSelf($queryBuilderMock, 'leftJoin');
        $this->expectMethodAtAnyTimeReturnSelf($queryBuilderMock, 'addOrderBy');
        $this->expectMethodAtAnyTimeReturnSelf($queryBuilderMock, 'add');

        $queryBuilderMock->expects($this->any())->method('getSQL')->willReturn(self::FAKE_SQL_STATEMENT);
        $dbalConnectionMock->expects($this->once())->method('createQueryBuilder')->willReturn($queryBuilderMock);
        $entityManagerMock->expects($this->once())->method('getConnection')->willReturn($dbalConnectionMock);

        return $entityManagerMock;
    }

    private function expectMethodAtAnyTimeReturnSelf(\PHPUnit_Framework_MockObject_MockObject $mock, $method)
    {
        $mock->expects($this->any())->method($method)->willReturnSelf();
    }
}
