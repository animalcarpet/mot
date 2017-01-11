<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
use DvsaCommon\Utility\ArrayUtils;

class DvlaVehicleService
{
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
     * @return int
     */
    public function save(array $data)
    {
        $vehicleData = $this->prepare($data);
        $this->entityManager->getConnection()->insert('dvla_vehicle', $vehicleData);

        return (int)$this->entityManager->getConnection()->lastInsertId();
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
        $vehicleData["engine_number"] = ArrayUtils::tryGet($data, 'engine_number',  'BBB 1231231');
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
        $vehicleData["mot1_legacy_id"] = ArrayUtils::tryGet($data, 'mot1_legacy_id');
        $vehicleData['created_by'] = ArrayUtils::tryGet($data, 'created_by', 2);

        return $vehicleData;
    }

}
