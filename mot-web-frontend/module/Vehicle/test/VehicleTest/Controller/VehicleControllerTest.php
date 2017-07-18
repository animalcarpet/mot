<?php

namespace VehicleTest\Controller;

use Application\Service\CatalogService;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaClient\Mapper\VehicleExpiryMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Obfuscate\EncryptionKey;
use DvsaCommon\Obfuscate\ParamEncoder;
use DvsaCommon\Obfuscate\ParamEncrypter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Specification\OfficialWeightSourceForVehicle;
use DvsaMotTestTest\Controller\AbstractDvsaMotTestTestCase;
use PHPUnit_Framework_Constraint_IsAnything;
use Vehicle\Controller\VehicleController;
use Vehicle\Helper\VehicleViewModelBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Http\PhpEnvironment\Request;

/**
 * Class VehicleControllerTest.
 */
class VehicleControllerTest extends AbstractDvsaMotTestTestCase
{
    const VEHICLE_ID = 999;
    const UNKNOWN_TEST = 'Unknown';
    const ACTION_INDEX = 'index';

    /** @var array $obfuscationMap */
    protected static $obfuscationMap = [
        1 => 'unit_obfuscate_id_1',
        2 => 'unit_obfuscate_id_2',
        999 => 'unit_obfuscate_id_999',
        1234 => 'unit_obfuscate_id_1234',
    ];

    /** @var MotTestService|MockObj $mockMotTestService */
    private $mockMotTestService;

    /** @var VehicleService|MockObj $mockVehicleService */
    private $mockVehicleService;

    /** @var ParamObfuscator|MockObj $paramObfuscator */
    private $paramObfuscator;

    /** @var FeatureToggles|MockObj */
    private $featureToggles;

    /** @var OfficialWeightSourceForVehicle|MockObj */
    private $officialWeightSourceForVehicle;

    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $this->mockVehicleService = XMock::of(VehicleService::class);
        $this->mockMotTestService = XMock::of(MotTestService::class);

        $serviceManager->setService(
            VehicleService::class,
            $this->mockVehicleService
        );
        $serviceManager->setService(
            MotTestService::class,
            $this->mockMotTestService
        );
        $this->setServiceManager($serviceManager);

        $mockVehicleViewModelBuilder = $this
            ->getMockBuilder(VehicleViewModelBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockVehicleViewModelBuilder
            ->expects($this->any())
            ->method(new PHPUnit_Framework_Constraint_IsAnything())
            ->will($this->returnSelf());

        $this->paramObfuscator = $this->createParamObfuscatorMock(self::$obfuscationMap);

        $this->featureToggles = XMock::of(FeatureToggles::class);
        $this->officialWeightSourceForVehicle = XMock::of(OfficialWeightSourceForVehicle::class);

        $this->setController(
            new VehicleController(
                $this->paramObfuscator,
                $this->mockVehicleService,
                XMock::of(CatalogService::class),
                XMock::of(MotAuthorisationServiceInterface::class),
                $mockVehicleViewModelBuilder,
                XMock::of(VehicleExpiryMapper::class),
                $this->officialWeightSourceForVehicle,
                $this->featureToggles
            )
        );
        $this->getController()->setServiceLocator($serviceManager);
        $this->createHttpRequestForController('Vehicle');

        parent::setUp();
    }

    /**
     * Test has user access to page or not with/out auth and permission.
     *
     * @param string $method      Http method
     * @param string $action      Request action
     * @param array  $params      Action parameters
     * @param array  $permissions User has permissions
     * @param string $expectedUrl Expect location
     *
     * @dataProvider dataProviderTestCanAccessHasRight
     */
    public function testCanAccessHasRight(
        $method,
        $action,
        $params = [],
        $permissions = [],
        $expectedUrl = null
    ) {
        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $this->request->setMethod($method);

        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
        $this->setupAuthorizationService($permissions);

        $this->getResultForAction($action, $params);

        if ($expectedUrl) {
            $this->assertRedirectLocation2($expectedUrl);
        } else {
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }
    }

