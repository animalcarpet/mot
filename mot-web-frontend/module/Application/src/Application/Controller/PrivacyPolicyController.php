<?php

namespace Application\Controller;

use Core\Controller\AbstractDvsaActionController;
use Zend\View\Model\ViewModel;

/**
 * Class PrivacyPolicyController
 */
class PrivacyPolicyController extends AbstractDvsaActionController
{
    public function indexAction()
    {
        $view = new ViewModel();

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => ['Privacy Policy' => '']]);
        $this->layout()->setVariable('pageTitle', 'Your personal information');
        $this->setHeadTitle('Your personal information');

        $view->setTemplate('application/index/privacy-policy.phtml');

        return $view;
    }
}