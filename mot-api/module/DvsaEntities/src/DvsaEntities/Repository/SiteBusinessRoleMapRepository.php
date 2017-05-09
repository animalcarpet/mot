<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommon\Enum\BusinessRoleStatusCode;

/**
 * Repository for {@link \DvsaEntities\Entity\SiteBusinessRoleMap}.
 */
class SiteBusinessRoleMapRepository extends EntityRepository
{
    public function getActiveUserRoles($personId)
    {
        return $this->getUserRoles($personId, BusinessRoleStatusCode::ACTIVE);
    }

    public function getPendingUserRoles($personId)
    {
        return $this->getUserRoles($personId, BusinessRoleStatusCode::PENDING);
    }

    private function getUserRoles($personId, $businessRoleStatusCode)
    {
        $qb = $this
            ->createQueryBuilder('srbm')
            ->addSelect(['p', 'br', 'st', 'site', 's_cnt', 'cnt_detail', 'addr'])
            ->innerJoin('srbm.person', 'p')
            ->innerJoin('srbm.businessRoleStatus', 'st')
            ->innerJoin('srbm.siteBusinessRole', 'br')
            ->innerJoin('srbm.site', 'site')
            ->leftJoin('site.contacts', 's_cnt')
            ->leftJoin('s_cnt.contactDetail', 'cnt_detail')
            ->leftJoin('cnt_detail.address', 'addr')
            ->where('p.id = :personId')
            ->andWhere('st.code in (:statusCode)')
            ->setParameter('personId', $personId)
            ->setParameter('statusCode', $businessRoleStatusCode);

        $roles = $qb->getQuery()->getResult();

        return $roles;
    }

    public function getActiveOrPendingUserRolesInASite($siteId, $personId)
    {
        $qb = $this
            ->createQueryBuilder('srbm')
            ->innerJoin('srbm.person', 'p')
            ->innerJoin('srbm.businessRoleStatus', 'st')
            ->innerJoin('srbm.siteBusinessRole', 'br')
            ->innerJoin('srbm.businessRoleStatus', 'rs')
            ->innerJoin('srbm.site', 'site')
            ->leftJoin('site.contacts', 's_cnt')
            ->leftJoin('s_cnt.contactDetail', 'cnt_detail')
            ->leftJoin('cnt_detail.address', 'addr')
            ->where('p.id = :personId')
            ->andWhere('site.id = :siteId')
            ->andWhere('st.code in (:statusCode)')
            ->setParameter('personId', $personId)
            ->setParameter('siteId', $siteId)
            ->setParameter('statusCode', BusinessRoleStatusCode::ACCEPTED)
            ->setParameter('statusCode', BusinessRoleStatusCode::PENDING);

        $roles = $qb->getQuery()->getResult();

        return $roles;
    }
}
