<?php

namespace DashboardTest\Service;

use Application\Data\ApiPersonalDetails;
use Application\Service\CatalogService;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\RoleCode;
use Dashboard\Service\TradeRolesAssociationsService;
use DvsaCommonTest\TestUtils\XMock;

class TradeRolesAssociationsServiceTest extends \PHPUnit_Framework_TestCase
{
    const PERSON_ID = 105;

    /** @var CatalogService */
    private $catalogService;

    public function setUp()
    {
        $this->catalogService = $this->createCatalogService();
    }

    /**
     * @dataProvider dataProvider
     */
    public function test_returnsRolesAndAssociations_whenUserHasRoles(array $roles)
    {
        $personalDetailsData = array_merge($roles, $this->getPersonalDetails());

        $apiPersonalDetails = XMock::of(ApiPersonalDetails::class);
        $apiPersonalDetails
            ->expects($this->any())
            ->method('getPersonalDetailsData')
            ->willReturn($personalDetailsData);

        $tradeRolesAssociationsService = new TradeRolesAssociationsService(
            $apiPersonalDetails,
            $this->catalogService
        );

        $rolesAndAssociations = $tradeRolesAssociationsService->getRolesAndAssociations(self::PERSON_ID);

        $organisations = $personalDetailsData['roles']['organisations'];
        $sites = $personalDetailsData['roles']['sites'];

        $assertRoles = function ($expectedId, array $expectedRoles, array $rolesAndAssociations) {
            foreach ($expectedRoles as $role) {
                $found = false;
                foreach ($rolesAndAssociations as $raa) {
                    if ($raa['id'] === $expectedId && $role === $raa['role']) {
                        $found = true;
                    }
                }

                $this->assertTrue($found);
            }
        };

        $organisationRoles = 0;
        foreach ($organisations as $id => $organisation) {
            if (!array_key_exists('roles', $organisation)) {
                continue;
            }

            $assertRoles($id, $organisation['roles'], $rolesAndAssociations);

            $organisationRoles += count($organisation['roles']);
        }

        $siteRoles = 0;
        foreach ($sites as $id => $site) {
            if (!array_key_exists('roles', $site)) {
                continue;
            }

            $assertRoles($id, $site['roles'], $rolesAndAssociations);

            $siteRoles += count($site['roles']);
        }

        $roles = $organisationRoles + $siteRoles;

        $this->assertEquals($roles, count($rolesAndAssociations));
    }

    public function dataProvider()
    {
        return [
            [
                [
                    'roles' => [
                            'system' => [
                                'roles' => [
                                    RoleCode::USER,
                                ],
                            ],
                            'organisations' => [],
                            'sites' => [
                                1 => [
                                    'name' => 'Garage 1',
                                    'number' => 'V1',
                                    'address' => 'Elm Street',
                                    'roles' => [
                                        SiteBusinessRoleCode::TESTER,
                                    ],
                                ],
                            ],
                        ],
                ],
            ],
            [
                [
                    'roles' => [
                            'system' => [
                                'roles' => [
                                    RoleCode::USER,
                                ],
                            ],
                            'organisations' => [
                                9 => [
                                    'name' => 'Organisation 1',
                                    'number' => 'AE1',
                                    'address' => 'New street',
                                    'roles' => [
                                        OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                                        OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                                    ],
                                ],
                            ],
                            'sites' => [],
                        ],
                ],
            ],
            [
                [
                    'roles' => [
                            'system' => [
                                'roles' => [
                                    RoleCode::USER,
                                    RoleCode::VEHICLE_EXAMINER,
                                ],
                            ],
                            'organisations' => [
                                9 => [
                                    'name' => 'Organisation 1',
                                    'number' => 'AE1',
                                    'address' => 'New street',
                                    'roles' => [
                                        OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                                        OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                                    ],
                                ],
                            ],
                            'sites' => [
                                1 => [
                                    'name' => 'Garage 1',
                                    'number' => 'V1',
                                    'address' => 'Elm Street',
                                    'roles' => [
                                        SiteBusinessRoleCode::TESTER,
                                    ],
                                ],
                            ],
                        ],
                ],
            ],
            [
                [
                    'roles' => [
                            'system' => [
                                'roles' => [
                                    RoleCode::USER,
                                ],
                            ],
                            'organisations' => [],
                            'sites' => [],
                        ],
                ],
            ],
        ];
    }

    private function getPersonalDetails()
    {
        return [
            'id' => self::PERSON_ID,
            'username' => 'marty',
            'firstName' => 'Marty',
            'middleName' => '',
            'surname' => 'McFly',
            'dateOfBirth' => '1968-10-23',
            'title' => 'Mr',
            'gender' => 'male',
            'addressLine1' => '',
            'addressLine2' => '',
            'addressLine3' => '',
            'town' => 'Hill Valley',
            'postcode' => 'L1 1PQ',
            'email' => 'traderolesassociationstest@dvsa.test',
            'phone' => 'iphone 6',
            'drivingLicenceNumber' => '1234567831234',
            'drivingLicenceRegion' => 'GB',
            'positions' => [],

        ];
    }

    private function createCatalogService()
    {
        $catalogService = XMock::of(CatalogService::class);
        $catalogService
            ->expects($this->any())
            ->method('getBusinessRoles')
            ->willReturn($this->getBusinessRoles());

        return $catalogService;
    }

    private function getBusinessRoles()
    {
        return [
            [
                'role' => 'organisation',
                'id' => 1,
                'code' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                'name' => 'Authorised examiner designated manager',
            ],
            [
                'role' => 'organisation',
                'id' => 12,
                'code' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                'name' => 'Authorised examiner designated manager',
            ],
            [
                'role' => 'site',
                'id' => 1,
                'code' => SiteBusinessRoleCode::TESTER,
                'name' => 'tester',
            ],
        ];
    }
}
