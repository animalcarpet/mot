<?php

namespace DvsaEntityTest\Repository;

use DvsaCommon\Model\ListOfRolesAndPermissions;
use DvsaEntities\Repository\SqlRbacRepository;

/**
 * Tests the mapping part of RbacRepository.
 */
class SqlRbacRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testRbacAndRepoAndPersonAuthorization()
    {
        /** PersonAuthorization $personAuthorization */
        $personAuthorization = SqlRbacRepository::mapToPersonAuthorization(
            [
                [
                    'role_type' => 'SYSTEM',
                    'site_id' => null,
                    'organisation_id' => null,
                    'permission_name' => 'NORMAL-ROLE-1-PERMISSION-1',
                    'role_name' => 'NORMAL-ROLE-1',
                    'site_org_id' => null,
                    'transition_status_code' => 'LIVE',
                    'restricted' => 0,
                ],
                [
                    'role_type' => 'SYSTEM',
                    'site_id' => null,
                    'organisation_id' => null,
                    'permission_name' => 'NORMAL-ROLE-1-PERMISSION-2',
                    'role_name' => 'NORMAL-ROLE-1',
                    'site_org_id' => null,
                    'transition_status_code' => 'LIVE',
                    'restricted' => 0,
                ],
                [
                    'role_type' => 'VEHICLE-CLASS',
                    'site_id' => null,
                    'organisation_id' => null,
                    'permission_name' => 'VEHICLE-CLASS-ROLE-2-PERMISSION-1',
                    'role_name' => 'VEHICLE-CLASS-ROLE-2',
                    'site_org_id' => null,
                    'transition_status_code' => 'LIVE',
                    'restricted' => 0,
                ],
                [
                    'role_type' => 'SITE',
                    'site_id' => '10',
                    'organisation_id' => null,
                    'permission_name' => 'SITE-ROLE-A-PERMISSION-1',
                    'role_name' => 'SITE-ROLE-A',
                    'site_org_id' => '1',
                    'transition_status_code' => 'LIVE',
                    'restricted' => 0,
                ],
                [
                    'role_type' => 'SITE',
                    'site_id' => '20',
                    'organisation_id' => null,
                    'permission_name' => 'SITE-ROLE-A-PERMISSION-1',
                    'role_name' => 'SITE-ROLE-A',
                    'site_org_id' => '3',
                    'transition_status_code' => 'LIVE',
                    'restricted' => 0,
                ],
                [
                    'role_type' => 'SITE',
                    'site_id' => '20',
                    'organisation_id' => null,
                    'permission_name' => 'SITE-ROLE-B-PERMISSION-1',
                    'role_name' => 'SITE-ROLE-B',
                    'site_org_id' => '3',
                    'transition_status_code' => 'LIVE',
                    'restricted' => 0,
                ],
                [
                    'role_type' => 'SITE',
                    'site_id' => '30',
                    'organisation_id' => null,
                    'permission_name' => 'SITE-ROLE-B-PERMISSION-1',
                    'role_name' => 'SITE-ROLE-B',
                    'site_org_id' => '2',
                    'transition_status_code' => 'LIVE',
                    'restricted' => 0,
                ],
                [
                    'role_type' => 'ORGANISATION',
                    'site_id' => null,
                    'organisation_id' => '3',
                    'permission_name' => 'ORGANISATION-ROLE-1-PERMISSION-1',
                    'role_name' => 'ORGANISATION-ROLE-1',
                    'site_org_id' => null,
                    'transition_status_code' => 'LIVE',
                    'restricted' => 0,
                ],
            ],
            [
                '10' => '1',
                '20' => '3',
                '30' => '2',
                '100' => '20',
            ]
        );

        $authAsArray = $personAuthorization->asArray();

        // Try to retrieve a role for a non-existent organisation
        $this->assertEquals(
            ListOfRolesAndPermissions::emptyList(),
            $personAuthorization->getRolesForOrganisation('-1')
        );

        $this->assertEquals(
            [
                'normal' => [
                        'roles' => [
                            'NORMAL-ROLE-1',
                            'VEHICLE-CLASS-ROLE-2',
                        ],
                        'permissions' => [
                            'NORMAL-ROLE-1-PERMISSION-1',
                            'NORMAL-ROLE-1-PERMISSION-2',
                            'VEHICLE-CLASS-ROLE-2-PERMISSION-1',
                        ],
                    ],
                'sites' => [
                    '10' => [
                        'roles' => ['SITE-ROLE-A'],
                        'permissions' => ['SITE-ROLE-A-PERMISSION-1'],
                    ],
                    '20' => [
                        'roles' => ['SITE-ROLE-A', 'SITE-ROLE-B'],
                        'permissions' => ['SITE-ROLE-A-PERMISSION-1', 'SITE-ROLE-B-PERMISSION-1'],
                    ],
                    '30' => [
                        'roles' => ['SITE-ROLE-B'],
                        'permissions' => ['SITE-ROLE-B-PERMISSION-1'],
                    ],
                ],
                'organisations' => [
                    '3' => [
                        'roles' => ['ORGANISATION-ROLE-1'],
                        'permissions' => ['ORGANISATION-ROLE-1-PERMISSION-1'],
                    ],
                ],
                'siteOrganisationMap' => [
                    '10' => '1',
                    '20' => '3',
                    '30' => '2',
                    '100' => '20',
                ],
            ],
            $authAsArray
        );
    }
}
