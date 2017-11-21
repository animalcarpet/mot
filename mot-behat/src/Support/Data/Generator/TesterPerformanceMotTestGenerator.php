<?php

namespace Dvsa\Mot\Behat\Support\Data\Generator;

use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\EuReasonForRejectionToggle;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupB\GroupBEuReasonForRejection;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\ReasonForRejection;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\Params\VehicleParams;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\MotTestTypeCode;
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
        if (EuReasonForRejectionToggle::isEnabled()) {
            $this->generateForEuRfrs($site, $tester);
        }

        $vehicle = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_1
            ]
        );
        $this->motTestGenerator
            ->setDuration(60)
            ->setStartedDate("first day of 1 months ago")
            ->setRfrId(null)
        ;
        $this->motTestGenerator->generateMotTests($tester, $site, $vehicle);

        $vehicle = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_1
            ]
        );
        $motTest = $this->motTestData->create($tester, $vehicle, $site);
        $this->motTestData->failMotTestWithManyRfrs($motTest, [ReasonForRejection::getGroupA()->getForClass1(), ReasonForRejection::getGroupA()->getForClass1Advisory()]);

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
            ->setRfrId(ReasonForRejection::getGroupB()->getForClass4Advisory());
        $this->motTestGenerator->generateFailedMotTestsWithAdvisories($tester, $site, $vehicle);

        $vehicle = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_5
            ]
        );
        $this->motTestData->createPassedMotTest($tester, $site, $vehicle, MotTestTypeCode::MYSTERY_SHOPPER);
    }

    private function generateForEuRfrs(SiteDto $site, AuthenticatedUser $tester)
    {
        $vehicle = $this->vehicleData->createWithParams(
            $tester->getAccessToken(),
            [
                VehicleParams::TEST_CLASS => VehicleClassCode::CLASS_4
            ]
        );
        $this->motTestGenerator
            ->setDuration(30)
            ->setStartedDate("first day of 2 months ago")
            ->setRfrId((new GroupBEuReasonForRejection())->getForClass4Dangerous());
        $this->motTestGenerator->generateFailedMotTests($tester, $site, $vehicle);
    }

    public function generateTQIMysqlReport(AuthenticatedUser $tester, $monthsAgo = 1)
    {
        $this->motTestData->generateTQIReport($tester, $monthsAgo);
    }
}
