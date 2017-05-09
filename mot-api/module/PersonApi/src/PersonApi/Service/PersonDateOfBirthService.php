<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Validator\DateOfBirthValidator;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\BadRequestException;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;

class PersonDateOfBirthService extends AbstractService
{
    /**
     * @var DateOfBirthValidator
     */
    private $dayOfBirthValidator;
    /**
     * @var AuthorisationService
     */
    private $authService;

    /**
     * @var PersonDetailsChangeNotificationHelper
     */
    private $notificationHelper;

    public function __construct(
        EntityManager $entityManager,
        DateOfBirthValidator $dayOfBirthValidator,
        AuthorisationService $authService,
        PersonDetailsChangeNotificationHelper $notificationHelper
    ) {
        parent::__construct($entityManager);
        $this->dayOfBirthValidator = $dayOfBirthValidator;
        $this->authService = $authService;
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * @param $personId
     * @param $data
     *
     * @throws BadRequestException
     * @throws UnauthorisedException
     * @throws \DvsaCommon\Date\Exception\IncorrectDateFormatException
     */
    public function update($personId, $data)
    {
        $this->authService->assertGranted(PermissionInSystem::EDIT_PERSON_DATE_OF_BIRTH);
        $this->assertNotChangingOwnName($personId);

        $person = $this->findPerson((int) $personId);

        if ($this->dayOfBirthValidator->isValid($data)) {
            $dayOfBirthDate = DateUtils::toDateFromParts(
                $data[DateOfBirthValidator::FIELD_DAY],
                $data[DateOfBirthValidator::FIELD_MONTH],
                $data[DateOfBirthValidator::FIELD_YEAR]
            );
            $person->setDateOfBirth($dayOfBirthDate);

            $this->entityManager->persist($person);
            $this->entityManager->flush($person);

            $this->notificationHelper->sendChangedPersonalDetailsNotification($person);
        } else {
            throw new BadRequestException('validation failed', 400);
        }
    }

    /**
     * @param $personId
     *
     * @throws UnauthorisedException
     */
    private function assertNotChangingOwnName($personId)
    {
        $identity = $this->authService->getIdentity();
        if ($identity->getUserId() == $personId) {
            throw new UnauthorisedException('Cannot edit your own date of birth');
        }
    }
}
