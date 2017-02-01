<?php

namespace Dashboard\Authorisation;

use Application\Data\ApiPersonalDetails;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Enum\RoleCode;

class ViewNewHomepageAssertion
{
    const ROLES_ALLOWED_TO_VIEW_NEW_HOMEPAGE = [
        RoleCode::USER
    ];

    /** @var MotAuthorisationServiceInterface $authorisationService */
    private $authorisationService;

    /** @var MotFrontendIdentityInterface $identity */
    private $identity;

    /** @var ApiPersonalDetails $personalDetailsService */
    private $personalDetailsService;

    /**
     * UserAuthorisationHelper constructor.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param MotFrontendIdentityInterface     $identity
     * @param ApiPersonalDetails               $personalDetailsService
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        MotFrontendIdentityInterface $identity,
        ApiPersonalDetails $personalDetailsService
    ) {
        $this->authorisationService = $authorisationService;
        $this->identity = $identity;
        $this->personalDetailsService = $personalDetailsService;
    }

    /**
     * @return bool
     */
    public function canViewNewHomepage()
    {
        $userRoles = $this->getUserRoles();
        if (empty($userRoles)) {
            return true;
        }
        foreach ($userRoles as $role) {
            if (!in_array($role, self::ROLES_ALLOWED_TO_VIEW_NEW_HOMEPAGE)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    private function getUserRoles()
    {
        return $this->getUserPersonalDetails()->getRoles();
    }

    /**
     * @return PersonalDetails
     */
    private function getUserPersonalDetails()
    {
        $userId = $this->getUserId();
        $personalDetailsData = $this->personalDetailsService->getPersonalDetailsData($userId);

        return new PersonalDetails($personalDetailsData);
    }

    /**
     * @return int
     */
    private function getUserId()
    {
        return $this->identity->getUserId();
    }
}
