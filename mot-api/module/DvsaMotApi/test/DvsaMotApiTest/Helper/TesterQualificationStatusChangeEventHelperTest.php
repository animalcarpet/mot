<?php

namespace DvsaMotApiTest\Helper;

use DvsaAuthentication\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\EventType;
use DvsaEntities\Entity\Person;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Constants\EventDescription;
use DvsaEventApi\Service\EventService;
use DvsaMotApi\Helper\TesterQualificationStatusChangeEventHelper;
use DvsaEntities\Repository\EventPersonMapRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use Doctrine\ORM\EntityManager;
use DvsaEntities\Repository\EventRepository;
use Doctrine\ORM\EntityRepository;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaEventApi\Service\Mapper\EventListMapper;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Repository\EventTypeOutcomeCategoryMapRepository;

class TesterQualificationStatusChangeEventHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var MotIdentityProviderInterface */
    private $identityProvider;

    /** @var EventPersonMapRepository */
    private $eventPersonMapRepository;

    /** @var Person */
    private $person;

    /** @var Person */
    private $tester;

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

        $tester = (new Person())
            ->setId(123)
            ->setFirstName('Marty')
            ->setFamilyName('McFly');

        $this->identityProvider = $identityProvider;
        $this->person = $person;
        $this->tester = $tester;
        $this->eventPersonMapRepository = XMock::of(EventPersonMapRepository::class);
    }

    public function testEventIsSendForGroupA()
    {
        $eventType = (new EventType())->setCode(EventTypeCode::GROUP_A_TESTER_QUALIFICATION);
        $helper = $this->createTesterQualificationStatusChangeEventHelper($eventType);

        $eventPersonMap = $helper->create($this->tester, 'A');
        $event = $eventPersonMap->getEvent();
        $description = sprintf(EventDescription::TESTER_QUALIFICATION_STATUS_CHANGE, 'A', $this->person->getDisplayName());

        $this->assertEquals($this->tester, $eventPersonMap->getPerson());
        $this->assertEquals($description, $event->getShortDescription());
    }

    public function testEventIsSendForGroupB()
    {
        $eventType = (new EventType())->setCode(EventTypeCode::GROUP_B_TESTER_QUALIFICATION);
        $helper = $this->createTesterQualificationStatusChangeEventHelper($eventType);

        $eventPersonMap = $helper->create($this->tester, 'B');
        $event = $eventPersonMap->getEvent();
        $description = sprintf(EventDescription::TESTER_QUALIFICATION_STATUS_CHANGE, 'B', $this->person->getDisplayName());

        $this->assertEquals($this->tester, $eventPersonMap->getPerson());
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
     * @param EventType $eventType
     *
     * @return TesterQualificationStatusChangeEventHelper
     *
     * @throws \Exception
     */
    private function createTesterQualificationStatusChangeEventHelper(EventType $eventType)
    {
        return new TesterQualificationStatusChangeEventHelper(
            $this->identityProvider,
            $this->createEventService($eventType),
            $this->eventPersonMapRepository,
            new DateTimeHolder()
        );
    }
}
