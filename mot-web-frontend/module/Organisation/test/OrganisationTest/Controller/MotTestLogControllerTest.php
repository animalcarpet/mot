<?php

namespace Organisation\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Mapper\MotTestLogMapper;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Organisation\MotTestLogSummaryDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Organisation\ViewModel\MotTestLog\MotTestLogFormViewModel;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Http\Response;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class MotTestLogControllerTest
 *
 * @package OrganisationTest\Controller
 */
class MotTestLogControllerTest extends AbstractFrontendControllerTestCase
{
    use TestCasePermissionTrait;

    private static $AE_ID = 1;

    /** @var DtoHydrator $dtoHydrator */
    private $dtoHydrator;

    /** @var MotFrontendAuthorisationServiceInterface|MockObj $mockAuthSrv */
    private $mockAuthSrv;

    /** @var MapperFactory|MockObj $mockMapperFactory*/
    private $mockMapperFactory;

    /** @var MotTestLogMapper|MockObj $mockMotTestLogMapper */
    private $mockMotTestLogMapper;

    /** @var OrganisationMapper|MockObj $mockOrganisationMapper */
    private $mockOrganisationMapper;

    /** @var FeatureToggles|MockObj $mockFeatureToggles */
    private $mockFeatureToggles;

    public function setUp()
    {
        $this->dtoHydrator = new DtoHydrator();

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->mockAuthSrv = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->mockMapperFactory = $this->getMapperFactory();
        $this->mockFeatureToggles = XMock::of(FeatureToggles::class);

        $this->setController(
            new MotTestLogController(
                $this->mockAuthSrv,
                $this->mockMapperFactory,
                $this->mockFeatureToggles
            )
        );
        $this->getController()->setServiceLocator($serviceManager);

        $this->createHttpRequestForController('MotTestLog');

        parent::setUp();
    }

