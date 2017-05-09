<?php

namespace NotificationApiTest\Controller;

use DvsaCommonTest\TestUtils\XMock;
use NotificationApi\Controller\PersonNotificationController;
use NotificationApi\Service\NotificationService;

/**
 * Class PersonNotificationControllerTest.
 */
class PersonNotificationControllerTest extends AbstractNotificationApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new PersonNotificationController(XMock::of(NotificationService::class));
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['create', 'getList']
        );
    }
}
