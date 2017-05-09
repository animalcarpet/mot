<?php

namespace NotificationApiTest\Controller;

use NotificationApi\Controller\NotificationActionController;

/**
 * Unit tests for NotificationActionController.
 */
class NotificationActionControllerTest extends AbstractNotificationApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new NotificationActionController();
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['update']
        );
    }
}
