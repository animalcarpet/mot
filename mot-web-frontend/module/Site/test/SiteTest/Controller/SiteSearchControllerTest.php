<?php

namespace SiteTest\Controller;

use Account\ViewModel\PasswordResetFormModel;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Mapper\SiteMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Dto\Search\SiteSearchParamsDto;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommon\UrlBuilder\SiteUrlBuilderWeb;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use Report\Table\Table;
use Site\Controller\SiteSearchController;
use Zend\View\Model\ViewModel;
use Site\Service\SiteSearchService;

/**
 * Class SiteSearchControllerTest.
 */
class SiteSearchControllerTest extends AbstractFrontendControllerTestCase
{
    const QUESTION_NUNMBER = 1;
    const PERSON_ID = 999999;
    const QUESTION_ONE = 'question1';
    const ANSWER = 'blah';

    protected $mapper;
    protected $siteMapper;
    protected $service;

    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->mapper = $this->getMapperFactory();
        $this->service = XMock::of(SiteSearchService::class);

        $this->setController(
            new SiteSearchController($this->mapper, $this->service)
        );

        $this->getController()->setServiceLocator($serviceManager);

        parent::setUp();
    }

    /**
     * @dataProvider dataProviderTestActionsResultAndAccess
     */
    public function testActionsResultAndAccess($method, $action, $query, $mocks, $expect)
    {
        $result = null;

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

        $result = $this->getResultForAction2($method, $action, null, $query);

        //  --  check   --
        if (!empty($expect['viewModel'])) {
            $this->assertInstanceOf(ViewModel::class, $result);
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }

        if (!empty($expect['errors'])) {
            $this->assertInstanceOf(ViewModel::class, $result);

            /** @var PasswordResetFormModel $form */
            $form = $result->getVariable('viewModel');

            foreach ($expect['errors'] as $field => $error) {
                $this->assertEquals($error, $form->getError($field));
            }
        }

        if (!empty($expect['url'])) {
            $this->assertRedirectLocation2($expect['url']);
        }
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        return [
            //  --  search: access action  --
            [
                'method' => 'get',
                'action' => 'search',
                'postData' => [],
                'mocks' => [],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            //  --  result: get with invalid data   --
            [
                'method' => 'get',
                'action' => 'result',
                'query' => [],
                'mocks' => [],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            //  --  result: get with valid data Exact match   --
            [
                'method' => 'get',
                'action' => 'result',
                'query' => [
                    SiteSearchParamsDto::SITE_NUMBER => 'v1234',
                ],
                'mocks' => [
                    [
                        'class' => 'siteMapper',
                        'method' => 'search',
                        'params' => [],
                        'result' => (
                            new SiteListDto())
                            ->setTotalResultCount(1)
                            ->setData([['id' => 1]]),
                    ],
                ],
                'expect' => [
                    'url' => SiteUrlBuilderWeb::of(1),
                ],
            ],
            //  --  result: get with valid data No result   --
            [
                'method' => 'get',
                'action' => 'result',
                'query' => [
                    SiteSearchParamsDto::SITE_NUMBER => 'v1234',
                ],
                'mocks' => [
                    [
                        'class' => 'siteMapper',
                        'method' => 'search',
                        'params' => [],
                        'result' => (new SiteListDto())->setTotalResultCount(0),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            //  --  result: get with valid data Multiple result   --
            [
                'method' => 'get',
                'action' => 'result',
                'query' => [
                    SiteSearchParamsDto::SITE_NUMBER => 'v1234',
                ],
                'mocks' => [
                    [
                        'class' => 'siteMapper',
                        'method' => 'search',
                        'params' => [],
                        'result' => (new SiteListDto())->setTotalResultCount(2),
                    ],
                    [
                        'class' => 'service',
                        'method' => 'initTable',
                        'params' => [],
                        'result' => new Table(),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
        ];
    }

    private function getMapperFactory()
    {
        $mapperFactory = XMock::of(MapperFactory::class);

        $this->siteMapper = XMock::of(SiteMapper::class);

        $map = [
            [MapperFactory::SITE, $this->siteMapper],
        ];

        $mapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mapperFactory;
    }
}
