<?php
namespace NotificationApiTest\Controller;

use DvsaCommonTest\TestUtils\XMock;
use NotificationApi\Controller\NotificationController;
use NotificationApi\Service\NotificationService;

/**
 * Unit tests for NotificationController
 */
class NotificationControllerTest extends AbstractNotificationApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new NotificationController(XMock::of(NotificationService::class));
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['delete', 'get', 'update', 'create']
        );
    }
}
