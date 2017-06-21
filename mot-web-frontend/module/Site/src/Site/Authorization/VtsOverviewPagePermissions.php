<?php

namespace Site\Authorization;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Security\RolesMapDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\AuthorisationForTestingMot;

/**
 * Class VtsOverviewPagePermissions.
 *
 * Wraps authorisation service to provide a clean way of verifying what a person can do/see on VTS overview page
 */
class VtsOverviewPagePermissions
{
    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;
    /** @var MotFrontendIdentityInterface */
    private $identity;

    private $vts;

    private $positions;

    private $authorisedExaminerId;

    /**
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param MotFrontendIdentityInterface     $identity
     * @param VehicleTestingStationDto         $vts
     * @param RolesMapDto[]                    $positions
     * @param int                              $authorisedExaminerId
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        MotFrontendIdentityInterface $identity,
        VehicleTestingStationDto $vts,
        $positions,
        $authorisedExaminerId
    ) {
        $this->authorisationService = $authorisationService;
        $this->identity = $identity;
        $this->vts = $vts;
        $this->positions = $positions;
        $this->authorisedExaminerId = $authorisedExaminerId;
    }

    private function isGranted($permission)
    {
        return $this->authorisationService->isGrantedAtSite($permission, $this->vts->getId());
    }

    public function canViewTestsInProgress()
    {
        return $this->isGranted(PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS);
    }

    public function canViewProfile(PersonDto $person)
    {
        return $this->authorisationService->isGrantedAtSite(
            PermissionAtSite::VTS_EMPLOYEE_PROFILE_READ,
            $this->vts->getId()
        )
        && $this->personIsEmployee($person);
    }

    public function canViewRecentCertificates()
    {
        return $this->authorisationService->isGrantedAtSite(
            PermissionAtSite::RECENT_CERTIFICATE_PRINT,
            $this->vts->getId()
        );
    }

    public function canViewUsername()
    {
        return $this->authorisationService->isGrantedAtSite(
            PermissionAtSite::VTS_USERNAME_VIEW,
            $this->vts->getId()
        );
    }

    private function personIsEmployee(PersonDto $person)
    {
        return ArrayUtils::anyMatch(
            $this->positions,
            function (RolesMapDto $position) use ($person) {
                return $position->getPerson()->getId() == $person->getId()
                && $position->getRoleStatus()->getCode() == BusinessRoleStatusCode::ACTIVE;
            }
        );
    }

    public function canViewAuthorisedExaminer()
    {
        return $this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::AUTHORISED_EXAMINER_READ,
            $this->authorisedExaminerId
        );
    }

    public function canTestClass1And2()
    {
        return VtsAuthorisationForTesting::canTestClass1Or2($this->vts->getTestClasses());
    }

    public function canTestAnyOfClass3AndAbove()
    {
        return VtsAuthorisationForTesting::canTestAnyOfClass3AndAbove($this->vts->getTestClasses());
    }

    public function canChangeDefaultBrakeTests()
    {
        return $this->authorisationService->isGrantedAtSite(
            PermissionAtSite::DEFAULT_BRAKE_TESTS_CHANGE,
            $this->vts->getId()
        );
    }

    public function canAbortMotTest()
    {
        return $this->authorisationService->isGrantedAtSite(
            PermissionAtSite::MOT_TEST_ABORT_AT_SITE, $this->vts->getId()
        );
    }

    public function canNominateRole()
    {
        return $this->authorisationService->isGrantedAtSite(
            PermissionAtSite::NOMINATE_ROLE_AT_SITE, $this->vts->getId()
        );
    }

    public function canRemoveRoleAtSite()
    {
        return $this->authorisationService->isGrantedAtSite(PermissionAtSite::REMOVE_ROLE_AT_SITE, $this->vts->getId());
    }

    public function canUpdateTestingSchedule()
    {
        return $this->authorisationService->isGrantedAtSite(
            PermissionAtSite::TESTING_SCHEDULE_UPDATE, $this->vts->getId()
        );
    }

    public function canViewEventHistory()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::EVENT_READ);
    }

    public function canRemovePositionAtSite($positionRoleCode)
    {
        // Are we trying to remove a site manager?
        if ($positionRoleCode == SiteBusinessRoleCode::SITE_MANAGER) {
            // Only an AE or AEDM with permission of REMOVE-SITE-MANAGER can do this.
            return $this->authorisationService->isGrantedAtSite(
                PermissionAtSite::REMOVE_SITE_MANAGER,
                $this->vts->getId()
            );
        }

        return $this->canRemoveRoleAtSite();
    }

    public function canChangeTestingFacilities()
    {
        return $this->isGranted(PermissionAtSite::VTS_UPDATE_TESTING_FACILITIES_DETAILS);
    }

    public function canChangeSiteName()
    {
        return $this->isGranted(PermissionAtSite::VTS_UPDATE_NAME);
    }

    public function canChangeSitePhone()
    {
        return $this->isGranted(PermissionAtSite::VTS_UPDATE_PHONE);
    }

    public function canChangeSiteAddress()
    {
        return $this->isGranted(PermissionAtSite::VTS_UPDATE_ADDRESS);
    }

    public function canChangeSiteCountry()
    {
        return $this->isGranted(PermissionAtSite::VTS_UPDATE_COUNTRY);
    }

    public function canChangeSiteEmail()
    {
        return $this->isGranted(PermissionAtSite::VTS_UPDATE_EMAIL);
    }

    public function canChangeSiteClasses()
    {
        return $this->isGranted(PermissionAtSite::VTS_UPDATE_CLASSES);
    }

    public function canChangeSiteType()
    {
        return $this->isGranted(PermissionAtSite::VTS_UPDATE_TYPE);
    }

    public function canChangeSiteStatus()
    {
        return $this->isGranted(PermissionAtSite::VTS_UPDATE_STATUS);
    }

    public function canSearchVts()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::VEHICLE_TESTING_STATION_SEARCH);
    }

    public function canChangeRiskAssessment()
    {
        return $this->authorisationService->isGrantedAtSite(
            PermissionAtSite::VTS_UPDATE_SITE_RISK_ASSESSMENT,
            $this->vts->getId()
        );
    }

    public function canViewRiskAssessment()
    {
        return $this->authorisationService->isGrantedAtSite(
            PermissionAtSite::VTS_VIEW_SITE_RISK_ASSESSMENT,
            $this->vts->getId()
        );
    }

    public function canViewCountry()
    {
        return $this->isGranted(PermissionAtSite::VTS_VIEW_CONTACT_DETAILS_COUNTRY);
    }

    public function canViewVtsType()
    {
        return $this->authorisationService->isGrantedAtSite(
            PermissionAtSite::VTS_DETAILS_VIEW_TYPE,
            $this->vts->getId()
        );
    }
}
