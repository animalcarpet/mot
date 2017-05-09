<?php

namespace EventTest\Factory\Service;

use DvsaCommonTest\TestUtils\XMock;
use Event\Factory\Service\ManualEventServiceFactory;
use Event\Service\ManualEventService;
use Zend\ServiceManager\ServiceManager;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class ManualEventServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $factory = new ManualEventServiceFactory();

        $sm = new ServiceManager();

        $sm->setService(HttpRestJsonClient::class, XMock::of(HttpRestJsonClient::class));

        $this->assertInstanceOf(
            ManualEventService::class,
            $factory->createService($sm)
        );
    }
}
