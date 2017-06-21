<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\GoogleAnalyticsModule\ControllerPlugin\DataLayerPlugin;
use Dvsa\Mot\Frontend\GoogleAnalyticsModule\TagManager\DataLayer;
use Dvsa\Mot\Frontend\MotTestModule\Controller\MotTestResultsController;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Model\OdometerReadingViewObject;
use DvsaMotTestTest\Controller\AbstractDvsaMotTestTestCase;
use DvsaMotTestTest\TestHelper\Fixture;

/**
 * Test clas for MotTestResultsController.
 */
class MotTestResultsControllerTest extends AbstractDvsaMotTestTestCase
{
    const DEFAULT_MOT_TEST_NUMBER = 1;

    /**
     * @var MotFrontendAuthorisationServiceInterface
     */
    private $authorisationService;

    protected $mockMotTestServiceClient;
    protected $mockVehicleServiceClient;

    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        $this->authorisationService = $this->getMockBuilder(MotFrontendAuthorisationServiceInterface::class)->disableOriginalConstructor()->getMock();

        $odometerViewObject = XMock::of(OdometerReadingViewObject::class);
        $this->controller = new MotTestResultsController($this->authorisationService, $odometerViewObject);

        $dataLayerPlugin = new DataLayerPlugin(new DataLayer());
        $this->controller->getPluginManager()->setService('gtmDataLayer', $dataLayerPlugin);

        $serviceManager = Bootstrap::getServiceManager();

        $serviceManager->setService(
            MotTestService::class,
            $this->getMockMotTestServiceClient()
        );

        $serviceManager->setService(
            VehicleService::class,
            $this->getMockVehicleServiceClient()
        );

        $this->controller->setServiceLocator($this->serviceManager);
        $this->setServiceManager($serviceManager);

        parent::setUp();
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

    public function testMotTestIndexCanBeAccessedForAuthenticatedRequest()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $result = $this->getResultForAction('index', ['motTestNumber' => self::DEFAULT_MOT_TEST_NUMBER]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
        $this->assertEquals($testMotTestData, $result->motTest);
    }

    public function testMotTestIndexWithoutIdParameterFails()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());

        $this->controller->dispatch($this->request);

        $this->assertResponseStatus(self::HTTP_ERR_404);
    }
}
