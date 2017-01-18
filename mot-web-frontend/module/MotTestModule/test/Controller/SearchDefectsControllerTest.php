<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\MotTestModule\Controller\SearchDefectsController;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTestTest\TestHelper\Fixture;

class SearchDefectsControllerTest extends AbstractFrontendControllerTestCase
{
    protected $mockMotTestServiceClient;
    protected $mockVehicleServiceClient;

    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService(
            MotTestService::class,
            $this->getMockMotTestServiceClient()
        );

        $this->serviceManager->setService(
            VehicleService::class,
            $this->getMockVehicleServiceClient()
        );

        $this->setServiceManager($this->serviceManager);
        $this->setController(
            new SearchDefectsController()
        );

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

    public function testLoadIndex()
    {
        $motTestNumber = 1;

        $routeParams = [
            'motTestNumber' => $motTestNumber,
        ];

        $queryParams = [
            'q' => '',
            'p' => 0,
        ];

        $vehicleData = Fixture::getDvsaVehicleTestDataVehicleClass4(true);
        $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);

        $motTest = new MotTest($testMotTestData);
        $vehicle = new DvsaVehicle($vehicleData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($motTest));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicle));

        $this->getResultForAction2('get', 'index', $routeParams, $queryParams);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testLoadIndexWithSearchTerms()
    {
        $motTestNumber = 1;

        $restClientMock = $this->getRestClientMockForServiceManager();

        $restClientMock->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $this->getDefects()]);

        $routeParams = [
            'motTestNumber' => $motTestNumber,
        ];

        $queryParams = [
            'q' => 'door',
            'p' => 0,
        ];

        $vehicleData = Fixture::getDvsaVehicleTestDataVehicleClass4(true);
        $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);

        $motTest = new MotTest($testMotTestData);
        $vehicle = new DvsaVehicle($vehicleData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($motTest));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicle));

        $this->getResultForAction2('get', 'index', $routeParams, $queryParams);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @return array
     */
    private function getDefects()
    {
        return [
            'reasonsForRejection' => [
                [
                    'rfrId' => 1,
                    'testItemSelectorId' => 1,
                    'description' => 'asd',
                    'testItemSelectorName' => 'asda',
                    'advisoryText' => 'asdsad',
                    'inspectionManualReference' => 'asdsa',
                    'testItemSelector' => 'asd',
                    'isAdvisory' => false,
                    'isPrsFail' => true,
                ],
            ],
        ];
    }
}
