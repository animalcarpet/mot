<?php

namespace Organisation\Controller;

use Core\Controller\AbstractAuthActionController;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Organisation\MotTestLogSummaryDto;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use Organisation\ViewModel\MotTestLog\MotTestLogFormViewModel;
use Organisation\ViewModel\MotTestLog\MotTestLogViewModel;
use Zend\Http\Headers;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

/**
 * Class MotTestLogController
 *
 * @package Organisation\Controller
 */
class MotTestLogController extends AbstractAuthActionController
{
    const TABLE_ROWS_COUNT = 10;
    const PER_PAGE_COUNT = 10000;
    const DEF_SORT_BY = 'testDateTime';
    const DEF_SORT_DIRECTION = SearchParamConst::SORT_DIRECTION_DESC;
    const DATETIME_FORMAT = 'd/m/Y H:i:s';
    const DATETIME_FORMAT_EMERG = 'd/m/Y';
    const ERR_NO_DATA = 'There are no test logs for the selected date range. Please select a wider date range.';

    const ROUTE_INDEX = 'authorised-examiner/mot-test-log';

    public static $CSV_COLUMNS = [
        'Site Number',
        'Client IP',
        'Test date/time',
        'Test Number',
        'Registration',
        'VIN',
        'Make',
        'Model',
        'Class',
        'User Id',
        'Test type',
        'Result',
        'Test Duration',
        'Tester who recorded test',
        'Date/time of recording CT test',
        'Contingency Test Reason',
        'Contingency Code',
    ];

    protected $csvHandle;

    /** @var MotFrontendAuthorisationServiceInterface $authService */
    private $authService;

    /** @var \Zend\Http\Request $request */
    protected $request;

    /**
     * @param MotFrontendAuthorisationServiceInterface $authService
     * @param MapperFactory $mapperFactory
     */
    public function __construct(
        MotFrontendAuthorisationServiceInterface $authService,
        MapperFactory $mapperFactory
    ) {
        $this->authService = $authService;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $organisationId = $this->params()->fromRoute('id');

        if (!$this->authService->isGrantedAtOrganisation(PermissionAtOrganisation::AE_TEST_LOG, $organisationId)) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
        }

        $this->request = $this->getRequest();

        //  logical block :: get auth examiner and summary data
        $organisation = $this->getAuthorisedExaminer($organisationId);
        $motTestLogs = $this->getLogSummary($organisationId);

        //  logical block :: prepare models for view
        $viewModel = new MotTestLogViewModel($organisation, $motTestLogs);
        $viewModel->parseData($this->request->getQuery());

        $formModel = $viewModel->getFormModel();
        if ($formModel->isValid()) {
            //  logical block :: create object with parameters for sending to api   --
            $searchParams = $this->prepareSearchParams($formModel);
            if ($searchParams->getRowsCount() === 0) {
                $searchParams->setRowsCount($viewModel->getTable()->getTableOptions()->getItemsPerPage());
            }

            //  logical block :: request total count of records(mot tests in log)
            $apiResult = $this->getLogDataBySearchCriteria($organisationId, $searchParams);

            $totalRecordsCount = (int) $apiResult->getTotalResultCount();
            if ($totalRecordsCount === 0) {
                $this->addErrorMessages(self::ERR_NO_DATA);
            }

            //  logical block :: set search parameters and date to table
            $viewModel->getTable()
                ->setSearchParams($apiResult->getSearched())
                ->setRowsTotalCount($apiResult->getTotalResultCount())
                ->setData($apiResult->getData());

            //  logical block :: set search parameters to date range
            $viewModel->getFilterBuilder()
                ->setQueryParams($apiResult->getSearched()->toQueryParams());
        }

        //  logic block: prepare view
        $this->layout('layout/layout-govuk.phtml');

        $breadcrumbs = [
            $organisation->getName() => AuthorisedExaminerUrlBuilderWeb::of($organisationId),
            'Test logs' => '',
        ];

        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->layout()->setVariable('pageTitle', $viewModel->getOrganisation()->getName());
        $this->layout()->setVariable('pageSubTitle', 'Test logs of Authorised Examiner');

