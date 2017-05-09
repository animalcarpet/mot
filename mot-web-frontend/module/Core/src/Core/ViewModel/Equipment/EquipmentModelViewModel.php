<?php

namespace Core\ViewModel\Equipment;

use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\Equipment\EquipmentModelDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\RomanNumeralsConverter;

class EquipmentModelViewModel
{
    private $equipmentModel;
    private $status;

    public function __construct(EquipmentModelDto $equipmentModel, $status)
    {
        $this->equipmentModel = $equipmentModel;
        $this->status = $status;
    }

    public function getTypeName()
    {
        return $this->equipmentModel->getTypeName();
    }

    public function getMakeName()
    {
        return $this->equipmentModel->getMakeName();
    }

    public function getName()
    {
        return $this->equipmentModel->getName();
    }

    public function getEquipmentIdentificationNumber()
    {
        return $this->equipmentModel->getEquipmentIdentificationNumber();
    }

    public function getSoftwareVersion()
    {
        return $this->equipmentModel->getSoftwareVersion();
    }

    public function getApprovalDate()
    {
        return DateTimeDisplayFormat::textDate($this->equipmentModel->getCertificationDate());
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Arranges vehicle classes as roman numerals separated by comas.
     *
     * @return string
     */
    public function getVehicleClassDisplay()
    {
        $classes = $this->equipmentModel->getVehicleClasses();
        $classesAsRomanNumerals = ArrayUtils::map(
            $classes, function (VehicleClassDto $vehicleClass) {
                return RomanNumeralsConverter::toRomanNumerals($vehicleClass->getName());
            }
        );

        $allClassesAsString = implode(', ', $classesAsRomanNumerals);

        return $allClassesAsString;
    }
}
