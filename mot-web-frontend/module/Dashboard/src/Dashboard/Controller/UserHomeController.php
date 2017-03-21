<?php

namespace Dashboard\Controller;

use Application\Data\ApiPersonalDetails;
use Application\Helper\DataMappingHelper;
use Application\Service\CatalogService;
use Application\Service\LoggedInUserManager;
use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;
use Core\Controller\AbstractAuthActionController;
use Dashboard\Authorisation\ViewNewHomepageAssertion;
use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dashboard\Data\ApiDashboardResource;
use Dashboard\Model\Dashboard;
use Dashboard\Model\PersonalDetails;
use Dashboard\PersonStore;
use Dashboard\Security\DashboardGuard;
use Dashboard\Service\TradeRolesAssociationsService;
use Dashboard\ViewModel\DashboardViewModelBuilder;
use Dashboard\ViewModel\Sidebar\ProfileSidebar;
use Dashboard\ViewModel\UserHomeViewModel;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\CountryOfRegistrationCode;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Model\DvsaRole;
use DvsaCommon\Model\TradeRole;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaMotTest\Service\OverdueSpecialNoticeAssertion;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\Mvc\Controller\Plugin\Identity;
use Zend\View\Model\ViewModel;

/**
 * Controller for dashboard.
 */
class UserHomeController extends AbstractAuthActionController
{
    const ROUTE = 'user-home';
    const ROUTE_PROFILE = 'user-home/profile/byId';
    const PAGE_TITLE = 'Reset PIN';
    const PAGE_SUBTITLE = 'MOT Testing Service';
    const ERR_PIN_UPDATE_FAIL = 'There was a problem updating your PIN.';
    const ERR_COMMON_API = 'Something went wrong.';

    /** @var LoggedInUserManager $loggedInUserManager */
    private $loggedInUserManager;

    /** @var ApiPersonalDetails $personalDetailsService */
    private $personalDetailsService;

    /** @var PersonStore $personStoreService */
    private $personStoreService;

    /** @var ApiDashboardResource $dashboardResourceService */
    private $dashboardResourceService;

    /** @var CatalogService $catalogService */
    private $catalogService;

    /** @var WebAcknowledgeSpecialNoticeAssertion $acknowledgeSpecialNoticeAssertion */
    private $acknowledgeSpecialNoticeAssertion;

    /** @var UserAdminSessionManager $userAdminSessionManager */
    private $userAdminSessionManager;

    /** @var TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper */
    private $testerGroupAuthorisationMapper;

    /** @var MotAuthorisationServiceInterface $authorisationService */
    private $authorisationService;

    /** @var viewTradeRolesAssertion $viewTradeRolesAssertion */
    private $viewTradeRolesAssertion;

    /** @var TradeRolesAssociationsService $tradeRolesAssociationsService */
    protected $tradeRolesAssociationsService;

    /** @var DashboardGuard $dashboardGuard */
    protected $dashboardGuard;

    public function __construct(
        LoggedInUserManager $loggedInUserManager,
        ApiPersonalDetails $personalDetailsService,
        PersonStore $personStoreService,
        ApiDashboardResource $dashboardResourceService,
        CatalogService $catalogService,
        WebAcknowledgeSpecialNoticeAssertion $acknowledgeSpecialNoticeAssertion,
        UserAdminSessionManager $userAdminSessionManager,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        MotAuthorisationServiceInterface $authorisationService,
        UserAdminSessionManager $userAdminSessionManager,
        ViewTradeRolesAssertion $canViewTradeRolesAssertion,
        TradeRolesAssociationsService $tradeRolesAssociationsService,
        DashboardGuard $dashboardGuard
    ) {
        $this->loggedInUserManager = $loggedInUserManager;
        $this->personalDetailsService = $personalDetailsService;
        $this->personStoreService = $personStoreService;
        $this->dashboardResourceService = $dashboardResourceService;
        $this->catalogService = $catalogService;
        $this->acknowledgeSpecialNoticeAssertion = $acknowledgeSpecialNoticeAssertion;
        $this->userAdminSessionManager = $userAdminSessionManager;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->authorisationService = $authorisationService;
        $this->viewTradeRolesAssertion = $canViewTradeRolesAssertion;
        $this->tradeRolesAssociationsService = $tradeRolesAssociationsService;
        $this->dashboardGuard = $dashboardGuard;
    }

