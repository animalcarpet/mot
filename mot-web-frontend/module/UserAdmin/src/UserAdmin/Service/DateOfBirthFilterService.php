<?php

namespace UserAdmin\Service;

use Application\Data\ApiPersonalDetails;
use Dashboard\Model\PersonalDetails;
use DvsaCommon\Dto\Person\SearchPersonResultDto;
use DvsaCommon\Model\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Auth\PermissionInSystem;
use Core\Service\MotFrontendAuthorisationServiceInterface;

class DateOfBirthFilterService
{
    /** @var MotFrontendAuthorisationServiceInterface $authorisationService */
    private $authorisationService;

    /** @var ApiPersonalDetails $personalDetailsService */
    private $personalDetailsService;

    private static $tradeRolesForTarget = [
        OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
        OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
        RoleCode::SITE_MANAGER,
        RoleCode::SITE_ADMIN,
        RoleCode::TESTER,
    ];

    /**
     * DateOfBirthFilterService constructor.
     *
     * @param MotFrontendAuthorisationServiceInterface $authorisationService
     * @param ApiPersonalDetails                       $personalDetailsService
     */
    public function __construct(MotFrontendAuthorisationServiceInterface $authorisationService,
                                ApiPersonalDetails $personalDetailsService)
    {
        $this->authorisationService = $authorisationService;
        $this->personalDetailsService = $personalDetailsService;
    }

    /**
     * remove any date of birth not viewable by this logged in user.
     *
     * @param SearchPersonResultDto[]
     */
    public function filterPersonalDetails(&$users)
    {
        /* @var $users SearchPersonResultDto[] */
        foreach ($users as $key => $user) {
            /* @var $user SearchPersonResultDto */
            if (!$this->canViewDateOfBirth($user->getPersonId())) {
                $users[$key]->setDateOfBirth(null);
            }
        }
    }

    /**
     * Can view date of birth on user search.
     *
     * @param string $targetUserId
     *
     * @return bool
     */
    public function canViewDateOfBirth($targetUserId)
    {
        if (!$this->authorisationService->isGranted(PermissionInSystem::VIEW_DATE_OF_BIRTH)) {
            return false;
        }

        return $this->targetPersonHasATradeRoleOrNoRole($this->getRolesForPersonWithId($targetUserId));
    }

    /**
     * Get the roles associated with the person for the given user id.
     *
     * @param string $targetUserId
     *
     * @return array
     */
    private function getRolesForPersonWithId($targetUserId)
    {
        /** @var array $personalDetailsData */
        $personalDetailsData = $this->personalDetailsService->getPersonalDetailsData($targetUserId);

        /** @var PersonalDetails $personalDetails */
        $personalDetails = new PersonalDetails($personalDetailsData);

        /** @var array $roles */
        $roles = $personalDetails->getRoles();

        return $roles;
    }

    /**
     * A NO-ROLES user can actually have the USER role and still be considered NO-ROLES hence we remove this special
     * role before performing our checks.
     *
     * @param array $targetPersonRoles
     *
     * @return bool
     */
    private function targetPersonHasATradeRoleOrNoRole(array $targetPersonRoles)
    {
        foreach (array_keys($targetPersonRoles) as $key) {
            if (RoleCode::USER === $targetPersonRoles[$key]) {
                unset($targetPersonRoles[$key]);
                break;
            }
        }

        return empty($targetPersonRoles) || !empty(array_intersect(self::$tradeRolesForTarget, $targetPersonRoles));
    }
}
