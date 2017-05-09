<?php

namespace DvsaCommonApiTest\Service;

use DvsaAuthentication\Identity;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionNotFoundException;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Model\ListOfRolesAndPermissions;
use DvsaCommon\Model\PersonAuthorization;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\RbacRepository;

class MotAuthorisationPermissionMismatchTest extends \PHPUnit_Framework_TestCase
{
    /** @var AuthorisationServiceInterface */
    private $authorisationService;

    /** @var RbacRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var PersonAuthorization */
    private $authorisation;

    private $systemPermissions;

    /** @var ListOfRolesAndPermissions[] */
    private $organisationRoles;

    /** @var ListOfRolesAndPermissions[] */
    private $siteRoles;

    private $siteOrganisationMap;

    /**
     * Return a mock of the Zend AuthorisationService.
     *
     * @param $identity
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
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

        $this->repository->expects($this->any())
            ->method('authorizationDetails')
            ->will($this->returnValue($this->authorisation));

        $this->authorisationService = new AuthorisationService($identityProvider, $this->repository);
    }

    /**
     * @expectedException \DvsaCommon\Auth\PermissionNotFoundException
     */
    public function testAskingForUnExistingSystemLevelPermissionThrowsException()
    {
        // WHEN I am asking for NOT EXISTING system level permission
        $this->authorisationService->assertGranted('NOT-EXISTING-PERMISSION');

        // THEN  (exception is thrown)
    }

    public function testAskingForExistingSystemLevelPermissionDoesNotThrowUnExistingPermissionException()
    {
        // WHEN I am asking for EXISTING system level permission
        $this->assertPermissionExists(
            function () {
                $this->authorisationService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
            }
        );

        // THEN PermissionNotFoundException is not exception is thrown
    }

    /**
     * @expectedException \DvsaCommon\Auth\PermissionNotFoundException
     */
    public function testAskingForUnExistingOrganisationLevelPermissionThrowsException()
    {
        // WHEN I am asking for NOT EXISTING organisation level permission
        $this->authorisationService->assertGrantedAtOrganisation('NOT-EXISTING-PERMISSION', 1);

        // THEN  (exception is thrown)
    }

    public function testAskingForExistingOrganisationLevelDoesNotThrowException()
    {
        // WHEN I am asking for EXISTING organisation level permission
        $this->assertPermissionExists(
            function () {
                $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::VIEW_AE_PERSONNEL, 1);
            }
        );

        // THEN PermissionNotFoundException is not exception is thrown
    }

    /**
     * @expectedException \DvsaCommon\Auth\PermissionNotFoundException
     */
    public function testAskingForUnExistingSiteLevelPermissionThrowsException()
    {
        // WHEN I am asking for NOT EXISTING site level permission
        $this->authorisationService->assertGrantedAtSite('NOT-EXISTING-PERMISSION', 1);

        // THEN  (exception is thrown)
    }

    public function testAskingForExistingSiteLevelDoesNotThrowException()
    {
        // WHEN I am asking for EXISTING site level permission
        $this->assertPermissionExists(
            function () {
                $this->authorisationService->assertGrantedAtSite(PermissionAtSite::MOT_TEST_ABORT_AT_SITE, 1);
            }
        );

        // THEN PermissionNotFoundException is not exception is thrown
    }

    private function assertPermissionExists($function)
    {
        try {
            $function();
        } catch (PermissionNotFoundException $e) {
            $this->fail('PermissionNotFoundException should not be thrown');
        } catch (UnauthorisedException $e) {
            //  it's okay for UnauthorizedException to happen
        }
    }
}
