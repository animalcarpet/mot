<?php

namespace DvsaCommon\Auth;

use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Model\PersonAuthorization;
use DvsaCommon\Model\TradeRole;
use DvsaCommon\Model\TwoFaTesterApplicantRole;
use DvsaCommon\Utility\ArrayUtils;

/**
 * An abstract common service for api and web implementations
 */
abstract class AbstractMotAuthorisationService
{
    public function isGranted($permissionName)
    {
        $this->validateIfPermissionIsSystemLevel($permissionName);

        return $this->hasIdentity() && $this->getPersonAuthorization()->isGranted($permissionName);
    }

    public function assertGranted($permissionName)
    {
        if (!$this->isGranted($permissionName)) {
            $this->throwUnauthorizedException($permissionName);
        }
    }

    public function isGrantedAtSite($permissionName, $siteId)
    {
        $this->validateIfPermissionIsSiteLevel($permissionName);

        return $this->hasIdentity()
        && $this->getPersonAuthorization()->isGrantedAtSite($permissionName, $siteId);
    }

    public function assertGrantedAtSite($permissionName, $siteId)
    {
        if (!$this->isGrantedAtSite($permissionName, $siteId)) {
            $this->throwUnauthorizedException($permissionName);
        }

        return true;
    }

    public function assertGrantedAtAnySite($permissionName)
    {
        if (!$this->isGrantedAtAnySite($permissionName)) {
            $this->throwUnauthorizedException($permissionName);
        }
    }

    public function isGrantedAtAnySite($permissionName)
    {
        $personAuthorization = $this->getPersonAuthorization()->asArray();
        $siteRoles = ArrayUtils::get($personAuthorization, "sites");
        $sites = ArrayUtils::getKeys($siteRoles);

        foreach ($sites as $siteId) {
            if ($this->isGrantedAtSite($permissionName, $siteId)) {
                return true;
            }
        }

        return false;
    }

    public function isGrantedAtOrganisation($permissionName, $orgId)
    {
        $this->validateIfPermissionIsOrganisationLevel($permissionName);

        return $this->hasIdentity()
        && $this->getPersonAuthorization()->isGrantedAtOrganisation($permissionName, $orgId);
    }

    public function assertGrantedAtOrganisation($permissionName, $orgId)
    {
        if (!$this->isGrantedAtOrganisation($permissionName, $orgId)) {
            $this->throwUnauthorizedException($permissionName);
        }
    }

    /**
     * Does the logged in user have the specified role?
     *
     * @param $roleName
     *
     * @return boolean
     * @deprecated check permissions, rather than roles
     */
    public function hasRole($roleName)
    {
        return $this->getPersonAuthorization()->getRoles()->includesRole($roleName);
    }

    /**
     * @return MotIdentityInterface
     */
    abstract protected function getIdentity();

    /**
     * @return PersonAuthorization
     */
    abstract protected function getPersonAuthorization();

    /**
     * @param $permissions
     *
     * @throws \DvsaCommon\Exception\UnauthorisedException
     */
    protected function throwUnauthorizedException($permissions)
    {
        $description = "Asserting permission [$permissions] failed.; permissions [" . print_r(
                $this->getPersonAuthorization()->asArray(),
                true
            ) . "]";
        throw new UnauthorisedException($description);
    }

    private function hasIdentity()
    {
        return $this->getIdentity() ? true : false;
    }

    private function validateIfPermissionIsSystemLevel($permissionName)
    {
        if (!in_array($permissionName, PermissionInSystem::all())) {
            throw new PermissionNotFoundException($permissionName, PermissionLevel::SYSTEM_LEVEL);
        };
    }

    private function validateIfPermissionIsOrganisationLevel($permissionName)
    {
        if (!in_array($permissionName, PermissionAtOrganisation::all())) {
            throw new PermissionNotFoundException($permissionName, PermissionLevel::ORGANISATION_LEVEL);
        };
    }

    private function validateIfPermissionIsSiteLevel($permissionName)
    {
        if (!in_array($permissionName, PermissionAtSite::all())) {
            throw new PermissionNotFoundException($permissionName, PermissionLevel::SITE_LEVEL);
        };
    }

    public function getRolesAsArray($personId = null)
    {
        $roles = $this->getPersonAuthorization($personId)->asArray();
        $results = [];
        foreach ($roles['sites'] as $site) {
            foreach ($site['roles'] as $id => $role) {
                $results[] = $role;
            }
        }
        foreach ($roles['organisations'] as $site) {
            foreach ($site['roles'] as $id => $role) {
                $results[] = $role;
            }
        }
        foreach ($roles['normal']['roles'] as $id => $role) {
            $results[] = $role;
        }

        return $results;
    }

    /**
     * Returns true if the current user is a trade user
     * @return bool
     */
    public function isTradeUser() {

        $roles = $this->getPersonAuthorization()->getAllRoles();
        return TradeRole::containsTradeRole($roles);
    }

    public function isNewTester() {
        $roles = $roles = $this->getPersonAuthorization()->getAllRoles();
        return TwoFaTesterApplicantRole::containsTwoFaTesterApplicantRole($roles);
    }
}
