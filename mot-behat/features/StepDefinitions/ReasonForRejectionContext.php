<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionEU;
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
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupB;

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

        PHPUnit::assertTrue($areRfrsEqual, 'Rfrs are not equal!');
    }

    /**
     * @Then All returned reasons for rejection contain the exact :expectedTerm term
     */
    public function allReturnedReasonsForRejectionContainTheExactTerm($expectedTerm)
    {
        PHPUnit::assertGreaterThan(0, $this->reasonForRejectionResponse->getTotalCount());

        $rfrs = $this->reasonForRejectionResponse->getData();

        foreach ($rfrs as $rfr) {
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

        PHPUnit::assertFalse($rfrContainsBaseTerm, 'The base term was found within the RFR');
        PHPUnit::assertTrue($rfrContainsSynonymTerm, 'The synonym was not found within the RFR');
    }

    /**
     * @Then Any of the returned reasons for rejection contains the exact :expectedTerm term
     */
    public function anyOfTheReturnedReasonsForRejectionContainsTheExactTerm($expectedTerm)
    {
        PHPUnit::assertGreaterThan(0, $this->reasonForRejectionResponse->getTotalCount());

        $rfrs = $this->reasonForRejectionResponse->getData();

        $containsTerm = false;
        foreach ($rfrs as $rfr) {
            if ($this->checkIfRfrDataContainsTerm($rfr, $expectedTerm)) {
                $containsTerm = true;
                break;
            }
        }

        PHPUnit::assertTrue($containsTerm, 'Not even one of the returned RFRs contains the specified term!');
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

    private function checkIfRfrDataContainsTerm(ReasonForRejectionDto $rfr, $expectedTerm)
    {
        $description = $rfr->getDescription();
        $testItemSelectorName = $rfr->getTestItemSelectorName();
        $inspectionManualReference = $rfr->getInspectionManualReference();
        $rfrId = $rfr->getRfrId();

        $expectedTermRegExp = '/'.$expectedTerm.'/';

        $rfrDataContainsTerm = (preg_match($expectedTermRegExp, $description)) ||
            (preg_match($expectedTermRegExp, $testItemSelectorName)) ||
            (preg_match($expectedTermRegExp, $inspectionManualReference)) ||
            (preg_match($expectedTermRegExp, $rfrId));

        return $rfrDataContainsTerm;
    }

    /**
     * @When /^I add a (.*) to the test$/
     */
    public function iAddADefectTypeToTheTest($defect)
    {
        if ($defect === 'Advisory') {
            $this->reasonForRejectionData->addDefaultAdvisoryByUser(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast()
            );
        } elseif ($defect === 'PRS') {
            $this->reasonForRejectionData->addDefaultPrsByUser(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast()
            );
        } elseif ($defect === 'Failure') {
            $this->reasonForRejectionData->addDefaultFailureByUser(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast()
            );
        } elseif ($defect === 'Failure PRS') {
            $this->reasonForRejectionData->addPrsByUser(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast(),
                ReasonForRejectionGroupB::RFR_BODY_STRUCTURE_CONDITION
            );
        }
        $response = $this->reasonForRejectionData->getLastResponse();

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then /^the (.*) is associated with the MOT test$/
     */
    public function theDefectIsAssociatedWithTheMOTTest($defect)
    {
        if ($defect === 'Failure') {
            $defect = 'Fail';
        }

        $expectedDefectCategory = strtoupper($defect);
        $expectedMOT = $this->motTestData->getLast()->getMotTestNumber();

        $response = $this->reasonForRejectionData->getLastResponse();
        $requestBody = json_decode($response->getRequest()->getBody());

        $actualDefectCategory = $requestBody->type;
        $motURIString = $response->getRequest()->getUriAsSting();

        PHPUnit::assertContains($expectedMOT, $motURIString);
        PHPUnit::assertSame($expectedDefectCategory, $actualDefectCategory);
    }

    /**
     * @When /^I edit the defect$/
     */
    public function iEditTheDefect()
    {
        $mot = $this->motTestData->getLast();

        $this->reasonForRejectionData->editRFRByUser(
            $this->userData->getCurrentLoggedUser(),
            $mot,
            $this->reasonForRejectionData->getLastResponse()->getBody()->getData()
        );
    }

    /**
     * @Then /^the edited defect is updated$/
     */
    public function theEditedDefectIsUpdated()
    {
        $expectedLongitudinalPosition = 'rear';
        $expectedMOT = $this->motTestData->getLast()->getMotTestNumber();

        $response = $this->reasonForRejectionData->getLastResponse();
        $requestBody = json_decode($response->getRequest()->getBody());

        $actualPosition = $requestBody->locationLongitudinal;
        $motURIString = $response->getRequest()->getUriAsSting();

        PHPUnit::assertContains($expectedMOT, $motURIString);
        PHPUnit::assertSame($expectedLongitudinalPosition, $actualPosition);
    }

    /**
     * @When /^I remove the defect$/
     */
    public function iRemoveTheDefect()
    {
        $mot = $this->motTestData->getLast();

        $this->reasonForRejectionData->removeRFRByUser(
            $this->userData->getCurrentLoggedUser(),
            $mot,
            $this->reasonForRejectionData->getLastResponse()->getBody()->getData()
        );
    }

    /**
     * @Then /^the defect is not associated with the MOT test$/
     */
    public function theDefectIsNotAssociatedWithTheMOTTest()
    {
        $response = $this->reasonForRejectionData->getLastResponse();
        $motTest = $this->motTestData->getLast();
        $data = $response->getBody()->getData();

        PHPUnit::assertEmpty($motTest->getReasonsForRejection());
        PHPUnit::assertEquals('successfully updated Reason for Rejection', $data);
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @When /^I add an new EU (.*) to the test$/
     */
    public function iAddAnNewEUDefectToTheTest($defect)
    {
        if ($defect === 'Dangerous') {
            $this->reasonForRejectionData->addDefaultDangerousDefectByUser(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast()
            );
        } elseif ($defect === 'Major') {
            $this->reasonForRejectionData->addDefaultMajorDefectByUser(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast()
            );
        } elseif ($defect === 'Minor') {
            $this->reasonForRejectionData->addDefaultMinorDefectByUser(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast()
            );
        } elseif ($defect === 'Advisory') {
            $this->reasonForRejectionData->addDefaultAdvisoryByUser(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast()
            );
        } elseif ($defect === 'Major PRS') {
            $this->reasonForRejectionData->addPrsByUser(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast(),
                ReasonForRejectionEU::RFR_REGISTRATION_PLATES_MAJOR
            );
        } elseif ($defect === 'Dangerous PRS') {
            $this->reasonForRejectionData->addPrsByUser(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast(),
                ReasonForRejectionEU::RFR_VEHICLE_IDENTIFICATION_NUMBER_DANGEROUS
            );
        }

        $response = $this->reasonForRejectionData->getLastResponse();

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then the new start-dated past rfr is available to use
     */
    public function theNewStartDatedRfrIsAvailableToUse()
    {
        $this->reasonForRejectionData->addDefaultStartDatedPastReasonForRejection(
            $this->userData->getCurrentLoggedUser(),
            $this->motTestData->getLast()
        );

        $response = $this->reasonForRejectionData->getLastResponse();

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then the new end-dated past rfr is not available to use
     */
    public function theNewEndDatedPastRfrIsNotAvailableToUse()
    {
        try {
            $this->reasonForRejectionData->addDefaultEndDatedPastReasonForRejection(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast()
            );
        } catch (UnexpectedResponseStatusCodeException $exception) {
        }

        $response = $this->reasonForRejectionData->getLastResponse();

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $response->getStatusCode());
        PHPUnit::assertSame('End-dated RFR can not be added', $exception->getMessage());
    }

    /**
     * @Then the new start-dated future rfr is not available to use
     */
    public function theNewNewStartDatedFutureRfrIsNotAvailableToUse()
    {
        try {
            $this->reasonForRejectionData->addDefaultStartDatedFutureReasonForRejection(
                $this->userData->getCurrentLoggedUser(),
                $this->motTestData->getLast()
            );
        } catch (UnexpectedResponseStatusCodeException $exception) {
        }

        $response = $this->reasonForRejectionData->getLastResponse();

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $response->getStatusCode());
        PHPUnit::assertSame('Future Start-dated RFR can not be added', $exception->getMessage());
    }
}
