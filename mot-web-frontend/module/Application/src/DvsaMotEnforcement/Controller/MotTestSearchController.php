<?php

namespace DvsaMotEnforcement\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Constants\VehicleSearchType;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\VehicleUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use DvsaMotEnforcement\Service\VehicleTestSearch;
use Vehicle\Controller\VehicleController;
use Zend\Session\Container;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Model\ViewModel;

/**
 * Class MotTestSearchController.
 *
 * This class is responsible for coordinating the different search
 * methods to user so they can quickly locate an existing VTS station,
 * vehicle or other entity.
 */
class MotTestSearchController extends AbstractAuthActionController
{
    const SEARCH_TITLE_BY_VTS        = 'Recent MOT(s) found for site "%s"';
    const SEARCH_TITLE_BY_DATE_RANGE = 'MOT tests found for %s';
    const SEARCH_TITLE_BY_VIN        = 'MOT(s) found for VIN/Chassis "%s"';
    const SEARCH_TITLE_BY_VRM        = 'MOT(s) found with registration mark "%s"';
    const INVALID_SEARCH_TERM_MSG    = 'Please enter a valid search';

    const SITE_NUMBER_NOT_FOUND_MSG = 'No results found for that site';
    const TEST_NOT_FOUND_MSG        = 'No results found for that site';
    const TESTER_NOT_FOUND_MSG      = 'No results found for that tester';
    const DATE_FORMAT_ERROR         = 'Date Range format invalid';
    const MOT_TEST_NOT_FOUND        = 'No results found for that test number';

    const SEARCH_TYPE_TEST_NUMBER = 'testNumber';

    const NO_RESULT_FOUND_FOR_VEHICLE = 'This vehicle has no test history.';

    /** @var \DvsaCommon\Obfuscate\ParamObfuscator */
    protected $paramObfuscator;

    /**
     * @param \DvsaCommon\Obfuscate\ParamObfuscator $paramObfuscator
     */
    public function __construct(ParamObfuscator $paramObfuscator = null)
    {
        $this->paramObfuscator = $paramObfuscator;
    }

    public function motTestSearchAction()
    {
        $this->layout('layout/layout_enforcement');
        $this->assertGranted(PermissionInSystem::DVSA_SITE_SEARCH);

        $vehicleTestSearchService = new VehicleTestSearch($this->params(), $this->paramObfuscator);

        return new ViewModel(
            [
                'searchType' => $vehicleTestSearchService->getSearchType(),
                'searchTerm' => $vehicleTestSearchService->getSearchTerm(),
                'dateRange'  => $vehicleTestSearchService->getDateRange(),
            ]
        );
    }

    /**
     * This handles a search request by VTS number. The VTS search term is *assumed*
     * * to be a fully qualifed term but may nit be if the user pressed RETURN before
     * auto-completion happened so we should still deal with no returned rows.
     *
     * Once the data is available (ot otherwise) then the relevant results page can
     * be selected.
     *
     * @return ViewModel
     */
    public function motTestSearchByVtsAction()
    {
        $this->layout('layout/layout_enforcement');
        $this->assertGranted(PermissionInSystem::DVSA_SITE_SEARCH);

        $vehicleTestSearchService = new VehicleTestSearch(
            $this->params(),
            $this->paramObfuscator,
            $this->getServiceLocator(),
            $this->getRestClient()
        );

        if (!$vehicleTestSearchService->isSearchTermValid()) {
            $this->addErrorMessages(self::INVALID_SEARCH_TERM_MSG);

            return $vehicleTestSearchService->prepareRouteQueryForRedirect('mot-test-search', $this);
        }

        $siteNr = $vehicleTestSearchService->getSearchTermValid();

        $searchParamsDto = new MotTestSearchParamsDto();
        $searchParamsDto
            ->setSiteNr($siteNr)
            ->setFormat(SearchParamConst::FORMAT_DATA_TABLES)
            ->setSortDirection(SearchParamConst::SORT_DIRECTION_DESC)
            ->setRowsCount(25000)
            ->setIsSearchRecent(true);

        try {
            $resultData = $vehicleTestSearchService->getRecentMotTest($searchParamsDto);
        } catch (RestApplicationException $e) {
            $resultData = null;
        }

        if (empty($resultData)) {
            $this->addErrorMessages(self::SITE_NUMBER_NOT_FOUND_MSG);

            return $vehicleTestSearchService->prepareRouteQueryForRedirect('mot-test-search', $this);
        }

        return new ViewModel(
            [
                'searchTitle'   => sprintf(self::SEARCH_TITLE_BY_VTS, strtoupper(trim($siteNr))),
                'searchResults' => $resultData,
                'searchTerm'    => $siteNr,
                'searchType'    => $vehicleTestSearchService->getSearchType(),
                'siteNumber'    => $siteNr,
            ]
        );
    }

