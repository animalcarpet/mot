<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\ChangeSecurityQuestions\Action;

use Core\Action\ViewActionResult;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsReviewAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsConfirmationController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionTwoController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\ViewModel\ChangeSecurityQuestionsSubmissionModel;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Request;

class ChangeSecurityQuestionsReviewActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChangeSecurityQuestionsStepService $changeSecurityQuestionsStepService */
    private $changeSecurityQuestionsStepService;

    /** @var Request $request */
    private $request;

    /** @var ChangeSecurityQuestionsService $changeSecurityQuestionsService */
    private $changeSecurityQuestionsService;

    /** @var ChangeSecurityQuestionsSubmissionModel $changeSecurityQuestionsSubmissionModel */
    private $changeSecurityQuestionsSubmissionModel;

    public function setUp()
    {
        parent::setUp();
        $this->changeSecurityQuestionsStepService = XMock::of(ChangeSecurityQuestionsStepService::class);
        $this->request = XMock::of(Request::class);
        $this->changeSecurityQuestionsService = XMock::of(ChangeSecurityQuestionsService::class);
        $this->changeSecurityQuestionsSubmissionModel = XMock::of(ChangeSecurityQuestionsSubmissionModel::class);
    }

    public function testWhenPost_isAllowedOnStep_shouldRedirectToConfirmationPage()
    {
        $this->mockIsAllowedOnStep(true);

        $this->mockIsPost(true);

        $actionResult = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertSame(ChangeSecurityQuestionsConfirmationController::ROUTE, $actionResult->getRouteName());
    }

    public function testWhenGet_isAllowedOnStep_shouldDisplayReviewStagePage()
    {
        $this->mockIsAllowedOnStep(true);

        $actionResult = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(ViewActionResult::class, $actionResult);
        $this->assertSame('Review security question changes', $actionResult->layout()->getPageTitle());
    }

    public function testWhenGet_notAllowedOnStep_shouldRedirectToPreviousStage()
    {
        $this->mockIsAllowedOnStep(false);

        $actionResult = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertSame(ChangeSecurityQuestionTwoController::ROUTE, $actionResult->getRouteName());
    }

    public function mockIsAllowedOnStep($allowed)
    {
        $this->changeSecurityQuestionsStepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(ChangeSecurityQuestionsStepService::REVIEW_STEP)
            ->willReturn($allowed);
    }

    public function buildAction()
    {
        $action = new ChangeSecurityQuestionsReviewAction(
            $this->changeSecurityQuestionsStepService,
            $this->changeSecurityQuestionsService
        );

        return $action;
    }

    public function mockIsPost($isPost)
    {
        if ($isPost) {
            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
        } else {
            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
        }
    }
}
