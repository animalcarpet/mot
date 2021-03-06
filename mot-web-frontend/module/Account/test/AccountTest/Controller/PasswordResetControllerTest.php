<?php

namespace AccountTest\Controller;

use Account\Controller\PasswordResetController;
use Account\Service\PasswordResetService;
use Account\ViewModel\ChangePasswordFormModel;
use Account\ViewModel\PasswordResetFormModel;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Mapper\AccountMapper;
use DvsaCommon\Dto\Account\MessageDto;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\View\Helper\Url;
use Zend\View\Model\ViewModel;

/**
 * Class PasswordResetControllerTest.
 */
class PasswordResetControllerTest extends AbstractFrontendControllerTestCase
{
    const USER_NAME = 'username';
    const TOKEN = 'TOKEN_123456789';
    const PERSON_ID = 999999;

    /** @var UserAdminSessionManager|MockObj $mockSessionManager */
    private $mockSessionManager;

    /** @var PasswordResetService|MockObj $mockPasswordResetSrv */
    private $mockPasswordResetSrv;

    /** @var AccountMapper|MockObj $mockAccountMapper */
    private $mockAccountMapper;

    /** @var ParamObfuscator $mockObfuscator */
    private $mockObfuscator;

    /** @var array $config */
    private $config;

    /** @var Url */
    private $urlPlugin;

    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager = clone $serviceManager;
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->mockAccountMapper = XMock::of(AccountMapper::class);
        $this->mockSessionManager = XMock::of(UserAdminSessionManager::class);
        $this->mockPasswordResetSrv = XMock::of(PasswordResetService::class);
        $this->mockObfuscator = XMock::of(ParamObfuscator::class);

        $moduleConfig = include __DIR__.'/../../../config/module.config.php';

        $this->config = [
            PasswordResetController::CFG_PASSWORD_RESET => [
                PasswordResetController::CFG_PASSWORD_RESET_EXPIRE_TIME => 5400,
            ],
            'helpdesk' => [
                'name' => 'DVSA Helpdesk',
                'phoneNumber' => '0330 123 5654',
            ],
            'router' => $moduleConfig['router'],
        ];

        $serviceManager->setService('config', $this->config);

        $this->setController(
            new PasswordResetController(
                $this->mockPasswordResetSrv,
                $this->mockSessionManager,
                $this->mockAccountMapper,
                $this->config,
                $this->mockObfuscator
            )
        );

        $this->getController()->setServiceLocator($serviceManager);

        $this->createHttpRequestForController('Reset');

        $this->urlPlugin = $this->getController()->getPluginManager()->get('url');

