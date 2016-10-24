<?php

namespace TestSupport\Service;

use Dvsa\Mot\ApiClient\Service\VehicleService as NewVehicleService;
use Dvsa\Mot\ApiClient\Request\CreateDvsaVehicleRequest;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Helper\TestSupportAccessTokenManager;

class VehicleService
{
    const KEY_VEHICLE = 'vehicle';
    const KEY_MODEL_DETAIL = 'modelDetail';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var TestSupportAccessTokenManager
     */
    private $tokenManager;

    /**
     * @var array
     */
    private $newVehicleServiceAdditionalConfig;

    /**
     * VehicleService constructor.
     * @param EntityManager $entityManager
     * @param TestSupportAccessTokenManager $tokenManager
     * @param string $vehicleServiceUrl java vehicle-service url
     */
    public function __construct(
        EntityManager $entityManager,
        TestSupportAccessTokenManager $tokenManager,
        $vehicleServiceUrl
    )
    {
        $this->entityManager = $entityManager;
        $this->tokenManager = $tokenManager;

        $this->newVehicleServiceAdditionalConfig = [
            'http_client' => [
                'base_uri' =>  $vehicleServiceUrl
            ]
        ];
    }

    /**
     * Uses save internally to minimise what data is needed to be provided
     * @param array $overrideData
     * @return int
     */
    public function createWithDefaults(array $overrideData = [])
    {
        $defaults = [
            'countryOfRegistrationId' => 1, //GB
            'makeId' => 100024, //BMW
            'modelId' => 104420, //Alpina
            'transmissionTypeId' => 1, //automatic
            'colourCode' => "C", //red
            'secondaryColourCode' => "C", //red
            'cylinderCapacity' => 1300, //red
            'fuelTypeCode' => "PE", //petrol
            'testClass' => 4,
            // Not required for a save but required for the API
            'registrationNumber' => 'ABCD123',
            'vin' => 'VIN123456789',
            'dateOfRegistration' => '1980-01-01',
            'dateOfManufacture' => '1980-01-01',
            'dateOfFirstUse' => '1980-01-01',
            'oneTimePassword' => '123456'
        ];

        $data = array_merge($defaults, $overrideData);

        $vehicleService = $this->getNewVehicleServiceForUser('tester3', 'Password1');

        $createDvsaVehicleRequest = new CreateDvsaVehicleRequest();
        $createDvsaVehicleRequest
            -> setModelId($data['modelId'])
            -> setMakeId($data['makeId'])
            -> setTransmissionTypeId($data['transmissionTypeId'])
            -> setRegistration($data['registrationNumber'])
            -> setVin($data['vin'])
            -> setCountryOfRegistrationId($data['countryOfRegistrationId'])
            -> setColourCode($data['colourCode'])
            -> setSecondaryColourCode($data['secondaryColourCode'])
            -> setFuelTypeCode($data['fuelTypeCode'])
            -> setFirstUsedDate(new \DateTime($data['dateOfFirstUse']))
            -> setCylinderCapacity($data['cylinderCapacity'])
            -> setOneTimePassword($data['oneTimePassword'])
            -> setVehicleClassCode($data['testClass']);

        $vehicleId = $vehicleService->createDvsaVehicle($createDvsaVehicleRequest)->getId();

        $connection = $this->entityManager->getConnection();
        $updateDatesSql = "UPDATE vehicle SET manufacture_date = '" . $data['dateOfManufacture'] . "'";
        $updateDatesSql .= " ,first_registration_date = '" . $data['dateOfRegistration'] . "'";
        $updateDatesSql .= " WHERE id = " . $vehicleId . ";";
        $connection->prepare($updateDatesSql)->execute();

        return $vehicleId;
    }

    /**
     * @param array $data
     * @return int
     */
    public function save(array $data)
    {
        $preparedData = $this->prepare($data);
        $modelDetailData = $preparedData[self::KEY_MODEL_DETAIL];
        $vehicleData = $preparedData[self::KEY_VEHICLE];

        $connection = $this->entityManager->getConnection();
        $connection->insert('model_detail', $modelDetailData);
        $vehicleData['model_detail_id'] = $connection->lastInsertId();

        $types = [];
        if (is_null($vehicleData['manufacture_date']))
        {
            $types['manufacture_date'] = \PDO::PARAM_NULL;
        }

        $connection->executeQuery(
            $this->prepareInsertQuery($vehicleData),
            [],
            $types
        );

        return (int)$connection->lastInsertId();
    }

    /**
     * @param string $id
     * @param array $data
     */
    public function update($id, $data)
    {
        $this->entityManager->getConnection()->update(
            'vehicle', $data, ['id' => $id]
        );
    }

