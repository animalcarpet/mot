<?php

namespace OrganisationApi\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine1Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine2Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine3Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\CountryInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\DateOfBirthInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FamilyNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FirstNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\MiddleNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\PostcodeInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\TownInput;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\AuthorisedExaminerPrincipal;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\AuthorisedExaminerPrincipalRepository;
use OrganisationApi\Service\Mapper\AuthorisedExaminerPrincipalWithAddressMapper;
use DvsaEventApi\Service\EventService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeDisplayFormat;
use OrganisationApi\Service\Validator\AuthorisedExaminerPrincipalValidator;

/**
 * AEP service for getting, adding and deleting in context of an AE.
 */
class AuthorisedExaminerPrincipalService
{
    const NO_ASSOCIATION_FOUND
        = 'Could not delete Authorised Examiner Principal that is not associated with this Authorised Examiner';

    private $organisationRepository;
    private $mapper;
    private $authorisationService;
    private $eventService;
    private $aepRepository;
    private $aepValidator;

    public function __construct(
        OrganisationRepository $organisationRepository,
        MotAuthorisationServiceInterface $authorisationService,
        EventService $eventService,
        AuthorisedExaminerPrincipalRepository $aepRepository,
        MotIdentityProviderInterface $identityProvider,
        AuthorisedExaminerPrincipalValidator $aepValidator

    ) {
        $this->organisationRepository = $organisationRepository;
        $this->mapper = new AuthorisedExaminerPrincipalWithAddressMapper();
        $this->authorisationService = $authorisationService;
        $this->eventService = $eventService;
        $this->aepRepository = $aepRepository;
        $this->identityProvider = $identityProvider;
        $this->aepValidator = $aepValidator;
    }

    public function getForAuthorisedExaminer($authorisedExaminerId)
    {
        $permission = PermissionAtOrganisation::LIST_AEP_AT_AUTHORISED_EXAMINER;
        $this->authorisationService->assertGrantedAtOrganisation($permission, $authorisedExaminerId);

        $authorisedExaminer = $this->organisationRepository->getAuthorisedExaminer($authorisedExaminerId);

        $authorisedExaminerAuthorisation = $authorisedExaminer->getAuthorisedExaminer();
        $aeps = $this->aepRepository->findAllByAuthForAe($authorisedExaminerAuthorisation->getId());
        $data = $this->mapper->manyToDto($aeps);

        return $data;
    }

    public function getForAuthorisedExaminerById($authorisedExaminerId, $authorisedExaminerPrincipalId)
    {
        $permission = PermissionAtOrganisation::LIST_AEP_AT_AUTHORISED_EXAMINER;
        $this->authorisationService->assertGrantedAtOrganisation($permission, $authorisedExaminerId);

        $authorisedExaminer = $this->organisationRepository->getAuthorisedExaminer($authorisedExaminerId);

        $authorisedExaminerAuthorisation = $authorisedExaminer->getAuthorisedExaminer();
        $aep = $this->aepRepository->findByIdAndAuthForAe($authorisedExaminerPrincipalId, $authorisedExaminerAuthorisation->getId());
        $data = $this->mapper->toDto($aep);

        return $data;
    }

