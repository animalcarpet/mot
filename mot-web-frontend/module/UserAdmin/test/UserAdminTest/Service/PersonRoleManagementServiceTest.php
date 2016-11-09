<?php

namespace UserAdminTest\Service;

use CoreTest\Service\StubCatalogService;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonTest\TestUtils\Auth\GrantAllAuthorisationServiceStub;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase as TestCase;
use UserAdmin\Service\PersonRoleManagementService;

class PersonRoleManagementServiceTest extends TestCase
{
    // Currently we only mock a single user and its expected internal roles and conversation around them
    const PID_AO1 = 147;

    /** @var PersonRoleManagementService */
    private $service;

    /** @var MotAuthorisationServiceInterface */
    private $authorisationMock;

    /** @var MotIdentityProviderInterface */
    private $identityMock;

    public function setUp()
    {
        $this->identityMock = XMock::of(MotIdentityProviderInterface::class);
        $this->authorisationMock = new GrantAllAuthorisationServiceStub();
        $mockRestClient = $this->stubHttpRestJsonClient();
        $mockCatalogService = new StubCatalogService();
        $userAdminMapper = new UserAdminMapper($mockRestClient);

        $this->service = new PersonRoleManagementService(
            $this->identityMock,
            $this->authorisationMock,
            $mockRestClient,
            $mockCatalogService,
            $userAdminMapper
        );
    }

    public function testAddRole()
    {
        $mockCatalogService = new StubCatalogService();

        $personId = 1;
        $role = 2;
        $url = PersonUrlBuilder::manageInternalRoles($personId);
        $data = ['personSystemRoleCode' => $mockCatalogService->getPersonSystemRoles()[$role]['code']];

        // Using this mock to assert it hits the post with the correctly converted role id to code
        $mockRestClient = $this->stubHttpRestJsonClient();
        $mockRestClient->expects($this->once())
            ->method('post')
            ->with($url, $data);

        $userAdminMapper = new UserAdminMapper($mockRestClient);

        (new PersonRoleManagementService(
            $this->identityMock,
            $this->authorisationMock,
            $mockRestClient,
            $mockCatalogService,
            $userAdminMapper
        ))->addRole($personId, $role);
    }

    public function testGetPersonManageableInternalRoles()
    {
        $this->assertEquals(
            $this->expectedDataForMockPersonId(PersonRoleManagementService::ROLES_MANAGEABLE),
            $this->service->getPersonManageableInternalRoles(self::PID_AO1)
        );
    }

