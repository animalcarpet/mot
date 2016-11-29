<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\Vehicle;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Data\UserData;

use PHPUnit_Framework_Assert as PHPUnit;

class VehicleContext implements Context
{
    private $siteData;

    private $userData;

    private $vehicleData;

    public function __construct(
        Vehicle $vehicle,
        SiteData $siteData,
        VehicleData $vehicleData,
        UserData $userData
    )
    {
        $this->siteData = $siteData;
        $this->vehicleData = $vehicleData;
        $this->userData = $userData;
    }

    /**
     * @When /^I Create a new Vehicle Technical Record with Class of (.*)$/
     *
     * @param $testClass
     */
    public function iCreateANewVehicleTechnicalRecordWithClassOf($testClass)
    {
        $this->vehicleData->createWithVehicleClass($this->userData->getCurrentLoggedUser()->getAccessToken(), $testClass);
    }

    /**
     * @Then vehicle weight is updated
     */
    public function vehicleWeightIsUpdated()
    {
        $vehicle = $this->vehicleData->getAll()->last();
        $vehicleDetails = $this->vehicleData->getVehicleDetails($vehicle->getId(), $this->userData->getCurrentLoggedUser()->getUsername());
        PHPUnit::assertSame(BrakeTestResult::VEHICLE_WEIGHT, $vehicleDetails->getWeight());
    }

    /**
     * @Then vehicle weight is not updated
     */
    public function vehicleWeightIsNotUpdated()
    {
        $vehicle = $this->vehicleData->getAll()->last();
        $vehicleDetails = $this->vehicleData->getVehicleDetails($vehicle->getId(), $this->userData->getCurrentLoggedUser()->getUsername());
        PHPUnit::assertSame(null, $vehicleDetails->getWeight());
    }

    /**
     * @Given I Create a new vehicle
     */
    public function iCreateANewVehicle()
    {
        $this->vehicleData->createByUser($this->userData->getCurrentLoggedUser()->getAccessToken());
    }
}
