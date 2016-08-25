<?php

namespace Application\Model;

class RoleSummaryCollection
{
    private $organisationRoleData;

    private $siteRoleData;

    public function __construct(array $roleData)
    {
        $this->organisationRoleData = isset($roleData['organisations']) ? $roleData['organisations'] : [];
        $this->siteRoleData = isset($roleData['sites']) ? $roleData['sites'] : [];
    }

    public function isEmpty()
    {
        return empty($this->organisationRoleData) && empty($this->siteRoleData);
    }

    public function containsOrganisationRole($role)
    {
        return $this->isRoleInRoleData($role, $this->organisationRoleData);
    }

    public function containsSiteRole($role)
    {
        return $this->isRoleInRoleData($role, $this->siteRoleData);
    }

    public function containsRole($role)
    {
        return $this->containsOrganisationRole($role) || $this->containsSiteRole($role);
    }

    private function isRoleInRoleData($role, array $roleData)
    {
        foreach ($roleData as $datum) {
            if (isset($datum['roles']) && in_array($role, $datum['roles'])) {
                return true;
            }
        }

        return false;
    }
}
