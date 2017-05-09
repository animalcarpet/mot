<?php

namespace AccountApiTest\Controller;

use AccountApi\Controller\PasswordResetController;
use AccountApi\Service\TokenService;
use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class TokenControllerTest.
 */
class PasswordResetControllerTest extends AbstractRestfulControllerTestCase
{
    const USER_NAME = 'unitUserName';
    const TOKEN = 'unitToken_1234';

    /** @var TokenService|MockObj */
    private $mockTokenSrv;
    /** @var EntityManager|MockObj */
    private $mockEntityManager;

    protected function setUp()
    {
        $this->mockTokenSrv = XMock::of(TokenService::class);
        $this->mockEntityManager = XMock::of(EntityManager::class);

        $this->setController(
            new PasswordResetController(
                $this->mockTokenSrv,
                $this->mockEntityManager
            )
        );

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
                    $this->mockTokenSrv, $mock['method'], $this->once(), $mock['result'], $mock['params']
                );
            }
        }

        //  --  set expected exception  --
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message'], $exception['code']);
        }

        $result = $this->getResultForAction($method, $action, $params['route'], null, $params['post']);

        //  --  check   --
        if (!empty($expect['result'])) {
            $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expect['result'], $result);
        }
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        $exceptionMessage = 'Some exception happens in service';
        $srvResult = 'service result';

        return [
            //  --  password: service return exception  --
            [
                'method' => 'post',
                'action' => null,
                'params' => [
                    'route' => null,
                    'post' => ['userId' => self::MOCK_USER_ID],
                ],
                'mocks' => [
                    [
                        'method' => 'createTokenAndEmailForgottenLink',
                        'params' => [self::MOCK_USER_ID],
                        'result' => new ServiceException($exceptionMessage, ServiceException::DEFAULT_STATUS_CODE),
                    ],
                ],
                'expect' => [
                    'exception' => [
                        'class' => ServiceException::class,
                        'message' => $exceptionMessage,
                        'code' => ServiceException::DEFAULT_STATUS_CODE,
                    ],
                ],
            ],
            //  --  password: service return result --
            [
                'method' => 'post',
                'action' => null,
                'params' => [
                    'route' => null,
                    'post' => ['userId' => self::MOCK_USER_ID],
                ],
                'mocks' => [
                    [
                        'method' => 'createTokenAndEmailForgottenLink',
                        'params' => [self::MOCK_USER_ID],
                        'result' => $srvResult,
                    ],
                ],
                'expect' => [
                    'result' => ['data' => $srvResult],
                ],
            ],
            //  --  validate: service return exception  --
            [
                'method' => 'get',
                'action' => null,
                'params' => [
                    'route' => ['token' => self::TOKEN],
                    'post' => null,
                ],
                'mocks' => [
                    [
                        'method' => 'getToken',
                        'params' => self::TOKEN,
                        'result' => new NotFoundException('Message by Token', self::TOKEN),
                    ],
                ],
                'expect' => [
                    'exception' => [
                        'class' => NotFoundException::class,
                        'message' => 'Message by Token '.self::TOKEN.' not found',
                        'code' => NotFoundException::ERROR_CODE_NOT_FOUND,
                    ],
                ],
            ],
            //  --  validate: service return result --
            [
                'method' => 'get',
                'action' => null,
                'params' => [
                    'route' => ['token' => self::TOKEN],
                    'post' => null,
                ],
                'mocks' => [
                    [
                        'method' => 'getToken',
                        'params' => self::TOKEN,
                        'result' => $srvResult,
                    ],
                ],
                'expect' => [
                    'result' => ['data' => $srvResult],
                ],
            ],
        ];
    }
}
