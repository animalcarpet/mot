<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Vehicle;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Utility\DtoHydrator;
use TestSupport\Helper\DataGeneratorHelper;

class VehicleData
{
    private $vehicle;
    private $userData;
    private $siteData;
    private $testSupportHelper;

    private $vehicleCollection;

    public function __construct(UserData $userData, SiteData $siteData, Vehicle $vehicle, TestSupportHelper $testSupportHelper)
    {
        $this->userData = $userData;
        $this->siteData = $siteData;
        $this->vehicle = $vehicle;
        $this->testSupportHelper = $testSupportHelper;
        $this->vehicleCollection = SharedDataCollection::get(VehicleDto::class);
    }

    /**
     * @param array $data
     * @return VehicleDto
     */
    public function create(array $data = [])
    {
        $dataGenerator = DataGeneratorHelper::buildForDifferentiator([]);

        $default = [
            "registrationNumber" => $dataGenerator->generateRandomString(7),
            "vin" => $dataGenerator->generateRandomString(17),
            "testClass" => VehicleClassCode::CLASS_4
        ];

        $data = array_replace($default, $data);

        $vehicleService = $this->testSupportHelper->getVehicleService();
        $id = $vehicleService->createWithDefaults($data);

        $vehicleClassDto = new VehicleClassDto();
        $vehicleClassDto->setCode($data["testClass"]);

        $dto = new VehicleDto();
        $dto
            ->setId($id)
            ->setVin($data["vin"])
            ->setRegistration($data["registrationNumber"])
            ->setVehicleClass($vehicleClassDto);

        $this->vehicleCollection->add($dto, $id);

        return $dto;
    }

    public function getVehicleDetails($vehicleId, $userName)
    {
        $response = $this->vehicle->getVehicleDetails($this->userData->get($userName)->getAccessToken(), $vehicleId);

        /** @var VehicleDto $dto */
        $dto = DtoHydrator::jsonToDto($response->getBody()->toArray()["data"]);
        $this->vehicleCollection->add($dto, $dto->getId());
        return $dto;
    }

    public function createWithVehicleClass($vehicleClass)
    {
        return $this->create(["testClass" => $vehicleClass]);
    }

    public function get($id)
    {
        return $this->vehicleCollection->get($id);
    }

    public function getAll()
    {
        return $this->vehicleCollection;
    }
}
