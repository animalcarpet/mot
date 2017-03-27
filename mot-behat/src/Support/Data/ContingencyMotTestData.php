<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\ContingencyTest;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\OdometerReading;
use Dvsa\Mot\Behat\Support\Api\ReasonForRejection;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestTypeCode;

class ContingencyMotTestData extends AbstractMotTestData
{
    private $contingencyData;
    private $contingencyTest;

    public function __construct(
        ContingencyData $contingencyData,
        ContingencyTest $contingencyTest,
        UserData $userData,
        MotTest $motTest,
        BrakeTestResultData $brakeTestResultData,
        OdometerReadingData $odometerReadingData,
        ReasonForRejectionData $reasonForRejectionData,
        TestSupportHelper $testSupportHelper
    )
    {
        parent::__construct($userData, $motTest, $brakeTestResultData, $odometerReadingData, $reasonForRejectionData, $testSupportHelper);

        $this->contingencyData = $contingencyData;
        $this->contingencyTest = $contingencyTest;
    }

    public function create(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site, array $contingencyParams = [])
    {
        $contingencyParams[SiteParams::SITE_NAME] = $site->getName();
        $this->contingencyData->create($tester, $contingencyParams);
        $emergencyLogId = $this->contingencyData->getEmergencyLogId($site->getName());

        $mot = $this->contingencyTest->startContingencyTest(
            $tester->getAccessToken(),
            $emergencyLogId,
            $vehicle->getId(),
            $vehicle->getVehicleClass()->getCode(),
            $site->getId()
        );

        $dto = $this->mapToMotTestDto(
            $tester,
            $vehicle,
            $mot->getBody()->getData()[MotTestParams::MOT_TEST_NUMBER],
            MotTestTypeCode::NORMAL_TEST,
            $site
        );

        $dto->setEmergencyLog(['id' => $emergencyLogId]);

        $this->motCollection->add($dto, $dto->getMotTestNumber());

        return $dto;
    }

    public function createPassedMotTest(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site)
    {
        $mot = $this->create($tester, $vehicle, $site);
        return $this->passMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    public function createFailedMotTest(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site, $contingencyParams = [])
    {
        $mot = $this->create($tester, $vehicle, $site, $contingencyParams);
        return $this->failMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    public function createFailedMotTestWithPrs(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site, $rfrId = null)
    {
        $mot = $this->create($tester, $vehicle, $site);
        return $this->failMotTestWithPrs($mot, $rfrId);
    }

    public function createAbandonedMotTest(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site, $rfrId = 23)
    {
        $mot = $this->create($tester, $vehicle, $site);
        return $this->abandonMotTest($mot, $rfrId);
    }

    public function createAbortedMotTest(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site)
    {
        $mot = $this->create($tester, $vehicle, $site);
        return $this->abortMotTest($mot);
    }

    public function getLastResponse()
    {
        return $this->contingencyTest->getLastResponse();
    }
}
