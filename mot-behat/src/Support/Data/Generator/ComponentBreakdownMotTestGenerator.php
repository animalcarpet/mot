<?php

namespace Dvsa\Mot\Behat\Support\Data\Generator;

use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupA;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupB;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\Params\VehicleParams;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\VehicleClassCode;

class ComponentBreakdownMotTestGenerator
{
    private $motTestData;
    private $motTestGenerator;
    private $vehicleData;

    public function __construct(MotTestData $motTestData, VehicleData $vehicleData)
    {
        $this->motTestData = $motTestData;
        $this->vehicleData = $vehicleData;
        $this->motTestGenerator = new MotTestGenerator($motTestData);
    }

    public function generate(SiteDto $site, AuthenticatedUser $tester)
    {
        $testStartedDate = "first day of previous month";
        $dateOfManufacture = new \DateTime("first day of 2 years ago");
        $motorcycleClass1 = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_1,
                VehicleParams::DATE_OF_MANUFACTURE => $dateOfManufacture->format("Y-m-d")
            ]
        );

        $this->motTestGenerator
            ->setDuration(60)
            ->setStartedDate($testStartedDate);
        $this->motTestGenerator->generatePassedMotTests($tester, $site, $motorcycleClass1);

        $dateOfManufacture = new \DateTime("first day of 4 years ago");
        $motorcycleClass2 = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_2,
                VehicleParams::DATE_OF_MANUFACTURE => $dateOfManufacture->format("Y-m-d")
            ]
        );
        $this->motTestGenerator
            ->setDuration(50)
            ->setStartedDate($testStartedDate)
            ->setRfrId(ReasonForRejectionGroupA::RFR_POSITION_LAMPS_MOTORCYCLE_FRONT);
        $this->motTestGenerator->generateFailedMotTests($tester, $site, $motorcycleClass2);

        $this->motTestGenerator
            ->setDuration(50)
            ->setStartedDate($testStartedDate)
            ->setRfrId(ReasonForRejectionGroupA::RFR_SIDECAR_SHOCK_ABSORBER_LEAKING);
        $this->motTestGenerator->generateFailedMotTestsWithAdvisories($tester, $site, $motorcycleClass2);

        $motTest = $this->motTestData->create($tester, $motorcycleClass2, $site);
        $this->motTestData->failMotTestWithManyRfrs($motTest, [ReasonForRejectionGroupA::RFR_POSITION_LAMPS_MOTORCYCLE_FRONT, ReasonForRejectionGroupA::RFR_BRAKES_PERFORMANCE_GRADIENT]);

        $dateOfManufacture = new \DateTime("first day of 14 years ago");
        $vehicleClass4 = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_4,
                VehicleParams::DATE_OF_MANUFACTURE => $dateOfManufacture->format("Y-m-d")
            ]
        );
        $this->motTestGenerator
            ->setDuration(40)
            ->setStartedDate($testStartedDate)
            ->setRfrId(ReasonForRejectionGroupB::RFR_BODY_STRUCTURE_CONDITION);
        $this->motTestGenerator->generateFailedMotTests($tester, $site, $vehicleClass4);

        $this->motTestGenerator
            ->setDuration(30)
            ->setStartedDate($testStartedDate)
            ->setRfrId(ReasonForRejectionGroupB::RFR_ROAD_WHEELS_CONDITION);
        $this->motTestGenerator->generateFailedMotTestsWithAdvisories($tester, $site, $vehicleClass4);
    }
}
