<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for {@link \DvsaEntities\Entity\Role}.
 */
class RoleRepository extends EntityRepository
{
    /**
     * Returns an array of all the internal roles.
     *
     * @return array
     */
    public function getAllInternalRoles()
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->where('r.isInternal = 1');

        $roles = $qb->getQuery()->getResult();

        return $roles;
    }

    /**
     * Returns an array of all the trade roles.
     *
     * @return array
     */
    public function getAllTradeRoles()
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->where('r.isTrade = 1');

        $roles = $qb->getQuery()->getResult();

        return $roles;
    }
}
