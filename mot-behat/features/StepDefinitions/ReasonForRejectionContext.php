<?php

use Behat\Behat\Context\Context;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Data\ReasonForRejectionData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionResponseInterface;
use Zend\Http\Response as HttpResponse;
use Dvsa\Mot\Behat\Support\Helper\ApiResourceHelper;
use DvsaCommon\ApiClient\ReasonForRejection\ReasonForRejectionApiResource;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;
use DvsaCommon\Dto\ReasonForRejection\ReasonForRejectionDto;

class ReasonForRejectionContext implements Context
{
    private $reasonForRejectionData;

    private $userData;

    private $motTestData;

    private $apiResourceHelper;

    /** @var SearchReasonForRejectionResponseInterface */
    private $reasonForRejectionResponse = null;

    /** @var SearchReasonForRejectionResponseInterface */
    private $synonymReasonForRejectionResponse = null;

    public function __construct(
        ReasonForRejectionData $reasonForRejectionData,
        UserData $userData,
        MotTestData $motTestData,
        ApiResourceHelper $apiResourceHelper
    ) {
        $this->reasonForRejectionData = $reasonForRejectionData;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->apiResourceHelper = $apiResourceHelper;
    }

    /**
     * @Then /^I can search for Rfr$/
     */
    public function iCanSearchForRfr()
    {
        $response = $this->reasonForRejectionData->searchWithDefaultParams(
            $this->motTestData->getLast()
        );

        PHPUnit::assertGreaterThan(0, $response->getTotalCount());
    }

    /**
     * @When I search for reason for rejection by :searchTerm for vehicle class :vehicleClassCode
     */
    public function iSearchForReasonForRejectionByForVehicleClass($searchTerm, $vehicleClassCode)
    {
        $this->reasonForRejectionResponse = $this->reasonForRejectionData->searchWithParams($vehicleClassCode, $searchTerm);
    }

    /**
     * @When I search for reason for rejection by :searchTerm for vehicle class :vehicleClassCode via api
     */
    public function iSearchForReasonForRejectionByForVehicleClassViaApi($searchTerm, $vehicleClassCode)
    {
        /** @var ReasonForRejectionApiResource $rfrApiClient */
        $rfrApiClient = $this->apiResourceHelper->create(ReasonForRejectionApiResource::class);

        $this->reasonForRejectionResponse = $rfrApiClient->search($searchTerm, $vehicleClassCode, SearchReasonForRejectionInterface::TESTER_ROLE_FLAG, 1);
    }

    /**
     * @Then Reason for rejection is returned
     */
    public function reasonForRejectionIsReturned()
    {
        PHPUnit::assertGreaterThan(0, $this->reasonForRejectionResponse->getTotalCount());
    }

    /**
     * @When I search for reason for rejection by synonym :synonymedTerm for vehicle class :vehicleClass
     */
    public function iSearchForReasonForRejectionBySynonymForVehicleClass($synonymedTerm, $vehicleClass)
    {
        $this->synonymReasonForRejectionResponse = $this->reasonForRejectionData->searchWithParams($vehicleClass, $synonymedTerm);
    }

    /**
     * @Then Both sets of returned reasons for rejection are the same
     */
    public function bothSetsOfReturnedReasonsForRejectionAreTheSame()
    {
        PHPUnit::assertGreaterThan(0, $this->reasonForRejectionResponse->getTotalCount());

        $areRfrsEqual = $this->synonymReasonForRejectionResponse->getData() === $this->reasonForRejectionResponse->getData();

        PHPUnit::assertTrue($areRfrsEqual, "Rfrs are not equal!");
    }

    /**
     * @Then All returned reasons for rejection contain the exact :expectedTerm term
     */
    public function allReturnedReasonsForRejectionContainTheExactTerm($expectedTerm)
    {
        PHPUnit::assertGreaterThan(0, $this->reasonForRejectionResponse->getTotalCount());

        $rfrs = $this->reasonForRejectionResponse->getData();

        foreach($rfrs as $rfr) {
            $rfrDataContainsTerm = $this->checkIfRfrDataContainsTerm($rfr, $expectedTerm);

            PHPUnit::assertTrue($rfrDataContainsTerm, "Not all returned RFRs doesn't contain the specified term!");
        }
    }

    /**
     * @Then Reason for rejection is not returned
     */
    public function reasonForRejectionIsNotReturned()
    {
        PHPUnit::assertEquals(0, $this->reasonForRejectionResponse->getTotalCount());
    }

