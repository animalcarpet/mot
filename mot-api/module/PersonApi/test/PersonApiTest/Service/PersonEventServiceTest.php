<?php

namespace PersonApi\test\PersonApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\EventCategoryCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Event;
use DvsaEntities\Entity\Person;
use PersonApi\Service\PersonEventService;
use DvsaEventApi\Service\EventService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaEntities\Repository\PersonRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;

class PersonEventServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $mocks = [];

    private $createData;

    public function setUp()
    {
        $this->createData = [
            'eventCategoryCode' => EventCategoryCode::NT_EVENTS,
        ];
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     * @expectedExceptionMessage Not allowed
     */
    public function testCreateNotGranted_Exception()
    {
        $mock = $this->getMockService(AuthorisationServiceInterface::class);
        $mock->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::EVENT_CREATE)
            ->willThrowException(new UnauthorisedException('Not allowed'));

        $obj = $this->createServiceWithMocks();
        $obj->create(1, $this->createData);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     * @expectedExceptionMessage Not found
     */
    public function testCreateNoEntity_Exception()
    {
        // Granted
        $mock = $this->getMockService(AuthorisationServiceInterface::class);
        $mock->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::EVENT_CREATE)
            ->willReturn(true);

        // Not exist
        $mock = $this->getMockService(PersonRepository::class);
        $mock->expects($this->once())
            ->method('find')
            ->willThrowException(new NotFoundException('Not found'));

        $obj = $this->createServiceWithMocks();
        $obj->create(1, $this->createData);
    }

    public function testCreateWithEventPersonMap()
    {
        // granted
        $mock = $this->getMockService(AuthorisationServiceInterface::class);
        $mock->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::EVENT_CREATE)
            ->willReturn(true);

        // Return a Person
        $mock = $this->getMockService(PersonRepository::class);
        $mock->expects($this->once())
            ->method('find')
            ->willReturn(new Person());

        // Return an Event
        $mock = $this->getMockService(EventService::class);
        $mock->expects($this->once())
            ->method('recordManualEvent')
            ->willReturn(new Event());

        $obj = $this->createServiceWithMocks();
        $obj->create(1, $this->createData);
    }

    private function createServiceWithMocks()
    {
        return new PersonEventService(
            $this->getMockService(EventService::class),
            $this->getMockService(AuthorisationServiceInterface::class),
            $this->getMockService(EntityManager::class),
            $this->getMockService(PersonRepository::class)
        );
    }

    /**
     * @param $name
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @throws \Exception
     */
    private function getMockService($name)
    {
        if (!isset($this->mocks[$name])) {
            $this->mocks[$name] = XMock::of($name);
        }

        return $this->mocks[$name];
    }
}
