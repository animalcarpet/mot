<?php


namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action;

use Core\Action\ActionResult;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsReviewController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;

class ChangeSecurityQuestionsConfirmationAction
{
    const CHANGE_SECURITY_QUESTIONS_CONFIRMATION_TEMPLATE = "profile/change-security-questions/confirmation";

    private $changeSecurityQuestionsStepService;

    private $changeSecurityQuestionsSessionService;

    public function __construct(ChangeSecurityQuestionsStepService $changeSecurityQuestionsStepService,
                                ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService)
    {
        $this->changeSecurityQuestionsStepService = $changeSecurityQuestionsStepService;
        $this->changeSecurityQuestionsSessionService = $changeSecurityQuestionsSessionService;
    }

    public function execute()
    {
        if (!$this->changeSecurityQuestionsStepService->isAllowedOnStep(ChangeSecurityQuestionsStepService::CONFIRMATION_STEP)) {
            return new RedirectToRoute(ChangeSecurityQuestionsReviewController::ROUTE);
        }

        $result = new ActionResult();
        $result->setTemplate(self::CHANGE_SECURITY_QUESTIONS_CONFIRMATION_TEMPLATE);

        $this->changeSecurityQuestionsSessionService->clear();

        return $result;
    }
}