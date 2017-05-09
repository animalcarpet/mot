<?php

namespace UserAdminTest\Controller;

use Application\Helper\PrgHelper;
use Application\Service\CatalogService;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Controller\UserProfileController;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\View\Model\ViewModel;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use Zend\Session\Container;

class UserProfileControllerTest extends AbstractFrontendControllerTestCase
{
    const PERSON_ID = 7;
    const TEST_USERNAME = 'TEST_USERNAME';

    private $accountAdminServiceMock;
    private $authorisationMock;
    private $testerGroupAuthorisationMapper;
    /** @var PersonRoleManagementService | \PHPUnit_Framework_MockObject_MockObject */
    private $personRoleManagementService;
    private $catalogService;
    private $registeredCardServiceMock;
    private $twoFaFeatureToggleMock;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->authorisationMock = XMock::of(MotAuthorisationServiceInterface::class);
        $this->accountAdminServiceMock = XMock::of(HelpdeskAccountAdminService::class);
        $this->testerGroupAuthorisationMapper = XMock::of(TesterGroupAuthorisationMapper::class);
        $this->testerGroupAuthorisationMapper->expects($this->any())
            ->method('getAuthorisation')
            ->willReturn(new TesterAuthorisation());
        $this->personRoleManagementService = XMock::of(PersonRoleManagementService::class);
        $this->catalogService = XMock::of(CatalogService::class);
        $this->registeredCardServiceMock = XMock::of(RegisteredCardService::class);
        $this->twoFaFeatureToggleMock = XMock::of(TwoFaFeatureToggle::class);

        $this->setController(
            new UserProfileController(
                $this->authorisationMock,
                $this->accountAdminServiceMock,
                $this->testerGroupAuthorisationMapper,
                $this->personRoleManagementService,
                $this->catalogService,
                XMock::of(ViewTradeRolesAssertion::class),
                $this->registeredCardServiceMock,
                $this->twoFaFeatureToggleMock
            )
        );

        $this->getController()->setServiceLocator($serviceManager);

        $this->createHttpRequestForController('Reset');

