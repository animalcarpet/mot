<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller;

use Core\Controller\AbstractDvsaActionController;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
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
        $helpdesk = $this->config['helpdesk'];
        $viewModel = new ViewModel([]);
        $viewModel->setVariable('helpdesk', $helpdesk);
        $viewModel->setTemplate('2fa/lost-forgotten/forgot-question');
        return $viewModel;
    }
}