<?php

namespace Application\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Utility\ArrayUtils;
use Zend\View\Model\ViewModel;

/**
 * ReportController.
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReportController extends AbstractAuthActionController
{
    /**
     * Choose whether to download the report as CSV or PDF.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function chooseAction()
    {
        $request = $this->getRequest();

        $routeParameters = $this->params()->fromQuery('routeParameters');

        if ($routeParameters == null) {
            $routeParameters = $this->params()->fromPost('routeParameters');
        }

        $authorisedExaminerId = ArrayUtils::tryGet($routeParameters, 'id', 0);

        $this->getAuthorisationService()->assertGrantedAtOrganisation(
            PermissionAtOrganisation::AE_SLOTS_USAGE_READ,
            $authorisedExaminerId
        );

        if ($request->isPost()) {
            $route = $this->params()->fromPost('route');
            $format = $this->params()->fromPost('format');
            $routeParameters = $this->params()->fromPost('routeParameters');
            $routeParameters['extension'] = '.'.$format;
            $this->redirect()->toRoute($route, $routeParameters, ['query' => ['format' => $format]]);
        }

        $route = $this->params()->fromQuery('route');
        $routeParameters = $this->params()->fromQuery('routeParameters');
        $returnTo = $this->params()->fromQuery('returnTo');

        $view = new ViewModel(array('route' => $route, 'routeParameters' => $routeParameters, 'returnTo' => $returnTo));

        // If we have requested this via ajax, we just want to return this view with no layout
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }

        return $view;
    }

    /**
     * @return MotAuthorisationServiceInterface
     */
    public function getAuthorisationService()
    {
        return $this->getServiceLocator()->get('AuthorisationService');
    }
}
