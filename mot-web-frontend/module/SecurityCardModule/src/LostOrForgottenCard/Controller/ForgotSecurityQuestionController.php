<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\LogoutController;
use Zend\View\Model\ViewModel;

class ForgotSecurityQuestionController extends AbstractDvsaActionController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function forgotQuestionAnswerAction()
    {
        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('hideUserNav', true);

        $viewModel = new ViewModel();
        $viewModel->setTemplate('2fa/lost-forgotten/forgot-questions.twig');
        $viewModel->setVariable('config', $this->config['helpdesk']);
        $viewModel->setVariable('logout', LogoutController::ROUTE_LOGOUT);
        $viewModel->setVariable('headTitle', 'Forgotten security question(s)');

        return $viewModel;
    }
}
