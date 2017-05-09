<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace UserAdmin\Controller;

use Core\Action\ViewActionResult;
use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\PersonModule\Controller\PersonProfileController;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\Role;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use UserAdmin\Form\ChangeEmailForm;
use UserAdmin\Presenter\UserProfilePresenter;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\Service\IsEmailDuplicateService;
use UserAdmin\ViewModel\ChangeEmailViewModel;
use UserAdmin\ViewModel\UserProfile\TesterAuthorisationViewModel;
use Zend\Http\Request;

/**
 * EmailAddress Controller.
 */
class EmailAddressController extends AbstractDvsaActionController
{
    const CHANGE_EMAIL_TEMPLATE = 'user-admin/email-address/form.phtml';

    const PAGE_TITLE = 'Change email address';
    const PAGE_SUBTITLE_YOUR_PROFILE = 'Your profile';
    const PAGE_SUBTITLE_USER_PROFILE = 'User profile';
    const PAGE_SUBTITLE_INDEX = 'User profile';

    const MAX_EMAIL_LENGTH = 255;

    const MSG_EMAIL_CHANGED_SUCCESS = 'Email address has been changed successfully.';
    const MSG_EMAIL_CHANGED_FAILURE = 'Email address could not be changed. Please try again.';

    const MSG_DUPLICATE_EMAIL_ERROR = 'Email address - This email address is already in use. Each account must have a different email address.';
    const MSG_BLANK_EMAIL_ERROR = 'Enter your email address';
    const MSG_INVALID_EMAIL_ERROR = 'Enter a valid email address';
    const MSG_EMAILS_DONT_MATCH_ERROR = 'The email addresses must be the same';

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var HelpdeskAccountAdminService
     */
    private $userAccountAdminService;

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

    /** @var IsEmailDuplicateService */
    private $duplicateEmailService;

    /** @var Request */
    protected $request;

    /** @var MotIdentityProviderInterface */
    private $identityProvider;

    /**
     * EmailAddressController constructor.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param HelpdeskAccountAdminService      $userAccountAdminService
     * @param TesterGroupAuthorisationMapper   $testerGroupAuthorisationMapper
     * @param MapperFactory                    $mapperFactory
     * @param PersonProfileUrlGenerator        $personProfileUrlGenerator
     * @param ContextProvider                  $contextProvider
     * @param IsEmailDuplicateService          $duplicateEmailService
     * @param Request                          $request
     * @param MotIdentityProviderInterface     $identityProvider
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        HelpdeskAccountAdminService $userAccountAdminService,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        MapperFactory $mapperFactory,
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        ContextProvider $contextProvider,
        IsEmailDuplicateService $duplicateEmailService,
        Request $request,
        MotIdentityProviderInterface $identityProvider
    ) {
        $this->userAccountAdminService = $userAccountAdminService;
        $this->authorisationService = $authorisationService;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->mapperFactory = $mapperFactory;
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
        $this->contextProvider = $contextProvider;
        $this->duplicateEmailService = $duplicateEmailService;
        $this->request = $request;
        $this->identityProvider = $identityProvider;
    }

    public function indexAction()
    {
        $personId = $this->getPersonId();

        $context = $this->contextProvider->getContext();

        if (!$this->canChangeEmailAddress($context)) {
            throw new UnauthorisedException(sprintf("Person with ID '%d' is not allowed to change email with context '%s'",
                $personId, $context));
        }

        $result = new ViewActionResult();
        $viewModel = new ChangeEmailViewModel();
        $result->layout()->setPageTitle(self::PAGE_TITLE);
        if ($this->contextProvider->getContext() === ContextProvider::YOUR_PROFILE_CONTEXT) {
            $result->layout()->setPageSubTitle(self::PAGE_SUBTITLE_YOUR_PROFILE);
        } elseif ($this->contextProvider->getContext() === ContextProvider::USER_SEARCH_CONTEXT) {
            $result->layout()->setPageSubTitle(self::PAGE_SUBTITLE_USER_PROFILE);
        }
        $result->setTemplate(self::CHANGE_EMAIL_TEMPLATE);

        $this->setHeadTitle('Change email address');

        $presenter = $this->createPresenter($personId);

        if ($presenter) {
            $currentEmail = $presenter->displayEmail();
        }

        $form = new ChangeEmailForm($currentEmail);

        if ($this->request->isPost()) {
            $params = $this->request->getPost()->toArray();
            $email = $params['email'];
            $emailConfirm = $params['emailConfirm'];

            $form->getEmail()->setValue($email);
            $form->getEmailConfirm()->setValue($emailConfirm);

            if ($form->isValid()) {
                if ($this->duplicateEmailService->isEmailDuplicate($email)) {
                    $this->addErrorMessageForKey('duplicateEmailValidation', self::MSG_DUPLICATE_EMAIL_ERROR);
                } else {
                    try {
                        $validated = $this->callApi($personId, $params['email']);
                        $this->flashMessenger()->addSuccessMessage(self::MSG_EMAIL_CHANGED_SUCCESS);
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage(self::MSG_EMAIL_CHANGED_FAILURE);
                    }

                    if ($validated) {
                        $url = $this->personProfileUrlGenerator->toPersonProfile();

                        return $this->redirect()->toUrl($url);
                    }
                }
            }
        }

        $viewModel->setIsViewingOwnProfile($this->contextProvider->getContext() === ContextProvider::YOUR_PROFILE_CONTEXT);

        $viewModel->setForm($form);

        $result->setViewModel($viewModel);

        $breadcrumbs = $this->getBreadcrumbs($personId, $presenter, false);

        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        return $this->applyActionResult($result);
    }

    /**
     * @param int    $personId
     * @param string $email
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
     * @return int
     */
    private function getPersonId()
    {
        $context = $this->contextProvider->getContext();

        return $context === ContextProvider::YOUR_PROFILE_CONTEXT ?
            $this->identityProvider->getIdentity()->getUserId() : (int) $this->params()->fromRoute('id', null);
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
        $params = $this->request->getQuery()->toArray();
        if (empty($params)) {
            return $url;
        }

        return $url.'?'.http_build_query($params);
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
                ? $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userProfile($personId)) : '';
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
        return ContextProvider::YOUR_PROFILE_CONTEXT === $context ||
            $this->authorisationService->isGranted(PermissionInSystem::PROFILE_EDIT_OTHERS_EMAIL_ADDRESS);
    }
}
