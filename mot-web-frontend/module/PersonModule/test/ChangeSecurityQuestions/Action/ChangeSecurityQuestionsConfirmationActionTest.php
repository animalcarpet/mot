<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\ChangeSecurityQuestions\Action;

use Core\Action\ViewActionResult;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsConfirmationAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsReviewAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsReviewController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use DvsaCommonTest\TestUtils\XMock;

class ChangeSecurityQuestionsConfirmationActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ChangeSecurityQuestionsStepService */
    private $changeSecurityQuestionsStepService;
    /** @var  ChangeSecurityQuestionsSessionService */
    private $changeSecurityQuestionsSessionService;

    public function setUp()
    {
        parent::setUp();
        $this->changeSecurityQuestionsStepService = XMock::of(ChangeSecurityQuestionsStepService::class);
        $this->changeSecurityQuestionsSessionService = XMock::of(ChangeSecurityQuestionsSessionService::class);
    }

    public function testWhenGet_whenNotAllowedOnStep_shouldRedirectToPreviousStep()
    {
        $this->mockIsAllowedOnStep(false);

        $actionResult = $this->buildAction()->execute();

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertSame(ChangeSecurityQuestionsReviewController::ROUTE, $actionResult->getRouteName());
    }

    public function testWhenGet_whenAllowedOnStep_shouldDisplayTheConfirmationPage()
    {
        $this->mockIsAllowedOnStep(true);

        $actionResult = $this->buildAction()->execute();

        $this->assertInstanceOf(ViewActionResult::class, $actionResult);
        $this->assertSame('profile/change-security-questions/confirmation', $actionResult->getTemplate());
    }

    public function mockIsAllowedOnStep($allowed)
    {
        $this->changeSecurityQuestionsStepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(ChangeSecurityQuestionsStepService::CONFIRMATION_STEP)
            ->willReturn($allowed);
    }

    public function buildAction()
    {
        return new ChangeSecurityQuestionsConfirmationAction(
            $this->changeSecurityQuestionsStepService,
            $this->changeSecurityQuestionsSessionService
        );
    }
}