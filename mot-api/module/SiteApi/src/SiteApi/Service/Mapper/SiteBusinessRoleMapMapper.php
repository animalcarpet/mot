<?php

namespace SiteApi\Service\Mapper;

use DvsaCommon\Utility\Hydrator;
use DvsaCommonApi\Service\DateMappingUtils;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use OrganisationApi\Service\Mapper\PersonMapper;

/**
 * Class SiteBusinessRoleMapMapper.
 */
class SiteBusinessRoleMapMapper
{
    private $roleMapper;
    private $personMapper;

    public function __construct(Hydrator $hydrator)
    {
        $this->roleMapper = new SiteBusinessRoleMapper();
        $this->personMapper = new PersonMapper();
    }

    /**
     * @param SiteBusinessRoleMap[] $maps
     *
     * @return array
     */
    public function manyToArray($maps)
    {
        $data = [];

        foreach ($maps as $map) {
            $data[] = $this->toArray($map);
        }

        return $data;
    }

    /**
     * @param SiteBusinessRoleMap $map
     *
     * @return array
     */
    public function toArray(SiteBusinessRoleMap $map)
    {
        $data = [];
        $data['role'] = $this->roleMapper->toArray($map->getSiteBusinessRole());
        $data['person'] = $this->personMapper->toArray($map->getPerson());
        $data['id'] = $map->getId();
        $data['status'] = $map->getBusinessRoleStatus()->getCode();
        $data['id'] = $map->getId();
        if ($map->getValidFrom()) {
            $data['actionedOn'] = DateMappingUtils::extractDateTimeObject($map->getValidFrom());
        } else {
            $data['actionedOn'] = DateMappingUtils::extractDateTimeObject($map->getCreatedOn());
        }
        $data['_clazz'] = 'SitePosition';

        return $data;
    }
}
