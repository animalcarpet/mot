<?php

namespace AccountApiTest\Factory\Controller;

use AccountApi\Controller\SecurityQuestionController;
use AccountApi\Factory\Controller\SecurityQuestionControllerFactory;
use AccountApi\Service\SecurityQuestionService;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonSecurityAnswerRecorder;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SecurityQuestionControllerFactoryTest.
 */
class SecurityQuestionControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(SecurityQuestionService::class);
        $serviceManager->setService(SecurityQuestionService::class, $service);

        $personSecurityAnswerRecorder = XMock::of(PersonSecurityAnswerRecorder::class);
        $serviceManager->setService(PersonSecurityAnswerRecorder::class, $personSecurityAnswerRecorder);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new SecurityQuestionControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SecurityQuestionController::class, $factoryResult);
    }
}
