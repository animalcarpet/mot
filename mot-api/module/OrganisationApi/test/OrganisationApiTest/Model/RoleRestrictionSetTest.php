<?php

namespace OrganisationApiTest\Model;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Constants\Role;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\Person;
use OrganisationApi\Model\OrganisationPersonnel;
use OrganisationApi\Model\RoleRestriction\AbstractOrganisationRoleRestriction;
use OrganisationApi\Model\RoleRestriction\AedmRestriction;
use OrganisationApi\Model\RoleRestriction\AedRestriction;
use OrganisationApi\Model\RoleRestrictionsSet;

/**
 * unit tests for RoleRestrictionSet.
 */
class RoleRestrictionSetTest extends \PHPUnit_Framework_TestCase
{
    /** @var $authService AuthorisationServiceInterface */
    private $authService;
    public $aedRole;
    public $aedmRole;

    public function setUp()
    {
        $this->authService = XMock::of(AuthorisationServiceInterface::class);

        $this->aedRole = new OrganisationBusinessRole();
        $this->aedRole->setName(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE);

        $this->aedmRole = new OrganisationBusinessRole();
        $this->aedmRole->setName(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);
    }
    /**
     * @expectedException \RuntimeException
     */
    public function test_getRestrictionForRole_emptySet_shouldThrowException()
    {
        $set = new RoleRestrictionsSet([]);
        $set->getRestrictionForRole($this->aedRole);
    }

    public function test_getRestrictionForRole_onlyAed()
    {
        $aed = new AedRestriction($this->authService);

        $setWithAedOnly = new RoleRestrictionsSet([$aed]);
        $this->assertSame($aed, $setWithAedOnly->getRestrictionForRole($this->aedRole));
    }

    public function test_getRestrictionForRole_onlyAedm()
    {
        $aedm = new AedmRestriction($this->authService);

        $setWithAedmOnly = new RoleRestrictionsSet([$aedm]);
        $this->assertSame($aedm, $setWithAedmOnly->getRestrictionForRole($this->aedmRole));
    }

    public function test_getRestrictionForRole_bothRoles()
    {
        $aed = new AedRestriction($this->authService);
        $aedm = new AedmRestriction($this->authService);

        $setWithBothRole = new RoleRestrictionsSet([$aed, $aedm]);
        $this->assertSame($aedm, $setWithBothRole->getRestrictionForRole($this->aedmRole));
        $this->assertSame($aed, $setWithBothRole->getRestrictionForRole($this->aedRole));
    }

    public function test_DvsaRoleOwnerCantBeNomineeForTradeRole()
    {
        $this->authService->expects($this->any())
            ->method('getRolesAsArray')
            ->willReturn(
                [
                    Role::TESTER_ACTIVE,
                    Role::DVSA_AREA_OFFICE_1,
                ]
            );

        $ae = XMock::of(Organisation::class);
        $ae->expects($this->any())
            ->method('isAuthorisedExaminer')
            ->willReturn('true');
        $ae->expects($this->any())
            ->method('getPositions')
            ->willReturn(new ArrayCollection());

        $aed = new AedRestriction($this->authService);
        $aedm = new AedmRestriction($this->authService);

        $this->assertContains(
            AbstractOrganisationRoleRestriction::DVSA_ROLE_OWNER_ERROR,
            $aed->verify(
                new Person(),
                new OrganisationPersonnel($ae)
            )->getGlobal()
        );

        $this->assertContains(
            AbstractOrganisationRoleRestriction::DVSA_ROLE_OWNER_ERROR,
            $aedm->verify(
                new Person(),
                new OrganisationPersonnel($ae)
            )->getGlobal()
        );
    }
}
