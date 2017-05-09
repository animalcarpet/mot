<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Site\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Mapper\MotTestLogMapper;
use DvsaClient\Mapper\SiteMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommon\Dto\Site\MotTestLogSummaryDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Site\ViewModel\MotTestLog\MotTestLogFormViewModel;
use Zend\Http\Response;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

/**
 * Class MotTestLogControllerTest.
 */
class MotTestLogControllerTest extends AbstractFrontendControllerTestCase
{
    use TestCasePermissionTrait;

    const VTS_ID = 1;

    /** @var DtoHydrator $dtoHydrator */
    private $dtoHydrator;

    /** @var MotFrontendAuthorisationServiceInterface|MockObj $mockAuthSrv */
    private $mockAuthSrv;

    /** @var MapperFactory|MockObj $mockMapperFactory */
    private $mockMapperFactory;

    /** @var MotTestLogMapper|MockObj $mockMotTestLogMapper */
    private $mockMotTestLogMapper;

    /** @var SiteMapper|MockObj $mockSiteMapper */
    private $mockSiteMapper;

    public function setUp()
    {
        $this->dtoHydrator = new DtoHydrator();

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->mockAuthSrv = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->mockMapperFactory = $this->getMapperFactory();

        $this->setController(
            new MotTestLogController(
                $this->mockAuthSrv,
                $this->mockMapperFactory
            )
        );
        $this->getController()->setServiceLocator($serviceManager);

        $this->createHttpRequestForController('MotTestLog');

        parent::setUp();
    }

    /**
     * Test has user access to page or not with/out auth and permission.
     *
     * @param string $action      Request action
     * @param array  $params      Action parameters
     * @param array  $permissions User has permissions
     * @param string $expectedUrl Expect redirect if failure
     *
     * @dataProvider dataProviderMotTestLogControllerTestCanNotAccess
     */
    public function testMotTestLogControllerCanNotAccess(
        $action,
        $params = [],
        $permissions = [],
        $expectedUrl = null
    ) {
        if (!empty($permissions)) {
            $this->mockIsGrantedAtSite($this->mockAuthSrv, $permissions, $params['id']);
        }

        $this->getResponseForAction($action, $params);

        if ($expectedUrl) {
            $this->assertRedirectLocation2($expectedUrl);
        } else {
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }
    }

    public function dataProviderMotTestLogControllerTestCanNotAccess()
    {
        $homeUrl = '/';

        return [
            ['index', [], [], $homeUrl],
            ['index', ['id' => self::VTS_ID], [PermissionInSystem::MOT_TEST_READ], $homeUrl],
            ['index', ['id' => self::VTS_ID], [], $homeUrl],
            ['downloadCsv', ['id' => self::VTS_ID], [], $homeUrl],
        ];
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
                'testNumber' => 1234,
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
                'testNumber' => 1234,
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
                'testNumber' => 1234,
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

        $this->mockMethod($this->mockMotTestLogMapper, 'getSiteData', null, $resultDto);
        $this->mockIsGrantedAtSite($this->mockAuthSrv, [PermissionAtSite::VTS_TEST_LOGS], self::VTS_ID);

        $queryParams = [
            SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM => (new \DateTime('2013-12-11'))->getTimestamp(),
            SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM => (new \DateTime('2014-03-02'))->getTimestamp(),
        ];
        $this->getResultForAction2('get', 'downloadCsv', ['id' => self::VTS_ID], $queryParams);

        /** @var Response $response */
        $response = $this->getController()->getResponse();

        $this->assertResponseStatus(self::HTTP_OK_CODE, $response);

        $csvBuffer = fopen('php://memory', 'w');
        foreach (([MotTestLogController::$CSV_COLUMNS] + $expectCsvResult) as $line) {
            fputcsv($csvBuffer, $line);
        }
        rewind($csvBuffer);
        $expectContent = stream_get_contents($csvBuffer);
        fclose($csvBuffer);

        $this->assertEquals($expectContent, $response->getContent());

        $fileName = 'test-log-11122013-02032014.csv';

        $expectHeaders = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0, must-revalidate, no-cache, no-store',
            'Pragma' => 'no-cache',
            'Content-Length' => strlen($expectContent),
        ];

