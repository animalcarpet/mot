<?php

namespace SiteApiTest\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\DefaultBrakeTestsController;
use SiteApi\Service\DefaultBrakeTestsService;

/**
 * Tests for DefaultBrakeTestsController.
 *
 * @property-read DefaultBrakeTestsController $controller
 */
class DefaultBrakeTestsControllerTest extends AbstractRestfulControllerTestCase
{
    private $vehicleTestingStationId = 1;
    private $defaultBrakeTestsService;

    protected function setUp()
    {
        $this->controller = new DefaultBrakeTestsController();
        $this->defaultBrakeTestsService = $this->getDefaultBrakeTestsService();
        $this->setupServiceManager();

        parent::setUp();
    }

    public function testUpdateCanBeAccessed()
    {
        //when
        $result = $this->controller->update($this->vehicleTestingStationId, []);

        //then
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
    }

    private function getDefaultBrakeTestsService()
    {
        $defaultBrakeTestsServiceMock = XMock::of(DefaultBrakeTestsService::class);

        $defaultBrakeTestsServiceMock->expects($this->any())
            ->method('put')
            ->with($this->vehicleTestingStationId, [])
            ->will($this->returnValue([]));

        return $defaultBrakeTestsServiceMock;
    }

    private function setupServiceManager()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(
            DefaultBrakeTestsService::class, $this->defaultBrakeTestsService
        );

        $this->controller->setServiceLocator($serviceManager);
    }
}
