<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\DemoTest;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestTypeCode;
use Zend\Http\Response;

class DemoMotTestData extends AbstractMotTestData
{
    private $demoTest;

    public function __construct(
        DemoTest $demoTest,
        UserData $userData,
        MotTest $motTest,
        BrakeTestResultData $brakeTestResultData,
        OdometerReadingData $odometerReadingData,
        ReasonForRejectionData $reasonForRejectionData,
        TestSupportHelper $testSupportHelper
    )
    {
        parent::__construct($userData, $motTest, $brakeTestResultData, $odometerReadingData, $reasonForRejectionData, $testSupportHelper);

        $this->demoTest = $demoTest;
    }

    public function create(AuthenticatedUser $tester, VehicleDto $vehicle)
    {
        $mot = $this
            ->demoTest
            ->startMotTest(
                $tester->getAccessToken(),
                $vehicle->getId(),
                $vehicle->getVehicleClass()->getCode()
            );

        $dto = $this->mapToMotTestDto(
            $tester,
            $vehicle,
            $mot->getBody()->getData()[MotTestParams::MOT_TEST_NUMBER],
            MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING
        );

        $this->motCollection->add($dto, $dto->getMotTestNumber());

        return $dto;
    }

    public function createPassedMotTest(AuthenticatedUser $tester, VehicleDto $vehicle)
    {
        $mot = $this->create($tester, $vehicle);
        return $this->passMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    public function createFailedMotTest(AuthenticatedUser $tester, VehicleDto $vehicle)
    {
        $mot = $this->create($tester, $vehicle);
        return $this->failMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    /**
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->demoTest->getLastResponse();
    }
}
