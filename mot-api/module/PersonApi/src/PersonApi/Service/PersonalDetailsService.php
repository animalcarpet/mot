<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaAuthorisation\Service\UserRoleService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\PersonContactType;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\PhoneContactType;
use PersonApi\Dto\PersonDetails;
use PersonApi\Service\Validator\PersonalDetailsValidator;

/**
 * Service to handle updating and getting user details.
 */
class PersonalDetailsService extends AbstractService
{
    /**
     * @var PersonalDetailsValidator
     */
    private $validator;

    /**
     * @var \DvsaCommon\Auth\MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var \DvsaAuthorisation\Service\AuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var \DvsaCommonApi\Filter\XssFilter
     */
    private $xssFilter;

    /**
     * @var UserRoleService
     */
    private $roleService;

    /**
     * @param \Doctrine\ORM\EntityManager                              $entityManager
     * @param \PersonApi\Service\Validator\PersonalDetailsValidator    $validator
     * @param \DvsaAuthorisation\Service\AuthorisationServiceInterface $authorisationService
     * @param \DvsaCommon\Auth\MotIdentityProviderInterface            $identityProvider
     * @param \DvsaCommonApi\Filter\XssFilter                          $xssFilter
     * @param \DvsaAuthorisation\Service\UserRoleService               $roleService
     */
    public function __construct(
        EntityManager $entityManager,
        PersonalDetailsValidator $validator,
        AuthorisationServiceInterface $authorisationService,
        MotIdentityProviderInterface $identityProvider,
        XssFilter $xssFilter,
        UserRoleService $roleService
    ) {
        parent::__construct($entityManager);

        $this->validator = $validator;
        $this->authorisationService = $authorisationService;
        $this->identityProvider = $identityProvider;
        $this->xssFilter = $xssFilter;
        $this->roleService = $roleService;
    }

    /**
     * @param int   $personId
     * @param array $data
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \DvsaCommon\Date\Exception\IncorrectDateFormatException
     * @throws \DvsaCommon\Exception\UnauthorisedException
     *
     * @return \PersonApi\Dto\PersonDetails
     */
    public function update($personId, $data)
    {
        $this->assertUpdateGranted($personId);

        // Strip script tags from form data (avoid XSS vulns)
        $data = $this->xssFilter->filterMultiple($data);
        $this->validator->validateContactDetails($data, true);
        $person = $this->findPerson($personId);

        $personContact = $this->updatePersonalContactDetails($person, $data);

        $this->entityManager->flush();

        return new PersonDetails(
            $person,
            $personContact->getDetails(),
            $this->entityManager,
            $this->getUserRoles($person)
        );
    }

    /**
     * @param int $personId
     *
     * @throws UnauthorisedException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return PersonDetails
     */
    public function get($personId)
    {
        $person = $this->findPerson($personId);

        $this->assertViewGranted($person);

        $contact = $this->getContactByPerson($person);

        if (null === $contact) {
            $contact = $this->createContactDetailPlaceholder($person);
        }

        return new PersonDetails(
            $person, $contact->getDetails(), $this->entityManager, $this->getUserRoles($person)
        );
    }

    /**
     * @param Person $person
     *
     * @return PersonContact
     */
    private function getContactByPerson(Person $person)
    {
        $personContactTypeRepository = $this->entityManager->getRepository(
            \DvsaEntities\Entity\PersonContactType::class
        );
        $personContactType = $personContactTypeRepository->findOneBy(['name' => PersonContactType::PERSONAL]);

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

    /**
     * Check access for current user to view profile of specified person.
     *
     * @param Person $person
     *
     * @throws UnauthorisedException
     */
    public function assertViewGranted(Person $person)
    {
        if ($this->authorisationService->isGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE)) {
            return;
        }

        $userId = $this->identityProvider->getIdentity()->getUserId();

        if ($userId === $person->getId()) {
            return;
        }

        //  ----    check access by site    ----
        foreach ($person->findSites() as $site) {
            if ($this->authorisationService->isGrantedAtSite(
                PermissionAtSite::VTS_EMPLOYEE_PROFILE_READ,
                $site->getId()
            )
            ) {
                return;
            }
        }

        //  ----    check access in organisation    --
        foreach ($person->findOrganisations() as $organisation) {
            if ($this->authorisationService->isGrantedAtOrganisation(
                PermissionAtOrganisation::AE_EMPLOYEE_PROFILE_READ,
                $organisation->getId()
            )
            ) {
                return;
            }
        }

        throw new UnauthorisedException('Cannot access profiles of other users');
    }

