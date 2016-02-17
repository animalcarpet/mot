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
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileSidebar;
use DvsaClient\MapperFactory;
use DvsaCommon\Constants\FeatureToggle;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\View\Model\ViewModel;

/**
 * Controller for the Person Profile page.
 */
class PersonProfileController extends AbstractAuthActionController
{
    const CONTENT_HEADER_TYPE__USER_SEARCH = 'User search';
    const CONTENT_HEADER_TYPE__YOUR_PROFILE = 'Your profile';

    /**
     * @var ApiPersonalDetails
     */
    private $personalDetailsService;

    /**
     * @var ApiDashboardResource
     */
    private $dashboardResourceService;

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var UserAdminSessionManager
     */
    private $userAdminSessionManager;

    /**
     * @var ViewTradeRolesAssertion
     */
    private $viewTradeRolesAssertion;

    /**
     * @var PersonProfileGuardBuilder
     */
    private $personProfileGuardBuilder;

    /**
     * @var MapperFactory
     */
    private $mapperFactory;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /**
     * PersonProfileController constructor.
     *
     * @param ApiPersonalDetails        $personalDetailsService
     * @param ApiDashboardResource      $dashboardResourceService
     * @param CatalogService            $catalogService
     * @param UserAdminSessionManager   $userAdminSessionManager
     * @param ViewTradeRolesAssertion   $canViewTradeRolesAssertion
     * @param PersonProfileGuardBuilder $personProfileGuardBuilder
     * @param MapperFactory             $mapperFactory
     * @param ContextProvider           $contextProvider
     */
    public function __construct(ApiPersonalDetails $personalDetailsService,
                                ApiDashboardResource $dashboardResourceService,
                                CatalogService $catalogService,
                                UserAdminSessionManager $userAdminSessionManager,
                                ViewTradeRolesAssertion $canViewTradeRolesAssertion,
                                PersonProfileGuardBuilder $personProfileGuardBuilder,
                                MapperFactory $mapperFactory,
                                ContextProvider $contextProvider

    ) {
        $this->personalDetailsService = $personalDetailsService;
        $this->dashboardResourceService = $dashboardResourceService;
        $this->catalogService = $catalogService;
        $this->userAdminSessionManager = $userAdminSessionManager;
        $this->viewTradeRolesAssertion = $canViewTradeRolesAssertion;
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
        $this->mapperFactory = $mapperFactory;
        $this->contextProvider = $contextProvider;
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

        $personId = $this->getPersonIdFromRequest();
        $context = $this->contextProvider->getContext();
        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard($personDetails, $context);
        $profileSidebar = $this->createProfileSidebar($personId, $personProfileGuard);
        $this->setSidebar($profileSidebar);

        $breadcrumbs = $this->generateBreadcrumbsFromRequest($context, $personDetails);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $routeName = $this->getRouteName();
        $routeParams = $this->getRouteParams($personDetails, $routeName);

        return $this->createViewModel('profile/index.phtml', [
            'personalDetails'           => $personDetails,
            'systemRoles'               => $this->getSystemRoles($personDetails),
            'personProfileGuard'        => $personProfileGuard,
            'userHomeRoute'             => UserHomeController::ROUTE,
            'routeName'                 => $routeName,
            'routeParams'               => $routeParams,
            'context'                   => $context,
            'userSearchResultUrl'       => $this->getUserSearchResultUrl(),

        ]);
    }

    /**
     * @param string $template
     * @param array  $variables
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
        $personId = $this->getPersonIdFromRequest();
        $identity = $this->getIdentity();

        $personalDetailsData = array_merge(
            $this->personalDetailsService->getPersonalDetailsData($personId),
            $personalDetailsData
        );

        $personalDetails = new PersonalDetails($personalDetailsData);

        $authorisations = $this->personalDetailsService->getPersonalAuthorisationForMotTesting($personId);

        $isViewingOwnProfile = ($identity->getUserId() == $personId);

        return [
            'personalDetails'      => $personalDetails,
            'motAuthorisations'    => $authorisations,
            'isViewingOwnProfile'  => $isViewingOwnProfile,
            'systemRoles'          => $this->getSystemRoles($personalDetails),
        ];
    }

    /**
     * @return int
     */
    private function getPersonIdFromRequest()
    {
        $personId = (int) $this->params()->fromRoute('id', null);
        $identity = $this->getIdentity();

        if ($personId == 0) {
            $personId = $identity->getUserId();
        }

        return $personId;
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
     * @throws \Exception
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
            'id'       => $id,
            'role'     => $role,
            'nicename' => $nicename,
            'name'     => $name,
            'address'  => $address,
            'roletype' => $roletype,
        ];
    }

    /**
     * @param int                $targetPersonId
     * @param PersonProfileGuard $personProfileGuard
     *
     * @return \Dvsa\Mot\Frontend\PersonModule\View\PersonProfileSidebar
     */
    private function createProfileSidebar($targetPersonId, PersonProfileGuard $personProfileGuard)
    {
        $routeName = $this->getRouteName();
        $personalDetails = $this->getAuthenticatedData()['personalDetails'];

        $testerAuthorisation = $this->personProfileGuardBuilder->getTesterAuthorisation($targetPersonId);
        $newProfileEnabled = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE);
        $currentUrl = $this->url()->fromRoute($routeName, $this->getRouteParams($personalDetails, $routeName));

        return new PersonProfileSidebar($targetPersonId, $personProfileGuard, $testerAuthorisation, $newProfileEnabled,
            $currentUrl);
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
     * @param string          $route
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
        return $this->url()->fromRoute('user_admin/user-search-results', [], [ 'query' => $this->getRequest()->getQuery()->toArray()]);
    }
}
