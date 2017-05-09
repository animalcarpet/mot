<?php

namespace Dvsa\Mot\AuditApiTest\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Dvsa\Mot\AuditApi\Service\HistoryAuditService;
use DvsaEntities\Entity\Person;
use PHPUnit_Framework_MockObject_MockObject;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;

/**
 * Class KDD069ServiceTest.
 */
class HistoryAuditServiceTest extends AbstractServiceTestCase
{
    /**
     * @var EntityManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    const TESTING_USER_ID = 250;

    public function setUp()
    {
        $this->em = $this->getMockEntityManager();
    }

    public function testExecuteInvokesDoctrineExecuteQueryWithCorrectValue()
    {
        $connection = $this->getMockWithDisabledConstructor(Connection::class);
        $this->mockMethod(
            $connection,
            'executeQuery',
            $this->once(),
            null,
            [sprintf("SET @app_user_id = '%d'", self::TESTING_USER_ID)]
        );

        $this->mockMethod($this->em, 'getConnection', $this->once(), $connection);
        $user = $this->getPerson();
        $service = new HistoryAuditService($this->em, $user);

        $service->execute();
    }

    /**
     * When you pass in a NULL param for the Person object and attempt to execute() it should bail out.
     *
     * @expectedException \LogicException
     */
    public function testExecuteShouldThrowExceptionWithoutPersonDependency()
    {
        $service = new HistoryAuditService($this->em, null);
        $service->execute();
    }

    public function testCreateQuery()
    {
        $createQueryMethod = new \ReflectionMethod(
            HistoryAuditService::class, 'createQuery'
        );
        $createQueryMethod->setAccessible(true);
        $this->assertSame(
            sprintf("SET @app_user_id = '%d'", self::TESTING_USER_ID),
            $createQueryMethod->invoke($this->getHistoryAuditService(), $this->getSessionVariables())
        );
    }

    protected function getSessionVariables()
    {
        return [HistoryAuditService::AUDIT_USER_VAR => self::TESTING_USER_ID];
    }

    protected function getPerson()
    {
        $user = new Person();
        $user->setId(self::TESTING_USER_ID);
        $user->setFamilyName('Testing Surname');
        $user->setFirstName('Testing Firstname');

        return $user;
    }

    protected function getHistoryAuditService()
    {
        $em = $this->getMockEntityManager();
        $person = $this->getPerson();

        return new HistoryAuditService($em, $person);
    }
}
