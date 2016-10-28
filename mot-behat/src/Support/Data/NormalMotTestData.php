<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestParams;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestTypeCode;
use Zend\Http\Response as HttpResponse;

class NormalMotTestData extends AbstractMotTestData
{
    public function create(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site)
    {
        $mot = $this
            ->motTest
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
            MotTestTypeCode::NORMAL_TEST,
            $site
        );

        $this->motCollection->add($dto, $dto->getMotTestNumber());

        return $dto;
    }

    public function createPassedMotTest(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site)
    {
        $mot = $this->create($tester, $vehicle, $site);
        return $this->passMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    public function createFailedMotTest(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site)
    {
        $mot = $this->create($tester, $vehicle, $site);
        return $this->failMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    public function createFailedMotTestWithManyRfrs(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site, array $rfrs)
    {
        $mot = $this->create($tester, $vehicle, $site);
        return $this->failMotTestWithManyRfrs($mot, $rfrs);
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
}
