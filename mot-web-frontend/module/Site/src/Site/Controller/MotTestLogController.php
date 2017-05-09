<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Site\Controller;

use Core\Controller\AbstractAuthActionController;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use DateTime;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\Dto\Site\MotTestLogSummaryDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilderWeb;
use Site\ViewModel\MotTestLog\MotTestLogFormViewModel;
use Site\ViewModel\MotTestLog\MotTestLogViewModel;
use Zend\View\Model\ViewModel;

/**
 * MotTestLog Controller.
 */
class MotTestLogController extends AbstractAuthActionController
{
    const DATETIME_FORMAT = 'd/m/Y H:i:s';
    const DATETIME_FORMAT_EMERG = 'd/m/Y';
    const DEF_SORT_BY = 'testDateTime';
    const DEF_SORT_DIRECTION = SearchParamConst::SORT_DIRECTION_DESC;
    const ERR_NO_DATA = 'There are no test logs for the selected date range. Please select a wider date range.';
    const MAX_TESTS_COUNT = 50000;
    const TABLE_ROWS_COUNT = 10;

    /** @var array */
    public static $CSV_COLUMNS
        = [
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

    /** @var \Zend\Http\Request $request */
    protected $request;

    /** @var MotFrontendAuthorisationServiceInterface $authService */
    private $authService;

    /**
     * @param MotFrontendAuthorisationServiceInterface $authService
     * @param MapperFactory                            $mapperFactory
     */
    public function __construct(
        MotFrontendAuthorisationServiceInterface $authService,
        MapperFactory $mapperFactory
    ) {
        $this->authService = $authService;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $siteId = $this->params()->fromRoute('id');

        if (!$this->authService->isGrantedAtSite(PermissionAtSite::VTS_TEST_LOGS, $siteId)) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
        }

        $this->request = $this->getRequest();

        $site = $this->getSite($siteId);
        $motTestLogs = $this->getLogSummary($siteId);

        $viewModel = new MotTestLogViewModel($site, $motTestLogs);
        $viewModel->parseData($this->request->getQuery());

        $formModel = $viewModel->getFormModel();
        if ($formModel->isValid()) {
            $searchParams = $this->prepareSearchParams($formModel);
            if ($searchParams->getRowsCount() === 0) {
                $searchParams->setRowsCount($viewModel->getTable()->getTableOptions()->getItemsPerPage());
            }

            $apiResult = $this->getLogDataBySearchCriteria($siteId, $searchParams);

            $totalRecordsCount = (int) $apiResult->getTotalResultCount();
            if ($totalRecordsCount === 0) {
                $this->addErrorMessages(self::ERR_NO_DATA);
            }

            $viewModel
                ->getTable()
                ->setSearchParams($apiResult->getSearched())
                ->setRowsTotalCount($apiResult->getTotalResultCount())
                ->setData($apiResult->getData());

            $viewModel
                ->getFilterBuilder()
                ->setQueryParams($apiResult->getSearched()->toQueryParams());
        }

        $this->layout('layout/layout-govuk.phtml');

        $breadcrumbs = [
            $site->getName() => VehicleTestingStationUrlBuilderWeb::byId($siteId),
            'Test logs' => '',
        ];

        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($site, $breadcrumbs);

        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->layout()->setVariable('pageTitle', $viewModel->getSite()->getName());
        $this->layout()->setVariable('pageSubTitle', 'Test logs of Vehicle Testing Station');

        return new ViewModel(
            [
                'viewModel' => $viewModel,
            ]
        );
    }

    /**
     * @return \Zend\Http\Response
     */
    public function downloadCsvAction()
    {
        $siteId = $this->params()->fromRoute('id');

        if (!$this->authService->isGrantedAtSite(PermissionAtSite::VTS_TEST_LOGS, $siteId)) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
        }

        $searchParams = $this->prepareSearchParams();
        $searchParams
            ->setFormat(SearchParamConst::FORMAT_DATA_CSV)
            ->setRowsCount(self::MAX_TESTS_COUNT)
            ->setIsApiGetTotalCount(false)
            ->setIsApiGetData(true);

        $apiResult = $this->getLogDataBySearchCriteria($siteId, $searchParams);

        $csvBody = ($apiResult->getResultCount() > 0) ? $this->prepareCsvBody($apiResult->getData()) : '';

        $fileName = 'test-log-'.
            (new DateTime('@'.$searchParams->getDateFromTs()))->format('dmY').'-'.
            (new DateTime('@'.$searchParams->getDateToTs()))->format('dmY').'.csv';

