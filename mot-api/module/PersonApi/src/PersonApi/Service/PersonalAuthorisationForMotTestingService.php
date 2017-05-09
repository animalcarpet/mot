<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\AuthorisationForTestingMotStatus;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository;
use DvsaEventApi\Service\EventService;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;
use PersonApi\Dto\MotTestingAuthorisationCollector;
use PersonApi\Service\Validator\PersonalAuthorisationForMotTestingValidator;
use DvsaEntities\Entity\Person;
use Zend\Authentication\AuthenticationService;

/**
 * Personal Authorisation For Mot Testing.
 */
class PersonalAuthorisationForMotTestingService
{
    const GROUP_A_VEHICLE = 1;
    const GROUP_B_VEHICLE = 2;
    const SUCCESS = 1;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @var PersonalAuthorisationForMotTestingValidator
     */
    private $validator;

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var EventService
     */
    private $eventService;

    /**
     * @var PersonService
     */
    private $personService;

    /**
     * @var AuthorisationForTestingMotStatusRepository
     */
    private $authorisationForTestingMotStatusRepository;

    /**
     * @var VehicleClassRepository
     */
    private $vehicleClassRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var array
     */
    private $vehicleClassGroupLookup = ['1' => 'A', '2' => 'B'];

    /**
     * @var array
     */
    private $authStatusLookup = [
        AuthorisationForTestingMotStatusCode::UNKNOWN => 'Unknown',
        AuthorisationForTestingMotStatusCode::SUSPENDED => 'Suspended',
        AuthorisationForTestingMotStatusCode::QUALIFIED => 'Qualified',
        AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED => 'Demo test needed',
        AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED => 'Initial training needed',
        AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED => 'Refresher Needed',
    ];

    /**
     * @param EntityManager                               $entityManager
     * @param NotificationService                         $notificationService
     * @param PersonalAuthorisationForMotTestingValidator $validator
     * @param MotAuthorisationServiceInterface            $authorisationService
     * @param EventService                                $eventService
     * @param PersonService                               $personService
     * @param AuthorisationForTestingMotStatusRepository  $authorisationForTestingMotStatusRepository
     * @param VehicleClassRepository                      $vehicleClassRepository
     * @param AuthenticationService                       $authenticationService
     */
    public function __construct(
        EntityManager $entityManager,
        NotificationService $notificationService,
        PersonalAuthorisationForMotTestingValidator $validator,
        MotAuthorisationServiceInterface $authorisationService,
        EventService $eventService,
        PersonService $personService,
        AuthorisationForTestingMotStatusRepository $authorisationForTestingMotStatusRepository,
        VehicleClassRepository $vehicleClassRepository,
        AuthenticationService $authenticationService
    ) {
        $this->entityManager = $entityManager;
        $this->notificationService = $notificationService;
        $this->validator = $validator;
        $this->authorisationService = $authorisationService;
        $this->eventService = $eventService;
        $this->personService = $personService;
        $this->authorisationForTestingMotStatusRepository = $authorisationForTestingMotStatusRepository;
        $this->vehicleClassRepository = $vehicleClassRepository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param int $personId
     *
     * @return MotTestingAuthorisationCollector
     */
    public function getPersonalTestingAuthorisation($personId)
    {
        $authList = $this->getPersonalTestingAuthorisationList($personId);

        return new MotTestingAuthorisationCollector($authList);
    }

    /**
     * @param int   $personId
     * @param array $data
     *
     * @return MotTestingAuthorisationCollector
     */
    public function updatePersonalTestingAuthorisationGroup($personId, $data)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS);

        $this->validator->validate($data);

        $person = $this->personService->getPersonById($personId);

        $group = (int) $data['group'];
        $status = (string) $data['result'];

        /** @var array $classes */
        $classes = $this->getClassesFromGroup($group, $status);

        /** @var \DvsaEntities\Entity\AuthorisationForTestingMot[] $applicationClasses */
        $applicationClasses = $this->getPersonalTestingAuthorisationList($personId);

        try {
            /** @var string $statusCodeFromAuthorisationListForGroup */
            $statusCodeFromAuthorisationListForGroup = $this->getStatusCodeFromAuthorisationListForGroup(
                $applicationClasses,
                $group
            );
        } catch (\InvalidArgumentException $e) {
            $statusCodeFromAuthorisationListForGroup = AuthorisationForTestingMotStatusCode::UNKNOWN;
            $applicationClasses = $this->createAuthorisationRecords($person, $applicationClasses, $group);
        }

        $this->sendNotification(
            $personId,
            $group,
            $statusCodeFromAuthorisationListForGroup,
            $status
        );

