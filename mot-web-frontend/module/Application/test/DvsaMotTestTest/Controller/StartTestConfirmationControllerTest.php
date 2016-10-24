<?php

namespace DvsaMotTestTest\Controller;

use Application\Service\ContingencySessionManager;
use Dvsa\Mot\ApiClient\Request\Payload;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaClient\Mapper\VehicleMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Obfuscate\EncryptionKey;
use DvsaCommon\Obfuscate\ParamEncoder;
use DvsaCommon\Obfuscate\ParamEncrypter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\MotTestUrlBuilderWeb;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Constants\VehicleSearchSource;
use DvsaMotTest\Controller\StartTestConfirmationController;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Session\Container;
use Application\Helper\PrgHelper;
use DvsaMotTest\ViewModel\StartTestConfirmationViewModel;
use Dvsa\Mot\ApiClient\Service\VehicleService;

/**
 * Class StartTestConfirmationControllerTest.
 */
class StartTestConfirmationControllerTest extends AbstractDvsaMotTestTestCase
{
    const VEHICLE_ID            = 999;
    const OBFUSCATED_VEHICLE_ID = '34eT';
    const ACTION_TRAINING = 'training';
    const ACTION_INDEX = 'index';
    const ACTION_RETEST = 'retest';

    /** @var  DvsaVehicleBuilder */
    private $dvsaVehicleBuilder;

    /** VehicleMapper|@var MockObj */
    protected $mockVehicleMapper;

    /** @var \Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation */
    protected $vts;

    protected function setUp()
    {
        $this->dvsaVehicleBuilder = new DvsaVehicleBuilder();
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $dummyVehicleDetail = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $dummyVehicleDetail->id = '1';

        $dummyVehicle = new DvsaVehicle($dummyVehicleDetail);
        $mockVehicleService = $this->getMock(
            VehicleService::class,
            [],
            [$this->getMockHttpClientFactory(['response'=>'here we have something'])],
            'VehicleService',
            false
        );
        $mockVehicleService->expects($this->any())->method('getVehicleById')->willReturn($dummyVehicle);

        $serviceManager->setService(
            VehicleService::class,
            $mockVehicleService
        );

        $this->setServiceManager($serviceManager);

        $this->setController(new StartTestConfirmationController($this->createParamObfuscator()));

        $mockMapperFactory = $this->getMapperFactoryMock();
        $serviceManager->setService(MapperFactory::class, $mockMapperFactory);

        parent::setUp();

        $identity  = $this->getCurrentIdentity();
        $this->vts = $this->getVtsData();
        $identity->setCurrentVts($this->vts);
    }

    protected function tearDown()
    {
        $this->getServiceManager()->setService(ContingencySessionManager::class, new ContingencySessionManager());

        parent::tearDown();
    }

