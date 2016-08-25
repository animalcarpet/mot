<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace UserAdmin\Controller;

use Application\Helper\PrgHelper;
use Application\Service\CatalogService;
use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dashboard\ViewModel\Sidebar\ProfileSidebar;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\MessageTypeCode;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use UserAdmin\Presenter\UserProfilePresenter;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\Service\PersonRoleManagementService;
use UserAdmin\ViewModel\UserProfile\TesterAuthorisationViewModel;
use Zend\View\Model\ViewModel;

/**
 * UserProfile Controller.
 */
class UserProfileController extends AbstractDvsaMotTestController
{
    const PAGE_SUBTITLE_INDEX = 'User profile';
    const RECLAIM_ACCOUNT_SUCCESS = 'Account reclaim by email was requested';
    const RECLAIM_ACCOUNT_FAILURE = 'Account reclaim by email has failed';
    const RECLAIM_ACCOUNT_SYSTEM_MESSAGE_NON_2FA_USER = 'This will reset the user\'s password and require them to set up their security questions and PIN when they next sign in.';
    const RECLAIM_ACCOUNT_SYSTEM_MESSAGE_2FA_USER = 'This will reset the user\'s password and require them to set up their security questions when they next sign in.';

    /**
     * @var HelpdeskAccountAdminService
     */
    private $userAccountAdminService;

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var TesterGroupAuthorisationMapper
     */
    private $testerGroupAuthorisationMapper;

    /**
     * @var PersonRoleManagementService
     */
    private $personRoleManagementService;

    /**
     * @var ViewTradeRolesAssertion
     */
    private $viewTradeRolesAssertion;

    /**
     * @var RegisteredCardService
     */
    private $registeredCardService;

    /**
     * @var TwoFaFeatureToggle
     */
    private $twoFaFeatureToggle;


