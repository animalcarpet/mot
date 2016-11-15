<?php

use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Dto\Common\MotTestDto;
use Behat\Behat\Context\Context;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class DemoMotTestContext implements Context
{
    private $userData;
    private $motTestData;
    private $vehicleData;

    public function __construct(
        UserData $userData,
        MotTestData $motTestData,
        VehicleData $vehicleData
    )
    {
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->vehicleData = $vehicleData;
    }

    /**
     * @When I start a Demo MOT Test
     * @When /^I have a Demo MOT Test In Progress$/
     * @Given /^I attempt to create a Demo MOT Test$/
     * @Given vehicle has a Demonstration Test following training test started
     */
    public function iStartDemoTest()
    {
        $this->motTestData->create(
            $this->userData->getCurrentLoggedUser(),
            $this->vehicleData->create(),
            null,
            MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING
        );
    }

    /**
     * @Given I start a Demo MOT test as a Tester
     */
    public function iStartDemoMotTestAsTester()
    {
        $tester = $this->userData->createTester();
        $this->userData->setCurrentLoggedUser($tester);
        $this->iStartDemoTest();
    }

    /**
     * @Given I try start a Demo MOT test as a Tester
     */
    public function iTryStartDemoMotTestAsTester()
    {
        try {
            $this->iStartDemoMotTestAsTester();
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then I am unable to start a new Demo MOT test
     */
    public function iAmUnableToStartANewDemoMotTest()
    {
        $mot = $this->motTestData->getLast();
        $motDetails = $this->motTestData->fetchMotTestData($this->userData->getCurrentLoggedUser(), $mot->getMotTestNumber());

        PHPUnit::assertEquals(MotTestStatusName::ACTIVE, $motDetails->getStatus());

        $this->iTryStartDemoMotTestAsTester();
        $response = $this->motTestData->getDemoMotTestLastResponse();
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_400, $response->getStatusCode());
    }

    /**
     * @When I don't have a demo test already in progress
     */
    function iDontHaveDemoTestAlreadyInProgress()
    {
        $collection = $this->motTestData->getAll()->filter(function (MotTestDto $mot) {
            $hasDemoTestFollowingTraining = ($mot->getTestType()->getCode() === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);
            $hasRoutineDemoTest = ($mot->getTestType()->getCode() === MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST);

            return $hasDemoTestFollowingTraining || $hasRoutineDemoTest;
        });

        PHPUnit::assertCount(0, $collection);
    }
}
