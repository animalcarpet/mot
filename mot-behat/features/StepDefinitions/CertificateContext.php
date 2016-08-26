<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Certificate;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Enum\MotTestStatusName;
use PHPUnit_Framework_Assert as PHPUnit;
use Smalot\PdfParser\Document;

class CertificateContext implements Context
{
    /**
     * @var Certificate
     */
    private $certificate;

    /**
     * @var MotTest
     */
    private $motTest;

    /**
     * @var array
     */
    private $motTests;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var MotTestContext
     */
    private $motTestContext;

    private $certificateResult;
    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @param Certificate $certificate
     * @param MotTest $motTest
     * @param TestSupportHelper $testSupportHelper
     */
    public function __construct(Certificate $certificate, MotTest $motTest, TestSupportHelper $testSupportHelper)
    {
        $this->certificate = $certificate;
        $this->motTest = $motTest;
        $this->testSupportHelper = $testSupportHelper;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->motTestContext = $scope->getEnvironment()->getContext(MotTestContext::class);
    }

    /**
     * @Then /^the certificate contains (.*) pages$/
     */
    public function theCertificateContains($noOfPages)
    {
        $pdf = $this->parsePdf($this->certificateResult);

        PHPUnit::assertEquals($noOfPages, count($pdf->getPages()), 'PDF does not contain correct number of pages');
    }

    /**
     * @Given /^requests the certificate$/
     */
    public function requestsTheCertificate()
    {
        $this->certificateResult = $this->certificate->requestCertificate(
            $this->motTestContext->getMotTestNumber(),
            $this->sessionContext->getCurrentAccessToken()
        );
    }

    /**
     * @Given /^the certificate contains the text (.*)$/
     */
    public function theCertificateContainsTheText($expectedText)
    {
        $pdf = $this->parsePdf($this->certificateResult);

        if ((bool)strpos($expectedText, '&&')) {
            $expectedText = explode('&&', $expectedText);
        }

        if (is_array($expectedText)) {
            foreach ($expectedText as $text) {
                $result = (bool)strpos($pdf->getText(), trim($text));
                PHPUnit::assertTrue($result, 'Could not find expected text in document - ' . $text);
            }
        } else {
            $result = (bool)strpos($pdf->getText(), $expectedText);
            PHPUnit::assertTrue($result, 'Could not find expected text in document - ' . $expectedText);
        }
    }

    /**
     * @When /^I reprint an MOT Test Certificate$/
     */
    public function iReprintAnMOTTestCertificate()
    {
        $token = $this->sessionContext->getCurrentAccessToken();
        $this->motTest->getMOTCertificateDetails($token, '651157444199');

        $this->certificate->getDuplicateCertificate($token);
    }

    /**
     * @param mixed $certificate
     *
     * @return Document
     *
     * @throws \Exception
     */
    private function parsePdf($certificate)
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();

