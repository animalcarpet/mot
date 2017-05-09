<?php

namespace DvsaCommonApi\Service\Mapper;

use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaEntities\Entity\VehicleClass;

/**
 * Class VehicleClassMapper.
 */
class VehicleClassMapper extends AbstractStaticDataMapper
{
    /**
     * @param \DvsaEntities\Entity\VehicleClass $entity
     * @param string                            $dtoClazz
     *
     * @return VehicleClassDto
     */
    public function toDto($entity, $dtoClazz = null)
    {
        if (!($entity instanceof VehicleClass)) {
            throw new \InvalidArgumentException('$entity argument must be instance of VehicleClass');
        }

        /** @var VehicleClassDto $dto */
        $dto = parent::toDto($entity, VehicleClassDto::class);

        $dto->setGroup($entity->getGroup());

        return $dto;
    }
}
