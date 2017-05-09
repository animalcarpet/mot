<?php

namespace NotificationApiTest\Service\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\EventType;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\NotificationField;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Constants\EventDescription;
use DvsaEventApi\Service\EventService;
use NotificationApi\Service\Helper\SiteNominationEventHelper;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use Doctrine\ORM\EntityManager;
use DvsaEntities\Repository\EventRepository;
use Doctrine\ORM\EntityRepository;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaEventApi\Service\Mapper\EventListMapper;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Repository\EventSiteMapRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\EventTypeOutcomeCategoryMapRepository;

class SiteNominationEventHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var Person */
    private $person;

    /** @var EventService */
    private $eventService;

    /** @var SiteNominationEventHelper */
    private $siteEventHelper;

    /** @var Site */
    private $site;

    public function setUp()
    {
        $person = (new Person())
            ->setId(1)
            ->setFirstName('John')
            ->setFamilyName('Rambo');

        $this->person = $person;

        $eventType = (new EventType())->setCode(EventTypeCode::ROLE_ASSOCIATION_CHANGE);
        $this->eventService = $this->createEventService($eventType);

        $site = new Site();
        $site->setName('Garage');
        $site->setSiteNumber('NUMBER13');

        $this->site = $site;

        $siteRepository = XMock::of(SiteRepository::class);
        $siteRepository
            ->expects($this->any())
            ->method('getBySiteNumber')
            ->willReturn($site);

        $this->siteEventHelper = new SiteNominationEventHelper(
            $this->eventService,
            XMock::of(EventSiteMapRepository::class),
            $siteRepository,
            new DateTimeHolder()
        );
    }

    public function testSiteEventIsCreatedWhenUserAcceptNomination()
    {
        $helper = $this->siteEventHelper;
        $notification = $this->createNotification();

        $eventMap = $helper->create($notification);
        $event = $eventMap->getEvent();
        $description = sprintf(
            EventDescription::ROLE_NOMINATION_ACCEPT,
            SiteBusinessRoleCode::SITE_ADMIN,
            $this->person->getDisplayName(),
            $this->person->getUsername(),
            $this->site->getSiteNumber(),
            $this->site->getName()
        );

        $this->assertEquals($this->site->getSiteNumber(), $eventMap->getSite()->getSiteNumber());
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
            Xmock::of(EventTypeOutcomeCategoryMapRepository::class),
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
        $fieldPositionName->setValue(SiteBusinessRoleCode::SITE_ADMIN);

        $fieldSiteName = new NotificationField();
        $fieldSiteName->setField('siteName');
        $fieldSiteName->setValue($this->site->getName());

        $fieldSiteId = new NotificationField();
        $fieldSiteId->setField('siteOrOrganisationId');
        $fieldSiteId->setValue($this->site->getSiteNumber());

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
