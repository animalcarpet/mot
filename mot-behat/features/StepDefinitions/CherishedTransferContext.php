<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DvsaCommon\Enum\VehicleClassCode;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Vehicle;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\OdometerReading;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\Params\VehicleParams;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class CherishedTransferContext implements Context
{
    private $testSupportHelper;
    private $vehicleData;
    private $siteData;
    private $userData;
    private $motTestData;
    private $dvlaRegistration;
    private $dvlaVin;

    public function __construct(
        TestSupportHelper $testSupportHelper,
        SiteData $siteData,
        UserData $userData,
        VehicleData $vehicleData,
        MotTestData $motTestData
    ) {
        $this->testSupportHelper = $testSupportHelper;
        $this->siteData = $siteData;
        $this->userData = $userData;
        $this->vehicleData = $vehicleData;
        $this->motTestData = $motTestData;
        $this->dvlaRegistration = $this->vehicleData->generateRandomRegistration();
        $this->dvlaVin = $this->vehicleData->generateRandomVin();
    }

    /**
     * @Given I have imported a dvla vehicle
     */
    public function iHaveImportedAVehicleWithRegistrationAndVinFromDvla()
    {
        $this->vehicleData->createDvlaVehicle($this->dvlaRegistration, $this->dvlaVin);
    }

    /**
     * @Given I have completed an MOT test on the vehicle
     */
    public function iHaveCompletedAnMotTestOnAVehicleWithRegistrationAndVin()
    {
        $vehicle = $this->vehicleData->createVehicleFromDvla(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->dvlaRegistration,
            $this->dvlaVin
        );

        $this->motTestData->createPassedMotTest($this->userData->getCurrentLoggedUser(), $this->siteData->get(), $vehicle);
    }

    /**
     * @When I update the vehicle to a new registration of :newRegistration
     */
    public function iUpdateTheVehicleWithRegistrationAndVinToANewRegistrationOf($newRegistration)
    {
        $dvlaVehicleId = $this->vehicleData->fetchDvlaVehicleId($this->dvlaRegistration, $this->dvlaVin);
        $this->vehicleData->updateDvlaVehicle($dvlaVehicleId, [VehicleParams::REGISTRATION => $newRegistration]);

        $vehicle = $this->getCreatedVehicle($this->dvlaRegistration, $this->dvlaVin);

        $this->vehicleData->update($vehicle->getId(), [VehicleParams::REGISTRATION => $newRegistration]);

        $this->motTestData->updateLatestMotTestWithNewDvlaVehicleDetails($vehicle->getId(), [VehicleParams::REGISTRATION => $newRegistration]);
    }

    /**
     * @When I create a cherished transfer replacement MOT certificate
     */
    public function createCherishedTransferReplacementCertificate()
    {
        $vehicle = $this->getCreatedVehicle($this->dvlaRegistration, $this->dvlaVin);
        $this->vehicleData->createDvlaVehicleUpdatedCertificat($this->userData->getCurrentLoggedUser()->getAccessToken(), $vehicle->getId());
    }

    /**
     * @When I attempt to create a cherished transfer replacement MOT certificate
     */
    public function attemptToCreateACherishedTransferReplacementCertificate()
    {
        $vehicleId = $this->completeMotTest();
        try {
            $this->vehicleData->createDvlaVehicleUpdatedCertificat($this->userData->getCurrentLoggedUser()->getAccessToken(), $vehicleId);
        } catch (\Exception $e) {

        }
    }

    /**
     * @Then a replacement certificate will be created
     */
    public function aReplacementCertificateWillBeCreated()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $this->vehicleData->getLastResponse()->getStatusCode());
    }

    /**
     * @Then I would be forbidden to create replacement
     */
    public function iWouldBeForbiddenToCreateReplacement()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_403, $this->vehicleData->getLastResponse()->getStatusCode());
    }

    /**
     * @Then a replacement certificate of type :type is created
     *
     * @param string $type
     */
    public function aReplacementCertificateOfTypeIsCreated($type)
    {
        if ($type == 'Transfer') {
            $certificateCode = 'F';
        } elseif ($type == 'Replacement') {
            $certificateCode = 'R';
        } else {
            throw new \InvalidArgumentException("Wrong certificate type");
        }

        $certificateReplacementService = $this->testSupportHelper->getCertificateReplacementService();

        $vehicle = $this->getCreatedVehicle($this->dvlaRegistration, $this->dvlaVin);

        $motId = $this->motTestData->getLatestMotTestIdForVehicle($vehicle->getId());

        $certificateType = $certificateReplacementService->getCertificateReplacementType($motId);

        PHPUnit::assertEquals($certificateType, $certificateCode);
    }

    /**
     * Create a vehicle, complete an MOT test for it and return it's id.
     *
     * @return string
     */
    private function completeMotTest()
    {
        $tester = $this->userData->createTesterWithParams([PersonParams::SITE_IDS => [$this->siteData->get()->getId()]]);
        $vehicle = $this->vehicleData->createWithVehicleClass($tester->getAccessToken(), VehicleClassCode::CLASS_4);
        $this->motTestData->createPassedMotTest($tester, $this->siteData->get(), $vehicle);

        return $vehicle->getId();
    }

    /**
     * @param $registration
     * @param $vin
     * @return VehicleDto
     */
    private function getCreatedVehicle($registration, $vin)
    {
        $collection = $this->vehicleData->getAll()->filter(function (VehicleDto $vehicle) use ($registration, $vin) {
            if ($vehicle->getRegistration() === $registration && $vehicle->getVin() === $vin) {
                return true;
            }

            return false;
        });

        return $collection->first();
    }
}