        $headers = $response->getHeaders();
        foreach ($expectHeaders as $name => $value) {
            $this->assertEquals($value, $headers->get($name)->getFieldValue());
        }
    }

    /**
     * Test class methods getLogDataBySearchCriteria and getLogSummary.
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

    /**
     * @return array
     */
    public function dataProviderTestGetLogX()
    {
        $paramsDto = $this->getSearchParams();

        return [
            //  getLogDataBySearchCriteria
            [
                'method' => [
                    'name' => 'getLogDataBySearchCriteria',
                    'params' => [self::VTS_ID, $paramsDto],
                ],
                'mocks' => [
                    [
                        'class' => 'mockMotTestLogMapper',
                        'method' => 'getSiteData',
                        'params' => [self::VTS_ID, $paramsDto],
                        'result' => ['RESULT'],
                    ],
                ],
                'expect' => [
                    'return' => ['RESULT'],
                ],
            ],
            [
                'method' => [
                    'name' => 'getLogDataBySearchCriteria',
                    'params' => [self::VTS_ID, $paramsDto],
                ],
                'mocks' => [
                    [
                        'class' => 'mockMotTestLogMapper',
                        'method' => 'getSiteData',
                        'params' => [self::VTS_ID, $paramsDto],
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
                    'params' => [self::VTS_ID],
                ],
                'mocks' => [
                    [
                        'class' => 'mockMotTestLogMapper',
                        'method' => 'getSiteSummary',
                        'params' => [self::VTS_ID],
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
                    'params' => [self::VTS_ID],
                ],
                'mocks' => [
                    [
                        'class' => 'mockMotTestLogMapper',
                        'method' => 'getSiteSummary',
                        'params' => [self::VTS_ID],
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

            //  getSite
            [
                'method' => [
                    'name' => 'getSite',
                    'params' => [self::VTS_ID],
                ],
                'mocks' => [
                    [
                        'class' => 'mockSiteMapper',
                        'method' => 'getById',
                        'params' => [self::VTS_ID],
                        'result' => ['RESULT'],
                    ],
                ],
                'expect' => [
                    'return' => ['RESULT'],
                ],
            ],
            [
                'method' => [
                    'name' => 'getSite',
                    'params' => [self::VTS_ID],
                ],
                'mocks' => [
                    [
                        'class' => 'mockSiteMapper',
                        'method' => 'getById',
                        'params' => [self::VTS_ID],
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

    /**
     * Test Error messages for different conditions.
     *
     * @param $postData
     * @param $mocks
     * @param $expectErr
     *
     * @dataProvider dataProviderTestErrors
     *
     * @throws \Exception
     */
    public function testErrors($postData, $mocks, $expectErr)
    {
        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $this->mockMethod(
                    $this->{$mock['class']}, $mock['method'], $this->once(), $mock['result'],
                    ArrayUtils::tryGet($mock, 'params')
                );
            }
        }

        $flashMock = XMock::of(FlashMessenger::class);
        $this->getController()->getPluginManager()->setService('flashMessenger', $flashMock, false);

        foreach ($expectErr as $idx => $err) {
            $this->mockMethod($flashMock, 'addErrorMessage', $this->at($idx), null, $err);
        }

        $this->mockIsGrantedAtSite($this->mockAuthSrv, [PermissionAtSite::VTS_TEST_LOGS], self::VTS_ID);

        $this->getResultForAction2('get', 'index', ['id' => self::VTS_ID], $postData + ['_csrf_token' => true]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @return array
     */
    public function dataProviderTestErrors()
    {
        $siteDto = new SiteDto();
        $siteDto->setId(self::VTS_ID);

        $currDate = new \DateTime();
        $currYear = (int) $currDate->format('Y');

        $dateFrom = ['Day' => $currDate->format('j'), 'Month' => $currDate->format('n'), 'Year' => $currYear];
        $dateTo = ['Day' => $currDate->format('j'), 'Month' => $currDate->format('n'), 'Year' => $currYear];

        $resultDto = (new SearchResultDto())
            ->setSearched(new MotTestSearchParamsDto());

        return [
            //  date interval is valid, api return empty result (0 rows)
            [
                'post' => [
                    MotTestLogFormViewModel::FLD_DATE_FROM => $dateFrom,
                    MotTestLogFormViewModel::FLD_DATE_TO => $dateTo,
                ],
                'mocks' => [
                    [
                        'class' => 'mockSiteMapper',
                        'method' => 'getById',
                        'params' => [self::VTS_ID],
                        'result' => $siteDto,
                    ],
                    [
                        'class' => 'mockMotTestLogMapper',
                        'method' => 'getSiteSummary',
                        'params' => [self::VTS_ID],
                        'result' => new MotTestLogSummaryDto(),
                    ],
                    [
                        'class' => 'mockMotTestLogMapper',
                        'method' => 'getSiteData',
                        'result' => self::cloneObject($resultDto)->setTotalResultCount(0),
                    ],
                ],
                'expect' => [
                    '1' => MotTestLogController::ERR_NO_DATA,
                ],
            ],
        ];
    }

    /**
     * @return \DvsaCommon\Dto\Search\MotTestSearchParamsDto
     */
    private function getSearchParams()
    {
        $paramsDto = new MotTestSearchParamsDto();
        $paramsDto
            ->setStatus([MotTestStatusName::PASSED])
            ->setDateFromTs((new \DateTime('2013-12-11'))->getTimestamp())
            ->setDateToTs((new \DateTime('2014-03-02'))->getTimestamp());

        return $paramsDto;
    }

    /**
     * @throws \Exception
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMapperFactory()
    {
        $mockMapperFactory = XMock::of(MapperFactory::class);

        $this->mockSiteMapper = XMock::of(SiteMapper::class);
        $this->mockMotTestLogMapper = XMock::of(MotTestLogMapper::class);

        $map = [
            [MapperFactory::SITE, $this->mockSiteMapper],
            [MapperFactory::MOT_TEST_LOG, $this->mockMotTestLogMapper],
        ];

        $mockMapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mockMapperFactory;
    }

    /**
     * @return SearchResultDto
     */
    private static function cloneObject($obj)
    {
        return clone $obj;
    }
}
