<?php

namespace UserApiTest\SpecialNotice\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\Bootstrap;
use UserApi\SpecialNotice\Controller\SpecialNoticeController;
use UserApi\SpecialNotice\Service\SpecialNoticeService;

/**
 * Class SpecialNoticeControllerTest.
 */
class SpecialNoticeControllerTest extends AbstractRestfulControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new SpecialNoticeController();
        parent::setUp();
    }

    public function testCreate_whenAcknowledge_shouldMarkNoticeAcknowledgedAndUpdateTesterActiveStatus()
    {
        //given
        $this->mockValidAuthorization();
        $specialNoticeId = 123;
        $this->routeMatch->setParam('snId', $specialNoticeId);
        $this->request->setMethod('post');
        $this->request->getPost()->set('isAcknowledged', true);

        $specialNoticeServiceMock = \DvsaCommonTest\TestUtils\XMock::of(SpecialNoticeService::class);
        $specialNoticeServiceMock->expects($this->once())
            ->method('markAcknowledged')
            ->with($specialNoticeId);

        $testerServiceMock = \DvsaCommonTest\TestUtils\XMock::of(\DvsaMotApi\Service\TesterService::class);

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(SpecialNoticeService::class, $specialNoticeServiceMock);
        $serviceManager->setService('TesterService', $testerServiceMock);

        //when
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
}
