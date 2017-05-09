<?php

namespace DvsaMotTestTest\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\Mapper\MotTestLogMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Organisation\MotTestLogSummaryDto;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Controller\TesterMotTestLogController;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

class TesterMotTestLogControllerTest extends AbstractFrontendControllerTestCase
{
    use TestCasePermissionTrait;

    private static $testerId = 1;

    /** @var DtoHydrator $dtoHydrator */
    private $dtoHydrator;

    /** @var MotFrontendAuthorisationServiceInterface|MockObj $mockAuthSrv */
    private $mockAuthSrv;

    /** @var MapperFactory|MockObj $mockMapperFactory */
    private $mockMapperFactory;

    /** @var MotTestLogMapper|MockObj $mockMotTestLogMapper */
    private $mockMotTestLogMapper;

    /** @var ContextProvider|MockObj $mockContextProvider */
    private $mockContextProvider;

    /** @var FeatureToggles|MockObj $mockFeatureToggles */
    private $mockFeatureToggles;

    public function setUp()
    {
        $this->dtoHydrator = new DtoHydrator();

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->mockMapperFactory = $this->getMapperFactory();
        $this->mockAuthSrv = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->mockContextProvider = XMock::of(ContextProvider::class);
        $this->mockFeatureToggles = XMock::of(FeatureToggles::class);

        $this->setController(
            new TesterMotTestLogController(
                $this->mockAuthSrv,
                $this->mockMapperFactory,
                $this->mockFeatureToggles,
                $this->mockContextProvider
            )
        );

        $this->getController()->setServiceLocator($serviceManager);

        $this->createHttpRequestForController('MotTestLog');

        parent::setUp();

        $this->mockMethod(
            $this->mockContextProvider,
            'getContext',
            $this->any(),
            ContextProvider::YOUR_PROFILE_CONTEXT
        );

        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
    }

    public function testIndexAction()
    {
        $motTestLogSummaryDto = new MotTestLogSummaryDto();

        $this->mockMethod(
            $this->mockMotTestLogMapper,
            'getTesterSummary',
            $this->any(),
            $motTestLogSummaryDto
        );

        $searchResultDto = new SearchResultDto();
        $searchParamsDto = new SearchParamsDto();
        $searchResultDto->setSearched($searchParamsDto);

        $this->mockMethod(
            $this->mockMotTestLogMapper,
            'getTesterData',
            $this->any(),
            $searchResultDto
        );

        $this->getResultForAction2('get', 'index', [], ['_csrf_token' => true]);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test creation of CVS file.
     *
     * @throws \Exception
     */
    public function testDownloadCsv()
    {
        $apiResult = [
            '1' => [
                'testNumber' => '1234',
                'testDateTime' => '2014-12-10 23:59:59.000000',
                'emRecDateTime' => '2013-12-11 23:59:59.000000',
                'vehicleVIN' => 123456789012345,
                'vehicleModel' => '100',
                'emCode' => null,
                'column11' => 'row1 col1',
                'column12' => 99999,
                'column13' => 'row1 col2',
            ],
            '2' => [
                'testNumber' => '1234',
                'testDateTime' => '2014-12-10 23:59:59.000000',
                'emRecDateTime' => '2013-12-11 23:59:59.000000',
                'vehicleVIN' => 123456789012345,
                'vehicleModel' => 200,
                'emCode' => '1',
                'column21' => 'row2 col1',
                'column22' => 88888,
                'column23' => 'row2 col2',
            ],
            '3' => [
                'testNumber' => '1234',
                'testDateTime' => '2014-12-10 23:59:59.000000',
                'emRecDateTime' => null,
                'vehicleVIN' => 123456789012345,
                'vehicleModel' => '9-4',
                'emCode' => '1',
                'column21' => 'row2 col1',
                'column22' => 88888,
                'column23' => 'row2 col2',
            ],
        ];

        $expectCsvResult = array_replace_recursive(
            $apiResult,
            [
                1 => [
                    'testDateTime' => '10/12/2014 23:59:59',
                    'emRecDateTime' => '11/12/2013 23:59:59',
                    'vehicleModel' => '="100"',
                    'testNumber' => '="1234"',
                    'vehicleVIN' => '="123456789012345"',
                ],
                2 => [
                    'testDateTime' => '10/12/2014',
                    'emRecDateTime' => '11/12/2013 23:59:59',
                    'vehicleModel' => '="200"',
                    'testNumber' => '="1234"',
                    'vehicleVIN' => '="123456789012345"',
                ],
                3 => [
                    'testDateTime' => '10/12/2014',
                    'emRecDateTime' => null,
                    'vehicleModel' => '="9-4"',
                    'testNumber' => '="1234"',
                    'vehicleVIN' => '="123456789012345"',
                ],
            ]
        );

        $resultDto = new SearchResultDto();
        $resultDto
            ->setData($apiResult)
            ->setResultCount(count($apiResult))
            ->setTotalResultCount(9999);

        $this->mockMethod($this->mockMotTestLogMapper, 'getTesterData', null, $resultDto);

        $queryParams = [
            SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM => (new \DateTime('2013-12-11'))->getTimestamp(),
            SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM => (new \DateTime('2014-03-02'))->getTimestamp(),
        ];

        // Turn on output buffering and catch data being written to php://output
        ob_start();
        $this->getResultForAction2('get', 'downloadCsv', ['id' => self::$testerId], $queryParams);
        $output = ob_get_clean();

        /** @var $response \Zend\Http\PhpEnvironment\Response */
        $response = $this->getController()->getResponse();
        $this->assertResponseStatus(self::HTTP_OK_CODE, $response);
        $this->assertTrue($response->headersSent());

        $csvBuffer = fopen('php://memory', 'w');
        foreach (([TesterMotTestLogController::$CSV_COLUMNS] + $expectCsvResult) as $line) {
            fputcsv($csvBuffer, $line);
        }

        rewind($csvBuffer);
        $expectContent = stream_get_contents($csvBuffer);
        fclose($csvBuffer);

        $this->assertEquals($expectContent, $output);

        $expectHeaders = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="test-log-11122013-02032014.csv"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'max-age=0, must-revalidate, no-cache, no-store',
            'Pragma' => 'no-cache',
        ];

        $headers = $response->getHeaders();
        foreach ($expectHeaders as $name => $value) {
            $this->assertEquals($value, $headers->get($name)->getFieldValue());
        }
    }

