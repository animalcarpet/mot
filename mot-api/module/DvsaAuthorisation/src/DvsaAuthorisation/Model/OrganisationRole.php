<?php

namespace DvsaAuthorisation\Model;

/**
 * Class OrganisationRole.
 */
class OrganisationRole extends Role
{
    protected $organisationId;

    /**
     * @return mixed
     */
    public function getOrganisationId()
    {
        return $this->organisationId;
    }

    /**
     * @param mixed $organisationId
     */
    public function setOrganisationId($organisationId)
    {
        $this->organisationId = $organisationId;
    }
}
