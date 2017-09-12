<?php

namespace Site\Controller;

use Application\Service\CatalogService;
use Core\BackLink\BackLinkQueryParam;
use Core\Catalog\BusinessRole\BusinessRoleCatalog;
use Core\Controller\AbstractAuthActionController;
use Core\Routing\VtsRouteList;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs\TesterTqiComponentsAtSiteBreadcrumbs;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Constants\Role;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\Site\EnforcementSiteAssessmentDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilderWeb;
use Organisation\Presenter\StatusPresenter;
use Site\Action\SiteTestQualityAction;
use Site\Action\SiteTestQualityCsvAction;
use Site\Action\UserTestQualityAction;
use Site\Authorization\VtsOverviewPagePermissions;
use Site\Form\VtsCreateForm;
use Site\Form\VtsSiteAssessmentForm;
use Site\Form\VtsUpdateTestingFacilitiesForm;
use Site\Service\RiskAssessmentScoreRagClassifier;
use Site\Service\SiteBreadcrumbsBuilder;
use Site\ViewModel\Sidebar\VtsOverviewSidebar;
use Site\ViewModel\SiteViewModel;
use Site\ViewModel\VehicleTestingStation\VtsFormViewModel;
use Zend\Http\Request;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

/**
 * Class SiteController.
 */
class SiteController extends AbstractAuthActionController
{
    const SESSION_CNTR_KEY = 'SITE_CREATE_UPDATE';
    const SESSION_KEY = 'data';
    const SITE_ASSESSMENT_SESSION_KEY = 'SITE_ASSESSMENT';

    const CREATE_TITLE = 'Create a site';
    const CREATE_CONFIRM_TITLE = 'Review new site';
    const SITE_SUBTITLE = 'Site management';
    const STEP_ONE = 'Step 1 of 2';
    const STEP_TWO = 'Step 2 of 2';

    const REFERER = 'refererToSite';

    const EDIT_SUBTITLE = 'Vehicle Testing Station';
    const EDIT_TESTING_FACILITIES = 'Change testing facilities';
    const EDIT_TESTING_FACILITIES_CONFIRM = 'Review change to testing facilities';

    const ROUTE_CONFIGURE_BRAKE_TEST_DEFAULTS = 'site/configure-brake-test-defaults';

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

    private $businessRoleCatalog;

    private $siteTestQualityAction;

    private $siteTestQualityCsvAction;

    private $viewVtsTestQualityAssertion;

    private $userTestQualityAction;

    private $contextProvider;

    private $testerTqiComponentsAtSiteBreadcrumbs;

    private $siteBreadcrumbsBuilder;

    public function __construct(
        MotFrontendAuthorisationServiceInterface $auth,
        MapperFactory $mapper,
        MotIdentityProviderInterface $identity,
        CatalogService $catalog,
        Container $session,
        BusinessRoleCatalog $businessRoleCatalog,
        SiteTestQualityAction $siteTestQualityAction,
        SiteTestQualityCsvAction $siteTestQualityCsvAction,
        UserTestQualityAction $userTestQualityAction,
        ViewVtsTestQualityAssertion $viewVtsTestQualityAssertion,
        ContextProvider $contextProvider,
        TesterTqiComponentsAtSiteBreadcrumbs $testerTqiComponentsAtSiteBreadcrumbs,
        SiteBreadcrumbsBuilder $siteBreadcrumbsBuilder
    ) {
        $this->auth = $auth;
        $this->mapper = $mapper;
        $this->identity = $identity;
        $this->catalog = $catalog;
        $this->session = $session;
        $this->businessRoleCatalog = $businessRoleCatalog;
        $this->siteTestQualityAction = $siteTestQualityAction;
        $this->siteTestQualityCsvAction = $siteTestQualityCsvAction;
        $this->userTestQualityAction = $userTestQualityAction;
        $this->viewVtsTestQualityAssertion = $viewVtsTestQualityAssertion;
        $this->contextProvider = $contextProvider;
        $this->testerTqiComponentsAtSiteBreadcrumbs = $testerTqiComponentsAtSiteBreadcrumbs;
        $this->siteBreadcrumbsBuilder = $siteBreadcrumbsBuilder;
    }

