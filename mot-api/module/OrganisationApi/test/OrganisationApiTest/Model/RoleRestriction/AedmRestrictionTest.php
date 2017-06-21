<?php

namespace OrganisationApiTest\Model\RoleRestriction;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Constants\Role;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use OrganisationApi\Model\OrganisationPersonnel;
use OrganisationApi\Model\RoleRestriction\AbstractOrganisationRoleRestriction;
use OrganisationApi\Model\RoleRestriction\AedmRestriction;
use OrganisationApi\Model\RoleRestrictionInterface;

class AedmRestrictionTest extends \PHPUnit_Framework_TestCase
{
    const PERSON_ID = 42;

    /**
     * @var AedmRestriction
     */
    private $roleRestriction;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authorisationService;

    protected function setUp()
    {
        $this->authorisationService = $this->getMockBuilder(AuthorisationServiceInterface::class)->disableOriginalConstructor()->getMock();
        $this->roleRestriction = new AedmRestriction($this->authorisationService);
    }
    public function testItIsARoleRestriction()
    {
        $this->assertInstanceOf(RoleRestrictionInterface::class, $this->roleRestriction);
    }

    public function testItReturnsAnErrorSchema()
    {
        $this->authorisationService->expects($this->any())
            ->method('getRolesAsArray')
            ->with(self::PERSON_ID)
            ->willReturn([]);

        $errors = $this->roleRestriction->verify($this->getPerson(), $this->getAnyPersonnel());

        $this->assertInstanceOf(ErrorSchema::class, $errors);
    }

    public function testItAddsAnErrorForDvsaRoles()
    {
        $this->authorisationService->expects($this->any())
            ->method('getRolesAsArray')
            ->with(self::PERSON_ID)
            ->willReturn([Role::DVSA_AREA_OFFICE_1]);

        $errors = $this->roleRestriction->verify($this->getPerson(), $this->getPersonnelWithNoAuthorisedExaminerDesignatedManagerRole());

        $this->assertContains(AbstractOrganisationRoleRestriction::DVSA_ROLE_OWNER_ERROR, $errors->getGlobal());
    }

    public function testItAddsNoErrorsIfNoAuthorisedExaminerDesignatedManagerExists()
    {
        $this->authorisationService->expects($this->any())
            ->method('getRolesAsArray')
            ->with(self::PERSON_ID)
            ->willReturn([Role::TESTER_ACTIVE]);

        $errors = $this->roleRestriction->verify(
            $this->getPerson(),
            $this->getPersonnelWithNoAuthorisedExaminerDesignatedManagerRole()
        );

        $this->assertFalse($errors->hasErrors(), sprintf('Expected no errors, but got: "%s"', implode(', ', $errors->getAll())));
    }

    public function testItAddsAnErrorsAuthorisedExaminerDesignatedManagerAlreadyExists()
    {
        $this->authorisationService->expects($this->any())
            ->method('getRolesAsArray')
            ->with(self::PERSON_ID)
            ->willReturn([Role::TESTER_ACTIVE]);

        $errors = $this->roleRestriction->verify(
            $this->getPerson(),
            $this->getPersonnelWithExistingAuthorisedExaminerDesignatedManagerRole()
        );

        $this->assertTrue($errors->hasErrors());
        $this->assertContains(AedmRestriction::SITE_ALREADY_HAS_AEDM, $errors->getGlobal());
    }

    public function testItAddsAnErrorIfNoAuthorisedExaminerIsAssignedToOrganisation()
    {
        $this->authorisationService->expects($this->any())
            ->method('getRolesAsArray')
            ->with(self::PERSON_ID)
            ->willReturn([Role::TESTER_ACTIVE]);

        $errors = $this->roleRestriction->verify($this->getPerson(), $this->getAnyPersonnel());

        $this->assertSame(1, $errors->count());
        $this->assertContains(AedmRestriction::NOT_AE_ERROR, $errors->getGlobal());
    }

    private function getPerson()
    {
        $person = new Person();
        $person->setId(self::PERSON_ID);

        return $person;
    }

    private function getAnyPersonnel()
    {
        return new OrganisationPersonnel(new Organisation());
    }

    private function getPersonnelWithNoAuthorisedExaminerDesignatedManagerRole()
    {
        $status = new BusinessRoleStatus();
        $status->setCode(BusinessRoleStatusCode::ACCEPTED);

        $businessRole = new OrganisationBusinessRole();
        $businessRole->setName(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_PRINCIPAL);

        $organisation = $this->getOrganisation($status, $businessRole);

        return new OrganisationPersonnel($organisation);
    }

    private function getPersonnelWithExistingAuthorisedExaminerDesignatedManagerRole()
    {
        $status = new BusinessRoleStatus();
        $status->setCode(BusinessRoleStatusCode::ACCEPTED);

        $businessRole = new OrganisationBusinessRole();
        $businessRole->setName(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);

        $organisation = $this->getOrganisation($status, $businessRole);

        return new OrganisationPersonnel($organisation);
    }

    private function getOrganisation($status, $businessRole)
    {
        $position = new OrganisationBusinessRoleMap();
        $position->setBusinessRoleStatus($status);
        $position->setOrganisationBusinessRole($businessRole);

        $organisation = new Organisation();
        $organisation->addPosition($position);
        $organisation->setAuthorisedExaminer(new AuthorisationForAuthorisedExaminer());

        return $organisation;
    }
}