        return new ViewModel(['viewModel' => $viewModel]);
    }

    /**
     * Generate a CSV containing test log entries and stream it to the browser
     * @return void|Response
     */
    public function downloadCsvAction()
    {
        $organisationId = $this->params()->fromRoute('id');

        if (!$this->authService->isGrantedAtOrganisation(PermissionAtOrganisation::AE_TEST_LOG, $organisationId)) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
        }

        $searchParams = $this->prepareSearchParams();
        $searchParams
            ->setFormat(SearchParamConst::FORMAT_DATA_CSV)
            ->setRowsCount(self::PER_PAGE_COUNT)
            ->setIsApiGetTotalCount(true)
            ->setIsApiGetData(false);

        $apiResult = $this->getLogDataBySearchCriteria($organisationId, $searchParams);

        // Determine the number of pages to retrieve based on total results and per page count
        $lastPageNumber = ceil($apiResult->getTotalResultCount() / self::PER_PAGE_COUNT);

        // Now we want to fetch the data from the API, and not the total count
        $searchParams->setIsApiGetTotalCount(false)->setIsApiGetData(true);

        $fileName = 'test-log-' .
            (new \DateTime('@' . $searchParams->getDateFromTs()))->format('dmY') . '-' .
            (new \DateTime('@' . $searchParams->getDateToTs()))->format('dmY') . '.csv';

        // Prepare the headers and send them
        $headers = (new Headers)->addHeaders([
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
        ]);

        $this->response = new Response;
        $this->response->setHeaders($headers);
        $this->response->sendHeaders();

        // Open a file handle for writing to php://output
        $this->csvHandle = fopen('php://output', 'w');

        // Output the CSV column headings
        if (!empty(self::$CSV_COLUMNS)) {
            fputcsv($this->csvHandle, self::$CSV_COLUMNS);
            flush();
        }

        // Grab each page of results and output them
        for ($i = 1; $i < $lastPageNumber + 1; $i++) {
            $searchParams->setPageNr($i);
            $apiResult = $this->getLogDataBySearchCriteria($organisationId, $searchParams);
            $this->prepareCsvBody($apiResult->getData());
            flush();
            set_time_limit(30);
        }

        fclose($this->csvHandle);

        return $this->response;
    }

    /**
     * Open a handle on php://output, iterate the rows and write each to the CSV
     * @param array $rows
     * @return void
     */
    protected function prepareCsvBody(array $rows)
    {
        foreach ($rows as $row) {
            //  --  date time format for emergency test --
            $testDateFormat = (empty($row['emCode']) ? self::DATETIME_FORMAT : self::DATETIME_FORMAT_EMERG);

            //  --  convert to local time zone  --
            $row['testDateTime'] = DateUtils::toUserTz(new \DateTime($row['testDateTime']))->format($testDateFormat);

            $emRecDateTime = $row['emRecDateTime'];
            if ($emRecDateTime !== null) {
                $row['emRecDateTime'] = DateUtils::toUserTz(new \DateTime($emRecDateTime))
                    ->format(self::DATETIME_FORMAT);
            }

            // Only print primary IP address
            if (isset($row['clientIp'])) {
                $ips = explode(", ", $row['clientIp']);
                $row['clientIp'] = $ips[0];
            }

            //="formula" hack preventing Excel from converting columns to Date or Number
            $row['vehicleModel'] = '="' . $row['vehicleModel'] . '"';
            $row['testNumber'] = '="' . $row['testNumber'] . '"';

            // VIN must use ="<vin>" to prevent Excel truncating numeric VIN longer than 15 digits
            $row['vehicleVIN'] = '="' . $row['vehicleVIN'] . '"';

            fputcsv($this->csvHandle, $row);
        }
    }

    /**
     * @return SearchResultDto|null
     */
    private function getLogDataBySearchCriteria($orgId, MotTestSearchParamsDto $searchParams)
    {
        try {
            return $this->mapperFactory->MotTestLog->getData($orgId, $searchParams);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * @return \DvsaCommon\Dto\Organisation\OrganisationDto|null
     */
    protected function getAuthorisedExaminer($organisationId)
    {
        try {
            return $this->mapperFactory->Organisation->getAuthorisedExaminer($organisationId);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * Get mot tests log summary information from api (year, prev month, prev week, today)
     *
     * @param int $organisationId
     *
     * @return MotTestLogSummaryDto|null
     */
    protected function getLogSummary($organisationId)
    {
        try {
            return $this->mapperFactory->MotTestLog->getSummary($organisationId);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * @param MotTestLogFormViewModel $formModel
     * @return MotTestSearchParamsDto
     */
    protected function prepareSearchParams(MotTestLogFormViewModel $formModel = null)
    {
        $queryParams = $this->request->getQuery();

        $optionalMotTestTypes = [MotTestTypeCode::MYSTERY_SHOPPER];

        $dto = new MotTestSearchParamsDto();
        $dto
            ->setFormat(SearchParamConst::FORMAT_DATA_TABLES)
            ->setStatus(
                [
                    MotTestStatusName::ABANDONED,
                    MotTestStatusName::ABORTED,
                    MotTestStatusName::ABORTED_VE,
                    MotTestStatusName::FAILED,
                    MotTestStatusName::PASSED,
                    MotTestStatusName::REFUSED,
                ]
            )
            ->setTestType(array_merge(
                [
                    MotTestTypeCode::NORMAL_TEST,
                    MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS,
                    MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS,
                    MotTestTypeCode::RE_TEST,
                ],
                $optionalMotTestTypes)
            )
            ->setRowsCount($queryParams->get(SearchParamConst::ROW_COUNT, 0))
            ->setPageNr($queryParams->get(SearchParamConst::PAGE_NR, 1))
            ->setSortBy($queryParams->get(SearchParamConst::SORT_BY, self::DEF_SORT_BY))
            ->setSortDirection($queryParams->get(SearchParamConst::SORT_DIRECTION, self::DEF_SORT_DIRECTION));

        //  logical block: set filter parameters   --
        if ($formModel !== null) {
            $dateFrom = $formModel->getDateFrom()->getDate()->getTimestamp();
            $dateTo = $formModel->getDateTo()->getDate()->getTimestamp();
        } else {
            $dateFrom = $queryParams->get(SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM);
            $dateTo = $queryParams->get(SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM);
        }

        $dto->setDateFromTs($dateFrom)
            ->setDateToTs($dateTo);

        return $dto;
    }
}
