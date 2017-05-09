<?php

namespace DvsaClient\Mapper;

/**
 * Class OrganisationRoleMapper.
 */
class OrganisationRoleMapper extends DtoMapper
{
    const CLASS_PATH = __CLASS__;

    /**
     * @param $organisationId
     * @param $personId
     *
     * @return string[]
     */
    public function fetchAllForPerson($organisationId, $personId)
    {
        $url = 'organisation/'.$organisationId.'/person/'.$personId.'/role';

        return $this->get($url);
    }
}
