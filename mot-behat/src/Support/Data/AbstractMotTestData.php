<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\OdometerReading;
use Dvsa\Mot\Behat\Support\Api\ReasonForRejection;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupA;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupB;
use Dvsa\Mot\Behat\Support\Response;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Utility\DtoHydrator;

abstract class AbstractMotTestData
{
    protected $userData;
    protected $motTest;
    private $brakeTestResult;
    private $odometerReading;
    private $reasonForRejection;

    protected $motCollection;

    public function __construct(
        UserData $userData,
        MotTest $motTest,
        BrakeTestResult $brakeTestResult,
        OdometerReading $odometerReading,
        ReasonForRejection $reasonForRejection
    )
    {
        $this->userData = $userData;
        $this->motTest = $motTest;
        $this->brakeTestResult = $brakeTestResult;
        $this->odometerReading = $odometerReading;
        $this->reasonForRejection = $reasonForRejection;
        $this->motCollection = new DataCollection(MotTestDto::class);
    }

    public function passMotTest(MotTestDto $mot)
    {
        $tester = $this->getTester($mot);

        $this->addBrakeTestDecelerometerClass($mot);
        $this->addMeterReading($mot);

        $response = $this->motTest->passed($tester->getAccessToken(), $mot->getMotTestNumber());

        $mot = $this->hydrateToDto($response);
        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function passMotTestWithAdvisory(MotTestDto $mot, $rfrId)
    {
        $tester = $this->getTester($mot);
        $this->reasonForRejection->addAdvisory($tester->getAccessToken(), $mot->getMotTestNumber(), $rfrId);
        $this->passMotTest($mot);
        return $mot;
    }

    public function failMotTestWithAdvisory(MotTestDto $mot, $rfrId)
    {
        $tester = $this->getTester($mot);
        $this->reasonForRejection->addAdvisory($tester->getAccessToken(), $mot->getMotTestNumber(), $rfrId);
        $this->failMotTest($mot);
        return $mot;
    }

    public function failMotTest(MotTestDto $mot, $rfrId = null)
    {
        $tester = $this->getTester($mot);

        $this->addBrakeTestDecelerometerClass($mot);
        $this->addMeterReading($mot);

        if ($rfrId === null) {
            $rfrId = ($mot->getVehicleClass()->getCode() < 3)
                ? ReasonForRejectionGroupA::RFR_BRAKE_HANDLEBAR_LEVER
                : ReasonForRejectionGroupB::RFR_BODY_STRUCTURE_CONDITION;
        }

        $this->reasonForRejection->addFailure($tester->getAccessToken(), $mot->getMotTestNumber(), $rfrId);
        $response = $this->motTest->failed($tester->getAccessToken(), $mot->getMotTestNumber());

        $mot = $this->hydrateToDto($response);

        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function failMotTestWithPrs(MotTestDto $mot, $rfrId = null)
    {
        $tester = $this->getTester($mot);

        $this->addBrakeTestDecelerometerClass($mot);
        $this->addMeterReading($mot);

        if ($rfrId === null) {
            $rfrId = ($mot->getVehicleClass()->getCode() < VehicleClassCode::CLASS_3)
                ? ReasonForRejectionGroupA::RFR_BRAKE_HANDLEBAR_LEVER
                : ReasonForRejectionGroupB::RFR_BODY_STRUCTURE_CONDITION;
        }

        $this->reasonForRejection->addPrs($tester->getAccessToken(), $mot->getMotTestNumber(), $rfrId);
        $response = $this->motTest->passed($tester->getAccessToken(), $mot->getMotTestNumber());

        $mot = $this->hydrateToDto($response);
        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function abandonMotTest(MotTestDto $mot, $cancelReasonId = 23)
    {
        $tester = $this->getTester($mot);

        $this->addBrakeTestDecelerometerClass($mot);
        $this->addMeterReading($mot);

        $response = $this->motTest->abandon($tester->getAccessToken(), $mot->getMotTestNumber(), $cancelReasonId);

        $mot = $this->hydrateToDto($response);
        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function abortMotTest(MotTestDto $mot)
    {
        return $this->abandonMotTest($mot, 5);
    }

    protected function addBrakeTestDecelerometerClass(MotTestDto $motTest)
    {
        $tester = $this->getTester($motTest);

        if ($motTest->getVehicleClass()->getCode() < 3) {
            return $this->brakeTestResult->addBrakeTestDecelerometerClass1To2($tester->getAccessToken(), $motTest->getMotTestNumber());
        } else {
            return $this->brakeTestResult->addBrakeTestDecelerometerClass3To7($tester->getAccessToken(), $motTest->getMotTestNumber());
        }
    }

    protected function addMeterReading(MotTestDto $motTest)
    {
        $tester = $this->getTester($motTest);

        $this->odometerReading->addMeterReading($tester->getAccessToken(), $motTest->getMotTestNumber(), 658, 'mi');
    }

    /**
     * @param MotTestDto $mot
     * @return AuthenticatedUser
     */
    protected function getTester(MotTestDto $mot)
    {
        return $this
            ->userData
            ->get($mot->getTester()->getUsername());
    }

    private function hydrateToDto(Response $response)
    {
        /** @var MotTestDto $dto */
        $dto = DtoHydrator::jsonToDto($response->getBody()->toArray()["data"]);

        return $dto;
    }

    public function get($motTestNumber)
    {
        return $this->motCollection->get($motTestNumber);
    }

    public function tryGet($motTestNumber)
    {
        return $this->motCollection->tryGet($motTestNumber);
    }

    public function getMotCollection()
    {
        return $this->motCollection;
    }

    protected function mapToMotTestDto(
        AuthenticatedUser $tester,
        VehicleDto $vehicle,
        $motTestNumber,
        $motTestType,
        SiteDto $site = null
    )
    {
        $personDto = new PersonDto();
        $personDto
            ->setId($tester->getUserId())
            ->setUsername($tester->getUsername());

        $typeDto = new MotTestTypeDto();
        $typeDto->setCode($motTestType);

        $vts = [];
        if ($site !== null) {
            $vts = [
                'name' => $site->getName(),
                'address' => [],
                'siteNumber' => $site->getSiteNumber(),
            ];
        }

        $dto = new MotTestDto();
        $dto
            ->setMotTestNumber($motTestNumber)
            ->setTester($personDto)
            ->setVehicle($vehicle)
            ->setVehicleClass($vehicle->getVehicleClass())
            ->setTestType($typeDto)
            ->setVehicleTestingStation($vts);

        return $dto;
    }
}
