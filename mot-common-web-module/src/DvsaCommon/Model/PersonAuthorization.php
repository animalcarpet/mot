<?php

namespace DvsaCommon\Model;

use DvsaCommon\Constants\Role;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Internal class for use in RBAC implementation only - should not be used by business code.
 */
class PersonAuthorization
{
    /**
     * @var ListOfRolesAndPermissions
     */
    private $normalRoles;

    /**
     * An associative array siteID => ListOfRolesAndPermissions.
     *
     * @var ListOfRolesAndPermissions[]
     */
    private $siteRoles;

    /**
     * An associative array organisationID => ListOfRolesAndPermissions.
     *
     * @var ListOfRolesAndPermissions[]
     */
    private $organisationRoles;

    /**
     * Associative array of site ID => organisation ID for recursive lookups of permissions.
     *
     * @var array
     */
    private $siteOrganisationMap;

    /**
     * PersonAuthorization constructor.
     *
     * @param ListOfRolesAndPermissions $normalRoles
     * @param array                     $organisationRoles
     * @param array                     $siteRoles
     * @param array                     $siteOrganisationMap
     */
    public function __construct(ListOfRolesAndPermissions $normalRoles, $organisationRoles = [], $siteRoles = [],
                                   $siteOrganisationMap = [])
    {
        $this->normalRoles = $normalRoles;
        $this->organisationRoles = $organisationRoles;
        $this->siteRoles = $siteRoles;
        $this->siteOrganisationMap = $siteOrganisationMap;
    }

    /**
     * @return PersonAuthorization
     */
    public static function emptyAuthorization()
    {
        return new PersonAuthorization(ListOfRolesAndPermissions::emptyList(), [], [], []);
    }

    /**
     * @param $siteId
     *
     * @return mixed|null
     */
    public function getOrganisationIdForSite($siteId)
    {
        $map = ArrayUtils::tryGet($this->siteOrganisationMap, $siteId);

        return $map ? reset($map) : null;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        $siteArray = [];

        /**
         * @var int
         * @var ListOfRolesAndPermissions $siteListOfRoleAndPermissions
         */
        foreach ($this->siteRoles as $siteId => $siteListOfRoleAndPermissions) {
            $siteArray += [$siteId => $siteListOfRoleAndPermissions->asArray()];
        }

        $orgArray = [];

        /**
         * @var int
         * @var ListOfRolesAndPermissions $orgListOfRoleAndPermissions
         */
        foreach ($this->organisationRoles as $orgId => $orgListOfRoleAndPermissions) {
            $orgArray += [$orgId => $orgListOfRoleAndPermissions->asArray()];
        }

        return [
            'normal' => $this->normalRoles->asArray(),
            'sites' => $siteArray,
            'organisations' => $orgArray,
            'siteOrganisationMap' => $this->siteOrganisationMap,
        ];
    }

    /**
     * Maps an array, originally created by #asArray, back to an object.
     *
     * @param array $rbacRoles
     *
     * @return PersonAuthorization
     */
    public static function fromArray($rbacRoles)
    {
        $normalArray = $rbacRoles['normal'];
        $normalRoles = new ListOfRolesAndPermissions(
            ArrayUtils::get($normalArray, 'roles'),
            ArrayUtils::get($normalArray, 'permissions')
        );
        $organisations = self::mapPlacesFromArray($rbacRoles['organisations']);
        $sites = self::mapPlacesFromArray($rbacRoles['sites']);

        return new PersonAuthorization($normalRoles, $organisations, $sites, $rbacRoles['siteOrganisationMap']);
    }

    /**
     * @return ListOfRolesAndPermissions[]
     */
    private static function mapPlacesFromArray($idMap)
    {
        /** @var ListOfRolesAndPermissions[] $places */
        $places = [];

        foreach ($idMap as $placeId => $placeListOfRolesAndPermissionsArray) {
            $places += [
                $placeId => new ListOfRolesAndPermissions(
                    ArrayUtils::get($placeListOfRolesAndPermissionsArray, 'roles'),
                    ArrayUtils::get($placeListOfRolesAndPermissionsArray, 'permissions')
                ),
            ];
        }

        return $places;
    }

    /**
     * Return an array of all the unique roles assigned to the person in the system level,
     *  as well as all the associated sites and organisation.
     *
     * @return array
     */
    public function getAllRoles()
    {
        $allRoles = $this->normalRoles->asArray()['roles'];

        foreach ($this->siteRoles as $siteRolesPermissions) {
            $roles = $siteRolesPermissions->asArray()['roles'];
            foreach ($roles as $role) {
                $allRoles[] = $role;
            }
        }

        foreach ($this->organisationRoles as $organisationRolesPermissions) {
            $roles = $organisationRolesPermissions->asArray()['roles'];
            foreach ($roles as $role) {
                $allRoles[] = $role;
            }
        }

        return array_unique($allRoles);
    }

