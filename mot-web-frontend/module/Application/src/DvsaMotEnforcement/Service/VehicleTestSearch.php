<?php

namespace DvsaMotEnforcement\Service;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Constants\VehicleSearchType;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\HttpRestJson\Client as RestClient;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\TesterUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\UrlBuilder\VehicleTestUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use DvsaMotEnforcement\Controller\MotTestSearchController;
use DvsaMotEnforcement\Model\MotTest as MotTestModel;

/**
 * Class VehicleTestSearch.
 */
class VehicleTestSearch
{
    const VRM_NO_RESULTS_FOUND_MSG = 'No results found for that registration';
    const VIN_NO_RESULTS_FOUND_MSG = 'No results found for that vehicle';
    const MINIMUM_LENGTH_OF_SEARCH_TERM = 2;
    const MAXIMUM_LENGTH_OF_SEARCH_TERM = 200;

    const SEARCH_TERM_NOT_SEARCH = 'not-search';

    private $searchType;
    private $searchTerm;
    private $searchTermResult;
    private $dateRange;

    private $searchTermApi;
    private $searched;
    private $resultCount;

    /** @var \DateTime $dateFrom */
    private $dateFrom;
    /** @var \DateTime $dateTo */
    private $dateTo;

    private $formErrorData;

    /** @var \Zend\Mvc\Controller\Plugin\Params */
    private $params;

    /** @var \Zend\ServiceManager\ServiceLocatorInterface */
    private $serviceLocator;
    /** @var \DvsaCommon\HttpRestJson\Client */
    private $restClient;
    /** @var \Application\Service\CatalogService */
    private $catalogService;
    /** @var \Zend\View\Renderer\PhpRenderer */
    private $viewRender;
    /** @var ParamObfuscator */
    private $paramObfuscator;

    public function __construct(
        $params,
        ParamObfuscator $paramObfuscator,
        \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator = null,
        \DvsaCommon\HttpRestJson\Client $restClient = null
    ) {
        $this->paramObfuscator = $paramObfuscator;
        $this->serviceLocator = $serviceLocator;
        $this->restClient = $restClient;

        $currentDate = DateUtils::today();

        $this->params = $params;
        $this->searchType = $this->params->fromQuery('type', 'vts');
        $this->searchTerm = $this->params->fromQuery('search', '');
        $this->searchTermResult = $this->params->fromQuery('search-result', self::SEARCH_TERM_NOT_SEARCH);
        $this->dateRange = [
            'month1' => $this->params->fromQuery('month1', $currentDate->format('m')),
            'year1' => $this->params->fromQuery('year1', $currentDate->format('Y')),
            'month2' => $this->params->fromQuery('month2', $currentDate->format('m')),
            'year2' => $this->params->fromQuery('year2', $currentDate->format('Y')),
        ];
        $this->resultCount = 0;
        $this->searched = '';
    }

    /**
     * Prepare the redirection by putting the form element in the query.
     *
     * @param string                  $routeName
     * @param MotTestSearchController $controller
     *
     * @return \Zend\Http\Response
     */
    public function prepareRouteQueryForRedirect($routeName, $controller)
    {
        return $controller->redirect()->toRoute(
            $routeName,
            [],
            [
                'query' => [
                    'type' => $this->searchType,
                    'search' => $this->searchTerm,
                    'month1' => $this->dateRange['month1'],
                    'year1' => $this->dateRange['year1'],
                    'month2' => $this->dateRange['month2'],
                    'year2' => $this->dateRange['year2'],
                ],
            ]
        );
    }

    /**
     * Return the valid search typeahead or direct input.
     *
     * @return string
     */
    public function getSearchTermValid()
    {
        if (self::SEARCH_TERM_NOT_SEARCH !== $this->searchTermResult) {
            return $this->searchTermResult;
        } else {
            return $this->searchTerm;
        }
    }

