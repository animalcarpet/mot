<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\SlotReport;
use Dvsa\Mot\Behat\Support\Response;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

/**
 * Class SlotsReportContext
 */
class SlotsReportContext implements Context
{
    /**
     * @var SlotReport
     */
    private $reportApi;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var Response
     */
    private $responseReceived;

    /**
     * @param SlotReport $reportApi
     */
    public function __construct(SlotReport $reportApi)
    {
        $this->reportApi = $reportApi;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    /**
     * @When I request to generate a :slotUsage report from :fromDate to :toDate
     */
    public function iRequestToGenerateASlotUsageReport($report, $fromDate, $toDate)
    {
        $token                  = $this->sessionContext->getCurrentAccessToken();
        $filter                 = new \Zend\Filter\Word\CamelCaseToSeparator(' ');
        $this->responseReceived = $this->reportApi->createReport(
            $token,
            $report,
            $filter->filter($fromDate),
            $filter->filter($toDate)
        );

        PHPUnit::assertEquals(
            HttpResponse::STATUS_CODE_200,
            $this->responseReceived->getStatusCode(),
            'Unable to generate report'
        );
    }

    /**
     * @Then The report should be added to the queue
     */
    public function theReportShouldBeAddedToTheQueue()
    {

        $body = $this->responseReceived->getBody();
        $this->checkReporFieldsExist($body);
    }

    /**
     * @When I request to get the status of the report
     */
    public function iRequestToGetTheStatusOfReport()
    {
        $token                  = $this->sessionContext->getCurrentAccessToken();
        $body                   = $this->responseReceived->getBody();
        $this->responseReceived = $this->reportApi->getReportStatus($token, $body['data']['reference']);

        PHPUnit::assertEquals(
            HttpResponse::STATUS_CODE_200,
            $this->responseReceived->getStatusCode(),
            'Unable to generate report'
        );
    }

    /**
     * @Then The status should be returned
     */
    public function theStatusOfTheReportShouldBeReturned()
    {
        $body = $this->responseReceived->getBody();
        $this->checkReporFieldsExist($body);
    }

    private function checkReporFieldsExist($body)
    {
        $fields = [
            'reference',
            'totalRows',
            'processedRows',
            'fileSize',
            'dateFrom',
            'dateTo',
            'reportTypeName',
            'reportTypeReference',
            'readyForDownload',
            'isComplete'
        ];

        PHPUnit::assertArrayHasKey('data', $body, 'data key not returned in response');

        foreach ($fields as $field) {
            PHPUnit::assertArrayHasKey($field, $body['data'], "$field not found in response");
        }
    }
}
