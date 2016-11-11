<?php

namespace DvsaMotTest\Controller;

use Application\Service\CatalogService;
use Application\Service\ContingencySessionManager;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Constants\FeatureToggle;
use DvsaMotTest\Service\OverdueSpecialNoticeAssertion;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Vehicle\History\VehicleHistoryDto;
use DvsaCommon\Dto\Vehicle\History\VehicleHistoryMapper;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;
use DvsaCommon\UrlBuilder\VehicleUrlBuilderWeb;
use DvsaCommon\Utility\AddressUtils;
use DvsaCommon\Utility\ArrayUtils;
use DvsaMotTest\Form\VehicleSearch;
use DvsaMotTest\Model\VehicleSearchResult;
use DvsaMotTest\Service\VehicleSearchService;
use DvsaMotTest\View\VehicleSearchResult\VehicleSearchResultMessage;
use DvsaMotTest\View\VehicleSearchResult\VehicleSearchResultUrlTemplateInterface;
use Zend\Escaper\Escaper;
use Zend\Form\Element\Hidden;
use Zend\Http\Response;
use Zend\I18n\Filter\Alnum;
use Zend\View\Model\ViewModel;
use DvsaCommon\HttpRestJson\Client;

/**
 * VehicleSearch Controller.
 */
class VehicleSearchController extends AbstractDvsaMotTestController
{
    const CONTINGENCY_FORM_NOT_RECORDED = 'The contingency test form has not been filled in.';
    const PARTIAL_VIN_NO_REG_ERROR = 'Please complete the registration number field.';
    const NO_VIN_AND_NO_REG_ERROR = 'Enter the registration mark and Vehicle Identification Number (VIN) to search for a vehicle.';
    const VIN_REQUIRED_ERROR = 'Enter a VIN to perform the search';
    const MOT_TEST_NUMBER_PARAM = 'motTestNumberOriginal';

    const SEARCH_RESULT_NO_MATCH = 'NO-MATCH';
    const SEARCH_RESULT_EXACT_MATCH = 'EXACT-MATCH';
    const SEARCH_RESULT_MULTIPLE_MATCHES = 'MULTIPLE-MATCHES';
    const SEARCH_RESULT_TOO_MANY_MATCHES = 'TOO-MANY-MATCHES';
    const SEARCH_REGISTRATION_NUMBER_MAX_LENGTH = 13;
    const SEARCH_VIN_NUMBER_MAX_LENGTH = 20;

    // If the VIN length is this long, then a partial match will be used (based on the N last characters)
    const PARTIAL_MATCH_VIN_LENGTH = 6;

    const SEARCH_TYPE = 'searchType';
    const VIN_SEARCH_TYPE = 'vinSearchType';
    const FULL_VIN = 'fullVin';
    const PARTIAL_VIN = 'partialVin';
    const NO_VIN = 'noVin';

    const SEARCH_PARAM_MOT_ID = 'id';
    const SEARCH_PARAM_SEARCH_CRITERIA = 'criteria';
    const SEARCH_PARAM_SEARCH_TYPE = 'type';

    const ROUTE_REPLACEMENT_CERTIFICATE_VEHICLE_SEARCH = 'replacement-certificate-vehicle-search';
    const ROUTE_VEHICLE_SEARCH = 'vehicle-search';
    const ROUTE_VEHICLE_SEARCH_RETEST = 'retest-vehicle-search';
    const ROUTE_VEHICLE_SEARCH_TRAINING = 'training-test-vehicle-search';
    const ROUTE_VEHICLE_SEARCH_NON_MOT = 'non-mot-test-vehicle-search';

    const PRM_SUBMIT = 'submit';
    const PRM_VIN = 'vin';
    const PRM_REG = 'registration';
    const PRM_VIN_TYPE = 'vinType';
    const PRM_TEST_NR = 'testNumber';

