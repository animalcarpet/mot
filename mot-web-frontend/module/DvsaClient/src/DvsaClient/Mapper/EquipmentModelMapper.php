<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\Equipment\EquipmentModelDto;
use DvsaCommon\UrlBuilder\UrlBuilder;

/**
 * Class EquipmentModelMapper.
 */
class EquipmentModelMapper extends DtoMapper
{
    /**
     * @return EquipmentModelDto[]
     */
    public function getAll()
    {
        $path = UrlBuilder::equipmentModel();

        return $this->get($path);
    }
}
