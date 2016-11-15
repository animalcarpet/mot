<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Vehicle;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Data\Params\VehicleParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use DvsaCommon\Dto\Vehicle\DvlaVehicleDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Utility\DtoHydrator;
use Zend\Http\Response as HttpResponse;

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
    public function createWithParams($token, array $data)
    {
        $default = VehicleParams::getDefaultParams();

        $data = array_replace($default, $data);

        $vehicleService = $this->testSupportHelper->getVehicleService();
        $id = $vehicleService->createWithDefaults($token, $data);

        $vehicleClassDto = new VehicleClassDto();
        $vehicleClassDto->setCode($data[VehicleParams::TEST_CLASS]);

        $dto = new VehicleDto();
        $dto
            ->setId($id)
            ->setVin(strtoupper($data[VehicleParams::VIN]))
            ->setRegistration(strtoupper($data[VehicleParams::REGISTRATION_NUMBER]))
            ->setVehicleClass($vehicleClassDto);

        $this->vehicleCollection->add($dto, $id);

        return $dto;
    }

    public function create($vehicleClass = VehicleClassCode::CLASS_4)
    {
        $tester = $this->userData->createTester("Bob The Builder");
        return $this->createWithVehicleClass($tester->getAccessToken(), $vehicleClass);
    }

    public function createByUser($token)
    {
        return $this->createWithParams($token, []);
    }

    public function createWithVehicleClass($token, $vehicleClass)
    {
        return $this->createWithParams($token, [VehicleParams::TEST_CLASS => $vehicleClass]);
    }

    /**
     * @param $registration
     * @param $vin
     * @return int
     */
    public function createDvlaVehicle($registration, $vin)
    {
        $service = $this->testSupportHelper->getDVLAVehicleService();

        $vehicleData = [
            VehicleParams::REGISTRATION => $registration,
            VehicleParams::VIN => $vin
        ];



        return $service->save($vehicleData);
    }

    public function createVehicleFromDvla($token, $registration, $vin)
    {
        $dvlaVehicleCollection = $this->searchDvlaVehicle($token, $registration, $vin);
        $dvlaVehicle = $dvlaVehicleCollection->last();

        $vehicleData = [
            VehicleParams::REGISTRATION_NUMBER => $registration,
            VehicleParams::VIN => $vin
        ];

        $vehicle = $this->createWithParams($token, $vehicleData);

        $dvlaVehicleService = $this->testSupportHelper->getDVLAVehicleService();
        $dvlaVehicleService->update($dvlaVehicle->getId(), ['vehicle_id' => $vehicle->getId()]);

        return $vehicle;
    }

    public function searchVehicle($token, $registration, $vin)
    {
        return $this->search($token, $registration, $vin, false);
    }

    public function searchDvlaVehicle($token, $registration, $vin)
    {
        $collection = new DataCollection(DvlaVehicleDto::class);
        $response = $this->search($token, $registration, $vin, true);
        $vehicles = $response->getBody()->getData()["vehicles"];
        foreach ($vehicles as $vehicle) {
            $dto = new DvlaVehicleDto();
            $dto->setId($vehicle[VehicleParams::ID]);
            $dto->setRegistration($vehicle[VehicleParams::REGISTRATION]);
            $dto->setVin($vehicle[VehicleParams::VIN]);

            $collection->add($dto, $dto->getId());
        }

        return $collection;
    }

    private function search($token, $registration, $vin, $isDvla)
    {
        return $this->vehicle->vehicleSearch($token, $registration, $vin, $isDvla);
    }

    public function createDvlaVehicleUpdatedCertificat($token, $vehicleId)
    {
        $this->vehicle->dvlaVehicleUpdated(
            $token,
            $vehicleId
        );
    }

    public function fetchDvlaVehicleId($registration, $vin)
    {
        $service = $this->testSupportHelper->getDVLAVehicleService();

        return $service->fetchId($registration, $vin);
    }

    public function getVehicleDetails($vehicleId, $userName)
    {
        $response = $this->vehicle->getVehicleDetails($this->userData->get($userName)->getAccessToken(), $vehicleId);

        /** @var VehicleDto $dto */
        $dto = DtoHydrator::jsonToDto($response->getBody()->getData());
        $this->vehicleCollection->add($dto, $dto->getId());
        return $dto;
    }

    public function updateDvlaVehicle($id, array $data)
    {
        $service = $this->testSupportHelper->getDVLAVehicleService();
        $service->update($id, $data);
    }

    public function update($id, array $data)
    {
        $service = $this->testSupportHelper->getVehicleService();
        $service->update($id, $data);
    }

    public function get($id)
    {
        return $this->vehicleCollection->get($id);
    }

    public function getAll()
    {
        return $this->vehicleCollection;
    }

    /**
     * @return VehicleDto
     */
    public function getLast()
    {
        return $this->getAll()->last();
    }

    public function generateRandomVin()
    {
        return $this->vehicle->randomVin();
    }

    public function generateRandomRegistration()
    {
        return $this->vehicle->randomRegNumber();
    }

    /**
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->vehicle->getLastResponse();
    }
}
