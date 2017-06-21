<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\QualificationAnnualCertificate;

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

    public function getTestersWithTheirAnnualAssessmentsForGroupA($siteId)
    {
        return $this->getTestersWithTheirAnnualAssessments(
            $siteId,
            VehicleClassGroup::getGroupAClasses(),
            VehicleClassGroupCode::BIKES
        );
    }

    public function getTestersWithTheirAnnualAssessmentsForGroupB($siteId)
    {
        return $this->getTestersWithTheirAnnualAssessments(
            $siteId,
            VehicleClassGroup::getGroupBClasses(),
            VehicleClassGroupCode::CARS_ETC
        );
    }

    private function getTestersWithTheirAnnualAssessments($siteId, $testClasses, $groupCode)
    {
        $subqb = $this->getEntityManager()->createQueryBuilder()
            ->select(["sqac.id"])
            ->from(QualificationAnnualCertificate::class, "sqac")
            ->innerJoin('sqac.vehicleClassGroup', 'svcg')
            ->innerJoin("sqac.person", "sp")
            ->innerJoin("sp.siteBusinessRoleMaps", "ssbrm")
            ->innerJoin('ssbrm.site', 'ssite')
            ->where("ssite.id = :siteId")
            ->andWhere('svcg.code = :vehicleClassGroupCode OR svcg.code is NULL')
            ->groupBy('sp.id');

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select(['p.id', 'p.username', 'p.firstName', 'p.middleName', 'p.familyName', 'MAX(qac.dateAwarded) AS dateAwarded'])
            ->from(Person::class, "p")
            ->innerJoin("p.siteBusinessRoleMaps", "sbrm")
            ->innerJoin('sbrm.siteBusinessRole', 'sbr')
            ->innerJoin('sbrm.site', 'site')
            ->innerJoin('p.authorisationsForTestingMot', 'auth')
            ->innerJoin('auth.status', 'authStatus')
            ->leftJoin(QualificationAnnualCertificate::class, 'qac', Join::WITH, 'p.id = qac.person AND qac.id IN (' . $subqb->getDQL() . ')')
            ->where("sbr.code = :roleCode")
            ->andWhere('site.id = :siteId')
            ->andWhere('authStatus.code IN (:authStatus)')
            ->andWhere('auth.vehicleClass in (:vehicleClasses)')
            ->setParameter('vehicleClasses', $testClasses)
            ->setParameter('roleCode', SiteBusinessRoleCode::TESTER)
            ->setParameter('siteId', $siteId)
            ->setParameter('authStatus', AuthorisationForTestingMotStatusCode::getAll())
            ->setParameter('vehicleClassGroupCode', $groupCode)
            ->groupBy('p.id')
            ->orderBy('p.familyName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }
}
