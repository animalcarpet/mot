<?php

namespace DvsaMotTestTest\Factory\Service;

use DvsaMotTest\Factory\Service\SurveyServiceFactory;
use DvsaMotTest\Service\SurveyService;
use Zend\Http\Client;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommonTest\Bootstrap;
use Zend\Session\Container;

class SurveyServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testSurveyServiceFactory()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $containerMock = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serviceManager->setService(Client::class, $clientMock);
        $serviceManager->setService(Container::class, $containerMock);

        $factory = (new SurveyServiceFactory())->createService($serviceManager);

        $this->assertInstanceOf(SurveyService::class, $factory);
    }

}
