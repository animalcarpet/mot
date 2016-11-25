<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Api\Vehicle;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\Data\Params\VehicleParams;
use Dvsa\Mot\Behat\Support\Data\Params\VehicleClassParams;

use PHPUnit_Framework_Assert as PHPUnit;

class VehicleContext implements Context
{
    /**
     * @var Vehicle
     */
    private $vehicle;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var Response
     */
    private $vehicleDetailsResponse;

    /**
     * @var Response
     */
    private $vehicleCreateResponse;

    /**
     * @var string|null
     */
    private $vehicleId;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var SiteData
     */
    private $siteData;

    private $userData;

    private $vehicleData;

    /**
     * @param Vehicle           $vehicle
     * @param TestSupportHelper $testSupportHelper
     */
    public function __construct(
        Vehicle $vehicle,
        TestSupportHelper $testSupportHelper,
        SiteData $siteData,
        VehicleData $vehicleData,
        UserData $userData
    )
    {
        $this->vehicle = $vehicle;
        $this->testSupportHelper = $testSupportHelper;
        $this->siteData = $siteData;
        $this->vehicleData = $vehicleData;
        $this->userData = $userData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    /**
     * @When /^I Create a new Vehicle Technical Record with Class of (.*)$/
     *
     * @param $testClass
     */
    public function iCreateANewVehicleTechnicalRecordWithClassOf($testClass)
    {
        $this->createVehicle($this->sessionContext->getCurrentAccessTokenOrNull(), [VehicleParams::TEST_CLASS => $testClass]);
    }

    /**
     * Uses TestSupport to create a vehicle
     * @param $token
     * @param array $vehicleDetails
     * @return int
     */
    public function createVehicle($token, array $vehicleDetails = [])
    {
        $vehicleDetails[VehicleParams::ONE_TIME_PASSWORD] = Authentication::ONE_TIME_PASSWORD;
        $vehicleService = $this->testSupportHelper->getVehicleService();
        $this->vehicleId = $vehicleService->createWithDefaults($token, $vehicleDetails);
        return $this->getCurrentVehicleId();
    }

    /**
     * @return string
     */
    public function getCurrentVehicleClass()
    {
        return $this->getCurrentVehicleDetails()->getBody()->getData()[VehicleParams::VEHICLE_CLASS][VehicleClassParams::CODE];
    }

    /**
     * @return Array|null
     */
    public function getCurrentVehicleData()
    {
        return $this->getCurrentVehicleDetails()->getBody();
    }

    /**
     * @return string
     */
    public function getCurrentVehicleId()
    {
        if (null === $this->vehicleId && null !== $this->vehicleCreateResponse) {
            $this->vehicleId = (string) $this->vehicleCreateResponse->getBody()->getData()[VehicleParams::VEHICLE_ID];
        }

        if (null === $this->vehicleId) {
            throw new \BadMethodCallException('There is no vehicle created');
        }

        return $this->vehicleId;
    }

    /**
     * @return Response
     */
    private function getCurrentVehicleDetails()
    {
        if (null === $this->vehicleDetailsResponse && null !== $this->vehicleId) {
            $this->vehicleDetailsResponse = $this->vehicleData->getVehicleDetails($this->sessionContext->getCurrentAccessToken(), $this->vehicleId);
        }

        if (!$this->vehicleDetailsResponse) {
            throw new \BadMethodCallException('There is no vehicle created');
        }

        return $this->vehicleDetailsResponse;
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
