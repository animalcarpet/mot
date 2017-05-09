<?php

namespace EquipmentApi\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Equipment\EquipmentModelDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaEntities\Entity\EquipmentModel;

/**
 * Class EquipmentMapper.
 */
class EquipmentModelMapper
{
    public function toDto(EquipmentModel $equipmentModel)
    {
        $dto = new EquipmentModelDto();
        $dto->setName($equipmentModel->getName());
        $dto->setCode($equipmentModel->getCode());
        $dto->setMakeName($equipmentModel->getMake()->getName());
        $dto->setTypeName($equipmentModel->getType()->getName());
        $dto->setStatus($equipmentModel->getStatus()->getCode());

        $dto->setEquipmentIdentificationNumber($equipmentModel->getEquipmentIdentificationNumber());
        $dto->setCertificationDate(DateTimeApiFormat::date($equipmentModel->getCertificationDate()));
        $dto->setSoftwareVersion($equipmentModel->getSoftwareVersion());

        $vDtoStack = [];
        foreach ($equipmentModel->getVehiclesClasses() as $vehicleClass) {
            $vDto = new VehicleClassDto();
            $vDto->setId($vehicleClass->getId());
            $vDto->setName($vehicleClass->getName());
            $vDtoStack[] = $vDto;
        }
        $dto->setVehicleClasses($vDtoStack);

        return $dto;
    }

    /**
     * @param $equipmentModels EquipmentModel[]
     *
     * @return EquipmentModelDto[]
     */
    public function manyToDto($equipmentModels)
    {
        $stack = [];
        foreach ($equipmentModels as $model) {
            $stack[] = $this->toDto($model);
        }

        return $stack;
    }
}
