<?php
namespace Dashboard\Controller;

use Account\Service\SecurityQuestionService;
use Application\Data\ApiPersonalDetails;
use Application\Service\CatalogService;
use Application\Service\LoggedInUserManager;
use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;
use Core\Controller\AbstractAuthActionController;
use Dashboard\Data\ApiDashboardResource;
use Dashboard\Model\Dashboard;
use Dashboard\Model\PersonalDetails;
use Dashboard\PersonStore;
use Dvsa\OpenAM\Exception\OpenAMClientException;
use Dvsa\OpenAM\Exception\OpenAMUnauthorisedException;
use Dvsa\OpenAM\Model\OpenAMLoginDetails;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\CountryOfRegistrationCode;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Application\Helper\DataMappingHelper;

/**
 * Controller for dashboard
 */
class UserHomeController extends AbstractAuthActionController
{
    const ROUTE = 'user-home';

    const PAGE_TITLE    = 'Reset PIN';
    const PAGE_SUBTITLE = 'MOT Testing Service';

    const ERR_PIN_UPDATE_FAIL = 'There was a problem updating your PIN.';
    const ERR_COMMON_API = 'Something went wrong.';

    /** @var  LoggedInUserManager */
    private $loggedIdUserManager;
    /** @var  ApiPersonalDetails */
    private $personalDetailsService;
    /** @var  PersonStore */
    private $personStoreService;
    /** @var  ApiDashboardResource */
    private $dashboardResourceService;
    /** @var CatalogService  */
    private $catalogService;
    /** @var WebAcknowledgeSpecialNoticeAssertion  */
    private $acknowledgeSpecialNoticeAssertion;
    /** @var SecurityQuestionService */
    protected $service;
    /** @var UserAdminSessionManager */
    protected $userAdminSessionManager;
    /** @var TesterGroupAuthorisationMapper */
    private $testerGroupAuthorisationMapper;
    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;

    public function __construct(
        LoggedInUserManager $loggedIdUserManager,
        ApiPersonalDetails $personalDetailsService,
        PersonStore $personStoreService,
        ApiDashboardResource $dashboardResourceService,
        CatalogService $catalogService,
        WebAcknowledgeSpecialNoticeAssertion $acknowledgeSpecialNoticeAssertion,
        SecurityQuestionService $securityQuestionService,
        UserAdminSessionManager $userAdminSessionManager,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        MotAuthorisationServiceInterface $authorisationService
    ) {
        $this->loggedIdUserManager = $loggedIdUserManager;
        $this->personalDetailsService = $personalDetailsService;
        $this->personStoreService = $personStoreService;
        $this->dashboardResourceService = $dashboardResourceService;
        $this->catalogService = $catalogService;
        $this->acknowledgeSpecialNoticeAssertion = $acknowledgeSpecialNoticeAssertion;
        $this->service = $securityQuestionService;
        $this->userAdminSessionManager = $userAdminSessionManager;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->authorisationService = $authorisationService;
    }

    public function userHomeAction()
    {
        $identity = $this->getIdentity();
        $personId = $identity->getUserId();

        // TODO this should be moved to loginAction
        $this->loggedIdUserManager->discoverCurrentLocation($identity->getCurrentVts());

        $dashboard = $this->getDashboardDetails($personId);
        $authenticatedData = $this->getAuthenticatedData();
        $specialNotice = array_merge(
            $dashboard->getSpecialNotice()->toArray(),
            [
                'canRead' => $authenticatedData['canRead'],
                'canAcknowledge' => $authenticatedData['canAcknowledge']
            ]
        );

        return array_merge(
            [
                'dashboard' => $dashboard
            ],
            $authenticatedData,
            [
                'specialNotice' => $specialNotice
            ]
        );
    }

