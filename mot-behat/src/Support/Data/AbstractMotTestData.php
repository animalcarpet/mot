<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
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

abstract class AbstractMotTestData extends AbstractData
{
    protected $motTest;
    private $brakeTestResultData;
    private $odometerReadingData;
    private $reasonForRejectionData;
    protected $testSupportHelper;

    protected $motCollection;

    public function __construct(
        UserData $userData,
        MotTest $motTest,
        BrakeTestResultData $brakeTestResultData,
        OdometerReadingData $odometerReadingData,
        ReasonForRejectionData $reasonForRejectionData,
        TestSupportHelper $testSupportHelper
    )
    {
        parent::__construct($userData);

        $this->motTest = $motTest;
        $this->brakeTestResultData = $brakeTestResultData;
        $this->odometerReadingData = $odometerReadingData;
        $this->reasonForRejectionData = $reasonForRejectionData;
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

        $mot = $this->hydrateResponseToDto($response);
        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function passMotTestWithAdvisory(MotTestDto $mot, $rfrId)
    {
        $this->reasonForRejectionData->addAdvisory($mot, $rfrId);
        return $this->passMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    public function failMotTestWithAdvisory(MotTestDto $mot, $rfrId)
    {
        $this->reasonForRejectionData->addAdvisory($mot, $rfrId);
        return $this->failMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    public function failMotTestWithDefaultBrakeTestAndMeterReading(MotTestDto $mot, $rfrId = null)
    {
        $this->addBrakeTestDecelerometerClass($mot);
        $this->addMeterReading($mot);

        if ($rfrId === null) {
            return $this->failMotTestWithDefaultRfr($mot);
        }

        return $this->failMotTestWithRfr($mot, $rfrId);
    }

    public function failMotTestWithRfr(MotTestDto $mot, $rfrId)
    {
        $this->reasonForRejectionData->addFailure($mot, $rfrId);

        return $this->failMotTest($mot);
    }

    public function failMotTestWithDefaultRfr(MotTestDto $mot)
    {
        $this->reasonForRejectionData->addDefaultFailure($mot);
        return $this->failMotTest($mot);
    }

    public function failMotTest(MotTestDto $mot)
    {
        $tester = $this->getTester($mot);
        $response = $this->motTest->failed($tester->getAccessToken(), $mot->getMotTestNumber());

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
            $this->reasonForRejectionData->addFailure($mot, $id);
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
            $this->reasonForRejectionData->addDefaultPrs($mot);
        } else {
            $this->reasonForRejectionData->addPrs($mot, $rfrId);
        }

        $response = $this->motTest->passed($tester->getAccessToken(), $mot->getMotTestNumber());

        $mot = $this->hydrateResponseToDto($response);
        $this->motCollection->add($mot, $mot->getMotTestNumber());

        return $mot;
    }

    public function abandonMotTestByUser(MotTestDto $mot, AuthenticatedUser $user, $cancelReasonId = 23)
    {
        $response = $this->motTest->abandon($user->getAccessToken(), $mot->getMotTestNumber(), $cancelReasonId);

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

    protected function addBrakeTestDecelerometerClass(MotTestDto $motTest)
    {
        if ($motTest->getVehicleClass()->getCode() < VehicleClassCode::CLASS_3) {
            $this->brakeTestResultData->addDefaultBrakeTestDecelerometerClass1To2($motTest);
        } else {
            $this->brakeTestResultData->addBrakeTestDecelerometerClass3To7($motTest);
        }
    }

    protected function addMeterReading(MotTestDto $motTest)
    {
        $this->odometerReadingData->addDefaultMeterReading($motTest);
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

    public function getInProgressTestByUser(AuthenticatedUser $requestor, AuthenticatedUser $tester)
    {
        $motTestNumber = $this->motTest->getInProgressTestId($requestor->getAccessToken(), $tester->getUserId());
        return $this->fetchMotTestData($requestor, $motTestNumber);
    }

    public function getInProgressTest(AuthenticatedUser $user)
    {
        return $this->getInProgressTestByUser($user, $user);
    }

    /**
     * @param $vehicleId
     * @param AuthenticatedUser $tester
     * @return MotTestDto[]
     */
    public function getTestHistory($vehicleId, AuthenticatedUser $tester)
    {
        $response = $this->motTest->getTestHistory($tester->getAccessToken(), $vehicleId);
        $history = [];

        foreach ($response->getBody()->getData() as $datum) {
            $history[] = DtoHydrator::jsonToDto($datum);
        }

        return $history;
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