    /**
     * This handles a search request by VTS number/Ttester Id. The VTS search/Tester Id term is *assumed*
     * * to be a fully qualifed term but may nit be if the user pressed RETURN before
     * auto-completion happened so we should still deal with no returned rows.
     *
     * Once the data is available (ot otherwise) then the relevant results page can
     * be selected.
     *
     * @return ViewModel
     */
    public function motTestSearchByDateRangeAction()
    {
        $this->layout('layout/layout_enforcement');
        $this->assertGranted(PermissionInSystem::DVSA_SITE_SEARCH);

        $vehicleTestSearchService = new VehicleTestSearch($this->params(), $this->paramObfuscator);

        if (!$vehicleTestSearchService->isSearchTermValid()) {
            $this->addErrorMessages(self::INVALID_SEARCH_TERM_MSG);

            return $vehicleTestSearchService->prepareRouteQueryForRedirect('mot-test-search', $this);
        } else {
            if (!$vehicleTestSearchService->validateAndSetSearchDateRange()) {
                $this->addErrorMessages(self::DATE_FORMAT_ERROR);

                return $vehicleTestSearchService->prepareRouteQueryForRedirect('mot-test-search', $this);
            } else {
                $resultData = $vehicleTestSearchService->getSearchTermFromQuery(
                    $this,
                    $this->getRestClient(),
                    $this->getServiceLocator()
                );
            }
        }
        if (empty($resultData)) {
            $this->addErrorMessages(
                ($vehicleTestSearchService->getSearchType() === VehicleSearchType::SEARCH_TYPE_TESTER
                    ? self::TESTER_NOT_FOUND_MSG
                    : self::SITE_NUMBER_NOT_FOUND_MSG)
            );

            return $vehicleTestSearchService->prepareRouteQueryForRedirect('mot-test-search', $this);
        }
        if ($vehicleTestSearchService->getSearchType() === VehicleSearchType::SEARCH_TYPE_TESTER) {
            $searchTypeName = 'Tester "'
                . trim($vehicleTestSearchService->getTesterInformation(
                    $vehicleTestSearchService->getSearchTermApi(),
                    $this->getRestClient()
                )) . '"';
        } else {
            $searchTypeName = 'Site "' . strtoupper(trim($vehicleTestSearchService->getSearchTermApi())) . '"';
        }

        $view = new ViewModel(
            [
                'searchTitle'   => sprintf(self::SEARCH_TITLE_BY_DATE_RANGE, $searchTypeName),
                'userDetails'   => $this->getUserDisplayDetails(),
                'searchResults' => $resultData,
                'searchTerm'    => $vehicleTestSearchService->getSearchTermApi(),
                'searchType'    => $vehicleTestSearchService->getSearchType(),
                'siteNumber'    => $vehicleTestSearchService->getSearchTermApi(),
                'dateFrom'      => $vehicleTestSearchService->getDateFrom(),
                'dateTo'        => $vehicleTestSearchService->getDateTo(),
                'searched'      => $vehicleTestSearchService->getSearched(),
                'resultCount'   => $vehicleTestSearchService->getResultCount(),
                'summaryParams' => http_build_query($vehicleTestSearchService->getParams()->fromQuery()),
            ]
        );
        $view->setTemplate('dvsa-mot-enforcement/mot-test-search/mot-test-search-by-date-range.phtml');

        return $view;
    }

    /**
     * This will find MOT tests given a complete Vehicle Registration Mark string Or a Vin/Chassis string.
     *
     * @return ViewModel
     */
    public function motTestSearchByVrmOrVinAction()
    {
        $this->layout('layout/layout_enforcement');
        $this->assertGranted(PermissionInSystem::DVSA_SITE_SEARCH);

        $comparedTest             = null;
        $vehicleTestSearchService = new VehicleTestSearch($this->params(), $this->paramObfuscator);

        if (!$vehicleTestSearchService->isSearchTermValid()) {
            $this->addErrorMessages(self::INVALID_SEARCH_TERM_MSG);

            return $vehicleTestSearchService->prepareRouteQueryForRedirect('mot-test-search', $this);
        }

        if ($vehicleTestSearchService->getSearchType() == 'vts') {
            $vehicleTestSearchService->setSearchType(VehicleSearchType::SEARCH_TYPE_VRM);
        }

        $refSession      = new Container('referralSession');
        $refSession->url = $this->params()->fromQuery();

        // Check Comparison
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $comparedTest = $request->getPost();
            $result       = $vehicleTestSearchService->compareMotTest($this, $this->getRestClient(), $comparedTest);
            if (isset($result['data'])) {
                return $this->redirect()->toRoute(
                    'enforcement-compare-tests',
                    [
                        'motTestNumber'          => $request->getPost()['motTestNumber'],
                        'motTestNumberToCompare' => $request->getPost()['motTestNumberToCompare'],
                    ]
                );
            }
        }