    public function profileAction()
    {
        $this->userAdminSessionManager->deleteUserAdminSession();
        $this->layout('layout/layout-govuk.phtml');
        return $this->getAuthenticatedData();
    }

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
            'config'   => $this->getConfig(),
            'userId'   => $userId,
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
                $data['phone'] = $data['phoneNumber']; // fixing field naming inconcistency
                return $this->getAuthenticatedData($data);
            }
        }
        return $this->getAuthenticatedData();
    }

    /**
     * @param $personId int
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

        $countries=[];
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
     * @return array
     */
    private function getAuthenticatedData($personalDetailsData = null)
    {
        $personId = (int)$this->params()->fromRoute('id', null);
        $identity = $this->getIdentity();

        if ($personId == 0) {
            $personId = $identity->getUserId();
        }

        $isAllowEdit = ($personId > 0 && $identity->getUserId() == $personId
        && $this->getAuthorizationService()->isGranted(PermissionInSystem::PROFILE_EDIT_OWN_CONTACT_DETAILS)
        );

        $personalDetailsData = array_merge(
            $this->personalDetailsService->getPersonalDetailsData($personId),
            $personalDetailsData ?: []
        );

        $personalDetails = new PersonalDetails($personalDetailsData);

        $authorisations = $this->personalDetailsService->getPersonalAuthorisationForMotTesting($personId);

        $isViewingOwnProfile = ($identity->getUserId() == $personId);

        $canViewUsername = $this->authorisationService->isGranted(PermissionInSystem::USERNAME_VIEW)
                            && !$isViewingOwnProfile ;

        return [
            'personalDetails'      => $personalDetails,
            'isAllowEdit'          => $isAllowEdit,
            'motAuthorisations'    => $authorisations,
            'isViewingOwnProfile'  => $isViewingOwnProfile,
            'countries'            => $this->getCountries(),
            'canAcknowledge'       => $this->acknowledgeSpecialNoticeAssertion->isGranted($personId),
            'canRead'              => $this->getAuthorizationService()->isGranted(PermissionInSystem::SPECIAL_NOTICE_READ),
            'authorisation'        => $this->testerGroupAuthorisationMapper->getAuthorisation($personId),
            'rolesAndAssociations' => $this->getRolesAndAssociations($personalDetails),
            'canViewUsername'      => $canViewUsername,
            'systemRoles'          => $this->getSystemRoles($personalDetails),
            'roleNiceNameList'     => $this->getRoleNiceNameList($personalDetails),
        ];
    }

    /**
     * Gets and returns an array of System (internal) DVLA/DVSA roles
     * @param PersonalDetails $personalDetails
     * @return array
     * @throws \Exception
     */
    private function getSystemRoles(PersonalDetails $personalDetails)
    {
        $roles = [];
        $systemRoles = $personalDetails->getSystemRoles();

        $personSystemRoles = $this->catalogService->getPersonSystemRoles();

        foreach ($systemRoles as $systemRole) {
            $temp = (new DataMappingHelper($personSystemRoles, 'code', $systemRole))
                ->setReturnKeys(['name'])
                ->getValue();

            $temp = $temp['name'];
            $roles[] = $this->createRoleData($systemRole, $temp);
        }


        return $roles;
    }


    /**
     * @param PersonalDetails $personalDetails
     * @return array
     * @throws \Exception
     */
    private function getRoleNiceNameList(PersonalDetails $personalDetails)
    {
        $currentUserRoles = $personalDetails->getRoles();
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
     * @param PersonalDetails $personalDetails
     * @return array
     * @throws \Exception
     */
    private function getRolesAndAssociations(PersonalDetails $personalDetails)
    {
        $rolesAndAssociations = [];
        $siteAndOrganisationRoles = $personalDetails->getSiteAndOrganisationRoles();

        $personSiteAndOrganisationRoles = $this->catalogService->getBusinessRoles();

        foreach ($siteAndOrganisationRoles as $id => $siteAndOrganisationRole) {
            foreach ($siteAndOrganisationRole['roles'] as $role) {
                $niceName = (new DataMappingHelper($personSiteAndOrganisationRoles, 'code', $role))
                    ->setReturnKeys(['name'])
                    ->getValue();

                $rolesAndAssociations[] = $this->createRoleData(
                    $role,
                    $niceName['name'],
                    $id,
                    $siteAndOrganisationRole["name"],
                    $siteAndOrganisationRole["address"]
                );
            }
        }

        return $rolesAndAssociations;
    }

    /**
     * @param int $role
     * @param string $id
     * @param string $name
     * @param string $address
     * @return array
     */
    private function createRoleData($role, $nicename, $id = "", $name = "", $address = "")
    {
        return [
            "id"      => $id,
            "role"    => $role,
            'nicename' => $nicename,
            "name"    => $name,
            "address" => $address
        ];
    }
}

