<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestTypeCode;

class NormalMotTestData extends AbstractMotTestData
{
    public function create(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site)
    {
        $mot = $this
            ->motTest
            ->startMOTTest(
                $tester->getAccessToken(),
                $vehicle->getId(),
                $vehicle->getVehicleClass()->getCode(),
                ["vehicleTestingStationId" => $site->getId()]
            );

        $dto = $this->mapToMotTestDto(
            $tester,
            $vehicle,
            $mot->getBody()->toArray()["data"]["motTestNumber"],
            MotTestTypeCode::NORMAL_TEST,
            $site
        );

        $this->motCollection->add($dto, $dto->getMotTestNumber());

        return $dto;
    }

    public function createPassedMotTest(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site)
    {
        $mot = $this->create($tester, $vehicle, $site);
        return $this->passMotTest($mot);
    }

    public function createFailedMotTest(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site)
    {
        $mot = $this->create($tester, $vehicle, $site);
        return $this->failMotTest($mot);
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