    public function testGetPersonAssignedInternalRoles()
    {
        $this->assertEquals(
            $this->expectedDataForMockPersonId(PersonRoleManagementService::ROLES_ASSIGNED),
            $this->service->getPersonAssignedInternalRoles(self::PID_AO1)
        );
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testForbidManagementOfSelf()
    {
        $mockRestClient = $this->stubHttpRestJsonClient();
        $mockCatalogService = new StubCatalogService();

        $id = 123;
        $mockIdentity = XMock::of(MotIdentityInterface::class);
        $mockIdentity->expects($this->once())
            ->method('getUserId')
            ->willReturn($id);

        $mockIdentityProvider = XMock::of(MotIdentityProviderInterface::class);
        $mockIdentityProvider->expects($this->once())
            ->method('getIdentity')
            ->willReturn($mockIdentity);

        $userAdminMapper = new UserAdminMapper($mockRestClient);

        $obj = new PersonRoleManagementService(
            $mockIdentityProvider,
            $this->authorisationMock,
            $mockRestClient,
            $mockCatalogService,
            $userAdminMapper
        );
        $obj->forbidManagementOfSelf($id);
    }

    public function testPersonToManageIsSelf()
    {
        $mockRestClient = $this->stubHttpRestJsonClient();
        $mockCatalogService = new StubCatalogService();

        $id = 123;
        $mockIdentity = XMock::of(MotIdentityInterface::class);
        $mockIdentity->expects($this->exactly(2))
            ->method('getUserId')
            ->willReturn($id);

        $mockIdentityProvider = XMock::of(MotIdentityProviderInterface::class);
        $mockIdentityProvider->expects($this->exactly(2))
            ->method('getIdentity')
            ->willReturn($mockIdentity);

        $userAdminMapper = new UserAdminMapper($mockRestClient);

        $obj = new PersonRoleManagementService(
            $mockIdentityProvider,
            $this->authorisationMock,
            $mockRestClient,
            $mockCatalogService,
            $userAdminMapper
        );
        $this->assertTrue($obj->personToManageIsSelf($id));
        $this->assertFalse($obj->personToManageIsSelf(321));
    }

    public function testUserHasPermissionToReadPersonDvsaRolesShouldReturnTrue()
    {
        $this->assertUserHasPermission('userHasPermissionToReadPersonDvsaRoles');
    }

    public function testUserHasPermissionToResetPasswordShouldReturnTrue()
    {
        $this->assertUserHasPermission('userHasPermissionToResetPassword');
    }

    public function testUserHasPermissionToRecoveryUsernameShouldReturnTrue()
    {
        $this->assertUserHasPermission('userHasPermissionToRecoveryUsername');
    }

    public function testUserHasPermissionToReclaimUserAccountShouldReturnTrue()
    {
        $this->assertUserHasPermission('userHasPermissionToReclaimUserAccount');
    }

    private function assertUserHasPermission($methodName)
    {
        $service = new PersonRoleManagementService(
            XMock::of(MotIdentityProviderInterface::class),
            $this->authorisationMock,
            $mockRestClient = $this->stubHttpRestJsonClient(),
            new StubCatalogService(),
            $userAdminMapper = new UserAdminMapper($mockRestClient)
    );
        $this->assertTrue($service->$methodName());
    }

    private function stubHttpRestJsonClient()
    {
        $mockRestClient = XMock::of(HttpRestJsonClient::class);

        $mockRestClient->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($url) {
                        return $this->mockRestClientResponse($url);
                    }
                )
            );

        return $mockRestClient;
    }

    private function mockRestClientResponse($url)
    {
        $urlDataMap = [
            PersonUrlBuilder::manageInternalRoles(self::PID_AO1)->toString() => [
                'data' => [
                    PersonRoleManagementService::ROLES_ASSIGNED => [
                        'VEHICLE-EXAMINER',
                        'DVSA-AREA-OFFICE-2',
                    ],
                    PersonRoleManagementService::ROLES_MANAGEABLE => [
                        'DVSA-AREA-OFFICE-1',
                    ],
                ],
            ]
        ];

        return $urlDataMap[$url];
    }

    private function expectedDataForMockPersonId($url)
    {
        $map = [
            PersonRoleManagementService::ROLES_MANAGEABLE => [
                'DVSA-AREA-OFFICE-1' => [
                    'id' => 5,
                    'name' => 'DVSA Area Admin',
                    'url' => [
                        'route' => 'newProfileUserAdmin/manage-user-internal-role/add-internal-role',
                        'params' => [
                            'id' => 147,
                            'personSystemRoleId' => 5,
                        ],
                    ],
                ],
            ],
            PersonRoleManagementService::ROLES_ASSIGNED => [
                'VEHICLE-EXAMINER' => [
                    'id' => 2,
                    'name' => 'Vehicle Examiner',
                    'canManageThisRole' => true,
                    'url' => [
                        'route' => 'newProfileUserAdmin/manage-user-internal-role/remove-internal-role',
                        'params' => [
                            'id' => 147,
                            'personSystemRoleId' => 2,
                        ],
                    ],
                ],
                'DVSA-AREA-OFFICE-2' => [
                    'id' => 11,
                    'name' => 'DVSA Area Admin 2',
                    'canManageThisRole' => true,
                    'url' => [
                        'route' => 'newProfileUserAdmin/manage-user-internal-role/remove-internal-role',
                        'params' => [
                            'id' => 147,
                            'personSystemRoleId' => 11,
                        ],
                    ],
                ],
            ],
        ];

        return $map[$url];
    }
}
