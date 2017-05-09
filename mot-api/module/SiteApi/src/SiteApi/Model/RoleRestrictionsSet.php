<?php

namespace SiteApi\Model;

use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\SiteBusinessRole;

/**
 * Class RoleRestrictionsSet.
 */
class RoleRestrictionsSet
{
    private $restrictions = [];

    /**
     * @param $restrictions RoleRestrictionInterface[]
     */
    public function __construct(array $restrictions)
    {
        foreach ($restrictions as $restriction) {
            $this->restrictions[$restriction->getRole()] = $restriction;
        }
    }

    public function add(RoleRestrictionInterface $restriction)
    {
        $roleCode = $restriction->getRole();
        $this->restrictions[$roleCode] = $restriction;
    }

    /**
     * @param SiteBusinessRole $role
     *
     * @return RoleRestrictionInterface
     *
     * @throws \Exception
     */
    public function getRestrictionForRole(SiteBusinessRole $role)
    {
        $restriction = ArrayUtils::tryGet($this->restrictions, $role->getCode());
        if ($restriction) {
            return $restriction;
        } else {
            throw new \Exception('There are no known restrictions for role: "'.$role->getCode().'"');
        }
    }
}
