<?php

namespace SiteApiTest\Controller;

use DvsaCommon\Dto\Search\SiteSearchParamsDto;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use SiteApi\Controller\SiteSearchController;
use SiteApi\Service\SiteSearchService;

/**
 * Class SiteSearchControllerTest.
 */
class SiteSearchControllerTest extends AbstractRestfulControllerTestCase
{
    const SITE_NAME = 'unitSiteName';

    /** @var SiteSearchService|MockObj */
    private $siteSearchService;

    protected function setUp()
    {
        $this->siteSearchService = XMock::of(SiteSearchService::class);

        $this->setController(new SiteSearchController($this->siteSearchService));

        parent::setUp();
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
                    $this->siteSearchService, $mock['method'], $this->once(), $mock['result'], $mock['params']
                );
            }
        }

        $result = $this->getResultForAction($method, $action, $params['route'], null, $params['post']);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expect['result'], $result);
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        return [
            //  --  create: access to the create action  --
            [
                'method' => 'post',
                'action' => null,
                'params' => [
                    'route' => null,
                    'post' => [
                        'siteName' => self::SITE_NAME,
                        '_class' => SiteSearchParamsDto::class,
                    ],
                ],
                'mocks' => [
                    [
                        'method' => 'findSites',
                        'params' => (new SiteSearchParamsDto())->setSiteName(self::SITE_NAME),
                        'result' => (new SiteListDto())
                            ->setTotalResultCount(0),
                    ],
                ],
                'expect' => [
                    'result' => [
                        'data' => [
                            '_class' => SiteListDto::class,
                            'totalResultCount' => 0,
                            'data' => null,
                            'searched' => null,
                        ],
                    ],
                ],
            ],
        ];
    }
}
