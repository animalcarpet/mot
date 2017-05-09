<?php

namespace DvsaAuthorisationTest\Service;

use DvsaAuthorisation\Service\UserRoleService;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonSystemRole;
use DvsaEntities\Entity\PersonSystemRoleMap;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Repository\OrganisationBusinessRoleMapRepository;
use DvsaEntities\Repository\PersonSystemRoleMapRepository;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;

class UserRoleServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var OrganisationBusinessRoleMapRepository */
    private $organisationRoleMapRepository;
    /** @var SiteBusinessRoleMapRepository */
    private $siteRoleMapRepository;
    /** @var PersonSystemRoleMapRepository */
    private $personSystemRoleMapRepository;

    public function setUp()
    {
        $this->organisationRoleMapRepository = XMock::of(OrganisationBusinessRoleMapRepository::class);
        $this->siteRoleMapRepository = XMock::of(SiteBusinessRoleMapRepository::class);
        $this->personSystemRoleMapRepository = XMock::of(PersonSystemRoleMapRepository::class);
    }

    public function testGetNoRolesWillReturnEmptyArrays()
    {
        $this->personSystemRoleMapRepositoryWithResults([]);
        $this->organisationRoleMapRepositoryWithResults([]);
        $this->siteRoleMapRepositoryWithResults([]);

        $service = $this->getService();
        $roles = $service->getDetailedRolesForPerson($this->getTestPerson());

        $this->assertEquals([], $roles['system']['roles']);
        $this->assertEquals([], $roles['organisations']);
        $this->assertEquals([], $roles['sites']);
    }

    public function testPersonHasPersonSystemRolesReturnArrayOfRoles()
    {
        $this->personSystemRoleMapRepositoryWithResults(
            [
                (new PersonSystemRoleMap())->setPersonSystemRole(
                    (new PersonSystemRole())->setId(1)
                        ->setName('TEST')
                ),
            ]
        );

        $this->organisationRoleMapRepositoryWithResults([]);

        $this->siteRoleMapRepositoryWithResults([]);

        $service = $this->getService();
        $roles = $service->getDetailedRolesForPerson($this->getTestPerson());
        $this->assertEquals(['roles' => ['TEST']], $roles['system']);
        $this->assertEquals([], $roles['organisations']);
        $this->assertEquals([], $roles['sites']);
    }

    public function testPersonHasOrganisationRolesReturnArrayOfRoles()
    {
        $this->personSystemRoleMapRepositoryWithResults([]);
        $this->organisationRoleMapRepositoryWithResults(
            [
                (new OrganisationBusinessRoleMap())->setOrganisationBusinessRole(
                    (new OrganisationBusinessRole())->setId(1)
                        ->setName('TEST')
                        ->setShortName('Test Short')
                )->setOrganisation(
                    (new Organisation())->setId(1)
                        ->setName('Test Ltd')
                        ->setAuthorisedExaminer(
                            (new AuthorisationForAuthorisedExaminer())
                                ->setId(1)
                                ->setNumber('AE123')
                        )
                        ->setContact(
                            (new ContactDetail())->setAddress(
                                (new Address())->setId(1)
                                    ->setAddressLine1('Stoke Gifford Office')
                                    ->setAddressLine2('Test Road')
                                    ->setAddressLine3('Bristol')
                                    ->setAddressLine4('Bristol 2')
                                    ->setPostcode('BS12')
                            ),
                            (new \DvsaEntities\Entity\OrganisationContactType())->setId(1)
                                ->setCode(OrganisationContactTypeCode::REGISTERED_COMPANY)
                        )
                ),
            ]
        );

        $this->siteRoleMapRepositoryWithResults([]);

        $service = $this->getService();
        $roles = $service->getDetailedRolesForPerson($this->getTestPerson());

        $this->assertEquals([], $roles['system']['roles']);

        $this->assertEquals(
            [
                'name' => 'Test Ltd',
                'number' => 'AE123',
                'address' => 'Stoke Gifford Office, Test Road, Bristol, Bristol 2, BS12',
                'roles' => [
                    'Test Short',
                ],
            ],
            current($roles['organisations'])
        );

        $this->assertEquals([], $roles['sites']);
    }

    public function testPersonHasSiteRolesReturnArrayOfRoles()
    {
        $this->personSystemRoleMapRepositoryWithResults([]);
        $this->organisationRoleMapRepositoryWithResults([]);

        $this->siteRoleMapRepositoryWithResults(
            [
                (new SiteBusinessRoleMap())->setSiteBusinessRole(
                    (new SiteBusinessRole())->setId(1)->setCode('ROLE')->setName('ROLE')
                )->setSite(
                    (new Site())->setId(1)
                                ->setName('SITE NAME')
                                ->setSiteNumber('SITE1234')
                                ->setContact(
                                    (new ContactDetail())->setAddress(
                                        (new Address())->setId(1)
                                            ->setAddressLine1('Stoke Gifford Office')
                                            ->setAddressLine2('Test Road')
                                            ->setAddressLine3('Bristol')
                                            ->setAddressLine4('Bristol 2')
                                            ->setPostcode('BS12')
                                    ),
                                    (new \DvsaEntities\Entity\SiteContactType())->setId(1)
                                        ->setCode(SiteContactTypeCode::BUSINESS)
                                )
                ),
            ]
        );

        $service = $this->getService();
        $roles = $service->getDetailedRolesForPerson($this->getTestPerson());

        $this->assertEquals([], $roles['system']['roles']);
        $this->assertEquals([], $roles['organisations']);

        $this->assertEquals(
            [
                'name' => 'SITE NAME',
                'number' => 'SITE1234',
                'address' => 'Stoke Gifford Office, Test Road, Bristol, Bristol 2, BS12',
                'addressParts' => [
                    'addressLine1' => 'Stoke Gifford Office',
                    'addressLine2' => 'Test Road',
                    'addressLine3' => 'Bristol',
                    'addressLine4' => 'Bristol 2',
                    'town' => '',
                    'postcode' => 'BS12',
                ],
                'roles' => [
                    'ROLE',
                ],
            ],
            current($roles['sites'])
        );
    }

    private function personSystemRoleMapRepositoryWithResults($result)
    {
        $this->personSystemRoleMapRepository->expects($this->any())
            ->method('getActiveUserRoles')
            ->willReturn($result);
    }

    private function organisationRoleMapRepositoryWithResults($result)
    {
        $this->organisationRoleMapRepository->expects($this->any())
            ->method('getActiveUserRoles')
            ->willReturn($result);
    }

    private function siteRoleMapRepositoryWithResults($result)
    {
        $this->siteRoleMapRepository->expects($this->any())
            ->method('getActiveUserRoles')
            ->willReturn($result);
    }

    private function personSystemPendingRoleMapRepositoryWithResults($result)
    {
        $this->personSystemRoleMapRepository->expects($this->any())
            ->method('getPendingUserRoles')
            ->willReturn($result);
    }

    private function organisationPendingRoleMapRepositoryWithResults($result)
    {
        $this->organisationRoleMapRepository->expects($this->any())
            ->method('getPendingUserRoles')
            ->willReturn($result);
    }

    private function sitePendingRoleMapRepositoryWithResults($result)
    {
        $this->siteRoleMapRepository->expects($this->any())
            ->method('getPendingUserRoles')
            ->willReturn($result);
    }

    private function getService()
    {
        $service = new UserRoleService(
            $this->organisationRoleMapRepository,
            $this->siteRoleMapRepository,
            $this->personSystemRoleMapRepository
        );

        return $service;
    }

    private function getTestPerson()
    {
        return (new Person())->setId(1)->setUsername('test');
    }
}