        $resultData = $vehicleTestSearchService->getMotTestByVrmOrVin(
            $this,
            $this->getRestClient(),
            $this->getServiceLocator()
        );

        if (empty($resultData)) {
            return $vehicleTestSearchService->prepareRouteQueryForRedirect('mot-test-search', $this);
        }

        $view = new ViewModel(
            [
                'pageTitle'     => 'MOT Test History',
                'searchTitle'   => sprintf(
                    ($vehicleTestSearchService->getSearchType() === VehicleSearchType::SEARCH_TYPE_VRM
                        ? self::SEARCH_TITLE_BY_VRM
                        : self::SEARCH_TITLE_BY_VIN), strtoupper(trim($vehicleTestSearchService->getSearchTerm()))
                ),
                'searchResults' => $resultData,
                'searchTerm'    => $vehicleTestSearchService->getSearchTerm(),
                'searchType'    => $vehicleTestSearchService->getSearchType(),
                'searchData'    => http_build_query(['type' => $vehicleTestSearchService->getSearchType()]),
                'formErrors'    => $vehicleTestSearchService->getFormErrorData(),
                'comparedTest'  => $comparedTest,
                'testTypes'     => $this->getCatalogService()->getMotTestTypeDescriptions(),
            ]
        );

        $view->setTemplate('dvsa-mot-enforcement/mot-test-search/mot-test-search-by-vin-or-vrm.phtml');

