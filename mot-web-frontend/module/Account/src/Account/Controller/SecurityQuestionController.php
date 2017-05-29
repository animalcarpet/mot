<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://github.com/dvsa/mot
 */
namespace Account\Controller;

use Account\AbstractClass\AbstractSecurityQuestionController;
use Account\Exception\LimitReachedException;
use Account\Form\SecurityQuestionAnswersForm;
use Account\ViewModel\SecurityQuestionViewModel;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaCommon\InputFilter\Account\SecurityQuestionAnswersInputFilter;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\View\Model\ViewModel;

/**
 * SecurityQuestion Controller.
 */
class SecurityQuestionController extends AbstractSecurityQuestionController
{
    const PAGE_TITLE = 'Forgotten your password';
    const PAGE_SUBTITLE = 'MOT testing service';

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

        $verificationMessages = [];

        $personId = $this->params()->fromRoute('personId');

        $form = $this->getSecurityQuestionAnswersFormForPerson($personId);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->bind($request->getPost());

            if ($form->isValid()) {
                try {
                    $verificationResult = $this->service->verifyAnswers($personId, $form->getMappedQuestionsAndAnswers());

                    if ($this->service->isVerified()) {
                        $this->flashMessenger()->getContainer()->offsetSet(
                            PasswordResetController::SESSION_KEY_EMAIL,
                            $this->service->resetPersonPassword($personId)
                        );

                        return $this->redirect()->toRoute('forgotten-password/confirmationEmail');
                    } else {
                        foreach ($verificationResult as $failedQuestionId => $value) {
                            $form->flagFailedAnswerVerifications($failedQuestionId);
                        }

                        if ($this->service->hasRemainingAttempts()) {
                            if ($this->service->getRemainingAttempts() <= 1) {
                                $verificationMessages = [[
                                    SecurityQuestionAnswersInputFilter::MSG_LAST_ATTEMPT_WARNING,
                                ]];
                            }
                        } else {
                            throw new LimitReachedException();
                        }
                    }
                } catch (LimitReachedException $e) {
                    return $this->redirectToTheNotAuthenticate();
                } catch (\RuntimeException $e) {
                    return $this->redirectToTheNotAuthenticate();
                }
            }
        }

        $messages = $form->getMessages() + $verificationMessages;

        $viewModel = (new ViewModel())->setTemplate('account/security-question/get-questions.twig')
            ->setVariables([
                'form' => $form,
                'validationMessages' => $messages,
                'urlBack' => $this->url()->fromRoute('forgotten-password'),
                'config' => $this->getConfig()['helpdesk'],
            ]);

        return $viewModel;
    }

    /**
     * @return \Zend\Http\Response
     */
    private function redirectToTheNotAuthenticate()
    {
        return $this->redirect()->toUrl($this->url()->fromRoute('forgotten-password/notAuthenticated'));
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

    /**
     * @param $personId
     *
     * @return SecurityQuestionAnswersForm
     */
    private function getSecurityQuestionAnswersFormForPerson($personId)
    {
        $securityQuestions = $this->service->getQuestionsForPerson($personId);

        $form = new SecurityQuestionAnswersForm($securityQuestions[0], $securityQuestions[1]);

        $form->setAction($this->url()->fromRoute('forgotten-password/security-questions', ['personId' => $personId]));

        $form->setInputFilter(new SecurityQuestionAnswersInputFilter());

        return $form;
    }
}
