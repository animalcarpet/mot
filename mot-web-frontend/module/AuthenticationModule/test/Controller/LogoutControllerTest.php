<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Controller;

use Core\Service\SessionService;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\LogoutController;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use DvsaCommon\Constants\FeatureToggle;
use Zend\EventManager\EventManager;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Session\Container;
use DvsaCommonTest\Bootstrap;
use Zend\Http\Response;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class LogoutControllerTest
 * @package Dvsa\Mot\Frontend\AuthenticationModuleTest\Controller
 */
class LogoutControllerTest extends AbstractFrontendControllerTestCase
{
    /**
     * @var EventManager|PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManager;

    /**
     * @var WebLogoutService|PHPUnit_Framework_MockObject_MockObject
     */
    private $logoutService;

    /**
     * @var SessionService|PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionManager;

    public function setUp()
    {
        Bootstrap::setupServiceManager();
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->setServiceManager($this->serviceManager);

        $this->logoutService = $this
            ->getMockBuilder(WebLogoutService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventManager = $this
            ->getMockBuilder(EventManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['trigger'])
            ->getMock();

        $this->sessionManager = $this
            ->getMockBuilder(SessionService::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'save'])
            ->getMock();

        $this->controller = new LogoutController(
            $this->eventManager,
            $this->sessionManager,
            $this->logoutService
        );

        $this->setController($this->controller);
        $this->getController()->setServiceLocator($this->serviceManager);

        parent::setUp();
    }

    public function testRedirectWithSurveyToggledOff()
    {
        $this->withFeatureToggles([FeatureToggle::SURVEY_PAGE => false]);

        $this->getResultForAction('logout');

        $this->assertRedirectLocation2('/login');
    }

    public function testRedirectWithSurveyToggledOn()
    {
        $this->withFeatureToggles([FeatureToggle::SURVEY_PAGE => true]);

        $this->getResultForAction('logout');

        $this->assertRedirectLocation2('/login');
    }

    public function testSurveyEventIsFiredWhenSurveyShouldBeDisplayed()
    {
        $this->withFeatureToggles([FeatureToggle::SURVEY_PAGE => true]);

        $this->sessionManager->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnValue('testToken'));

        $this->eventManager->expects($this->once())
            ->method('trigger');

        $this->getResultForAction('logout');
    }
}
