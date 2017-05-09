<?php

namespace AccountApiTest\Controller;

use AccountApi\Controller\ValidateUsernameController;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use PersonApi\Service\PersonService;

/**
 * Class ValidateUsernameControllerTest.
 */
class ValidateUsernameControllerTest extends AbstractRestfulControllerTestCase
{
    const USERNAME = 'tester1';

    /** @var PersonService|MockObj */
    private $mockPersonSrv;

    protected function setUp()
    {
        $this->mockPersonSrv = XMock::of(PersonService::class);

        $this->setController(new ValidateUsernameController($this->mockPersonSrv));

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
                    $this->mockPersonSrv, $mock['method'], $this->once(), $mock['result'], $mock['params']
                );
            }
        }

        //  --  set expected exception  --
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message'], $exception['code']);
        }

        $result = $this->getResultForAction($method, $action, null, $params['get']);

        //  --  check   --
        if (!empty($expect['result'])) {
            $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expect['result'], $result);
        }
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        return [
            //  --  validate: service return exception  --
            [
                'method' => 'get',
                'action' => null,
                'params' => [
                    'get' => ['username' => self::USERNAME],
                ],
                'mocks' => [
                    [
                        'method' => 'assertUsernameIsValidAndHasAnEmail',
                        'params' => self::USERNAME,
                        'result' => new NotFoundException('Person', self::USERNAME),
                    ],
                ],
                'expect' => [
                    'exception' => [
                        'class' => NotFoundException::class,
                        'message' => 'Person '.self::USERNAME.' not found',
                        'code' => NotFoundException::ERROR_CODE_NOT_FOUND,
                    ],
                ],
            ],
            //  --  validate: service return result --
            [
                'method' => 'get',
                'action' => null,
                'params' => [
                    'get' => ['username' => self::USERNAME],
                ],
                'mocks' => [
                    [
                        'method' => 'assertUsernameIsValidAndHasAnEmail',
                        'params' => self::USERNAME,
                        'result' => 1,
                    ],
                ],
                'expect' => [
                    'result' => ['data' => 1],
                ],
            ],
        ];
    }
}
