<?php

namespace DvsaMotTest\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\MapperFactory;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Organisation\MotTestLogSummaryDto;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaFeature\FeatureToggles;
use Organisation\Controller\MotTestLogController;
use DvsaMotTest\ViewModel\TesterMotTestLog\TesterMotTestLogViewModel;
use Zend\View\Model\ViewModel;
use Zend\Http\Headers;
use Zend\Http\PhpEnvironment\Response;

/**
 * Class TesterMotTestLogController.
 */
class TesterMotTestLogController extends MotTestLogController
{
    private $contextProvider;

    public function __construct(
        MotFrontendAuthorisationServiceInterface $authService,
        MapperFactory $mapperFactory,
        FeatureToggles $featureToggles,
        ContextProvider $contextProvider
    ) {
        parent::__construct($authService, $mapperFactory, $featureToggles);
        $this->contextProvider = $contextProvider;
    }

    public function indexAction()
    {
        $testerId = $this->getIdentity()->getUserId();

        $this->request = $this->getRequest();

        $motTestLogs = $this->getLogSummary($testerId);

        //  logical block :: prepare models for view
        $viewModel = new TesterMotTestLogViewModel($motTestLogs);
        $viewModel->parseData($this->request->getQuery());

        $formModel = $viewModel->getFormModel();
        if ($formModel->isValid()) {
            //  logical block :: create object with parameters for sending to api   --
            $searchParams = $this->prepareSearchParams($formModel);
            if ($searchParams->getRowsCount() === 0) {
                $searchParams->setRowsCount($viewModel->getTable()->getTableOptions()->getItemsPerPage());
            }

            $apiResult = $this->getTesterLogDataBySearchCriteria($testerId, $searchParams);

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

        $viewModel->setReturnLink($this->getPreviousPageLink());
        $breadcrumbs = $this->buildBreadcrumbs();
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $this->layout()->setVariable('pageTitle', $this->getIdentity()->getDisplayName());
        $this->layout()->setVariable('pageSubTitle', 'Test logs of Tester');

        return new ViewModel([
            'viewModel' => $viewModel,
        ]);
    }

    public function downloadCsvAction()
    {
        $testerId = $this->getIdentity()->getUserId();

        $searchParams = $this->prepareSearchParams();
        $searchParams
            ->setFormat(SearchParamConst::FORMAT_DATA_CSV)
            ->setRowsCount(self::PER_PAGE_COUNT)
            ->setIsApiGetTotalCount(true)
            ->setIsApiGetData(false);

        $apiResult = $this->getTesterLogDataBySearchCriteria($testerId, $searchParams);

        // Determine the number of pages to retrieve based on total results and per page count
        $lastPageNumber = ceil($apiResult->getTotalResultCount() / self::PER_PAGE_COUNT);

        // Now we want to fetch the data from the API, and not the total count
        $searchParams->setIsApiGetTotalCount(false)->setIsApiGetData(true);

        $fileName = 'test-log-'.
            (new \DateTime('@'.$searchParams->getDateFromTs()))->format('dmY').'-'.
            (new \DateTime('@'.$searchParams->getDateToTs()))->format('dmY').'.csv';

        // Prepare the headers and send them
        $headers = (new Headers())->addHeaders([
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
        ]);

        $this->response = new Response();
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
        for ($i = 1; $i < $lastPageNumber + 1; ++$i) {
            $searchParams->setPageNr($i);
            $apiResult = $this->getTesterLogDataBySearchCriteria($testerId, $searchParams);
            $this->prepareCsvBody($apiResult->getData());
            flush();
            set_time_limit(30);
        }

        fclose($this->csvHandle);

        return $this->response;
    }

    /**
     * @param $testerId
     * @param MotTestSearchParamsDto $searchParams
     *
     * @return SearchResultDto|null
     */
    protected function getTesterLogDataBySearchCriteria($testerId, MotTestSearchParamsDto $searchParams)
    {
        try {
            return $this->mapperFactory->MotTestLog->getTesterData($testerId, $searchParams);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * Get mot tests log summary information from api (year, prev month, prev week, today).
     *
     * @param $testerId
     *
     * @return MotTestLogSummaryDto|null
     */
    protected function getLogSummary($testerId)
    {
        try {
            return $this->mapperFactory->MotTestLog->getTesterSummary($testerId);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    private function getPreviousPageLink()
    {
        switch ($this->contextProvider->getContext()) {
            case ContextProvider::YOUR_PROFILE_CONTEXT:
                return ['Your profile' => $this->url()->fromRoute(ContextProvider::YOUR_PROFILE_PARENT_ROUTE)];
            case ContextProvider::NO_CONTEXT:
                return ['Performance dashboard' => PersonUrlBuilderWeb::of()->stats()];
        }

        throw new \UnexpectedValueException();
    }

    private function buildBreadcrumbs()
    {
        $breadcrumbs = [
            $this->getPreviousPageLink(),
            ['Tester Test logs' => ''],
        ];

        return $breadcrumbs;
    }
}
