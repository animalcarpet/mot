<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace AccountTest\Factory\Controller;

use Account\Action\PasswordReset\AnswerSecurityQuestionsAction;
use Account\Controller\SecurityQuestionController;
use Account\Factory\Controller\SecurityQuestionControllerFactory;
use Account\Service\SecurityQuestionService;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\UserAdminSessionManager;
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

        $service = XMock::of(UserAdminSessionManager::class);
        $serviceManager->setService(UserAdminSessionManager::class, $service);

        $service = XMock::of(AnswerSecurityQuestionsAction::class);
        $serviceManager->setService(AnswerSecurityQuestionsAction::class, $service);

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
