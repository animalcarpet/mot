<?php

namespace UserAdmin\Controller;

use Application\Helper\PrgHelper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\Role;
use DvsaCommon\Enum\MessageTypeCode;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use UserAdmin\Presenter\UserProfilePresenter;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\View\Model\ViewModel;

/**
 * Class UserProfileController
 * @package UserAdmin\Controller
 */
class UserProfileController extends AbstractDvsaMotTestController
{
    const PAGE_SUBTITLE_INDEX = 'User profile';
    const RECLAIM_ACCOUNT_SUCCESS = 'Account reclaim by email was requested';
    const RECLAIM_ACCOUNT_FAILURE = 'Account reclaim by email has failed';

    /** @var HelpdeskAccountAdminService */
    private $userAccountAdminService;
    /** @var MotAuthorisationServiceInterface*/
    private $authorisationService;

    /**
     * @param MotAuthorisationServiceInterface $$authorisationService
     * @param HelpdeskAccountAdminService $userAccountAdminService
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        HelpdeskAccountAdminService $userAccountAdminService
    ) {
        $this->userAccountAdminService = $userAccountAdminService;
        $this->authorisationService = $authorisationService;
    }

    /**
     * Action to display the user profile for the helpdesk
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE);

        // Get the person ID from the URL
        $personId = $this->params()->fromRoute('personId');

        $presenter = new UserProfilePresenter(
            $this->userAccountAdminService->getUserProfile($personId),
            $this->authorisationService->isGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE_DVSA_USER) &&
            !$this->authorisationService->hasRole(Role::CUSTOMER_SERVICE_CENTRE_OPERATIVE)
        );
        $presenter->setId($personId);

        $viewModel = $this->createViewModel($personId, $presenter->displayTitleAndFullName(), $presenter, true);
        $viewModel->setTemplate($presenter->getTemplate());

        return $viewModel;
    }

    /**
     * Action to process the record of the password reset
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function passwordResetAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE);

        $personId = $this->params()->fromRoute('personId');
        $presenter = new UserProfilePresenter($this->userAccountAdminService->getUserProfile($personId));
        $presenter->setId($personId);
        $pageTitleSuccess = 'Reset password for ' . $presenter->displayFullName();
        $pageTitleFailure = 'Unable to reset password for ' . $presenter->displayFullName();

        return $this->processRequest(
            MessageTypeCode::PASSWORD_RESET_BY_LETTER,
            $presenter,
            $pageTitleSuccess,
            $pageTitleFailure
        );
    }

    /**
     * Action to process the record of the username reminder
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function usernameRecoverAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE);

        $personId = $this->params()->fromRoute('personId');
        $presenter = new UserProfilePresenter($this->userAccountAdminService->getUserProfile($personId));
        $presenter->setId($personId);
        $pageTitleSuccess = 'Recover username for ' . $presenter->displayFullName();

        return $this->processRequest(
            MessageTypeCode::PASSWORD_RESET_BY_LETTER,
            $presenter,
            $pageTitleSuccess,
            $pageTitleSuccess
        );
    }

    /**
     * Process the reset password/username remainder process to the api
     *
     * @param string $messageTypeCode
     * @param UserProfilePresenter $presenter
     * @param string $pageTitleSuccess
     * @param string $pageTitleFailure
     *
     * @return \Zend\Http\Response|ViewModel
     */
    private function processRequest($messageTypeCode, $presenter, $pageTitleSuccess, $pageTitleFailure)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::CREATE_MESSAGE_FOR_OTHER_USER);

        try {
            $params = [
                'personId'        => $presenter->getId(),
                'messageTypeCode' => $messageTypeCode
            ];
            $this->userAccountAdminService->postMessage($params);
        } catch (ValidationException $e) {
            $view = $this->createViewModel($presenter->getId(), $pageTitleFailure, $presenter);
            $view->setVariable('isFailure', true);
            return $view;
        }

        return $this->createViewModel($presenter->getId(), $pageTitleSuccess, $presenter);
    }

    /**
     * Action to display the reset claim account page
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function claimAccountAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE);
        $personId = $this->params()->fromRoute('personId');

        $prgHelper = new PrgHelper($this->getRequest());
        if ($prgHelper->isRepeatPost()) {
            return $this->redirect()->toUrl($prgHelper->getRedirectUrl());
        };

        if ($this->getRequest()->isPost() === true) {
            return $this->claimAccountProcess($personId, $prgHelper);
        }
        $presenter = new UserProfilePresenter($this->userAccountAdminService->getUserProfile($personId));

        $pageTitle = 'Reclaim account';
        $view = $this->createViewModel($personId, $pageTitle, $presenter);
        return $view->setVariable('prgHelper', $prgHelper);
    }

    /**
     * Process the claim account reset process to the api
     *
     * @param int $personId
     * @param PrgHelper $prgHelper
     * @return \Zend\Http\Response
     */
    private function claimAccountProcess($personId, PrgHelper $prgHelper)
    {
        $url = $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::userProfile($personId));

        try {
            $this->userAccountAdminService->resetClaimAccount($personId);
            $this->addSuccessMessage(self::RECLAIM_ACCOUNT_SUCCESS);
        } catch (\Exception $e) {
            $this->addErrorMessage(self::RECLAIM_ACCOUNT_FAILURE);
        }

        $prgHelper->setRedirectUrl($url);

        return $this->redirect()->toUrl($url);
    }

    /**
     * Create the view model with all the information needed
     *
     * @param int                  $personId
     * @param string               $pageTitle
     * @param UserProfilePresenter $presenter
     * @param bool                 $isProfile
     *
     * @return ViewModel
     */
    private function createViewModel($personId, $pageTitle, UserProfilePresenter $presenter, $isProfile = false)
    {
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE_INDEX);
        $this->layout()->setVariable('pageTitle', $pageTitle);
        $breadcrumbs = [
            'User search' => $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userSearch()),
            $presenter->displayTitleAndFullName() =>
                $isProfile === false ? $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::of()->userProfile($personId)
                ) : '',
        ];
        $this->layout()->setVariable('progressBar', ['breadcrumbs' => $breadcrumbs]);
        $this->layout('layout/layout-govuk.phtml');

        $resultViewModel = new ViewModel(
            [
                'presenter' => $presenter,
                'passwordResetUrl' => $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::userProfileResetPassword($personId)
                ),
                'usernameRecoverUrl' => $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::userProfileRecoverUsername($personId)
                ),
                'searchResultsUrl' => $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::of()->userResults()
                ),
                'resetClaimAccountUrl' => $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::userProfileClaimAccount($personId)
                ),
                'resetClaimAccountUrlPost' => $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::userProfileClaimAccountPost($personId)
                ),
                'userProfileUrl' => $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::userProfile($personId)
                ),
            ]
        );

        return $resultViewModel;
    }

    /**
     * Build a url with the query params
     *
     * @param string $url
     *
     * @return string
     */
    private function buildUrlWithCurrentSearchQuery($url)
    {
        $params = $this->getRequest()->getQuery()->toArray();
        if (empty($params)) {
            return $url;
        }
        return $url . '?' . http_build_query($params);
    }
}
