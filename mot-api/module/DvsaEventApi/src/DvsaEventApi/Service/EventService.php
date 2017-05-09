<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaEventApi\Service;

use DateTime;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Event\EventDto;
use DvsaCommon\Dto\Event\EventFormDto;
use DvsaCommon\Dto\Event\EventListDto;
use DvsaCommon\Enum\EventCategoryCode;
use DvsaCommon\Enum\EventOutcomeCode;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Validator\RecordEventDateValidator;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Event;
use DvsaEntities\Entity\EventCategory;
use DvsaEntities\Entity\EventOrganisationMap;
use DvsaEntities\Entity\EventOutcome;
use DvsaEntities\Entity\EventType;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use DvsaCommon\InputFilter\Event\RecordInputFilter;
use DvsaEntities\Repository\EventRepository;
use DvsaEntities\Repository\EventTypeOutcomeCategoryMapRepository;
use DvsaEventApi\Service\Mapper\EventListMapper;
use Zend\Log\Logger;
use Zend\Log\LoggerInterface;

/**
 * This class is the service use by the event module.
 * It's going to be used to create a new event and list the event.
 */
class EventService extends AbstractService
{
    const EVENT_CATEGORY_CODE = 'eventCategoryCode';
    const EVENT_TYPE_CODE = 'eventTypeCode';
    const EVENT_OUTCOME_CODE = 'eventOutcomeCode';
    const EVENT_DESCRIPTION = 'eventDescription';
    const EVENT_DATE = 'eventDate';
    /**
     * @var AuthorisationServiceInterface
     */
    private $authService;

    /**
     * @var DoctrineObject
     */
    private $objectHydrator;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EntityRepository
     */
    private $eventTypeRepository;

    /**
     * @var EntityRepository
     */
    private $eventCategoryCodeRepository;

    /**
     * @var EntityRepository
     */
    private $eventOutcomeCodeRepository;

    /**
     * @var EventTypeOutcomeCategoryMapRepository
     */
    private $eventTypeOutcomeCategoryMapRepository;

    /**
     * @var EventListMapper
     */
    private $mapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /** @var DateTimeHolder */
    private $dateTimeHolder;

    /**
     * Creates the event service class.
     *
     * @param AuthorisationServiceInterface         $authService
     * @param EntityManager                         $entityManager
     * @param EventRepository                       $eventRepository
     * @param EntityRepository                      $eventTypeRepository
     * @param EntityRepository                      $eventCategoryCodeRepository
     * @param EntityRepository                      $eventOutcomeCodeRepository
     * @param EventTypeOutcomeCategoryMapRepository $eventTypeOutcomeCategoryMapRepository
     * @param DoctrineObject                        $objectHydrator
     * @param EventListMapper                       $mapper
     */
    public function __construct(
        AuthorisationServiceInterface $authService,
        EntityManager $entityManager,
        EventRepository $eventRepository,
        EntityRepository $eventTypeRepository,
        EntityRepository $eventCategoryCodeRepository,
        EntityRepository $eventOutcomeCodeRepository,
        EventTypeOutcomeCategoryMapRepository $eventTypeOutcomeCategoryMapRepository,
        DoctrineObject $objectHydrator,
        EventListMapper $mapper

    ) {
        parent::__construct($entityManager);

        $this->authService = $authService;
        $this->eventRepository = $eventRepository;
        $this->eventTypeRepository = $eventTypeRepository;
        $this->eventCategoryCodeRepository = $eventCategoryCodeRepository;
        $this->eventOutcomeCodeRepository = $eventOutcomeCodeRepository;
        $this->eventTypeOutcomeCategoryMapRepository = $eventTypeOutcomeCategoryMapRepository;
        $this->objectHydrator = $objectHydrator;
        $this->mapper = $mapper;
        $this->dateTimeHolder = new DateTimeHolder();
    }

