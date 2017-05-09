<?php

namespace NotificationApiTest\Controller;

use DvsaCommonApiTest\Controller\ApiControllerUnitTestInterface;
use DvsaCommonApiTest\Controller\ApiControllerUnitTestTrait;
use NotificationApi\Service\NotificationService;
use NotificationApiTest\Entity\NotificationCreatorTrait;

/**
 * Base for controller test in NotificationApi module.
 */
abstract class AbstractNotificationApiControllerTestCase extends \PHPUnit_Framework_TestCase implements
    ApiControllerUnitTestInterface
{
    use ApiControllerUnitTestTrait;
    use NotificationCreatorTrait;

    public function mockServices()
    {
        $mock = $this->createMock(NotificationService::class);

        $mock->expects($this->any())->method('getAllByPersonId')->will(
            $this->returnValue(
                [
                    $this->createNotification(),
                ]
            )
        );

        $this->serviceManager->setService(NotificationService::class, $mock);
    }
}
