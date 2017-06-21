<?php

namespace OrganisationApiTest\Factory\Controller;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaElasticSearch\Service\ElasticSearchService;
use OrganisationApi\Controller\MotTestLogController;
use OrganisationApi\Factory\Controller\MotTestLogControllerFactory;
use OrganisationApi\Service\MotTestLogService;
use Zend\ServiceManager\ServiceManager;

class MotTestLogControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(MotTestLogService::class, XMock::of(MotTestLogService::class));
        $serviceManager->setService('ElasticSearchService', XMock::of(ElasticSearchService::class));
        $serviceManager->setService(EntityManager::class, XMock::of(EntityManager::class));

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        // Create the factory
        $factory = new MotTestLogControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(MotTestLogController::class, $factoryResult);
    }
}
