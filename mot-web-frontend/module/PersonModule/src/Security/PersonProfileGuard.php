<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule\Security;

use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Auth\Assertion\CreateMotTestingCertificateAssertion;
use DvsaCommon\Auth\Assertion\UpdateMotTestingCertificateAssertion;
use DvsaCommon\Auth\Assertion\RemoveMotTestingCertificateAssertion;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Model\OrganisationBusinessRoleCode;
use InvalidArgumentException;
use PersonApi\Dto\PersonDetails;

/**
 * Class PersonProfileGuard.
 */
class PersonProfileGuard
{
    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var int
     */
    private $loggedInPersonId;

    /**
     * @var PersonDetails
     */
    private $targetPersonDetails;

    /**
     * @var TesterAuthorisation
     */
    private $testerAuthorisation;

    /**
     * @var array
     */
    private $tradeRolesAndAssociations;

    /**
     * @var string
     */
    private $context;

    /**
     * @var array
     */
    private $targetPersonRoles;

    /**
     * @var array
     */
    private $loggedInPersonRoles;

    private static $dvsaRoles = [
        RoleCode::SCHEME_MANAGER,
        RoleCode::SCHEME_USER,
        RoleCode::AREA_OFFICE_1,
        RoleCode::AREA_OFFICE_2,
        RoleCode::VEHICLE_EXAMINER,
        RoleCode::CUSTOMER_SERVICE_MANAGER,
        RoleCode::CUSTOMER_SERVICE_OPERATIVE,
        RoleCode::DVLA_MANAGER,
        RoleCode::DVLA_OPERATIVE,
        RoleCode::FINANCE,
        RoleCode::SCHEME_MANAGER,
    ];

    /**
     * PersonProfileGuard constructor.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param MotIdentityProviderInterface     $identityProvider
     * @param PersonalDetails                  $targetPersonDetails
     * @param TesterAuthorisation              $testerAuthorisation
     * @param array                            $tradeRolesAndAssociations
     * @param string                           $context                   The context in which we are viewing the profile. Could be AE, VE
     *                                                                    or User Search.
     */
    public function __construct(MotAuthorisationServiceInterface $authorisationService,
                                MotIdentityProviderInterface $identityProvider, PersonalDetails $targetPersonDetails,
                                TesterAuthorisation $testerAuthorisation, array $tradeRolesAndAssociations,
                                $context)
    {
        $this->authorisationService = $authorisationService;
        $this->identityProvider = $identityProvider;
        $this->targetPersonDetails = $targetPersonDetails;
        $this->testerAuthorisation = $testerAuthorisation;
        $this->tradeRolesAndAssociations = $tradeRolesAndAssociations;

        $availableContexts = ContextProvider::getAvailableContexts();
        if (!in_array($context, $availableContexts)) {
            throw new InvalidArgumentException(sprintf('Invalid context "%s". These are the valid ones: "%s"',
                $context, implode('", "', $availableContexts)));
        }
        $this->context = $context;
    }

    /**
     * The user is viewing himself in the "Your Profile" page context.
     *
     * @return bool
     */
    public function isViewingOwnProfile()
    {
        return ContextProvider::YOUR_PROFILE_CONTEXT === $this->context;
    }

    /**
     * The user is viewing himself in any context.
     *
     * @return bool
     */
    public function isViewingHimself()
    {
        return (int) $this->getLoggedInPersonId() === (int) $this->targetPersonDetails->getId();
    }

    /**
     * View Driving licence.
     *
     * Rule: When SM, SU, AO1, AO2, VE, CSM, CSCO View AEDM, AED, SITE-M, SITE-A, TESTER, NO-ROLES.
     *       OR When AEDM, AED, SITE-M, SITE-A, TESTER, NO-ROLES View 'Your profile'.
     *
     * @return bool
     */
    public function canViewDrivingLicence()
    {
        return ($this->authorisationService->isGranted(PermissionInSystem::VIEW_DRIVING_LICENCE)
            && $this->targetPersonHasNoneOrAnyRoleOf([
                OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                RoleCode::SITE_MANAGER,
                RoleCode::SITE_ADMIN,
                RoleCode::TESTER,
            ]) || ($this->isViewingOwnProfile() && $this->loggedInPersonHasNoneOrAnyRoleOf([
                   RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                   RoleCode::AUTHORISED_EXAMINER_DELEGATE,
                   RoleCode::SITE_MANAGER,
                   RoleCode::SITE_ADMIN,
                   RoleCode::TESTER,
       ])));
    }

