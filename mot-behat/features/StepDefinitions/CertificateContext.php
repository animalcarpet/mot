<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Certificate;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestParams;
use Dvsa\Mot\Behat\Support\Data\Params\OdometerReadingParams;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestPdfDocumentParams;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Dto\Common\MotTestDto;
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

    private $motTestData;

    private $userData;

    private $documents = [];

    /**
     * @param Certificate $certificate
     * @param MotTest $motTest
     * @param TestSupportHelper $testSupportHelper
     */
    public function __construct(Certificate $certificate, MotTest $motTest, TestSupportHelper $testSupportHelper, MotTestData $motTestData, UserData $userData)
    {
        $this->certificate = $certificate;
        $this->motTest = $motTest;
        $this->testSupportHelper = $testSupportHelper;
        $this->motTestData = $motTestData;
        $this->userData = $userData;
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
            $this->motTestData->getLast()->getMotTestNumber(),
            $this->userData->getCurrentLoggedUser()->getAccessToken()
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
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $this->motTest->getMOTCertificateDetails($token);

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
        $motTests = $this->motTestData->getAll();
        /** @var MotTestDto $mot */
        foreach($motTests as $mot)
        {
            $document = $this->testSupportHelper->getDocumentService()->get($mot->getDocument());
            $this->documents[$mot->getId()] = $document;
        }
    }

    /**
     * @Then document has only odometer readings from tests performed in past
     */
    public function documentHasOnlyOdometerReadingsFromTestsPerformedInPast()
    {
        $fullOdometerHistory = [];
        foreach($this->motTestContext->getMotTests() as $motTest) {
            $fullOdometerHistory[]= [
                MotTestParams::STARTED_DATE => $motTest[MotTestParams::STARTED_DATE],
                MotTestParams::ODOMETER_READING => $motTest[MotTestParams::ODOMETER_READING]
            ];
        }

        foreach($this->motTestContext->getMotTests() as $motTest) {
            $documentContent = json_decode($motTest[MotTestParams::DOCUMENT][MotTestPdfDocumentParams::DOCUMENT_CONTENT], true);
            $documentOdometerHistory = $documentContent[MotTestPdfDocumentParams::ODOMETER_HISTORY];
            $this->validateOdometerHistory($fullOdometerHistory, $documentOdometerHistory, $motTest[MotTestParams::STARTED_DATE]);
        }
    }

    /**
     * @Then document has only :number odometer readings from newest passed tests
     */
    public function documentHasOnlyOdometerReadingsFromPassedTests($number)
    {
        $testsNotShown = [];
        $passedTests = [];
        $motTests = $this->motTestData->getAll();
        /** @var MotTestDto $motTest */
        foreach($motTests as $motTest) {
            if($motTest->getStatus() !== MotTestStatusName::PASSED){
                $testsNotShown[] = $motTest;
            } else {
                $passedTests[] = $motTest;
            }
        }

        // remove 4 newest passed MOT tests, to get old tests
        $passedTests = array_reverse($passedTests);
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
            $odometerReadingDate = new DateTime($value[MotTestParams::STARTED_DATE]);

            return $motTestDate >= $odometerReadingDate;
        });

        $expectedOdometerValues = [];
        foreach($expectedOdometerReadings as $reading) {
            $expectedOdometerValues[]= $reading[MotTestParams::ODOMETER_READING][OdometerReadingParams::VALUE] . ' ' . $reading[MotTestParams::ODOMETER_READING][OdometerReadingParams::UNIT];
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
            $this->certificate->requestCertificate($motTest[MotTestParams::MOT_TEST_NUMBER], $this->sessionContext->getCurrentAccessToken());
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
            $this->testSupportHelper->getDocumentService()->delete($motTest[MotTestParams::DOCUMENT]);
            unset($motTest[MotTestParams::DOCUMENT]);
            $motTests[]=$motTest;
        }
        $this->motTestContext->setMotTests($motTests);
    }

    /**
     * @param MotTestDto[] $motTestCollection
     * @param MotTestDto $motTest
     * @return MotTestDto[]
     */
    private function getMotTestsPerformedBeforeTest(array $motTestCollection, MotTestDto $motTest)
    {
        $testsInThePast = [];
        foreach ($motTestCollection as $test) {
            if($motTest->getCompletedDate() > $test->getCompletedDate()){
                $testsInThePast[] = $test;
            }
        }

        return $testsInThePast;
    }

    /**
     * @param MotTestDto[] $testsShown
     * @param MotTestDto $motTestToCheck
     */
    private function validateOdometerHistoryIsCreatedFromPassedTests(array $testsShown, MotTestDto $motTestToCheck)
    {
        $motTestsPerformedBefore = $this->getMotTestsPerformedBeforeTest($testsShown, $motTestToCheck);
        $document = array_shift($this->documents[$motTestToCheck->getId()]);
        $odometerHistory = json_decode($document[MotTestPdfDocumentParams::DOCUMENT_CONTENT], true)[MotTestPdfDocumentParams::ODOMETER_HISTORY];
        foreach ($motTestsPerformedBefore as $testThatShoudBeInHistory) {
            $expectedOdometerHistory = (string)$testThatShoudBeInHistory->getOdometerReading()->getValue();
            PHPUnit::assertGreaterThan(
                0,
                strpos($odometerHistory, $expectedOdometerHistory),
                'one of the odometer readings from passed MOT tests is not in certificate'
            );
        }
    }

    /**
     * @param MotTestDto[] $testsNotShown
     * @param MotTestDto $motTestToCheck
     */
    private function validateOdometerHistoryDoesNotContainFailedAndOldTests(array $testsNotShown, MotTestDto $motTestToCheck)
    {
        $document = array_shift($this->documents[$motTestToCheck->getId()]);
        $odometerHistory = json_decode($document[MotTestPdfDocumentParams::DOCUMENT_CONTENT], true)[MotTestPdfDocumentParams::ODOMETER_HISTORY];
        foreach ($testsNotShown as $mot) {
            PHPUnit::assertFalse(
                strpos($odometerHistory, (string)$mot->getOdometerReading()->getValue()),
                'one of the odometer readings is from failed or too old test'
            );
        }
    }
}