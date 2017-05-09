<?php

namespace DvlaInfo\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use IntegrationApi\DvlaInfo\Controller\DvlaInfoMotHistoryController;
use IntegrationApi\DvlaInfo\Service\DvlaInfoMotHistoryService;
use Zend\Log\Logger;
use Zend\Stdlib\Parameters;

class DvlaInfoMotHistoryControllerTest extends AbstractRestfulControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new DvlaInfoMotHistoryController();
        parent::setUp();

        $mockLogger = XMock::of(Logger::class);
        $this->serviceManager->setService('Application/Logger', $mockLogger);
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function test_givenMissingParameters_shouldThrowAnException()
    {
        //given
        $this->request->setMethod('get');

        //when
        $this->controller->dispatch($this->request);

        //then exception is thrown and below is not invoked
        $this->assertTrue(false, 'An exception expected to be thrown.');
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function test_givenVrmAndMissingAnotherParameter_shouldThrowAnException()
    {
        //given
        $this->request->setMethod('get');
        $this->request->setQuery(new Parameters(['vrm' => '1']));

        //when
        $this->controller->dispatch($this->request);

        //then exception is thrown and below is not invoked
        $this->assertTrue(false, 'An exception expected to be thrown.');
    }

    public function test_givenVrmAndTestNumber_controllerOk()
    {
        //given
        $this->request->setMethod('get');
        $this->request->setQuery(new Parameters(['vrm' => '1', 'testNumber' => '1234567']));

        $mockService = $this->getDvlaInfoMotHistoryService();
        $mockService
            ->expects($this->once())
            ->method('getMotTests')
            ->will($this->returnValue([]));

        //when
        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        //then
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_givenVrmAndV5cReference_controllerOk()
    {
        //given
        $this->request->setMethod('get');
        $this->request->setQuery(new Parameters(['vrm' => '1', 'v5cReference' => '1234567']));

        $mockService = $this->getDvlaInfoMotHistoryService();
        $mockService
            ->expects($this->once())
            ->method('getMotTests')
            ->will($this->returnValue([]));

        //when
        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        //then
        $this->assertEquals(200, $response->getStatusCode());
    }

    private function getDvlaInfoMotHistoryService()
    {
        return $this->getMockServiceManagerClass(
            DvlaInfoMotHistoryService::class, DvlaInfoMotHistoryService::class
        );
    }
}
