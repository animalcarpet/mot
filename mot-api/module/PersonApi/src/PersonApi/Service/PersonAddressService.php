<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Validator\AddressValidator;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\InvalidFieldValueException;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\PersonContactType;
use DvsaCommon\Constants\PersonContactType as ContactType;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;

class PersonAddressService extends AbstractService
{
    const FIRST_LINE = 'firstLine';
    const SECOND_LINE = 'secondLine';
    const THIRD_LINE = 'thirdLine';
    const TOWN_OR_CITY = 'townOrCity';
    const COUNTRY = 'country';
    const POSTCODE = 'postcode';

    /**
     * @var AddressValidator
     */
    private $validator;

    /**
     * @var AuthorisationService
     */
    private $authService;

    /**
     * @var PersonDetailsChangeNotificationHelper
     */
    private $notificationHelper;

    /**
     * PersonAddressService constructor.
     *
     * @param EntityManager                         $entityManager
     * @param AddressValidator                      $validator
     * @param AuthorisationService                  $authorisationService
     * @param PersonDetailsChangeNotificationHelper $notificationHelper
     */
    public function __construct(
        EntityManager $entityManager,
        AddressValidator $validator,
        AuthorisationService $authorisationService,
        PersonDetailsChangeNotificationHelper $notificationHelper
    ) {
        parent::__construct($entityManager);

        $this->validator = $validator;
        $this->authService = $authorisationService;
        $this->notificationHelper = $notificationHelper;
    }

    public function update($personId, $data)
    {
        $identity = $this->authService->getIdentity();

        if ($personId != $identity->getUserId()) {
            $this->authService->assertGranted(PermissionInSystem::EDIT_PERSON_ADDRESS);
        }

        if (!$this->validator->isValid($data)) {
            throw new InvalidFieldValueException(implode(', ', $this->validator->getMessages()));
        }
        $person = $this->findPerson($personId);

        $personContact = $this->getContactByPerson($person);
        $address = $personContact->getDetails()->getAddress();

        $address->setAddressLine1($data[self::FIRST_LINE]);
        $address->setAddressLine2($data[self::SECOND_LINE]);
        $address->setAddressLine3($data[self::THIRD_LINE]);
        $address->setTown($data[self::TOWN_OR_CITY]);
        $address->setCountry($data[self::COUNTRY]);
        $address->setPostcode($data[self::POSTCODE]);

        $this->entityManager->flush();

        if ($identity->getUserId() != $personId) {
            $this->notificationHelper->sendChangedPersonalDetailsNotification($person);
        }

        return $data;
    }

    /**
     * @param Person $person
     *
     * @return PersonContact
     */
    private function getContactByPerson(Person $person)
    {
        $personContactTypeRepository = $this->entityManager->getRepository(
            PersonContactType::class
        );
        $personContactType = $personContactTypeRepository->findOneBy(['name' => ContactType::PERSONAL]);

        return $this
            ->entityManager
            ->getRepository(PersonContact::class)
            ->findOneBy(
                [
                    'person' => $person,
                    'type' => $personContactType,
                ]
            );
    }
}