    /**
     * Get The recent Mot Test done at a specific site.
     *
     * @param MotTestSearchParamsDto $params
     *
     * @return array|null
     */
    public function getRecentMotTest(MotTestSearchParamsDto $params)
    {
        $motTestModel = new MotTestModel();

        $apiUrl = MotTestUrlBuilder::search()->toString();
        $apiResult = $this->restClient->post($apiUrl, DtoHydrator::dtoToJson($params));

        /** @var \DvsaCommon\Dto\Search\SearchResultDto $result */
        $result = DtoHydrator::jsonToDto($apiResult['data']);

        $resultData = $motTestModel->prepareDataForVehicleExaminerListRecentMotTestsView(
            $result->getData(),
            $this->getViewRender(),
            $this->getCatalogService()
        );

        foreach ($resultData as $motId => &$motTest) {
            $motTest['link'] = $this->getSummaryUrl($motId);
            $motTest['id'] = $motId;
        }

        return $resultData;
    }

    /**
     * Searches for given MOT test number, and returns information if that test exists.
     *
     * @param MotTestSearchParamsDto $params
     *
     * @return bool
     */
    public function checkIfMotTestExists(MotTestSearchParamsDto $params)
    {
        try {
            $apiResult = $this->restClient->getWithParams(
                MotTestUrlBuilder::search()->toString(),
                [
                    SearchParamConst::SEARCH_TEST_NUMBER_QUERY_PARAM => $params->getTestNumber(),
                    SearchParamConst::FORMAT => $params->getFormat(),
                ]
            );
            /** @var \DvsaCommon\Dto\Search\SearchResultDto $result */
            $result = DtoHydrator::jsonToDto($apiResult['data']);

            return $result->getResultCount() == 1;
        } catch (ValidationException $e) {
            return false;
        }
    }

    /**
     * Retrieve the correct search term from the query.
     * In case we search for a tester,
     * we verify that we have the correct tester Id.
     *
     * @param MotTestSearchController                      $controller
     * @param RestClient                                   $restClient
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return mixed|null
     */
    public function getSearchTermFromQuery($controller, $restClient, $serviceLocator)
    {
        $this->searchTermApi = null;
        $resultData = null;

        if (VehicleSearchType::SEARCH_TYPE_TESTER === $this->searchType) {
            if (self::SEARCH_TERM_NOT_SEARCH === $this->searchTermResult) {
                try {
                    $params['search'] = $this->searchTerm;
                    $params['format'] = SearchParamConst::FORMAT_TYPE_AHEAD;
                    $apiResult = $restClient->getWithParams(
                        TesterUrlBuilder::create()->testerFull()->toString(),
                        $params
                    );
                    // VM-3424 - Tester search mod to allow exact match queries
                    if (!empty($apiResult['data']['data'])) {
                        $this->searchTermApi = key($apiResult['data']['data']);
                    }
                } catch (RestApplicationException $e) {
                    $controller->addErrorMessagesFromDecorator($e->getDisplayMessages());
                }
            } else {
                $this->searchTermApi = $this->searchTermResult;
            }
        } elseif (self::SEARCH_TERM_NOT_SEARCH !== $this->searchTermResult) {
            $this->searchTermApi = $this->searchTermResult;
        } else {
            $this->searchTermApi = $this->searchTerm;
        }

        if (!empty($this->searchTermApi)) {
            $resultData = $this->getMotTestHistoryAt($controller, $restClient);
        }

        if (!empty($resultData)) {
            if (!empty($resultData['data']['searched'])) {
                $this->searched = DtoHydrator::jsonToDto($resultData['data']['searched']);
            }

            if (!empty($resultData['data']['resultCount'])) {
                $this->resultCount = $resultData['data']['resultCount'];
            }

            return $this->prepareMotDataForView($resultData, $serviceLocator);
        }

        return null;
    }

