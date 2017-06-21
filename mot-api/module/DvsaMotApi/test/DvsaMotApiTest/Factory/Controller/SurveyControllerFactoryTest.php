<?php

namespace DvsaMotApiTest\Factory\Controller;

use DvsaMotApi\Controller\SurveyController;
use DvsaMotApi\Factory\Controller\SurveyControllerFactory;
use DvsaMotApi\Service\SurveyService;
use DvsaCommonTest\Bootstrap;

class SurveyControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsUserControllerInstance()
    {
        $serviceManager = Bootstrap::getServiceManager();

        $surveyServiceMock = $this
            ->getMockBuilder(SurveyService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->setService(SurveyService::class, $surveyServiceMock);

        $controllerManager = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $controllerManager->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));
        $factory = new SurveyControllerFactory();

        $this->assertInstanceOf(SurveyController::class, $factory->createService($controllerManager));
    }
}
