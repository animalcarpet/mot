<?php

namespace DvsaMotTest\Controller;

use Application\Service\CatalogService;
use Application\Service\ContingencySessionManager;
use Core\Service\MotFrontendIdentityProviderInterface;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaMotTest\Action\DuplicateCertificateSearchByRegistrationAction;
use DvsaMotTest\Action\DuplicateCertificateSearchByVinAction;
use DvsaMotTest\Action\VehicleCertificatesAction;
use DvsaMotTest\Form\VehicleSearch;
use DvsaMotTest\Model\VehicleSearchResult;
use DvsaMotTest\Service\OverdueSpecialNoticeAssertion;
use DvsaMotTest\Service\StartTestChangeService;
use DvsaMotTest\Service\VehicleSearchService;
use DvsaMotTest\View\VehicleSearchResult\VehicleSearchResultMessage;
use DvsaMotTest\View\VehicleSearchResult\VehicleSearchResultUrlTemplateInterface;
use Zend\Escaper\Escaper;
use Zend\Form\Element\Hidden;
use Zend\I18n\Filter\Alnum;
use Zend\View\Model\ViewModel;

/**
 * VehicleSearch Controller.
 */
class VehicleSearchController extends AbstractDvsaMotTestController implements AutoWireableInterface
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
    const REPLACEMENT_CERTIFICATE_SEARCH_PAGE_TITLE = 'Duplicate or replacement certificate';

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

    private $motFrontendIdentity;
    private $duplicateCertificateSearchByRegistrationAction;
    private $duplicateCertificateSearchByVinAction;

    /** @var MotAuthorisationServiceInterface */
    protected $authorisationService;

    /** @var  StartTestChangeService */
    private $startTestChangeService;

    /**
     * @param VehicleSearchService $vehicleSearchService
     * @param ParamObfuscator $paramObfuscator
     * @param CatalogService $catalogService
     * @param VehicleSearchResult $vehicleSearchResultModel
     * @param MapperFactory $mapperFactory
     * @param Client $client
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param StartTestChangeService $startTestChangeService
     */
    public function __construct(
        VehicleSearchService $vehicleSearchService,
        ParamObfuscator $paramObfuscator,
        CatalogService $catalogService,
        VehicleSearchResult $vehicleSearchResultModel,
        MapperFactory $mapperFactory,
        Client $client,
        MotAuthorisationServiceInterface $authorisationService,
        MotFrontendIdentityProviderInterface $motFrontendIdentity,
        DuplicateCertificateSearchByRegistrationAction $duplicateCertificateSearchByRegistrationAction,
        DuplicateCertificateSearchByVinAction $duplicateCertificateSearchByVinAction,
        StartTestChangeService $startTestChangeService
    ) {
        $this->paramObfuscator = $paramObfuscator;
        $this->catalogService = $catalogService;
        $this->vehicleSearchResultModel = $vehicleSearchResultModel;
        $this->vehicleSearchService = $vehicleSearchService;
        $this->mapperFactory = $mapperFactory;
        $this->client = $client;
        $this->authorisationService = $authorisationService;
        $this->motFrontendIdentity = $motFrontendIdentity;
        $this->duplicateCertificateSearchByRegistrationAction = $duplicateCertificateSearchByRegistrationAction;
        $this->duplicateCertificateSearchByVinAction = $duplicateCertificateSearchByVinAction;
        $this->startTestChangeService = $startTestChangeService;
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
     * Duplicate and Replacement vehicle search by registration
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function replacementCertificateVehicleRegistrationSearchAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::CERTIFICATE_SEARCH);

        return $this->applyActionResult(
            $this->duplicateCertificateSearchByRegistrationAction->execute(
                $this->getRequest()->getQuery()->toArray()
            )
        );
    }

    /**
     * Duplicate and Replacement vehicle search by vin
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function replacementCertificateVehicleVinSearchAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::CERTIFICATE_SEARCH);

        return $this->applyActionResult(
            $this->duplicateCertificateSearchByVinAction->execute(
                $this->getRequest()->getQuery()->toArray()
            )
        );
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function trainingTestVehicleSearchAction()
    {
        $this->getAuthorizationService()->assertGranted(PermissionInSystem::MOT_DEMO_TEST_PERFORM);

        return $this->vehicleSearch(VehicleSearchService::SEARCH_TYPE_TRAINING);
    }

    public function certificateListAction()
    {
        /** @var VehicleCertificatesAction $action */
        $action = $this->getServiceLocator()->get(VehicleCertificatesAction::class);

        $vrm = $this->params()->fromQuery('vrm');
        $vin = $this->params()->fromQuery('vin');
        $params = $this->getRequest()->getQuery()->toArray();

        $result = $action->execute($vrm, $vin, $params);

        return $this->applyActionResult($result);
    }

    /**
     * @return \Zend\View\Model\ViewModel|array
     */
    public function nonMotVehicleSearchAction()
    {
        $this->getAuthorizationService()->assertGranted(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM);

        return $this->vehicleSearch(VehicleSearchService::SEARCH_TYPE_NON_MOT);
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

        $this->startTestChangeService->loadAllowedChangesIntoSession();

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
                        $this->vehicleSearchService->getUrlTemplate($noRegistration, $this->url()),
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
     * @param boolean $hideIncognitoVehicles true if the user is not authorised to view vehicles masked under a mystery shopper campaign
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
        VehicleSearchResultMessage $searchResultMessage = null,
        $hideIncognitoVehicles = true
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
                'hideIncognitoVehicles' => $this->hideIncognitoVehicles()
            ]
        );

        $viewModel->setTemplate('dvsa-mot-test/vehicle-search/vehicle-search.phtml');

        return $viewModel;
    }

    /**
     * @return bool
     */
    private function hideIncognitoVehicles()
    {
        if ($this->authorisationService->isGranted(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES)) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    private function selectPageSubTitle()
    {
        $isRetestSearchType = $this->vehicleSearchService->isRetestSearchType();

        if (!$isRetestSearchType) {
            $this->layout()->setVariable('pageSubTitle', 'MOT testing');
        }

        if ($this->vehicleSearchService->isTrainingSearchType()) {
            $this->layout()->setVariable('pageSubTitle', 'Training test');
        }

        if ($this->vehicleSearchService->isNonMotSearchType()) {
            $this->layout()->setVariable('pageSubTitle', 'Non-MOT test');
        }
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