    public function deletePrincipalForAuthorisedExaminer($authorisedExaminerId, $authorisedExaminerPrincipalId)
    {
        $permission = PermissionAtOrganisation::AUTHORISED_EXAMINER_PRINCIPAL_REMOVE;
        $this->authorisationService->assertGrantedAtOrganisation($permission, $authorisedExaminerId);

        $authorisedExaminer = $this->organisationRepository->getAuthorisedExaminer($authorisedExaminerId);

        $authorisedExaminerAuthorisation = $authorisedExaminer->getAuthorisedExaminer();

        $aep = $this->aepRepository->findByIdAndAuthForAe($authorisedExaminerPrincipalId, $authorisedExaminerAuthorisation->getId());
        $contactDetails = $aep->getContactDetails();
        $address = $contactDetails->getAddress();

        if ($contactDetails->getAddress()) {
            $this->aepRepository->remove($address);
        }

        foreach ($contactDetails->getEmails() as $email) {
            $this->aepRepository->remove($email);
        }

        foreach ($contactDetails->getPhones() as $phone) {
            $this->aepRepository->remove($phone);
        }

        $this->aepRepository->remove($aep);

        $description = sprintf(
            EventDescription::AEP_REMOVED_TO_AE,
            $aep->getDisplayName(),
            DateTimeDisplayFormat::date($aep->getDateOfBirth()),
            $authorisedExaminer->getAuthorisedExaminer()->getNumber(),
            $authorisedExaminer->getName(),
            $this->identityProvider->getIdentity()->getUsername(),
            DateTimeDisplayFormat::dateTime(new \DateTime())
        );

        $this->eventService->addOrganisationEvent($authorisedExaminer, EventTypeCode::REMOVE_AEP, $description);

        $this->aepRepository->flush();
    }

    public function createForAuthorisedExaminer($authorisedExaminerId, $data)
    {
        $permission = PermissionAtOrganisation::AUTHORISED_EXAMINER_PRINCIPAL_CREATE;
        $this->authorisationService->assertGrantedAtOrganisation($permission, $authorisedExaminerId);

        $this->aepValidator->validate($data);

        $authorisedExaminer = $this->organisationRepository->getAuthorisedExaminer($authorisedExaminerId);
        $authorisedExaminerAuthorisation = $authorisedExaminer->getAuthorisedExaminer();

        $aep = $this->createPrincipal($authorisedExaminerAuthorisation, $data);
        $this->aepRepository->persist($aep);

        $description = sprintf(
            EventDescription::AEP_ADDED_TO_AE,
            $aep->getDisplayName(),
            DateTimeDisplayFormat::date($aep->getDateOfBirth()),
            $authorisedExaminer->getAuthorisedExaminer()->getNumber(),
            $authorisedExaminer->getName(),
            $this->identityProvider->getIdentity()->getUsername(),
            DateTimeDisplayFormat::dateTime(new \DateTime())
        );

        $this->eventService->addOrganisationEvent($authorisedExaminer, EventTypeCode::CREATE_AEP, $description);

        $this->aepRepository->flush();

        return ['authorisedExaminerPrincipalId' => $aep->getId()];
    }

    /**
     * @param AuthorisationForAuthorisedExaminer $authorisationForAuthorisedExaminer
     * @param array                              $data
     *
     * @return AuthorisedExaminerPrincipal
     */
    private function createPrincipal(AuthorisationForAuthorisedExaminer $authorisationForAuthorisedExaminer, array $data)
    {
        $address = new Address();
        $address->setAddressLine1(ArrayUtils::get($data, AddressLine1Input::FIELD));
        $address->setAddressLine2(ArrayUtils::tryGet($data, AddressLine2Input::FIELD));
        $address->setAddressLine3(ArrayUtils::tryGet($data, AddressLine3Input::FIELD));
        $address->setPostcode(ArrayUtils::get($data, PostcodeInput::FIELD));
        $address->setTown(ArrayUtils::get($data, TownInput::FIELD));
        $address->setCountry(ArrayUtils::tryGet($data, CountryInput::FIELD));

        $contactDetail = new ContactDetail();
        $contactDetail->setAddress($address);

        $aep = new AuthorisedExaminerPrincipal();
        $aep->setAuthorisationForAuthorisedExaminer($authorisationForAuthorisedExaminer);
        $aep->setContactDetails($contactDetail);
        $aep->setFirstName(ArrayUtils::get($data, FirstNameInput::FIELD));
        $aep->setMiddleName(ArrayUtils::tryGet($data, MiddleNameInput::FIELD));
        $aep->setFamilyName(ArrayUtils::tryGet($data, FamilyNameInput::FIELD));
        $aep->setDateOfBirth((new \DateTime(ArrayUtils::get($data, DateOfBirthInput::FIELD))));

        return $aep;
    }
}
