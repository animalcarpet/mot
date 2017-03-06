<?php

namespace DashboardTest\Controller;

use Application\Data\ApiPersonalDetails;
use Application\Service\CatalogService;
use Application\Service\LoggedInUserManager;
use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use CoreTest\TestUtils\Identity\FrontendIdentityProviderStub;
use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dashboard\Controller\UserHomeController;
use Dashboard\Data\ApiDashboardResource;
use Dashboard\Model\PersonalDetails;
use Dashboard\PersonStore;
use Dashboard\Service\TradeRolesAssociationsService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use Dvsa\OpenAM\OpenAMClient;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class UserHomeControllerTest extends AbstractFrontendControllerTestCase
{
    const USER_ID = 9999;

    private static $CONFIG = [
        'dvsa_authentication' => [
            'openAM' => [
                'realm' => [],
            ],
        ],
    ];

    /** @var ApiPersonalDetails|MockObj $mockPersonalDetailsSrv */
    private $mockPersonalDetailsSrv;

    /** @var HttpRestJsonClient|MockObj $mockRestClient */
    private $mockRestClient;

    /** @var OpenAMClient|MockObj $mockOpenAMClient */
    private $mockOpenAMClient;

    /** @var ApiDashboardResource|MockObj $mockDashboardSrv */
    private $mockDashboardSrv;

    /** @var LoggedInUserManager|MockObj $loggedInUserManagerMock */
    private $loggedInUserManagerMock;

    /** @var PersonStore|MockObj $mockPersonStoreSrv */
    private $mockPersonStoreSrv;

    /** @var CatalogService|MockObj $mockCatalogSrv */
    private $mockCatalogSrv;

    /** @var UserAdminSessionManager|MockObj $mockUserAdminSessionSrv */
    private $mockUserAdminSessionSrv;

    /** @var AuthorisationServiceMock $authorisationService*/
    private $authorisationService;

    /** @var FrontendIdentityProviderStub $identityProvider */
    private $identityProvider;

    public function setUp()
    {
        $sm = Bootstrap::getServiceManager();
        $sm->setAllowOverride(true);

        $this->setServiceManager($sm);

        $this->mockDashboardSrv = XMock::of(ApiDashboardResource::class, ['get']);
        $this->mockMethod($this->mockDashboardSrv, 'get', null, $this->getDashboardData());

        $this->mockPersonalDetailsSrv = XMock::of(ApiPersonalDetails::class);

        $this->mockPersonStoreSrv = XMock::of(PersonStore::class);

        $this->mockOpenAMClient = $this->mockOpenAMClient();
        $this->loggedInUserManagerMock = XMock::of(LoggedInUserManager::class);

        $this->mockCatalogSrv = XMock::of(CatalogService::class);
        $this->mockMethod($this->mockCatalogSrv, 'getCountriesOfRegistrationByCode', null, ['uk' => 'ukLong']);

        $this->mockUserAdminSessionSrv = XMock::of(UserAdminSessionManager::class);

        $catalogMockOrgData = $this->buildBusinessRolesData();
        $catalogMockSysData = $this->buildPersonSystemCatalog();

        $this->mockCatalogSrv->expects($this->any())
            ->method("getBusinessRoles")
            ->willReturn($catalogMockOrgData);

        $this->mockCatalogSrv->expects($this->any())
            ->method("getPersonSystemRoles")
            ->willReturn($catalogMockSysData);

        $this->authorisationService = new AuthorisationServiceMock();

        $this->identityProvider = new FrontendIdentityProviderStub();
        $this->identityProvider->setIdentity(new Identity());
        $this->identityProvider->getIdentity()->setUserId(self::USER_ID);

        $this->setController(
            new UserHomeController(
                $this->loggedInUserManagerMock,
                $this->mockPersonalDetailsSrv,
                $this->mockPersonStoreSrv,
                $this->mockDashboardSrv,
                $this->mockCatalogSrv,
                XMock::of(WebAcknowledgeSpecialNoticeAssertion::class),
                $this->mockUserAdminSessionSrv,
                XMock::of(TesterGroupAuthorisationMapper::class),
                $this->authorisationService,
                $this->mockUserAdminSessionSrv,
                new ViewTradeRolesAssertion($this->authorisationService, $this->identityProvider),
                XMock::of(TradeRolesAssociationsService::class)
            )
        );

        parent::setUp();

        $this->mockRestClient = XMock::of(HttpRestJsonClient::class, ['put']);
        $sm->setService(HttpRestJsonClient::class, $this->mockRestClient);

        $sm->setService('config', self::$CONFIG);

        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester(self::USER_ID));
    }

    /**
     * Test has user access to page or not with/out auth and permission
     *
     * @param string $action Request action
     * @param array $params Action parameters
     * @param boolean $isAuth Can user access page without Auth
     * @param array $permissions User has permissions
     * @param boolean $expectCanAccess Expect user has or not access to page
     * @param null $expectedUrl
     * @param string $expectException
     * @param null $expectErrMsg
     * @param bool $isUserPassSecurity
     *
     * @dataProvider dataProviderTestCanAccessHasRight
     */
    public function testTesterQualificationStatusService(
        $action,
        $params = [],
        $isAuth = true,
        $permissions = [],
        $expectCanAccess = true,
        $expectedUrl = null,
        $expectException = 'Exception',
        $expectErrMsg = null,
        $isUserPassSecurity = true
    )
    {
        $this->mockMethod(
            $this->mockPersonalDetailsSrv, 'getPersonalDetailsData', null, $this->getPersonalDetailsData()
        );

        if (!$isAuth) {
            $this->getAuthenticationServiceMockForFailure();

            $this->getResponseForAction($action, $params);
        } else {
            $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
            $this->setupAuthorizationService($permissions);

            if (!$expectCanAccess) {
                $this->setExpectedException($expectException, ($expectErrMsg ? $expectErrMsg : ''));
            }

            $this->mockUserAdminSessionSrv->expects($this->any())
                ->method('isUserAuthenticated')
                ->willReturn($isUserPassSecurity);

            $method = 'get';
            $this->getResultForAction2($method, $action, $params);

            if ($expectedUrl) {
                $this->assertRedirectLocation2($expectedUrl);
            } else {
                $this->assertResponseStatus(self::HTTP_OK_CODE);
            }
        }
    }

    public function dataProviderTestCanAccessHasRight()
    {
        return [
            [
                'action'          => 'userHome',
                'params'          => [],
                'isAuth'          => true,
                'permissions'     => [],
                'expectCanAccess' => true,
            ],
            ['profile', [], true, [], true],
            ['securitySettings', [], true, [], true],
            ['securitySettings', [], true, [], true, '/profile/security-question', 'Exception', null, false],
        ];
    }

    /**
     * @param bool $hasPermissionToViewOtherProfiles
     * @param bool $profileSubjectIsDvsa
     * @param bool $profileSubjectHasTradeRoles
     * @param bool $isViewingOwnProfile
     * @param bool $shouldSeeLink
     *
     * @dataProvider dataProviderTestSidebarIsShown
     */
    public function testSidebarIsShown($hasPermissionToViewOtherProfiles, $profileSubjectIsDvsa, $profileSubjectHasTradeRoles, $isViewingOwnProfile, $shouldSeeLink)
    {
        $this->setUpTestSidebarIsShown($hasPermissionToViewOtherProfiles, $profileSubjectIsDvsa, $profileSubjectHasTradeRoles);

        $this->getResponseForAction('profile', ['id' => $isViewingOwnProfile ? self::USER_ID : 10]);

        if ($shouldSeeLink) {
            $this->assertNotNull($this->getController()->getSidebar());
        } else {
            $this->assertNull($this->getController()->getSidebar());
        }
    }

    /**
     * @param bool $hasPermissionToViewOtherProfiles
     * @param bool $profileSubjectIsDvsa
     * @param bool $profileSubjectHasTradeRoles
     */
    private function setUpTestSidebarIsShown($hasPermissionToViewOtherProfiles, $profileSubjectIsDvsa, $profileSubjectHasTradeRoles)
    {
        $personalDetailsData = $this->getPersonalDetailsData();

        $roles = [
            'system'        => [
                'roles' => [RoleCode::USER]
            ],
            'organisations' => [],
            'sites'         => [],
        ];

        if ($profileSubjectIsDvsa) {
            $roles['system']['roles'][] = RoleCode::AREA_OFFICE_1;
        }

        if ($profileSubjectHasTradeRoles) {
            $roles['organisations'][10] = [
                'name'    => 'testing',
                'number'  => 'VTESTING',
                'address' => '34 Test Road',
                'roles'   => [RoleCode::AUTHORISED_EXAMINER_DELEGATE],
            ];
        }

        $personalDetailsData['roles'] = $roles;

        $this->mockPersonalDetailsSrv
            ->expects($this->any())
            ->method('getPersonalDetailsData')
            ->willReturn($personalDetailsData);

        if ($hasPermissionToViewOtherProfiles) {
            $this->authorisationService->granted(PermissionInSystem::VIEW_TRADE_ROLES_OF_ANY_USER);
        }
    }

    public function dataProviderTestSidebarIsShown()
    {
        return [
            [false, false, false, false, false],
            [false, false, false, true, true],
            [false, false, true, false, false],
            [false, false, true, true, true],
            [false, true, false, false, false],
            [false, true, false, true, false],
            [false, true, true, false, false],
            [false, true, true, true, true],
            [true, false, false, false, true],
            [true, false, false, true, true],
            [true, false, true, false, true],
            [true, false, true, true, true],
            [true, true, false, false, false],
            [true, true, false, true, false],
            [true, true, true, false, true],
            [true, true, true, true, true],
        ];
    }

    /**
     * @dataProvider dataProviderTestActionsResultAndAccess
     */
    public function testActionsResultAndAccess($method, $action, $params, $mocks, $expect)
    {
        $this->mockMethod(
            $this->mockPersonalDetailsSrv, 'getPersonalDetailsData', null, $this->getPersonalDetailsData()
        );

        $result = null;

        $session = new Container('prgHelperSession');
        $session->offsetSet('testToken', 'redirectUrl');

        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $this->mockMethod(
                    $this->{$mock['class']}, $mock['method'], $this->once(), $mock['result'], $mock['params']
                );
            }
        }

        // Set expected exception
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException(
                $exception['class'], $exception['message'], ArrayUtils::tryGet($exception, 'code')
            );
        }

        $result = $this->getResultForAction2(
            $method, $action, ArrayUtils::tryGet($params, 'route'), null, ArrayUtils::tryGet($params, 'post')
        );

        if (!empty($expect['viewModel'])) {
            $this->assertInstanceOf(ViewModel::class, $result);
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }

        if (!empty($expect['result'])) {
            $this->assertResponseStatus(self::HTTP_OK_CODE);
            $this->assertEquals($expect['result'], $result);
        }

        if (!empty($expect['errors'])) {
            $form = $result->getVariable('viewModel');

            foreach ($expect['errors'] as $field => $error) {
                $this->assertEquals($error, $form->getError($field));
            }
        }

        if (!empty($expect['flashError'])) {
            $this->assertEquals(
                $expect['flashError'],
                $this->getController()->flashMessenger()->getCurrentErrorMessages()[0]
            );
        }

        if (!empty($expect['url'])) {
            $this->assertRedirectLocation2($expect['url']);
        }
    }

    /**
     * Mock for Catalog System Roles Data - if you change this please change $this->setMockRoles.
     *
     * @return array
     */
    private function buildPersonSystemCatalog()
    {
        return [
            [
                'id'   => 1,
                'code' => 'USER',
                'name' => 'User',
            ],
            [
                'id'   => 4,
                'code' => RoleCode::AREA_OFFICE_1,
                'name' => '',
            ],
            [
                'id'   => 5,
                'code' => RoleCode::USER,
                'name' => '',
            ],
        ];
    }

    /**
     * Mock for Catalog Business Roles Data - if you change this please change $this->setMockRoles.
     *
     * @return array
     */
    private function buildBusinessRolesData()
    {
        return [
            [
                'id'   => 1,
                'code' => 'TESTER',
                'name' => 'Tester',
            ],
            [
                'id'   => 2,
                'code' => 'AEDM',
                'name' => 'Authorised Examiner Designated Manager',
            ],
            [
                'id'   => 3,
                'code' => RoleCode::AUTHORISED_EXAMINER_DELEGATE,
                'name' => '',
            ],
        ];
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        $newPin = 'unit_newPin';

        return [
            // SecuritySettings: success
            [
                'method' => 'post',
                'action' => 'securitySettings',
                'params' => [
                    'post' => [],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockRestClient',
                        'method' => 'put',
                        'params' => [
                            PersonUrlBuilder::resetPin(self::USER_ID),
                            null
                        ],
                        'result' => ['data' => ['pin' => $newPin]],
                    ],
                    [
                        'class'  => 'mockUserAdminSessionSrv',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'result' => [
                        'pin'      => $newPin,
                        'fullName' => 'Mr foo bar baz',
                        'userId'   => self::USER_ID,
                        'config'   => self::$CONFIG,
                    ],
                ],
            ],
            // SecuritySettings: fail (GeneralRestException)
            [
                'method' => 'post',
                'action' => 'securitySettings',
                'params' => [
                    'post' => [],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockRestClient',
                        'method' => 'put',
                        'params' => [
                            PersonUrlBuilder::resetPin(self::USER_ID),
                            null
                        ],
                        'result' => new \Exception('/', 10),
                    ],
                    [
                        'class'  => 'mockUserAdminSessionSrv',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'result' => [
                        'fullName' => 'Mr foo bar baz',
                        'config'   => self::$CONFIG,
                        'userId'   => self::USER_ID,
                    ],
                ],
            ],
            // SecuritySettings: fail
            [
                'method' => 'post',
                'action' => 'securitySettings',
                'params' => [
                    'post' => [],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockRestClient',
                        'method' => 'put',
                        'params' => [
                            PersonUrlBuilder::resetPin(self::USER_ID),
                            null
                        ],
                        'result' => new GeneralRestException('/', 'post', [], 10, 'Person not found'),
                    ],
                    [
                        'class'  => 'mockUserAdminSessionSrv',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'result' => [
                        'fullName' => 'Mr foo bar baz',
                        'config'   => self::$CONFIG,
                        'userId'   => self::USER_ID,
                    ],
                ],
            ],
        ];
    }

    private function getPersonalDetailsData()
    {
        return [
            'id'                   => 1,
            'firstName'            => 'foo',
            'middleName'           => 'bar',
            'surname'              => 'baz',
            'username'             => 'tester1',
            'dateOfBirth'          => '1979-12-20',
            'title'                => 'Mr',
            'gender'               => 'male',
            'addressLine1'         => 'foo',
            'addressLine2'         => 'foo',
            'addressLine3'         => 'foo',
            'town'                 => 'foo',
            'postcode'             => 'AA11 1AA',
            'email'                => 'foo',
            'emailConfirmation'    => null,
            'phone'                => 1234,
            'drivingLicenceNumber' => 'foo',
            'drivingLicenceRegion' => 'bar',
            'positions'            => [],
            'roles'                => $this->setMockRoles(),

        ];
    }

    private function setMockRoles()
    {
        return [
            'system'        => [
                'roles' => ['USER'],
            ],
            'organisations' => [10 => [
                'name'    => 'testing',
                'number'  => 'VTESTING',
                'address' => '34 Test Road',
                'roles'   => ['AEDM'],
            ]],
            'sites'         => [20 => [
                'name'    => 'testing',
                'number'  => 'VTESTING',
                'address' => '34 Test Road',
                'roles'   => ['TESTER'],
            ]]
        ];
    }

    /**
     * @return array
     */
    private function getDashboardData()
    {
        return [
            'hero'                   => null,
            'permissions'            => null,
            'specialNotice'          => [
                'daysLeftToView' => null,
                'unreadCount'    => null,
                'overdueCount'   => null,
            ],
            'overdueSpecialNotices' => array_combine(VehicleClassCode::getAll(), array_fill(0, count(VehicleClassCode::getAll()), 0)),
            'notifications'          => [],
            'unreadNotificationsCount' => 0,
            'inProgressTestNumber'   => null,
            'inProgressTestTypeCode' => null,
            'authorisedExaminers'    => [],
        ];
    }

    public function testGetAuthenticatedDataResult()
    {
        $this->mockMethod(
            $this->mockPersonalDetailsSrv, 'getPersonalDetailsData', null, $this->getPersonalDetailsData()
        );

        $authResult = 'authResult';
        $this->mockMethod(
            $this->mockPersonalDetailsSrv, 'getPersonalAuthorisationForMotTesting', null, $authResult, self::USER_ID
        );

        $actual = $this->getResultForAction('profile');

        $arrayKeys = array(
            "personalDetails",
            "isAllowEdit",
            "motAuthorisations",
            "isViewingOwnProfile",
            "systemRoles",
            "rolesAndAssociations",
            "authorisation",
            "canRead",
            "canAcknowledge",
            "canReceiveSpecialNotice",
            "countries",
            "roleNiceNameList",
            "canViewUsername",
        );

        foreach ($arrayKeys as $key) {
            $this->assertArrayHasKey($key, $actual);
        }

        // Test will fail if any more keys are added to the returned value
        $count = count($actual);
        $this->assertEquals(count($arrayKeys), $count);

        $this->assertEquals(new PersonalDetails($this->getPersonalDetailsData()), $actual['personalDetails']);
        $this->assertEquals($authResult, $actual['motAuthorisations']);
        $this->assertEquals(true, $actual['isViewingOwnProfile']);
        $this->assertEquals(['uk' => 'ukLong'], $actual['countries']);
    }

    public function testNonMotTestPanelIsDisplayedWhenPermissionIsGranted()
    {
        $this->mockMethod(
            $this->mockPersonalDetailsSrv, 'getPersonalDetailsData', null, $this->getPersonalDetailsData()
        );

        $this->authorisationService->granted(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM);

        $actual = $this->getResultForAction('userHome');

        $this->assertTrue($actual['canPerformNonMotTest']);
    }

    public function testNonMotTestPanelIsNotDisplayedWhenPermissionIsNotGranted()
    {
        $this->mockMethod(
            $this->mockPersonalDetailsSrv, 'getPersonalDetailsData', null, $this->getPersonalDetailsData()
        );

        $actual = $this->getResultForAction('userHome');

        $this->assertFalse($actual['canPerformNonMotTest']);
    }

    /**
     * @return UserHomeController
     */
    protected function getController()
    {
        return parent::getController();
    }
}
