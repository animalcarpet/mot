<?php

namespace VehicleTest\Controller;

use Application\Service\CatalogService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaClient\Mapper\VehicleExpiryMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Obfuscate\EncryptionKey;
use DvsaCommon\Obfuscate\ParamEncoder;
use DvsaCommon\Obfuscate\ParamEncrypter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\VehicleUrlBuilderWeb;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTestTest\Controller\AbstractDvsaMotTestTestCase;
use Vehicle\Controller\VehicleController;
use Vehicle\Helper\VehicleViewModelBuilder;
use Zend\View\Model\ViewModel;

/**
 * Class VehicleControllerTest.
 */
class VehicleControllerTest extends AbstractDvsaMotTestTestCase
{
    /**
     * @var array
     */
    protected static $obfuscationMap = [
        1    => 'unit_obfuscate_id_1',
        2    => 'unit_obfuscate_id_2',
        999  => 'unit_obfuscate_id_999',
        1234 => 'unit_obfuscate_id_1234',
    ];

    protected function setUp()
    {

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $serviceManager->setService(
            VehicleService::class,
            new VehicleService('to be token')
        );

        $this->setServiceManager($serviceManager);
        $paramObfuscator = $this->createParamObfuscatorMock(self::$obfuscationMap);
        $this->setController(
            new VehicleController($paramObfuscator,
            XMock::of(VehicleService::class),
            XMock::of(CatalogService::class),
            XMock::of(MotAuthorisationServiceInterface::class),
            XMock::of(VehicleViewModelBuilder::class),
            XMock::of(VehicleExpiryMapper::class)
        ));
        $this->getController()->setServiceLocator($serviceManager);
        $this->createHttpRequestForController('Vehicle');

        parent::setUp();
    }

    /**
     * Test has user access to page or not with/out auth and permission.
     *
     * @param string  $method          Http method
     * @param string  $action          Request action
     * @param array   $params          Action parameters
     * @param array   $permissions     User has permissions
     * @param string  $expectedUrl     Expect location
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
     * @return array
     */
    public function dataProviderTestCanAccessHasRight()
    {
        $homeUrl   = '/';
        $urlSearch = VehicleUrlBuilderWeb::vehicle()->search();

        return [
            ['get', 'index', [], [],$homeUrl],
            [
                'get',
                'index',
                ['id' => self::$obfuscationMap[999], ],
                [PermissionInSystem::FULL_VEHICLE_MOT_TEST_HISTORY_VIEW],
            ],
            ['get', 'search', [], [], $homeUrl],
            ['get', 'search', [], [PermissionInSystem::VEHICLE_READ]],
            ['get', 'result', [], [], $homeUrl],
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
        $paramEncoder   = new ParamEncoder();

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
}
