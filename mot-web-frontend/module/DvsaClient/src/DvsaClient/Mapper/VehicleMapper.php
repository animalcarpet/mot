<?php

namespace DvsaClient\Mapper;

use DvsaCommon\UrlBuilder\VehicleUrlBuilder;

/**
 * Class VehicleMapper.
 */
class VehicleMapper extends DtoMapper
{
    /**
     * @param $id
     *
     * @return \DvsaCommon\Dto\Vehicle\VehicleDto
     */
    public function getById($id)
    {
        $url = VehicleUrlBuilder::vehicle($id);

        return $this->get($url);
    }

    /**
     * @param $id
     *
     * @return \DvsaCommon\Dto\Vehicle\VehicleDto
     */
    public function getDvlaById($id)
    {
        $url = VehicleUrlBuilder::dvlaVehicle($id);

        return $this->get($url);
    }
}