    /**
     * Get The Mot Test History done at a specific date.
     *
     * @param MotTestSearchController $controller
     * @param RestClient              $restClient
     *
     * @return mixed|null
     */
    public function getMotTestHistoryAt($controller, $restClient)
    {
        $resultData = null;
        // Fetch the vehicle testing station MOTs in question
        try {
            $apiUrl = MotTestUrlBuilder::search()->toString();

            if (VehicleSearchType::SEARCH_TYPE_TESTER === $this->searchType) {
                $params[SearchParamConst::SEARCH_TESTER_ID_QUERY_PARAM] = $this->searchTermApi;
            } else {
                $params[SearchParamConst::SEARCH_SITE_NUMBER_QUERY_PARAM] = $this->searchTermApi;
            }
            $params[SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM] = $this->dateFrom->getTimestamp();
            $params[SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM] = $this->dateTo->getTimestamp();

            $params[SearchParamConst::SEARCH_SEARCH_FILTER] = null;

            $params[SearchParamConst::FORMAT] = SearchParamConst::FORMAT_DATA_TABLES;
            $params[SearchParamConst::ROW_COUNT] = 10;
            $params[SearchParamConst::START] = 0;
            $params[SearchParamConst::SORT_COLUMN_ID] = 0;
            $params[SearchParamConst::SORT_DIRECTION] = 'desc';
            $params[SearchParamConst::PAGE_NR] = 1;

            $resultData = $restClient->getWithParams($apiUrl, $params);
        } catch (RestApplicationException $e) {
            $controller->addErrorMessagesFromDecorator($e->getDisplayMessages());
        }

        return $resultData;
    }

    /**
     * Retrieve the information related to a User.
     *
     * @param string     $searchTerm
     * @param RestClient $restClient
     *
     * @return string
     */
    public function getTesterInformation($searchTerm, $restClient)
    {
        $titleName = '';
        try {
            $apiResult = $restClient->get(TesterUrlBuilder::create()->routeParam('id', $searchTerm));
        } catch (NotFoundException $e) {
        }

        if (!empty($apiResult['data'])) {
            $titleName = $apiResult['data']['user']['username'].
                ' - '.$apiResult['data']['user']['displayName'];
        }

        return $titleName;
    }

    /**
     * Prepare the Mot Test Data to be format correctly for the view.
     *
     * @param                                              $resultData
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return array
     */
    public function prepareMotDataForView($resultData, $serviceLocator)
    {
        $data = [];

        if (!empty($resultData['data']['data'])) {
            $motTestModel = new MotTestModel();
            $viewHelperManager = $serviceLocator->get('ViewHelperManager');
            $viewRender = $serviceLocator->get('ViewRenderer');
            $escapeHtml = $viewHelperManager->get('escapeHtml');
            $catalog = $serviceLocator->get('CatalogService');
            $preparedResultData = $motTestModel->prepareDataForVehicleExaminerListRecentMotTestsView(
                $resultData['data']['data'], $viewRender, $catalog
            );
            foreach ($preparedResultData as $motTestNumber => $motTest) {
                $data[] = [
                    'display_date' => $escapeHtml($motTest['display_date']),
                    'test_date' => $motTest['test_date'],
                    'display_status' => $motTest['display_status'],
                    'status' => $motTest['status'],
                    'vin' => $escapeHtml($motTest['vin']),
                    'vrm' => $escapeHtml($motTest['registration']),
                    'link' => $this->getSummaryUrl($motTestNumber),
                    'motTestNumber' => $motTestNumber,
                    'id' => $motTestNumber,
                    'make' => $escapeHtml($motTest['make']),
                    'model' => $escapeHtml($motTest['model']),
                    'test_type' => $escapeHtml($motTest['display_test_type']),
                    'site_number' => $escapeHtml($motTest['siteNumber']),
                    'username' => $escapeHtml($motTest['testerUsername']),
                ];
            }
        }

        return $data;
    }

