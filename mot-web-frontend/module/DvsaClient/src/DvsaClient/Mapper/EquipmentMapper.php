<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\Equipment\EquipmentDto;

/**
 * Class EquipmentMapper.
 */
class EquipmentMapper extends DtoMapper
{
    /**
     * @param $vtsId
     *
     * @return EquipmentDto[]
     */
    public function fetchAllForVts($vtsId)
    {
        $url = 'vehicle-testing-station/'.$vtsId.'/equipment';

        return $this->get($url);
    }
}