    /**
     * @return array|ViewModel
     */
    public function userHomeAction()
    {
        /** @var \Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity $identity */
        $identity = $this->getIdentity();

        if ($this->shouldShowNewHomepage($this->authorisationService)) {
            return $this->redirectToNewHomepage();
        }

        $authenticatedData = $this->getAuthenticatedData();
        $personId = $identity->getUserId();

        $this->loggedInUserManager->discoverCurrentLocation($identity->getCurrentVts());

        $dashboard = $this->getDashboardDetails($personId);

        $specialNotice = array_merge(
            $dashboard->getSpecialNotice()->toArray(),
            [
                'canRead' => $authenticatedData['canRead'],
                'canAcknowledge' => $authenticatedData['canAcknowledge'],
                'canReceiveSpecialNotice' => $authenticatedData['canReceiveSpecialNotice'],
            ]
        );

        $canPerformTest = true;
        if ($this->getAuthorizationService()->isTester()) {
            $overdueSpecialNoticeAssertion = $this->getOverdueSpecialNoticeAssertion($dashboard);
            $canPerformTest = $overdueSpecialNoticeAssertion->canPerformTest();
        }

        $canPerformNonMotTest =
            $this->authorisationService->isGranted(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM);

        $return = array_merge(
            [
                'dashboard' => $dashboard,
            ],
            $authenticatedData,
            [
                'specialNotice' => $specialNotice,
                'canPerformTest' => $canPerformTest,
                'canPerformNonMotTest' => $canPerformNonMotTest,
            ]
        );

        $this->layout()->setVariable('isHomePage', true);

        return $return;
    }

    /**
     * @return ViewModel
     */
    public function userHomeRefactorAction()
    {
        $identity = $this->getIdentity();

        $currentVts = $identity->getCurrentVts();
        $personId = $identity->getUserId();

        $this->loggedInUserManager->discoverCurrentLocation($currentVts);

        $dashboard = $this->getDashboardDetails($personId);

        if ($this->getAuthorizationService()->isTester()) {
            $this->dashboardGuard->setOverdueSpecialNoticeAssertion(
                $this->getOverdueSpecialNoticeAssertion($dashboard)
            );
        }

        $this->dashboardGuard->setOverdueSpecialNoticeCount(
            $dashboard->getSpecialNotice()->getOverdueCount()
        );

        $dashboardViewModelBuilder = new DashboardViewModelBuilder(
            $identity,
            $dashboard,
            $this->dashboardGuard,
            $this->url()
        );

        $userHomeViewModel = new UserHomeViewModel($dashboardViewModelBuilder->build());
        $userHomeViewModel->setTemplate('/dashboard/user-home/user-home-refactor.twig');

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('isHomePage', true);

        return $userHomeViewModel;
    }

    /**
     * @return array
     */
    public function profileAction()
    {
        $this->userAdminSessionManager->deleteUserAdminSession();
        $this->layout('layout/layout-govuk.phtml');
        $data = $this->getAuthenticatedData();

        /** @var PersonalDetails $personDetails */
        $personDetails = $data['personalDetails'];

        $profileId = $this->getPersonIdFromRequest();

        $roles = $personDetails->getRoles();
        $hasInternalRoles = DvsaRole::containDvsaRole($roles);
        $hasTradeRoles = TradeRole::containsTradeRole($roles);

        if ($this->viewTradeRolesAssertion->shouldViewLink($profileId, $hasInternalRoles, $hasTradeRoles)) {
            $this->setSidebar(new ProfileSidebar($profileId));
        }

        return $data;
    }

