<?php

namespace Site\Controller;

use Application\Service\CatalogService;
use Core\Controller\AbstractAuthActionController;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Core\View\Sidebar\GeneralSidebar;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Constants\Role;
use DvsaCommon\Dto\Site\EnforcementSiteAssessmentDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilderWeb;
use Site\Authorization\VtsOverviewPagePermissions;
use Site\Form\VtsContactDetailsUpdateForm;
use Site\Form\VtsCreateForm;
use Site\Form\VtsSiteAssessmentForm;
use Site\Form\VtsSiteDetailsForm;
use Site\Form\VtsUpdateTestingFacilitiesForm;
use Site\Service\RiskAssessmentScoreRagClassifier;
use Site\ViewModel\SiteViewModel;
use Site\ViewModel\VehicleTestingStation\VtsFormViewModel;
use Zend\Http\Request;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

/**
 * Class SiteController
 *
 * @package DvsaMotTest\Controller
 */
class SiteController extends AbstractAuthActionController
{
    const SESSION_CNTR_KEY = 'SITE_CREATE_UPDATE';
    const SESSION_KEY = 'data';
    const SITE_ASSESSMENT_SESSION_KEY = 'SITE_ASSESSMENT';

    const CREATE_TITLE = 'Create Site';
    const CREATE_CONFIRM_TITLE = 'Confirm new site details';
    const SITE_SUBTITLE = 'Site management';
    const STEP_ONE = 'Step 1 of 2';
    const STEP_TWO = 'Step 2 of 2';

    const REFERER = 'refererToSite';

    const EDIT_TITLE = 'Change contact details';
    const EDIT_SUBTITLE = 'Vehicle testing station';
    const EDIT_TESTING_FACILITIES = 'Change testing facilities';
    const EDIT_TESTING_FACILITIES_CONFIRM = 'Confirm testing facilities';
    const EDIT_SITE_DETAILS = 'Change site details';
    const EDIT_SITE_DETAILS_CONFIRM = 'Confirm site details';

    const ROUTE_CONFIGURE_BRAKE_TEST_DEFAULTS = 'site/configure-brake-test-defaults';

    const FORM_ERROR = 'Unable to find VTS';
    const CHANGE_BRAKE_TEST_DEFAULTS_AUTHORISATION_ERROR = "You are not authorised to change the brake test defaults";
    const SEARCH_RESULT_PARAM = 'q';

    const ERR_MSG_INVALID_SITE_ID_OR_NR = 'No Id or Site Number provided';
    const MSG_ADD_RISK_ASSESSMENT_SUCCESS = 'The site assessment has been updated';

    /**
     * @var MotFrontendAuthorisationServiceInterface
     */
    private $auth;
    /**
     * @var MapperFactory
     */
    private $mapper;
    /**
     * @var MotIdentityProviderInterface
     */
    private $identity;
    /**
     * @var CatalogService
     */
    private $catalog;
    /**
     * @var Container
     */
    private $session;

    /**
     * @param MotFrontendAuthorisationServiceInterface $auth
     * @param MapperFactory                            $mapper
     * @param MotIdentityProviderInterface             $identity
     * @param CatalogService                           $catalog
     * @param Container                                $session
     */
    public function __construct(
        MotFrontendAuthorisationServiceInterface $auth,
        MapperFactory $mapper,
        MotIdentityProviderInterface $identity,
        CatalogService $catalog,
        Container $session
    ) {
        $this->auth = $auth;
        $this->mapper = $mapper;
        $this->identity = $identity;
        $this->catalog = $catalog;
        $this->session = $session;
    }

