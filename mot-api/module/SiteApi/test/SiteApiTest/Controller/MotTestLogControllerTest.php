<?php

namespace SiteApiTest\Controller;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\Dto\Site\MotTestLogSummaryDto;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaElasticSearch\Service\ElasticSearchService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use SiteApi\Controller\MotTestLogController;
use SiteApi\Service\MotTestLogService;

class MotTestLogControllerTest extends AbstractRestfulControllerTestCase
{
    const CONTENT_TYPE_JSON = 'application/json; charset=utf-8';

    const AE_ID = 1;

    /**
     * @var MotTestLogService|MockObj
     */
    private $mockMotTestLogSrv;
    /**
     * @var ElasticSearchService|MockObj
     */
    private $mockEsSrv;
    /**
     * @var EntityManager
     */
    private $mockEntityManager;

    protected function setUp()
    {
        $this->mockMotTestLogSrv = XMock::of(MotTestLogService::class);
        $this->mockEsSrv = XMock::of(ElasticSearchService::class);
        $this->mockEntityManager = XMock::of(EntityManager::class);

        $this->setController(
            new MotTestLogController(
                $this->mockMotTestLogSrv,
                $this->mockEsSrv,
                $this->mockEntityManager
            )
        );

        parent::setUp();
    }

    /**
     * Test access for specified action and parameters.
     *
     * @param string $method HTTP request type (get, post, put)
     * @param string $action Route action
     * @param array  $params Route, post parameters
     * @param array  $mocks  Service Mocks
     * @param array  $expect Expected result
     *
     * @dataProvider dataProviderTestActionsResultAndAccess
     */
    public function testActionsResultAndAccess($method, $action, $params, $mocks, $expect)
    {
        $result = null;

        //  logic block: mock
        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $this->mockMethod(
                    $this->{$mock['class']}, $mock['method'], $this->once(), $mock['result'], $mock['params']
                );
            }
        }

        //  logic block: check exception
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message'], $exception['code']);
        }

        //  logic block: call
        if (isset($params['postContent'])) {
            $this->request->setContent(json_encode($params['postContent']));
            $this->request->getHeaders()->addHeaderLine('Content-Type', self::CONTENT_TYPE_JSON);
        }

        $result = $this->getResultForAction($method, $action, $params['route']);

        //  logic block: check
        if (isset($expect['error'])) {
            $this->assertResponseStatusAndResultHasError(
                $this->getController()->getResponse(),
                $expect['statusCode'],
                $result,
                $expect['error']['message'],
                $expect['error']['code']
            );
        } else {
            $this->assertResponseStatusAndResult($expect['statusCode'], $expect['result'], $result);
        }
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        $dto = (new MotTestLogSummaryDto())
            ->setYear(1024)
            ->setMonth(1);

        return [
            // get summary
            [
                'method' => 'get',
                'action' => 'summary',
                'params' => [
                    'route' => [
                        'id' => self::AE_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockMotTestLogSrv',
                        'method' => 'getMotTestLogSummaryForSite',
                        'params' => self::AE_ID,
                        'result' => $dto,
                    ],
                ],
                'expect' => [
                    'statusCode' => self::HTTP_OK_CODE,
                    'result' => ['data' => DtoHydrator::dtoToJson($dto)],
                ],
            ],

            //  get log data :: invalid id
            [
                'method' => 'post',
                'action' => 'logData',
                'params' => [
                    'route' => [
                        'id' => 'invalidId',
                    ],
                ],
                'mocks' => [],
                'expect' => [
                    'statusCode' => self::HTTP_ERR_400,
                    'error' => [
                        'message' => MotTestLogController::ERR_SITE_ID,
                        'code' => AbstractDvsaRestfulController::ERROR_CODE_REQUIRED,
                    ],
                ],
            ],
            //  get log data :: valid id
            [
                'method' => 'post',
                'action' => 'logData',
                'params' => [
                    'route' => [
                        'id' => self::AE_ID,
                    ],
                    'postContent' => DtoHydrator::dtoToJson(new MotTestSearchParamsDto()),
                ],
                'mocks' => [
                    [
                        'class' => 'mockEsSrv',
                        'method' => 'findSiteTestsLog',
                        'params' => null,
                        'result' => 'SERVICE RESULT',
                    ],
                ],
                'expect' => [
                    'statusCode' => self::HTTP_OK_CODE,
                    'result' => ['data' => 'SERVICE RESULT'],
                ],
            ],
        ];
    }
}
