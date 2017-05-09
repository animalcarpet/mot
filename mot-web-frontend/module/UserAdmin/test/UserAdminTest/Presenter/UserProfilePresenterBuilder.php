<?php

namespace UserAdminTest\Presenter;

use Application\Service\CatalogService;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Presenter\UserProfilePresenter;
use UserAdmin\Service\PersonRoleManagementService;
use UserAdmin\ViewModel\UserProfile\TesterAuthorisationViewModel;

/**
 * Builder for UserProfilePresenter object in unit tests.
 */
class UserProfilePresenterBuilder
{
    /** @var PersonHelpDeskProfileDto */
    private $personHelpDeskProfileDto;
    /** @var \PHPUnit_Framework_MockObject_MockObject|CatalogService */
    private $catalogService;
    /** @var \PHPUnit_Framework_MockObject_MockObject|PersonRoleManagementService $personRoleServiceMock */
    private $personRoleManagerService;
    /** @var bool */
    private $isDvsaUser;
    /** @var int $personId */
    private $personId;

    /** @var \PHPUnit_Framework_TestCase $testCase */
    private $testCase;

    /**
     * Private constructor. Please use `self::of` instead.
     *
     * You must provide \PHPUnit_Framework_TestCase object (which is `$this` in test you write)
     *
     * @param \PHPUnit_Framework_TestCase $testCase
     *
     * @throws \Exception
     */
    private function __construct(\PHPUnit_Framework_TestCase $testCase)
    {
        $this->personHelpDeskProfileDto = self::buildBasicPersonHelpDeskProfileDto();
        $this->catalogService = XMock::of(CatalogService::class);
        $this->isDvsaUser = false;
        $this->personRoleManagerService = XMock::of(PersonRoleManagementService::class);
        $this->personId = 0;

        $this->testCase = $testCase;
    }

    public static function of(\PHPUnit_Framework_TestCase $testCase)
    {
        return new self($testCase);
    }

    /**
     * @return UserProfilePresenter
     */
    public function build()
    {
        $presenter = new UserProfilePresenter(
            $this->personHelpDeskProfileDto,
            XMock::of(TesterAuthorisationViewModel::class),
            $this->catalogService,
            $this->isDvsaUser,
            $this->personRoleManagerService
        );

        $presenter->setPersonId($this->personId);

        return $presenter;
    }

    private function setTradeRoles($roles)
    {
        $this->personHelpDeskProfileDto->setRoles($roles);

        return $this;
    }

    public function withTradeRoles()
    {
        $this->catalogService->expects($this->testCase->atLeastOnce())
            ->method('getBusinessRoles')
            ->willReturn(self::buildSiteAndOrganisationCatalog());

        return $this->setTradeRoles(
            [
                'organisations' => [
                    [
                        'roles' => ['AEDM'],
                    ],
                ],
                'sites' => [
                    [
                        'roles' => ['TESTER'],
                    ],
                ],
            ]
        );
    }

    public function noTradeRoles()
    {
        return $this->setTradeRoles(['organisations' => [], 'sites' => []]);
    }

    private function setDvsaRoles(array $roles)
    {
        $returnedRoles = [];

        foreach ($roles as $role) {
            $returnedRoles[]['name'] = $role;
        }

        $this->personRoleManagerService
            ->expects($this->testCase->any())
            ->method('getPersonAssignedInternalRoles')
            ->willReturn($returnedRoles);

        return $this;
    }

    public function withDvsaRoles()
    {
        return $this->setDvsaRoles(['role1', 'role2']);
    }

    public function noDvsaRoles()
    {
        return $this->setDvsaRoles([]);
    }

    public function isDvsaUser()
    {
        $this->isDvsaUser = true;

        return $this;
    }

    public function setPersonId($personId)
    {
        $this->personId = $personId;

        return $this;
    }

    /**
     * @param bool $isViewingHisOwnProfilePage false by default
     *
     * @return $this
     */
    public function userIsViewingHisOwnProfilePage($isViewingHisOwnProfilePage = false)
    {
        $this->personRoleManagerService
            ->expects($this->testCase->any())
            ->method('personToManageIsSelf')
            ->with($this->personId)
            ->willReturn(!$isViewingHisOwnProfilePage);

        return $this;
    }

    /**
     * @param bool $userHasPermission true by default
     *
     * @return $this
     */
    public function userHasManageDvsaRolesPermission($userHasPermission = true)
    {
        $this->personRoleManagerService
            ->expects($this->testCase->any())
            ->method('userHasPermissionToManagePersonDvsaRoles')
            ->willReturn($userHasPermission);

        return $this;
    }

    public function userHasPermissionToReadDvsaRoles($userHasPermission = true)
    {
        $this->personRoleManagerService
            ->expects($this->testCase->any())
            ->method('userHasPermissionToReadPersonDvsaRoles')
            ->willReturn($userHasPermission);

        return $this;
    }

    /**
     * @return PersonHelpDeskProfileDto
     */
    private static function buildBasicPersonHelpDeskProfileDto()
    {
        $address = (new AddressDto())
            ->setAddressLine1('Address line 1')
            ->setAddressLine2('Address line 2')
            ->setAddressLine3('Address line 3')
            ->setAddressLine4('Address line 4')
            ->setTown('Bristol')
            ->setPostcode('Postcode');

        return (new PersonHelpDeskProfileDto())
            ->setUserName('Username')
            ->setDrivingLicenceNumber('drivingLicenceNumber')
            ->setTitle('Mrs')
            ->setFirstName('Harriet')
            ->setLastName('Jones')
            ->setDateOfBirth('1992-05-29')
            ->setAddress($address)
            ->setEmail('userprofilepresentertest@'.EmailAddressValidator::TEST_DOMAIN)
            ->setTelephone('+768-45-4433630');
    }

    /**
     * @return array
     */
    private static function buildSiteAndOrganisationCatalog()
    {
        return [
            [
                'id' => 1,
                'code' => 'AEDM',
                'name' => 'Authorised Examiner Designated Manager',
            ],
            [
                'id' => 1,
                'code' => 'TESTER',
                'name' => 'Tester',
            ],
        ];
    }
}
