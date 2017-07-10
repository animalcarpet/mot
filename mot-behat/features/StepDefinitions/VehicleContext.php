<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Vehicle;
use Dvsa\Mot\Behat\Support\Data\Params\VehicleParams;
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
    ) {
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
        $vehicleId = $this->vehicleData->getAll()->last()->getId();
        $vehicleDetails = $this->vehicleData->getVehicleDetails($vehicleId, $this->userData->getCurrentLoggedUser()->getUsername());
        PHPUnit::assertNotEquals(VehicleParams::WEIGHT_VALUE, $vehicleDetails->getWeight());
    }

    /**
     * @Then vehicle weight source is updated
     */
    public function vehicleWeightSourceIsUpdated()
    {
        $vehicleId = $this->vehicleData->getAll()->last()->getId();
        $vehicleDetails = $this->vehicleData->getVehicleDetails($vehicleId, $this->userData->getCurrentLoggedUser()->getUsername());
        PHPUnit::assertNotEquals(VehicleParams::WEIGHT_SOURCE_VALUE, $vehicleDetails->getWeightSource());
    }

    /**
     * @Then vehicle weight is not updated
     */
    public function vehicleWeightIsNotUpdated()
    {
        $vehicleId = $this->vehicleData->getAll()->last()->getId();
        $vehicleDetails = $this->vehicleData->getVehicleDetails($vehicleId, $this->userData->getCurrentLoggedUser()->getUsername());
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
