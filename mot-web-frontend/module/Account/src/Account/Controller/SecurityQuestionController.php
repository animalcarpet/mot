<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://github.com/dvsa/mot
 */
namespace Account\Controller;

use Account\AbstractClass\AbstractSecurityQuestionController;
use Account\Action\PasswordReset\AnswerSecurityQuestionsAction;
use Account\Service\SecurityQuestionService;
use Account\ViewModel\SecurityQuestionViewModel;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\View\Model\ViewModel;

/**
 * SecurityQuestion Controller.
 */
class SecurityQuestionController extends AbstractSecurityQuestionController
{
    const PAGE_TITLE = 'Forgotten your password';
    const PAGE_SUBTITLE = 'MOT testing service';
    const ROUTE_NOT_AUTHENTICATED = 'forgotten-password/notAuthenticated';
    const ROUTE_CONFIRMATION_EMAIL = 'forgotten-password/confirmationEmail';
    const ROUTE_SECURITY_QUESTIONS ='forgotten-password/security-questions';

    /** @var AnswerSecurityQuestionsAction $answerSecurityQuestionsAction */
    private $answerSecurityQuestionsAction;

    public function __construct(
        SecurityQuestionService $securityQuestionService,
        UserAdminSessionManager $userAdminSessionManager,
        AnswerSecurityQuestionsAction $answerSecurityQuestionsAction
    ) {
        parent::__construct($securityQuestionService, $userAdminSessionManager);

        $this->answerSecurityQuestionsAction = $answerSecurityQuestionsAction;
    }

    /**
     * This action is the end point to enter the question answer for the help desk.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->userAdminSessionManager->updateUserAdminSession(UserAdminSessionManager::USER_NAME_KEY, '');
        $personId = $this->params()->fromRoute('personId');
        $questionNumber = $this->params()->fromRoute('questionNumber');

        $viewModel = $this->createViewModel();
        $view = $this->index($personId, $questionNumber, $viewModel);

        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE);
        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function getQuestionsAction()
    {
        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariables([
            'pageSubTitle' => 'Forgotten your password',
            'pageTitle' => 'Your security questions',
        ]);
        $this->setHeadTitle('Your security questions');

        $personId = $this->params()->fromRoute('personId');
        $action = $this->answerSecurityQuestionsAction;

        $action
            ->setFormActionUrl($this->url()->fromRoute(self::ROUTE_SECURITY_QUESTIONS, ['personId' => $personId]))
            ->setBackUrl($this->url()->fromRoute('forgotten-password'));

        $actionResult = $this->getRequest()->isPost() ?
            $action->execute($personId, $this->getRequest()->getPost()) :
            $action->executeNoAnswers($personId);

        return $this->applyActionResult($actionResult);
    }

    /**
     * @return SecurityQuestionViewModel
     */
    private function createViewModel()
    {
        /** @var PersonProfileUrlGenerator $personProfileUrlGenerator */
        $personProfileUrlGenerator = $this->getServiceLocator()->get(PersonProfileUrlGenerator::class);

        return new SecurityQuestionViewModel($this->service, $personProfileUrlGenerator);
    }
}
