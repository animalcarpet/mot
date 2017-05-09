<?php

namespace NotificationApiTest\Service\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\EventType;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\NotificationField;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Constants\EventDescription;
use DvsaEventApi\Service\EventService;
use NotificationApi\Service\Helper\OrganisationNominationEventHelper;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use Doctrine\ORM\EntityManager;
use DvsaEntities\Repository\EventRepository;
use DvsaEntities\Repository\EventOrganisationMapRepository;
use DvsaEntities\Repository\AuthorisationForAuthorisedExaminerRepository;
use Doctrine\ORM\EntityRepository;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaEventApi\Service\Mapper\EventListMapper;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Repository\EventTypeOutcomeCategoryMapRepository;

class OrganisationNominationEventHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var Person */
    private $person;

    /** @var EventService */
    private $eventService;

    /** @var OrganisationNominationEventHelper */
    private $organisationEventHelper;

    /** @var Organisation */
    private $organisation;

    public function setUp()
    {
        $person = (new Person())
            ->setId(1)
            ->setFirstName('John')
            ->setFamilyName('Rambo');

        $this->person = $person;

        $eventType = (new EventType())->setCode(EventTypeCode::ROLE_ASSOCIATION_CHANGE);
        $this->eventService = $this->createEventService($eventType);

        $org = new Organisation();
        $org->setName('Organisation LTD');

        $this->organisation = $org;

        $auth = new AuthorisationForAuthorisedExaminer();
        $auth->setNumber('AE1234');
        $auth->setOrganisation($org);

        $this->organisation->setAuthorisedExaminer($auth);

        $authRepository = XMock::of(AuthorisationForAuthorisedExaminerRepository::class);
        $authRepository
            ->expects($this->any())
            ->method('getByNumber')
            ->willReturn($auth);

        $this->organisationEventHelper = new OrganisationNominationEventHelper(
            $this->eventService,
            XMock::of(EventOrganisationMapRepository::class),
            $authRepository,
            new DateTimeHolder()
        );
    }

    public function testOrganisationEventIsCreatedWhenUserAcceptNomination()
    {
        $helper = $this->organisationEventHelper;
        $notification = $this->createNotification();

        $eventMap = $helper->create($notification);
        $event = $eventMap->getEvent();
        $description = sprintf(
            EventDescription::ROLE_NOMINATION_ACCEPT,
            OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
            $this->person->getDisplayName(),
            $this->person->getUsername(),
            $this->organisation->getAuthorisedExaminer()->getNumber(),
            $this->organisation->getName()
        );

        $this->assertEquals($this->organisation->getAuthorisedExaminer()->getNumber(), $eventMap->getOrganisation()->getAuthorisedExaminer()->getNumber());
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
     * @return Notification
     */
    private function createNotification()
    {
        $fieldPositionName = new NotificationField();
        $fieldPositionName->setField('positionName');
        $fieldPositionName->setValue(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);

        $fieldSiteName = new NotificationField();
        $fieldSiteName->setField('organisationName');
        $fieldSiteName->setValue($this->organisation->getName());

        $fieldSiteId = new NotificationField();
        $fieldSiteId->setField('siteOrOrganisationId');
        $fieldSiteId->setValue($this->organisation->getAuthorisedExaminer()->getNumber());

        $fields = new ArrayCollection();
        $fields->add($fieldPositionName);
        $fields->add($fieldSiteName);
        $fields->add($fieldSiteId);

        $notification = new Notification();
        $notification->setRecipient($this->person);
        $notification->setFields($fields);

        return $notification;
    }
}
