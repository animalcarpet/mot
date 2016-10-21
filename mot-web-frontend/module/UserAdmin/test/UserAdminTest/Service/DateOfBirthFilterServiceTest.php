<?php

namespace UserAdminTest\Service;

use Application\Data\ApiPersonalDetails;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Model\OrganisationBusinessRoleCode;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\DateOfBirthFilterService;

class DateOfBirthFilterServiceTest extends \PHPUnit_Framework_TestCase
{
    const TEST_USER_ID = 9999;
    /** @var DateOfBirthFilterService $dateOfBirthFilterService */
    private $dateOfBirthFilterService;

    /** @var MotFrontendAuthorisationServiceInterface $authorisationService */
    private $authorisationService;

    /** @var ApiPersonalDetails $personalDetailsService*/
    private $personalDetailsService;

    public function setUp() {
        $this->authorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->personalDetailsService = XMock::of(ApiPersonalDetails::class);
        $this->dateOfBirthFilterService
            = new DateOfBirthFilterService($this->authorisationService, $this->personalDetailsService);
    }

    /**
     * @dataProvider testPermissionCheckDataProvider
     * @param array $hasViewDateOfBirthPermission
     * @param array $canViewDateOfBirth
     */
    public function testPermissionCheckOfLoggedInUser($hasViewDateOfBirthPermission, $canViewDateOfBirth) {
        $this->authorisationService
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn($hasViewDateOfBirthPermission);

        $this->personalDetailsService
            ->expects($hasViewDateOfBirthPermission ? $this->once() : $this->never())
            ->method('getPersonalDetailsData')
            ->with(self::TEST_USER_ID)
            ->willReturn($this->getMockPersonalDetailsData([RoleCode::USER]));

        $canViewResult = $this->dateOfBirthFilterService->canViewDateOfBirth(self::TEST_USER_ID);
        $this->assertEquals($canViewDateOfBirth, $canViewResult);
    }

    public function testPermissionCheckDataProvider() {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * @dataProvider testRoleCheckDataProvider
     * @param array $roles
     * @param array $canViewDateOfBirth
     */
    public function testWhenUserHasPermissionCorrectRolesCanViewDateOfBirth($roles, $canViewDateOfBirth) {
        $this->authorisationService
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);

        $this->personalDetailsService
            ->expects($this->once())
            ->method('getPersonalDetailsData')
            ->with(self::TEST_USER_ID)
            ->willReturn($this->getMockPersonalDetailsData($roles));

        $canViewResult = $this->dateOfBirthFilterService->canViewDateOfBirth(self::TEST_USER_ID);
        $this->assertEquals($canViewDateOfBirth, $canViewResult);
    }

    public function testRoleCheckDataProvider() {
        return [
            [[RoleCode::USER, RoleCode::TESTER], true],
            [[RoleCode::USER, RoleCode::SITE_MANAGER], true],
            [[RoleCode::USER, RoleCode::SITE_ADMIN], true],
            [[RoleCode::USER, OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], true],
            [[RoleCode::USER, OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE], true],
            [[RoleCode::USER, RoleCode::SCHEME_MANAGER], false],
            [[RoleCode::USER, RoleCode::SCHEME_USER], false],
            [[RoleCode::USER, RoleCode::DVLA_OPERATIVE], false],
            [[RoleCode::USER, RoleCode::DVLA_MANAGER], false],
        ];
    }

    private function getMockPersonalDetailsData(array $systemRoles)
    {
        return [
            'id' => self::TEST_USER_ID,
            'firstName' => 'test_firstName',
            'middleName' => 'test_middleName',
            'surname' => 'test_surname',
            'dateOfBirth' => '2016-10-13',
            'title' => '',
            'gender' => 'Unknown',
            'addressLine1' => 'test_addressLine1',
            'addressLine2' => 'test_addressLine2',
            'addressLine3' => 'test_addressLine3',
            'town' => 'test_town',
            'postcode' => 'BT13 3BU',
            'email' => 'test@email.com',
            'phone' => '12345',
            'drivingLicenceNumber' => null,
            'drivingLicenceRegion' => null,
            'username' => 'test_username',
            'positions' => [],
            'roles' => [
                'system' => [
                    'roles' => $systemRoles,
                ],
                'organisations' => [],
                'sites' => [],
            ],
        ];
    }
}