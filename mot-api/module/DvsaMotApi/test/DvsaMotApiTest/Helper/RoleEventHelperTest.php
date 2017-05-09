<?php

namespace DvsaMotApiTest\Helper;

use DvsaAuthentication\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Entity\EventType;
use DvsaEntities\Entity\Person;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Constants\EventDescription;
use DvsaEntities\Entity\PersonSystemRole;
use DvsaEventApi\Service\EventService;
use DvsaMotApi\Helper\RoleEventHelper;
use DvsaEntities\Repository\EventPersonMapRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use Doctrine\ORM\EntityManager;
use DvsaEntities\Repository\EventRepository;
use Doctrine\ORM\EntityRepository;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaEventApi\Service\Mapper\EventListMapper;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Repository\EventTypeOutcomeCategoryMapRepository;

class RoleEventHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var MotIdentityProviderInterface */
    private $identityProvider;

    /** @var EventPersonMapRepository */
    private $eventPersonMapRepository;

    /** @var Person */
    private $person;

    /** @var Person */
    private $user;

    /** @var PersonSystemRole */
    private $role;

    public function setUp()
    {
        $personId = 1;

        $person = (new Person())
            ->setId($personId)
            ->setFirstName('John')
            ->setFamilyName('Rambo');

        $identity = XMock::of(Identity::class);
        $identity
            ->expects($this->any())
            ->method('getPerson')
            ->willReturn($person);
        $identity
            ->expects($this->any())
            ->method('getDisplayName')
            ->willReturn('John Rambo');

        $identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        $user = (new Person())
            ->setId(123)
            ->setFirstName('Marty')
            ->setFamilyName('McFly');

        $role = new PersonSystemRole();
        $role->setFullName('DVSA Area Admin');

        $this->identityProvider = $identityProvider;
        $this->person = $person;
        $this->user = $user;
        $this->eventPersonMapRepository = XMock::of(EventPersonMapRepository::class);
        $this->role = $role;
    }

    public function testRemoveRoleEventIsCreatedForUser()
    {
        $roleEventHelper = $this->createRoleEventHelper();
        $eventPersonMap = $roleEventHelper->createRemoveRoleEvent($this->user, $this->role);
        $description = $this->getDescription(EventDescription::DVSA_ROLE_ASSOCIATION_REMOVE);

        $this->assertEvent($eventPersonMap, $description);
    }

    public function testAssignRoleEventIsCreatedForUser()
    {
        $roleEventHelper = $this->createRoleEventHelper();
        $eventPersonMap = $roleEventHelper->createAssignRoleEvent($this->user, $this->role);
        $description = $this->getDescription(EventDescription::DVSA_ROLE_ASSOCIATION_ASSIGN);

        $this->assertEvent($eventPersonMap, $description);
    }

    private function getDescription($eventDescription)
    {
        return sprintf($eventDescription, $this->role->getFullName(), $this->person->getDisplayName());
    }

    private function assertEvent(EventPersonMap $eventPersonMap, $description)
    {
        $event = $eventPersonMap->getEvent();

        $this->assertEquals($this->user, $eventPersonMap->getPerson());
        $this->assertEquals($description, $event->getShortDescription());
    }

    private function createEventService(EventType $eventType)
    {
        $entityRepository = XMock::of(EntityRepository::class, ['findOneBy']);
        $entityRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->willReturn($eventType);

        return new EventService(
            XMock::of(AuthorisationServiceInterface::class),
            XMock::of(EntityManager::class),
            XMock::of(EventRepository::class),
            $entityRepository,
            $entityRepository,
            $entityRepository,
            XMock::of(EventTypeOutcomeCategoryMapRepository::class),
            XMock::of(DoctrineObject::class),
            XMock::of(EventListMapper::class)
        );
    }

    /**
     * @return RoleEventHelper
     *
     * @throws \Exception
     */
    private function createRoleEventHelper()
    {
        $eventType = (new EventType())->setCode(EventTypeCode::ROLE_ASSOCIATION_CHANGE);

        return new RoleEventHelper(
            $this->identityProvider,
            $this->createEventService($eventType),
            $this->eventPersonMapRepository,
            new DateTimeHolder()
        );
    }
}
