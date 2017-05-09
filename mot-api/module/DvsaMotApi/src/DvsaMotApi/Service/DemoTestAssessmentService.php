<?php

namespace DvsaMotApi\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\AuthorisationForTestingMotRepository;
use DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository;
use DvsaEntities\Repository\PersonRepository;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;
use DvsaMotApi\Helper\TesterQualificationStatusChangeEventHelper;
use DvsaMotApi\Service\Validator\DemoTestAssessmentValidator;

/**
 * Class DemoTestAssessmentService.
 *
 * I am ashamed that I created such non objective code, but I'm "following BEST dvsa practices".
 */
class DemoTestAssessmentService
{
    const ERROR_WRONG_NUMBER_OF_AUTHORISATION_RECORDS = "The amount of rows in database in table 'auth_for_testing_mot' for person with id '%s' for vehicle class group '%s' is invalid. The amount is '%s' while required is '%s'.";

    const ERROR_NOT_DEMO_REQUIRED_STATUS = 'The given person is not in demo test needed status for the given vehicle group';

    private $testerQualificationStatusChangeEvent;

    private $authorisationService;

    private $notificationService;

    private $personRepository;

    private $authorisationStatusRepository;

    private $authorisationRepository;

    private $dateTimeHolder;

    private $validator;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        NotificationService $notificationService,
        PersonRepository $personRepository,
        AuthorisationForTestingMotRepository $authorisationRepository,
        AuthorisationForTestingMotStatusRepository $authorisationForTestingMotStatusRepository,
        TesterQualificationStatusChangeEventHelper $testerQualificationStatusChangeEvent,
        DateTimeHolderInterface $dateTimeHolder
    ) {
        $this->authorisationService = $authorisationService;
        $this->notificationService = $notificationService;
        $this->testerQualificationStatusChangeEvent = $testerQualificationStatusChangeEvent;
        $this->notificationService = $notificationService;
        $this->personRepository = $personRepository;
        $this->authorisationRepository = $authorisationRepository;
        $this->authorisationStatusRepository = $authorisationForTestingMotStatusRepository;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->validator = new DemoTestAssessmentValidator();
    }

    public function create(array $data)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::ASSESS_DEMO_TEST);
        $this->validator->validate($data);

        $testerId = $data[DemoTestAssessmentValidator::FIELD_TESTER_ID];
        $group = $data[DemoTestAssessmentValidator::FIELD_VEHICLE_CLASS_GROUP];
        $person = $this->personRepository->find($testerId);

        $authorisations = $this->getAuthorisationsForGroup($person, $group);
        $this->validateDataConsistency($authorisations, $group, $person);
        $this->validateAuthorisationStatus($authorisations);

        $this->qualifyTester($authorisations);
        $this->sendNotification($person, $group);
        $this->testerQualificationStatusChangeEvent->create($person, $group);
    }

    /**
     * @param AuthorisationForTestingMot[] $authorisations
     */
    private function qualifyTester($authorisations)
    {
        $qualifiedStatus = $this->getQualifiedStatus();

        foreach ($authorisations as $authorisation) {
            $authorisation->setStatus($qualifiedStatus);
        }
    }

    private function getAuthorisationsForGroup(Person $person, $group)
    {
        $classesInGroup = VehicleClassGroup::getClassesForGroup($group);

        /** @var AuthorisationForTestingMot[] $authorisations */
        $authorisations = ArrayUtils::filter(
            $person->getAuthorisationsForTestingMot(),
            function (AuthorisationForTestingMot $authorisationForTestingMot) use ($classesInGroup) {
                return in_array($authorisationForTestingMot->getVehicleClass(), $classesInGroup);
            }
        );

        return $authorisations;
    }

    /**
     * @param AuthorisationForTestingMot[] $authorisations
     */
    private function validateAuthorisationStatus(array $authorisations)
    {
        $errorSchema = new ErrorSchema();

        if (!$authorisations
            || $authorisations[0]->getStatus()->getCode() != AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED
        ) {
            $errorSchema->throwError(self::ERROR_NOT_DEMO_REQUIRED_STATUS);
        }
    }

    /**
     * @param AuthorisationForTestingMot[] $authorisations
     * @param                              $group
     * @param Person                       $person
     *
     * @throws \DomainException
     */
    private function validateDataConsistency(array $authorisations, $group, Person $person)
    {
        $classesInGroup = VehicleClassGroup::getClassesForGroup($group);

        if ($authorisations && count($authorisations) != count($classesInGroup)) {
            throw new \DomainException(sprintf(
                    self::ERROR_WRONG_NUMBER_OF_AUTHORISATION_RECORDS,
                    $person->getId(),
                    $group,
                    count($authorisations),
                    count($classesInGroup)
                )
            );
        }

        if (ArrayUtils::anyMatch(
            $authorisations,
            function (AuthorisationForTestingMot $authorisationA) use ($authorisations) {
                return ArrayUtils::anyMatch(
                    $authorisations, function (AuthorisationForTestingMot $authorisationB) use ($authorisationA) {
                        return $authorisationA->getStatus() != $authorisationB->getStatus();
                    }
                );
            }
        )
        ) {
            throw new \DomainException('Mismatch in statuses of mot testing authorisations.');
        }
    }

    private function getQualifiedStatus()
    {
        return $this->authorisationStatusRepository->getByCode(AuthorisationForTestingMotStatusCode::QUALIFIED);
    }

    /**
     * @param Person $person
     * @param string $groupId
     *
     * @return int
     */
    private function sendNotification(Person $person, $groupId)
    {
        $data = (new Notification())
            ->setRecipient($person->getId())
            ->setTemplate(Notification::TEMPLATE_TESTER_QUALIFICATION_STATUS)
            ->addField('vehicle_group', $groupId)
            ->toArray();

        return $this->notificationService->add($data);
    }
}
