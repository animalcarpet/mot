<?php

namespace DvsaMotEnforcementTest\Controller;

use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Obfuscate\EncryptionKey;
use DvsaCommon\Obfuscate\ParamEncoder;
use DvsaCommon\Obfuscate\ParamEncrypter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaMotEnforcement\Controller\MotTestSearchController;
use DvsaMotEnforcement\Service\VehicleTestSearch;
use DvsaMotTestTest\Controller\AbstractDvsaMotTestTestCase;
use Zend\Mvc\Router\RouteMatch;
use Zend\Stdlib\Parameters;

/**
 * Class MotTestSearchControllerTest.
 */
class MotTestSearchControllerTest extends AbstractDvsaMotTestTestCase
{
    //unobfuscated value is 1
    const OBFUSCATED_VEHICLE_ID = "1w";

    protected function setUp()
    {
        $this->setServiceManager(Bootstrap::getServiceManager());
        $this->setController(new MotTestSearchController($this->createParamObfuscator()));
        parent::setUp();
    }

    public function testMotSearchAction()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $this->getResponseForAction('motTestSearch');
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test we get a redirect if no data is found from the Api call.
     */
    public function testMotTestSearchByVtsActionRedirect()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $apiUrl = MotTestUrlBuilder::search();

        $validSearchTerm = str_repeat('Z', VehicleTestSearch::MINIMUM_LENGTH_OF_SEARCH_TERM);
        $this->request->getQuery()->set('search', $validSearchTerm);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->at(0))
            ->method('post')
            ->with($apiUrl)
            ->willReturn(
                [
                    'data' => [
                        'data'   => [],
                        "_class" => "DvsaCommon\\Dto\\Search\\SearchResultDto",
                    ],
                ]
            );

        $this->getViewRendererMock();

        $this->getResponseForAction('motTestSearchByVts');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * Test we get a redirect if there is a Rest Exception.
     */
    public function testMotTestSearchByVtsActionRedirectsForRestException()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $apiUrl = MotTestUrlBuilder::search();

        $validSearchTerm = str_repeat('Z', VehicleTestSearch::MINIMUM_LENGTH_OF_SEARCH_TERM);
        $this->request->getQuery()->set('search', $validSearchTerm);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->once())
            ->method('post')
            ->with($apiUrl)
            ->willThrowException(new RestApplicationException('', 'null', new \Exception('REST ERROR'), 0));

        $this->getViewRendererMock();

        $this->getResponseForAction('motTestSearchByVts');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * Test we get a redirect if search term sent is invalid.
     */
    public function testMotTestSearchByVtsActionRedirectsForInvalidSearch()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $apiUrl = MotTestUrlBuilder::search();

        $invalidSearchTerm = str_repeat('Z', VehicleTestSearch::MINIMUM_LENGTH_OF_SEARCH_TERM - 1);
        $this->request->getQuery()->set('search', $invalidSearchTerm);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->never())
            ->method('post')
            ->with($apiUrl)
            ->willReturn(['data' => []]);

        $this->getViewRendererMock();

        $this->getResponseForAction('motTestSearchByVts');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * Test we get a 200 on the happy path.
     */
    public function testMotTestSearchByVtsAction()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $this->request->getQuery()
            ->set('search-result', 1)
            ->set('search', 'V1234');

        $motTestData = $this->getTestMotData();
        $apiUrl      = MotTestUrlBuilder::search();

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->once())
            ->method('post')
            ->with($apiUrl)
            ->willReturn(
                [
                    'data' => [
                        'data'   => [
                            0 => $motTestData,
                            1 => $motTestData,
                        ],
                        "_class" => "DvsaCommon\\Dto\\Search\\SearchResultDto",
                    ],
                ]
            );

        $this->getViewRendererMock();

        $this->getResponseForAction('motTestSearchByVts');
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test a date error: the to date is earlier than the from date.
     */
    public function testMotTestSearchByDateRangeActionWithDateError()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $this->request->getQuery()
            ->set('search', 'V1234')
            ->set('month1', '01')
            ->set('year1', date('Y'))
            ->set('month2', '01')
            ->set('year2', date('Y') - 1);

        $this->getResponseForAction('motTestSearchByDateRange');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * Search by tester and date range.
     */
    public function testMotTestSearchByDateRangeActionSearchingByTester()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $search = 'username';
        $this->request->getQuery()
            ->set('search', $search)
            ->set('type', 'tester');

        $testerData     = $this->getTesterData();
        $testerFullData = $this->getTesterFullData($search);

        $restClientMock = $this->getRestClientMockForServiceManager();

        $this->getViewRendererMock();

        $restClientMock->expects($this->at(0))
            ->method('getWithParams')
            ->with('tester/full')
            ->will($this->returnValue($testerFullData));

        $restClientMock->expects($this->at(1))
            ->method('getWithParams')
            ->with('mot-test-search')
            ->willReturn($this->getMotTestSearchFullData2());

        $restClientMock->expects($this->at(2))
            ->method('get')
            ->with('tester/1')
            ->will($this->returnValue(['data' => ['user' => $testerData, 'id' => 1]]));

        $this->getResponseForAction('motTestSearchByDateRange');
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * An error path.
     */
    public function testMotTestSearchByDateRangeActionSearchingByTesterWithEmptyDataReturned()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $search = 'username';
        $this->request->getQuery()
            ->set('search', $search)
            ->set('type', 'tester');

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->at(0))
            ->method('getWithParams')
            ->with('tester/full')
            ->will($this->returnValue(['data' => ['data' => []]]));

        $this->getResponseForAction('motTestSearchByDateRange');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * Search by date range with invalid search term results in redirect.
     */
    public function testMotTestSearchByDateRangeActionSearchingByTesterWithInvalidSearchTerm()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $invalidSearchTerm = str_repeat('Z', VehicleTestSearch::MINIMUM_LENGTH_OF_SEARCH_TERM - 1);
        $this->request->getQuery()->set('search', $invalidSearchTerm);
        $this->request->getQuery()->set('type', 'tester');

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->never())
            ->method('getWithParams')
            ->with('tester/full')
            ->will($this->returnValue(['data' => ['data' => []]]));

        $this->getResponseForAction('motTestSearchByDateRange');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * A numeric search-result.
     */
    public function testMotTestSearchByDateRangeActionSearchingByTesterWithNumericSearchResult()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $search = 'username';
        $this->request->getQuery()
            ->set('search', $search)
            ->set('type', 'tester')
            ->set('search-result', 1);

        $restClientMock = $this->getRestClientMockForServiceManager();

        $this->getViewRendererMock();
        $testerData = $this->getTesterData();

        $restClientMock->expects($this->at(0))
            ->method('getWithParams')
            ->with('mot-test-search')
            ->willReturn($this->getMotTestSearchFullData2());

        $restClientMock->expects($this->at(1))
            ->method('get')
            ->with('tester/1')
            ->willReturn(['data' => ['user' => $testerData, 'id' => 1]]);

        $this->getResponseForAction('motTestSearchByDateRange');
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testMotTestSearchByDateRangeActionSearchingByVts()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $search = 'V1234';

        $this->request->setQuery(
            new Parameters(
                [
                    'search'        => $search,
                    'type'          => 'vts',
                    'search-result' => 'not-search',
                ]
            )
        );

        $restClientMock = $this->getRestClientMockForServiceManager();
        $this->getViewRendererMock();

        $restClientMock->expects($this->at(0))
            ->method('getWithParams')
            ->with('mot-test-search')
            ->willReturn($this->getMotTestSearchFullData2());

        $this->getResponseForAction('motTestSearchByDateRange');
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Basic VRM search.
     */
    public function testMotTestSearchByVrmActionVrmType()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $search = 'ABC1';
        $this->request->getQuery()->set('search', $search);
        $restClientMock = $this->getRestClientMockForServiceManager();
        $this->getViewRendererMock();

        $restClientMock->expects($this->at(0))
            ->method('getWithParams')
            ->with('mot-test-search')
            ->willReturn($this->jsonFixture('mot-test-search-vrm', __DIR__));

        $this->getResponseForAction('motTestSearchByVrmOrVin');
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * VRM search - no data returned.
     */
    public function testMotTestSearchByVrmActionVrmTypeNoData()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $search = 'ABC1';
        $this->request->getQuery()->set('search', $search);
        $restClientMock = $this->getRestClientMockForServiceManager();
        $this->getViewRendererMock();

        $restClientMock->expects($this->at(0))
            ->method('getWithParams')
            ->with('mot-test-search')
            ->will($this->returnValue(['data' => ['totalResultCount' => 0]]));

        $this->getResponseForAction('motTestSearchByVrmOrVin');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * VRM search - Rest API Exception.
     */
    public function testMotTestSearchByVrmActionVrmTypeRestException()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $search = 'ABC1';
        $this->request->getQuery()->set('search', $search);
        $restClientMock = $this->getRestClientMockForServiceManager();
        $this->getViewRendererMock();

        $restClientMock->expects($this->at(0))
            ->method('getWithParams')
            ->with('mot-test-search')
            ->will($this->throwException(new RestApplicationException('', 'null', new \Exception('REST ERROR'), 0)));

        $this->getResponseForAction('motTestSearchByVrmOrVin');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * VRM search - Post request.
     */
    public function testMotTestSearchByVrmActionVrmCompareTests()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $this->request->setMethod('post');
        $param = new Parameters();
        $param->set('motTestNumber', '1');
        $param->set('motTestNumberToCompare', '2');
        $this->request->setPost($param);
        $validSearchTerm = str_repeat('Z', VehicleTestSearch::MINIMUM_LENGTH_OF_SEARCH_TERM);
        $this->request->getQuery()->set('search', $validSearchTerm);
        $restClientMock = $this->getRestClientMockForServiceManager();
        $this->getViewRendererMock();

        $restClientMock->expects($this->at(0))
            ->method('getWithParams')
            ->with('mot-test/compare', ['motTestNumber' => 1, 'motTestNumberToCompare' => 2])
            ->will($this->returnValue($this->jsonFixture('mot-test-compare', __DIR__)));

        $this->getResponseForAction('motTestSearchByVrmOrVin');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * VRM search - Post request - no data returned.
     */
    public function testMotTestSearchByVrmActionVrmCompareTestsNoData()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $this->request->setMethod('post');
        $param = new Parameters();
        $param->set('motTestNumber', '1');
        $param->set('motTestNumberToCompare', '2');
        $this->request->setPost($param);
        $validSearchTerm = str_repeat('Z', VehicleTestSearch::MINIMUM_LENGTH_OF_SEARCH_TERM);
        $this->request->getQuery()->set('search', $validSearchTerm);
        $restClientMock = $this->getRestClientMockForServiceManager();
        $this->getViewRendererMock();

        $restClientMock->expects($this->at(0))
            ->method('getWithParams')
            ->with('mot-test/compare', ['motTestNumber' => 1, 'motTestNumberToCompare' => 2])
            ->will($this->returnValue([]));

        $this->getResponseForAction('motTestSearchByVrmOrVin');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * VRM search - Post request - invalid search term.
     */
    public function testMotTestSearchByVrmActionVrmCompareTestsInvalidSearchTerm()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $this->request->setMethod('post');
        $param = new Parameters();
        $param->set('motTestNumber', '1');
        $param->set('motTestNumberToCompare', '2');
        $this->request->setPost($param);
        $invalidSearchTerm = str_repeat('Z', VehicleTestSearch::MINIMUM_LENGTH_OF_SEARCH_TERM - 1);
        $this->request->getQuery()->set('search', $invalidSearchTerm);
        $restClientMock = $this->getRestClientMockForServiceManager();
        $this->getViewRendererMock();

        $restClientMock->expects($this->never())
            ->method('getWithParams')
            ->with('mot-test/compare', ['motTestNumber' => 1, 'motTestNumberToCompare' => 2])
            ->will($this->returnValue([]));

        $this->getResponseForAction('motTestSearchByVrmOrVin');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * VRM search - Post request - returned Rest Exception.
     */
    public function testMotTestSearchByVrmActionVrmCompareTestsRestException()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
        $this->request->setMethod('post');
        $param = new Parameters();
        $param->set('motTestNumber', '1');
        $param->set('motTestNumberToCompare', '2');
        $this->request->setPost($param);
        $validSearchTerm = str_repeat('Z', VehicleTestSearch::MINIMUM_LENGTH_OF_SEARCH_TERM);
        $this->request->getQuery()->set('search', $validSearchTerm);
        $restClientMock = $this->getRestClientMockForServiceManager();
        $this->getViewRendererMock();

        $restClientMock->expects($this->at(0))
            ->method('getWithParams')
            ->with('mot-test/compare', ['motTestNumber' => 1, 'motTestNumberToCompare' => 2])
            ->will($this->throwException(new RestApplicationException('', 'null', new \Exception('REST ERROR'), 0)));

        $this->getResponseForAction('motTestSearchByVrmOrVin');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * Basic Mot test search by Vehicle.
     */
    public function testMotTestSearchByVehicleAction()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asVehicleExaminer());
        $this->setupAuthorizationService([PermissionInSystem::FULL_VEHICLE_MOT_TEST_HISTORY_VIEW]);

        $search = 'fnz6110';
        $this->request->getQuery()->set('type', 'registration');
        $this->request->getQuery()->set('search', $search);
        $this->routeMatch->setParam('id', self::OBFUSCATED_VEHICLE_ID);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $this->getViewRendererMock();

        $restClientMock->expects($this->at(0))
            ->method('post')
            ->willReturn($this->jsonFixture('mot-test-search-vrm-dto', __DIR__));

        $this->getResponseForAction('motTestSearchByVehicle');
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    protected function getViewRendererMock()
    {
        $viewRendererMock = \DvsaCommonTest\TestUtils\XMock::of(\Zend\View\Renderer\PhpRenderer::class);
        $serviceManager   = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('ViewRenderer', $viewRendererMock);

        return $viewRendererMock;
    }

    protected function getTesterData()
    {
        return [
            'username'    => 'username',
            'id'          => 1,
            'displayName' => 'John Smith',
        ];
    }

    protected function getTestMotData()
    {
        $vehicleData = [
            'id'            => 1,
            'registration'  => 'ELFA 1111',
            'vin'           => '1M2GDM9AXKP042725',
            'vehicle_class' => '4',
            'make'          => 'Volvo',
            'model'         => 'S80 GTX',
            'year'          => 2011,
            'colour'        => 'Black',
            'fuel_type'     => 'X',
        ];
        $vehicleTestStation = [
            'id'                   => '1',
            'siteNumber'           => 'V12345',
            'authorisedExaminerId' => 1,
            'name'                 => 'Example Name',
            'address'              => '1 road name, town, postcode',
        ];
        $tester = [
            'username' => 'testerNo42',
        ];
        $motTest = [
            "id"                    => 1,
            "status"                => 'FAILED',
            "vehicle"               => $vehicleData,
            "vehicleTestingStation" => $vehicleTestStation,
            "tester"                => $tester,
            "startedDate"           => '2014-02-05T10:28:00Z',
            "completedDate"         => '2014-02-05T11:47:34Z',
            "odometerValue"         => '1234',
            "testType"              => 'NT',
            "odometerUnit"          => 'Km',
            "reasons_for_rejection" => [['rfr-id'          => 1], ['rfr-id' => 2]],
            "break_test_results"    => [['break-result-id' => 1]],
            "hasRegistration"       => true,
            'testDate'              => '2014-12-16T11:00:00Z',
        ];

        return $motTest;
    }

    public function getMotTestSearchFullData()
    {
        return $this->jsonFixture('mot-test-full', __DIR__);
    }

    public function getMotTestSearchFullData2()
    {
        return $this->jsonFixture('mot-test-search-full', __DIR__);
    }

    /**
     * @param $search
     *
     * @return array
     */
    protected function getTesterFullData($search)
    {
        $data                               = $this->jsonFixture('tester-full', __DIR__);
        $data['data']['searched']['search'] = $search;

        return $data;
    }

    /**
     * @return ParamObfuscator
     */
    protected function createParamObfuscator()
    {
        $config = ['security' => ['obfuscate' => ['key' => 'ggg', 'entries' => ['vehicleId' => true]]]];
        $paramEncrypter = new ParamEncrypter(new EncryptionKey($config['security']['obfuscate']['key']));
        $paramEncoder   = new ParamEncoder();


        return new ParamObfuscator($paramEncrypter, $paramEncoder, $config);
    }
}
