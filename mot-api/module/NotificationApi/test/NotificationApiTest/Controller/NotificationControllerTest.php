<?php
namespace NotificationApiTest\Controller;

use NotificationApi\Controller\NotificationController;

/**
 * Unit tests for NotificationController
 */
class NotificationControllerTest extends AbstractNotificationApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new NotificationController();
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['delete', 'get', 'update', 'create']
        );
    }
}
