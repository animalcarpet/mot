<?php

namespace DvsaClient\Mapper;

use DvsaClient\Entity\Role;

/**
 * Class SiteRoleMapper.
 */
class SiteRoleMapper extends Mapper
{
    protected $entityClass = Role::class;

    /**
     * @param $siteId
     * @param $personId
     *
     * @return string[]
     *
     * @see SiteBusinessRoleCode
     */
    public function fetchAllForPerson($siteId, $personId)
    {
        $url = 'site/'.$siteId.'/person/'.$personId.'/role';
        $roleCodes = $this->client->get($url)['data'];

        return $roleCodes;
    }
}
