<?php

namespace OrganisationApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Validator\ValidationChain;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\AuthForAeStatus;
use DvsaEntities\Entity\CompanyType;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationContactType;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Repository\AuthForAeStatusRepository;
use DvsaEntities\Repository\CompanyTypeRepository;
use DvsaEntities\Repository\OrganisationContactTypeRepository;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\PhoneContactTypeRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEventApi\Service\EventService;
use OrganisationApi\Service\Validator\UpdateProperty\AeAddressValidator;
use OrganisationApi\Service\Validator\UpdateProperty\AeAreaOfficeValidator;
use OrganisationApi\Service\Validator\UpdateProperty\AeEmailValidator;
use OrganisationApi\Service\Validator\UpdateProperty\AeNameValidator;
use OrganisationApi\Service\Validator\UpdateProperty\AePhoneValidator;
use OrganisationApi\Service\Validator\UpdateProperty\AeStatusValidator;
use OrganisationApi\Service\Validator\UpdateProperty\AeTradingNameValidator;
use OrganisationApi\Service\Validator\UpdateProperty\AeTypeValidator;

class UpdateAeDetailsService implements AutoWireableInterface
{
    const DIFF_OLD_VALUE = 'old';
    const DIFF_NEW_VALUE = 'new';

    protected $companyTypeRepository;
    private $organisationRepository;
    private $organisationContactTypeRepository;
    private $entityManager;
    private $authorisationService;
    private $phoneContactTypeRepository;
    private $authForAeStatusRepository;
    private $siteRepository;
    private $eventService;
    private $identityProvider;

    public function __construct(
        OrganisationRepository $organisationRepository,
        OrganisationContactTypeRepository $organisationContactTypeRepository,
        CompanyTypeRepository $companyTypeRepository,
        EntityManager $entityManager,
        MotAuthorisationServiceInterface $authorisationService,
        PhoneContactTypeRepository $phoneContactTypeRepository,
        AuthForAeStatusRepository $authForAeStatusRepository,
        SiteRepository $siteRepository,
        EventService $eventService,
        MotIdentityProviderInterface $identityProvider
    ) {
        $this->organisationRepository = $organisationRepository;
        $this->organisationContactTypeRepository = $organisationContactTypeRepository;
        $this->entityManager = $entityManager;
        $this->authorisationService = $authorisationService;
        $this->companyTypeRepository = $companyTypeRepository;
        $this->phoneContactTypeRepository = $phoneContactTypeRepository;
        $this->authForAeStatusRepository = $authForAeStatusRepository;
        $this->siteRepository = $siteRepository;
        $this->eventService = $eventService;
        $this->identityProvider = $identityProvider;
    }

    public function update($id, array $data)
    {
        $this->entityManager->beginTransaction();
        $ae = $this->organisationRepository->getAuthorisedExaminer($id);

        $dataKeys = array_keys($data);

        $validatorChain = $this->buildValidatorChain($ae, $data);

        $isChangingRegisteredAddress = $this->isRegisteredAddressBeingChanged($dataKeys);
        if ($isChangingRegisteredAddress) {
            $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_ADDRESS, $id);
            $validatorChain->addValidator(new AeAddressValidator('registered'));
        }

        $isChangingCorrespondenceAddress = $this->isCorrespondenceAddressBeingChanged($dataKeys);

