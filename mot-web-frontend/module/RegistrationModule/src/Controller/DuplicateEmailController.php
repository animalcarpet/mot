<?php

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Core\Controller\AbstractDvsaActionController;
use Zend\View\Model\ViewModel;

class DuplicateEmailController extends AbstractDvsaActionController
{
    const TITLE = 'This email is already in use';

    public function indexAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('dvsa/duplicate-email/index.twig');

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariables([
            'pageTitle' => self::TITLE,
            'pageSubTitle' => RegistrationBaseController::DEFAULT_SUB_TITLE,
        ]);

        return $viewModel;
    }
}