    /**
     * Returns the summary's url for a given test.
     *
     * @param $motTestNumber
     *
     * @return string
     */
    protected function getSummaryUrl($motTestNumber, $queryParams = [])
    {
        $testSummaryUrl = VehicleTestUrlBuilder::of()
            ->testSummary($motTestNumber)
            ->queryParams($queryParams + $this->params->fromQuery());

        return $testSummaryUrl->toString();
    }

    /**
     * Check if the date range is valid.
     *
     * @return bool
     */
    public function validateAndSetSearchDateRange()
    {
        $isValid = false;

        $dateTimeHolder = new DateTimeHolder();
        $currentDate = $dateTimeHolder->getCurrentDate();
        $month1 = str_pad($this->dateRange['month1'], 2, '0', STR_PAD_LEFT);
        $month2 = str_pad($this->dateRange['month2'], 2, '0', STR_PAD_LEFT);
        $this->dateFrom = DateUtils::toDate("{$this->dateRange['year1']}-$month1-01");
        $this->dateTo = DateUtils::toDate("{$this->dateRange['year2']}-$month2-01");

        if ($this->dateFrom <= $this->dateTo && $currentDate >= $this->dateFrom && $currentDate >= $this->dateTo) {
            $isValid = true;
        }

        return $isValid;
    }

    /**
     * Check if the Mot Test are valid to be compare.
     *
     * @param MotTestSearchController $controller
     * @param RestClient              $restClient
     * @param                         $comparedTest
     */
    public function compareMotTest($controller, RestClient $restClient, $comparedTest)
    {
        $result = null;
        if (!empty($comparedTest['motTestNumber']) && !empty($comparedTest['motTestNumberToCompare'])) {
            try {
                $params['motTestNumber'] = $comparedTest['motTestNumber'];
                $params['motTestNumberToCompare'] = $comparedTest['motTestNumberToCompare'];

                $apiUrl = (new UrlBuilder())->compareMotTest()->toString();
                $result = $restClient->getWithParams($apiUrl, $params);
            } catch (RestApplicationException $e) {
                $controller->addErrorMessagesFromDecorator($e->getDisplayMessages());
                $this->formErrorData = $e->getExpandedErrorData();
                $controller->addFormErrorMessagesToSessionFromDecorator($e->getFormErrorDisplayMessages());
            }
        }

        return $result;
    }

    /**
     * Returns true if the search term is valid.
     *
     * @return bool
     */
    public function isSearchTermValid()
    {
        $length = strlen(trim($this->getSearchTerm()));

        return $length >= self::MINIMUM_LENGTH_OF_SEARCH_TERM;
    }

    /**
     * Returns true if the search for MOT test term is valid.
     *
     * @return bool
     */
    public function isMotTestSearchTermValid()
    {
        $testNr = trim($this->getSearchTerm());
        $length = strlen($testNr);
        $doesSearchContainNonNumericCharacters = (bool) preg_match('/[^0-9]/', $testNr);

        return $length >= self::MINIMUM_LENGTH_OF_SEARCH_TERM
            && $length <= self::MAXIMUM_LENGTH_OF_SEARCH_TERM
            && !$doesSearchContainNonNumericCharacters;
    }

