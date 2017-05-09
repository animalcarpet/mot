<?php

namespace SiteApiTest\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\MotTestInProgressController;
use SiteApi\Service\MotTestInProgressService;
use Zend\Http\PhpEnvironment\Request;

class MotTestInProgressControllerTest extends AbstractRestfulControllerTestCase
{
    const SITE_ID = 1;

    private $mockService;

    protected function setUp()
    {
        $this->mockService = XMock::of(MotTestInProgressService::class);
        $this->setController(new MotTestInProgressController($this->mockService));

        parent::setUp();
    }

    /**
     * @dataProvider dataProviderTestActionsResultAndAccess
     */
    public function testActionsResultAndAccess($action, $params, $mocks, $expect)
    {
        foreach ($mocks as $mock) {
            $this->mockMethod(
                $this->mockService, $mock['method'], $this->once(), $mock['result'], $mock['params']
            );
        }

        $result = $this->getResultForAction(Request::METHOD_GET, $action, $params['route']);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expect['result'], $result);
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        $srvResult = 'service result';

        return [
            //  check post (create)
            [
                'action' => null,
                'params' => [
                    'route' => [
                        'id' => self::SITE_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'method' => 'getAllForSite',
                        'params' => [self::SITE_ID],
                        'result' => $srvResult,
                    ],
                ],
                'expect' => [
                    'result' => ['data' => $srvResult],

                ],
            ],
            //  check put (update)
            [
                'action' => 'count',
                'params' => [
                    'route' => [
                        'id' => self::SITE_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'method' => 'getCountForSite',
                        'params' => [self::SITE_ID],
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
