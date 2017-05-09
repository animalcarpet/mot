<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Validator\PersonNameValidator;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\InvalidFieldValueException;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;

class PersonNameService extends AbstractService
{
    const FIRST_NAME = 'firstName';
    const MIDDLE_NAME = 'middleName';
    const LAST_NAME = 'lastName';
    /**
     * @var PersonNameValidator
     */
    private $validator;

    /**
     * @var XssFilter
     */
    private $xssFilter;

    /**
     * @var AuthorisationService
     */
    private $authService;

    /**
     * @var PersonDetailsChangeNotificationHelper
     */
    private $notificationHelper;

    /**
     * PersonNameService constructor.
     *
     * @param EntityManager                         $entityManager
     * @param PersonNameValidator                   $validator
     * @param XssFilter                             $xssFilter
     * @param AuthorisationServiceInterface         $authService
     * @param PersonDetailsChangeNotificationHelper $notificationHelper
     */
    public function __construct(
        EntityManager $entityManager,
        PersonNameValidator $validator,
        XssFilter $xssFilter,
        AuthorisationServiceInterface $authService,
        PersonDetailsChangeNotificationHelper $notificationHelper
    ) {
        parent::__construct($entityManager);

        $this->validator = $validator;
        $this->xssFilter = $xssFilter;
        $this->authService = $authService;
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * @param $personId
     * @param array $data contains firstName, middleName and lastName
     *
     * @return \DvsaEntities\Entity\Person
     *
     * @throws InvalidFieldValueException
     * @throws UnauthorisedException
     */
    public function update($personId, $data)
    {
        $this->authService->assertGranted(PermissionInSystem::EDIT_PERSON_NAME);
        $identity = $this->authService->getIdentity();

        if ($identity->getUserId() == $personId) {
            throw new UnauthorisedException('Not authorised');
        }

        $person = $this->findPerson($personId);

        $data = $this->xssFilter->filterMultiple($data);

        if (!$this->validator->isValid($data)) {
            throw new InvalidFieldValueException(implode(', ', $this->validator->getMessages()));
        }

        $person->setFirstName($data[self::FIRST_NAME]);
        $person->setMiddleName($data[self::MIDDLE_NAME]);
        $person->setFamilyName($data[self::LAST_NAME]);

        $this->entityManager->flush();

        $this->notificationHelper->sendChangedPersonalDetailsNotification($person);

        return $data;
    }
}
