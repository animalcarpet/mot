<?php

namespace DvsaEntitiesTest\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use DvsaEntities\Entity\PersonContactType;
use DvsaEntities\Repository\PersonRepository;

class PersonRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const EMAIL_ADDRESS = 'personrepositorytest@dvsa.test';

    /**
     * @group VM-10289
     */
    public function testFindPersonEmailReturnsNullOnEmptyResult()
    {
        $query = $this->createQueryMock();
        $query
            ->expects($this->once())
            ->method('getSingleScalarResult')
            ->willThrowException(new NoResultException());
        $entityManager = $this->createEntityManagerMock($query);
        $classMetadata = $this->createClassMetadataMock();

        $personRepository = new PersonRepository($entityManager, $classMetadata);

        $personId = 1;
        $contactType = new PersonContactType();

        $this->assertNull($personRepository->findPersonEmail($personId, $contactType));
    }

    /**
     * @group VM-10289
     */
    public function testFindPersonEmailReturnsEmailOnValidResult()
    {
        $query = $this->createQueryMock();
        $query
            ->expects($this->once())
            ->method('getSingleScalarResult')
            ->will($this->returnValue(self::EMAIL_ADDRESS));
        $entityManager = $this->createEntityManagerMock($query);
        $classMetadata = $this->createClassMetadataMock();

        $personRepository = new PersonRepository($entityManager, $classMetadata);

        $personId = 1;
        $contactType = new PersonContactType();

        $this->assertEquals(self::EMAIL_ADDRESS, $personRepository->findPersonEmail($personId, $contactType));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createClassMetadataMock()
    {
        return $this
            ->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createQueryMock()
    {
        return $this
            ->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param \Doctrine\ORM\AbstractQuery $query
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createEntityManagerMock(AbstractQuery $query)
    {
        $queryBuilder = $this
            ->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        foreach (['select', 'from', 'join', 'where', 'andWhere', 'setParameter', 'setMaxResults'] as $method) {
            $queryBuilder
                ->expects($this->atLeastOnce())
                ->method($method)
                ->will($this->returnValue($queryBuilder));
        }
        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        return $entityManager;
    }
}

class Query extends AbstractQuery
{
    public function getSQL()
    {
    }
    protected function _doExecute()
    {
    }
}
