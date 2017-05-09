<?php

namespace Organisation\Authorisation;

use DvsaCommon\Auth\Assertion\RemovePositionAtAeAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Dto\Person\PersonDto;

class AuthorisedExaminerViewAuthorisation
{
    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;
    /** @var int */
    private $authorisedExaminerId;
    /** @var MotIdentityProviderInterface */
    private $identityProvider;
    /** @var RemovePositionAtAeAssertion */
    private $removePositionAssertion;
    /** @var OrganisationPositionDto[] */
    private $positions;

    /**
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param MotIdentityProviderInterface     $identityProvider
     * @param int                              $authorisedExaminerId
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        MotIdentityProviderInterface $identityProvider,
        $authorisedExaminerId
    ) {
        $this->authorisationService = $authorisationService;
        $this->authorisedExaminerId = $authorisedExaminerId;
        $this->identityProvider = $identityProvider;

        $this->removePositionAssertion = new RemovePositionAtAeAssertion($authorisationService, $identityProvider);
    }

    /**
     * @param OrganisationPositionDto[] $positions
     */
    public function setPositions(array $positions)
    {
        $this->positions = $positions;
    }

    public function canViewAuthorisedExaminerPrincipals()
    {
        $permission = PermissionAtOrganisation::LIST_AEP_AT_AUTHORISED_EXAMINER;

        return $this->authorisationService->isGrantedAtOrganisation($permission, $this->authorisedExaminerId);
    }

    public function canCreateAuthorisedExaminerPrincipal()
    {
        $permission = PermissionAtOrganisation::AUTHORISED_EXAMINER_PRINCIPAL_CREATE;

        return $this->authorisationService->isGrantedAtOrganisation($permission, $this->authorisedExaminerId);
    }

    public function canRemoveAuthorisedExaminerPrincipal()
    {
        $permission = PermissionAtOrganisation::AUTHORISED_EXAMINER_PRINCIPAL_REMOVE;

        return $this->authorisationService->isGrantedAtOrganisation($permission, $this->authorisedExaminerId);
    }

    public function canViewVtsList()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::VEHICLE_TESTING_STATION_LIST_AT_AE, $this->authorisedExaminerId
        );
    }

    public function canCreateSiteAssociation()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_SITE_LINK, $this->authorisedExaminerId
        );
    }

    public function canRemoveSiteAssociation()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_SITE_UNLINK, $this->authorisedExaminerId
        );
    }

    public function canViewVts($id)
    {
        return $this->authorisationService->isGrantedAtSite(PermissionAtSite::VEHICLE_TESTING_STATION_READ, $id);
    }

    public function canRemovePosition(OrganisationPositionDto $position)
    {
        return $this->removePositionAssertion->isGranted(
            $position->getRole(),
            $position->getPerson()->getId(),
            $this->authorisedExaminerId
        );
    }

    public function canViewPersonnel()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::LIST_AE_POSITIONS, $this->authorisedExaminerId
        );
    }

    public function canNominate()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::NOMINATE_ROLE_AT_AE, $this->authorisedExaminerId
        );
    }

    public function canBuySlots()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::SLOTS_PURCHASE, $this->authorisedExaminerId
        );
    }

    public function canViewTransactionHistory()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::SLOTS_TRANSACTION_READ_FULL, $this->authorisedExaminerId
        );
    }

    public function canViewSlotUsage()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_SLOTS_USAGE_READ, $this->authorisedExaminerId
        );
    }

    public function canViewTestLogs()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_TEST_LOG, $this->authorisedExaminerId
        );
    }

    public function canSetDirectDebit()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::SLOTS_PAYMENT_DIRECT_DEBIT, $this->authorisedExaminerId
        );
    }

    public function canRefund()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::SLOTS_REFUND);
    }

    public function canSettlePayment()
    {
        return $this->authorisationService->isGranted(
            PermissionAtOrganisation::SLOTS_INSTANT_SETTLEMENT
        );
    }

    public function canViewSlotBalance()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_SLOTS_BALANCE_READ, $this->authorisedExaminerId
        );
    }

    public function canAdjustSlotBalance()
    {
        return $this->authorisationService->isGranted(
            PermissionInSystem::SLOTS_ADJUSTMENT
        );
    }

    public function canViewSlotsSection()
    {
        return $this->canBuySlots()
            || $this->canViewTransactionHistory()
            || $this->canViewSlotUsage()
            || $this->canViewTestLogs()
            || $this->canSetDirectDebit()
            || $this->canSettlePayment()
            || $this->canViewSlotBalance();
    }

    public function canViewAeStatus()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::AUTHORISED_EXAMINER_READ_FULL);
    }

    public function canSearchAe()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::AUTHORISED_EXAMINER_LIST);
    }

    public function canSearchUser()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::USER_SEARCH);
    }

    public function canViewProfile(PersonDto $person)
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_EMPLOYEE_PROFILE_READ,
            $this->authorisedExaminerId
        )
        && $this->personIsEmployee($person);
    }

    public function canViewUsername()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_USERNAME_VIEW,
            $this->authorisedExaminerId
        );
    }

    private function personIsEmployee(PersonDto $person)
    {
        return ArrayUtils::anyMatch(
            $this->positions,
            function (OrganisationPositionDto $position) use ($person) {
                return $position->getPerson()->getId() == $person->getId()
                && $position->isActive();
            }
        );
    }

    public function canViewEventHistory()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::LIST_EVENT_HISTORY);
    }

    public function canViewAETestQualityInformation()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_VIEW_TEST_QUALITY,
            $this->authorisedExaminerId
        );
    }

    public function canSetupDirectDebit()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::SLOTS_PAYMENT_DIRECT_DEBIT,
            $this->authorisedExaminerId
        );
    }

    public function canManageDirectDebit()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::SLOTS_MANAGE_DIRECT_DEBIT,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEBusinessDetailsName()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_NAME,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEBusinessDetailsTradingName()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_TRADING_NAME,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEBusinessDetailsBusinessType()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_TYPE,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEBusinessDetailsStatus()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_STATUS,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEBusinessDetailsDVSAAreaOffice()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_DVSA_AREA_OFFICE,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEContactDetailsRegisteredOfficeAddress()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_ADDRESS,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEContactDetailsRegisteredOfficeEmail()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_EMAIL,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEContactDetailsRegisteredOfficeTelephone()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_PHONE,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEContactDetailsCorrespondenceAddress()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_ADDRESS,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEContactDetailsCorrespondenceEmail()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_EMAIL,
            $this->authorisedExaminerId
        );
    }

    public function canUpdateAEContactDetailsCorrespondenceTelephone()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_PHONE,
            $this->authorisedExaminerId
        );
    }
}