    /**
     * @Then /^I can list child test items selector$/
     */
    public function iCanListChildTestItemSelector()
    {
        $this->reasonForRejectionData->listTestItemSelectors($this->motTestData->getLast());
        $response = $this->reasonForRejectionData->getLastResponse();

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then the new start-dated defect :category is available to use
     */
    public function theNewStartDatedDefectIsAvailableToUse($category)
    {
        $this->reasonForRejectionData->listTestItemSelectors($this->motTestData->getLast());
        $response = $this->reasonForRejectionData->getLastResponse();
        $body = $response->getBody()->getData();
        $testItemSelectors = $body[0]['testItemSelectors'];

        $foundItemSelector = false;
        foreach ($testItemSelectors as $itemSelector) {
            if ($itemSelector['name'] == $category) {
                $foundItemSelector = true;
            }
        }

        PHPUnit::assertTrue($foundItemSelector, "Category of $category not found in Item Selectors");
    }

    /**
     * @Then the end-dated defect :category is not available to use
     */
    public function theEndDatedDefectIsNotAvailableToUse($category)
    {
        $this->reasonForRejectionData->listTestItemSelectors($this->motTestData->getLast());
        $response = $this->reasonForRejectionData->getLastResponse();
        $body = $response->getBody()->getData();
        $testItemSelectors = $body[0]['testItemSelectors'];

        $foundItemSelector = false;
        foreach ($testItemSelectors as $itemSelector) {
            if ($itemSelector['name'] == $category) {
                $foundItemSelector = true;
            }
        }

        PHPUnit::assertFalse($foundItemSelector, "Category of $category was found in Item Selectors");
    }

    /**
     * @Then The first returned element contains :synonymTerm but not :baseTerm
     */
    public function theFirstReturnedElementContainsButNot($baseTerm, $synonymTerm)
    {
        PHPUnit::assertGreaterThan(0, $this->reasonForRejectionResponse->getTotalCount());

        $topRfr = $this->reasonForRejectionResponse->getData()[0];

        $rfrContainsBaseTerm = $this->checkIfRfrDataContainsTerm($topRfr, $baseTerm);
        $rfrContainsSynonymTerm = $this->checkIfRfrDataContainsTerm($topRfr, $synonymTerm);

        PHPUnit::assertFalse($rfrContainsBaseTerm, "The base term was found within the RFR");
        PHPUnit::assertTrue($rfrContainsSynonymTerm, "The synonym was not found within the RFR");
    }

    /**
     * @Then Any of the returned reasons for rejection contains the exact :expectedTerm term
     */
    public function anyOfTheReturnedReasonsForRejectionContainsTheExactTerm($expectedTerm)
    {
        PHPUnit::assertGreaterThan(0, $this->reasonForRejectionResponse->getTotalCount());

        $rfrs = $this->reasonForRejectionResponse->getData();

        $containsTerm = false;
        foreach($rfrs as $rfr) {

            if($this->checkIfRfrDataContainsTerm($rfr, $expectedTerm)) {
                $containsTerm = true;
                break;
            }
        }

        PHPUnit::assertTrue($containsTerm, "Not even one of the returned RFRs contains the specified term!");
    }

    /**
     * @Given /^I can add PRS to test$/
     */
    public function iCanAddPRSToTest()
    {
        $this->reasonForRejectionData->addDefaultPrsByUser(
            $this->userData->getCurrentLoggedUser(),
            $this->motTestData->getLast()
        );

        $response = $this->reasonForRejectionData->getLastResponse();

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Given /^I can add a Failure to test$/
     * @Given /^the Tester adds a Reason for Rejection$/
     */
    public function iCanAddAFailureToTest()
    {
        $this->reasonForRejectionData->addDefaultFailureByUser(
            $this->userData->getCurrentLoggedUser(),
            $this->motTestData->getLast()
        );

        $response = $this->reasonForRejectionData->getLastResponse();

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then I can edit previously added Rfr
     */
    public function iCanEditPreviouslyAddedRfr()
    {
        $mot = $this->motTestData->getLast();

        $this->reasonForRejectionData->editRFRByUser(
            $this->userData->getCurrentLoggedUser(),
            $mot,
            $this->reasonForRejectionData->getLastResponse()->getBody()->getData()
        );
    }

    private function checkIfRfrDataContainsTerm(ReasonForRejectionDto $rfr, $expectedTerm) {
        $description = $rfr->getDescription();
        $testItemSelectorName = $rfr->getTestItemSelectorName();
        $inspectionManualReference = $rfr->getInspectionManualReference();
        $rfrId = $rfr->getRfrId();

        $expectedTermRegExp = "/" . $expectedTerm . "/";

        $rfrDataContainsTerm = (preg_match($expectedTermRegExp, $description)) ||
            (preg_match($expectedTermRegExp, $testItemSelectorName)) ||
            (preg_match($expectedTermRegExp, $inspectionManualReference)) ||
            (preg_match($expectedTermRegExp, $rfrId));

        return $rfrDataContainsTerm;
    }
}
