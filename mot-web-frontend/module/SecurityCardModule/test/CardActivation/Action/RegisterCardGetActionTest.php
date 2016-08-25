<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardActivation\Action;

use Core\Action\ActionResult;
use Core\Action\NotFoundActionResult;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardGetAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardViewStrategy;
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

class RegisterCardGetActionTest extends \PHPUnit_Framework_TestCase
{
    private $viewStrategy;
    private $breadcrumbs = ['breadcrumbs'];
    private $skipCtaTemplate = 'someTemplate';
    private $subTitle = 'somePageSubtitle';


    public function setUp()
    {
        $this->viewStrategy = XMock::of(RegisterCardViewStrategy::class);
        $this->viewStrategy->expects($this->any())->method('breadcrumbs')->willReturn($this->breadcrumbs);
        $this->viewStrategy->expects($this->any())->method('skipCtaTemplate')->willReturn($this->skipCtaTemplate);
        $this->viewStrategy->expects($this->any())->method('pageSubTitle')->willReturn($this->subTitle);
    }

    public function testWhenRegistrationApplicable_shouldDisplayCorrectPage()
    {
        $this->withCanSeePage(true);
        /** @var ActionResult $result */
        $result = $this->action()->execute(new Request());

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertEquals('2fa/register-card/register-card', $result->getTemplate());
        $this->assertEquals($this->subTitle, $result->layout()->getPageSubTitle());
        $this->assertEquals($this->skipCtaTemplate, $result->getViewModel()->getSkipCtaTemplate());
        $this->assertEquals($this->breadcrumbs, $result->layout()->getBreadcrumbs());
    }

    private function withCanSeePage($val)
    {
        $this->viewStrategy->expects($this->any())->method('canSee')->willReturn($val);

    }

    private function action()
    {
        return new RegisterCardGetAction($this->viewStrategy);
    }
}