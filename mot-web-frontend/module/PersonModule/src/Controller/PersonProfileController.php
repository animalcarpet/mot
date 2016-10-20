<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Application\Data\ApiPersonalDetails;
use Application\Helper\DataMappingHelper;
use Application\Service\CatalogService;
use Core\Controller\AbstractAuthActionController;
use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dashboard\Controller\UserHomeController;
use Dashboard\Data\ApiDashboardResource;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Application\Service\CanTestWithoutOtpService;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileSidebar;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\MapperFactory;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use Exception;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCard;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\View\Model\ViewModel;

/**
 * Controller for the Person Profile page.
 */
class PersonProfileController extends AbstractAuthActionController
{
    const CONTENT_HEADER_TYPE__USER_SEARCH = 'User search';
    const CONTENT_HEADER_TYPE__YOUR_PROFILE = 'Your profile';
    const ERR_PIN_UPDATE_FAIL = 'There was a problem updating your PIN.';
    const ERR_COMMON_API = 'Something went wrong.';

    /** @var ApiPersonalDetails */
    private $personalDetailsService;

    /** @var ApiDashboardResource */
    private $dashboardResourceService;

    /** @var CatalogService */
    private $catalogService;

    /** @var UserAdminSessionManager */
    private $userAdminSessionManager;

    /** @var ViewTradeRolesAssertion */
    private $viewTradeRolesAssertion;

    /** @var PersonProfileGuardBuilder */
    private $personProfileGuardBuilder;

    /** @var MapperFactory */
    private $mapperFactory;

    /** @var ContextProvider */
    private $contextProvider;

    /** @var CanTestWithoutOtpService */
    private $canTestWithoutOtpService;

    /** @var SecurityCardService */
    private $securityCardService;

    /** @var SecurityCardGuard */
    private $securityCardGuard;

    /**
     * @param ApiPersonalDetails $personalDetailsService
     * @param ApiDashboardResource $dashboardResourceService
     * @param CatalogService $catalogService
     * @param UserAdminSessionManager $userAdminSessionManager
     * @param ViewTradeRolesAssertion $canViewTradeRolesAssertion
     * @param PersonProfileGuardBuilder $personProfileGuardBuilder
     * @param MapperFactory $mapperFactory
     * @param ContextProvider $contextProvider
     * @param CanTestWithoutOtpService $canTestWithoutOtpService
     * @param SecurityCardService $securityCardService
     * @param SecurityCardGuard $securityCardGuard
     * @param TwoFaFeatureToggle $twoFaFeatureToggle
     */
    public function __construct(ApiPersonalDetails $personalDetailsService,
                                ApiDashboardResource $dashboardResourceService,
                                CatalogService $catalogService,
                                UserAdminSessionManager $userAdminSessionManager,
                                ViewTradeRolesAssertion $canViewTradeRolesAssertion,
                                PersonProfileGuardBuilder $personProfileGuardBuilder,
                                MapperFactory $mapperFactory,
                                ContextProvider $contextProvider,
                                CanTestWithoutOtpService $canTestWithoutOtpService,
                                SecurityCardService $securityCardService,
                                SecurityCardGuard $securityCardGuard,
                                TwoFaFeatureToggle $twoFaFeatureToggle
    ) {
        $this->personalDetailsService = $personalDetailsService;
        $this->dashboardResourceService = $dashboardResourceService;
        $this->catalogService = $catalogService;
        $this->userAdminSessionManager = $userAdminSessionManager;
        $this->viewTradeRolesAssertion = $canViewTradeRolesAssertion;
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
        $this->mapperFactory = $mapperFactory;
        $this->contextProvider = $contextProvider;
        $this->canTestWithoutOtpService = $canTestWithoutOtpService;
        $this->securityCardService = $securityCardService;
        $this->securityCardGuard = $securityCardGuard;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
    }