    /**
     * Display the details of a VTS
     */
    public function indexAction()
    {
        $isEnforcementUser = $this->auth->hasRole(Role::VEHICLE_EXAMINER);

        //  --  process request --
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $vtsId = $this->params()->fromRoute('id', null);

        //  --  store url for back url in following pages   --
        $refBack = new Container(self::REFERER);
        $refBack->uri = $request->getUri();

        if (isset($vtsId)) {
            $site = $this->mapper->Site->getById($vtsId);
        } else {
            throw new \Exception(self::ERR_MSG_INVALID_SITE_ID_OR_NR);
        }

        $permissions = $this->getPermissions($site);

        //  --  prepare view data   --
        $equipment = $this->mapper->Equipment->fetchAllForVts($site->getId());
        $testInProgress = ($permissions->canViewTestsInProgress()
            ? $this->mapper->MotTestInProgress->fetchAllForVts($site->getId())
            : []
        );

        $equipmentModelStatusMap = $this->catalog->getEquipmentModelStatuses();
        $siteStatusMap = $this->catalog->getSiteStatus();

        $view = new SiteViewModel($site, $equipment, $testInProgress, $permissions, $equipmentModelStatusMap);

        //  --  get ref page    --
        $refSession = new Container('referralSession');
        if ($isEnforcementUser && !empty($refSession->url)) {
            $escRefPage = '/mot-test-search/vrm?' . http_build_query($refSession->url);
        } else {
            $escRefPage = null;
        }
        $refSession->url = false;

        $assessmentDto = $site->getAssessment();
        if($assessmentDto instanceof EnforcementSiteAssessmentDto){
            $riskAssessmentScore = $assessmentDto->getSiteAssessmentScore();
        }
        else {
            $riskAssessmentScore = 0;
        }

        $config = $this->getServiceLocator()->get(MotConfig::class);
        $ragClassifier = new RiskAssessmentScoreRagClassifier($riskAssessmentScore, $config);
        //  --
        $searchString = null;
        if ($isEnforcementUser) {
            // Used when constructing the back-link.  If the searchString is provided we know
            // that the previous page was a VE search result page.  We can re-create the query from
            // the search string param; otherwise we default to the VE search page.
            $searchString = $request->getQuery(self::SEARCH_RESULT_PARAM);
        }

        //  logical block :: prepare view model
        $this->layout()->setVariable(
            'pageTertiaryTitle',
            $site->getContactByType(SiteContactTypeCode::BUSINESS)->getAddress()->getFullAddressString()
        );

        $id = (int)$this->params()->fromRoute('id');
        $eventsLink = '/event/list/site/' . $id;
        $motCertsLink = '/vehicle-testing-station/' . $id . '/mot-test-certificates';
        $viewTestLogsLink = VehicleTestingStationUrlBuilderWeb::motTestLog($id)->toString();

        $siteStatus = $siteStatusMap[$site->getStatus()];
        $modifierClass = '';
        if (strtolower($siteStatus) == 'approved') {
            $modifierClass = 'success';
        }

        $relatedLinks = [];
        $canReadEvents = $this->auth->isGranted(PermissionInSystem::EVENT_READ);
        if ($canReadEvents) {
            $relatedLinks [] = ['Events history' => ['id' => 'event-history', 'href' => $eventsLink]];
        }

        $canViewTestLogs = $this->auth->isGrantedAtSite(PermissionAtSite::VTS_TEST_LOGS, $vtsId);
        if($canViewTestLogs){
            $relatedLinks[] = ['Test logs' => ['id' => 'test-logs', 'href' => $viewTestLogsLink]];
        }
        $isJasperAsyncEnabled = $this->isFeatureEnabled(FeatureToggle::JASPER_ASYNC);
        $canSeeRecentCertificates = $this->auth->isGrantedAtSite(PermissionAtSite::RECENT_CERTIFICATE_PRINT, $id);

        if ($isJasperAsyncEnabled && $canSeeRecentCertificates) {
            $relatedLinks [] = ['MOT test certificates' => [ 'id' => 'mot-test-recent-certificates-link', 'href' => $motCertsLink]];
        }

        $related = [];
        if(!empty($relatedLinks)) {
            $related = [
                'type' => 'linkList',
                'title' => 'Related',
                'items' =>  $relatedLinks
            ];
        }

        $sideBarItems = [
            [
                'type' => 'statusBox',
                'title' => '',
                'statusItems1' => [
                    'key' => 'Status',
                    'value' => $siteStatus,
                    'modifier' => $modifierClass,
                ],
            ],
        ];

        if (!empty($related)) {
            array_push($sideBarItems, $related);
        }

        $sidebar = new GeneralSidebar($sideBarItems);

        $this->setSidebar($sidebar);

        $viewModel = new ViewModel(
            [
                'viewModel'     => $view,
                'searchString'  => $searchString,
                'escRefPage'    => $escRefPage,
                'siteStatusMap' => $siteStatusMap,
                'ragClassifier' => $ragClassifier,
                'isVtsRiskEnabled' => $this->isFeatureEnabled(FeatureToggle::VTS_RISK_SCORE)
            ]
        );

        $breadcrumbs = [];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($site,$breadcrumbs);

        return $this->prepareViewModel($viewModel, $site->getName(), 'Vehicle Testing Station',$breadcrumbs);
    }

