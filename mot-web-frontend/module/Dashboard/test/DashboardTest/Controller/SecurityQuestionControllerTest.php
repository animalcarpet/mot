<?php

namespace DashboardTest\Controller;

use Account\Service\SecurityQuestionService;
use Application\Helper\PrgHelper;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dashboard\Controller\SecurityQuestionController;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

/**
 * SecurityQuestionController Test.
 */
class SecurityQuestionControllerTest extends AbstractFrontendControllerTestCase
{
    const QUESTION_NUNMBER = 1;
    const PERSON_ID = 999999;
    const QUESTION_ONE = 'question1';
    const ANSWER = 'blah';

    /**
     * @var SecurityQuestionService
     */
    protected $securityQuestionService;

    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->securityQuestionService = XMock::of(SecurityQuestionService::class);

        $personProfileUrlGenerator = $this
            ->getMockBuilder(PersonProfileUrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this
            ->getServiceManager()
            ->setService(PersonProfileUrlGenerator::class, $personProfileUrlGenerator);

        $this->setController(
            new SecurityQuestionController($this->securityQuestionService)
        );

        $this->getController()->setServiceLocator($serviceManager);

        $this->createHttpRequestForController('SecurityQuestion');

        parent::setUp();

        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester(self::PERSON_ID));
    }

    /**
     * @dataProvider dataProviderTestActionsResultAndAccess
     */
    public function testActionsResultAndAccess($method, $action, $postData, $mocks, $expect)
    {
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

        //  --  set expected exception  --
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message']);
        }

        $result = $this->getResultForAction2($method, $action, null, null, $postData);

        //  --  check   --
        if (!empty($expect['viewModel'])) {
            $this->assertInstanceOf(ViewModel::class, $result);
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }

        if (!empty($expect['url'])) {
            $this->assertRedirectLocation2($expect['url']);
        }
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        return [
            //  --  index: access action  --
            [
                'method'   => 'get',
                'action'   => 'index',
                'postData' => [],
                'mocks'    => [],
                'expect'   => [
                    'viewModel' => true,
                ],
            ],
            //  --  index: post with valid data    --
            [
                'method'   => 'post',
                'action'   => 'index',
                'postData' => [
                    self::QUESTION_ONE => self::ANSWER,
                ],
                'mocks'    => [
                    [
                        'class'  => 'securityQuestionService',
                        'method' => 'manageSessionQuestion',
                        'params' => [],
                        'result' => true,
                    ],
                ],
                'expect'   => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated(),
                ],
            ],
            //  --  index: post with invalid data  --
            [
                'method'   => 'post',
                'action'   => 'index',
                'postData' => [
                    self::QUESTION_ONE => self::ANSWER,
                ],
                'mocks'    => [
                    [
                        'class'  => 'securityQuestionService',
                        'method' => 'manageSessionQuestion',
                        'params' => [],
                        'result' => false,
                    ],
                    [
                        'class'  => 'securityQuestionService',
                        'method' => 'getQuestionNumber',
                        'params' => [],
                        'result' => self::QUESTION_NUNMBER,
                    ],
                ],
                'expect'   => [
                    'url' => '',
                ],
            ],
            //  --  index: get with error get question  --
            [
                'method'   => 'get',
                'action'   => 'index',
                'postData' => [
                    self::QUESTION_ONE => self::ANSWER,
                ],
                'mocks'    => [
                    [
                        'class'  => 'securityQuestionService',
                        'method' => 'manageSessionQuestion',
                        'params' => [],
                        'result' => false,
                    ],
                    [
                        'class'  => 'securityQuestionService',
                        'method' => 'getQuestion',
                        'params' => [],
                        'result' => new NotFoundException('/', 'post', [], 10, 'Question not found'),
                    ],
                ],
                'expect'   => [
                    'url' => AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated(),
                ],
            ],
            //  --  index: post multi data  --
            [
                'method'   => 'post',
                'action'   => 'index',
                'postData' => [
                    self::QUESTION_ONE => self::ANSWER,
                    PrgHelper::FORM_GUID_FIELD_NAME => 'testToken',
                ],
                'mocks'    => [],
                'expect'   => [
                    'url' => 'redirectUrl',
                ],
            ],
        ];
    }
}