        if ($isChangingCorrespondenceAddress) {
            $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_ADDRESS, $id);
            $validatorChain->addValidator(new AeAddressValidator('correspondence'));
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::REGISTERED_PHONE, $data)) {
            $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_PHONE, $id);
            $validatorChain->addValidator(new AePhoneValidator('registered'));
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::CORRESPONDENCE_PHONE, $data)) {
            $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_PHONE, $id);
            $validatorChain->addValidator(new AePhoneValidator('correspondence'));
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::REGISTERED_EMAIL, $data)) {
            $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_EMAIL, $id);
            $validatorChain->addValidator(new AeEmailValidator('registered'));
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::CORRESPONDENCE_EMAIL, $data)) {
            $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_EMAIL, $id);
            $validatorChain->addValidator(new AeEmailValidator('correspondence'));
        }

        // ############################## - VALIDATION - ##############################

        $validatorChain->validate($data);

        $diff = [];

        if (array_key_exists(AuthorisedExaminerPatchModel::NAME, $data)) {
            $this->updateName($ae, $data[AuthorisedExaminerPatchModel::NAME], $diff);
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::TRADING_NAME, $data)) {
            $this->updateTradingName($ae, $data[AuthorisedExaminerPatchModel::TRADING_NAME], $diff);
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::TYPE, $data)) {
            $this->updateCompanyType($ae, $data[AuthorisedExaminerPatchModel::TYPE], $diff);
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::COMPANY_NUMBER, $data)) {
            $this->updateCompanyNumber($ae, $data[AuthorisedExaminerPatchModel::COMPANY_NUMBER], $diff);
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::STATUS, $data)) {
            $this->updateStatus($ae, $data[AuthorisedExaminerPatchModel::STATUS], $diff);
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::AREA_OFFICE, $data)) {
            $this->updateAreaOffice($ae, $data[AuthorisedExaminerPatchModel::AREA_OFFICE], $diff);
        }

        if ($isChangingRegisteredAddress) {
            $this->updateAddress($ae, AuthorisedExaminerPatchModel::createForRegisteredContact(), $data);
        }

        if ($isChangingCorrespondenceAddress) {
            $this->updateAddress($ae, AuthorisedExaminerPatchModel::createForCorrespondenceContact(), $data);
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::REGISTERED_PHONE, $data)) {
            $this->updatePhone($ae, AuthorisedExaminerPatchModel::createForRegisteredContact(), $data);
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::CORRESPONDENCE_PHONE, $data)) {
            $this->updatePhone($ae, AuthorisedExaminerPatchModel::createForCorrespondenceContact(), $data);
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::REGISTERED_EMAIL, $data)) {
            $this->updateEmail($ae, AuthorisedExaminerPatchModel::createForRegisteredContact(), $data);
        }

        if (array_key_exists(AuthorisedExaminerPatchModel::CORRESPONDENCE_EMAIL, $data)) {
            $this->updateEmail($ae, AuthorisedExaminerPatchModel::createForCorrespondenceContact(), $data);
        }

        if (!empty($diff)) {
            $this->sendDiffEvent($ae, $diff);
        }

        $this->organisationRepository->persist($ae);
        $this->entityManager->flush();

        $this->entityManager->commit();
    }

    private function updateName(Organisation $ae, $name, &$diff)
    {
        $oldName = $ae->getName();

        $ae->setName($name);

        if ($oldName != $name) {
            $diff += $this->createDiffItem('Name', $oldName, $name);
        }
    }

    private function updateTradingName(Organisation $ae, $tradingName, &$diff)
    {
        $ae->setTradingAs($tradingName);
    }

    private function updateCompanyNumber(Organisation $ae, $companyNumber, &$diff)
    {
        $ae->setRegisteredCompanyNumber($companyNumber);
    }

    private function updateStatus(Organisation $ae, $statusCode, &$diff)
    {
        $oldStatusName = $ae->getAuthorisedExaminer()->getStatus()->getName();

        /** @var AuthForAeStatus $status */
        $status = $this->authForAeStatusRepository->getByCode($statusCode);
        $ae->getAuthorisedExaminer()
            ->setStatus($status)
            ->setStatusChangedOn(new \DateTime());

        if ($oldStatusName != $status->getName()) {
            $diff += $this->createDiffItem('Status', $oldStatusName, $status->getName());
        }
    }

    private function updateCompanyType(Organisation $ae, $companyTypeCode, &$diff)
    {
        /** @var CompanyType $companyType */
        $companyType = $this->companyTypeRepository->getByCode($companyTypeCode);
        if ($companyType->getCode() != CompanyTypeCode::COMPANY) {
            $this->updateCompanyNumber($ae, null, $diff);
        }
        $ae->setCompanyType($companyType);
    }

    private function updateAreaOffice(Organisation $ae, $areaOfficeNumber, &$diff)
    {
        $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_DVSA_AREA_OFFICE, $ae->getId());

        $areaOfficeId = $this->getAreaOfficeIdByNumber($areaOfficeNumber);
        $areaOffice = $this->siteRepository->get($areaOfficeId);

        $oldAreaOfficeNumber = ($ao = $ae->getAuthorisedExaminer()->getAreaOffice()) ? $ao->getSiteNumber() : 'N\A';
        $ae->getAuthorisedExaminer()->setAreaOffice($areaOffice);

        $newAreaOfficeNumber = $areaOffice->getSiteNumber();

        if ($newAreaOfficeNumber != $oldAreaOfficeNumber) {
            $diff += $this->createDiffItem('Area Office', $oldAreaOfficeNumber, $newAreaOfficeNumber);
        }
    }

    private function getAreaOfficeIdByNumber($aoNumber)
    {
        $allAreaOffices = $this->siteRepository->getAllAreaOffices();
        $aoNumber = (int) $aoNumber;

        foreach ($allAreaOffices as $areaOffice) {
            if ($aoNumber == $areaOffice['areaOfficeNumber']) {
                return $areaOffice['id'];
            }
        }

        return null;
    }

    private function updateAddress(Organisation $ae, AuthorisedExaminerPatchModel $patchModel, $data)
    {
        $addressLine1 = ArrayUtils::tryGet($data, $patchModel->getAddressLine1Field(), '');
        $addressLine2 = ArrayUtils::tryGet($data, $patchModel->getAddressLine2Field(), '');
        $addressLine3 = ArrayUtils::tryGet($data, $patchModel->getAddressLine3Field(), '');

        $town = ArrayUtils::tryGet($data, $patchModel->getTownField(), '');
        $country = ArrayUtils::tryGet($data, $patchModel->getCountryField(), '');
        $postcode = ArrayUtils::tryGet($data, $patchModel->getPostcodeField(), '');

        $organisationContact = $ae->getContactByType($patchModel->getOrganisationContactTypeCode());

        /** @var OrganisationContactType $type */
        $type = $this->organisationContactTypeRepository->getByCode($patchModel->getOrganisationContactTypeCode());

        $contact = $organisationContact ? $organisationContact->getDetails() : new ContactDetail();

        if ($contact->getAddress() === null) {
            $address = new Address();
            $contact->setAddress($address);
        }

        $address = $contact->getAddress();

        $address->setAddressLine1($addressLine1);
        $address->setAddressLine2($addressLine2);
        $address->setAddressLine3($addressLine3);
        $address->setTown($town);
        $address->setCountry($country);
        $address->setPostcode($postcode);

        $ae->setContact($contact, $type);

        $this->entityManager->persist($address);
        $this->entityManager->persist($contact);
        $this->entityManager->persist($ae);
    }

    private function updatePhone(Organisation $ae, AuthorisedExaminerPatchModel $patchModel, $data)
    {
        /** @var OrganisationContactType $type */
        $type = $this->organisationContactTypeRepository
            ->getByCode($patchModel->getOrganisationContactTypeCode());

        $phoneType = $this->phoneContactTypeRepository
            ->getByCode(PhoneContactTypeCode::BUSINESS);
        $phone = new Phone();
        $phone->setNumber($data[$patchModel->getPhoneField()]);
        $phone->setIsPrimary(true);
        $phone->setContactType($phoneType);
        $organisationContact = $ae->getContactByType($patchModel->getOrganisationContactTypeCode());

        $contact = $organisationContact ? $organisationContact->getDetails() : null;
        if ($organisationContact === null) {
            $contact = new ContactDetail();
            $contact->setAddress(new Address());
            $ae->setContact($contact, $type);
        }

        /** @var Phone[] $primaryPhones */
        $primaryPhones = ArrayUtils::filter($contact->getPhones(), function (Phone $phone) {
            return $phone->getIsPrimary();
        });

        foreach ($primaryPhones as $primaryPhone) {
            $contact->removePhone($primaryPhone);
            $this->entityManager->remove($primaryPhone);
        }

        $phone->setContact($contact);
        $contact->addPhone($phone);

        $this->entityManager->persist($contact);
        $this->entityManager->persist($ae);
        $this->entityManager->persist($phone);
    }

    private function updateEmail(Organisation $ae, AuthorisedExaminerPatchModel $patchModel, $data)
    {
        /** @var OrganisationContactType $type */
        $type = $this->organisationContactTypeRepository
            ->getByCode($patchModel->getOrganisationContactTypeCode());

        $email = new Email();
        $email->setEmail($data[$patchModel->getEmailField()]);
        $email->setIsPrimary(true);
        $organisationContact = $ae->getContactByType($patchModel->getOrganisationContactTypeCode());

        $contact = $organisationContact ? $organisationContact->getDetails() : null;
        if ($organisationContact === null) {
            $contact = new ContactDetail();
            $contact->setAddress(new Address());
            $ae->setContact($contact, $type);
        }

        /** @var Email[] $primaryEmails */
        $primaryEmails = ArrayUtils::filter($contact->getEmails(), function (Email $email) {
            return $email->getIsPrimary();
        });

        foreach ($primaryEmails as $primaryEmail) {
            $contact->removeEmail($primaryEmail);
            $this->entityManager->remove($primaryEmail);
        }

        $email->setContact($contact);
        $contact->addEmail($email);

        $this->entityManager->persist($contact);
        $this->entityManager->persist($ae);
        $this->entityManager->persist($email);
    }

    private function isRegisteredAddressBeingChanged($dataKeys)
    {
        return AuthorisedExaminerPatchModel::containsRegisteredAddressProperty($dataKeys);
    }

    private function isCorrespondenceAddressBeingChanged($dataKeys)
    {
        return AuthorisedExaminerPatchModel::containsCorrespondenceAddressProperty($dataKeys);
    }

    private function buildValidatorChain(Organisation $ae, array $data)
    {
        $validatorChain = new ValidationChain();

        $fields = [
            AuthorisedExaminerPatchModel::NAME => function (ValidationChain &$validatorChain) use ($ae) {
                $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_NAME, $ae->getId());
                $validatorChain->addValidator(new AeNameValidator());
            },
            AuthorisedExaminerPatchModel::TRADING_NAME => function (ValidationChain &$validatorChain) use ($ae) {
                $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_TRADING_NAME, $ae->getId());
                $validatorChain->addValidator(new AeTradingNameValidator());
            },
            AuthorisedExaminerPatchModel::TYPE => function (ValidationChain &$validatorChain) use ($ae) {
                $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_TYPE, $ae->getId());
                $validatorChain->addValidator(new AeTypeValidator());
            },
            AuthorisedExaminerPatchModel::STATUS => function (ValidationChain &$validatorChain) use ($ae) {
                $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_STATUS, $ae->getId());
                $validatorChain->addValidator(new AeStatusValidator());
            },
            AuthorisedExaminerPatchModel::AREA_OFFICE => function (ValidationChain &$validatorChain) use ($ae) {
                $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_TYPE, $ae->getId());
                $validatorChain->addValidator(new AeAreaOfficeValidator());
            },
        ];

        foreach ($fields as $field => $addValidator) {
            if (array_key_exists($field, $data)) {
                $addValidator($validatorChain);
            }
        }

        return $validatorChain;
    }

    private function createDiffItem($key, $oldValue, $newValue)
    {
        return [
            $key => [
                self::DIFF_OLD_VALUE => $oldValue,
                self::DIFF_NEW_VALUE => $newValue,
            ],
        ];
    }

    private function sendDiffEvent(Organisation $ae, $diff)
    {
        $keys = array_keys($diff);
        $oldValues = array_column($diff, self::DIFF_OLD_VALUE);
        $newValues = array_column($diff, self::DIFF_NEW_VALUE);

        $description = sprintf(EventDescription::UPDATE_AE_PROPERTY,
            implode(', ', $keys),
            implode(', ', $oldValues),
            implode(', ', $newValues),
            $ae->getAuthorisedExaminer()->getNumber(),
            $ae->getName(),
            $this->identityProvider->getIdentity()->getUsername()
        );

        $this->eventService->addOrganisationEvent($ae, EventTypeCode::UPDATE_AE, $description);
    }
}
