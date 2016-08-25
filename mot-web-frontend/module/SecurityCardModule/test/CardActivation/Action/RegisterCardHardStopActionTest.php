<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardActivation\Action;

use Core\Action\ActionResult;
use Core\Action\NotFoundActionResult;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardHardStopAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Zend\Di\ServiceLocator;
use Zend\EventManager\Exception\DomainException;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;

class RegisterCardHardStopActionTest extends \PHPUnit_Framework_TestCase
{
    private $hardStopCondition;
    private $helpdeskConfig = ['helpdeskConfig'];

    public function setUp()
    {
        $this->hardStopCondition = XMock::of(RegisterCardHardStopCondition::class);
    }

    private function withHardStop($val)
    {
        $this->hardStopCondition->expects($this->any())
            ->method('isTrue')
            ->willReturn($val);
    }

    public function testWhenHardStopConditionSatisfied_shouldShowHardStop()
    {
        $this->withHardStop(true);
        $result = $this->action()->execute();
        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertEquals('2fa/register-card/hard-stop', $result->getTemplate());
        $this->assertEquals($this->helpdeskConfig, $result->getViewModel()->getHelpdeskConfig());
    }

    public function testWhenHardStopConditionSatisfied_shouldShowNoHardStop()
    {
        $this->withHardStop(false);
        $result = $this->action()->execute();
        $this->assertInstanceOf(NotFoundActionResult::class, $result);
    }

    private function action()
    {
        return new RegisterCardHardStopAction($this->hardStopCondition, $this->helpdeskConfig);
    }
}