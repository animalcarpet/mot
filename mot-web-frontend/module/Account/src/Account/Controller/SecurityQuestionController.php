<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
namespace Account\Controller;

use Account\AbstractClass\AbstractSecurityQuestionController;
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

    /**
     * This action is the end point to enter the question answer for the help desk.
     *
     * @return \Zend\Http\Response|ViewModel
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
     * @return SecurityQuestionViewModel
     */
    private function createViewModel()
    {
        /** @var PersonProfileUrlGenerator $personProfileUrlGenerator */
        $personProfileUrlGenerator = $this->getServiceLocator()->get(PersonProfileUrlGenerator::class);

        return new SecurityQuestionViewModel($this->service, $personProfileUrlGenerator);
    }
}
