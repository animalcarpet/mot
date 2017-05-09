<?php

namespace SiteApiTest\Model\Operation;

use DvsaCommon\Constants\Role;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\Auth\GrantAllAuthorisationServiceStub;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use SiteApi\Factory\SitePersonnelFactory;
use SiteApi\Model\NominationVerifier;
use SiteApi\Model\Operation\NominateOperation;
use SiteApi\Model\RoleRestriction\AbstractSiteRoleRestriction;
use SiteApi\Model\RoleRestriction\SiteManagerRestriction;
use SiteApi\Model\RoleRestrictionsSet;
use SiteApi\Service\SiteNominationService;

/**
 * Class NominateOperationTest.
 */
class NominateOperationTest extends AbstractServiceTestCase
{
    /**
     * @var SitePositionRepository
     */
    private $positionRepository;

    /**
     * @var NominateOperation
     */
    private $nominateOperation;

    /**
     * @var Person
     */
    private $larry;

    /**
     * @var SiteBusinessRole;
     */
    private $janitorRole;

    /** @var GrantAllAuthorisationServiceStub */
    private $authorizationService;

    public function setUp()
    {
        $this->authorizationService = new GrantAllAuthorisationServiceStub();

        $this->janitorRole = new SiteBusinessRole();
        $this->janitorRole->setCode(SiteBusinessRoleCode::SITE_MANAGER);
        $janitorRestriction = new SiteManagerRestriction($this->authorizationService);
        $this->larry = new Person();
        $roleRestrictionsSet = new RoleRestrictionsSet([$janitorRestriction]);
        $nominationVerifier = new NominationVerifier($roleRestrictionsSet, new SitePersonnelFactory());
        $this->positionRepository = $this->getMockWithDisabledConstructor(\Doctrine\ORM\EntityManager::class);
        $siteNominationServiceMock = $this->getMockWithDisabledConstructor(SiteNominationService::class);
        $this->nominateOperation = new NominateOperation(
            $this->positionRepository, $nominationVerifier, $siteNominationServiceMock
        );
    }

    public function test_adding_existing_nomination_should_fail()
    {
        $this->positionRepository->expects($this->never())->method('persist');

        $schoolWhereLarryWillWork = $this->buildSiteWherePersonIsNominated();
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::PENDING);
        $existingNomination = new SiteBusinessRoleMap();
        $existingNomination->setSite($schoolWhereLarryWillWork);
        $existingNomination->setPerson($this->larry);
        $existingNomination->setSiteBusinessRole($this->janitorRole);
        $existingNomination->setBusinessRoleStatus($status);
        $schoolWhereLarryWillWork->getPositions()->add($existingNomination);

        $error = 'No error';
        try {
            $this->nominateOperation->nominate(new Person(), $existingNomination);
        } catch (BadRequestException $e) {
            $error = $e->getErrors()[0]['message'];
        }

        $this->assertEquals(NominationVerifier::ERROR_ALREADY_HAS_NOMINATION, $error);
    }

    public function test_adding_nomination_when_position_exists_should_fail()
    {
        $this->positionRepository->expects($this->never())->method('persist');

        $schoolWhereLarryWorks = $this->buildSiteWherePersonAlreadyWorks();
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::ACTIVE);
        $existingPosition = new SiteBusinessRoleMap();
        $existingPosition->setSite($schoolWhereLarryWorks);
        $existingPosition->setPerson($this->larry);
        $existingPosition->setSiteBusinessRole($this->janitorRole);
        $existingPosition->setBusinessRoleStatus($status);

        $error = 'No error';
        try {
            $this->nominateOperation->nominate(new Person(), $existingPosition);
        } catch (BadRequestException $e) {
            $error = $e->getErrors()[0]['message'];
        }

        $this->assertEquals(NominationVerifier::ERROR_ALREADY_HAS_ROLE, $error);
    }

    public function test_adding_nomination_when_nominee_has_dvsa_role_fail()
    {
        $this->authorizationService->withRole(Role::CUSTOMER_SERVICE_CENTRE_OPERATIVE);

        $schoolWhereLarryDoesNotWork = $this->buildOrganisationWherePersonDoesNotWork();
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::ACTIVE);
        $position = new SiteBusinessRoleMap();
        $position->setSite($schoolWhereLarryDoesNotWork);
        $position->setPerson($this->larry);
        $position->setSiteBusinessRole($this->janitorRole);
        $position->setBusinessRoleStatus($status);

        $error = 'No error';
        try {
            $this->nominateOperation->nominate(new Person(), $position);
        } catch (BadRequestException $e) {
            $error = $e->getErrors()[0]['message'];
        }

        $this->assertEquals(AbstractSiteRoleRestriction::DVSA_ROLE_OWNER_ERROR, $error);

        // No exceptions
    }

    public function test_adding_nomination_works()
    {
        $this->positionRepository->expects($this->once())->method('persist');

        $schoolWhereLarryDoesNotWork = $this->buildOrganisationWherePersonDoesNotWork();
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::ACTIVE);
        $position = new SiteBusinessRoleMap();
        $position->setSite($schoolWhereLarryDoesNotWork);
        $position->setPerson($this->larry);
        $position->setSiteBusinessRole($this->janitorRole);
        $position->setBusinessRoleStatus($status);

        $this->nominateOperation->nominate(new Person(), $position);

        // No exceptions
    }

    private function buildSiteWherePersonIsNominated()
    {
        $site = new Site();
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::PENDING);
        $nomination = new SiteBusinessRoleMap();
        $nomination->setSite($site);
        $nomination->setPerson($this->larry);
        $nomination->setSiteBusinessRole($this->janitorRole);
        $nomination->setBusinessRoleStatus($status);

        $site->getPositions()->add($nomination);

        return $site;
    }

    private function buildSiteWherePersonAlreadyWorks()
    {
        $site = new Site();
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::ACTIVE);
        $nomination = new SiteBusinessRoleMap();
        $nomination->setPerson($this->larry);
        $nomination->setSiteBusinessRole($this->janitorRole);
        $nomination->setSite($site);
        $nomination->setBusinessRoleStatus($status);

        $site->getPositions()->add($nomination);

        return $site;
    }

    private function buildOrganisationWherePersonDoesNotWork()
    {
        $site = new Site();

        return $site;
    }
}