        $this->createEvent($group, $statusCodeFromAuthorisationListForGroup, $status, $person);

        return $this->updatePersonalTestingAuthorisation($applicationClasses, $classes);
    }

    /**
     * @param int    $group
     * @param string $oldStatus
     * @param string $newStatus
     * @param Person $person
     *
     * @return EventPersonMap
     */
    private function createEvent($group, $oldStatus, $newStatus, Person $person)
    {
        if ($group === 1) {
            $eventTypeCode = EventTypeCode::GROUP_A_TESTER_QUALIFICATION;
            $groupName = 'A';
        } else {
            $eventTypeCode = EventTypeCode::GROUP_B_TESTER_QUALIFICATION;
            $groupName = 'B';
        }

        if ($oldStatus === AuthorisationForTestingMotStatusCode::UNKNOWN) {
            $event = $this->createNewStatusAssignedEvent($groupName, $eventTypeCode, $newStatus);
        } else {
            $event = $this->createUpdateStatusAssignedEvent($groupName, $eventTypeCode, $oldStatus, $newStatus);
        }

        $eventPersonMap = new EventPersonMap();
        $eventPersonMap->setEvent($event)
                       ->setPerson($person);

        $this->entityManager->persist($eventPersonMap);

        return $eventPersonMap;
    }

    /**
     * @param string $groupName
     * @param string $eventTypeCode
     * @param string $newlyAssignedStatus
     *
     * @return \DvsaEntities\Entity\Event
     *
     * @throws \Exception
     */
    private function createNewStatusAssignedEvent($groupName, $eventTypeCode, $newlyAssignedStatus)
    {
        $eventDescription = EventDescription::TESTER_QUALIFICATION_STATUS_CHANGE_NEW;

        $event = $this->eventService->addEvent(
            $eventTypeCode,
            sprintf(
                $eventDescription,
                $groupName,
                $this->authStatusLookup[$newlyAssignedStatus],
                $this->getAuthorisedUsername()
            ),
            new \DateTime()
        );

        return $event;
    }

    /**
     * @param string $groupName
     * @param string $eventTypeCode
     * @param string $oldStatus
     * @param string $newlyAssignedStatus
     *
     * @return \DvsaEntities\Entity\Event
     *
     * @throws \Exception
     */
    private function createUpdateStatusAssignedEvent($groupName, $eventTypeCode, $oldStatus, $newlyAssignedStatus)
    {
        $eventDescription = EventDescription::TESTER_QUALIFICATION_STATUS_CHANGE_UPDATE;

        $event = $this->eventService->addEvent(
            $eventTypeCode,
            sprintf(
                $eventDescription,
                $groupName,
                $this->authStatusLookup[$oldStatus],
                $this->authStatusLookup[$newlyAssignedStatus],
                $this->getAuthorisedUsername()
            ),
            new \DateTime()
        );

        return $event;
    }

    /**
     * @param int    $personId
     * @param int    $group
     * @param string $fromStatus
     * @param string $toStatus
     *
     * @return int
     */
    private function sendNotification($personId, $group, $fromStatus, $toStatus)
    {
        if ($fromStatus === AuthorisationForTestingMotStatusCode::UNKNOWN) {
            $template = Notification::TEMPLATE_TESTER_STATUS_CHANGE_NEW;
        } else {
            $template = Notification::TEMPLATE_TESTER_STATUS_CHANGE;
        }

        $notification = (new Notification())
            ->setRecipient($personId)
            ->setTemplate($template)
            ->setFields(
                [
                    'group' => $this->vehicleClassGroupLookup[$group],
                    'previousStatus' => $this->authStatusLookup[$fromStatus],
                    'newStatus' => $this->authStatusLookup[$toStatus],
                ]
            );

        return $this->notificationService->add($notification->toArray());
    }

    /**
     * @param Person                       $person
     * @param AuthorisationForTestingMot[] $applicationClasses
     * @param int                          $group
     *
     * @return \DvsaEntities\Entity\AuthorisationForTestingMot[]
     *
     * @throws NotFoundException
     */
    private function createAuthorisationRecords(Person $person, $applicationClasses, $group)
    {
        $defaultStatusCode = $this->getDefaultStatus();

        foreach (VehicleClassCode::getAll() as $classCode) {
            $addAuthorisationForTestingMot = true;

            foreach ($applicationClasses as $applicationClass) {
                $vehicleClassCode = $applicationClass->getVehicleClass()->getCode();

                if ($vehicleClassCode == $classCode) {
                    $addAuthorisationForTestingMot = false;
                }
            }

            if ($addAuthorisationForTestingMot && $this->isClassInGroup($classCode, $group)) {
                $vehicleClass = $this->vehicleClassRepository->getByCode($classCode);

                $authorisationForTestingMot = new AuthorisationForTestingMot();
                $authorisationForTestingMot->setVehicleClass(
                    $vehicleClass
                )
                    ->setStatus($defaultStatusCode)
                    ->setPerson($person);

                $this->entityManager->persist($authorisationForTestingMot);

                $person->addAuthorisationForTestingMot(
                    $authorisationForTestingMot
                );
            }
        }

        $this->entityManager->flush();

        return $person->getAuthorisationsForTestingMot();
    }

    /**
     * @return string
     */
    private function getAuthorisedUsername()
    {
        /** @var Person $identity */
        $identity = $this->authenticationService->getIdentity();

        return $identity->getUsername();
    }

    /**
     * @param int    $group
     * @param string $status
     *
     * @return array
     *
     * @throws NotFoundException
     */
    private function getClassesFromGroup($group, $status)
    {
        if (self::GROUP_A_VEHICLE === $group) {
            $classes = [
                'class'.VehicleClassCode::CLASS_1 => $status,
                'class'.VehicleClassCode::CLASS_2 => $status,
            ];
        } elseif (self::GROUP_B_VEHICLE === $group) {
            $classes = [
                'class'.VehicleClassCode::CLASS_3 => $status,
                'class'.VehicleClassCode::CLASS_4 => $status,
                'class'.VehicleClassCode::CLASS_5 => $status,
                'class'.VehicleClassCode::CLASS_7 => $status,
            ];
        } else {
            throw new NotFoundException('Group', $group, false);
        }

        return $classes;
    }

    /**
     * @param AuthorisationForTestingMot[] $authorisationList
     * @param int                          $group
     *
     * @throws \Exception
     *
     * @return string
     */
    private function getStatusCodeFromAuthorisationListForGroup($authorisationList, $group)
    {
        /** @var $vcAuth AuthorisationForTestingMot */
        foreach ($authorisationList as $vcAuth) {
            if ($group === self::GROUP_A_VEHICLE && in_array($vcAuth->getVehicleClass()->getCode(), [1, 2])) {
                return $vcAuth->getStatus()->getCode();
            } elseif ($group == self::GROUP_B_VEHICLE
                && in_array(
                    $vcAuth->getVehicleClass()->getCode(),
                    [3, 4, 5, 6]
                )
            ) {
                return $vcAuth->getStatus()->getCode();
            }
        }
        throw new \InvalidArgumentException('Authorisation list does not contain record for vehicle group '.$group);
    }

    /**
     * @param int $vehicleClassCode
     * @param int $group
     *
     * @return bool
     */
    private function isClassInGroup($vehicleClassCode, $group)
    {
        if ($group === self::GROUP_A_VEHICLE) {
            return VehicleClassGroup::isGroupA($vehicleClassCode);
        } elseif ($group === self::GROUP_B_VEHICLE) {
            return VehicleClassGroup::isGroupB($vehicleClassCode);
        }

        throw new \InvalidArgumentException('Vehicle Class Code specified '.$vehicleClassCode.' is not part of Group '.$group);
    }

    /**
     * @param AuthorisationForTestingMot[] $applicationClasses
     * @param array                        $data
     *
     * @return MotTestingAuthorisationCollector
     */
    private function updatePersonalTestingAuthorisation($applicationClasses, $data)
    {
        if ($applicationClasses) {
            /** @var $vcAuth AuthorisationForTestingMot */
            foreach ($applicationClasses as $vcAuth) {
                $key = 'class'.$vcAuth->getVehicleClass()->getCode();

                if (isset($data[$key])) {
                    $vcAuth->setStatus($this->getStatusByCode($data[$key]));
                    $this->entityManager->persist($vcAuth);
                }
            }
            $this->entityManager->flush();
        }

        return new MotTestingAuthorisationCollector($applicationClasses);
    }

    /**
     * @return AuthorisationForTestingMotStatus
     */
    private function getDefaultStatus()
    {
        return $this->getStatusByCode(AuthorisationForTestingMotStatusCode::UNKNOWN);
    }

    /**
     * @param string $code
     *
     * @return AuthorisationForTestingMotStatus
     *
     * @throws NotFoundException
     */
    private function getStatusByCode($code)
    {
        $status = $this->authorisationForTestingMotStatusRepository->getByCode($code);

        return $status;
    }

    /**
     * @param $personId
     *
     * @return \DvsaEntities\Entity\AuthorisationForTestingMot[]
     */
    private function getPersonalTestingAuthorisationList($personId)
    {
        $person = $this->personService->getPersonById($personId);

        return $person->getAuthorisationsForTestingMot();
    }
}