    /**
     * @param $logger Object Something that can log an event somewhere
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Writes a single event into the event history log.
     *
     * @param $eventType   string a value from the \DvsaCommon\Enum\EventTypeCode event types list
     * @param $description string a mandatory short description of the event
     * @param $eventDate   \DateTime the recorded event date
     *
     * @throws \Exception
     *
     * @return Event the new event history record
     */
    public function addEvent($eventType, $description, \DateTime $eventDate = null)
    {
        if ($eventDate === null) {
            $eventDate = $this->dateTimeHolder->getCurrent(true);
        }
        try {
            $eventTypeObj = $this->validateEventType($eventType);

            /** @var Event $event */
            $event = new Event();
            $event
                ->setEventType($eventTypeObj)
                ->setShortDescription($description)
                ->setEventDate($eventDate);

            $this->entityManager->persist($event);
            $this->entityManager->flush();

            return $event;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log(
                    Logger::ERR,
                    'EventService::addEvent failed: '.$e->getMessage()
                );
            }
            throw $e;
        }
    }

    /**
     * Writes a single event into the event history log.
     *
     * @param Organisation $organisation Organisation of organisation
     * @param string       $eventType    a value from the \DvsaCommon\Enum\EventTypeCode event types list
     * @param string       $description  a mandatory short description of the event
     * @param \DateTime    $eventDate    the recorded event date
     *
     * @throws \Exception
     *
     * @return Event the new event history record
     */
    public function addOrganisationEvent(Organisation $organisation, $eventType, $description, \DateTime $eventDate = null)
    {
        $event = $this->addEvent($eventType, $description, $eventDate);

        $eventMap = (new EventOrganisationMap())
            ->setEvent($event)
            ->setOrganisation($organisation);

        $this->entityManager->persist($eventMap);
    }

    /**
     * Records a manual event.
     *
     * @param array $data
     *
     * @throws \Exception
     * @throws \InvalidArgumentException on validation error
     *
     * @return Event|null
     */
    public function recordManualEvent(array $data)
    {
        $this->assertEventDate($data[self::EVENT_DATE]);
        $eventData = $this->loadEntities($data);

        try {
            /** @var Event $event */
            $event = new Event();
            $event
                ->setEventType($eventData[self::EVENT_TYPE_CODE])
                ->setShortDescription($eventData[self::EVENT_DESCRIPTION])
                ->setEventDate($eventData[self::EVENT_DATE])
                ->setEventOutcome($eventData[self::EVENT_OUTCOME_CODE])
                ->setIsManualEvent(true);

            $this->entityManager->persist($event);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->err('EventService::addEvent failed: '.$e->getMessage());
            }

            $event = null;
        }

        return $event;
    }

    /**
     * Uses the custom validator to ensure the date is correct.
     *
     * @param array $date
     *
     * @throws \InvalidArgumentException
     */
    private function assertEventDate(array $date)
    {
        $validator = new RecordEventDateValidator();
        if (!$validator->isValid($date)) {
            throw new \InvalidArgumentException(self::EVENT_DATE.' '.implode(', ', $validator->getMessages()));
        }
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     *
     * @return EventDto
     */
    public function get($id)
    {
        $this->authService->assertGranted(PermissionInSystem::EVENT_READ);
        /* @var Event $event */
        $event = $this->eventRepository->findEvent($id);

        return $this->mapper->toDto($event);
    }

    /**
     * @param int          $id
     * @param string       $type
     * @param EventFormDto $dto
     *
     * @throws \UnexpectedValueException
     *
     * @return EventListDto
     */
    public function getList($id, $type, $dto)
    {
        $this->authService->assertGranted(PermissionInSystem::LIST_EVENT_HISTORY);
        if (method_exists($this, 'getListFor'.ucfirst($type))) {
            return $this->{'getListFor'.ucfirst($type)}($id, $dto, $type);
        }
        throw new \UnexpectedValueException(
            'Invalid type passed, the type of event list must be ae/site/person'
        );
    }

    /**
     * Validates data received by Api and returns the Entity objects in array.
     *
     * @param array $data
     *
     * @return array
     */
    public function loadEntities(array $data)
    {
        $eventCategoryID = $this->getCategoryId($this->getEventCategoryCode($data));
        $eventTypeID = $this->getEventTypeId($this->getEventTypeCode($data));
        $eventOutcomeID = $this->getEventOutcomeId($this->getEventOutcomeCode($data));

        if ($this->outcomeExists($eventCategoryID, $eventTypeID, $eventOutcomeID)) {
            return [
                self::EVENT_CATEGORY_CODE => $this->getEventCategoryCode($data),
                self::EVENT_TYPE_CODE => $this->getEventTypeCode($data),
                self::EVENT_OUTCOME_CODE => $this->getEventOutcomeCode($data),
                self::EVENT_DESCRIPTION => $this->getEventDescription($data),
                self::EVENT_DATE => $this->getEventDate($data),
            ];
        }
    }

    /**
     * @param int          $personId
     * @param EventFormDto $dto
     * @param string       $type
     *
     * @return EventListDto
     */
    protected function getListForPerson($personId, EventFormDto $dto, $type)
    {
        $eventList = $this->eventRepository->findEvents($personId, $dto, $type);
        $eventCount = $this->eventRepository->findEventsCount($personId, $dto, $type);
        $dto = new EventListDto();
        $dto
            ->setPersonId($personId)
            ->setTotalResult($eventCount)
            ->setEvents($this->mapper->manyToDto($eventList));

        return $dto;
    }

    /**
     * This function test if the user have already an event register with a specific code.
     *
     * @param Person $person
     * @param string $code
     *
     * @return bool
     */
    public function isEventCreatedBy(Person $person, $code)
    {
        return $this->eventRepository->isEventCreatedBy($person, $code);
    }

    /**
     * @param array $request
     *
     * @throws \InvalidArgumentException
     *
     * @return EventCategory $eventCategoryCode
     */
    protected function getEventCategoryCode(array $request)
    {
        $eventCategoryCode = ArrayUtils::tryGet($request, self::EVENT_CATEGORY_CODE);

        if (null === $eventCategoryCode) {
            throw new \InvalidArgumentException('Event Category Code Not Found');
        }
        $eventCategoryCode = $this->validateEventCategory($eventCategoryCode);

        return $eventCategoryCode;
    }

    /**
     * Ensures that the event type string is actually correctly typed and
     * corresponds to a real value. If the data fails to validate we throw
     * an exception otherwise we return the entity.
     *
     * @param string $eventCategoryCode
     *
     * @throws \InvalidArgumentException
     *
     * @return EventCategory $eventCategoryCodeObj
     */
    protected function validateEventCategory($eventCategoryCode)
    {
        if (is_string($eventCategoryCode) && EventCategoryCode::exists($eventCategoryCode)) {
            /** @var EventCategory $eventCategoryCodeObj */
            $eventCategoryCodeObj = $this->eventCategoryCodeRepository->findOneBy(['code' => $eventCategoryCode]);

            if (is_null($eventCategoryCodeObj)) {
                throw new \InvalidArgumentException('Failed to get event category code from database');
            }

            return $eventCategoryCodeObj;
        }
        throw new \InvalidArgumentException('Unrecognised event category code');
    }

    /**
     * @param array $request
     *
     * @throws \InvalidArgumentException
     *
     * @return EventType $eventTypeCode
     */
    protected function getEventTypeCode(array $request)
    {
        $eventTypeCode = ArrayUtils::tryGet($request, self::EVENT_TYPE_CODE);

        if (null === $eventTypeCode) {
            throw new \InvalidArgumentException('Event Type Code Not Found');
        }
        $eventTypeCode = $this->validateEventType($eventTypeCode);

        return $eventTypeCode;
    }

    /**
     * Ensures that the event type string is actually correctly typed and
     * corresponds to a real value. If the data fails to validate we throw
     * an exception otherwise we return the entity.
     *
     * @param string $eventType
     *
     * @throws \InvalidArgumentException
     *
     * @return EventType $eventTypeEntity
     */
    protected function validateEventType($eventType)
    {
        if (is_string($eventType) && EventTypeCode::exists($eventType)) {
            /** @var EventType $eventTypeObj */
            $eventTypeObj = $this->eventTypeRepository->findOneBy(['code' => $eventType]);

            if (is_null($eventTypeObj)) {
                throw new \InvalidArgumentException('Failed to get event type from database');
            }

            return $eventTypeObj;
        }
        throw new \InvalidArgumentException('Unrecognised event type');
    }

    /**
     * @param array $request
     *
     * @throws \InvalidArgumentException
     *
     * @return EventOutcome $eventOutcomeCode
     */
    protected function getEventOutcomeCode(array $request)
    {
        $eventOutcomeCode = ArrayUtils::tryGet($request, self::EVENT_OUTCOME_CODE);

        if (null === $eventOutcomeCode) {
            throw new \InvalidArgumentException('Event Type Code Not Found');
        }
        $eventOutcomeCode = $this->validateEventOutcomeCode($eventOutcomeCode);

        return $eventOutcomeCode;
    }

    /**
     * Ensures that the event type string is actually correctly typed and
     * corresponds to a real value. If the data fails to validate we throw
     * an exception otherwise we return the entity.
     *
     * @param string $eventOutcomeCode
     *
     * @throws \InvalidArgumentException
     *
     * @return EventOutcome $eventOutcomeCodeObj
     */
    protected function validateEventOutcomeCode($eventOutcomeCode)
    {
        if (is_string($eventOutcomeCode) && EventOutcomeCode::exists($eventOutcomeCode)) {
            /* @var EventOutcome $eventTypeObj */
            $eventOutcomeCodeObj = $this->eventOutcomeCodeRepository->findOneBy(['code' => $eventOutcomeCode]);

            if (is_null($eventOutcomeCodeObj)) {
                throw new \InvalidArgumentException('Failed to get event outcome code from database');
            }

            return $eventOutcomeCodeObj;
        }
        throw new \InvalidArgumentException('Unrecognised event outcome code');
    }

    /**
     * @param array $request
     *
     * @return array|null
     */
    protected function getEventDescription(array $request)
    {
        return ArrayUtils::tryGet($request, self::EVENT_DESCRIPTION, '');
    }

    /**
     * @param array $data
     *
     * @return DateTime
     */
    protected function getEventDate(array $data)
    {
        $date = DateUtils::toDateFromParts(
            $data[self::EVENT_DATE][RecordInputFilter::FIELD_DAY],
            $data[self::EVENT_DATE][RecordInputFilter::FIELD_MONTH],
            $data[self::EVENT_DATE][RecordInputFilter::FIELD_YEAR]
        );

        return $date;
    }

    /**
     * @param $eventCategoryID
     * @param $eventTypeID
     * @param $eventOutcomeID
     *
     *@return bool
     */
    public function outcomeExists($eventCategoryID, $eventTypeID, $eventOutcomeID)
    {
        $eventTypeCategory = $this->eventTypeOutcomeCategoryMapRepository
            ->isOutcomeAssociatedWithCategoryAndType($eventCategoryID, $eventTypeID, $eventOutcomeID);
        if (null === $eventTypeCategory) {
            throw new \InvalidArgumentException('Event Outcome and Category do not match');
        }

        return $eventTypeCategory;
    }

    /**
     * @param int          $organisationId
     * @param EventFormDto $dto
     * @param string       $type
     *
     * @return EventListDto
     */
    protected function getListForAe($organisationId, EventFormDto $dto, $type)
    {
        $eventList = $this->eventRepository->findEvents($organisationId, $dto, $type);
        $eventCount = $this->eventRepository->findEventsCount($organisationId, $dto, $type);
        $dto = new EventListDto();
        $dto
            ->setOrganisationId($organisationId)
            ->setTotalResult($eventCount)
            ->setEvents($this->mapper->manyToDto($eventList));

        return $dto;
    }

    /**
     * @param int          $siteId
     * @param EventFormDto $dto
     * @param string       $type
     *
     * @return EventListDto
     */
    protected function getListForSite($siteId, EventFormDto $dto, $type)
    {
        $eventList = $this->eventRepository->findEvents($siteId, $dto, $type);
        $eventCount = $this->eventRepository->findEventsCount($siteId, $dto, $type);
        $dto = new EventListDto();
        $dto
            ->setSiteId($siteId)
            ->setTotalResult($eventCount)
            ->setEvents($this->mapper->manyToDto($eventList));

        return $dto;
    }

    /**
     * @param $eventCategoryCodeObj
     *
     * @return int
     */
    private function getCategoryId($eventCategoryCodeObj)
    {
        /* @var EventCategory $eventCategoryCodeObj */
        return $eventCategoryCodeObj->getId();
    }

    /**
     * @param $eventTypeObj
     *
     * @return int
     */
    private function getEventTypeId($eventTypeObj)
    {
        /* @var EventType $eventTypeObj */
        return $eventTypeObj->getId();
    }

    /**
     * @param $eventTypeObj
     *
     * @return int
     */
    private function getEventOutcomeId($eventTypeObj)
    {
        /* @var EventOutcome $eventTypeObj */
        return $eventTypeObj->getId();
    }
}