    public function createAction()
    {
        $this->auth->assertGranted(PermissionInSystem::VEHICLE_TESTING_STATION_CREATE);

        /** @var Request $request */
        $request = $this->getRequest();

        //  create new form or get from session when come back from confirmation
        $sessionKey = $request->getQuery(self::SESSION_KEY) ?: uniqid();
        $form = $this->session->offsetGet($sessionKey);

        if (!$form instanceof VtsCreateForm) {
            $form = new VtsCreateForm();
        }
        $form->setFormUrl(VehicleTestingStationUrlBuilderWeb::create()->queryParam(self::SESSION_KEY, $sessionKey));

        if ($request->isPost()) {
            $form->fromPost($request->getPost());

            try {
                $this->mapper->Site->validate($form->toDto());

                $this->session->offsetSet($sessionKey, $form);

                $url = VehicleTestingStationUrlBuilderWeb::createConfirm()
                    ->queryParam(self::SESSION_KEY, $sessionKey);

                return $this->redirect()->toUrl($url);
            } catch (ValidationException $ve) {
                $form->addErrorsFromApi($ve->getErrors());
            }
        }

        //  logical block :: prepare view model
        $model = (new VtsFormViewModel())
            ->setForm($form)
            ->setCancelUrl('/');

        return $this->prepareViewModel(
            new ViewModel(['model' => $model]), self::CREATE_TITLE, self::SITE_SUBTITLE, [], self::STEP_ONE
        );
    }

    public function confirmationAction()
    {
        $this->auth->assertGranted(PermissionInSystem::VEHICLE_TESTING_STATION_CREATE);

        $urlCreate = VehicleTestingStationUrlBuilderWeb::create();

        /** @var Request $request */
        $request = $this->getRequest();

        //  get form from session
        $sessionKey = $request->getQuery(self::SESSION_KEY);
        $form = $this->session->offsetGet($sessionKey);

        //  redirect to create ae page if form data not provided
        if (!($form instanceof VtsCreateForm)) {
            return $this->redirect()->toUrl($urlCreate);
        }

        //  save ae to db and redirect to ae view page
        if ($request->isPost()) {
            try {
                $result = $this->mapper->Site->create($form->toDto());

                //  clean session after self
                $this->session->offsetUnset($sessionKey);

                return $this->redirect()->toUrl(VehicleTestingStationUrlBuilderWeb::byId($result['id']));
            } catch (RestApplicationException $ve) {
                $this->addErrorMessages($ve->getDisplayMessages());
            }
        }

        //  create a model
        $model = new VtsFormViewModel();
        $model
            ->setForm($form)
            ->setCancelUrl($urlCreate->queryParam(self::SESSION_KEY, $sessionKey));

        $form->setFormUrl(
            VehicleTestingStationUrlBuilderWeb::createConfirm()
                ->queryParam(self::SESSION_KEY, $sessionKey)
        );

        return $this->prepareViewModel(
            new ViewModel(['model' => $model]), self::CREATE_CONFIRM_TITLE, self::SITE_SUBTITLE, null, self::STEP_TWO
        );
    }

    public function contactDetailsAction()
    {
        $siteId = $this->params('id');
        if ((int)$siteId == 0) {
            throw new \Exception(self::ERR_MSG_INVALID_SITE_ID_OR_NR);
        }

        //  --  check permission  --
        $this->getUpdateVtsAssertion()->assertGranted($siteId);

        //  --  request site data from api  --
        $vtsDto = $this->mapper->Site->getById($siteId);

        //  --  form model  --
        $form = new VtsContactDetailsUpdateForm();
        $form->fromDto($vtsDto);

        $vtsViewUrl = VehicleTestingStationUrlBuilderWeb::byId($siteId)->toString();

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->fromPost($request->getPost());

            if ($form->isValid()) {
                $contactDto = $form->toDto();

                try {
                    $this->mapper->Site->updateContactDetails($siteId, $contactDto);

                    return $this->redirect()->toUrl($vtsViewUrl);
                } catch (ValidationException $ve) {
                    $form->addErrors($ve->getErrors());
                }
            }
        }

