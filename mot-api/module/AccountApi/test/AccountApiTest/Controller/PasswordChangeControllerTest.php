<?php

namespace AccountApiTest\Controller;

use AccountApi\Controller\PasswordChangeController;
use AccountApi\Service\TokenService;
use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class TokenControllerTest.
 */
class PasswordChangeControllerTest extends AbstractRestfulControllerTestCase
{
    const TOKEN = 'unitToken_1234';
    const PASSWORD_OK = 'Password12';

    /** @var TokenService|MockObj */
    private $mockTokenSrv;
    /** @var EntityManager|MockObj */
    private $mockEntityManager;

    protected function setUp()
    {
        $this->mockTokenSrv = XMock::of(TokenService::class);
        $this->mockEntityManager = XMock::of(EntityManager::class);

        $this->setController(
            new PasswordChangeController(
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
        return [
            // no token exception
            [
                'method' => 'post',
                'action' => null,
                'params' => [
                    'route' => null,
                    'post' => null,
                ],
                'mocks' => null,
                'expect' => [
                    'exception' => [
                        'class' => NotFoundException::class,
                        'message' => 'token not found',
                        'code' => '404',
                    ],
                ],
            ],
            // no password exception
            [
                'method' => 'post',
                'action' => null,
                'params' => [
                    'route' => null,
                    'post' => ['token' => self::TOKEN],
                ],
                'mocks' => null,
                'expect' => [
                    'exception' => [
                        'class' => NotFoundException::class,
                        'message' => 'newPassword not found',
                        'code' => '404',
                    ],
                ],
            ],
            // happy path to change password
            [
                'method' => 'post',
                'action' => null,
                'params' => [
                    'route' => null,
                    'post' => [
                        'token' => self::TOKEN,
                        'newPassword' => self::PASSWORD_OK,
                    ],
                ],
                'mocks' => null,
                'expect' => [
                    'result' => [
                        'data' => null,
                    ],
                ],
            ],
        ];
    }
}