    private function prepare(array $data)
    {
        $vehicleData = [
            self::KEY_VEHICLE => [],
            self::KEY_MODEL_DETAIL => []
        ];

        $createdBy = ArrayUtils::tryGet($data, 'createdBy', 2);

        $stmt = $this->entityManager->getConnection()->prepare($this->getSql("model"));
        $stmt->bindValue("code", ArrayUtils::tryGet($data, 'model'));
        $stmt->execute();
        $modelId = $stmt->fetchColumn();

        $vehicleData[self::KEY_MODEL_DETAIL] = [
            'body_type_id' => $this->fetchId("body_type", ArrayUtils::tryGet($data, 'bodyType')),
            'created_by' => $createdBy,
            'fuel_type_id' => $this->fetchId("fuel_type", ArrayUtils::tryGet($data, 'fuelType')),
            'model_id' => $modelId,
            'transmission_type_id' => $this->fetchId("transmission_type", ArrayUtils::tryGet($data, 'transmissionType')),
            'vehicle_class_id' => $this->fetchId("vehicle_class", ArrayUtils::tryGet($data, 'testClass')),
        ];

        $vehicleData[self::KEY_VEHICLE] = [
            'country_of_registration_id' => $this->fetchId(
                "country_of_registration_lookup",
                ArrayUtils::tryGet($data, 'countryOfRegistration')
            ),
            'created_by' => $createdBy,
            'first_registration_date' => ArrayUtils::tryGet($data, 'firstRegistrationDate', null),
            'first_used_date' => ArrayUtils::tryGet($data, 'dateOfFirstUse'),
            'is_new_at_first_reg' => ArrayUtils::tryGet($data, 'newAtFirstReg', 0),
            'manufacture_date' => ArrayUtils::tryGet($data, 'manufactureDate', null),
            'primary_colour_id' => $this->fetchId("colour_lookup", ArrayUtils::tryGet($data, 'colour')),
            'registration' => ArrayUtils::tryGet($data, 'registrationNumber'),
            'secondary_colour_id' => $this->fetchId("colour_lookup", ArrayUtils::tryGet($data, 'secondaryColour')),
            'vin' => ArrayUtils::tryGet($data, 'vin'),
            'weight' => ArrayUtils::tryGet($data, "weight", 1000),
        ];

        return $vehicleData;
    }

    private function getSql($table)
    {
        return sprintf("SELECT `id` FROM `%s` where `code` = :code", $table);
    }

    private function fetchId($table, $code)
    {
        $connection = $this->entityManager->getConnection();
        $stmt = $connection->prepare($this->getSql($table));
        $stmt->bindValue("code", $code);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    private function prepareInsertQuery($vehicleData)
    {
        $countryOfRegistrationId = $vehicleData['country_of_registration_id'];
        $createdBy = $vehicleData['created_by'];
        $firstRegistrationDate = $vehicleData['first_registration_date'];
        $firstUsedDate = $vehicleData['first_used_date'];
        $isNewAtFirstReg = $vehicleData['is_new_at_first_reg'];
        $manufactureDate = $vehicleData['manufacture_date'];
        $modelDetailId = $vehicleData['model_detail_id'];
        $primaryColourId = $vehicleData['primary_colour_id'];
        $registration = $vehicleData['registration'];
        $secondaryColourId = $vehicleData['secondary_colour_id'];
        $vin = $vehicleData['vin'];
        $weight = $vehicleData['weight'];

        $manufactureDate == null ? null : '\'' . $manufactureDate . '\'';

        $sql = <<<EOF
INSERT INTO `vehicle` (
        `vin`,
        `vin_collapsed`,
        `registration`,
        `registration_collapsed`,
        `model_detail_id`,
        `first_registration_date`,
        `first_used_date`,
        `primary_colour_id`,
        `secondary_colour_id`,
        `country_of_registration_id`,
        `is_new_at_first_reg`,
        `is_damaged`,
        `is_destroyed`,
        `is_incognito`,
        `created_by`,
        `created_on`,
        `version`,
        `weight`
) VALUES (
        '$vin',
        f_collapse('$vin'),
        '$registration',
        f_collapse('$registration'),
        $modelDetailId,
        '$firstRegistrationDate',
        '$firstUsedDate',
        $primaryColourId,
        $secondaryColourId,
        $countryOfRegistrationId,
        $isNewAtFirstReg,
        0,
        0,
        0,
        $createdBy,
        CURRENT_TIMESTAMP(6),
        1,
        $weight
);
EOF;
        return $sql;
    }

    /**
     * @param string $username
     * @param string $password
     * @return NewVehicleService
     * @throws \Exception
     */
    private function getNewVehicleServiceForUser($username, $password)
    {
        $token = $this->tokenManager->getToken($username, $password);

        $newVehicleService = new NewVehicleService(
            $token,
            $this->newVehicleServiceAdditionalConfig
        );

        return $newVehicleService;
    }
}