        /** @var \Zend\Http\Response $response */
        $response = $this->getResponse();

        $headers = $response->getHeaders();
        $headers
            ->clearHeaders()
            ->addHeaderLine('Content-Type', 'text/csv; charset=utf-8')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="'.$fileName.'"')
            ->addHeaderLine('Accept-Ranges', 'bytes')
            ->addHeaderLine('Content-Length', strlen($csvBody))
            ->addHeaderLine('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->addHeaderLine('Pragma', 'no-cache');

        $response->setContent($csvBody);

        return $response;
    }

    /**
     * @param $testsData
     *
     * @return string
     */
    protected function prepareCsvBody($testsData)
    {
        $csvBuffer = fopen('php://memory', 'w');

        if (!empty(self::$CSV_COLUMNS)) {
            fputcsv($csvBuffer, self::$CSV_COLUMNS);
        }

        foreach ($testsData as $row) {
            $testDateFormat = (empty($row['emCode']) ? self::DATETIME_FORMAT : self::DATETIME_FORMAT_EMERG);

            $row['testDateTime'] = DateUtils::toUserTz(new DateTime($row['testDateTime']))->format($testDateFormat);

            $emRecDateTime = $row['emRecDateTime'];
            if ($emRecDateTime !== null) {
                $row['emRecDateTime'] = DateUtils::toUserTz(new DateTime($emRecDateTime))
                    ->format(self::DATETIME_FORMAT);
            }

            if (isset($row['clientIp'])) {
                $ips = explode(', ', $row['clientIp']);
                $row['clientIp'] = $ips[0];
            }

            // "formula" hack preventing Excel from converting columns to Date or Number
            $row['vehicleModel'] = '="'.$row['vehicleModel'].'"';
            $row['testNumber'] = '="'.$row['testNumber'].'"';

            // VIN must use ="<vin>" to prevent Excel truncating numeric VIN longer than 15 digits
            $row['vehicleVIN'] = '="'.$row['vehicleVIN'].'"';

            fputcsv($csvBuffer, $row);
        }

        rewind($csvBuffer);
        $output = stream_get_contents($csvBuffer);

        fclose($csvBuffer);

        return $output;
    }

    /**
     * @return \DvsaCommon\Dto\Site\SiteDto|null
     */
    protected function getSite($siteId)
    {
        try {
            return $this->mapperFactory->Site->getById($siteId);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * Get mot tests log summary information from api (year, prev month, prev week, today).
     *
     * @param int $siteId
     *
     * @return MotTestLogSummaryDto|null
     */
    protected function getLogSummary($siteId)
    {
        try {
            return $this->mapperFactory->MotTestLog->getSiteSummary($siteId);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * @param \Site\ViewModel\MotTestLog\MotTestLogFormViewModel|null $formModel
     *
     * @return \DvsaCommon\Dto\Search\MotTestSearchParamsDto
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

        if ($formModel !== null) {
            $dateFrom = $formModel->getDateFrom()->getDate()->getTimestamp();
            $dateTo = $formModel->getDateTo()->getDate()->getTimestamp();
        } else {
            $dateFrom = $queryParams->get(SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM);
            $dateTo = $queryParams->get(SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM);
        }

        $dto
            ->setDateFromTs($dateFrom)
            ->setDateToTs($dateTo);

        return $dto;
    }

    /**
     * @param $siteId
     * @param \DvsaCommon\Dto\Search\MotTestSearchParamsDto $searchParams
     *
     * @return \DvsaCommon\Dto\Search\MotTestSearchParamsDto|null
     */
    private function getLogDataBySearchCriteria($siteId, MotTestSearchParamsDto $searchParams)
    {
        try {
            return $this->mapperFactory->MotTestLog->getSiteData($siteId, $searchParams);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * @param SiteDto $site
     * @param array   $breadcrumbs
     *
     * @return array
     */
    private function prependBreadcrumbsWithAeLink(SiteDto $site, &$breadcrumbs)
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

    /**
     * @param $orgId
     *
     * @return bool
     */
    private function canAccessAePage($orgId)
    {
        return
            $this->authService->isGranted(PermissionInSystem::AUTHORISED_EXAMINER_READ_FULL) ||
            $this->authService->isGrantedAtOrganisation(PermissionAtOrganisation::AUTHORISED_EXAMINER_READ, $orgId);
    }
}
