<?php

namespace DvsaAuthorisation\Model;

/**
 * Class SiteRole.
 */
class SiteRole extends Role
{
    protected $siteId;

    /**
     * @return mixed
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @param mixed $siteId
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
    }
}