        parent::setUp();
    }

    /**
     * @dataProvider dataProviderTestWordingCorrectDependingOn2FaEnabled
     */
    public function testWordingCorrectDependingOn2FaEnabled($method, $action, $params, $mocks, $expect)
    {
        $result = null;
        $session = new Container('prgHelperSession');
        $session->offsetSet('testToken', 'redirectUrl');

        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $this->mockMethod(
                    $this->{$mock['class']},
                    $mock['method'],
                    $this->once(),
                    $mock['result'],
                    $mock['params']
                );
            }
        }

        $this->personRoleManagementService
            ->expects($this->any())
            ->method('getPersonAssignedInternalRoles')
            ->willReturn([]);

        $result = $this->getResultForAction2(
            $method,
            $action,
            ArrayUtils::tryGet($params, 'route'),
            ArrayUtils::tryGet($params, 'get'),
            ArrayUtils::tryGet($params, 'post')
        );

        //  --  check   --
        if (!empty($expect['viewModel'])) {
            $this->assertInstanceOf(ViewModel::class, $result);
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }

        if (!empty($expect['viewModelVariables'])) {
            foreach ($expect['viewModelVariables'] as $variable => $value) {
                $this->assertArrayHasKey($variable, $result->getVariables());
                $this->assertSame($result->getVariable($variable), $value);
            }
        }
    }

    /**
     * @dataProvider dataProviderTestActionsResultAndAccess
     */
    public function testActionsResultAndAccess($method, $action, $params, $mocks, $expect)
    {
        $result = null;

        $session = new Container('prgHelperSession');
        $session->offsetSet('testToken', 'redirectUrl');

        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $this->mockMethod(
                    $this->{$mock['class']},
                    $mock['method'],
                    $this->once(),
                    $mock['result'],
                    $mock['params']
                );
            }
        }

        //  --  set expected exception  --
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message']);
        }

        $this->personRoleManagementService
            ->expects($this->any())
            ->method('getPersonAssignedInternalRoles')
            ->willReturn([]);

        $result = $this->getResultForAction2(
            $method,
            $action,
            ArrayUtils::tryGet($params, 'route'),
            ArrayUtils::tryGet($params, 'get'),
            ArrayUtils::tryGet($params, 'post')
        );

        //  --  check   --
        if (!empty($expect['viewModel'])) {
            $this->assertInstanceOf(ViewModel::class, $result);
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }

        if (!empty($expect['viewModelVariables'])) {
            foreach ($expect['viewModelVariables'] as $variable => $value) {
                $this->assertArrayHasKey($variable, $result->getVariables());
                $this->assertSame($result->getVariable($variable), $value);
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

    public function dataProviderTestWordingCorrectDependingOn2FaEnabled()
    {
        return [
            //  --  claimAccount: wording appropriate for 2fa user  --
            [
                'method' => 'get',
                'action' => 'claimAccount',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                    'get' => [
                        'test' => 'test',
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'getUserProfile',
                        'params' => [self::PERSON_ID],
                        'result' => $this->createPersonHelpDeskProfileDtoWithUsername(),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'viewModelVariables' => [
                        'reclaimSystemMessage' => UserProfileController::RESET_ACCOUNT_SYSTEM_MESSAGE,
                    ],
                ],
            ],
            //  --  claimAccount: wording appropriate for NON-2fa user when not 2fa user but toggle on --
            [
                'method' => 'get',
                'action' => 'claimAccount',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                    'get' => [
                        'test' => 'test',
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'getUserProfile',
                        'params' => [self::PERSON_ID],
                        'result' => $this->createPersonHelpDeskProfileDtoWithUsername(),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'viewModelVariables' => [
                        'reclaimSystemMessage' => UserProfileController::RESET_ACCOUNT_SYSTEM_MESSAGE,
                    ],
                ],
            ],
            //  --  claimAccount: wording appropriate for NON-2fa user when 2fa toggle off --
            [
                'method' => 'get',
                'action' => 'claimAccount',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                    'get' => [
                        'test' => 'test',
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'getUserProfile',
                        'params' => [self::PERSON_ID],
                        'result' => $this->createPersonHelpDeskProfileDtoWithUsername(),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'viewModelVariables' => [
                        'reclaimSystemMessage' => UserProfileController::RESET_ACCOUNT_SYSTEM_MESSAGE,
                    ],
                ],
            ],
        ];
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        return [
            //  --  index: access action, test feature "2FA method display" toggled on  --
            [
                'method' => 'get',
                'action' => 'index',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'getUserProfile',
                        'params' => [self::PERSON_ID],
                        'result' => $this->createEmptyPersonHelpDeskProfileDto(),
                    ],

                ],
                'expect' => [
                    'viewModel' => true,
                    'viewModelVariables' => [
                    ],
                ],
            ],
            //  --  index: access action, test feature "2FA method display" toggled off   --
            [
                'method' => 'get',
                'action' => 'index',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'getUserProfile',
                        'params' => [self::PERSON_ID],
                        'result' => $this->createEmptyPersonHelpDeskProfileDto(),
                    ],

                ],
                'expect' => [
                    'viewModel' => true,
                    'viewModelVariables' => [
                    ],
                ],
            ],
            //  --  passwordReset: access action  --
            [
                'method' => 'post',
                'action' => 'passwordReset',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                    'post' => [],
                ],
                'mocks' => [
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'getUserProfile',
                        'params' => [self::PERSON_ID],
                        'result' => $this->createEmptyPersonHelpDeskProfileDto(),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            //  --  usernameRecover: access action  --
            [
                'method' => 'post',
                'action' => 'usernameRecover',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                    'post' => [],
                ],
                'mocks' => [
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'getUserProfile',
                        'params' => [self::PERSON_ID],
                        'result' => $this->createEmptyPersonHelpDeskProfileDto(),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            //  --  usernameRecover: post action Failure --
            [
                'method' => 'post',
                'action' => 'usernameRecover',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                    'post' => [],
                ],
                'mocks' => [
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'getUserProfile',
                        'params' => [self::PERSON_ID],
                        'result' => $this->createEmptyPersonHelpDeskProfileDto(),
                    ],
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'postMessage',
                        'params' => [],
                        'result' => new ValidationException('/', 'post', [], 10, 'Person not found'),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            //  --  claimAccount: access action  --
            [
                'method' => 'get',
                'action' => 'claimAccount',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                    'get' => [
                        'test' => 'test',
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'getUserProfile',
                        'params' => [self::PERSON_ID],
                        'result' => $this->createEmptyPersonHelpDeskProfileDto(),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            //  --  claimAccount: post action  --
            [
                'method' => 'post',
                'action' => 'claimAccount',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                ],
                'mocks' => null,
                'expect' => [
                    'url' => '/user-admin/user/'.self::PERSON_ID,
                ],
            ],
            //  --  claimAccount: post action Failed  --
            [
                'method' => 'post',
                'action' => 'claimAccount',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'accountAdminServiceMock',
                        'method' => 'resetClaimAccount',
                        'params' => [self::PERSON_ID],
                        'result' => new \Exception('Person not found'),
                    ],
                ],
                'expect' => [
                    'url' => '/user-admin/user/'.self::PERSON_ID,
                ],
            ],
            //  --  claimAccount: post multi data  --
            [
                'method' => 'post',
                'action' => 'claimAccount',
                'params' => [
                    'route' => [
                        'id' => self::PERSON_ID,
                    ],
                    'post' => [
                        PrgHelper::FORM_GUID_FIELD_NAME => 'testToken',
                    ],
                ],
                'mocks' => null,
                'expect' => [
                    'url' => 'redirectUrl',
                ],
            ],
        ];
    }

    private function createEmptyPersonHelpDeskProfileDto()
    {
        $dto = new PersonHelpDeskProfileDto();
        $dto->setRoles(['organisations' => [], 'sites' => []]);

        return $dto;
    }

    private function createPersonHelpDeskProfileDtoWithUsername()
    {
        $dto = new PersonHelpDeskProfileDto();
        $dto->setRoles(['organisations' => [], 'sites' => []]);
        $dto->setUserName(self::TEST_USERNAME);

        return $dto;
    }
}
