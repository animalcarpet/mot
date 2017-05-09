<?php

namespace SiteTest\Authorisation;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaClient\Entity\Person;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Security\RolesMapDto;
use DvsaCommon\Dto\Security\RoleStatusDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Site\Authorization\VtsOverviewPagePermissions;

class VtsOverviewPagePermissionsTest extends PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    private static $USER_ID = 9999;
    private static $SITE_ID = 8888;
    private static $ORG_ID = 8881;

    /** @var MotAuthorisationServiceInterface|MockObj */
    private $mockAuthSrv;
    private $mockIdentity;
    /** @var VehicleTestingStationDto */
    private $mockVtsData;

    public function setUp()
    {
        $this->mockAuthSrv = XMock::of(
            MotAuthorisationServiceInterface::class, ['isGrantedAtSite', 'isGrantedAtOrganisation']
        );

        $this->mockIdentity = XMock::of(MotFrontendIdentityInterface::class, ['getUserId']);
        $this->mockIdentity->expects($this->any())
            ->method('getUserId')
            ->willReturn(self::$USER_ID);

        $this->mockVtsData = (new VehicleTestingStationDto())
            ->setId(self::$SITE_ID)
            ->setOrganisation((new OrganisationDto())->setId(self::$ORG_ID));
    }

    public function testCanViewTestsInProgress()
    {
        $expectedResult = 'expectedResult';

        $this->mockAuthSrv->expects($this->once())
            ->method('isGrantedAtSite')
            ->with(PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS, self::$SITE_ID)
            ->willReturn($expectedResult);

        $obj = new VtsOverviewPagePermissions($this->mockAuthSrv, $this->mockIdentity, $this->mockVtsData, [], 1);

        $actualResult = $obj->canViewTestsInProgress();

        $this->assertEquals($actualResult, $expectedResult);
    }

    public function testCanViewProfileDependsOnAcceptingNomination()
    {
        $this->mockAuthSrv->expects($this->any())
            ->method('isGrantedAtSite')
            ->with(PermissionAtSite::VTS_EMPLOYEE_PROFILE_READ, self::$SITE_ID)
            ->willReturn(true);

        $positions = [];

        $active = (new RoleStatusDto())->setCode(BusinessRoleStatusCode::ACTIVE);
        $pending = (new RoleStatusDto())->setCode(BusinessRoleStatusCode::PENDING);

        // GIVEN I have a person who accepted at least one nomination
        $employee = new PersonDto();
        $employee->setId(1);

        $position = new RolesMapDto();
        $position->setRoleStatus($active);
        $position->setPerson($employee);

        $positions[] = $position;

        $position = new RolesMapDto();
        $position->setRoleStatus($pending);
        $position->setPerson($employee);

        $positions[] = $position;

        // AND a person that is nominated but haven't accepted yet.

        $nominee = new PersonDto();
        $nominee->setId(2);
        $position = new RolesMapDto();
        $position->setRoleStatus($pending);
        $position->setPerson($nominee);

        $positions[] = $position;

        $permissions = new VtsOverviewPagePermissions(
            $this->mockAuthSrv,
            $this->mockIdentity,
            $this->mockVtsData,
            $positions,
            1
        );

        // WHEN I want to view their profiles
        $employeeAccessGranted = $permissions->canViewProfile($employee);

        $nomineeAccessGranted = $permissions->canViewProfile($nominee);

        // THEN I can my employee profile
        $this->assertTrue($employeeAccessGranted);

        // AND I cannot view profile of the nominee
        $this->assertFalse($nomineeAccessGranted);
    }

    public function dataProviderTestCanViewUserProfile()
    {
        return [
            [
                'personId' => null,
                'isGranted' => true,
                'expect' => true,
            ],
            [1, false, false],
            [1, true, true],
            [self::$USER_ID, false, true],
        ];
    }

    /**
     * @dataProvider dataProviderCanTestClasses
     */
    public function testCanTestClasses($roles, $expect12, $expect3to7)
    {
        $this->mockVtsData->setTestClasses($roles);

        $obj = new VtsOverviewPagePermissions($this->mockAuthSrv, $this->mockIdentity, $this->mockVtsData, [], 1);

        $actualResult12 = $obj->canTestClass1And2();
        $actualResult3to7 = $obj->canTestAnyOfClass3AndAbove();

        $this->assertEquals($actualResult12, $expect12);
        $this->assertEquals($actualResult3to7, $expect3to7);
    }

    public function dataProviderCanTestClasses()
    {
        return [
            [
                'roles' => [1, 3],
                'expect12' => true,
                'expect3to7' => true,
            ],
            [[4, 5], false, true],
            [[1], true, false],
            [[], false, false],
        ];
    }

    public function testCanChangeDefaultBrakeTests()
    {
        $expectResult = 'expect Result';

        $this->mockAuthSrv->expects($this->once())
            ->method('isGrantedAtSite')
            ->with(PermissionAtSite::DEFAULT_BRAKE_TESTS_CHANGE, self::$SITE_ID)
            ->willReturn($expectResult);

        $obj = new VtsOverviewPagePermissions($this->mockAuthSrv, $this->mockIdentity, $this->mockVtsData, [], 1);

        $actualResult = $obj->canChangeDefaultBrakeTests();

        $this->assertEquals($actualResult, $expectResult);
    }

    public function testCanAbortMotTest()
    {
        $expectResult = 'expect Result';

        $this->mockAuthSrv->expects($this->once())
            ->method('isGrantedAtSite')
            ->with(PermissionAtSite::MOT_TEST_ABORT_AT_SITE, self::$SITE_ID)
            ->willReturn($expectResult);

        $obj = new VtsOverviewPagePermissions($this->mockAuthSrv, $this->mockIdentity, $this->mockVtsData, [], 1);

        $actualResult = $obj->canAbortMotTest();

        $this->assertEquals($actualResult, $expectResult);
    }

    public function testCanRemoveRoleAtSite()
    {
        $expectResult = true;

        $this->mockAuthSrv->expects($this->once())
            ->method('isGrantedAtSite')
            ->with(PermissionAtSite::REMOVE_ROLE_AT_SITE, self::$SITE_ID)
            ->willReturn($expectResult);

        $obj = new VtsOverviewPagePermissions($this->mockAuthSrv, $this->mockIdentity, $this->mockVtsData, [], 1);

        $actualResult = $obj->canRemoveRoleAtSite();

        $this->assertEquals($actualResult, $expectResult);
    }

    /**
     * @dataProvider dataProviderTestPermission
     */
    public function testPermission($permission, $method, $expected, $params = null)
    {
        $this->mockMethod(
            $this->mockAuthSrv, $permission['method'], $this->once(), $permission['result'], $permission['params']
        );

        $obj = new VtsOverviewPagePermissions(
            $this->mockAuthSrv,
            $this->mockIdentity,
            $this->mockVtsData,
            [],
            self::$ORG_ID
        );

        $actualResult = $obj->$method($params);

        $this->assertEquals($actualResult, $expected);
    }

    public function dataProviderTestPermission()
    {
        return [
            [
                'permission' => [
                    'method' => 'isGrantedAtOrganisation',
                    'params' => [PermissionAtOrganisation::AUTHORISED_EXAMINER_READ, self::$ORG_ID],
                    'result' => true,
                ],
                'method' => 'canViewAuthorisedExaminer',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canViewTestsInProgress',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::DEFAULT_BRAKE_TESTS_CHANGE, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canChangeDefaultBrakeTests',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::MOT_TEST_ABORT_AT_SITE, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canAbortMotTest',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::NOMINATE_ROLE_AT_SITE, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canNominateRole',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::REMOVE_ROLE_AT_SITE, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canRemoveRoleAtSite',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::TESTING_SCHEDULE_UPDATE, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canUpdateTestingSchedule',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGranted',
                    'params' => [PermissionInSystem::EVENT_READ],
                    'result' => true,
                ],
                'method' => 'canViewEventHistory',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGranted',
                    'params' => [PermissionInSystem::VEHICLE_TESTING_STATION_SEARCH],
                    'result' => true,
                ],
                'method' => 'canSearchVts',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::REMOVE_SITE_MANAGER, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canRemovePositionAtSite',
                'expected' => true,
                'params' => SiteBusinessRoleCode::SITE_MANAGER,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::REMOVE_ROLE_AT_SITE, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canRemovePositionAtSite',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::VTS_UPDATE_TESTING_FACILITIES_DETAILS, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canChangeTestingFacilities',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::VTS_UPDATE_SITE_RISK_ASSESSMENT, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canChangeRiskAssessment',
                'expected' => true,
            ],
            [
                'permission' => [
                    'method' => 'isGrantedAtSite',
                    'params' => [PermissionAtSite::VTS_VIEW_SITE_RISK_ASSESSMENT, self::$SITE_ID],
                    'result' => true,
                ],
                'method' => 'canViewRiskAssessment',
                'expected' => true,
            ],
        ];
    }
}
