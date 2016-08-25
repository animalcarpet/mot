<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dashboard\Controller;

use Account\AbstractClass\AbstractSecurityQuestionController;
use Dashboard\ViewModel\SecurityQuestionViewModel;
use Dvsa\Mot\Frontend\PersonModule\Controller\PersonProfileController;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaCommon\Constants\FeatureToggle;
use Zend\View\Model\ViewModel;

/**
 * Class SecurityQuestionController.
 */
class SecurityQuestionController extends AbstractSecurityQuestionController
{
    const PAGE_TITLE = 'Reset your PIN';

    /**
     * This action is the end point to enter the question answer for the help desk.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        if($this->getIdentity()->isSecondFactorRequired()) {
            return $this->notFoundAction();
        }
        $personId = $this->getIdentity()->getUserId();
        $questionNumber = $this->params()->fromRoute('questionNumber', 1);
        $viewModel = $this->createViewModel();

        $this->layout()->setVariable('pageSubTitle', PersonProfileController::CONTENT_HEADER_TYPE__YOUR_PROFILE);
        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);

        $breadcrumbs = [
            PersonProfileController::CONTENT_HEADER_TYPE__YOUR_PROFILE => $this->url()->fromRoute(ContextProvider::YOUR_PROFILE_PARENT_ROUTE),
            self::PAGE_TITLE => '',
        ];
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        return $this->index($personId, $questionNumber, $viewModel);
    }

    /**
     * @return SecurityQuestionViewModel
     */
    private function createViewModel()
    {
        $isNewPersonProfileEnabled = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE);
        /** @var PersonProfileUrlGenerator $personProfileUrlGenerator */
        $personProfileUrlGenerator = $this->getServiceLocator()->get(PersonProfileUrlGenerator::class);

        return new SecurityQuestionViewModel($this->service, $isNewPersonProfileEnabled, $personProfileUrlGenerator);
    }
}
