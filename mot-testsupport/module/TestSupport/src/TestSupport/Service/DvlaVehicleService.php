<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\DvlaVehicle;

class DvlaVehicleService
{
    const TABLE_NAME = 'dvla_vehicle';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $data
     * @param bool $returnVehicleDetail as oppose to returning the the vehicle's ID only
     * @return int
     * @throws NotFoundException
     */
    public function save(array $data, $returnVehicleDetail = false)
    {
        $vehicleData = $this->prepare($data);
        $this->getConnection()->insert('dvla_vehicle', $vehicleData);
        $vehicleId = (int)$this->getConnection()->lastInsertId();

        if (!isset($data['dvla_vehicle_id'])) {
            $this->getConnection()->update(
                self::TABLE_NAME,
                ['dvla_vehicle_id' => $vehicleId],
                ['id' => $vehicleId]
            );
        }

        if ($returnVehicleDetail) {
            $createdDvlaVehicle = $this->getConnection()->fetchAll(
                'SELECT * FROM dvla_vehicle WHERE id = ?', [$vehicleId]
            );

            if (empty($createdDvlaVehicle)) {
                throw new NotFoundException(
                    sprintf("Failed to fetch just careetd DVLA vehicle with id %!", $vehicleId)
                );
            }

            return $createdDvlaVehicle[0];
        }

        return $vehicleId;
    }

    private function prepare(array $data)
    {
        $vehicleData = [];

        $vehicleData["registration"] = ArrayUtils::tryGet($data, 'registration', '10FE3RK');
        $vehicleData["registration_validation_character"] = ArrayUtils::tryGet($data, 'registration_validation_character', '+');
        $vehicleData["vin"] = ArrayUtils::tryGet($data, 'vin', 'QZ7SEZH5CMZDZC5G8');
        $vehicleData["model_code"] = ArrayUtils::tryGet($data, 'model_code');
        $vehicleData["make_code"] = ArrayUtils::tryGet($data, 'make_code');
        $vehicleData["make_in_full"] = ArrayUtils::tryGet($data, 'make_in_full');
        $vehicleData["colour_1_code"] = ArrayUtils::tryGet($data, 'colour_1_code', 's');
        $vehicleData["colour_2_code"] = ArrayUtils::tryGet($data, 'colour_2_code', 's');
        $vehicleData["propulsion_code"] = ArrayUtils::tryGet($data, 'propulsion_code', 1);
        $vehicleData["designed_gross_weight"] = ArrayUtils::tryGet($data, 'designed_gross_weight', 1);
        $vehicleData["unladen_weight"] = ArrayUtils::tryGet($data, 'unladen_weight', 1327);
        $vehicleData["engine_number"] = ArrayUtils::tryGet($data, 'engine_number', 'BBB 1231231');
        $vehicleData["engine_capacity"] = ArrayUtils::tryGet($data, 'engine_capacity', 6590);
        $vehicleData["seating_capacity"] = ArrayUtils::tryGet($data, 'seating_capacity', 5);
        $vehicleData["manufacture_date"] = ArrayUtils::tryGet($data, 'manufacture_date', '2014-12-01');
        $vehicleData["first_registration_date"] = ArrayUtils::tryGet($data, 'first_registration_date', '2015-01-01');
        $vehicleData["is_seriously_damaged"] = ArrayUtils::tryGet($data, 'is_seriously_damaged', 0);
        $vehicleData["recent_v5_document_number"] = ArrayUtils::tryGet($data, 'recent_v5_document_number', '12312312312');
        $vehicleData["is_vehicle_new_at_first_registration"] = ArrayUtils::tryGet($data, 'is_vehicle_new_at_first_registration', 1);
        $vehicleData["body_type_code"] = ArrayUtils::tryGet($data, 'body_type_code', 'h');
        $vehicleData["wheelplan_code"] = ArrayUtils::tryGet($data, 'wheelplan_code', 'c');
        $vehicleData["sva_emission_standard"] = ArrayUtils::tryGet($data, 'sva_emission_standard');
        $vehicleData["ct_related_mark"] = ArrayUtils::tryGet($data, 'ct_related_mark');
        $vehicleData["vehicle_id"] = ArrayUtils::tryGet($data, 'vehicle_id');
        $vehicleData["dvla_vehicle_id"] = ArrayUtils::tryGet($data, 'dvla_vehicle_id');
        $vehicleData["eu_classification"] = ArrayUtils::tryGet($data, 'eu_classification');
        $vehicleData["mass_in_service_weight"] = ArrayUtils::tryGet($data, 'mass_in_service_weight');
        $vehicleData['created_by'] = ArrayUtils::tryGet($data, 'created_by', 2);

        return $vehicleData;
    }

    /**
     * Update the dvla_vehicle entry with the associated $id with $data
     * @param int $id
     * @param array $data
     */
    public function update($id, $data)
    {
        //update vehicle with new data
        $this->getConnection()->update(
            'dvla_vehicle', $data, ['id' => $id]
        );
    }

    /**
     * @param $registration
     * @param $vin
     *
     * @return int|null
     */
    public function fetchId($registration, $vin)
    {
        $dql = sprintf('SELECT dv.id FROM %s dv WHERE dv.registration = :registration AND dv.vin = :vin',
            DvlaVehicle::class);

        $query = $this->entityManager
            ->createQuery($dql)
            ->setParameters([
                'registration' => $registration,
                'vin' => $vin
            ]);

        $id = $query->getSingleScalarResult();

        return $id ?: null;
    }

    private function getConnection()
    {
        return $this->entityManager->getConnection();
    }

}
