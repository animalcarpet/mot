<?php

namespace OrganisationApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Controller\AuthorisedExaminerStatusController;
use OrganisationApi\Factory\Controller\AuthorisedExaminerStatusControllerFactory;
use OrganisationApi\Service\AuthorisedExaminerStatusService;
use Zend\ServiceManager\ServiceManager;

class AuthorisedExaminerStatusControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(
            AuthorisedExaminerStatusService::class,
            XMock::of(AuthorisedExaminerStatusService::class)
        );

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        // Create the factory
        $factory = new AuthorisedExaminerStatusControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(AuthorisedExaminerStatusController::class, $factoryResult);
    }
}