    /**
     * @return array
     */
    public function securitySettingsAction()
    {
        $userId = $this->getIdentity()->getUserId();

        if ($this->userAdminSessionManager->isUserAuthenticated($userId) !== true) {
            $this->redirect()->toUrl(PersonUrlBuilderWeb::securityQuestions());
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
            } catch (\Exception $e) {
                if ($e instanceof GeneralRestException) {
                    $errMsg = self::ERR_PIN_UPDATE_FAIL;
                } else {
                    $errMsg = self::ERR_COMMON_API;
                }
                $this->flashMessenger()->addErrorMessage($errMsg);
            }
        } else {
            $this->layout('layout/layout-govuk.phtml');
        }

        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE);
        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);

        return $returnData;
    }

    /**
     * @return array|\Zend\Http\Response
     */
    public function editAction()
    {
        $identity = $this->getIdentity();
        if (!$this->getAuthorizationService()->isGranted(PermissionInSystem::PROFILE_EDIT_OWN_CONTACT_DETAILS)) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::profile());
        }
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            try {
                $this->personStoreService->update($identity->getUserId(), $data);

                return $this->redirect()->toUrl(PersonUrlBuilderWeb::profile());
            } catch (ValidationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
                $data['phone'] = $data['phoneNumber']; // fixing field naming inconsistency
                return $this->getAuthenticatedData($data);
            }
        }

        return $this->getAuthenticatedData();
    }

    /**
     * @param int $personId
     *
     * @return Dashboard
     */
    private function getDashboardDetails($personId)
    {
        $dashboardData = $this->dashboardResourceService->get($personId);

        return new Dashboard($dashboardData);
    }

    /**
     * @return array
     */
    private function getCountries()
    {
        $allCountries = $this->catalogService->getCountriesOfRegistrationByCode();

        $countries = [];
        foreach ($allCountries as $code => $country) {
            $countries[$code] = $country;

            if (CountryOfRegistrationCode::NOT_APPLICABLE === $code) {
                break;
            }
        }

        return $countries;
    }

    /**
     * @param null $personalDetailsData
     *
     * @return array
     */
    private function getAuthenticatedData($personalDetailsData = null)
    {
        $personId = $this->getPersonIdFromRequest();
        $identity = $this->getIdentity();

        $isAllowEdit = ($personId > 0 && $identity->getUserId() == $personId
        && $this->authorisationService->isGranted(PermissionInSystem::PROFILE_EDIT_OWN_CONTACT_DETAILS)
        );

        $personalDetailsData = array_merge(
            $this->personalDetailsService->getPersonalDetailsData($personId),
            $personalDetailsData ?: []
        );

        $personalDetails = new PersonalDetails($personalDetailsData);

        $authorisations = $this->personalDetailsService->getPersonalAuthorisationForMotTesting($personId);

        $isViewingOwnProfile = ($identity->getUserId() == $personId);

        $canViewUsername = $this->authorisationService->isGranted(PermissionInSystem::USERNAME_VIEW)
            && !$isViewingOwnProfile;

        return [
            'personalDetails' => $personalDetails,
            'isAllowEdit' => $isAllowEdit,
            'motAuthorisations' => $authorisations,
            'isViewingOwnProfile' => $isViewingOwnProfile,
            'countries' => $this->getCountries(),
            'canAcknowledge' => $this->acknowledgeSpecialNoticeAssertion->isGranted(),
            'canRead' => $this->authorisationService->isGranted(PermissionInSystem::SPECIAL_NOTICE_READ),
            'authorisation' => $this->testerGroupAuthorisationMapper->getAuthorisation($personId),
            'rolesAndAssociations' => $this->tradeRolesAssociationsService->prepareRolesAndAssociations($personalDetails),
            'canViewUsername' => $canViewUsername,
            'systemRoles' => $this->getSystemRoles($personalDetails),
            'roleNiceNameList' => $this->getRoleNiceNameList($personalDetails),
            'canReceiveSpecialNotice' => $this->authorisationService->isGranted(PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT),
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
     * Gets and returns an array of System (internal) DVLA/DVSA roles.
     *
     * @param PersonalDetails $personalDetails
     *
     * @return array
     *
     * @throws \Exception
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
     * @param PersonalDetails $personalDetails
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getRoleNiceNameList(PersonalDetails $personalDetails)
    {
        $currentUserRoles = $personalDetails->getDisplayableRoles();
        $roles = [];

        $allRoles = $this->catalogService->getBusinessRoles();
        $allRoles = array_merge($allRoles, $this->catalogService->getPersonSystemRoles());
        foreach ($currentUserRoles as $currentUserRole) {
            $temp = (new DataMappingHelper($allRoles, 'code', $currentUserRole))
                ->setReturnKeys(['name'])
                ->getValue();
            $roles[] = $temp['name'];
        }

        return $roles;
    }

    /**
     * @param int $role
     * @param string $nicename
     * @param string $roletype
     * @param string $id
     * @param string $name
     * @param string $address
     *
     * @return array
     */
    private function createRoleData($role, $nicename, $roletype, $id = '', $name = '', $address = '')
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
     * @param Dashboard $dashboard
     *
     * @return OverdueSpecialNoticeAssertion
     */
    private function getOverdueSpecialNoticeAssertion(Dashboard $dashboard)
    {
        $tester = $this->loggedInUserManager->getTesterData();
        $authorisationsForTestingMot = $this->getAuthorisationsForTestingMot($tester);
        
        $overdueSpecialNotices = $dashboard->getOverdueSpecialNotices();

        return new OverdueSpecialNoticeAssertion($overdueSpecialNotices, $authorisationsForTestingMot);
    }

    /**
     * @param array $tester
     *
     * @return array
     */
    private function getAuthorisationsForTestingMot(array $tester)
    {
        $authorisationsForTestingMot = [];

        if (isset($tester['authorisationsForTestingMot']) && !is_null($tester['authorisationsForTestingMot'])) {
           return $authorisationsForTestingMot = $tester['authorisationsForTestingMot'];
        }

        return $authorisationsForTestingMot;
    }

    /**
     * @param MotAuthorisationServiceInterface $authorisationService
     *
     * @return bool
     */
    private function shouldShowNewHomepage(MotAuthorisationServiceInterface $authorisationService)
    {
        if (!$this->isFeatureEnabled(FeatureToggle::NEW_HOMEPAGE)) {
            return false;
        }

        $viewNewHomepageAssertion = new ViewNewHomepageAssertion($authorisationService);

        return $viewNewHomepageAssertion->canViewNewHomepage();
    }

    /**
     * @return ViewModel
     */
    private function redirectToNewHomepage()
    {
        return $this->userHomeRefactorAction();
    }
}
