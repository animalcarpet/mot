<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaCommon\Enum\TransitionStatusCode;
use DvsaCommon\Model\ListOfRolesAndPermissions;
use DvsaCommon\Model\PersonAuthorization;

/**
 * Groups the DB queries used by roles and permissions.
 */
class SqlRbacRepository implements RbacRepository
{
    const ALL_ROLES_SQL
        = <<<'EOQ'
SELECT
    allroles.role_type,
    allroles.site_id,
    allroles.organisation_id,
    allroles.role_name,
    allroles.permission_name,
    allroles.site_org_id,
    allroles.transition_status_code,
    allroles.restricted
FROM
(
    SELECT 'ORGANISATION' role_type, null site_id, oobrm.organisation_id organisation_id,
		role.code as role_name, permission.code as permission_name, null site_org_id,
		ots.code as transition_status_code, permission.is_restricted as restricted
	FROM permission
	JOIN role_permission_map orpm ON (permission.id = orpm.permission_id)
	JOIN role ON (orpm.role_id = role.id)
	JOIN organisation_business_role oobr ON (oobr.role_id = role.id)
	JOIN organisation_business_role_map oobrm ON (oobr.id = oobrm.business_role_id)
	JOIN person person ON oobrm.person_id = person.id
	JOIN business_role_status obrs ON (oobrm.status_id = obrs.id)
	JOIN organisation o ON (oobrm.organisation_id = o.id)
	LEFT JOIN transition_status ots ON (o.transition_status_id = ots.id)
	WHERE obrs.code = 'AC'
	AND person.id = :personId
UNION ALL
SELECT 'SITE' role_type, site.id site_id, null organisation_id,
		role.code as role_name, permission.code as permission_name, site.organisation_id site_org_id,
		sts.code as transition_status_code, permission.is_restricted as restricted
	FROM permission
	JOIN role_permission_map rpm ON (permission.id = rpm.permission_id)
	JOIN role ON (rpm.role_id = role.id)
	JOIN site_business_role sbr ON (sbr.role_id = role.id)
	JOIN site_business_role_map sbrm ON (sbr.id = sbrm.site_business_role_id)
	JOIN site ON (site.id = sbrm.site_id)
	LEFT JOIN transition_status sts ON (site.transition_status_id = sts.id)
	JOIN person ON sbrm.person_id = person.id
	JOIN business_role_status brs ON (sbrm.status_id = brs.id)
	WHERE brs.code = 'AC'
	AND person.id = :personId
UNION ALL
SELECT 'VEHICLE-CLASS' role_type, null site_id, null organisation_id, role.code as role_name,
        permission.code as permission_name, null as site_org_id, vcts.code as transition_status_code,
        permission.is_restricted as restricted
	FROM permission
	JOIN role_permission_map rpm ON (permission.id = rpm.permission_id)
	JOIN role ON (rpm.role_id = role.id)
	JOIN auth_for_testing_mot_role_map aftmrm ON (aftmrm.role_id = role.id)
	JOIN auth_for_testing_mot aftm ON (aftm.vehicle_class_id = aftmrm.vehicle_class_id AND aftm.status_id = aftmrm.auth_status_id)
	JOIN person ON (aftm.person_id = person.id)
	LEFT JOIN transition_status vcts ON (person.transition_status_id = vcts.id)
	WHERE person.id = :personId
UNION ALL
SELECT 'SYSTEM' role_type, null site_id, null organisation_id, role.code as role_name,
        permission.code as permission_name, null site_ord_id, pts.code AS transition_status_code,
        permission.is_restricted as restricted
	FROM permission
	JOIN role_permission_map rpm ON (permission.id = rpm.permission_id)
	JOIN role ON (rpm.role_id = role.id)
	JOIN person_system_role psr ON (psr.role_id = role.id)
	JOIN person_system_role_map psrm ON (psrm.person_system_role_id = psr.id)
	JOIN person ON (person.id = psrm.person_id)
	LEFT JOIN transition_status pts ON (person.transition_status_id = pts.id)
	JOIN business_role_status brs ON (psrm.status_id = brs.id)
	WHERE
	brs.code = 'AC'
	AND person.id = :personId
) allroles
order by site_id, organisation_id, role_type;
EOQ;

