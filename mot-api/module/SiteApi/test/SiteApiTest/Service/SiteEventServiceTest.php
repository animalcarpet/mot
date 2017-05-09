<?php

namespace SiteApiTest\Service;

use DvsaCommon\Enum\EventCategoryCode;
use DvsaEntities\Repository\SiteRepository;
use DvsaEventApi\Service\EventService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use SiteApi\Service\SiteEventService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Event;

/**
 * Class SiteEventServiceTest.
 *
 * @group event
 */
class SiteEventServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $mocks = [];

    private $createData;

    public function setUp()
    {
        $this->createData = [
            'eventCategoryCode' => EventCategoryCode::VTS_EVENT,
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
        $mock = $this->getMockService(SiteRepository::class);
        $mock->expects($this->once())
            ->method('find')
            ->willThrowException(new NotFoundException('Not found'));

        $obj = $this->createServiceWithMocks();
        $obj->create(1, $this->createData);
    }

    public function testCreateWithEventSiteMap()
    {
        // granted
        $mock = $this->getMockService(AuthorisationServiceInterface::class);
        $mock->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::EVENT_CREATE)
            ->willReturn(true);

        // Return a Person
        $mock = $this->getMockService(SiteRepository::class);
        $mock->expects($this->once())
            ->method('find')
            ->willReturn(new Site());

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
        return new SiteEventService(
            $this->getMockService(EventService::class),
            $this->getMockService(AuthorisationServiceInterface::class),
            $this->getMockService(EntityManager::class),
            $this->getMockService(SiteRepository::class)
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
