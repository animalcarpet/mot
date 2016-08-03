<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Api\Vehicle;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Datasource\Authentication;

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
     * @var Response[]
     */
    private $vehicles = [];

    /**
     * @var Response
     */
    private $searchedVehicleResponse;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @param Vehicle           $vehicle
     * @param TestSupportHelper $testSupportHelper
     */
    public function __construct(Vehicle $vehicle, TestSupportHelper $testSupportHelper)
    {
        $this->vehicle = $vehicle;
        $this->testSupportHelper = $testSupportHelper;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    /**
     * @Then the Vehicle Record is Created with an MOT Test Number allocated
     */
    public function theVehicleRecordIsCreatedWithAnMotTestNumberAllocated()
    {
        PHPUnit::assertEquals(200, $this->vehicleCreateResponse->getStatusCode());
        PHPUnit::assertTrue(isset($this->vehicleCreateResponse->getBody()['data']), 'Vehicle not created');
        PHPUnit::assertTrue(!empty($this->vehicleCreateResponse->getBody()['data']), 'Incorrect vehicle ID');
    }

    /**
     * @Then /^the Vehicle Records are Created$/
     */
    public function theVehicleRecordsAreCreated()
    {
        foreach ($this->vehicles as $item) {
            PHPUnit::assertEquals(200, $item->getStatusCode());
            PHPUnit::assertTrue(isset($item->getBody()['data']), 'Vehicle not created');
            PHPUnit::assertTrue(!empty($item->getBody()['data']), 'Incorrect vehicle ID');
        }
    }

    /**
     * @Then /^the Vehicle Record is not Created$/
     */
    public function theVehicleRecordIsNotCreated()
    {
        PHPUnit::assertEquals(400, $this->vehicleCreateResponse->getStatusCode());
        PHPUnit::assertFalse(empty($this->vehicleCreateResponse->getBody()['errors'][0]['message']));
    }

    /**
     * @When /^I Create a duplicate Vehicle Technical Record with Class of (\d+)$/
     *
     * @param $testClass
     */
    public function iCreateADuplicateVehicleTechnicalRecordWithClassOf($testClass)
    {
        $this->createVehicleFromApi(['testClass' => $testClass]);
        $this->createVehicleFromApi(['testClass' => $testClass]);
    }

    /**
     * @When /^I Create a new Vehicle Technical Record with Class of (.*)$/
     *
     * @param $testClass
     */
    public function iCreateANewVehicleTechnicalRecordWithClassOf($testClass)
    {
        $this->vehicleCreateResponse = $this->vehicle->create(
            $this->sessionContext->getCurrentAccessTokenOrNull(),
            ['testClass' => $testClass]
        );
    }

    /**
     * @When /^I Create a new Vehicle Technical Record with fuel (.*) and no cylinder capacity$/
     *
     * @param $fuelType
     */
    public function ICreateANewVehicleTechnicalRecordWithFuelTypeAndNoCylinderCapacity($fuelType)
    {
        $this->createVehicleFromApi([
            'fuelType' => $fuelType,
            'cylinderCapacity' => null
        ]);
    }

    /**
     * @When /^I Create a new Vehicle Technical Record with the following (\d+) (.*) (.*) (.*) (.*) (\d+) (.*) (.*)$/
     *
     * @param $class
     * @param $make
     * @param $model
     * @param $fuelType
     * @param $transmissionType
     * @param $countryOfRegistration
     * @param $cylinderCapacity
     * @param $dateOfFirstUse
     */
    public function iCreateANewVehicleTechnicalRecord($class, $make, $model, $fuelType, $transmissionType, $countryOfRegistration, $cylinderCapacity, $dateOfFirstUse)
    {
        $this->createVehicleFromApi(
            [
                'countryOfRegistration' => $countryOfRegistration,
                'cylinderCapacity' => $cylinderCapacity,
                'fuelType' => $fuelType,
                'make' => $make,
                'model' => $model,
                'testClass' => $class,
                'transmissionType' => $transmissionType,
                'dateOfFirstUse' => $dateOfFirstUse,
            ]
        );
    }

    /**
     * @When /^I Create a new Vehicle Technical Record with a date of first use of (.*)$/
     *
     * @param $dateOfFirstUse
     */
    public function iCreateANewVehicleTechnicalRecordWithADateOfFirstUseOf($dateOfFirstUse)
    {
        $this->createVehicleFromApi(['dateOfFirstUse' => $dateOfFirstUse]);
    }

    /**
     * @When /^I Create a new Vehicle Technical Record with the following data:$/
     *
     * @param TableNode $table
     */
    public function iCreateANewVehicleTechnicalRecordWithTheFollowingData(TableNode $table)
    {
        $hash = $table->getColumnsHash();

        if (count($hash) !== 1) {
            throw new \InvalidArgumentException(sprintf('Expected a single vehicle record but got: %d', count($hash)));
        }

        $row = $hash[0];

        $vehicleData = [
            'countryOfRegistration' => $row['countryOfRegistration'],
            'cylinderCapacity' => $row['cylinderCapacity'],
            'fuelType' => $row['fuelType'],
            'make' => $row['make'],
            'model' => $row['model'],
            'testClass' => $row['class'],
            'transmissionType' => $row['transmissionType'],
            'dateOfFirstUse' => $row['dateOfFirstUse'],
        ];

        $this->createVehicleFromApi($vehicleData);
    }

    /**
     * @When /^I Create a new Vehicle Technical Record with cylinder capacity of (.*)$/
     */
    public function iCreateANewVehicleTechnicalRecordWithCylinderCapacityOf($cylinderCapacity)
    {
        $this->createVehicleFromApi(['cylinderCapacity' => $cylinderCapacity]);
    }

    /**
     * @When /^I create a Vehicle of Class (.*) and Fuel Type (.*) and cylinder capacity (.*)$/
     *
     * @param $class
     * @param $fuelType
     */
    public function iCreateAVehicleOfClassAndFuelType($class, $fuelType, $cylinderCapacity)
    {
        $this->createVehicleFromApi(['fuelType' => $fuelType, 'testClass' => $class, 'cylinderCapacity' => $cylinderCapacity]);
    }

    /**
     * @Given /^the vehicle details are correct$/
     */
    public function theVehicleDetailsAreCorrect()
    {
        $carData = $this->vehicle->getVehicleDetails($this->sessionContext->getCurrentAccessToken(), $this->vehicleCreateResponse->getBody()['data']['vehicleId']);

        PHPUnit::assertEquals($this->vehicleCreateResponse->getBody()['data']['vehicleId'], $carData->getBody()['data']['id'], 'Vehicle id does not match.');
    }

    /**
     * @When /^I search for a vehicle by registration number "([^"]*)"$/
     *
     * @param $regNumber
     */
    public function iSearchForAVehicleByRegistrationNumber($regNumber)
    {
        $this->searchedVehicleResponse = $this->vehicle->vehicleSearch($this->sessionContext->getCurrentAccessToken(), $regNumber);
    }

    /**
     * @When /^I search for a vehicle by registration number "([^"]*)" and VIN "([^"]*)"$/
     *
     * @param $regNumber
     * @param $vin
     */
    public function iSearchForAVehicleByRegistrationNumberAndVin($regNumber, $vin)
    {
        $this->searchedVehicleResponse = $this->vehicle->vehicleSearch($this->sessionContext->getCurrentAccessToken(), $regNumber, $vin);
    }

    /**
     * @Given /^a vehicle with registration number (.*) and VIN (.*) exists$/
     *
     * @param $regNumber
     * @param $vin
     */
    public function createVehicleForSearch($regNumber, $vin)
    {
        $this->createVehicle(
            [
                'registrationNumber' => $regNumber,
                'vin' => $vin
            ]
        );
    }

    /**
     * @Then /^the vehicle with "([^"]*)" and VIN "([^"]*)" is found$/
     *
     * @param $reg
     * @param $vin
     */
    public function theVehicleRegistrationNumberIsFound($reg, $vin)
    {
        $data = $this->searchedVehicleResponse->getBody()['data'];

        if(array_key_exists('vehicles', $data)) {
            PHPUnit::assertSame($data['vehicles'][0]['registration'], $reg);
            PHPUnit::assertSame($data['vehicles'][0]['vin'], $vin);
        }
    }


    /**
     * @Then /^the vehicle registration number "([^"]*)" is returned$/
     * @Then /^the vehicle with registration "([^"]*)" is returned$/
     *
     * @param $reg
     */
    public function theVehicleRegistrationNumberIsReturned($reg)
    {
        $data = $this->searchedVehicleResponse->getBody()['data'];
        PHPUnit::assertSame($data['vehicle']['registration'], $reg);
    }

    /**
     * Creates the vehicle and automatically starts an MOT for it
     * @param array $vehicleDetails
     * @return string|null
     */
    private function createVehicleFromApi(array $vehicleDetails = [])
    {
        $this->vehicleCreateResponse = $this->vehicle->create($this->sessionContext->getCurrentAccessToken(), $vehicleDetails);

        return 200 === $this->vehicleCreateResponse->getStatusCode() ? $this->getCurrentVehicleId() : null;
    }

    /**
     * Uses TestSupport to create the vehicle
     * @param array $vehicleDetails
     * @return int
     */
    public function createVehicle(array $vehicleDetails = [])
    {
        $vehicleDetails["oneTimePassword"] = Authentication::ONE_TIME_PASSWORD;
        $vehicleService = $this->testSupportHelper->getVehicleService();
        $this->vehicleId = $vehicleService->createWithDefaults($vehicleDetails);
        return $this->getCurrentVehicleId();
    }

    /**
     * @return string
     */
    public function getCurrentVehicleClass()
    {
        return $this->getCurrentVehicleDetails()->getBody()['data']['vehicleClass']['code'];
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
            $this->vehicleId = (string) $this->vehicleCreateResponse->getBody()['data']['vehicleId'];
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
            $this->vehicleDetailsResponse = $this->vehicle->getVehicleDetails($this->sessionContext->getCurrentAccessToken(), $this->vehicleId);
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
        PHPUnit::assertSame(BrakeTestResult::VEHICLE_WEIGHT, $this->getCurrentVehicleData()['data']['weight']);
    }

    /**
     * @Then vehicle weight is not updated
     */
    public function vehicleWeightIsNotUpdated()
    {
        PHPUnit::assertSame(null, $this->getCurrentVehicleData()['data']['weight']);
    }


}
