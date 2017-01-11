<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace UserAdmin\Controller;

use Dvsa\Mot\Frontend\PersonModule\Controller\PersonProfileController;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Constants\Role;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use UserAdmin\Presenter\UserProfilePresenter;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\ViewModel\UserProfile\TesterAuthorisationViewModel;
use Zend\View\Model\ViewModel;

/**
 * EmailAddress Controller.
 */
class EmailAddressController extends AbstractDvsaMotTestController
{
    const PAGE_TITLE          = 'Change email address';
    const PAGE_SUBTITLE_INDEX = 'User profile';

    /**
     * @var HelpdeskAccountAdminService
     */
    private $userAccountAdminService;

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var TesterGroupAuthorisationMapper
     */
    private $testerGroupAuthorisationMapper;

    /**
     * @var MapperFactory
     */
    private $mapperFactory;

    /**
     * @var PersonProfileUrlGenerator
     */
    private $personProfileUrlGenerator;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /**
     * EmailAddressController constructor.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param HelpdeskAccountAdminService      $userAccountAdminService
     * @param TesterGroupAuthorisationMapper   $testerGroupAuthorisationMapper
     * @param MapperFactory                    $mapperFactory
     * @param PersonProfileUrlGenerator        $personProfileUrlGenerator
     * @param ContextProvider                  $contextProvider
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        HelpdeskAccountAdminService $userAccountAdminService,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        MapperFactory $mapperFactory,
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        ContextProvider $contextProvider
    ) {
        $this->userAccountAdminService = $userAccountAdminService;
        $this->authorisationService = $authorisationService;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->mapperFactory = $mapperFactory;
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
        $this->contextProvider = $contextProvider;
    }

    /**
     * @throws UnauthorisedException
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $isNewPersonProfileEnabled = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE);
        $personId = $this->params()->fromRoute(true === $isNewPersonProfileEnabled ? 'id' : 'personId');

        if (true === $isNewPersonProfileEnabled) {
            $context = $this->contextProvider->getContext();
            if (!$this->canChangeEmailAddress($context)) {
                throw new UnauthorisedException(sprintf("Person with ID '%d' is not allowed to change email with context '%s'",
                    $personId, $context));
            }
        } else {
            $this->authorisationService->assertGranted(PermissionInSystem::PROFILE_EDIT_OTHERS_EMAIL_ADDRESS);
        }

        $presenter = $this->createPresenter($personId);
        $email = $emailConfirm = $presenter->displayEmail();

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost()->toArray();

            if (true === ($validated = $this->validate($params['email'], $params['emailConfirm']))) {
                $validated = $this->callApi($personId, $params['email'], $params['emailConfirm']);
            };

            if ($validated) {
                $url = (true === $isNewPersonProfileEnabled)
                    ? $this->personProfileUrlGenerator->toPersonProfile()
                    : UserAdminUrlBuilderWeb::of()->UserProfile($personId);

                $this->redirect()->toUrl($url);
            }

            $email        = $params['email'];
            $emailConfirm = $params['emailConfirm'];
        }
        $viewModel = $this->createViewModel($personId, self::PAGE_TITLE, $presenter, false, $email, $emailConfirm);
        $viewModel->setTemplate(UserProfilePresenter::CHANGE_EMAIL_TEMPLATE);

        return $viewModel;
    }

    /**
     * @param string $email
     * @param string $emailConfirm
     *
     * @return bool
     */
    private function validate($email, $emailConfirm)
    {
        $hasErrors = false;
        if ($email != $emailConfirm) {
            $this->addErrorMessage("Emails do not match");
            $hasErrors = true;
        }
        if ($email == '') {
            $this->addErrorMessage("Email cannot be blank");
            $hasErrors = true;
        }

        return !$hasErrors;
    }

    /**
     * @param integer $personId
     * @param string  $email
     *
     * @return bool
     */
    private function callApi($personId, $email)
    {
        $hasErrors = false;
        try {
            $this->userAccountAdminService->updatePersonContactEmail($personId, $email);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
            $hasErrors = true;
        }

        return !$hasErrors;
    }

    private function createPresenter($personId)
    {
        $presenter = new UserProfilePresenter(
            $this->userAccountAdminService->getUserProfile($personId),
            $this->getTesterAuthorisationViewModel($personId),
            null,
            $this->authorisationService->isGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE_DVSA_USER) &&
            !$this->authorisationService->hasRole(Role::CUSTOMER_SERVICE_CENTRE_OPERATIVE)
        );
        $presenter->setPersonId($personId);

