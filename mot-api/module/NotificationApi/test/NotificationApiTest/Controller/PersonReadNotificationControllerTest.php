<?php

namespace NotificationApiTest\Controller;

use NotificationApi\Controller\PersonReadNotificationController;

/**
 * Unit tests for PersonReadNotificationController.
 */
class PersonReadNotificationControllerTest extends AbstractNotificationApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new PersonReadNotificationController();
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['replaceList', 'getList']
        );
    }
}
