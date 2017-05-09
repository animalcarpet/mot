<?php

namespace OrganisationApiTest\Model;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Constants\Role;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use OrganisationApi\Model\RoleAvailability;
use OrganisationApi\Model\RoleRestriction\AedmRestriction;
use OrganisationApi\Model\RoleRestriction\AedRestriction;
use OrganisationApi\Model\RoleRestriction\SchemeManagerRestriction;
use OrganisationApi\Model\RoleRestrictionsSet;

/**
 * unit tests for RoleAvailability.
 */
class RoleAvailabilityTest extends \PHPUnit_Framework_TestCase
{
    /** @var $authService AuthorisationServiceInterface */
    private $authService;
    /** @var $roleAvailability RoleAvailability */
    private $roleAvailability;

    public function setUp()
    {
        /* @var $authService AuthorisationServiceInterface */
        $this->authService = XMock::of(AuthorisationServiceInterface::class);

        $roleRestrictionsSet = new RoleRestrictionsSet(
            [
                new AedRestriction($this->authService),
                new AedmRestriction($this->authService),
                new SchemeManagerRestriction($this->authService),
            ]
        );

        $obrRepository = XMock::of(\Doctrine\ORM\EntityRepository::class);
        $obrRepository->expects($this->any())->method('findAll')->will($this->returnValue([]));

        $this->roleAvailability = new RoleAvailability($roleRestrictionsSet, $this->authService, $obrRepository);
    }

    public function testListAvailableRolesForNominee()
    {
        $roles = $this->roleAvailability->listAvailableRolesForNominee(new Person(), new Organisation());

        $this->assertTrue(is_array($roles));
    }

    public function test_listRolesNominatorIsPermittedToAssignToPerson_onlyAedRole()
    {
        $this->authService->expects($this->any())->method('getRolesAsArray')->will($this->returnValue([]));

        $roles = $this->roleAvailability->listRolesNominatorIsPermittedToAssignToPerson(new Organisation(), 1);
        $this->assertCount(0, $roles);
    }

    public function test_listRolesNominatorIsPermittedToAssignToPerson_AedAndAedmRolesAvailable()
    {
        $this->authService->expects($this->once())->method('isGranted')->will($this->returnValue(true));
        $this->authService->expects($this->once())->method('isGrantedAtOrganisation')->will($this->returnValue(true));
        $this->authService->expects($this->any())->method('getRolesAsArray')->will($this->returnValue([]));

        $roles = $this->roleAvailability->listRolesNominatorIsPermittedToAssignToPerson(new Organisation(), 1);

        $this->assertCount(2, $roles);
    }

    public function test_listRolesNominatorIsPermittedToAssignToPerson_canNotNominateDvsaRoleOwnerForTradeRole()
    {
        $this->authService->expects($this->any())->method('isGranted')->will($this->returnValue(true));
        $this->authService->expects($this->any())->method('isGrantedAtOrganisation')->will($this->returnValue(true));
        $this->authService->expects($this->any())->method('getRolesAsArray')->will($this->returnValue([Role::CUSTOMER_SERVICE_CENTRE_OPERATIVE]));

        $roles = $this->roleAvailability->listRolesNominatorIsPermittedToAssignToPerson(new Organisation(), 1);
        $this->assertCount(0, $roles);
    }
}
