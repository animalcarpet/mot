<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action;

use Core\Action\ViewActionResult;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsConfirmationController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionTwoController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\ViewModel\ChangeSecurityQuestionReviewViewModel;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\ViewModel\ChangeSecurityQuestionsSubmissionModel;
use Zend\Http\Request;

class ChangeSecurityQuestionsReviewAction
{
    const CHANGE_SECURITY_QUESTIONS_REVIEW_PAGE_TITLE = 'Review security question changes';
    const CHANGE_SECURITY_QUESTIONS_REVIEW_PAGE_SUBTITLE = 'Your profile';
    const CHANGE_SECURITY_QUESTIONS_REVIEW_TEMPLATE = 'profile/change-security-questions/review';

    private $changeSecurityQuestionsStepService;

    private $changeSecurityQuestionsService;

    public function __construct(ChangeSecurityQuestionsStepService $changeSecurityQuestionsStepService,
                                ChangeSecurityQuestionsService $changeSecurityQuestionsService)
    {
        $this->changeSecurityQuestionsStepService = $changeSecurityQuestionsStepService;
        $this->changeSecurityQuestionsService = $changeSecurityQuestionsService;
    }

    public function execute(Request $request)
    {
        if (!$this->changeSecurityQuestionsStepService->isAllowedOnStep(ChangeSecurityQuestionsStepService::REVIEW_STEP)) {
            return new RedirectToRoute(ChangeSecurityQuestionTwoController::ROUTE);
        }

        if ($request->isPost()) {
            // change to submission of security questions when endpoint ready
            $this->changeSecurityQuestionsService->updateSecurityQuestions($this->getSubmittedData());
            $this->changeSecurityQuestionsStepService->updateStepStatus(ChangeSecurityQuestionsStepService::REVIEW_STEP, true);

            return new RedirectToRoute(ChangeSecurityQuestionsConfirmationController::ROUTE);
        }

        $result = new ViewActionResult();
        $viewModel = $this->populateViewModel();
        $result->setViewModel($viewModel);
        $result->layout()->setPageTitle(self::CHANGE_SECURITY_QUESTIONS_REVIEW_PAGE_TITLE);
        $result->layout()->setPageSubTitle(self::CHANGE_SECURITY_QUESTIONS_REVIEW_PAGE_SUBTITLE);
        $result->setTemplate(self::CHANGE_SECURITY_QUESTIONS_REVIEW_TEMPLATE);

        return $result;
    }

    private function populateViewModel()
    {
        $sessionData = $this->changeSecurityQuestionsStepService->getSessionData();
        $stepData = $sessionData[ChangeSecurityQuestionsSessionService::SUBMITTED_VALUES];

        $viewModel = new ChangeSecurityQuestionReviewViewModel();
        $viewModel->setQuestionOneText($stepData['questionOneText']);
        $viewModel->setQuestionTwoText($stepData['questionTwoText']);

        return $viewModel;
    }

    /**
     * @return ChangeSecurityQuestionsSubmissionModel
     */
    private function getSubmittedData()
    {
        $sessionData = $this->changeSecurityQuestionsStepService->getSessionData();
        $stepData = $sessionData[ChangeSecurityQuestionsSessionService::SUBMITTED_VALUES];

        $model = new ChangeSecurityQuestionsSubmissionModel();

        return $model
            ->setQuestionOneId($stepData['questionOneId'])
            ->setQuestionOneAnswer($stepData['questionOneAnswer'])
            ->setQuestionTwoId($stepData['questionTwoId'])
            ->setQuestionTwoAnswer($stepData['questionTwoAnswer']);
    }
}