        //  logical block :: prepare view model
        $viewModel = new ViewModel(
            [
                'form'      => $form,
                'cancelUrl' => $vtsViewUrl,
            ]
        );

        $breadcrumbs = [
            $form->getVtsDto()->getName() => $vtsViewUrl,
        ];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($form->getVtsDto(), $breadcrumbs);

        $subTitle = self::EDIT_SUBTITLE . ' - ' . $form->getVtsDto()->getSiteNumber();

        return $this->prepareViewModel($viewModel, self::EDIT_TITLE, $subTitle, $breadcrumbs);
    }

    public function testingFacilitiesAction()
    {
        $siteId = $this->getSiteIdOrFail();
        $this->auth->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_TESTING_FACILITIES_DETAILS, $siteId);
        /** @var VehicleTestingStationDto $vtsDto */
        $vtsDto = $this->mapper->Site->getById($siteId);

        /**
         * @var \Zend\Http\Request $request
         */
        $request = $this->getRequest();

        //  create new form or get from session when come back from confirmation
        $sessionKey = $request->getQuery(self::SESSION_KEY) ?: uniqid();
        $form = $this->session->offsetGet($sessionKey);

        if (!$form instanceof VtsUpdateTestingFacilitiesForm) {
            /**
             * @var VehicleTestingStationDto $vtsDto
             */
            $vtsDto = $this->mapper->Site->getById($siteId);
            $form = new VtsUpdateTestingFacilitiesForm();
            $form->fromDto($vtsDto);
        }

        $form->setFormUrl(VehicleTestingStationUrlBuilderWeb::testingFacilities($siteId)
            ->queryParam(self::SESSION_KEY, $sessionKey));

        $vtsViewUrl = VehicleTestingStationUrlBuilderWeb::byId($siteId)->toString();

        if ($request->isPost()) {
            $form->fromPost($request->getPost());
            $dto = $form->toDto();

            try {
                $this->mapper->Site->validateTestingFacilities($siteId, $dto);
                $this->session->offsetSet($sessionKey, $form);

                $confirmUrl = VehicleTestingStationUrlBuilderWeb::testingFacilitiesConfirmation($siteId)
                    ->queryParam(self::SESSION_KEY, $sessionKey);

                return $this->redirect()->toUrl($confirmUrl);
            } catch (ValidationException $ve) {
                $form->addErrorsFromApi($ve->getErrors());
            }
        }

        //  logical block :: prepare view model
        $viewModel = new ViewModel([
            'form'      => $form,
            'cancelUrl' => $vtsViewUrl,
        ]);

        $breadcrumbs = [
            $form->getVtsDto()->getName() => $vtsViewUrl,
        ];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($form->getVtsDto(), $breadcrumbs);

        $subTitle = self::EDIT_SUBTITLE . ' - ' . $form->getVtsDto()->getSiteNumber();

        $this->layout()->setVariable(
            'pageTertiaryTitle',
            $vtsDto->getContactByType(SiteContactTypeCode::BUSINESS)
                ->getAddress()->getFullAddressString()
        );

        return $this->prepareViewModel(
            $viewModel,
            self::EDIT_TESTING_FACILITIES,
            $subTitle,
            $breadcrumbs,
            self::STEP_ONE
        );
    }

    public function testingFacilitiesConfirmationAction()
    {
        $siteId = $this->getSiteIdOrFail();
        $this->auth->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_TESTING_FACILITIES_DETAILS, $siteId);

        $vtsViewUrl = VehicleTestingStationUrlBuilderWeb::byId($siteId)->toString();

        /** @var Request $request */
        $request = $this->getRequest();

        //  get form from session
        $sessionKey = $request->getQuery(self::SESSION_KEY);
        $form = $this->session->offsetGet($sessionKey);

        if (!$form instanceof VtsUpdateTestingFacilitiesForm) {
            return $this->redirect()->toUrl($vtsViewUrl);
        }

        /**
         * @var VehicleTestingStationDto $vtsDto
         */
        $vtsDto = $form->getVtsDto();

        if ($request->isPost()) {
            try {
                $this->mapper->Site->updateTestingFacilities($siteId, $form->toDto());
                $this->session->offsetUnset($sessionKey);
                return $this->redirect()->toUrl($vtsViewUrl);
            } catch (RestApplicationException $ve) {
                $this->addErrorMessages($ve->getDisplayMessages());
            }
        }

        $breadcrumbs = [
            $vtsDto->getName() => $vtsViewUrl,
        ];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($vtsDto, $breadcrumbs);

        $form->setFormUrl(VehicleTestingStationUrlBuilderWeb::testingFacilitiesConfirmation($siteId)
            ->queryParam(self::SESSION_KEY, $sessionKey));

        $cancelUrl = VehicleTestingStationUrlBuilderWeb::testingFacilities($siteId)
            ->queryParam(self::SESSION_KEY, $sessionKey);

        $viewModel = new ViewModel([
            'form'      => $form,
            'cancelUrl' => $cancelUrl,
        ]);

        $this->layout()->setVariable(
            'pageTertiaryTitle',
            $vtsDto->getContactByType(SiteContactTypeCode::BUSINESS)
                ->getAddress()->getFullAddressString()
        );

        return $this->prepareViewModel(
            $viewModel,
            self::EDIT_TESTING_FACILITIES_CONFIRM,
            sprintf('Site - %s', $vtsDto->getSiteNumber()),
            $breadcrumbs,
            self::STEP_TWO
        );
    }

    public function siteDetailsAction()
    {
        $siteId = $this->getSiteIdOrFail();
        $this->auth->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_SITE_DETAILS, $siteId);

        /** @var Request $request */
        $request = $this->getRequest();

        /** @var VehicleTestingStationDto $vtsDto */
        $vtsDto = $this->mapper->Site->getById($siteId);

        //  create new form or get from session when come back from confirmation
        $sessionKey = $request->getQuery(self::SESSION_KEY) ?: uniqid();
        $form = $this->session->offsetGet($sessionKey);

        //  redirect to site details page if form data not provided
        if (!($form instanceof VtsSiteDetailsForm)) {
            $form = new VtsSiteDetailsForm();
            $form->fromDto($vtsDto);
        }

        $form->setFormUrl(
            VehicleTestingStationUrlBuilderWeb::siteDetails($siteId)
                ->queryParam(self::SESSION_KEY, $sessionKey)
        );

        if ($request->isPost()) {
            $form->fromPost($request->getPost());
            $dto = $form->toDto();

            try {
                $this->mapper->Site->validateSiteDetails($siteId, $dto);

                $this->session->offsetSet($sessionKey, $form);

                $url = VehicleTestingStationUrlBuilderWeb::siteDetailsConfirm($siteId)
                    ->queryParam(self::SESSION_KEY, $sessionKey);

                return $this->redirect()->toUrl($url);
            } catch (ValidationException $ve) {
                $form->addErrorsFromApi($ve->getErrors());
            }
        }

        $vtsViewUrl = VehicleTestingStationUrlBuilderWeb::byId($siteId)->toString();

        //  logical block :: prepare view model
        $model = (new VtsFormViewModel())
            ->setForm($form)
            ->setCancelUrl($vtsViewUrl)
        ;

        $subTitle = self::EDIT_SUBTITLE . ' - ' . $form->getVtsDto()->getSiteNumber();


        $breadcrumbs = [
            $form->getVtsDto()->getName() => $vtsViewUrl,
        ];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($form->getVtsDto(), $breadcrumbs);

        $this->layout()->setVariable(
            'pageTertiaryTitle',
            $vtsDto->getContactByType(SiteContactTypeCode::BUSINESS)
                ->getAddress()->getFullAddressString()
        );

        return $this->prepareViewModel(
            new ViewModel(['model' => $model]),
            self::EDIT_SITE_DETAILS,
            $subTitle,
            $breadcrumbs,
            self::STEP_ONE
        );
    }

    public function siteDetailsConfirmationAction()
    {
        $siteId = $this->getSiteIdOrFail();
        $this->auth->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_TESTING_FACILITIES_DETAILS, $siteId);

        $vtsViewUrl = VehicleTestingStationUrlBuilderWeb::byId($siteId)->toString();

        /** @var Request $request */
        $request = $this->getRequest();

        //  get form from session
        $sessionKey = $request->getQuery(self::SESSION_KEY);
        $form = $this->session->offsetGet($sessionKey);

        if (!$form instanceof VtsSiteDetailsForm) {
            return $this->redirect()->toUrl($vtsViewUrl);
        }

        /**
         * @var VehicleTestingStationDto $vtsDto
         */
        $vtsDto = $form->getVtsDto();

        if ($request->isPost()) {
            try {
                $this->mapper->Site->updateSiteDetails($siteId, $form->toDto());
                $this->session->offsetUnset($sessionKey);
                return $this->redirect()->toUrl($vtsViewUrl);
            } catch (RestApplicationException $ve) {
                $this->addErrorMessages($ve->getDisplayMessages());
            }
        }

        $breadcrumbs = [
            $vtsDto->getName() => $vtsViewUrl,
        ];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($vtsDto, $breadcrumbs);

        $form->setFormUrl(VehicleTestingStationUrlBuilderWeb::siteDetailsConfirm($siteId)
            ->queryParam(self::SESSION_KEY, $sessionKey));

        $cancelUrl = VehicleTestingStationUrlBuilderWeb::siteDetails($siteId)
            ->queryParam(self::SESSION_KEY, $sessionKey);

        $siteStatusMap = $this->catalog->getSiteStatus();

        $hasStatusChanged = $form->getStatus() != $vtsDto->getStatus();

        $viewModel = new ViewModel([
            'form'      => $form,
            'cancelUrl' => $cancelUrl,
            'siteStatusMap' => $siteStatusMap,
            'site' => $vtsDto,
            'hasStatusChanged' => $hasStatusChanged,
        ]);

        $this->layout()->setVariable(
            'pageTertiaryTitle',
            $vtsDto->getContactByType(SiteContactTypeCode::BUSINESS)
                ->getAddress()->getFullAddressString()
        );

        return $this->prepareViewModel(
            $viewModel,
            self::EDIT_SITE_DETAILS_CONFIRM,
            sprintf('Site - %s', $vtsDto->getSiteNumber()),
            $breadcrumbs,
            self::STEP_TWO
        );
    }

    /**
     * Prepare the view model for all the step of the create ae
     *
     * @param ViewModel $view
     * @param string $title
     * @param string $subtitle
     * @param array $breadcrumbs
     * @param array $progress
     * @param string $template
     * @return ViewModel
     * @internal param ViewModel $model
     */
    private function prepareViewModel(
        ViewModel $view,
        $title,
        $subtitle,
        $breadcrumbs = null,
        $progress = null,
        $template = null
    ) {
        //  logical block:: prepare view
        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', $title);
        $this->layout()->setVariable('pageSubTitle', $subtitle);


        if (!empty($progress)) {
            $this->layout()->setVariable('progress', $progress);
        }

        $breadcrumbs = (!empty($breadcrumbs) ? $breadcrumbs : []) + [$title => ''];
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        return $template !== null ? $view->setTemplate($template) : $view;

    }

    public function configureBrakeTestDefaultsAction()
    {
        $id = (int)$this->params()->fromRoute('id');

        if ($id <= 0) {
            throw new \Exception(self::ERR_MSG_INVALID_SITE_ID_OR_NR);
        }

        $this->auth->assertGrantedAtSite(PermissionAtSite::DEFAULT_BRAKE_TESTS_CHANGE, $id);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();

            $this->mapper->Site->saveDefaultBrakeTests($id, $postData);

            return $this->redirect()->toUrl(VehicleTestingStationUrlBuilderWeb::byId($id));
        }

        $site = $this->mapper->Site->getById($id);

        $vtsPageUrl = VehicleTestingStationUrlBuilderWeb::byId($id);

        $permission = $this->getPermissions($site);

        return new ViewModel(
            [
                'defaultBrakeTestClass1And2' => $site->getDefaultBrakeTestClass1And2(),
                'defaultParkingBrakeTestClass3AndAbove' => $site->getDefaultParkingBrakeTestClass3AndAbove(),
                'defaultServiceBrakeTestClass3AndAbove' => $site->getDefaultServiceBrakeTestClass3AndAbove(),
                'cancelRoute' => $vtsPageUrl,
                'canTestClass1Or2' => $permission->canTestClass1And2(),
                'canTestAnyOfClass3AndAbove' => $permission->canTestAnyOfClass3AndAbove(),
                'brakeTestTypes' => $this->catalog->getBrakeTestTypes(),
            ]
        );
    }

    /**
     * @param VehicleTestingStationDto $site
     * @return VtsOverviewPagePermissions
     */
    private function getPermissions($site)
    {
        $permissions = new VtsOverviewPagePermissions(
            $this->auth,
            $this->identity->getIdentity(),
            $site,
            $site->getPositions(),
            !empty($site->getOrganisation()) ? $site->getOrganisation()->getId() : ''
        );

        return $permissions;
    }

    /**
     * @return UpdateVtsAssertion
     */
    private function getUpdateVtsAssertion()
    {
        return new UpdateVtsAssertion($this->auth);
    }

    public function riskAssessmentAction()
    {
        $this->assertFeatureEnabled(FeatureToggle::VTS_RISK_SCORE);

        $siteId = (int)$this->params()->fromRoute('id');
        $this->auth->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_SITE_RISK_ASSESSMENT, $siteId);

        $vtsDto = $this->mapper->Site->getById($siteId);
        /** @var EnforcementSiteAssessmentDto $assessmentDto */
        $assessmentDto = $vtsDto->getAssessment();

        if(!$assessmentDto){
            return $this->createHttpNotFoundModel($this->getResponse());
        }

        $config = $this->getServiceLocator()->get(MotConfig::class);
        $ragClassifier = new RiskAssessmentScoreRagClassifier($assessmentDto->getSiteAssessmentScore(), $config);

        $permissions = $this->getPermissions($vtsDto);
        $vtsViewUrl = VehicleTestingStationUrlBuilderWeb::byId($siteId)->toString();
        $viewModel = new ViewModel(
            [
                'siteId' => $siteId,
                'vtsViewUrl' => $vtsViewUrl,
                'assessmentDto' => $assessmentDto,
                'permissions' => $permissions,
                'ragClassifier' => $ragClassifier
            ]
        );

        $breadcrumbs = [
            $vtsDto->getName() => $vtsViewUrl,
        ];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($vtsDto, $breadcrumbs);

        return $this->prepareViewModel(
            $viewModel,
            'Site assessment',
            'Vehicle Testing Station',
            $breadcrumbs
        );
    }

    public function addRiskAssessmentAction()
    {
        $this->assertFeatureEnabled(FeatureToggle::VTS_RISK_SCORE);

        $siteId = (int)$this->params()->fromRoute('id');
        $this->auth->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_SITE_RISK_ASSESSMENT, $siteId);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $vtsDto = $this->mapper->Site->getById($siteId);

        $form = $this->session->offsetGet(self::SITE_ASSESSMENT_SESSION_KEY);

        if (!($form instanceof VtsSiteAssessmentForm)) {
            $form = new VtsSiteAssessmentForm();
        }

        $form->setFormUrl(
            VehicleTestingStationUrlBuilderWeb::addSiteRiskAssessment($siteId)
        );

        if ($request->isPost()) {
            $form->fromPost($request->getPost());
            $dto = $form->toDto();
            $dto->setSiteId($vtsDto->getId());

            try {
                $dto = $this->mapper->Site->validateSiteAssessment($siteId, $dto);

                if($dto instanceof EnforcementSiteAssessmentDto){
                    $form = new VtsSiteAssessmentForm();
                    $form->fromDto($dto);
                }

                $this->session->offsetSet(self::SITE_ASSESSMENT_SESSION_KEY, $form);
                $confirmUrl = VehicleTestingStationUrlBuilderWeb::addSiteRiskAssessmentConfirm($siteId);

                return $this->redirect()->toUrl($confirmUrl);
            } catch (ValidationException $ve) {
                $form->addErrorsFromApi($ve->getErrors());
            }
        }

        $vtsViewUrl = VehicleTestingStationUrlBuilderWeb::byId($siteId)->toString();
        $cancelUrl  = VehicleTestingStationUrlBuilderWeb::cancelSiteRiskAssessment($siteId)->toString();

        $viewModel = new ViewModel(
            [
                'siteId' => $siteId,
                'vtsViewUrl' =>$vtsViewUrl,
                'cancelUrl' =>$cancelUrl,
                'form' => $form,
            ]
        );

        $breadcrumbs = [
            $vtsDto->getName() => $vtsViewUrl,
        ];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($vtsDto, $breadcrumbs);


        return $this->prepareViewModel(
            $viewModel,
            'Enter site assessment',
            'Vehicle Testing Station',
            $breadcrumbs
        );
    }

    public function addRiskAssessmentConfirmationAction()
    {
        $this->assertFeatureEnabled(FeatureToggle::VTS_RISK_SCORE);

        $siteId = (int)$this->params()->fromRoute('id');
        $this->auth->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_SITE_RISK_ASSESSMENT, $siteId);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $vtsDto = $this->mapper->Site->getById($siteId);
        $vtsViewUrl = VehicleTestingStationUrlBuilderWeb::byId($siteId)->toString();
        $cancelUrl = VehicleTestingStationUrlBuilderWeb::addSiteRiskAssessment($siteId)->toString();

        $form = $this->session->offsetGet(self::SITE_ASSESSMENT_SESSION_KEY);

        if (!$form instanceof VtsSiteAssessmentForm) {
            return $this->redirect()->toUrl($vtsViewUrl);
        }

        $form->setFormUrl(
            VehicleTestingStationUrlBuilderWeb::addSiteRiskAssessmentConfirm($siteId)
        );

        if ($request->isPost()) {
            try {
                $dto = $form->toDto();
                $dto->setSiteId($vtsDto->getId());
                $this->mapper->Site->updateSiteAssessment($siteId, $dto);
                $this->session->offsetUnset(self::SITE_ASSESSMENT_SESSION_KEY);

                $this->flashMessenger()->addSuccessMessage(self::MSG_ADD_RISK_ASSESSMENT_SUCCESS);
                return $this->redirect()->toUrl($vtsViewUrl);

            } catch (RestApplicationException $ve) {
                $this->addErrorMessages($ve->getDisplayMessages());
            }
        }

        $breadcrumbs = [
            $vtsDto->getName() => $vtsViewUrl,
        ];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($vtsDto, $breadcrumbs);

        $config = $this->getServiceLocator()->get(MotConfig::class);
        $ragClassifier = new RiskAssessmentScoreRagClassifier($form->getSiteAssessmentScore(), $config);

        $viewModel = new ViewModel(
            [
                'vtsViewUrl' => $vtsViewUrl,
                'cancelUrl' => $cancelUrl,
                'form' => $form,
                'ragClassifier' => $ragClassifier,
            ]
        );

        return $this->prepareViewModel(
            $viewModel,
            'Site assessment summary',
            'Vehicle Testing Station',
            $breadcrumbs
        );
    }

    public function cancelAddRiskAssessmentAction()
    {
        $siteId = $this->getSiteIdOrFail();
        $this->session->offsetUnset(self::SITE_ASSESSMENT_SESSION_KEY);

        $cancelUrl = VehicleTestingStationUrlBuilderWeb::byId($siteId)->toString();
        return $this->redirect()->toUrl($cancelUrl);
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getSiteIdOrFail()
    {
        $siteId = (int)$this->params('id');
        if ($siteId == 0) {
            throw new \Exception(self::ERR_MSG_INVALID_SITE_ID_OR_NR);
        }
        return $siteId;
    }



    /**
     * @param SiteDto $site
     * @param array $breadcrumbs
     * @return array
     */
    private function prependBreadcrumbsWithAeLink(SiteDto $site, &$breadcrumbs)
    {
        $org = $site->getOrganisation();

        if ($org) {
            $canVisitAePage = $this->canAccessAePage($org->getId());

            if($canVisitAePage) {
                $aeBreadcrumb = [$org->getName() => AuthorisedExaminerUrlBuilderWeb::of($org->getId())->toString()];
                $breadcrumbs = $aeBreadcrumb + $breadcrumbs;
            }
        }

        return $breadcrumbs;
    }

    private function canAccessAePage($orgId)
    {
        return
            $this->auth->isGranted(PermissionInSystem::AUTHORISED_EXAMINER_READ_FULL) ||
            $this->auth->isGrantedAtOrganisation(PermissionAtOrganisation::AUTHORISED_EXAMINER_READ, $orgId);
        ;
    }
}
