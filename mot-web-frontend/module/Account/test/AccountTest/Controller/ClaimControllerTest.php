<?php

namespace AccountTest\Controller;

use Account\Controller\ClaimController;
use Account\Service\ClaimAccountService;
use Account\Validator\ClaimValidator;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use Zend\Session\Container;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;

/**
 * Class ClaimControllerTest
 */
class ClaimControllerTest extends AbstractFrontendControllerTestCase
{
    /** @var ClaimAccountService|MockObj $mockClaimAccountSrv */
    private $mockClaimAccountSrv;

    /** @var ClaimValidator|MockObj $mockClaimValidator */
    private $mockClaimValidator;

    /** @var Container|MockObj $mockSession */
    private $mockSession;

    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->mockSession = XMock::of(Container::class);

        $this->mockClaimAccountSrv = XMock::of(ClaimAccountService::class);
        $this->mockMethod($this->mockClaimAccountSrv, 'getSession', null, $this->mockSession);

        $this->mockClaimValidator = XMock::of(ClaimValidator::class);
        $this->mockClaimValidator->expects($this->any())
            ->method('getMessages')
            ->willReturn([]);

        $identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $identity = new Identity();
        $identity->setSecondFactorRequired(false);

        $identityProvider->expects($this->any())->method("getIdentity")->willReturn($identity);

        $this->setController(
            new ClaimController(
                $this->mockClaimAccountSrv,
                $this->mockClaimValidator,
                $identityProvider
            )
        );

        $this->getController()->setServiceLocator($serviceManager);

        $this->createHttpRequestForController('Claim');

