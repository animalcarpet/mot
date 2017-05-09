<?php

namespace Dashboard\Authorisation;

use Core\Service\MotFrontendIdentityProviderInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Utility\TypeCheck;

class ViewTradeRolesAssertion
{
    private $authorisationService;

    private $identityProvider;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        MotFrontendIdentityProviderInterface $identityProvider
    ) {
        $this->authorisationService = $authorisationService;
        $this->identityProvider = $identityProvider;
    }

    /**
     * @param int   $profilePersonId
     * @param array $profileRoles
     *
     * @return bool
     */
    public function shouldViewLink($profilePersonId, $hasInternalRoles, $hasTradeRoles)
    {
        TypeCheck::isPositiveInteger($profilePersonId);

        if ($hasInternalRoles && !$hasTradeRoles) {
            return false;
        }

        return $this->isGratedViewProfileTradeRolesPage($profilePersonId);
    }

    public function isGratedViewProfileTradeRolesPage($profilePersonId)
    {
        return $this->isUserViewingHisOwnProfile($profilePersonId)
            || $this->hasUserPermissionToViewOtherProfiles();
    }

    private function isUserViewingHisOwnProfile($profilePersonId)
    {
        return $this->identityProvider->getIdentity()->getUserId() == $profilePersonId;
    }

    private function hasUserPermissionToViewOtherProfiles()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::VIEW_TRADE_ROLES_OF_ANY_USER);
    }

    public function assertGratedViewProfileTradeRolesPage($profilePersonId)
    {
        if (!$this->isGratedViewProfileTradeRolesPage($profilePersonId)) {
            throw new UnauthorisedException('Not authorised to view trade roles.');
        }
    }

    public function assertGrantedViewRemoveRolePage($profilePersonId)
    {
        if (!$this->isUserViewingHisOwnProfile($profilePersonId)) {
            throw new UnauthorisedException('Not authorised to view trade roles.');
        }
    }
}
