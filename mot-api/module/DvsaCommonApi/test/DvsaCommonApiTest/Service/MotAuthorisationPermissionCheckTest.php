<?php

namespace DvsaCommonApiTest\Service;

use DvsaAuthentication\Identity;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use Zend\Authentication\AuthenticationService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Model\ListOfRolesAndPermissions;
use DvsaCommon\Model\PersonAuthorization;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\RbacRepository;

class MotAuthorisationPermissionCheckTest extends \PHPUnit_Framework_TestCase
{
    /** @var AuthorisationServiceInterface */
    private $authorisationService;

    /** @var RbacRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var PersonAuthorization */
    private $authorisation;

    private $systemPermissions;

    /** @var  ListOfRolesAndPermissions[] */
    private $organisationRoles;

    /** @var  ListOfRolesAndPermissions[] */
    private $siteRoles;

    private $siteOrganisationMap;

    /**
     * Return a mock of the Zend AuthorisationService
     *
     * @param $identity
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     * @throws \Exception
     */
    private function getIdentityMock($identity)
    {
        $mock = XMock::of(\Zend\Authentication\AuthenticationService::class);

        $mock->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($identity));

        return $mock;
    }

    public function setUp()
    {
        $identity = new Identity(new Person());
        //$identityProvider = new MotIdentityProvider($identity);
        $identityProvider = $this->getIdentityMock($identity);

        $this->systemPermissions = [];
        $this->organisationRoles = [];
        $this->siteRoles = [];
        $this->siteOrganisationMap = [];

        $permissionsAndRoles = new ListOfRolesAndPermissions([], $this->systemPermissions);

        $this->authorisation = new PersonAuthorization(
            $permissionsAndRoles,
            $this->organisationRoles,
            $this->siteRoles,
            $this->siteOrganisationMap
        );

        $this->repository = XMock::of(RbacRepository::class);

        $this->authorisationService = new AuthorisationService($identityProvider, $this->repository);
    }

    public function testHavingRequiredSystemLevelPermissionsGrantsSystemLevelAccess()
    {
        $requiredPermissionA = PermissionInSystem::DATA_CATALOG_READ;
        $requiredPermissionB = PermissionInSystem::APPLICATION_COMMENT;

        // GIVEN I have required permissions
        $this->addPermission($requiredPermissionA);
        $this->addPermission($requiredPermissionB);

        $this->setUpPermissions();

        // WHEN I am asking for system level access
        $this->authorisationService->assertGranted($requiredPermissionA);

        // THEN I am granted access (no exception occurred)
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testNotHavingRequiredSystemLevelPermissionsDeniesSystemLevelAccess()
    {
        $wrongPermissionA = PermissionInSystem::MOT_CAN_ASSIGN_TESTER_PENDING_DEMO_ROLE;
        $wrongPermissionB = PermissionAtOrganisation::AE_EMPLOYEE_PROFILE_READ;
        $requiredPermission = PermissionInSystem::DATA_CATALOG_READ;

        // GIVEN I do not have a required permission
        $this->addPermission($wrongPermissionA);
        $this->addPermission($wrongPermissionB);

        $this->setUpPermissions();

        // WHEN I am asking for system level access
        $this->authorisationService->assertGranted($requiredPermission);

        // THEN I am not granted access (exception occurs)
    }

    public function testHavingRequiredOrganisationLevelPermissionsGrantsOrganisationLevelAccessInGivenOrganisation()
    {
        // GIVEN I have an organisation
        $organisationId = 1;

        // AND required permission in that organisation
        $requiredA = PermissionAtOrganisation::MANAGE_AE_PERSONNEL;
        $requiredB = PermissionAtOrganisation::VIEW_AE_PERSONNEL;

        $this->addOrganisationPermission($requiredA, $organisationId);
        $this->addOrganisationPermission($requiredB, $organisationId);

        $this->setUpPermissions();

        // WHEN I am asking for organisation level access at that organisation
        $this->authorisationService->assertGrantedAtOrganisation($requiredA, $organisationId);
        $this->authorisationService->assertGrantedAtOrganisation($requiredB, $organisationId);

        // THEN I am granted access (no exception occurred)
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testOrganisationLevelPermissionDeniesAccessInOtherOrganisations()
    {
        $requiredPermission = PermissionAtOrganisation::MANAGE_AE_PERSONNEL;

        // GIVEN I have two organisations
        $firstOrganisation = 1;
        $otherOrganisation = 123;

        // AND required permission in the first one
        $this->addOrganisationPermission($requiredPermission, $firstOrganisation);
        $this->setUpPermissions();

        // WHEN I am asking for access to the other organisation
        $this->authorisationService->assertGrantedAtOrganisation($requiredPermission, $otherOrganisation);

        // THEN I am not granted access (exception occurs)
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testNotHavingRequiredOrganisationLevelPermissionsDeniesAccess()
    {
        $requiredPermission = PermissionAtOrganisation::MANAGE_AE_PERSONNEL;
        $wrongPermissions = PermissionAtOrganisation::VIEW_AE_PERSONNEL;

        // GIVEN I have an organisation
        $organisation = 1;

        // AND I don't have required permission
        $this->addOrganisationPermission($wrongPermissions, $organisation);
        $this->setUpPermissions();

        // WHEN I am asking for access to the organisation
        $this->authorisationService->assertGrantedAtOrganisation($requiredPermission, $organisation);

        // THEN I am not granted access (exception occurs)
    }

    public function testHavingRequiredSystemLevelPermissionsGrantsOrganisationLevelAccessInAllOrganisations()
    {
        $requiredPermission = PermissionAtOrganisation::MANAGE_AE_PERSONNEL;
        // GIVEN I have an organisations
        $organisationA = 1;
        $organisationB = 10;

        // AND required permission in that organisation
        $this->addPermission($requiredPermission);
        $this->setUpPermissions();

        // WHEN I am asking for organisation level access at both organisations organisation
        $this->authorisationService->assertGrantedAtOrganisation($requiredPermission, $organisationA);
        $this->authorisationService->assertGrantedAtOrganisation($requiredPermission, $organisationB);

        // THEN I am granted access (no exception occurred)
    }

    public function testHavingRequiredSiteLevelPermissionsGrantsSiteLevelAccessInGivenSite()
    {
        $requiredA = PermissionAtSite::MOT_TEST_ABORT_AT_SITE;
        $requiredB = PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS;

        // GIVEN I have a site
        $site = 1;

        // AND required permission at that site
        $this->addSitePermission($requiredA, $site);
        $this->addSitePermission($requiredB, $site);

        $this->setUpPermissions();

        // WHEN I am asking for site level access at that site
        $this->authorisationService->assertGrantedAtSite($requiredA, $site);
        $this->authorisationService->assertGrantedAtSite($requiredB, $site);

        // THEN I am granted access (no exception occurred)
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testNotHavingRequiredSiteLevelPermissionsDeniesAccessInGivenSite()
    {
        $requiredPermission = PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS;
        $wrongPermission  = PermissionAtSite::MOT_TEST_ABORT_AT_SITE;

        // GIVEN I have a site
        $site = 1;

        // AND I Don't have the required permissions at that site
        $this->addSitePermission($wrongPermission, $site);

        $this->setUpPermissions();

        // WHEN I am asking for site level access at that site
        $this->authorisationService->assertGrantedAtSite($requiredPermission, $site);

        // THEN I am denied access (exception occurs)
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testHavingRequiredSiteLevelPermissionDeniesAccessInSameOrganisationSites()
    {
        $required = PermissionAtSite::MOT_TEST_ABORT_AT_SITE;

        // GIVEN I have an organisation
        $organisation = 1;

        // AND sites
        $mySite = 1;
        $otherSite = 20;

        // AND sites belong to that organisation
        $this->linkSiteToOrganisation($mySite, $organisation);
        $this->linkSiteToOrganisation($otherSite, $organisation);

        // AND I have a required permission at one site
        $this->addSitePermission($required, $mySite);

        $this->setUpPermissions();

        // WHEN I am asking for access to the other site
        $this->authorisationService->assertGrantedAtSite($required, $otherSite);

        // THEN I am denied access (exception occurs)
    }

    public function testHavingRequiredOrganisationLevelPermissionGrantsSiteLevelAccessInChildSite()
    {
        $required = PermissionAtSite::MOT_TEST_ABORT_AT_SITE;

        // GIVEN I have an organisation
        $organisation = 1;

        // AND sites
        $siteA = 1;
        $siteB = 20;

        // AND sites belong to that organisation
        $this->linkSiteToOrganisation($siteA, $organisation);
        $this->linkSiteToOrganisation($siteB, $organisation);

        // AND I have a required permission at that organisation
        $this->addOrganisationPermission($required, $organisation);
        $this->setUpPermissions();

        // WHEN I am asking for access to those sites
        $this->authorisationService->assertGrantedAtSite($required, $siteA);
        $this->authorisationService->assertGrantedAtSite($required, $siteB);

        // THEN I am granted access (no exception occurred)
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testHavingRequiredOrganisationLevelPermissionDeniesAccessInNonChildSites()
    {
        $required = PermissionAtSite::MOT_TEST_ABORT_AT_SITE;

        // GIVEN I have an organisation
        $organisation = 1;

        // AND a site that belongs to that organisation
        $childSite = 1;
        $this->linkSiteToOrganisation($childSite, $organisation);

        // AND a site that doesn't belong to that organisation
        $notChildSite = 20;

        // AND I have a required permission at that organisation
        $this->addOrganisationPermission($required, $organisation);

        $this->setUpPermissions();

        // WHEN I am asking for access to the not a child site
        $this->authorisationService->assertGrantedAtSite($required, $notChildSite);

        // THEN I am denied access (exception occurs)
    }

    public function testHavingRequiredSystemLevelPermissionGrantsAccessInAllSites()
    {
        $required = PermissionAtSite::MOT_TEST_ABORT_AT_SITE;

        // GIVEN There are sites
        $siteA = 1;
        $siteB = 20;

        // AND I have a required system-level permission
        $this->addPermission($required);

        $this->setUpPermissions();

        // WHEN I am asking for access to those sites
        $this->authorisationService->assertGrantedAtSite($required, $siteA);
        $this->authorisationService->assertGrantedAtSite($required, $siteB);

        // THEN I am granted access (no exception occurred)
    }

    private function addPermission($permission)
    {
        $this->systemPermissions[] = $permission;
    }

    private function addOrganisationPermission($permission, $organisationId)
    {
        if (!array_key_exists($organisationId, $this->organisationRoles)) {
            $this->organisationRoles[$organisationId] = ListOfRolesAndPermissions::emptyList();
        }

        $permissions = $this->organisationRoles[$organisationId]->asArray()['permissions'];
        $permissions[] = $permission;

        $this->organisationRoles[$organisationId] = new ListOfRolesAndPermissions([], $permissions);
    }

    private function addSitePermission($permission, $siteId)
    {
        if (!array_key_exists($siteId, $this->siteRoles)) {
            $this->siteRoles[$siteId] = ListOfRolesAndPermissions::emptyList();
        }

        $permissions = $this->siteRoles[$siteId]->asArray()['permissions'];
        $permissions[] = $permission;

        $this->siteRoles[$siteId] = new ListOfRolesAndPermissions([], $permissions);
    }

    private function linkSiteToOrganisation($siteId, $organisationId)
    {
        $this->siteOrganisationMap[$siteId] = [$organisationId];
    }

    private function setUpPermissions()
    {
        $permissionsAndRoles = new ListOfRolesAndPermissions([], $this->systemPermissions);

        $this->authorisation = new PersonAuthorization(
            $permissionsAndRoles,
            $this->organisationRoles,
            $this->siteRoles,
            $this->siteOrganisationMap
        );

        $this->repository->expects($this->any())
            ->method('authorizationDetails')
            ->will($this->returnValue($this->authorisation));
    }
}
