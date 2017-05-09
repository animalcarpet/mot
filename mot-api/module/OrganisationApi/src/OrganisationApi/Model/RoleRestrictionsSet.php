<?php

namespace OrganisationApi\Model;

use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\OrganisationBusinessRole;

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
            $this->add($restriction);
        }
    }

    public function add(RoleRestrictionInterface $restriction)
    {
        $roleName = $restriction->getRole();
        $this->restrictions[$roleName] = $restriction;
    }

    public function getRestrictionForRole(OrganisationBusinessRole $role)
    {
        $restriction = ArrayUtils::tryGet($this->restrictions, $role->getName());
        if ($restriction) {
            return $restriction;
        } else {
            throw new \RuntimeException('There are no known restrictions for role: "'.$role->getName().'"');
        }
    }
}
