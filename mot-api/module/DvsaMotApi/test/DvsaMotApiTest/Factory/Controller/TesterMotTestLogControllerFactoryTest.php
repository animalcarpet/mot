<?php

namespace DvsaMotApiTest\Factory\Controller;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaElasticSearch\Service\ElasticSearchService;
use DvsaMotApi\Controller\TesterMotTestLogController;
use DvsaMotApi\Factory\Controller\TesterMotTestLogControllerFactory;
use DvsaMotApi\Service\TesterMotTestLogService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class TesterMotTestLogControllerFactoryTest.
 */
class TesterMotTestLogControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(TesterMotTestLogService::class, XMock::of(TesterMotTestLogService::class));
        $serviceManager->setService('ElasticSearchService', XMock::of(ElasticSearchService::class));
        $serviceManager->setService(EntityManager::class, XMock::of(EntityManager::class));

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        // Create the factory
        $factory = new TesterMotTestLogControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(TesterMotTestLogController::class, $factoryResult);
    }
}
