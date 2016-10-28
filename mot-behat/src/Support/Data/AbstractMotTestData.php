<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\OdometerReading;
use Dvsa\Mot\Behat\Support\Api\ReasonForRejection;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupA;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupB;
use Dvsa\Mot\Behat\Support\Data\Params\OdometerReadingParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Response;
use Zend\Http\Response as HttpResponse;
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
    protected $testSupportHelper;

    protected $motCollection;

    public function __construct(
        UserData $userData,
        MotTest $motTest,
        BrakeTestResult $brakeTestResult,
        OdometerReading $odometerReading,
        ReasonForRejection $reasonForRejection,
        TestSupportHelper $testSupportHelper
    )
    {
        $this->userData = $userData;
        $this->motTest = $motTest;
        $this->brakeTestResult = $brakeTestResult;
        $this->odometerReading = $odometerReading;
        $this->reasonForRejection = $reasonForRejection;
        $this->testSupportHelper = $testSupportHelper;
        $this->motCollection = SharedDataCollection::get(MotTestDto::class);
    }

    public function passMotTestWithDefaultBrakeTestAndMeterReading(MotTestDto $mot)
    {
        $this->addBrakeTestDecelerometerClass($mot);
        $this->addMeterReading($mot);

        return $this->passMotTest($mot);
    }

    public function passMotTest(MotTestDto $mot)
    {
        $tester = $this->getTester($mot);
        $response = $this->motTest->passed($tester->getAccessToken(), $mot->getMotTestNumber());

        if ($response->getStatusCode() !== HttpResponse::STATUS_CODE_200) {
            throw new \Exception("Something went wrong during passing mot test");
        }

        $mot = $this->hydrateResponseToDto($response);
        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function passMotTestWithAdvisory(MotTestDto $mot, $rfrId)
    {
        $tester = $this->getTester($mot);
        $this->reasonForRejection->addAdvisory($tester->getAccessToken(), $mot->getMotTestNumber(), $rfrId);
        return $this->passMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    public function failMotTestWithAdvisory(MotTestDto $mot, $rfrId)
    {
        $tester = $this->getTester($mot);
        $this->reasonForRejection->addAdvisory($tester->getAccessToken(), $mot->getMotTestNumber(), $rfrId);
        return $this->failMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    public function failMotTestWithDefaultBrakeTestAndMeterReading(MotTestDto $mot, $rfrId = null)
    {
        $this->addBrakeTestDecelerometerClass($mot);
        $this->addMeterReading($mot);

        return $this->failMotTestWithRfr($mot, $rfrId);
    }

    public function failMotTestWithRfr(MotTestDto $mot, $rfrId = null)
    {
        $tester = $this->getTester($mot);

        if ($rfrId === null) {
            $rfrId = ($mot->getVehicleClass()->getCode() < VehicleClassCode::CLASS_3)
                ? ReasonForRejectionGroupA::RFR_BRAKE_HANDLEBAR_LEVER
                : ReasonForRejectionGroupB::RFR_BODY_STRUCTURE_CONDITION;
        }

        $this->reasonForRejection->addFailure($tester->getAccessToken(), $mot->getMotTestNumber(), $rfrId);

        return $this->failMotTest($mot);
    }

    public function failMotTest(MotTestDto $mot)
    {
        $tester = $this->getTester($mot);
        $response = $this->motTest->failed($tester->getAccessToken(), $mot->getMotTestNumber());

        if ($response->getStatusCode() !== HttpResponse::STATUS_CODE_200) {
            throw new \Exception("Something went wrong during failing mot tests");
        }

        $mot = $this->hydrateResponseToDto($response);

        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function failMotTestWithManyRfrs(MotTestDto $mot, array $rfrs)
    {
        $tester = $this->getTester($mot);

        $this->addBrakeTestDecelerometerClass($mot);
        $this->addMeterReading($mot);

        foreach ($rfrs as $id) {
            $this->reasonForRejection->addFailure($tester->getAccessToken(), $mot->getMotTestNumber(), $id);
        }

        $response = $this->motTest->failed($tester->getAccessToken(), $mot->getMotTestNumber());

        $mot = $this->hydrateResponseToDto($response);

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

        $mot = $this->hydrateResponseToDto($response);
        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function abandonMotTestByUser(MotTestDto $mot, AuthenticatedUser $user, $cancelReasonId = 23)
    {
        $this->addBrakeTestDecelerometerClass($mot);
        $this->addMeterReading($mot);

        $response = $this->motTest->abandon($user->getAccessToken(), $mot->getMotTestNumber(), $cancelReasonId);
        if ($response->getStatusCode() !== HttpResponse::STATUS_CODE_200) {
            throw new \Exception(join("; ", $response->getBody()->getErrorMessages()));
        }

        $mot = $this->hydrateResponseToDto($response);
        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function abandonMotTest(MotTestDto $mot, $cancelReasonId = 23)
    {
        $tester = $this->getTester($mot);

        $this->abandonMotTestByUser($mot, $tester, $cancelReasonId);

        return $mot;
    }

    public function abortMotTestByUser(MotTestDto $mot, AuthenticatedUser $user)
    {
        return $this->abandonMotTestByUser($mot,$user, 5);
    }

    public function abortMotTest(MotTestDto $mot)
    {
        return $this->abandonMotTest($mot, 5);
    }

    public function abortMotTestByVE(MotTestDto $mot, AuthenticatedUser $user)
    {
        $response = $this->motTest->abortTestByVE($user->getAccessToken(), $mot->getMotTestNumber());
        $mot = $this->hydrateResponseToDto($response);
        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function addBrakeTestDecelerometerClass(MotTestDto $motTest)
    {
        $tester = $this->getTester($motTest);

        if ($motTest->getVehicleClass()->getCode() < VehicleClassCode::CLASS_3) {
            return $this->brakeTestResult->addBrakeTestDecelerometerClass1To2($tester->getAccessToken(), $motTest->getMotTestNumber());
        } else {
            return $this->brakeTestResult->addBrakeTestDecelerometerClass3To7($tester->getAccessToken(), $motTest->getMotTestNumber());
        }
    }

    public function addMeterReading(MotTestDto $motTest)
    {
        $tester = $this->getTester($motTest);

        $this->odometerReading->addMeterReading($tester->getAccessToken(), $motTest->getMotTestNumber(), 658, OdometerReadingParams::MI);
    }

    public function updateLatestMotTestWithNewDvlaVehicleDetails($id, array $data)
    {
        $service = $this->testSupportHelper->getMotService();
        $service->updateLatest($id, $data);
    }

    /**
     * @param int $vehicleId
     * @return int
     */
    public function getLatestMotTestIdForVehicle($vehicleId)
    {
        $service = $this->testSupportHelper->getMotService();
        $mot = $service->getLatestTest($vehicleId);

        return $mot[MotTestParams::ID];
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

    private function hydrateResponseToDto(Response $response)
    {
        /** @var MotTestDto $dto */
        $dto = DtoHydrator::jsonToDto($response->getBody()->getData());

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

    public function fetchMotTestData(AuthenticatedUser $user, $motTestNumber)
    {
        $response = $this->motTest->getMotData($user->getAccessToken(), $motTestNumber);

        $mot = $this->hydrateResponseToDto($response);
        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    /**
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->motTest->getLastResponse();
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
                SiteParams::NAME => $site->getName(),
                'address' => [],
                SiteParams::SITE_NUMBER => $site->getSiteNumber(),
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
