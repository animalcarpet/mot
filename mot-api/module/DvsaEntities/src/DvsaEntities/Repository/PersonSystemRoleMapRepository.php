<?php

namespace DvsaEntities\Repository;

use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaEntities\Entity\PersonSystemRoleMap;

/**
 * Repository for {@link \DvsaEntities\Entity\PersonSystemRoleMap}.
 */
class PersonSystemRoleMapRepository extends AbstractMutableRepository
{
    /**
     * @param $personId
     *
     * @return PersonSystemRoleMap[]
     */
    public function getActiveUserRoles($personId)
    {
        return $this->getUserRoles($personId, BusinessRoleStatusCode::ACTIVE);
    }

    /**
     * @param $personId
     *
     * @return PersonSystemRoleMap[]
     */
    public function getPendingUserRoles($personId)
    {
        return $this->getUserRoles($personId, BusinessRoleStatusCode::PENDING);
    }

    private function getUserRoles($personId, $businessRoleStatusCode)
    {
        $qb = $this->createQueryBuilder('srbm')
            ->innerJoin('srbm.person', 'p')
            ->innerJoin('srbm.businessRoleStatus', 'st')
            ->innerJoin('srbm.personSystemRole', 'sr')
            ->innerJoin('srbm.businessRoleStatus', 'rs')
            ->where('p.id = :personId')
            ->andWhere('st.code in (:statusCode)')
            ->setParameter('personId', $personId)
            ->setParameter('statusCode', $businessRoleStatusCode);

        $roles = $qb->getQuery()->getResult();

        return $roles;
    }

    /**
     * @param int $personId
     * @param int $personSystemRoleId
     *
     * @return null|PersonSystemRoleMap
     */
    public function findByPersonAndSystemRole($personId, $personSystemRoleId)
    {
        return $this->findOneBy(['person' => $personId, 'personSystemRole' => $personSystemRoleId]);
    }

    /**
     * Return person's internal roles only.
     *
     * @param int $personId
     *
     * @return array
     */
    public function getPersonActiveInternalRoleCodes($personId)
    {
        $qb = $this->createQueryBuilder('psrm')
            ->select('r.code')
            ->innerJoin('psrm.businessRoleStatus', 'brs')
            ->innerJoin('psrm.person', 'p')
            ->innerJoin('psrm.personSystemRole', 'psr')
            ->innerJoin('psr.role', 'r')
            ->where('p.id = :personId')
            ->andWhere('r.isInternal = 1')
            ->andWhere('brs.code = :statusCode')
            ->setParameter('personId', $personId)
            ->setParameter('statusCode', BusinessRoleStatusCode::ACTIVE);

        $internalRoleCodes = $qb->getQuery()->getResult();

        return $internalRoleCodes;
    }
}
