<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\OdometerReading;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Data\Params\MeterReadingParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class OdometerReadingContext implements Context
{
    private $odometerReading;

    private $userData;

    private $motTestData;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var MotTestContext
     */
    private $motTestContext;

    /**
     * @var Response
     */
    private $odometerReadingResponse;

    /**
     * @param OdometerReading $odometerReading
     */
    public function __construct(
        OdometerReading $odometerReading,
        UserData $userData,
        MotTestData $motTestData
    )
    {
        $this->odometerReading = $odometerReading;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
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
     * @Given /^the Tester adds an Odometer Reading "(NO METER|NOT READ)"$/
     */
    public function theTesterAddsNoOdometerReading($type)
    {
        if ($type == 'NO METER') {
            $this->odometerReading->addNoMeterReadingToTest(
                $this->userData->getCurrentLoggedUser()->getAccessToken(),
                $this->motTestData->getAll()->last()->getMotTestNumber()
            );
        } else {
            $this->odometerReading->addOdometerNotReadToTest(
                $this->userData->getCurrentLoggedUser()->getAccessToken(),
                $this->motTestData->getAll()->last()->getMotTestNumber()
            );
        }
    }

    /**
     * @Given /^the Tester adds an Odometer Reading of (\d+) (mi|km)$/
     * @Given the Tester adds an Odometer Reading
     */
    public function theTesterAddsAnOdometerReadingOfMiles($value = 1000, $unit = MeterReadingParams::KM)
    {
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $motTestId = $this->motTestData->getAll()->last()->getMotTestNumber();
        $response = $this->odometerReading->addMeterReading($token, $motTestId, $value, $unit);

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Given /^the Tester attempts to add an Odometer Reading of (?P<value>.*) (?P<unit>.*)$/
     */
    public function theTesterAttemptsToAddAnOdometerReadingOf($value, $unit)
    {
        $this->odometerReadingResponse = $this->odometerReading->addMeterReading(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->motTestData->getLast()->getMotTestNumber(),
            $value,
            $unit
        );
    }

    /**
     * @Then the odometer reading is rejected
     */
    public function theOdometerReadingIsRejected()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_422, $this->odometerReadingResponse->getStatusCode(), 'Odometer reading not rejected as expected.');
    }
}
