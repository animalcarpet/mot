<?php

namespace SiteApi\Service\Mapper;

use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;

/**
 * Class SiteBusinessRoleMapper.
 */
class SiteBusinessRoleMapper
{
    /**
     * @param SiteBusinessRole $role
     *
     * @return array
     */
    public function toArray(SiteBusinessRole $role)
    {
        return $role->getCode();
    }

    /**
     * @param SiteBusinessRole[] $roles
     *
     * @return array
     */
    public function manyToArray($roles)
    {
        $data = [];

        foreach ($roles as $role) {
            $data[] = $this->toArray($role);
        }

        return $data;
    }

    /**
     * @param SiteBusinessRoleMap[] $roles
     *
     * @return array
     */
    public function convertRoleMapToArray($siteBusinessRoleMap)
    {
        $roles = [];

        foreach ($siteBusinessRoleMap as $siteBusinessRoleMapObject) {
            $roles[] = $siteBusinessRoleMapObject->getSiteBusinessRole()->getName();
        }

        return $roles;
    }
}