    const SITE_ORGANISATION_MAP_SQL
        = <<<'EOQ'
SELECT obrm.person_id, obrm.organisation_id, site.id site_id
FROM
	organisation_business_role_map obrm
	INNER JOIN site ON obrm.organisation_id = site.organisation_id
WHERE obrm.person_id = :personId
UNION ALL
SELECT person_id, site.organisation_id, sbrm.site_id
FROM
	site_business_role_map sbrm
	INNER JOIN site ON sbrm.site_id = site.id
WHERE person_id = :personId
EOQ;

    const HAS_ROLE_SQL
        = <<<'EOQ'
SELECT count(person.id) matchCount
FROM
person
	LEFT JOIN site_business_role_map sbrm ON (person.id = sbrm.person_id)
	LEFT JOIN site_business_role sbr ON (sbrm.site_business_role_id = sbr.id)
	LEFT JOIN role site_role on (site_role.id = sbr.role_id)
	LEFT JOIN organisation_business_role_map obrm ON (person.id = obrm.person_id)
	LEFT JOIN organisation_business_role obr ON (obrm.business_role_id = obr.id)
	LEFT JOIN role organisation_role on (organisation_role.id = obr.role_id)
	LEFT JOIN person_system_role_map psrm ON (person.id = psrm.person_id)
	LEFT JOIN person_system_role psr ON (psrm.person_system_role_id = psr.id)
	LEFT JOIN role system_role ON (system_role.id = psr.role_id)
	LEFT JOIN auth_for_testing_mot auth ON (auth.person_id = person.id)
	LEFT JOIN auth_for_testing_mot_role_map amap ON (auth.vehicle_class_id = amap.vehicle_class_id AND auth.status_id = amap.auth_status_id)
	LEFT JOIN role auth_role ON (amap.role_id = auth_role.id)
WHERE
	person.id = :personId
	AND
(
	site_role.code = :roleName
	OR
	organisation_role.code = :roleName
	OR
	system_role.code = :roleName
	OR
	auth_role.code = :roleName
)
EOQ;

    /** @var EntityManager $entityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function personIdHasRole($personId, $roleName)
    {
        $rsm = (new ResultSetMapping())
            ->addScalarResult('matchCount', 'matchCount', 'integer');

        $result = $this->entityManager->createNativeQuery(
            self::HAS_ROLE_SQL,
            $rsm
        )->setParameters(
            [
                'personId' => $personId,
                'roleName' => $roleName,
            ]
        )->getSingleResult()['matchCount'];

        // @todo need to review if this is correct.
        return $result > 0;
    }

    public function authorizationDetails($personId)
    {
        $result = $this->entityManager->getConnection()->fetchAll(
            self::ALL_ROLES_SQL,
            ['personId' => $personId]
        );

        $mapSiteRoleQueryResult = $this->entityManager->getConnection()->fetchAll(
            self::SITE_ORGANISATION_MAP_SQL,
            ['personId' => $personId]
        );

        $siteOrganisationMap = [];

        foreach ($mapSiteRoleQueryResult as $row) {
            self::addValueIfNeededAtKey($row['site_id'], $siteOrganisationMap, $row['organisation_id']);
        }

        return $this->mapToPersonAuthorization($result, $siteOrganisationMap);
    }

    /**
     * Adds the $roleName to the $existingRoles array of arrays at index $siteOrOrgId, if necessary.
     *
     * @param $siteOrOrgId
     * @param $existingRoles
     * @param $roleName
     */
    private static function addValueIfNeededAtKey($siteOrOrgId, &$existingRoles, $roleName)
    {
        if (!array_key_exists($siteOrOrgId, $existingRoles)) {
            $existingRoles[$siteOrOrgId] = [];
        }
        if (!in_array($roleName, $existingRoles[$siteOrOrgId])) {
            $existingRoles[$siteOrOrgId][] = $roleName;
        }
    }