            return $parser->parseContent($certificate);
        } catch (\Exception $ex) {
            throw new \Exception('Unable to parse the MOT certificate. ' . $ex->getMessage());
        }
    }

    /**
     * @When I fetch jasper document for test
     */
    public function iFetchJasperDocumentForTest()
    {
        $motTests = [];
        foreach($this->motTestContext->getMotTests() as $key => $motTest)
        {
            $document = $this->testSupportHelper->getDocumentService()->get($motTest['document'])[0];
            $motTest['document'] = $document;
            $motTests[]= $motTest;
        }
        $this->motTestContext->setMotTests($motTests);
    }

    /**
     * @Then document has only odometer readings from tests performed in past
     */
    public function documentHasOnlyOdometerReadingsFromTestsPerformedInPast()
    {
        $fullOdometerHistory = [];
        foreach($this->motTestContext->getMotTests() as $motTest) {
            $fullOdometerHistory[]= [
                'startedDate' => $motTest['startedDate'],
                'odometerReading' => $motTest['odometerReading']
            ];
        }

        foreach($this->motTestContext->getMotTests() as $motTest) {
            $documentContent = json_decode($motTest['document']['document_content'], true);
            $documentOdometerHistory = $documentContent['OdometerHistory'];
            $this->validateOdometerHistory($fullOdometerHistory, $documentOdometerHistory, $motTest['startedDate']);
        }
    }

    /**
     * @Then document has only :number odometer readings from newest passed tests
     */
    public function documentHasOnlyOdometerReadingsFromPassedTests($number)
    {
        $testsNotShown = [];
        $passedTests = [];
        foreach($this->motTestContext->getMotTests() as $motTest) {
            if($motTest['status'] != MotTestStatusName::PASSED){
                $testsNotShown[] = $motTest;
            } else {
                $passedTests[] = $motTest;
            }
        }

        // remove 4 newest passed MOT tests, to get old tests
        $passedTestsReadingsNotShown = array_slice($passedTests, $number);
        $testsNotShown += $passedTestsReadingsNotShown;
        // get 4 newest mot tests
        $passedTestsReadingsShown = array_slice($passedTests, 0, $number);

        $latestMotTest = $passedTests[0];
        $this->validateOdometerHistoryIsCreatedFromPassedTests($passedTestsReadingsShown, $latestMotTest);
        $this->validateOdometerHistoryDoesNotContainFailedAndOldTests($testsNotShown, $latestMotTest);
    }

    private function validateOdometerHistory($fullOdometerHistory, $documentOdometerHistory, $startedDate)
    {
        $expectedOdometerReadings = array_filter($fullOdometerHistory, function($value) use ($startedDate) {
            $motTestDate = new DateTime($startedDate);
            $odometerReadingDate = new DateTime($value['startedDate']);

            return $motTestDate >= $odometerReadingDate;
        });

        $expectedOdometerValues = [];
        foreach($expectedOdometerReadings as $reading) {
            $expectedOdometerValues[]= $reading['odometerReading']['value'] . ' ' . $reading['odometerReading']['unit'];
        }

        $documentOdometerValues = array_reduce(explode("\n", $documentOdometerHistory), function($result, $value) {
            preg_match('/: ([0-9].*)/', $value, $matches);
            $result[]=$matches[1];

            return $result;
        });

        PHPUnit::assertEquals($expectedOdometerValues, $documentOdometerValues);
    }

    /**
     * @Given print of created mot tests is issued
     * @Given print of migrated mot tests is issued
     */
    public function printOfCreatedMotTestsIsIssued()
    {
        foreach($this->motTestContext->getMotTests() as $motTest) {
            $this->certificate->requestCertificate($motTest['motTestNumber'], $this->sessionContext->getCurrentAccessToken());
        }

        $this->motTestContext->refreshMotTests();
    }


    /**
     * @Given jasper documents were not printed
     */
    public function removeJasperDocumentsForMotTests()
    {
        $motTests=[];
        foreach($this->motTestContext->getMotTests() as $motTest)
        {
            $this->testSupportHelper->getDocumentService()->delete($motTest['document']);
            unset($motTest['document']);
            $motTests[]=$motTest;
        }
        $this->motTestContext->setMotTests($motTests);
    }

    private function getMotTestsPerformedBeforeTest($motTestCollection, $motTest)
    {
        $testsInThePast = [];
        foreach ($motTestCollection as $test) {
            if($motTest['completedDate'] > $test['completedDate']){
                $testsInThePast[] = $test;
            }
        }

        return $testsInThePast;
    }
    
    private function validateOdometerHistoryIsCreatedFromPassedTests($testsShown, $motTestToCheck)
    {
        $motTestsPerformedBefore = $this->getMotTestsPerformedBeforeTest($testsShown, $motTestToCheck);
        foreach ($motTestsPerformedBefore as $testThatShoudBeInHistory) {
            $odometerHistory = json_decode($motTestToCheck['document']['document_content'], true)['OdometerHistory'];
            PHPUnit::assertGreaterThan(
                0,
                strpos($odometerHistory, (string)$testThatShoudBeInHistory['odometerReading']['value']),
                'one of the odometer readings from passed MOT tests is not in certificate'
            );
        }
    }

    private function validateOdometerHistoryDoesNotContainFailedAndOldTests($testsNotShown, $motTestToCheck)
    {
        $odometerHistory = json_decode($motTestToCheck['document']['document_content'], true)['OdometerHistory'];
        foreach ($testsNotShown as $odometer) {
            PHPUnit::assertFalse(
                strpos($odometerHistory, (string)$odometer['odometerReading']['value']),
                'one of the odometer readings is from failed or too old test'
            );
        }
    }
}