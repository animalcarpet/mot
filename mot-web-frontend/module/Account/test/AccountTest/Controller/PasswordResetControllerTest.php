<?php

namespace AccountTest\Controller;

use Account\Controller\PasswordResetController;
use Account\Service\PasswordResetService;
use Account\ViewModel\ChangePasswordFormModel;
use Account\ViewModel\PasswordResetFormModel;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Entity\Person;
use DvsaClient\Mapper\AccountMapper;
use DvsaClient\Mapper\PersonMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Dto\Account\MessageDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\View\Model\ViewModel;

/**
 * Class PasswordResetControllerTest
 *
 * @package AccountTest\Controller
 */
class PasswordResetControllerTest extends AbstractFrontendControllerTestCase
{
    const USER_NAME = 'username';
    const TOKEN = 'TOKEN_123456789';
    const PERSON_ID = 999999;

    /** @var  MapperFactory|MockObj */
    private $mockMapperFactory;
    /** @var  UserAdminSessionManager|MockObj */
    private $mockSessionManager;
    /** @var  PasswordResetService|MockObj */
    private $mockPasswordResetSrv;
    /** @var  AccountMapper|MockObj */
    private $mockAccountMapper;
    /** @var  ParamObfuscator */
    private $mockObfuscator;

    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager = clone $serviceManager;
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->mockMapperFactory = $this->getMapperFactory();
        $this->mockSessionManager = XMock::of(UserAdminSessionManager::class);
        $this->mockPasswordResetSrv = XMock::of(PasswordResetService::class);
        $this->mockObfuscator = XMock::of(ParamObfuscator::class);

        $config = [
            PasswordResetController::CFG_PASSWORD_RESET => [
                PasswordResetController::CFG_PASSWORD_RESET_EXPIRE_TIME => 5400,
            ],
        ];

        $this->setController(
            new PasswordResetController(
                $this->mockPasswordResetSrv,
                $this->mockSessionManager,
                $this->mockMapperFactory,
                $config,
                $this->mockObfuscator
            )
        );

        $this->getController()->setServiceLocator($serviceManager);
        $serviceManager->setService('config', ['helpdesk'=> [ 'name' => 'DVSA Helpdesk', 'phoneNumber' => '0330 123 5654']]);

        $this->createHttpRequestForController('Reset');