    /**
     * Test class methods getTesterLogDataBySearchCriteria and getTesterLogSummary.
     *
     * @dataProvider dataProviderTestGetLogX
     */
    public function testGetMotLogX($classMethod, $mocks, $expect)
    {
        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $this->mockMethod(
                    $this->{$mock['class']}, $mock['method'], $this->once(), $mock['result'], $mock['params']
                );
            }
        }

        //  logical block: call
        $result = XMock::invokeMethod($this->getController(), $classMethod['name'], $classMethod['params']);

        //  logical block: check
        if (!empty($expect['errorMsg'])) {
            $this->assertSame(
                $expect['errorMsg'],
                $this->controller->flashMessenger()->getCurrentErrorMessages()[0]
            );
        }

        $this->assertSame($expect['return'], $result);
    }

    public function dataProviderTestGetLogX()
    {
        $paramsDto = $this->getSearchParams();

        return [
            //  getTesterLogDataBySearchCriteria
            [
                'method' => [
                    'name' => 'getTesterLogDataBySearchCriteria',
                    'params' => [self::$testerId, $paramsDto],
                ],
                'mocks' => [
                    [
                        'class' => 'mockMotTestLogMapper',
                        'method' => 'getTesterData',
                        'params' => [self::$testerId, $paramsDto],
                        'result' => ['RESULT'],
                    ],
                ],
                'expect' => [
                    'return' => ['RESULT'],
                ],
            ],
            [
                'method' => [
                    'name' => 'getTesterLogDataBySearchCriteria',
                    'params' => [self::$testerId, $paramsDto],
                ],
                'mocks' => [
                    [
                        'class' => 'mockMotTestLogMapper',
                        'method' => 'getTesterData',
                        'params' => [self::$testerId, $paramsDto],
                        'result' => new RestApplicationException(
                            '/', 'post', [], 10, [['displayMessage' => 'ErrorText']]
                        ),
                    ],
                ],
                'expect' => [
                    'return' => null,
                    'errorMsg' => 'ErrorText',
                ],
            ],

            //  getLogSummary
            [
                'method' => [
                    'name' => 'getLogSummary',
                    'params' => [self::$testerId],
                ],
                'mocks' => [
                    [
                        'class' => 'mockMotTestLogMapper',
                        'method' => 'getTesterSummary',
                        'params' => [self::$testerId],
                        'result' => ['RESULT'],
                    ],
                ],
                'expect' => [
                    'return' => ['RESULT'],
                ],
            ],
            [
                'method' => [
                    'name' => 'getLogSummary',
                    'params' => [self::$testerId],
                ],
                'mocks' => [
                    [
                        'class' => 'mockMotTestLogMapper',
                        'method' => 'getTesterSummary',
                        'params' => [self::$testerId],
                        'result' => new RestApplicationException(
                            '/', 'get', [], 10, [['displayMessage' => 'ErrorText']]
                        ),
                    ],
                ],
                'expect' => [
                    'return' => null,
                    'errorMsg' => 'ErrorText',
                ],
            ],
        ];
    }

    private function getSearchParams()
    {
        $paramsDto = new MotTestSearchParamsDto();
        $paramsDto
            ->setStatus([MotTestStatusName::PASSED])
            ->setDateFromTs((new \DateTime('2013-12-11'))->getTimestamp())
            ->setDateToTs((new \DateTime('2014-03-02'))->getTimestamp());

        return $paramsDto;
    }

    private function getMapperFactory()
    {
        $mockMapperFactory = XMock::of(MapperFactory::class);

        $this->mockMotTestLogMapper = XMock::of(MotTestLogMapper::class);

        $map = [
            [MapperFactory::MOT_TEST_LOG, $this->mockMotTestLogMapper],
        ];

        $mockMapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mockMapperFactory;
    }
}
