<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotEnforcementTest\Controller;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\Vehicle\VehicleParamDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotEnforcement\Controller\MotTestController;
use DvsaMotTest\Model\OdometerReadingViewObject;
use DvsaMotTestTest\Controller\AbstractDvsaMotTestTestCase;
use DvsaMotTestTest\TestHelper\Fixture;

/**
 * Class MotTestControllerTest
 *
 * @package DvsaMotEnforcementTest\Controller
 */
class MotTestControllerTest extends AbstractDvsaMotTestTestCase
{
    const EXAMPLE_SITE_NUMBER = 'V1234';
    protected $mockMotTestServiceClient;
    protected $mockVehicleServiceClient;

    protected function setUp()
    {
        $paramObfuscator = XMock::of(ParamObfuscator::class);
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setService(
            MotTestService::class,
            $this->getMockMotTestServiceClient()
        );
        $serviceManager->setService(
            VehicleService::class,
            $this->getMockVehicleServiceClient()
        );

        $odometerViewObject = XMock::of(OdometerReadingViewObject::class);
        $this->controller = new MotTestController($paramObfuscator, $odometerViewObject);

        $this->controller->setServiceLocator($serviceManager);

        parent::setUp();
    }

    /**
     * @return MotTestController
     */
    protected function getController()
    {
        return parent::getController();
    }

    private function getMockMotTestServiceClient()
    {
        if ($this->mockMotTestServiceClient == null) {
            $this->mockMotTestServiceClient = XMock::of(MotTestService::class);
        }
        return $this->mockMotTestServiceClient;
    }

    private function getMockVehicleServiceClient()
    {
        if ($this->mockVehicleServiceClient == null) {
            $this->mockVehicleServiceClient = XMock::of(VehicleService::class);
        }
        return $this->mockVehicleServiceClient;
    }

    public function testDisplayTestSummaryActionCanBeAccessedForAuthenticatedRequest()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(0)
            ->will($this->returnValue($testMotTestData));

