<?php

namespace Application\Controller;

use Core\Controller\AbstractDvsaActionController;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Class CookiesController
 *
 * @package Application\Controller
 */
class CookiesController extends AbstractDvsaActionController {

    public function indexAction()
    {
        $view = new ViewModel();

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => ['Cookies' => '']]);
        $this->layout()->setVariable('pageTitle', 'Cookies');
        $this->setHeadTitle('Cookies');

        $view->setTemplate('application/index/cookies.phtml');

        return $view;
    }
}