        parent::setUp();
    }

    /**
     * @dataProvider testActionsResultAndAccessDataProvider
     *
     * @param string $method
     * @param string $action
     * @param array  $params
     * @param array  $mocks
     * @param array  $expect
     */
    public function testActionsResultAndAccess($method, $action, $params, $mocks, $expect)
    {
        $result = null;

        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $this->mockMethod(
                    $this->{$mock['class']},
                    $mock['method'],
                    isset($mock['call']) ? $mock['call'] : $this->once(),
                    $mock['result'],
                    $mock['params']
                );
            }
        }

        // Set expected exception
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message']);
        }

        $result = $this->getResultForAction2(
            $method,
            $action,
            ArrayUtils::tryGet($params, 'route'),
            null,
            ArrayUtils::tryGet($params, 'post')
        );

        if (!empty($expect['viewModel'])) {
            $this->assertInstanceOf(ViewModel::class, $result);
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }

        if (!empty($expect['errors'])) {
            /** @var PasswordResetFormModel $form */
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

        if (!empty($expect['route'])) {

            // This is in preparation of switching from deprecated AccountUrlBuilderWeb to ZF's URL plugin
            if ($expect['route'] instanceof AccountUrlBuilderWeb) {
                $url = $expect['route'];
            } else {
                $url = $this->urlPlugin->fromRoute($expect['route']['url'], $expect['route']['params']);
            }

            $this->assertRedirectLocation2($url);
        }
    }

    /**
     * @dataProvider testGetEmailFromApiResponseDataProvider
     *
     * @param messageDto[] $apiResponse
     * @param string       $expectedEmailAddress
     */
    public function testGetEmailFromApiResponse($apiResponse, $expectedEmailAddress)
    {
        $reflection = new \ReflectionClass(get_class($this->controller));
        $method = $reflection->getMethod('getEmailFromApiResponse');
        $method->setAccessible(true);
        $emailAddressFromApiResponse = $method->invokeArgs($this->controller, $apiResponse);

        $this->assertEquals($expectedEmailAddress, $emailAddressFromApiResponse);
    }

    public function testGetEmailFromApiResponseDataProvider()
    {
        return [
            [
                'apiResponse' => [
                    new messageDto(),
                ],
                'expectedEmailAddress' => '',
            ],
            [
                'apiResponse' => [
                    (new messageDto())->setPerson(
                        new PersonDto()
                    ),
                ],
                'expectedEmailAddress' => '',
            ],
            [
                'apiResponse' => [
                    (new messageDto())->setPerson(
                        (new PersonDto())->setContactDetails(
                            [
                                new ContactDto(),
                            ]
                        )
                    ),
                ],
                'expectedEmailAddress' => '',
            ],
            [
                'apiResponse' => [
                    (new messageDto())->setPerson(
                        (new PersonDto())->setContactDetails(
                            [
                                (new ContactDto())->setEmails(
                                    [
                                        new EmailDto(),
                                    ]
                                ),
                            ]
                        )
                    ),
                ],
                'expectedEmailAddress' => '',
            ],
            [
                'apiResponse' => [
                    (new messageDto())->setPerson(
                        (new PersonDto())->setContactDetails(
                            [
                                (new ContactDto())->setEmails(
                                    [
                                        (new EmailDto())->setEmail(null),
                                    ]
                                ),
                            ]
                        )
                    ),
                ],
                'expectedEmailAddress' => '',
            ],
            [
                'apiResponse' => [
                    (new messageDto())->setPerson(
                        (new PersonDto())->setContactDetails(
                            [
                                (new ContactDto())->setEmails(
                                    [
                                        (new EmailDto())->setEmail('myemail@domaim.com'),
                                    ]
                                ),
                            ]
                        )
                    ),
                ],
                'expectedEmailAddress' => 'myemail@domaim.com',
            ],
        ];
    }

    public function testActionsResultAndAccessDataProvider()
    {
        return [
            // Username: access action
            [
                'method' => 'get',
                'action' => 'username',
                'params' => [
                    'post' => [],
                ],
                'mocks' => [],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            // Username: post with empty data
            [
                'method' => 'post',
                'action' => 'username',
                'params' => [
                    'post' => [],
                ],
                'mocks' => [],
                'expect' => [
                    'viewModel' => true,
                    'errors' => [
                        PasswordResetFormModel::FIELD_USERNAME => PasswordResetFormModel::USER_REQUIRED,
                    ],
                ],
            ],
            // Username: post with invalid data
            [
                'method' => 'post',
                'action' => 'username',
                'params' => [
                    'post' => [
                        PasswordResetFormModel::FIELD_USERNAME => 'NOT_EXIST',
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockPasswordResetSrv',
                        'method' => 'validateUsername',
                        'params' => 'NOT_EXIST',
                        'result' => new NotFoundException('/', 'post', [], 10, 'Person not found'),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'errors' => [
                        PasswordResetFormModel::FIELD_USERNAME => PasswordResetFormModel::USER_NOT_FOUND,
                    ],
                ],
            ],
            // Username: post success and redirect
            [
                'method' => 'post',
                'action' => 'username',
                'params' => [
                    'post' => [
                        PasswordResetFormModel::FIELD_USERNAME => self::USER_NAME,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockPasswordResetSrv',
                        'method' => 'validateUsername',
                        'params' => self::USER_NAME,
                        'result' => self::PERSON_ID,
                    ],
                ],
                'expect' => [
                    'route' => [
                        'url' => 'forgotten-password/security-questions',
                        'params' => ['personId' => self::PERSON_ID],
                    ],
//                    'route' => '/forgotten-password/security-questions/' . self::PERSON_ID,
                ],
            ],
            // Username: post success but no email
            [
                'method' => 'post',
                'action' => 'username',
                'params' => [
                    'post' => [
                        PasswordResetFormModel::FIELD_USERNAME => self::USER_NAME,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockPasswordResetSrv',
                        'method' => 'validateUsername',
                        'params' => self::USER_NAME,
                        'result' => false,
                    ],
                ],
                'expect' => [
                    'route' => AccountUrlBuilderWeb::forgottenPasswordEmailNotFound(),
                ],
            ],
            // Authenticate: not authenticated
            [
                'method' => 'get',
                'action' => 'authenticated',
                'params' => [],
                'mocks' => [
                    [
                        'class' => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => false,
                    ],
                ],
                'expect' => [
                    'route' => AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated(),
                ],
            ],
            // Authenticate: get email sent
            [
                'method' => 'get',
                'action' => 'authenticated',
                'params' => [],
                'mocks' => [
                    [
                        'class' => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                    [
                        'class' => 'mockSessionManager',
                        'method' => 'getElementOfUserAdminSession',
                        'call' => $this->any(),
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'route' => AccountUrlBuilderWeb::forgottenPasswordConfirmation(),
                ],
            ],
            // Authenticate: get is authenticated, service return exception
            [
                'method' => 'get',
                'action' => 'authenticated',
                'params' => [],
                'mocks' => [
                    [
                        'class' => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                    [
                        'class' => 'mockAccountMapper',
                        'method' => 'resetPassword',
                        'params' => [],
                        'result' => new NotFoundException('/', 'post', [], 10, 'Token not found'),
                    ],

                ],
                'expect' => [
                    'route' => AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated(),
                ],
            ],
            // Authenticate: get is authenticated, service return success
            [
                'method' => 'get',
                'action' => 'authenticated',
                'params' => [],
                'mocks' => [
                    [
                        'class' => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                    [
                        'class' => 'mockAccountMapper',
                        'method' => 'resetPassword',
                        'params' => [],
                        'result' => (new MessageDto())
                            ->setToken(self::TOKEN)
                            ->setPerson(new PersonDto()),
                    ],

                ],
                'expect' => [
                    'route' => AccountUrlBuilderWeb::forgottenPasswordConfirmation(),
                ],
            ],
            // Confirmation: get is authenticated, service return exception
            [
                'method' => 'get',
                'action' => 'confirmation',
                'params' => [],
                'mocks' => [],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            // Not authenticated
            [
                'method' => 'get',
                'action' => 'notAuthenticated',
                'params' => [],
                'mocks' => [
                    [
                        'class' => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => false,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            // Not authenticated: but authenticate in fact
            [
                'method' => 'get',
                'action' => 'notAuthenticated',
                'params' => [],
                'mocks' => [
                    [
                        'class' => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'route' => AccountUrlBuilderWeb::forgottenPasswordAuthenticated(),
                ],
            ],
            // Email not found
            [
                'method' => 'get',
                'action' => 'emailNotFound',
                'params' => [],
                'mocks' => [
                    [
                        'class' => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => false,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
             // Email not found: but authenticate in fact
            [
                'method' => 'get',
                'action' => 'emailNotFound',
                'params' => [],
                'mocks' => [
                    [
                        'class' => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'route' => AccountUrlBuilderWeb::forgottenPasswordAuthenticated(),
                ],
            ],
            // Change password: token is not here
            [
                'method' => 'get',
                'action' => 'changePassword',
                'params' => [
                    'route' => [],
                ],
                'mocks' => [],
                'expect' => [
                    'exception' => [
                        'class' => 'DvsaCommon\HttpRestJson\Exception\NotFoundException',
                        'message' => '',
                    ],
                ],
            ],
            // Change password: token is invalid
            [
                'method' => 'get',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => null,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'flashError' => PasswordResetController::ERR_CHANGE_PASS_TOKEN_NOT_FOUND,
                ],
            ],

            // Change password: token already been used
            [
                'method' => 'get',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => (new MessageDto())->setIsAcknowledged(true)->setExpiryDate('2011-04-23T12:26:19Z'),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'flashError' => sprintf(
                        PasswordResetController::ERR_CHANGE_PASS_TOKEN_BEEN_USED, 'DVSA Helpdesk', '0330 123 5654'
                    ),
                ],
            ],

            // Change password: user disabled
            [
                'method' => 'get',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => (new MessageDto())->setPerson(null)->setExpiryDate('2020-04-23T12:26:19Z'),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'flashError' => PasswordResetController::ERR_CHANGE_PASS_USER_DISABLED,
                ],
            ],

            // Change password: first load, token is ok
            [
                'method' => 'get',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => (new MessageDto())->setPerson(new PersonDto())->setExpiryDate('2020-04-23T12:26:19Z'),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],

            // Change password: post with empty password value
            [
                'method' => 'post',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => (new MessageDto())->setPerson(new PersonDto())->setExpiryDate('2020-04-23T12:26:19Z'),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'errors' => [
                        ChangePasswordFormModel::FIELD_PASS => ChangePasswordFormModel::ERR_REQUIRED,
                    ],
                ],
            ],
            // Change password: post data are ok, request service to change
            [
                'method' => 'post',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                    'post' => [
                        ChangePasswordFormModel::FIELD_PASS => 'Aa345678',
                        ChangePasswordFormModel::FIELD_PASS_CONFIRM => 'Aa345678',
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => (new MessageDto())
                            ->setPerson(new PersonDto())
                            ->setExpiryDate('2020-04-23T12:26:19Z'),
                    ],
                ],
                'expect' => [
                    'route' => AccountUrlBuilderWeb::of()->passwordChangedSuccessfullyConfirmation(self::TOKEN),
                ],
            ],
        ];
    }
}
