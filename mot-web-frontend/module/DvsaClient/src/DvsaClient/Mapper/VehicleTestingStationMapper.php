<?php

namespace DvsaClient\Mapper;

use DvsaClient\Entity\VehicleTestingStation;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Class VehicleTestingStationMapper.
 *
 * @deprecated Use SiteMapper. This class left for back compatibility with Slots
 */
class VehicleTestingStationMapper extends Mapper
{
    protected $entityClass = VehicleTestingStation::class;

    /**
     * @param int $vtsId
     *
     * @return array
     *
     * @deprecated do not use it
     */
    public function getById($vtsId)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::vtsById($vtsId);

        /** @var VehicleTestingStationDto $dto */
        $result = $this->client->get($apiUrl);
        $dto = DtoHydrator::jsonToDto($result['data']);

        return [
            'id' => $dto->getId(),
            'siteNumber' => $dto->getSiteNumber(),
            'name' => $dto->getName(),
            'address' => $dto->getContactByType(SiteContactTypeCode::BUSINESS)->getAddress(),
        ];
    }
}