    /**
     * Display the details of a VTS.
     */
    public function indexAction()
    {
        $isEnforcementUser = $this->auth->hasRole(Role::VEHICLE_EXAMINER);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $vtsId = $this->params()->fromRoute('id', null);
        $backTo = $this->params()->fromQuery(BackLinkQueryParam::PARAM_BACK_TO, '');

        // Store url for back URL in following pages
        $refBack = new Container(self::REFERER);
        $refBack->uri = $request->getUri();

        if (isset($vtsId)) {
            $site = $this->mapper->Site->getById($vtsId);
        } else {
            throw new \Exception(self::ERR_MSG_INVALID_SITE_ID_OR_NR);
        }

        $permissions = $this->getPermissions($site);

        // Prepare view data
        $equipment = $this->mapper->Equipment->fetchAllForVts($site->getId());

        $testInProgress = ($permissions->canViewTestsInProgress()
            ? $this->mapper->MotTestInProgress->fetchAllForVts($site->getId())
            : []
        );

        $equipmentModelStatusMap = $this->catalog->getEquipmentModelStatuses();
        $siteStatusMap = $this->catalog->getSiteStatus();

        $view = new SiteViewModel($site, $equipment, $testInProgress, $permissions, $equipmentModelStatusMap, $this->url(), $backTo);

        // Get ref page
        $refSession = new Container('referralSession');
        if ($isEnforcementUser && !empty($refSession->url)) {
            $escRefPage = '/mot-test-search/vrm?'.http_build_query($refSession->url);
        } else {
            $escRefPage = null;
        }
        $refSession->url = false;

        $assessmentDto = $site->getCurrentAssessment();
        if ($assessmentDto instanceof EnforcementSiteAssessmentDto) {
            $riskAssessmentScore = $assessmentDto->getSiteAssessmentScore();
        } else {
            $riskAssessmentScore = 0;
        }

        $config = $this->getServiceLocator()->get(MotConfig::class);
        $ragClassifier = new RiskAssessmentScoreRagClassifier($riskAssessmentScore, $config);

        $searchString = null;
        if ($isEnforcementUser) {
            // Used when constructing the back-link.  If the searchString is provided we know
            // that the previous page was a VE search result page.  We can re-create the query from
            // the search string param; otherwise we default to the VE search page.
            $searchString = $request->getQuery(self::SEARCH_RESULT_PARAM);
        }

        // Logical block - prepare view model
        $this->layout()->setVariable(
            'pageTertiaryTitle',
            $site->getContactByType(SiteContactTypeCode::BUSINESS)->getAddress()->getFullAddressString()
        );

        $this->setHeadTitle('Vehicle Testing Station');
        $this->setUpIndexSidebar(
            $site->getStatus(), $testInProgress, is_object($site->getCurrentAssessment()), $ragClassifier, $this->getAssessmentDate($site)
        );

        $viewModel = new ViewModel(
            [
                'viewModel' => $view,
                'searchString' => $searchString,
                'escRefPage' => $escRefPage,
                'siteStatusMap' => $siteStatusMap,
                'ragClassifier' => $ragClassifier,
                'isVtsRiskEnabled' => $this->isFeatureEnabled(FeatureToggle::VTS_RISK_SCORE),
                'businessRoleCatalog' => $this->businessRoleCatalog,
                'statusPresenter' => new StatusPresenter(),
            ]
        );

        $breadcrumbs =
            $this->siteBreadcrumbsBuilder->getAeBreadcrumb($site)
            + ($view->userCameFromServiceReports() ? ['Service reports' => $view->getBackToServiceReportsLink()] : [])
            + $this->siteBreadcrumbsBuilder->getVtsBreadcrumb($site, false);

        return $this->prepareViewModel($viewModel, $site->getName(), self::EDIT_SUBTITLE, $breadcrumbs);
    }

    /**
     * @param VehicleTestingStationDto $site
     * @return null|string
     */
    private function getAssessmentDate(VehicleTestingStationDto $site) {
        $assessmentDate = null;
        if(is_object($site->getCurrentAssessment())) {
            $assessmentDate = DateTimeDisplayFormat::dateShort(new \DateTime($site->getCurrentAssessment()->getDateOfAssessment()));
        }
        return $assessmentDate;
    }

    private function setUpIndexSidebar($siteStatusCode, $testsInProgress, $hasBeenAssessed, RiskAssessmentScoreRagClassifier $ragClassifier, $assessmentDate)
    {
        $vtsId = (int) $this->params()->fromRoute('id');

        $activeTestsCount = count($testsInProgress);

        $sidebar = new VtsOverviewSidebar(
            $this->auth,
            $this->getFeatureToggles(),
            $this->catalog->getSiteStatus(),
            $vtsId,
            $siteStatusCode,
            $hasBeenAssessed,
            $ragClassifier,
            $activeTestsCount,
            $this->viewVtsTestQualityAssertion,
            $assessmentDate
        );

        $this->setSidebar($sidebar);
    }

