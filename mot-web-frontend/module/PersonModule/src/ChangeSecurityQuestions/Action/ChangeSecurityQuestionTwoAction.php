<?php


namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action;


use Core\Action\ActionResult;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionOneController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsReviewController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionTwoController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Form\ChangeSecurityQuestionForm;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\ViewModel\ChangeSecurityQuestionsViewModel;
use DvsaClient\Entity\SecurityQuestionSet;
use Zend\Http\Request;

class ChangeSecurityQuestionTwoAction
{
    const CHANGE_SECURITY_QUESTIONS_QUESTION_TWO_PAGE_TITLE = "Second security question";
    const CHANGE_SECURITY_QUESTIONS_QUESTION_TWO_PAGE_SUBTITLE = "Your profile";
    const CHANGE_SECURITY_QUESTIONS_QUESTION_TWO_TEMPLATE = "profile/change-security-questions/question-two";

    private $changeSecurityQuestionsService;

    private $changeSecurityQuestionsStepService;

    public function __construct(ChangeSecurityQuestionsService $changeSecurityQuestionsService,
                                ChangeSecurityQuestionsStepService $changeSecurityQuestionsStepService)
    {
        $this->changeSecurityQuestionsService = $changeSecurityQuestionsService;
        $this->changeSecurityQuestionsStepService = $changeSecurityQuestionsStepService;
    }

    public function execute(Request $request)
    {
        if (!$this->changeSecurityQuestionsStepService->isAllowedOnStep(ChangeSecurityQuestionsStepService::QUESTION_TWO_STEP)) {
            return new RedirectToRoute(ChangeSecurityQuestionOneController::ROUTE);
        }

        $securityQuestions = $this->changeSecurityQuestionsService->getSecurityQuestions();
        $form = new ChangeSecurityQuestionForm($this->filterSecurityQuestions($securityQuestions));
        $result = new ActionResult();
        $viewModel = new ChangeSecurityQuestionsViewModel();
        $viewModel = $this->populateViewModelFromSession($viewModel);
        $result->layout()->setPageTitle(self::CHANGE_SECURITY_QUESTIONS_QUESTION_TWO_PAGE_TITLE);
        $result->layout()->setPageSubTitle(self::CHANGE_SECURITY_QUESTIONS_QUESTION_TWO_PAGE_SUBTITLE);
        $result->setTemplate(self::CHANGE_SECURITY_QUESTIONS_QUESTION_TWO_TEMPLATE);

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $form->setData($postData);

            if ($form->isValid()) {
                $this->saveToSession($form);
                $this->changeSecurityQuestionsStepService->updateStepStatus(ChangeSecurityQuestionsStepService::QUESTION_TWO_STEP, true);
                return new RedirectToRoute(ChangeSecurityQuestionsReviewController::ROUTE);
            } else {
                $viewModel->setForm($form);
                $result->setViewModel($viewModel);
                return $result;
            }
        }

        $viewModel->setForm($form);
        $result->setViewModel($viewModel);

        return $result;
    }

    private function saveToSession(ChangeSecurityQuestionForm $form)
    {
        $questionId = $form->getSecurityQuestion()->getValue();
        $questionChosen = $form->getSecurityQuestion()->getValueOptions()[$form->getSecurityQuestion()->getValue()];
        $questionAnswer = $form->getSecurityQuestionAnswer()->getValue();
        $this->changeSecurityQuestionsStepService->updateQuestion(
            ChangeSecurityQuestionsStepService::QUESTION_TWO_STEP,
            $questionId,
            $questionChosen,
            $questionAnswer
        );
    }

    private function populateViewModelFromSession(ChangeSecurityQuestionsViewModel $viewModel)
    {
        $sessionData = $this->changeSecurityQuestionsStepService->getSessionData();
        $stepData = $sessionData[ChangeSecurityQuestionsSessionService::SUBMITTED_VALUES];
        $viewModel->setAnswer($stepData['questionTwoAnswer']);
        $viewModel->setQuestion($stepData['questionTwoId']);
        return $viewModel;
    }

    private function filterSecurityQuestions(SecurityQuestionSet $securityQuestions)
    {
        $groupTwo = $securityQuestions->getGroupTwo();

        $result = [];
        foreach ($groupTwo as $question) {
            $result[$question->getId()] = $question->getText();
        }

        return $result;
    }
}