    /**
     * Change Driving licence.
     *
     * Rule: When SM, SU, AO1, AO2, VE View AEDM, AED, SITE-M, SITE-A, TESTER, NO-ROLES.
     *
     * @return bool
     */
    public function canChangeDrivingLicence()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::ADD_EDIT_DRIVING_LICENCE)
            && $this->targetPersonHasNoneOrAnyRoleOf([
                OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                RoleCode::SITE_MANAGER,
                RoleCode::SITE_ADMIN,
                RoleCode::TESTER,
        ]);
    }

    /**
     * Change Email.
     *
     * Rule: When ANYONE View ’Your profile’ OR When SM, SU, A01, A02, VE, CSM, CSCO View ANYONE.
     *
     * @return bool
     */
    public function canChangeEmailAddress()
    {
        return $this->isViewingOwnProfile()
            || $this->authorisationService->isGranted(PermissionInSystem::PROFILE_EDIT_OTHERS_EMAIL_ADDRESS);
    }
    /**
     * Change Telephone.
     *
     * Rule: When ANYONE View 'Your profile' OR When SM, SU, AO1, AO2, VE, CSM, CSCO Views ANYONE
     *
     * @return bool
     */
    public function canChangeTelephoneNumber()
    {
        return $this->isViewingOwnProfile()
        || $this->authorisationService->isGranted(PermissionInSystem::EDIT_TELEPHONE_NUMBER);
    }

    /**
     * @return bool
     */
    public function shouldDisplayGroupAStatus()
    {
        return null !== $this->testerAuthorisation->getGroupAStatus()->getCode()
            && AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED != $this->testerAuthorisation->getGroupAStatus()->getCode();
    }

    /**
     * @return bool
     */
    public function shouldDisplayGroupBStatus()
    {
        return null !== $this->testerAuthorisation->getGroupBStatus()->getCode()
            && AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED != $this->testerAuthorisation->getGroupBStatus()->getCode();
    }

    /**
     * Tester status box with both statuses should be shown if either status != "Initial Training Needed".
     *
     * @return bool
     */
    public function shouldDisplayTesterQualificationStatusBox()
    {
        return $this->shouldDisplayGroupAStatus() || $this->shouldDisplayGroupBStatus();
    }

    /**
     * @return bool
     */
    public function canResetAccount()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::USER_ACCOUNT_RECLAIM);
    }

    /**
     * @return bool
     */
    public function canSendUserIdByPost()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::USERNAME_RECOVERY);
    }

    /**
     * @return bool
     */
    public function canSendPasswordResetByPost()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::USER_PASSWORD_RESET);
    }

    /**
     * @return bool
     */
    public function canViewTradeRoles()
    {
        return (
            ($this->isViewingOwnProfile() && $this->loggedInPersonHasNoneOrAnyRoleOf([
                RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                RoleCode::AUTHORISED_EXAMINER_DELEGATE,
                RoleCode::SITE_MANAGER,
                RoleCode::SITE_ADMIN,
                RoleCode::TESTER,
            ]))
            || ($this->authorisationService->isGranted(PermissionInSystem::VIEW_TRADE_ROLES_OF_ANY_USER)
                && $this->targetPersonHasNoneOrAnyRoleOf([
                    OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                    OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                    RoleCode::SITE_MANAGER,
                    RoleCode::SITE_ADMIN,
                    RoleCode::TESTER,
            ]))
        );
    }

    /**
     * @return bool
     */
    public function canManageDvsaRoles()
    {
        return (
            !($this->authorisationService->isGranted(PermissionInSystem::MANAGE_DVSA_ROLES) && $this->isViewingHimself())
            && ($this->authorisationService->isGranted(PermissionInSystem::MANAGE_DVSA_ROLES)
                && ($this->targetPersonHasNoneOrAnyRoleOf([
                     RoleCode::SCHEME_MANAGER,
                     RoleCode::SCHEME_USER,
                     RoleCode::AREA_OFFICE_1,
                     RoleCode::AREA_OFFICE_2,
                     RoleCode::VEHICLE_EXAMINER,
                     RoleCode::CUSTOMER_SERVICE_MANAGER,
                     RoleCode::CUSTOMER_SERVICE_OPERATIVE,
                     RoleCode::DVLA_MANAGER,
                     RoleCode::DVLA_OPERATIVE,
                     RoleCode::FINANCE,
                     RoleCode::SCHEME_MANAGER,
            ])))
        );
    }

    /**
     * @return bool
     */
    public function canChangeTesterQualificationStatus()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS)
            && ($this->targetPersonHasNoneOrAnyRoleOf([
                OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                RoleCode::SITE_MANAGER,
                RoleCode::SITE_ADMIN,
                RoleCode::TESTER,
            ]) || (!in_array($this->testerAuthorisation->getGroupAStatus()->getCode(), [
                    AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, null,
                ])
                && !in_array($this->testerAuthorisation->getGroupBStatus()->getCode(), [
                    AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, null,
            ])));
    }

    /**
     * @return bool
     */
    public function canViewEventHistory()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::LIST_EVENT_HISTORY);
    }

    /**
     * @return bool
     */
    public function canViewQualificationDetails()
    {
        // we don't show the link when we look at a profile
        return !$this->targetPersonHasAnyRoleOf(self::$dvsaRoles);
    }

    public function canViewAnnualAssessmentCertificates()
    {
        return !$this->targetPersonHasAnyRoleOf(self::$dvsaRoles);
    }

    /**
     * @return bool
     */
    public function canViewAccountSecurity()
    {
        return $this->isViewingOwnProfile() && $this->context === ContextProvider::YOUR_PROFILE_CONTEXT;
    }

    /**
     * return bool.
     */
    public function canViewAccountManagement()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::MANAGE_USER_ACCOUNTS)
            && ($this->isViewingOwnProfile() && $this->context !== ContextProvider::YOUR_PROFILE_CONTEXT
                || !$this->isViewingOwnProfile());
    }

    /**
     * @return bool
     */
    public function canEditName()
    {
        return !$this->isViewingHimself()
        && $this->authorisationService->isGranted(PermissionInSystem::EDIT_PERSON_NAME);
    }

    /**
     * @return bool
     */
    public function canChangeAddress()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::EDIT_PERSON_ADDRESS)
            || ($this->isViewingOwnProfile());
    }

    /**
     * @return int
     */
    private function getLoggedInPersonId()
    {
        if (null === $this->loggedInPersonId) {
            $this->loggedInPersonId = $this->identityProvider->getIdentity()->getUserId();
        }

        return $this->loggedInPersonId;
    }

    /**
     * @return array
     */
    private function getTargetPersonRoles()
    {
        if (null === $this->targetPersonRoles) {
            $this->targetPersonRoles = $this->targetPersonDetails->getRoles();
        }

        return $this->targetPersonRoles;
    }

    /**
     * @param array $roles
     *
     * @return bool
     */
    private function targetPersonHasAnyRoleOf(array $roles)
    {
        return !empty(array_intersect($roles, $this->getTargetPersonRoles()));
    }

    /**
     * A NO-ROLES user can actually have the USER role and still be considered NO-ROLES hence we remove this special
     * role before performing our checks.
     *
     * @param array $roles
     *
     * @return bool
     */
    private function targetPersonHasNoneOrAnyRoleOf(array $roles)
    {
        $targetPersonRoles = $this->getTargetPersonRoles();
        foreach (array_keys($targetPersonRoles) as $k) {
            if (RoleCode::USER === $targetPersonRoles[$k]) {
                unset($targetPersonRoles[$k]);
                break;
            }
        }

        return empty($targetPersonRoles) || $this->targetPersonHasAnyRoleOf($roles);
    }

    /**
     * @return array
     */
    private function getLoggedInPersonRoles()
    {
        if (null === $this->loggedInPersonRoles) {
            $this->loggedInPersonRoles = $this->authorisationService->getRolesAsArray();
        }

        return $this->loggedInPersonRoles;
    }

    /**
     * @param array $roles
     *
     * @return bool
     */
    private function loggedInPersonHasAnyRoleOf(array $roles)
    {
        return !empty(array_intersect($roles, $this->getLoggedInPersonRoles()));
    }

    /**
     * A NO-ROLES user can actually have the USER role and still be considered NO-ROLES hence we remove this special
     * role before performing our checks.
     *
     * @param array $roles
     *
     * @return bool
     */
    private function loggedInPersonHasNoneOrAnyRoleOf(array $roles)
    {
        $loggedInUserRoles = $this->authorisationService->getRolesAsArray();
        foreach (array_keys($loggedInUserRoles) as $k) {
            if (RoleCode::USER === $loggedInUserRoles[$k]) {
                unset($loggedInUserRoles[$k]);
                break;
            }
        }

        return empty($loggedInUserRoles) || $this->loggedInPersonHasAnyRoleOf($roles);
    }

    /**
     * Rule:
     *
     * When SM, SU, AO1, AO2, VE View ANYONE
     * EXCEPT
     * When SM, SU, AO1, AO2, VE View THEMSELVES - ALL CONTEXTS
     *
     * @return bool
     */
    public function canChangeDateOfBirth()
    {
        return !$this->isViewingHimself()
        && $this->authorisationService->isGranted(PermissionInSystem::EDIT_PERSON_DATE_OF_BIRTH);
    }

    public function canCreateQualificationDetails($vehicleClassGroupCode)
    {
        $createMotTestingCertificateAssertion = new CreateMotTestingCertificateAssertion(
            $this->authorisationService,
            $this->identityProvider
        );

        return $createMotTestingCertificateAssertion->isGranted(
            $this->targetPersonDetails->getId(),
            $vehicleClassGroupCode,
            $this->targetPersonDetails->getRolesAndAssociations()['system']['roles'],
            $this->testerAuthorisation
        );
    }

    public function canUpdateQualificationDetails($vehicleClassGroupCode)
    {
        $createMotTestingCertificateAssertion = new UpdateMotTestingCertificateAssertion(
            $this->authorisationService,
            $this->identityProvider
        );

        return $createMotTestingCertificateAssertion->isGranted(
            $this->targetPersonDetails->getId(),
            $vehicleClassGroupCode,
            $this->targetPersonDetails->getRolesAndAssociations()['system']['roles'],
            $this->testerAuthorisation
        );
    }

    public function canRemoveQualificationDetails($vehicleClassGroupCode)
    {
        if ($this->getStatusForGroup($vehicleClassGroupCode) === AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED) {
            return false;
        }

        $assertion = new RemoveMotTestingCertificateAssertion(
            $this->authorisationService,
            $this->identityProvider
        );

        return $assertion->isGranted(
            $this->targetPersonDetails->getId(),
            $vehicleClassGroupCode,
            $this->testerAuthorisation
        );
    }

    public function canViewGuidance($vehicleClassAGroupCode, $vehicleClassBGroupCode)
    {
        return $this->isViewingOwnProfile() ?
            (($this->canCreateQualificationDetails($vehicleClassAGroupCode)) ||
                ($this->canCreateQualificationDetails($vehicleClassBGroupCode))) :
            false ;
    }

    private function getStatusForGroup($vehicleClassGroupCode)
    {
        $status = null;
        if ($vehicleClassGroupCode === VehicleClassGroupCode::BIKES && $this->testerAuthorisation->hasGroupAStatus()) {
            $status = $this->testerAuthorisation->getGroupAStatus()->getCode();
        } elseif ($vehicleClassGroupCode === VehicleClassGroupCode::CARS_ETC && $this->testerAuthorisation->hasGroupBStatus()) {
            $status = $this->testerAuthorisation->getGroupBStatus()->getCode();
        }

        return $status;
    }

    public function canViewTestLogs()
    {
        return $this->isViewingOwnProfile() && $this->authorisationService->isGranted(PermissionInSystem::TESTER_VIEW_TEST_LOGS);
    }

    public function canViewTestQuality()
    {
        $assertion = (new ViewTesterTestQualityAssertion($this->authorisationService, $this->identityProvider))
            ->isGranted($this->targetPersonDetails->getId(), $this->testerAuthorisation);

        return $assertion;
    }
}
