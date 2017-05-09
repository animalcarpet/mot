<?php

namespace OrganisationApiTest\Controller;

use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Controller\SiteLinkController;
use OrganisationApi\Service\SiteLinkService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Http\PhpEnvironment\Request;

class SiteLinkControllerTest extends AbstractRestfulControllerTestCase
{
    const AE_ID = 111;
    const SITE_ID = 222;
    const SITE_NR = 'S00001';
    const LINK_ID = 777;

    /**
     * @var SiteLinkService|MockObj
     */
    private $mockSiteSrv;
    /**
     * @var SiteLinkController
     */
    protected $controller;

    protected function setUp()
    {
        $this->mockSiteSrv = XMock::of(SiteLinkService::class);

        $this->controller = new SiteLinkController($this->mockSiteSrv);

        parent::setUp();
    }

    /**
     * @dataProvider dataProviderTestActionsResultAndAccess
     */
    public function testActionsResultAndAccess($method, $action, $params, $mocks, $expect)
    {
        foreach ($mocks as $mock) {
            $mockParams = ArrayUtils::tryGet($mock, 'params');

            $this->mockMethod(
                $this->mockSiteSrv, $mock['method'], $this->once(), $mock['result'], $mockParams
            );
        }

        $result = $this->getResultForAction(
            $method,
            $action,
            ArrayUtils::tryGet($params, 'route'),
            null,
            ArrayUtils::tryGet($params, 'post'),
            ArrayUtils::tryGet($params, 'put')
        );

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expect['result'], $result);
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        $srvResult = 'service result';

        $status = 'STATUS';

        return [
            //  check get list
            [
                'method' => Request::METHOD_GET,
                'action' => null,
                'params' => null,
                'mocks' => [
                    [
                        'method' => 'getApprovedUnlinkedSite',
                        'result' => $srvResult,
                    ],
                ],
                'expect' => [
                    'result' => ['data' => $srvResult],

                ],
            ],

            //  check get
            [
                'method' => Request::METHOD_GET,
                'action' => null,
                'params' => [
                    'route' => [
                        'linkId' => self::LINK_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'method' => 'get',
                        'params' => [self::LINK_ID, OrganisationSiteStatusCode::ACTIVE],
                        'result' => $srvResult,
                    ],
                ],
                'expect' => [
                    'result' => ['data' => $srvResult],
                ],
            ],

            //  check post (create)
            [
                'method' => Request::METHOD_POST,
                'action' => null,
                'params' => [
                    'route' => [
                        'id' => self::AE_ID,
                    ],
                    'post' => [
                        'siteNumber' => self::SITE_NR,
                    ],
                ],
                'mocks' => [
                    [
                        'method' => 'siteLink',
                        'params' => [self::AE_ID, self::SITE_NR],
                        'result' => $srvResult,
                    ],
                ],
                'expect' => [
                    'result' => ['data' => $srvResult],

                ],
            ],
            //  check put (update)
            [
                'method' => Request::METHOD_PUT,
                'action' => null,
                'params' => [
                    'route' => [
                        'linkId' => self::LINK_ID,
                    ],
                    'put' => $status,
                ],
                'mocks' => [
                    [
                        'method' => 'siteChangeStatus',
                        'params' => [self::LINK_ID, $status],
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
