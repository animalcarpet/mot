<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action;

use Core\Action\ViewActionResult;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionOneController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Form\ChangeSecurityQuestionsPasswordForm;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\PasswordValidationService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\ViewModel\ChangeSecurityQuestionsViewModel;
use Zend\Http\Request;

class ChangeSecurityQuestionsAction
{
    const CHANGE_SECURITY_QUESTIONS_START_PAGE_TITLE = 'Change security questions';
    const CHANGE_SECURITY_QUESTIONS_START_PAGE_SUBTITLE = 'Your profile';
    const CHANGE_SECURITY_QUESTIONS_START_TEMPLATE = 'profile/change-security-questions/start';

    private $changeSecurityQuestionsStepService;

    private $changeSecurityQuestionsSessionService;

    private $passwordValidationService;

    public function __construct(ChangeSecurityQuestionsStepService $changeSecurityQuestionsStepService,
                                ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService,
                                PasswordValidationService $passwordValidationService)
    {
        $this->changeSecurityQuestionsStepService = $changeSecurityQuestionsStepService;
        $this->changeSecurityQuestionsSessionService = $changeSecurityQuestionsSessionService;
        $this->passwordValidationService = $passwordValidationService;
    }

    public function execute(Request $request)
    {
        $result = new ViewActionResult();
        $viewModel = new ChangeSecurityQuestionsViewModel();
        $form = new ChangeSecurityQuestionsPasswordForm();
        $result->layout()->setPageTitle(self::CHANGE_SECURITY_QUESTIONS_START_PAGE_TITLE);
        $result->layout()->setPageSubTitle(self::CHANGE_SECURITY_QUESTIONS_START_PAGE_SUBTITLE);
        $result->setTemplate(self::CHANGE_SECURITY_QUESTIONS_START_TEMPLATE);

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $form->setData($postData);

            if ($form->isValid()) {
                if ($this->passwordValidationService->isPasswordValid($form->getPassword()->getValue())) {
                    $this->setUpSession();

                    return new RedirectToRoute(ChangeSecurityQuestionOneController::ROUTE);
                } else {
                    $form->setCustomError($form->getPassword(), $form::MSG_PROBLEM_WITH_PASSWORD);
                    $form->showLabelOnError($form::FIELD_PASSWORD, $form::PASSWORD_LABEL);
                }
            }
        }

        $viewModel->setForm($form);
        $result->setViewModel($viewModel);

        return $result;
    }

    private function setUpSession()
    {
        $sessionArray = [];
        $stepArray = [];

        foreach ($this->changeSecurityQuestionsStepService->getSteps() as $step) {
            $stepArray[$step] = false;
        }

        $stepArray[ChangeSecurityQuestionsStepService::START_STEP] = true;

        $sessionArray[ChangeSecurityQuestionsSessionService::STEP_SESSION_STORE] = $stepArray;

        $this->changeSecurityQuestionsSessionService->save(ChangeSecurityQuestionsSessionService::UNIQUE_KEY, $sessionArray);
    }
}