    /**
     * Test has user access to page or not with/out auth and permission
     *
     * @param string  $action          Request action
     * @param array   $params          Action parameters
     * @param array   $permissions     User has permissions
     * @param string  $expectedUrl     Expect redirect if failure
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
            $this->mockIsGrantedAtOrganisation($this->mockAuthSrv, $permissions, $params['id']);
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
            ['index', ['id' => self::$AE_ID], [PermissionInSystem::MOT_TEST_READ], $homeUrl],
            ['index', ['id' => self::$AE_ID], [], $homeUrl],
            ['downloadCsv', ['id' => self::$AE_ID], [], $homeUrl],
        ];
    }

    /**
     * Test creation of CSV file
     *
     * @throws \Exception
     */
    public function testDownloadCsv()
    {
        $apiResult = [
            '1' => [
                'siteNumber' => 'V1234',
                'clientIp' => '0.0.0.0',
                'testDateTime'  => '12/10/2014',
                'vehicleVRM' => 'FEZ2918',
                'vehicleMake' => 'VOLKSWAGEN',
                'vehicleModel' => '100',
                'vehicleClass' => '4',
                'testUsername' => 'tester1',
                'testType' => 'Normal Test',
                'status' => "FAILED",
                'testDuration' => '00:03:51',
                'emRecTester' => null,
                'testNumber'    => 1234,
                'emRecDateTime' => '12/11/2013 23:59:59',
                'emReason' => null,
				'vehicleVIN'    => 123456789012345,
				'emCode'        => '1',
            ],
            '2' => [
                'siteNumber' => 'V1234',
                'clientIp' => '',
                'testDateTime'  => '12/10/2014',
                'vehicleVRM' => 'FEZ2918',
                'vehicleMake' => 'VOLKSWAGEN',
                'vehicleModel' => '200',
                'vehicleClass' => '4',
                'testUsername' => 'tester1',
                'testType' => 'Normal Test',
                'status' => "FAILED",
                'testDuration' => '00:03:51',
                'emRecTester' => null,
                'testNumber'    => 1234,
                'emRecDateTime' => '12/11/2013 23:59:59',
                'emReason' => null,
                'vehicleVIN'    => 123456789012345,
                'emCode' => '1',
            ],
            '3' => [
                'siteNumber' => 'V1234',
                'clientIp' => '0.0.0.0, 1.1.1.1, 2.2.2.2',
                'vehicleVRM' => 'FEZ2918',
                'vehicleMake' => 'VOLKSWAGEN',
                'vehicleModel' => '9-4',
                'vehicleClass' => '4',
                'testUsername' => 'tester1',
                'testType' => 'Normal Test',
                'status' => "FAILED",
                'testDuration' => '00:03:51',
                'emRecTester' => null,
                'testNumber'    => 1234,
                'testDateTime'  => '12/10/2014',
                'emRecDateTime' => '12/11/2013 23:59:59',
                'emReason' => null,
                'emCode' => '1',
				'vehicleVIN'    => 123456789012345,
            ],
            '4' => [
                'siteNumber' => 'V1234',
                'testDateTime'  => '12/10/2014',
                'vehicleVRM' => 'FEZ2918',
                'vehicleMake' => 'VOLKSWAGEN',
                'vehicleModel' => '300',
                'vehicleClass' => '4',
                'testUsername' => 'tester1',
                'testType' => 'Normal Test',
                'status' => "FAILED",
                'testDuration' => '00:03:51',
                'emRecTester' => null,
                'emRecDateTime' => '12/11/2013 23:59:59',
                'emReason' => null,
                'emCode' => '1',
                'testNumber'    => 1234,
                'vehicleVIN'    => 123456789012345,
            ],
        ];

        $expectCsvResult = array_replace_recursive(
            $apiResult,
            [
                1 => [
                    'testDateTime'  => '10/12/2014',
                    'emRecDateTime' => '11/12/2013 23:59:59',
                    'vehicleModel'  => '="100"',
                    'clientIp' => '0.0.0.0',
                    'testNumber'    => '="1234"',
                    'vehicleVIN'    => '="123456789012345"',
                ],
                2 => [
                    'testDateTime'  => '10/12/2014',
                    'emRecDateTime' => '11/12/2013 23:59:59',
                    'vehicleModel'  => '="200"',
                    'clientIp' => '',
                    'testNumber'    => '="1234"',
                    'vehicleVIN'    => '="123456789012345"',
                ],
                3 => [
                    'testDateTime'  => '10/12/2014',
                    'emRecDateTime' => '11/12/2013 23:59:59',
                    'testNumber'    => '="1234"',
                    'vehicleVIN'    => '="123456789012345"',
                    'vehicleModel'  => '="9-4"',
                    'clientIp' => '0.0.0.0',
                ],
                4 => [
                    'testDateTime'  => '10/12/2014',
                    'emRecDateTime' => '11/12/2013 23:59:59',
                    'testNumber'    => '="1234"',
                    'vehicleVIN'    => '="123456789012345"',
                    'vehicleModel'  => '="300"',
                ],
            ]
        );

        $resultDto = new SearchResultDto();
        $resultDto
            ->setData($apiResult)
            ->setResultCount(count($apiResult))
            ->setTotalResultCount(9999);

        $this->mockMethod($this->mockMotTestLogMapper, 'getData', null, $resultDto);
        $this->mockIsGrantedAtOrganisation($this->mockAuthSrv, [PermissionAtOrganisation::AE_TEST_LOG], self::$AE_ID);

        $queryParams = [
            SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM => (new \DateTime('2013-12-11'))->getTimestamp(),
            SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM   => (new \DateTime('2014-03-02'))->getTimestamp(),
        ];

        // Turn on output buffering and catch data being written to php://output
        ob_start();
        $this->getResultForAction2('get', 'downloadCsv', ['id' => self::$AE_ID], $queryParams);
        $output = ob_get_clean();

        /** @var $response \Zend\Http\PhpEnvironment\Response */
        $response = $this->getController()->getResponse();
        $this->assertResponseStatus(self::HTTP_OK_CODE, $response);
        $this->assertTrue($response->headersSent());

        $csvBuffer = fopen('php://memory', 'w');
        foreach (([MotTestLogController::$CSV_COLUMNS] + $expectCsvResult) as $line) {
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
     * Test class methods getLogDataBySearchCriteria and getLogSummary
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
            //  getLogDataBySearchCriteria
            [
                'method' => [
                    'name' => 'getLogDataBySearchCriteria',
                    'params' => [self::$AE_ID, $paramsDto],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockMotTestLogMapper',
                        'method' => 'getData',
                        'params' => [self::$AE_ID, $paramsDto],
                        'result' => ['RESULT'],
                    ],
                ],
                'expect'     => [
                    'return' => ['RESULT'],
                ],
            ],
            [
                'method' => [
                    'name' => 'getLogDataBySearchCriteria',
                    'params' => [self::$AE_ID, $paramsDto],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockMotTestLogMapper',
                        'method' => 'getData',
                        'params' => [self::$AE_ID, $paramsDto],
                        'result' => new RestApplicationException(
                            '/', 'post', [], 10, [['displayMessage' => 'ErrorText']]
                        ),
                    ],
                ],
                'expect'     => [
                    'return'   => null,
                    'errorMsg' => 'ErrorText',
                ],
            ],