    /**
     * @return ListOfRolesAndPermissions
     */
    public function getRoles()
    {
        return $this->normalRoles;
    }

    /**
     * @return ListOfRolesAndPermissions
     */
    public function getRolesForSite($siteId)
    {
        return ArrayUtils::tryGet($this->siteRoles, $siteId, ListOfRolesAndPermissions::emptyList());
    }

    /**
     * @return ListOfRolesAndPermissions
     */
    public function getRolesForOrganisation($organisationId)
    {
        return ArrayUtils::tryGet($this->organisationRoles, $organisationId, ListOfRolesAndPermissions::emptyList());
    }

    /**
     * @param $permissionName
     *
     * @return bool
     */
    public function isGranted($permissionName)
    {
        if ($this->getRoles()->includesPermission($permissionName)) {
            return true;
        }
        foreach ($this->organisationRoles as $organisationRole) {
            if ($organisationRole->includesPermission($permissionName)) {
                return true;
            }
        }
        foreach ($this->siteRoles as $siteRole) {
            if ($siteRole->includesPermission($permissionName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $permissionName
     *
     * @return bool
     */
    public function isGrantedAtSystem($permissionName)
    {
        return $this->getRoles()->includesPermission($permissionName);
    }

    /**
     * @param $permissionName
     * @param $siteId
     *
     * @return bool
     */
    public function isGrantedAtSite($permissionName, $siteId)
    {
        $isGrantedAtSite = $this->getRolesForSite($siteId)->includesPermission(
            $permissionName
        );

        $isGrantedAtOrganisation = $this->isGrantedForOrganisationAssociatedWithSite($permissionName, $siteId);

        $isGrantedGlobally = $this->isGrantedAtSystem($permissionName);

        return $isGrantedAtSite || $isGrantedAtOrganisation || $isGrantedGlobally;
    }

    /**
     * @param $permissionName
     * @param $orgId
     *
     * @return bool
     */
    public function isGrantedAtOrganisation($permissionName, $orgId)
    {
        $isGrantedAtOrganisation = $this->getRolesForOrganisation($orgId)->includesPermission(
            $permissionName
        );
        $isGrantedGlobally = $this->isGrantedAtSystem($permissionName);
        $isGrantedAtSiteForOrganisation = false;
        foreach ($this->siteOrganisationMap as $siteId => $organisationId) {
            if (isset($organisationId[0]) && $organisationId[0] == $orgId) {
                $isGrantedAtSiteForOrganisation = $this->isGrantedAtSite($permissionName, $siteId);
                if ($isGrantedAtSiteForOrganisation) {
                    break;
                }
            }
        }

        return $isGrantedAtOrganisation || $isGrantedGlobally || $isGrantedAtSiteForOrganisation;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return ($this->normalRoles->includesRole(Role::DVSA_AREA_OFFICE_1)
            || $this->normalRoles->includesRole(Role::DVSA_SCHEME_MANAGEMENT)
            || $this->normalRoles->includesRole(Role::DVLA_MANAGER)
            || $this->normalRoles->includesRole(Role::DVSA_SCHEME_USER)
        );
    }

    /**
     * @return bool
     */
    public function isVe()
    {
        return ($this->normalRoles->includesRole(Role::VEHICLE_EXAMINER));
    }

    /**
     * @return bool
     */
    public function isFinance()
    {
        return ($this->normalRoles->includesRole(Role::FINANCE));
    }

    /**
     * @return bool
     */
    public function isAedm()
    {
        foreach ($this->organisationRoles as $aListOfRolesAndPermissions) {
            if ($aListOfRolesAndPermissions->includesRole(
                    RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER
                )
                || $aListOfRolesAndPermissions->includesRole(RoleCode::AUTHORISED_EXAMINER_DELEGATE)
            ) {
                return true;
            }
        }
        foreach ($this->siteRoles as $aListOfRolesAndPermissions) {
            if ($aListOfRolesAndPermissions->includesRole(SiteBusinessRoleCode::SITE_MANAGER)
                || $aListOfRolesAndPermissions->includesRole(SiteBusinessRoleCode::SITE_ADMIN)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $permissionName
     * @param $siteId
     *
     * @return bool
     */
    private function isGrantedForOrganisationAssociatedWithSite($permissionName, $siteId)
    {
        $organisationId = $this->getOrganisationIdForSite($siteId);
        $isGrantedAtOrganisation = false;

        if (!is_null($organisationId)) {
            $isGrantedAtOrganisation = $this->getRolesForOrganisation($organisationId)
                ->includesPermission($permissionName);
        }

        return $isGrantedAtOrganisation;
    }
}
