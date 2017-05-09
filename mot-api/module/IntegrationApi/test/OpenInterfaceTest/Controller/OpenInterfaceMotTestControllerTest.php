<?php

namespace OpenInterfaceTest\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use IntegrationApi\OpenInterface\Controller\OpenInterfaceMotTestController;
use IntegrationApi\OpenInterface\Service\OpenInterfaceMotTestService;
use Zend\Log\Logger;
use Zend\Stdlib\Parameters;

class OpenInterfaceMotTestControllerTest extends AbstractRestfulControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new OpenInterfaceMotTestController();
        parent::setUp();

        $mockLogger = XMock::of(Logger::class);
        $this->serviceManager->setService('Application/Logger', $mockLogger);
    }

    public function test_getList_shouldAcceptValidVRM()
    {
        //given
        $this->request->setMethod('get');
        $this->request->setQuery(new Parameters(['vrm' => 'GGG455']));

        $mockService = $this->getDvlaInfoMotHistoryService();
        $mockService
            ->expects($this->once())
            ->method('getPassMotTestForVehicleIssuedBefore')
            ->will($this->returnValue([]));

        $this->serviceManager->setService(OpenInterfaceMotTestService::class, $mockService);

        //when
        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        //then
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_getList_shouldAccept0VRM()
    {
        //given
        $this->request->setMethod('get');
        $this->request->setQuery(new Parameters(['vrm' => '0']));

        $mockService = $this->getDvlaInfoMotHistoryService();
        $mockService
            ->expects($this->once())
            ->method('getPassMotTestForVehicleIssuedBefore')
            ->will($this->returnValue([]));

        $this->serviceManager->setService(OpenInterfaceMotTestService::class, $mockService);

        //when
        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        //then
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function test_getList_shouldRejectEmptyVRM()
    {
        //given
        $this->request->setMethod('get');
        $this->request->setQuery(new Parameters(['vrm' => '']));

        $mockService = $this->getDvlaInfoMotHistoryService();
        $mockService
            ->expects($this->never())
            ->method('getPassMotTestForVehicleIssuedBefore')
            ->will($this->returnValue([]));

        $this->serviceManager->setService(OpenInterfaceMotTestService::class, $mockService);

        //when
        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        //then exception is thrown and below is not invoked
        $this->assertTrue(false, 'An exception expected to be thrown.');
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function test_getList_shouldRejectNullVRM()
    {
        //given
        $this->request->setMethod('get');
        $this->request->setQuery(new Parameters(['vrm' => null]));

        $mockService = $this->getDvlaInfoMotHistoryService();
        $mockService
            ->expects($this->never())
            ->method('getPassMotTestForVehicleIssuedBefore')
            ->will($this->returnValue([]));

        $this->serviceManager->setService(OpenInterfaceMotTestService::class, $mockService);

        //when
        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        //then exception is thrown and below is not invoked
        $this->assertTrue(false, 'An exception expected to be thrown.');
    }

    private function getDvlaInfoMotHistoryService()
    {
        return $this->getMockServiceManagerClass(
            OpenInterfaceMotTestService::class, OpenInterfaceMotTestService::class
        );
    }
}