    /**
     * public for unit-test access.
     */
    public static function mapToPersonAuthorization($resultRows, $siteOrganisationMap)
    {
        /** @var array $normalRoles */
        $normalRoles = [];

        /** @var array $normalPermissions */
        $normalPermissions = [];

        /** @var array $organisationRoles */
        $organisationRoles = [];

        /** @var array $organisationPermissions */
        $organisationPermissions = [];

        /** @var array $siteRoles */
        $siteRoles = [];

        /** @var array $sitePermissions */
        $sitePermissions = [];

        /** @var array $permissions */
        $permissions = [];

        foreach ($resultRows as $resultRow) {
            if (
                !is_null($resultRow['transition_status_code']) &&
                $resultRow['transition_status_code'] !== TransitionStatusCode::FULL_FUNCTIONALITY &&
                $resultRow['restricted']
            ) {
                continue;
            }
            $roleName = $resultRow['role_name'];
            $permissionName = $resultRow['permission_name'];

            if (!in_array($permissionName, $permissions)) {
                $permissions[] = $permissionName;
            }

            $roleType = $resultRow['role_type'];
            switch ($roleType) {
                case 'SYSTEM':
                case 'VEHICLE-CLASS':
                    if (!in_array($roleName, $normalRoles)) {
                        $normalRoles[] = $roleName;
                    }
                    if (!in_array($permissionName, $normalPermissions)) {
                        $normalPermissions[] = $permissionName;
                    }
                    break;
                case 'SITE':
                    $siteId = $resultRow['site_id'];
                    self::addValueIfNeededAtKey($siteId, $siteRoles, $roleName);
                    self::addValueIfNeededAtKey($siteId, $sitePermissions, $permissionName);

                    break;
                case 'ORGANISATION':
                    $orgId = $resultRow['organisation_id'];
                    self::addValueIfNeededAtKey($orgId, $organisationRoles, $roleName);
                    self::addValueIfNeededAtKey($orgId, $organisationPermissions, $permissionName);
                    break;
                default:
                    throw new \LogicException('Unknown result type: '.$roleType);
            }
        }

        $arrayOfSiteListOfRolesAndPermissions = self::mapRoleAndPermissionsToSingleArray(
            $siteRoles,
            $sitePermissions
        );

        $arrayOfOrgListOfRolesAndPermissions = self::mapRoleAndPermissionsToSingleArray(
            $organisationRoles,
            $organisationPermissions
        );

        return new PersonAuthorization(
            new ListOfRolesAndPermissions($normalRoles, $normalPermissions),
            $arrayOfOrgListOfRolesAndPermissions,
            $arrayOfSiteListOfRolesAndPermissions,
            $siteOrganisationMap
        );
    }

    /**
     * Combines the associative arrays.
     *
     * @param $arrayOfRoles
     * @param $arrayOfPermissions
     *
     * @return array of ListOfRolesAndPermissions
     */
    private static function mapRoleAndPermissionsToSingleArray($arrayOfRoles, $arrayOfPermissions)
    {
        $arrayOfListOfRolesAndPermissions = [];

        foreach ($arrayOfRoles as $id => $roleArray) {
            $permissionsForSpecificId = [];
            // unlikely, but a site's roles might not have any associated permissions
            if (array_key_exists($id, $arrayOfPermissions)) {
                $permissionsForSpecificId = $arrayOfPermissions[$id];
            }
            $arrayOfListOfRolesAndPermissions[$id] = new ListOfRolesAndPermissions(
                $roleArray,
                $permissionsForSpecificId
            );
        }

        return $arrayOfListOfRolesAndPermissions;
    }
}
