<?php

namespace NotificationApi\Service;

use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;

/**
 * Calculate which message to display.
 */
class PositionRemovalNotificationService
{
    const DVSA_OFFICE = 'your local DVSA office';
    const ORGANISATION = 'the AEDM of the Organisation';
    const VTS = 'the VTS';

    /** @var array */
    private $roles;

    /**
     * @param array $roles
     */
    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @param $siteId
     *
     * @return string
     */
    public function getSiteRoleRemovalContactText($siteId)
    {
        if ($this->isSiteManager($siteId)) {
            return self::VTS;
        }

        if ($this->hasRole(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, $this->roles['normal'])) {
            return self::ORGANISATION;
        }

        $orgId = $this->getOrganisationIdFromMap($siteId);

        if ($orgId) {
            $organisationRoles = $this->getOrganisationRoles($orgId);

            if ($this->hasRole(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, $organisationRoles)) {
                return self::ORGANISATION;
            }
        }

        return self::DVSA_OFFICE;
    }

    /**
     * @param $orgId
     *
     * @return string
     */
    public function getOrganisationRoleRemovalContactText($orgId)
    {
        $organisationRoles = $this->getOrganisationRoles($orgId);

        if ($organisationRoles && $this->hasRole(
                RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                $organisationRoles
            )
        ) {
            return self::ORGANISATION;
        }

        return self::DVSA_OFFICE;
    }

    /**
     * @param $siteId
     *
     * @return bool
     */
    private function isSiteManager($siteId)
    {
        if (!$this->rolesInSite($siteId)) {
            return false;
        }

        if (!isset($this->roles['sites'][$siteId])) {
            return false;
        }

        $siteRoles = $this->roles['sites'][$siteId]['roles'];

        return in_array(SiteBusinessRoleCode::SITE_MANAGER, $siteRoles);
    }

    /**
     * @param $siteId
     *
     * @return bool|mixed
     */
    private function getOrganisationIdFromMap($siteId)
    {
        $map = $this->roles['siteOrganisationMap'];

        if (!isset($map[$siteId])) {
            return false;
        }

        return current($map[$siteId]);
    }

    /**
     * @param $orgId
     *
     * @return bool
     */
    private function getOrganisationRoles($orgId)
    {
        if (!isset($this->roles['organisations'][$orgId])) {
            return false;
        }

        $organisationRoles = $this->roles['organisations'][$orgId]['roles'];

        return $organisationRoles;
    }

    /**
     * @param $roleName
     * @param $roles
     *
     * @return bool
     */
    private function hasRole($roleName, $roles)
    {
        return in_array($roleName, $roles);
    }

    /**
     * @param $siteId
     *
     * @return bool
     */
    private function rolesInSite($siteId)
    {
        return isset($this->roles['sites'][$siteId]);
    }
}