        $this->getResponseForAction('displayTestSummary', ['id' => 0]);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test startInspection action, happy path
     */
    public function testStartInspectionAction()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);

        $response = $this->getResponseForAction('startInspection', ['motTestNumber' => 1]);
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * Throw an Exception from the first API call to fetch the mot data
     */
    public function testStartInspectionActionMotFetchException()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);


        $this->getResponseForAction('startInspection', ['motTestNumber' => 1]);
        $this->assertRedirectLocation2('/mot-test/1');
    }

    /**
     * Throw an Exception on the second API request: posting the new mot data
     */
    public function testStartInspectionActionExceptionOnPost()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);

        $this->getResponseForAction('startInspection', ['motTestNumber' => 1]);
        $this->assertRedirectLocation2('/mot-test/1');
    }

    /**
     * Happy path for differencesFoundBetweenTestsAction GET
     */
    public function testDifferencesFoundBetweenTestsAction()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);

        $restMock = $this->getRestClientMockForServiceManager();
        $restMock
            ->expects($this->at(0))
            ->method('get')
            ->with('mot-test/1/compare')
            ->will($this->returnValue($this->jsonFixture('mot-test-compare', __DIR__)));

        $this->getResponseForAction('differencesFoundBetweenTests', ['motTestNumber' => 1]);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * testDifferencesFoundAction: Get request with an Exception on the compare API call.
     */
    public function testDifferencesFoundBetweenTestsActionWithCompareException()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);

        $restMock = $this->getRestClientMockForServiceManager();
        $restMock
            ->expects($this->at(0))
            ->method('get')
            ->with('mot-test/1/compare')
            ->will($this->throwException($this->getRestException()));

        $this->getResponseForAction('differencesFoundBetweenTests', ['motTestNumber' => 1]);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testDisplayTestSummaryActionUnauthenticated()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asAnonymous());

        $this->getResponseForAction('displayTestSummary');
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testAbortMotTestActionGivenUnauthenticatedRequestShouldThrowException()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asAnonymous());

        $this->getResponseForAction('abortMotTest');
    }

    public function testAbortMotTestActionGivenAuthenticatedRequestCanBeAccessed()
    {
        $this->setupAuthorizationService([PermissionInSystem::VE_MOT_TEST_ABORT]);
        $this->getResponseForAction('abortMotTest');
    }

    public function testAbortMotTestActionException()
    {
        $this->setupAuthorizationService([PermissionInSystem::VE_MOT_TEST_ABORT]);

        $restMock = $this->getRestClientMockForServiceManager($this->restClientServiceName);
        $restMock
            ->expects($this->at(0))
            ->method('postJson')
            ->with($this->anything())
            ->will($this->throwException($this->getRestExceptionWithDisplayMessage('This is a message')));

        $this->getResultForAction2('post', 'abortMotTest', ['id' => 1]);
    }

    public function testAbortMotTestActionGivenCurrentTestStatusIsActiveShouldPostToApiAndRedirectToList()
    {
        $this->setupAuthorizationService([PermissionInSystem::VE_MOT_TEST_ABORT]);

        //given
        $motTestNumber = 2006;
        $status = 'ABORTED_VE';
        $reasonForAbort = 'Aborted by VE for unit test';

        $expectedResultData = [
            "status" => $status,
            "reasonForTerminationComment" => $reasonForAbort,
        ];

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->once())
            ->method('postJson')
            ->with("mot-test/$motTestNumber/status")
            ->will($this->returnValue($expectedResultData));

        //when
        $postParams = [
            'status' => $status,
            'reasonForAbortt' => $reasonForAbort,
        ];
        $this->getResultForAction2('post', 'abortMotTest', ['motTestNumber' => $motTestNumber], null, $postParams);

        //then
        $this->assertRedirectLocation2('/');
    }

    /**
     * Prove that when a RestApplicationException occurs we get a flash message
     */
    public function testRequestMotTestDataWithError()
    {
        $this->getMockMotTestServiceClient()
            ->expects($this->at(0))
            ->method('getMotTestByTestNumber')
            ->will($this->throwException($this->getRestException()));

        $this->getController()->tryGetMotTestOrAddErrorMessages(1);
    }

    /**
     * Prove that currently known routes map correctly to a test type
     */
    public function testGetRouteForMotTestTypeMustPass()
    {
        $fixtures = [
            [
                'type' => MotTestTypeCode::MOT_COMPLIANCE_SURVEY,
                'route' => 'enforcement-step',
            ],
            [
                'type' => MotTestTypeCode::NON_MOT_TEST,
                'route' => 'enforcement-step',
            ],
            [
                'type' => MotTestTypeCode::INVERTED_APPEAL,
                'route' => 'enforcement-step',
            ],
            [
                'type' => MotTestTypeCode::TARGETED_REINSPECTION,
                'route' => 'enforcement-step',
            ],
            [
                'type' => MotTestTypeCode::STATUTORY_APPEAL,
                'route' => 'enforcement-step',
            ],
        ];

        foreach ($fixtures as $fixture) {
            $this->assertEquals($fixture['route'], $this->getController()->getRouteForMotTestType($fixture['type']));
        }
    }

    /**
     * Prove that unknown types get an exception
     *
     * @return array
     */
    public function testGetRouteForMotTestTypeMustFail()
    {
        $fixtures = [
            MotTestTypeCode::OTHER,
            MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST,
            MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
            MotTestTypeCode::NORMAL_TEST,
            MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS,
            MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS,
            MotTestTypeCode::RE_TEST,
        ];

        foreach ($fixtures as $type) {
            try {
                $this->getController()->getRouteForMotTestType($type);
                $this->fail('should fail');
            } catch (\Exception $e) {
                $this->assertEquals(
                    $e->getMessage(),
                    'Unknown inspection report type: ' . $type
                );
            }
        }
    }

    /**
     * Test the display of the comparison screen after a reinspection test
     */
    public function testDifferencesFoundBetweenTests()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);
        $this->getRestClientMock('getWithParams', $this->getCompare());

        $this->getResponseForAction('differencesFoundBetweenTests', ['id' => 1]);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test the display of the comparison screen after a reinspection test when the id is wrong
     */
    public function testDifferencesFoundBetweenTestsWrongMotTestNumber()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);
        $restController = $this->getRestClientMockForServiceManager();

        $restController->expects($this->at(0))
            ->method('get')
            ->will($this->throwException(new ValidationException('/', 'get', [], 10, [['displayMessage' => 'error']])));

        $this->getResponseForAction('differencesFoundBetweenTests', ['id' => -1]);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test the Post of the comparison screen after a reinspection test
     */
    public function testDifferencesFoundBetweenTestsPostData()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);

        $this->setPostAndPostParams([]);
        $this->getRestClientMock('postJson', ['data' => ['id' => 1]]);
        $this->getRestClientMock('get', $this->getResultCompare());

        $this->getResponseForAction('differencesFoundBetweenTests', ['id' => 1]);
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * Test the Post of the comparison screen after a reinspection test with error
     */
    public function testDifferencesFoundBetweenTestsPostDataThrowError()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);
        $restController = $this->getRestClientMockForServiceManager();

        $this->setPostAndPostParams(
            [
                'mappedRfrs' => [
                    '1' => [
                        'score' => 3,
                        'decision' => 0,
                        'category' => 0,
                    ]
                ]
            ]
        );
        $restController->expects($this->at(0))
            ->method('postJson')
            ->will(
                $this->throwException(
                    new ValidationException('/', 'postJson', [], 10, [['displayMessage' => 'error']])
                )
            );

        $this->getResponseForAction('differencesFoundBetweenTests', ['motTestNumber' => 1]);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test the display of the comparison screen from the compare of two test
     */
    public function testMotTestStartCompare()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);

        $this->getRestClientMock('getWithParams', $this->getCompare());

        $this->getResponseForAction(
            'motTestStartCompare', [
                'motTestNumber' => 1,
                'motTestNumberToCompare' => 2,
            ]
        );
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test the display of the comparison screen from the compare of two test when the id is wrong
     */
    public function testMotTestStartCompareWrongMotTestNumber()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);
        $restController = $this->getRestClientMockForServiceManager();

        $restController->expects($this->at(0))
            ->method('getWithParams')
            ->will(
                $this->throwException(
                    new ValidationException('/', 'getWithParams', [], 10, [['displayMessage' => 'error']])
                )
            );

        $this->routeMatch->setParam('motTestNumber', -1);
        $this->routeMatch->setParam('motTestNumberToCompare', -2);

        $this->getResponseForAction('motTestStartCompare');
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test the Post of the comparaison screen from the compare of two test
     */
    public function testMotTestStartComparePostData()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);
        $this->setPostAndPostParams([]);
        $this->getRestClientMock('postJson', ['data' => ['id' => 1]]);
        $this->getRestClientMock('get', $this->getResultCompare());
        $this->routeMatch->setParam('motTestNumber', 1);
        $this->routeMatch->setParam('motTestNumberToCompare', 2);

        $this->getResponseForAction('motTestStartCompare');
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * Test the Post of the comparison screen from the compare of two test with error
     */
    public function testMotTestStartComparePostDataThrowError()
    {
        $this->setupAuthorizationService([PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM]);
        $restController = $this->getRestClientMockForServiceManager();

        $this->setPostAndPostParams(
            [
                'mappedRfrs' => [
                    '1' => [
                        'score' => 3,
                        'decision' => 0,
                        'category' => 0,
                    ]
                ]
            ]
        );
        $restController->expects($this->at(0))
            ->method('postJson')
            ->will(
                $this->throwException(
                    new ValidationException('/', 'postJson', [], 10, [['displayMessage' => 'error']])
                )
            );
        $this->routeMatch->setParam('motTestNumber', 1);
        $this->routeMatch->setParam('motTestNumberToCompare', 2);

        $this->getResponseForAction('motTestStartCompare');
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    protected function getResultCompare()
    {
        return [
            'data' => [
                'motTests' => [
                    '1' => [
                        'testType' => 'EN',
                    ]
                ],
                'enforcementResult' => [
                    'id' => 1,
                    'motTestInspection' => 1
                ]
            ]
        ];
    }

    protected function getCompare()
    {
        return [
            'data' => [
            ]
        ];
    }

    protected function getTestMotRestListTestData()
    {
        $motTest = $this->getTestMotData();

        return [
            "data" =>
                [
                    0 => $motTest
                ],
        ];
    }

    protected function getTestMotRestData()
    {
        $motTest = $this->getTestMotData();
        return ["data" => $motTest];
    }

    protected function getTestMotData()
    {
        $fuelType = [
            'id' => 0,
        ];

        $vehicleClass = 4;

        $vehicle = new VehicleDto();
        $vehicle
            ->setId(1)
            ->setRegistration('ELFA 1111')
            ->setVin('1M2GDM9AXKP042725')
            ->setYear(2011)
            ->setVehicleClass((new VehicleClassDto())->setId($vehicleClass)->setCode($vehicleClass))
            ->setMakeName('Volvo')
            ->setModelName('S80 GTX')
            ->setColour(
                (new ColourDto())->setCode('B')->setName('Black')
            )
            ->setFuelType(
                (new VehicleParamDto())
                    ->setId(0)
            );

        $vehicleTestStation = [
            'id' => '1',
            'siteNumber' => self::EXAMPLE_SITE_NUMBER,
            'authorisedExaminerId' => 1,
            'name' => 'Example Name',
            'address' => '1 road name, town, postcode',
        ];
        $tester = (new PersonDto())->setUsername('tester1');

        $motTest = [
            "motTestNumber" => 1,
            "vehicle" => $vehicle, // $vehicleData,
            "vehicleTestingStation" => $vehicleTestStation,
            "tester" => $tester,
            "odometerReading" => 1045,
            "fuelType" => $fuelType,
            "startedDate" => '2014-02-05T10:28:00Z',
            "completedDate" => "",
            "odometerValue" => '1234',
            "odometerUnit" => 'Km',
            "reasons_for_rejection" => [['rfr-id' => 1], ['rfr-id' => 2]],
            "break_test_results" => [['break-result-id' => 1]],
            "hasRegistration" => true,
            "testType" => MotTestTypeCode::NORMAL_TEST,
            "document" => 1
        ];

        return $motTest;
    }

    /**
     * @return RestApplicationException
     */
    protected function getRestException()
    {
        $restException = new RestApplicationException('', 'null', new \Exception('REST ERROR'), 0);

        return $restException;
    }

    /**
     * @param $displayMessage
     *
     * @return RestApplicationException
     */
    protected function getRestExceptionWithDisplayMessage($displayMessage)
    {
        $errors = [
            [
                'displayMessage' => $displayMessage
            ]
        ];
        $restException = new RestApplicationException('', 'null', new \Exception('REST ERROR'), 0, $errors);

        return $restException;
    }
}
