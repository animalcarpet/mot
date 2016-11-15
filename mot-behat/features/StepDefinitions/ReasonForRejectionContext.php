<?php

use Behat\Behat\Context\Context;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Data\ReasonForRejectionData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupA;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupB;
use DvsaCommon\Enum\VehicleClassCode;
use Zend\Http\Response as HttpResponse;

class ReasonForRejectionContext implements Context
{
    private $reasonForRejectionData;

    private $userData;

    private $motTestData;

    public function __construct(
        ReasonForRejectionData $reasonForRejectionData,
        UserData $userData,
        MotTestData $motTestData
    )
    {
        $this->reasonForRejectionData = $reasonForRejectionData;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
    }

    /**
     * @Then /^I can search for Rfr$/
     */
    public function iCanSearchForRfr()
    {
        $this->reasonForRejectionData->searchWithDefaultParamsByUser(
            $this->userData->getCurrentLoggedUser(),
            $this->motTestData->getLast()
        );

        $response = $this->reasonForRejectionData->getLastResponse();

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
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
     * @Given /^I can add PRS to test$/
     */
    public function iCanAddPRSToTest()
    {
        $this->reasonForRejectionData->addDefaultPrsByUser(
            $this->userData->getCurrentLoggedUser(),
            $this->motTestData->getLast()
        );

        $response =$this->reasonForRejectionData->getLastResponse();

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
}