            //  getLogSummary
            [
                'method' => [
                    'name' => 'getLogSummary',
                    'params' => [self::$AE_ID],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockMotTestLogMapper',
                        'method' => 'getSummary',
                        'params' => [self::$AE_ID],
                        'result' => ['RESULT'],
                    ],
                ],
                'expect'     => [
                    'return' => ['RESULT'],
                ],
            ],
            [
                'method' => [
                    'name' => 'getLogSummary',
                    'params' => [self::$AE_ID],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockMotTestLogMapper',
                        'method' => 'getSummary',
                        'params' => [self::$AE_ID],
                        'result' => new RestApplicationException(
                            '/', 'get', [], 10, [['displayMessage' => 'ErrorText']]
                        ),
                    ],
                ],
                'expect'     => [
                    'return'   => null,
                    'errorMsg' => 'ErrorText',
                ],
            ],

            //  getAuthorisedExaminer
            [
                'method' => [
                    'name' => 'getAuthorisedExaminer',
                    'params' => [self::$AE_ID],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockOrganisationMapper',
                        'method' => 'getAuthorisedExaminer',
                        'params' => [self::$AE_ID],
                        'result' => ['RESULT'],
                    ],
                ],
                'expect'     => [
                    'return' => ['RESULT'],
                ],
            ],
            [
                'method' => [
                    'name' => 'getAuthorisedExaminer',
                    'params' => [self::$AE_ID],
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockOrganisationMapper',
                        'method' => 'getAuthorisedExaminer',
                        'params' => [self::$AE_ID],
                        'result' => new RestApplicationException(
                            '/', 'get', [], 10, [['displayMessage' => 'ErrorText']]
                        ),
                    ],
                ],
                'expect'     => [
                    'return'   => null,
                    'errorMsg' => 'ErrorText',
                ],
            ],
        ];
    }

    /**
     * Test Error messages for different conditions
     *
     * @param array                $postData
     * @param SearchResultDto|null $apiResult
     * @param array                $expect
     *
     * @dataProvider dataProviderTestErrors
     */
    public function testErrors($postData, $mocks, $expectErr)
    {
        //  ----  mock    ----
        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $this->mockMethod(
                    $this->{$mock['class']}, $mock['method'], $this->once(), $mock['result'],
                    ArrayUtils::tryGet($mock, 'params')
                );
            }
        }

        //  --  error messages  --
        $flashMock = XMock::of(FlashMessenger::class);
        $this->getController()->getPluginManager()->setService('flashMessenger', $flashMock, false);

        foreach ($expectErr as $idx => $err) {
            $this->mockMethod($flashMock, 'addErrorMessage', $this->at($idx), null, $err);
        }

        //  logic block: call
        $this->mockIsGrantedAtOrganisation($this->mockAuthSrv, [PermissionAtOrganisation::AE_TEST_LOG], self::$AE_ID);

        $this->getResultForAction2('get', 'index', ['id' => self::$AE_ID], $postData + ['_csrf_token' => true]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function dataProviderTestErrors()
    {
        $orgDto = new OrganisationDto();
        $orgDto->setId(self::$AE_ID);

        $currDate = new \DateTime();
        $currYear = (int) $currDate->format('Y');

        $dateFrom = ['Day' => $currDate->format('j'), 'Month' => $currDate->format('n'), 'Year' => $currYear];
        $dateTo   = ['Day' => $currDate->format('j'), 'Month' => $currDate->format('n'), 'Year' => $currYear];

        $resultDto = (new SearchResultDto())
            ->setSearched(new MotTestSearchParamsDto());

        return [
            //  date interval is valid, api return empty result (0 rows)
            [
                'post'  => [
                    MotTestLogFormViewModel::FLD_DATE_FROM => $dateFrom,
                    MotTestLogFormViewModel::FLD_DATE_TO   => $dateTo,
                ],
                'mocks'  => [
                    [
                        'class'  => 'mockOrganisationMapper',
                        'method' => 'getAuthorisedExaminer',
                        'params' => [self::$AE_ID],
                        'result' => $orgDto,
                    ],
                    [
                        'class'  => 'mockMotTestLogMapper',
                        'method' => 'getSummary',
                        'params' => [self::$AE_ID],
                        'result' => new MotTestLogSummaryDto(),
                    ],
                    [
                        'class'  => 'mockMotTestLogMapper',
                        'method' => 'getData',
                        'result' => self::cloneObject($resultDto)->setTotalResultCount(0),
                    ],
                ],
                'expect' => [
                    '1' => MotTestLogController::ERR_NO_DATA,
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

        $this->mockOrganisationMapper = XMock::of(OrganisationMapper::class);
        $this->mockMotTestLogMapper = XMock::of(MotTestLogMapper::class);

        $map = [
            [MapperFactory::ORGANISATION, $this->mockOrganisationMapper],
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
        return clone ($obj);
    }
}
