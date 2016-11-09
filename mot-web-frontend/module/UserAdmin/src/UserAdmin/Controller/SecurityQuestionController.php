<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace UserAdmin\Controller;

use Account\AbstractClass\AbstractSecurityQuestionController;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use UserAdmin\ViewModel\SecurityQuestionViewModel;
use Zend\View\Model\ViewModel;

/**
 * Class SecurityQuestionController.
 */
class SecurityQuestionController extends AbstractSecurityQuestionController
{
    const PAGE_TITLE    = 'User profile';
    const PAGE_SUBTITLE = 'Authenticate %s';

    /**
     * This action is the end point to enter the question answer for the help desk.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->getAuthorizationService()->assertGranted(PermissionInSystem::SECURITY_QUESTION_READ_USER);

        $personId   = $this->params()->fromRoute('personId');
        $questionNumber = $this->params()->fromRoute('questionNumber', 1);

        $viewModel = $this->createViewModel();
        $view = $this->index($personId, $questionNumber, $viewModel);

        if ($view instanceof ViewModel) {
            $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);
            $this->layout()->setVariable(
                'pageSubTitle',
                sprintf(self::PAGE_SUBTITLE, $this->viewModel->getPerson()->getFullName())
            );

            $breadcrumbs = $this->createBreadcrumbs();
            $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        }

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

    /**
     * @return array
     */
    private function createBreadcrumbs()
    {
        $breadcrumbs = [
            'User search' => UserAdminUrlBuilderWeb::of()->userSearch(),
            'Authenticate user' => '',
        ];

        return $breadcrumbs;
    }
}
