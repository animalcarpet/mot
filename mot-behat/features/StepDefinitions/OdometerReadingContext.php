<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Data\Params\MeterReadingParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\OdometerReadingData;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class OdometerReadingContext implements Context
{
    private $userData;

    private $motTestData;

    private $odometerReadingData;

    public function __construct(
        UserData $userData,
        MotTestData $motTestData,
        OdometerReadingData $odometerReadingData
    )
    {
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->odometerReadingData = $odometerReadingData;
    }

    /**
     * @Given /^the Tester adds an Odometer Reading "(NO METER|NOT READ)"$/
     */
    public function theTesterAddsNoOdometerReading($type)
    {
        $mot = $this->motTestData->getLast();
        if ($type == 'NO METER') {
            $this->odometerReadingData->addNoMeterReadingToTest($mot);
        } else {
            $this->odometerReadingData->addOdometerNotReadToTest($mot);
        }
    }

    /**
     * @Given /^the Tester adds an Odometer Reading of (\d+) (mi|km)$/
     * @Given the Tester adds an Odometer Reading
     */
    public function theTesterAddsAnOdometerReadingOfMiles($value = 1000, $unit = MeterReadingParams::KM)
    {
        $mot = $this->motTestData->getLast();
        $this->odometerReadingData->addMeterReading($mot, $value, $unit);

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $this->odometerReadingData->getLastResponse()->getStatusCode());
    }

    /**
     * @Given /^the Tester tries to add an Odometer Reading of (?P<value>.*) (?P<unit>.*)$/
     */
    public function theTesterTryAddAnOdometerReadingOfMiles($value = 1000, $unit = MeterReadingParams::KM)
    {
        try {
            $mot = $this->motTestData->getLast();
            $this->odometerReadingData->addMeterReading($mot, $value, $unit);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then the odometer reading is rejected
     */
    public function theOdometerReadingIsRejected()
    {
        $response = $this->odometerReadingData->getLastResponse();
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_422, $response->getStatusCode(), 'Odometer reading not rejected as expected.');
    }
}