    public function createAction()
    {
        $this->auth->assertGranted(PermissionInSystem::VEHICLE_TESTING_STATION_CREATE);

        /** @var Request $request */
        $request = $this->getRequest();

        // Create new form or get from session when come back from confirmation
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

        // Logical block - prepare view model
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

        // Get form from session
        $sessionKey = $request->getQuery(self::SESSION_KEY);
        $form = $this->session->offsetGet($sessionKey);

        // Redirect to create AE page if form data not provided
        if (!($form instanceof VtsCreateForm)) {
            return $this->redirect()->toUrl($urlCreate);
        }

        // Save AE to DB and redirect to AE view page
        if ($request->isPost()) {
            try {
                $result = $this->mapper->Site->create($form->toDto());

                // Clean session
                $this->session->offsetUnset($sessionKey);

                return $this->redirect()->toUrl(VehicleTestingStationUrlBuilderWeb::byId($result['id']));
            } catch (RestApplicationException $ve) {
                $this->addErrorMessages($ve->getDisplayMessages());
            }
        }

        // Create a model
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

    public function testingFacilitiesAction()
    {
        $siteId = $this->getSiteIdOrFail();
        $this->auth->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_TESTING_FACILITIES_DETAILS, $siteId);
        /** @var VehicleTestingStationDto $vtsDto */
        $vtsDto = $this->mapper->Site->getById($siteId);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        // Create new form or get from session when come back from confirmation
        $sessionKey = $request->getQuery(self::SESSION_KEY) ?: uniqid();
        $form = $this->session->offsetGet($sessionKey);

        if (!$form instanceof VtsUpdateTestingFacilitiesForm) {
            /** @var VehicleTestingStationDto $vtsDto */
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

        // Logical block - prepare view model
        $viewModel = new ViewModel([
            'form' => $form,
            'cancelUrl' => $vtsViewUrl,
        ]);

        $breadcrumbs = [
            $form->getVtsDto()->getName() => $vtsViewUrl,
        ];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($form->getVtsDto(), $breadcrumbs);

        $subTitle = self::EDIT_SUBTITLE.' - '.$form->getVtsDto()->getSiteNumber();

        $this->layout()->setVariable(
            'pageTertiaryTitle',
            $vtsDto->getContactByType(SiteContactTypeCode::BUSINESS)
                ->getAddress()->getFullAddressString()
        );

        $this->setHeadTitle('Change testing facilities');

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

        // Get form from session
        $sessionKey = $request->getQuery(self::SESSION_KEY);
        $form = $this->session->offsetGet($sessionKey);

        if (!$form instanceof VtsUpdateTestingFacilitiesForm) {
            return $this->redirect()->toUrl($vtsViewUrl);
        }

        /** @var VehicleTestingStationDto $vtsDto */
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
            'form' => $form,
            'cancelUrl' => $cancelUrl,
        ]);

        $this->layout()->setVariable(
            'pageTertiaryTitle',
            $vtsDto->getContactByType(SiteContactTypeCode::BUSINESS)
                ->getAddress()->getFullAddressString()
        );

        $this->setHeadTitle('Review change to testing facilities');