        parent::setUp();

        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->getResultForAction2('get', 'index');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @dataProvider dataProviderTestActionsResultAndAccess
     *
     * @param $method
     * @param $action
     * @param $params
     * @param $mocks
     * @param $expect
     */
    public function testActionsResultAndAccess($method, $action, $params, $mocks, $expect)
    {
        $result = null;

        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $this->mockMethod(
                    $this->{$mock['class']},
                    $mock['method'],
                    ArrayUtils::tryGet($mock, 'invocation', $this->once()),
                    ArrayUtils::tryGet($mock, 'result'),
                    ArrayUtils::tryGet($mock, 'params')
                );
            }
        }

        // set expected exception
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message']);
        }

        $result = $this->getResultForAction2(
            $method, $action, ArrayUtils::tryGet($params, 'route'), null, ArrayUtils::tryGet($params, 'post')
        );

        // check
        if (!empty($expect['viewModel'])) {
            $this->assertInstanceOf(ViewModel::class, $result);
            $this->assertResponseStatus(self::HTTP_OK_CODE);

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
        $postData = [
            'field1' => 'value1',
        ];

        $postParameters = new Parameters();
        $postParameters->fromArray($postData);

        $sessionData = [
            ClaimController::STEP_1_NAME => [],
            ClaimController::STEP_2_NAME => [],
        ];

        return [
            // reset: access action
            [
                'method' => 'get',
                'action' => 'reset',
                'params' => [],
                'mocks'  => [
                    [
                        'class'  => 'mockClaimAccountSrv',
                        'method' => 'clearSession',
                    ],
                ],
                'expect' => [
                    'url' => AccountUrlBuilderWeb::claimEmailAndPassword(),
                ],
            ],

            // confirmPassword: get: access action
            [
                'method' => 'get',
                'action' => 'confirmPassword',
                'params' => [],
                'mocks'  => [],
                'expect' => [
                    'viewModel' => true,
                ],
            ],

            // confirmPassword: post: validation failed
            [
                'method' => 'post',
                'action' => 'confirmPassword',
                'params' => [
                    'post' => $postData,
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockClaimValidator',
                        'method' => 'validateStep',
                        'params' => [ClaimController::STEP_1_NAME, $postParameters],
                        'result' => false,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],

            // confirmPassword: post: validation success
            [
                'method' => 'post',
                'action' => 'confirmPassword',
                'params' => [
                    'post' => $postData,
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockClaimValidator',
                        'method' => 'validateStep',
                        'params' => [ClaimController::STEP_1_NAME, $postParameters],
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'url' => AccountUrlBuilderWeb::claimSecurityQuestions(),
                ],
            ],

            // setSecurityQuestion: get: come back to STEP1 because not stored
            [
                'method' => 'get',
                'action' => 'setSecurityQuestion',
                'params' => [],
                'mocks'  => [
                    [
                        'class'  => 'mockClaimAccountSrv',
                        'method' => 'isStepRecorded',
                        'params' => [ClaimController::STEP_1_NAME],
                        'result' => false,
                    ],
                ],
                'expect' => [
                    'url' => AccountUrlBuilderWeb::claimEmailAndPassword(),
                ],
            ],

            // setSecurityQuestion: get: access action
            [
                'method' => 'get',
                'action' => 'setSecurityQuestion',
                'params' => [],
                'mocks'  => [
                    [
                        'class'  => 'mockClaimAccountSrv',
                        'method' => 'isStepRecorded',
                        'params' => [ClaimController::STEP_1_NAME],
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],

            // setSecurityQuestion: post: validation failed
            [
                'method' => 'post',
                'action' => 'setSecurityQuestion',
                'params' => [
                    'post' => $postData,
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockClaimAccountSrv',
                        'method' => 'isStepRecorded',
                        'params' => [ClaimController::STEP_1_NAME],
                        'result' => true,
                    ],
                    [
                        'class'  => 'mockClaimValidator',
                        'method' => 'validateStep',
                        'params' => [ClaimController::STEP_2_NAME, $postParameters],
                        'result' => false,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],

            // setSecurityQuestion: post: validation success
            [
                'method' => 'post',
                'action' => 'setSecurityQuestion',
                'params' => [
                    'post' => $postData,
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockClaimValidator',
                        'method' => 'validateStep',
                        'params' => [ClaimController::STEP_2_NAME, $postParameters],
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'url' => AccountUrlBuilderWeb::claimReview(),
                ],
            ],

            // review: get: come back to STEP1 because not stored
            [
                'method' => 'get',
                'action' => 'review',
                'params' => [],
                'mocks'  => [
                    [
                        'class'  => 'mockClaimAccountSrv',
                        'method' => 'isStepRecorded',
                        'params' => [ClaimController::STEP_1_NAME],
                        'result' => false,
                    ],
                ],
                'expect' => [
                    'url' => AccountUrlBuilderWeb::claimEmailAndPassword(),
                ],
            ],

            // review: get: access action
            [
                'method' => 'get',
                'action' => 'review',
                'params' => [],
                'mocks' => [
                    [
                        'class'  => 'mockClaimValidator',
                        'method' => 'validateStep',
                        'invocation' => $this->any(),
                        'result' => $this->returnValueMap(
                            [
                                [ClaimController::STEP_1_NAME, $sessionData[ClaimController::STEP_1_NAME], true, true],
                                [ClaimController::STEP_2_NAME, $sessionData[ClaimController::STEP_2_NAME], true, false],
                            ]
                        ),
                    ],
                    [
                        'class'  => 'mockClaimAccountSrv',
                        'method' => 'isStepRecorded',
                        'invocation' => $this->any(),
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],

            // review: post: validation failed
            [
                'method' => 'post',
                'action' => 'review',
                'params' => [],
                'mocks'  => [
                    [
                        'class'  => 'mockSession',
                        'method' => 'getArrayCopy',
                        'result' => $sessionData,
                    ],
                    [
                        'class'  => 'mockClaimValidator',
                        'method' => 'validateStep',
                        'invocation' => $this->any(),
                        'result' => $this->returnValueMap(
                            [
                                [ClaimController::STEP_1_NAME, $sessionData[ClaimController::STEP_1_NAME], true, true],
                                [ClaimController::STEP_2_NAME, $sessionData[ClaimController::STEP_2_NAME], true, false],
                            ]
                        ),
                    ],
                    [
                        'class'  => 'mockClaimValidator',
                        'method' => 'getMessages',
                        'invocation' => $this->any(),
                        'result' => [],
                    ],

                    [
                        'class'  => 'mockClaimAccountSrv',
                        'method' => 'isStepRecorded',
                        'invocation' => $this->any(),
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],

            // review: post: both step validation success, send to api and goto home
            [
                'method' => 'post',
                'action' => 'review',
                'params' => [
                    'post' => $postData,
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockSession',
                        'method' => 'getArrayCopy',
                        'result' => $sessionData,
                    ],
                    [
                        'class'  => 'mockClaimValidator',
                        'method' => 'validateStep',
                        'invocation' => $this->any(),
                        'result' => true,
                    ],
                    [
                        'class'  => 'mockClaimValidator',
                        'method' => 'getMessages',
                        'invocation' => $this->any(),
                        'result' => [],
                    ],
                    [
                        'class'  => 'mockClaimAccountSrv',
                        'method' => 'isStepRecorded',
                        'invocation' => $this->any(),
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'url' => '/account/claim/success',
                ],
            ],


            // generatePin: get: access action
            [
                'method' => 'get',
                'action' => 'success',
                'params' => [],
                'mocks'  => [
                    [
                        'class'  => 'mockClaimAccountSrv',
                        'method' => 'isStepRecorded',
                        'invocation' => $this->any(),
                        'result' => true,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
        ];
    }
}