        return $view;
    }

    /**
     * This will find MOT tests given a complete Vehicle Registration Mark string Or a Vin/Chassis string.
     *
     * @return ViewModel
     */
    public function motTestSearchByMotTestNumberAction()
    {
        $this->layout('layout/layout_enforcement');
        $this->assertGranted(PermissionInSystem::DVSA_SITE_SEARCH);

        $vehicleTestSearchService = new VehicleTestSearch(
            $this->params(),
            $this->paramObfuscator,
            $this->getServiceLocator(),
            $this->getRestClient()
        );

        if (!$vehicleTestSearchService->isSearchTermValid()) {
            $this->addErrorMessages(self::INVALID_SEARCH_TERM_MSG);

            return $vehicleTestSearchService->prepareRouteQueryForRedirect('mot-test-search', $this);
        }

        if (!$vehicleTestSearchService->isMotTestSearchTermValid()) {
            $this->addErrorMessages(self::MOT_TEST_NOT_FOUND);

            return $vehicleTestSearchService->prepareRouteQueryForRedirect('mot-test-search', $this);
        }

        $testNumber = $vehicleTestSearchService->getSearchTermValid();

        $searchParamsDto = new MotTestSearchParamsDto();
        $searchParamsDto
            ->setTestNumber($testNumber)
            ->setFormat(SearchParamConst::FORMAT_DATA_TABLES);

        if ($vehicleTestSearchService->checkIfMotTestExists($searchParamsDto)) {
            return $this->redirect()->toRoute(
                'enforcement-view-mot-test',
                ['motTestNumber' => trim($searchParamsDto->getTestNumber())],
                ['query' => [
                    'type' => static::SEARCH_TYPE_TEST_NUMBER,
                ]]
            );
        } else {
            $this->addErrorMessages(self::MOT_TEST_NOT_FOUND);
            return $vehicleTestSearchService->prepareRouteQueryForRedirect('mot-test-search', $this);
        }

    }

    /**
     * This will find MOT tests given a vehicle id.
     *
     * @return ViewModel
     */
    public function motTestSearchByVehicleAction()
    {
        $this->assertGranted(PermissionInSystem::FULL_VEHICLE_MOT_TEST_HISTORY_VIEW);

        $obfuscatedVehicleId = (string) $this->params()->fromRoute('id', null);
        $vehicleId = $this->paramObfuscator->deobfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $obfuscatedVehicleId);

        $searchParams = $this->getRequest()->getQuery();

        $searchParamsDto = new MotTestSearchParamsDto();
        $searchParamsDto
            ->setVehicleId($vehicleId)
            ->setFormat(SearchParamConst::FORMAT_DATA_TABLES)
            ->setSortBy('0')    //  sort by testDate
            ->setSortDirection(SearchParamConst::SORT_DIRECTION_DESC)
            ->setRowsCount(500);

        $resultData = null;
        try {
            $vehicleTestSearchService = new VehicleTestSearch(
                $this->params(),
                $this->paramObfuscator,
                $this->getServiceLocator(),
                $this->getRestClient()
            );

            $resultData = $vehicleTestSearchService->getMotTestByVehicleId($searchParamsDto);
        } catch (RestApplicationException $e) {
            $resultData = null;
        }

        if (empty($resultData)) {
            $this->addErrorMessages(self::NO_RESULT_FOUND_FOR_VEHICLE);

            return $this->getRedirectVehicle($obfuscatedVehicleId, $searchParams);
        }

        $view = new ViewModel(
            [
                'pageTitle'     => 'Vehicle MOT test history',
                'searchResults' => $resultData,
                'escGoBackLink' => $this->getGoBackLink($obfuscatedVehicleId, $searchParams),
            ]
        );
        $view->setTemplate('dvsa-mot-enforcement/mot-test-search/mot-test-search-by-vehicle.phtml');

        return $view;
    }

    /**
     * This will construct the link to go back to the previous page.
     *
     * @param string                   $obfuscatedVehicleId
     * @param ParametersInterface|null $searchData
     *
     * @return string
     */
    public function getGoBackLink($obfuscatedVehicleId, ParametersInterface $searchData = null)
    {
        $backTo = ArrayUtils::tryGet($searchData, 'backTo');
        if ($backTo) {
            unset($searchData['backTo']);
        }

        if (($searchData instanceof ParametersInterface) && $searchData->get("oneResult")) {
            $searchData->set("backTo", VehicleController::BACK_TO_SEARCH);
        }
        $searchData = ($searchData && $searchData->count() ? '?' . http_build_query($searchData) : '');

        switch ($backTo) {
            case VehicleController::BACK_TO_DETAIL:
                return VehicleUrlBuilderWeb::vehicle($obfuscatedVehicleId) . $searchData;
            case VehicleController::BACK_TO_RESULT:
                return VehicleUrlBuilderWeb::searchResult() . $searchData;
        }

        return null;
    }

    /**
     * This will construct the link to redirect to the previous page.
     *
     * @param $obfuscatedVehicleId
     * @param ParametersInterface $searchData
     *
     * @return string
     */
    public function getRedirectVehicle($obfuscatedVehicleId, ParametersInterface $searchData = null)
    {
        $backTo = ArrayUtils::tryGet($searchData, 'backTo');
        if ($backTo) {
            unset($searchData['backTo']);
        }

        switch ($backTo) {
            case (VehicleController::BACK_TO_DETAIL && $searchData->get("oneResult")):
                return $this->redirect()->toUrl(
                    VehicleUrlBuilderWeb::vehicle($obfuscatedVehicleId)
                        ->queryParams(
                            [
                                'backTo' => VehicleController::BACK_TO_SEARCH,
                                'type'   => $searchData['type'],
                                'search' => $searchData['search'],
                            ]
                        )
                );

            case VehicleController::BACK_TO_DETAIL:
                return $this->redirect()->toUrl(
                    VehicleUrlBuilderWeb::vehicle($obfuscatedVehicleId)
                        ->queryParams(
                            [
                                'type'   => $searchData['type'],
                                'search' => $searchData['search'],
                            ]
                        )
                );

            case VehicleController::BACK_TO_RESULT:
                return $this->redirect()->toUrl(
                    VehicleUrlBuilderWeb::searchResult()->queryParams(
                        [
                            'type'   => $searchData['type'],
                            'search' => $searchData['search'],
                        ]
                    )
                );
            case VehicleController::BACK_TO_SEARCH:
                return $this->redirect()->toUrl(
                    VehicleUrlBuilderWeb::searchResult()->queryParams(
                        [
                            'type'   => $searchData['type'],
                            'search' => $searchData['search'],
                            'backTo' => VehicleController::BACK_TO_SEARCH
                        ]
                    )
                );
        }

        return null;
    }

    /**
     * Callback function to add an error from the decorator.
     *
     * @param $errors
     */
    public function addErrorMessagesFromDecorator($errors)
    {
        $this->addErrorMessages($errors);
    }

    /**
     * Callback function to add an error to the session from the decorator.
     *
     * @param $errors
     */
    public function addFormErrorMessagesToSessionFromDecorator($errors)
    {
        $this->addFormErrorMessagesToSession($errors);
    }
}