    /**
     * @dataProvider dataProviderTestVehicleWeightIsDisplayedCorrectlyWithFTOn
     *
     * @param array $vehicleClassDp
     * @param array $weightSourceDp
     * @param int $weightDp
     * @param bool $isSatisfied
     */
    public function testVehicleWeightIsDisplayedCorrectlyWithFTOn($vehicleClassDp, $weightSourceDp, $weightDp, $isSatisfied, $featureToggleStatus)
    {
        $this->withFeatureToggle($featureToggleStatus);

        $vehicle = $this->mockDvsaVehicle($vehicleClassDp, $weightSourceDp, $weightDp);

        $this->mockVehicleService
            ->expects($this->any())
            ->method('getDvsaVehicleById')
            ->willReturn(new DvsaVehicle($vehicle));

        $this->officialWeightSourceForVehicle
            ->expects(true == $featureToggleStatus ? $this->once() : $this->never())
            ->method('isSatisfiedBy')
            ->willReturn($isSatisfied);

        $obfuscatedVehicleId = $this->paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, self::VEHICLE_ID);

        $this->getResultForAction2(
            Request::METHOD_GET,
            self::ACTION_INDEX,
            [
                VehicleController::ROUTE_PARAM_ID => $obfuscatedVehicleId,
            ]
        );