    public function testIndexActionPostSetsErrorMessagesForInvalidTest()
    {
        $errorMessage = 'You do not have permission to test this vehicle';

        $this->setupAuthorizationService(
            [PermissionInSystem::MOT_TEST_WITHOUT_OTP, PermissionInSystem::MOT_TEST_START]
        );

        //  --  mocks   --
        $exceptionValidation = new ValidationException('/', 'post', [], 10, [['displayMessage' => $errorMessage]]);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $this->mockMethod($restClientMock, 'post', $this->at(1), $exceptionValidation);
        $this->mockMethod($restClientMock, 'post', $this->at(2), $this->getEligibilityforRetestOk());

        $restClientMock->expects($this->any())->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    $this->getTestVehicleResult(),
                    $this->getInProgressTest(),
                    $this->getCertificateExpiryResult()
                )
            );

        //  --  call   --
        $vehicleId           = 1;
        $paramObfuscator     = $this->createParamObfuscator();
        $obfuscatedVehicleId = $paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $vehicleId);

        $this->getResultForAction2(
            'post',
            self::ACTION_INDEX,
            [
                StartTestConfirmationController::ROUTE_PARAM_ID     => $obfuscatedVehicleId,
                StartTestConfirmationController::ROUTE_PARAM_SOURCE => VehicleSearchSource::VTR,
            ],
            [],
            $this->getDefaultPostParams()
        );

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @dataProvider dataProviderTestNoRegistrationParam
     */
    public function testNoRegistrationParam($action, $params, $expect)
    {
        $mock = [
            'vehicle'    => $this->getTestVehicleResult(),
            'inProgress' => $this->getInProgressTest(),
            'expire'     => $this->getCertificateExpiryResult(),
        ];

        $expect = [
            'status' => self::HTTP_OK_CODE,
            'result' => [
                'noRegistration' => $expect,
            ],
        ];

        $user = ['permissions' => [PermissionInSystem::MOT_TEST_START]];

        $this->commonFlowTest($user, 'get', $action, $params, $mock, $expect);
    }

    public function dataProviderTestNoRegistrationParam()
    {
        $paramCommon = [
            StartTestConfirmationController::ROUTE_PARAM_ID => self::OBFUSCATED_VEHICLE_ID,
        ];

        return [
            [
                'action' => self::ACTION_INDEX,
                'params' => $paramCommon,
                'expect' => false,
            ],
            [self::ACTION_RETEST, $paramCommon, false],
            [self::ACTION_INDEX, $paramCommon + [StartTestConfirmationController::ROUTE_PARAM_NO_REG => '1'], true],
            [self::ACTION_RETEST, $paramCommon + [StartTestConfirmationController::ROUTE_PARAM_NO_REG => '1'], true],
        ];
    }

    /**
     * @dataProvider dataProviderTestCheckEligibilityForRetest
     */
    public function testCheckEligibilityForRetest($action, $params, array $mock, $expect)
    {
        $mock = [
            'vehicle'           => $this->getTestVehicleResult(),
            'inProgress'        => $this->getInProgressTest(),
            'expire'            => $this->getCertificateExpiryResult(),
            'eligibleForRetest' => isset($mock['resp']) ? $mock['resp'] : null,
            'ctSess'            => isset($mock['ctSess']) ? $mock['ctSess'] : null,
        ];

        $expect = [
            'status' => self::HTTP_OK_CODE,
            'result' => $expect,
        ];

        $user = ['permissions' => [PermissionInSystem::MOT_TEST_START]];
        $this->commonFlowTest($user, 'get', $action, $params, $mock, $expect);
    }

    public function dataProviderTestCheckEligibilityForRetest()
    {
        $okResp      = $this->getEligibilityforRetestOk();
        $failResp    = $this->getEligibilityForRetestFail();
        $failNotices = [
            'Vehicle is not eligible for a retest',
            'Not allow retest::Other reason',
        ];

        $paramCommon = [
            StartTestConfirmationController::ROUTE_PARAM_ID => self::OBFUSCATED_VEHICLE_ID,
        ];

        return [
            [
                'action' => self::ACTION_INDEX,
                'params' => $paramCommon,
                'mock'   => [
                    'resp' => $okResp,
                ],
                'expect' => [
                    'isEligibleForRetest' => false,
                ],
            ],
            [
                'action' => self::ACTION_INDEX,
                'params' => $paramCommon + [
                        StartTestConfirmationController::ROUTE_PARAM_SOURCE => VehicleSearchSource::VTR,
                    ],
                'mock'   => [
                    'resp' => $okResp,
                ],
                'expect' => [
                    'isEligibleForRetest' => true,
                    'eligibilityNotices'  => null,
                ],
            ],
            [
                'action' => self::ACTION_INDEX,
                'params' => $paramCommon + [
                        StartTestConfirmationController::ROUTE_PARAM_SOURCE => VehicleSearchSource::VTR,
                    ],
                'mock'   => [
                    'resp' => $failResp,
                ],
                'expect' => [
                    'isEligibleForRetest' => false,
                    'eligibilityNotices'  => $failNotices,
                ],
            ],
            [
                'action' => self::ACTION_RETEST,
                'params' => $paramCommon,
                'mock'   => [
                    'resp' => $okResp,
                ],
                'expect' => [
                    'isEligibleForRetest' => true,
                    'eligibilityNotices'  => null,
                ],
            ],
            [
                'action' => self::ACTION_RETEST,
                'params' => $paramCommon,
                'mock'   => [
                    'resp' => $failResp,
                ],
                'expect' => [
                    'isEligibleForRetest' => false,
                    'eligibilityNotices'  => $failNotices,
                ],
            ],
            [
                'action' => self::ACTION_RETEST,
                'params' => $paramCommon,
                'mock'   => [
                    'resp' => $this->getEligibilityForRetestErr(),
                ],
                'expect' => [],
            ],
            [
                'action' => self::ACTION_TRAINING,
                'params' => $paramCommon,
                'mock'   => [],
                'expect' => [],
            ],
            [
                'action' => self::ACTION_RETEST,
                'params' => $paramCommon,
                'mock'   => [
                    'ctSess' => (object) [
                        'formData'      => 'some CT data',
                        'contingencyId' => 99999,
                    ],
                    'resp'   => $okResp,
                ],
                'expect' => [
                    'isEligibleForRetest' => true,
                    'eligibilityNotices'  => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestAccess
     */
    public function testAccess($method, $action, $permissions, $mock, $expect)
    {
        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $user = ['permissions' => $permissions];

        $params = [
            StartTestConfirmationController::ROUTE_PARAM_ID     => self::OBFUSCATED_VEHICLE_ID,
            StartTestConfirmationController::ROUTE_PARAM_NO_REG => VehicleSearchSource::VTR,
        ];

        $this->commonFlowTest($user, $method, $action, $params, $mock, $expect);
    }

    public function dataProviderTestAccess()
    {
        $motTestNumber = 'ABCD1234';

        $mockGet = [
            'vehicle'    => $this->getTestVehicleResult(),
            'inProgress' => $this->getInProgressTest(),
            'expire'     => $this->getCertificateExpiryResult(),
        ];

        $mockCtSess = [
            'ctSess' => (object) [
                'formData'      => 'some CT data',
                'contingencyId' => 99999,
            ],
        ];

        $mockPost = [
                'createNew' => $this->getSuccessfulMotTestPostResult($motTestNumber),
            ] + $mockGet;

        $unauthException = [
            'class'   => UnauthorisedException::class,
            'message' => 'Asserting permission [MOT-TEST-START] failed.',
        ];

        return [
            [
                'method'     => 'get',
                'action'     => self::ACTION_INDEX,
                'permission' => [PermissionInSystem::MOT_TEST_START],
                'mock'       => $mockGet,
                'expect'     => [
                    'status' => self::HTTP_OK_CODE,
                ],
            ],
            [
                'get', self::ACTION_INDEX, [], [],
                [
                    'status'    => self::HTTP_OK_CODE,
                    'exception' => $unauthException,
                ],
            ],
            [
                'get', self::ACTION_RETEST, [PermissionInSystem::MOT_TEST_START], $mockGet,
                'expect' => ['status' => self::HTTP_OK_CODE],
            ],
            [
                'get', self::ACTION_RETEST, [], [],
                [
                    'status'    => self::HTTP_OK_CODE,
                    'exception' => $unauthException,
                ],
            ],
            ['get', self::ACTION_TRAINING, [], $mockGet, ['status' => self::HTTP_OK_CODE]],
            [
                'post', self::ACTION_INDEX, [PermissionInSystem::MOT_TEST_START], $mockPost,
                ['url' => MotTestUrlBuilderWeb::options($motTestNumber)],
            ],
            [
                'post', self::ACTION_INDEX, [], [],
                [
                    'status'    => self::HTTP_OK_CODE,
                    'exception' => $unauthException,
                ],
            ],
            [
                'post', self::ACTION_RETEST, [PermissionInSystem::MOT_TEST_START], $mockPost,
                ['url' => MotTestUrlBuilderWeb::options($motTestNumber)],
            ],
            [
                'post', self::ACTION_TRAINING, [PermissionInSystem::MOT_TEST_START], $mockPost,
                ['url' => MotTestUrlBuilderWeb::options($motTestNumber)],
            ],
            [
                'post', self::ACTION_INDEX, [PermissionInSystem::MOT_TEST_START], $mockCtSess + $mockPost,
                ['url' => MotTestUrlBuilderWeb::motTest($motTestNumber)],
            ],
        ];
    }

    protected function commonFlowTest($user, $method, $action, $params, $mock, $expect)
    {
        if (isset($user['permissions'])) {
            $this->setupAuthorizationService($user['permissions']);
        }

        //  --  merge get and post response --
        $mockRestClient = $this->getRestClientMockForServiceManager();

        //  --  mock Contingency Session    --
        $postData = [];
        if (!empty($mock['ctSess'])) {
            $ctSessMng = new ContingencySessionManager();
            XMock::mockClassField($ctSessMng, 'contingencySession', $mock['ctSess']);

            $this->getServiceManager()->setService(ContingencySessionManager::class, $ctSessMng);

            $postData = ['contingencyDto' => 'some CT data'];
        }

        //  --  mock POST requests  --
        $postIdx = 0;
        if (self::ACTION_TRAINING === $action) {
            $postIdx--;
        }

        if (!empty($mock['createNew'])) {
            $this->mockMethod($mockRestClient, 'post', $this->at(++$postIdx), $mock['createNew']);
        }

        if (isset($mock['eligibleForRetest'])) {
            $this->mockMethod(
                $mockRestClient, 'post', $this->at(++$postIdx), $mock['eligibleForRetest'], [
                    VehicleUrlBuilder::retestEligibilityCheck(self::VEHICLE_ID, $this->vts->getVtsId()),
                    $postData,
                ]
            );
        }

        //  --  mock GET requests   --
        $getReqKey    = ['inProgress', 'expire'];
        $getReqResult = array_intersect_key($mock, array_flip($getReqKey));

        if (!empty($getReqResult)) {
            $this->mockMethod(
                $mockRestClient, 'get', null, new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($getReqResult)
            );
        }

        //  --  call & check    --
        $this->assertException($expect);
        $result = $this->getResultForAction2(
            $method,
            $action,
            $params,
            [],
            $this->getDefaultPostParams(self::ACTION_TRAINING === $action)
        );
        $this->assertResult($expect, $result);
    }

    protected function getTestVehicleResult(array $params = [], $asDto = false)
    {
        $motTest = $this->jsonFixture('vehicle', __DIR__);

        $result = array_replace_recursive($motTest['data'], $params);

        if ($asDto) {
            return DtoHydrator::jsonToDto($result);
        }

        return ['data' => $result];
    }

    protected function getEligibilityforRetestOk()
    {
        return [
            'data' => [
                'isEligible' => true,
            ],
        ];
    }

    protected function getEligibilityForRetestFail()
    {
        return new ValidationException(
            '/', 'post', [], 10, [
                ['displayMessage' => "Vehicle is not eligible for a retest"],
                ['displayMessage' => "Not allow retest::Other reason"],
            ]
        );
    }

    protected function getEligibilityForRetestErr()
    {
        return new RestApplicationException(
            '/', 'post', [], 10, [
                ['displayMessage' => "Some error happened"],
            ]
        );
    }

    protected function getCertificateExpiryResult()
    {
        return [
            'data' => [
                'checkResult' => [
                    'previousCertificateExists' => true,
                    'expiryDate'                => '2014-05-10',
                ],
            ],
        ];
    }

    protected function getSuccessfulMotTestPostResult($motTestNumber = 1)
    {
        return ['data' => ['motTestNumber' => $motTestNumber]];
    }

    protected function getVtsData()
    {
        return StubIdentityAdapter::createStubVts();
    }

    protected function getMapperFactoryMock()
    {
        $factoryMapper           = XMock::of(MapperFactory::class);
        $this->mockVehicleMapper = XMock::of(VehicleMapper::class);

        $map = [
            ['Vehicle', $this->mockVehicleMapper],
        ];

        $factoryMapper->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $factoryMapper;
    }

    protected function getInProgressTest()
    {
        return ['data' => []];
    }

    /**
     * @param array                      $expect
     * @param \Zend\View\Model\ViewModel $result
     */
    protected function assertResult($expect, $result)
    {
        if (!empty($expect['url'])) {
            $this->assertRedirectLocation2($expect['url']);
        }

        if (!empty($expect['status'])) {
            $this->assertResponseStatus($expect['status']);
        }

        if (!empty($expect['result'])) {
            $actualResult = $result->getVariables();

            foreach ($expect['result'] as $key => $val) {
                if ($key == 'noRegistration') {
                    /** @var StartTestConfirmationViewModel $viewModelResult */
                    $viewModelResult = $actualResult['viewModel'];
                    $this->assertEquals($viewModelResult->isNoRegistration(), $val);
                    continue;
                }

                if ($key == 'eligibilityNotices') {
                    /** @var StartTestConfirmationViewModel $viewModelResult */
                    $viewModelResult = $actualResult['viewModel'];
                    $this->assertEquals($viewModelResult->getEligibilityNotices(), $val);
                    continue;
                }

                if ($key == 'isEligibleForRetest') {
                    /** @var StartTestConfirmationViewModel $viewModelResult */
                    $viewModelResult = $actualResult['viewModel'];
                    $this->assertEquals($viewModelResult->isEligibleForRetest(), $val);
                    continue;
                }

                $actualVal = $actualResult[$key];
                $this->assertEquals(
                    $val,
                    $actualVal,
                    'Assert failure for field "' . $key . '", ' .
                    ' value ' . var_export($actualVal, true) .
                    ' expect ' . var_export($val, true)
                );
            }
        }
    }

    /**
     * Check for double post
     * @dataProvider dataProviderTestDoublePost
     */
    public function testDoublePost($action)
    {
        $this->setupAuthorizationService(
            [PermissionInSystem::MOT_TEST_WITHOUT_OTP, PermissionInSystem::MOT_TEST_START]
        );

        $tokenGuid = 'testToken';

        $session = new Container('prgHelperSession');
        $session->offsetSet($tokenGuid, 'redirectUrl');

        $postParams = [
            PrgHelper::FORM_GUID_FIELD_NAME => $tokenGuid,
        ];
        $this->getResultForAction2('post', self::ACTION_INDEX, null, null, $postParams);

        $this->assertRedirectLocation2('redirectUrl');
    }

    public function dataProviderTestDoublePost()
    {
        return [
            ['action' => self::ACTION_INDEX],
            ['action' => 'cancelMotTest'],
        ];
    }

    protected function assertException($expect)
    {
        if (empty($expect['exception'])) {
            return;
        }

        $exc = $expect['exception'];
        $this->setExpectedException($exc['class'], $exc['message']);
    }

    /**
     * @return ParamObfuscator
     */
    protected function createParamObfuscator()
    {
        $config         = $this->getServiceManager()->get('Config');
        $paramEncrypter = new ParamEncrypter(new EncryptionKey($config['security']['obfuscate']['key']));
        $paramEncoder   = new ParamEncoder();

        return new ParamObfuscator($paramEncrypter, $paramEncoder, $config);
    }

    private function getDefaultPostParams($isTrainingMotTest = false)
    {
        return [
            'colourId' => $isTrainingMotTest ? 'Black' : 2,
            'secondaryColourId' => $isTrainingMotTest ? 'Black' : 2,
            'fuelTypeId' => $isTrainingMotTest ? 'Diesel' : 2,
            'vehicleClassId' => 3,
            'oneTimePassword' => 'something'
        ];
    }
}