    /**
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param HelpdeskAccountAdminService      $userAccountAdminService
     * @param TesterGroupAuthorisationMapper   $testerGroupAuthorisationMapper
     * @param PersonRoleManagementService      $personRoleManagementService
     * @param CatalogService                   $catalogService
     * @param ViewTradeRolesAssertion          $viewTradeRolesAssertion
     * @param RegisteredCardService            $registeredCardService
     * @param TwoFaFeatureToggle               $twoFaFeatureToggle
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        HelpdeskAccountAdminService $userAccountAdminService,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        PersonRoleManagementService $personRoleManagementService,
        CatalogService $catalogService,
        ViewTradeRolesAssertion $viewTradeRolesAssertion,
        RegisteredCardService $registeredCardService,
        TwoFaFeatureToggle $twoFaFeatureToggle
    ) {
        $this->userAccountAdminService = $userAccountAdminService;
        $this->authorisationService = $authorisationService;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->personRoleManagementService = $personRoleManagementService;
        $this->catalogService = $catalogService;
        $this->viewTradeRolesAssertion = $viewTradeRolesAssertion;
        $this->registeredCardService = $registeredCardService;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
    }

    /**
     * Action to display the user profile for the helpdesk.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE);

        // Get the person ID from the URL
        $personId = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
            $this->params()->fromRoute('id') :
            $this->params()->fromRoute('personId');

        $presenter = new UserProfilePresenter(
            $this->userAccountAdminService->getUserProfile($personId),
            $this->getTesterAuthorisationViewModel($personId),
            $this->catalogService,
            $this->authorisationService->isGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE_DVSA_USER),
            $this->personRoleManagementService
        );
        $presenter->setPersonId($personId);

        $viewModel = $this->createViewModel($personId, $presenter->displayTitleAndFullName(), $presenter, true);
        $viewModel->setTemplate($presenter->getTemplate());

        if ($this->viewTradeRolesAssertion->shouldViewLink($personId, $presenter->hasDvsaRoles(), $presenter->hasTradeRoles())) {
            $this->setSidebar(new ProfileSidebar($personId, $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE)));
        }

        return $viewModel;
    }

    /**
     * Action to process the record of the password reset.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function passwordResetAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE);

        $personId = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
            $this->params()->fromRoute('id') :
            $this->params()->fromRoute('personId');

        $presenter = new UserProfilePresenter(
            $this->userAccountAdminService->getUserProfile($personId),
            $this->getTesterAuthorisationViewModel($personId),
            $this->catalogService
        );
        $presenter->setPersonId($personId);
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
     * Action to process the record of the username reminder.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function usernameRecoverAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE);

        $personId = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
            $this->params()->fromRoute('id') :
            $this->params()->fromRoute('personId');

        $presenter = new UserProfilePresenter(
            $this->userAccountAdminService->getUserProfile($personId),
            $this->getTesterAuthorisationViewModel($personId),
            $this->catalogService
        );
        $presenter->setPersonId($personId);
        $pageTitleSuccess = 'Recover username for ' . $presenter->displayFullName();

        return $this->processRequest(
            MessageTypeCode::USERNAME_REMINDER_BY_LETTER,
            $presenter,
            $pageTitleSuccess,
            $pageTitleSuccess
        );
    }

    /**
     * Process the reset password/username remainder process to the api.
     *
     * @param string               $messageTypeCode
     * @param UserProfilePresenter $presenter
     * @param string               $pageTitleSuccess
     * @param string               $pageTitleFailure
     *
     * @return \Zend\Http\Response|ViewModel
     */
    private function processRequest($messageTypeCode, $presenter, $pageTitleSuccess, $pageTitleFailure)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::CREATE_MESSAGE_FOR_OTHER_USER);

        try {
            $params = [
                'personId'        => $presenter->getPersonId(),
                'messageTypeCode' => $messageTypeCode,
            ];
            $this->userAccountAdminService->postMessage($params);
        } catch (ValidationException $e) {
            $view = $this->createViewModel($presenter->getPersonId(), $pageTitleFailure, $presenter);
            $view->setVariable('isFailure', true);

            return $view;
        }

        return $this->createViewModel($presenter->getPersonId(), $pageTitleSuccess, $presenter);
    }

    /**
     * Action to display the reset claim account page.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function claimAccountAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE);
        $personId = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
            $this->params()->fromRoute('id') :
            $this->params()->fromRoute('personId');

        $person = $this->userAccountAdminService->getUserProfile($personId);

        $prgHelper = new PrgHelper($this->getRequest());
        if ($prgHelper->isRepeatPost()) {
            return $this->redirect()->toUrl($prgHelper->getRedirectUrl());
        };

        if ($this->getRequest()->isPost() === true) {
            return $this->claimAccountProcess($personId, $prgHelper);
        }
        $presenter = new UserProfilePresenter(
            $person,
            $this->getTesterAuthorisationViewModel($personId),
            $this->catalogService
        );

        $pageTitle = 'Reclaim account';

        /* @var bool */
        $is2faActiveUser =$this->twoFaFeatureToggle->isEnabled() &&
            $this->registeredCardService->is2faActiveUser($person->getUserName());

        $view = $this->createViewModel($personId, $pageTitle, $presenter, false, $is2faActiveUser);

        return $view->setVariable('prgHelper', $prgHelper);
    }

    /**
     * Process the claim account reset process to the api.
     *
     * @param int       $personId
     * @param PrgHelper $prgHelper
     *
     * @return \Zend\Http\Response
     */
    private function claimAccountProcess($personId, PrgHelper $prgHelper)
    {
        $url = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
            $this->url()->fromRoute('newProfileUserAdmin', ['id' => $personId]) :
            $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::userProfile($personId));

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
     * Create the view model with all the information needed.
     *
     * @param int                  $personId
     * @param string               $pageTitle
     * @param UserProfilePresenter $presenter
     * @param bool                 $isProfile
     * @param bool                 $isFor2FaEnabledUser
     *
     * @return ViewModel
     */
    private function createViewModel($personId, $pageTitle, UserProfilePresenter $presenter,
                                     $isProfile = false, $isFor2FaEnabledUser = false)
    {
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE_INDEX);
        $this->layout()->setVariable('pageTitle', $pageTitle);

        if (false === $isProfile) {
            $userProfileUrl = (true === $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE))
                ? $this->url()->fromRoute(ContextProvider::USER_SEARCH_PARENT_ROUTE, ['id' => $personId])
                : $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userProfile($personId));
        } else {
            $userProfileUrl = '';
        }

        $breadcrumbs = [
            'User search' => $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userSearch()),
            $presenter->displayTitleAndFullName() => $userProfileUrl,
        ];

        if (true === $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE)) {
            $breadcrumbs += ['Reclaim account' => ''];
        }

        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
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
                'searchQueryParams' => $this->getRequest()->getQuery()->toArray(),
                'resetClaimAccountUrl' => $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::userProfileClaimAccount($personId)
                ),
                'resetClaimAccountUrlPost' => $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::userProfileClaimAccountPost($personId)
                ),
                'userProfileUrl' => $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
                    $this->url()->fromRoute('newProfileUserAdmin', ['id' => $personId]) :
                    $this->buildUrlWithCurrentSearchQuery(
                        UserAdminUrlBuilderWeb::userProfile($personId)
                    ),
                'reclaimSystemMessage' => $isFor2FaEnabledUser ?
                    self::RECLAIM_ACCOUNT_SYSTEM_MESSAGE_2FA_USER :
                    self::RECLAIM_ACCOUNT_SYSTEM_MESSAGE_NON_2FA_USER,

            ]
        );

        return $resultViewModel;
    }

    /**
     * Build a url with the query params.
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

    private function getTesterAuthorisationViewModel($personId)
    {
        return new TesterAuthorisationViewModel(
            $personId,
            $this->testerGroupAuthorisationMapper->getAuthorisation($personId),
            $this->authorisationService
        );
    }
}