        parent::setUp();
    }

    /**
     * @dataProvider dataProviderTestActionsResultAndAccess
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

        //  --  set expected exception  --
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

        //  --  check   --
        if (!empty($expect['viewModel'])) {
            $this->assertInstanceOf(ViewModel::class, $result);
            $this->assertResponseStatus(self::HTTP_OK_CODE);

        }

        if (!empty($expect['errors'])) {
            /** @var  PasswordResetFormModel $form */
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

    public function dataProviderTestActionsResultAndAccess()
    {
        return [
            //  --  username: access action  --
            [
                'method' => 'get',
                'action' => 'username',
                'params' => [
                    'post' => [],
                ],
                'mocks'  => [],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            //  --  username: post with empty data    --
            [
                'method' => 'post',
                'action' => 'username',
                'params' => [
                    'post' => [],
                ],
                'mocks'  => [],
                'expect' => [
                    'viewModel' => true,
                    'errors'    => [
                        PasswordResetFormModel::FIELD_USERNAME => PasswordResetFormModel::USER_REQUIRED,
                    ],
                ],
            ],
            //  --  username: post with invalid data  --
            [
                'method' => 'post',
                'action' => 'username',
                'params' => [
                    'post' => [
                        PasswordResetFormModel::FIELD_USERNAME => 'NOT_EXIST',
                    ],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockPasswordResetSrv',
                        'method' => 'validateUsername',
                        'params' => 'NOT_EXIST',
                        'result' => new NotFoundException('/', 'post', [], 10, 'Person not found'),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'errors'    => [
                        PasswordResetFormModel::FIELD_USERNAME => PasswordResetFormModel::USER_NOT_FOUND,
                    ],
                ],
            ],
            //  --  username: post success and redirect --
            [
                'method' => 'post',
                'action' => 'username',
                'params' => [
                    'post' => [
                        PasswordResetFormModel::FIELD_USERNAME => self::USER_NAME,
                    ],
                ],
                'mocks'    => [
                    [
                        'class'  => 'mockPasswordResetSrv',
                        'method' => 'validateUsername',
                        'params' => self::USER_NAME,
                        'result' => self::PERSON_ID,
                    ],
                ],
                'expect' => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordSecurityQuestion(
                        self::PERSON_ID,
                        UserAdminSessionManager::FIRST_QUESTION
                    ),
                ],
            ],
            //  --  username: post success but no email --
            [
                'method'   => 'post',
                'action'   => 'username',
                'params' => [
                    'post' => [
                        PasswordResetFormModel::FIELD_USERNAME => self::USER_NAME,
                    ]
                ],
                'mocks'    => [
                    [
                        'class'  => 'mockPasswordResetSrv',
                        'method' => 'validateUsername',
                        'params' => self::USER_NAME,
                        'result' => false,
                    ],
                ],
                'expect'   => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordEmailNotFound(),
                ],
            ],
            //  --  authenticate: get not auth --
            [
                'method'   => 'get',
                'action'   => 'authenticated',
                'params' => [],
                'mocks'    => [
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => false,
                    ],
                ],
                'expect' => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated()
                ],
            ],
            //  --  authenticate: get email sent --
            [
                'method'   => 'get',
                'action'   => 'authenticated',
                'params' => [],
                'mocks'    => [
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'getElementOfUserAdminSession',
                        'call'   => $this->any(),
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordConfirmation()
                ],
            ],            //  --  authenticate: get is auth, service return exception --
            [
                'method'   => 'get',
                'action'   => 'authenticated',
                'params' => [],
                'mocks'    => [
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                    [
                        'class'  => 'mockAccountMapper',
                        'method' => 'resetPassword',
                        'params' => [],
                        'result' => new NotFoundException('/', 'post', [], 10, 'Token not found'),
                    ],

                ],
                'expect' => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated()
                ],
            ],
            //  --  authenticate: get is auth, service return success   --
            [
                'method'   => 'get',
                'action'   => 'authenticated',
                'params' => [],
                'mocks'    => [
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                    [
                        'class'  => 'mockAccountMapper',
                        'method' => 'resetPassword',
                        'params' => [],
                        'result' => ['token' => self::TOKEN],
                    ],

                ],
                'expect' => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordConfirmation(),
                ],
            ],
            //  --  confirmation: get not auth --
            [
                'method'   => 'get',
                'action'   => 'confirmation',
                'params' => [],
                'mocks'    => [
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => false,
                    ],
                ],
                'expect'   => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated(),
                ],
            ],
            //  --  confirmation: get is auth, service return exception --
            [
                'method'   => 'get',
                'action'   => 'confirmation',
                'params' => [],
                'mocks'    => [
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect'   => [
                    'viewModel' => true,
                ],
            ],
            //  --  not authenticated --
            [
                'method'   => 'get',
                'action'   => 'notAuthenticated',
                'params' => [],
                'mocks'    => [
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => false,
                    ],
                ],
                'expect'   => [
                    'viewModel' => true,
                ],
            ],
            //  --  not authenticated: but authenticate in fact --
            [
                'method'   => 'get',
                'action'   => 'notAuthenticated',
                'params' => [],
                'mocks'    => [
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect'   => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordAuthenticated(),
                ],
            ],
            //  --  email not found --
            [
                'method'   => 'get',
                'action'   => 'emailNotFound',
                'params' => [],
                'mocks'    => [
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => false,
                    ],
                ],
                'expect'   => [
                    'viewModel' => true,
                ],
            ],
             //  --  email not found: but authenticate in fact --
            [
                'method'   => 'get',
                'action'   => 'emailNotFound',
                'params' => [],
                'mocks'    => [
                    [
                        'class'  => 'mockSessionManager',
                        'method' => 'isUserAuthenticated',
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect'   => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordAuthenticated(),
                ],
            ],
            //  --  change password: token is not here --
            [
                'method' => 'get',
                'action' => 'changePassword',
                'params' => [
                    'route' => [],
                ],
                'mocks'  => [],
                'expect' => [
                    'exception' => [
                        'class' => 'DvsaCommon\HttpRestJson\Exception\NotFoundException',
                        'message' => '',
                    ],
                ],
            ],
            //  --  change password: token is invalid --
            [
                'method' => 'get',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => null,
                    ],
                ],
                'expect' => [
                    'viewModel'  => true,
                    'flashError' => PasswordResetController::ERR_CHANGE_PASS_TOKEN_NOT_FOUND,
                ],
            ],

            //  --  change password: token already been used --
            [
                'method' => 'get',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => (new MessageDto())->setIsAcknowledged(true)->setExpiryDate('2011-04-23T12:26:19Z'),
                    ],
                ],
                'expect' => [
                    'viewModel'  => true,
                    'flashError' => sprintf(
                        PasswordResetController::ERR_CHANGE_PASS_TOKEN_BEEN_USED, 'DVSA Helpdesk', '0330 123 5654'
                    ),
                ],
            ],

            //  --  change password: user disabled  --
            [
                'method' => 'get',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => (new MessageDto())->setPerson(null)->setExpiryDate('2020-04-23T12:26:19Z'),
                    ],
                ],
                'expect' => [
                    'viewModel'  => true,
                    'flashError' => PasswordResetController::ERR_CHANGE_PASS_USER_DISABLED,
                ],
            ],

            //  --  change password: first load, token is ok --
            [
                'method' => 'get',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => (new MessageDto())->setPerson(new PersonDto)->setExpiryDate('2020-04-23T12:26:19Z'),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],

            //  --  change password: post with empty password value --
            [
                'method' => 'post',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => (new MessageDto())->setPerson(new PersonDto)->setExpiryDate('2020-04-23T12:26:19Z'),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'errors'    => [
                        ChangePasswordFormModel::FIELD_PASS => ChangePasswordFormModel::ERR_REQUIRED,
                    ],
                ],
            ],
            //  --  change password: post data are ok, request service to change --
            [
                'method' => 'post',
                'action' => 'changePassword',
                'params' => [
                    'route' => [
                        'resetToken' => self::TOKEN,
                    ],
                    'post'  => [
                        ChangePasswordFormModel::FIELD_PASS         => 'Aa345678',
                        ChangePasswordFormModel::FIELD_PASS_CONFIRM => 'Aa345678',
                    ],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockPasswordResetSrv',
                        'method' => 'getToken',
                        'params' => [self::TOKEN],
                        'result' => (new MessageDto())
                            ->setPerson(new PersonDto())
                            ->setExpiryDate('2020-04-23T12:26:19Z'),
                    ],
                ],
                'expect' => [
                    'expect' => [
                        'url' => UrlBuilder::of()->home(),
                    ],
                ],
            ],
        ];
    }

    private function getMapperFactory()
    {
        $mapperFactory = XMock::of(MapperFactory::class);

        $this->mockAccountMapper = XMock::of(AccountMapper::class);

        $map = [
            [MapperFactory::ACCOUNT, $this->mockAccountMapper],
            [MapperFactory::PERSON, $this->getPersonMapperMock()],
        ];

        $mapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mapperFactory;
    }

    private function getPersonMapperMock()
    {
        $person = new Person();
        $person->setId(self::PERSON_ID);

        $mapper = XMock::of(PersonMapper::class);
        $this->mockMethod($mapper, 'getByIdentifier', null, $person, self::USER_NAME);

        return $mapper;
    }
}
