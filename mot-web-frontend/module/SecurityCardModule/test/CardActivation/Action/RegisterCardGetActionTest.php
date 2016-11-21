<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardActivation\Action;

use Core\Action\ViewActionResult;
use Core\Action\RedirectToRoute;
use CoreTest\Controller\AbstractLightWebControllerTest;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardGetAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardViewStrategy;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Request;

class RegisterCardGetActionTest extends AbstractLightWebControllerTest
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
        $this->withCanActivateACard(true);
        /** @var ViewActionResult $result */
        $result = $this->action()->execute(new Request());

        $this->assertInstanceOf(ViewActionResult::class, $result);
        $this->assertEquals('2fa/register-card/register-card', $result->getTemplate());
        $this->assertEquals($this->subTitle, $result->layout()->getPageSubTitle());
        $this->assertEquals($this->skipCtaTemplate, $result->getViewModel()->getSkipCtaTemplate());
        $this->assertEquals($this->breadcrumbs, $result->layout()->getBreadcrumbs());
    }

    public function testWhenGet_notAllowedToActivateCard_shouldRedirect()
    {
        $this->withCanActivateACard(false);

        $actual = $this->action()->execute(new Request());

        $this->assertInstanceOf(RedirectToRoute::class, $actual);
    }

    private function withCanActivateACard($val)
    {
        return $this->viewStrategy
            ->expects($this->once())
            ->method('canActivateACard')
            ->willReturn($val);
    }

    private function action()
    {
        return new RegisterCardGetAction($this->viewStrategy);
    }
}