    /**
     * @param int $personId
     *
     * @throws UnauthorisedException
     */
    private function assertUpdateGranted($personId)
    {
        if ($this->identityProvider->getIdentity()->getUserId() != $personId) {
            throw new UnauthorisedException('Cannot access profiles of other users');
        }
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    private function getUserRoles(Person $person)
    {
        return $this->roleService->getDetailedRolesForPerson($person);
    }

    /**
     * @param Person $person
     * @param array  $data
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return PersonContact
     */
    private function updatePersonalContactDetails(Person $person, $data)
    {
        $personContact = $this->getContactByPerson($person);

        if (!$personContact) {
            $contactDetails = $this->createContactDetail();
            $personContact = $this->createPersonContact($person, $contactDetails);
        } else {
            $contactDetails = $personContact->getDetails();
        }

        // Update, create or remove an Address entity as this field is optional.
        $address = $contactDetails->getAddress();
        if (true === $this->validator->hasAddressData($data)) {
            if (!$address) {
                $address = new Address();
                $contactDetails->setAddress($address);
                $this->entityManager->persist($address);
            }

            $address
                ->setAddressLine1($data['addressLine1'])
                ->setAddressLine2($data['addressLine2'])
                ->setAddressLine3($data['addressLine3'])
                ->setTown($data['town'])
                ->setPostcode($data['postcode']);
        } else {
            // The user didn't provided address details, unlink it from the ContactDetails table and remove it from the
            // Address table if this entry already exists.
            $contactDetails->setAddress(null);
            if ($address) {
                $this->entityManager->remove($address);
            }
        }

        /** @var $email Email */
        $email = $this
            ->entityManager
            ->getRepository(Email::class)
            ->findOneBy(
                [
                    'contact' => $personContact->getDetails(),
                ]
            );
        if (!$email) {
            $email = $this->createEmail($contactDetails);
        }
        $email->setEmail($data['email']);

        // Update, create or remove a Phone entity as this field is optional.
        $phone = $this
            ->entityManager
            ->getRepository(Phone::class)
            ->findOneBy(['contact' => $contactDetails]);
        if (true === $this->validator->hasPhoneData($data)) {
            if (!$phone) {
                $phone = new Phone();
                /** @var PhoneContactType $phoneContactType */
                $phoneContactType = $this
                    ->entityManager
                    ->getRepository(PhoneContactType::class)
                    ->findOneBy(['code' => PhoneContactTypeCode::PERSONAL]);
                $phone
                    ->setContact($contactDetails)
                    ->setIsPrimary(true)
                    ->setContactType($phoneContactType);
                $this->entityManager->persist($phone);
            }

            $phone->setNumber($data['phoneNumber']);
        } else {
            if ($phone) {
                // The user didn't provided a phone number remove it from the Phone table if this entry already exists.
                $this->entityManager->remove($phone);
            }
        }

        $this->entityManager->persist($email);

        return $personContact;
    }

    /**
     * Returns an empty PersonContact instance used as a placeholder for classes that require a PersonContact object.
     * It's meant to be used when a Person instance doesn't have a PersonContact associated.
     *
     * @param Person $person
     *
     * @return PersonContact
     */
    private function createContactDetailPlaceholder(Person $person)
    {
        $contactDetail = new ContactDetail();
        $personContactTypeRepository = $this->entityManager->getRepository(
            \DvsaEntities\Entity\PersonContactType::class
        );
        $personContactType = $personContactTypeRepository->findOneBy(['name' => PersonContactType::PERSONAL]);
        $personContact = new PersonContact($contactDetail, $personContactType, $person);

        return $personContact;
    }

    /**
     * Creates an empty ContactDetail to be populated after.
     *
     * @return ContactDetail
     */
    private function createContactDetail()
    {
        $address = new Address();
        $contactDetail = new ContactDetail();
        $contactDetail->setAddress();

        $this->entityManager->persist($address);
        $this->entityManager->persist($contactDetail);

        return $contactDetail;
    }

    /**
     * Creates a personal PersonContactType linking Person and ContactDetail entities.
     *
     * @param Person        $person
     * @param ContactDetail $contactDetail
     *
     * @return PersonContactType
     */
    private function createPersonContact(Person $person, ContactDetail $contactDetail)
    {
        $personContactTypeRepository = $this->entityManager->getRepository(
            \DvsaEntities\Entity\PersonContactType::class
        );
        $personContactType = $personContactTypeRepository->findOneBy(['name' => PersonContactType::PERSONAL]);
        $personContact = new PersonContact($contactDetail, $personContactType, $person);

        $this->entityManager->persist($personContact);

        return $personContact;
    }

    /**
     * Creates an empty Email entity associated to a ContactDetail entity.
     *
     * @param ContactDetail $contactDetail
     *
     * @return Email
     */
    private function createEmail(ContactDetail $contactDetail)
    {
        $email = new Email();
        $email->setContact($contactDetail);

        $this->entityManager->persist($email);

        return $email;
    }
}
