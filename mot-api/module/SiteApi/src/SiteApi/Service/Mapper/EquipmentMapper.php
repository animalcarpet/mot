<?php

namespace SiteApi\Service\Mapper;

use DvsaCommon\Dto\Equipment\EquipmentDto;
use DvsaCommonApi\Service\DateMappingUtils;
use DvsaEntities\Entity\Equipment;
use EquipmentApi\Mapper\EquipmentModelMapper;

/**
 * Class EquipmentMapper.
 */
class EquipmentMapper
{
    private $equipmentModelMapper;

    public function __construct()
    {
        $this->equipmentModelMapper = new EquipmentModelMapper();
    }

    /**
     * @param Equipment[] $equipments
     *
     * @return array
     */
    public function manyToDto($equipments)
    {
        $data = [];

        foreach ($equipments as $equipment) {
            $data[] = $this->toDto($equipment);
        }

        return $data;
    }

    /**
     * @param Equipment $equipment
     *
     * @return EquipmentDto
     */
    public function toDto(Equipment $equipment)
    {
        $dto = new EquipmentDto();
        $dto->setSerialNumber($equipment->getSerialNumber());
        $dto->setId($equipment->getIdentifier());

        $date = DateMappingUtils::extractDateTimeObject($equipment->getDateAdded());

        $dto->setDateAdded($date);

        $modelDto = $this->equipmentModelMapper->toDto($equipment->getEquipmentModel());
        $dto->setModel($modelDto);

        return $dto;
    }
}