    const START_TEST_CONFIRMATION_ROUTE = 'start-test-confirmation';
    const START_RETEST_CONFIRMATION_ROUTE = 'start-retest-confirmation';
    const START_TRAINING_CONFIRMATION_ROUTE = 'start-training-confirmation';

    /**
     * @var array
     */
    private static $VIN_SEARCH_TYPES
        = [
            self::FULL_VIN => 'Full vin',
            self::PARTIAL_VIN => 'Partial vin',
            self::NO_VIN => 'No vin',
        ];

    /** @var VehicleSearchService */
    private $vehicleSearchService;

    /** @var \DvsaCommon\Obfuscate\ParamObfuscator */
    private $paramObfuscator;

    /** @var CatalogService */
    private $catalogService;

    /** @var VehicleSearchResult */
    private $vehicleSearchResultModel;

    /** @var MapperFactory */
    private $mapperFactory;

    /** @var Client */
    private $client;

    /**
     * @param VehicleSearchService $vehicleSearchService
     * @param ParamObfuscator $paramObfuscator
     * @param CatalogService $catalogService
     * @param VehicleSearchResult $vehicleSearchResultModel
     */
    public function __construct(
        VehicleSearchService $vehicleSearchService,
        ParamObfuscator $paramObfuscator,
        CatalogService $catalogService,
        VehicleSearchResult $vehicleSearchResultModel,
        MapperFactory $mapperFactory,
        Client $client
    ) {
        $this->paramObfuscator = $paramObfuscator;
        $this->catalogService = $catalogService;
        $this->vehicleSearchResultModel = $vehicleSearchResultModel;
        $this->vehicleSearchService = $vehicleSearchService;
        $this->mapperFactory = $mapperFactory;
        $this->client = $client;
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function vehicleSearchAction()
    {
        $this->assertGranted(PermissionInSystem::MOT_TEST_START);

        return $this->vehicleSearch(VehicleSearchService::SEARCH_TYPE_STANDARD);
    }

    /**
     * Duplicate and Replacement vehicle search.s.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function replacementCertificateVehicleSearchAction()
    {
        $this->assertGranted(PermissionInSystem::CERTIFICATE_SEARCH);

        if (!$this->getAuthorizationService()->isGranted(PermissionInSystem::CERTIFICATE_READ_FROM_ANY_SITE)
            && !$this->getIdentity()->getCurrentVts()
        ) {
            return $this->redirectToLocationSelectScreen();
        }

        return $this->vehicleSearch(VehicleSearchService::SEARCH_TYPE_CERTIFICATE);
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function trainingTestVehicleSearchAction()
    {
        $this->getAuthorizationService()->assertGranted(PermissionInSystem::MOT_DEMO_TEST_PERFORM);

        return $this->vehicleSearch(VehicleSearchService::SEARCH_TYPE_TRAINING);
    }

    /**
     * @return \Zend\View\Model\ViewModel|array
     */
    public function nonMotVehicleSearchAction()
    {
        if (!$this->isFeatureEnabled(FeatureToggle::MYSTERY_SHOPPER)) {
            return $this->notFoundAction();
        }

        $this->getAuthorizationService()->assertGranted(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM);

        return $this->vehicleSearch(VehicleSearchService::SEARCH_TYPE_NON_MOT);
    }

    /**
     * History for DVSA users.
     *
     * @return ViewModel
     */
    public function dvsaTestHistoryAction()
    {
        $this->getAuthorizationService()->assertGranted(PermissionInSystem::CERTIFICATE_READ);

        $obfuscatedVehicleId = (string)$this->params('id', 0);
        $vehicleId = $this->paramObfuscator->deobfuscateEntry(
            ParamObfuscator::ENTRY_VEHICLE_ID,
            $obfuscatedVehicleId,
            false
        );
        $vin = $this->params()->fromQuery('vin');
        $registration = $this->params()->fromQuery('registration');

        $vehicleHistory = new VehicleHistoryDto();
        $vehicle = [];

        try {
            // Get vehicle data.
            /** @var VehicleService $vehicleService */
            $vehicleService = $this->getServiceLocator()->get(VehicleService::class);
            $vehicle = $vehicleService->getDvsaVehicleById((int) $vehicleId);

            // Get history data.
            $apiUrl = VehicleUrlBuilder::testHistory($vehicleId);
            $apiResult = $this->getRestClient()->get($apiUrl);

            $vehicleHistory = (new VehicleHistoryMapper())->fromArrayToDto($apiResult['data'], 0);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return new ViewModel(
            [
                'vehicleHistory' => $vehicleHistory,
                'vehicle' => $vehicle,
                'vin' => $vin,
                'registration' => $registration
            ]
        );
    }

    /**
     * @param string $obfuscatedVehicleId
     * @param array $postParams
     *
     * @return \Zend\Http\Response
     */
    private function handleDifferentVtsAction($obfuscatedVehicleId, $postParams)
    {
        $motTestId = ArrayUtils::get($postParams, 'id');
        $motTestNumber = ArrayUtils::get($postParams, 'number');
        $v5c = ArrayUtils::get($postParams, 'v5c');

        if (($v5c && $motTestNumber) || (!$v5c && !$motTestNumber)) {
            $this->addErrorMessages('Please enter either V5C number or MOT certificate number');

            return $this->redirect()->toUrl(VehicleUrlBuilderWeb::historyMotCertificates($obfuscatedVehicleId));
        }

        if ($motTestId) {
            $apiUrl = $searchCriteria = $searchType = null;

            if ($v5c) {
                $apiUrl = MotTestUrlBuilder::findByMotTestIdAndV5c($motTestId, $v5c)->toString();
                $searchCriteria = $v5c;
            } elseif ($motTestNumber) {
                $apiUrl = MotTestUrlBuilder::findByMotTestIdAndMotTestNumber($motTestId, $motTestNumber)->toString();
                $searchCriteria = $motTestNumber;
            }

            return $this->handleV5cOrMotTestNumberCall(
                $obfuscatedVehicleId,
                $apiUrl,
                $searchCriteria,
                $motTestId
            );
        }

        return $this->redirect()->toUrl(VehicleUrlBuilderWeb::historyMotCertificates($obfuscatedVehicleId));
    }

    /**
     * @param string $obfuscatedVehicleId
     * @param string $apiUrl
     * @param string $searchCriteria
     * @param string $motTestId
     *
     * @return \Zend\Http\Response
     */
    private function handleV5cOrMotTestNumberCall(
        $obfuscatedVehicleId,
        $apiUrl,
        $searchCriteria,
        $motTestId
    ) {
        $apiResult = $this->getRestClient()->get($apiUrl);

        $motTestNumberFromApi = $apiResult['data'];

        if ($motTestNumberFromApi) {
            return $this->redirect()->toRoute(
                'mot-test-certificate',
                ['motTestNumber' => $motTestNumberFromApi]
            );
        }

        if ($this->vehicleSearchService->isV5cSearchType()) {
            $this->addErrorMessages('The V5C number is incorrect');
        } else {
            $this->addErrorMessages('The MOT certificate number is incorrect');
        }

        return $this->redirect()->toRoute(
            'vehicle-test-history',
            ['id' => $obfuscatedVehicleId],
            [
                'query' => [
                    self::SEARCH_PARAM_SEARCH_CRITERIA => urlencode($searchCriteria),
                    self::SEARCH_PARAM_SEARCH_TYPE => urlencode($this->vehicleSearchService->getSearchType()),
                    self::SEARCH_PARAM_MOT_ID => urlencode($motTestId),
                ],
                'fragment' => 'show-form-' . $motTestId,
            ]
        );
    }

    /**
     * History for testers.
     *
     * @throws \DvsaCommon\Obfuscate\InvalidArgumentException
     *
     * @return ViewModel
     */
    public function testHistoryAction()
    {
        $this->getAuthorizationService()->assertGranted(PermissionInSystem::CERTIFICATE_READ);
        $obfuscatedVehicleId = (string)$this->params()->fromRoute('id', 0);
        $vehicleId = $this->paramObfuscator->deobfuscateEntry(
            ParamObfuscator::ENTRY_VEHICLE_ID,
            $obfuscatedVehicleId,
            false
        );
        $motId = (int)$this->params()->fromQuery('id', 0);
        $searchType = $this->params()->fromQuery('type', VehicleSearchService::SEARCH_TYPE_V5C);
        $searchCriteria = urldecode($this->params()->fromQuery('criteria', ''));
        $vin = $this->params()->fromQuery('vin');
        $registration = $this->params()->fromQuery('registration');

        $this->vehicleSearchService->setSearchType($searchType);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            return $this->handleDifferentVtsAction($obfuscatedVehicleId, $request->getPost()->toArray());
        }

        /** @var \Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation $site */
        $site = $this->getIdentity()->getCurrentVts();
        $siteId = $site->getVtsId();

        $vehicleHistory = new VehicleHistoryDto();
        $vehicle = [];

        try {
            // Get vehicle data.
            /** @var VehicleService $vehicleService */
            $vehicleService = $this->getServiceLocator()->get(VehicleService::class);
            $vehicle = $vehicleService->getDvsaVehicleById((int) $vehicleId);

            // Get history data.
            $apiUrl = VehicleUrlBuilder::testHistory($vehicleId);
            $apiResult = $this->getRestClient()->get($apiUrl);

            $vehicleHistory = (new VehicleHistoryMapper())->fromArrayToDto($apiResult['data'], $siteId);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return new ViewModel(
            [
                'obfuscatedVehicleId' => $obfuscatedVehicleId,
                'vehicleHistory' => $vehicleHistory,
                'vehicle' => $vehicle,
                'siteName' => $site->getName() . ', ' . AddressUtils::stringify($site->getAddress()),
                'searchParamMotId' => $motId,
                'searchParamType' => $searchType,
                'searchParamCriteria' => $searchCriteria,
                'registration' => $registration,
                'vin' => $vin
            ]
        );
    }

    /**
     * @param string $searchType
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    private function vehicleSearch($searchType)
    {
        if (is_null($searchType)) {
            throw new \InvalidArgumentException('A search type must be specified');
        }

        $loggedInUserManager = $this->getServiceLocator()->get('LoggedInUserManager');
        $tester = $loggedInUserManager->getTesterData();

        if ($this->getAuthorizationService()->isTester() && in_array($searchType, [
                    VehicleSearchService::SEARCH_TYPE_STANDARD, VehicleSearchService::SEARCH_TYPE_RETEST
                ])) {
            $authorisationsForTestingMot = (!is_null($tester["authorisationsForTestingMot"]))? $tester["authorisationsForTestingMot"]: [];
            $url = (new UrlBuilder())->specialNoticeOverdue()->toString();
            $overdueSpecialNotices = $this->client->get($url)["data"];

            $overdueSpecialNotices = new OverdueSpecialNoticeAssertion($overdueSpecialNotices, $authorisationsForTestingMot);
            $overdueSpecialNotices->assertPerformTest();
        }

        $this->vehicleSearchService->setSearchType($searchType);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        /*
         * We check if we the user start to test a contingency test and validate if the form is really set in the
         * session. If not we redirect to an error page who ask the user to fill the form.
         */
        $ctSessionMng = $this->getContingencySessionManager();

        $isContingency = ($request->getQuery('contingency') !== null);
        if ($isContingency) {
            if ($ctSessionMng->isMotContingency() === false) {
                $ctSessionMng->deleteContingencySession();

                return $this->redirect()->toRoute('contingency-error');
            }
        } else {
            $ctSessionMng->deleteContingencySession();
        }

        if (!$this->vehicleSearchService->isTrainingSearchType()
            && $this->getAuthorizationService()->isTester()
            && !$this->getIdentity()->getCurrentVts()
        ) {


            // Avoid redirecting to the LocationSelectionController if the tester has only one associated site.
            if (count($tester['vtsSites']) == 1) {
                $loggedInUserManager->changeCurrentLocation($tester['vtsSites'][0]['id']);
            } else {
                $routeMatch = $this->getRouteMatch();
                $route = $routeMatch->getMatchedRouteName();
                $params = [];
                if ($isContingency) {
                    $params = ['contingency' => 1];
                }

                $container = $this->getServiceLocator()->get('LocationSelectContainerHelper');
                $container->persistConfig(['route' => $route, 'params' => $params]);

                return $this->redirect()->toRoute('location-select');
            }
        }

        if ($this->getVehicleSearchService()->areSlotsNeeded($searchType)) {
            $slots = $this->getIdentity()->getCurrentVts()->getSlots();
            if (!$slots) {
                $this->addErrorMessages('Your VTS has no slots available');

                return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
            }
        }

        $showCreateButton = false;
        $form = $this->getForm(new VehicleSearch());

        if ($ctSessionMng->isMotContingency() === true) {
            $hiddenContingencyField = new Hidden('contingency');
            $hiddenContingencyField->setValue('1');
            $form->add($hiddenContingencyField);
        }

        $regEntered = $this->params()->fromQuery(self::PRM_REG, null);
        $vinEntered = $this->params()->fromQuery(self::PRM_VIN, null);
        $escaper = new Escaper('utf-8');

        if (!is_null($regEntered) && !is_null($vinEntered)) {
            $filter = new Alnum();
            $filter->setAllowWhiteSpace(false);

            $vin = $filter->filter($vinEntered);
            $reg = $filter->filter($regEntered);

            $data = [
                self::PRM_VIN => strtoupper($vin),
                self::PRM_REG => strtoupper($reg),
            ];
            $form->setData($data);

            if ($form->isValid()) {
                $form->setData([self::PRM_VIN => $vinEntered, self::PRM_REG => $regEntered]);
                $noRegistration = ($reg) ? 0 : 1;

                if (!($reg || $vin)) {
                    return $this->returnViewModel(
                        $form,
                        true,
                        true,
                        $this->vehicleSearchService->isStandardSearchType(),
                        [],
                        false,
                        null,
                        $this->getVehicleSearchService()->getSearchResultMessage(
                            $escaper->escapeHtml($regEntered),
                            $escaper->escapeHtml($vinEntered),
                            0
                        )
                    );
                }

                try {
                    $vtsId = false;

                    if ($this->getIdentity()->getCurrentVts()) {
                        $vtsId = $this->getIdentity()->getCurrentVts()->getVtsId();
                    }

                    $vehicles = $this->getVehicleSearchService()->search(
                        $vin,
                        $reg,
                        $this->getVehicleSearchService()->shouldSearchInDvlaVehicleList($searchType),
                        $vtsId,
                        $ctSessionMng->isMotContingency()
                    );

                    return $this->returnViewModel(
                        $form,
                        empty($vehicles),
                        false,
                        $this->vehicleSearchService->isStandardSearchType(),
                        $vehicles,
                        false,
                        $this->vehicleSearchService->getUrlTemplate($searchType, $noRegistration, $this->url()),
                        $this->getVehicleSearchService()->getSearchResultMessage(
                            $escaper->escapeHtml($regEntered),
                            $escaper->escapeHtml($vinEntered),
                            count($vehicles)
                        )
                    );
                } catch (RestApplicationException $e) {
                    $this->addErrorMessages($e->getDisplayMessages());

                    return $this->returnViewModel($form, false, false, $showCreateButton);
                }
            }
        }

        return $this->returnViewModel($form);
    }

    /**
     * @param \Zend\Form\Form $form form object for vehicle search
     * @param bool $noMatches true if there were no matches in previous search
     * @param bool $regError true if reg was not provided, but required
     * @param bool $showCreateButton true to show Create Vehicle button
     * @param array $results show any non exact or multiple results for search criteria
     * @param bool $tooManyMatches more than five results returned
     * @param VehicleSearchResultUrlTemplateInterface $urlTemplate creates a searchType specific url to follow from results list
     * @param VehicleSearchResultMessage $searchResultMessage message for the user when no results are found
     *
     *
     * @return ViewModel vehicle search view model
     */
    private function returnViewModel(
        $form,
        $noMatches = false,
        $regError = false,
        $showCreateButton = false,
        $results = null,
        $tooManyMatches = false,
        VehicleSearchResultUrlTemplateInterface $urlTemplate = null,
        VehicleSearchResultMessage $searchResultMessage = null
    ) {
        $contingencySession = $this->getContingencySessionManager();

        $this->selectPageSubTitle();

        $viewModel = new ViewModel(
            [
                'form' => $form,
                'regError' => $regError,
                'noMatches' => $noMatches,
                'showCreateButton' => $showCreateButton,
                'results' => $results,
                'tooManyMatches' => $tooManyMatches,
                'isMotContingency' => $contingencySession->isMotContingency(),
                'registrationNumberMaxLength' => self::SEARCH_REGISTRATION_NUMBER_MAX_LENGTH,
                'searchResultMessage' => $searchResultMessage,
                self::SEARCH_TYPE => $this->vehicleSearchService->getSearchType(),
                self::VIN_SEARCH_TYPE => self::$VIN_SEARCH_TYPES,
                'urlTemplate' => $urlTemplate,
                'isRetest' => $this->vehicleSearchService->isRetestSearchType(),
                'isTraining' => $this->vehicleSearchService->isTrainingSearchType(),
                'isReplCert' => $this->vehicleSearchService->isReplacementCertifificateSearchType()
            ]
        );

        $viewModel->setTemplate('dvsa-mot-test/vehicle-search/vehicle-search.phtml');

        return $viewModel;
    }

    /**
     * @return bool
     */
    private function selectPageSubTitle()
    {
        $isRetestSearchType = $this->vehicleSearchService->isRetestSearchType();
        $isReplacementCertificateSearchType = $this->vehicleSearchService->isReplacementCertifificateSearchType();

        if (!$isRetestSearchType && !$isReplacementCertificateSearchType) {
            $this->layout()->setVariable('pageSubTitle', 'MOT testing');
        }

        if ($this->vehicleSearchService->isTrainingSearchType()) {
            $this->layout()->setVariable('pageSubTitle', 'Training test');
        }

        if ($this->vehicleSearchService->isReplacementCertifificateSearchType()) {
            $this->layout()->setVariable('pageSubTitle', 'Duplicate or replacement certificate');
        }

        if($this->isFeatureEnabled(FeatureToggle::MYSTERY_SHOPPER) && $this->vehicleSearchService->isNonMotSearchType()) {
            $this->layout()->setVariable('pageSubTitle', 'Non-MOT test');
        }
    }

    /**
     * @return \Zend\Http\Response
     */
    private function redirectToLocationSelectScreen()
    {
        $routeMatch = $this->getRouteMatch();
        $container = $this->getServiceLocator()->get('LocationSelectContainerHelper');
        $container->persistConfig(
            [
                'route' => $routeMatch->getMatchedRouteName(),
                'params' => $routeMatch->getParams(),
            ]
        );

        return $this->redirect()->toRoute(LocationSelectController::ROUTE);
    }

    /**
     * @return ContingencySessionManager
     */
    private function getContingencySessionManager()
    {
        return $this->serviceLocator->get(ContingencySessionManager::class);
    }

    /**
     * @return VehicleSearchService
     */
    private function getVehicleSearchService()
    {
        return $this->vehicleSearchService;
    }

    /**
     * @return \Zend\Mvc\Router\RouteMatch
     */
    private function getRouteMatch()
    {
        return $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
    }
}