        $this->assertEquals($vehicle->weight, $weightDp);
    }


    public function dataProviderTestVehicleWeightIsDisplayedCorrectlyWithFTOn()
    {
        return [
            //acceptable weight source for given class
            [
                ['name' => '4', 'code' => '4'],
                ['name' => 'vsi', 'code' => 'VSI'],
                1000,
                true,
                true,
            ],
            [
                ['name' => '5', 'code' => '5'],
                ['name' => 'ord_dgw_mam', 'code' => 'ORD_DGW_MAM'],
                1000,
                true,
                true,
            ],
            [
                ['name' => '7', 'code' => '7'],
                ['name' => 'ord_dgw', 'code' => 'ORD_DGW'],
                1000,
                true,
                true,
            ],

            //unacceptable weight source for given class
            [
                ['name' => '4', 'code' => '4'],
                ['name' => 'dgw', 'code' => 'DGW'],
                'Unknown',
                false,
                true,
            ],
            [
                ['name' => '5', 'code' => '5'],
                ['name' => 'misw', 'code' => 'MISW'],
                'Unknown',
                false,
                true,
            ],
            [
                ['name' => '7', 'code' => '7'],
                ['name' => 'ord_dgw_mam', 'code' => 'ORD_DGW_MAM'],
                'Unknown',
                false,
                true,
            ]
        ];
    }

    /**
     * @param $vehicleClassDp
     * @param $weightSourceDp
     * @param $weightDp
     * @return DvsaVehicle $vehicle
     */
    private function mockDvsaVehicle($vehicleClassDp, $weightSourceDp, $weightDp)
    {
        $dvsaVehicleBuilder = new DvsaVehicleBuilder();
        $data = $dvsaVehicleBuilder->getEmptyVehicleStdClass();

        $vehicleClass = new \stdClass();
        $vehicleClass->name = $vehicleClassDp['name'];
        $vehicleClass->code = $vehicleClassDp['code'];
        $data->vehicleClass = $vehicleClass;

        $weightSource = new \stdClass();
        $weightSource->name = $weightSourceDp['name'];
        $weightSource->code = $weightSourceDp['code'];
        $data->weightSource = $weightSource;

        $data->weight = $weightDp;
        return $data;
    }

    /**
     * @return array
     */
    public function dataProviderTestCanAccessHasRight()
    {
        $homeUrl = '/';

        return [
            ['get', 'index', [], [], $homeUrl],
            [
                'get',
                'index',
                ['id' => self::$obfuscationMap[999]],
                [PermissionInSystem::FULL_VEHICLE_MOT_TEST_HISTORY_VIEW],
            ],
            ['get', 'search', [], [], $homeUrl],
            ['get', 'search', [], [PermissionInSystem::VEHICLE_READ]],
            ['get', 'result', [], [], $homeUrl],
        ];
    }

    /**
     * @dataProvider dataProviderTestVehicleWeightIsDisplayedCorrectlyForDifferentVehicleClasses
     *
     * @param int        $weight
     * @param string     $vehicleClass
     * @param int|string $expectedWeight
     */
    public function testVehicleWeightIsDisplayedCorrectlyForDifferentVehicleClasses($weight, $vehicleClass, $expectedWeight)
    {
        $this->withFeatureToggle(false);
        $mockDvsaVehicle = XMock::of(DvsaVehicle::class);

        $this->mockVehicleService
            ->expects($this->any())
            ->method('getDvsaVehicleById')
            ->willReturn($mockDvsaVehicle);

        $this->mockMotTestService
            ->expects($this->any())
            ->method('getVehicleTestWeight')
            ->with(self::VEHICLE_ID)
            ->willReturn($weight);

        $mockDvsaVehicle
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn(
                (new VehicleClassDto())
                    ->setCode($vehicleClass)
            );

        $mockDvsaVehicle
            ->expects($this->any())
            ->method('setWeight')
            ->with($expectedWeight)
            ->willReturnSelf();

        $obfuscatedVehicleId = $this->paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, self::VEHICLE_ID);

        $this->getResultForAction2(
            Request::METHOD_GET,
            self::ACTION_INDEX,
            [
                VehicleController::ROUTE_PARAM_ID => $obfuscatedVehicleId,
            ]
        );

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function dataProviderTestVehicleWeightIsDisplayedCorrectlyForDifferentVehicleClasses()
    {
        return [
            [0, VehicleClassCode::CLASS_1, self::UNKNOWN_TEST],
            [1000, VehicleClassCode::CLASS_1, self::UNKNOWN_TEST],
            [0, VehicleClassCode::CLASS_2, self::UNKNOWN_TEST],
            [1000, VehicleClassCode::CLASS_2, self::UNKNOWN_TEST],
            [0, VehicleClassCode::CLASS_3, self::UNKNOWN_TEST],
            [1000, VehicleClassCode::CLASS_3, 1000],
            [0, VehicleClassCode::CLASS_5, self::UNKNOWN_TEST],
            [1000, VehicleClassCode::CLASS_5, 1000],
        ];
    }

    /**
     * @param array $map
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createParamObfuscatorMock(array $map)
    {
        $toId = array_flip($map);

        $config = ['security' => ['obfuscate' => ['key' => 'ggg', 'entries' => ['vehicleId' => true]]]];
        $paramEncrypter = new ParamEncrypter(new EncryptionKey($config['security']['obfuscate']['key']));
        $paramEncoder = new ParamEncoder();

        $mockParamObfuscator = $this->getMockBuilder(ParamObfuscator::class)
            ->setConstructorArgs([$paramEncrypter, $paramEncoder, $config])
            ->setMethods(['obfuscateEntry', 'deobfuscateEntry'])
            ->getMock();

        $this->mockMethod(
            $mockParamObfuscator, 'obfuscateEntry', null, function ($entryKey, $id) use ($map) {
            return $map[$id];
        }
        );

        $this->mockMethod(
            $mockParamObfuscator, 'deobfuscateEntry', null, function ($entryKey, $obfuscatedId) use ($toId) {
            return $toId[$obfuscatedId];
        }
        );

        return $mockParamObfuscator;
    }

    /**
     * @param bool $isFeatureToggleEnabled
     *
     * @return $this
     */
    private function withFeatureToggle($isFeatureToggleEnabled)
    {
        $this->featureToggles
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isFeatureToggleEnabled);

        return $this;
    }
}
