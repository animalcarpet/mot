<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\MotTesting\MotTestInProgressDto;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilder;

/**
 * Class MotTestInProgressMapper.
 */
class MotTestInProgressMapper extends DtoMapper
{
    /**
     * @param $vtsId
     *
     * @return MotTestInProgressDto[]
     */
    public function fetchAllForVts($vtsId)
    {
        $url = VehicleTestingStationUrlBuilder::testInProgress($vtsId)->toString();

        return $this->get($url);
    }

    public function getCount($vtsId)
    {
        $url = VehicleTestingStationUrlBuilder::testInProgressCount($vtsId);

        return $this->get($url);
    }
}
