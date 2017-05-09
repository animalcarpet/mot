<?php

namespace SiteApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Constants\Role;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Service\Mapper\SiteBusinessRoleMapper;
use SiteApi\Service\SiteBusinessRoleService;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;

/**
 * Testing that service returns array.
 */
class SiteRoleServiceTest extends AbstractServiceTestCase
{
    private $personId = 1;
    private $siteBusinessRoleService;

    /** @var $authService AuthorisationServiceInterface */
    private $authService;

    // Given
    public function setup()
    {
        $this->authService = XMock::of(AuthorisationServiceInterface::class);

        $roleMapper = new SiteBusinessRoleMapper();
        $repository = XMock::of(\Doctrine\ORM\EntityRepository::class);
        $repository->expects($this->any())->method('findAll')->willReturn([]);

        $siteBusinessRoleMapRepository = XMock::of(SiteBusinessRoleMapRepository::class);
        $siteBusinessRoleMapRepository->expects($this->any())
                                      ->method('getActiveOrPendingUserRolesInASite')
                                      ->willReturn([]);

        $this->siteBusinessRoleService = new SiteBusinessRoleService(
            $roleMapper,
            $repository,
            $siteBusinessRoleMapRepository,
            $this->authService
        );
    }

    public function testGetAllRolesForPersonWithoutAnyDvsaRole()
    {
        $this->authService->expects($this->any())->method('getRolesAsArray')->will($this->returnValue([]));
        // When
        $roleList = $this->siteBusinessRoleService->getListForPerson($this->personId);

        // Then
        $this->assertTrue(is_array($roleList));
    }

    public function testGetAllRolesForPersonWithADvsaRole()
    {
        $this->authService->expects($this->any())->method('getRolesAsArray')->will($this->returnValue([Role::CUSTOMER_SERVICE_CENTRE_OPERATIVE]));

        $roleList = $this->siteBusinessRoleService->getListForPerson($this->personId);
        $this->assertTrue(is_array($roleList));
        $this->assertEmpty($roleList);
    }
}