        return $this->prepareViewModel(
            $viewModel,
            self::EDIT_TESTING_FACILITIES_CONFIRM,
            sprintf('Site - %s', $vtsDto->getSiteNumber()),
            $breadcrumbs,
            self::STEP_TWO
        );
    }

    /**
     * Prepare the view model for all the step of the create ae.
     *
     * @param ViewModel $view
     * @param string    $title
     * @param string    $subtitle
     * @param array     $breadcrumbs
     * @param array     $progress
     * @param string    $template
     *
     * @return ViewModel
     *
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
        // Logical block - prepare view
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
        $id = (int) $this->params()->fromRoute('id');

        if ($id <= 0) {
            throw new \Exception(self::ERR_MSG_INVALID_SITE_ID_OR_NR);
        }

        $this->auth->assertGrantedAtSite(PermissionAtSite::DEFAULT_BRAKE_TESTS_CHANGE, $id);

        $defaultBrakeTestClass1And2Options = [
            BrakeTestTypeCode::ROLLER,
            BrakeTestTypeCode::PLATE,
            BrakeTestTypeCode::DECELEROMETER,
            BrakeTestTypeCode::FLOOR,
            BrakeTestTypeCode::GRADIENT,
        ];

        $defaultParkingBrakeTestClass3AndAboveOptions = [
            BrakeTestTypeCode::ROLLER,
            BrakeTestTypeCode::PLATE,
            BrakeTestTypeCode::DECELEROMETER,
            BrakeTestTypeCode::GRADIENT,
        ];

        $defaultServiceBrakeTestClass3AndAboveOptions = [
            BrakeTestTypeCode::ROLLER,
            BrakeTestTypeCode::PLATE,
            BrakeTestTypeCode::DECELEROMETER,
        ];

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
                'defaultBrakeTestClass1And2Options' => $defaultBrakeTestClass1And2Options,
                'defaultParkingBrakeTestClass3AndAboveOptions' => $defaultParkingBrakeTestClass3AndAboveOptions,
                'defaultServiceBrakeTestClass3AndAboveOptions' => $defaultServiceBrakeTestClass3AndAboveOptions,
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
     *
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

    public function riskAssessmentAction()
    {
        $this->assertFeatureEnabled(FeatureToggle::VTS_RISK_SCORE);

        $siteId = (int) $this->params()->fromRoute('id');
        $this->auth->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_SITE_RISK_ASSESSMENT, $siteId);

        $vtsDto = $this->mapper->Site->getById($siteId);
        /** @var EnforcementSiteAssessmentDto $assessmentDto */
        $assessmentDto = $vtsDto->getCurrentAssessment();

        if (!$assessmentDto) {
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
                'ragClassifier' => $ragClassifier,
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

        $siteId = (int) $this->params()->fromRoute('id');
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

                if ($dto instanceof EnforcementSiteAssessmentDto) {
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
        $cancelUrl = VehicleTestingStationUrlBuilderWeb::cancelSiteRiskAssessment($siteId)->toString();

        $viewModel = new ViewModel(
            [
                'siteId' => $siteId,
                'vtsViewUrl' => $vtsViewUrl,
                'cancelUrl' => $cancelUrl,
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

        $siteId = (int) $this->params()->fromRoute('id');
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
     * @throws \Exception
     *
     * @return int
     */
    private function getSiteIdOrFail()
    {
        $siteId = (int) $this->params('id');
        if ($siteId == 0) {
            throw new \Exception(self::ERR_MSG_INVALID_SITE_ID_OR_NR);
        }

        return $siteId;
    }

    /**
     * @param SiteDto $site
     * @param array   $breadcrumbs
     *
     * @return array
     */
    private function prependBreadcrumbsWithAeLink(SiteDto $site, $breadcrumbs)
    {
        $org = $site->getOrganisation();

        if ($org) {
            $canVisitAePage = $this->canAccessAePage($org->getId());

            if ($canVisitAePage) {
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
    }

    public function testQualityAction()
    {
        $id = (int) $this->params('id');
        $monthRange = (int) $this->params()->fromQuery('monthRange', 1);
        $isReturnToAETQI = (bool) $this->params()->fromQuery(BackLinkQueryParam::RETURN_TO_AE_TQI);

        return $this->applyActionResult(
            $this->siteTestQualityAction->execute(
                $id, $monthRange, $isReturnToAETQI,
                $this->buildBreadcrumbs($id)
            )
        );
    }

    public function userTestQualityAction()
    {
        $isReturnToAETQI = (bool) $this->params()->fromQuery(BackLinkQueryParam::RETURN_TO_AE_TQI);

        $group = $this->params('group');
        $monthRange = (int) $this->params()->fromQuery('monthRange', 1);

        if ($this->contextProvider->isYourProfileContext() || $this->contextProvider->isPerformanceDashboardContext()) {
            $vtsId = $this->params('site');
            $userId = $this->identity->getIdentity()->getUserId();
            $breadcrumbs = $this->testerTqiComponentsAtSiteBreadcrumbs->getBreadcrumbs($userId, $monthRange);
        } elseif ($this->contextProvider->isUserSearchContext()) {
            $vtsId = $this->params('site');
            $userId = $this->params('id');
            $breadcrumbs = $this->testerTqiComponentsAtSiteBreadcrumbs->getBreadcrumbs($userId, $monthRange);
        } elseif ($this->getEvent()->getRouteMatch()->getMatchedRouteName() === VtsRouteList::VTS_USER_PROFILE_TEST_QUALITY) {
            $vtsId = $this->params('id');
            $userId = $this->params('userId');
            $breadcrumbs = $this->testerTqiComponentsAtSiteBreadcrumbs->getBreadcrumbs($userId, $monthRange);
        } else {
            $vtsId = $this->params('id');
            $userId = $this->params('userId');
            $breadcrumbs = $this->buildBreadcrumbs($vtsId);
        }

        return $this->applyActionResult(
            $this->userTestQualityAction->execute($vtsId, $userId, $monthRange, $group, $breadcrumbs, $isReturnToAETQI, $this->url())
        );
    }

    private function buildBreadcrumbs($vtsId)
    {
        $vtsDto = $this->mapper->Site->getById($vtsId);
        $breadcrumbs = [
            $vtsDto->getName() => $this->url()->fromRoute('vehicle-testing-station', ['id' => $vtsId]),
        ];

        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($vtsDto, $breadcrumbs);

        return $breadcrumbs;
    }

    public function testQualityCsvAction()
    {
        $id = $this->params('id');
        $group = $this->params('group');
        $monthRange = (int) $this->params('monthRange');

        $csv = $this->siteTestQualityCsvAction->execute($id, $monthRange, $group);

        return $this->applyActionResult($csv);
    }
}