    /**
     * @return array|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE)) {
            return $this->notFoundAction();
        }

        $this->userAdminSessionManager->deleteUserAdminSession();
        $this->layout('layout/layout-govuk.phtml');
        $data = $this->getAuthenticatedData();

        /** @var PersonalDetails $personDetails */
        $personDetails = $data['personalDetails'];

        $personId = $this->getPersonId();
        $context = $this->contextProvider->getContext();
        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard($personDetails, $context);

        $profileSidebar = $this->createProfileSidebar($personId, $personProfileGuard);
        $this->setSidebar($profileSidebar);

        $securityCard = null;
        if ($personProfileGuard->canViewSecurityCard() && $this->hasActiveSecurityCard($personDetails->getUsername())) {
            $securityCard = $this->securityCardService->getSecurityCardForUser($personDetails->getUsername());
        }

        $breadcrumbs = $this->generateBreadcrumbsFromRequest($context, $personDetails);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $routeName = $this->getRouteName();
        $routeParams = $this->getRouteParams($personDetails, $routeName);

        return $this->createViewModel('profile/index.phtml', [
            'personalDetails' => $personDetails,
            'systemRoles' => $this->getSystemRoles($personDetails),
            'personProfileGuard' => $personProfileGuard,
            'userHomeRoute' => UserHomeController::ROUTE,
            'routeName' => $routeName,
            'routeParams' => $routeParams,
            'context' => $context,
            'userSearchResultUrl' => $this->getUserSearchResultUrl(),
            'securityCard'              => $securityCard,
            'displayResetAccountError' => $personProfileGuard->canSeeResetAccountByEmailButton()
        ]);
    }

    /**
     * @return array
     */
    public function securitySettingsAction()
    {
        $userId = $this->getIdentity()->getUserId();

        if($this->getIdentity()->isSecondFactorRequired()) {
            return $this->notFoundAction();
        }

        if ($this->userAdminSessionManager->isUserAuthenticated($userId) !== true) {
            $url = $this->url()->fromRoute(ContextProvider::YOUR_PROFILE_PARENT_ROUTE . '/security-questions', [
                'id' => $userId,
            ]);

            return $this->redirect()->toUrl($url);
        }

        $personalInfo = $this->getAuthenticatedData();

        /** @var PersonalDetails $personalDetails */
        $personalDetails = $personalInfo['personalDetails'];

        $returnData = [
            'fullName' => $personalDetails->getFullName(),
            'config' => $this->getConfig(),
            'userId' => $userId,
        ];

        if ($this->getRequest()->isPost()) {
            try {
                $apiUrl = PersonUrlBuilder::resetPin($userId);
                $responseData = $this->getRestClient()->put($apiUrl, null);

                $returnData['pin'] = $responseData['data']['pin'];
                $this->flashMessenger()->clearMessages();
            } catch (Exception $e) {
                $errorMessage = ($e instanceof GeneralRestException) ? self::ERR_PIN_UPDATE_FAIL : self::ERR_COMMON_API;
                $this->flashMessenger()->addErrorMessage($errorMessage);
            }
        } else {
            $this->layout('layout/layout-govuk.phtml');
        }

        $breadcrumbs = [
            self::CONTENT_HEADER_TYPE__YOUR_PROFILE => $this->url()->fromRoute(ContextProvider::YOUR_PROFILE_PARENT_ROUTE),
            'Reset your PIN' => '',
        ];

        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->layout()->setVariable('pageSubTitle', self::CONTENT_HEADER_TYPE__YOUR_PROFILE);
        $this->layout()->setVariable('pageTitle', 'Reset your PIN');

        return $this->createViewModel('profile/security-settings.phtml', $returnData);
    }

    /**
     * @param string $template
     * @param array $variables
     *
     * @return ViewModel
     */
    private function createViewModel($template, array $variables)
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate($template);
        $viewModel->setVariables($variables);

        return $viewModel;
    }

    /**
     * @param array $personalDetailsData
     *
     * @return array
     */
    private function getAuthenticatedData($personalDetailsData = [])
    {
        $personId = $this->getPersonId();
        $identity = $this->getIdentity();

        $personalDetailsData = array_merge(
            $this->personalDetailsService->getPersonalDetailsData($personId),
            $personalDetailsData
        );

        $personalDetails = new PersonalDetails($personalDetailsData);

        $authorisations = $this->personalDetailsService->getPersonalAuthorisationForMotTesting($personId);

        $isViewingOwnProfile = ($identity->getUserId() == $personId);

        return [
            'personalDetails' => $personalDetails,
            'motAuthorisations' => $authorisations,
            'isViewingOwnProfile' => $isViewingOwnProfile,
            'systemRoles' => $this->getSystemRoles($personalDetails),
        ];
    }

    /**
     * @return int
     */
    private function getPersonId()
    {
        $context = $this->contextProvider->getContext();

        return $context === ContextProvider::YOUR_PROFILE_CONTEXT ?
            $this->getIdentity()->getUserId() : (int)$this->params()->fromRoute('id', null);
    }

    /**
     * @param $context
     * @param PersonalDetails $personalDetails
     *
     * @return array
     */
    private function generateBreadcrumbsFromRequest($context, PersonalDetails $personalDetails)
    {
        $breadcrumbs = [];

        if (ContextProvider::AE_CONTEXT === $context) {
            /*
             * AE context.
             */
            $aeId = $this->params()->fromRoute('authorisedExaminerId');
            $ae = $this->mapperFactory->Organisation->getAuthorisedExaminer($aeId);
            $aeUrl = $this->url()->fromRoute('authorised-examiner', ['id' => $ae->getId()]);
            $breadcrumbs += [$ae->getName() => $aeUrl];
            $breadcrumbs += [$personalDetails->getFullName() => ''];
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
            $breadcrumbs += [$personalDetails->getFullName() => ''];
        } elseif (ContextProvider::USER_SEARCH_CONTEXT === $context) {
            /*
             * User search context.
             */
            $userSearchUrl = $this->url()->fromRoute(
                'user_admin/user-search',
                [],
                ['query' => $this->getRequest()->getQuery()->toArray()]
            );
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl];
            $breadcrumbs += [$personalDetails->getFullName() => ''];
        } elseif (ContextProvider::YOUR_PROFILE_CONTEXT === $context) {
            /*
             * Your Profile context.
             */
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__YOUR_PROFILE => ''];
        } else {
            /*
             * Undefined context.
             */
            $breadcrumbs += [$personalDetails->getFullName() => ''];
        }

        return $breadcrumbs;
    }

    /**
     * Gets and returns an array of System (internal) DVLA/DVSA roles.
     *
     * @param PersonalDetails $personalDetails
     *
     * @throws Exception
     *
     * @return array
     */
    private function getSystemRoles(PersonalDetails $personalDetails)
    {
        $roles = [];
        $systemRoles = $personalDetails->getDisplayableSystemRoles();

        $personSystemRoles = $this->catalogService->getPersonSystemRoles();

        foreach ($systemRoles as $systemRole) {
            $temp = (new DataMappingHelper($personSystemRoles, 'code', $systemRole))
                ->setReturnKeys(['name'])
                ->getValue();

            $temp = $temp['name'];
            $roles[] = $this->createRoleData($systemRole, $temp, 'system');
        }

        return $roles;
    }

    /**
     * @param int $role
     * @param $nicename
     * @param $roletype
     * @param string $id
     * @param string $name
     * @param string $address
     *
     * @return array
     */
    private function createRoleData($role, $nicename, $roletype, $id = "", $name = "", $address = "")
    {
        return [
            'id' => $id,
            'role' => $role,
            'nicename' => $nicename,
            'name' => $name,
            'address' => $address,
            'roletype' => $roletype,
        ];
    }

    /**
     * @param int $targetPersonId
     * @param PersonProfileGuard $personProfileGuard
     *
     * @return \Dvsa\Mot\Frontend\PersonModule\View\PersonProfileSidebar
     */
    private function createProfileSidebar($targetPersonId, PersonProfileGuard $personProfileGuard)
    {
        $routeName = $this->getRouteName();

        /** @var PersonalDetails $personalDetails */
        $personalDetails = $this->getAuthenticatedData()['personalDetails'];
        $testerAuthorisation = $this->personProfileGuardBuilder->getTesterAuthorisation($targetPersonId);
        $newProfileEnabled = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE);
        $twoFactorAuthEnabled = $this->twoFaFeatureToggle->isEnabled();
        $currentUrl = $this->url()->fromRoute($routeName, $this->getRouteParams($personalDetails, $routeName));
        $canOrderSecurityCard =
            $this->securityCardGuard->isEligibleForNewTwoFaCardAfterMtessSubmission($this->getIdentity(), $testerAuthorisation) ||
            $this->securityCardGuard->isEligibleForReplacementTwoFaCard($this->getIdentity());

        $hasSecurityCardOrders = $twoFactorAuthEnabled
            && $this->securityCardGuard->hasSecurityCardOrders($this->getIdentity());
        $hasDeactivated2FaCard = $twoFactorAuthEnabled
            && $this->securityCardGuard->hasInactiveTwoFaCard($this->getIdentity());
        $isAuthenticatedWithLostAndForgotten = $this->getIdentity()->isAuthenticatedWithLostForgotten();

        return new PersonProfileSidebar(
            $targetPersonId,
            $personProfileGuard,
            $testerAuthorisation,
            $newProfileEnabled,
            $currentUrl,
            new PersonProfileRoutes($this->contextProvider), 
            $this->url(),
            $this->canTestWithoutOtpService->canTestWithoutOtp(),
            $twoFactorAuthEnabled,
            $canOrderSecurityCard,
            $hasSecurityCardOrders,
            $hasDeactivated2FaCard,
            $isAuthenticatedWithLostAndForgotten,
            $personProfileGuard->canSeeResetAccountByEmailButton()
        );
    }

    /**
     * Return the name of the current route.
     *
     * @return string
     */
    private function getRouteName()
    {
        $router = $this->getServiceLocator()->get('Router');

        return $router->match($this->getRequest())->getMatchedRouteName();
    }

    /**
     * Return the appropriate parameters for use in view based on the current url.
     *
     * @param PersonalDetails $personDetails
     * @param string $route
     *
     * @return array
     */
    private function getRouteParams(PersonalDetails $personDetails, $route)
    {
        $userId = $personDetails->getId();

        switch ($route) {
            case ContextProvider::YOUR_PROFILE_PARENT_ROUTE:
                return ['id' => $userId];
            case ContextProvider::USER_SEARCH_PARENT_ROUTE:
                return ['id' => $userId];
            case ContextProvider::VTS_PARENT_ROUTE:
                $vtsId = $this->params()->fromRoute('vehicleTestingStationId');

                return ['vehicleTestingStationId' => $vtsId, 'id' => $userId];
            case ContextProvider::AE_PARENT_ROUTE:
                $aeId = $this->params()->fromRoute('authorisedExaminerId');

                return ['authorisedExaminerId' => $aeId, 'id' => $userId];
        }
    }

    /**
     * @return string|null
     */
    private function getUserSearchResultUrl()
    {
        return $this->url()->fromRoute('user_admin/user-search-results', [],
            ['query' => $this->getRequest()->getQuery()->toArray()]);
    }

    private function hasActiveSecurityCard($username)
    {
        $securityCard = $this->securityCardService->getSecurityCardForUser($username);

        return $securityCard instanceof SecurityCard && $securityCard->isActive();
    }
}
