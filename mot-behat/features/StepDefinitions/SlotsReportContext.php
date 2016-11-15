<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\SlotReport;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

/**
 * Class SlotsReportContext
 */
class SlotsReportContext implements Context
{
    private $reportApi;
    private $userData;

    /**
     * @var Response
     */
    private $responseReceived;

    public function __construct(SlotReport $reportApi, UserData $userData)
    {
        $this->reportApi = $reportApi;
        $this->userData = $userData;
    }

    /**
     * @When I request to generate a :slotUsage report from :fromDate to :toDate
     */
    public function iRequestToGenerateASlotUsageReport($report, $fromDate, $toDate)
    {
        $token                  = $this->userData->getCurrentLoggedUser()->getAccessToken();
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
        $token                  = $this->userData->getCurrentLoggedUser()->getAccessToken();
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
