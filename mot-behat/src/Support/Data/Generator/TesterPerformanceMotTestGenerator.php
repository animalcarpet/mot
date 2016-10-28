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

class TesterPerformanceMotTestGenerator
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
        $vehicle = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_1
            ]
        );
        $this->motTestGenerator
            ->setDuration(60)
            ->setStartedDate("first day of 1 months ago");
        $this->motTestGenerator->generateMotTests($tester, $site, $vehicle);

        $vehicle = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_1
            ]
        );
        $motTest = $this->motTestData->create($tester, $vehicle, $site);
        $this->motTestData->failMotTestWithManyRfrs($motTest, [ReasonForRejectionGroupA::RFR_POSITION_LAMPS_MOTORCYCLE_FRONT, ReasonForRejectionGroupA::RFR_BRAKES_PERFORMANCE_GRADIENT]);

        $vehicle = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_4
            ]
        );
        $this->motTestGenerator
            ->setDuration(70)
            ->setStartedDate("first day of 2 months ago");
        $this->motTestGenerator->generateMotTests($tester, $site, $vehicle);

        $vehicle = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_4
            ]
        );
        $this->motTestGenerator
            ->setDuration(30)
            ->setStartedDate("first day of 2 months ago")
            ->setRfrId(ReasonForRejectionGroupB::RFR_ROAD_WHEELS_CONDITION);
        $this->motTestGenerator->generateFailedMotTestsWithAdvisories($tester, $site, $vehicle);
    }
}