    /**
     * Get the Mot Test History from a VRM or a Vin string.
     *
     * @param MotTestSearchController                      $controller
     * @param RestClient                                   $restClient
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return array|null
     */
    public function getMotTestByVrmOrVin($controller, RestClient $restClient, $serviceLocator)
    {
        $resultData = null;
        try {
            $apiUrl = MotTestUrlBuilder::search()->toString();

            $result = $restClient->getWithParams(
                $apiUrl,
                (VehicleSearchType::SEARCH_TYPE_VRM === $this->searchType
                    ? [
                        'vrm' => $this->searchTerm,
                        'format' => SearchParamConst::FORMAT_DATA_TABLES,
                        'sortDirection' => SearchParamConst::SORT_DIRECTION_DESC,
                        'rowCount' => 100,
                    ]
                    : [
                        'vin' => $this->searchTerm,
                        'format' => SearchParamConst::FORMAT_DATA_TABLES,
                        'sortDirection' => SearchParamConst::SORT_DIRECTION_DESC,
                        'rowCount' => 100,
                    ]
                )
            );

            $resultData = $result['data'];
            // Modify the raw data prior to presentation
            $motTestModel = new MotTestModel();
            $viewRender = $serviceLocator->get('ViewRenderer');
            $catalog = $serviceLocator->get('CatalogService');

            if ($resultData['totalResultCount'] > 0) {
                $resultData = $motTestModel->prepareDataForVehicleExaminerListRecentMotTestsView(
                    $resultData['data'], $viewRender, $catalog
                );
                foreach ($resultData as $motTestNumber => &$motTest) {
                    $motTest['link'] = $this->getSummaryUrl($motTestNumber);
                    $motTest['id'] = $motTestNumber;
                }
            } else {
                $resultData = null;
                $controller->addErrorMessagesFromDecorator(
                    (VehicleSearchType::SEARCH_TYPE_VRM === $this->searchType
                        ? self::VRM_NO_RESULTS_FOUND_MSG
                        : self::VIN_NO_RESULTS_FOUND_MSG)
                );
            }
        } catch (RestApplicationException $e) {
            $controller->addErrorMessagesFromDecorator($e->getDisplayMessages());
        }

        return $resultData;
    }

    public function getMotTestByVehicleId(MotTestSearchParamsDto $params)
    {
        $motTestModel = new MotTestModel();
        $apiUrl = MotTestUrlBuilder::search()->toString();
        $apiResult = $this->restClient->post($apiUrl, DtoHydrator::dtoToJson($params));

        /** @var \DvsaCommon\Dto\Search\SearchResultDto $result */
        $result = DtoHydrator::jsonToDto($apiResult['data']);

        $resultData = $motTestModel->prepareDataForVehicleExaminerListRecentMotTestsView(
            $result->getData(),
            $this->getViewRender(),
            $this->getCatalogService()
        );

        foreach ($resultData as $motId => &$motTest) {
            $motTest['link'] = $this->getSummaryUrl(
                $motId,
                [
                    'vehicleId' => $this->paramObfuscator->obfuscateEntry(
                        ParamObfuscator::ENTRY_VEHICLE_ID, $params->getVehicleId()
                    ),
                ]
            );
            $motTest['id'] = $motId;
        }

        return $resultData;
    }

    /**
     * @return array
     */
    public function getDateRange()
    {
        return $this->dateRange;
    }

    /**
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * @return string
     */
    public function getSearchType()
    {
        return $this->searchType;
    }

    /**
     * @return \Zend\Mvc\Controller\Plugin\Params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @return int
     */
    public function getResultCount()
    {
        return $this->resultCount;
    }

    /**
     * @return string
     */
    public function getSearchTermApi()
    {
        return $this->searchTermApi;
    }

    /**
     * @return string
     */
    public function getSearched()
    {
        return $this->searched;
    }

    /**
     * @return mixed
     */
    public function getFormErrorData()
    {
        return $this->formErrorData;
    }

    /**
     * @param $searchType
     */
    public function setSearchType($searchType)
    {
        $this->searchType = $searchType;
    }

    /**
     * @return \Application\Service\CatalogService
     */
    public function getCatalogService()
    {
        if ($this->catalogService === null) {
            $this->catalogService = $this->serviceLocator->get('CatalogService');
        }

        return $this->catalogService;
    }

    /**
     * @return \Zend\View\Renderer\PhpRenderer
     */
    public function getViewRender()
    {
        if ($this->viewRender === null) {
            $this->viewRender = $this->serviceLocator->get('ViewRenderer');
        }

        return $this->viewRender;
    }
}
