<?php


namespace DashboardTest\Service;


use Core\Catalog\BusinessRole\BusinessRole;
use Core\Catalog\BusinessRole\BusinessRoleCatalog;
use Core\Catalog\EnumCatalog;
use Dashboard\Service\PersonTradeRoleSorterService;
use DvsaCommon\ApiClient\Person\PersonTradeRoles\Dto\PersonTradeRoleDto;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Model\OrganisationBusinessRoleCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Role;

class PersonTradeRoleSorterServiceTest extends \PHPUnit_Framework_TestCase
{
    protected static $siteRoles = [
        SiteBusinessRoleCode::SITE_MANAGER,
        SiteBusinessRoleCode::SITE_ADMIN,
        SiteBusinessRoleCode::TESTER,
    ];

    /**
     * @dataProvider getTradeRoles
     * @param $tradeRoles
     * @param $expectedRolesOrder
     */
    public function testRoleSorting($tradeRoles, $expectedRolesOrder)
    {
        $sorterService = $this->getServiceWithMocks();
        $sortedRoles = $sorterService->sortTradeRoles($tradeRoles);

        if (empty($tradeRoles)) {
            $this->assertEquals(true, is_array($sortedRoles));
            $this->assertEmpty($sortedRoles);
            return;
        }


        foreach ($sortedRoles as $aeName => $siteAndOrganisationRoles) {
            foreach ($siteAndOrganisationRoles as $roleType => $roles) {
                $i = 0;
                foreach ($roles as $role) {
                    /** @var PersonTradeRoleDto $role */
                    $this->assertEquals($role->getRoleCode(), $expectedRolesOrder[$aeName][$roleType][$i]);
                    $i++;
                }
            }
        }
    }

    public function getTradeRoles()
    {
        $AE4 = 'AE44444';
        $AE2 = 'AE22222';
        $SITE1 = 'AE33333';
        $SITE2 = 'AE1111';

        return [
            [
                // no roles
                'tradeRoles' => [],
                'expectedOrgRolesOrder' => [],
            ],
            [
                // only one role
                'tradeRoles' => [
                    $this->createPersonTradeRoleDTO([
                        'workplaceId' => $SITE1,
                        'roleCode' => SiteBusinessRoleCode::SITE_MANAGER,
                    ]),
                ],
                'expectedOrgRolesOrder' => [
                    $SITE1 => [
                        BusinessRole::SITE_TYPE => [
                            SiteBusinessRoleCode::SITE_MANAGER,
                        ],
                    ],
                ],
            ],
            [
                // roles in different AE
                'tradeRoles' => [
                    $this->createPersonTradeRoleDTO([
                        'aeId' => $AE2,
                        'roleCode' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                    ]),
                    $this->createPersonTradeRoleDTO([
                        'aeId' => $AE2,
                        'roleCode' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                    ]),
                    $this->createPersonTradeRoleDTO([
                        'workplaceId' => $SITE1,
                        'roleCode' => SiteBusinessRoleCode::TESTER,
                    ]),
                    $this->createPersonTradeRoleDTO([
                        'workplaceId' => $SITE1,
                        'roleCode' => SiteBusinessRoleCode::SITE_MANAGER,
                    ]),
                ],
                'expectedOrgRolesOrder' => [
                    $AE2 => [
                        BusinessRole::ORGANISATION_TYPE => [
                            OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                            OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                        ],
                    ],
                    $SITE1 => [
                        BusinessRole::SITE_TYPE => [
                            SiteBusinessRoleCode::SITE_MANAGER,
                            SiteBusinessRoleCode::TESTER,
                        ],
                    ],
                ],
            ],
            [
                // all of the possible roles
                'tradeRoles' => [
                    $this->createPersonTradeRoleDTO([
                        'aeId' => $AE4,
                        'roleCode' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                    ]),
                    $this->createPersonTradeRoleDTO([
                        'aeId' => $AE4,
                        'roleCode' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_PRINCIPAL,
                    ]),
                    $this->createPersonTradeRoleDTO([
                        'aeId' => $AE4,
                        'roleCode' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                    ]),
                    $this->createPersonTradeRoleDTO([
                        'workplaceId' => $SITE2,
                        'roleCode' => SiteBusinessRoleCode::SITE_ADMIN,
                    ]),
                    $this->createPersonTradeRoleDTO([
                        'workplaceId' => $SITE2,
                        'roleCode' => SiteBusinessRoleCode::TESTER,
                    ]),
                    $this->createPersonTradeRoleDTO([
                        'workplaceId' => $SITE2,
                        'roleCode' => SiteBusinessRoleCode::SITE_MANAGER,
                    ]),
                ],
                'expectedOrgRolesOrder' => [
                    $AE4 => [
                        BusinessRole::ORGANISATION_TYPE => [
                            OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                            OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
                            OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_PRINCIPAL,
                        ],
                    ],
                    $SITE2 => [
                        BusinessRole::SITE_TYPE => [
                            SiteBusinessRoleCode::SITE_MANAGER,
                            SiteBusinessRoleCode::SITE_ADMIN,
                            SiteBusinessRoleCode::TESTER,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getServiceWithMocks()
    {
        $enumCatalog = XMock::of(EnumCatalog::class);
        $businessCatalog = XMock::of(BusinessRoleCatalog::class);
        $businessCatalog->expects($this->any())->method('getByCode')->willReturnCallback(function ($role) {
            return new BusinessRole(
                $role,
                $role,
                in_array($role, static::$siteRoles) ? BusinessRole::SITE_TYPE : BusinessRole::ORGANISATION_TYPE
            );
        });
        $enumCatalog->expects($this->any())->method('businessRole')->willReturn($businessCatalog);

        return new PersonTradeRoleSorterService($enumCatalog);
    }

    protected function createPersonTradeRoleDTO($data)
    {
        $roleDto = (new PersonTradeRoleDto())->setRoleCode($data['roleCode']);
        if (isset($data['aeId'])) {
            $roleDto->setAeId($data['aeId']);
        }
        if (isset($data['workplaceId'])) {
            $roleDto->setWorkplaceId($data['workplaceId']);
        }
        return $roleDto;
    }
}