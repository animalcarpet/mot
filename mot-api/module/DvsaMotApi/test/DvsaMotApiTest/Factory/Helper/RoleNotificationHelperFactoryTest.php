<?php

namespace DvsaMotApiTest\Factory\Helper;

use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;
use NotificationApi\Service\NotificationService;
use DvsaMotApi\Helper\RoleNotificationHelper;
use DvsaMotApi\Factory\Helper\RoleNotificationHelperFactory;

class RoleNotificationHelperFactoryTest extends AbstractServiceTestCase
{
    /** @var ServiceManager */
    private $serviceLocator;

    public function setUp()
    {
        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(NotificationService::class, Xmock::of(NotificationService::class));
    }

    public function testService()
    {
        $this->assertInstanceOf(
            RoleNotificationHelper::class,
            (new RoleNotificationHelperFactory())->createService($this->serviceLocator)
        );
    }
}
