<?php

namespace SiteApiTest\Model\Operation;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use SiteApi\Factory\SitePersonnelFactory;
use SiteApi\Model\NominationVerifier;
use SiteApi\Model\Operation\NominateOperation;
use SiteApi\Model\RoleRestriction\SiteManagerRestriction;
use SiteApi\Model\RoleRestrictionsSet;
use SiteApi\Service\SiteNominationService;

/**
 * Class NominateSiteManagerTest.
 */
class NominateSiteManagerTest extends AbstractServiceTestCase
{
    /** @var SitePositionRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $positionRepository;
    /** @var NominateOperation */
    private $nominateOperation;
    private $person;
    private $siteManagerRole;

    /** @var AuthorisationServiceInterface */
    private $authorizationService;

    public function setUp()
    {
        $this->authorizationService = $this->getMockWithDisabledConstructor(AuthorisationServiceInterface::class);

        $this->siteManagerRole = (new SiteBusinessRole())->setCode(SiteBusinessRoleCode::SITE_MANAGER);
        $this->person = new Person();
        $roleRestrictionsSet = new RoleRestrictionsSet([new SiteManagerRestriction($this->authorizationService)]);
        $nominationVerifier = new NominationVerifier($roleRestrictionsSet, new SitePersonnelFactory());
        $this->positionRepository = $this->getMockWithDisabledConstructor(\Doctrine\ORM\EntityManager::class);
        $siteNominationService = $this->getMockWithDisabledConstructor(SiteNominationService::class);
        $this->nominateOperation
            = new NominateOperation($this->positionRepository, $nominationVerifier, $siteNominationService);
    }

    public function test_dummy()
    {
        /*
         * For some reason jenkins is not able to find SiteRoleName class.
         * Test locally pass normally
         * Test are commented out until the issue is resolved.
         */

        $this->assertEquals(1, 1);
    }

    public function test_adding_second_site_manager_should_succeed()
    {
        $this->authorizationService->expects($this->once())->method('getRolesAsArray')->willReturn($this->returnValue([]));

        $vtsWithASiteManager = $this->buildVehicleTestingStationWithASiteManager();
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::ACTIVE);
        $siteManagerNomination = new SiteBusinessRoleMap();
        $siteManagerNomination->setSite($vtsWithASiteManager);
        $siteManagerNomination->setPerson($this->person);
        $siteManagerNomination->setSiteBusinessRole($this->siteManagerRole);
        $siteManagerNomination->setBusinessRoleStatus($status);

        $error = null;

        try {
            $this->nominateOperation->nominate(new Person(), $siteManagerNomination);
        } catch (BadRequestException $e) {
            $error = $e->getErrors()[0]['message'];
        }

        $this->assertNull($error);
    }

    public function test_adding_site_manager_to_authorised_examiner_works()
    {
        $this->positionRepository->expects($this->once())->method('persist');
        $this->authorizationService->expects($this->once())->method('getRolesAsArray')->willReturn($this->returnValue([]));

        $vts = $this->buildVehicleTestingStation();
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::ACTIVE);
        $position = new SiteBusinessRoleMap();
        $position->setSite($vts);
        $position->setPerson($this->person);
        $position->setSiteBusinessRole($this->siteManagerRole);
        $position->setBusinessRoleStatus($status);

        $error = 'No error';
        try {
            $this->nominateOperation->nominate(new Person(), $position);
        } catch (BadRequestException $e) {
            $error = $e->getErrors()[0]['message'];
        }

        $this->assertEquals('No error', $error);
    }

    private function buildVehicleTestingStationWithASiteManager()
    {
        $site = $this->buildVehicleTestingStation();
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::ACTIVE);
        $position = new SiteBusinessRoleMap();
        $position->setPerson(new Person());
        $position->setSiteBusinessRole($this->siteManagerRole);
        $position->setSite($site);
        $position->setBusinessRoleStatus($status);

        $site->getPositions()->add($position);

        return $site;
    }

    private function buildVehicleTestingStation()
    {
        $site = new Site();

        return $site;
    }
}
