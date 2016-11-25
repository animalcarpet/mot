<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\MysteryShopperTest;
use Dvsa\Mot\Behat\Support\Api\OdometerReading;
use Dvsa\Mot\Behat\Support\Api\ReasonForRejection;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestTypeCode;
use Zend\Http\Response as HttpResponse;

class MysteryShopperMotTestData extends AbstractMotTestData
{
    private $mysteryShopperTest;

    public function __construct(
        MysteryShopperTest $mysteryShopperTest,
        UserData $userData,
        MotTest $motTest,
        BrakeTestResultData $brakeTestResultData,
        OdometerReadingData $odometerReadingData,
        ReasonForRejectionData $reasonForRejectionData,
        TestSupportHelper $testSupportHelper
    )
    {
        parent::__construct($userData, $motTest, $brakeTestResultData, $odometerReadingData, $reasonForRejectionData, $testSupportHelper);

        $this->mysteryShopperTest = $mysteryShopperTest;
    }

    public function create(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site)
    {
        $mot = $this
            ->mysteryShopperTest
            ->startMOTTest(
                $tester->getAccessToken(),
                $vehicle->getId(),
                $site->getId(),
                $vehicle->getVehicleClass()->getCode()
            );

        if ($mot->getStatusCode() !== HttpResponse::STATUS_CODE_200) {
            throw new \Exception("Something went wrong during creating mot test");
        }

        $dto = $this->mapToMotTestDto(
            $tester,
            $vehicle,
            $mot->getBody()->getData()[MotTestParams::MOT_TEST_NUMBER],
            MotTestTypeCode::MYSTERY_SHOPPER,
            $site
        );

        $this->motCollection->add($dto, $dto->getMotTestNumber());

        return $dto;
    }
}
