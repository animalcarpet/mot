<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\UrlBuilder\OrganisationUrlBuilder;

/**
 * Class OrganisationPositionMapper
 *
 * @package DvsaClient\Mapper
 */
class OrganisationPositionMapper extends DtoMapper implements BusinessPositionMapperInterface
{
    /**
     * @param $organisationId
     *
     * @return OrganisationPositionDto[]
     */
    public function fetchAllPositionsForOrganisation($organisationId)
    {
        $url = OrganisationUrlBuilder::position($organisationId);

        return $this->get($url);
    }

    public function createPosition($organisationId, $nomineeId, $roleId)
    {
        $apiUrl = OrganisationUrlBuilder::position($organisationId);
        $data = [
            'nomineeId' => $nomineeId,
            'roleId'    => $roleId
        ];

        return parent::post($apiUrl, $data);
    }

    /**
     * Removes position in organisation from a person
     *
     * @param $workplaceId
     * @param $positionId
     */
    public function deletePosition($workplaceId, $positionId)
    {
        $urlBuilder = OrganisationUrlBuilder::position($workplaceId, $positionId);

        parent::delete($urlBuilder->toString());
    }
}