        return $presenter;
    }

    /**
     * Create the view model with all the information needed.
     *
     * @param $personId
     * @param $pageTitle
     * @param \UserAdmin\Presenter\UserProfilePresenter $presenter
     * @param bool                                      $isProfile
     * @param $emailValue
     * @param $emailConfirmValue
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function createViewModel($personId, $pageTitle, UserProfilePresenter $presenter, $isProfile = false,
                                     $emailValue, $emailConfirmValue)
    {
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE_INDEX . ' - ' . $presenter->displayFullName());
        $this->layout()->setVariable('pageTitle', $pageTitle);
        $breadcrumbs = $this->getBreadcrumbs($personId, $presenter, $isProfile);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->layout('layout/layout-govuk.phtml');

        $resultViewModel = new ViewModel(
            [
                'presenter' => $presenter,
                'searchResultsUrl' => $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::of()->userResults()
                ),
                'searchQueryParams' => $this->getRequest()->getQuery()->toArray(),
                'emailAddressUrl' => $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ? $uri = $this->getRequest()->getUri() : UserAdminUrlBuilderWeb::emailChange($personId),
                'emailValue'      => $emailValue,
                'emailConfirmValue'      => $emailConfirmValue,
                'newProfileEnabled' => $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE),
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

    /**
     * Get the breadcrumbs given the context of the url.
     *
     * @param int                  $personId
     * @param UserProfilePresenter $presenter
     * @param bool                 $isProfile
     *
     * @return array
     */
    private function getBreadcrumbs($personId, UserProfilePresenter $presenter, $isProfile)
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE)) {
            return [
                'User search' => $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userSearch()),
                $presenter->displayTitleAndFullName() => $isProfile === false ? $this->buildUrlWithCurrentSearchQuery(
                    UserAdminUrlBuilderWeb::of()->UserProfile($personId)
                ) : '',
                 'Change email address' => '',
            ];
        }

        $breadcrumbs = [];
        $personName = $presenter->displayTitleAndFullName();
        $context = $this->contextProvider->getContext();

        if (ContextProvider::YOUR_PROFILE_CONTEXT === $context) {
            /*
             * Your Profile context.
             */
            $profileUrl = $isProfile === false ? $this->url()->fromRoute('newProfile', ['id' => $personId]) : '';
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__YOUR_PROFILE => $profileUrl];
        } elseif (ContextProvider::USER_SEARCH_CONTEXT === $context) {
            /*
             * User search context.
             */
            $userSearchUrl = $this->url()->fromRoute('user_admin/user-search');
            $profileUrl = $isProfile === false
                ? $this->url()->fromRoute('newProfileUserAdmin', ['id' => $personId]) : '';

            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl];
            $breadcrumbs += [$personName => $profileUrl];
        } elseif (ContextProvider::AE_CONTEXT === $context) {
            /*
             * AE context.
             */
            $aeId = $this->params()->fromRoute('authorisedExaminerId');
            $ae = $this->mapperFactory->Organisation->getAuthorisedExaminer($aeId);
            $aeUrl = $this->url()->fromRoute('authorised-examiner', ['id' => $aeId]);
            $breadcrumbs += [$ae->getName() => $aeUrl];

            $profileUrl = $isProfile === false ? $this->url()->fromRoute(ContextProvider::AE_PARENT_ROUTE, [
                'authorisedExaminerId' => $aeId, 'id' => $personId, ]) : '';
            $breadcrumbs += [$personName => $profileUrl];
        } elseif (ContextProvider::VTS_CONTEXT === $context) {
            /*
             * VTS context.
             */
            $vtsId = $this->params()->fromRoute('vehicleTestingStationId');
            $vts = $this->mapperFactory->Site->getById($vtsId);
            $ae = $vts->getOrganisation();

            if ($ae) {
                $aeUrl = $this->url()->fromRoute('authorised-examiner', ['id' => $ae->getId()]);
                $breadcrumbs += [$ae->getName() => $aeUrl];
            }

            $vtsUrl = $this->url()->fromRoute('vehicle-testing-station', ['id' => $vtsId]);
            $breadcrumbs += [$vts->getName() => $vtsUrl];
            $profileUrl = $isProfile === false ? $this->url()->fromRoute(ContextProvider::VTS_PARENT_ROUTE, [
                'vehicleTestingStationId' => $vtsId, 'id' => $personId, ]) : '';
            $breadcrumbs += [$personName => $profileUrl];
        } else {
            $userSearchUrl = $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userSearch());
            $profileUrl = $isProfile === false
                ? $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->UserProfile($personId)) : '';
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl, $personName => $profileUrl];
        }

        $breadcrumbs += ['Change email address' => ''];

        return $breadcrumbs;
    }

    /**
     * Change Email.
     *
     * Rule: When ANYONE View ’Your profile’ OR When SM, SU, A01, A02, VE, CSM, CSCO View ANYONE.
     *
     * @return bool
     */
    private function canChangeEmailAddress($context)
    {
        return (ContextProvider::YOUR_PROFILE_CONTEXT === $context ||
            $this->authorisationService->isGranted(PermissionInSystem::PROFILE_EDIT_OTHERS_EMAIL_ADDRESS));
    }
}
